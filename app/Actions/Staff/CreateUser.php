<?php

namespace App\Actions\Staff;

use App\Models\Staff\User;
use Illuminate\Auth\Events\Registered;

class CreateUser
{
    /**
     * @param array{name: string, email: string, password: string} $data
     */
    public function __invoke(array $data): User
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        event(new Registered($user));

        return $user;
    }
}
