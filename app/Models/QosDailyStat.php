<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QosDailyStat extends Model
{
    protected $fillable = [
        'date',
        'customer_id',
        'carrier_id',
        'total_calls',
        'measured_calls',
        'avg_mos',
        'min_mos',
        'max_mos',
        'avg_pdd',
        'min_pdd',
        'max_pdd',
        'avg_jitter',
        'avg_packet_loss',
        'excellent_count',
        'good_count',
        'fair_count',
        'poor_count',
        'bad_count',
    ];

    protected $casts = [
        'date' => 'date',
        'total_calls' => 'integer',
        'measured_calls' => 'integer',
        'avg_mos' => 'decimal:2',
        'min_mos' => 'decimal:2',
        'max_mos' => 'decimal:2',
        'avg_pdd' => 'integer',
        'min_pdd' => 'integer',
        'max_pdd' => 'integer',
        'avg_jitter' => 'decimal:2',
        'avg_packet_loss' => 'decimal:2',
        'excellent_count' => 'integer',
        'good_count' => 'integer',
        'fair_count' => 'integer',
        'poor_count' => 'integer',
        'bad_count' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function scopeForPeriod($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function getQualityDistributionAttribute(): array
    {
        $total = $this->measured_calls ?: 1;
        return [
            'excellent' => round($this->excellent_count / $total * 100, 1),
            'good' => round($this->good_count / $total * 100, 1),
            'fair' => round($this->fair_count / $total * 100, 1),
            'poor' => round($this->poor_count / $total * 100, 1),
            'bad' => round($this->bad_count / $total * 100, 1),
        ];
    }
}
