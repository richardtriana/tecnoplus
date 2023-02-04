<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'barcode' =>  $this->faker->unique()->ean13(),
            'product' =>  $this->faker->name(),
            'type' =>  $this->faker->numberBetween(1,3),
            'cost_price_tax_exc' =>  $this->faker->randomFloat(2,1, 10000),
            'cost_price_tax_inc' =>  $this->faker->randomFloat(2,1, 10000),
            'gain' =>  $this->faker->randomFloat(2,1, 10000),
            'sale_price_tax_exc' =>  $this->faker->randomFloat(2,1, 10000),
            'sale_price_tax_inc' =>  $this->faker->randomFloat(2,1, 10000),
            'wholesale_price_tax_exc' =>  $this->faker->randomFloat(2,1, 10000),
            'wholesale_price_tax_inc' =>  $this->faker->randomFloat(2,1, 10000),
            'stock' =>  $this->faker->numberBetween(0,1),
            'quantity' =>  $this->faker->numberBetween(0,100),
            'minimum' =>  $this->faker->numberBetween(0,100),
            'maximum' =>  $this->faker->numberBetween(0,100),
            'state' => '1',
            'category_id' =>  $this->faker->numberBetween(1,10),
            'tax_id' =>  $this->faker->numberBetween(1,3),
            'brand_id' =>  $this->faker->numberBetween(1,3),
           
        ];
    }
}
