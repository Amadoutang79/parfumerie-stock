@extends('layouts.app')
@section('title', 'Modifier '.$supplier->name)

@section('content')
<form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 space-y-4">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="text-sm font-medium">Nom *</label>
            <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
        <div><label class="text-sm font-medium">Téléphone</label>
            <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
        <div><label class="text-sm font-medium">WhatsApp</label>
            <input type="text" name="whatsapp" value="{{ old('whatsapp', $supplier->whatsapp) }}" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
        <div><label class="text-sm font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
    </div>
    <div><label class="text-sm font-medium">Adresse</label>
        <input type="text" name="address" value="{{ old('address', $supplier->address) }}" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
    <div><label class="text-sm font-medium">Notes</label>
        <textarea name="notes" rows="3" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('notes', $supplier->notes) }}</textarea></div>
    <div class="flex gap-2 pt-2">
        <button class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">Mettre à jour</button>
        <a href="{{ route('suppliers.index') }}" class="px-5 py-2 bg-slate-100 rounded-lg text-sm">Annuler</a>
    </div>
</form>
@endsection