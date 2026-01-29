<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RatePlan extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'default_markup_percent',
        'default_markup_fixed',
        'billing_increment',
        'min_duration',
        'active',
    ];

    protected $casts = [
        'default_markup_percent' => 'decimal:2',
        'default_markup_fixed' => 'decimal:6',
        'billing_increment' => 'integer',
        'min_duration' => 'integer',
        'active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function rates(): HasMany
    {
        return $this->hasMany(RatePlanRate::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function calculatePriceFromCost(float $cost): float
    {
        $markupAmount = ($cost * $this->default_markup_percent / 100) + $this->default_markup_fixed;
        return round($cost + $markupAmount, 6);
    }

    public function getRateForDestination(int $destinationPrefixId, $date = null): ?RatePlanRate
    {
        $date = $date ?? now()->toDateString();

        return $this->rates()
            ->where('destination_prefix_id', $destinationPrefixId)
            ->where('active', true)
            ->where('effective_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->orderBy('effective_date', 'desc')
            ->first();
    }
}
