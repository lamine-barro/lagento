@extends('layouts.app')

@section('title', 'Mes projets')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-medium" style="color: var(--gray-900);">Mes projets</h2>
        <a href="{{ route('projets.create') }}" class="btn btn-primary">Nouveau projet</a>
    </div>

    @if($projets->isEmpty())
        <x-card title="Aucun projet">
            <p class="text-sm" style="color: var(--gray-700);">Vous n'avez pas encore créé de projet.</p>
        </x-card>
    @else
        <div class="grid gap-4">
        @foreach($projets as $projet)
            <x-card :title="$projet->nom_projet" :subtitle="$projet->region">
                <p class="text-sm" style="color: var(--gray-700);">{{ Str::limit($projet->description, 160) }}</p>
                <div class="mt-3">
                    <a href="{{ route('projets.show', $projet) }}" class="btn btn-ghost">Voir</a>
                    <a href="{{ route('projets.edit', $projet) }}" class="btn btn-secondary">Modifier</a>
                </div>
            </x-card>
        @endforeach
        </div>

        <div class="mt-4">
            {{ $projets->links() }}
        </div>
    @endif
</div>
@endsection


