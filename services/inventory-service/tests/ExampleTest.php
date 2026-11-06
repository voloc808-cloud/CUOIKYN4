<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function test_inventory_requires_jwt(): void
    {
        $this->get('/api/v1/batches');
        $this->assertResponseStatus(401);
        $this->seeJson(['code' => 'UNAUTHENTICATED']);
    }
}
