@extends('cool.layouts.app')

@section('title', 'Brands')
@section('heading', 'Brands')

@section('content')
    <div
        class="space-y-4"
        x-data="brandModal({
            storeUrl: @js(route('cool.brands.store')),
            updateUrl: @js(url('cool/brands')),
            openOnLoad: @js($errors->any()),
            old: @js(old()),
        })"
    >
        <div class="flex justify-end">
            <button type="button" class="cs-btn-primary" @click="openCreate()">Add Brand</button>
        </div>

        <section class="cs-panel overflow-hidden">
            <table class="cs-table">
                <thead>
                    <tr><th>Name</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($brands as $brand)
                        <tr>
                            <td class="font-semibold">{{ $brand->name }}</td>
                            <td>@if($brand->is_active)<span class="cs-badge-ok">Active</span>@else<span class="cs-badge-warn">Off</span>@endif</td>
                            <td class="text-right space-x-3">
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-[var(--cs-accent-deep)]"
                                    @click="openEdit(@js([
                                        'id' => $brand->id,
                                        'name' => $brand->name,
                                        'is_active' => (bool) $brand->is_active,
                                    ]))"
                                >Edit</button>
                                <form method="POST" action="{{ route('cool.brands.destroy', $brand->id) }}" class="inline" onsubmit="return confirm('Delete brand?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-rose-600" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-[var(--cs-muted)]">No brands yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $brands->links() }}</div>
        </section>

        <div class="cs-modal-backdrop" x-show="open" x-cloak @keydown.escape.window="open = false">
            <div class="cs-modal" @click.outside="open = false">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="brand-font text-lg font-bold" x-text="editing ? 'Edit Brand' : 'Add Brand'"></h2>
                    <button type="button" class="cs-btn-ghost px-3 py-1" @click="open = false">Close</button>
                </div>
                <form method="POST" :action="formAction" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="!editing">
                    <input class="cs-input" name="name" required placeholder="Stabilo, Pilot..." x-model="form.name">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active">
                        Active
                    </label>
                    <button class="cs-btn-primary w-full" type="submit" x-text="editing ? 'Update' : 'Save'"></button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function brandModal(boot) {
            return {
                open: !!boot.openOnLoad,
                editing: false,
                storeUrl: boot.storeUrl,
                updateUrl: boot.updateUrl,
                form: { id: null, name: '', is_active: true },
                get formAction() {
                    return this.editing ? `${this.updateUrl}/${this.form.id}` : this.storeUrl;
                },
                init() {
                    if (boot.openOnLoad && boot.old) {
                        this.form = {
                            id: boot.old._editing_id || null,
                            name: boot.old.name || '',
                            is_active: !!boot.old.is_active,
                        };
                        this.editing = !!this.form.id;
                    }
                },
                openCreate() {
                    this.editing = false;
                    this.form = { id: null, name: '', is_active: true };
                    this.open = true;
                },
                openEdit(item) {
                    this.editing = true;
                    this.form = { id: item.id, name: item.name, is_active: !!item.is_active };
                    this.open = true;
                },
            };
        }
    </script>
@endsection
