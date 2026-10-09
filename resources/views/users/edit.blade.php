@extends('layouts.app')
@section('title', 'Modifier '.$user->name)

@section('content')
<form method="POST" action="{{ route('users.update', $user) }}" class="max-w-xl bg-white rounded-xl border border-slate-200 p-6 space-y-4">
    @csrf @method('PUT')
    <div><label class="text-sm font-medium">Nom *</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
    <div><label class="text-sm font-medium">Email *</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
    <div><label class="text-sm font-medium">Nouveau mot de passe (laisser vide)</label>
        <input type="password" name="password" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
    <div><label class="text-sm font-medium">Confirmer</label>
        <input type="password" name="password_confirmation" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
    <div><label class="text-sm font-medium">Rôle *</label>
        <select name="role" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            @foreach($roles as $r)
                <option value="{{ $r->name }}" @selected($user->hasRole($r->name))>{{ $r->name }}</option>
            @endforeach
        </select></div>
    <div class="flex gap-2 pt-2">
        <button class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">Mettre à jour</button>
        <a href="{{ route('users.index') }}" class="px-5 py-2 bg-slate-100 rounded-lg text-sm">Annuler</a>
    </div>
</form>
@endsection