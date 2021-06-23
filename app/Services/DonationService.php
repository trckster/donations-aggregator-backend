<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Arr;

class DonationService
{
    public function getDonations(array $data): iterable
    {
        $isHidden = Arr::get($data, 'is-hidden', false);
        $sortField = Arr::get($data, 'sort-field');
        $sortOrder = Arr::get($data, 'sort-order', 'desc');
        $skip = Arr::get($data, 'skip', 0);
        $limit = Arr::get($data, 'limit', 25);

        return Donation::query()
            ->where('is_hidden', $isHidden)
            ->where('status', Donation::STATUS_SUCCESS)
            ->orderBy($sortField, $sortOrder)
            ->skip($skip)
            ->limit($limit)
            ->get();
    }
}
