<?php

namespace Tests\Traits;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait HasPermissionsTrait
{
    /**
     * Adiciona uma permissão específica para o usuário usado no teste
     */
    public function withPermission($permission)
    {
        // Criar a permissão se não existir
        $permissionModel = Permission::firstOrCreate(['name' => $permission]);
        
        // Atribuir a permissão ao usuário
        $this->user->givePermissionTo($permissionModel);
        
        return $this;
    }

    /**
     * Atribui um papel específico ao usuário do teste
     */
    public function withRole($role)
    {
        $roleModel = Role::firstOrCreate(['name' => $role]);
        
        $this->user->assignRole($roleModel);
        
        return $this;
    }
}
