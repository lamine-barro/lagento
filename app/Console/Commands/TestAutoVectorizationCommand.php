<?php

namespace App\Console\Commands;

use App\Services\AutoVectorizationService;
use App\Services\OpenAIVectorService;
use App\Models\User;
use App\Models\UserAnalytics;
use App\Models\Conversation;
use App\Models\UserMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestAutoVectorizationCommand extends Command
{
    protected $signature = 'test:auto-vectorization 
                          {--create-test-data : Create test data for demonstration}';
    
    protected $description = 'Test automatic vectorization for diagnostics, conversations, and attachments';

    protected AutoVectorizationService $autoVectorService;
    protected OpenAIVectorService $vectorService;

    public function __construct(AutoVectorizationService $autoVectorService, OpenAIVectorService $vectorService)
    {
        parent::__construct();
        $this->autoVectorService = $autoVectorService;
        $this->vectorService = $vectorService;
    }

    public function handle()
    {
        $this->info("🧪 Testing Auto-Vectorization Services...");
        $this->newLine();

        if ($this->option('create-test-data')) {
            $this->createTestData();
        }

        // Test 1: User Diagnostics Vectorization
        $this->testUserDiagnosticVectorization();
        
        // Test 2: Conversation Summary Vectorization  
        $this->testConversationSummaryVectorization();
        
        // Test 3: Message Attachment Vectorization
        $this->testMessageAttachmentVectorization();
        
        // Test 4: Search in each namespace
        $this->testNamespaceSearches();

        $this->newLine();
        $this->info("✅ Auto-vectorization test completed!");
        
        return 0;
    }

    private function createTestData()
    {
        $this->info("🔨 Creating test data...");
        
        // Create test user
        $user = User::firstOrCreate([
            'email' => 'test@vectorization.com'
        ], [
            'name' => 'Test User Vectorization',
            'password' => bcrypt('password')
        ]);

        // Create test diagnostic
        $diagnostic = UserAnalytics::firstOrCreate([
            'user_id' => $user->id
        ], [
            'entrepreneur_profile' => [
                'niveau_global' => 'Intermédiaire',
                'profil_type' => 'Innovateur Tech',
                'score_potentiel' => 75,
                'forces' => [
                    ['domaine' => 'Innovation technologique'],
                    ['domaine' => 'Vision stratégique']
                ],
                'axes_progression' => [
                    ['domaine' => 'Marketing digital'],
                    ['domaine' => 'Gestion financière']
                ],
                'besoins_formation' => [
                    'Techniques de vente',
                    'Levée de fonds'
                ]
            ],
            'score_sante' => 78,
            'viabilite' => 'Bonne',
            'position_marche' => 'Prometteuse',
            'nombre_opportunites' => 12,
            'message_principal' => 'Votre startup a un potentiel élevé dans le secteur tech. Focus sur le marketing pour accélérer la croissance.',
            'generated_at' => now()
        ]);

        // Create test conversation
        $conversation = Conversation::firstOrCreate([
            'user_id' => $user->id,
            'title' => 'Test Conversation Vectorization'
        ]);

        // Create test messages
        for ($i = 1; $i <= 12; $i++) {
            UserMessage::firstOrCreate([
                'conversation_id' => $conversation->id,
                'role' => $i % 2 === 1 ? 'user' : 'assistant',
                'created_at' => now()->subMinutes(20 - $i)
            ], [
                'text_content' => $i % 2 === 1 
                    ? "Question utilisateur {$i}: Comment développer ma startup dans le secteur fintech?"
                    : "Réponse assistant {$i}: Voici des conseils pour développer votre startup fintech..."
            ]);
        }

        $this->info("✅ Test data created");
        $this->newLine();
    }

    private function testUserDiagnosticVectorization()
    {
        $this->info("🔍 Testing User Diagnostic Vectorization...");
        
        try {
            // Find a test diagnostic
            $diagnostic = UserAnalytics::whereNotNull('entrepreneur_profile')->first();
            
            if (!$diagnostic) {
                $this->warn("⚠️  No diagnostic found - use --create-test-data to create test data");
                return;
            }

            $this->line("Found diagnostic for user ID: {$diagnostic->user_id}");
            
            // Test vectorization
            $success = $this->autoVectorService->vectorizeDiagnostic($diagnostic);
            
            if ($success) {
                $this->info("✅ Diagnostic vectorized successfully");
                
                // Test search in user_diagnostics namespace
                $results = $this->vectorService->searchSimilar(
                    query: "profil entrepreneur innovateur",
                    topK: 3,
                    filter: ['user_id' => $diagnostic->user_id],
                    namespace: 'user_diagnostics'
                );
                
                $this->line("🔍 Search results in user_diagnostics: " . count($results) . " found");
                
                if (!empty($results)) {
                    $sample = $results[0];
                    $score = round($sample['score'], 3);
                    $this->line("   📌 Best match score: {$score}");
                }
            } else {
                $this->error("❌ Diagnostic vectorization failed");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error testing diagnostic vectorization: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testConversationSummaryVectorization()
    {
        $this->info("🔍 Testing Conversation Summary Vectorization...");
        
        try {
            // Find a conversation with enough messages
            $conversation = Conversation::whereHas('messages')->first();
            
            if (!$conversation) {
                $this->warn("⚠️  No conversation found - use --create-test-data to create test data");
                return;
            }

            $messageCount = $conversation->messages()->count();
            $this->line("Found conversation with {$messageCount} messages");
            
            if ($messageCount >= 10) {
                // Test vectorization
                $success = $this->autoVectorService->vectorizeConversationSummary($conversation);
                
                if ($success) {
                    $this->info("✅ Conversation summary vectorized successfully");
                    
                    // Test search in conversation_summaries namespace
                    $results = $this->vectorService->searchSimilar(
                        query: "conversation startup fintech",
                        topK: 3,
                        filter: ['user_id' => $conversation->user_id],
                        namespace: 'conversation_summaries'
                    );
                    
                    $this->line("🔍 Search results in conversation_summaries: " . count($results) . " found");
                    
                    if (!empty($results)) {
                        $sample = $results[0];
                        $score = round($sample['score'], 3);
                        $this->line("   📌 Best match score: {$score}");
                    }
                } else {
                    $this->error("❌ Conversation summary vectorization failed");
                }
            } else {
                $this->warn("⚠️  Conversation needs at least 10 messages for auto-vectorization");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error testing conversation vectorization: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testMessageAttachmentVectorization()
    {
        $this->info("🔍 Testing Message Attachment Vectorization...");
        
        try {
            // Create a test text file
            $testContent = "Ceci est un document test pour la vectorisation automatique des attachements.\n\n";
            $testContent .= "Contenu: Business plan pour startup fintech en Côte d'Ivoire.\n";
            $testContent .= "Secteur: Technologie financière, paiements mobiles.\n";
            $testContent .= "Marché cible: Entrepreneurs et PME ivoiriennes.\n";
            $testContent .= "Financement demandé: 500 000 EUR pour expansion.";
            
            $testFilePath = storage_path('app/test_attachment.txt');
            file_put_contents($testFilePath, $testContent);
            
            $this->line("Created test attachment: " . basename($testFilePath));
            
            // Test vectorization
            $success = $this->autoVectorService->vectorizeAttachment($testFilePath, [
                'user_id' => 'test-user-123',
                'conversation_id' => 'test-conv-456',
                'filename' => 'business_plan_test.txt',
                'mime_type' => 'text/plain',
                'size' => strlen($testContent),
                'uploaded_at' => now()->toISOString()
            ]);
            
            if ($success) {
                $this->info("✅ Attachment vectorized successfully");
                
                // Test search in message_attachments namespace
                $results = $this->vectorService->searchSimilar(
                    query: "business plan fintech startup",
                    topK: 3,
                    filter: [],
                    namespace: 'message_attachments'
                );
                
                $this->line("🔍 Search results in message_attachments: " . count($results) . " found");
                
                if (!empty($results)) {
                    $sample = $results[0];
                    $score = round($sample['score'], 3);
                    $fileName = $sample['metadata']['file_name'] ?? 'unknown';
                    $this->line("   📌 Best match: {$fileName} (score: {$score})");
                }
            } else {
                $this->error("❌ Attachment vectorization failed");
            }
            
            // Cleanup
            if (file_exists($testFilePath)) {
                unlink($testFilePath);
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error testing attachment vectorization: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testNamespaceSearches()
    {
        $this->info("🔍 Testing searches across all namespaces...");
        
        $namespaces = [
            'user_diagnostics',
            'conversation_summaries', 
            'message_attachments'
        ];
        
        $testQuery = "startup entrepreneur financement";
        
        foreach ($namespaces as $namespace) {
            try {
                $results = $this->vectorService->searchSimilar(
                    query: $testQuery,
                    topK: 5,
                    filter: [],
                    namespace: $namespace
                );
                
                $count = count($results);
                if ($count > 0) {
                    $this->info("✅ {$namespace}: {$count} vectors found");
                } else {
                    $this->line("⚪ {$namespace}: empty (normal if no data vectorized yet)");
                }
                
            } catch (\Exception $e) {
                $this->error("❌ {$namespace}: Error - " . $e->getMessage());
            }
        }
        
        $this->newLine();
    }
}