<?php

namespace App\Http\Middleware;

use App\Models\Projet;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboardingComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip si pas authentifié ou déjà sur une page d'onboarding
        if (!Auth::check() || $request->routeIs('onboarding.*')) {
            return $next($request);
        }

        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();

        // Si aucun projet ou onboarding incomplet, rediriger vers onboarding
        if (!$projet || !$projet->isOnboardingComplete()) {
            $redirectRoute = 'onboarding.step1';
            
            if ($projet) {
                $progress = $projet->getOnboardingProgress();
                
                // Rediriger vers la première étape incomplète
                if (!$progress['steps']['step1']) {
                    $redirectRoute = 'onboarding.step1';
                } elseif (!$progress['steps']['step4']) {
                    $redirectRoute = 'onboarding.step4';
                }
            }
            
            return redirect()->route($redirectRoute)
                ->with('info', 'Veuillez compléter votre profil pour accéder à cette page.');
        }

        return $next($request);
    }
}
