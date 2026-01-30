<?php

namespace Tests\Feature;

use App\Models\ActiveCall;
use App\Models\Carrier;
use App\Models\CarrierRate;
use App\Models\Cdr;
use App\Models\Customer;
use App\Models\CustomerIp;
use App\Models\CustomerRate;
use App\Models\DestinationPrefix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * Integration tests for Kamailio VoIP system
 *
 * These tests verify the system components work together
 * Run with: php artisan test --filter=KamailioIntegrationTest
 */
class KamailioIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;
    protected Carrier $carrier;
    protected CustomerIp $customerIp;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test customer
        $this->customer = Customer::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Test Customer',
            'company' => 'Test Company',
            'email' => 'test@example.com',
            'max_channels' => 10,
            'max_cps' => 5,
            'active' => true,
        ]);

        // Create test customer IP
        $this->customerIp = CustomerIp::create([
            'customer_id' => $this->customer->id,
            'ip_address' => '192.168.1.100',
            'description' => 'Test IP',
            'active' => true,
        ]);

        // Create test carrier
        $this->carrier = Carrier::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Test Carrier',
            'host' => '10.0.0.1',
            'port' => 5060,
            'transport' => 'udp',
            'codecs' => 'G729,PCMA,PCMU',
            'priority' => 1,
            'weight' => 100,
            'max_cps' => 10,
            'max_channels' => 50,
            'state' => 'active',
        ]);
    }

    /**
     * Test that Redis is accessible and working
     */
    public function test_redis_connection(): void
    {
        $result = Redis::ping();
        $this->assertEquals('PONG', $result);
    }

    /**
     * Test that customer IP authorization data exists
     */
    public function test_customer_ips_exist(): void
    {
        $count = CustomerIp::where('active', true)->count();
        $this->assertGreaterThan(0, $count, 'No active customer IPs found');
    }

    /**
     * Test that active carriers exist
     */
    public function test_active_carriers_exist(): void
    {
        $count = Carrier::where('state', 'active')->count();
        $this->assertGreaterThan(0, $count, 'No active carriers found');
    }

    /**
     * Test customer can be found by IP
     */
    public function test_customer_lookup_by_ip(): void
    {
        $customerIp = CustomerIp::where('active', true)
            ->whereHas('customer', fn($q) => $q->where('active', true))
            ->first();

        $this->assertNotNull($customerIp, 'No active customer IP found');

        $customer = Customer::whereHas('ips', function ($query) use ($customerIp) {
            $query->where('ip_address', $customerIp->ip_address)->where('active', true);
        })->where('active', true)->first();

        $this->assertNotNull($customer);
        $this->assertEquals($customerIp->customer_id, $customer->id);
    }

    /**
     * Test carrier rate lookup for destination
     */
    public function test_carrier_rate_lookup(): void
    {
        // Create destination prefix for Spain
        $prefix = DestinationPrefix::create([
            'prefix' => '34',
            'name' => 'Spain',
            'country_code' => 'ES',
        ]);

        // Create carrier rate
        CarrierRate::create([
            'carrier_id' => $this->carrier->id,
            'destination_prefix_id' => $prefix->id,
            'cost_per_minute' => 0.015,
            'billing_increment' => 6,
            'effective_date' => now()->toDateString(),
            'active' => true,
        ]);

        // Check if we can find a rate for Spain (34)
        $carrier = Carrier::where('state', 'active')
            ->whereHas('rates', function ($query) {
                $query->where('active', true)
                    ->whereHas('destinationPrefix', function ($q) {
                        $q->where('prefix', 'like', '34%');
                    });
            })
            ->first();

        $this->assertNotNull($carrier, 'No carrier with Spain rate found');
    }

    /**
     * Test customer rate lookup for destination
     */
    public function test_customer_rate_lookup(): void
    {
        // Create destination prefix
        $prefix = DestinationPrefix::create([
            'prefix' => '1',
            'name' => 'USA',
            'country_code' => 'US',
        ]);

        // Create customer rate
        CustomerRate::create([
            'customer_id' => $this->customer->id,
            'destination_prefix_id' => $prefix->id,
            'price_per_minute' => 0.02,
            'billing_increment' => 6,
            'effective_date' => now()->toDateString(),
            'active' => true,
        ]);

        $customer = Customer::where('active', true)
            ->whereHas('rates', function ($query) {
                $query->where('active', true);
            })
            ->first();

        $this->assertNotNull($customer, 'No customer with rates found');
        $this->assertGreaterThan(0, $customer->rates()->count());
    }

    /**
     * Test CDR creation
     */
    public function test_cdr_can_be_created(): void
    {
        $cdr = Cdr::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'call_id' => 'test-call-' . uniqid(),
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'source_ip' => '127.0.0.1',
            'caller' => '34666123456',
            'caller_original' => '34666123456',
            'callee' => '34911234567',
            'callee_original' => '34911234567',
            'start_time' => now(),
            'sip_code' => 200,
            'sip_reason' => 'OK',
            'duration' => 60,
            'billable_duration' => 60,
        ]);

        $this->assertNotNull($cdr->id);
        $this->assertEquals($this->customer->id, $cdr->customer_id);
    }

    /**
     * Test active call tracking
     */
    public function test_active_call_tracking(): void
    {
        $callId = 'test-active-' . uniqid();

        $activeCall = ActiveCall::create([
            'call_id' => $callId,
            'customer_id' => $this->customer->id,
            'caller' => '34666123456',
            'callee' => '34911234567',
            'source_ip' => '127.0.0.1',
            'start_time' => now(),
            'answered' => false,
        ]);

        $this->assertNotNull($activeCall->id);

        // Verify we can find it
        $found = ActiveCall::where('call_id', $callId)->first();
        $this->assertNotNull($found);
    }

    /**
     * Test Redis call counter
     */
    public function test_redis_call_counters(): void
    {
        $key = "customer:{$this->customer->id}:active_calls";

        // Set counter
        Redis::set($key, 5);
        $count = Redis::get($key);
        $this->assertEquals(5, (int) $count);

        // Increment
        Redis::incr($key);
        $count = Redis::get($key);
        $this->assertEquals(6, (int) $count);

        // Cleanup
        Redis::del($key);
    }

    /**
     * Test customer channel limit check
     */
    public function test_customer_channel_limit(): void
    {
        $this->assertGreaterThan(0, $this->customer->max_channels);

        $key = "customer:{$this->customer->id}:active_calls";

        // Set calls to max - 1
        Redis::set($key, $this->customer->max_channels - 1);
        $currentCalls = (int) Redis::get($key);

        $this->assertTrue($currentCalls < $this->customer->max_channels, 'Customer should have capacity');

        // Set calls to max
        Redis::set($key, $this->customer->max_channels);
        $currentCalls = (int) Redis::get($key);

        $this->assertFalse($currentCalls < $this->customer->max_channels, 'Customer should be at capacity');

        // Cleanup
        Redis::del($key);
    }

    /**
     * Test billing calculation
     */
    public function test_billing_calculation(): void
    {
        // Create destination prefix
        $prefix = DestinationPrefix::create([
            'prefix' => '44',
            'name' => 'UK',
            'country_code' => 'GB',
        ]);

        // Create customer rate
        $rate = CustomerRate::create([
            'customer_id' => $this->customer->id,
            'destination_prefix_id' => $prefix->id,
            'price_per_minute' => 0.025,
            'billing_increment' => 6,
            'effective_date' => now()->toDateString(),
            'active' => true,
        ]);

        // Calculate cost for 60 seconds
        $duration = 60;
        $billingIncrement = $rate->billing_increment ?: 1;
        $billableSeconds = ceil($duration / $billingIncrement) * $billingIncrement;
        $cost = ($rate->price_per_minute / 60) * $billableSeconds;

        $this->assertGreaterThan(0, $cost);
        $this->assertIsFloat($cost);
    }

    /**
     * Test carrier failover order
     */
    public function test_carrier_priority_order(): void
    {
        // Create additional carriers with different priorities
        Carrier::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Carrier Priority 2',
            'host' => '10.0.0.2',
            'port' => 5060,
            'transport' => 'udp',
            'codecs' => 'G729,PCMA',
            'priority' => 2,
            'weight' => 50,
            'max_cps' => 10,
            'max_channels' => 50,
            'state' => 'active',
        ]);

        $carriers = Carrier::where('state', 'active')
            ->orderBy('priority')
            ->orderByDesc('weight')
            ->get();

        $this->assertGreaterThan(0, $carriers->count());

        // Verify they're ordered by priority
        $prevPriority = 0;
        foreach ($carriers as $carrier) {
            $this->assertGreaterThanOrEqual($prevPriority, $carrier->priority);
            $prevPriority = $carrier->priority;
        }
    }
}
