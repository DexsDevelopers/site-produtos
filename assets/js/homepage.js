/**
 * HOMEPAGE JAVASCRIPT - VERSÃO ORGANIZADA
 * Funcionalidades específicas da página principal
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ===== INICIALIZAÇÃO DOS CARROSSÉIS =====
    initializeCarousels();
    
    // ===== ANIMAÇÕES DE SCROLL =====
    initializeScrollAnimations();
    
    // ===== LAZY LOADING =====
    initializeLazyLoading();
    
    // ===== INTERAÇÕES ESPECÍFICAS =====
    initializeInteractions();
    
    // ===== PERFORMANCE MONITORING =====
    initializePerformanceMonitoring();
});

/**
 * Inicializa todos os carrosséis da página
 */
function initializeCarousels() {
    // Banner Principal
    if (document.querySelector('.main-banner-swiper')) {
        new Swiper('.main-banner-swiper', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            on: {
                slideChange: function() {
                    // Pausar autoplay ao hover
                    this.autoplay.stop();
                },
                slideChangeTransitionEnd: function() {
                    // Retomar autoplay após transição
                    this.autoplay.start();
                }
            }
        });
    }
    
    // Depoimentos
    if (document.querySelector('.testimonials-swiper')) {
        new Swiper('.testimonials-swiper', {
            loop: true,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            slidesPerView: 1,
            spaceBetween: 20,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 40,
                }
            }
        });
    }
}

/**
 * Inicializa animações de scroll
 */
function initializeScrollAnimations() {
    // Verifica se ScrollReveal está disponível
    if (typeof ScrollReveal !== 'undefined') {
        const sr = ScrollReveal({
            distance: '60px',
            duration: 1000,
            easing: 'cubic-bezier(0.5, 0, 0, 1)',
            reset: false,
            mobile: true
        });
        
        // Animações específicas
        sr.reveal('.hero-content', { 
            origin: 'top', 
            delay: 200,
            interval: 100
        });
        
        sr.reveal('.section-header', { 
            origin: 'top', 
            delay: 100
        });
        
        sr.reveal('.product-card', { 
            origin: 'bottom', 
            delay: 200,
            interval: 100
        });
        
        sr.reveal('.category-card', { 
            origin: 'bottom', 
            delay: 100,
            interval: 50
        });
        
        sr.reveal('.testimonial-card', { 
            origin: 'left', 
            delay: 200
        });
        
        sr.reveal('.cta-content', { 
            origin: 'bottom', 
            delay: 300
        });
    }
}

/**
 * Inicializa lazy loading para imagens
 */
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback para navegadores sem suporte
        images.forEach(img => {
            img.classList.add('loaded');
        });
    }
}

/**
 * Inicializa interações específicas
 */
function initializeInteractions() {
    // Smooth scroll para links internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Efeito hover nos cards de produto
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Efeito parallax no hero (opcional)
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallax = scrolled * 0.5;
            heroSection.style.transform = `translateY(${parallax}px)`;
        });
    }
}

/**
 * Inicializa monitoramento de performance
 */
function initializePerformanceMonitoring() {
    // Medir tempo de carregamento
    window.addEventListener('load', function() {
        const loadTime = performance.now();
        console.log(`Página carregada em ${loadTime.toFixed(2)}ms`);
        
        // Enviar métricas para analytics (se configurado)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'page_load_time', {
                'value': Math.round(loadTime),
                'event_category': 'Performance'
            });
        }
    });
    
    // Monitorar erros de JavaScript
    window.addEventListener('error', function(e) {
        console.error('Erro JavaScript:', e.error);
        
        // Enviar erro para sistema de monitoramento
        if (typeof gtag !== 'undefined') {
            gtag('event', 'javascript_error', {
                'error_message': e.error.message,
                'error_filename': e.filename,
                'error_lineno': e.lineno,
                'event_category': 'Error'
            });
        }
    });
}

/**
 * Utilitários para interações
 */
const HomepageUtils = {
    /**
     * Debounce function para otimizar eventos
     */
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    /**
     * Throttle function para limitar execução
     */
    throttle: function(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    /**
     * Verifica se elemento está visível
     */
    isElementVisible: function(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    },
    
    /**
     * Anima contador numérico
     */
    animateCounter: function(element, start, end, duration) {
        const startTime = performance.now();
        const difference = end - start;
        
        function updateCounter(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.floor(start + (difference * progress));
            
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        }
        
        requestAnimationFrame(updateCounter);
    }
};

/**
 * Eventos de otimização
 */
// Otimizar scroll com throttle
window.addEventListener('scroll', HomepageUtils.throttle(function() {
    // Lógica de scroll otimizada aqui
}, 16)); // ~60fps

// Otimizar resize com debounce
window.addEventListener('resize', HomepageUtils.debounce(function() {
    // Recalcular layouts se necessário
    console.log('Window resized');
}, 250));

/**
 * Exportar utilitários para uso global
 */
window.HomepageUtils = HomepageUtils;
