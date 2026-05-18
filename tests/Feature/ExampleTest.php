<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_responds(): void
    {
        $response = $this->get('/');
        $this->assertContains($response->getStatusCode(), [200, 301, 302]);
    }
}
