@extends('layouts.guest')

@section('title', 'Configuration du profil - Étape 4')

@section('content')
<div class="min-h-screen bg-white flex flex-col p-4">
    <x-onboarding.header :current-step="4" />

    <!-- Main Content -->
    <div class="flex-1 w-full max-w-4xl mx-auto">

        <form id="step4-form" method="POST" action="{{ route('onboarding.step4') }}" class="space-y-6 mt-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre de fondateurs -->
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de fondateurs *</label>
                    <div class="flex items-center border rounded-lg" style="border-color: var(--gray-300);" x-data="{ count: {{ old('founders_count', 1) }} }">
                        <div class="flex items-center gap-3 px-3 py-2 flex-1">
                            <i data-lucide="users" class="w-4 h-4" style="color: var(--gray-500); stroke-width: 1.25;"></i>
                            <span x-text="count" class="text-lg font-medium" style="color: var(--gray-900);"></span>
                        </div>
                        <div class="flex">
                            <button type="button" @click="if(count > 1) count--" class="p-2 hover:bg-gray-100 transition-colors border-l" style="border-color: var(--gray-300);">
                                <i data-lucide="minus" class="w-4 h-4" style="color: var(--gray-600); stroke-width: 1.25;"></i>
                            </button>
                            <button type="button" @click="count++" class="p-2 hover:bg-gray-100 transition-colors border-l" style="border-color: var(--gray-300);">
                                <i data-lucide="plus" class="w-4 h-4" style="color: var(--gray-600); stroke-width: 1.25;"></i>
                            </button>
                        </div>
                        <input type="hidden" name="founders_count" x-model="count" />
                    </div>
                </div>

                <!-- Nombre de fondatrices -->
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de femmes fondatrices *</label>
                    <div class="flex items-center border rounded-lg" style="border-color: var(--gray-300);" x-data="{ count: {{ old('female_founders_count', 0) }} }">
                        <div class="flex items-center gap-3 px-3 py-2 flex-1">
                            <i data-lucide="user-check" class="w-4 h-4" style="color: var(--gray-500); stroke-width: 1.25;"></i>
                            <span x-text="count" class="text-lg font-medium" style="color: var(--gray-900);"></span>
                        </div>
                        <div class="flex">
                            <button type="button" @click="if(count > 0) count--" class="p-2 hover:bg-gray-100 transition-colors border-l" style="border-color: var(--gray-300);">
                                <i data-lucide="minus" class="w-4 h-4" style="color: var(--gray-600); stroke-width: 1.25;"></i>
                            </button>
                            <button type="button" @click="count++" class="p-2 hover:bg-gray-100 transition-colors border-l" style="border-color: var(--gray-300);">
                                <i data-lucide="plus" class="w-4 h-4" style="color: var(--gray-600); stroke-width: 1.25;"></i>
                            </button>
                        </div>
                        <input type="hidden" name="female_founders_count" x-model="count" />
                    </div>
                    <p class="text-xs mt-1" style="color: var(--gray-500);">Doit être ≤ nombre de fondateurs</p>
                </div>
            </div>

            <!-- Tranches d'âge -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Tranches d'âge des fondateurs</label>
                <p class="text-sm mb-4" style="color: var(--gray-600);">Sélectionnez toutes les tranches d'âge qui s'appliquent</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach(config('constants.AGE_RANGES') as $a)
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="age_ranges[]" value="{{ $a }}" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm font-medium">{{ $a }} ans</span>
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
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Structures d'accompagnement</label>
                <p class="text-sm mb-4" style="color: var(--gray-600);">Avez-vous déjà bénéficié de l'accompagnement d'une structure d'appui ?</p>
                <div class="grid grid-cols-1 gap-3 max-h-80 overflow-y-auto border rounded-lg p-4" style="border-color: var(--gray-200);">
                    @foreach(config('constants.STRUCTURES_ACCOMPAGNEMENT') as $s)
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="support_structures[]" value="{{ $s }}" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm">{{ $s }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Types de soutien (max 3) -->
            <div x-data="supportSelection()">
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Quels sont vos besoins prioritaires ? (5 max)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach(config('constants.TYPES_SOUTIEN') as $key => $value)
                        <button type="button" 
                                @click="toggleSupport('{{ $key }}')"
                                :class="isSelected('{{ $key }}') ? 'bg-green-600 text-white border-green-600' : 'bg-gray-100 text-gray-700 border-gray-300 hover:border-gray-400'"
                                :disabled="!isSelected('{{ $key }}') && selected.length >= 5"
                                class="p-4 rounded-lg border-2 transition-all text-left font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ $value }}
                        </button>
                    @endforeach
                </div>
                <p class="text-sm mt-3 flex items-center gap-2" style="color: var(--gray-600);">
                    <span x-text="selected.length + '/5 besoins sélectionnés'"></span>
                    <template x-if="selected.length === 5">
                        <span class="text-orange-600 text-xs">(Maximum atteint)</span>
                    </template>
                </p>
                
                <!-- Inputs cachés pour le formulaire -->
                <template x-for="item in selected">
                    <input type="hidden" name="support_types[]" :value="item">
                </template>
            </div>

            <!-- Détails des besoins -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Détails des besoins</label>
                <textarea name="additional_info" rows="4" class="input-field w-full resize-none" placeholder="Décrivez vos besoins prioritaires..."></textarea>
            </div>
        </form>
    </div>

    <x-onboarding.footer :next-form-id="'step4-form'" next-label="Finaliser" :is-final="true" />
</div>

@push('scripts')
<script>
function supportSelection() {
    return {
        selected: [],
        
        toggleSupport(key) {
            const index = this.selected.indexOf(key);
            if (index > -1) {
                // Désélectionner
                this.selected.splice(index, 1);
            } else {
                // Sélectionner si on n'a pas atteint le maximum
                if (this.selected.length < 5) {
                    this.selected.push(key);
                }
            }
        },
        
        isSelected(key) {
            return this.selected.includes(key);
        }
    }
}
</script>
@endpush

@endsection
