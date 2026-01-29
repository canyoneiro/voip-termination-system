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
     * Test that the health endpoint is accessible.
     */
    public function test_health_endpoint_is_public(): void
    {
        $response = $this->get('/api/v1/health');

        $response->assertStatus(200);
    }
}
