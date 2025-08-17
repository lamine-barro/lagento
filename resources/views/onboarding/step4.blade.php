@extends('layouts.guest')

@section('title', 'Configuration du profil - Étape 4')

@section('content')
<div class="min-h-screen bg-white flex flex-col p-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <button onclick="history.back()" class="btn btn-ghost p-2">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </button>
        <div class="text-center">
            <div class="text-sm font-medium" style="color: var(--orange-primary);">Étape 4 sur 4</div>
        </div>
        <div class="w-10"></div>
    </div>

    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="h-2 rounded-full" style="background: var(--gray-100);">
            <div class="h-2 rounded-full transition-all duration-500" style="background: var(--orange-primary); width: 100%;"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 w-full" style="max-width: 720px; margin-left: auto; margin-right: auto;">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-medium mb-2" style="color: var(--gray-900);">
                <i data-lucide="users" class="w-5 h-5 mr-2 align-[-2px]"></i>
                Équipe & Accompagnement
            </h1>
            <p style="color: var(--gray-700);">Composition de l'équipe et besoins de soutien</p>
        </div>

        <form method="POST" action="{{ route('onboarding.step4') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de fondateurs *</label>
                    <input type="number" min="1" step="1" name="founders_count" value="{{ old('founders_count', 1) }}" class="input-field w-full" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de fondatrices *</label>
                    <input type="number" min="0" step="1" name="female_founders_count" value="{{ old('female_founders_count', 0) }}" class="input-field w-full" required />
                    <p class="text-xs mt-1" style="color: var(--gray-500);">Doit être ≤ nombre de fondateurs</p>
                </div>
            </div>

            <!-- Tranches d'âge -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Tranches d'âge des fondateurs</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach(config('constants.AGE_RANGES') as $a)
                        <label class="flex items-center">
                            <input type="checkbox" name="age_ranges[]" value="{{ $a }}" class="w-4 h-4 mr-3" style="accent-color: var(--orange-primary);">
                            <span>{{ $a }} ans</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Localisation des fondateurs</label>
                    <select name="founders_location" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        @foreach(config('constants.LOCALISATION_FONDATEURS') as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Taille totale de l'équipe</label>
                    <select name="team_size" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        @foreach(config('constants.TEAM_SIZES') as $size)
                            <option value="{{ $size }}">{{ $size }} personnes</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Structures d'accompagnement -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Structures d'accompagnement existants</label>
                <div class="space-y-2">
                    @foreach(config('constants.STRUCTURES_ACCOMPAGNEMENT') as $s)
                        <label class="flex items-center">
                            <input type="checkbox" name="support_structures[]" value="{{ $s }}" class="w-4 h-4 mr-3" style="accent-color: var(--orange-primary);">
                            <span>{{ $s }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Types de soutien (max 3) -->
            <div x-data="{ selected: [] }">
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Types de soutien nécessaires (max 3)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach(config('constants.TYPES_SOUTIEN') as $key => $value)
                        <label class="flex items-center">
                            <input type="checkbox" name="support_types[]" value="{{ $key }}" class="w-4 h-4 mr-3" style="accent-color: var(--orange-primary);" @change="(e)=>{ if(e.target.checked && selected.length>=3){ e.target.checked=false; } else { selected = Array.from(document.querySelectorAll('input[name=\\'support_types[]\\']:checked')).map(i=>i.value) } }">
                            <span>{{ $value }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs mt-1" style="color: var(--gray-500);" x-text="selected.length + ' / 3 sélectionnés'"></p>
            </div>

            <!-- Détails des besoins -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Détails des besoins</label>
                <textarea name="additional_info" rows="4" class="input-field w-full resize-none" placeholder="Décrivez vos besoins prioritaires..."></textarea>
            </div>
        </form>
    </div>

    <!-- Footer Navigation -->
    <div class="flex justify-between items-center mt-8">
        <button onclick="history.back()" class="btn btn-ghost">Retour</button>
        <button type="submit" class="btn btn-primary" onclick="document.querySelector('form').submit()">Finaliser</button>
    </div>
</div>
@endsection
