<?php

namespace App\Models;

use App\Services\KamailioService;
use App\Traits\ReloadsKamailio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerIp extends Model
{
    use ReloadsKamailio;

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

    /**
     * Specify Kamailio reload type for this model
     */
    protected function getKamailioReloadType(): string
    {
        return 'permissions';
    }

    /**
     * Reload Kamailio permissions (static helper)
     */
    public static function reloadPermissions(): bool
    {
        return KamailioService::reloadPermissions();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
