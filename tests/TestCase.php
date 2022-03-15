<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\V1\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;

    protected $token, $response;
    protected $header = 'Authorization';

    protected function signIn()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt($password = 'pass')
        ]);

        $this->response = $this->json('POST', '/api/v1/auth/login', [
            'email' => $user->email,
            'password' => $password
        ]);

        $this->token = $this->response->headers->get($this->header);
    }
}
