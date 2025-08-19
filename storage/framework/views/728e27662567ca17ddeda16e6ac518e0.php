<?php $__env->startSection('seo_title', 'Réseau d\'entrepreneurs ivoiriens - LagentO'); ?>
<?php $__env->startSection('meta_description', 'Rejoignez le plus grand réseau d\'entrepreneurs ivoiriens avec LagentO. Networking, partenariats, mentoring et collaborations business en Côte d\'Ivoire. Connectez-vous avec Lamine Barro et la communauté Ci20.'); ?>
<?php $__env->startSection('meta_keywords', 'réseau entrepreneur ivoirien, networking business ci, communauté startup abidjan, partenariats entrepreneurs, mentoring business côte ivoire'); ?>
<?php $__env->startSection('canonical_url', route('seo.reseau')); ?>

<?php $__env->startSection('og_title', 'Réseau Entrepreneurs Ivoiriens - LagentO Community'); ?>
<?php $__env->startSection('og_description', 'Le plus grand réseau d\'entrepreneurs ivoiriens. Networking, partenariats et collaborations business avec LagentO Community.'); ?>
<?php $__env->startSection('og_type', 'website'); ?>

<?php $__env->startSection('schema_org'); ?>

{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Réseau Entrepreneurs Ivoiriens",
    "description": "Communauté et réseau d'entrepreneurs ivoiriens avec LagentO",

    "url": "<?php echo e(route('seo.reseau')); ?>",

    "mainEntity": {
        "@type": "Organization",
        "name": "LagentO Community",
        "description": "Réseau d'entrepreneurs ivoiriens et africains",
        "founder": {
            "@type": "Person",
            "name": "Lamine Barro",
            "jobTitle": "Président Ci20"
        },
        "memberOf": {
            "@type": "Organization",
            "name": "Ci20 - Collège Startups Ivoiriennes"
        },
        "areaServed": {
            "@type": "Country",
            "name": "Côte d'Ivoire"
        }
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
                "name": "Réseau Entrepreneurs",

                "item": "<?php echo e(route('seo.reseau')); ?>"

            }
        ]
    }
}

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_title', 'Réseau Entrepreneurs Ivoiriens'); ?>
<?php $__env->startSection('title', 'Réseau Entrepreneurs Ivoiriens'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen" style="background: var(--gray-50);">
    
    <!-- Hero Section -->
    <section class="py-20" style="background: linear-gradient(135deg, var(--indigo-600) 0%, var(--indigo-500) 100%);">
        <div class="container max-w-4xl mx-auto px-4 text-center text-white">
            <h1 class="text-4xl font-bold mb-6">
                🤝 Réseau d'entrepreneurs ivoiriens
            </h1>
            <p class="text-xl mb-8 opacity-90">
                Rejoignez la communauté d'entrepreneurs ivoiriens avec Lamine Barro et le réseau Ci20
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo e(route('landing')); ?>" class="btn btn-lg px-8 py-4 text-lg" style="background: white; color: var(--indigo-600);">
                    🚀 Rejoindre la Communauté
                </a>
                <a href="<?php echo e(route('seo.assistant-ia')); ?>" class="btn btn-lg px-8 py-4 text-lg" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                    🤖 Découvrir LagentO
                </a>
            </div>
        </div>
    </section>

    <!-- Chiffres du Réseau -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-6" style="color: var(--gray-900);">
                    Réseau entrepreneurial en Côte d'Ivoire
                </h2>
                <p class="text-lg" style="color: var(--gray-700);">
                    Animé par Lamine Barro, Président du Collège Ci20 et Ambassadeur Innovation
                </p>
            </div>

            <div class="grid md:grid-cols-4 gap-8 mb-16">
                <div class="text-center">
                    <div class="text-4xl font-bold mb-2" style="color: var(--indigo-600);">15M+</div>
                    <div class="text-sm" style="color: var(--gray-700);">Entrepreneurs touchés</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold mb-2" style="color: var(--indigo-600);">500+</div>
                    <div class="text-sm" style="color: var(--gray-700);">Organisations partenaires</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold mb-2" style="color: var(--indigo-600);">36</div>
                    <div class="text-sm" style="color: var(--gray-700);">Pays africains</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold mb-2" style="color: var(--indigo-600);">17M€</div>
                    <div class="text-sm" style="color: var(--gray-700);">CA généré par Ci20</div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h3 class="text-2xl font-semibold mb-6" style="color: var(--gray-900);">
                        🏆 Ci20 - Excellence Entrepreneuriale
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <span class="text-2xl mr-4">✨</span>
                            <div>
                                <strong>15 startups membres</strong><br>
                                <span style="color: var(--gray-700);">Sélectionnées parmi l'élite ivoirienne</span>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-2xl mr-4">👥</span>
                            <div>
                                <strong>350 emplois créés</strong><br>
                                <span style="color: var(--gray-700);">Impact direct sur l'économie locale</span>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-2xl mr-4">🏅</span>
                            <div>
                                <strong>115 prix remportés</strong><br>
                                <span style="color: var(--gray-700);">Excellence reconnue nationalement et internationalement</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <div class="text-6xl mb-4">🌟</div>
                        <h4 class="text-2xl font-semibold mb-2" style="color: var(--gray-900);">Lamine Barro</h4>
                        <p class="text-sm mb-6" style="color: var(--gray-700);">
                            Président Ci20<br>
                            Ambassadeur Innovation CI<br>
                            PDG Etudesk - Executive MBA HEC
                        </p>
                        <div class="space-y-2">
                            <div class="text-lg font-semibold" style="color: var(--indigo-600);">Mentorat Direct</div>
                            <div class="text-xs" style="color: var(--gray-600);">Accès privilégié à l'expertise</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Avantages du Réseau -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Avantages du réseau LagentO
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div class="card p-6">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Networking</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Connectez-vous avec des entrepreneurs sélectionnés et des investisseurs actifs</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Événements privés mensuels</li>
                        <li>• Matchmaking entreprises</li>
                        <li>• Accès investisseurs</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">🤝</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Partenariats</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Collaborations business et partenariats mutuellement bénéfiques</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Joint-ventures</li>
                        <li>• Partenariats commerciaux</li>
                        <li>• Synergies sectorielles</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">🧠</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Mentoring</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Accompagnement personnalisé par des entrepreneurs expérimentés</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Sessions individuelles</li>
                        <li>• Mentorat groupe</li>
                        <li>• Expertise sectorielle</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">🚀</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Accélération business</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Programmes d'accélération et de développement commercial</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Bootcamps spécialisés</li>
                        <li>• Formations business</li>
                        <li>• Coaching croissance</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">💰</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Accès au financement</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Opportunités de financement exclusives et accompagnement levées</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Présentations investisseurs</li>
                        <li>• Due diligence support</li>
                        <li>• Négociation accompagnée</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">🌍</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Expansion en Afrique</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Réseau panafricain pour votre développement international</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Partenaires locaux</li>
                        <li>• Missions économiques</li>
                        <li>• Intelligence marché</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Types de Membres -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Qui compose notre réseau ?
            </h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--gray-900);">👨‍💼 Entrepreneurs & Fondateurs</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-3" style="background: var(--indigo-500);"></span>
                            <span>Startups tech et innovation</span>
                        </li>
                        <li class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-3" style="background: var(--indigo-500);"></span>
                            <span>PME en croissance</span>
                        </li>
                        <li class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-3" style="background: var(--indigo-500);"></span>
                            <span>Entrepreneurs sociaux</span>
                        </li>
                        <li class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-3" style="background: var(--indigo-500);"></span>
                            <span>Créateurs d'entreprise</span>
                        </li>
                    </ul>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--gray-900);">💼 Investisseurs & Partners</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-3" style="background: var(--indigo-500);"></span>
                            <span>Business angels ivoiriens</span>
                        </li>
                        <li class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-3" style="background: var(--indigo-500);"></span>
                            <span>Fonds d'investissement</span>
                        </li>
                        <li class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-3" style="background: var(--indigo-500);"></span>
                            <span>Partenaires institutionnels</span>
                        </li>
                        <li class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-3" style="background: var(--indigo-500);"></span>
                            <span>Mentors & conseillers</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Événements & Activités -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Événements & Activités Régulières
            </h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--indigo-100); color: var(--indigo-600);">🎤</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">Meetups Mensuels</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Rencontres physiques et virtuelles avec speakers experts et sessions networking</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--indigo-100); color: var(--indigo-600);">🎯</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">Pitch Sessions</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Présentations de projets devant investisseurs et sessions de feedback</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--indigo-100); color: var(--indigo-600);">📚</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">Formations Exclusives</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Workshops et masterclass par des experts business reconnus</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Témoignages -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Ce que disent nos membres
            </h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl" style="background: var(--indigo-100);">👨‍💼</div>
                        <div class="ml-4">
                            <h4 class="font-semibold" style="color: var(--gray-900);">Kouame A.</h4>
                            <p class="text-sm" style="color: var(--gray-600);">CEO TechStart CI</p>
                        </div>
                    </div>
                    <p style="color: var(--gray-700);">"Le réseau LagentO a été déterminant pour lever 100M FCFA. L'accès aux investisseurs et le mentoring de Lamine ont fait la différence."</p>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl" style="background: var(--indigo-100);">👩‍💼</div>
                        <div class="ml-4">
                            <h4 class="font-semibold" style="color: var(--gray-900);">Fatou D.</h4>
                            <p class="text-sm" style="color: var(--gray-600);">Fondatrice EcoSolutions</p>
                        </div>
                    </div>
                    <p style="color: var(--gray-700);">"Grâce au réseau, j'ai trouvé mes co-fondateurs et développé des partenariats stratégiques. Une communauté incontournable !"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6" style="color: var(--gray-900);">
                Prêt à rejoindre le réseau ?
            </h2>
            <p class="text-lg mb-8" style="color: var(--gray-700);">
                Rejoignez Lamine Barro et la communauté d'entrepreneurs de Côte d'Ivoire
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo e(route('landing')); ?>" class="btn btn-primary btn-lg px-8 py-4 text-lg">
                    🤝 Rejoindre le Réseau LagentO
                </a>
                <a href="<?php echo e(route('seo.diagnostic')); ?>" class="btn btn-outline-primary btn-lg px-8 py-4 text-lg">
                    📊 Évaluer mon Profil
                </a>
            </div>
        </div>
    </section>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/seo/reseau-entrepreneurs.blade.php ENDPATH**/ ?>