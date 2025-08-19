<?php $__env->startSection('seo_title', 'Financement pour startups et PME à Abidjan - LagentO'); ?>
<?php $__env->startSection('meta_description', 'Trouvez le financement parfait pour votre startup en Côte d\'Ivoire avec LagentO. Subventions, prêts, investisseurs, concours - toutes les opportunités de financement à Abidjan et en Afrique.'); ?>
<?php $__env->startSection('meta_keywords', 'financement startup abidjan, pme côte ivoire, investisseur ivoirien, subvention entreprise ci, prêt startup, concours entrepreneur, levée fonds afrique'); ?>
<?php $__env->startSection('canonical_url', route('seo.financement')); ?>

<?php $__env->startSection('og_title', 'Financement Startup PME Abidjan - LagentO'); ?>
<?php $__env->startSection('og_description', 'Toutes les solutions de financement pour startups et PME en Côte d\'Ivoire. LagentO vous guide vers les bonnes opportunités.'); ?>
<?php $__env->startSection('og_type', 'website'); ?>

<?php $__env->startSection('schema_org'); ?>

{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Financement Startup PME Abidjan",
    "description": "Solutions de financement pour startups et PME en Côte d'Ivoire avec LagentO",

    "url": "<?php echo e(route('seo.financement')); ?>",

    "mainEntity": {
        "@type": "Service",
        "name": "Solutions de Financement Startup",
        "provider": {
            "@type": "Organization",
            "name": "LagentO",
            "founder": {
                "@type": "Person",
                "name": "Lamine Barro"
            }
        },
        "serviceType": "Business Financing",
        "areaServed": {
            "@type": "Country",
            "name": "Côte d'Ivoire"
        },
        "offers": [
            {
                "@type": "Offer",
                "name": "Accompagnement Financement",
                "description": "Guidance pour obtenir des financements startup",
                "price": "0",
                "priceCurrency": "XOF"
            }
        ]
    },
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Accueil",

                "item": "<?php echo e(url('/')); ?>"

            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Financement Startup",

                "item": "<?php echo e(route('seo.financement')); ?>"

            }
        ]
    }
}

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_title', 'Financement Startup PME Abidjan'); ?>
<?php $__env->startSection('title', 'Financement Startup PME Abidjan'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen" style="background: var(--gray-50);">
    
    <!-- Hero Section -->
    <section class="py-20" style="background: linear-gradient(135deg, var(--green-600) 0%, var(--green-500) 100%);">
        <div class="container max-w-4xl mx-auto px-4 text-center text-white">
            <h1 class="text-4xl font-bold mb-6">
                💰 Financement pour startups et PME en Côte d'Ivoire
            </h1>
            <p class="text-xl mb-8 opacity-90">
                LagentO vous aide à trouver les bonnes opportunités de financement pour développer votre entreprise en Côte d'Ivoire
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo e(route('landing')); ?>" class="btn btn-lg px-8 py-4 text-lg" style="background: white; color: var(--green-600);">
                    🚀 Trouver mon Financement
                </a>
                <a href="<?php echo e(route('seo.diagnostic')); ?>" class="btn btn-lg px-8 py-4 text-lg" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                    📊 Évaluer mon Projet
                </a>
            </div>
        </div>
    </section>

    <!-- Types de Financement -->
    <section class="py-20">
        <div class="container max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--gray-900);">
                    Types de financement en Côte d'Ivoire
                </h2>
                <p class="text-lg" style="color: var(--gray-700);">
                    Explorez toutes les options de financement adaptées à votre stade de développement
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Subventions -->
                <div class="card p-6">
                    <div class="text-4xl mb-4">🎁</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Subventions publiques</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Financements non remboursables de l'État ivoirien, FASI, AGEPE et organismes internationaux</p>
                    <ul class="text-sm" style="color: var(--gray-600);">
                        <li>• FASI (Fonds d'Appui au Secteur Informel)</li>
                        <li>• AGEPE programmes entrepreneurs</li>
                        <li>• Subventions ministérielles</li>
                    </ul>
                </div>

                <!-- Prêts bancaires -->
                <div class="card p-6">
                    <div class="text-4xl mb-4">🏦</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Prêts bancaires</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Crédits adaptés aux startups par les banques locales et institutions spécialisées</p>
                    <ul class="text-sm" style="color: var(--gray-600);">
                        <li>• Crédit PME/PMI</li>
                        <li>• Prêts innovation</li>
                        <li>• Microcrédits</li>
                    </ul>
                </div>

                <!-- Investisseurs -->
                <div class="card p-6">
                    <div class="text-4xl mb-4">👥</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Investisseurs privés</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Business angels, fonds d'investissement et partenaires stratégiques en Afrique</p>
                    <ul class="text-sm" style="color: var(--gray-600);">
                        <li>• Business Angels ivoiriens</li>
                        <li>• Fonds d'investissement</li>
                        <li>• Partenaires stratégiques</li>
                    </ul>
                </div>

                <!-- Concours -->
                <div class="card p-6">
                    <div class="text-4xl mb-4">🏆</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Concours et prix</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Compétitions entrepreneuriales avec dotations financières et accompagnement</p>
                    <ul class="text-sm" style="color: var(--gray-600);">
                        <li>• Concours nationaux</li>
                        <li>• Prix innovation Afrique</li>
                        <li>• Challenges sectoriels</li>
                    </ul>
                </div>

                <!-- Crowdfunding -->
                <div class="card p-6">
                    <div class="text-4xl mb-4">🌍</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Financement participatif</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Plateformes de crowdfunding et financement communautaire</p>
                    <ul class="text-sm" style="color: var(--gray-600);">
                        <li>• Crowdfunding international</li>
                        <li>• Financement communautaire</li>
                        <li>• Précommandes produits</li>
                    </ul>
                </div>

                <!-- Incubateurs -->
                <div class="card p-6">
                    <div class="text-4xl mb-4">🚀</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Incubateurs et accélérateurs</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Programmes d'accompagnement avec financement et mentorat intégrés</p>
                    <ul class="text-sm" style="color: var(--gray-600);">
                        <li>• Incubateurs locaux</li>
                        <li>• Accélérateurs panafricains</li>
                        <li>• Programmes sectoriels</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Processus -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Comment LagentO vous aide à obtenir votre financement
            </h2>
            
            <div class="grid md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl" style="background: var(--green-100); color: var(--green-600);">1</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Analyse</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Évaluation de votre projet et besoins financiers</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl" style="background: var(--green-100); color: var(--green-600);">2</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Matching</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Identification des financements les plus adaptés</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl" style="background: var(--green-100); color: var(--green-600);">3</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Préparation</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Accompagnement dans la préparation des dossiers</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl" style="background: var(--green-100); color: var(--green-600);">4</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Suivi</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Suivi des candidatures et négociations</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6" style="color: var(--gray-900);">
                Besoin d'aide pour votre financement ?
            </h2>
            <p class="text-lg mb-8" style="color: var(--gray-700);">
                Laissez LagentO vous accompagner dans votre recherche de financement en Côte d'Ivoire
            </p>
            <a href="<?php echo e(route('landing')); ?>" class="btn btn-primary btn-lg px-8 py-4 text-lg">
                💰 Commencer ma recherche de financement
            </a>
        </div>
    </section>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/seo/financement-startup.blade.php ENDPATH**/ ?>