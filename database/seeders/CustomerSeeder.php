<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customerUser = User::where(
            'email',
            'customer@vending.test'
        )->first();

        Customer::create([
            'user_id' => $customerUser->id,
            'name' => 'Customer Vending',
            'email' => 'customer@vending.test',
            'phone' => '081234567890',
            'address' => 'Batam, Kepulauan Riau',
        ]);
    }
}