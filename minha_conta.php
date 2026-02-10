<?php
// minha_conta.php — MACARIO BRAZIL
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT nome, email, data_registro FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    $stmt_pedidos = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC");
    $stmt_pedidos->execute([$user_id]);
    $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

}
catch (PDOException $e) {
    error_log("Erro minha_conta: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

$page_title = 'Minha Conta';
require_once 'templates/header.php';
?>

<div class="container" style="padding-top: 40px; min-height: 80vh;">

    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; margin-bottom: 8px;">Minha Conta</h1>
        <p style="color: var(--text-muted);">Olá,
            <?= htmlspecialchars($usuario['nome'])?>. Gerencie seus pedidos e dados.
        </p>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
    <div
        style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10B981; padding: 16px; border-radius: var(--radius-md); margin-bottom: 24px;">
        <?= $_SESSION['success_message']?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
    <?php
endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px;">

        <!-- Sidebar / Dados -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Dados Pessoais -->
            <div
                style="background: var(--bg-card); padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                <h3 style="margin-bottom: 20px; font-size: 1.1rem; text-transform: uppercase;">Meus Dados</h3>
                <form action="processa_conta.php" method="POST">
                    <div style="margin-bottom: 16px;">
                        <label
                            style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px;">Nome</label>
                        <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome'])?>" required
                            class="form-control"
                            style="width: 100%; padding: 10px; background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm);">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label
                            style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px;">E-mail</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email'])?>" required
                            class="form-control"
                            style="width: 100%; padding: 10px; background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm);">
                    </div>
                    <button type="submit" name="atualizar_perfil" class="btn-primary"
                        style="width: 100%; padding: 12px; border-radius: var(--radius-md); font-size: 0.9rem;">Salvar
                        Alterações</button>
                </form>
            </div>

            <!-- Alterar Senha -->
            <div
                style="background: var(--bg-card); padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                <h3 style="margin-bottom: 20px; font-size: 1.1rem; text-transform: uppercase;">Segurança</h3>
                <form action="processa_conta.php" method="POST">
                    <div style="margin-bottom: 16px;">
                        <label
                            style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px;">Nova
                            Senha</label>
                        <input type="password" name="nova_senha" required class="form-control"
                            style="width: 100%; padding: 10px; background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm);">
                    </div>
                    <button type="submit" name="alterar_senha"
                        style="background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border-color); padding: 12px; width: 100%; border-radius: var(--radius-md); cursor: pointer; font-weight: 600;">Atualizar
                        Senha</button>
                </form>
            </div>

            <a href="logout.php"
                style="text-align: center; color: var(--text-muted); text-decoration: underline; font-size: 0.9rem;">Sair
                da Conta</a>
        </div>

        <!-- Pedidos -->
        <div
            style="background: var(--bg-card); padding: 32px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); height: fit-content;">
            <h3 style="margin-bottom: 24px; font-size: 1.2rem; text-transform: uppercase;">Histórico de Pedidos</h3>

            <?php if (empty($pedidos)): ?>
            <div style="text-align: center; padding: 40px 0;">
                <i class="fas fa-box" style="font-size: 2rem; color: var(--border-color); margin-bottom: 16px;"></i>
                <p style="color: var(--text-muted);">Nenhum pedido realizado ainda.</p>
            </div>
            <?php
else: ?>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($pedidos as $pedido): ?>
                <div
                    style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: var(--bg-tertiary); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Pedido #
                            <?= $pedido['id']?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                            <?= date('d/m/Y', strtotime($pedido['data_pedido']))?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">
                            <?= formatarPreco($pedido['valor_total'])?>
                        </div>
                        <span
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 4px 8px; border-radius: 4px; background: rgba(255,255,255,0.1); color: var(--text-primary);">
                            <?= htmlspecialchars($pedido['status'])?>
                        </span>
                    </div>
                    <a href="pedido_detalhes.php?id=<?= $pedido['id']?>"
                        style="margin-left: 16px; color: var(--text-muted);"><i class="fas fa-chevron-right"></i></a>
                </div>
                <?php
    endforeach; ?>
            </div>
            <?php
endif; ?>
        </div>

    </div>

    <style>
        @media (max-width: 900px) {
            div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</div>

<?php require_once 'templates/footer.php'; ?>