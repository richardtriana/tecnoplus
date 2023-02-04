<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

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
            'type_document' => $this->faker->text(1, 3),
            'document' => $this->faker->randomNumber(),
            'type_person' => $this->faker->text(2),
            'departament' => $this->faker->numberBetween(1, 32),
            'city' => $this->faker->numberBetween(1, 1105),
            'active' => $this->faker->numberBetween(0, 1),
            'tax' => $this->faker->numberBetween(1, 9),

        ];
    }
}
