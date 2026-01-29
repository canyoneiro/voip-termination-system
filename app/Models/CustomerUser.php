<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class CustomerUser extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'uuid',
        'customer_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'active',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'login_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'login_count' => 'integer',
    ];

    public const ROLES = [
        'owner' => 'Owner',
        'manager' => 'Manager',
        'viewer' => 'Viewer',
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

    public function ipRequests(): HasMany
    {
        return $this->hasMany(CustomerIpRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['owner', 'manager']);
    }

    public function canManageUsers(): bool
    {
        return $this->isManager();
    }

    public function canCreateApiTokens(): bool
    {
        return $this->isManager() && $this->customer->portalSettings?->allow_api_tokens;
    }

    public function canRequestIps(): bool
    {
        return $this->customer->portalSettings?->allow_ip_requests;
    }

    public function canManageWebhooks(): bool
    {
        return $this->isManager() && $this->customer->portalSettings?->allow_webhook_management;
    }

    public function recordLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
            'login_count' => $this->login_count + 1,
        ]);
    }

    public function getRoleNameAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
}
