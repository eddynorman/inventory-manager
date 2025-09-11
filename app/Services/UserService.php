<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserService
{
    public function rules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email' . ($id ? ",$id" : '')],
            'password' => $id ? ['nullable', Password::default()] : ['required', Password::default()],
            'type' => ['required', 'in:staff,customer,supplier,accountant,other'],
            'role' => ['required', 'in:super,admin,manager'],
            'isActive' => ['required', 'boolean'],
        ];
    }

    public function save(?int $id, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $data['isActive'];
        unset($data['isActive']);
        return User::updateOrCreate(['id' => $id], $data);

    }

    public function delete(int $id): void
    {
        User::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids): void
    {
        User::whereIn('id', $ids)->delete();
    }

    public function bulkAssignRoles(array $ids, string $role): void
    {
        User::whereIn('id', $ids)->update(['role' => $role]);
    }
}
