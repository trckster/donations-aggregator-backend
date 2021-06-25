<?php

namespace App\Services\API;

use App\Models\Donation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DonatePay
{
    /**
     * @var string
     */
    private $apiKey;

    private string $url = 'https://donatepay.ru/api/v1/transactions';

    public function __construct()
    {
        $this->apiKey = config('api.donatepay-key');
    }

    public function loadDonations()
    {
        $data = Http::get($this->url, [
            'access_token' => $this->apiKey,
            'type' => 'donation',
            'status' => 'success',
            'limit' => 100
        ])->json();

        $status = Arr::get($data, 'status');

        if ($status === 'success') {
            return Arr::get($data, 'data');
        }

        Log::error('Bad answer from donatepay: ' . json_encode($data));

        return [];
    }
}
