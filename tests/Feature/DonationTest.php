<?php

namespace Tests\Feature;

use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\UserTestCase;
use function Couchbase\fastlzCompress;

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

    /** @test */
    public function canRetrieveDonation()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'is_hidden' => false,
        ])->refresh();

        $data = [
            'sort-field' => 'paid_at'
        ];

        $this->getJson(route('donations.index', $data))
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'id' => $donation->id,
                    'is_hidden' => $donation->is_hidden,
                    'source' => $donation->source,
                    'external_id' => $donation->external_id,
                    'from' => $donation->from,
                    'amount' => $donation->amount,
                    'commission' => $donation->commission,
                    'text' => $donation->text,
                    'admin_comment' => $donation->admin_comment,
                    'status' => $donation->status,
                    'additional_data' => $donation->additional_data,
                    'paid_at' => $donation->paid_at,
                    'created_at' => $donation->created_at,
                    'updated_at' => $donation->updated_at
                ]
            ]);
    }

    /** @test */
    public function getDonationsSortedByAmount()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create(['is_hidden' => false, 'amount' => 200]);
        /** @var Donation $donation2 */
        $donation2 = Donation::factory()->create(['is_hidden' => false, 'amount' => 300]);
        /** @var Donation $donation3 */
        $donation3 = Donation::factory()->create(['is_hidden' => false, 'amount' => 100]);

        $data = [
            'sort-field' => 'amount',
            'sort-order' => 'asc'
        ];

        $this->getJson(route('donations.index', $data))
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => $donation3->id,
                    'amount' => $donation3->amount
                ], [
                    'id' => $donation->id,
                    'amount' => $donation->amount
                ], [
                    'id' => $donation2->id,
                    'amount' => $donation2->amount
                ]
            ]);
    }

    /** @test */
    public function getDonationsSortedByPaidAt()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()
            ->create(['is_hidden' => false, 'paid_at' => Carbon::now()->addMinute()])
            ->refresh();
        /** @var Donation $donation2 */
        $donation2 = Donation::factory()
            ->create(['is_hidden' => false, 'paid_at' => Carbon::now()->subMinute()])
            ->refresh();
        /** @var Donation $donation3 */
        $donation3 = Donation::factory()
            ->create(['is_hidden' => false, 'paid_at' => Carbon::now()])
            ->refresh();

        $data = [
            'sort-field' => 'paid_at',
            'sort-order' => 'asc'
        ];

        $this->getJson(route('donations.index', $data))
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => $donation2->id,
                    'paid_at' => $donation2->paid_at
                ], [
                    'id' => $donation3->id,
                    'paid_at' => $donation3->paid_at
                ], [
                    'id' => $donation->id,
                    'paid_at' => $donation->paid_at
                ]
            ]);
    }

    /** @test */
    public function getHiddenDonations()
    {
        Donation::factory()->create(['is_hidden' => false]);
        /** @var Donation $donation */
        $donation = Donation::factory()->create(['is_hidden' => true, 'amount' => 500]);
        Donation::factory()->create(['is_hidden' => false]);
        /** @var Donation $donation2 */
        $donation2 = Donation::factory()->create(['is_hidden' => true, 'amount' => 700]);

        $data = [
            'sort-field' => 'amount',
            'sort-order' => 'desc',
            'is-hidden' => true
        ];

        $this->getJson(route('donations.index', $data))
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => $donation2->id,
                    'amount' => $donation2->amount
                ], [
                    'id' => $donation->id,
                    'amount' => $donation->amount
                ]
            ]);
    }

    /** @test */
    public function getDonationsWithLimitAndSkippingSome()
    {
        $donations = Donation::factory(20)
            ->create(['is_hidden' => false])
            ->sortByDesc('amount')
            ->values();

        $data = [
            'sort-field' => 'amount',
            'limit' => 3,
            'skip' => 11
        ];

        $this->getJson(route('donations.index', $data))
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => $donations[11]->id,
                    'amount' => $donations[11]->amount
                ], [
                    'id' => $donations[12]->id,
                    'amount' => $donations[12]->amount
                ], [
                    'id' => $donations[13]->id,
                    'amount' => $donations[13]->amount
                ]
            ]);
    }

    /** @test */
    public function hideDonation()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create(['is_hidden' => false]);

        $data = [
            'donation' => $donation,
            'is-hidden' => true
        ];

        $this->putJson(route('donations.update', $data))
            ->assertStatus(200)
            ->assertJson([
                'id' => $donation->id,
                'is_hidden' => true
            ]);

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'is_hidden' => true
        ]);
    }

    /** @test */
    public function exposeDonation()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create(['is_hidden' => true]);

        $data = [
            'donation' => $donation,
            'is-hidden' => false
        ];

        $this->putJson(route('donations.update', $data))
            ->assertStatus(200)
            ->assertJson([
                'id' => $donation->id,
                'is_hidden' => false
            ]);

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'is_hidden' => false
        ]);
    }

    /**
     * @test
     */
    public function canAddAdminMessageToDonation()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create(['is_hidden' => true]);

        $data = [
            'donation' => $donation,
            'admin-message' => 'Hey there! Say something about political prisoners!'
        ];

        $this->putJson(route('donations.update', $data))
            ->assertStatus(200)
            ->assertJson([
                'id' => $donation->id,
                'admin_comment' => $data['admin-message']
            ]);

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'admin_comment' => $data['admin-message']
        ]);
    }

    /**
     * @test
     */
    public function canDeleteAdminMessageToDonation()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create(['is_hidden' => true]);

        $data = [
            'donation' => $donation,
            'admin-message' => ''
        ];

        $this->putJson(route('donations.update', $data))
            ->assertJson([
                'id' => $donation->id,
                'admin_comment' => ''
            ]);

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'admin_comment' => ''
        ]);
    }
}

