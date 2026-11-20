@extends('cool.layouts.app')

@section('title', 'Categories')
@section('heading', 'Categories')

@section('content')
    <div
        class="space-y-4"
        x-data="categoryModal({
            storeUrl: @js(route('cool.categories.store')),
            updateUrl: @js(url('cool/categories')),
            parents: @js($parents),
            openOnLoad: @js($errors->any()),
            old: @js(old()),
        })"
    >
        <div class="flex justify-end">
            <button type="button" class="cs-btn-primary" @click="openCreate()">Add Category</button>
        </div>

        <section class="cs-panel overflow-hidden">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="font-semibold">{{ $category->name }}</td>
                            <td>{{ optional($category->parent)->name ?? '—' }}</td>
                            <td>
                                @if($category->is_active)<span class="cs-badge-ok">Active</span>@else<span class="cs-badge-warn">Off</span>@endif
                            </td>
                            <td class="text-right space-x-3">
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-[var(--cs-accent-deep)]"
                                    @click="openEdit(@js([
                                        'id' => $category->id,
                                        'name' => $category->name,
                                        'parent_id' => $category->parent_id,
                                        'sort_order' => $category->sort_order,
                                        'is_active' => (bool) $category->is_active,
                                    ]))"
                                >Edit</button>
                                <form method="POST" action="{{ route('cool.categories.destroy', $category->id) }}" class="inline" onsubmit="return confirm('Delete category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-rose-600" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-[var(--cs-muted)]">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $categories->links() }}</div>
        </section>

        <div class="cs-modal-backdrop" x-show="open" x-cloak @keydown.escape.window="open = false">
            <div class="cs-modal" @click.outside="open = false">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="brand-font text-lg font-bold" x-text="editing ? 'Edit Category' : 'Add Category'"></h2>
                    <button type="button" class="cs-btn-ghost px-3 py-1" @click="open = false">Close</button>
                </div>
                <form method="POST" :action="formAction" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="!editing">
                    <input class="cs-input" name="name" placeholder="Name" x-model="form.name" required>
                    <select class="cs-input" name="parent_id" x-model="form.parent_id">
                        <option value="">No parent</option>
                        <template x-for="parent in parents" :key="parent.id">
                            <option :value="parent.id" x-text="parent.name" :disabled="editing && parent.id === form.id"></option>
                        </template>
                    </select>
                    <input class="cs-input" type="number" name="sort_order" x-model="form.sort_order">
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
        function categoryModal(boot) {
            return {
                open: !!boot.openOnLoad,
                editing: false,
                storeUrl: boot.storeUrl,
                updateUrl: boot.updateUrl,
                parents: boot.parents || [],
                form: { id: null, name: '', parent_id: '', sort_order: 0, is_active: true },
                get formAction() {
                    return this.editing ? `${this.updateUrl}/${this.form.id}` : this.storeUrl;
                },
                init() {
                    if (boot.openOnLoad && boot.old) {
                        this.editing = !!boot.old._editing_id;
                        this.form = {
                            id: boot.old._editing_id || null,
                            name: boot.old.name || '',
                            parent_id: boot.old.parent_id || '',
                            sort_order: boot.old.sort_order ?? 0,
                            is_active: !!boot.old.is_active,
                        };
                    }
                },
                openCreate() {
                    this.editing = false;
                    this.form = { id: null, name: '', parent_id: '', sort_order: 0, is_active: true };
                    this.open = true;
                },
                openEdit(item) {
                    this.editing = true;
                    this.form = {
                        id: item.id,
                        name: item.name,
                        parent_id: item.parent_id || '',
                        sort_order: item.sort_order ?? 0,
                        is_active: !!item.is_active,
                    };
                    this.open = true;
                },
            };
        }
    </script>
@endsection
