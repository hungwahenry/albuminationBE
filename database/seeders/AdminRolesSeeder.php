<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRolesSeeder extends Seeder
{
    private const GUARD = 'admin';

    /**
     * Permissions grouped by section.
     */
    private const PERMISSIONS = [
        // Users
        'users.view',
        'users.edit',
        'users.delete',
        'users.impersonate',

        // Content moderation
        'reports.view',
        'reports.action',
        'report_reasons.manage',
        'content.delete',

        // Music catalog
        'catalog.view',
        'catalog.edit',
        'catalog.featured.manage',
        'catalog.cache.flush',
        'catalog.sync',

        // Content overview
        'rotations.view',
        'takes.view',
        'vibetags.manage',

        // Badges
        'badges.manage',

        // Feed
        'feed.manage',

        // Notifications
        'notifications.manage',
        'device_tokens.manage',

        // App config
        'app_config.manage',

        // Email
        'email.manage',

        // Search index
        'search_index.manage',

        // Media
        'media.manage',

        // Compliance
        'compliance.manage',

        // System (super admin only)
        'system.queue',
        'system.schedule',
        'admin_users.manage',

        // Analytics
        'analytics.view',

        // Audit log
        'audit_log.view',
    ];

    /**
     * Permissions granted to each role.
     */
    private const ROLE_PERMISSIONS = [
        'super_admin' => '*', // all

        'moderator' => [
            'users.view',
            'users.edit',
            'reports.view',
            'reports.action',
            'report_reasons.manage',
            'content.delete',
            'rotations.view',
            'takes.view',
            'analytics.view',
            'audit_log.view',
        ],

        'content_manager' => [
            'catalog.view',
            'catalog.edit',
            'catalog.featured.manage',
            'catalog.cache.flush',
            'catalog.sync',
            'rotations.view',
            'takes.view',
            'vibetags.manage',
            'feed.manage',
            'media.manage',
            'analytics.view',
        ],

        'analyst' => [
            'analytics.view',
            'users.view',
            'rotations.view',
            'takes.view',
            'catalog.view',
            'audit_log.view',
        ],
    ];

    public function run(): void
    {
        // Create all permissions
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, self::GUARD);
        }

        $allPermissions = Permission::where('guard_name', self::GUARD)->get();

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, self::GUARD);

            if ($permissions === '*') {
                $role->syncPermissions($allPermissions);
            } else {
                $role->syncPermissions(
                    $allPermissions->whereIn('name', $permissions)
                );
            }
        }
    }
}
