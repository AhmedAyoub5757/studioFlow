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
        // ==========================================================
        // 1. ROLES — the fixed list of system roles for the whole app.
        //    Add a new role here ONLY if a brand-new actor type appears
        //    (rare after Sprint 0). Most sprints only add PERMISSIONS,
        //    not new roles.
        // ==========================================================
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

        // ==========================================================
        // 2. PERMISSIONS — every permission string that exists in the
        //    system, across ALL sprints. This list only ever GROWS.
        //    Grouped by sprint/module so it's obvious where to add
        //    the next one.
        // ==========================================================
        $permissions = [
            // --- Sprint 0: core ---
            'users.manage' => 'Manage Users',
            'roles.manage' => 'Manage Roles',

            // --- Sprint 1: Projects module ---
            'projects.view_any' => 'View All Projects',
            'projects.create' => 'Create Projects',
            'projects.update' => 'Update Projects',
            'projects.delete' => 'Delete Projects',
            'projects.assign_staff' => 'Assign Staff to Projects',

            // --- Sprint 2: Milestones module ---
            'milestones.create' => 'Create Milestones',
            'milestones.update' => 'Update Milestones',
            'milestones.delete' => 'Delete Milestones',

            // --- Sprint 2: Tasks module ---
            'tasks.create' => 'Create Tasks',
            'tasks.update' => 'Update Tasks (full edit)',
            'tasks.update_status' => 'Update Own Task Status',
            'tasks.delete' => 'Delete Tasks',

            // --- Sprint 2: Time Logs module ---
            'time_logs.create' => 'Log Own Time',
            'time_logs.view_any' => 'View All Time Logs on a Project',

            // --- Sprint 3: Comments module ---
            // Note: creating a comment needs NO permission string — it's pure
            // ownership (can you view the parent Task/Milestone's project?),
            // enforced entirely in CommentPolicy::create(). Only moderation
            // (deleting someone else's comment) needs an explicit permission.
            'comments.delete_any' => 'Delete Any Comment (moderation)',

            // --- Sprint 3: Approvals module ---
            'approvals.request' => 'Request Milestone Approval (mark ready for client)',
            'approvals.decide' => 'Approve or Reject a Milestone',

            // --- Sprint 4+ permissions go here, grouped under a new
            //     "--- Sprint N: X module ---" comment. Never delete
            //     or rename an old line without a migration plan —
            //     other roles' pivot rows reference these by name.
            'bugs.create' => 'Report Bugs',
            'bugs.update' => 'Full Edit Bugs (reassign, severity, etc.)',
            'bugs.transition_dev' => 'Move Own Bug open->in_progress->fixed',
            'bugs.transition_qa' => 'Verify/Close/Reopen Bugs (QA authority)',
            'bugs.wont_fix' => 'Mark Bug as Wont Fix',
            'bugs.delete' => 'Delete Bugs',
        ];

        foreach ($permissions as $name => $label) {
            Permission::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        // ==========================================================
        // 3. ROLE -> PERMISSION WIRING
        //
        //    IMPORTANT PATTERN: each role gets ONE syncWithoutDetaching()
        //    call per sprint block below, and every call ADDS permission
        //    IDs to whatever that role already has — it never removes.
        //    This is deliberate so Sprint 2/3/4 blocks can safely run
        //    after Sprint 1's block without wiping it out.
        //
        //    If you ever need to grant a NEW sprint's permissions to a
        //    role that already appears further down this file, DO NOT
        //    write a second isolated `Role::where(...)->first()->...`
        //    call with only the new sprint's permissions — that's fine
        //    AS LONG AS it's syncWithoutDetaching (which it is below).
        //    The bug we hit was not sync() vs syncWithoutDetaching() —
        //    it was that the Sprint 1 block itself was commented out,
        //    so projects.* was never attached to any role in the first
        //    place. Keep every sprint's block UNCOMMENTED and present.
        // ==========================================================

        // --- Sprint 1: Agency Manager — full project control + user mgmt ---
        Role::where('name', 'agency_manager')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'users.manage',
                'projects.view_any',
                'projects.create',
                'projects.update',
                'projects.delete',
                'projects.assign_staff',
            ])->pluck('id')
        );

        // --- Sprint 1: Project Manager — can update/assign on OWNED
        //     projects only (ownership enforced in ProjectPolicy, not here).
        //     Deliberately NO projects.create / projects.delete / projects.view_any.
        Role::where('name', 'project_manager')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'projects.update',
                'projects.assign_staff',
            ])->pluck('id')
        );

        // --- Sprint 2: Agency Manager — full milestone/task control + view all time logs ---
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

        // --- Sprint 2: Project Manager — same milestone/task control as
        //     Agency Manager, but scoped to OWNED projects via Policy ---
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

        // --- Sprint 2: Developer / Designer / QA — narrow permissions only:
        //     can flip status on THEIR OWN assigned task, and log THEIR OWN
        //     time. Full task edit (tasks.update) is deliberately withheld —
        //     enforced further by TaskPolicy::updateStatus() checking
        //     assigned_to === $user->id.
        foreach (['developer', 'designer', 'qa'] as $roleName) {
            Role::where('name', $roleName)->first()->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['tasks.update_status', 'time_logs.create'])->pluck('id')
            );
        }

        // --- Sprint 3: Agency Manager — can moderate any comment, and can
        //     both request AND decide approvals (override power over the
        //     normal PM-requests / Client-decides split below).
        Role::where('name', 'agency_manager')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'comments.delete_any',
                'approvals.request',
                'approvals.decide',
            ])->pluck('id')
        );

        // --- Sprint 3: Project Manager — can REQUEST approval (mark a
        //     milestone ready for client review) but deliberately CANNOT
        //     decide it. That decision belongs to the client. Ownership
        //     (must be manager on that specific project) is enforced in
        //     ApprovalPolicy::request().
        Role::where('name', 'project_manager')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', ['approvals.request'])->pluck('id')
        );

        // --- Sprint 3: Client — can DECIDE (approve/reject) approvals on
        //     their OWN projects only. Ownership enforced in
        //     ApprovalPolicy::decide() via project.client_id === user.id.
        //     Comment creation needs no permission entry — see note above
        //     the comments.delete_any permission definition.
        Role::where('name', 'client')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', ['approvals.decide'])->pluck('id')
        );

        // --- Sprint 4+ role wiring goes here as new
        //     "--- Sprint N: Role X — description ---" blocks, following
        //     the exact same syncWithoutDetaching() pattern.
        // Agency Manager: everything
        Role::where('name', 'agency_manager')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'bugs.create',
                'bugs.update',
                'bugs.transition_qa',
                'bugs.wont_fix',
                'bugs.delete',
            ])->pluck('id')
        );

        // PM: full control on owned projects, except QA verification authority
        Role::where('name', 'project_manager')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', ['bugs.create', 'bugs.update', 'bugs.wont_fix', 'bugs.delete'])->pluck('id')
        );

        // QA: creates bugs, and holds sole verification/close/reopen authority
        Role::where('name', 'qa')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', ['bugs.create', 'bugs.transition_qa'])->pluck('id')
        );

        // Developer: can only move their own assigned bug through the dev-side transitions
        Role::where('name', 'developer')->first()->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', ['bugs.transition_dev'])->pluck('id')
        );

        // ==========================================================
        // 4. SUPER ADMIN TEST USER
        //    Bypasses ALL permission checks via Gate::before() in
        //    AuthServiceProvider — doesn't need explicit permissions.
        // ==========================================================
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@studioflow.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $superAdmin->roles()->syncWithoutDetaching(
            Role::where('name', 'super_admin')->pluck('id')
        );
    }
}
