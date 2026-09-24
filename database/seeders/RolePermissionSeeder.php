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
            // Projects module
            'projects.view_any' => 'View All Projects',       // agency-wide visibility
            'projects.create' => 'Create Projects',
            'projects.update' => 'Update Projects',
            'projects.delete' => 'Delete Projects',
            'projects.assign_staff' => 'Assign Staff to Projects',
            // Milestones
            'milestones.create' => 'Create Milestones',
            'milestones.update' => 'Update Milestones',
            'milestones.delete' => 'Delete Milestones',
            // Tasks
            'tasks.create' => 'Create Tasks',
            'tasks.update' => 'Update Tasks (full edit)',
            'tasks.update_status' => 'Update Own Task Status',
            'tasks.delete' => 'Delete Tasks',
            // Time logs
            'time_logs.create' => 'Log Own Time',
            'time_logs.view_any' => 'View All Time Logs on a Project',
        ];

        foreach ($permissions as $name => $label) {
            Permission::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        // Agency Manager gets users.manage as an example wiring
        // $agencyManager = Role::where('name', 'agency_manager')->first();
        // $agencyManager->permissions()->syncWithoutDetaching(
        //     Permission::whereIn('name', ['users.manage'])->pluck('id')
        // );

        // Agency Manager: full project control
        Role::where('name', 'agency_manager')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'milestones.create',
                'milestones.update',
                'milestones.delete',
                'tasks.create',
                'tasks.update',
                'tasks.delete',
                'time_logs.view_any',
            ])->pluck('id')
        );

        // Project Manager: can update projects (their own, enforced by Policy) and assign staff,
        // but cannot create new projects or delete them — that's an agency-level decision.
        Role::where('name', 'project_manager')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'milestones.create',
                'milestones.update',
                'milestones.delete',
                'tasks.create',
                'tasks.update',
                'tasks.delete',
                'time_logs.view_any',
            ])->pluck('id')
        );

        // Developer / Designer / QA: can only flip status on their own task + log their own time
        foreach (['developer', 'designer', 'qa'] as $roleName) {
            Role::where('name', $roleName)->first()->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['tasks.update_status', 'time_logs.create'])->pluck('id')
            );
        }

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
