<?php

namespace Tests\Unit\Services;

use App\Models\Carrier;
use App\Models\CarrierRate;
use App\Models\Customer;
use App\Models\DestinationPrefix;
use App\Services\LcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LcrServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LcrService $lcrService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lcrService = app(LcrService::class);
    }

    public function test_find_destination_by_prefix(): void
    {
        DestinationPrefix::create([
            'prefix' => '34',
            'country' => 'Spain',
            'description' => 'Spain Fixed',
        ]);

        DestinationPrefix::create([
            'prefix' => '346',
            'country' => 'Spain',
            'description' => 'Spain Mobile',
            'is_mobile' => true,
        ]);

        // Should match longest prefix
        $destination = $this->lcrService->findDestination('34612345678');
        $this->assertEquals('346', $destination->prefix);

        // Should match shorter prefix
        $destination = $this->lcrService->findDestination('34912345678');
        $this->assertEquals('34', $destination->prefix);
    }

    public function test_select_carrier_by_priority(): void
    {
        $carrier1 = Carrier::factory()->create([
            'name' => 'High Priority',
            'priority' => 1,
            'state' => 'active',
        ]);

        $carrier2 = Carrier::factory()->create([
            'name' => 'Low Priority',
            'priority' => 10,
            'state' => 'active',
        ]);

        DestinationPrefix::create(['prefix' => '34', 'country' => 'Spain']);

        CarrierRate::create([
            'carrier_id' => $carrier1->id,
            'prefix' => '34',
            'rate_per_minute' => 0.01,
            'billing_increment' => 1,
            'active' => true,
        ]);

        CarrierRate::create([
            'carrier_id' => $carrier2->id,
            'prefix' => '34',
            'rate_per_minute' => 0.005,
            'billing_increment' => 1,
            'active' => true,
        ]);

        $result = $this->lcrService->selectCarrier('34612345678');

        // Should select carrier with highest priority (lowest number)
        $this->assertNotNull($result);
        $this->assertEquals('High Priority', $result['carrier']->name);
    }

    public function test_select_carrier_excludes_inactive(): void
    {
        $activeCarrier = Carrier::factory()->create([
            'name' => 'Active Carrier',
            'state' => 'active',
        ]);

        $inactiveCarrier = Carrier::factory()->create([
            'name' => 'Inactive Carrier',
            'state' => 'inactive',
        ]);

        DestinationPrefix::create(['prefix' => '34', 'country' => 'Spain']);

        CarrierRate::create([
            'carrier_id' => $activeCarrier->id,
            'prefix' => '34',
            'rate_per_minute' => 0.02,
            'billing_increment' => 1,
            'active' => true,
        ]);

        CarrierRate::create([
            'carrier_id' => $inactiveCarrier->id,
            'prefix' => '34',
            'rate_per_minute' => 0.01,
            'billing_increment' => 1,
            'active' => true,
        ]);

        $result = $this->lcrService->selectCarrier('34612345678');

        $this->assertNotNull($result);
        $this->assertEquals('Active Carrier', $result['carrier']->name);
    }

    public function test_returns_null_for_no_routes(): void
    {
        $result = $this->lcrService->selectCarrier('99912345678');
        $this->assertNull($result);
    }

    public function test_carrier_prefix_filter(): void
    {
        $carrier = Carrier::factory()->create([
            'name' => 'Spain Only',
            'state' => 'active',
            'prefix_filter' => '34*,351*', // Only Spain and Portugal
        ]);

        DestinationPrefix::create(['prefix' => '34', 'country' => 'Spain']);
        DestinationPrefix::create(['prefix' => '33', 'country' => 'France']);

        CarrierRate::create([
            'carrier_id' => $carrier->id,
            'prefix' => '34',
            'rate_per_minute' => 0.01,
            'billing_increment' => 1,
            'active' => true,
        ]);

        // Spain should match
        $result = $this->lcrService->selectCarrier('34612345678');
        $this->assertNotNull($result);

        // France should not match (no rate configured and prefix_filter)
        $result = $this->lcrService->selectCarrier('33612345678');
        $this->assertNull($result);
    }
}
