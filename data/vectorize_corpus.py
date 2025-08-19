#!/usr/bin/env python3
"""
Script pour vectoriser le corpus lagento_contexte.txt complet
Utilise l'API Voyage pour créer les embeddings
"""

import os
import sys
import requests
import json
from datetime import datetime

# Configuration Voyage API
VOYAGE_API_KEY = os.getenv('VOYAGE_API_KEY')
VOYAGE_API_URL = "https://api.voyageai.com/v1/embeddings"

def vectorize_text(text, model="voyage-large-2"):
    """Vectorise un texte avec l'API Voyage"""
    if not VOYAGE_API_KEY:
        raise ValueError("VOYAGE_API_KEY environment variable not set")
    
    headers = {
        "Authorization": f"Bearer {VOYAGE_API_KEY}",
        "Content-Type": "application/json"
    }
    
    payload = {
        "input": [text],
        "model": model
    }
    
    try:
        response = requests.post(VOYAGE_API_URL, headers=headers, json=payload)
        response.raise_for_status()
        
        data = response.json()
        return data['data'][0]['embedding']
        
    except requests.exceptions.RequestException as e:
        print(f"❌ Erreur API Voyage: {e}")
        return None
    except Exception as e:
        print(f"❌ Erreur vectorisation: {e}")
        return None

def main():
    corpus_file = "lagento_contexte.txt"
    
    if not os.path.exists(corpus_file):
        print(f"❌ Fichier {corpus_file} non trouvé!")
        return
    
    print("📖 Lecture du corpus...")
    with open(corpus_file, 'r', encoding='utf-8') as f:
        corpus_content = f.read()
    
    corpus_size = len(corpus_content)
    print(f"📊 Taille du corpus: {corpus_size:,} caractères ({corpus_size/1024/1024:.1f} MB)")
    
    # Vérifier si le contenu est trop long pour l'API Voyage (limite ~32k tokens)
    # Estimation: ~4 caractères par token
    estimated_tokens = corpus_size // 4
    print(f"🔢 Tokens estimés: {estimated_tokens:,}")
    
    if estimated_tokens > 32000:
        print("⚠️ Le corpus est trop volumineux pour être vectorisé en une seule fois")
        print("🔄 Division en chunks plus petits...")
        
        # Diviser en chunks de ~25k tokens (100k caractères)
        chunk_size = 100000
        chunks = []
        
        for i in range(0, len(corpus_content), chunk_size):
            chunk = corpus_content[i:i+chunk_size]
            chunks.append(chunk)
        
        print(f"📦 Corpus divisé en {len(chunks)} chunks")
        
        # Vectoriser chaque chunk
        vectors = []
        for i, chunk in enumerate(chunks):
            print(f"🔄 Vectorisation chunk {i+1}/{len(chunks)}...")
            vector = vectorize_text(chunk)
            if vector:
                vectors.append({
                    'chunk_id': i,
                    'content_preview': chunk[:200] + "...",
                    'size': len(chunk),
                    'embedding': vector
                })
                print(f"✅ Chunk {i+1} vectorisé ({len(vector)} dimensions)")
            else:
                print(f"❌ Erreur vectorisation chunk {i+1}")
        
        # Sauvegarder les vecteurs
        output = {
            'corpus_info': {
                'source': corpus_file,
                'total_size': corpus_size,
                'chunks_count': len(chunks),
                'vectorized_chunks': len(vectors),
                'created_at': datetime.now().isoformat()
            },
            'vectors': vectors
        }
        
    else:
        print("🔄 Vectorisation du corpus complet...")
        vector = vectorize_text(corpus_content)
        
        if not vector:
            print("❌ Échec de la vectorisation")
            return
        
        print(f"✅ Corpus vectorisé ({len(vector)} dimensions)")
        
        # Sauvegarder le vecteur unique
        output = {
            'corpus_info': {
                'source': corpus_file,
                'total_size': corpus_size,
                'chunks_count': 1,
                'vectorized_chunks': 1,
                'created_at': datetime.now().isoformat()
            },
            'vectors': [{
                'chunk_id': 0,
                'content_preview': corpus_content[:200] + "...",
                'size': corpus_size,
                'embedding': vector
            }]
        }
    
    # Sauvegarder les résultats
    output_file = "lagento_contexte_vectors.json"
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(output, f, ensure_ascii=False, indent=2)
    
    output_size = os.path.getsize(output_file)
    print(f"\n🎉 VECTORISATION TERMINÉE!")
    print(f"📁 Fichier de sortie: {output_file}")
    print(f"📊 Taille: {output_size:,} octets ({output_size/1024/1024:.1f} MB)")
    print(f"🔢 Vecteurs créés: {len(output['vectors'])}")

if __name__ == "__main__":
    main()