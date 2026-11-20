@extends('cool.layouts.app')

@section('title', 'Settings')
@section('heading', 'Shop Settings')

@section('content')
    <form method="POST" action="{{ route('cool.settings.update') }}" class="cs-panel p-5 space-y-4 max-w-3xl">
        @csrf
        @method('PUT')
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="text-sm font-semibold">Shop name</label>
                <input class="cs-input mt-1" name="name" value="{{ old('name', $shop->name) }}" required>
            </div>
            <div>
                <label class="text-sm font-semibold">Legal name</label>
                <input class="cs-input mt-1" name="legal_name" value="{{ old('legal_name', $shop->legal_name) }}">
            </div>
            <div>
                <label class="text-sm font-semibold">Phone</label>
                <input class="cs-input mt-1" name="phone" value="{{ old('phone', $shop->phone) }}">
            </div>
            <div>
                <label class="text-sm font-semibold">Currency</label>
                <input class="cs-input mt-1" name="currency_code" value="{{ old('currency_code', $shop->currency_code) }}" maxlength="3" required>
            </div>
            <div>
                <label class="text-sm font-semibold">Tax rate (%)</label>
                <input class="cs-input mt-1" type="number" step="0.0001" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $shop->tax_rate) }}" required>
            </div>
            <div class="sm:col-span-2">
                <label class="text-sm font-semibold">Address</label>
                <textarea class="cs-input mt-1" name="address" rows="2">{{ old('address', $shop->address) }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="text-sm font-semibold">Receipt header</label>
                <textarea class="cs-input mt-1" name="receipt_header" rows="2">{{ old('receipt_header', $shop->receipt_header) }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="text-sm font-semibold">Receipt footer</label>
                <textarea class="cs-input mt-1" name="receipt_footer" rows="2">{{ old('receipt_footer', $shop->receipt_footer) }}</textarea>
            </div>
        </div>
        <button class="cs-btn-primary" type="submit">Save settings</button>
    </form>
@endsection
