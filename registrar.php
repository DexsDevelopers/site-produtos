<?php
// registrar.php

session_start();
require_once 'config.php';
require_once 'templates/header.php';
?>

<!-- CSS Específico da Página de Registro -->
<style>
.registro-container {
    background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
    min-height: 100vh;
    padding: 120px 0 60px;
    position: relative;
    overflow: hidden;
}

.registro-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 30%, rgba(255, 0, 0, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 0, 0, 0.05) 0%, transparent 50%);
    animation: backgroundPulse 8s ease-in-out infinite;
    pointer-events: none;
}

.registro-container h1 {
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
    animation: glow 2s ease-in-out infinite alternate;
}

.registro-form {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 0, 0, 0.2);
    box-shadow: 0 8px 32px rgba(255, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.registro-form::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 0, 0, 0.1), transparent);
    transition: left 0.6s;
}

.registro-form:hover::before {
    left: 100%;
}

.btn-registro {
    background: linear-gradient(45deg, #ff0000, #ff3333);
    color: white;
    padding: 15px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
    border: none;
    cursor: pointer;
    width: 100%;
}

.btn-registro::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-registro:hover::before {
    left: 100%;
}

.btn-registro:hover {
    background: linear-gradient(45deg, #ff3333, #ff0000);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.5);
}
</style>

<div class="registro-container">

<div class="w-full max-w-2xl mx-auto py-24 px-4">
    <div class="pt-16 text-center">
        <h1 class="text-4xl md:text-5xl font-black text-white">Crie sua Conta</h1>
        <p class="mt-4 text-lg text-brand-gray-text">Junte-se à nossa comunidade e aproveite ofertas exclusivas.</p>
    </div>

    <div class="mt-10 registro-form p-8 rounded-xl">
        <?php
        // Exibe mensagens de erro ou sucesso que podem ter sido definidas no processamento
        if (isset($_SESSION['error_message'])) {
            echo '<div class="bg-red-500/20 text-red-300 p-4 rounded-lg mb-6 text-center">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']); // Limpa a mensagem para não aparecer novamente
        }
        ?>
        <form action="processa_registro.php" method="POST">
            <div class="space-y-6">
                <div>
                    <label for="nome" class="block text-sm font-medium text-brand-gray-text">Nome Completo</label>
                    <input type="text" id="nome" name="nome" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray-light text-white focus:ring-brand-red focus:border-brand-red">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-brand-gray-text">E-mail</label>
                    <input type="email" id="email" name="email" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray-light text-white focus:ring-brand-red focus:border-brand-red">
                </div>
                <div>
                    <label for="senha" class="block text-sm font-medium text-brand-gray-text">Senha</label>
                    <input type="password" id="senha" name="senha" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray-light text-white focus:ring-brand-red focus:border-brand-red">
                </div>
                <div>
                    <label for="confirmar_senha" class="block text-sm font-medium text-brand-gray-text">Confirme sua Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray-light text-white focus:ring-brand-red focus:border-brand-red">
                </div>
            </div>
            <button type="submit" class="btn-registro mt-8">
                Criar Conta
            </button>
            <p class="text-center mt-4 text-sm text-brand-gray-text">
                Já tem uma conta? <a href="login.php" class="font-semibold text-brand-red hover:underline">Faça Login</a>
            </p>
        </form>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>