<?php

namespace Database\Factories;

use App\Models\DetailOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DetailOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_id' => $this->faker->numberBetween(1, 10),
            'product_id' => $this->faker->numberBetween(1, 10),
            'barcode' => $this->faker->ean13(),
            'discount_percentage' => $this->faker->randomFloat(2, 2000, 3000),
            'discount_price' => $this->faker->randomFloat(2, 2000, 3000),
            'price_tax_exc' => $this->faker->randomFloat(2, 2000, 3000),
            'price_tax_inc' => $this->faker->randomFloat(2, 2000, 3000),
            'price_tax_inc_total' => $this->faker->randomFloat(2, 2000, 3000),
            'cost_price_tax_inc' => $this->faker->randomFloat(2, 2000, 3000),
            'cost_price_tax_inc_total' => $this->faker->randomFloat(2, 2000, 3000),
            'quantity' => $this->faker->randomFloat(2, 2000, 3000),
            'product' => $this->faker->name(),
        ];
    }
}
