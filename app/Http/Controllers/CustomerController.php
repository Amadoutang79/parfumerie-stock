<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Customer::withCount('sales')->withSum('sales', 'total');

        if ($s = $filters['search'] ?? null) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"));
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();
        $stats = [
            'total' => Customer::count(),
            'sales' => (int) \App\Models\Sale::count(),
            'revenue' => (float) \App\Models\Sale::sum('total'),
        ];
        return view('customers.index', compact('customers', 'stats', 'filters'));
    }

    public function store(Request $request)
    {
        Customer::create($this->validated($request));
        return back()->with('success', 'Client ajouté.');
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));
        return back()->with('success', 'Client mis à jour.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales' => fn($q) => $q->latest('sold_at')->limit(30)]);
        return view('customers.show', compact('customer'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('success', 'Client supprimé.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'notes'   => 'nullable|string',
        ]);
    }
}