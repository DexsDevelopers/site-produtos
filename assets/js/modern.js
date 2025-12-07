// assets/js/modern.js - JavaScript Moderno com Animações Profissionais

document.addEventListener('DOMContentLoaded', function() {
    
    // ===================================
    // INICIALIZAÇÃO DAS BIBLIOTECAS
    // ===================================
    
    // Inicializa AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 1000,
            easing: 'ease-out-cubic',
            once: true,
            offset: 100,
            delay: 100
        });
    }
    
    // Inicializa GSAP ScrollTrigger
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
        
        // Animação de entrada para elementos
        gsap.fromTo('.fade-in-up', 
            { opacity: 0, y: 50 },
            { 
                opacity: 1, 
                y: 0, 
                duration: 0.8, 
                ease: 'power2.out',
                stagger: 0.1,
                scrollTrigger: {
                    trigger: '.fade-in-up',
                    start: 'top 80%',
                    toggleActions: 'play none none reverse'
                }
            }
        );
    }
    
    // ===================================
    // CURSOR GLOW ANIMADO
    // ===================================
    const cursorGlow = document.getElementById('cursor-glow');
    if (cursorGlow) {
        document.addEventListener('mousemove', (e) => {
            gsap.to(cursorGlow, {
                x: e.clientX,
                y: e.clientY,
                duration: 0.3,
                ease: 'power2.out'
            });
        });
    }
    
    // ===================================
    // NAVEGAÇÃO COM EFEITO DE SCROLL
    // ===================================
    const mainNav = document.getElementById('main-nav');
    if (mainNav) {
        let lastScrollY = window.scrollY;
        
        window.addEventListener('scroll', () => {
            const currentScrollY = window.scrollY;
            
            if (currentScrollY > 100) {
                mainNav.classList.add('frosted-glass-nav');
                gsap.to(mainNav, {
                    y: currentScrollY > lastScrollY ? -100 : 0,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            } else {
                mainNav.classList.remove('frosted-glass-nav');
                gsap.to(mainNav, { y: 0, duration: 0.3 });
            }
            
            lastScrollY = currentScrollY;
        });
    }
    
    // ===================================
    // MENU MOBILE ANIMADO
    // ===================================
    const menuToggle = document.getElementById('menu-toggle');
    const sideMenu = document.getElementById('side-menu');
    const sideMenuPanel = document.getElementById('side-menu-panel');
    const body = document.body;

    if (menuToggle && sideMenu && sideMenuPanel) {
        menuToggle.addEventListener('click', function() {
            const isOpen = sideMenu.classList.contains('translate-x-0');
            
            if (isOpen) {
                // Fechar menu com animação GSAP
                gsap.to(sideMenu, {
                    x: '100%',
                    duration: 0.4,
                    ease: 'power2.inOut',
                    onComplete: () => {
                        sideMenu.classList.add('hidden');
                    }
                });
                sideMenuPanel.classList.remove('is-open');
                body.classList.remove('menu-open');
            } else {
                // Abrir menu com animação GSAP
                sideMenu.classList.remove('hidden');
                gsap.fromTo(sideMenu, 
                    { x: '100%' },
                    { 
                        x: '0%', 
                        duration: 0.4, 
                        ease: 'power2.inOut',
                        onComplete: () => {
                            sideMenuPanel.classList.add('is-open');
                        }
                    }
                );
                body.classList.add('menu-open');
            }
        });

        // Fechar menu ao clicar fora
        sideMenu.addEventListener('click', function(e) {
            if (e.target === sideMenu) {
                gsap.to(sideMenu, {
                    x: '100%',
                    duration: 0.4,
                    ease: 'power2.inOut',
                    onComplete: () => {
                        sideMenu.classList.add('hidden');
                    }
                });
                sideMenuPanel.classList.remove('is-open');
                body.classList.remove('menu-open');
            }
        });
    }
    
    // ===================================
    // SWIPER CAROUSELS MODERNOS
    // ===================================
    
    // Banner Principal
    if (document.querySelector('.main-banner-carousel')) {
        new Swiper('.main-banner-carousel', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            on: {
                init: function() {
                    gsap.fromTo('.swiper-slide', 
                        { opacity: 0, scale: 1.1 },
                        { opacity: 1, scale: 1, duration: 1, ease: 'power2.out' }
                    );
                }
            }
        });
    }
    
    // Carrossel de Produtos
    if (document.querySelector('.product-carousel')) {
        new Swiper('.product-carousel', {
            slidesPerView: 1.5,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            breakpoints: {
                480: { slidesPerView: 2, spaceBetween: 20 },
                640: { slidesPerView: 2.5, spaceBetween: 25 },
                768: { slidesPerView: 3, spaceBetween: 30 },
                1024: { slidesPerView: 4, spaceBetween: 30 },
                1280: { slidesPerView: 5, spaceBetween: 30 }
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            on: {
                init: function() {
                    // Animação de entrada para os slides
                    gsap.fromTo('.swiper-slide', 
                        { opacity: 0, y: 30, scale: 0.9 },
                        { 
                            opacity: 1, 
                            y: 0, 
                            scale: 1, 
                            duration: 0.6, 
                            ease: 'power2.out',
                            stagger: 0.1
                        }
                    );
                }
            }
        });
    }
    
    // ===================================
    // LAZY LOADING AVANÇADO
    // ===================================
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Animação de entrada
                    gsap.fromTo(img, 
                        { opacity: 0, scale: 1.1 },
                        { 
                            opacity: 1, 
                            scale: 1, 
                            duration: 0.8, 
                            ease: 'power2.out',
                            onComplete: () => {
                                img.classList.add('loaded');
                            }
                        }
                    );
                    
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.1
        });

        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback para navegadores que não suportam IntersectionObserver
        lazyImages.forEach(img => {
            img.classList.add('loaded');
        });
    }
    
    // ===================================
    // ANIMAÇÕES DE HOVER AVANÇADAS
    // ===================================
    
    // Cards de Produto
    const productCards = document.querySelectorAll('.glass-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            gsap.to(this, {
                scale: 1.05,
                y: -10,
                duration: 0.3,
                ease: 'power2.out'
            });
        });
        
        card.addEventListener('mouseleave', function() {
            gsap.to(this, {
                scale: 1,
                y: 0,
                duration: 0.3,
                ease: 'power2.out'
            });
        });
    });
    
    // Botões Modernos
    const modernButtons = document.querySelectorAll('.btn-modern');
    modernButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            gsap.to(this, {
                scale: 1.05,
                duration: 0.2,
                ease: 'power2.out'
            });
        });
        
        button.addEventListener('mouseleave', function() {
            gsap.to(this, {
                scale: 1,
                duration: 0.2,
                ease: 'power2.out'
            });
        });
    });
    
    // ===================================
    // SISTEMA DE NOTIFICAÇÕES (TOAST)
    // ===================================
    function showToast(message, type = 'info') {
        const toast = document.getElementById('toast-notification');
        const toastIcon = document.getElementById('toast-icon');
        const toastMessage = document.getElementById('toast-message');
        
        if (!toast || !toastIcon || !toastMessage) return;
        
        // Define ícone e cor baseado no tipo
        let icon, bgColor;
        switch (type) {
            case 'success':
                icon = '✓';
                bgColor = 'bg-green-600';
                break;
            case 'error':
                icon = '✕';
                bgColor = 'bg-red-600';
                break;
            case 'warning':
                icon = '⚠';
                bgColor = 'bg-yellow-600';
                break;
            default:
                icon = 'ℹ';
                bgColor = 'bg-blue-600';
        }
        
        // Atualiza conteúdo
        toastIcon.textContent = icon;
        toastMessage.textContent = message;
        
        // Remove classes de cor anteriores
        toast.classList.remove('bg-green-600', 'bg-red-600', 'bg-yellow-600', 'bg-blue-600');
        toast.classList.add(bgColor);
        
        // Animação de entrada
        gsap.fromTo(toast, 
            { opacity: 0, y: 50, scale: 0.8 },
            { 
                opacity: 1, 
                y: 0, 
                scale: 1, 
                duration: 0.5, 
                ease: 'back.out(1.7)' 
            }
        );
        
        // Esconde após 3 segundos
        setTimeout(() => {
            gsap.to(toast, {
                opacity: 0,
                y: 50,
                scale: 0.8,
                duration: 0.3,
                ease: 'power2.in'
            });
        }, 3000);
    }
    
    // ===================================
    // AJAX PARA ADICIONAR AO CARRINHO
    // ===================================
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('#add-to-cart-button');
            const originalText = button.textContent;
            
            // Animação de loading
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
            button.disabled = true;
            
            fetch('adicionar_carrinho.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Produto adicionado ao carrinho!', 'success');
                    
                    // Atualiza contador do carrinho
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        const currentCount = parseInt(cartCount.textContent) || 0;
                        gsap.fromTo(cartCount, 
                            { scale: 1 },
                            { 
                                scale: 1.3, 
                                duration: 0.2, 
                                yoyo: true, 
                                repeat: 1,
                                ease: 'power2.out',
                                onComplete: () => {
                                    cartCount.textContent = currentCount + parseInt(formData.get('quantidade'));
                                }
                            }
                        );
                    }
                } else {
                    showToast(data.message || 'Erro ao adicionar produto', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro de conexão. Tente novamente.', 'error');
            })
            .finally(() => {
                button.textContent = originalText;
                button.disabled = false;
            });
        });
    }
    
    // ===================================
    // ANIMAÇÕES DE SCROLL REVEAL
    // ===================================
    if (typeof ScrollReveal !== 'undefined') {
        const sr = ScrollReveal({
            distance: '30px',
            duration: 1000,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
            reset: false
        });
        
        // Animações específicas
        sr.reveal('.gradient-title', { 
            origin: 'top',
            delay: 200
        });
        
        sr.reveal('.glass-card', { 
            origin: 'bottom',
            interval: 100,
            delay: 300
        });
        
        sr.reveal('.btn-modern', { 
            origin: 'bottom',
            delay: 400
        });
    }
    
    // ===================================
    // PARTÍCULAS FLUTUANTES
    // ===================================
    function createParticles() {
        const particlesContainer = document.querySelector('.particles');
        if (!particlesContainer) return;
        
        // Cria partículas adicionais com JavaScript
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.cssText = `
                left: ${Math.random() * 100}%;
                width: ${Math.random() * 4 + 2}px;
                height: ${Math.random() * 4 + 2}px;
                animation-delay: ${Math.random() * 20}s;
                animation-duration: ${Math.random() * 10 + 15}s;
            `;
            particlesContainer.appendChild(particle);
        }
    }
    
    createParticles();
    
    // ===================================
    // PERFORMANCE: THROTTLE SCROLL EVENTS
    // ===================================
    let ticking = false;
    
    function updateOnScroll() {
        // Lógica de scroll aqui
        ticking = false;
    }
    
    function requestTick() {
        if (!ticking) {
            requestAnimationFrame(updateOnScroll);
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', requestTick);
    
    console.log('🚀 JavaScript moderno carregado com sucesso!');
});
