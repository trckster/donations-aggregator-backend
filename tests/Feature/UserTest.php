<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function cantGetUserInformationWhenUnauthenticated()
    {
        $this->getJson(route('me'))->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    /** @test */
    public function canGetUserInformation()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('me'))
            ->assertStatus(200)
            ->assertExactJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);
    }
}
