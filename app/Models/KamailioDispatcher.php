<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la vista kamailio_dispatcher
 * Esta vista lee directamente de la tabla carriers
 * Es usada por el módulo dispatcher de Kamailio
 */
class KamailioDispatcher extends Model
{
    protected $table = 'kamailio_dispatcher';

    public $timestamps = false;

    protected $casts = [
        'setid' => 'integer',
        'flags' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * Obtiene el conteo actual de carriers activos
     */
    public static function getCount(): int
    {
        return self::count();
    }

    /**
     * Recarga el módulo dispatcher en Kamailio
     * La vista se actualiza automáticamente desde carriers
     * Includes retry logic for "Ongoing reload" errors
     */
    public static function reloadKamailio(): bool
    {
        $maxRetries = 3;
        $retryDelay = 500000; // 500ms

        for ($i = 0; $i < $maxRetries; $i++) {
            exec('kamcmd dispatcher.reload 2>&1', $output, $code);
            $outputStr = implode(' ', $output);

            // Check if reload is ongoing (another reload in progress)
            if (stripos($outputStr, 'ongoing') !== false) {
                usleep($retryDelay);
                $output = [];
                continue;
            }

            // Check for success
            $success = $code === 0 ||
                       stripos($outputStr, 'ok') !== false ||
                       stripos($outputStr, 'success') !== false ||
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
