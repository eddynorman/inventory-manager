<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'super@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'type' => 'staff',
                'role' => 'super',
            ]
        );

        if ($group = Group::where('name', 'Super Administrators')->first()) {
            $user->groups()->syncWithoutDetaching([$group->id]);
        }
    }
}


