<?php
    $currentStep = $currentStep ?? 1;
    $items = $items ?? [
        ['label' => 'Identité', 'icon' => 'badge-check', 'route' => route('onboarding.step1'), 'step' => 1, 'description' => 'Les informations essentielles de votre entreprise'],
        ['label' => 'Contact', 'icon' => 'user', 'route' => route('onboarding.step2'), 'step' => 2, 'description' => 'Vos coordonnées pour rester connecté'],
        ['label' => 'Activité', 'icon' => 'bar-chart-3', 'route' => route('onboarding.step3'), 'step' => 3, 'description' => 'Votre secteur et vos ambitions'],
        ['label' => 'Équipe', 'icon' => 'users', 'route' => route('onboarding.step4'), 'step' => 4, 'description' => 'Les forces vives de votre projet'],
    ];
    $progressPercent = max(0, min(100, ($currentStep - 1) * 25));
?>

<div class="w-full max-w-4xl mx-auto" x-data>
    <!-- Top bar with logo + logout -->
    <div class="flex items-center justify-between py-3 mb-4" style="border-bottom: 1px solid var(--gray-100);">
        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center">
            <?php if (isset($component)) { $__componentOriginal987d96ec78ed1cf75b349e2e5981978f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.logo','data' => ['size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal987d96ec78ed1cf75b349e2e5981978f)): ?>
<?php $attributes = $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f; ?>
<?php unset($__attributesOriginal987d96ec78ed1cf75b349e2e5981978f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal987d96ec78ed1cf75b349e2e5981978f)): ?>
<?php $component = $__componentOriginal987d96ec78ed1cf75b349e2e5981978f; ?>
<?php unset($__componentOriginal987d96ec78ed1cf75b349e2e5981978f); ?>
<?php endif; ?>
        </a>
        <div class="flex items-center gap-2">
            <?php if (isset($component)) { $__componentOriginal2090438866f3dcdb76cd8b070bcc302d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.theme-toggle','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('theme-toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $attributes = $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $component = $__componentOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-ghost p-2" title="Déconnexion">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold" style="color: var(--black);">Configuration du profil</h2>
        <p class="text-sm mt-1" style="color: var(--gray-600);">Étape <?php echo e($currentStep); ?> sur 4</p>
        <p class="text-sm mt-2" style="color: var(--gray-700);"><?php echo e($items[$currentStep - 1]['description']); ?></p>
    </div>

    <nav class="grid grid-cols-4 gap-3 mb-4">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($item['route']); ?>" class="flex flex-col items-center py-3 rounded-md transition-colors"
               style="color: <?php echo e($currentStep === $item['step'] ? 'white' : 'var(--gray-600)'); ?>; background: <?php echo e($currentStep === $item['step'] ? 'var(--orange)' : 'transparent'); ?>">
                <i data-lucide="<?php echo e($item['icon']); ?>" class="w-6 h-6 mb-1"></i>
                <span class="text-sm font-medium"><?php echo e($item['label']); ?></span>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <div>
        <div class="h-1 rounded-full" style="background: var(--gray-100);">
            <div class="h-1 rounded-full transition-all duration-500" style="background: var(--orange); width: <?php echo e($progressPercent); ?>%;"></div>
        </div>
    </div>
</div>


<?php /**PATH /Users/laminebarro/agent-O/resources/views/components/onboarding/header.blade.php ENDPATH**/ ?>