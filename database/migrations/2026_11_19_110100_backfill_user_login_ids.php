<?php

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Services\LoginIdGenerator;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        $roles = Role::query()->pluck('name', 'id');

        User::query()
            ->whereNull('login_id')
            ->orderBy('created_at')
            ->each(function (User $user) use ($roles) {
                $roleName = $roles[$user->role_id] ?? 'cashier';
                $user->forceFill([
                    'login_id' => LoginIdGenerator::next($user->shop_id, $roleName),
                ])->save();
            });
    }

    public function down()
    {
        //
    }
};
