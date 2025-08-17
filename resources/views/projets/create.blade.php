@extends('layouts.app')

@section('title', 'Créer un projet')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <x-card :title="'Nouveau Projet'">
        <form method="POST" action="{{ route('projets.store') }}" enctype="multipart/form-data">
            @csrf
            <x-input name="nom_projet" label="Nom du projet" required />
            <x-input name="raison_sociale" label="Raison sociale" />
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="input-field textarea-field" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="input-field" />
            </div>
            <x-button type="submit" variant="primary">Enregistrer</x-button>
        </form>
    </x-card>
    
    @if ($errors->any())
        <div class="alert alert-danger mt-4">
            <i data-lucide="x-circle" class="w-5 h-5"></i>
            <div>
                <strong>Veuillez corriger les erreurs du formulaire.</strong>
            </div>
        </div>
    @endif
</div>
@endsection


