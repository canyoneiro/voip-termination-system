<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FraudRule extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'type',
        'parameters',
        'threshold',
        'action',
        'severity',
        'customer_id',
        'active',
        'cooldown_minutes',
        'last_triggered_at',
        'trigger_count',
    ];

    protected $casts = [
        'parameters' => 'array',
        'threshold' => 'decimal:2',
        'active' => 'boolean',
        'cooldown_minutes' => 'integer',
        'trigger_count' => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    public const TYPES = [
        'high_cost_destination' => 'High Cost Destination',
        'traffic_spike' => 'Traffic Spike',
        'wangiri' => 'Wangiri (Short Calls)',
        'unusual_destination' => 'Unusual Destination',
        'high_failure_rate' => 'High Failure Rate',
        'off_hours_traffic' => 'Off-Hours Traffic',
        'caller_id_manipulation' => 'Caller ID Manipulation',
        'accelerated_consumption' => 'Accelerated Consumption',
        'simultaneous_calls' => 'Simultaneous Calls',
        'short_calls_burst' => 'Short Calls Burst',
    ];

    public const ACTIONS = [
        'log' => 'Log Only',
        'alert' => 'Send Alert',
        'throttle' => 'Throttle Traffic',
        'block' => 'Block Calls',
    ];

    public const SEVERITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(FraudIncident::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('customer_id');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where(function ($q) use ($customerId) {
            $q->whereNull('customer_id')
                ->orWhere('customer_id', $customerId);
        });
    }

    public function isInCooldown(): bool
    {
        if (!$this->last_triggered_at) {
            return false;
        }

        return $this->last_triggered_at->addMinutes($this->cooldown_minutes) > now();
    }

    public function markTriggered(): void
    {
        $this->update([
            'last_triggered_at' => now(),
            'trigger_count' => $this->trigger_count + 1,
        ]);
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getActionNameAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'low' => 'info',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
            default => 'secondary',
        };
    }
}
