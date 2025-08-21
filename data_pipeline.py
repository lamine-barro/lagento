#!/usr/bin/env python3
"""
Pipeline de normalisation et fusion des données d'opportunités
Audit et normalisation d'Opportunites_2 vers Opportunites_Jeunesse_Entrepreneuriales_Sept_Dec_2025
"""

import pandas as pd
import numpy as np
import os
from datetime import datetime
import logging

# Configuration du logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class OpportunitiesPipeline:
    def __init__(self, data_dir="/Users/laminebarro/agent-O/data"):
        self.data_dir = data_dir
        self.reference_file = "Opportunites_Jeunesse_Entrepreneuriales_Sept_Dec_2025.csv"
        self.source_file = "Opportunites_2.csv"
        self.output_file = "Opportunites_Fusionnees.csv"
        
        # Colonnes de référence (format cible)
        self.reference_columns = [
            'institution', 'institution_type', 'statut', 'titre', 'description', 
            'type', 'pays', 'regions_ciblees', 'date_limite_candidature', 
            'date_debut', 'duree', 'remuneration', 'nombre_places', 'secteurs', 
            'criteres_eligibilite', 'contact_email_enrichi', 'lien_externe', 'origine_initiative'
        ]
        
    def audit_data_quality(self, df, filename):
        """Audite la qualité des données"""
        logger.info(f"=== AUDIT DE {filename} ===")
        logger.info(f"Nombre de lignes: {len(df)}")
        logger.info(f"Nombre de colonnes: {len(df.columns)}")
        logger.info(f"Colonnes: {list(df.columns)}")
        
        # Valeurs manquantes
        missing_data = df.isnull().sum()
        if missing_data.any():
            logger.info("\nValeurs manquantes:")
            for col, count in missing_data[missing_data > 0].items():
                logger.info(f"  {col}: {count} ({count/len(df)*100:.1f}%)")
        
        # Doublons
        duplicates = df.duplicated().sum()
        logger.info(f"\nDoublons: {duplicates}")
        
        return {
            'rows': len(df),
            'columns': len(df.columns),
            'missing_data': missing_data.to_dict(),
            'duplicates': duplicates
        }
    
    def normalize_opportunites_2(self, df_source):
        """Normalise Opportunites_2 vers le format de référence"""
        logger.info("=== NORMALISATION D'OPPORTUNITES_2 ===")
        
        # Créer un DataFrame avec les colonnes de référence
        df_normalized = pd.DataFrame(columns=self.reference_columns)
        
        # Mapping des colonnes existantes vers le format de référence
        column_mapping = {
            'institution': 'institution',
            'institution_type': 'institution_type', 
            'statut': 'statut',
            'titre': 'titre',
            'description': 'description',
            'type': 'type',
            'pays': 'pays',
            'regions_ciblees': 'regions_ciblees',
            'date_limite_candidature': 'date_limite_candidature',
            'date_debut': 'date_debut',
            'duree': 'duree',
            'remuneration': 'remuneration',
            'nombre_places': 'nombre_places',
            'secteurs': 'secteurs',
            'criteres_eligibilite': 'criteres_eligibilite',
            'contact_email_enrichi': 'contact_email_enrichi',
            'lien_externe': 'lien_externe',
            'origine_initiative': 'origine_initiative'
        }
        
        # Copier les données existantes
        for target_col, source_col in column_mapping.items():
            if source_col in df_source.columns:
                df_normalized[target_col] = df_source[source_col]
            else:
                logger.warning(f"Colonne {source_col} non trouvée dans Opportunites_2")
                df_normalized[target_col] = ""
        
        # Normalisation spécifique pour les données gouvernementales
        
        # 1. Standardiser les types d'institutions
        institution_type_mapping = {
            'MINISTERE': 'MINISTERE_AGENCE',
            'AGENCE_GOUVERNEMENTALE': 'MINISTERE_AGENCE', 
            'FONDS_PUBLIC': 'FONDS_INVESTISSEMENT',
            'PARTENAIRE_PUBLIC': 'ASSOCIATION_ENTREPRENEURIALE'
        }
        
        df_normalized['institution_type'] = df_normalized['institution_type'].replace(institution_type_mapping)
        
        # 2. Standardiser les statuts
        df_normalized['statut'] = df_normalized['statut'].fillna('Ouvert')
        df_normalized['statut'] = df_normalized['statut'].replace({
            'Actif': 'Ouvert',
            'En cours': 'Ouvert'
        })
        
        # 3. Standardiser les types d'opportunités
        type_mapping = {
            'FINANCEMENT/FORMATION/SUBVENTION': 'FINANCEMENT',
            'FINANCEMENT/FORMATION': 'FORMATION',
            'FINANCEMENT/INCUBATION': 'INCUBATION',
            'INCUBATION/FINANCEMENT': 'INCUBATION',
            'INCUBATION/ACCELERATION': 'ACCELERATION',
            'FORMATION/FINANCEMENT': 'FORMATION',
            'FORMATION/INSERTION': 'FORMATION',
            'FORMATION/APPRENTISSAGE': 'FORMATION',
            'STAGE/APPRENTISSAGE': 'FORMATION',
            'FINANCEMENT/INFRASTRUCTURE': 'FINANCEMENT',
            'FINANCEMENT/SUBVENTION': 'FINANCEMENT',
            'INFRASTRUCTURE/FORMATION': 'FORMATION',
            'ACCELERATION/FINANCEMENT': 'ACCELERATION',
            'FORMATION/INCUBATION': 'FORMATION',
            'RECHERCHE/INNOVATION': 'FORMATION',
            'CERTIFICATION/FORMATION': 'FORMATION',
            'CONCOURS/INCUBATION': 'CONCOURS',
            'CONCOURS/FORMATION': 'CONCOURS',
            'ASSISTANCE_TECHNIQUE': 'FORMATION'
        }
        
        df_normalized['type'] = df_normalized['type'].replace(type_mapping)
        
        # 4. Standardiser les secteurs (séparer par des points-virgules)
        df_normalized['secteurs'] = df_normalized['secteurs'].str.replace(';', ';')
        
        # 5. Standardiser l'origine
        df_normalized['origine_initiative'] = df_normalized['origine_initiative'].replace({
            'GOUVERNEMENTAL': 'PUBLIC'
        })
        
        # 6. Nettoyer les valeurs vides
        df_normalized = df_normalized.fillna("")
        
        logger.info(f"Données normalisées: {len(df_normalized)} lignes")
        return df_normalized
    
    def merge_datasets(self, df_reference, df_normalized):
        """Fusionne les deux datasets"""
        logger.info("=== FUSION DES DATASETS ===")
        
        # Identifier les doublons potentiels par titre et institution
        logger.info("Vérification des doublons...")
        
        # Créer une clé unique pour détecter les doublons
        df_reference['key'] = df_reference['institution'].str.strip() + "|" + df_reference['titre'].str.strip()
        df_normalized['key'] = df_normalized['institution'].str.strip() + "|" + df_normalized['titre'].str.strip()
        
        # Identifier les doublons
        duplicates = df_normalized[df_normalized['key'].isin(df_reference['key'])]
        new_entries = df_normalized[~df_normalized['key'].isin(df_reference['key'])]
        
        logger.info(f"Doublons détectés: {len(duplicates)}")
        logger.info(f"Nouvelles entrées: {len(new_entries)}")
        
        # Fusionner
        df_merged = pd.concat([df_reference.drop('key', axis=1), new_entries.drop('key', axis=1)], 
                             ignore_index=True)
        
        logger.info(f"Dataset fusionné: {len(df_merged)} lignes")
        return df_merged, len(duplicates), len(new_entries)
    
    def generate_report(self, audit_ref, audit_source, duplicates_count, new_entries_count):
        """Génère un rapport détaillé"""
        report = f"""
=== RAPPORT DE FUSION DES OPPORTUNITÉS ===
Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

DONNÉES DE RÉFÉRENCE (Opportunites_Jeunesse_Entrepreneuriales_Sept_Dec_2025):
- Nombre d'entrées: {audit_ref['rows']}
- Colonnes: {audit_ref['columns']}
- Doublons: {audit_ref['duplicates']}

DONNÉES SOURCE (Opportunites_2):
- Nombre d'entrées: {audit_source['rows']}
- Colonnes: {audit_source['columns']}
- Doublons: {audit_source['duplicates']}

RÉSULTATS DE LA FUSION:
- Doublons détectés et ignorés: {duplicates_count}
- Nouvelles entrées ajoutées: {new_entries_count}
- Total après fusion: {audit_ref['rows'] + new_entries_count}

NORMALISATION APPLIQUÉE:
- Standardisation des types d'institutions
- Uniformisation des statuts (Actif/En cours → Ouvert)
- Consolidation des types d'opportunités
- Nettoyage des secteurs
- Standardisation origine_initiative (GOUVERNEMENTAL → PUBLIC)

RECOMMANDATIONS:
1. Vérifier manuellement les doublons détectés
2. Valider les nouvelles entrées ajoutées
3. Compléter les champs manquants si nécessaire
4. Effectuer une validation finale des données
        """
        
        return report
    
    def run_pipeline(self):
        """Exécute le pipeline complet"""
        logger.info("=== DÉMARRAGE DU PIPELINE ===")
        
        try:
            # 1. Charger les données
            logger.info("Chargement des données...")
            df_reference = pd.read_csv(os.path.join(self.data_dir, self.reference_file))
            # Charger avec des paramètres spécifiques pour gérer les guillemets et champs problématiques
            df_source = pd.read_csv(os.path.join(self.data_dir, self.source_file), 
                                  quotechar='"', 
                                  skipinitialspace=True,
                                  encoding='utf-8',
                                  on_bad_lines='skip',
                                  engine='python')
            
            # 2. Audit des données
            audit_ref = self.audit_data_quality(df_reference, self.reference_file)
            audit_source = self.audit_data_quality(df_source, self.source_file)
            
            # 3. Normalisation
            df_normalized = self.normalize_opportunites_2(df_source)
            
            # 4. Fusion
            df_merged, duplicates_count, new_entries_count = self.merge_datasets(df_reference, df_normalized)
            
            # 5. Sauvegarde
            output_path = os.path.join(self.data_dir, self.output_file)
            df_merged.to_csv(output_path, index=False, encoding='utf-8')
            logger.info(f"Données fusionnées sauvegardées: {output_path}")
            
            # 6. Génération du rapport
            report = self.generate_report(audit_ref, audit_source, duplicates_count, new_entries_count)
            
            # Sauvegarder le rapport
            report_path = os.path.join(self.data_dir, "rapport_fusion.txt")
            with open(report_path, 'w', encoding='utf-8') as f:
                f.write(report)
            
            logger.info("=== PIPELINE TERMINÉ AVEC SUCCÈS ===")
            print(report)
            
            return {
                'success': True,
                'output_file': output_path,
                'report_file': report_path,
                'stats': {
                    'original_ref': audit_ref['rows'],
                    'original_source': audit_source['rows'],
                    'duplicates': duplicates_count,
                    'new_entries': new_entries_count,
                    'final_total': audit_ref['rows'] + new_entries_count
                }
            }
            
        except Exception as e:
            logger.error(f"Erreur dans le pipeline: {str(e)}")
            return {'success': False, 'error': str(e)}

if __name__ == "__main__":
    pipeline = OpportunitiesPipeline()
    result = pipeline.run_pipeline()
    
    if result['success']:
        print(f"\n✅ Pipeline exécuté avec succès!")
        print(f"📁 Fichier de sortie: {result['output_file']}")
        print(f"📊 Rapport: {result['report_file']}")
        print(f"📈 Statistiques:")
        for key, value in result['stats'].items():
            print(f"   {key}: {value}")
    else:
        print(f"❌ Erreur: {result['error']}")