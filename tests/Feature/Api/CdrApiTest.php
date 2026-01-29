<?php

namespace Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\Cdr;
use App\Models\Customer;
use App\Models\Carrier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CdrApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;
    protected Customer $customer;
    protected Carrier $carrier;

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

        $this->customer = Customer::factory()->create();
        $this->carrier = Carrier::factory()->create();
    }

    protected function apiHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ];
    }

    public function test_list_cdrs(): void
    {
        Cdr::factory()->count(5)->create([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
        ]);

        $response = $this->getJson('/api/v1/cdrs', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'uuid', 'caller', 'callee', 'duration', 'sip_code'],
                ],
            ]);
    }

    public function test_filter_cdrs_by_customer(): void
    {
        $customer2 = Customer::factory()->create();

        Cdr::factory()->count(3)->create(['customer_id' => $this->customer->id]);
        Cdr::factory()->count(2)->create(['customer_id' => $customer2->id]);

        $response = $this->getJson("/api/v1/cdrs?customer_id={$this->customer->id}", $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_filter_cdrs_by_date_range(): void
    {
        Cdr::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
            'start_time' => now()->subDays(2),
        ]);

        Cdr::factory()->count(2)->create([
            'customer_id' => $this->customer->id,
            'start_time' => now()->subDays(10),
        ]);

        $from = now()->subDays(5)->format('Y-m-d');
        $to = now()->format('Y-m-d');

        $response = $this->getJson("/api/v1/cdrs?from={$from}&to={$to}", $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_filter_cdrs_by_caller(): void
    {
        Cdr::factory()->create([
            'customer_id' => $this->customer->id,
            'caller' => '+34612345678',
        ]);

        Cdr::factory()->create([
            'customer_id' => $this->customer->id,
            'caller' => '+34999888777',
        ]);

        $response = $this->getJson('/api/v1/cdrs?caller=612345', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_get_single_cdr(): void
    {
        $cdr = Cdr::factory()->answered()->create([
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'caller' => '+34612345678',
            'callee' => '+34987654321',
        ]);

        $response = $this->getJson("/api/v1/cdrs/{$cdr->uuid}", $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'caller' => '+34612345678',
                    'callee' => '+34987654321',
                ],
            ]);
    }

    public function test_get_nonexistent_cdr(): void
    {
        $response = $this->getJson('/api/v1/cdrs/nonexistent-uuid', $this->apiHeaders());

        $response->assertStatus(404);
    }

    public function test_filter_answered_only(): void
    {
        Cdr::factory()->answered()->count(3)->create(['customer_id' => $this->customer->id]);
        Cdr::factory()->failed()->count(2)->create(['customer_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/cdrs?sip_code=200', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_filter_by_minimum_duration(): void
    {
        Cdr::factory()->create([
            'customer_id' => $this->customer->id,
            'duration' => 30,
            'sip_code' => 200,
        ]);

        Cdr::factory()->create([
            'customer_id' => $this->customer->id,
            'duration' => 120,
            'sip_code' => 200,
        ]);

        Cdr::factory()->create([
            'customer_id' => $this->customer->id,
            'duration' => 300,
            'sip_code' => 200,
        ]);

        $response = $this->getJson('/api/v1/cdrs?min_duration=60', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }
}
