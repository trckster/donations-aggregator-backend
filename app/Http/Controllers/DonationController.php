<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\DonationService;

class DonationController extends Controller
{
    /** @var DonationService */
    private $service;

    public function __construct(DonationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = request()->validate([
            'is-hidden' => 'bool',
            'sort-field' => 'required|in:amount,paid_at',
            'sort-order' => 'in:asc,desc',
            'skip' => 'int|min:0',
            'limit' => 'int|min:1'
        ]);

        return $this->service->getDonations($data);
    }

    public function update(Donation $donation)
    {
        $data = request()->validate([
            'is-hidden' => 'bool',
            'admin-message' => 'string'
        ]);

        if (isset($data['is-hidden'])) {
            $donation->is_hidden = $data['is-hidden'];
        }

        if (isset($data['admin-message'])) {
            $donation->admin_comment = $data['admin-message'];
        }

        $donation->save();

        return $donation;
    }
}
