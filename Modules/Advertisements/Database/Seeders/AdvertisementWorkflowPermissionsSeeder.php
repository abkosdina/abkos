<?php

namespace Modules\Advertisements\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Advertisement Workflow Permissions Seeder
 *
 * Seeds all permissions and role assignments for the workflow module.
 */
class AdvertisementWorkflowPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->createPermissions();
        $this->assignPermissionsToRoles();
    }

    /**
     * Create all permissions
     */
    protected function createPermissions(): void
    {
        $permissions = [
            // User permissions
            'create-advertisement',
            'update-own-advertisement',
            'delete-own-advertisement',
            'submit-advertisement',
            'pause-advertisement',
            'resume-advertisement',
            'archive-advertisement',
            'view-own-advertisement',

            // Operator permissions
            'view-pending-advertisements',
            'approve-advertisement',
            'reject-advertisement',
            'request-correction',
            'hide-advertisement',
            'view-reports',

            // Senior Operator permissions
            'restore-advertisement',
            'feature-advertisement',

            // Moderator permissions
            'suspend-advertisement',
            'remove-advertisement',
            'investigate-reports',
            'manage-violations',

            // Admin permissions
            'manage-all-advertisements',
            'force-archive',
            'force-publish',
            'force-pause',
            'change-owner',
            'manage-priorities',

            // Super Admin permissions
            'manage-workflow',
            'manage-permissions',
            'manage-roles',
            'manage-advertisement-settings',
            'manage-workflow-templates',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }
    }

    /**
     * Assign permissions to roles
     */
    protected function assignPermissionsToRoles(): void
    {
        $rolePermissions = config('advertisement-workflow.role_permissions', []);

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);

            // Get permission objects
            $permissionObjects = Permission::whereIn('name', $permissions)
                ->where('guard_name', 'api')
                ->get();

            // Sync permissions to role
            $role->syncPermissions($permissionObjects);
        }
    }
}
