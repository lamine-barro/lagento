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
        <div class="text-center mb-8">
            <h1 class="text-2xl font-medium mb-2" style="color: var(--gray-900);">
                <i data-lucide="user" class="w-5 h-5 mr-2 align-[-2px]"></i>
                Contact
            </h1>
            <p style="color: var(--gray-700);">
                Coordonnées de contact et représentant du projet
            </p>
        </div>

        <form id="step2-form" method="POST" action="<?php echo e(route('onboarding.step2')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>

            <!-- Contact principal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Téléphone -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="phone" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Téléphone
                    </label>
                    <input type="tel" name="telephone" value="<?php echo e(old('telephone')); ?>" placeholder="Ex: +225 07 00 00 00" class="input-field w-full" />
                </div>
                
                <!-- Email -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="mail" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Email
                    </label>
                    <input type="email" name="email" value="<?php echo e(old('email', auth()->user()->email)); ?>" class="input-field w-full" />
                </div>
                
                <!-- Nom & prénom du représentant -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="user" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Nom & prénom du représentant
                    </label>
                    <input type="text" name="nom_representant" value="<?php echo e(old('nom_representant')); ?>" placeholder="Ex: Jean Kouassi" class="input-field w-full" />
                </div>
                
                <!-- Position du représentant -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="briefcase" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Position du représentant
                    </label>
                    <input type="text" name="role_representant" value="<?php echo e(old('role_representant')); ?>" placeholder="Ex: PDG, Directeur" class="input-field w-full" />
                </div>
                
                <!-- Site web -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="globe" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Site web
                    </label>
                    <input type="url" name="site_web" value="<?php echo e(old('site_web')); ?>" placeholder="https://monsite.com" class="input-field w-full" />
                </div>
                
                <!-- WhatsApp -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="message-circle" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        WhatsApp Business
                    </label>
                    <input type="url" name="reseaux_whatsapp" value="<?php echo e(old('reseaux_whatsapp')); ?>" placeholder="https://wa.me/22507000000" class="input-field w-full" />
                </div>
            </div>

            <!-- Réseaux sociaux -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- LinkedIn -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="linkedin" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            LinkedIn
                        </label>
                        <input type="url" name="reseaux_linkedin" value="<?php echo e(old('reseaux_linkedin')); ?>" placeholder="https://linkedin.com/in/votre-profil" class="input-field w-full" />
                    </div>
                    
                    <!-- Facebook -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="facebook" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            Facebook
                        </label>
                        <input type="url" name="reseaux_facebook" value="<?php echo e(old('reseaux_facebook')); ?>" placeholder="https://facebook.com/votre-page" class="input-field w-full" />
                    </div>
                    
                    <!-- YouTube -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="youtube" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            YouTube
                        </label>
                        <input type="url" name="reseaux_youtube" value="<?php echo e(old('reseaux_youtube')); ?>" placeholder="https://youtube.com/@votre-chaine" class="input-field w-full" />
                    </div>
                    
                    <!-- Instagram -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="instagram" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            Instagram
                        </label>
                        <input type="url" name="reseaux_instagram" value="<?php echo e(old('reseaux_instagram')); ?>" placeholder="https://instagram.com/votre-compte" class="input-field w-full" />
                    </div>
                    
                    <!-- X (Twitter) -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="twitter" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            X (Twitter)
                        </label>
                        <input type="url" name="reseaux_x" value="<?php echo e(old('reseaux_x')); ?>" placeholder="https://x.com/votre-compte" class="input-field w-full" />
                    </div>
                    
                    <!-- TikTok -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="video" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            TikTok
                        </label>
                        <input type="url" name="reseaux_tiktok" value="<?php echo e(old('reseaux_tiktok')); ?>" placeholder="https://tiktok.com/@votre-compte" class="input-field w-full" />
                    </div>
                </div>
        </form>
    </div>

    <?php if (isset($component)) { $__componentOriginal4973fa7765c1d7ef7e43a98d4867113c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding.footer','data' => ['nextFormId' => 'step2-form','nextLabel' => 'Suivant']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['next-form-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('step2-form'),'next-label' => 'Suivant']); ?>
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
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/onboarding/step2.blade.php ENDPATH**/ ?>