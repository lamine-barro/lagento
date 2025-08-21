#!/usr/bin/env python3
"""
Script intelligent de détection de doublons
Analyse multi-niveaux pour Opportunites_Complete_Finale.csv
"""

import pandas as pd
import numpy as np
import os
from difflib import SequenceMatcher
from collections import Counter
import re
from datetime import datetime
import logging

# Configuration du logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class IntelligentDuplicateChecker:
    def __init__(self, data_dir="/Users/laminebarro/agent-O/data"):
        self.data_dir = data_dir
        self.file_name = "Opportunites_Nettoyees_Finales.csv"
        self.similarity_threshold = 0.8  # Seuil de similarité pour détection
        
    def load_data(self):
        """Charge le dataset"""
        file_path = os.path.join(self.data_dir, self.file_name)
        return pd.read_csv(file_path)
    
    def normalize_text(self, text):
        """Normalise le texte pour comparaison"""
        if pd.isna(text) or text == "":
            return ""
        
        # Convertir en string et normaliser
        text = str(text).lower()
        
        # Supprimer la ponctuation et les espaces multiples
        text = re.sub(r'[^\w\s]', ' ', text)
        text = re.sub(r'\s+', ' ', text)
        text = text.strip()
        
        # Supprimer les mots courants
        stop_words = ['le', 'la', 'les', 'de', 'du', 'des', 'et', 'ou', 'pour', 'avec', 'dans', 'sur', 'un', 'une']
        words = [word for word in text.split() if word not in stop_words and len(word) > 2]
        
        return ' '.join(words)
    
    def calculate_similarity(self, text1, text2):
        """Calcule la similarité entre deux textes"""
        if not text1 or not text2:
            return 0.0
        
        return SequenceMatcher(None, text1, text2).ratio()
    
    def exact_duplicates_check(self, df):
        """Détecte les doublons exacts"""
        logger.info("=== DÉTECTION DOUBLONS EXACTS ===")
        
        # Doublons sur toutes les colonnes
        exact_all = df.duplicated()
        exact_count = exact_all.sum()
        
        if exact_count > 0:
            logger.info(f"🔍 Doublons exacts (toutes colonnes): {exact_count}")
            exact_rows = df[exact_all]
            for idx, row in exact_rows.iterrows():
                logger.info(f"  Ligne {idx}: {row['institution']} - {row['titre']}")
        else:
            logger.info("✅ Aucun doublon exact détecté")
        
        return exact_rows if exact_count > 0 else pd.DataFrame()
    
    def institutional_duplicates_check(self, df):
        """Détecte les doublons par institution + titre"""
        logger.info("\n=== DOUBLONS INSTITUTIONNELS ===")
        
        # Créer clé composite
        df_temp = df.copy()
        df_temp['inst_title_key'] = (
            df_temp['institution'].fillna('').astype(str).str.strip().str.upper() + 
            "|" + 
            df_temp['titre'].fillna('').astype(str).str.strip().str.upper()
        )
        
        # Détecter doublons
        institutional_dups = df_temp.duplicated(subset=['inst_title_key'])
        dup_count = institutional_dups.sum()
        
        if dup_count > 0:
            logger.info(f"🔍 Doublons institutionnels: {dup_count}")
            dup_rows = df_temp[institutional_dups]
            for idx, row in dup_rows.iterrows():
                logger.info(f"  Ligne {idx}: {row['institution']} - {row['titre']}")
        else:
            logger.info("✅ Aucun doublon institutionnel détecté")
        
        return df_temp[institutional_dups] if dup_count > 0 else pd.DataFrame()
    
    def semantic_similarity_check(self, df):
        """Détection intelligente par similarité sémantique"""
        logger.info("\n=== ANALYSE SIMILARITÉ SÉMANTIQUE ===")
        
        similar_pairs = []
        total_comparisons = 0
        
        # Normaliser les textes pour comparaison
        df_temp = df.copy()
        df_temp['normalized_title'] = df_temp['titre'].apply(self.normalize_text)
        df_temp['normalized_desc'] = df_temp['description'].apply(self.normalize_text)
        df_temp['normalized_inst'] = df_temp['institution'].apply(self.normalize_text)
        
        logger.info(f"Analyse de {len(df)} opportunités...")
        
        # Comparer chaque paire
        for i in range(len(df_temp)):
            for j in range(i + 1, len(df_temp)):
                total_comparisons += 1
                
                row1 = df_temp.iloc[i]
                row2 = df_temp.iloc[j]
                
                # Calculer similarités
                title_sim = self.calculate_similarity(row1['normalized_title'], row2['normalized_title'])
                desc_sim = self.calculate_similarity(row1['normalized_desc'], row2['normalized_desc'])
                inst_sim = self.calculate_similarity(row1['normalized_inst'], row2['normalized_inst'])
                
                # Score composite pondéré
                composite_score = (
                    title_sim * 0.4 +      # Titre: 40%
                    desc_sim * 0.4 +       # Description: 40%
                    inst_sim * 0.2         # Institution: 20%
                )
                
                # Détecter similarités élevées
                if composite_score >= self.similarity_threshold:
                    similar_pairs.append({
                        'index1': i,
                        'index2': j,
                        'institution1': row1['institution'],
                        'titre1': row1['titre'],
                        'institution2': row2['institution'],
                        'titre2': row2['titre'],
                        'title_similarity': title_sim,
                        'desc_similarity': desc_sim,
                        'inst_similarity': inst_sim,
                        'composite_score': composite_score
                    })
        
        logger.info(f"📊 {total_comparisons} comparaisons effectuées")
        logger.info(f"🎯 {len(similar_pairs)} paires similaires détectées (seuil: {self.similarity_threshold})")
        
        if similar_pairs:
            logger.info("\n🔍 Paires similaires détectées:")
            for pair in similar_pairs:
                logger.info(f"  Similarité {pair['composite_score']:.3f}:")
                logger.info(f"    [{pair['index1']}] {pair['institution1']} - {pair['titre1']}")
                logger.info(f"    [{pair['index2']}] {pair['institution2']} - {pair['titre2']}")
                logger.info(f"    Détail: Titre={pair['title_similarity']:.3f}, Desc={pair['desc_similarity']:.3f}, Inst={pair['inst_similarity']:.3f}")
                logger.info("")
        
        return similar_pairs
    
    def program_name_variations_check(self, df):
        """Détecte les variations de noms de programmes"""
        logger.info("=== VARIATIONS NOMS DE PROGRAMMES ===")
        
        variations = []
        
        # Normaliser les titres
        df_temp = df.copy()
        df_temp['clean_title'] = df_temp['titre'].apply(self.normalize_text)
        
        # Grouper par mots clés communs
        title_groups = {}
        for idx, row in df_temp.iterrows():
            words = row['clean_title'].split()
            if len(words) >= 2:
                # Utiliser les 2 premiers mots comme clé
                key = ' '.join(words[:2])
                if key not in title_groups:
                    title_groups[key] = []
                title_groups[key].append((idx, row['institution'], row['titre']))
        
        # Identifier les groupes avec variations
        for key, items in title_groups.items():
            if len(items) > 1:
                # Vérifier si ce sont vraiment des variations
                institutions = [item[1] for item in items]
                if len(set(institutions)) > 1:  # Différentes institutions
                    variations.append({
                        'keyword': key,
                        'programs': items
                    })
        
        if variations:
            logger.info(f"🔍 {len(variations)} groupes de variations détectés:")
            for var in variations:
                logger.info(f"  Mot-clé: '{var['keyword']}'")
                for idx, inst, title in var['programs']:
                    logger.info(f"    [{idx}] {inst} - {title}")
                logger.info("")
        else:
            logger.info("✅ Aucune variation de nom détectée")
        
        return variations
    
    def institution_consolidation_check(self, df):
        """Vérifie les variations de noms d'institutions"""
        logger.info("=== CONSOLIDATION INSTITUTIONS ===")
        
        institution_variations = []
        
        # Normaliser les noms d'institutions
        df_temp = df.copy()
        df_temp['clean_institution'] = df_temp['institution'].apply(self.normalize_text)
        
        # Grouper par similarité
        institutions = df_temp['clean_institution'].unique()
        checked_pairs = set()
        
        for i, inst1 in enumerate(institutions):
            if not inst1:
                continue
            for j, inst2 in enumerate(institutions):
                if i >= j or not inst2 or (i, j) in checked_pairs:
                    continue
                
                checked_pairs.add((i, j))
                similarity = self.calculate_similarity(inst1, inst2)
                
                if similarity >= 0.7:  # Seuil plus bas pour institutions
                    # Récupérer les lignes correspondantes
                    rows1 = df_temp[df_temp['clean_institution'] == inst1]
                    rows2 = df_temp[df_temp['clean_institution'] == inst2]
                    
                    institution_variations.append({
                        'similarity': similarity,
                        'institution1': rows1['institution'].iloc[0],
                        'institution2': rows2['institution'].iloc[0],
                        'count1': len(rows1),
                        'count2': len(rows2)
                    })
        
        if institution_variations:
            logger.info(f"🔍 {len(institution_variations)} variations d'institutions détectées:")
            for var in institution_variations:
                logger.info(f"  Similarité {var['similarity']:.3f}:")
                logger.info(f"    {var['institution1']} ({var['count1']} opportunités)")
                logger.info(f"    {var['institution2']} ({var['count2']} opportunités)")
                logger.info("")
        else:
            logger.info("✅ Aucune variation d'institution détectée")
        
        return institution_variations
    
    def cross_reference_analysis(self, df):
        """Analyse croisée pour détecter des incohérences"""
        logger.info("=== ANALYSE CROISÉE COHÉRENCE ===")
        
        issues = []
        
        # 1. Même institution, types différents pour programmes similaires
        df_grouped = df.groupby('institution')
        for institution, group in df_grouped:
            if len(group) > 1:
                types = group['type'].unique()
                if len(types) > 1:
                    # Vérifier si les titres sont similaires
                    titles = group['titre'].tolist()
                    for i, title1 in enumerate(titles):
                        for j, title2 in enumerate(titles[i+1:], i+1):
                            sim = self.calculate_similarity(
                                self.normalize_text(title1), 
                                self.normalize_text(title2)
                            )
                            if sim > 0.6:
                                issues.append({
                                    'type': 'type_inconsistency',
                                    'institution': institution,
                                    'program1': title1,
                                    'program2': title2,
                                    'type1': group.iloc[i]['type'],
                                    'type2': group.iloc[j]['type'],
                                    'similarity': sim
                                })
        
        # 2. URLs identiques pour institutions différentes
        url_groups = df.groupby('lien_externe')
        for url, group in url_groups:
            if len(group) > 1 and url and url != "":
                institutions = group['institution'].unique()
                if len(institutions) > 1:
                    issues.append({
                        'type': 'shared_url',
                        'url': url,
                        'institutions': institutions.tolist(),
                        'count': len(group)
                    })
        
        if issues:
            logger.info(f"⚠️ {len(issues)} incohérences détectées:")
            for issue in issues:
                if issue['type'] == 'type_inconsistency':
                    logger.info(f"  Type incohérent - {issue['institution']}:")
                    logger.info(f"    {issue['program1']} ({issue['type1']})")
                    logger.info(f"    {issue['program2']} ({issue['type2']})")
                    logger.info(f"    Similarité: {issue['similarity']:.3f}")
                elif issue['type'] == 'shared_url':
                    logger.info(f"  URL partagée: {issue['url']}")
                    logger.info(f"    Institutions: {', '.join(issue['institutions'])}")
                logger.info("")
        else:
            logger.info("✅ Aucune incohérence détectée")
        
        return issues
    
    def generate_comprehensive_report(self, exact_dups, institutional_dups, similar_pairs, variations, institution_vars, issues):
        """Génère un rapport complet"""
        report = f"""
=== RAPPORT INTELLIGENT DÉTECTION DOUBLONS ===
Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
Fichier analysé: {self.file_name}

📊 RÉSUMÉ EXÉCUTIF:
- Doublons exacts: {len(exact_dups)}
- Doublons institutionnels: {len(institutional_dups)}
- Paires similaires (seuil {self.similarity_threshold}): {len(similar_pairs)}
- Variations de programmes: {len(variations)}
- Variations d'institutions: {len(institution_vars)}
- Incohérences détectées: {len(issues)}

🔍 DÉTAIL DES ANALYSES:

1. DOUBLONS EXACTS:
   Status: {'❌ Détectés' if len(exact_dups) > 0 else '✅ Aucun'}
   Action: {'Suppression recommandée' if len(exact_dups) > 0 else 'Aucune action requise'}

2. DOUBLONS INSTITUTIONNELS:
   Status: {'❌ Détectés' if len(institutional_dups) > 0 else '✅ Aucun'}
   Action: {'Consolidation requise' if len(institutional_dups) > 0 else 'Aucune action requise'}

3. SIMILARITÉ SÉMANTIQUE:
   Status: {'⚠️ Similarités détectées' if len(similar_pairs) > 0 else '✅ Pas de similarité excessive'}
   Action: {'Révision manuelle recommandée' if len(similar_pairs) > 0 else 'Aucune action requise'}

4. VARIATIONS DE PROGRAMMES:
   Status: {'ℹ️ Variations détectées' if len(variations) > 0 else '✅ Noms cohérents'}
   Action: {'Standardisation possible' if len(variations) > 0 else 'Aucune action requise'}

5. VARIATIONS D'INSTITUTIONS:
   Status: {'ℹ️ Variations détectées' if len(institution_vars) > 0 else '✅ Noms cohérents'}
   Action: {'Consolidation recommandée' if len(institution_vars) > 0 else 'Aucune action requise'}

6. INCOHÉRENCES CROISÉES:
   Status: {'⚠️ Incohérences détectées' if len(issues) > 0 else '✅ Données cohérentes'}
   Action: {'Correction recommandée' if len(issues) > 0 else 'Aucune action requise'}

🎯 RECOMMANDATIONS:

PRIORITÉ ÉLEVÉE:
- {'Supprimer les doublons exacts identifiés' if len(exact_dups) > 0 else 'Aucune action critique'}
- {'Consolider les doublons institutionnels' if len(institutional_dups) > 0 else 'Structure satisfaisante'}

PRIORITÉ MOYENNE:
- {'Réviser les paires similaires pour éviter confusion utilisateur' if len(similar_pairs) > 0 else 'Clarté satisfaisante'}
- {'Corriger les incohérences de types et URLs' if len(issues) > 0 else 'Cohérence satisfaisante'}

PRIORITÉ FAIBLE:
- {'Standardiser les noms de programmes similaires' if len(variations) > 0 else 'Nommage satisfaisant'}
- {'Unifier les variations de noms d institutions' if len(institution_vars) > 0 else 'Institutions bien définies'}

📈 SCORE DE QUALITÉ:
"""
        
        # Calcul du score de qualité
        total_issues = len(exact_dups) + len(institutional_dups) + len(similar_pairs) + len(issues)
        if total_issues == 0:
            quality_score = 100
        elif total_issues <= 5:
            quality_score = 85
        elif total_issues <= 10:
            quality_score = 70
        else:
            quality_score = max(50, 90 - total_issues * 2)
        
        report += f"Score: {quality_score}/100\n"
        
        if quality_score >= 90:
            report += "Statut: ✅ EXCELLENT - Dataset prêt production\n"
        elif quality_score >= 75:
            report += "Statut: ⚠️ BON - Corrections mineures recommandées\n"
        else:
            report += "Statut: ❌ AMÉLIORATIONS REQUISES - Nettoyage nécessaire\n"
        
        report += f"""
🔧 ACTIONS AUTOMATISABLES:
- Script de suppression des doublons exacts
- Consolidation automatique par règles de similarité
- Standardisation des noms par mappings
- Validation croisée des URLs et contacts

💡 VALIDATION MANUELLE RECOMMANDÉE:
- Révision des paires à similarité élevée
- Vérification des variations d'institutions
- Validation de la cohérence sectorielle
        """
        
        return report, quality_score
    
    def run_intelligent_check(self):
        """Exécute l'analyse complète de détection intelligente"""
        logger.info("🧠 DÉMARRAGE ANALYSE INTELLIGENTE DOUBLONS")
        logger.info("="*70)
        
        try:
            # Charger les données
            df = self.load_data()
            logger.info(f"📊 Dataset chargé: {len(df)} opportunités")
            
            # 1. Doublons exacts
            exact_dups = self.exact_duplicates_check(df)
            
            # 2. Doublons institutionnels
            institutional_dups = self.institutional_duplicates_check(df)
            
            # 3. Similarité sémantique
            similar_pairs = self.semantic_similarity_check(df)
            
            # 4. Variations de programmes
            variations = self.program_name_variations_check(df)
            
            # 5. Variations d'institutions
            institution_vars = self.institution_consolidation_check(df)
            
            # 6. Analyse croisée
            issues = self.cross_reference_analysis(df)
            
            # 7. Rapport complet
            report, quality_score = self.generate_comprehensive_report(
                exact_dups, institutional_dups, similar_pairs, 
                variations, institution_vars, issues
            )
            
            # Sauvegarder le rapport
            report_path = os.path.join(self.data_dir, "rapport_doublons_intelligent.txt")
            with open(report_path, 'w', encoding='utf-8') as f:
                f.write(report)
            
            logger.info(f"📋 Rapport sauvegardé: {report_path}")
            print(report)
            
            return {
                'success': True,
                'quality_score': quality_score,
                'total_issues': len(exact_dups) + len(institutional_dups) + len(similar_pairs) + len(issues),
                'report_file': report_path,
                'details': {
                    'exact_duplicates': len(exact_dups),
                    'institutional_duplicates': len(institutional_dups),
                    'similar_pairs': len(similar_pairs),
                    'program_variations': len(variations),
                    'institution_variations': len(institution_vars),
                    'cross_issues': len(issues)
                }
            }
            
        except Exception as e:
            logger.error(f"❌ Erreur analyse: {str(e)}")
            return {'success': False, 'error': str(e)}

if __name__ == "__main__":
    checker = IntelligentDuplicateChecker()
    result = checker.run_intelligent_check()
    
    if result['success']:
        print(f"\n✅ Analyse terminée!")
        print(f"🎯 Score qualité: {result['quality_score']}/100")
        print(f"🔍 Issues détectées: {result['total_issues']}")
        print(f"📊 Détail: {result['details']}")
    else:
        print(f"❌ Erreur: {result['error']}")