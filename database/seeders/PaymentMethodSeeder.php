<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        PaymentMethod::create(['code' => '10', 'name' => 'Efectivo']);
        PaymentMethod::create(['code' => '42', 'name' => 'Consignación']);
        PaymentMethod::create(['code' => '20', 'name' => 'Cheque']);
        PaymentMethod::create(['code' => '47', 'name' => 'Transferencia']);
        PaymentMethod::create(['code' => '71', 'name' => 'Bonos']);
        PaymentMethod::create(['code' => '72', 'name' => 'Vales']);
        PaymentMethod::create(['code' => '1', 'name' => 'Medio de pago no definido']);
        PaymentMethod::create(['code' => '49', 'name' => 'Tarjeta Débito']);
        PaymentMethod::create(['code' => '48', 'name' => 'Tarjeta Crédito']);
        PaymentMethod::create(['code' => 'ZZZ', 'name' => 'Otro*']);
    }
}
