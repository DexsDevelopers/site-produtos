// assets/js/main.js (VERSÃO FINAL UNIFICADA)

document.addEventListener('DOMContentLoaded', () => {
    
    // ===================================
    // LÓGICA DO NAVBAR COM EFEITO DE SCROLL
    // ===================================
    const mainNav = document.getElementById('main-nav');
    if (mainNav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                mainNav.classList.add('frosted-glass-nav');
            } else {
                mainNav.classList.remove('frosted-glass-nav');
            }
        });
    }

    // ===================================
    // LÓGICA DO MENU LATERAL
    // ===================================
    const body = document.body;
    const menuBtn = document.getElementById('menu-btn');
    const closeBtn = document.getElementById('close-menu-btn');
    const menuContainer = document.getElementById('side-menu-container');
    const menuPanel = document.getElementById('side-menu-panel');
    const menuOverlay = document.getElementById('side-menu-overlay');
    const menuLinks = document.querySelectorAll('.menu-link');

    const openMenu = () => {
        if (!menuContainer || !menuPanel) return;
        menuContainer.classList.remove('hidden');
        body.classList.add('menu-open');
        menuContainer.setAttribute('aria-hidden', 'false');
        setTimeout(() => {
            menuOverlay.classList.remove('opacity-0');
            menuPanel.classList.remove('-translate-x-full');
            menuPanel.classList.add('is-open');
        }, 10);
    };

    const closeMenu = () => {
        if (!menuContainer || !menuPanel) return;
        body.classList.remove('menu-open');
        menuPanel.classList.remove('is-open');
        menuOverlay.classList.add('opacity-0');
        menuPanel.classList.add('-translate-x-full');
        setTimeout(() => {
            menuContainer.classList.add('hidden');
            menuContainer.setAttribute('aria-hidden', 'true');
        }, 300);
    };

    if (menuBtn) menuBtn.addEventListener('click', openMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    if (menuOverlay) menuOverlay.addEventListener('click', closeMenu);
    
    menuLinks.forEach(link => {
        link.addEventListener('click', closeMenu);
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !menuContainer.classList.contains('hidden')) {
            closeMenu();
        }
    });

    // ===================================
    // LÓGICA DO CARRINHO AJAX
    // ===================================
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const button = document.getElementById('add-to-cart-button');
            const originalButtonText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = 'Adicionando...';
            
            const formData = new FormData(addToCartForm);
            
            fetch('adicionar_carrinho.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza contador do carrinho no desktop
                    const cartCountSpan = document.getElementById('cart-count');
                    if (cartCountSpan) {
                        cartCountSpan.textContent = data.cart_count;
                        cartCountSpan.classList.remove('hidden');
                    }
                    
                    // Atualiza contador do carrinho no mobile
                    const cartCountMobile = document.getElementById('cart-count-mobile');
                    if (cartCountMobile) {
                        cartCountMobile.textContent = data.cart_count;
                        cartCountMobile.classList.remove('hidden');
                    }
                    
                    // Mostra notificação de sucesso
                    showToast(`${data.produto_nome} adicionado ao carrinho!`, 'success');
                    
                    button.innerHTML = '✓ Adicionado!';
                    button.classList.add('bg-green-600');
                    button.classList.remove('bg-brand-red');
                    
                    setTimeout(() => {
                        button.innerHTML = originalButtonText;
                        button.disabled = false;
                        button.classList.remove('bg-green-600');
                        button.classList.add('bg-brand-red');
                    }, 2000);
                } else {
                    showToast(data.message || 'Erro ao adicionar produto', 'error');
                    button.innerHTML = originalButtonText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                showToast('Erro de conexão. Tente novamente.', 'error');
                button.innerHTML = originalButtonText;
                button.disabled = false;
            });
        });
    }

    // ===================================
    // LÓGICA DAS ANIMAÇÕES DE ROLAGEM
    // ===================================
    if (typeof ScrollReveal !== 'undefined') {
        const sr = ScrollReveal({
            distance: '50px',
            duration: 1000,
            easing: 'ease-in-out',
            reset: false
        });
        sr.reveal('.main-banner-carousel', { origin: 'top' });
        sr.reveal('.flex.gap-4.overflow-x-auto', { origin: 'bottom', delay: 200 });
        sr.reveal('.scroll-reveal-section', { origin: 'bottom', interval: 200 });
    }

    // ===================================
    // LAZY LOADING PARA IMAGENS
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
    // INICIALIZAÇÃO DE TODOS OS CARROSSÉIS (SWIPER.JS)
    // ===================================
    
    // Inicialização do Carrossel de Banners Principais
    const mainBanner = new Swiper('.main-banner-carousel', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    });

    // Inicialização dos Carrosséis de Produtos
    const productCarousels = new Swiper('.product-carousel', {
        loop: false,
        slidesPerView: 1.5,
        spaceBetween: 12,
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        breakpoints: { 
            480: { slidesPerView: 2, spaceBetween: 16 },
            640: { slidesPerView: 2.5, spaceBetween: 16 }, 
            768: { slidesPerView: 3, spaceBetween: 20 },
            1024: { slidesPerView: 4, spaceBetween: 24 },
            1280: { slidesPerView: 5, spaceBetween: 24 }
        }
    });

    // Inicialização do Carrossel de Depoimentos
    const testimonialCarousel = new Swiper('.testimonial-carousel', {
        loop: true,
        autoplay: {
            delay: 6000,
            disableOnInteraction: false,
        },
        slidesPerView: 1,
        spaceBetween: 20,
        navigation: {
            nextEl: '.testimonial-next-btn',
            prevEl: '.testimonial-prev-btn',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
                spaceBetween: 24,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
            1280: {
                slidesPerView: 4,
                spaceBetween: 30,
            }
        }
    });

});