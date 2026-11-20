<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Identity\Models\Shop;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $shop = Shop::query()->whereKey(auth()->user()->shop_id)->firstOrFail();

        return view('cool.settings.edit', compact('shop'));
    }

    public function update(Request $request)
    {
        $shop = Shop::query()->whereKey(auth()->user()->shop_id)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'receipt_header' => ['nullable', 'string'],
            'receipt_footer' => ['nullable', 'string'],
            'currency_code' => ['required', 'string', 'size:3'],
        ]);

        $shop->update($data);

        return back()->with('success', 'Shop settings saved.');
    }
}
