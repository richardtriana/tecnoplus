<?php

namespace Database\Factories;

use App\Models\Configuration;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigurationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Configuration::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Tecnoplus',
            'legal_representative' => 'Richard Arturo Peña',
            'nit' => '123459789-0',
            'address' => 'Añadir direccion',
            'email' => 'empresa@nombredominio.com',
            'tax_regime' => 'No tributa',
            'logo' => 'images/logo.jpeg'
        ];
    }
}
