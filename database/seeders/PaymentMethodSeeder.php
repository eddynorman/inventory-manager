<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::firstOrCreate(
            ['name' => 'Cash'],
            [
                'reference_number' => 'CASH-DEFAULT',
            ]
        );
    }
}
