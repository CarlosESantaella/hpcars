<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin-user';

    protected $description = 'Create an admin user for the system';

    public function handle(): int
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@hpcars.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin'),
                'email_verified_at' => now(),
            ]
        );

        $this->info("Usuario creado exitosamente:");
        $this->line("  Email: admin@hpcars.test");
        $this->line("  Password: admin");

        return Command::SUCCESS;
    }
}
