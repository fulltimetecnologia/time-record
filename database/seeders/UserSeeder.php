<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::count() > 0) {
            return;
        }

        User::factory(1)->create([
            'name' => 'Administrador',
            'email' => 'admin@teste.com',
            'password' => bcrypt('admin')
        ]);
    }
}
