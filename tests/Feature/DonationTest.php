<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\UserTestCase;

class DonationTest extends UserTestCase
{
    use RefreshDatabase;

    /** @test */
    public function canRetrieveEmptyArray()
    {
        $data = [
            'sort-field' => 'paid_at'
        ];

        $this->getJson(route('donations.index', $data))
            ->assertStatus(200)
            ->assertExactJson([]);
    }

    /**
     * Tests todo:
     * 1. Can retrieve one (simple)
     * 2. Can retrieve sorted by amount
     * 3. Can retrieve sorted by paid_at asc
     * 4. Can retrieve where is_hidden=true
     * 5. Can retrieve with skip+limit
     */
}

