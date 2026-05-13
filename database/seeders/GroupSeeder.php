<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [

            [
                'name' => 'Super Administrators',
                'description' => 'Full unrestricted system access',
                'permissions' => ['*'],
            ],

            [
                'name' => 'General Managers',
                'description' => 'Manage overall business operations',
                'permissions' => [
                    'dashboard.*',
                    'reports.*',
                    'sales.*',
                    'purchases.*',
                    'stock.*',
                    'requisitions.*',
                    'issues.*',
                    'transfers.*',
                    'receivings.*',
                    'customers.*',
                    'suppliers.*',
                ],
            ],

            [
                'name' => 'Inventory Controllers',
                'description' => 'Manage inventory and stock movement',
                'permissions' => [
                    'items.*',
                    'item_kits.*',
                    'stock.*',
                    'transfers.*',
                    'issues.*',
                    'receivings.*',
                    'requisitions.view',
                ],
            ],

            [
                'name' => 'Procurement Officers',
                'description' => 'Handle purchasing and supplier management',
                'permissions' => [
                    'purchases.*',
                    'orders.*',
                    'suppliers.*',
                    'receivings.view',
                    'requisitions.*',
                ],
            ],

            [
                'name' => 'Cashiers',
                'description' => 'Manage sales',
                'permissions' => [
                    'dashboard.view',
                    'sales.view',
                    'sales.create',
                    'sales.edit',
                    'reports.view_sales',
                    'customers.view',
                ],
            ],


            [
                'name' => 'Accountants',
                'description' => 'Manage expenses and financial reports',
                'permissions' => [
                    'banking.*',
                    'expenses.*',
                    'reports.view_financial',
                    'reports.view_expenses',
                    'sales.view',
                    'purchases.view',
                ],
            ],

            [
                'name' => 'Auditors',
                'description' => 'View reports and audit logs',
                'permissions' => [
                    'reports.*',
                    'audit_logs.view',
                ],
            ],
        ];

        foreach ($groups as $data) {

            $group = Group::updateOrCreate(
                [
                    'name' => $data['name']
                ],
                [
                    'description' => $data['description']
                ]
            );

            $permissionIds = [];

            foreach ($data['permissions'] as $permission) {

                if ($permission === '*') {

                    $permissionIds = Permission::pluck('id')->toArray();
                    break;
                }

                if (str_ends_with($permission, '.*')) {

                    $category = str_replace('.*', '', $permission);

                    $permissionIds = array_merge(
                        $permissionIds,
                        Permission::where('category', $category)
                            ->pluck('id')
                            ->toArray()
                    );

                    continue;
                }

                $perm = Permission::where('name', $permission)->first();

                if ($perm) {
                    $permissionIds[] = $perm->id;
                }
            }

            $group->permissions()->sync(array_unique($permissionIds));
        }
    }
}
