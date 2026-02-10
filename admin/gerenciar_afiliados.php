<?php
// admin/gerenciar_afiliados.php - Gestão de Afiliados Premium
require_once 'secure.php';
$page_title = 'Afiliados';
require_once 'templates/header_admin.php';

// Processar ações
if (isset($_POST['adicionar'])) {
    $email_usuario = $_POST['email'];
    $codigo = strtoupper($_POST['codigo']);
    $chave_pix = $_POST['chave_pix'];

    try {
        // Encontra ID do usuário pelo email
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email_usuario]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("INSERT INTO afiliados (usuario_id, codigo, chave_pix) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $codigo, $chave_pix]);
            $_SESSION['admin_message'] = "Afiliado cadastrado com sucesso!";
            $_SESSION['admin_message_type'] = "success";
        }
        else {
            $_SESSION['admin_message'] = "Usuário não encontrado com esse email.";
            $_SESSION['admin_message_type'] = "error";
        }
    }
    catch (Exception $e) {
        $_SESSION['admin_message'] = "Erro ao cadastrar: " . $e->getMessage();
        $_SESSION['admin_message_type'] = "error";
    }
}

// Listar afiliados
try {
    $afiliados = $pdo->query("
        SELECT a.*, u.nome, u.email 
        FROM afiliados a 
        JOIN usuarios u ON a.usuario_id = u.id 
        ORDER BY a.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    $afiliados = [];
}
?>

<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Afiliados</h1>
            <p class="text-admin-gray-400">Parceiros que promovem sua loja</p>
        </div>
    </div>

    <!-- Novo Afiliado Form -->
    <div class="admin-card p-6">
        <h3 class="text-xl font-bold text-white mb-4">Novo Afiliado</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Email do
                    Usuário</label>
                <input type="email" name="email" required placeholder="cliente@email.com" class="w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Código de
                    Afiliado</label>
                <input type="text" name="codigo" required placeholder="PARCEIRO10" class="w-full uppercase">
            </div>
            <div>
                <label class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Chave
                    PIX</label>
                <input type="text" name="chave_pix" placeholder="CPF/Email/Telefone" class="w-full">
            </div>
            <div class="md:col-span-3">
                <button type="submit" name="adicionar" class="btn btn-primary bg-white text-black hover:bg-gray-200">
                    Cadastrar Afiliado
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela -->
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-admin-gray-800/50">
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Afiliado</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Código</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Saldo</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            PIX</th>
                        <th
                            class="px-6 py-4 text-right text-xs font-semibold text-admin-gray-400 uppercase tracking-wider">
                            Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($afiliados)): ?>
                    <tr>
                        <td colspan="5" class="p-6 text-center text-admin-gray-500">Nenhum afiliado encontrado.</td>
                    </tr>
                    <?php
else: ?>
                    <?php foreach ($afiliados as $afiliado): ?>
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-white font-medium">
                                <?= htmlspecialchars($afiliado['nome'])?>
                            </div>
                            <div class="text-xs text-admin-gray-500">
                                <?= htmlspecialchars($afiliado['email'])?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-admin-primary/10 text-white px-2 py-1 rounded text-xs font-mono">
                                <?= htmlspecialchars($afiliado['codigo'])?>
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-green-400">
                            R$
                            <?= number_format($afiliado['saldo'], 2, ',', '.')?>
                        </td>
                        <td class="px-6 py-4 text-sm text-admin-gray-400">
                            <?= htmlspecialchars($afiliado['chave_pix'] ?? '-')?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-admin-gray-400 hover:text-white transition-colors" title="Ver Vendas">
                                <i class="fas fa-chart-line"></i>
                            </button>
                        </td>
                    </tr>
                    <?php
    endforeach; ?>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>