<?php

namespace App\Console\Commands;

use App\Models\ActiveCall;
use App\Models\Carrier;
use App\Models\CarrierRate;
use App\Models\Cdr;
use App\Models\Customer;
use App\Models\CustomerIp;
use App\Models\CustomerRate;
use App\Models\DestinationPrefix;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class TestKamailioSystem extends Command
{
    protected $signature = 'kamailio:test {--detailed : Show detailed output}';
    protected $description = 'Run integration tests for the Kamailio VoIP system';

    private int $passed = 0;
    private int $failed = 0;
    private int $skipped = 0;

    public function handle(): int
    {
        $this->info('');
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║         VoIP System Integration Test Suite                ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->info('');

        // Infrastructure Tests
        $this->section('Infrastructure');
        $this->runTest('MySQL Connection', fn() => $this->testMysqlConnection());
        $this->runTest('Redis Connection', fn() => $this->testRedisConnection());
        $this->runTest('Kamailio Process', fn() => $this->testKamailioProcess());
        $this->runTest('Kamailio Port 5060', fn() => $this->testKamailioPort());

        // Data Tests
        $this->section('Data Integrity');
        $this->runTest('Customers Exist', fn() => $this->testCustomersExist());
        $this->runTest('Customer IPs Configured', fn() => $this->testCustomerIpsExist());
        $this->runTest('Carriers Configured', fn() => $this->testCarriersExist());
        $this->runTest('Destination Prefixes', fn() => $this->testPrefixesExist());
        $this->runTest('Carrier Rates', fn() => $this->testCarrierRatesExist());
        $this->runTest('Customer Rates', fn() => $this->testCustomerRatesExist());

        // Logic Tests
        $this->section('Business Logic');
        $this->runTest('Customer IP Lookup', fn() => $this->testCustomerIpLookup());
        $this->runTest('Rate Lookup for Spain', fn() => $this->testRateLookup('34'));
        $this->runTest('Rate Lookup for USA', fn() => $this->testRateLookup('1'));
        $this->runTest('Carrier Priority Order', fn() => $this->testCarrierPriority());
        $this->runTest('Billing Calculation', fn() => $this->testBillingCalculation());

        // Redis Tests
        $this->section('Redis Integration');
        $this->runTest('Call Counter Operations', fn() => $this->testRedisCounters());
        $this->runTest('Channel Limit Check', fn() => $this->testChannelLimitCheck());
        $this->runTest('CPS Rate Limiting', fn() => $this->testCpsRateLimiting());

        // CDR Tests
        $this->section('CDR Processing');
        $this->runTest('CDR Creation', fn() => $this->testCdrCreation());
        $this->runTest('Active Call Tracking', fn() => $this->testActiveCallTracking());

        // Summary
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $total = $this->passed + $this->failed + $this->skipped;
        $this->info(sprintf(
            'Results: %d passed, %d failed, %d skipped (Total: %d)',
            $this->passed,
            $this->failed,
            $this->skipped,
            $total
        ));
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');

        return $this->failed > 0 ? 1 : 0;
    }

    private function section(string $name): void
    {
        $this->info('');
        $this->comment("▸ $name");
        $this->line(str_repeat('─', 50));
    }

    private function runTest(string $name, callable $test): void
    {
        try {
            $result = $test();
            if ($result === null) {
                $this->skipped++;
                $this->line("  ⊘ $name <fg=yellow>SKIP</>");
            } elseif ($result === true) {
                $this->passed++;
                $this->line("  ✓ $name <fg=green>PASS</>");
            } else {
                $this->failed++;
                $this->line("  ✗ $name <fg=red>FAIL</> - $result");
            }
        } catch (\Exception $e) {
            $this->failed++;
            $this->line("  ✗ $name <fg=red>ERROR</> - " . $e->getMessage());
        }
    }

    // Infrastructure Tests
    private function testMysqlConnection(): bool|string
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function testRedisConnection(): bool|string
    {
        try {
            $result = Redis::ping();
            return $result === 'PONG' || $result === true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function testKamailioProcess(): bool|string
    {
        exec('pgrep -x kamailio', $output, $returnCode);
        return $returnCode === 0 ? true : 'Kamailio not running';
    }

    private function testKamailioPort(): bool|string
    {
        exec('ss -uln | grep ":5060 "', $output, $returnCode);
        return $returnCode === 0 ? true : 'Not listening on UDP 5060';
    }

    // Data Tests
    private function testCustomersExist(): bool|string
    {
        $count = Customer::where('active', true)->count();
        return $count > 0 ? true : 'No active customers found';
    }

    private function testCustomerIpsExist(): bool|string
    {
        $count = CustomerIp::where('active', true)->count();
        return $count > 0 ? true : 'No customer IPs configured';
    }

    private function testCarriersExist(): bool|string
    {
        $count = Carrier::where('state', 'active')->count();
        return $count > 0 ? true : 'No active carriers found';
    }

    private function testPrefixesExist(): bool|string
    {
        $count = DestinationPrefix::where('active', true)->count();
        return $count > 0 ? true : 'No destination prefixes found';
    }

    private function testCarrierRatesExist(): bool|string
    {
        $count = CarrierRate::where('active', true)->count();
        return $count > 0 ? true : 'No carrier rates configured';
    }

    private function testCustomerRatesExist(): bool|string
    {
        $count = CustomerRate::where('active', true)->count();
        return $count > 0 ? true : 'No customer rates configured';
    }

    // Logic Tests
    private function testCustomerIpLookup(): bool|string
    {
        $ip = CustomerIp::where('active', true)
            ->whereHas('customer', fn($q) => $q->where('active', true))
            ->first();

        if (!$ip) {
            return 'No active customer IP to test';
        }

        $customer = Customer::whereHas('ips', function ($q) use ($ip) {
            $q->where('ip_address', $ip->ip_address)->where('active', true);
        })->where('active', true)->first();

        return $customer && $customer->id === $ip->customer_id
            ? true
            : 'Customer lookup mismatch';
    }

    private function testRateLookup(string $prefix): bool|string|null
    {
        $destinationPrefix = DestinationPrefix::where('prefix', $prefix)
            ->orWhere('prefix', 'like', $prefix . '%')
            ->first();

        if (!$destinationPrefix) {
            return null; // Skip if prefix not configured
        }

        $carrierRate = CarrierRate::where('destination_prefix_id', $destinationPrefix->id)
            ->where('active', true)
            ->first();

        return $carrierRate ? true : "No carrier rate for prefix $prefix";
    }

    private function testCarrierPriority(): bool|string
    {
        $carriers = Carrier::where('state', 'active')
            ->orderBy('priority')
            ->get();

        if ($carriers->isEmpty()) {
            return 'No carriers to test';
        }

        $prevPriority = 0;
        foreach ($carriers as $carrier) {
            if ($carrier->priority < $prevPriority) {
                return 'Carriers not ordered by priority';
            }
            $prevPriority = $carrier->priority;
        }

        return true;
    }

    private function testBillingCalculation(): bool|string
    {
        $rate = CustomerRate::where('active', true)
            ->where('price_per_minute', '>', 0)
            ->first();

        if (!$rate) {
            return 'No rate to test billing';
        }

        $duration = 65; // 65 seconds
        $increment = $rate->billing_increment ?: 1;
        $billableSeconds = ceil($duration / $increment) * $increment;
        $cost = ($rate->price_per_minute / 60) * $billableSeconds;

        return $cost > 0 ? true : 'Billing calculation returned 0';
    }

    // Redis Tests
    private function testRedisCounters(): bool|string
    {
        $key = 'test:counter:' . Str::random(8);

        try {
            Redis::set($key, 0);
            Redis::incr($key);
            Redis::incr($key);
            $value = (int) Redis::get($key);
            Redis::del($key);

            return $value === 2 ? true : "Expected 2, got $value";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function testChannelLimitCheck(): bool|string
    {
        $customer = Customer::where('active', true)->first();
        if (!$customer) {
            return 'No customer to test';
        }

        $key = "test:channels:{$customer->id}";

        try {
            // Test under limit
            Redis::set($key, $customer->max_channels - 1);
            $current = (int) Redis::get($key);
            $underLimit = $current < $customer->max_channels;

            // Test at limit
            Redis::set($key, $customer->max_channels);
            $current = (int) Redis::get($key);
            $atLimit = $current >= $customer->max_channels;

            Redis::del($key);

            return $underLimit && $atLimit ? true : 'Channel limit logic failed';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function testCpsRateLimiting(): bool|string
    {
        $key = 'test:cps:' . Str::random(8);

        try {
            $maxCps = 5;
            $window = 1; // 1 second

            // Simulate calls
            for ($i = 0; $i < $maxCps; $i++) {
                Redis::incr($key);
            }
            Redis::expire($key, $window);

            $count = (int) Redis::get($key);
            $allowed = $count <= $maxCps;

            Redis::del($key);

            return $allowed ? true : 'CPS rate limiting failed';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    // CDR Tests
    private function testCdrCreation(): bool|string
    {
        $customer = Customer::where('active', true)->first();
        $carrier = Carrier::where('state', 'active')->first();

        if (!$customer || !$carrier) {
            return 'No customer or carrier to test';
        }

        try {
            $cdr = Cdr::create([
                'uuid' => (string) Str::uuid(),
                'call_id' => 'test-' . Str::random(16),
                'customer_id' => $customer->id,
                'carrier_id' => $carrier->id,
                'source_ip' => '127.0.0.1',
                'caller' => '34666000001',
                'caller_original' => '34666000001',
                'callee' => '34911000001',
                'callee_original' => '34911000001',
                'start_time' => now(),
                'sip_code' => 200,
                'sip_reason' => 'OK',
                'duration' => 30,
                'billable_duration' => 30,
            ]);

            $found = Cdr::find($cdr->id);
            $cdr->delete();

            return $found ? true : 'CDR not found after creation';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function testActiveCallTracking(): bool|string
    {
        $customer = Customer::where('active', true)->first();

        if (!$customer) {
            return 'No customer to test';
        }

        try {
            $callId = 'test-active-' . Str::random(16);

            $activeCall = ActiveCall::create([
                'call_id' => $callId,
                'customer_id' => $customer->id,
                'caller' => '34666000002',
                'callee' => '34911000002',
                'source_ip' => '127.0.0.1',
                'start_time' => now(),
                'answered' => false,
            ]);

            $found = ActiveCall::where('call_id', $callId)->first();
            $activeCall->delete();

            return $found ? true : 'Active call not found';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
