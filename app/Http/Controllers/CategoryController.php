<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');
        if ($search = trim((string) $request->input('search'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        $categories = $query->orderBy('name')->paginate(20)->withQueryString();
        $stats = [
            'total' => Category::count(),
            'products' => \App\Models\Product::count(),
            'with_products' => Category::has('products')->count(),
        ];

        return view('categories.index', compact('categories', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:255',
        ]);
        Category::create($data);
        return back()->with('success', 'Catégorie ajoutée.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,'.$category->id,
            'description' => 'nullable|string|max:255',
        ]);
        $category->update($data);
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }
}