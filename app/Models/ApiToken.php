<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $table = 'api_tokens';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'name',
        'token_hash',
        'customer_id',
        'type',
        'permissions',
        'rate_limit',
        'rate_limit_window',
        'last_used_at',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'active' => 'boolean',
        'rate_limit' => 'integer',
        'rate_limit_window' => 'integer',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            $model->created_at = now();
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function generateToken(): string
    {
        return 'voip_' . Str::random(40);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->type === 'admin') return true;
        return in_array($permission, $this->permissions ?? []);
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) return false;
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->active && !$this->isExpired();
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
