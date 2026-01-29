<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\CustomerIp;
use App\Models\ActiveCall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_created(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'company' => 'Test Company',
            'email' => 'test@example.com',
            'max_channels' => 10,
            'max_cps' => 5,
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);
        $this->assertNotNull($customer->uuid);
    }

    public function test_customer_has_ips_relationship(): void
    {
        $customer = Customer::factory()->create();

        CustomerIp::create([
            'customer_id' => $customer->id,
            'ip_address' => '192.168.1.100',
            'active' => true,
        ]);

        $this->assertCount(1, $customer->ips);
        $this->assertEquals('192.168.1.100', $customer->ips->first()->ip_address);
    }

    public function test_customer_daily_minutes_percentage(): void
    {
        $customer = Customer::factory()->create([
            'max_daily_minutes' => 100,
            'used_daily_minutes' => 75,
        ]);

        $this->assertEquals(75.0, $customer->daily_minutes_percentage);
    }

    public function test_customer_daily_minutes_percentage_with_no_limit(): void
    {
        $customer = Customer::factory()->create([
            'max_daily_minutes' => null,
            'used_daily_minutes' => 100,
        ]);

        $this->assertEquals(0, $customer->daily_minutes_percentage);
    }

    public function test_customer_monthly_minutes_percentage(): void
    {
        $customer = Customer::factory()->create([
            'max_monthly_minutes' => 1000,
            'used_monthly_minutes' => 500,
        ]);

        $this->assertEquals(50.0, $customer->monthly_minutes_percentage);
    }

    public function test_customer_scope_active(): void
    {
        Customer::factory()->count(3)->create(['active' => true]);
        Customer::factory()->count(2)->create(['active' => false]);

        $activeCustomers = Customer::where('active', true)->get();
        $this->assertCount(3, $activeCustomers);
    }

    public function test_customer_uuid_is_generated_automatically(): void
    {
        $customer = Customer::create([
            'name' => 'UUID Test Customer',
        ]);

        $this->assertNotNull($customer->uuid);
        $this->assertEquals(36, strlen($customer->uuid));
    }

    public function test_customer_has_active_calls_relationship(): void
    {
        $customer = Customer::factory()->create();

        ActiveCall::create([
            'call_id' => 'test-call-123',
            'customer_id' => $customer->id,
            'caller' => '+34612345678',
            'callee' => '+34987654321',
            'source_ip' => '192.168.1.100',
            'start_time' => now(),
        ]);

        $this->assertCount(1, $customer->activeCalls);
    }
}
