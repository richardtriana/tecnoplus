<?php

namespace Database\Factories;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tax::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'IVA' . $this->faker->numberBetween(0, 19),
            'percentage' =>  $this->faker->numberBetween(0, 19),
            'active' => $this->faker->boolean(),
        ];
    }
}
