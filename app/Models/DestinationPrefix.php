<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DestinationPrefix extends Model
{
    protected $fillable = [
        'prefix',
        'country_code',
        'country_name',
        'region',
        'description',
        'is_mobile',
        'is_premium',
        'active',
    ];

    protected $casts = [
        'is_mobile' => 'boolean',
        'is_premium' => 'boolean',
        'active' => 'boolean',
    ];

    public function carrierRates(): HasMany
    {
        return $this->hasMany(CarrierRate::class);
    }

    public function customerRates(): HasMany
    {
        return $this->hasMany(CustomerRate::class);
    }

    public function ratePlanRates(): HasMany
    {
        return $this->hasMany(RatePlanRate::class);
    }

    public function cdrs(): HasMany
    {
        return $this->hasMany(Cdr::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function getFullDescriptionAttribute(): string
    {
        $parts = [];
        if ($this->country_name) {
            $parts[] = $this->country_name;
        }
        if ($this->region) {
            $parts[] = $this->region;
        }
        if ($this->is_mobile) {
            $parts[] = 'Mobile';
        }
        return implode(' - ', $parts) ?: $this->prefix;
    }
}
