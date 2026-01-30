<?php

namespace App\Models;

use App\Traits\ReloadsKamailio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrierIp extends Model
{
    use ReloadsKamailio;

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

    /**
     * Specify Kamailio reload type for this model
     * Carrier IPs affect dispatcher or permissions depending on use
     */
    protected function getKamailioReloadType(): string
    {
        return 'dispatcher';
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }
}
