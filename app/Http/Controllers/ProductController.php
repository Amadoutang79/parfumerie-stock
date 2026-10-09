<?php
namespace App\Http\Controllers;

use App\Models\{Product, Brand, Category};
use App\Services\StockInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand','category']);

        if ($s = $request->input('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('reference', 'like', "%$s%")
                  ->orWhere('barcode', 'like', "%$s%");
            });
        }
        if ($brand = $request->input('brand_id'))       $query->where('brand_id', $brand);
        if ($cat   = $request->input('category_id'))    $query->where('category_id', $cat);
        if ($gender= $request->input('gender'))         $query->where('gender', $gender);
        if ($concentration = $request->input('concentration')) $query->where('concentration', $concentration);
        if ($stock = $request->input('stock')) {
            if ($stock === 'low')  $query->lowStock();
            if ($stock === 'out')  $query->outOfStock();
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
            'stock_value' => (float) Product::sum(DB::raw('stock_quantity * purchase_price')),
        ];

        return view('products.index', [
            'products'   => $products,
            'stats'      => $stats,
            'brands'     => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'filters'    => $request->only(['search','brand_id','category_id','gender','concentration','stock']),
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'brands'     => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'reference'  => Product::generateReference(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['reference'] = $data['reference'] ?: Product::generateReference();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = DB::transaction(function () use ($data) {
            $initialStock = $data['stock_quantity'];
            $data['stock_quantity'] = 0;
            $product = Product::create($data);

            if ($initialStock > 0) {
                StockInventory::receive(
                    $product,
                    $initialStock,
                    (float) $product->purchase_price,
                    'INITIAL-'.$product->reference,
                );

                $product->movements()->create([
                    'type'          => 'in',
                    'quantity'      => $initialStock,
                    'stock_before'  => 0,
                    'stock_after'   => $initialStock,
                    'unit_price'    => $product->purchase_price,
                    'reason'        => 'Stock initial',
                    'user_id'       => auth()->id(),
                    'movement_date' => now(),
                ]);
            }

            return $product;
        });

        return redirect()->route('products.index')->with('success', 'Parfum créé avec succès.');
    }

    public function show(Product $product)
    {
        $product->load(['brand','category']);
        $product->load(['batches' => fn ($query) => $query->where('quantity', '>', 0)->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')->orderBy('expires_at')]);
        $movements = $product->movements()->with('user')->latest()->paginate(20);
        return view('products.show', compact('product','movements'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product'    => $product,
            'brands'     => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product->id);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($product, $data) {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $before = $product->stock_quantity;
            $desiredStock = $data['stock_quantity'];
            unset($data['stock_quantity']);
            $product->update($data);
            StockInventory::setQuantity($product, $desiredStock, 'stock_quantity');
            $after = $product->fresh()->stock_quantity;

            if ($before !== $after) {
                $product->movements()->create([
                    'type'          => 'adjustment',
                    'quantity'      => $after - $before,
                    'stock_before'  => $before,
                    'stock_after'   => $after,
                    'unit_price'    => $product->purchase_price,
                    'reason'        => 'Ajustement depuis la fiche parfum',
                    'user_id'       => auth()->id(),
                    'movement_date' => now(),
                ]);
            }
        });

        return redirect()->route('products.index')->with('success', 'Parfum mis à jour.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Parfum supprimé.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'            => 'required|string|max:255',
            'reference'       => 'nullable|string|max:100|unique:products,reference,'.($ignoreId ?? 'NULL'),
            'barcode'         => 'nullable|string|max:100|unique:products,barcode,'.($ignoreId ?? 'NULL'),
            'brand_id'        => 'nullable|exists:brands,id',
            'category_id'     => 'nullable|exists:categories,id',
            'gender'          => 'required|in:homme,femme,unisexe',
            'volume'          => 'required|integer|min:1',
            'concentration'   => ['nullable', Rule::in(array_keys(Product::CONCENTRATIONS))],
            'purchase_price'  => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'promo_price'     => 'nullable|numeric|min:0|lte:selling_price',
            'stock_quantity'  => 'required|integer|min:0',
            'alert_threshold' => 'required|integer|min:0',
            'image'           => 'nullable|image|max:2048',
            'description'     => 'nullable|string',
            'is_active'       => 'nullable|boolean',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}