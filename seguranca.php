<?php
// seguranca.php - Página de Segurança Garantida
session_start();
require_once 'config.php';

$page_title = 'Segurança Garantida';
$page_description = 'Proteção total dos seus dados com criptografia avançada e protocolos de segurança de última geração.';
$page_keywords = 'segurança, proteção, criptografia, dados seguros, privacidade';

require_once 'templates/header.php';
?>

<style>
.security-hero {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    padding: 140px 0 80px;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.security-hero::before {
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

.security-hero h1 {
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

.contact-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.contact-card {
    background: linear-gradient(145deg, #1a0000, #000000);
    border-radius: 15px;
    padding: 2rem;
    border: 1px solid rgba(255, 0, 0, 0.2);
    text-align: center;
    transition: all 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-5px);
    border-color: rgba(255, 0, 0, 0.4);
}

@media (max-width: 768px) {
    .security-hero h1 {
        font-size: 2.5rem;
    }
}
</style>

<section class="security-hero">
    <div class="container">
        <div class="flex items-center justify-center mb-6">
            <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
        <h1>Segurança Garantida</h1>
        <p class="text-xl opacity-90">Proteção total dos seus dados e transações</p>
    </div>
</section>

<section class="features-section">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Criptografia SSL/TLS</h3>
                <p class="text-gray-300 leading-relaxed">
                    Todas as comunicações são protegidas com criptografia de ponta a ponta, garantindo que seus dados estejam sempre seguros durante transmissão.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Proteção de Dados</h3>
                <p class="text-gray-300 leading-relaxed">
                    Seus dados pessoais e informações de pagamento são armazenados com os mais altos padrões de segurança, seguindo as melhores práticas da indústria.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-firewall"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Firewall Avançado</h3>
                <p class="text-gray-300 leading-relaxed">
                    Sistema de firewall multicamadas que bloqueia tentativas de acesso não autorizado e protege contra ataques DDoS e outras ameaças.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-virus"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Proteção Antivírus</h3>
                <p class="text-gray-300 leading-relaxed">
                    Varredura contínua de arquivos e uploads para detectar e bloquear qualquer tipo de malware ou código malicioso.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Autenticação Segura</h3>
                <p class="text-gray-300 leading-relaxed">
                    Sistema de autenticação robusto com hash de senhas e proteção contra força bruta, garantindo que apenas você acesse sua conta.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Certificados de Segurança</h3>
                <p class="text-gray-300 leading-relaxed">
                    Plataforma certificada e em conformidade com os principais padrões de segurança internacionais, incluindo LGPD e GDPR.
                </p>
            </div>
        </div>

        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-white mb-4">Entre em Contato</h2>
            <p class="text-gray-300 text-lg">Escolha a forma de contato que preferir</p>
        </div>

        <div class="contact-methods">
            <div class="contact-card">
                <div class="feature-icon mx-auto mb-4">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">E-mail</h3>
                <p class="text-gray-300 mb-4">Envie sua dúvida por e-mail</p>
                <a href="mailto:empresatokio@gmail.com" class="text-red-400 hover:text-red-300 font-semibold">
                    empresatokio@gmail.com
                </a>
            </div>

            <div class="contact-card">
                <div class="feature-icon mx-auto mb-4">
                    <i class="fas fa-phone"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Telefone</h3>
                <p class="text-gray-300 mb-4">Ligue para nossa central</p>
                <a href="tel:+5551996148568" class="text-red-400 hover:text-red-300 font-semibold">
                    (51) 99614-8568
                </a>
            </div>

            <div class="contact-card">
                <div class="feature-icon mx-auto mb-4">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">WhatsApp</h3>
                <p class="text-gray-300 mb-4">Chat direto pelo WhatsApp</p>
                <a href="https://wa.me/5551996148568" target="_blank" class="text-red-400 hover:text-red-300 font-semibold">
                    Falar no WhatsApp
                </a>
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


session_start();
require_once 'config.php';

$page_title = 'Segurança Garantida';
$page_description = 'Proteção total dos seus dados com criptografia avançada e protocolos de segurança de última geração.';
$page_keywords = 'segurança, proteção, criptografia, dados seguros, privacidade';

require_once 'templates/header.php';
?>

<style>
.security-hero {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    padding: 140px 0 80px;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.security-hero::before {
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

.security-hero h1 {
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

.contact-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.contact-card {
    background: linear-gradient(145deg, #1a0000, #000000);
    border-radius: 15px;
    padding: 2rem;
    border: 1px solid rgba(255, 0, 0, 0.2);
    text-align: center;
    transition: all 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-5px);
    border-color: rgba(255, 0, 0, 0.4);
}

@media (max-width: 768px) {
    .security-hero h1 {
        font-size: 2.5rem;
    }
}
</style>

<section class="security-hero">
    <div class="container">
        <div class="flex items-center justify-center mb-6">
            <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
        <h1>Segurança Garantida</h1>
        <p class="text-xl opacity-90">Proteção total dos seus dados e transações</p>
    </div>
</section>

<section class="features-section">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Criptografia SSL/TLS</h3>
                <p class="text-gray-300 leading-relaxed">
                    Todas as comunicações são protegidas com criptografia de ponta a ponta, garantindo que seus dados estejam sempre seguros durante transmissão.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Proteção de Dados</h3>
                <p class="text-gray-300 leading-relaxed">
                    Seus dados pessoais e informações de pagamento são armazenados com os mais altos padrões de segurança, seguindo as melhores práticas da indústria.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-firewall"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Firewall Avançado</h3>
                <p class="text-gray-300 leading-relaxed">
                    Sistema de firewall multicamadas que bloqueia tentativas de acesso não autorizado e protege contra ataques DDoS e outras ameaças.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-virus"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Proteção Antivírus</h3>
                <p class="text-gray-300 leading-relaxed">
                    Varredura contínua de arquivos e uploads para detectar e bloquear qualquer tipo de malware ou código malicioso.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Autenticação Segura</h3>
                <p class="text-gray-300 leading-relaxed">
                    Sistema de autenticação robusto com hash de senhas e proteção contra força bruta, garantindo que apenas você acesse sua conta.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Certificados de Segurança</h3>
                <p class="text-gray-300 leading-relaxed">
                    Plataforma certificada e em conformidade com os principais padrões de segurança internacionais, incluindo LGPD e GDPR.
                </p>
            </div>
        </div>

        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-white mb-4">Entre em Contato</h2>
            <p class="text-gray-300 text-lg">Escolha a forma de contato que preferir</p>
        </div>

        <div class="contact-methods">
            <div class="contact-card">
                <div class="feature-icon mx-auto mb-4">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">E-mail</h3>
                <p class="text-gray-300 mb-4">Envie sua dúvida por e-mail</p>
                <a href="mailto:empresatokio@gmail.com" class="text-red-400 hover:text-red-300 font-semibold">
                    empresatokio@gmail.com
                </a>
            </div>

            <div class="contact-card">
                <div class="feature-icon mx-auto mb-4">
                    <i class="fas fa-phone"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Telefone</h3>
                <p class="text-gray-300 mb-4">Ligue para nossa central</p>
                <a href="tel:+5551996148568" class="text-red-400 hover:text-red-300 font-semibold">
                    (51) 99614-8568
                </a>
            </div>

            <div class="contact-card">
                <div class="feature-icon mx-auto mb-4">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">WhatsApp</h3>
                <p class="text-gray-300 mb-4">Chat direto pelo WhatsApp</p>
                <a href="https://wa.me/5551996148568" target="_blank" class="text-red-400 hover:text-red-300 font-semibold">
                    Falar no WhatsApp
                </a>
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
