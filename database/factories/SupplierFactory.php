<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'address' => $this->faker->address(),
            'mobile' => $this->faker->phoneNumber(),
            'contact' => $this->faker->firstName(),
            'email' => $this->faker->unique()->safeEmail(),
            'type_person' => $this->faker->numberBetween(1, 9),
            'departament' => $this->faker->numberBetween(1, 32),
            'city' => $this->faker->numberBetween(1, 1105),
            'type_document' => $this->faker->numberBetween(1, 3),
            'document' => $this->faker->randomNumber(),
            'active' => $this->faker->numberBetween(0, 1),
            'tax' => $this->faker->numberBetween(1, 9),
        ];
    }
}
