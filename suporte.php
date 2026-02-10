<?php
// suporte.php — MACARIO BRAZIL
session_start();
require_once 'config.php';

$page_title = 'Suporte';
require_once 'templates/header.php';
?>

<div class="container" style="padding-top: 60px; min-height: 80vh;">

    <div style="text-align: center; margin-bottom: 60px;">
        <h1 style="font-size: clamp(2.5rem, 5vw, 4rem); margin-bottom: 16px;">Suporte 24/7</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
            Nossa equipe está pronta para ajudar você com qualquer dúvida ou solicitação.
        </p>
    </div>

    <!-- Cards de Contato -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px; margin-bottom: 80px;">

        <div
            style="background: var(--bg-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); text-align: center;">
            <div
                style="width: 64px; height: 64px; background: var(--bg-tertiary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: var(--text-primary); font-size: 1.5rem;">
                <i class="fab fa-whatsapp"></i>
            </div>
            <h3 style="margin-bottom: 12px; font-size: 1.2rem;">WhatsApp</h3>
            <p style="color: var(--text-muted); margin-bottom: 24px;">Atendimento rápido e direto.</p>
            <a href="https://wa.me/5551996148568" target="_blank" class="btn-primary"
                style="display: inline-block; padding: 12px 32px; text-decoration: none;">Iniciar Chat</a>
        </div>

        <div
            style="background: var(--bg-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); text-align: center;">
            <div
                style="width: 64px; height: 64px; background: var(--bg-tertiary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: var(--text-primary); font-size: 1.5rem;">
                <i class="fas fa-envelope"></i>
            </div>
            <h3 style="margin-bottom: 12px; font-size: 1.2rem;">E-mail</h3>
            <p style="color: var(--text-muted); margin-bottom: 24px;">empresatokio@gmail.com</p>
            <a href="mailto:empresatokio@gmail.com" class="btn-secondary"
                style="display: inline-block; padding: 12px 32px; text-decoration: none; border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary);">Enviar
                E-mail</a>
        </div>

        <div
            style="background: var(--bg-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); text-align: center;">
            <div
                style="width: 64px; height: 64px; background: var(--bg-tertiary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: var(--text-primary); font-size: 1.5rem;">
                <i class="fas fa-phone"></i>
            </div>
            <h3 style="margin-bottom: 12px; font-size: 1.2rem;">Telefone</h3>
            <p style="color: var(--text-muted); margin-bottom: 24px;">(51) 99614-8568</p>
            <a href="tel:+5551996148568" class="btn-secondary"
                style="display: inline-block; padding: 12px 32px; text-decoration: none; border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary);">Ligar
                Agora</a>
        </div>

    </div>

    <!-- FAQ -->
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="text-align: center; margin-bottom: 40px; font-size: 2rem;">Perguntas Frequentes</h2>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php
$faqs = [
    ['P' => 'Como recebo meus produtos digitais?', 'R' => 'Após a confirmação do pagamento, você receberá os dados de acesso ou o código do produto diretamente no seu e-mail e WhatsApp.'],
    ['P' => 'Quais as formas de pagamento?', 'R' => 'Aceitamos PIX com aprovação imediata e cartões de crédito via InfinitePay.'],
    ['P' => 'E se eu tiver problemas com o acesso?', 'R' => 'Nossa equipe de suporte está disponível 24/7 para resolver qualquer problema. Basta nos chamar no WhatsApp.'],
    ['P' => 'Os produtos têm garantia?', 'R' => 'Sim, todos os nossos produtos possuem garantia de funcionamento total durante o período contratado.']
];
foreach ($faqs as $faq):
?>
            <div
                style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
                <h4 style="margin-bottom: 8px; font-size: 1.1rem; color: var(--text-primary);">
                    <?= $faq['P']?>
                </h4>
                <p style="color: var(--text-secondary); line-height: 1.6;">
                    <?= $faq['R']?>
                </p>
            </div>
            <?php
endforeach; ?>
        </div>
    </div>

</div>

<?php require_once 'templates/footer.php'; ?>