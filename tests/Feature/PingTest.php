<?php

namespace Tests\Feature;

use Tests\TestCase;

class PingTest extends TestCase
{
    /** @test */
    public function pingWorks()
    {
        $this->get(route('ping'))->assertSeeText('pong');
    }
}
