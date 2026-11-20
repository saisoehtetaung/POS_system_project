<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->orderBy('name')
            ->paginate(15);

        return view('cool.catalog.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $this->save($request);

        return redirect()->route('cool.brands.index')->with('success', 'Brand saved.');
    }

    public function update(Request $request, string $id)
    {
        $brand = $this->shopBrand($id);
        $this->save($request, $brand);

        return redirect()->route('cool.brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(string $id)
    {
        $brand = $this->shopBrand($id);

        if (Product::query()->where('brand_id', $brand->id)->exists()) {
            $brand->update(['is_active' => false]);

            return redirect()->route('cool.brands.index')->with('success', 'Brand is in use, so it was deactivated.');
        }

        $brand->delete();

        return redirect()->route('cool.brands.index')->with('success', 'Brand deleted.');
    }

    private function save(Request $request, ?Brand $brand = null): Brand
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'shop_id' => auth()->user()->shop_id,
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if ($brand) {
            $brand->update($payload);

            return $brand;
        }

        return Brand::query()->firstOrCreate(
            ['shop_id' => $payload['shop_id'], 'name' => $payload['name']],
            ['is_active' => $payload['is_active']]
        );
    }

    private function shopBrand(string $id): Brand
    {
        return Brand::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();
    }
}
