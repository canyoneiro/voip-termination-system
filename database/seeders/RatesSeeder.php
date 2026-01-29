<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\CarrierRate;
use App\Models\Customer;
use App\Models\CustomerRate;
use App\Models\DestinationPrefix;
use Illuminate\Database\Seeder;

class RatesSeeder extends Seeder
{
    public function run(): void
    {
        // Create destination prefixes for common countries
        $prefixes = [
            // Spain
            ['prefix' => '34', 'country_name' => 'Spain', 'description' => 'Spain - All', 'country_code' => 'ES', 'is_mobile' => false],
            ['prefix' => '346', 'country_name' => 'Spain', 'description' => 'Spain - Mobile', 'country_code' => 'ES', 'is_mobile' => true],
            ['prefix' => '349', 'country_name' => 'Spain', 'description' => 'Spain - Premium', 'country_code' => 'ES', 'is_premium' => true],

            // Portugal
            ['prefix' => '351', 'country_name' => 'Portugal', 'description' => 'Portugal - All', 'country_code' => 'PT', 'is_mobile' => false],
            ['prefix' => '3519', 'country_name' => 'Portugal', 'description' => 'Portugal - Mobile', 'country_code' => 'PT', 'is_mobile' => true],

            // France
            ['prefix' => '33', 'country_name' => 'France', 'description' => 'France - All', 'country_code' => 'FR', 'is_mobile' => false],
            ['prefix' => '336', 'country_name' => 'France', 'description' => 'France - Mobile', 'country_code' => 'FR', 'is_mobile' => true],
            ['prefix' => '337', 'country_name' => 'France', 'description' => 'France - Mobile', 'country_code' => 'FR', 'is_mobile' => true],

            // Germany
            ['prefix' => '49', 'country_name' => 'Germany', 'description' => 'Germany - All', 'country_code' => 'DE', 'is_mobile' => false],
            ['prefix' => '4915', 'country_name' => 'Germany', 'description' => 'Germany - Mobile', 'country_code' => 'DE', 'is_mobile' => true],
            ['prefix' => '4916', 'country_name' => 'Germany', 'description' => 'Germany - Mobile', 'country_code' => 'DE', 'is_mobile' => true],
            ['prefix' => '4917', 'country_name' => 'Germany', 'description' => 'Germany - Mobile', 'country_code' => 'DE', 'is_mobile' => true],

            // UK
            ['prefix' => '44', 'country_name' => 'United Kingdom', 'description' => 'UK - All', 'country_code' => 'GB', 'is_mobile' => false],
            ['prefix' => '447', 'country_name' => 'United Kingdom', 'description' => 'UK - Mobile', 'country_code' => 'GB', 'is_mobile' => true],

            // Italy
            ['prefix' => '39', 'country_name' => 'Italy', 'description' => 'Italy - All', 'country_code' => 'IT', 'is_mobile' => false],
            ['prefix' => '393', 'country_name' => 'Italy', 'description' => 'Italy - Mobile', 'country_code' => 'IT', 'is_mobile' => true],

            // USA
            ['prefix' => '1', 'country_name' => 'USA/Canada', 'description' => 'USA/Canada - All', 'country_code' => 'US', 'is_mobile' => false],

            // Mexico
            ['prefix' => '52', 'country_name' => 'Mexico', 'description' => 'Mexico - All', 'country_code' => 'MX', 'is_mobile' => false],
            ['prefix' => '521', 'country_name' => 'Mexico', 'description' => 'Mexico - Mobile', 'country_code' => 'MX', 'is_mobile' => true],

            // Colombia
            ['prefix' => '57', 'country_name' => 'Colombia', 'description' => 'Colombia - All', 'country_code' => 'CO', 'is_mobile' => false],
            ['prefix' => '573', 'country_name' => 'Colombia', 'description' => 'Colombia - Mobile', 'country_code' => 'CO', 'is_mobile' => true],

            // Argentina
            ['prefix' => '54', 'country_name' => 'Argentina', 'description' => 'Argentina - All', 'country_code' => 'AR', 'is_mobile' => false],
            ['prefix' => '549', 'country_name' => 'Argentina', 'description' => 'Argentina - Mobile', 'country_code' => 'AR', 'is_mobile' => true],

            // Chile
            ['prefix' => '56', 'country_name' => 'Chile', 'description' => 'Chile - All', 'country_code' => 'CL', 'is_mobile' => false],
            ['prefix' => '569', 'country_name' => 'Chile', 'description' => 'Chile - Mobile', 'country_code' => 'CL', 'is_mobile' => true],

            // Peru
            ['prefix' => '51', 'country_name' => 'Peru', 'description' => 'Peru - All', 'country_code' => 'PE', 'is_mobile' => false],
            ['prefix' => '519', 'country_name' => 'Peru', 'description' => 'Peru - Mobile', 'country_code' => 'PE', 'is_mobile' => true],

            // Brazil
            ['prefix' => '55', 'country_name' => 'Brazil', 'description' => 'Brazil - All', 'country_code' => 'BR', 'is_mobile' => false],
            ['prefix' => '559', 'country_name' => 'Brazil', 'description' => 'Brazil - Mobile', 'country_code' => 'BR', 'is_mobile' => true],

            // Netherlands
            ['prefix' => '31', 'country_name' => 'Netherlands', 'description' => 'Netherlands - All', 'country_code' => 'NL', 'is_mobile' => false],
            ['prefix' => '316', 'country_name' => 'Netherlands', 'description' => 'Netherlands - Mobile', 'country_code' => 'NL', 'is_mobile' => true],

            // Belgium
            ['prefix' => '32', 'country_name' => 'Belgium', 'description' => 'Belgium - All', 'country_code' => 'BE', 'is_mobile' => false],
            ['prefix' => '324', 'country_name' => 'Belgium', 'description' => 'Belgium - Mobile', 'country_code' => 'BE', 'is_mobile' => true],

            // Switzerland
            ['prefix' => '41', 'country_name' => 'Switzerland', 'description' => 'Switzerland - All', 'country_code' => 'CH', 'is_mobile' => false],
            ['prefix' => '417', 'country_name' => 'Switzerland', 'description' => 'Switzerland - Mobile', 'country_code' => 'CH', 'is_mobile' => true],

            // Poland
            ['prefix' => '48', 'country_name' => 'Poland', 'description' => 'Poland - All', 'country_code' => 'PL', 'is_mobile' => false],
            ['prefix' => '485', 'country_name' => 'Poland', 'description' => 'Poland - Mobile', 'country_code' => 'PL', 'is_mobile' => true],

            // Morocco
            ['prefix' => '212', 'country_name' => 'Morocco', 'description' => 'Morocco - All', 'country_code' => 'MA', 'is_mobile' => false],
            ['prefix' => '2126', 'country_name' => 'Morocco', 'description' => 'Morocco - Mobile', 'country_code' => 'MA', 'is_mobile' => true],

            // China
            ['prefix' => '86', 'country_name' => 'China', 'description' => 'China - All', 'country_code' => 'CN', 'is_mobile' => false],
            ['prefix' => '861', 'country_name' => 'China', 'description' => 'China - Mobile', 'country_code' => 'CN', 'is_mobile' => true],
        ];

        $createdPrefixes = [];
        foreach ($prefixes as $data) {
            $prefix = DestinationPrefix::firstOrCreate(
                ['prefix' => $data['prefix']],
                $data
            );
            $createdPrefixes[$data['prefix']] = $prefix;
        }

        $this->command->info('Created ' . count($createdPrefixes) . ' destination prefixes');

        // Get carriers and customers
        $carriers = Carrier::all();
        $customers = Customer::all();

        if ($carriers->isEmpty()) {
            $this->command->warn('No carriers found. Skipping carrier rates.');
            return;
        }

        // Create carrier rates (cost prices)
        $carrierRatesData = [
            // Base rates vary by carrier
            '34' => [0.008, 0.009, 0.010, 0.0085, 0.0095],
            '346' => [0.025, 0.028, 0.030, 0.026, 0.029],
            '349' => [0.150, 0.160, 0.155, 0.145, 0.165],
            '351' => [0.012, 0.013, 0.011, 0.0125, 0.0135],
            '3519' => [0.035, 0.038, 0.036, 0.034, 0.039],
            '33' => [0.010, 0.011, 0.0095, 0.0105, 0.0115],
            '336' => [0.045, 0.048, 0.046, 0.044, 0.049],
            '337' => [0.045, 0.048, 0.046, 0.044, 0.049],
            '49' => [0.009, 0.010, 0.0085, 0.0095, 0.0105],
            '4915' => [0.055, 0.058, 0.056, 0.054, 0.059],
            '4916' => [0.055, 0.058, 0.056, 0.054, 0.059],
            '4917' => [0.055, 0.058, 0.056, 0.054, 0.059],
            '44' => [0.008, 0.009, 0.0075, 0.0085, 0.0095],
            '447' => [0.040, 0.043, 0.041, 0.039, 0.044],
            '39' => [0.010, 0.011, 0.0095, 0.0105, 0.0115],
            '393' => [0.048, 0.051, 0.049, 0.047, 0.052],
            '1' => [0.005, 0.006, 0.0055, 0.0052, 0.0058],
            '52' => [0.015, 0.016, 0.0145, 0.0155, 0.0165],
            '521' => [0.045, 0.048, 0.046, 0.044, 0.049],
            '57' => [0.020, 0.022, 0.021, 0.019, 0.023],
            '573' => [0.055, 0.058, 0.056, 0.054, 0.059],
            '54' => [0.018, 0.020, 0.019, 0.017, 0.021],
            '549' => [0.065, 0.068, 0.066, 0.064, 0.069],
            '56' => [0.016, 0.018, 0.017, 0.015, 0.019],
            '569' => [0.050, 0.053, 0.051, 0.049, 0.054],
            '51' => [0.014, 0.016, 0.015, 0.013, 0.017],
            '519' => [0.042, 0.045, 0.043, 0.041, 0.046],
            '55' => [0.025, 0.028, 0.026, 0.024, 0.029],
            '559' => [0.075, 0.078, 0.076, 0.074, 0.079],
            '31' => [0.009, 0.010, 0.0085, 0.0095, 0.0105],
            '316' => [0.042, 0.045, 0.043, 0.041, 0.046],
            '32' => [0.010, 0.011, 0.0095, 0.0105, 0.0115],
            '324' => [0.044, 0.047, 0.045, 0.043, 0.048],
            '41' => [0.012, 0.013, 0.0115, 0.0125, 0.0135],
            '417' => [0.065, 0.068, 0.066, 0.064, 0.069],
            '48' => [0.008, 0.009, 0.0075, 0.0085, 0.0095],
            '485' => [0.035, 0.038, 0.036, 0.034, 0.039],
            '212' => [0.095, 0.100, 0.098, 0.093, 0.102],
            '2126' => [0.180, 0.190, 0.185, 0.175, 0.195],
            '86' => [0.008, 0.009, 0.0075, 0.0085, 0.0095],
            '861' => [0.012, 0.013, 0.0115, 0.0125, 0.0135],
        ];

        $carrierRateCount = 0;
        foreach ($carriers as $index => $carrier) {
            foreach ($createdPrefixes as $prefixCode => $prefix) {
                if (isset($carrierRatesData[$prefixCode])) {
                    $cost = $carrierRatesData[$prefixCode][$index % 5] ?? $carrierRatesData[$prefixCode][0];

                    CarrierRate::firstOrCreate(
                        [
                            'carrier_id' => $carrier->id,
                            'destination_prefix_id' => $prefix->id,
                        ],
                        [
                            'cost_per_minute' => $cost,
                            'connection_fee' => 0,
                            'billing_increment' => 6,
                            'min_duration' => 0,
                            'effective_date' => now()->subDays(30)->toDateString(),
                            'active' => true,
                        ]
                    );
                    $carrierRateCount++;
                }
            }
        }
        $this->command->info('Created ' . $carrierRateCount . ' carrier rates');

        // Create customer rates (sell prices) with markup
        $customerRateCount = 0;
        foreach ($customers as $customer) {
            // Different markup based on customer
            $markup = 1.25 + (rand(0, 25) / 100); // 25-50% markup

            foreach ($createdPrefixes as $prefixCode => $prefix) {
                if (isset($carrierRatesData[$prefixCode])) {
                    $baseCost = array_sum($carrierRatesData[$prefixCode]) / 5; // Average cost
                    $price = round($baseCost * $markup, 4);

                    CustomerRate::firstOrCreate(
                        [
                            'customer_id' => $customer->id,
                            'destination_prefix_id' => $prefix->id,
                        ],
                        [
                            'price_per_minute' => $price,
                            'connection_fee' => 0,
                            'billing_increment' => 6,
                            'min_duration' => 0,
                            'effective_date' => now()->subDays(30)->toDateString(),
                            'active' => true,
                        ]
                    );
                    $customerRateCount++;
                }
            }
        }
        $this->command->info('Created ' . $customerRateCount . ' customer rates');
    }
}
