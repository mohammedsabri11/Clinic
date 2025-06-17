<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Full access to all data'],
            ['name' => 'doctor', 'description' => 'Access to personal appointments'],
            ['name' => 'receptionist', 'description' => 'Manage appointments without sensitive patient data'],
            ['name' => 'patient', 'description' => 'View own appointments'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}