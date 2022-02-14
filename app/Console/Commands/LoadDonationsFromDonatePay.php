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

    private array $mappedStatuses = [
        'success' => Donation::STATUS_SUCCESS,
        'cancel' => Donation::STATUS_CANCEL,
        'wait' => Donation::STATUS_WAIT,
        'user' => Donation::STATUS_TEST
    ];

    private DonatePay $api;

    public function __construct(DonatePay $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    public function handle()
    {
        Log::info('[DonatePay] Command started');

        $this->load();
    }

    public function load()
    {
        $donations = $this->api->loadDonations();
        $newDonations = 0;

        foreach ($donations as $donation) {
            /**
             * Project has small load, so we can afford 100 queries every ~20 sec
             */
            if ($this->addDonation($donation)) {
                $newDonations += 1;
            }
        }

        Log::info("[DonatePay] New donations count: $newDonations");
    }

    public function addDonation(array $donation): bool
    {
        $alreadyExists = Donation::query()->where('external_id', $donation['id'])->exists();

        if ($alreadyExists) {
            return false;
        }

        Donation::query()->create([
            'source' => Donation::SOURCE_DONATEPAY,
            'external_id' => $donation['id'],
            'from' => $donation['what'],
            'amount' => (int)((double)$donation['sum'] * 100),
            'commission' => (int)((double)$donation['commission'] * 100),
            'status' => $this->mappedStatuses[$donation['status']],
            'text' => $donation['comment'],
            'paid_at' => Carbon::parse($donation['created_at']),
            'additional_data' => [
                'to_cash' => $donation['to_cash'],
                'to_pay' => $donation['to_pay'],
                'vars' => $donation['vars']
            ]
        ]);

        return true;
    }
}
