<?php $__env->startSection('seo_title', 'LAgentO - Assistant IA Entrepreneurial N°1 en Côte d\'Ivoire'); ?>
<?php $__env->startSection('meta_description', 'Découvrez LAgentO, le premier assistant IA spécialement conçu pour accompagner les entrepreneurs ivoiriens. Conseils personnalisés, opportunités de financement, réseau d\'affaires et accompagnement 24/7 pour réussir votre entreprise en Côte d\'Ivoire.'); ?>
<?php $__env->startSection('meta_keywords', 'assistant IA côte ivoire, entrepreneur abidjan, startup CI, financement pme, conseil business, innovation afrique, lamine barro, etudesk'); ?>
<?php $__env->startSection('og_title', 'LAgentO - Votre Assistant IA Entrepreneurial en Côte d\'Ivoire'); ?>
<?php $__env->startSection('og_description', 'Rejoignez plus de 15M+ entrepreneurs qui font confiance à LAgentO pour développer leur business en Afrique. Gratuit et disponible 24/7.'); ?>
<?php $__env->startSection('canonical_url', route('landing')); ?>
<?php $__env->startSection('title', 'Agent O - Assistant IA Entrepreneurial'); ?>

<?php $__env->startSection('vite'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('head'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('schema_org', json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'LAgentO',
    'alternateName' => 'Agent O',
    'description' => 'Premier assistant IA entrepreneurial pour la Côte d\'Ivoire',
    'url' => url('/'),
    'sameAs' => [
        'https://linkedin.com/company/lagento',
        'https://twitter.com/LAgentO_CI'
    ],
    'offers' => [
        '@type' => 'Offer',
        'price' => '0',
        'priceCurrency' => 'XOF',
        'availability' => 'https://schema.org/InStock',
        'description' => 'Assistant IA gratuit pour entrepreneurs'
    ],
    'audience' => [
        '@type' => 'Audience',
        'audienceType' => 'Entrepreneurs',
        'geographicArea' => [
            '@type' => 'Country',
            'name' => 'Côte d\'Ivoire',
            'alternateName' => 'Ivory Coast'
        ]
    ],
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => url('/projets') . '?search={search_term_string}',
        'query-input' => 'required name=search_term_string'
    ]
])); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex flex-col" style="background: linear-gradient(180deg, #fff 0%, #fff7f2 100%);">

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-xl text-center">
            <!-- Logo Text Centered -->
            <div class="mx-auto mb-6 text-3xl sm:text-4xl font-semibold" style="font-family: 'Poppins', sans-serif; color: var(--gray-900);">
                Lagent<span style="color: var(--orange-primary);">O</span>
            </div>
            
            <!-- Subtitle -->
            <p class="text-lg mb-8 mt-4" style="color: var(--gray-700);">
                Conseils personnalisés, opportunités de financement et accompagnement 24/7 pour passer de l'idée à l'action.
            </p>

            <!-- Email Form -->
            <form method="POST" action="<?php echo e(route('auth.email')); ?>" class="space-y-4">
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
                        <p class="text-sm mt-1" style="color: var(--danger);"><?php echo e($message); ?></p>
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

    <!-- Footer -->
    <footer class="p-4 text-center">
        <p class="text-sm" style="color: var(--gray-500);">
            © 2024 Agent O. Tous droits réservés.
        </p>
        <a href="<?php echo e(route('legal')); ?>" class="text-sm" style="color: var(--gray-700);">Mentions légales</a>
    </footer>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/landing.blade.php ENDPATH**/ ?>