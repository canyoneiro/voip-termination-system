<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveCall extends Model
{
    protected $table = 'active_calls';
    public $timestamps = false;

    protected $fillable = [
        'call_id',
        'customer_id',
        'carrier_id',
        'caller',
        'callee',
        'source_ip',
        'start_time',
        'answered',
        'answer_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'answer_time' => 'datetime',
        'answered' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function getDurationAttribute(): int
    {
        $start = $this->answered ? $this->answer_time : $this->start_time;
        return now()->diffInSeconds($start);
    }

    public function getDurationFormattedAttribute(): string
    {
        $duration = $this->duration;
        $minutes = floor($duration / 60);
        $seconds = $duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
