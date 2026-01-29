<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $table = 'webhook_deliveries';
    public $timestamps = false;

    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_code',
        'response_body',
        'attempts',
        'success',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_code' => 'integer',
        'attempts' => 'integer',
        'success' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_id');
    }
}
