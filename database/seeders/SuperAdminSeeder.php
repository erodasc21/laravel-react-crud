<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'SuperAdmin'],
            ['guard_name' => 'web']
        );

        // Crear usuario Super Admin si no existe
        $admin = User::factory()->create([
            'name' => 'Syedgrodas',
            'email' => 'erodascarias@gmail.com',
        ]);

        // Asignar rol si no lo tiene
        if (! $admin->hasRole('SuperAdmin')) {
            $admin->assignRole('SuperAdmin');
        }
    }
}
