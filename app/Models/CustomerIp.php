<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerIp extends Model
{
    protected $table = 'customer_ips';

    protected $fillable = [
        'customer_id',
        'ip_address',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public $timestamps = false;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
