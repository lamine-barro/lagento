<?php

namespace Database\Seeders;

use App\Services\VoyageVectorService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentChunkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vectorService = app(VoyageVectorService::class);

        // Sample entrepreneurship content for Côte d'Ivoire
        $sampleDocuments = [
            [
                'content' => 'Pour créer une entreprise en Côte d\'Ivoire, il faut suivre plusieurs étapes essentielles. Premièrement, choisir la forme juridique de votre entreprise (SARL, SA, SASU, etc.). Deuxièmement, effectuer les formalités au Centre de Formalités des Entreprises (CFE). Ces démarches incluent l\'immatriculation au Registre du Commerce et du Crédit Mobilier (RCCM), l\'obtention du numéro d\'identification fiscale et l\'inscription aux organismes sociaux.'
            ],
            [
                'content' => 'Les entrepreneurs ivoiriens peuvent bénéficier de plusieurs dispositifs de financement. Le Fonds de Développement de l\'Entrepreneuriat (FDE) offre des prêts à taux préférentiels. La Banque Africaine de Développement (BAD) propose des programmes spécifiques pour les PME. Les incubateurs comme CIPREL Startup accelerator et Orange Fab Côte d\'Ivoire accompagnent les startups technologiques.'
            ],
            [
                'content' => 'Le secteur de l\'agritech présente des opportunités importantes en Côte d\'Ivoire. Avec une économie largement agricole, l\'innovation technologique dans l\'agriculture peut transformer la productivité. Les solutions de digitalisation des chaînes d\'approvisionnement, les plateformes de mise en relation entre producteurs et acheteurs, et les outils de monitoring des cultures sont particulièrement recherchés.'
            ],
            [
                'content' => 'L\'Agence Côte d\'Ivoire PME (ACIPME) est un établissement public qui accompagne les Petites et Moyennes Entreprises. Elle offre des services d\'appui-conseil, de formation, de facilitation d\'accès au financement et aux marchés. L\'ACIPME dispose de centres régionaux dans tout le pays pour se rapprocher des entrepreneurs.'
            ]
        ];

        // Use the new vector memory approach
        foreach ($sampleDocuments as $index => $doc) {
            $context = "Documentation entrepreneuriale Côte d'Ivoire";
            $chunks = $vectorService->intelligentChunk($doc['content'], $context, 400);
            $embeddings = $vectorService->embedWithContext($chunks, $context);

            foreach ($chunks as $chunkIndex => $chunk) {
                if (isset($embeddings[$chunkIndex])) {
                    \DB::table('vector_memories')->insert([
                        'id' => Str::uuid(),
                        'memory_type' => 'sample_content',
                        'source_id' => 'seed_' . $index . '_' . $chunkIndex,
                        'chunk_content' => $chunk,
                        'embedding' => json_encode($embeddings[$chunkIndex]['embedding']),
                        'metadata' => json_encode([
                            'source' => 'seeder',
                            'type' => 'documentation',
                            'pays' => 'CI'
                        ]),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        $this->command->info('DocumentChunk seeding completed with new vector memory system!');
    }
}
