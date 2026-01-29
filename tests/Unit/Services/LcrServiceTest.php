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
            'country_code' => 'ES',
            'country_name' => 'Spain',
            'region' => 'Fixed',
            'active' => true,
        ]);

        DestinationPrefix::create([
            'prefix' => '346',
            'country_code' => 'ES',
            'country_name' => 'Spain',
            'region' => 'Mobile',
            'is_mobile' => true,
            'active' => true,
        ]);

        // Should match longest prefix
        $destination = $this->lcrService->findDestinationPrefix('34612345678');
        $this->assertNotNull($destination);
        $this->assertEquals('346', $destination->prefix);

        // Should match shorter prefix for fixed line
        $destination = $this->lcrService->findDestinationPrefix('34912345678');
        $this->assertNotNull($destination);
        $this->assertEquals('34', $destination->prefix);
    }

    public function test_select_carrier_by_cost(): void
    {
        $customer = Customer::factory()->create(['active' => true]);

        $carrier1 = Carrier::factory()->create([
            'name' => 'Expensive',
            'priority' => 1,
            'state' => 'active',
        ]);

        $carrier2 = Carrier::factory()->create([
            'name' => 'Cheap',
            'priority' => 1,
            'state' => 'active',
        ]);

        $prefix = DestinationPrefix::create([
            'prefix' => '34',
            'country_code' => 'ES',
            'country_name' => 'Spain',
            'active' => true,
        ]);

        CarrierRate::create([
            'carrier_id' => $carrier1->id,
            'destination_prefix_id' => $prefix->id,
            'cost_per_minute' => 0.02,
            'billing_increment' => 1,
            'effective_date' => now()->subDay(),
            'active' => true,
        ]);

        CarrierRate::create([
            'carrier_id' => $carrier2->id,
            'destination_prefix_id' => $prefix->id,
            'cost_per_minute' => 0.01,
            'billing_increment' => 1,
            'effective_date' => now()->subDay(),
            'active' => true,
        ]);

        $result = $this->lcrService->selectCarrier('34612345678', $customer);

        // Should select carrier with lowest cost
        $this->assertNotNull($result);
        $this->assertFalse($result['error'] ?? false);
        $this->assertEquals('Cheap', $result['carrier']->name);
    }

    public function test_select_carrier_excludes_inactive(): void
    {
        $customer = Customer::factory()->create(['active' => true]);

        $activeCarrier = Carrier::factory()->create([
            'name' => 'Active Carrier',
            'state' => 'active',
        ]);

        $inactiveCarrier = Carrier::factory()->create([
            'name' => 'Inactive Carrier',
            'state' => 'inactive',
        ]);

        $prefix = DestinationPrefix::create([
            'prefix' => '34',
            'country_code' => 'ES',
            'country_name' => 'Spain',
            'active' => true,
        ]);

        CarrierRate::create([
            'carrier_id' => $activeCarrier->id,
            'destination_prefix_id' => $prefix->id,
            'cost_per_minute' => 0.02,
            'billing_increment' => 1,
            'effective_date' => now()->subDay(),
            'active' => true,
        ]);

        CarrierRate::create([
            'carrier_id' => $inactiveCarrier->id,
            'destination_prefix_id' => $prefix->id,
            'cost_per_minute' => 0.01,
            'billing_increment' => 1,
            'effective_date' => now()->subDay(),
            'active' => true,
        ]);

        $result = $this->lcrService->selectCarrier('34612345678', $customer);

        $this->assertNotNull($result);
        $this->assertFalse($result['error'] ?? false);
        $this->assertEquals('Active Carrier', $result['carrier']->name);
    }

    public function test_returns_null_for_no_routes(): void
    {
        $customer = Customer::factory()->create(['active' => true]);
        $result = $this->lcrService->selectCarrier('99912345678', $customer);
        $this->assertNull($result);
    }

    public function test_lcr_lookup_returns_carrier_info(): void
    {
        $carrier = Carrier::factory()->create([
            'name' => 'Test Carrier',
            'state' => 'active',
        ]);

        $prefix = DestinationPrefix::create([
            'prefix' => '34',
            'country_code' => 'ES',
            'country_name' => 'Spain',
            'active' => true,
        ]);

        CarrierRate::create([
            'carrier_id' => $carrier->id,
            'destination_prefix_id' => $prefix->id,
            'cost_per_minute' => 0.015,
            'connection_fee' => 0.001,
            'billing_increment' => 6,
            'effective_date' => now()->subDay(),
            'active' => true,
        ]);

        $result = $this->lcrService->lcrLookup('34612345678');

        $this->assertEquals('34612345678', $result['number']);
        $this->assertNotNull($result['prefix']);
        $this->assertEquals('34', $result['prefix']['prefix']);
        $this->assertCount(1, $result['carriers']);
        $this->assertEquals('Test Carrier', $result['carriers'][0]['carrier_name']);
        $this->assertEquals(0.015, $result['carriers'][0]['cost_per_minute']);
    }
}
