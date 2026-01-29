<?php

namespace Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\Customer;
use App\Models\CustomerIp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;
    protected string $tokenHash;

    protected function setUp(): void
    {
        parent::setUp();

        // Create API token
        $this->token = Str::random(64);
        $this->tokenHash = hash('sha256', $this->token);

        ApiToken::create([
            'uuid' => Str::uuid(),
            'name' => 'Test Token',
            'token_hash' => $this->tokenHash,
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

    public function test_list_customers(): void
    {
        Customer::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/customers', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'uuid', 'name', 'active'],
                ],
            ]);
    }

    public function test_list_customers_with_search(): void
    {
        Customer::factory()->create(['name' => 'Alpha Company']);
        Customer::factory()->create(['name' => 'Beta Corp']);
        Customer::factory()->create(['name' => 'Gamma Inc']);

        $response = $this->getJson('/api/v1/customers?search=Alpha', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Alpha Company', $response->json('data.0.name'));
    }

    public function test_list_customers_filter_active(): void
    {
        Customer::factory()->count(3)->create(['active' => true]);
        Customer::factory()->count(2)->create(['active' => false]);

        $response = $this->getJson('/api/v1/customers?active=1', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_single_customer(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        $response = $this->getJson("/api/v1/customers/{$customer->id}", $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test Customer',
                    'email' => 'test@example.com',
                ],
            ]);
    }

    public function test_get_nonexistent_customer(): void
    {
        $response = $this->getJson('/api/v1/customers/99999', $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_create_customer(): void
    {
        $response = $this->postJson('/api/v1/customers', [
            'name' => 'New Customer',
            'company' => 'New Company',
            'email' => 'new@example.com',
            'max_channels' => 20,
            'max_cps' => 10,
        ], $this->apiHeaders());

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'New Customer',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'New Customer',
            'email' => 'new@example.com',
        ]);
    }

    public function test_create_customer_validation_error(): void
    {
        $response = $this->postJson('/api/v1/customers', [
            // Missing required 'name' field
            'company' => 'Test Company',
        ], $this->apiHeaders());

        $response->assertStatus(422);
    }

    public function test_update_customer(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Original Name',
        ]);

        $response = $this->putJson("/api/v1/customers/{$customer->id}", [
            'name' => 'Updated Name',
            'max_channels' => 50,
        ], $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Name',
                    'max_channels' => 50,
                ],
            ]);
    }

    public function test_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/v1/customers/{$customer->id}", [], $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'deleted' => true,
                ],
            ]);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_get_customer_ips(): void
    {
        $customer = Customer::factory()->create();

        CustomerIp::create([
            'customer_id' => $customer->id,
            'ip_address' => '192.168.1.100',
            'description' => 'Main office',
            'active' => true,
        ]);

        $response = $this->getJson("/api/v1/customers/{$customer->id}/ips", $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_add_customer_ip(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->postJson("/api/v1/customers/{$customer->id}/ips", [
            'ip_address' => '10.0.0.50',
            'description' => 'Branch office',
        ], $this->apiHeaders());

        $response->assertStatus(201);

        $this->assertDatabaseHas('customer_ips', [
            'customer_id' => $customer->id,
            'ip_address' => '10.0.0.50',
        ]);
    }

    public function test_add_duplicate_ip_fails(): void
    {
        $customer = Customer::factory()->create();

        CustomerIp::create([
            'customer_id' => $customer->id,
            'ip_address' => '192.168.1.100',
            'active' => true,
        ]);

        $response = $this->postJson("/api/v1/customers/{$customer->id}/ips", [
            'ip_address' => '192.168.1.100',
        ], $this->apiHeaders());

        $response->assertStatus(409);
    }

    public function test_get_customer_usage(): void
    {
        $customer = Customer::factory()->create([
            'max_channels' => 10,
            'max_cps' => 5,
            'max_daily_minutes' => 1000,
            'used_daily_minutes' => 500,
            'max_monthly_minutes' => 10000,
            'used_monthly_minutes' => 3000,
        ]);

        $response = $this->getJson("/api/v1/customers/{$customer->id}/usage", $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'max_channels' => 10,
                    'used_daily_minutes' => 500,
                    'daily_minutes_pct' => 50.0,
                ],
            ]);
    }

    public function test_reset_customer_minutes(): void
    {
        $customer = Customer::factory()->create([
            'used_daily_minutes' => 500,
            'used_monthly_minutes' => 3000,
        ]);

        $response = $this->postJson("/api/v1/customers/{$customer->id}/reset-minutes", [
            'daily' => true,
            'monthly' => false,
        ], $this->apiHeaders());

        $response->assertStatus(200);

        $customer->refresh();
        $this->assertEquals(0, $customer->used_daily_minutes);
        $this->assertEquals(3000, $customer->used_monthly_minutes);
    }

    public function test_unauthorized_without_token(): void
    {
        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(401);
    }
}
