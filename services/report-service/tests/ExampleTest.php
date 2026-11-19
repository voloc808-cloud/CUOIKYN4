<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function test_health_endpoint_returns_a_successful_response(): void
    {
        $this->get('/health');

        $this->assertResponseStatus(200);
        $payload = json_decode($this->response->getContent(), true);
        $this->assertSame('success', $payload['status'] ?? null);
        $this->assertSame('report-service', $payload['data']['service'] ?? null);
    }
}