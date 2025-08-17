<?php

namespace Database\Seeders;

use App\Services\EmbeddingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentChunkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $embeddingService = app(EmbeddingService::class);

        // Sample entrepreneurship content for Côte d'Ivoire
        $sampleDocuments = [
            [
                'source_type' => 'official_text',
                'source_id' => 1,
                'content' => 'Pour créer une entreprise en Côte d\'Ivoire, il faut suivre plusieurs étapes essentielles. Premièrement, choisir la forme juridique de votre entreprise (SARL, SA, SASU, etc.). Deuxièmement, effectuer les formalités au Centre de Formalités des Entreprises (CFE). Ces démarches incluent l\'immatriculation au Registre du Commerce et du Crédit Mobilier (RCCM), l\'obtention du numéro d\'identification fiscale et l\'inscription aux organismes sociaux.'
            ],
            [
                'source_type' => 'official_text',
                'source_id' => 2,
                'content' => 'Les entrepreneurs ivoiriens peuvent bénéficier de plusieurs dispositifs de financement. Le Fonds de Développement de l\'Entrepreneuriat (FDE) offre des prêts à taux préférentiels. La Banque Africaine de Développement (BAD) propose des programmes spécifiques pour les PME. Les incubateurs comme CIPREL Startup accelerator et Orange Fab Côte d\'Ivoire accompagnent les startups technologiques.'
            ],
            [
                'source_type' => 'opportunity',
                'source_id' => 1,
                'content' => 'Le secteur de l\'agritech présente des opportunités importantes en Côte d\'Ivoire. Avec une économie largement agricole, l\'innovation technologique dans l\'agriculture peut transformer la productivité. Les solutions de digitalisation des chaînes d\'approvisionnement, les plateformes de mise en relation entre producteurs et acheteurs, et les outils de monitoring des cultures sont particulièrement recherchés.'
            ],
            [
                'source_type' => 'institution',
                'source_id' => 1,
                'content' => 'L\'Agence Côte d\'Ivoire PME (ACIPME) est un établissement public qui accompagne les Petites et Moyennes Entreprises. Elle offre des services d\'appui-conseil, de formation, de facilitation d\'accès au financement et aux marchés. L\'ACIPME dispose de centres régionaux dans tout le pays pour se rapprocher des entrepreneurs.'
            ]
        ];

        foreach ($sampleDocuments as $doc) {
            $embeddingService->storeDocumentChunk(
                $doc['source_type'],
                $doc['source_id'],
                $doc['content']
            );
        }

        $this->command->info('DocumentChunk seeding completed successfully!');
    }
}
