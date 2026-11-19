<?php

namespace Tests;

<<<<<<< HEAD
class ExampleTest extends TestCase
{
    public function test_health_endpoint_returns_a_successful_response(): void
    {
        $this->get('/health');

        $this->assertResponseStatus(200);
        $payload = json_decode($this->response->getContent(), true);
        $this->assertSame('success', $payload['status'] ?? null);
        $this->assertSame('prescription-service', $payload['data']['service'] ?? null);
    }
}
=======
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_that_base_endpoint_returns_a_successful_response()
    {
        $this->get('/');

        $this->assertEquals(
            $this->app->version(), $this->response->getContent()
        );
    }
}
>>>>>>> 3527913bcd19ffe3ec061734a13a1fe7a457a0b8
