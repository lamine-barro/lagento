@extends('layouts.guest')

@section('title', 'Configuration du profil - Étape 4')

@section('content')
<div class="min-h-screen bg-white flex flex-col p-4">
    <x-onboarding.header :current-step="4" />

    <!-- Main Content -->
    <div class="flex-1 w-full max-w-4xl mx-auto">

        <form id="step4-form" method="POST" action="{{ route('onboarding.step4.process') }}" class="space-y-6 mt-4">
            @csrf

            <!-- Debug des erreurs -->
            @if(config('app.debug'))
                <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; font-family: monospace; font-size: 12px;">
                    Debug: Errors count = {{ $errors->count() }}<br>
                    All errors: {{ json_encode($errors->all()) }}<br>
                    Session errors: {{ json_encode(session()->get('errors')) }}
                </div>
            @endif

            <!-- Alertes d'erreurs -->
            @if ($errors->any())
                <div class="alert alert-error" style="display: block !important; background-color: #FEF2F2 !important; border-left: 4px solid #DC2626 !important; color: #B91C1C !important; padding: 16px !important; border-radius: 8px !important; margin: 16px 0 !important;">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <div>
                        <strong>Erreurs de validation</strong>
                        <ul class="mt-2 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ founders: {{ old('founders_count', $projet->nombre_fondateurs ?? 1) }}, female: {{ old('female_founders_count', $projet->nombre_fondatrices ?? 0) }}, decFounders() { if (this.founders > 1) { this.founders--; if (this.female > this.founders) this.female = this.founders; } }, incFounders() { this.founders++; }, decFemale() { if (this.female > 0) this.female--; }, incFemale() { if (this.female < this.founders) this.female++; } }" x-init="if (female > founders) female = founders">
                <!-- Nombre de fondateurs -->
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de fondateurs *</label>
                    <div class="flex items-center border rounded-lg" style="border-color: var(--gray-300);">
                        <div class="flex items-center px-4 py-2 flex-1">
                            <span x-text="founders" class="text-base font-medium" style="color: var(--gray-900);"></span>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" @click="decFounders()" class="px-4 py-2 transition-colors border-l text-gray-700 hover:text-orange-600 active:text-orange-700" style="border-color: var(--gray-300);">
                                <span class="text-base">−</span>
                            </button>
                            <button type="button" @click="incFounders()" class="px-4 py-2 transition-colors border-l text-gray-700 hover:text-orange-600 active:text-orange-700" style="border-color: var(--gray-300);">
                                <span class="text-base">+</span>
                            </button>
                        </div>
                        <input type="hidden" name="founders_count" x-model="founders" />
                    </div>
                </div>

                <!-- Nombre de fondatrices -->
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de femmes fondatrices *</label>
                    <div class="flex items-center border rounded-lg" style="border-color: var(--gray-300);">
                        <div class="flex items-center px-4 py-2 flex-1">
                            <span x-text="female" class="text-base font-medium" style="color: var(--gray-900);"></span>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" @click="decFemale()" class="px-4 py-2 transition-colors border-l text-gray-700 hover:text-orange-600 active:text-orange-700" style="border-color: var(--gray-300);">
                                <span class="text-base">−</span>
                            </button>
                            <button type="button" @click="incFemale()" class="px-4 py-2 transition-colors border-l text-gray-700 hover:text-orange-600 active:text-orange-700" style="border-color: var(--gray-300);">
                                <span class="text-base">+</span>
                            </button>
                        </div>
                        <input type="hidden" name="female_founders_count" x-model="female" />
                    </div>
                </div>
            </div>

            <!-- Tranches d'âge -->
            <div>
                <label class="block text-sm font-medium mb-2 mt-4" style="color: var(--gray-700);">Tranches d'âge des fondateurs</label>
                <p class="text-sm mb-4" style="color: var(--gray-600);">Sélectionnez toutes les tranches d'âge qui s'appliquent</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" x-data="checkboxLimit(5, 'age_ranges[]')" x-init="updateDisabled()" @change="updateDisabled()">
                    @foreach(config('constants.AGE_RANGES') as $a)
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-orange-50 has-[:checked]:bg-orange-50 has-[:checked]:border-orange-500" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="age_ranges[]" value="{{ $a }}" class="w-4 h-4 rounded" style="accent-color: var(--orange);" @change="onChange($event)" {{ in_array($a, old('age_ranges', $projet->tranches_age_fondateurs ?? [])) ? 'checked' : '' }}>
                            <span class="text-sm font-medium">{{ $a }} ans</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Localisation des fondateurs</label>
                    <select name="founders_location" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        @foreach(config('constants.LOCALISATION_FONDATEURS') as $key => $label)
                            <option value="{{ $key }}" {{ old('founders_location', $projet->localisation_fondateurs ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Taille totale de l'équipe</label>
                    <select name="team_size" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        @foreach(config('constants.TEAM_SIZES') as $size)
                            <option value="{{ $size }}" {{ old('team_size', $projet->taille_equipe ?? '') == $size ? 'selected' : '' }}>{{ $size }} personnes</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Structures d'accompagnement -->
            <div>
                <label class="block text-sm font-medium mb-2 mt-4" style="color: var(--gray-700);">Structures d'accompagnement (max 5)</label>
                <p class="text-sm mb-4" style="color: var(--gray-600);">Avez-vous déjà bénéficié de l'accompagnement d'une structure d'appui ?</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" x-data="checkboxLimit(5, 'support_structures[]')" x-init="updateDisabled()" @change="updateDisabled()">
                    @foreach(config('constants.STRUCTURES_ACCOMPAGNEMENT') as $s)
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-orange-50 has-[:checked]:bg-orange-50 has-[:checked]:border-orange-500" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="support_structures[]" value="{{ $s }}" class="w-4 h-4 rounded" style="accent-color: var(--orange);" {{ in_array($s, old('support_structures', $projet->structures_accompagnement ?? [])) ? 'checked' : '' }}>
                            <span class="text-sm font-medium">{{ $s }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Types de soutien (max 5) -->
            <div>
                <label class="block text-sm font-medium mb-2 mt-4" style="color: var(--gray-700);">Quels sont vos besoins prioritaires ? (5 max)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" x-data="checkboxLimit(5, 'support_types[]')" x-init="updateDisabled()" @change="updateDisabled()">
                    @foreach(config('constants.TYPES_SOUTIEN') as $key => $value)
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-orange-50 has-[:checked]:bg-orange-50 has-[:checked]:border-orange-500" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="support_types[]" value="{{ $key }}" class="w-4 h-4 rounded" style="accent-color: var(--orange);" {{ in_array($key, old('support_types', $projet->types_soutien ?? [])) ? 'checked' : '' }}>
                            <span class="text-sm font-medium">{{ $value }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Détails des besoins -->
            <div>
                <label class="block text-sm font-medium mb-2 mt-4" style="color: var(--gray-700);">Détails des besoins</label>
                <textarea name="additional_info" rows="4" class="input-field w-full resize-none" placeholder="Décrivez vos besoins prioritaires..." maxlength="800">{{ old('additional_info', $projet->details_besoins ?? '') }}</textarea>
            </div>

            <!-- Bouton dans le formulaire -->
            <div class="flex justify-between items-center mt-12 pt-6">
                <div class="w-full max-w-4xl mx-auto flex justify-between items-center gap-4 mt-4">
                    <a href="{{ route('onboarding.step3') }}" class="btn btn-ghost">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                        Retour
                    </a>
                    
                    <button type="button" class="btn btn-primary" onclick="submitForm(this)">
                        <span id="btn-text">Finaliser</span>
                        <i data-lucide="check" class="w-4 h-4 ml-1" id="btn-icon"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function submitForm(button) {
    button.disabled = true;
    button.style.opacity = '0.7';
    document.getElementById('btn-text').textContent = 'Finalisation...';
    document.getElementById('btn-icon').style.display = 'none';
    document.getElementById('step4-form').submit();
}

function checkboxLimit(max, nameAttr) {
    return {
        max: max,
        nameAttr: nameAttr,
        init() {
            this.updateDisabled();
        },
        updateDisabled() {
            const inputs = this.$el.querySelectorAll(`input[type="checkbox"][name="${this.nameAttr}"]`);
            const checkedCount = Array.from(inputs).filter(i => i.checked).length;
            const shouldDisableOthers = checkedCount >= this.max;
            inputs.forEach(input => {
                if (!input.checked) {
                    input.disabled = shouldDisableOthers;
                } else {
                    input.disabled = false;
                }
            });
        },
        onChange() {
            this.updateDisabled();
        }
    }
}
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
