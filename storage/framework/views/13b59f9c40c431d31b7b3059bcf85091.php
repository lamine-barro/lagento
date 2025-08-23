<?php $__env->startSection('seo_title', 'Agento - Assistant IA entrepreneurial n°1 en Côte d\'Ivoire'); ?>
<?php $__env->startSection('meta_description', 'Découvrez Agento, le premier assistant IA spécialement conçu pour accompagner les entrepreneurs ivoiriens.'); ?>
<?php $__env->startSection('meta_keywords', 'assistant IA côte ivoire, entrepreneur abidjan, startup CI, financement pme'); ?>
<?php $__env->startSection('title', 'Agento - Assistant IA entrepreneurial'); ?>

<?php $__env->startSection('vite'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<style>
    .two-column-layout {
        display: flex;
        min-height: 100vh;
        transition: background-color 0.3s ease;
    }
    
    /* Light mode */
    .left-column {
        width: 50%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4rem;
        background: white;
        transition: background-color 0.3s ease;
    }
    
    .right-column {
        width: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem;
        background: white;
        transition: background 0.3s ease;
    }
    
    /* Dark mode */
    [data-theme="dark"] .two-column-layout {
        background: #0a0a0a;
    }
    
    [data-theme="dark"] .left-column {
        background: #0a0a0a;
    }
    
    [data-theme="dark"] .right-column {
        background: #0a0a0a;
    }
    
    [data-theme="dark"] .main-title {
        color: #f5f5f5 !important;
    }
    
    [data-theme="dark"] .subtitle {
        color: #a0a0a0 !important;
    }
    
    [data-theme="dark"] .description {
        color: #cccccc !important;
    }
    
    [data-theme="dark"] .email-input {
        background: #2a2a2a !important;
        border-color: #404040 !important;
        color: #f5f5f5 !important;
    }
    
    [data-theme="dark"] .email-input::placeholder {
        color: #808080 !important;
    }
    
    .email-input:focus {
        border-color: var(--orange) !important;
        outline: none;
    }
    
    .submit-button {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .submit-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        background: var(--orange-dark) !important;
    }
    
    .illustration-container {
        position: relative;
        width: 100%;
        max-width: 600px;
    }
    
    .illustration-image {
        width: 100%;
        height: auto;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .illustration-image:hover {
        transform: scale(1.02);
    }
    
    
    /* Decorative elements */
    .decoration-1 {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(255, 107, 53, 0.3) 0%, transparent 70%);
        border-radius: 50%;
        filter: blur(40px);
    }
    
    .decoration-2 {
        position: absolute;
        bottom: -20px;
        left: -20px;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(255, 153, 102, 0.3) 0%, transparent 70%);
        border-radius: 50%;
        filter: blur(40px);
    }
    
    /* Gestion responsive des illustrations */
    .mobile-illustration {
        display: none;
    }
    
    @media (max-width: 1024px) {
        .two-column-layout {
            flex-direction: column;
        }
        .left-column, .right-column {
            width: 100%;
            padding: 2rem;
        }
        .main-title {
            font-size: 2.5rem !important;
        }
        
        /* Afficher l'illustration mobile et masquer celle de droite */
        .mobile-illustration {
            display: block;
        }
        .right-column {
            display: none;
        }
        
        /* Optimisation des espacements mobile */
        .logo-container {
            margin-bottom: 1.5rem !important;
        }
        
        .mobile-illustration {
            margin-bottom: 2rem !important;
        }
        
        .main-title {
            font-size: 2.25rem !important;
            margin-bottom: 1.5rem !important;
            line-height: 1.3 !important;
        }
        
        .description {
            font-size: 1rem !important;
            line-height: 1.6 !important;
            margin-bottom: 2rem !important;
        }
        
        .description span {
            font-size: 1.125rem !important;
        }
    }
    
    /* Espacements encore plus compacts sur très petits écrans */
    @media (max-width: 640px) {
        .left-column {
            padding: 1.5rem !important;
        }
        
        .logo-container {
            margin-bottom: 1rem !important;
        }
        
        .mobile-illustration {
            margin-bottom: 1.5rem !important;
        }
        
        .mobile-illustration div {
            max-width: 320px !important;
        }
        
        .main-title {
            font-size: 2rem !important;
            margin-bottom: 1rem !important;
        }
        
        .description {
            margin-bottom: 1.5rem !important;
        }
    }
</style>

<div class="two-column-layout" data-theme="light">
    <!-- COLONNE GAUCHE - Texte et Formulaire -->
    <div class="left-column">
        <!-- Logo -->
        <div class="logo-container" style="margin-bottom: 3rem;">
            <?php if (isset($component)) { $__componentOriginal987d96ec78ed1cf75b349e2e5981978f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.logo','data' => ['size' => 'xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'xl']); ?>
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
        
        <!-- Illustration Mobile uniquement -->
        <div class="mobile-illustration" style="margin-bottom: 3rem;">
            <div style="width: 100%; max-width: 400px; margin: 0 auto;">
                <img 
                    src="<?php echo e(asset('lagento_illustration.png')); ?>" 
                    alt="LagentO - Assistant IA pour entrepreneurs" 
                    style="width: 100%; height: auto; border-radius: 1.5rem; box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.2);"
                    loading="eager"
                />
            </div>
        </div>
        
        <!-- Titre Principal -->
        <h1 class="main-title" style="font-size: 3.5rem; font-weight: bold; line-height: 1.2; margin-bottom: 2rem; color: var(--gray-900);">
            Le Président Inspire,<br>
            <span style="display: inline-block; background: var(--orange); color: white; padding: 0.5rem 2rem; border-radius: var(--radius-lg); margin: 0.5rem 0; box-shadow: var(--shadow-lg);">
                LagentO Soutient,
            </span><br>
            La Jeunesse Construit
        </h1>
        
        <!-- Description -->
        <p class="description" style="font-size: 1.25rem; line-height: 1.8; color: var(--gray-700); margin-bottom: 3rem;">
            LagentO a cartographié <span style="color: var(--orange); font-weight: bold; font-size: 1.5rem;">1,000+ milliards de FCFA</span> 
            d'opportunités pour les jeunes entrepreneurs en Côte d'Ivoire à travers les initiatives 
            gouvernementales et les écosystèmes. Il est disponible 24h/7 pour vous accompagner à les saisir.
        </p>
        
        <!-- Formulaire Email -->
        <form method="POST" action="<?php echo e(route('auth.email')); ?>" style="max-width: 500px;">
            <?php echo csrf_field(); ?>
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Votre adresse email"
                    value="<?php echo e(old('email')); ?>"
                    class="email-input"
                    style="flex: 1; padding: 1rem 1.5rem; font-size: 1.125rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); background: var(--white); color: var(--gray-900); transition: var(--transition);"
                    required
                    autofocus
                />
                <button 
                    type="submit" 
                    class="submit-button"
                    style="padding: 1rem 2rem; background: var(--orange); color: white; font-weight: var(--font-weight-semibold); font-size: 1.125rem; border: none; border-radius: var(--radius-lg); cursor: pointer; white-space: nowrap; transition: var(--transition);"
                >
                    Évaluer mon projet
                </button>
            </div>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p style="color: var(--orange); font-size: 0.875rem;"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </form>
    </div>
    
    <!-- COLONNE DROITE - Illustration -->
    <div class="right-column">
        <div class="illustration-container">
            <!-- Decorative elements -->
            <div class="decoration-1"></div>
            <div class="decoration-2"></div>
            
            <!-- Main illustration -->
            <img 
                src="<?php echo e(asset('lagento_illustration.png')); ?>" 
                alt="LagentO - Assistant IA pour entrepreneurs" 
                class="illustration-image"
                loading="eager"
            />
        </div>
    </div>
</div>

<!-- Chat Widget en bas à droite -->
<?php echo $__env->make('components.guest-chat', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
    // Theme detection and switching
    document.addEventListener('DOMContentLoaded', function() {
        const layout = document.querySelector('.two-column-layout');
        
        // Check for saved theme preference or default to light
        const currentTheme = localStorage.getItem('theme') || 'light';
        layout.setAttribute('data-theme', currentTheme);
        
        // Listen for theme changes from other parts of the app
        window.addEventListener('theme-changed', function(e) {
            layout.setAttribute('data-theme', e.detail.theme);
        });
        
        // Check system preference
        if (!localStorage.getItem('theme')) {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                layout.setAttribute('data-theme', 'dark');
            }
        }
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!localStorage.getItem('theme')) {
                layout.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/landing.blade.php ENDPATH**/ ?>