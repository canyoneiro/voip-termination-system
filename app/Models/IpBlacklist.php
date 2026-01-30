<?php

namespace App\Models;

use App\Services\KamailioService;
use App\Traits\ReloadsKamailio;
use Illuminate\Database\Eloquent\Model;

class IpBlacklist extends Model
{
    use ReloadsKamailio;

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

    /**
     * Specify Kamailio reload type for this model
     */
    protected function getKamailioReloadType(): string
    {
        return 'htable';
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
              ->orWhereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}
