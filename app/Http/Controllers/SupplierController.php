<?php
namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Supplier::withCount('purchases')->withSum('purchases', 'total');
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('whatsapp', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();
        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'purchases' => (float) \App\Models\Purchase::sum('total'),
        ];

        return view('suppliers.index', compact('suppliers', 'stats', 'filters'));
    }

    public function create() { return view('suppliers.create'); }

    public function store(Request $request)
    {
        Supplier::create($this->validated($request));
        return redirect()->route('suppliers.index')->with('success', 'Fournisseur ajouté.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases' => fn($q) => $q->latest()->limit(20)]);
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier) { return view('suppliers.edit', compact('supplier')); }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request));
        return redirect()->route('suppliers.index')->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return back()->with('success', 'Fournisseur supprimé.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email'    => 'nullable|email|max:255',
            'address'  => 'nullable|string|max:255',
            'notes'    => 'nullable|string',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}