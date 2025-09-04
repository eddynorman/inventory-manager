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
            'Super Administrators' => [
                'all' => true,
                'description' => 'Full access to all features',
            ],
            'Administrators' => [
                'allow' => [
                    'items.*', 'sales.*', 'purchases.*', 'receivings.*', 'transfers.*', 'issues.*', 'requisitions.*', 'suppliers.*', 'customers.*', 'stock.*', 'users.view', 'settings.view', 'settings.edit'
                ],
                'description' => 'Manage core operations',
            ],
            'Managers' => [
                'allow' => [
                    'items.view', 'sales.view', 'purchases.view', 'receivings.view', 'transfers.view', 'issues.view', 'requisitions.view', 'suppliers.view', 'customers.view', 'stock.view'
                ],
                'description' => 'Read-only core operations',
            ],
        ];

        foreach ($groups as $name => $config) {
            $group = Group::firstOrCreate(['name' => $name], ['description' => $config['description'] ?? null]);

            if (!empty($config['all'])) {
                $permIds = Permission::pluck('id')->all();
                $group->permissions()->sync($permIds);
                continue;
            }

            $allow = $config['allow'] ?? [];
            $ids = [];
            foreach ($allow as $pattern) {
                if (str_ends_with($pattern, '.*')) {
                    $category = substr($pattern, 0, -2);
                    $ids = array_merge($ids, Permission::where('category', $category)->pluck('id')->all());
                } else {
                    $ids = array_merge($ids, Permission::where('name', $pattern)->pluck('id')->all());
                }
            }
            $group->permissions()->sync(array_unique($ids));
        }
    }
}


