// assets/js/lightweight.js - JavaScript Otimizado e Leve

document.addEventListener('DOMContentLoaded', function() {
    
    // ===================================
    // INICIALIZAÇÃO BÁSICA
    // ===================================
    
    // Inicializa ScrollReveal apenas se disponível
    if (typeof ScrollReveal !== 'undefined') {
        ScrollReveal().reveal('.gradient-title', { 
            duration: 800,
            distance: '30px',
            origin: 'top',
            reset: false
        });
        
        ScrollReveal().reveal('.glass-card', { 
            duration: 600,
            distance: '20px',
            origin: 'bottom',
            interval: 100,
            reset: false
        });
    }
    
    // ===================================
    // CURSOR GLOW OTIMIZADO (DESKTOP)
    // ===================================
    const cursorGlow = document.getElementById('cursor-glow');
    if (cursorGlow && window.innerWidth > 768) {
        let mouseX = 0, mouseY = 0;
        let glowX = 0, glowY = 0;
        let isAnimating = false;
        
        // Throttle mousemove para melhor performance
        let mouseMoveTimeout;
        document.addEventListener('mousemove', (e) => {
            if (mouseMoveTimeout) {
                clearTimeout(mouseMoveTimeout);
            }
            mouseMoveTimeout = setTimeout(() => {
                mouseX = e.clientX;
                mouseY = e.clientY;
            }, 16); // ~60fps
        });
        
        function animateGlow() {
            if (!isAnimating) {
                isAnimating = true;
                requestAnimationFrame(() => {
                    glowX += (mouseX - glowX) * 0.1;
                    glowY += (mouseY - glowY) * 0.1;
                    
                    cursorGlow.style.left = glowX + 'px';
                    cursorGlow.style.top = glowY + 'px';
                    
                    isAnimating = false;
                });
            }
        }
        
        // Inicia animação apenas quando necessário
        let animationId;
        function startAnimation() {
            if (!animationId) {
                animationId = setInterval(animateGlow, 16);
            }
        }
        
        function stopAnimation() {
            if (animationId) {
                clearInterval(animationId);
                animationId = null;
            }
        }
        
        // Inicia quando mouse entra na página
        document.addEventListener('mouseenter', startAnimation);
        document.addEventListener('mouseleave', stopAnimation);
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
            } else {
                mainNav.classList.remove('frosted-glass-nav');
            }
            
            lastScrollY = currentScrollY;
        });
    }
    
    // ===================================
    // MENU MOBILE SIMPLES
    // ===================================
    const menuToggle = document.getElementById('menu-toggle');
    const sideMenu = document.getElementById('side-menu');
    const sideMenuPanel = document.getElementById('side-menu-panel');
    const body = document.body;

    if (menuToggle && sideMenu && sideMenuPanel) {
        menuToggle.addEventListener('click', function() {
            const isOpen = sideMenu.classList.contains('translate-x-0');
            
            if (isOpen) {
                sideMenu.classList.remove('translate-x-0');
                sideMenu.classList.add('translate-x-full');
                sideMenuPanel.classList.remove('is-open');
                body.classList.remove('menu-open');
            } else {
                sideMenu.classList.remove('translate-x-full');
                sideMenu.classList.add('translate-x-0');
                sideMenuPanel.classList.add('is-open');
                body.classList.add('menu-open');
            }
        });

        // Fechar menu ao clicar fora
        sideMenu.addEventListener('click', function(e) {
            if (e.target === sideMenu) {
                sideMenu.classList.remove('translate-x-0');
                sideMenu.classList.add('translate-x-full');
                sideMenuPanel.classList.remove('is-open');
                body.classList.remove('menu-open');
            }
        });
    }
    
    // ===================================
    // SWIPER CAROUSELS OTIMIZADOS
    // ===================================
    
    // Banner Principal - Versão Corrigida
    function initializeMainBanner() {
        const mainBanner = document.querySelector('.main-banner-carousel');
        
        if (mainBanner) {
            const slides = mainBanner.querySelectorAll('.swiper-slide');
            const slidesCount = slides.length;
            
            let config = {
                loop: slidesCount > 1,
                autoplay: slidesCount > 1 ? {
                    delay: 5000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                } : false,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                    dynamicBullets: false,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                // Configurações para estabilidade
                watchSlidesProgress: false,
                watchSlidesVisibility: false,
                preventClicks: false,
                preventClicksPropagation: false,
                allowTouchMove: true,
                grabCursor: true,
                on: {
                    init: function() {
                        console.log('Banner principal inicializado com', slidesCount, 'slides');
                    }
                }
            };
            
            if (slidesCount > 1) {
                config.loopAdditionalSlides = 1;
                config.loopedSlides = 1;
            }
            
            new Swiper(mainBanner, config);
        }
    }
    
    // Inicializa banner principal
    initializeMainBanner();
    
    // Produtos agora são exibidos em grid simples - sem carrossel
    
    // ===================================
    // LAZY LOADING OTIMIZADO
    // ===================================
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.classList.add('loaded');
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
        // Fallback para navegadores antigos
        lazyImages.forEach(img => {
            img.classList.add('loaded');
        });
    }
    
    // ===================================
    // SISTEMA DE NOTIFICAÇÕES SIMPLES
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
        
        // Mostra o toast
        toast.classList.remove('opacity-0', 'translate-y-5');
        toast.classList.add('opacity-100', 'translate-y-0');
        
        // Esconde após 3 segundos
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-5');
            toast.classList.remove('opacity-100', 'translate-y-0');
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
                        cartCount.textContent = currentCount + parseInt(formData.get('quantidade'));
                        
                        // Animação simples
                        cartCount.style.transform = 'scale(1.2)';
                        setTimeout(() => {
                            cartCount.style.transform = 'scale(1)';
                        }, 200);
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
    
    console.log('🚀 JavaScript otimizado carregado!');
});
