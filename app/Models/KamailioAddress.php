<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la vista kamailio_address
 * Esta vista lee directamente de customer_ips + customers
 * Es usada por el módulo permissions de Kamailio
 */
class KamailioAddress extends Model
{
    protected $table = 'kamailio_address';

    public $timestamps = false;

    protected $casts = [
        'grp' => 'integer',
        'mask' => 'integer',
        'port' => 'integer',
    ];

    /**
     * Obtiene el conteo actual de IPs autorizadas
     */
    public static function getCount(): int
    {
        return self::count();
    }

    /**
     * Recarga el módulo de permisos en Kamailio
     * La vista se actualiza automáticamente desde customer_ips
     */
    public static function reloadKamailio(): bool
    {
        exec('kamcmd permissions.addressReload 2>&1', $output, $code);
        return $code === 0;
    }

    /**
     * Obtiene conteo y recarga
     */
    public static function syncAndReload(): array
    {
        $count = self::getCount();
        $reloaded = self::reloadKamailio();

        return [
            'synced' => $count,
            'reloaded' => $reloaded,
        ];
    }
}
