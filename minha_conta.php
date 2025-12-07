<?php
// minha_conta.php
session_start();
require_once 'config.php';

// --- SEGURANÇA ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Busca os dados atualizados do usuário no banco de dados
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT nome, email, data_registro FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Location: logout.php');
    exit();
}

if (!$usuario) {
    header('Location: logout.php');
    exit();
}

// --- BUSCA OS PEDIDOS DO USUÁRIO ---
$stmt_pedidos = $pdo->prepare("SELECT id, valor_total, data_pedido, status FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC");
$stmt_pedidos->execute([$user_id]);
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);


require_once 'templates/header.php';
?>

<div class="w-full max-w-4xl mx-auto py-24 px-4">
    <div class="pt-16">
        <h1 class="text-4xl md:text-5xl font-black text-white mb-4">Minha Conta</h1>
        <p class="text-lg text-brand-gray-text">Olá, <?= htmlspecialchars($usuario['nome']) ?>! Aqui você pode gerenciar suas informações e pedidos.</p>
        
        <?php
        // Exibe mensagens de sucesso ou erro
        if (isset($_SESSION['success_message'])) {
            echo '<div class="bg-green-500/20 text-green-300 p-4 rounded-lg my-6 text-center">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="bg-red-500/20 text-red-300 p-4 rounded-lg my-6 text-center">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="mt-10 bg-brand-gray/50 p-8 rounded-xl ring-1 ring-white/10">
            <h2 class="text-2xl font-bold text-white mb-6">Atualizar Perfil</h2>
            <form action="processa_conta.php" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="nome" class="block text-sm font-medium text-brand-gray-text">Nome Completo</label>
                        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray-light text-white">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-brand-gray-text">E-mail</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray-light text-white">
                    </div>
                </div>
                <button type="submit" name="atualizar_perfil" class="w-full md:w-auto mt-6 bg-brand-red hover:bg-brand-red-dark text-white font-bold py-3 px-6 rounded-lg">
                    Salvar Alterações
                </button>
            </form>
        </div>

        <div class="mt-10 bg-brand-gray/50 p-8 rounded-xl ring-1 ring-white/10">
            <h2 class="text-2xl font-bold text-white mb-6">Alterar Senha</h2>
            <form action="processa_conta.php" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="senha_atual" class="block text-sm font-medium text-brand-gray-text">Senha Atual</label>
                        <input type="password" id="senha_atual" name="senha_atual" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray-light text-white">
                    </div>
                    <div>
                        <label for="nova_senha" class="block text-sm font-medium text-brand-gray-text">Nova Senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray-light text-white">
                    </div>
                    <div>
                        <label for="confirmar_nova_senha" class="block text-sm font-medium text-brand-gray-text">Confirme a Nova Senha</label>
                        <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray-light text-white">
                    </div>
                </div>
                <button type="submit" name="alterar_senha" class="w-full md:w-auto mt-6 bg-brand-gray-light hover:bg-brand-gray text-white font-bold py-3 px-6 rounded-lg">
                    Alterar Senha
                </button>
            </form>
        </div>

        <div class="mt-10 bg-brand-gray/50 p-8 rounded-xl ring-1 ring-white/10">
            <h2 class="text-2xl font-bold text-white mb-6">Meus Pedidos</h2>
            <?php if (empty($pedidos)): ?>
                <p class="text-brand-gray-text">Você ainda não fez nenhum pedido.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-brand-gray-text uppercase">
                            <tr>
                                <th class="py-3 px-4">Pedido</th>
                                <th class="py-3 px-4">Data</th>
                                <th class="py-3 px-4">Total</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                            <tr class="border-t border-brand-gray-light">
                                <td class="py-4 px-4">
                                    <a href="pedido_detalhes.php?id=<?= $pedido['id'] ?>" class="font-medium text-brand-red hover:underline">#<?= $pedido['id'] ?></a>
                                </td>
                                <td class="py-4 px-4 text-white"><?= date('d/m/Y', strtotime($pedido['data_pedido'])) ?></td>
                                <td class="py-4 px-4 text-white"><?= formatarPreco($pedido['valor_total']) ?></td>
                                <td class="py-4 px-4 text-white">
                                    <span class="bg-yellow-500/20 text-yellow-300 text-xs font-semibold px-2.5 py-1 rounded-full"><?= htmlspecialchars($pedido['status']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php
require_once 'templates/footer.php';
?>