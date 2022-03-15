<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class UserTest extends TestCase
{
    /** @test */
    public function user_can_login()
    {
        $this->signIn();

        $this->response
            ->assertStatus(200)
            ->assertHeader('Authorization');
    }
}
