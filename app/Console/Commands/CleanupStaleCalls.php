<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use App\Models\Cdr;
use App\Models\ActiveCall;
use App\Models\Customer;
use App\Models\Carrier;

class CleanupStaleCalls extends Command
{
    protected $signature = 'calls:cleanup-stale
                            {--max-age=3600 : Maximum age in seconds for unanswered calls (default: 1 hour)}
                            {--max-duration=7200 : Maximum duration in seconds for answered calls (default: 2 hours)}
                            {--dry-run : Show what would be cleaned up without making changes}';

    protected $description = 'Clean up stale/orphan calls from active_calls table and create CDRs for them';

    public function handle()
    {
        $maxAge = (int) $this->option('max-age');
        $maxDuration = (int) $this->option('max-duration');
        $dryRun = $this->option('dry-run');

        $this->info('Cleaning up stale calls...');
        $this->info("Max age for unanswered calls: {$maxAge} seconds");
        $this->info("Max duration for answered calls: {$maxDuration} seconds");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Find stale unanswered calls (stuck in ringing state)
        $staleUnanswered = ActiveCall::where('answered', 0)
            ->where('start_time', '<', now()->subSeconds($maxAge))
            ->get();

        // Find stale answered calls (calls that have been running too long)
        $staleAnswered = ActiveCall::where('answered', 1)
            ->where('answer_time', '<', now()->subSeconds($maxDuration))
            ->get();

        $this->info("Found {$staleUnanswered->count()} stale unanswered calls");
        $this->info("Found {$staleAnswered->count()} stale answered calls");

        $cleanedCount = 0;

        // Process stale unanswered calls
        foreach ($staleUnanswered as $call) {
            $this->line("Processing stale unanswered call: {$call->call_id}");

            if (!$dryRun) {
                // Create CDR for failed call
                Cdr::create([
                    'uuid' => Str::uuid(),
                    'call_id' => $call->call_id,
                    'customer_id' => $call->customer_id,
                    'carrier_id' => $call->carrier_id,
                    'source_ip' => $call->source_ip,
                    'caller' => $call->caller,
                    'callee' => $call->callee,
                    'start_time' => $call->start_time,
                    'end_time' => now(),
                    'duration' => 0,
                    'billable_duration' => 0,
                    'sip_code' => 408,
                    'sip_reason' => 'Request Timeout',
                    'hangup_cause' => 'timeout',
                ]);

                // Decrement Redis counters safely
                $this->decrementRedisCounter("voip:calls:{$call->customer_id}");
                if ($call->carrier_id) {
                    $this->decrementRedisCounter("voip:carrier_calls:{$call->carrier_id}");
                }

                // Clean up Redis keys for this call
                $this->cleanupRedisKeys($call->call_id);

                // Delete from active_calls
                $call->delete();
            }

            $cleanedCount++;
        }

        // Process stale answered calls
        foreach ($staleAnswered as $call) {
            $this->line("Processing stale answered call: {$call->call_id}");

            if (!$dryRun) {
                // Calculate duration
                $duration = $call->answer_time
                    ? now()->diffInSeconds($call->answer_time)
                    : 0;

                // Create CDR for call
                Cdr::create([
                    'uuid' => Str::uuid(),
                    'call_id' => $call->call_id,
                    'customer_id' => $call->customer_id,
                    'carrier_id' => $call->carrier_id,
                    'source_ip' => $call->source_ip,
                    'caller' => $call->caller,
                    'callee' => $call->callee,
                    'start_time' => $call->start_time,
                    'answer_time' => $call->answer_time,
                    'end_time' => now(),
                    'duration' => $duration,
                    'billable_duration' => $duration,
                    'sip_code' => 200,
                    'sip_reason' => 'OK',
                    'hangup_cause' => 'timeout',
                ]);

                // Update customer minutes
                $billableMinutes = max(1, ceil($duration / 60));
                Customer::where('id', $call->customer_id)->increment('used_daily_minutes', $billableMinutes);
                Customer::where('id', $call->customer_id)->increment('used_monthly_minutes', $billableMinutes);

                // Update carrier statistics
                if ($call->carrier_id) {
                    Carrier::where('id', $call->carrier_id)->increment('daily_calls');
                    Carrier::where('id', $call->carrier_id)->increment('daily_minutes', $billableMinutes);
                }

                // Decrement Redis counters safely
                $this->decrementRedisCounter("voip:calls:{$call->customer_id}");
                if ($call->carrier_id) {
                    $this->decrementRedisCounter("voip:carrier_calls:{$call->carrier_id}");
                }

                // Clean up Redis keys for this call
                $this->cleanupRedisKeys($call->call_id);

                // Delete from active_calls
                $call->delete();
            }

            $cleanedCount++;
        }

        // Also check Redis for orphan call counters
        if (!$dryRun) {
            $this->cleanupOrphanRedisCounters();
        }

        if ($dryRun) {
            $this->info("Would have cleaned up {$cleanedCount} stale calls");
        } else {
            $this->info("Successfully cleaned up {$cleanedCount} stale calls");
        }

        return Command::SUCCESS;
    }

    private function decrementRedisCounter(string $key): void
    {
        try {
            $value = Redis::get($key);
            if ($value !== null && (int)$value > 0) {
                Redis::decr($key);
            }
        } catch (\Exception $e) {
            $this->warn("Error decrementing Redis counter {$key}: " . $e->getMessage());
        }
    }

    private function cleanupRedisKeys(string $callId): void
    {
        try {
            Redis::del([
                "voip:pdd:{$callId}",
                "voip:codec:{$callId}",
                "voip:ua:{$callId}",
                "voip:codecs:{$callId}",
                "voip:trace:{$callId}",
                "voip:progress:{$callId}",
            ]);
        } catch (\Exception $e) {
            $this->warn("Error cleaning up Redis keys for {$callId}: " . $e->getMessage());
        }
    }

    private function cleanupOrphanRedisCounters(): void
    {
        $this->line('Checking for orphan Redis counters...');

        try {
            // Get all customer call counters
            $customerKeys = Redis::keys('voip:calls:*');
            foreach ($customerKeys as $key) {
                // Extract customer ID from key
                preg_match('/voip:calls:(\d+)/', $key, $matches);
                if (!empty($matches[1])) {
                    $customerId = $matches[1];
                    $redisCount = (int) Redis::get($key);
                    $actualCount = ActiveCall::where('customer_id', $customerId)->count();

                    if ($redisCount > $actualCount) {
                        $this->warn("Fixing orphan counter for customer {$customerId}: Redis={$redisCount}, Actual={$actualCount}");
                        Redis::set($key, $actualCount);
                    }
                }
            }

            // Get all carrier call counters
            $carrierKeys = Redis::keys('voip:carrier_calls:*');
            foreach ($carrierKeys as $key) {
                preg_match('/voip:carrier_calls:(\d+)/', $key, $matches);
                if (!empty($matches[1])) {
                    $carrierId = $matches[1];
                    $redisCount = (int) Redis::get($key);
                    $actualCount = ActiveCall::where('carrier_id', $carrierId)->count();

                    if ($redisCount > $actualCount) {
                        $this->warn("Fixing orphan counter for carrier {$carrierId}: Redis={$redisCount}, Actual={$actualCount}");
                        Redis::set($key, $actualCount);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->warn("Error checking orphan counters: " . $e->getMessage());
        }
    }
}
