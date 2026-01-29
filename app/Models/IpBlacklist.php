<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpBlacklist extends Model
{
    protected $table = 'ip_blacklist';
    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'reason',
        'source',
        'attempts',
        'expires_at',
        'permanent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'permanent' => 'boolean',
        'attempts' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function isExpired(): bool
    {
        if ($this->permanent) return false;
        if (!$this->expires_at) return false;
        return $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('permanent', true)
              ->orWhere('expires_at', '>', now());
        });
    }
}
