<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function test_catalog_requires_jwt(): void
    {
        $this->get('/api/v1/medicines');

        $this->assertResponseStatus(401);
        $this->seeJson(['status' => 'error', 'code' => 'UNAUTHENTICATED']);
    }
}
