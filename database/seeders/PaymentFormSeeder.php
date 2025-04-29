<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentForm;

class PaymentFormSeeder extends Seeder
{
    public function run()
    {
        PaymentForm::create(['code' => '1', 'name' => 'Pago de contado']);
        PaymentForm::create(['code' => '2', 'name' => 'Pago a crédito']);
    }
}
