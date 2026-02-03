<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class SyncFirewallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

    /**
     * Debounce key to prevent multiple syncs in short succession.
     */
    protected const DEBOUNCE_KEY = 'firewall_sync_pending';
    protected const DEBOUNCE_SECONDS = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $reason = 'manual'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if sync was already done recently (debounce)
        $lastSync = Cache::get('firewall_last_sync');
        if ($lastSync && (now()->timestamp - $lastSync) < self::DEBOUNCE_SECONDS) {
            Log::info('Firewall sync skipped (debounced)', [
                'reason' => $this->reason,
                'seconds_since_last' => now()->timestamp - $lastSync,
            ]);
            return;
        }

        $scriptPath = '/opt/voip-scripts/sync-firewall.sh';

        if (!file_exists($scriptPath)) {
            Log::error('Firewall sync script not found', ['path' => $scriptPath]);
            return;
        }

        Log::info('Starting firewall sync', ['reason' => $this->reason]);

        $result = Process::timeout(60)->run("sudo {$scriptPath} 2>&1");

        if ($result->successful()) {
            Cache::put('firewall_last_sync', now()->timestamp, 300);

            Log::info('Firewall sync completed', [
                'reason' => $this->reason,
                'output' => $result->output(),
            ]);
        } else {
            Log::error('Firewall sync failed', [
                'reason' => $this->reason,
                'exit_code' => $result->exitCode(),
                'output' => $result->output(),
                'error' => $result->errorOutput(),
            ]);

            // Re-throw to trigger retry
            throw new \RuntimeException('Firewall sync failed: ' . $result->errorOutput());
        }
    }

    /**
     * Dispatch the job with debouncing.
     * Multiple calls within the debounce window will result in a single job.
     */
    public static function dispatchDebounced(string $reason = 'auto'): void
    {
        // Use cache lock to prevent multiple dispatches
        $lockKey = self::DEBOUNCE_KEY;

        if (Cache::has($lockKey)) {
            Log::debug('Firewall sync already pending, skipping dispatch', ['reason' => $reason]);
            return;
        }

        // Set pending flag
        Cache::put($lockKey, true, self::DEBOUNCE_SECONDS);

        // Dispatch with delay to allow batching
        static::dispatch($reason)->delay(now()->addSeconds(2));
    }
}
