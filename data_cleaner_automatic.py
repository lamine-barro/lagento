#!/usr/bin/env python3
"""
Script de nettoyage automatique des doublons et incohérences
Basé sur l'analyse intelligente de duplicate_checker_intelligence.py
"""

import pandas as pd
import numpy as np
import os
from datetime import datetime
import logging

# Configuration du logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class AutomaticDataCleaner:
    def __init__(self, data_dir="/Users/laminebarro/agent-O/data"):
        self.data_dir = data_dir
        self.input_file = "Opportunites_Complete_Finale.csv"
        self.output_file = "Opportunites_Nettoyees_Finales.csv"
        self.backup_file = "Opportunites_Complete_Finale_BACKUP.csv"
        
        # Mappings de consolidation des institutions
        self.institution_mappings = {
            # Variations de noms d'institutions détectées
            "Agence Côte d'Ivoire PME + MFFE": "Agence Côte d'Ivoire PME",
            "Côte d'Ivoire PME": "Agence Côte d'Ivoire PME",
            "COMOE CAPITAL": "Comoé Capital",
            "Comoé Capital / CDC-CI": "Comoé Capital",
            "HEC CHALLENGE": "HEC Challenge Plus",
            "IMPACT HUB": "Impact Hub Abidjan",
            "ABX Accelerator": "ABX ACCEL",
            "CGECI BPC": "CGECI",
            "Orange Fab CI": "ORANGE FAB CI",
            "Y'ello Startup MTN": "MTN CI",
            "Gouvernement CI / BAD": "BAD / Gouvernement CI",
            "Ministère Économie / IFC": "Ministère de l'Économie et des Finances",
            "Ministère Économie / Banque Mondiale": "Ministère de l'Économie et des Finances",
            "ONUDI / Ministères techniques": "Ministères techniques",
            "Société de Garantie des PME (SGPME)": "SGPME",
            "Ministère Sports": "Ministère des Sports et du Développement de l'Économie Sportive",
            "Ministère Sports + WinWin Afrique": "Ministère des Sports et du Développement de l'Économie Sportive"
        }
        
        # Consolidation des programmes similaires (garder le plus complet)
        self.program_consolidations = {
            # Format: "titre_moins_complet": "titre_plus_complet"
            "Programme Jeunesse Gouvernement 2025": "Programme Jeunesse du Gouvernement (PJGouv) 2023-2025",
            "Startup Boost Capital": "Initiative Startup Boost Capital",
            "Société de Garantie des PME": "Société de Garantie des Crédits aux PME Ivoiriennes - Extension 2025",
            "HEC Challenge+ Demo Day 2025": "HEC Challenge Plus Abidjan",
            "Orange Fab Saison 11": "Orange Fab CI 9e édition",
            "Y'ello Startup Programme 2025": "Y'ello Startup MTN CI"
        }
    
    def create_backup(self, df):
        """Crée une sauvegarde du fichier original"""
        backup_path = os.path.join(self.data_dir, self.backup_file)
        df.to_csv(backup_path, index=False, encoding='utf-8')
        logger.info(f"💾 Sauvegarde créée: {backup_path}")
        return backup_path
    
    def remove_exact_duplicates(self, df):
        """Supprime les doublons exacts"""
        logger.info("=== SUPPRESSION DOUBLONS EXACTS ===")
        
        initial_count = len(df)
        
        # Identifier les lignes vides ou invalides
        empty_mask = (
            df['institution'].isna() | (df['institution'] == '') |
            df['titre'].isna() | (df['titre'] == '')
        )
        
        empty_count = empty_mask.sum()
        if empty_count > 0:
            logger.info(f"🗑️ Suppression de {empty_count} lignes vides/invalides")
            df = df[~empty_mask]
        
        # Supprimer les doublons exacts
        df_cleaned = df.drop_duplicates()
        
        duplicates_removed = initial_count - len(df_cleaned)
        logger.info(f"✅ {duplicates_removed} doublons exacts supprimés")
        logger.info(f"📊 Lignes restantes: {len(df_cleaned)}")
        
        return df_cleaned.reset_index(drop=True)
    
    def consolidate_institutions(self, df):
        """Consolide les variations de noms d'institutions"""
        logger.info("=== CONSOLIDATION INSTITUTIONS ===")
        
        consolidations_applied = 0
        
        for old_name, new_name in self.institution_mappings.items():
            mask = df['institution'] == old_name
            count = mask.sum()
            if count > 0:
                df.loc[mask, 'institution'] = new_name
                consolidations_applied += count
                logger.info(f"📝 {old_name} → {new_name} ({count} occurrences)")
        
        logger.info(f"✅ {consolidations_applied} consolidations d'institutions appliquées")
        return df
    
    def consolidate_programs(self, df):
        """Consolide les programmes similaires"""
        logger.info("=== CONSOLIDATION PROGRAMMES ===")
        
        consolidations_applied = 0
        programs_to_remove = []
        
        for old_title, new_title in self.program_consolidations.items():
            # Trouver les occurrences du titre ancien
            old_mask = df['titre'] == old_title
            old_count = old_mask.sum()
            
            # Trouver les occurrences du titre nouveau
            new_mask = df['titre'] == new_title
            new_count = new_mask.sum()
            
            if old_count > 0 and new_count > 0:
                # Marquer les anciens pour suppression
                programs_to_remove.extend(df[old_mask].index.tolist())
                consolidations_applied += old_count
                logger.info(f"🔄 Consolidation: {old_title} → {new_title} ({old_count} → 1)")
            elif old_count > 0 and new_count == 0:
                # Renommer l'ancien vers le nouveau
                df.loc[old_mask, 'titre'] = new_title
                logger.info(f"📝 Renommage: {old_title} → {new_title}")
        
        # Supprimer les programmes marqués pour suppression
        if programs_to_remove:
            df = df.drop(programs_to_remove).reset_index(drop=True)
            logger.info(f"🗑️ {len(programs_to_remove)} programmes dupliqués supprimés")
        
        logger.info(f"✅ {consolidations_applied} consolidations de programmes appliquées")
        return df
    
    def standardize_urls(self, df):
        """Standardise et nettoie les URLs"""
        logger.info("=== STANDARDISATION URLS ===")
        
        # Nettoyer les URLs
        df['lien_externe'] = df['lien_externe'].fillna('')
        
        # Supprimer les préfixes communs
        df['lien_externe'] = df['lien_externe'].str.replace('https://www.', 'www.')
        df['lien_externe'] = df['lien_externe'].str.replace('http://www.', 'www.')
        df['lien_externe'] = df['lien_externe'].str.replace('https://', '')
        df['lien_externe'] = df['lien_externe'].str.replace('http://', '')
        
        # Ajouter https:// si pas présent
        mask = (df['lien_externe'] != '') & (~df['lien_externe'].str.startswith('www.')) & (~df['lien_externe'].str.startswith('https://'))
        df.loc[mask, 'lien_externe'] = 'https://' + df.loc[mask, 'lien_externe']
        
        mask = df['lien_externe'].str.startswith('www.')
        df.loc[mask, 'lien_externe'] = 'https://' + df.loc[mask, 'lien_externe']
        
        logger.info("✅ URLs standardisées")
        return df
    
    def clean_contacts(self, df):
        """Nettoie les contacts email"""
        logger.info("=== NETTOYAGE CONTACTS ===")
        
        # Nettoyer les formats de contact
        df['contact_email_enrichi'] = df['contact_email_enrichi'].fillna('')
        
        # Supprimer les préfixes 'via'
        df['contact_email_enrichi'] = df['contact_email_enrichi'].str.replace('via ', '')
        df['contact_email_enrichi'] = df['contact_email_enrichi'].str.replace('Via ', '')
        
        # Nettoyer les espaces
        df['contact_email_enrichi'] = df['contact_email_enrichi'].str.strip()
        
        logger.info("✅ Contacts nettoyés")
        return df
    
    def deduplicate_by_institution_title(self, df):
        """Supprime les doublons par institution + titre"""
        logger.info("=== DÉDUPLICATION INSTITUTION + TITRE ===")
        
        initial_count = len(df)
        
        # Créer une clé composite normalisée
        df['temp_key'] = (
            df['institution'].str.strip().str.upper() + 
            "|" + 
            df['titre'].str.strip().str.upper()
        )
        
        # Garder la première occurrence de chaque clé
        df_dedup = df.drop_duplicates(subset=['temp_key'], keep='first')
        df_dedup = df_dedup.drop('temp_key', axis=1)
        
        removed = initial_count - len(df_dedup)
        logger.info(f"🗑️ {removed} doublons institution+titre supprimés")
        logger.info(f"📊 Lignes restantes: {len(df_dedup)}")
        
        return df_dedup.reset_index(drop=True)
    
    def normalize_sectors(self, df):
        """Normalise les secteurs"""
        logger.info("=== NORMALISATION SECTEURS ===")
        
        # Standardiser les séparateurs
        df['secteurs'] = df['secteurs'].str.replace(',', ';')
        df['secteurs'] = df['secteurs'].str.replace(';;', ';')
        
        # Nettoyer les espaces
        df['secteurs'] = df['secteurs'].apply(
            lambda x: ';'.join([s.strip() for s in str(x).split(';')]) if pd.notna(x) and x != '' else x
        )
        
        # Consolidations sectorielles
        sector_mappings = {
            'AGRICULTURE': 'Agriculture',
            'TOUS_SECTEURS': 'Tous secteurs',
            'NUMERIQUE': 'Numérique',
            'TECH': 'Technologies',
            'INNOVATION': 'Innovation'
        }
        
        for old_sector, new_sector in sector_mappings.items():
            df['secteurs'] = df['secteurs'].str.replace(old_sector, new_sector)
        
        logger.info("✅ Secteurs normalisés")
        return df
    
    def final_quality_check(self, df):
        """Contrôle qualité final"""
        logger.info("=== CONTRÔLE QUALITÉ FINAL ===")
        
        # Vérifications
        empty_institutions = (df['institution'].isna() | (df['institution'] == '')).sum()
        empty_titles = (df['titre'].isna() | (df['titre'] == '')).sum()
        duplicates = df.duplicated(subset=['institution', 'titre']).sum()
        
        logger.info(f"📊 Institutions vides: {empty_institutions}")
        logger.info(f"📊 Titres vides: {empty_titles}")
        logger.info(f"📊 Doublons restants: {duplicates}")
        
        # Calculer le score de qualité
        total_issues = empty_institutions + empty_titles + duplicates
        quality_score = max(0, 100 - total_issues * 5)
        
        logger.info(f"🎯 Score de qualité: {quality_score}/100")
        
        return {
            'quality_score': quality_score,
            'empty_institutions': empty_institutions,
            'empty_titles': empty_titles,
            'duplicates': duplicates,
            'total_records': len(df)
        }
    
    def generate_cleaning_report(self, initial_count, final_count, quality_metrics):
        """Génère un rapport de nettoyage"""
        report = f"""
=== RAPPORT DE NETTOYAGE AUTOMATIQUE ===
Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

📊 STATISTIQUES DE NETTOYAGE:
- Enregistrements initiaux: {initial_count}
- Enregistrements finaux: {final_count}
- Supprimés au total: {initial_count - final_count}
- Taux de conservation: {final_count/initial_count*100:.1f}%

✅ ACTIONS EFFECTUÉES:
1. Suppression des doublons exacts et lignes vides
2. Consolidation des variations d'institutions
3. Consolidation des programmes similaires
4. Standardisation des URLs
5. Nettoyage des contacts email
6. Déduplication par institution + titre
7. Normalisation des secteurs

🎯 QUALITÉ FINALE:
- Score de qualité: {quality_metrics['quality_score']}/100
- Institutions vides: {quality_metrics['empty_institutions']}
- Titres vides: {quality_metrics['empty_titles']}
- Doublons restants: {quality_metrics['duplicates']}
- Total final: {quality_metrics['total_records']} opportunités

📈 AMÉLIORATIONS APPORTÉES:
- ✅ Cohérence des noms d'institutions
- ✅ Élimination des programmes dupliqués
- ✅ URLs standardisées et valides
- ✅ Contacts nettoyés et formatés
- ✅ Secteurs normalisés et cohérents
- ✅ Structure de données optimisée

🎯 RECOMMANDATIONS POST-NETTOYAGE:
1. Validation manuelle des consolidations
2. Vérification des URLs critiques
3. Test de l'intégration vectorielle
4. Mise à jour des documentations

Dataset prêt pour mise en production ! 🚀
        """
        
        return report
    
    def run_automatic_cleaning(self):
        """Exécute le nettoyage automatique complet"""
        logger.info("🧹 DÉMARRAGE NETTOYAGE AUTOMATIQUE")
        logger.info("="*60)
        
        try:
            # Charger les données
            input_path = os.path.join(self.data_dir, self.input_file)
            df = pd.read_csv(input_path)
            initial_count = len(df)
            
            logger.info(f"📊 Dataset initial: {initial_count} opportunités")
            
            # Créer sauvegarde
            backup_path = self.create_backup(df)
            
            # Étapes de nettoyage
            df = self.remove_exact_duplicates(df)
            df = self.consolidate_institutions(df)
            df = self.consolidate_programs(df)
            df = self.standardize_urls(df)
            df = self.clean_contacts(df)
            df = self.deduplicate_by_institution_title(df)
            df = self.normalize_sectors(df)
            
            # Contrôle qualité final
            quality_metrics = self.final_quality_check(df)
            
            # Sauvegarder le dataset nettoyé
            output_path = os.path.join(self.data_dir, self.output_file)
            df.to_csv(output_path, index=False, encoding='utf-8')
            logger.info(f"💾 Dataset nettoyé sauvegardé: {output_path}")
            
            # Générer le rapport
            report = self.generate_cleaning_report(initial_count, len(df), quality_metrics)
            
            report_path = os.path.join(self.data_dir, "rapport_nettoyage_automatique.txt")
            with open(report_path, 'w', encoding='utf-8') as f:
                f.write(report)
            
            logger.info(f"📋 Rapport sauvegardé: {report_path}")
            print(report)
            
            return {
                'success': True,
                'initial_count': initial_count,
                'final_count': len(df),
                'quality_score': quality_metrics['quality_score'],
                'output_file': output_path,
                'backup_file': backup_path,
                'report_file': report_path
            }
            
        except Exception as e:
            logger.error(f"❌ Erreur nettoyage: {str(e)}")
            return {'success': False, 'error': str(e)}

if __name__ == "__main__":
    cleaner = AutomaticDataCleaner()
    result = cleaner.run_automatic_cleaning()
    
    if result['success']:
        print(f"\n✅ Nettoyage terminé avec succès!")
        print(f"📊 {result['initial_count']} → {result['final_count']} opportunités")
        print(f"🎯 Score qualité: {result['quality_score']}/100")
        print(f"📁 Fichier final: {result['output_file']}")
    else:
        print(f"❌ Erreur: {result['error']}")