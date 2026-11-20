@extends('cool.layouts.app')

@section('title', 'Staff')
@section('heading', 'Staff & Roles')

@section('content')
    <div
        class="space-y-4"
        x-data="userModal({
            storeUrl: @js(route('cool.users.store')),
            updateUrl: @js(url('cool/users')),
            roles: @js($roles),
            openOnLoad: @js($errors->any()),
            old: @js(old()),
        })"
    >
        <div class="flex justify-end">
            <button type="button" class="cs-btn-primary" @click="openCreate()">Add Staff</button>
        </div>

        <section class="cs-panel overflow-hidden">
            <table class="cs-table">
                <thead>
                    <tr><th>Name</th><th>Login ID</th><th>Role</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $user->name }}</div>
                                <div class="text-xs text-[var(--cs-muted)]">{{ $user->email ?: '—' }}</div>
                            </td>
                            <td class="font-mono font-semibold">{{ $user->login_id }}</td>
                            <td class="capitalize">{{ optional($user->role)->name }}</td>
                            <td>
                                @if($user->is_active)<span class="cs-badge-ok">Active</span>@else<span class="cs-badge-warn">Off</span>@endif
                            </td>
                            <td class="text-right space-x-3">
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-[var(--cs-accent-deep)]"
                                    @click="openEdit(@js([
                                        'id' => $user->id,
                                        'login_id' => $user->login_id,
                                        'name' => $user->name,
                                        'email' => $user->email,
                                        'phone' => $user->phone,
                                        'role_id' => $user->role_id,
                                        'is_active' => (bool) $user->is_active,
                                    ]))"
                                >Edit</button>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('cool.users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Remove staff?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm text-rose-600" type="submit">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-[var(--cs-muted)]">No staff yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $users->links() }}</div>
        </section>

        <div class="cs-modal-backdrop" x-show="open" x-cloak @keydown.escape.window="open = false">
            <div class="cs-modal" @click.outside="open = false">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="brand-font text-lg font-bold" x-text="editing ? 'Edit Staff' : 'Add Staff'"></h2>
                    <button type="button" class="cs-btn-ghost px-3 py-1" @click="open = false">Close</button>
                </div>
                <form method="POST" :action="formAction" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="!editing">
                    <template x-if="editing">
                        <div class="rounded-xl bg-[var(--cs-paper)] px-3 py-2 text-sm">
                            Login ID: <span class="font-mono font-semibold" x-text="form.login_id"></span>
                        </div>
                    </template>
                    <p class="text-xs text-[var(--cs-muted)]" x-show="!editing">Login ID is auto-generated (A / M / S) on create.</p>
                    <input class="cs-input" name="name" placeholder="Name" x-model="form.name" required>
                    <input class="cs-input" name="email" type="email" placeholder="Email (optional)" x-model="form.email">
                    <input class="cs-input" name="phone" placeholder="Phone" x-model="form.phone">
                    <select class="cs-input" name="role_id" x-model="form.role_id" required>
                        <template x-for="role in roles" :key="role.id">
                            <option :value="role.id" x-text="role.name.charAt(0).toUpperCase() + role.name.slice(1)"></option>
                        </template>
                    </select>
                    <input class="cs-input" name="password" type="password" :placeholder="editing ? 'New password (optional)' : 'Password'" :required="!editing">
                    <input class="cs-input" name="pin" type="password" :placeholder="editing ? 'New PIN (optional)' : 'PIN (owner/manager)'">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active">
                        Active
                    </label>
                    <button class="cs-btn-primary w-full" type="submit" x-text="editing ? 'Update' : 'Create'"></button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function userModal(boot) {
            return {
                open: !!boot.openOnLoad,
                editing: false,
                storeUrl: boot.storeUrl,
                updateUrl: boot.updateUrl,
                roles: boot.roles || [],
                form: { id: null, login_id: '', name: '', email: '', phone: '', role_id: '', is_active: true },
                get formAction() {
                    return this.editing ? `${this.updateUrl}/${this.form.id}` : this.storeUrl;
                },
                init() {
                    const defaultRole = this.roles[0]?.id || '';
                    if (boot.openOnLoad && boot.old) {
                        this.form = {
                            id: boot.old._editing_id || null,
                            login_id: boot.old.login_id || '',
                            name: boot.old.name || '',
                            email: boot.old.email || '',
                            phone: boot.old.phone || '',
                            role_id: boot.old.role_id || defaultRole,
                            is_active: !!boot.old.is_active,
                        };
                        this.editing = !!this.form.id;
                    } else {
                        this.form.role_id = defaultRole;
                    }
                },
                openCreate() {
                    this.editing = false;
                    this.form = {
                        id: null,
                        login_id: '',
                        name: '',
                        email: '',
                        phone: '',
                        role_id: this.roles[0]?.id || '',
                        is_active: true,
                    };
                    this.open = true;
                },
                openEdit(item) {
                    this.editing = true;
                    this.form = {
                        id: item.id,
                        login_id: item.login_id || '',
                        name: item.name || '',
                        email: item.email || '',
                        phone: item.phone || '',
                        role_id: item.role_id || '',
                        is_active: !!item.is_active,
                    };
                    this.open = true;
                },
            };
        }
    </script>
@endsection
