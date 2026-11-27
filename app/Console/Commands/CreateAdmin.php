<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=Administrator}';

    protected $description = 'Create an administrator with a generated secure password';

    public function handle(): int
    {
        $data = ['email' => $this->argument('email'), 'name' => $this->option('name')];
        $validator = Validator::make($data, ['email' => ['required', 'email', 'max:255', 'unique:users,email'], 'name' => ['required', 'string', 'max:100']]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }
        $password = bin2hex(random_bytes(10));
        $admin = new User([...$data, 'password' => Hash::make($password)]);
        $admin->role = 'admin';
        $admin->save();
        $this->info('Administrator created: '.$data['email']);
        $this->line('Password (save now): '.$password);

        return self::SUCCESS;
    }
}
