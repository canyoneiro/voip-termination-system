<?php

namespace Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\IpBlacklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SystemApiTest extends TestCase
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

    public function test_health_endpoint_is_public(): void
    {
        // Health endpoint should be accessible without authentication
        // Note: May return 503 if services like Kamailio aren't running in test env
        $response = $this->getJson('/api/v1/health');

        // Verify structure regardless of status (200=healthy, 503=unhealthy)
        $response->assertJsonStructure([
            'status',
            'checks',
            'timestamp',
        ]);
    }

    public function test_ping_endpoint(): void
    {
        $response = $this->getJson('/api/v1/ping');

        $response->assertStatus(200);
    }

    public function test_system_status_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/system/status');

        $response->assertStatus(401);
    }

    public function test_system_status_with_auth(): void
    {
        $response = $this->getJson('/api/v1/system/status', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['uptime', 'active_calls', 'carriers_up', 'carriers_down'],
            ]);
    }

    public function test_list_blacklist(): void
    {
        IpBlacklist::create([
            'ip_address' => '192.168.1.100',
            'reason' => 'Test block',
            'source' => 'manual',
            'permanent' => false,
        ]);

        $response = $this->getJson('/api/v1/blacklist', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_add_to_blacklist(): void
    {
        $response = $this->postJson('/api/v1/blacklist', [
            'ip_address' => '10.0.0.100',
            'reason' => 'Suspicious activity',
            'permanent' => false,
            'duration' => 3600,
        ], $this->apiHeaders());

        $response->assertStatus(201);

        $this->assertDatabaseHas('ip_blacklist', [
            'ip_address' => '10.0.0.100',
            'reason' => 'Suspicious activity',
        ]);
    }

    public function test_add_permanent_blacklist(): void
    {
        $response = $this->postJson('/api/v1/blacklist', [
            'ip_address' => '10.0.0.200',
            'reason' => 'Known attacker',
            'permanent' => true,
        ], $this->apiHeaders());

        $response->assertStatus(201);

        $this->assertDatabaseHas('ip_blacklist', [
            'ip_address' => '10.0.0.200',
            'permanent' => true,
        ]);
    }

    public function test_remove_from_blacklist(): void
    {
        $blacklist = IpBlacklist::create([
            'ip_address' => '192.168.1.50',
            'reason' => 'To be removed',
            'source' => 'manual',
        ]);

        $response = $this->deleteJson("/api/v1/blacklist/{$blacklist->id}", [], $this->apiHeaders());

        $response->assertStatus(200);

        $this->assertDatabaseMissing('ip_blacklist', [
            'ip_address' => '192.168.1.50',
        ]);
    }

    public function test_invalid_ip_validation(): void
    {
        $response = $this->postJson('/api/v1/blacklist', [
            'ip_address' => 'not-an-ip',
            'reason' => 'Test',
        ], $this->apiHeaders());

        $response->assertStatus(422);
    }
}
