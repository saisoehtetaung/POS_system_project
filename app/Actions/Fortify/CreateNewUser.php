<?php

namespace App\Actions\Fortify;

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\Shop;
use App\Domain\Identity\Services\LoginIdGenerator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => $this->passwordRules(),
            'password_confirmation' => ['required'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $shopId = Shop::query()->value('id');
        $roleId = Role::query()->where('name', 'cashier')->value('id');

        if (! $shopId || ! $roleId) {
            throw ValidationException::withMessages([
                'email' => 'The shop is not ready for new accounts yet.',
            ]);
        }

        return User::create([
            'shop_id' => $shopId,
            'role_id' => $roleId,
            'login_id' => LoginIdGenerator::next($shopId, 'cashier'),
            'name' => $input['name'],
            'email' => $input['email'] ?? null,
            'phone' => $input['phone'] ?? null,
            'password' => Hash::make($input['password']),
            'is_active' => true,
        ]);
    }
}
