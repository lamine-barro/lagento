<?php $__env->startSection('seo_title', 'Agento - Assistant IA Entrepreneurial N°1 en Côte d\'Ivoire'); ?>
<?php $__env->startSection('meta_description', 'Découvrez Agento, le premier assistant IA spécialement conçu pour accompagner les entrepreneurs ivoiriens. Conseils personnalisés, opportunités de financement, réseau d\'affaires et accompagnement 24/7 pour réussir votre entreprise en Côte d\'Ivoire.'); ?>
<?php $__env->startSection('meta_keywords', 'assistant IA côte ivoire, entrepreneur abidjan, startup CI, financement pme, conseil business, innovation afrique, lamine barro, etudesk, agento'); ?>
<?php $__env->startSection('og_title', 'Agento - Votre Assistant IA Entrepreneurial en Côte d\'Ivoire'); ?>
<?php $__env->startSection('og_description', 'Rejoignez plus de 15M+ entrepreneurs qui font confiance à Agento pour développer leur business en Afrique. Gratuit et disponible 24/7.'); ?>
<?php $__env->startSection('canonical_url', route('landing')); ?>
<?php $__env->startSection('title', 'Agento - Assistant IA Entrepreneurial'); ?>

<?php $__env->startSection('vite'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('head'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('page_title', 'Agento - Assistant IA Entrepreneurial'); ?>

<?php $__env->startSection('schema_org'); ?>

{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "Agento",
    "alternateName": "Agent O",
    "description": "Premier assistant IA entrepreneurial pour la Côte d'Ivoire",

    "url": "<?php echo e(url('/')); ?>",

    "sameAs": [
        "https://linkedin.com/company/lagento",
        "https://twitter.com/Agento_CI"
    ],
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "XOF",
        "availability": "https://schema.org/InStock",
        "description": "Assistant IA gratuit pour entrepreneurs"
    },
    "audience": {
        "@type": "Audience",
        "audienceType": "Entrepreneurs",
        "geographicArea": {
            "@type": "Country",
            "name": "Côte d'Ivoire",
            "alternateName": "Ivory Coast"
        }
    },
    "potentialAction": {
        "@type": "SearchAction",

        "target": "<?php echo e(url('/projets')); ?>?search={search_term_string}",

        "query-input": "required name=search_term_string"
    }
}

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex flex-col bg-white" style="background: linear-gradient(180deg, #fff 0%, #fff7f2 100%);" data-dark-bg>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-2xl text-center">
            <!-- Logo adaptive -->
            <div class="mx-auto mb-6">
                <?php if (isset($component)) { $__componentOriginal987d96ec78ed1cf75b349e2e5981978f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.logo','data' => ['size' => 'xl','class' => 'mx-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xl','class' => 'mx-auto']); ?>
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
            </div>
            
            <!-- Subtitle -->
            <p class="text-lg mb-8 mt-4" style="color: var(--gray-700);">
                LagentO a cartographié plus de 2 000 milliards de FCFA d'opportunités pour les jeunes entrepreneurs en Côte d'Ivoire à travers les initiatives gouvernementales et les écosystèmes. Il est disponible 24h/7 pour vous accompagner à les saisir.
            </p>

            <!-- Email Form -->
            <form method="POST" action="<?php echo e(route('auth.email')); ?>" class="flex flex-col gap-4">
                <?php echo csrf_field(); ?>
                
                <div class="relative">
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="Votre adresse email"
                        value="<?php echo e(old('email')); ?>"
                        class="input-field w-full"
                        required
                        autofocus
                    />
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-sm mt-1" style="color: var(--error);"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button 
                    type="submit" 
                    class="btn btn-primary w-full flex items-center justify-center gap-2"
                >
                    Se connecter
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </main>
    
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/landing.blade.php ENDPATH**/ ?>