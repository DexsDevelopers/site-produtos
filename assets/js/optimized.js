// JavaScript Otimizado para Mobile e Desktop - Sem Travamentos
(function() {
    'use strict';

    // Configurações globais
    const CONFIG = {
        debounceDelay: 100,
        throttleDelay: 16,
        animationDuration: 300,
        lazyLoadMargin: '50px'
    };

    // Utilitários de Performance
    const Utils = {
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

        isMobile: function() {
            return window.innerWidth <= 768;
        },

        isTouch: function() {
            return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        }
    };

    // Sistema de Cache para DOM
    const DOMCache = {
        elements: new Map(),
        
        get: function(selector) {
            if (!this.elements.has(selector)) {
                this.elements.set(selector, document.querySelector(selector));
            }
            return this.elements.get(selector);
        },

        getAll: function(selector) {
            const key = selector + '_all';
            if (!this.elements.has(key)) {
                this.elements.set(key, document.querySelectorAll(selector));
            }
            return this.elements.get(key);
        }
    };

    // Cursor Glow Otimizado
    class CursorGlow {
        constructor() {
            this.glow = DOMCache.get('#cursor-glow');
            this.isActive = false;
            this.init();
        }

        init() {
            if (!this.glow) return;

            this.handleMouseMove = Utils.throttle((e) => {
                if (!this.isActive) return;
                
                requestAnimationFrame(() => {
                    this.glow.style.left = e.clientX + 'px';
                    this.glow.style.top = e.clientY + 'px';
                });
            }, CONFIG.throttleDelay);

            this.handleMouseEnter = () => {
                this.isActive = true;
                this.glow.style.opacity = '1';
            };

            this.handleMouseLeave = () => {
                this.isActive = false;
                this.glow.style.opacity = '0';
            };

            document.addEventListener('mousemove', this.handleMouseMove);
            document.addEventListener('mouseenter', this.handleMouseEnter);
            document.addEventListener('mouseleave', this.handleMouseLeave);
        }

        destroy() {
            document.removeEventListener('mousemove', this.handleMouseMove);
            document.removeEventListener('mouseenter', this.handleMouseEnter);
            document.removeEventListener('mouseleave', this.handleMouseLeave);
        }
    }

    // Navbar Otimizada
    class Navbar {
        constructor() {
            this.nav = DOMCache.get('#main-nav');
            this.lastScrollY = 0;
            this.init();
        }

        init() {
            if (!this.nav) return;

            this.handleScroll = Utils.throttle(() => {
                const currentScrollY = window.scrollY;
                
                if (currentScrollY > 50) {
                    this.nav.classList.add('frosted-glass-nav');
                } else {
                    this.nav.classList.remove('frosted-glass-nav');
                }

                this.lastScrollY = currentScrollY;
            }, CONFIG.throttleDelay);

            window.addEventListener('scroll', this.handleScroll, { passive: true });
        }
    }

    // Menu Lateral Otimizado
    class SideMenu {
        constructor() {
            this.menuBtn = DOMCache.get('#menu-btn');
            this.closeBtn = DOMCache.get('#close-menu-btn');
            this.menuContainer = DOMCache.get('#side-menu-container');
            this.menuPanel = DOMCache.get('#side-menu-panel');
            this.menuOverlay = DOMCache.get('#side-menu-overlay');
            this.menuLinks = DOMCache.getAll('.menu-link');
            this.body = document.body;
            this.isOpen = false;
            this.init();
        }

        init() {
            if (!this.menuBtn || !this.menuContainer) return;

            this.menuBtn.addEventListener('click', this.openMenu.bind(this));
            this.closeBtn?.addEventListener('click', this.closeMenu.bind(this));
            this.menuOverlay?.addEventListener('click', this.closeMenu.bind(this));
            
            this.menuLinks.forEach(link => {
                link.addEventListener('click', this.closeMenu.bind(this));
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.closeMenu();
                }
            });
        }

        openMenu() {
            if (this.isOpen) return;
            
            this.isOpen = true;
            this.menuContainer.classList.remove('hidden');
            this.body.classList.add('menu-open');
            this.menuContainer.setAttribute('aria-hidden', 'false');
            
            requestAnimationFrame(() => {
                this.menuOverlay?.classList.remove('opacity-0');
                this.menuPanel?.classList.remove('-translate-x-full');
                this.menuPanel?.classList.add('is-open');
            });
        }

        closeMenu() {
            if (!this.isOpen) return;
            
            this.isOpen = false;
            this.body.classList.remove('menu-open');
            this.menuPanel?.classList.remove('is-open');
            this.menuOverlay?.classList.add('opacity-0');
            this.menuPanel?.classList.add('-translate-x-full');
            
            setTimeout(() => {
                this.menuContainer.classList.add('hidden');
                this.menuContainer.setAttribute('aria-hidden', 'true');
            }, CONFIG.animationDuration);
        }
    }

    // Lazy Loading Otimizado
    class LazyLoader {
        constructor() {
            this.images = DOMCache.getAll('img[loading="lazy"]');
            this.observer = null;
            this.init();
        }

        init() {
            if (!('IntersectionObserver' in window) || this.images.length === 0) {
                this.fallback();
                return;
            }

            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        this.observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: CONFIG.lazyLoadMargin,
                threshold: 0.1
            });

            this.images.forEach(img => {
                this.observer.observe(img);
            });
        }

        loadImage(img) {
            if (img.dataset.src) {
                img.src = img.dataset.src;
                delete img.dataset.src;
            }
            
            img.classList.add('loaded');
        }

        fallback() {
            this.images.forEach(img => {
                this.loadImage(img);
            });
        }
    }

    // Sistema de Toast Otimizado
    class ToastSystem {
        constructor() {
            this.toast = DOMCache.get('#toast-notification');
            this.toastIcon = DOMCache.get('#toast-icon');
            this.toastMessage = DOMCache.get('#toast-message');
            this.timeout = null;
        }

        show(message, type = 'info', duration = 3000) {
            if (!this.toast || !this.toastIcon || !this.toastMessage) return;

            this.clear();

            const config = this.getConfig(type);
            
            this.toastIcon.textContent = config.icon;
            this.toastMessage.textContent = message;
            
            this.toast.className = `toast-notification ${config.bgColor}`;
            this.toast.classList.add('show');

            this.timeout = setTimeout(() => {
                this.hide();
            }, duration);
        }

        getConfig(type) {
            const configs = {
                success: { icon: '✓', bgColor: 'bg-green-600' },
                error: { icon: '✕', bgColor: 'bg-red-600' },
                warning: { icon: '⚠', bgColor: 'bg-yellow-600' },
                info: { icon: 'ℹ', bgColor: 'bg-blue-600' }
            };
            return configs[type] || configs.info;
        }

        hide() {
            if (this.toast) {
                this.toast.classList.remove('show');
            }
            this.clear();
        }

        clear() {
            if (this.timeout) {
                clearTimeout(this.timeout);
                this.timeout = null;
            }
        }
    }

    // Swiper Otimizado
    class SwiperManager {
        constructor() {
            this.swipers = new Map();
            this.init();
        }

        init() {
            if (typeof Swiper === 'undefined') {
                console.warn('Swiper not loaded');
                return;
            }

            this.initMainBanner();
            this.initProductCarousels();
            this.initTestimonialCarousel();
        }

        initMainBanner() {
            const element = DOMCache.get('.main-banner-carousel');
            if (!element) return;

            this.swipers.set('mainBanner', new Swiper(element, {
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
                }
            }));
        }

        initProductCarousels() {
            const elements = DOMCache.getAll('.product-carousel');
            elements.forEach((element, index) => {
                this.swipers.set(`productCarousel_${index}`, new Swiper(element, {
                    loop: false,
                    slidesPerView: Utils.isMobile() ? 1.5 : 2.5,
                    spaceBetween: 16,
                    navigation: {
                        nextEl: element.querySelector('.swiper-button-next'),
                        prevEl: element.querySelector('.swiper-button-prev'),
                    },
                    breakpoints: {
                        480: { slidesPerView: 2, spaceBetween: 16 },
                        640: { slidesPerView: 2.5, spaceBetween: 20 },
                        768: { slidesPerView: 3, spaceBetween: 20 },
                        1024: { slidesPerView: 4, spaceBetween: 24 },
                        1280: { slidesPerView: 5, spaceBetween: 24 }
                    }
                }));
            });
        }

        initTestimonialCarousel() {
            const element = DOMCache.get('.testimonial-carousel');
            if (!element) return;

            this.swipers.set('testimonial', new Swiper(element, {
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
                    768: { slidesPerView: 2, spaceBetween: 24 },
                    1024: { slidesPerView: 3, spaceBetween: 30 },
                    1280: { slidesPerView: 4, spaceBetween: 30 }
                }
            }));
        }

        destroy() {
            this.swipers.forEach(swiper => {
                if (swiper && swiper.destroy) {
                    swiper.destroy(true, true);
                }
            });
            this.swipers.clear();
        }
    }

    // ScrollReveal Otimizado
    class ScrollRevealManager {
        constructor() {
            this.init();
        }

        init() {
            if (typeof ScrollReveal === 'undefined') {
                console.warn('ScrollReveal not loaded');
                return;
            }

            const sr = ScrollReveal({
                distance: '30px',
                duration: 800,
                easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
                reset: false,
                mobile: true
            });

            sr.reveal('.gradient-title', { 
                origin: 'top',
                delay: 200
            });
            
            sr.reveal('.glass-card', { 
                origin: 'bottom',
                interval: 100,
                delay: 300
            });
            
            sr.reveal('.scroll-reveal-section', { 
                origin: 'bottom',
                interval: 150,
                delay: 200
            });
        }
    }

    // Carrinho AJAX Otimizado
    class CartManager {
        constructor() {
            this.form = DOMCache.get('#add-to-cart-form');
            this.toast = new ToastSystem();
            this.init();
        }

        init() {
            if (!this.form) return;

            this.form.addEventListener('submit', this.handleSubmit.bind(this));
        }

        async handleSubmit(event) {
            event.preventDefault();
            
            const button = DOMCache.get('#add-to-cart-button');
            if (!button) return;

            const originalText = button.innerHTML;
            const originalDisabled = button.disabled;
            
            this.setButtonLoading(button, 'Adicionando...');

            try {
                const formData = new FormData(this.form);
                const response = await fetch('adicionar_carrinho.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.updateCartCounters(data.cart_count);
                    this.toast.show(`${data.produto_nome} adicionado ao carrinho!`, 'success');
                    this.setButtonSuccess(button, '✓ Adicionado!');
                } else {
                    this.toast.show(data.message || 'Erro ao adicionar produto', 'error');
                    this.resetButton(button, originalText, originalDisabled);
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                this.toast.show('Erro de conexão. Tente novamente.', 'error');
                this.resetButton(button, originalText, originalDisabled);
            }
        }

        setButtonLoading(button, text) {
            button.disabled = true;
            button.innerHTML = text;
            button.classList.add('opacity-75');
        }

        setButtonSuccess(button, text) {
            button.innerHTML = text;
            button.classList.remove('opacity-75');
            button.classList.add('bg-green-600');
            
            setTimeout(() => {
                this.resetButton(button, 'Adicionar ao Carrinho', false);
            }, 2000);
        }

        resetButton(button, text, disabled) {
            button.innerHTML = text;
            button.disabled = disabled;
            button.classList.remove('opacity-75', 'bg-green-600');
        }

        updateCartCounters(count) {
            const desktopCounter = DOMCache.get('#cart-count');
            const mobileCounter = DOMCache.get('#cart-count-mobile');
            
            [desktopCounter, mobileCounter].forEach(counter => {
                if (counter) {
                    counter.textContent = count;
                    counter.classList.remove('hidden');
                }
            });
        }
    }

    // Inicialização Principal
    class App {
        constructor() {
            this.components = [];
            this.init();
        }

        init() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.start());
            } else {
                this.start();
            }
        }

        start() {
            try {
                this.components = [
                    new CursorGlow(),
                    new Navbar(),
                    new SideMenu(),
                    new LazyLoader(),
                    new ToastSystem(),
                    new SwiperManager(),
                    new ScrollRevealManager(),
                    new CartManager()
                ];

                console.log('App initialized successfully');
            } catch (error) {
                console.error('Error initializing app:', error);
            }
        }

        destroy() {
            this.components.forEach(component => {
                if (component && typeof component.destroy === 'function') {
                    component.destroy();
                }
            });
            this.components = [];
        }
    }

    // Inicializar App
    window.App = new App();

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (window.App) {
            window.App.destroy();
        }
    });

})();


