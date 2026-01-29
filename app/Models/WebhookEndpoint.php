<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WebhookEndpoint extends Model
{
    protected $table = 'webhook_endpoints';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'customer_id',
        'url',
        'secret',
        'events',
        'active',
        'last_triggered_at',
        'last_status_code',
        'failure_count',
    ];

    protected $casts = [
        'events' => 'array',
        'active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'last_status_code' => 'integer',
        'failure_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->secret)) {
                $model->secret = Str::random(32);
            }
            $model->created_at = now();
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'webhook_id');
    }

    public function subscribesTo(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }

    public function generateSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secret);
    }
}
