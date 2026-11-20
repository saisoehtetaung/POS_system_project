<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $shopId = auth()->user()->shop_id;

        $categories = Category::query()
            ->with('parent')
            ->where('shop_id', $shopId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        $parents = Category::query()
            ->where('shop_id', $shopId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('cool.catalog.categories.index', compact('categories', 'parents'));
    }

    public function store(Request $request)
    {
        $this->saveCategory($request);

        return redirect()->route('cool.categories.index')->with('success', 'Category added.');
    }

    public function update(Request $request, string $id)
    {
        $category = $this->shopCategory($id);
        $this->saveCategory($request, $category);

        return redirect()->route('cool.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(string $id)
    {
        $category = $this->shopCategory($id);

        if (Product::query()->where('category_id', $category->id)->exists()) {
            $category->update(['is_active' => false]);

            return redirect()->route('cool.categories.index')->with('success', 'Category is in use, so it was deactivated.');
        }

        $category->delete();

        return redirect()->route('cool.categories.index')->with('success', 'Category deleted.');
    }

    private function saveCategory(Request $request, ?Category $category = null): Category
    {
        $shopId = auth()->user()->shop_id;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where('shop_id', $shopId)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (
            Category::query()
                ->where('shop_id', $shopId)
                ->where('slug', $slug)
                ->when($category, fn ($q) => $q->whereKeyNot($category->id))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        $payload = [
            'shop_id' => $shopId,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $slug,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if ($category) {
            $category->update($payload);

            return $category;
        }

        return Category::query()->create($payload);
    }

    private function shopCategory(string $id): Category
    {
        return Category::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();
    }
}
