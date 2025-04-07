<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    protected $signature = 'make:admin {email} {password}';
    protected $description = 'Cria um usuário administrador com todas as permissões';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Admin User', 'password' => bcrypt($password)]
        );

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        $this->info("Usuário administrador criado com sucesso: {$email}");
    }
}
