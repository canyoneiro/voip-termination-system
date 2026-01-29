<?php

namespace Tests\Unit\Models;

use App\Models\Cdr;
use App\Models\Customer;
use App\Models\Carrier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CdrTest extends TestCase
{
    use RefreshDatabase;

    public function test_cdr_can_be_created(): void
    {
        $customer = Customer::factory()->create();
        $carrier = Carrier::factory()->create();

        $cdr = Cdr::create([
            'call_id' => 'test-call-123@example.com',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'source_ip' => '192.168.1.100',
            'caller' => '+34612345678',
            'callee' => '+34987654321',
            'start_time' => now(),
            'duration' => 120,
            'sip_code' => 200,
            'sip_reason' => 'OK',
        ]);

        $this->assertDatabaseHas('cdrs', [
            'call_id' => 'test-call-123@example.com',
        ]);
        $this->assertNotNull($cdr->uuid);
    }

    public function test_cdr_belongs_to_customer(): void
    {
        $customer = Customer::factory()->create(['name' => 'Test Customer']);
        $cdr = Cdr::factory()->create(['customer_id' => $customer->id]);

        $this->assertEquals('Test Customer', $cdr->customer->name);
    }

    public function test_cdr_belongs_to_carrier(): void
    {
        $carrier = Carrier::factory()->create(['name' => 'Test Carrier']);
        $cdr = Cdr::factory()->answered()->create(['carrier_id' => $carrier->id]);

        $this->assertEquals('Test Carrier', $cdr->carrier->name);
    }

    public function test_cdr_factory_answered_state(): void
    {
        $cdr = Cdr::factory()->answered()->create();

        $this->assertEquals(200, $cdr->sip_code);
        $this->assertEquals('OK', $cdr->sip_reason);
        $this->assertGreaterThan(0, $cdr->duration);
    }

    public function test_cdr_factory_failed_state(): void
    {
        $cdr = Cdr::factory()->failed()->create();

        $this->assertNotEquals(200, $cdr->sip_code);
        $this->assertEquals(0, $cdr->duration);
        $this->assertEquals('failed', $cdr->hangup_cause);
    }

    public function test_cdr_answered_scope(): void
    {
        Cdr::factory()->answered()->count(5)->create();
        Cdr::factory()->failed()->count(3)->create();

        $answered = Cdr::where('sip_code', 200)->get();
        $this->assertCount(5, $answered);
    }

    public function test_cdr_duration_calculation(): void
    {
        $cdr = Cdr::factory()->create([
            'duration' => 180,
            'billable_duration' => 180,
        ]);

        $this->assertEquals(180, $cdr->duration);
        $this->assertEquals(3, floor($cdr->duration / 60)); // 3 minutes
    }
}
