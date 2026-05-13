<?php

namespace App\Services;

use App\Models\Group;
use App\Services\AuditLogService;

class GroupService
{
    public function rules(?int $id = null): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:255',
                'unique:groups,name' . ($id ? ',' . $id : ''),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'selectedPermissions' => [
                'required',
                'array',
                'min:1',
            ],
        ];
    }

    public function save(?int $id, array $data): Group
    {
        $group = Group::updateOrCreate(
            [
                'id' => $id
            ],
            [
                'name' => $data['name'],
                'description' => $data['description'],
            ]
        );

        $group->permissions()->sync($data['selectedPermissions']);

        AuditLogService::log(
            $id ? 'updated' : 'created',
            'Group',
            $group->id,
            'Group ' . ($id ? 'updated' : 'created') . ': ' . $group->name,
            [
                'permissions_count' => count($data['selectedPermissions']),
            ]
        );

        return $group;
    }

    public function delete(int $id): void
    {
        $group = Group::findOrFail($id);

        $group->permissions()->detach();
        $group->users()->detach();

        $group->delete();
        AuditLogService::log(
            'deleted',
            'Group',
            $group->id,
            'Deleted group: ' . $group->name
        );
    }
}
