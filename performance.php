<?php
// performance.php - Página de Performance Otimizada
session_start();
require_once 'config.php';

$page_title = 'Performance Otimizada';
$page_description = 'Nossa plataforma foi desenvolvida com foco em velocidade e performance, garantindo uma experiência fluida para todos os usuários.';
$page_keywords = 'performance, velocidade, otimização, plataforma rápida';

require_once 'templates/header.php';
?>

<style>
.performance-hero {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    padding: 140px 0 80px;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.performance-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 50%, rgba(255, 0, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 0, 0, 0.1) 0%, transparent 50%);
    animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
}

.performance-hero h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from { text-shadow: 0 0 20px rgba(255, 0, 0, 0.3); }
    to { text-shadow: 0 0 30px rgba(255, 0, 0, 0.6), 0 0 40px rgba(255, 0, 0, 0.3); }
}

.features-section {
    padding: 80px 0;
    background: #000000;
}

.feature-card {
    background: linear-gradient(145deg, #1a0000, #000000);
    border-radius: 15px;
    padding: 2.5rem;
    border: 1px solid rgba(255, 0, 0, 0.2);
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(255, 0, 0, 0.2);
    border-color: rgba(255, 0, 0, 0.4);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(45deg, #ff0000, #ff3333);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    font-size: 2rem;
    color: white;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
}

@media (max-width: 768px) {
    .performance-hero h1 {
        font-size: 2.5rem;
    }
}
</style>

<section class="performance-hero">
    <div class="container">
        <div class="flex items-center justify-center mb-6">
            <div class="feature-icon">
                <i class="fas fa-rocket"></i>
            </div>
        </div>
        <h1>Performance Otimizada</h1>
        <p class="text-xl opacity-90">Velocidade e eficiência em primeiro lugar</p>
    </div>
</section>

<section class="features-section">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Carregamento Ultra-Rápido</h3>
                <p class="text-gray-300 leading-relaxed">
                    Nossa plataforma foi otimizada para carregar em menos de 2 segundos, garantindo que seus clientes tenham acesso imediato aos produtos e informações.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-server"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Infraestrutura Robusta</h3>
                <p class="text-gray-300 leading-relaxed">
                    Utilizamos servidores de alta performance com CDN global, garantindo que sua loja esteja sempre disponível, mesmo durante picos de tráfego.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Otimizado para Mobile</h3>
                <p class="text-gray-300 leading-relaxed">
                    Interface totalmente responsiva que se adapta perfeitamente a qualquer dispositivo, proporcionando a mesma velocidade em smartphones e tablets.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-compress-arrows-alt"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Compressão Inteligente</h3>
                <p class="text-gray-300 leading-relaxed">
                    Imagens e recursos são automaticamente comprimidos sem perder qualidade, reduzindo o tempo de carregamento e economizando dados dos usuários.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Cache Avançado</h3>
                <p class="text-gray-300 leading-relaxed">
                    Sistema de cache inteligente que armazena conteúdo frequentemente acessado, proporcionando respostas instantâneas e reduzindo carga no servidor.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Monitoramento em Tempo Real</h3>
                <p class="text-gray-300 leading-relaxed">
                    Acompanhamento constante da performance da plataforma, com alertas automáticos para garantir que tudo funcione perfeitamente.
                </p>
            </div>
        </div>

        <div class="text-center mt-12">
            <a href="index.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-red-600 to-red-500 text-white font-bold py-3 px-8 rounded-full hover:from-red-500 hover:to-red-600 transition-all shadow-lg hover:shadow-xl">
                <i class="fas fa-arrow-left"></i>
                Voltar para Início
            </a>
        </div>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?>

