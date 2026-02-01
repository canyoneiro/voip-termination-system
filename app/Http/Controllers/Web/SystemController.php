<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

class SystemController extends Controller
{
    /**
     * System overview dashboard
     */
    public function index()
    {
        $status = $this->getSystemStatus();
        return view('system.index', compact('status'));
    }

    /**
     * Log viewer
     */
    public function logs(Request $request)
    {
        $logType = $request->get('type', 'kamailio');
        $lines = $request->get('lines', 200);
        $filter = $request->get('filter', '');

        $logFiles = [
            'kamailio' => '/var/log/syslog',
            'laravel' => storage_path('logs/laravel.log'),
            'nginx_access' => '/var/log/nginx/access.log',
            'nginx_error' => '/var/log/nginx/error.log',
            'mysql' => '/var/log/syslog',  // MySQL/MariaDB logs to syslog
            'php' => '/var/log/php8.3-fpm.log',
            'fail2ban' => '/var/log/fail2ban.log',
        ];

        $logContent = '';
        $logFile = $logFiles[$logType] ?? $logFiles['kamailio'];

        if (file_exists($logFile) && is_readable($logFile)) {
            if ($logType === 'kamailio') {
                // For syslog, filter by kamailio
                $cmd = "grep -i kamailio $logFile | grep -v DEBUG | tail -n $lines";
                if (!empty($filter)) {
                    $filter = escapeshellarg($filter);
                    $cmd = "grep -i kamailio $logFile | grep -v DEBUG | grep -i $filter | tail -n $lines";
                }
                $logContent = shell_exec($cmd) ?? '';
            } elseif ($logType === 'mysql') {
                // MySQL/MariaDB logs to syslog
                $cmd = "grep -iE 'mysql|mariadb' $logFile | tail -n $lines";
                if (!empty($filter)) {
                    $filter = escapeshellarg($filter);
                    $cmd = "grep -iE 'mysql|mariadb' $logFile | grep -i $filter | tail -n $lines";
                }
                $logContent = shell_exec($cmd) ?? '';
                if (empty(trim($logContent))) {
                    $logContent = "No hay logs de MySQL/MariaDB recientes en syslog.";
                }
            } else {
                $cmd = "tail -n $lines $logFile";
                if (!empty($filter)) {
                    $filter = escapeshellarg($filter);
                    $cmd = "grep -i $filter $logFile | tail -n $lines";
                }
                $logContent = shell_exec($cmd) ?? '';
            }
        } else {
            $logContent = "Log file not found or not readable: $logFile";
        }

        return view('system.logs', [
            'logContent' => $logContent,
            'logType' => $logType,
            'logFiles' => array_keys($logFiles),
            'lines' => $lines,
            'filter' => $request->get('filter', ''),
        ]);
    }

    /**
     * Real-time log streaming via AJAX
     */
    public function logsStream(Request $request)
    {
        $logType = $request->get('type', 'kamailio');
        $since = $request->get('since', '');

        $logFiles = [
            'kamailio' => '/var/log/syslog',
            'laravel' => storage_path('logs/laravel.log'),
        ];

        $logFile = $logFiles[$logType] ?? $logFiles['kamailio'];
        $lines = [];

        if (file_exists($logFile)) {
            if ($logType === 'kamailio') {
                $cmd = "grep -i kamailio $logFile | tail -n 50";
            } else {
                $cmd = "tail -n 50 $logFile";
            }
            $output = shell_exec($cmd) ?? '';
            $lines = array_filter(explode("\n", $output));
        }

        return response()->json(['lines' => $lines]);
    }

    /**
     * System status page
     */
    public function status()
    {
        $status = $this->getSystemStatus();
        return view('system.status', compact('status'));
    }

    /**
     * Database viewer
     */
    public function database(Request $request)
    {
        $tables = DB::select('SHOW TABLES');
        $tableList = [];
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $count = DB::table($tableName)->count();
            $tableList[] = [
                'name' => $tableName,
                'count' => $count,
            ];
        }

        $selectedTable = $request->get('table');
        $tableData = null;
        $columns = [];
        $pagination = null;

        if ($selectedTable) {
            $columns = DB::select("SHOW COLUMNS FROM `$selectedTable`");
            $query = DB::table($selectedTable);

            // Apply search filter
            $search = $request->get('search');
            if ($search) {
                $query->where(function($q) use ($columns, $search) {
                    foreach ($columns as $col) {
                        $q->orWhere($col->Field, 'LIKE', "%$search%");
                    }
                });
            }

            $pagination = $query->orderByDesc(array_values((array)$columns[0])[0] ?? 'id')
                                ->paginate(50)
                                ->withQueryString();
            $tableData = $pagination->items();
        }

        return view('system.database', [
            'tables' => $tableList,
            'selectedTable' => $selectedTable,
            'columns' => $columns,
            'tableData' => $tableData,
            'pagination' => $pagination,
            'search' => $request->get('search', ''),
        ]);
    }

    /**
     * Execute system action
     */
    public function action(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'target' => 'required|string',
        ]);

        $action = $request->input('action');
        $target = $request->input('target');

        $allowedActions = ['restart', 'reload', 'stop', 'start', 'status'];
        $allowedTargets = [
            'kamailio' => 'kamailio',
            'mysql' => 'mysql',
            'nginx' => 'nginx',
            'redis' => 'redis-server',
            'php8.3-fpm' => 'php8.3-fpm',
            'fail2ban' => 'fail2ban',
            'supervisor' => 'supervisor',
        ];

        if (!in_array($action, $allowedActions)) {
            return back()->with('error', 'Invalid action');
        }

        if (!isset($allowedTargets[$target])) {
            return back()->with('error', 'Invalid target service');
        }

        $serviceName = $allowedTargets[$target];

        // Special handling for Kamailio reload
        if ($target === 'kamailio' && $action === 'reload') {
            $output = shell_exec('kamcmd cfg.reload 2>&1');
            $output .= shell_exec('kamcmd dispatcher.reload 2>&1');
            return back()->with('success', "Kamailio configuration reloaded. Output: $output");
        }

        // Special handling for PHP-FPM (restart in background to avoid killing current request)
        if ($target === 'php8.3-fpm' && $action === 'restart') {
            shell_exec('(sleep 1 && sudo /usr/bin/systemctl restart php8.3-fpm) > /dev/null 2>&1 &');
            return back()->with('success', 'PHP-FPM se reiniciara en 1 segundo. Recarga la pagina en unos segundos.');
        }

        $cmd = "sudo /usr/bin/systemctl $action $serviceName 2>&1";
        $output = shell_exec($cmd);

        return back()->with('success', "Executed: systemctl $action $serviceName")->with('output', $output ?: 'OK');
    }

    /**
     * Run full system verification
     */
    public function verify()
    {
        $results = [];

        // 1. Check services
        $services = ['kamailio', 'mysql', 'nginx', 'redis-server', 'php8.3-fpm', 'fail2ban', 'supervisor'];
        foreach ($services as $service) {
            $status = trim(shell_exec("systemctl is-active $service 2>/dev/null") ?? 'unknown');
            $results['services'][$service] = [
                'status' => $status,
                'ok' => $status === 'active',
            ];
        }

        // 2. Check database connection
        try {
            DB::select('SELECT 1');
            $results['database'] = ['ok' => true, 'message' => 'Conexion OK'];
        } catch (\Exception $e) {
            $results['database'] = ['ok' => false, 'message' => $e->getMessage()];
        }

        // 3. Check Redis connection
        try {
            Redis::ping();
            $results['redis'] = ['ok' => true, 'message' => 'Conexion OK'];
        } catch (\Exception $e) {
            $results['redis'] = ['ok' => false, 'message' => $e->getMessage()];
        }

        // 4. Check Kamailio
        $kamStatus = shell_exec('kamcmd core.uptime 2>&1');
        $results['kamailio_rpc'] = [
            'ok' => str_contains($kamStatus ?? '', 'uptime'),
            'message' => $kamStatus ? 'RPC OK' : 'RPC no responde',
        ];

        // 5. Check disk space
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskPercent = round((($diskTotal - $diskFree) / $diskTotal) * 100, 1);
        $results['disk'] = [
            'ok' => $diskPercent < 90,
            'message' => "Uso: {$diskPercent}%",
        ];

        // 6. Check queue workers
        $queueStatus = shell_exec('sudo /usr/bin/supervisorctl status 2>&1');
        $runningCount = substr_count($queueStatus ?? '', 'RUNNING');
        $results['queue'] = [
            'ok' => $runningCount >= 2,
            'message' => $runningCount >= 2 ? "$runningCount workers activos" : 'Workers caidos',
        ];

        // 7. Check pending jobs
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            $results['jobs'] = [
                'ok' => $failedJobs == 0,
                'message' => "Pendientes: $pendingJobs, Fallidos: $failedJobs",
            ];
        } catch (\Exception $e) {
            $results['jobs'] = ['ok' => true, 'message' => 'Sin tabla jobs'];
        }

        // Calculate overall status
        $allOk = true;
        foreach ($results as $key => $result) {
            if ($key === 'services') {
                foreach ($result as $svc) {
                    if (!$svc['ok']) $allOk = false;
                }
            } else {
                if (!$result['ok']) $allOk = false;
            }
        }
        $results['overall'] = $allOk;

        return back()->with('verification', $results);
    }

    /**
     * Kamailio specific actions
     */
    public function kamailioAction(Request $request)
    {
        $action = $request->input('action');

        $commands = [
            'reload_dispatcher' => 'kamcmd dispatcher.reload',
            'reload_permissions' => 'kamcmd permissions.addressReload',
            'show_dispatcher' => 'kamcmd dispatcher.list',
            'show_stats' => 'kamcmd stats.get_statistics all',
            'clear_dialogs' => 'kamcmd dlg.list',
            'ping_active_on' => 'kamcmd dispatcher.ping_active 1',
            'ping_active_off' => 'kamcmd dispatcher.ping_active 0',
        ];

        if (!isset($commands[$action])) {
            return back()->with('error', 'Invalid Kamailio action');
        }

        $output = shell_exec($commands[$action] . ' 2>&1');

        if ($request->ajax()) {
            return response()->json(['output' => $output]);
        }

        return back()->with('success', "Executed: {$commands[$action]}")->with('output', $output);
    }

    /**
     * Clear various caches
     */
    public function clearCache(Request $request)
    {
        $type = $request->input('type', 'all');

        switch ($type) {
            case 'laravel':
                \Artisan::call('cache:clear');
                \Artisan::call('config:clear');
                \Artisan::call('view:clear');
                \Artisan::call('route:clear');
                $message = 'Laravel cache cleared';
                break;
            case 'redis':
                Redis::flushdb();
                $message = 'Redis database flushed';
                break;
            case 'opcache':
                if (function_exists('opcache_reset')) {
                    opcache_reset();
                }
                $message = 'OPcache cleared';
                break;
            default:
                \Artisan::call('cache:clear');
                Redis::flushdb();
                if (function_exists('opcache_reset')) {
                    opcache_reset();
                }
                $message = 'All caches cleared';
        }

        return back()->with('success', $message);
    }

    /**
     * Get comprehensive system status
     */
    private function getSystemStatus(): array
    {
        $status = [
            'services' => [],
            'system' => [],
            'database' => [],
            'redis' => [],
            'kamailio' => [],
        ];

        // Services status
        $services = ['kamailio', 'mysql', 'nginx', 'redis-server', 'php8.3-fpm', 'fail2ban', 'supervisor'];
        foreach ($services as $service) {
            $isActive = trim(shell_exec("systemctl is-active $service 2>/dev/null") ?? 'unknown');
            $status['services'][$service] = [
                'status' => $isActive,
                'running' => $isActive === 'active',
            ];
        }

        // System info
        $status['system'] = [
            'hostname' => gethostname(),
            'uptime' => trim(shell_exec('uptime -p') ?? 'unknown'),
            'load' => sys_getloadavg(),
            'memory' => $this->getMemoryInfo(),
            'disk' => $this->getDiskInfo(),
            'cpu_cores' => (int)(shell_exec('nproc') ?? 1),
        ];

        // Database status
        try {
            $dbStatus = DB::select('SHOW STATUS WHERE Variable_name IN ("Connections", "Threads_connected", "Queries", "Uptime")');
            foreach ($dbStatus as $row) {
                $status['database'][$row->Variable_name] = $row->Value;
            }
            $status['database']['connected'] = true;
        } catch (\Exception $e) {
            $status['database']['connected'] = false;
            $status['database']['error'] = $e->getMessage();
        }

        // Redis status
        try {
            $redisInfo = Redis::info();
            $status['redis'] = [
                'connected' => true,
                'version' => $redisInfo['redis_version'] ?? 'unknown',
                'used_memory' => $redisInfo['used_memory_human'] ?? 'unknown',
                'connected_clients' => $redisInfo['connected_clients'] ?? 0,
                'total_connections' => $redisInfo['total_connections_received'] ?? 0,
            ];
        } catch (\Exception $e) {
            $status['redis'] = [
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }

        // Kamailio status
        $kamStats = shell_exec('kamcmd stats.get_statistics core: 2>/dev/null') ?? '';
        $status['kamailio'] = [
            'stats' => $kamStats,
            'dispatcher' => shell_exec('kamcmd dispatcher.list 2>/dev/null') ?? '',
        ];

        // VoIP specific stats
        $status['voip'] = [
            'active_calls' => DB::table('active_calls')->count(),
            'cdrs_today' => DB::table('cdrs')->whereDate('start_time', today())->count(),
            'customers_active' => DB::table('customers')->where('active', 1)->count(),
            'carriers_active' => DB::table('carriers')->where('state', 'active')->count(),
            'alerts_unacked' => DB::table('alerts')->where('acknowledged', 0)->count(),
        ];

        return $status;
    }

    private function getMemoryInfo(): array
    {
        $memInfo = [];
        $data = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/', $data, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $data, $available);

        $totalKb = $total[1] ?? 0;
        $availableKb = $available[1] ?? 0;
        $usedKb = $totalKb - $availableKb;

        return [
            'total' => round($totalKb / 1024 / 1024, 2) . ' GB',
            'used' => round($usedKb / 1024 / 1024, 2) . ' GB',
            'available' => round($availableKb / 1024 / 1024, 2) . ' GB',
            'percent_used' => $totalKb > 0 ? round(($usedKb / $totalKb) * 100, 1) : 0,
        ];
    }

    private function getDiskInfo(): array
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;

        return [
            'total' => round($total / 1024 / 1024 / 1024, 2) . ' GB',
            'used' => round($used / 1024 / 1024 / 1024, 2) . ' GB',
            'free' => round($free / 1024 / 1024 / 1024, 2) . ' GB',
            'percent_used' => $total > 0 ? round(($used / $total) * 100, 1) : 0,
        ];
    }

    /**
     * API endpoint for status JSON
     */
    public function statusJson()
    {
        return response()->json($this->getSystemStatus());
    }

    /**
     * Run a SQL query (read-only)
     */
    public function queryDatabase(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $query = $request->input('query');

        // Security: Only allow SELECT queries
        $queryLower = strtolower(trim($query));
        if (!str_starts_with($queryLower, 'select') && !str_starts_with($queryLower, 'show') && !str_starts_with($queryLower, 'describe')) {
            return back()->with('error', 'Only SELECT, SHOW, and DESCRIBE queries are allowed');
        }

        try {
            $results = DB::select($query);
            return back()->with('queryResults', $results)->with('executedQuery', $query);
        } catch (\Exception $e) {
            return back()->with('error', 'Query error: ' . $e->getMessage());
        }
    }
}
