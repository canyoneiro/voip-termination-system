<?php

namespace App\Jobs;

use App\Models\Alert;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Cdr;
use App\Models\SystemSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * CheckThresholdsJob - Verifica umbrales del sistema y genera alertas
 *
 * Este job se ejecuta cada minuto y verifica:
 *
 * 1. CANALES (channels_warning_pct):
 *    - Alerta cuando un customer usa >= X% de sus max_channels
 *    - Default: 80%
 *
 * 2. MINUTOS (minutes_warning_pct):
 *    - Alerta cuando un customer consume >= X% de sus minutos diarios/mensuales
 *    - Default: 80%
 *
 * 3. ASR GLOBAL (min_asr_global):
 *    - Alerta cuando el ASR de las últimas 4 horas cae por debajo del umbral
 *    - Default: 40%
 *
 * 4. OPTIONS TIMEOUT (options_timeout):
 *    - Alerta cuando un carrier no responde a OPTIONS en X segundos
 *    - Default: 90 segundos
 *    - Nota: El dispatcher de Kamailio ya maneja esto, pero generamos alerta adicional
 */
class CheckThresholdsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $this->checkChannelThresholds();
        $this->checkMinutesThresholds();
        $this->checkAsrThreshold();
        $this->checkOptionsTimeout();
    }

    /**
     * Verificar uso de canales por customer
     */
    protected function checkChannelThresholds(): void
    {
        $warningPct = (int) SystemSetting::getValue('alerts', 'channels_warning_pct', 80);

        if ($warningPct <= 0) {
            return; // Disabled
        }

        $customers = Customer::where('active', true)
            ->where('max_channels', '>', 0)
            ->withCount('activeCalls')
            ->get();

        foreach ($customers as $customer) {
            $usagePct = ($customer->active_calls_count / $customer->max_channels) * 100;

            if ($usagePct >= $warningPct) {
                $this->createAlertIfNotRecent(
                    type: 'channels_exceeded',
                    sourceType: 'customer',
                    sourceId: $customer->id,
                    sourceName: $customer->name,
                    severity: $usagePct >= 100 ? 'critical' : 'warning',
                    title: "Uso de canales al {$usagePct}%",
                    message: "El cliente {$customer->name} está usando {$customer->active_calls_count} de {$customer->max_channels} canales ({$usagePct}%).",
                    metadata: [
                        'current_channels' => $customer->active_calls_count,
                        'max_channels' => $customer->max_channels,
                        'usage_pct' => round($usagePct, 1),
                        'threshold_pct' => $warningPct,
                    ],
                    cooldownMinutes: 15
                );
            }
        }
    }

    /**
     * Verificar consumo de minutos por customer
     */
    protected function checkMinutesThresholds(): void
    {
        $warningPct = (int) SystemSetting::getValue('alerts', 'minutes_warning_pct', 80);

        if ($warningPct <= 0) {
            return; // Disabled
        }

        $customers = Customer::where('active', true)
            ->where(function ($q) {
                $q->where('max_daily_minutes', '>', 0)
                  ->orWhere('max_monthly_minutes', '>', 0);
            })
            ->get();

        foreach ($customers as $customer) {
            // Check daily minutes
            if ($customer->max_daily_minutes > 0) {
                $dailyPct = ($customer->used_daily_minutes / $customer->max_daily_minutes) * 100;

                if ($dailyPct >= $warningPct) {
                    $severity = $dailyPct >= 100 ? 'critical' : 'warning';
                    $type = $dailyPct >= 100 ? 'minutes_exhausted' : 'minutes_warning';

                    $this->createAlertIfNotRecent(
                        type: $type,
                        sourceType: 'customer',
                        sourceId: $customer->id,
                        sourceName: $customer->name,
                        severity: $severity,
                        title: "Minutos diarios al {$dailyPct}%",
                        message: "El cliente {$customer->name} ha consumido {$customer->used_daily_minutes} de {$customer->max_daily_minutes} minutos diarios ({$dailyPct}%).",
                        metadata: [
                            'period' => 'daily',
                            'used_minutes' => $customer->used_daily_minutes,
                            'max_minutes' => $customer->max_daily_minutes,
                            'usage_pct' => round($dailyPct, 1),
                            'threshold_pct' => $warningPct,
                        ],
                        cooldownMinutes: 60
                    );
                }
            }

            // Check monthly minutes
            if ($customer->max_monthly_minutes > 0) {
                $monthlyPct = ($customer->used_monthly_minutes / $customer->max_monthly_minutes) * 100;

                if ($monthlyPct >= $warningPct) {
                    $severity = $monthlyPct >= 100 ? 'critical' : 'warning';
                    $type = $monthlyPct >= 100 ? 'minutes_exhausted' : 'minutes_warning';

                    $this->createAlertIfNotRecent(
                        type: $type,
                        sourceType: 'customer',
                        sourceId: $customer->id,
                        sourceName: $customer->name,
                        severity: $severity,
                        title: "Minutos mensuales al {$monthlyPct}%",
                        message: "El cliente {$customer->name} ha consumido {$customer->used_monthly_minutes} de {$customer->max_monthly_minutes} minutos mensuales ({$monthlyPct}%).",
                        metadata: [
                            'period' => 'monthly',
                            'used_minutes' => $customer->used_monthly_minutes,
                            'max_minutes' => $customer->max_monthly_minutes,
                            'usage_pct' => round($monthlyPct, 1),
                            'threshold_pct' => $warningPct,
                        ],
                        cooldownMinutes: 240 // 4 hours for monthly alerts
                    );
                }
            }
        }
    }

    /**
     * Verificar ASR global del sistema
     */
    protected function checkAsrThreshold(): void
    {
        $minAsr = (int) SystemSetting::getValue('alerts', 'min_asr_global', 40);

        if ($minAsr <= 0) {
            return; // Disabled
        }

        // Calculate ASR for last 4 hours
        $stats = Cdr::where('start_time', '>=', now()->subHours(4))
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered')
            ->first();

        if ($stats->total < 10) {
            return; // Not enough calls to calculate meaningful ASR
        }

        $asr = ($stats->answered / $stats->total) * 100;

        if ($asr < $minAsr) {
            $this->createAlertIfNotRecent(
                type: 'high_failure_rate',
                sourceType: 'system',
                sourceId: null,
                sourceName: 'Sistema Global',
                severity: $asr < ($minAsr / 2) ? 'critical' : 'warning',
                title: "ASR global bajo: {$asr}%",
                message: "El ASR global de las últimas 4 horas es {$asr}%, por debajo del umbral de {$minAsr}%. Total llamadas: {$stats->total}, contestadas: {$stats->answered}.",
                metadata: [
                    'asr' => round($asr, 2),
                    'min_asr' => $minAsr,
                    'total_calls' => $stats->total,
                    'answered_calls' => $stats->answered,
                    'period_hours' => 4,
                ],
                cooldownMinutes: 30
            );
        }
    }

    /**
     * Verificar timeout de OPTIONS en carriers
     */
    protected function checkOptionsTimeout(): void
    {
        $timeoutSeconds = (int) SystemSetting::getValue('alerts', 'options_timeout', 90);

        if ($timeoutSeconds <= 0) {
            return; // Disabled
        }

        $cutoffTime = now()->subSeconds($timeoutSeconds);

        // Find carriers that haven't responded to OPTIONS recently
        $carriers = Carrier::where('state', '!=', 'disabled')
            ->where('probing_enabled', true)
            ->where(function ($q) use ($cutoffTime) {
                $q->whereNull('last_options_time')
                  ->orWhere('last_options_time', '<', $cutoffTime);
            })
            ->get();

        foreach ($carriers as $carrier) {
            $lastReply = $carrier->last_options_time
                ? $carrier->last_options_time->diffInSeconds(now())
                : 'nunca';

            $this->createAlertIfNotRecent(
                type: 'carrier_down',
                sourceType: 'carrier',
                sourceId: $carrier->id,
                sourceName: $carrier->name,
                severity: 'critical',
                title: "Carrier sin respuesta a OPTIONS",
                message: "El carrier {$carrier->name} ({$carrier->host}:{$carrier->port}) no ha respondido a OPTIONS en los últimos {$timeoutSeconds} segundos. Última respuesta: {$lastReply}s.",
                metadata: [
                    'carrier_host' => $carrier->host,
                    'carrier_port' => $carrier->port,
                    'timeout_seconds' => $timeoutSeconds,
                    'last_options_time' => $carrier->last_options_time?->toIso8601String(),
                    'carrier_state' => $carrier->state,
                ],
                cooldownMinutes: 5
            );
        }
    }

    /**
     * Crear alerta solo si no existe una reciente del mismo tipo/origen
     */
    protected function createAlertIfNotRecent(
        string $type,
        string $sourceType,
        ?int $sourceId,
        ?string $sourceName,
        string $severity,
        string $title,
        string $message,
        array $metadata,
        int $cooldownMinutes
    ): void {
        // Check for recent similar alert (cooldown period)
        $cacheKey = "alert_cooldown:{$type}:{$sourceType}:{$sourceId}";

        if (Cache::has($cacheKey)) {
            return; // Still in cooldown
        }

        // Check if there's an unacknowledged alert of the same type
        $existingAlert = Alert::where('type', $type)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('acknowledged', false)
            ->where('created_at', '>=', now()->subMinutes($cooldownMinutes))
            ->exists();

        if ($existingAlert) {
            return;
        }

        // Create new alert
        Alert::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'type' => $type,
            'severity' => $severity,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'source_name' => $sourceName,
            'title' => $title,
            'message' => $message,
            'metadata' => $metadata,
        ]);

        // Set cooldown
        Cache::put($cacheKey, true, now()->addMinutes($cooldownMinutes));

        Log::info("Threshold alert created", [
            'type' => $type,
            'source' => "{$sourceType}:{$sourceId}",
            'title' => $title,
        ]);
    }
}
