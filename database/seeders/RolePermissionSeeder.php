<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin' => 'Super Admin',
            'agency_manager' => 'Agency Manager',
            'project_manager' => 'Project Manager',
            'developer' => 'Developer',
            'designer' => 'Designer',
            'qa' => 'QA',
            'finance' => 'Finance',
            'client' => 'Client',
        ];

        foreach ($roles as $name => $label) {
            Role::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        // Sprint 0 only needs a minimal permission set to prove the mechanism works.
        // We'll add projects.*, tasks.*, invoices.* etc. as we build each module.
        $permissions = [
            'users.manage' => 'Manage Users',
            'roles.manage' => 'Manage Roles',
        ];

        foreach ($permissions as $name => $label) {
            Permission::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        // Agency Manager gets users.manage as an example wiring
        $agencyManager = Role::where('name', 'agency_manager')->first();
        $agencyManager->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', ['users.manage'])->pluck('id')
        );

        // Super Admin user for testing (bypasses permissions via Gate::before anyway)
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@studioflow.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $superAdmin->roles()->syncWithoutDetaching(
            Role::where('name', 'super_admin')->pluck('id')
        );
    }
}
