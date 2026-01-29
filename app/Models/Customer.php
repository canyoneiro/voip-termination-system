<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'company',
        'email',
        'phone',
        'max_channels',
        'max_cps',
        'max_daily_minutes',
        'max_monthly_minutes',
        'used_daily_minutes',
        'used_monthly_minutes',
        'active',
        'notes',
        'alert_email',
        'alert_telegram_chat_id',
        'notify_low_balance',
        'notify_channels_warning',
        'traces_enabled',
        'traces_until',
    ];

    protected $casts = [
        'active' => 'boolean',
        'notify_low_balance' => 'boolean',
        'notify_channels_warning' => 'boolean',
        'traces_enabled' => 'boolean',
        'traces_until' => 'datetime',
        'max_channels' => 'integer',
        'max_cps' => 'integer',
        'max_daily_minutes' => 'integer',
        'max_monthly_minutes' => 'integer',
        'used_daily_minutes' => 'integer',
        'used_monthly_minutes' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function ips(): HasMany
    {
        return $this->hasMany(CustomerIp::class);
    }

    public function cdrs(): HasMany
    {
        return $this->hasMany(Cdr::class);
    }

    public function activeCalls(): HasMany
    {
        return $this->hasMany(ActiveCall::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'source_id')->where('source_type', 'customer');
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(WebhookEndpoint::class);
    }

    public function getActiveCallsCountAttribute(): int
    {
        return $this->activeCalls()->count();
    }

    public function getDailyMinutesPercentageAttribute(): ?float
    {
        if (!$this->max_daily_minutes) return null;
        return round(($this->used_daily_minutes / $this->max_daily_minutes) * 100, 2);
    }

    public function getMonthlyMinutesPercentageAttribute(): ?float
    {
        if (!$this->max_monthly_minutes) return null;
        return round(($this->used_monthly_minutes / $this->max_monthly_minutes) * 100, 2);
    }
}
