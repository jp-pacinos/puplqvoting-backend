<?php

namespace Database\Seeders;

use App\Models\UserAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserAdmin::create([
           'name' => 'Admin',
           'email' => 'admin@admin.com',
           'password' => Hash::make('1234567890')
        ]);
    }
}
