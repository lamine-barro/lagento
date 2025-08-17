<div x-data="themeToggle()" class="flex items-center">
    <button @click="toggleTheme()" 
            class="p-2 rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
            :title="isDark ? 'Passer en mode clair' : 'Passer en mode sombre'">
        <!-- Sun icon for light mode -->
        <i data-lucide="sun" 
           x-show="!isDark" 
           class="w-5 h-5" 
           style="color: var(--gray-600); stroke-width: 1.25;">
        </i>
        
        <!-- Moon icon for dark mode -->
        <i data-lucide="moon" 
           x-show="isDark" 
           class="w-5 h-5" 
           style="color: var(--gray-400); stroke-width: 1.25;">
        </i>
    </button>
</div>

<script>
function themeToggle() {
    return {
        isDark: localStorage.getItem('theme') === 'dark' || 
                (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
        
        init() {
            this.setTheme(this.isDark ? 'dark' : 'light');
        },
        
        toggleTheme() {
            this.isDark = !this.isDark;
            this.setTheme(this.isDark ? 'dark' : 'light');
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },
        
        setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            
            // Update favicon
            const favicon = document.querySelector('link[rel="icon"]');
            if (favicon) {
                favicon.href = theme === 'dark' ? '/favicon-dark.png' : '/favicon-light.png';
            }
        }
    }
}
</script><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/theme-toggle.blade.php ENDPATH**/ ?>