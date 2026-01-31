<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ScheduledReport extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'type',
        'frequency',
        'cron_expression',
        'send_time',
        'day_of_week',
        'day_of_month',
        'recipients',
        'formats',
        'customer_id',
        'carrier_id',
        'filters',
        'include_details',
        'include_charts',
        'active',
        'created_by',
        'last_sent_at',
        'next_run_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'formats' => 'array',
        'filters' => 'array',
        'include_details' => 'boolean',
        'include_charts' => 'boolean',
        'active' => 'boolean',
        'day_of_week' => 'integer',
        'day_of_month' => 'integer',
        'last_sent_at' => 'datetime',
        'next_run_at' => 'datetime',
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

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(ReportExecution::class);
    }

    public function lastExecution(): BelongsTo
    {
        return $this->belongsTo(ReportExecution::class, 'id', 'scheduled_report_id')
            ->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('next_run_at')
                    ->orWhere('next_run_at', '<=', now());
            });
    }

    public function calculateNextRun(): ?\DateTime
    {
        $now = now();

        // Parse send_time (format "HH:MM" or "HH:MM:SS")
        $timeParts = explode(':', $this->send_time ?? '00:00');
        $hour = (int) ($timeParts[0] ?? 0);
        $minute = (int) ($timeParts[1] ?? 0);
        $second = (int) ($timeParts[2] ?? 0);

        switch ($this->frequency) {
            case 'daily':
                $next = $now->copy()->setTime($hour, $minute, $second);
                if ($next <= $now) {
                    $next->addDay();
                }
                return $next;

            case 'weekly':
                // day_of_week: 0=Sunday, 1=Monday, etc.
                $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $dayName = $dayNames[$this->day_of_week] ?? 'Monday';
                $next = $now->copy()
                    ->next($dayName)
                    ->setTime($hour, $minute, $second);
                return $next;

            case 'monthly':
                $next = $now->copy()
                    ->setTime($hour, $minute, $second)
                    ->day(min($this->day_of_month, $now->daysInMonth));
                if ($next <= $now) {
                    $next->addMonth();
                    $next->day(min($this->day_of_month, $next->daysInMonth));
                }
                return $next;

            case 'custom':
                // Parse cron expression - simplified
                return null;

            default:
                return null;
        }
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'cdr_summary' => 'CDR Summary',
            'customer_usage' => 'Customer Usage',
            'carrier_performance' => 'Carrier Performance',
            'billing' => 'Billing Report',
            'qos_report' => 'Quality of Service',
            'profit_loss' => 'Profit/Loss Analysis',
            'traffic_analysis' => 'Traffic Analysis',
            default => $this->type,
        };
    }

    public function getFrequencyNameAttribute(): string
    {
        return match($this->frequency) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'custom' => 'Custom',
            default => $this->frequency,
        };
    }
}
