<?php

namespace Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\ActiveCall;
use App\Models\Cdr;
use App\Models\Customer;
use App\Models\Carrier;
use App\Models\DailyStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StatsApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = Str::random(64);

        ApiToken::create([
            'uuid' => Str::uuid(),
            'name' => 'Test Token',
            'token_hash' => hash('sha256', $this->token),
            'type' => 'admin',
            'permissions' => ['*'],
            'active' => true,
        ]);
    }

    protected function apiHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ];
    }

    public function test_realtime_stats(): void
    {
        $customer = Customer::factory()->create();
        $carrier = Carrier::factory()->create(['state' => 'active']);

        ActiveCall::create([
            'call_id' => 'test-call-1',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'caller' => '+34612345678',
            'callee' => '+34987654321',
            'source_ip' => '192.168.1.100',
            'start_time' => now(),
        ]);

        $response = $this->getJson('/api/v1/stats/realtime', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['active_calls', 'carriers_up', 'carriers_down'],
            ]);

        $this->assertEquals(1, $response->json('data.active_calls'));
    }

    public function test_summary_stats(): void
    {
        $customer = Customer::factory()->create();

        Cdr::factory()->answered()->count(10)->create([
            'customer_id' => $customer->id,
            'start_time' => now()->subHours(2),
        ]);

        Cdr::factory()->failed()->count(3)->create([
            'customer_id' => $customer->id,
            'start_time' => now()->subHours(2),
        ]);

        $response = $this->getJson('/api/v1/stats/summary', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['total_calls', 'answered_calls', 'failed_calls'],
            ]);
    }

    public function test_stats_by_customer(): void
    {
        $customer1 = Customer::factory()->create(['name' => 'Customer A']);
        $customer2 = Customer::factory()->create(['name' => 'Customer B']);

        Cdr::factory()->answered()->count(5)->create(['customer_id' => $customer1->id]);
        Cdr::factory()->answered()->count(3)->create(['customer_id' => $customer2->id]);

        $response = $this->getJson('/api/v1/stats/by-customer', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_stats_by_carrier(): void
    {
        $carrier1 = Carrier::factory()->create(['name' => 'Carrier A']);
        $carrier2 = Carrier::factory()->create(['name' => 'Carrier B']);

        Cdr::factory()->answered()->count(10)->create(['carrier_id' => $carrier1->id]);
        Cdr::factory()->answered()->count(5)->create(['carrier_id' => $carrier2->id]);

        $response = $this->getJson('/api/v1/stats/by-carrier', $this->apiHeaders());

        $response->assertStatus(200);
    }

    public function test_daily_stats(): void
    {
        $customer = Customer::factory()->create();

        DailyStat::create([
            'date' => today(),
            'customer_id' => $customer->id,
            'total_calls' => 100,
            'answered_calls' => 80,
            'failed_calls' => 20,
            'total_duration' => 5000,
            'asr' => 80.00,
            'acd' => 62.50,
        ]);

        $response = $this->getJson('/api/v1/stats/daily', $this->apiHeaders());

        $response->assertStatus(200);
    }

    public function test_top_destinations(): void
    {
        $customer = Customer::factory()->create();

        // Create CDRs to different destinations
        Cdr::factory()->answered()->count(5)->create([
            'customer_id' => $customer->id,
            'callee' => '+34612345678',
        ]);

        Cdr::factory()->answered()->count(3)->create([
            'customer_id' => $customer->id,
            'callee' => '+33612345678',
        ]);

        $response = $this->getJson('/api/v1/stats/top-destinations', $this->apiHeaders());

        $response->assertStatus(200);
    }
}
