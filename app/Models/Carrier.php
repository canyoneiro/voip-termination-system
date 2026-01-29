<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrier extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'name',
        'host',
        'port',
        'transport',
        'codecs',
        'priority',
        'weight',
        'tech_prefix',
        'strip_digits',
        'prefix_filter',
        'prefix_deny',
        'max_cps',
        'max_channels',
        'state',
        'probing_enabled',
        'last_options_reply',
        'last_options_time',
        'failover_count',
        'daily_calls',
        'daily_minutes',
        'daily_failed',
        'notes',
    ];

    protected $casts = [
        'port' => 'integer',
        'priority' => 'integer',
        'weight' => 'integer',
        'strip_digits' => 'integer',
        'max_cps' => 'integer',
        'max_channels' => 'integer',
        'probing_enabled' => 'boolean',
        'last_options_reply' => 'integer',
        'last_options_time' => 'datetime',
        'failover_count' => 'integer',
        'daily_calls' => 'integer',
        'daily_minutes' => 'integer',
        'daily_failed' => 'integer',
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
        return $this->hasMany(CarrierIp::class);
    }

    public function cdrs(): HasMany
    {
        return $this->hasMany(Cdr::class);
    }

    public function activeCalls(): HasMany
    {
        return $this->hasMany(ActiveCall::class);
    }

    public function getActiveCallsCountAttribute(): int
    {
        return $this->activeCalls()->count();
    }

    public function getAsrTodayAttribute(): ?float
    {
        if ($this->daily_calls == 0) return null;
        $answered = $this->daily_calls - $this->daily_failed;
        return round(($answered / $this->daily_calls) * 100, 2);
    }

    public function getCodecsArrayAttribute(): array
    {
        return array_map('trim', explode(',', $this->codecs ?? ''));
    }

    public function getPrefixFilterArrayAttribute(): array
    {
        if (empty($this->prefix_filter)) return [];
        return array_filter(array_map('trim', explode("\n", $this->prefix_filter)));
    }

    public function getPrefixDenyArrayAttribute(): array
    {
        if (empty($this->prefix_deny)) return [];
        return array_filter(array_map('trim', explode("\n", $this->prefix_deny)));
    }

    public function isUp(): bool
    {
        return $this->state === 'active';
    }

    public function isDown(): bool
    {
        return in_array($this->state, ['inactive', 'probing']);
    }
}
