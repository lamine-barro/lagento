@extends('layouts.guest')

@section('title', 'Configuration du profil - Étape 3')

@section('content')
<div class="min-h-screen bg-white flex flex-col p-4">
    <x-onboarding.header :current-step="3" />

    <!-- Main Content -->
    <div class="flex-1 w-full max-w-4xl mx-auto">

        <form id="step3-form" method="POST" action="{{ route('onboarding.step3') }}" class="space-y-6 mt-4">
            @csrf
        
            <!-- Secteurs d'activité (max 5) -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Secteurs d'activité (max 5)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach(config('constants.SECTEURS') as $key => $value)
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="secteurs[]" value="{{ $key }}" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm font-medium">{{ $value }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Produits/Services (100 mots max) -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Produits/Services proposés (100 mots max)</label>
                <textarea name="produits_services" rows="3" class="input-field w-full resize-none" placeholder="Décrivez vos offres en 100 mots maximum"></textarea>
            </div>

            <!-- Clients cibles -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Clients cibles</label>
                <div class="grid grid-cols-1 gap-3">
                    @foreach(config('constants.CIBLES') as $key => $value)
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="cibles[]" value="{{ $key }}" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm font-medium">{{ $value }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Maturité & Financement & Revenus -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Maturité du projet</label>
                    <select name="maturite" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        @foreach(config('constants.STADES_MATURITE') as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Stade de financement</label>
                    <select name="stade_financement" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        @foreach(config('constants.STADES_FINANCEMENT') as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Revenus actuels</label>
                    <select name="revenus" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        @foreach(config('constants.TRANCHES_REVENUS') as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Modèles de revenus (max 5) -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Modèles de revenus (max 5)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach(config('constants.MODELES_REVENUS') as $key => $value)
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="modeles_revenus[]" value="{{ $key }}" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm font-medium">{{ $value }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    <x-onboarding.footer :next-form-id="'step3-form'" next-label="Suivant" />
</div>
@endsection