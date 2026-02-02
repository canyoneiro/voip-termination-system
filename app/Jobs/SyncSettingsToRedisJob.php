<?php

namespace App\Jobs;

use App\Models\SystemSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * SyncSettingsToRedisJob - Sincroniza SystemSettings a Redis para Kamailio
 *
 * Kamailio no puede leer de MySQL dinámicamente en cada request de forma
 * eficiente. Este job sincroniza los settings críticos a Redis para que
 * Kamailio los pueda leer en tiempo real.
 *
 * Settings sincronizados:
 *
 * 1. LÍMITES GLOBALES (limits/*):
 *    - global_max_channels: Máximo de canales simultáneos globales
 *    - global_max_cps: Máximo de llamadas por segundo globales
 *
 * 2. SEGURIDAD (security/*):
 *    - flood_threshold: CPS por IP para detectar flood (default: 50)
 *    - blacklist_duration: Segundos de bloqueo automático (default: 3600)
 *    - whitelist_ips: IPs que nunca deben ser bloqueadas (comma-separated)
 *
 * Formato en Redis:
 *    voip:settings:<category>:<name> = <value>
 *
 * Ejemplo:
 *    voip:settings:limits:global_max_channels = 100
 *    voip:settings:security:flood_threshold = 50
 */
class SyncSettingsToRedisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Settings que deben sincronizarse a Redis
     * Formato: ['category' => ['name' => 'default_value', ...], ...]
     */
    protected array $settingsToSync = [
        'limits' => [
            'global_max_channels' => 0,    // 0 = sin límite
            'global_max_cps' => 0,         // 0 = sin límite
        ],
        'security' => [
            'flood_threshold' => 50,       // CPS por IP para detectar flood
            'blacklist_duration' => 3600,  // 1 hora por defecto
            'whitelist_ips' => '',         // IPs que nunca se bloquean
        ],
    ];

    public function handle(): void
    {
        // Use 'kamailio' connection - no prefix, so Kamailio can read directly
        $redis = Redis::connection('kamailio');
        $syncedCount = 0;

        foreach ($this->settingsToSync as $category => $settings) {
            foreach ($settings as $name => $default) {
                $value = SystemSetting::getValue($category, $name, $default);
                $redisKey = "voip:settings:{$category}:{$name}";

                // Almacenar en Redis con TTL de 5 minutos
                // Si el job falla, los valores caducan y Kamailio usa defaults
                $redis->setex($redisKey, 300, $value);

                $syncedCount++;
            }
        }

        // Sync también la lista de whitelist como un SET para búsquedas rápidas
        $whitelistIps = SystemSetting::getValue('security', 'whitelist_ips', '');

        // Eliminar el set anterior
        $redis->del('voip:whitelist');

        if (!empty($whitelistIps) && $whitelistIps !== '[]') {
            $ips = array_filter(array_map('trim', explode(',', str_replace(['[', ']', '"'], '', $whitelistIps))));

            if (!empty($ips)) {
                foreach ($ips as $ip) {
                    $redis->sadd('voip:whitelist', $ip);
                }
                $redis->expire('voip:whitelist', 300);
            }
        }

        Log::debug("SyncSettingsToRedisJob: Synced {$syncedCount} settings to Redis");
    }
}
