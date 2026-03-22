<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature   = 'admin:create {--role=super_admin}';
    protected $description = 'Create a new admin user';

    public function handle(): void
    {
        $name     = $this->ask('Name');
        $email    = $this->ask('Email');
        $password = $this->secret('Password');
        $role     = $this->option('role');

        $admin = AdminUser::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $admin->assignRole($role);

        $this->info("Admin user [{$email}] created with role [{$role}].");
    }
}
