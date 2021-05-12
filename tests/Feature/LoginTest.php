<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function canLogin()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $data = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $this->postJson(route('login'), $data)
            ->assertStatus(204)
            ->assertCookie('laravel_session');
    }

    /** @test */
    public function cantLoginWithInvalidPassword()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $data = [
            'email' => $user->email,
            'password' => 'wrong-password'
        ];

        $this->postJson(route('login'), $data)
            ->assertStatus(422);
    }

    /** @test */
    public function cantLoginWithInvalidEmail()
    {
        User::factory()->create();

        $data = [
            'email' => 'wrong@mail.com',
            'password' => 'password'
        ];

        $this->postJson(route('login'), $data)
            ->assertStatus(422);
    }
}
