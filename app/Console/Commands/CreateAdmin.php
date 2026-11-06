<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email? : Admin email} {--name= : Display name}';

    protected $description = 'Create a local administrator with an interactively entered password';

    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Display name')));
        $email = Str::lower(trim((string) ($this->argument('email') ?: $this->ask('Admin email'))));
        $password = $this->secret('Password (at least 12 characters)', false);

        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'max:72'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if (strlen($password) > 72) {
            $this->error('Use a password no longer than 72 bytes.');

            return self::FAILURE;
        }

        if ($password !== $this->secret('Confirm password', false)) {
            $this->error('Passwords do not match.');

            return self::FAILURE;
        }

        $user = new User($validator->validated());
        $user->is_admin = true;
        $user->save();

        $this->info('Administrator created. Open /login to sign in.');

        return self::SUCCESS;
    }
}
