<?php
// registrar.php — MACARIO BRAZIL
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: minha_conta.php');
    exit;
}

$page_title = 'Criar Conta';
require_once 'templates/header.php';
?>

<div class="container"
    style="padding-top: 60px; min-height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">

    <div style="width: 100%; max-width: 440px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="font-size: 2.5rem; margin-bottom: 12px;">Criar Conta</h1>
            <p style="color: var(--text-muted);">Junte-se a nós e tenha acesso exclusivo.</p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div
            style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #EF4444; padding: 16px; border-radius: var(--radius-md); margin-bottom: 24px; text-align: center;">
            <?= $_SESSION['error_message']?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
        <?php
endif; ?>

        <div
            style="background: var(--bg-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
            <form action="processa_registro.php" method="POST">

                <div style="margin-bottom: 20px;">
                    <label
                        style="display: block; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; color: var(--text-muted);">Nome
                        Completo</label>
                    <input type="text" name="nome" required
                        style="width: 100%; padding: 14px 16px; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); outline: none; font-size: 0.95rem; transition: border-color 0.2s;"
                        onfocus="this.style.borderColor='var(--border-active)'"
                        onblur="this.style.borderColor='var(--border-color)'">
                </div>

                <div style="margin-bottom: 20px;">
                    <label
                        style="display: block; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; color: var(--text-muted);">E-mail</label>
                    <input type="email" name="email" required
                        style="width: 100%; padding: 14px 16px; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); outline: none; font-size: 0.95rem; transition: border-color 0.2s;"
                        onfocus="this.style.borderColor='var(--border-active)'"
                        onblur="this.style.borderColor='var(--border-color)'">
                </div>

                <div style="margin-bottom: 20px;">
                    <label
                        style="display: block; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; color: var(--text-muted);">Senha</label>
                    <input type="password" name="senha" required
                        style="width: 100%; padding: 14px 16px; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); outline: none; font-size: 0.95rem; transition: border-color 0.2s;"
                        onfocus="this.style.borderColor='var(--border-active)'"
                        onblur="this.style.borderColor='var(--border-color)'">
                </div>

                <div style="margin-bottom: 24px;">
                    <label
                        style="display: block; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; color: var(--text-muted);">Confirmar
                        Senha</label>
                    <input type="password" name="confirmar_senha" required
                        style="width: 100%; padding: 14px 16px; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); outline: none; font-size: 0.95rem; transition: border-color 0.2s;"
                        onfocus="this.style.borderColor='var(--border-active)'"
                        onblur="this.style.borderColor='var(--border-color)'">
                </div>

                <button type="submit" class="btn-primary"
                    style="width: 100%; padding: 16px; border-radius: var(--radius-md); font-weight: 700; text-transform: uppercase; cursor: pointer;">
                    Criar Conta
                </button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 32px; color: var(--text-secondary);">
            Já tem uma conta? <a href="login.php"
                style="color: var(--text-primary); font-weight: 600; margin-left: 4px;">Fazer Login</a>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>