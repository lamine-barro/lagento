<?php $__env->startSection('title', 'Configuration du profil - Étape 2'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-white flex flex-col p-4">
    <?php if (isset($component)) { $__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding.header','data' => ['currentStep' => 2]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['current-step' => 2]); ?>
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

        <form id="step2-form" method="POST" action="<?php echo e(route('onboarding.step2.process')); ?>" class="space-y-6 mt-4">
            <?php echo csrf_field(); ?>

            <!-- Alertes d'erreurs -->
            <?php if($errors->any()): ?>
                <div class="alert alert-error">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <div>
                        <strong>Erreurs de validation</strong>
                        <ul class="mt-2 space-y-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Contact principal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Téléphone -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="phone" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Téléphone *
                    </label>
                    <input type="tel" name="telephone" value="<?php echo e(old('telephone', $projet->telephone ?? '')); ?>" placeholder="Ex: +225 07 00 00 00" class="input-field w-full" maxlength="20" required />
                </div>
                
                <!-- Email -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="mail" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Email *
                    </label>
                    <input type="email" name="email" value="<?php echo e(old('email', $projet->email ?? auth()->user()->email)); ?>" class="input-field w-full" maxlength="190" required />
                </div>
                
                <!-- Nom & prénom du représentant -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="user" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Nom & prénom du représentant *
                    </label>
                    <input type="text" name="nom_representant" value="<?php echo e(old('nom_representant', $projet->nom_representant ?? '')); ?>" placeholder="Ex: Jean Kouassi" class="input-field w-full" maxlength="120" required />
                </div>
                
                <!-- Position du représentant -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="briefcase" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Position du représentant *
                    </label>
                    <input type="text" name="role_representant" value="<?php echo e(old('role_representant', $projet->role_representant ?? '')); ?>" placeholder="Ex: PDG, Directeur" class="input-field w-full" maxlength="80" required />
                </div>
                
                <!-- Site web -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="globe" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Site web
                    </label>
                    <input type="url" name="site_web" value="<?php echo e(old('site_web', $projet->site_web ?? '')); ?>" placeholder="https://monsite.com" class="input-field w-full" maxlength="200" />
                </div>
                
                <!-- WhatsApp -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="message-circle" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        WhatsApp Business
                    </label>
                    <input type="url" name="reseaux_whatsapp" value="<?php echo e(old('reseaux_whatsapp', $projet->reseaux_sociaux['whatsapp_business'] ?? '')); ?>" placeholder="https://wa.me/22507000000" class="input-field w-full" maxlength="200" />
                </div>
            </div>

            <!-- Réseaux sociaux -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <!-- LinkedIn -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="linkedin" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            LinkedIn
                        </label>
                        <input type="url" name="reseaux_linkedin" value="<?php echo e(old('reseaux_linkedin', $projet->reseaux_sociaux['linkedin'] ?? '')); ?>" placeholder="https://linkedin.com/in/votre-profil" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- Facebook -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="facebook" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            Facebook
                        </label>
                        <input type="url" name="reseaux_facebook" value="<?php echo e(old('reseaux_facebook', $projet->reseaux_sociaux['facebook'] ?? '')); ?>" placeholder="https://facebook.com/votre-page" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- YouTube -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="youtube" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            YouTube
                        </label>
                        <input type="url" name="reseaux_youtube" value="<?php echo e(old('reseaux_youtube', $projet->reseaux_sociaux['youtube'] ?? '')); ?>" placeholder="https://youtube.com/@votre-chaine" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- Instagram -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="instagram" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            Instagram
                        </label>
                        <input type="url" name="reseaux_instagram" value="<?php echo e(old('reseaux_instagram', $projet->reseaux_sociaux['instagram'] ?? '')); ?>" placeholder="https://instagram.com/votre-compte" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- X (Twitter) -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="twitter" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            X (Twitter)
                        </label>
                        <input type="url" name="reseaux_x" value="<?php echo e(old('reseaux_x', $projet->reseaux_sociaux['x'] ?? '')); ?>" placeholder="https://x.com/votre-compte" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- TikTok -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="video" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            TikTok
                        </label>
                        <input type="url" name="reseaux_tiktok" value="<?php echo e(old('reseaux_tiktok', $projet->reseaux_sociaux['tiktok'] ?? '')); ?>" placeholder="https://tiktok.com/@votre-compte" class="input-field w-full" maxlength="200" />
                    </div>
                </div>

            <!-- Bouton dans le formulaire -->
            <div class="flex justify-between items-center mt-12 pt-6">
                <div class="w-full max-w-4xl mx-auto flex justify-between items-center gap-4 mt-4">
                    <a href="<?php echo e(route('onboarding.step1')); ?>" class="btn btn-ghost">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                        Retour
                    </a>
                    
                    <button type="button" class="btn btn-primary" onclick="submitForm(this)">
                        <span id="btn-text">Suivant</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1" id="btn-icon"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function submitForm(button) {
    button.disabled = true;
    button.style.opacity = '0.7';
    document.getElementById('btn-text').textContent = 'Traitement...';
    document.getElementById('btn-icon').style.display = 'none';
    document.getElementById('step2-form').submit();
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/onboarding/step2_fixed.blade.php ENDPATH**/ ?>