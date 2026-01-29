<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatePlanRate extends Model
{
    protected $fillable = [
        'rate_plan_id',
        'destination_prefix_id',
        'price_per_minute',
        'connection_fee',
        'billing_increment',
        'min_duration',
        'effective_date',
        'end_date',
        'active',
    ];

    protected $casts = [
        'price_per_minute' => 'decimal:6',
        'connection_fee' => 'decimal:6',
        'billing_increment' => 'integer',
        'min_duration' => 'integer',
        'effective_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
    ];

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function destinationPrefix(): BelongsTo
    {
        return $this->belongsTo(DestinationPrefix::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        return $query->where('effective_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });
    }

    public function getBillingIncrementAttribute($value): int
    {
        return $value ?? $this->ratePlan->billing_increment ?? 1;
    }

    public function getMinDurationAttribute($value): int
    {
        return $value ?? $this->ratePlan->min_duration ?? 0;
    }

    public function calculatePrice(int $durationSeconds): float
    {
        $billableSeconds = $this->calculateBillableSeconds($durationSeconds);
        $minutesForBilling = $billableSeconds / 60;

        return round(($minutesForBilling * $this->price_per_minute) + $this->connection_fee, 6);
    }

    protected function calculateBillableSeconds(int $durationSeconds): int
    {
        if ($durationSeconds <= 0) {
            return 0;
        }

        $increment = $this->billing_increment;
        $minDuration = $this->min_duration;

        $billable = max($durationSeconds, $minDuration);

        if ($increment > 1) {
            $billable = ceil($billable / $increment) * $increment;
        }

        return (int) $billable;
    }
}
