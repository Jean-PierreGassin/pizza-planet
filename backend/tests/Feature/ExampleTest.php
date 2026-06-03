<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testRootRouteReturnsBackendStatus(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertJson([
                'name' => 'Pizza Planet',
                'status' => 'ok',
            ]);
    }

    public function testApiHealthRouteReturnsBackendStatus(): void
    {
        $this->get('/api/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
            ]);
    }
}
