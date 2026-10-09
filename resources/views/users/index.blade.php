@extends('layouts.app')
@section('title', 'Utilisateurs')

@section('content')
<div class="flex justify-between items-center mb-4">
    <p class="text-sm text-slate-500">{{ $users->total() }} utilisateur(s)</p>
    <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">➕ Ajouter</a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr><th class="px-4 py-3 text-left">Nom</th><th class="px-4 py-3 text-left">Email</th>
                <th class="px-4 py-3 text-left">Rôle</th><th class="px-4 py-3 text-right">Actions</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($users as $u)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                    <td class="px-4 py-3">{{ $u->email }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-700">
                            {{ $u->getRoleNames()->first() ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('users.edit', $u) }}" class="p-1.5 rounded hover:bg-slate-100">✏️</a>
                        @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded hover:bg-rose-50">🗑️</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection