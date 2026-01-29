<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'uuid',
        'type',
        'severity',
        'source_type',
        'source_id',
        'source_name',
        'title',
        'message',
        'metadata',
        'notified_email',
        'notified_telegram',
        'acknowledged',
        'acknowledged_by',
        'acknowledged_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'notified_email' => 'boolean',
        'notified_telegram' => 'boolean',
        'acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            $model->created_at = now();
        });
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function scopeUnacknowledged($query)
    {
        return $query->where('acknowledged', false);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeWarning($query)
    {
        return $query->where('severity', 'warning');
    }

    public function acknowledge(User $user): void
    {
        $this->update([
            'acknowledged' => true,
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
        ]);
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'red',
            'warning' => 'yellow',
            'info' => 'blue',
            default => 'gray',
        };
    }
}
