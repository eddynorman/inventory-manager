<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'sales' => ['add','edit','delete','view'],
            'items' => ['add','edit','delete','view'],
            'purchases' => ['add','edit','delete','view'],
            'receivings' => ['add','edit','delete','view'],
            'stock' => ['adjust','view'],
            'transfers' => ['add','edit','delete','view'],
            'issues' => ['add','edit','delete','view'],
            'requisitions' => ['add','edit','delete','view'],
            'suppliers' => ['add','edit','delete','view'],
            'customers' => ['add','edit','delete','view'],
            'users' => ['add','edit','delete','view'],
            'settings' => ['edit','view'],
        ];

        foreach ($categories as $category => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => $category.'.'.$action],
                    [
                        'category' => $category,
                        'action' => $action,
                        'description' => ucfirst($action).' permission for '.ucfirst($category),
                        'status' => 'pending',
                    ]
                );
            }
        }
    }
}


