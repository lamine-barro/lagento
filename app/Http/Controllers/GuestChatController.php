<?php

namespace App\Http\Controllers;

use App\Services\AgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuestChatController extends Controller
{
    private AgentService $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->get('message');
        
        try {
            // Contexte limité pour les invités (opportunités uniquement)
            $guestContext = "Tu es LagentO, un assistant IA pour entrepreneurs en Côte d'Ivoire. 
            Tu as accès uniquement aux opportunités de financement et au contexte entrepreneurial général.
            Tu n'as PAS accès aux projets personnels ou diagnostics des utilisateurs.
            
            IMPORTANT: À la fin de chaque réponse, recommande poliment à l'utilisateur de s'inscrire 
            pour accéder aux fonctionnalités avancées comme l'évaluation personnalisée de projet, 
            le diagnostic complet et le suivi personnalisé.
            
            Exemple de fin de message:
            '💡 Pour une analyse personnalisée de votre projet et accéder à toutes les fonctionnalités 
            (diagnostic complet, suivi de projet, recommandations sur mesure), inscrivez-vous gratuitement 
            en cliquant sur \"Évaluer votre projet\" ci-dessus.'";

            $response = $this->agentService->getResponse($userMessage, null, $guestContext);
            
            // Ajouter la recommandation si elle n'est pas déjà présente
            if (!str_contains($response, 'Évaluer votre projet')) {
                $response .= "\n\n💡 **Pour aller plus loin:** Inscrivez-vous gratuitement pour bénéficier d'une analyse personnalisée de votre projet, d'un diagnostic complet et d'un accompagnement sur mesure. Cliquez sur \"Évaluer votre projet\" pour commencer.";
            }

            return response()->json([
                'response' => $response,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Guest chat error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Une erreur est survenue. Veuillez réessayer.',
                'success' => false
            ], 500);
        }
    }
}