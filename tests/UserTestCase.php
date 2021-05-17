<?php

namespace Tests;

use App\Models\User;

class UserTestCase extends TestCase
{
    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->be($this->user);
    }
}
