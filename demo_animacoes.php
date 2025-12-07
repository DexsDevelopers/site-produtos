<?php
// demo_animacoes.php - Demonstração das Animações Modernas
session_start();
require_once 'config.php';
require_once 'templates/header.php';
?>

<!-- Página de Demonstração das Animações -->
<div class="min-h-screen bg-gradient-to-br from-brand-black via-brand-gray-900 to-brand-black">
    
    <!-- Hero Section com Partículas -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Partículas de Fundo -->
        <div class="particles absolute inset-0 z-0">
            <?php for($i = 0; $i < 100; $i++): ?>
            <div class="particle" style="
                left: <?= rand(0, 100) ?>%; 
                width: <?= rand(2, 8) ?>px; 
                height: <?= rand(2, 8) ?>px;
                animation-delay: <?= rand(0, 20) ?>s;
                animation-duration: <?= rand(15, 30) ?>s;
            "></div>
            <?php endfor; ?>
        </div>
        
        <div class="relative z-10 text-center px-4">
            <h1 class="text-8xl md:text-9xl font-black font-display mb-8 gradient-title" data-aos="fade-up">
                Animações
                <span class="block text-transparent bg-clip-text bg-gradient-to-r from-brand-red via-brand-pink to-brand-purple">
                    Profissionais
                </span>
            </h1>
            <p class="text-2xl text-brand-gray-300 mb-12 max-w-4xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                Explore nossa coleção de efeitos visuais modernos e animações fluidas
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center" data-aos="fade-up" data-aos-delay="400">
                <button class="btn-modern text-xl px-10 py-5 inline-flex items-center gap-3">
                    <i class="fas fa-play"></i>
                    Ver Demonstração
                </button>
                <button class="glass-card text-xl px-10 py-5 inline-flex items-center gap-3 hover:bg-brand-gray-700 transition-all duration-300">
                    <i class="fas fa-code"></i>
                    Ver Código
                </button>
            </div>
        </div>
    </section>
    
    <!-- Seção de Cards Animados -->
    <section class="py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-5xl font-black font-display text-center mb-16 gradient-title" data-aos="fade-up">
                Cards Interativos
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Card 1: Hover Scale -->
                <div class="glass-card rounded-2xl p-8 group hover:scale-105 transition-all duration-500 hover:shadow-2xl hover:shadow-brand-red/20" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-gradient-to-r from-brand-red to-brand-pink rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-rocket text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Hover Scale</h3>
                    <p class="text-brand-gray-300 mb-6">Efeito de escala suave ao passar o mouse sobre o card</p>
                    <button class="btn-modern w-full">Experimentar</button>
                </div>
                
                <!-- Card 2: Rotate -->
                <div class="glass-card rounded-2xl p-8 group hover:rotate-2 transition-all duration-500 hover:shadow-2xl hover:shadow-brand-purple/20" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-gradient-to-r from-brand-purple to-brand-blue rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-magic text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Rotate Effect</h3>
                    <p class="text-brand-gray-300 mb-6">Rotação sutil para criar dinamismo visual</p>
                    <button class="btn-modern w-full">Experimentar</button>
                </div>
                
                <!-- Card 3: Glow -->
                <div class="glass-card rounded-2xl p-8 group hover:shadow-2xl hover:shadow-brand-cyan/30 transition-all duration-500" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-gradient-to-r from-brand-cyan to-brand-blue rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-star text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Glow Effect</h3>
                    <p class="text-brand-gray-300 mb-6">Brilho suave que se intensifica no hover</p>
                    <button class="btn-modern w-full">Experimentar</button>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Seção de Botões Animados -->
    <section class="py-20 px-4 bg-gradient-to-r from-brand-gray-900/50 to-brand-black/50">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-5xl font-black font-display text-center mb-16 gradient-title" data-aos="fade-up">
                Botões Interativos
            </h2>
            
            <div class="flex flex-wrap justify-center gap-8">
                <!-- Botão 1: Pulse -->
                <button class="btn-modern text-lg px-8 py-4 animate-pulse-slow">
                    <i class="fas fa-heart mr-2"></i>
                    Pulse Effect
                </button>
                
                <!-- Botão 2: Bounce -->
                <button class="btn-modern text-lg px-8 py-4 animate-bounce-slow">
                    <i class="fas fa-bounce mr-2"></i>
                    Bounce Effect
                </button>
                
                <!-- Botão 3: Float -->
                <button class="btn-modern text-lg px-8 py-4 animate-float">
                    <i class="fas fa-feather mr-2"></i>
                    Float Effect
                </button>
                
                <!-- Botão 4: Glow -->
                <button class="btn-modern text-lg px-8 py-4 animate-glow">
                    <i class="fas fa-fire mr-2"></i>
                    Glow Effect
                </button>
            </div>
        </div>
    </section>
    
    <!-- Seção de Texto Animado -->
    <section class="py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-6xl font-black font-display mb-8 gradient-title" data-aos="fade-up">
                Texto com Gradiente Animado
            </h2>
            <p class="text-2xl text-brand-gray-300 mb-12" data-aos="fade-up" data-aos-delay="200">
                Cores que fluem e se transformam criando uma experiência visual única
            </p>
            
            <!-- Texto com diferentes animações -->
            <div class="space-y-8">
                <div class="text-4xl font-bold text-white" data-aos="slide-up" data-aos-delay="300">
                    <span class="gradient-title">Deslize para cima</span>
                </div>
                <div class="text-4xl font-bold text-white" data-aos="slide-down" data-aos-delay="400">
                    <span class="gradient-title">Deslize para baixo</span>
                </div>
                <div class="text-4xl font-bold text-white" data-aos="zoom-in" data-aos-delay="500">
                    <span class="gradient-title">Zoom In</span>
                </div>
                <div class="text-4xl font-bold text-white" data-aos="flip-left" data-aos-delay="600">
                    <span class="gradient-title">Flip Left</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Seção de Carrossel -->
    <section class="py-20 px-4 bg-gradient-to-b from-transparent to-brand-gray-900/30">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-5xl font-black font-display text-center mb-16 gradient-title" data-aos="fade-up">
                Carrossel Moderno
            </h2>
            
            <div class="swiper demo-carousel rounded-2xl overflow-hidden shadow-2xl" data-aos="fade-up" data-aos-delay="200">
                <div class="swiper-wrapper">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                    <div class="swiper-slide">
                        <div class="h-96 bg-gradient-to-br from-brand-red/20 to-brand-purple/20 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-24 h-24 bg-gradient-to-r from-brand-red to-brand-pink rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-image text-3xl text-white"></i>
                                </div>
                                <h3 class="text-3xl font-bold text-white mb-4">Slide <?= $i ?></h3>
                                <p class="text-brand-gray-300">Conteúdo do slide com animações suaves</p>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>
    
    <!-- Seção de Loading -->
    <section class="py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-5xl font-black font-display mb-16 gradient-title" data-aos="fade-up">
                Estados de Loading
            </h2>
            
            <div class="flex flex-wrap justify-center gap-8">
                <!-- Spinner 1 -->
                <div class="glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="100">
                    <div class="spinner mx-auto mb-4"></div>
                    <p class="text-white">Spinner Clássico</p>
                </div>
                
                <!-- Spinner 2 -->
                <div class="glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 border-4 border-brand-red/30 border-t-brand-red rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-white">Spinner Moderno</p>
                </div>
                
                <!-- Dots -->
                <div class="glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex space-x-2 justify-center mb-4">
                        <div class="w-3 h-3 bg-brand-red rounded-full animate-bounce"></div>
                        <div class="w-3 h-3 bg-brand-red rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-3 h-3 bg-brand-red rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                    <p class="text-white">Dots Bouncing</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action Final -->
    <section class="py-20 px-4 bg-gradient-to-r from-brand-red/10 to-brand-purple/10">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-6xl font-black font-display mb-8 gradient-title" data-aos="fade-up">
                Pronto para Experimentar?
            </h2>
            <p class="text-2xl text-brand-gray-300 mb-12" data-aos="fade-up" data-aos-delay="200">
                Todas essas animações estão disponíveis em nossa loja
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center" data-aos="fade-up" data-aos-delay="400">
                <a href="index.php" class="btn-modern text-xl px-12 py-6 inline-flex items-center gap-3">
                    <i class="fas fa-home"></i>
                    Voltar à Loja
                </a>
                <a href="busca.php" class="glass-card text-xl px-12 py-6 inline-flex items-center gap-3 hover:bg-brand-gray-700 transition-all duration-300">
                    <i class="fas fa-shopping-bag"></i>
                    Comprar Agora
                </a>
            </div>
        </div>
    </section>
</div>

<script>
// Inicializa o carrossel de demonstração
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.demo-carousel')) {
        new Swiper('.demo-carousel', {
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
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
        });
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
