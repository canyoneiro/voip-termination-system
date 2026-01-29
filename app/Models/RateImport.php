<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RateImport extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'carrier_id',
        'customer_id',
        'rate_plan_id',
        'filename',
        'total_rows',
        'imported_rows',
        'failed_rows',
        'skipped_rows',
        'errors',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'imported_rows' => 'integer',
        'failed_rows' => 'integer',
        'skipped_rows' => 'integer',
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error = null): void
    {
        $errors = $this->errors ?? [];
        if ($error) {
            $errors[] = $error;
        }
        $this->update([
            'status' => 'failed',
            'errors' => $errors,
            'completed_at' => now(),
        ]);
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return round(($this->imported_rows + $this->failed_rows + $this->skipped_rows) / $this->total_rows * 100, 2);
    }

    public function getSuccessRateAttribute(): ?float
    {
        $processed = $this->imported_rows + $this->failed_rows;
        if ($processed === 0) {
            return null;
        }
        return round($this->imported_rows / $processed * 100, 2);
    }
}
