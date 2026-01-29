<?php

namespace App\Http\Controllers\Api;

use App\Models\Carrier;
use App\Models\ActiveCall;
use App\Models\IpBlacklist;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Process;
use OpenApi\Annotations as OA;

class SystemController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/health",
     *     summary="Estado del sistema",
     *     description="Verifica el estado de todos los servicios",
     *     tags={"Health"},
     *     @OA\Response(response=200, description="Sistema saludable", @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="healthy"),
     *         @OA\Property(property="checks", type="object"),
     *         @OA\Property(property="timestamp", type="string", format="date-time")
     *     )),
     *     @OA\Response(response=503, description="Sistema con problemas")
     * )
     */
    public function health(): JsonResponse
    {
        $status = [
            'kamailio' => $this->checkKamailio(),
            'mysql' => $this->checkMysql(),
            'redis' => $this->checkRedis(),
            'disk' => $this->checkDisk(),
            'load' => $this->checkLoad(),
        ];

        $overall = !in_array('critical', array_column($status, 'status'));

        return response()->json([
            'status' => $overall ? 'healthy' : 'unhealthy',
            'checks' => $status,
            'timestamp' => now()->toIso8601String(),
        ], $overall ? 200 : 503);
    }

    /**
     * @OA\Get(
     *     path="/system/status",
     *     summary="Estado completo del sistema",
     *     tags={"System"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Estado del sistema")
     * )
     */
    public function status(): JsonResponse
    {
        return $this->success([
            'uptime' => $this->getUptime(),
            'active_calls' => ActiveCall::count(),
            'carriers_up' => Carrier::where('state', 'active')->count(),
            'carriers_down' => Carrier::whereIn('state', ['inactive', 'probing'])->count(),
            'blacklisted_ips' => IpBlacklist::active()->count(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ]);
    }

    public function config(): JsonResponse
    {
        // Return non-sensitive settings
        $settings = SystemSetting::whereNotIn('name', [
            'smtp_pass', 'telegram_bot_token'
        ])->get()->groupBy('category');

        return $this->success($settings);
    }

    /**
     * @OA\Get(
     *     path="/blacklist",
     *     summary="Listar IPs bloqueadas",
     *     tags={"System"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de IPs")
     * )
     */
    public function blacklist(): JsonResponse
    {
        $ips = IpBlacklist::active()->orderByDesc('created_at')->get();
        return $this->success($ips);
    }

    /**
     * @OA\Post(
     *     path="/blacklist",
     *     summary="Agregar IP a blacklist",
     *     tags={"System"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"ip_address", "reason"},
     *         @OA\Property(property="ip_address", type="string", example="192.168.1.100"),
     *         @OA\Property(property="reason", type="string", example="Flood attack"),
     *         @OA\Property(property="permanent", type="boolean", default=false),
     *         @OA\Property(property="duration", type="integer", description="Segundos", example=3600)
     *     )),
     *     @OA\Response(response=201, description="IP bloqueada")
     * )
     */
    public function addToBlacklist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255',
            'permanent' => 'boolean',
            'duration' => 'nullable|integer|min:60', // seconds
        ]);

        $expiresAt = null;
        if (!($validated['permanent'] ?? false) && isset($validated['duration'])) {
            $expiresAt = now()->addSeconds($validated['duration']);
        }

        $blacklist = IpBlacklist::updateOrCreate(
            ['ip_address' => $validated['ip_address']],
            [
                'reason' => $validated['reason'],
                'source' => 'manual',
                'permanent' => $validated['permanent'] ?? false,
                'expires_at' => $expiresAt,
            ]
        );

        // Also add to Redis for immediate effect
        if ($validated['permanent'] ?? false) {
            Redis::set("voip:blocked:{$validated['ip_address']}", 'manual');
        } else {
            Redis::setex("voip:blocked:{$validated['ip_address']}", $validated['duration'] ?? 3600, 'manual');
        }

        return $this->success($blacklist, [], 201);
    }

    public function removeFromBlacklist(int $id): JsonResponse
    {
        $blacklist = IpBlacklist::find($id);

        if (!$blacklist) {
            return $this->notFound('IP not found in blacklist');
        }

        // Remove from Redis
        Redis::del("voip:blocked:{$blacklist->ip_address}");

        $blacklist->delete();

        return $this->success(['deleted' => true]);
    }

    public function reloadKamailio(): JsonResponse
    {
        $result = Process::run('kamcmd dispatcher.reload');

        if ($result->successful()) {
            return $this->success(['message' => 'Kamailio reloaded successfully']);
        }

        return $this->error('Failed to reload Kamailio', 'RELOAD_FAILED', [
            'output' => $result->output(),
            'error' => $result->errorOutput(),
        ], 500);
    }

    public function flushCache(): JsonResponse
    {
        Redis::flushdb();

        return $this->success(['message' => 'Cache flushed successfully']);
    }

    private function checkKamailio(): array
    {
        $result = Process::run('systemctl is-active kamailio');
        $active = trim($result->output()) === 'active';

        return [
            'name' => 'kamailio',
            'status' => $active ? 'ok' : 'critical',
        ];
    }

    private function checkMysql(): array
    {
        try {
            DB::connection()->getPdo();
            return ['name' => 'mysql', 'status' => 'ok'];
        } catch (\Exception $e) {
            return ['name' => 'mysql', 'status' => 'critical'];
        }
    }

    private function checkRedis(): array
    {
        try {
            Redis::ping();
            return ['name' => 'redis', 'status' => 'ok'];
        } catch (\Exception $e) {
            return ['name' => 'redis', 'status' => 'critical'];
        }
    }

    private function checkDisk(): array
    {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        $usedPct = round((($total - $free) / $total) * 100, 2);

        $status = match (true) {
            $usedPct >= 95 => 'critical',
            $usedPct >= 85 => 'warning',
            default => 'ok',
        };

        return [
            'name' => 'disk',
            'status' => $status,
            'used_pct' => $usedPct,
        ];
    }

    private function checkLoad(): array
    {
        $load = sys_getloadavg()[0];
        $cpus = (int) shell_exec('nproc');

        $status = match (true) {
            $load >= $cpus * 2 => 'critical',
            $load >= $cpus => 'warning',
            default => 'ok',
        };

        return [
            'name' => 'load',
            'status' => $status,
            'load_1m' => $load,
        ];
    }

    private function getUptime(): string
    {
        $uptime = (int) file_get_contents('/proc/uptime');
        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $minutes = floor(($uptime % 3600) / 60);

        return "{$days}d {$hours}h {$minutes}m";
    }
}
