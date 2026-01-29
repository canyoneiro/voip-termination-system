<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrierIp extends Model
{
    protected $table = 'carrier_ips';

    protected $fillable = [
        'carrier_id',
        'ip_address',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public $timestamps = false;

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }
}
