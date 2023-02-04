<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'client_id' => '1',
            'user_id' => $this->faker->numberBetween(1,2),
            'no_invoice' => $this->faker->randomLetter(),
            'total_paid' => $this->faker->randomFloat(2,1, 10000),
            'total_iva_inc' => $this->faker->randomFloat(2,1, 10000),
            'total_iva_exc' => $this->faker->randomFloat(2,1, 10000),
            'total_discount' => $this->faker->randomFloat(2,1, 10000),
            'total_cost_price_tax_inc' => $this->faker->randomFloat(2,1, 10000),
            'payment_date' => $this->faker->dateTime('Y-m-d'),
            'state' =>'1',
        ];
    }
}
