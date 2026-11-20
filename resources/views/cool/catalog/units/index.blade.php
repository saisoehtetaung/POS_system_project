@extends('cool.layouts.app')

@section('title', 'Units')
@section('heading', 'Units of Measure')

@section('content')
    <div
        class="space-y-4"
        x-data="unitModal({
            storeUrl: @js(route('cool.units.store')),
            updateUrl: @js(url('cool/units')),
            openOnLoad: @js($errors->any()),
            old: @js(old()),
        })"
    >
        <div class="flex justify-end">
            <button type="button" class="cs-btn-primary" @click="openCreate()">Add Unit</button>
        </div>

        <section class="cs-panel overflow-hidden">
            <table class="cs-table">
                <thead>
                    <tr><th>Name</th><th>Abbreviation</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr>
                            <td class="font-semibold">{{ $unit->name }}</td>
                            <td>{{ $unit->abbreviation }}</td>
                            <td class="text-right space-x-3">
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-[var(--cs-accent-deep)]"
                                    @click="openEdit(@js([
                                        'id' => $unit->id,
                                        'name' => $unit->name,
                                        'abbreviation' => $unit->abbreviation,
                                    ]))"
                                >Edit</button>
                                <form method="POST" action="{{ route('cool.units.destroy', $unit->id) }}" class="inline" onsubmit="return confirm('Delete unit?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-rose-600" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-[var(--cs-muted)]">No units yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $units->links() }}</div>
        </section>

        <div class="cs-modal-backdrop" x-show="open" x-cloak @keydown.escape.window="open = false">
            <div class="cs-modal" @click.outside="open = false">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="brand-font text-lg font-bold" x-text="editing ? 'Edit Unit' : 'Add Unit'"></h2>
                    <button type="button" class="cs-btn-ghost px-3 py-1" @click="open = false">Close</button>
                </div>
                <form method="POST" :action="formAction" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="!editing">
                    <input class="cs-input" name="name" required placeholder="Piece" x-model="form.name">
                    <input class="cs-input" name="abbreviation" required placeholder="Pcs" x-model="form.abbreviation">
                    <button class="cs-btn-primary w-full" type="submit" x-text="editing ? 'Update' : 'Save'"></button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function unitModal(boot) {
            return {
                open: !!boot.openOnLoad,
                editing: false,
                storeUrl: boot.storeUrl,
                updateUrl: boot.updateUrl,
                form: { id: null, name: '', abbreviation: '' },
                get formAction() {
                    return this.editing ? `${this.updateUrl}/${this.form.id}` : this.storeUrl;
                },
                init() {
                    if (boot.openOnLoad && boot.old) {
                        this.form = {
                            id: boot.old._editing_id || null,
                            name: boot.old.name || '',
                            abbreviation: boot.old.abbreviation || '',
                        };
                        this.editing = !!this.form.id;
                    }
                },
                openCreate() {
                    this.editing = false;
                    this.form = { id: null, name: '', abbreviation: '' };
                    this.open = true;
                },
                openEdit(item) {
                    this.editing = true;
                    this.form = { id: item.id, name: item.name, abbreviation: item.abbreviation };
                    this.open = true;
                },
            };
        }
    </script>
@endsection
