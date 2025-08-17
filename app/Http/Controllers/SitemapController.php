<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Illuminate\Http\Response;
use Carbon\Carbon;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = collect();

        // Page d'accueil
        $urls->push([
            'loc' => url('/'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        ]);

        // Pages statiques
        $urls->push([
            'loc' => route('legal'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.3'
        ]);

        // Annuaire des projets
        $urls->push([
            'loc' => route('projets.index'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9'
        ]);

        // Projets publics et vérifiés
        $projets = Projet::public()
            ->verified()
            ->select('id', 'nom_projet', 'updated_at', 'last_updated_at')
            ->get();

        foreach ($projets as $projet) {
            $urls->push([
                'loc' => route('projets.show', $projet),
                'lastmod' => ($projet->last_updated_at ?? $projet->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ]);
        }

        // Génération du sitemap XML
        $content = $this->generateSitemapXml($urls);

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    private function generateSitemapXml($urls)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . e($url['loc']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . e($url['lastmod']) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . e($url['changefreq']) . '</changefreq>' . "\n";
            $xml .= '    <priority>' . e($url['priority']) . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Sitemap spécialisé pour les projets (si trop nombreux)
     */
    public function projets()
    {
        $projets = Projet::public()
            ->verified()
            ->select('id', 'nom_projet', 'updated_at', 'last_updated_at', 'secteurs', 'region')
            ->orderBy('last_updated_at', 'desc')
            ->limit(5000) // Limite Google
            ->get();

        $urls = collect();

        foreach ($projets as $projet) {
            $urls->push([
                'loc' => route('projets.show', $projet),
                'lastmod' => ($projet->last_updated_at ?? $projet->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'image' => $projet->logo_url ? Storage::url($projet->logo_url) : null
            ]);
        }

        $content = $this->generateImageSitemapXml($urls);

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    private function generateImageSitemapXml($urls)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . e($url['loc']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . e($url['lastmod']) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . e($url['changefreq']) . '</changefreq>' . "\n";
            $xml .= '    <priority>' . e($url['priority']) . '</priority>' . "\n";
            
            if ($url['image']) {
                $xml .= '    <image:image>' . "\n";
                $xml .= '      <image:loc>' . e($url['image']) . '</image:loc>' . "\n";
                $xml .= '      <image:caption>' . e('Logo du projet') . '</image:caption>' . "\n";
                $xml .= '    </image:image>' . "\n";
            }
            
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}