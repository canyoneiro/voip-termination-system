<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that the application redirects unauthenticated users to login.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Root route redirects to login for unauthenticated users
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test that the health endpoint is accessible without auth.
     * Note: May return 503 if services like Kamailio aren't running in test env.
     */
    public function test_health_endpoint_is_public(): void
    {
        $response = $this->get('/api/v1/health');

        // Endpoint should be accessible (200 or 503 depending on service availability)
        $response->assertJsonStructure(['status', 'checks', 'timestamp']);
    }
}
