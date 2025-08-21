#!/usr/bin/env python3
"""
Script de classification des opportunités fusionnées
Tri par type d'institution puis par type d'opportunité
"""

import pandas as pd
import os
from datetime import datetime

class ClassifierOpportunites:
    def __init__(self, data_dir="/Users/laminebarro/agent-O/data"):
        self.data_dir = data_dir
        self.input_file = "Opportunites_Fusionnees.csv"
        self.output_file = "Opportunites_Classees.csv"
        
        # Ordre de priorité pour les types d'institutions
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
        
        # Ordre de priorité pour les types d'opportunités
        self.opportunity_order = [
            'FINANCEMENT',
            'INCUBATION',
            'ACCELERATION',
            'FORMATION',
            'CONCOURS',
            'STAGE',
            'EVENEMENT',
            'ASSISTANCE_TECHNIQUE',
            'SUBVENTION/FINANCEMENT',
            'FINANCEMENT/RECHERCHE',
            'FINANCEMENT/ACCELERATION',
            'INFRASTRUCTURE/FINANCEMENT',
            'INCUBATION/FORMATION',
            'FORMATION/ACCELERATION'
        ]
    
    def load_data(self):
        """Charge les données fusionnées"""
        file_path = os.path.join(self.data_dir, self.input_file)
        return pd.read_csv(file_path)
    
    def create_sorting_keys(self, df):
        """Crée des clés de tri numériques"""
        print("🔄 Création des clés de tri...")
        
        # Créer un mapping pour les types d'institutions
        institution_map = {inst: i for i, inst in enumerate(self.institution_order)}
        # Ajouter des valeurs pour les institutions non prévues
        max_inst_order = len(self.institution_order)
        
        # Créer un mapping pour les types d'opportunités
        opportunity_map = {opp: i for i, opp in enumerate(self.opportunity_order)}
        max_opp_order = len(self.opportunity_order)
        
        # Appliquer les clés de tri
        df['institution_sort_key'] = df['institution_type'].map(
            lambda x: institution_map.get(x, max_inst_order)
        )
        
        df['opportunity_sort_key'] = df['type'].map(
            lambda x: opportunity_map.get(x, max_opp_order)
        )
        
        return df
    
    def sort_data(self, df):
        """Trie les données selon les critères définis"""
        print("📊 Tri des données...")
        
        # Tri principal : institution_type, puis type d'opportunité, puis titre
        df_sorted = df.sort_values([
            'institution_sort_key',
            'opportunity_sort_key', 
            'institution',
            'titre'
        ]).reset_index(drop=True)
        
        # Supprimer les colonnes de tri temporaires
        df_sorted = df_sorted.drop(['institution_sort_key', 'opportunity_sort_key'], axis=1)
        
        return df_sorted
    
    def generate_classification_stats(self, df_sorted):
        """Génère des statistiques sur la classification"""
        stats = {}
        
        print("\n📈 STATISTIQUES DE CLASSIFICATION")
        print("="*50)
        
        # Grouper par type d'institution
        by_institution = df_sorted.groupby('institution_type').size().reindex(
            self.institution_order, fill_value=0
        )
        
        print("\n🏢 RÉPARTITION PAR TYPE D'INSTITUTION:")
        total = len(df_sorted)
        cumul = 0
        for inst_type, count in by_institution.items():
            if count > 0:
                pct = count/total*100
                cumul += count
                print(f"  {inst_type}: {count} opportunités ({pct:.1f}%) - Cumul: {cumul}")
        
        # Grouper par type d'opportunité  
        by_opportunity = df_sorted.groupby('type').size().reindex(
            self.opportunity_order, fill_value=0
        )
        
        print("\n🎯 RÉPARTITION PAR TYPE D'OPPORTUNITÉ:")
        cumul = 0
        for opp_type, count in by_opportunity.items():
            if count > 0:
                pct = count/total*100
                cumul += count
                print(f"  {opp_type}: {count} opportunités ({pct:.1f}%) - Cumul: {cumul}")
        
        # Matrice croisée
        print("\n📊 MATRICE CROISÉE (TOP 5 de chaque):")
        cross_tab = pd.crosstab(df_sorted['institution_type'], df_sorted['type'])
        
        # Afficher seulement les combinaisons non nulles les plus importantes
        top_institutions = by_institution.head(5).index
        top_opportunities = by_opportunity.head(5).index
        
        cross_subset = cross_tab.loc[top_institutions, top_opportunities]
        print(cross_subset.to_string())
        
        stats = {
            'by_institution': by_institution.to_dict(),
            'by_opportunity': by_opportunity.to_dict(),
            'cross_tab': cross_tab.to_dict()
        }
        
        return stats
    
    def save_classified_data(self, df_sorted):
        """Sauvegarde les données classées"""
        output_path = os.path.join(self.data_dir, self.output_file)
        df_sorted.to_csv(output_path, index=False, encoding='utf-8')
        print(f"\n💾 Données classées sauvegardées: {output_path}")
        return output_path
    
    def generate_report(self, stats, total_records):
        """Génère un rapport de classification"""
        report = f"""
=== RAPPORT DE CLASSIFICATION DES OPPORTUNITÉS ===
Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

📊 RÉSUMÉ:
- Total d'opportunités classées: {total_records}
- Critère de tri principal: Type d'institution
- Critère de tri secondaire: Type d'opportunité
- Critère de tri tertiaire: Institution puis titre

🏢 HIÉRARCHIE DES INSTITUTIONS (ordre de priorité):
"""
        for i, inst_type in enumerate(self.institution_order, 1):
            count = stats['by_institution'].get(inst_type, 0)
            if count > 0:
                report += f"{i:2d}. {inst_type}: {count} opportunités\n"
        
        report += f"""
🎯 HIÉRARCHIE DES OPPORTUNITÉS (ordre de priorité):
"""
        for i, opp_type in enumerate(self.opportunity_order, 1):
            count = stats['by_opportunity'].get(opp_type, 0)
            if count > 0:
                report += f"{i:2d}. {opp_type}: {count} opportunités\n"
        
        report += f"""
📋 LOGIQUE DE CLASSIFICATION:
1. Priorité aux institutions gouvernementales et financières
2. Ensuite les incubateurs et centres de formation
3. Puis les organisations internationales et associations
4. Enfin les structures spécialisées

5. Au sein de chaque type d'institution:
   - Financement en priorité
   - Puis incubation/accélération
   - Ensuite formation
   - Enfin événements et concours

🎯 AVANTAGES DE CETTE CLASSIFICATION:
- Facilite la recherche par type d'acteur
- Groupe les opportunités similaires
- Optimise les recommandations de l'agent Lagento
- Améliore l'expérience utilisateur

📈 PROCHAINES ÉTAPES:
- Intégration dans la base vectorielle
- Configuration des filtres de recherche
- Tests des recommandations personnalisées
        """
        
        return report
    
    def run_classification(self):
        """Exécute la classification complète"""
        print("🚀 DÉMARRAGE DE LA CLASSIFICATION")
        print("="*50)
        
        try:
            # 1. Charger les données
            print("📂 Chargement des données fusionnées...")
            df = self.load_data()
            print(f"✅ {len(df)} opportunités chargées")
            
            # 2. Créer les clés de tri
            df_with_keys = self.create_sorting_keys(df)
            
            # 3. Trier les données
            df_sorted = self.sort_data(df_with_keys)
            
            # 4. Générer les statistiques
            stats = self.generate_classification_stats(df_sorted)
            
            # 5. Sauvegarder
            output_path = self.save_classified_data(df_sorted)
            
            # 6. Générer le rapport
            report = self.generate_report(stats, len(df_sorted))
            
            # Sauvegarder le rapport
            report_path = os.path.join(self.data_dir, "rapport_classification.txt")
            with open(report_path, 'w', encoding='utf-8') as f:
                f.write(report)
            
            print(f"📋 Rapport sauvegardé: {report_path}")
            print(report)
            
            return {
                'success': True,
                'output_file': output_path,
                'report_file': report_path,
                'total_records': len(df_sorted),
                'stats': stats
            }
            
        except Exception as e:
            print(f"❌ Erreur lors de la classification: {str(e)}")
            return {'success': False, 'error': str(e)}

if __name__ == "__main__":
    classifier = ClassifierOpportunites()
    result = classifier.run_classification()
    
    if result['success']:
        print(f"\n✅ Classification terminée avec succès!")
        print(f"📁 Fichier classé: {result['output_file']}")
        print(f"📊 Rapport: {result['report_file']}")
        print(f"📈 Total: {result['total_records']} opportunités classées")
    else:
        print(f"❌ Erreur: {result['error']}")