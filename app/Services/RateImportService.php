<?php

namespace App\Services;

use App\Models\CarrierRate;
use App\Models\CustomerRate;
use App\Models\DestinationPrefix;
use App\Models\RateImport;
use App\Models\RatePlanRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RateImportService
{
    protected RateImport $import;
    protected array $errors = [];
    protected int $imported = 0;
    protected int $failed = 0;
    protected int $skipped = 0;

    /**
     * Process a rate import
     */
    public function process(RateImport $import): bool
    {
        $this->import = $import;
        $this->errors = [];
        $this->imported = 0;
        $this->failed = 0;
        $this->skipped = 0;

        $import->markAsProcessing();

        try {
            $filePath = Storage::path($import->filename);

            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$import->filename}");
            }

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception("Could not open file: {$import->filename}");
            }

            // Read header
            $header = fgetcsv($handle);
            if (!$header) {
                throw new \Exception("Empty file or invalid CSV format");
            }

            $header = array_map('strtolower', array_map('trim', $header));
            $this->validateHeader($header, $import->type);

            $rowNumber = 1;
            $totalRows = 0;

            // Count total rows
            while (fgetcsv($handle) !== false) {
                $totalRows++;
            }
            rewind($handle);
            fgetcsv($handle); // Skip header again

            $import->update(['total_rows' => $totalRows]);

            // Process rows in chunks
            $batch = [];
            $batchSize = 100;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                try {
                    $data = $this->parseRow($header, $row, $rowNumber);
                    if ($data === null) {
                        $this->skipped++;
                        continue;
                    }

                    $batch[] = $data;

                    if (count($batch) >= $batchSize) {
                        $this->processBatch($batch, $import->type);
                        $batch = [];
                        $this->updateProgress();
                    }
                } catch (\Exception $e) {
                    $this->errors[] = "Row {$rowNumber}: {$e->getMessage()}";
                    $this->failed++;
                }
            }

            // Process remaining batch
            if (!empty($batch)) {
                $this->processBatch($batch, $import->type);
            }

            fclose($handle);

            $import->update([
                'imported_rows' => $this->imported,
                'failed_rows' => $this->failed,
                'skipped_rows' => $this->skipped,
                'errors' => $this->errors,
            ]);

            $import->markAsCompleted();

            return true;

        } catch (\Exception $e) {
            Log::error("Rate import failed: " . $e->getMessage(), [
                'import_id' => $import->id,
                'exception' => $e,
            ]);
            $import->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Validate CSV header based on import type
     */
    protected function validateHeader(array $header, string $type): void
    {
        $required = match($type) {
            'destinations' => ['prefix'],
            'carrier' => ['prefix', 'cost_per_minute'],
            'customer' => ['prefix', 'price_per_minute'],
            'rate_plan' => ['prefix', 'price_per_minute'],
            default => throw new \Exception("Unknown import type: {$type}"),
        };

        $missing = array_diff($required, $header);
        if (!empty($missing)) {
            throw new \Exception("Missing required columns: " . implode(', ', $missing));
        }
    }

    /**
     * Parse a CSV row into data array
     */
    protected function parseRow(array $header, array $row, int $rowNumber): ?array
    {
        if (count($row) < count($header)) {
            throw new \Exception("Insufficient columns");
        }

        $data = array_combine($header, array_slice($row, 0, count($header)));

        // Skip empty rows
        if (empty($data['prefix']) || trim($data['prefix']) === '') {
            return null;
        }

        // Clean and validate prefix
        $data['prefix'] = preg_replace('/[^0-9*]/', '', trim($data['prefix']));
        if (empty($data['prefix'])) {
            throw new \Exception("Invalid prefix");
        }

        // Parse numeric fields
        if (isset($data['cost_per_minute'])) {
            $data['cost_per_minute'] = $this->parseDecimal($data['cost_per_minute']);
        }
        if (isset($data['price_per_minute'])) {
            $data['price_per_minute'] = $this->parseDecimal($data['price_per_minute']);
        }
        if (isset($data['connection_fee'])) {
            $data['connection_fee'] = $this->parseDecimal($data['connection_fee']);
        }
        if (isset($data['billing_increment'])) {
            $data['billing_increment'] = (int) $data['billing_increment'];
        }
        if (isset($data['min_duration'])) {
            $data['min_duration'] = (int) $data['min_duration'];
        }

        // Parse date fields
        if (isset($data['effective_date']) && !empty($data['effective_date'])) {
            $data['effective_date'] = $this->parseDate($data['effective_date']);
        } else {
            $data['effective_date'] = now()->toDateString();
        }

        if (isset($data['end_date']) && !empty($data['end_date'])) {
            $data['end_date'] = $this->parseDate($data['end_date']);
        }

        return $data;
    }

    /**
     * Process a batch of rows
     */
    protected function processBatch(array $batch, string $type): void
    {
        DB::beginTransaction();
        try {
            foreach ($batch as $data) {
                $this->processRow($data, $type);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process a single row
     */
    protected function processRow(array $data, string $type): void
    {
        switch ($type) {
            case 'destinations':
                $this->importDestination($data);
                break;
            case 'carrier':
                $this->importCarrierRate($data);
                break;
            case 'customer':
                $this->importCustomerRate($data);
                break;
            case 'rate_plan':
                $this->importRatePlanRate($data);
                break;
        }

        $this->imported++;
    }

    /**
     * Import a destination prefix
     */
    protected function importDestination(array $data): void
    {
        DestinationPrefix::updateOrCreate(
            ['prefix' => $data['prefix']],
            [
                'country_code' => $data['country_code'] ?? null,
                'country_name' => $data['country_name'] ?? $data['country'] ?? null,
                'region' => $data['region'] ?? null,
                'description' => $data['description'] ?? null,
                'is_mobile' => $this->parseBool($data['is_mobile'] ?? false),
                'is_premium' => $this->parseBool($data['is_premium'] ?? false),
                'active' => $this->parseBool($data['active'] ?? true),
            ]
        );
    }

    /**
     * Import a carrier rate
     */
    protected function importCarrierRate(array $data): void
    {
        // Ensure destination prefix exists
        $prefix = DestinationPrefix::firstOrCreate(
            ['prefix' => $data['prefix']],
            [
                'country_name' => $data['country_name'] ?? $data['country'] ?? null,
                'region' => $data['region'] ?? null,
            ]
        );

        CarrierRate::updateOrCreate(
            [
                'carrier_id' => $this->import->carrier_id,
                'destination_prefix_id' => $prefix->id,
                'effective_date' => $data['effective_date'],
            ],
            [
                'cost_per_minute' => $data['cost_per_minute'],
                'connection_fee' => $data['connection_fee'] ?? 0,
                'billing_increment' => $data['billing_increment'] ?? 1,
                'min_duration' => $data['min_duration'] ?? 0,
                'end_date' => $data['end_date'] ?? null,
                'active' => true,
            ]
        );
    }

    /**
     * Import a customer rate
     */
    protected function importCustomerRate(array $data): void
    {
        // Ensure destination prefix exists
        $prefix = DestinationPrefix::firstOrCreate(
            ['prefix' => $data['prefix']],
            [
                'country_name' => $data['country_name'] ?? $data['country'] ?? null,
                'region' => $data['region'] ?? null,
            ]
        );

        CustomerRate::updateOrCreate(
            [
                'customer_id' => $this->import->customer_id,
                'destination_prefix_id' => $prefix->id,
                'effective_date' => $data['effective_date'],
            ],
            [
                'price_per_minute' => $data['price_per_minute'],
                'connection_fee' => $data['connection_fee'] ?? 0,
                'billing_increment' => $data['billing_increment'] ?? 1,
                'min_duration' => $data['min_duration'] ?? 0,
                'end_date' => $data['end_date'] ?? null,
                'active' => true,
            ]
        );
    }

    /**
     * Import a rate plan rate
     */
    protected function importRatePlanRate(array $data): void
    {
        // Ensure destination prefix exists
        $prefix = DestinationPrefix::firstOrCreate(
            ['prefix' => $data['prefix']],
            [
                'country_name' => $data['country_name'] ?? $data['country'] ?? null,
                'region' => $data['region'] ?? null,
            ]
        );

        RatePlanRate::updateOrCreate(
            [
                'rate_plan_id' => $this->import->rate_plan_id,
                'destination_prefix_id' => $prefix->id,
                'effective_date' => $data['effective_date'],
            ],
            [
                'price_per_minute' => $data['price_per_minute'],
                'connection_fee' => $data['connection_fee'] ?? 0,
                'billing_increment' => $data['billing_increment'] ?? null,
                'min_duration' => $data['min_duration'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'active' => true,
            ]
        );
    }

    /**
     * Update import progress
     */
    protected function updateProgress(): void
    {
        $this->import->update([
            'imported_rows' => $this->imported,
            'failed_rows' => $this->failed,
            'skipped_rows' => $this->skipped,
            'errors' => $this->errors,
        ]);
    }

    /**
     * Parse decimal value from string
     */
    protected function parseDecimal(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Handle comma as decimal separator
        $value = str_replace(',', '.', trim((string) $value));
        $value = preg_replace('/[^0-9.]/', '', $value);

        if (!is_numeric($value)) {
            throw new \Exception("Invalid numeric value");
        }

        return (float) $value;
    }

    /**
     * Parse date from string
     */
    protected function parseDate(string $value): string
    {
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, trim($value));
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        throw new \Exception("Invalid date format: {$value}");
    }

    /**
     * Parse boolean from various formats
     */
    protected function parseBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes', 'y', 'si', 'sí']);
    }
}
