<?php

namespace App\Models;

use App\Events\DonationCreated;
use App\Events\DonationUpdated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Donation
 * @package App\Models
 *
 * @property int $id
 * @property bool $is_hidden
 * @property string $source
 * @property int $external_id
 * @property string $from
 * @property int $amount
 * @property int $commission
 * @property string $text
 * @property string $admin_comment
 * @property string $status
 * @property array $additional_data
 * @property Carbon $paid_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Donation extends Model
{
    use HasFactory;

    protected $guarded = [];

    const SOURCE_DONATEPAY = 'donatepay';

    const STATUS_SUCCESS = 'success';
    const STATUS_CANCEL = 'cancel';
    const STATUS_WAIT = 'wait';
    const STATUS_TEST = 'test';

    public static function getAvailableSources(): array
    {
        return [
            self::SOURCE_DONATEPAY
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_SUCCESS,
            self::STATUS_CANCEL,
            self::STATUS_WAIT,
            self::STATUS_TEST
        ];
    }

    protected $casts = [
        'additional_data' => 'array'
    ];

    public static function boot()
    {
        parent::boot();

        self::created(function (Donation $donation) {
            if ($donation->status === Donation::STATUS_SUCCESS) {
                event(new DonationCreated($donation));
            }
        });

        self::updated(function (Donation $donation) {
            event(new DonationUpdated($donation));
        });
    }
}
