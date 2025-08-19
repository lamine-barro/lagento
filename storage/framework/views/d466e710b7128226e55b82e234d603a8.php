<header class="fixed top-0 left-0 right-0 z-fixed z-10" style="height: var(--header-height); background: var(--white); border-bottom: 1px solid var(--gray-200);">
    <div class="container max-w-4xl mx-auto flex items-center justify-between h-full">
        <!-- Toggle mobile (Diagnostic / Agent) -->
        <div class="md:hidden flex items-center gap-1 bg-gray-100 rounded-md" style="padding: 2px !important;">
            <a href="<?php echo e(route('diagnostic')); ?>" 
               class="touch-target rounded-md transition-colors <?php echo e(request()->routeIs('diagnostic') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600'); ?>"
               title="Diagnostic">
                <i data-lucide="chart-network" class="w-4 h-4"></i>
            </a>
            <a href="<?php echo e(route('chat.index')); ?>" 
               class="touch-target rounded-md transition-colors <?php echo e(request()->routeIs('chat.index') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600'); ?>"
               title="Agent">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
            </a>
        </div>
        
        <!-- Logo -->
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('chat.index')); ?>">
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
        </div>
        
        <!-- Navigation desktop -->
        <nav class="hidden md:flex items-center bg-gray-100 rounded-lg p-1">
            <a 
                href="<?php echo e(route('diagnostic')); ?>"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-colors
                       <?php echo e(request()->routeIs('diagnostic') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600 hover:text-gray-900'); ?>"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="hidden lg:inline">Diagnostic (<?php echo e(auth()->user()->getRemainingDiagnostics()); ?>/3)</span>
            </a>
            <a 
                href="<?php echo e(route('chat.index')); ?>"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-colors
                       <?php echo e(request()->routeIs('chat.index') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600 hover:text-gray-900'); ?>"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span class="hidden lg:inline">Agent</span>
            </a>
        </nav>
        
        <!-- Actions -->
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
            <a href="<?php echo e(route('profile')); ?>" class="touch-target rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </a>
        </div>
    </div>
</header>
<?php /**PATH /Users/laminebarro/agent-O/resources/views/components/navbar.blade.php ENDPATH**/ ?>