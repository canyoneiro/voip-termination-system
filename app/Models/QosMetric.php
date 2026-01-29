<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QosMetric extends Model
{
    protected $fillable = [
        'cdr_id',
        'customer_id',
        'carrier_id',
        'mos_score',
        'pdd',
        'jitter',
        'packet_loss',
        'rtt',
        'codec_used',
        'quality_rating',
        'call_time',
    ];

    protected $casts = [
        'mos_score' => 'decimal:2',
        'pdd' => 'integer',
        'jitter' => 'integer',
        'packet_loss' => 'decimal:2',
        'rtt' => 'integer',
        'call_time' => 'datetime',
    ];

    public function cdr(): BelongsTo
    {
        return $this->belongsTo(Cdr::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function scopeExcellent($query)
    {
        return $query->where('quality_rating', 'excellent');
    }

    public function scopeGood($query)
    {
        return $query->where('quality_rating', 'good');
    }

    public function scopePoorOrWorse($query)
    {
        return $query->whereIn('quality_rating', ['poor', 'bad']);
    }

    public function scopeForPeriod($query, $from, $to)
    {
        return $query->whereBetween('call_time', [$from, $to]);
    }

    public function getMosColorAttribute(): string
    {
        if ($this->mos_score >= 4.0) return 'success';
        if ($this->mos_score >= 3.5) return 'info';
        if ($this->mos_score >= 3.0) return 'warning';
        return 'danger';
    }

    public function getPddFormattedAttribute(): string
    {
        if ($this->pdd === null) return '-';
        return number_format($this->pdd) . ' ms';
    }
}
