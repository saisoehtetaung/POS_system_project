<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Catalog\Models\UnitConversion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->orderBy('name')
            ->paginate(15);

        return view('cool.catalog.units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $this->save($request);

        return redirect()->route('cool.units.index')->with('success', 'Unit saved.');
    }

    public function update(Request $request, string $id)
    {
        $unit = $this->shopUnit($id);
        $this->save($request, $unit);

        return redirect()->route('cool.units.index')->with('success', 'Unit updated.');
    }

    public function destroy(string $id)
    {
        $unit = $this->shopUnit($id);

        $inUse = Product::query()->where('base_unit_id', $unit->id)->exists()
            || UnitConversion::query()->where('unit_id', $unit->id)->exists();

        if ($inUse) {
            return redirect()->route('cool.units.index')->withErrors(['unit' => 'Unit is in use and cannot be deleted.']);
        }

        $unit->delete();

        return redirect()->route('cool.units.index')->with('success', 'Unit deleted.');
    }

    private function save(Request $request, ?Unit $unit = null): Unit
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abbreviation' => ['required', 'string', 'max:50'],
        ]);

        if ($unit) {
            $unit->update($data);

            return $unit;
        }

        return Unit::query()->firstOrCreate(
            [
                'shop_id' => auth()->user()->shop_id,
                'abbreviation' => $data['abbreviation'],
            ],
            ['name' => $data['name']]
        );
    }

    private function shopUnit(string $id): Unit
    {
        return Unit::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();
    }
}
