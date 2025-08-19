#!/usr/bin/env python3
"""
Script pour extraire et fusionner tout le contenu des fichiers du dossier lagento_contexte
en un seul corpus unifié lagento_contexte.txt

Auteur: LAgentO - Assistant IA
Date: 2025-08-19
"""

import os
import csv
import json
import pandas as pd
from pathlib import Path
import PyPDF2
import docx
from datetime import datetime

def extract_text_from_pdf(file_path):
    """Extrait le texte d'un fichier PDF"""
    try:
        with open(file_path, 'rb') as file:
            reader = PyPDF2.PdfReader(file)
            text = ""
            for page in reader.pages:
                text += page.extract_text() + "\n"
        return text.strip()
    except Exception as e:
        return f"[ERREUR EXTRACTION PDF: {str(e)}]"

def extract_text_from_docx(file_path):
    """Extrait le texte d'un fichier Word DOCX"""
    try:
        doc = docx.Document(file_path)
        text = ""
        for paragraph in doc.paragraphs:
            text += paragraph.text + "\n"
        return text.strip()
    except Exception as e:
        return f"[ERREUR EXTRACTION DOCX: {str(e)}]"

def extract_text_from_csv(file_path):
    """Extrait le contenu d'un fichier CSV"""
    try:
        # Essayer différentes stratégies pour les CSV mal formatés
        encodings = ['utf-8', 'latin-1', 'iso-8859-1', 'cp1252']
        
        for encoding in encodings:
            try:
                # Utiliser error_bad_lines=False pour ignorer les lignes problématiques
                df = pd.read_csv(file_path, encoding=encoding, on_bad_lines='skip')
                
                # Convertir le DataFrame en texte structuré
                text = f"Colonnes: {', '.join(df.columns.tolist())}\n\n"
                text += f"Nombre de lignes traitées: {len(df)}\n\n"
                
                for index, row in df.iterrows():
                    text += f"Ligne {index + 1}:\n"
                    for col in df.columns:
                        # Gérer les valeurs NaN
                        value = row[col] if pd.notna(row[col]) else "[VIDE]"
                        text += f"  {col}: {value}\n"
                    text += "\n"
                return text.strip()
                
            except Exception as e:
                continue
        
        # Si aucun encodage ne fonctionne, essayer de lire ligne par ligne
        try:
            with open(file_path, 'r', encoding='utf-8', errors='replace') as f:
                lines = f.readlines()
                header = lines[0].strip().split(',')
                content = f"Colonnes: {', '.join(header)}\n\n"
                content += f"Contenu brut du fichier CSV ({len(lines)} lignes):\n\n"
                
                for i, line in enumerate(lines[:100], 1):  # Limiter à 100 lignes pour éviter trop de texte
                    content += f"Ligne {i}: {line.strip()}\n"
                
                if len(lines) > 100:
                    content += f"\n... et {len(lines) - 100} lignes supplémentaires\n"
                
                return content
                
        except Exception as e2:
            return f"[ERREUR EXTRACTION CSV: {str(e2)}]"
            
    except Exception as e:
        return f"[ERREUR EXTRACTION CSV: {str(e)}]"

def extract_text_from_md(file_path):
    """Extrait le texte d'un fichier Markdown"""
    try:
        with open(file_path, 'r', encoding='utf-8') as file:
            return file.read().strip()
    except Exception as e:
        return f"[ERREUR EXTRACTION MD: {str(e)}]"

def extract_text_from_txt(file_path):
    """Extrait le texte d'un fichier TXT"""
    try:
        with open(file_path, 'r', encoding='utf-8') as file:
            return file.read().strip()
    except Exception as e:
        return f"[ERREUR EXTRACTION TXT: {str(e)}]"

def get_file_processor(file_extension):
    """Retourne le processeur approprié selon l'extension du fichier"""
    processors = {
        '.pdf': extract_text_from_pdf,
        '.md': extract_text_from_md,
        '.txt': extract_text_from_txt,
        '.csv': extract_text_from_csv,
        '.docx': extract_text_from_docx,
        '.doc': extract_text_from_docx,
    }
    return processors.get(file_extension.lower())

def create_divider(title, char="=", length=100):
    """Crée un séparateur visuel pour le corpus"""
    divider = char * length
    centered_title = f" {title} ".center(length, char)
    return f"\n{divider}\n{centered_title}\n{divider}\n"

def main():
    # Chemins
    source_dir = Path("lagento_contexte")
    output_file = Path("lagento_contexte.txt")
    
    if not source_dir.exists():
        print(f"❌ Erreur: Le dossier {source_dir} n'existe pas!")
        return
    
    # Initialiser le corpus
    corpus_content = []
    
    # En-tête du corpus
    header = f"""
{create_divider("CORPUS LAGENTO - CONTEXTE COMPLET", "=", 120)}

📄 CORPUS UNIFIÉ LAGENTO CONTEXTE
🗓️ Généré le: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
🤖 Par: Script d'extraction automatique LAgentO
📁 Source: {source_dir.absolute()}

Ce corpus contient l'intégralité du contenu brut de tous les fichiers 
du dossier lagento_contexte, organisé avec des séparateurs clairs 
pour faciliter l'utilisation par l'IA.

{create_divider("TABLE DES MATIÈRES", "-", 120)}
"""
    
    corpus_content.append(header)
    
    # Scanner tous les fichiers
    all_files = []
    supported_extensions = {'.pdf', '.md', '.txt', '.csv', '.docx', '.doc'}
    
    for file_path in source_dir.rglob("*"):
        if file_path.is_file() and file_path.suffix.lower() in supported_extensions:
            all_files.append(file_path)
    
    # Trier par extension puis par nom
    all_files.sort(key=lambda x: (x.suffix, x.name))
    
    # Table des matières
    toc = "FICHIERS À TRAITER:\n\n"
    for i, file_path in enumerate(all_files, 1):
        relative_path = file_path.relative_to(source_dir)
        file_size = file_path.stat().st_size
        toc += f"{i:3d}. {relative_path} ({file_size:,} octets)\n"
    
    corpus_content.append(toc)
    corpus_content.append(create_divider("DÉBUT DU CONTENU", "=", 120))
    
    # Traiter chaque fichier
    processed_count = 0
    error_count = 0
    
    for i, file_path in enumerate(all_files, 1):
        relative_path = file_path.relative_to(source_dir)
        print(f"📄 Traitement ({i}/{len(all_files)}): {relative_path}")
        
        # En-tête du fichier
        file_info = f"""
SOURCE: {relative_path}
TYPE: {file_path.suffix.upper()[1:]}
TAILLE: {file_path.stat().st_size:,} octets
MODIFIÉ: {datetime.fromtimestamp(file_path.stat().st_mtime).strftime('%Y-%m-%d %H:%M:%S')}
"""
        
        file_header = create_divider(f"FICHIER {i}/{len(all_files)}: {relative_path}", "#", 100)
        corpus_content.append(file_header)
        corpus_content.append(file_info)
        corpus_content.append("-" * 80)
        
        # Extraire le contenu
        processor = get_file_processor(file_path.suffix)
        
        if processor:
            try:
                content = processor(file_path)
                if content and not content.startswith("[ERREUR"):
                    corpus_content.append(content)
                    processed_count += 1
                    print(f"  ✅ Contenu extrait: {len(content):,} caractères")
                else:
                    corpus_content.append(content or "[FICHIER VIDE]")
                    error_count += 1
                    print(f"  ⚠️ Erreur ou fichier vide")
            except Exception as e:
                error_msg = f"[ERREUR TRAITEMENT: {str(e)}]"
                corpus_content.append(error_msg)
                error_count += 1
                print(f"  ❌ Erreur: {str(e)}")
        else:
            unsupported_msg = f"[FORMAT NON SUPPORTÉ: {file_path.suffix}]"
            corpus_content.append(unsupported_msg)
            error_count += 1
            print(f"  ❌ Format non supporté: {file_path.suffix}")
        
        # Séparateur entre fichiers
        corpus_content.append("\n" + "─" * 100 + "\n")
    
    # Footer du corpus
    footer = f"""
{create_divider("FIN DU CORPUS", "=", 120)}

📊 STATISTIQUES D'EXTRACTION:
• Fichiers traités avec succès: {processed_count}
• Fichiers en erreur: {error_count}  
• Total de fichiers: {len(all_files)}
• Taille totale du corpus: {len(''.join(corpus_content)):,} caractères

🔍 INSTRUCTIONS D'UTILISATION POUR L'IA:
1. Ce corpus contient toutes les informations contextuelles de LAgentO
2. Chaque fichier est clairement délimité par des séparateurs (#)
3. La source de chaque contenu est indiquée avant l'extraction
4. Utiliser ce corpus pour répondre aux questions sur le contexte légal, 
   réglementaire et institutionnel de la Côte d'Ivoire

{create_divider("CORPUS LAGENTO CONTEXTE - TERMINÉ", "=", 120)}
"""
    
    corpus_content.append(footer)
    
    # Écrire le corpus final
    try:
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write('\n'.join(corpus_content))
        
        final_size = output_file.stat().st_size
        print(f"\n🎉 CORPUS CRÉÉ AVEC SUCCÈS!")
        print(f"📁 Fichier: {output_file.absolute()}")
        print(f"📊 Taille: {final_size:,} octets ({final_size/1024/1024:.1f} MB)")
        print(f"✅ Fichiers traités: {processed_count}/{len(all_files)}")
        
        if error_count > 0:
            print(f"⚠️ Fichiers en erreur: {error_count}")
        
    except Exception as e:
        print(f"❌ Erreur lors de l'écriture du corpus: {str(e)}")

if __name__ == "__main__":
    main()