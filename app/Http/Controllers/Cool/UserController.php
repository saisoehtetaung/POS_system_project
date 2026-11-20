<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Services\LoginIdGenerator;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->with('role')
            ->where('shop_id', auth()->user()->shop_id)
            ->orderBy('name')
            ->paginate(15);

        $roles = Role::query()->orderBy('name')->get();

        return view('cool.people.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $this->save($request);

        return redirect()->route('cool.users.index')->with('success', 'Staff member created.');
    }

    public function update(Request $request, string $id)
    {
        $user = $this->shopUser($id);
        $this->save($request, $user);

        return redirect()->route('cool.users.index')->with('success', 'Staff member updated.');
    }

    public function destroy(string $id)
    {
        $user = $this->shopUser($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('cool.users.index')->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->update(['is_active' => false]);
        $user->delete();

        return redirect()->route('cool.users.index')->with('success', 'Staff member removed.');
    }

    private function save(Request $request, ?User $user = null): User
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
            'pin' => ['nullable', 'string', 'min:4', 'max:12'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $role = Role::query()->whereKey($data['role_id'])->firstOrFail();

        if (in_array($role->name, ['owner', 'manager'], true) && empty($data['pin']) && ! $user?->pin_hash) {
            throw ValidationException::withMessages([
                'pin' => 'Owner and manager accounts require a PIN.',
            ]);
        }

        $payload = [
            'shop_id' => auth()->user()->shop_id,
            'role_id' => $role->id,
            'name' => $data['name'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        if (! empty($data['pin'])) {
            $payload['pin_hash'] = Hash::make($data['pin']);
        }

        if ($user) {
            $user->update($payload);

            return $user;
        }

        $payload['login_id'] = LoginIdGenerator::next(auth()->user()->shop_id, $role->name);

        return User::query()->create($payload);
    }

    private function shopUser(string $id): User
    {
        return User::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();
    }
}
