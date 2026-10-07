<?php

namespace App\Actions\Staff;

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use App\Support\EmailAddress;
use Illuminate\Auth\Events\Registered;

class CreateUser
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function __invoke(array $data): User
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => EmailAddress::normalize($data['email']),
            'password' => $data['password'],
            // Registering creates the bakery owner, so the role is not left to the column default.
            'role' => UserRole::Owner,
        ]);

        event(new Registered($user));

        return $user;
    }
}
