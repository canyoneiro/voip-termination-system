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
     */
    public static function reloadKamailio(): bool
    {
        exec('kamcmd dispatcher.reload 2>&1', $output, $code);
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
