<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            'dashboard' => [
                'view',
            ],

            'users' => [
                'view',
                'create',
                'edit',
                'delete',
                'activate',
                'deactivate',
            ],

            'groups' => [
                'view',
                'create',
                'edit',
                'delete',
                'assign_permissions',
            ],

            'items' => [
                'view',
                'create',
                'edit',
                'delete',
                'adjust_stock',
                'import',
                'export',
            ],

            'item_kits' => [
                'view',
                'create',
                'edit',
                'delete',
            ],

            'categories' => [
                'view',
                'create',
                'edit',
                'delete',
            ],

            'sales' => [
                'view',
                'create',
                'edit',
                'delete',
                'cancel',
            ],

            'customers' => [
                'view',
                'create',
                'edit',
                'delete',
            ],

            'suppliers' => [
                'view',
                'create',
                'edit',
                'delete',
            ],

            'requisitions' => [
                'view',
                'create',
                'edit',
                'delete',
                'review',
                'approve',
                'reject',
                'fund',
            ],

            'purchases' => [
                'view',
                'create',
                'edit',
                'delete',
            ],
            'orders' => [
                'view',
                'create',
                'edit',
                'delete',
            ],

            'receivings' => [
                'view',
                'create',
                'edit',
            ],

            'issues' => [
                'view',
                'create',
                'edit',
                'reject',
                'process',
            ],

            'transfers' => [
                'view',
                'create',
                'approve',
                'receive',
            ],

            'stock' => [
                'view',
                'count',
                'adjust',
                'close_day',
                'view_valuation',
            ],

            'locations' => [
                'view',
                'create',
                'edit',
                'delete',
            ],

            'departments' => [
                'view',
                'create',
                'edit',
                'delete',
            ],

            'expenses' => [
                'view',
                'create',
                'edit',
                'delete',
                'approve',
            ],

            'banking' => [
                'view',
                'deposit',
                'withdraw',
                'add_account',
                'edit_account',
            ],

            'reports' => [
                'view_sales',
                'view_inventory',
                'view_profit',
                'view_expenses',
                'view_financial',
                'view_stock',
                'view_banking',
            ],

            'settings' => [
                'view',
                'edit',
            ],

            'audit_logs' => [
                'view',
            ],
        ];

        foreach ($permissions as $category => $actions) {

            foreach ($actions as $action) {

                Permission::updateOrCreate(
                    [
                        'name' => $category . '.' . $action
                    ],
                    [
                        'category' => $category,
                        'action' => $action,
                        'description' => ucfirst(str_replace('_', ' ', $action))
                            . ' permission for '
                            . ucfirst(str_replace('_', ' ', $category)),
                    ]
                );
            }
        }
    }
}
