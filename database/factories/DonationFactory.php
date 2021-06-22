<?php

namespace Database\Factories;

use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class DonationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Donation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $amount = rand(1, 30000) * 100;

        return [
            'is_hidden' => $this->faker->boolean(),
            'source' => Arr::random(Donation::getAvailableSources()),
            'external_id' => rand(0, 1000000),
            'from' => $this->faker->name(),
            'amount' => $amount,
            'commission' => (int) $amount * 0.15,
            'text' => $this->faker->text(),
            'admin_comment' => rand(0, 5) ? '' : $this->faker->text(),
            'status' => Arr::random(Donation::getAvailableStatuses()),
            'additional_data' => [$this->faker->word() => $this->faker->word()],
            'paid_at' => Carbon::now()->subMinutes(rand(1, 1000))->subSeconds(rand(1, 1000))
        ];
    }

    public function brandNew()
    {
        return $this->state(function () {
            return [
                'paid_at' => now()->subSeconds(5),
                'admin_comment' => '',
                'is_hidden' => false
            ];
        });
    }
}
