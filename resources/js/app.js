import './bootstrap';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Lazy-load lucide to keep main bundle small
let lucideModulePromise = null;
function loadLucideModule() {
    if (!lucideModulePromise) {
        lucideModulePromise = import('lucide');
    }
    return lucideModulePromise;
}

function renderIcons() {
    loadLucideModule().then(({ createIcons, icons }) => {
        createIcons({
            icons,
            attrs: {
                'stroke-width': 1.25
            }
        });
    }).catch(() => {});
}

// Initialize icons on DOM ready (deferred)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => renderIcons());
        } else {
            setTimeout(() => renderIcons(), 0);
        }
    });
} else {
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => renderIcons());
    } else {
        setTimeout(() => renderIcons(), 0);
    }
}

// Re-initialize icons when new content is added to DOM (throttled)
let reinitScheduled = false;
const observer = new MutationObserver(() => {
    if (reinitScheduled) return;
    reinitScheduled = true;
    setTimeout(() => {
        renderIcons();
        reinitScheduled = false;
    }, 100);
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});
