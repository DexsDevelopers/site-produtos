<?php
// demo_otimizado.php - Demonstração da Versão Otimizada
session_start();
require_once 'config_otimizado.php';
require_once 'templates/header.php';
?>

<!-- Página de Demonstração Otimizada -->
<div class="min-h-screen bg-gradient-to-br from-brand-black via-brand-gray-900 to-brand-black">
    
    <!-- Hero Section Otimizado -->
    <section class="relative py-12 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-black mb-6 gradient-title" data-aos="fade-up">
                Site Otimizado
                <span class="block text-transparent bg-clip-text bg-gradient-to-r from-brand-red to-brand-pink">
                    Ultra Rápido
                </span>
            </h1>
            <p class="text-xl text-brand-gray-300 mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                Versão otimizada para máxima performance e experiência do usuário
            </p>
            
            <!-- Banner Principal - Formato Instagram -->
            <div class="swiper instagram-banner rounded-xl overflow-hidden shadow-xl max-w-md mx-auto mb-12" data-aos="fade-up" data-aos-delay="400">
                <div class="swiper-wrapper">
                    <div class="swiper-slide relative aspect-square bg-gradient-to-br from-brand-red/20 to-brand-purple/20 flex items-center justify-center">
                        <div class="text-center text-white">
                            <div class="w-24 h-24 bg-gradient-to-r from-brand-red to-brand-pink rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-rocket text-3xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold mb-2">Performance</h3>
                            <p class="text-sm opacity-90">Carregamento ultra rápido</p>
                        </div>
                    </div>
                    <div class="swiper-slide relative aspect-square bg-gradient-to-br from-brand-purple/20 to-brand-blue/20 flex items-center justify-center">
                        <div class="text-center text-white">
                            <div class="w-24 h-24 bg-gradient-to-r from-brand-purple to-brand-blue rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-mobile-alt text-3xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold mb-2">Responsivo</h3>
                            <p class="text-sm opacity-90">Perfeito em todos os dispositivos</p>
                        </div>
                    </div>
                    <div class="swiper-slide relative aspect-square bg-gradient-to-br from-brand-blue/20 to-brand-cyan/20 flex items-center justify-center">
                        <div class="text-center text-white">
                            <div class="w-24 h-24 bg-gradient-to-r from-brand-blue to-brand-cyan rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-heart text-3xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold mb-2">UX</h3>
                            <p class="text-sm opacity-90">Experiência excepcional</p>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>
    
    <!-- Seção de Melhorias -->
    <section class="py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-4xl font-black text-center mb-12 gradient-title" data-aos="fade-up">
                Otimizações Implementadas
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card 1: Performance -->
                <div class="glass-card rounded-xl p-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 bg-gradient-to-r from-brand-red to-brand-pink rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-tachometer-alt text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Performance</h3>
                    <ul class="text-sm text-brand-gray-300 space-y-2">
                        <li>• Bibliotecas otimizadas</li>
                        <li>• JavaScript minificado</li>
                        <li>• Lazy loading eficiente</li>
                        <li>• Cache inteligente</li>
                    </ul>
                </div>
                
                <!-- Card 2: Design -->
                <div class="glass-card rounded-xl p-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 bg-gradient-to-r from-brand-purple to-brand-blue rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-palette text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Design</h3>
                    <ul class="text-sm text-brand-gray-300 space-y-2">
                        <li>• Banner formato Instagram</li>
                        <li>• Animações suaves</li>
                        <li>• Glassmorphism moderno</li>
                        <li>• Gradientes otimizados</li>
                    </ul>
                </div>
                
                <!-- Card 3: Mobile -->
                <div class="glass-card rounded-xl p-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-12 h-12 bg-gradient-to-r from-brand-blue to-brand-cyan rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-mobile-alt text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Mobile First</h3>
                    <ul class="text-sm text-brand-gray-300 space-y-2">
                        <li>• Design responsivo</li>
                        <li>• Touch friendly</li>
                        <li>• Carregamento rápido</li>
                        <li>• UX otimizada</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Seção de Comparação -->
    <section class="py-12 px-4 bg-gradient-to-r from-brand-gray-900/50 to-brand-black/50">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-4xl font-black text-center mb-12 gradient-title" data-aos="fade-up">
                Antes vs Depois
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Antes -->
                <div class="glass-card rounded-xl p-6" data-aos="fade-right">
                    <h3 class="text-2xl font-bold text-red-400 mb-4">❌ Antes</h3>
                    <ul class="text-brand-gray-300 space-y-3">
                        <li class="flex items-center gap-3">
                            <i class="fas fa-times text-red-400"></i>
                            <span>Múltiplas bibliotecas pesadas</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-times text-red-400"></i>
                            <span>Animações complexas desnecessárias</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-times text-red-400"></i>
                            <span>Banner retangular grande</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-times text-red-400"></i>
                            <span>Carregamento lento</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Depois -->
                <div class="glass-card rounded-xl p-6" data-aos="fade-left">
                    <h3 class="text-2xl font-bold text-green-400 mb-4">✅ Depois</h3>
                    <ul class="text-brand-gray-300 space-y-3">
                        <li class="flex items-center gap-3">
                            <i class="fas fa-check text-green-400"></i>
                            <span>Bibliotecas essenciais apenas</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-check text-green-400"></i>
                            <span>Animações otimizadas</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-check text-green-400"></i>
                            <span>Banner quadrado Instagram</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-check text-green-400"></i>
                            <span>Carregamento ultra rápido</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section class="py-12 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-black mb-8 gradient-title" data-aos="fade-up">
                Pronto para Experimentar?
            </h2>
            <p class="text-xl text-brand-gray-300 mb-8" data-aos="fade-up" data-aos-delay="200">
                A versão otimizada está pronta para uso
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center" data-aos="fade-up" data-aos-delay="400">
                <a href="index.php" class="btn-modern text-lg px-8 py-4 inline-flex items-center gap-3">
                    <i class="fas fa-home"></i>
                    Ver Loja Otimizada
                </a>
                <a href="busca.php" class="glass-card text-lg px-8 py-4 inline-flex items-center gap-3 hover:bg-brand-gray-700 transition-all duration-300">
                    <i class="fas fa-search"></i>
                    Buscar Produtos
                </a>
            </div>
        </div>
    </section>
</div>

<script>
// Inicializa o carrossel Instagram
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.instagram-banner')) {
        new Swiper('.instagram-banner', {
            loop: true,
            autoplay: {
                delay: 3000,
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
        });
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
