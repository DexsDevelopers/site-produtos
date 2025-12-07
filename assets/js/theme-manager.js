// assets/js/theme-manager.js - Gerenciador de Temas Dark/Light

class ThemeManager {
    constructor() {
        this.currentTheme = this.getStoredTheme() || this.getSystemTheme();
        this.init();
    }
    
    init() {
        this.applyTheme(this.currentTheme);
        this.createThemeToggle();
        this.setupEventListeners();
        this.saveUserPreference();
    }
    
    // Obter tema do sistema
    getSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }
    
    // Obter tema armazenado
    getStoredTheme() {
        return localStorage.getItem('theme') || sessionStorage.getItem('theme');
    }
    
    // Aplicar tema
    applyTheme(theme) {
        const validThemes = ['light', 'dark', 'auto'];
        const selectedTheme = validThemes.includes(theme) ? theme : 'auto';
        
        document.documentElement.setAttribute('data-theme', selectedTheme);
        this.currentTheme = selectedTheme;
        
        // Salvar no localStorage
        localStorage.setItem('theme', selectedTheme);
        
        // Atualizar toggle se existir
        this.updateThemeToggle(selectedTheme);
        
        // Disparar evento customizado
        document.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: selectedTheme }
        }));
    }
    
    // Alternar tema
    toggleTheme() {
        const themes = ['light', 'dark', 'auto'];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % themes.length;
        const nextTheme = themes[nextIndex];
        
        this.applyTheme(nextTheme);
        this.saveUserPreference();
    }
    
    // Criar botão de toggle
    createThemeToggle() {
        // Verificar se já existe
        if (document.getElementById('theme-toggle')) {
            return;
        }
        
        const toggle = document.createElement('button');
        toggle.id = 'theme-toggle';
        toggle.className = 'theme-toggle';
        toggle.setAttribute('aria-label', 'Alternar tema');
        toggle.innerHTML = `
            <i class="fas fa-sun theme-toggle-icon sun"></i>
            <i class="fas fa-moon theme-toggle-icon moon"></i>
        `;
        
        // Adicionar ao header se existir
        const header = document.querySelector('nav') || document.querySelector('header');
        if (header) {
            const navItems = header.querySelector('.flex.items-center.gap-4') || 
                           header.querySelector('.flex.items-center.justify-end.gap-4');
            if (navItems) {
                navItems.appendChild(toggle);
            }
        } else {
            // Adicionar ao body se não houver header
            document.body.appendChild(toggle);
            toggle.style.position = 'fixed';
            toggle.style.top = '20px';
            toggle.style.right = '20px';
            toggle.style.zIndex = '1000';
        }
        
        // Adicionar evento de clique
        toggle.addEventListener('click', () => this.toggleTheme());
    }
    
    // Atualizar aparência do toggle
    updateThemeToggle(theme) {
        const toggle = document.getElementById('theme-toggle');
        if (!toggle) return;
        
        const sunIcon = toggle.querySelector('.sun');
        const moonIcon = toggle.querySelector('.moon');
        
        if (theme === 'dark') {
            sunIcon.style.opacity = '0';
            moonIcon.style.opacity = '1';
        } else if (theme === 'light') {
            sunIcon.style.opacity = '1';
            moonIcon.style.opacity = '0';
        } else { // auto
            sunIcon.style.opacity = '0.5';
            moonIcon.style.opacity = '0.5';
        }
    }
    
    // Configurar event listeners
    setupEventListeners() {
        // Detectar mudanças no sistema
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (e) => {
                if (this.currentTheme === 'auto') {
                    this.applyTheme('auto');
                }
            });
        }
        
        // Salvar preferência quando o usuário interage
        document.addEventListener('click', () => {
            this.saveUserPreference();
        });
        
        // Detectar mudanças de visibilidade da página
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.saveUserPreference();
            }
        });
    }
    
    // Salvar preferência do usuário
    async saveUserPreference() {
        // Verificar se há usuário logado
        const userId = this.getUserId();
        if (!userId) return;
        
        try {
            const response = await fetch('salvar_preferencia_tema.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    tema: this.currentTheme
                })
            });
            
            if (!response.ok) {
                console.warn('Erro ao salvar preferência de tema');
            }
        } catch (error) {
            console.warn('Erro ao salvar preferência de tema:', error);
        }
    }
    
    // Obter ID do usuário (implementar conforme sua lógica)
    getUserId() {
        // Verificar se há sessão ativa
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        return userId || null;
    }
    
    // Obter tema atual
    getCurrentTheme() {
        return this.currentTheme;
    }
    
    // Definir tema específico
    setTheme(theme) {
        this.applyTheme(theme);
        this.saveUserPreference();
    }
    
    // Resetar para tema do sistema
    resetToSystem() {
        this.applyTheme('auto');
        this.saveUserPreference();
    }
    
    // Obter estatísticas de uso
    getThemeStats() {
        const stats = {
            current: this.currentTheme,
            stored: this.getStoredTheme(),
            system: this.getSystemTheme(),
            timestamp: new Date().toISOString()
        };
        
        return stats;
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// Função global para alternar tema (para uso em botões customizados)
window.toggleTheme = function() {
    if (window.themeManager) {
        window.themeManager.toggleTheme();
    }
};

// Função global para definir tema específico
window.setTheme = function(theme) {
    if (window.themeManager) {
        window.themeManager.setTheme(theme);
    }
};

// Exportar para uso em módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}
