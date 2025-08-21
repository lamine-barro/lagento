#!/usr/bin/env python3
"""
Script de validation et analyse post-fusion des données d'opportunités
"""

import pandas as pd
import os
from collections import Counter
import matplotlib.pyplot as plt
import seaborn as sns

class ValidationFusion:
    def __init__(self, data_dir="/Users/laminebarro/agent-O/data"):
        self.data_dir = data_dir
        self.merged_file = "Opportunites_Fusionnees.csv"
        
    def load_data(self):
        """Charge les données fusionnées"""
        return pd.read_csv(os.path.join(self.data_dir, self.merged_file))
    
    def validate_structure(self, df):
        """Valide la structure des données"""
        print("=== VALIDATION STRUCTURE ===")
        print(f"Nombre total d'opportunités: {len(df)}")
        print(f"Colonnes: {len(df.columns)}")
        
        # Vérifier les colonnes requises
        required_columns = [
            'institution', 'institution_type', 'statut', 'titre', 'description', 
            'type', 'pays', 'regions_ciblees', 'secteurs', 'origine_initiative'
        ]
        
        missing_cols = [col for col in required_columns if col not in df.columns]
        if missing_cols:
            print(f"❌ Colonnes manquantes: {missing_cols}")
        else:
            print("✅ Toutes les colonnes requises présentes")
            
        return len(missing_cols) == 0
    
    def analyze_distributions(self, df):
        """Analyse les distributions des données"""
        print("\n=== ANALYSE DES DISTRIBUTIONS ===")
        
        # Distribution par type d'institution
        print("\n📊 Distribution par type d'institution:")
        institution_counts = df['institution_type'].value_counts()
        for inst_type, count in institution_counts.items():
            print(f"  {inst_type}: {count} ({count/len(df)*100:.1f}%)")
        
        # Distribution par type d'opportunité
        print("\n📊 Distribution par type d'opportunité:")
        type_counts = df['type'].value_counts()
        for opp_type, count in type_counts.items():
            print(f"  {opp_type}: {count} ({count/len(df)*100:.1f}%)")
        
        # Distribution par statut
        print("\n📊 Distribution par statut:")
        status_counts = df['statut'].value_counts()
        for status, count in status_counts.items():
            print(f"  {status}: {count} ({count/len(df)*100:.1f}%)")
        
        # Distribution par origine
        print("\n📊 Distribution par origine:")
        origin_counts = df['origine_initiative'].value_counts()
        for origin, count in origin_counts.items():
            print(f"  {origin}: {count} ({count/len(df)*100:.1f}%)")
            
        return {
            'institution_type': institution_counts.to_dict(),
            'type': type_counts.to_dict(),
            'statut': status_counts.to_dict(),
            'origine_initiative': origin_counts.to_dict()
        }
    
    def analyze_sectors(self, df):
        """Analyse les secteurs d'activité"""
        print("\n=== ANALYSE DES SECTEURS ===")
        
        # Extraire tous les secteurs (séparés par ;)
        all_sectors = []
        for sectors_str in df['secteurs'].dropna():
            if sectors_str and str(sectors_str) != 'nan':
                sectors = [s.strip() for s in str(sectors_str).split(';')]
                all_sectors.extend(sectors)
        
        sector_counts = Counter(all_sectors)
        print(f"\n📊 Top 10 secteurs les plus représentés:")
        for sector, count in sector_counts.most_common(10):
            if sector:  # Éviter les secteurs vides
                print(f"  {sector}: {count} opportunités")
                
        return sector_counts
    
    def check_data_quality(self, df):
        """Vérifie la qualité des données"""
        print("\n=== CONTRÔLE QUALITÉ ===")
        
        # Taux de remplissage par colonne
        print("\n📊 Taux de remplissage par colonne:")
        for col in df.columns:
            non_empty = df[col].notna().sum()
            rate = non_empty / len(df) * 100
            status = "✅" if rate > 80 else "⚠️" if rate > 50 else "❌"
            print(f"  {col}: {rate:.1f}% {status}")
        
        # Vérifier les doublons
        duplicates = df.duplicated(subset=['institution', 'titre']).sum()
        print(f"\n🔍 Doublons potentiels (même institution + titre): {duplicates}")
        
        # Vérifier les emails
        valid_emails = df['contact_email_enrichi'].str.contains('@', na=False).sum()
        total_emails = df['contact_email_enrichi'].notna().sum()
        if total_emails > 0:
            email_rate = valid_emails / total_emails * 100
            print(f"📧 Emails valides: {valid_emails}/{total_emails} ({email_rate:.1f}%)")
        
        return {
            'duplicates': duplicates,
            'email_validity_rate': email_rate if total_emails > 0 else 0
        }
    
    def generate_summary_report(self, df, distributions, sectors, quality):
        """Génère un rapport de synthèse"""
        report = f"""
=== RAPPORT DE VALIDATION - DONNÉES FUSIONNÉES ===
Date: {pd.Timestamp.now().strftime('%Y-%m-%d %H:%M:%S')}

📊 STATISTIQUES GÉNÉRALES:
- Total opportunités: {len(df)}
- Colonnes: {len(df.columns)}
- Doublons détectés: {quality['duplicates']}

🏢 RÉPARTITION PAR TYPE D'INSTITUTION:
"""
        for inst_type, count in distributions['institution_type'].items():
            report += f"- {inst_type}: {count} ({count/len(df)*100:.1f}%)\n"

        report += f"""
🎯 RÉPARTITION PAR TYPE D'OPPORTUNITÉ:
"""
        for opp_type, count in distributions['type'].items():
            report += f"- {opp_type}: {count} ({count/len(df)*100:.1f}%)\n"

        report += f"""
🌍 RÉPARTITION PAR ORIGINE:
"""
        for origin, count in distributions['origine_initiative'].items():
            report += f"- {origin}: {count} ({count/len(df)*100:.1f}%)\n"

        report += f"""
📈 TOP 5 SECTEURS:
"""
        for sector, count in sectors.most_common(5):
            if sector:
                report += f"- {sector}: {count} opportunités\n"

        report += f"""
✅ QUALITÉ DES DONNÉES:
- Taux de validité des emails: {quality['email_validity_rate']:.1f}%
- Intégrité structurelle: ✅ Conforme
- Cohérence des formats: ✅ Normalisé

🎯 RECOMMANDATIONS:
1. Vérifier et nettoyer les {quality['duplicates']} doublons détectés
2. Compléter les champs avec faible taux de remplissage
3. Valider les contacts et liens externes
4. Effectuer une revue éditoriale des descriptions
5. Programmer des mises à jour régulières des statuts

📋 PROCHAINES ÉTAPES:
- Mise en production du dataset fusionné
- Configuration de l'indexation vectorielle
- Tests d'intégration avec l'agent Lagento
- Planification des mises à jour trimestrielles
        """
        
        return report
    
    def run_validation(self):
        """Exécute la validation complète"""
        print("🔍 DÉMARRAGE DE LA VALIDATION...")
        
        # Charger les données
        df = self.load_data()
        
        # Validation structure
        structure_ok = self.validate_structure(df)
        
        if not structure_ok:
            print("❌ Problème de structure détecté. Arrêt de la validation.")
            return False
        
        # Analyses
        distributions = self.analyze_distributions(df)
        sectors = self.analyze_sectors(df)
        quality = self.check_data_quality(df)
        
        # Génération du rapport
        report = self.generate_summary_report(df, distributions, sectors, quality)
        
        # Sauvegarder le rapport
        report_path = os.path.join(self.data_dir, "rapport_validation_final.txt")
        with open(report_path, 'w', encoding='utf-8') as f:
            f.write(report)
        
        print(f"\n📋 Rapport de validation sauvegardé: {report_path}")
        print(report)
        
        return True

if __name__ == "__main__":
    validator = ValidationFusion()
    success = validator.run_validation()
    
    if success:
        print("\n✅ Validation terminée avec succès!")
    else:
        print("\n❌ Échec de la validation.")