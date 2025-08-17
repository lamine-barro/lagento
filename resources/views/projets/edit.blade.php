@extends('layouts.app')

@section('title', 'Modifier le projet')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <x-card :title="'Modifier le Projet'">
        <form method="POST" action="{{ route('projets.update', $projet) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <x-input name="nom_projet" label="Nom du projet" :value="$projet->nom_projet" required />
            <x-input name="raison_sociale" label="Raison sociale" :value="$projet->raison_sociale" />
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="input-field textarea-field" rows="5" required>{{ $projet->description }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="input-field" />
            </div>
            <x-button type="submit" variant="primary">Mettre à jour</x-button>
        </form>
    </x-card>
</div>
@endsection


