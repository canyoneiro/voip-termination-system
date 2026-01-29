<?php

namespace Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\Carrier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CarrierApiTest extends TestCase
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

    public function test_list_carriers(): void
    {
        Carrier::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/carriers', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'uuid', 'name', 'host', 'state'],
                ],
            ]);
    }

    public function test_get_single_carrier(): void
    {
        $carrier = Carrier::factory()->create([
            'name' => 'Test Carrier',
            'host' => 'sip.test.com',
        ]);

        $response = $this->getJson("/api/v1/carriers/{$carrier->id}", $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test Carrier',
                    'host' => 'sip.test.com',
                ],
            ]);
    }

    public function test_create_carrier(): void
    {
        $response = $this->postJson('/api/v1/carriers', [
            'name' => 'New Carrier',
            'host' => 'sip.newcarrier.com',
            'port' => 5060,
            'transport' => 'udp',
            'codecs' => 'G729,PCMA',
            'priority' => 1,
            'weight' => 100,
            'max_cps' => 10,
            'max_channels' => 50,
        ], $this->apiHeaders());

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'New Carrier',
                ],
            ]);

        $this->assertDatabaseHas('carriers', [
            'name' => 'New Carrier',
            'host' => 'sip.newcarrier.com',
        ]);
    }

    public function test_update_carrier(): void
    {
        $carrier = Carrier::factory()->create([
            'name' => 'Original Carrier',
            'priority' => 5,
        ]);

        $response = $this->putJson("/api/v1/carriers/{$carrier->id}", [
            'name' => 'Updated Carrier',
            'priority' => 1,
        ], $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Carrier',
                    'priority' => 1,
                ],
            ]);
    }

    public function test_update_carrier_status(): void
    {
        $carrier = Carrier::factory()->create(['state' => 'active']);

        $response = $this->patchJson("/api/v1/carriers/{$carrier->id}/status", [
            'state' => 'disabled',
        ], $this->apiHeaders());

        $response->assertStatus(200);

        $carrier->refresh();
        $this->assertEquals('disabled', $carrier->state);
    }

    public function test_delete_carrier(): void
    {
        $carrier = Carrier::factory()->create();

        $response = $this->deleteJson("/api/v1/carriers/{$carrier->id}", [], $this->apiHeaders());

        $response->assertStatus(200);

        $this->assertDatabaseMissing('carriers', [
            'id' => $carrier->id,
        ]);
    }

    public function test_get_carrier_status(): void
    {
        $carrier = Carrier::factory()->create([
            'state' => 'active',
            'last_options_reply' => 200,
            'last_options_time' => now()->subMinutes(1),
        ]);

        $response = $this->getJson("/api/v1/carriers/{$carrier->id}/status", $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['state', 'last_options_reply', 'last_options_time'],
            ]);
    }
}
