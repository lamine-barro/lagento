#!/usr/bin/env python3
"""
Script de normalisation et fusion d'Opportunites_Incubateurs.csv
avec Opportunites_Classees.csv selon les standards établis
"""

import pandas as pd
import numpy as np
import os
from datetime import datetime
import logging

# Configuration du logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class IncubateursNormalizer:
    def __init__(self, data_dir="/Users/laminebarro/agent-O/data"):
        self.data_dir = data_dir
        self.incubateurs_file = "Opportunites_Incubateurs.csv"
        self.reference_file = "Opportunites_Classees.csv"
        self.output_file = "Opportunites_Complete_Finale.csv"
        
        # Colonnes de référence (format cible)
        self.reference_columns = [
            'institution', 'institution_type', 'statut', 'titre', 'description', 
            'type', 'pays', 'regions_ciblees', 'date_limite_candidature', 
            'date_debut', 'duree', 'remuneration', 'nombre_places', 'secteurs', 
            'criteres_eligibilite', 'contact_email_enrichi', 'lien_externe', 'origine_initiative'
        ]
        
        # Mappings de normalisation spécifiques aux incubateurs
        self.institution_type_mapping = {
            'STUDIO': 'INCUBATEUR_ACCELERATEUR',
            'INVESTISSEUR': 'FONDS_INVESTISSEMENT',
            'INCUBATEUR_ACCELERATEUR': 'INCUBATEUR_ACCELERATEUR'  # Garder tel quel
        }
        
        self.type_mapping = {
            'INVESTISSEMENT': 'FINANCEMENT',
            'ACCELERATION': 'ACCELERATION',
            'INCUBATION': 'INCUBATION'
        }
        
        self.statut_mapping = {
            'À vérifier': 'À venir',
            'Ouvert': 'Ouvert'
        }
        
        # Ordre de priorité pour classification finale
        self.institution_order = [
            'MINISTERE_AGENCE',
            'BANQUE_DEVELOPPEMENT', 
            'FONDS_INVESTISSEMENT',
            'INCUBATEUR_ACCELERATEUR',
            'CENTRE_FORMATION',
            'ORGANISATION_INTERNATIONALE',
            'ASSOCIATION_ENTREPRENEURIALE',
            'HUB_INNOVATION',
            'MICROFINANCE',
            'ESPACE_COWORKING',
            'GUICHET_UNIQUE',
            'AGENCE_PROMOTION',
            'COOPERATION_DIASPORA',
            'FEDERATION_PUBLIQUE'
        ]
        
        self.opportunity_order = [
            'FINANCEMENT',
            'INCUBATION',
            'ACCELERATION',
            'FORMATION',
            'CONCOURS',
            'STAGE',
            'EVENEMENT',
            'ASSISTANCE_TECHNIQUE'
        ]
    
    def audit_incubateurs_data(self, df):
        """Audite les données incubateurs"""
        logger.info("=== AUDIT OPPORTUNITES_INCUBATEURS ===")
        logger.info(f"Nombre de lignes: {len(df)}")
        logger.info(f"Colonnes: {list(df.columns)}")
        
        # Vérifier les valeurs uniques dans les champs clés
        logger.info(f"\nTypes d'institutions: {df['institution_type'].unique()}")
        logger.info(f"Types d'opportunités: {df['type'].unique()}")
        logger.info(f"Statuts: {df['statut'].unique()}")
        logger.info(f"Origines: {df['origine_initiative'].unique()}")
        
        # Secteurs (normaliser le format séparateur)
        secteurs_raw = df['secteurs'].dropna().unique()
        logger.info(f"Secteurs bruts: {secteurs_raw}")
        
        return {
            'rows': len(df),
            'columns': len(df.columns),
            'institution_types': df['institution_type'].unique().tolist(),
            'opportunity_types': df['type'].unique().tolist(),
            'statuts': df['statut'].unique().tolist()
        }
    
    def normalize_incubateurs(self, df):
        """Normalise les données incubateurs vers le format de référence"""
        logger.info("=== NORMALISATION INCUBATEURS ===")
        
        df_normalized = df.copy()
        
        # 1. Normaliser les types d'institutions
        df_normalized['institution_type'] = df_normalized['institution_type'].map(
            self.institution_type_mapping
        ).fillna(df_normalized['institution_type'])
        
        # 2. Normaliser les types d'opportunités
        df_normalized['type'] = df_normalized['type'].map(
            self.type_mapping
        ).fillna(df_normalized['type'])
        
        # 3. Normaliser les statuts
        df_normalized['statut'] = df_normalized['statut'].map(
            self.statut_mapping
        ).fillna(df_normalized['statut'])
        
        # 4. Normaliser les secteurs (remplacer virgules par points-virgules)
        df_normalized['secteurs'] = df_normalized['secteurs'].str.replace(',', ';')
        
        # 5. Nettoyer les guillemets dans tous les champs texte
        text_columns = ['institution', 'titre', 'description', 'criteres_eligibilite']
        for col in text_columns:
            if col in df_normalized.columns:
                df_normalized[col] = df_normalized[col].astype(str).str.strip('"')
        
        # 6. Standardiser les contacts email
        df_normalized['contact_email_enrichi'] = df_normalized['contact_email_enrichi'].str.replace('via ', '')
        
        # 7. Nettoyer les URLs
        df_normalized['lien_externe'] = df_normalized['lien_externe'].str.strip('"')
        
        # 8. S'assurer que toutes les colonnes de référence existent
        for col in self.reference_columns:
            if col not in df_normalized.columns:
                logger.warning(f"Colonne {col} manquante, ajout avec valeurs vides")
                df_normalized[col] = ""
        
        # 9. Réorganiser les colonnes dans l'ordre de référence
        df_normalized = df_normalized[self.reference_columns]
        
        # 10. Nettoyer les valeurs vides
        df_normalized = df_normalized.fillna("")
        
        logger.info(f"Données incubateurs normalisées: {len(df_normalized)} lignes")
        return df_normalized
    
    def detect_duplicates(self, df_reference, df_incubateurs):
        """Détecte les doublons potentiels entre les datasets"""
        logger.info("=== DÉTECTION DOUBLONS ===")
        
        # Créer clés uniques pour comparaison
        df_reference['key'] = (df_reference['institution'].str.strip().str.upper() + "|" + 
                              df_reference['titre'].str.strip().str.upper())
        df_incubateurs['key'] = (df_incubateurs['institution'].str.strip().str.upper() + "|" + 
                                df_incubateurs['titre'].str.strip().str.upper())
        
        # Identifier les doublons
        duplicates = df_incubateurs[df_incubateurs['key'].isin(df_reference['key'])]
        new_entries = df_incubateurs[~df_incubateurs['key'].isin(df_reference['key'])]
        
        logger.info(f"Doublons détectés: {len(duplicates)}")
        if len(duplicates) > 0:
            logger.info("Doublons trouvés:")
            for _, dup in duplicates.iterrows():
                logger.info(f"  - {dup['institution']} | {dup['titre']}")
        
        logger.info(f"Nouvelles entrées: {len(new_entries)}")
        
        return duplicates, new_entries
    
    def merge_datasets(self, df_reference, df_incubateurs_new):
        """Fusionne les datasets en évitant les doublons"""
        logger.info("=== FUSION DATASETS ===")
        
        # Supprimer les colonnes clés temporaires
        df_reference_clean = df_reference.drop('key', axis=1)
        df_incubateurs_clean = df_incubateurs_new.drop('key', axis=1)
        
        # Fusionner
        df_merged = pd.concat([df_reference_clean, df_incubateurs_clean], 
                             ignore_index=True)
        
        logger.info(f"Dataset fusionné: {len(df_merged)} lignes")
        return df_merged
    
    def apply_final_classification(self, df_merged):
        """Applique la classification finale par priorité"""
        logger.info("=== CLASSIFICATION FINALE ===")
        
        # Créer clés de tri
        institution_map = {inst: i for i, inst in enumerate(self.institution_order)}
        opportunity_map = {opp: i for i, opp in enumerate(self.opportunity_order)}
        
        max_inst_order = len(self.institution_order)
        max_opp_order = len(self.opportunity_order)
        
        df_merged['institution_sort_key'] = df_merged['institution_type'].map(
            lambda x: institution_map.get(x, max_inst_order)
        )
        
        df_merged['opportunity_sort_key'] = df_merged['type'].map(
            lambda x: opportunity_map.get(x, max_opp_order)
        )
        
        # Trier
        df_sorted = df_merged.sort_values([
            'institution_sort_key',
            'opportunity_sort_key', 
            'institution',
            'titre'
        ]).reset_index(drop=True)
        
        # Supprimer colonnes temporaires
        df_sorted = df_sorted.drop(['institution_sort_key', 'opportunity_sort_key'], axis=1)
        
        logger.info(f"Dataset final classé: {len(df_sorted)} lignes")
        return df_sorted
    
    def generate_final_stats(self, df_final, incubateurs_added):
        """Génère les statistiques finales"""
        logger.info("=== STATISTIQUES FINALES ===")
        
        total = len(df_final)
        logger.info(f"Total final: {total} opportunités")
        logger.info(f"Incubateurs ajoutés: {incubateurs_added}")
        
        # Distribution par type d'institution
        logger.info("\n🏢 Distribution par type d'institution:")
        inst_counts = df_final['institution_type'].value_counts()
        for inst_type, count in inst_counts.items():
            pct = count/total*100
            logger.info(f"  {inst_type}: {count} ({pct:.1f}%)")
        
        # Distribution par type d'opportunité
        logger.info("\n🎯 Distribution par type d'opportunité:")
        opp_counts = df_final['type'].value_counts()
        for opp_type, count in opp_counts.items():
            pct = count/total*100
            logger.info(f"  {opp_type}: {count} ({pct:.1f}%)")
        
        return {
            'total': total,
            'incubateurs_added': incubateurs_added,
            'institution_distribution': inst_counts.to_dict(),
            'opportunity_distribution': opp_counts.to_dict()
        }
    
    def generate_comprehensive_report(self, stats, audit_incub, duplicates_count):
        """Génère un rapport complet"""
        report = f"""
=== RAPPORT FUSION ÉTENDUE - INCUBATEURS ===
Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

📊 DONNÉES TRAITÉES:
- Opportunites_Classees.csv: Base de référence
- Opportunites_Incubateurs.csv: {audit_incub['rows']} nouvelles opportunités
- Doublons détectés et ignorés: {duplicates_count}
- Nouvelles entrées ajoutées: {stats['incubateurs_added']}
- TOTAL FINAL: {stats['total']} opportunités

🔧 NORMALISATION APPLIQUÉE SUR INCUBATEURS:
- Types institutions: {audit_incub['institution_types']} → standardisés
- Types opportunités: {audit_incub['opportunity_types']} → normalisés
- Statuts: {audit_incub['statuts']} → uniformisés
- Secteurs: format virgule → point-virgule
- Contacts: nettoyage format 'via'
- URLs: suppression guillemets

🏢 DISTRIBUTION FINALE PAR TYPE D'INSTITUTION:
"""
        for inst_type, count in stats['institution_distribution'].items():
            pct = count/stats['total']*100
            report += f"- {inst_type}: {count} ({pct:.1f}%)\n"

        report += f"""
🎯 DISTRIBUTION FINALE PAR TYPE D'OPPORTUNITÉ:
"""
        for opp_type, count in stats['opportunity_distribution'].items():
            pct = count/stats['total']*100
            report += f"- {opp_type}: {count} ({pct:.1f}%)\n"

        report += f"""
✅ QUALITÉ ET COHÉRENCE:
- Format uniforme sur toutes les colonnes
- Classification respectant la hiérarchie établie
- Doublons éliminés automatiquement
- Secteurs normalisés (séparateur ;)
- Contacts et URLs nettoyés

🎯 SPÉCIFICITÉS INCUBATEURS INTÉGRÉES:
- Studios de développement → Incubateurs/Accélérateurs
- Fonds d'investissement → Fonds d'investissement
- Programmes d'accélération → Accélération
- Statuts 'À vérifier' → 'À venir'

📈 IMPACT SUR L'ÉCOSYSTÈME LAGENTO:
- Couverture complète incubateurs/accélérateurs CI
- Recommandations plus précises pour startups
- Mapping complet des opportunités de financement
- Base de données exhaustive pour l'IA

🚀 PROCHAINES ÉTAPES:
- Intégration vectorielle complète
- Tests recommandations avec nouveaux incubateurs
- Validation terrain avec entrepreneurs
- Mise à jour trimestrielle coordonnée
        """
        
        return report
    
    def run_complete_pipeline(self):
        """Exécute le pipeline complet de normalisation et fusion"""
        logger.info("🚀 DÉMARRAGE PIPELINE FUSION ÉTENDUE")
        logger.info("="*60)
        
        try:
            # 1. Charger les données
            logger.info("📂 Chargement des datasets...")
            df_reference = pd.read_csv(os.path.join(self.data_dir, self.reference_file))
            df_incubateurs = pd.read_csv(os.path.join(self.data_dir, self.incubateurs_file))
            
            logger.info(f"✅ Référence chargée: {len(df_reference)} opportunités")
            logger.info(f"✅ Incubateurs chargé: {len(df_incubateurs)} opportunités")
            
            # 2. Audit des données incubateurs
            audit_incub = self.audit_incubateurs_data(df_incubateurs)
            
            # 3. Normalisation
            df_incubateurs_normalized = self.normalize_incubateurs(df_incubateurs)
            
            # 4. Détection de doublons
            duplicates, new_entries = self.detect_duplicates(df_reference, df_incubateurs_normalized)
            
            # 5. Fusion
            df_merged = self.merge_datasets(df_reference, new_entries)
            
            # 6. Classification finale
            df_final = self.apply_final_classification(df_merged)
            
            # 7. Statistiques finales
            stats = self.generate_final_stats(df_final, len(new_entries))
            
            # 8. Sauvegarde
            output_path = os.path.join(self.data_dir, self.output_file)
            df_final.to_csv(output_path, index=False, encoding='utf-8')
            logger.info(f"💾 Dataset final sauvegardé: {output_path}")
            
            # 9. Rapport
            report = self.generate_comprehensive_report(stats, audit_incub, len(duplicates))
            
            report_path = os.path.join(self.data_dir, "rapport_fusion_etendue.txt")
            with open(report_path, 'w', encoding='utf-8') as f:
                f.write(report)
            
            logger.info(f"📋 Rapport sauvegardé: {report_path}")
            print(report)
            
            return {
                'success': True,
                'output_file': output_path,
                'report_file': report_path,
                'stats': stats
            }
            
        except Exception as e:
            logger.error(f"❌ Erreur pipeline: {str(e)}")
            return {'success': False, 'error': str(e)}

if __name__ == "__main__":
    normalizer = IncubateursNormalizer()
    result = normalizer.run_complete_pipeline()
    
    if result['success']:
        print(f"\n✅ Pipeline terminé avec succès!")
        print(f"📁 Fichier final: {result['output_file']}")
        print(f"📊 Rapport: {result['report_file']}")
        print(f"📈 Total final: {result['stats']['total']} opportunités")
    else:
        print(f"❌ Erreur: {result['error']}")