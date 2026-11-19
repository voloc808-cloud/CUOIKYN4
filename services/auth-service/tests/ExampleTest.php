<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function test_login_requires_valid_input(): void
    {
        $this->post('/api/v1/auth/login', []);

        $this->assertResponseStatus(422);
        $this->seeJson(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
    }

    public function test_user_management_requires_jwt(): void
    {
        $this->get('/api/v1/users');

        $this->assertResponseStatus(401);
        $this->seeJson(['status' => 'error']);
    }
}
