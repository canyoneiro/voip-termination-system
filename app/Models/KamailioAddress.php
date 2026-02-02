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
     * Includes retry logic for "Ongoing reload" errors
     */
    public static function reloadKamailio(): bool
    {
        $maxRetries = 3;
        $retryDelay = 500000; // 500ms

        for ($i = 0; $i < $maxRetries; $i++) {
            exec('kamcmd permissions.addressReload 2>&1', $output, $code);
            $outputStr = implode(' ', $output);

            // Check if reload is ongoing
            if (stripos($outputStr, 'ongoing') !== false) {
                usleep($retryDelay);
                $output = [];
                continue;
            }

            // Check for success
            $success = $code === 0 ||
                       stripos($outputStr, 'ok') !== false ||
                       stripos($outputStr, 'reload') !== false ||
                       empty(trim($outputStr));

            if ($success) {
                // Wait for Kamailio to complete the reload
                usleep(200000); // 200ms
                return true;
            }

            // Unknown error, retry
            usleep($retryDelay);
            $output = [];
        }

        return false;
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
