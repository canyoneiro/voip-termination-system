<?php

namespace Tests\Unit\Models;

use App\Models\Carrier;
use App\Models\CarrierIp;
use App\Models\CarrierRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarrierTest extends TestCase
{
    use RefreshDatabase;

    public function test_carrier_can_be_created(): void
    {
        $carrier = Carrier::create([
            'name' => 'Test Carrier',
            'host' => 'sip.testcarrier.com',
            'port' => 5060,
            'transport' => 'udp',
            'codecs' => 'G729,PCMA',
            'priority' => 1,
            'weight' => 100,
            'max_cps' => 10,
            'max_channels' => 50,
        ]);

        $this->assertDatabaseHas('carriers', [
            'name' => 'Test Carrier',
            'host' => 'sip.testcarrier.com',
        ]);
        $this->assertNotNull($carrier->uuid);
    }

    public function test_carrier_states(): void
    {
        $activeCarrier = Carrier::factory()->create(['state' => 'active']);
        $inactiveCarrier = Carrier::factory()->create(['state' => 'inactive']);
        $probingCarrier = Carrier::factory()->create(['state' => 'probing']);
        $disabledCarrier = Carrier::factory()->create(['state' => 'disabled']);

        $this->assertEquals('active', $activeCarrier->state);
        $this->assertEquals('inactive', $inactiveCarrier->state);
        $this->assertEquals('probing', $probingCarrier->state);
        $this->assertEquals('disabled', $disabledCarrier->state);
    }

    public function test_carrier_has_ips_relationship(): void
    {
        $carrier = Carrier::factory()->create();

        CarrierIp::create([
            'carrier_id' => $carrier->id,
            'ip_address' => '10.0.0.1',
            'active' => true,
        ]);

        $this->assertCount(1, $carrier->ips);
    }

    public function test_carrier_codecs_array_accessor(): void
    {
        $carrier = Carrier::factory()->create([
            'codecs' => 'G729,PCMA,PCMU,GSM',
        ]);

        $this->assertIsString($carrier->codecs);
        $this->assertStringContainsString('G729', $carrier->codecs);
    }

    public function test_carrier_scope_active(): void
    {
        Carrier::factory()->count(3)->create(['state' => 'active']);
        Carrier::factory()->count(2)->create(['state' => 'inactive']);

        $activeCarriers = Carrier::where('state', 'active')->get();
        $this->assertCount(3, $activeCarriers);
    }

    public function test_carrier_transport_enum(): void
    {
        $udpCarrier = Carrier::factory()->create(['transport' => 'udp']);
        $tcpCarrier = Carrier::factory()->create(['transport' => 'tcp']);
        $tlsCarrier = Carrier::factory()->create(['transport' => 'tls']);

        $this->assertEquals('udp', $udpCarrier->transport);
        $this->assertEquals('tcp', $tcpCarrier->transport);
        $this->assertEquals('tls', $tlsCarrier->transport);
    }
}
