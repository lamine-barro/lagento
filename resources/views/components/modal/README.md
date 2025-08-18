# Composants Modal

## Vue d'ensemble

Système de composants modaux unifiés avec backdrop flou pour une expérience utilisateur cohérente.

## Composants disponibles

### 1. Modal de base (`x-modal.base`)

Composant de base pour tous les modaux avec backdrop flou uniforme.

```blade
<x-modal.base show="showModal" max-width="lg">
    <!-- Contenu du modal -->
</x-modal.base>
```

**Props:**
- `show`: Expression Alpine.js pour contrôler l'affichage
- `max-width`: sm, md, lg, xl, 2xl, 3xl, 4xl, full (défaut: md)
- `closable`: boolean, permet de fermer en cliquant sur le backdrop (défaut: true)
- `z-index`: valeur z-index (défaut: 9999)

### 2. Modal de confirmation (`x-modal.confirm`)

Modal spécialisé pour les confirmations avec gestion des états de chargement.

```blade
<x-modal.confirm 
    show="showDeleteModal"
    title="Supprimer l'élément"
    confirm-text="Supprimer"
    cancel-text="Annuler"
    on-confirm="deleteItem()"
    on-cancel="showDeleteModal = false"
    :danger="true"
    loading="isDeleting"
    loading-text="Suppression...">
    Êtes-vous sûr de vouloir supprimer cet élément ?
</x-modal.confirm>
```

**Props:**
- `show`: Expression Alpine.js pour contrôler l'affichage
- `title`: Titre du modal (défaut: "Confirmation")
- `message`: Message principal (optionnel si contenu dans slot)
- `confirm-text`: Texte du bouton de confirmation (défaut: "Confirmer")
- `cancel-text`: Texte du bouton d'annulation (défaut: "Annuler")
- `on-confirm`: Action Alpine.js à exécuter lors de la confirmation
- `on-cancel`: Action Alpine.js à exécuter lors de l'annulation
- `icon`: Icône Lucide à afficher (défaut: "alert-triangle")
- `icon-color`: Couleur de l'icône
- `icon-bg`: Couleur de fond de l'icône
- `danger`: boolean, style rouge pour actions dangereuses
- `loading`: Expression Alpine.js pour l'état de chargement
- `loading-text`: Texte affiché pendant le chargement

### 3. Modal générique (`x-modal.generic`)

Modal avec en-tête et pied de page optionnels pour usage général.

```blade
<x-modal.generic 
    show="showModal"
    title="Mon Modal"
    max-width="lg"
    :show-footer="true">
    
    <!-- Contenu principal -->
    <p>Contenu du modal...</p>
    
    <!-- Pied de page optionnel -->
    <x-slot name="footer">
        <button class="btn btn-primary">Action</button>
    </x-slot>
</x-modal.generic>
```

**Props:**
- `show`: Expression Alpine.js pour contrôler l'affichage
- `title`: Titre du modal
- `max-width`: Largeur maximale
- `closable`: boolean, permet de fermer (défaut: true)
- `show-header`: boolean, afficher l'en-tête (défaut: true)
- `show-footer`: boolean, afficher le pied de page (défaut: false)
- `on-close`: Action Alpine.js personnalisée pour la fermeture

## Caractéristiques

### Backdrop flou uniforme
Tous les modaux utilisent un backdrop avec:
- Fond semi-transparent: `rgba(0, 0, 0, 0.5)`
- Effet de flou: `backdrop-filter: blur(4px)`
- Compatible WebKit: `-webkit-backdrop-filter: blur(4px)`

### Positionnement optimal
- Utilisation de `x-teleport="body"` pour éviter les problèmes de z-index
- Z-index élevé (9999) pour s'assurer de l'affichage au premier plan

### Animations fluides
- Transitions d'entrée et de sortie configurées
- Animations de fondu et d'échelle

### Mode sombre
- Couleurs automatiquement adaptées selon le thème
- Boutons danger avec texte blanc en mode sombre

## Utilisation dans Alpine.js

```javascript
function myComponent() {
    return {
        showModal: false,
        isLoading: false,
        
        openModal() {
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
        },
        
        async confirmAction() {
            this.isLoading = true;
            try {
                // Action asynchrone
                await performAction();
                this.showModal = false;
            } finally {
                this.isLoading = false;
            }
        }
    }
}
```