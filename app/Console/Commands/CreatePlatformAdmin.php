<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreatePlatformAdmin extends Command
{
    protected $signature = 'platform-admin:create';

    protected $description = 'Create a platform administrator account';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Platform admin name'));

        if ($name === '') {
            $this->error('Name is required.');

            return self::FAILURE;
        }

        $email = strtolower(trim((string) $this->ask('Platform admin email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Enter a valid email address.');

            return self::FAILURE;
        }

        if (PlatformAdmin::where('email', $email)->exists()) {
            $this->error('A platform admin already exists with this email.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (minimum 12 characters)');
        $passwordConfirmation = (string) $this->secret('Confirm password');

        if (strlen($password) < 12) {
            $this->error('Password must be at least 12 characters long.');

            return self::FAILURE;
        }

        if (! hash_equals($password, $passwordConfirmation)) {
            $this->error('The passwords do not match.');

            return self::FAILURE;
        }

        PlatformAdmin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $this->info("Platform admin account created for {$email}.");

        return self::SUCCESS;
    }
}