# Guide d'utilisation - Enrichissement des Opportunités

## 📋 Aperçu

Ce script enrichit automatiquement votre base de données d'opportunités en utilisant:
- **OpenAI GPT** pour la classification et l'enrichissement des données
- **Brave Search API** pour la recherche d'informations et liens de candidature
- **Estimation automatique** de la taille des opportunités en FCFA
- **Système de logs avancé** pour audit et correction

## 🚀 Utilisation

### 1. Exécution complète
```bash
python3 enrich_opportunities.py
```

### 2. Test sur échantillon
```bash
python3 test_enrichment.py
```

### 3. Test avancé (nouvelles fonctionnalités)
```bash
python3 test_enrichment_advanced.py
```

## 📊 Fonctionnalités

### Nettoyage des données
- Standardisation des secteurs selon vos constantes
- Normalisation des types d'opportunités
- Standardisation des régions de Côte d'Ivoire
- Nettoyage des données textuelles

### Classification automatique
- **Types d'institutions** selon vos 20 catégories définies
- **Secteurs d'activité** standardisés
- **Types d'opportunités** normalisés

### Enrichissement intelligent avec Brave Search + OpenAI
- **Recherche automatique** d'informations supplémentaires sur internet
- **Descriptions enrichies** avec données trouvées en ligne
- **Critères d'éligibilité clarifiés** avec informations actualisées
- **Contacts email** manquants complétés
- **Informations complémentaires** synthétisées par l'IA

### Estimation taille opportunités en FCFA
- **Détection automatique** des montants dans les descriptions
- **Conversion automatique** EUR/USD vers FCFA (taux actuels)
- **Estimation par IA** si aucun montant détecté
- **Valeurs par défaut** intelligentes selon le type d'opportunité

### Recherche de liens avancée
- **Liens de candidature** trouvés automatiquement via Brave Search
- **Priorité aux liens officiels** contenant "candidature", "application", etc.
- **Recherche contextuelle** avec mots-clés pertinents

### Système de logs pour audit
- **Logs détaillés** dans `/logs/enrichment_YYYYMMDD_HHMMSS.log`
- **Traçabilité complète** de chaque opération
- **Gestion d'erreurs** avec fallbacks automatiques
- **Statistiques détaillées** de performance

## 📈 Résultats attendus

### Nouvelles colonnes ajoutées:
- `institution_type`: Catégorie de l'institution (20 types)
- `institution_clean`: Nom sans underscores
- `secteurs_clean`: Secteurs standardisés 
- `type_clean`: Type d'opportunité standardisé
- `regions_clean`: Régions standardisées
- `statut_clean`: Statut simplifié (A_VENIR, ACTIF, FERME)
- `taille_total_opportunite_fcfa`: **Estimation en FCFA** 🆕
- `description_enrichie`: Description améliorée avec recherche web
- `criteres_eligibilite_enrichis`: Critères clarifiés
- `contact_email_enrichi`: Email de contact trouvé
- `informations_complementaires`: Infos supplémentaires trouvées 🆕
- `lien_candidature_trouve`: Lien de candidature via recherche
- `date_traitement`: Date du traitement

### Statistiques générées:
- Répartition par type d'institution
- Répartition par type d'opportunité  
- Répartition par statut (3 catégories)
- **Taille totale des opportunités en FCFA** 🆕
- **Taille moyenne par opportunité** 🆕
- **Top 5 des plus grosses opportunités** 🆕
- Pourcentage de liens de candidature trouvés
- Pourcentage de noms d'institutions nettoyés

## ⚙️ Configuration

### Variables d'environnement (.env)
```bash
OPENAI_API_KEY=your_openai_api_key
BRAVE_SEARCH_API_KEY=your_brave_api_key
```

### Fichiers d'entrée/sortie
- **Entrée**: `/data/opportunites_prod.csv`
- **Sortie**: `/data/opportunites_enrichies.csv`
- **Sauvegarde temporaire**: `/data/opportunites_enrichies.csv.tmp`

## 🔧 Personnalisation

### Modification des constantes
Éditez le fichier `enrich_opportunities.py` pour ajuster:
- `REGIONS_CI`: Nouvelles régions
- `SECTEURS`: Nouveaux secteurs
- `TYPES_OPPORTUNITES`: Nouveaux types
- `CATEGORIES_INSTITUTIONS`: Nouvelles catégories

### Ajustement des prompts OpenAI
Modifiez les prompts dans les méthodes:
- `classify_institution()`: Classification des institutions
- `enrich_opportunity()`: Enrichissement des données

## 📊 Coûts estimés

### OpenAI API (GPT-3.5-turbo)
- ~$0.002 par opportunité pour classification + enrichissement
- Pour 100 opportunités: ~$0.20

### Brave Search API
- Gratuit jusqu'à 2000 requêtes/mois
- ~1 requête par opportunité sans lien externe

## 🛡️ Sécurité et bonnes pratiques

### Rate limiting
- Pause de 0.5s entre chaque traitement
- Pause de 1s entre les recherches Brave
- Traitement par lots de 10

### Gestion d'erreurs
- Logs détaillés de tous les traitements
- Sauvegarde intermédiaire tous les 50 éléments
- Catégories par défaut en cas d'erreur de classification

### Sauvegarde
- Fichier temporaire créé pendant le traitement
- Sauvegarde finale uniquement en cas de succès complet

## 🔍 Débogage

### Logs détaillés
Le script génère des logs complets pour:
- Progression du traitement
- Erreurs d'API
- Statistiques finales

### Mode test
Utilisez `test_enrichment.py` pour:
- Vérifier la configuration
- Tester sur un échantillon réduit
- Valider les modifications avant traitement complet

## 📞 Support

En cas de problème:
1. Vérifiez les clés API dans les variables d'environnement
2. Consultez les logs pour identifier l'erreur
3. Testez d'abord avec `test_enrichment.py`
4. Ajustez les paramètres de rate limiting si nécessaire