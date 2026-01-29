<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cdr extends Model
{
    protected $table = 'cdrs';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'call_id',
        'customer_id',
        'carrier_id',
        'source_ip',
        'caller',
        'caller_original',
        'callee',
        'callee_original',
        'destination_ip',
        'start_time',
        'progress_time',
        'answer_time',
        'end_time',
        'duration',
        'billable_duration',
        'pdd',
        'sip_code',
        'sip_reason',
        'hangup_cause',
        'hangup_sip_code',
        'codecs_offered',
        'codec_used',
        'user_agent',
        'destination_prefix_id',
        'cost',
        'price',
        'profit',
        'margin_percent',
    ];

    protected $casts = [
        'start_time' => 'datetime:Y-m-d H:i:s.v',
        'progress_time' => 'datetime:Y-m-d H:i:s.v',
        'answer_time' => 'datetime:Y-m-d H:i:s.v',
        'end_time' => 'datetime:Y-m-d H:i:s.v',
        'duration' => 'integer',
        'billable_duration' => 'integer',
        'pdd' => 'integer',
        'sip_code' => 'integer',
        'hangup_sip_code' => 'integer',
        'cost' => 'decimal:6',
        'price' => 'decimal:6',
        'profit' => 'decimal:6',
        'margin_percent' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
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

    public function sipTraces(): HasMany
    {
        return $this->hasMany(SipTrace::class, 'call_id', 'call_id');
    }

    public function destinationPrefix(): BelongsTo
    {
        return $this->belongsTo(DestinationPrefix::class);
    }

    public function qosMetric(): HasOne
    {
        return $this->hasOne(QosMetric::class);
    }

    public function isAnswered(): bool
    {
        return $this->sip_code === 200 && $this->duration > 0;
    }

    public function isFailed(): bool
    {
        return $this->sip_code >= 400;
    }

    public function getDurationFormattedAttribute(): string
    {
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
