<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyStat extends Model
{
    protected $table = 'daily_stats';
    public $timestamps = false;

    protected $fillable = [
        'date',
        'customer_id',
        'carrier_id',
        'total_calls',
        'answered_calls',
        'failed_calls',
        'total_duration',
        'billable_duration',
        'asr',
        'acd',
        'avg_pdd',
        'max_concurrent',
    ];

    protected $casts = [
        'date' => 'date',
        'total_calls' => 'integer',
        'answered_calls' => 'integer',
        'failed_calls' => 'integer',
        'total_duration' => 'integer',
        'billable_duration' => 'integer',
        'asr' => 'decimal:2',
        'acd' => 'decimal:2',
        'avg_pdd' => 'integer',
        'max_concurrent' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }
}
