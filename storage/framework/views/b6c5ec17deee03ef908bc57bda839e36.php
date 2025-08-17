<?php $__env->startSection('title', 'Profil'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-white">
    <!-- Header -->
    <div class="sticky top-0 z-10 bg-white border-b p-4" style="border-color: var(--gray-100);">
        <div class="flex items-center gap-3">
            <button onclick="history.back()" class="btn btn-ghost p-2">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </button>
            <h1 class="text-xl font-medium" style="color: var(--gray-900);">Profil</h1>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="p-4 space-y-6" x-data="profileManager()">
        
        <!-- User Info Section -->
        <div class="border" style="border-color: var(--black); border-radius: var(--radius-md);">
            <div class="p-4 border-b" style="border-color: var(--black);">
                <h2 class="font-medium" style="color: var(--gray-900);">Informations personnelles</h2>
            </div>
            
            <form @submit.prevent="updateProfile" class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Nom complet
                    </label>
                    <input 
                        type="text"
                        x-model="profile.name"
                        class="input-field w-full"
                        required
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Email
                    </label>
                    <input 
                        type="email"
                        x-model="profile.email"
                        class="input-field w-full"
                        readonly
                        style="background: var(--gray-100);"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Téléphone
                    </label>
                    <input 
                        type="tel"
                        x-model="profile.phone"
                        placeholder="+225 XX XX XX XX XX"
                        class="input-field w-full"
                    />
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Mettre à jour
                </button>
            </form>
        </div>

        <!-- Company Info Section -->
        <div class="border" style="border-color: var(--black); border-radius: var(--radius-md);">
            <div class="p-4 border-b" style="border-color: var(--black);">
                <h2 class="font-medium" style="color: var(--gray-900);">Entreprise</h2>
            </div>
            
            <form @submit.prevent="updateCompany" class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Nom de l'entreprise
                    </label>
                    <input 
                        type="text"
                        x-model="profile.company_name"
                        class="input-field w-full"
                        required
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Secteur d'activité
                    </label>
                    <select x-model="profile.business_sector" class="input-field w-full">
                        <option value="">Sélectionnez votre secteur</option>
                        <?php $__currentLoopData = config('constants.SECTEURS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Stade de développement
                    </label>
                    <select x-model="profile.business_stage" class="input-field w-full">
                        <option value="">Sélectionnez votre stade</option>
                        <?php $__currentLoopData = config('constants.STADES_MATURITE'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Taille de l'équipe
                    </label>
                    <select x-model="profile.team_size" class="input-field w-full">
                        <option value="">Sélectionnez la taille</option>
                        <?php $__currentLoopData = config('constants.TEAM_SIZES'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($size); ?>"><?php echo e($size); ?> personnes</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Mettre à jour
                </button>
            </form>
        </div>

        <!-- Project Section -->
        <div class="border" style="border-color: var(--black); border-radius: var(--radius-md);">
            <div class="p-4 border-b" style="border-color: var(--black);">
                <h2 class="font-medium" style="color: var(--gray-900);">Projet</h2>
            </div>
            
            <form @submit.prevent="updateProject" class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Description du projet
                    </label>
                    <textarea 
                        x-model="project.description"
                        rows="4"
                        placeholder="Décrivez votre projet en quelques lignes..."
                        class="input-field w-full resize-none"
                    ></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Marché cible
                    </label>
                    <select x-model="project.target_market" class="input-field w-full">
                        <option value="">Sélectionnez votre marché</option>
                        <?php $__currentLoopData = config('constants.CIBLES'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Modèle de revenus
                    </label>
                    <select x-model="project.revenue_model" class="input-field w-full">
                        <option value="">Sélectionnez votre modèle</option>
                        <?php $__currentLoopData = config('constants.MODELES_REVENUS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Mettre à jour
                </button>
            </form>
        </div>

        <!-- Data Sources Section -->
        <div class="border" style="border-color: var(--black); border-radius: var(--radius-md);">
            <div class="p-4 border-b" style="border-color: var(--black);">
                <h2 class="font-medium" style="color: var(--gray-900);">Sources de données</h2>
            </div>
            
            <div class="p-4 space-y-3">
                <div class="flex items-center justify-between p-3 border" style="border-color: var(--black); border-radius: var(--radius-md);">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-text" class="w-5 h-5" style="color: var(--gray-500);"></i>
                        <div>
                            <div class="text-sm font-medium" style="color: var(--gray-900);">Documents d'entreprise</div>
                            <div class="text-xs" style="color: var(--gray-500);">Statuts, bilans, etc.</div>
                        </div>
                    </div>
                    <button @click="$dispatch('open-documents-modal')" class="btn btn-ghost btn-sm">
                        Gérer
                    </button>
                </div>
                
                <div class="flex items-center justify-between p-3 border" style="border-color: var(--black); border-radius: var(--radius-md);">
                    <div class="flex items-center gap-3">
                        <i data-lucide="link" class="w-5 h-5" style="color: var(--gray-500);"></i>
                        <div>
                            <div class="text-sm font-medium" style="color: var(--gray-900);">Intégrations</div>
                            <div class="text-xs" style="color: var(--gray-500);">API, webhooks</div>
                        </div>
                    </div>
                    <button @click="$dispatch('open-integrations-modal')" class="btn btn-ghost btn-sm">
                        Configurer
                    </button>
                </div>
            </div>
        </div>

        <!-- Privacy Section -->
        <div class="border" style="border-color: var(--black); border-radius: var(--radius-md);">
            <div class="p-4 border-b" style="border-color: var(--black);">
                <h2 class="font-medium" style="color: var(--gray-900);">Confidentialité</h2>
            </div>
            
            <div class="p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium" style="color: var(--gray-900);">Profil public</div>
                        <div class="text-xs" style="color: var(--gray-500);">Permettre aux autres utilisateurs de voir votre profil</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="profile.is_public" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium" style="color: var(--gray-900);">Notifications email</div>
                        <div class="text-xs" style="color: var(--gray-500);">Recevoir des notifications par email</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="profile.email_notifications" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                    </label>
                </div>
                
                <button @click="updatePrivacy()" class="btn btn-primary">
                    Sauvegarder les préférences
                </button>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="border" style="border-color: var(--danger); border-radius: var(--radius-md);">
            <div class="p-4 border-b" style="border-color: var(--danger);">
                <h2 class="font-medium" style="color: var(--danger);">Zone de danger</h2>
            </div>
            
            <div class="p-4 space-y-4">
                <div>
                    <h3 class="text-sm font-medium mb-2" style="color: var(--gray-900);">Supprimer mon compte</h3>
                    <p class="text-xs mb-3" style="color: var(--gray-500);">
                        Cette action est irréversible. Toutes vos données seront définitivement supprimées.
                    </p>
                    <button 
                        @click="$dispatch('open-delete-account-modal')"
                        class="btn btn-sm"
                        style="background: var(--danger); color: white;"
                    >
                        Supprimer mon compte
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div x-data="{ open: false }" 
     @open-delete-account-modal.window="open = true"
     x-show="open" 
     class="fixed inset-0 z-50" 
     style="display: none;">
    
    <div class="modal-backdrop" @click="open = false">
        <div @click.stop class="modal max-w-md">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <i data-lucide="alert-triangle" class="w-6 h-6" style="color: var(--danger);"></i>
                    <h3 class="text-lg font-medium" style="color: var(--gray-900);">
                        Supprimer le compte
                    </h3>
                </div>
                
                <p class="text-sm mb-4" style="color: var(--gray-700);">
                    Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible et toutes vos données seront définitivement supprimées.
                </p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                        Tapez "SUPPRIMER" pour confirmer
                    </label>
                    <input 
                        type="text"
                        x-model="confirmText"
                        placeholder="SUPPRIMER"
                        class="input-field w-full"
                    />
                </div>
                
                <div class="flex gap-3">
                    <button 
                        type="button"
                        @click="open = false"
                        class="btn btn-ghost flex-1"
                    >
                        Annuler
                    </button>
                    <button 
                        type="button"
                        @click="deleteAccount()"
                        :disabled="confirmText !== 'SUPPRIMER'"
                        class="btn flex-1"
                        style="background: var(--danger); color: white;"
                        :style="confirmText !== 'SUPPRIMER' ? 'opacity: 0.5; cursor: not-allowed;' : ''"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function profileManager() {
    return {
        profile: <?php echo json_encode($user ?? [], 15, 512) ?>,
        project: <?php echo json_encode($project ?? [], 15, 512) ?>,
        confirmText: '',
        
        updateProfile() {
            fetch('<?php echo e(route("profile.update")); ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.profile)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profil mis à jour avec succès');
                }
            });
        },
        
        updateCompany() {
            this.updateProfile();
        },
        
        updateProject() {
            fetch('<?php echo e(route("profile.project.update")); ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.project)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Projet mis à jour avec succès');
                }
            });
        },
        
        updatePrivacy() {
            this.updateProfile();
        },
        
        deleteAccount() {
            if (this.confirmText === 'SUPPRIMER') {
                fetch('<?php echo e(route("profile.delete")); ?>', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json'
                    }
                })
                .then(() => {
                    window.location.href = '<?php echo e(route("landing")); ?>';
                });
            }
        }
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/profile.blade.php ENDPATH**/ ?>