@extends('layouts.guest')

@section('title', 'Configuration du profil - Étape 1')

@section('content')
<div class="min-h-screen bg-white flex flex-col p-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <button onclick="history.back()" class="btn btn-ghost p-2">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </button>
        <div class="text-center">
            <div class="text-sm font-medium" style="color: var(--orange-primary);">Étape 1 sur 3</div>
        </div>
        <div class="w-10"></div> <!-- Spacer -->
    </div>

    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="h-2 rounded-full" style="background: var(--gray-100);">
            <div class="h-2 rounded-full transition-all duration-500" style="background: var(--orange-primary); width: 33.33%;"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 w-full" style="max-width: 720px; margin-left: auto; margin-right: auto;">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-medium mb-2" style="color: var(--gray-900);">
                <i data-lucide="badge-check" class="w-5 h-5 mr-2 align-[-2px]"></i>
                Identité de votre entreprise
            </h1>
            <p style="color: var(--gray-700);">
                Commençons par l'essentiel : qui êtes-vous et où êtes-vous ?
            </p>
        </div>

        <form id="step1-form" method="POST" action="{{ route('onboarding.step1') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Identité & Contact -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nom du projet *</label>
                    <input type="text" name="nom_projet" value="{{ old('nom_projet') }}" placeholder="Ex: Etudesk" class="input-field w-full" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Raison sociale *</label>
                    <input type="text" name="raison_sociale" value="{{ old('raison_sociale') }}" placeholder="Ex: Etudesk SAS" class="input-field w-full" required />
                    @error('raison_sociale')<p class="text-sm mt-1" style="color: var(--danger);">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Description</label>
                    <textarea name="description" rows="3" maxlength="600" placeholder="Présentez brièvement votre projet (600 caractères max)" class="input-field w-full resize-none">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Année de création *</label>
                    <select name="annee_creation" class="input-field w-full" required>
                        <option value="">Sélectionnez</option>
                        @for ($y = date('Y'); $y >= 2010; $y--)
                            <option value="{{ $y }}" {{ old('annee_creation') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Projet formalisé *</label>
                    <select name="formalise" class="input-field w-full" required>
                        <option value="">Sélectionnez</option>
                        @foreach(config('constants.FORMALISE_OPTIONS') as $key => $label)
                            <option value="{{ $key }}" {{ old('formalise')===$key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Logo (PNG/JPG, 10 Mo max)</label>
                    <input type="file" name="logo" accept=".png,.jpg,.jpeg" class="input-field w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Région *</label>
                    <select name="region" class="input-field w-full" required>
                        <option value="">Sélectionnez votre région</option>
                        @foreach(config('constants.REGIONS') as $region => $coords)
                            <option value="{{ $region }}" {{ old('region') == $region ? 'selected' : '' }}>{{ $region }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Localisation -->
            <div x-data="onboardingMap()">
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Coordonnées GPS</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input x-ref="lat" type="text" name="latitude" value="{{ old('latitude') }}" placeholder="Latitude" class="input-field w-full" />
                    <input x-ref="lng" type="text" name="longitude" value="{{ old('longitude') }}" placeholder="Longitude" class="input-field w-full" />
                    <button type="button" class="btn btn-secondary w-full" @click="centerOnInputs()">Pointer sur la carte</button>
                </div>
                <div id="map" class="mt-4" style="height: 240px; border: 1px solid var(--black); border-radius: var(--radius-md);"></div>
            </div>
        </form>
    </div>

    <!-- Footer Navigation -->
    <div class="flex justify-between items-center mt-8" x-data>
        <button onclick="history.back()" class="btn btn-ghost">
            Retour
        </button>
        <button 
            type="submit" 
            class="btn btn-primary"
            form="step1-form"
        >
            Continuer
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Minimal Mapbox init with click-to-locate
function onboardingMap() {
    return {
        map: null,
        marker: null,
        init() {
            const token = @json(config('services.mapbox.token'));
            if (!token) return;
            const scriptId = 'mapbox-gl-js';
            const cssId = 'mapbox-gl-css';
            if (!document.getElementById(scriptId)) {
                const s = document.createElement('script');
                s.id = scriptId;
                s.src = 'https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js';
                document.head.appendChild(s);
            }
            if (!document.getElementById(cssId)) {
                const l = document.createElement('link');
                l.id = cssId;
                l.rel = 'stylesheet';
                l.href = 'https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css';
                document.head.appendChild(l);
            }
            const initMap = () => {
                if (!window.mapboxgl) { setTimeout(initMap, 50); return; }
                mapboxgl.accessToken = token;
                this.map = new mapboxgl.Map({
                    container: 'map',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [-4.0083, 5.3599], // Abidjan approx
                    zoom: 9
                });
                this.map.addControl(new mapboxgl.NavigationControl());
                this.map.on('click', (e) => {
                    const { lng, lat } = e.lngLat;
                    this.$refs.lat.value = lat.toFixed(6);
                    this.$refs.lng.value = lng.toFixed(6);
                    this.placeMarker([lng, lat]);
                });
            };
            initMap();
        },
        placeMarker(lngLat) {
            if (!this.map) return;
            if (this.marker) this.marker.remove();
            this.marker = new mapboxgl.Marker().setLngLat(lngLat).addTo(this.map);
            this.map.flyTo({ center: lngLat, zoom: 12 });
        },
        centerOnInputs() {
            const lat = parseFloat(this.$refs.lat.value);
            const lng = parseFloat(this.$refs.lng.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                this.placeMarker([lng, lat]);
            }
        }
    }
}
</script>
@endpush