<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Services\API\DonatePay;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoadDonationsFromDonatePay extends Command
{
    protected $signature = 'donations:load-from-donate-pay';

    private $mappedStatuses = [
        'success' => Donation::STATUS_SUCCESS,
        'cancel' => Donation::STATUS_CANCEL,
        'wait' => Donation::STATUS_WAIT,
        'user' => Donation::STATUS_TEST
    ];

    /**
     * @var DonatePay
     */
    private $api;

    public function __construct(DonatePay $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    public function handle()
    {
        $this->load();
    }

    public function load()
    {
        $donations = $this->api->loadDonations();
        $new = 0;

        foreach ($donations as $donation) {
            if ($donation['type'] !== 'donation') {
                continue;
            }

            $known = Donation::query()->where('external_id', $donation['id'])->exists();

            if ($known) {
                continue;
            }

            $this->addDonation($donation);
            $new++;
        }

        Log::info("Donatepay, new donations count: $new");
    }

    public function addDonation(array $donation)
    {
        Donation::query()->create([
            'source' => Donation::SOURCE_DONATEPAY,
            'external_id' => $donation['id'],
            'from' => $donation['what'],
            'amount' => (int)((double)$donation['sum'] * 100),
            'commission' => (int)((double)$donation['commission'] * 100),
            'status' => $this->mappedStatuses[$donation['status']],
            'comment' => $donation['comment'],
            'paid_at' => (new Carbon($donation['created_at']['date'], $donation['created_at']['timezone']))
                ->setTimezone('UTC'),
            'additional_data' => [
                'to_cash' => $donation['to_cash'],
                'to_pay' => $donation['to_pay'],
                'vars' => $donation['vars']
            ]
        ]);
    }
}
