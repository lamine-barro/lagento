<?php $__env->startSection('title', 'Configuration du profil - Étape 1'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-white flex flex-col p-4">
    <?php if (isset($component)) { $__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding.header','data' => ['currentStep' => 1]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['current-step' => 1]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f)): ?>
<?php $attributes = $__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f; ?>
<?php unset($__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f)): ?>
<?php $component = $__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f; ?>
<?php unset($__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f); ?>
<?php endif; ?>

    <!-- Main Content -->
    <div class="flex-1 w-full max-w-4xl mx-auto">

        <form id="step1-form" method="POST" action="<?php echo e(route('onboarding.step1.process')); ?>" enctype="multipart/form-data" class="space-y-6 mt-4">
            <?php echo csrf_field(); ?>

            <!-- Identité & Contact -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nom du projet *</label>
                    <input type="text" name="nom_projet" value="<?php echo e(old('nom_projet', $projet->nom_projet ?? '')); ?>" placeholder="Ex: Etudesk" class="input-field w-full" maxlength="100" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Raison sociale</label>
                    <input type="text" name="raison_sociale" value="<?php echo e(old('raison_sociale', $projet->raison_sociale ?? '')); ?>" placeholder="Ex: Etudesk SAS" class="input-field w-full" maxlength="120" />
                    <?php $__errorArgs = ['raison_sociale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm mt-1" style="color: var(--danger);"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Description</label>
                    <textarea name="description" rows="3" maxlength="600" placeholder="Présentez brièvement votre projet (600 caractères max)" class="input-field w-full resize-none"><?php echo e(old('description', $projet->description ?? '')); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Année de création *</label>
                    <select name="annee_creation" class="input-field w-full" required>
                        <option value="">Sélectionnez</option>
                        <?php for($y = date('Y'); $y >= 2010; $y--): ?>
                            <option value="<?php echo e($y); ?>" <?php echo e(old('annee_creation', $projet->annee_creation ?? '') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Projet formalisé *</label>
                    <select name="formalise" class="input-field w-full" required>
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.FORMALISE_OPTIONS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e(old('formalise', $projet->formalise ?? '')===$key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Logo du projet</label>
                    <div class="flex flex-col items-center justify-center w-full" x-data="logoUpload()">
                        <!-- Zone d'upload -->
                        <div @drop.prevent="handleDrop($event)" @dragover.prevent @dragenter.prevent 
                             class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-lg cursor-pointer transition-all hover:border-orange-400 hover:bg-orange-50 pt-8 pb-6"
                             :class="isDragging ? 'border-orange-500 bg-orange-50' : 'border-gray-300'"
                             @dragenter="isDragging = true" @dragleave="isDragging = false" @click="$refs.fileInput.click()">
                            
                            <!-- Aperçu de l'image -->
                            <template x-if="previewUrl">
                                <div class="flex flex-col items-center">
                                    <img :src="previewUrl" alt="Logo preview" class="w-20 h-20 object-cover rounded-lg mb-2">
                                    <p class="text-sm text-gray-600" x-text="fileName"></p>
                                </div>
                            </template>
                            
                            <!-- État initial -->
                            <template x-if="!previewUrl">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-3 mt-2">
                                        <i data-lucide="image" class="w-8 h-8" style="color: var(--gray-500); stroke-width: 1.25;"></i>
                                    </div>
                                    <div class="flex items-center gap-2 px-4 py-2 text-white rounded-md mt-4" style="background-color: var(--orange); color: #FFFFFF;">
                                        <i data-lucide="upload" class="w-4 h-4" style="stroke-width: 1.25; color: #FFFFFF;"></i>
                                        <span style="color: #FFFFFF;">Télécharger un logo</span>
                                    </div>
                                    <p class="text-sm text-gray-500 text-center">
                                        Glissez-déposez votre logo ici ou cliquez pour parcourir<br>
                                        <span class="text-xs">PNG, JPG jusqu'à 10 Mo</span>
                                    </p>
                                </div>
                            </template>
                        </div>
                        
                        <input type="file" name="logo" accept=".png,.jpg,.jpeg" x-ref="fileInput" @change="handleFileSelect($event)" class="hidden" />
                        
                        <!-- Bouton de suppression -->
                        <template x-if="previewUrl">
                            <button type="button" @click="clearFile()" class="mt-2 text-sm text-red-600 hover:text-red-800">
                                Supprimer le logo
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Localisation et Région -->
            <div x-data="onboardingMap()">
                <label class="block text-sm font-medium mb-2 mt-4" style="color: var(--gray-700);">Géolocalisation de votre projet</label>
                <p class="text-sm mb-3" style="color: var(--gray-600);">Cliquez sur la carte pour placer votre position ou utilisez le bouton de géolocalisation</p>
                <button type="button" class="btn btn-secondary w-full mb-4" @click="geolocate()">
                    <i data-lucide="crosshair" class="w-4 h-4 mr-2"></i>
                    Me localiser
                </button>
                <div id="map" class="mb-4" style="height: 260px; border-radius: var(--radius-md);"></div>
                
                <!-- Région -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Région *</label>
                    <select x-ref="regionSelect" name="region" class="input-field w-full" required @change="onRegionChange()">
                        <option value="">Sélectionnez votre région</option>
                        <?php $__currentLoopData = config('constants.REGIONS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region => $coords): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($region); ?>" data-lat="<?php echo e($coords['lat']); ?>" data-lng="<?php echo e($coords['lng']); ?>" <?php echo e(old('region', $projet->region ?? '') == $region ? 'selected' : ''); ?>><?php echo e($region); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Latitude</label>
                        <input x-ref="lat" type="text" name="latitude" value="<?php echo e(old('latitude', $projet->latitude ?? '')); ?>" placeholder="Latitude" class="input-field w-full" style="background-color: var(--gray-50); color: var(--gray-600);" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Longitude</label>
                        <input x-ref="lng" type="text" name="longitude" value="<?php echo e(old('longitude', $projet->longitude ?? '')); ?>" placeholder="Longitude" class="input-field w-full" style="background-color: var(--gray-50); color: var(--gray-600);" readonly />
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if (isset($component)) { $__componentOriginal4973fa7765c1d7ef7e43a98d4867113c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding.footer','data' => ['nextFormId' => 'step1-form','nextLabel' => 'Suivant']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['next-form-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('step1-form'),'next-label' => 'Suivant']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c)): ?>
<?php $attributes = $__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c; ?>
<?php unset($__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4973fa7765c1d7ef7e43a98d4867113c)): ?>
<?php $component = $__componentOriginal4973fa7765c1d7ef7e43a98d4867113c; ?>
<?php unset($__componentOriginal4973fa7765c1d7ef7e43a98d4867113c); ?>
<?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Logo upload component
function logoUpload() {
    return {
        isDragging: false,
        previewUrl: <?php echo json_encode($projet->logo_url ?? null, 15, 512) ?>,
        fileName: '',
        
        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.handleFile(files[0]);
            }
        },
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.handleFile(file);
            }
        },
        
        handleFile(file) {
            // Validation
            if (!file.type.startsWith('image/')) {
                alert('Veuillez sélectionner un fichier image');
                return;
            }
            
            if (file.size > 10 * 1024 * 1024) { // 10MB
                alert('Le fichier ne doit pas dépasser 10 Mo');
                return;
            }
            
            this.fileName = file.name;
            
            // Créer aperçu
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewUrl = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        
        clearFile() {
            this.previewUrl = null;
            this.fileName = '';
            this.$refs.fileInput.value = '';
        }
    }
}

// Minimal Mapbox init with click-to-locate & geolocation
function onboardingMap() {
    return {
        map: null,
        marker: null,
        regions: <?php echo json_encode(config('constants.REGIONS'), 15, 512) ?>,
        init() {
            const token = <?php echo json_encode(config('services.mapbox.token'), 15, 512) ?>;
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
                    center: [-4.024, 5.345],
                    zoom: 9,
                    attributionControl: false // Supprime les attributions
                });
                this.map.addControl(new mapboxgl.NavigationControl());
                this.map.on('click', (e) => {
                    const { lng, lat } = e.lngLat;
                    this.$refs.lat.value = lat.toFixed(6);
                    this.$refs.lng.value = lng.toFixed(6);
                    this.placeMarker([lng, lat]);
                    this.detectRegion(lat, lng);
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
        detectRegion(lat, lng) {
            // Trouver la région la plus proche
            let closestRegion = null;
            let minDistance = Infinity;
            
            for (const [region, coords] of Object.entries(this.regions)) {
                const distance = Math.sqrt(
                    Math.pow(coords.lat - lat, 2) + 
                    Math.pow(coords.lng - lng, 2)
                );
                if (distance < minDistance) {
                    minDistance = distance;
                    closestRegion = region;
                }
            }
            
            // Sélectionner la région trouvée
            if (closestRegion && this.$refs.regionSelect) {
                this.$refs.regionSelect.value = closestRegion;
            }
        },
        onRegionChange() {
            const select = this.$refs.regionSelect;
            const option = select.options[select.selectedIndex];
            if (option && option.dataset.lat && option.dataset.lng) {
                const lat = parseFloat(option.dataset.lat);
                const lng = parseFloat(option.dataset.lng);
                this.$refs.lat.value = lat.toFixed(6);
                this.$refs.lng.value = lng.toFixed(6);
                this.placeMarker([lng, lat]);
            }
        },
        geolocate() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition((pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                this.$refs.lat.value = lat.toFixed(6);
                this.$refs.lng.value = lng.toFixed(6);
                this.placeMarker([lng, lat]);
                this.detectRegion(lat, lng);
            });
        }
    }
}
</script>

<style>
/* Masquer toutes les attributions Mapbox */
.mapboxgl-ctrl-attrib,
.mapboxgl-ctrl-logo {
    display: none !important;
}

.mapboxgl-ctrl-bottom-right,
.mapboxgl-ctrl-bottom-left {
    display: none !important;
}
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/onboarding/step1.blade.php ENDPATH**/ ?>