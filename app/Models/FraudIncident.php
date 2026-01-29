<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FraudIncident extends Model
{
    protected $fillable = [
        'uuid',
        'fraud_rule_id',
        'customer_id',
        'cdr_id',
        'type',
        'severity',
        'title',
        'description',
        'metadata',
        'estimated_cost',
        'affected_calls',
        'status',
        'action_taken',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'notified_admin',
        'notified_customer',
    ];

    protected $casts = [
        'metadata' => 'array',
        'estimated_cost' => 'decimal:6',
        'affected_calls' => 'integer',
        'notified_admin' => 'boolean',
        'notified_customer' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => 'Pending Review',
        'investigating' => 'Investigating',
        'false_positive' => 'False Positive',
        'confirmed' => 'Confirmed',
        'resolved' => 'Resolved',
    ];

    public const ACTIONS_TAKEN = [
        'none' => 'No Action',
        'notified' => 'Notified',
        'throttled' => 'Traffic Throttled',
        'blocked' => 'Blocked',
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

    public function fraudRule(): BelongsTo
    {
        return $this->belongsTo(FraudRule::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cdr(): BelongsTo
    {
        return $this->belongsTo(Cdr::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNotIn('status', ['resolved', 'false_positive']);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function resolve(int $userId, string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function markAsFalsePositive(int $userId, string $notes = null): void
    {
        $this->update([
            'status' => 'false_positive',
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getActionTakenNameAttribute(): string
    {
        return self::ACTIONS_TAKEN[$this->action_taken] ?? $this->action_taken;
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

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'investigating' => 'info',
            'false_positive' => 'secondary',
            'confirmed' => 'danger',
            'resolved' => 'success',
            default => 'secondary',
        };
    }
}
