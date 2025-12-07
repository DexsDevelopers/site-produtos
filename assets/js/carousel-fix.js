// assets/js/carousel-fix.js - Correção para Carrosséis de Produtos
document.addEventListener('DOMContentLoaded', function() {
    
    // ===================================
    // CORREÇÃO PARA CARROSSÉIS DE PRODUTOS
    // ===================================
    
    function initializeProductCarousels() {
        const productCarousels = document.querySelectorAll('.product-carousel');
        
        productCarousels.forEach((carousel, index) => {
            const slides = carousel.querySelectorAll('.swiper-slide');
            const slidesCount = slides.length;
            
            // Configurações baseadas no número de slides
            let config = {
                slidesPerView: 1.5,
                spaceBetween: 16,
                loop: false, // Desabilita loop por padrão
                autoplay: false, // Desabilita autoplay por padrão
                breakpoints: {
                    480: { slidesPerView: 2, spaceBetween: 16 },
                    640: { slidesPerView: 2.5, spaceBetween: 20 },
                    768: { slidesPerView: 3, spaceBetween: 20 },
                    1024: { slidesPerView: 4, spaceBetween: 24 },
                    1280: { slidesPerView: 5, spaceBetween: 24 },
                    1440: { slidesPerView: 6, spaceBetween: 28 },
                    1920: { slidesPerView: 7, spaceBetween: 32 }
                },
                navigation: {
                    nextEl: carousel.querySelector('.swiper-button-next'),
                    prevEl: carousel.querySelector('.swiper-button-prev'),
                },
                // Configurações para estabilidade
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                preventClicks: false,
                preventClicksPropagation: false,
                // Configurações de paginação
                pagination: {
                    el: carousel.querySelector('.swiper-pagination'),
                    clickable: true,
                    dynamicBullets: true,
                },
                // Configurações de responsividade
                on: {
                    init: function() {
                        console.log('Carrossel de produtos inicializado:', index);
                        updateNavigationVisibility(this);
                    },
                    slideChange: function() {
                        updateNavigationVisibility(this);
                    },
                    resize: function() {
                        updateNavigationVisibility(this);
                    }
                }
            };
            
            // Ativa loop e autoplay apenas se houver slides suficientes
            if (slidesCount > 6) {
                config.loop = true;
                config.autoplay = {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                };
                config.loopAdditionalSlides = 2;
                config.loopedSlides = 2;
            }
            
            // Inicializa o Swiper
            const swiper = new Swiper(carousel, config);
            
            // Função para atualizar visibilidade da navegação
            function updateNavigationVisibility(swiperInstance) {
                const nextBtn = carousel.querySelector('.swiper-button-next');
                const prevBtn = carousel.querySelector('.swiper-button-prev');
                
                if (nextBtn && prevBtn) {
                    // Mostra/esconde botões baseado na posição
                    if (swiperInstance.isEnd) {
                        nextBtn.style.opacity = '0.3';
                        nextBtn.style.pointerEvents = 'none';
                    } else {
                        nextBtn.style.opacity = '1';
                        nextBtn.style.pointerEvents = 'auto';
                    }
                    
                    if (swiperInstance.isBeginning) {
                        prevBtn.style.opacity = '0.3';
                        prevBtn.style.pointerEvents = 'none';
                    } else {
                        prevBtn.style.opacity = '1';
                        prevBtn.style.pointerEvents = 'auto';
                    }
                }
            }
        });
    }
    
    // ===================================
    // CORREÇÃO PARA BANNER PRINCIPAL
    // ===================================
    
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
                    dynamicBullets: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                on: {
                    init: function() {
                        console.log('Banner principal inicializado');
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
    
    // ===================================
    // CORREÇÃO PARA DEPOIMENTOS
    // ===================================
    
    function initializeTestimonialCarousel() {
        const testimonialCarousel = document.querySelector('.testimonial-carousel');
        
        if (testimonialCarousel) {
            const slides = testimonialCarousel.querySelectorAll('.swiper-slide');
            const slidesCount = slides.length;
            
            let config = {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: slidesCount > 3,
                autoplay: slidesCount > 3 ? {
                    delay: 6000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                } : false,
                breakpoints: {
                    640: { slidesPerView: 2 },
                    1024: { slidesPerView: 3 },
                    1280: { slidesPerView: 4 }
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                on: {
                    init: function() {
                        console.log('Carrossel de depoimentos inicializado');
                    }
                }
            };
            
            if (slidesCount > 3) {
                config.loopAdditionalSlides = 1;
                config.loopedSlides = 1;
            }
            
            new Swiper(testimonialCarousel, config);
        }
    }
    
    // ===================================
    // INICIALIZAÇÃO
    // ===================================
    
    // Aguarda o Swiper estar disponível
    function waitForSwiper() {
        if (typeof Swiper !== 'undefined') {
            initializeProductCarousels();
            initializeMainBanner();
            initializeTestimonialCarousel();
        } else {
            setTimeout(waitForSwiper, 100);
        }
    }
    
    waitForSwiper();
    
    // ===================================
    // CORREÇÃO PARA REDIMENSIONAMENTO
    // ===================================
    
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // Recalcula carrosséis após redimensionamento
            const swipers = document.querySelectorAll('.swiper');
            swipers.forEach(swiper => {
                if (swiper.swiper) {
                    swiper.swiper.update();
                }
            });
        }, 250);
    });
    
    // ===================================
    // DEBUG E MONITORAMENTO
    // ===================================
    
    // Adiciona logs para debug
    console.log('Carousel Fix carregado');
    
    // Monitora erros do Swiper
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes('swiper')) {
            console.error('Erro do Swiper:', e.message);
        }
    });
});
