<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReportExecution extends Model
{
    protected $fillable = [
        'uuid',
        'scheduled_report_id',
        'status',
        'trigger_type',
        'triggered_by',
        'report_date_from',
        'report_date_to',
        'file_path',
        'file_path_csv',
        'file_size',
        'records_count',
        'metadata',
        'error_message',
        'email_sent_count',
        'email_failed_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'records_count' => 'integer',
        'email_sent_count' => 'integer',
        'email_failed_count' => 'integer',
        'report_date_from' => 'date',
        'report_date_to' => 'date',
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

    public function scheduledReport(): BelongsTo
    {
        return $this->belongsTo(ScheduledReport::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(array $data = []): void
    {
        $this->update(array_merge([
            'status' => 'completed',
            'completed_at' => now(),
        ], $data));
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }
        return $this->completed_at->diffInSeconds($this->started_at);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '-';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
