<?php
namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::withCount('products');
        if ($search = trim((string) $request->input('search'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        $brands = $query->orderBy('name')->paginate(20)->withQueryString();
        $stats = [
            'total' => Brand::count(),
            'products' => \App\Models\Product::count(),
            'with_products' => Brand::has('products')->count(),
        ];

        return view('brands.index', compact('brands', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string|max:255',
        ]);
        Brand::create($data);
        return back()->with('success', 'Marque ajoutée.');
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:brands,name,'.$brand->id,
            'description' => 'nullable|string|max:255',
        ]);
        $brand->update($data);
        return back()->with('success', 'Marque mise à jour.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return back()->with('success', 'Marque supprimée.');
    }
}