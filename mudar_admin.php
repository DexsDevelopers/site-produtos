<?php
require 'config.php';

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_email = $_POST['email'];
    $nova_senha = $_POST['senha'];

    if (!empty($novo_email) && !empty($nova_senha)) {
        try {
            // Verifica se já existe um admin
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            $admin_existente = $stmt->fetch();

            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

            if ($admin_existente) {
                // Atualiza o admin existente
                $stmt = $pdo->prepare("UPDATE usuarios SET email = ?, senha = ? WHERE id = ?");
                $stmt->execute([$novo_email, $senha_hash, $admin_existente['id']]);
                $mensagem = "✅ Admin atualizado com sucesso! Novo email: $novo_email";
            }
            else {
                // Cria um novo admin
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, 'admin')");
                $stmt->execute(['Administrador', $novo_email, $senha_hash]);
                $mensagem = "✅ Admin criado com sucesso! Email: $novo_email";
            }
        }
        catch (PDOException $e) {
            $mensagem = "❌ Erro ao atualizar: " . $e->getMessage();
        }
    }
    else {
        $mensagem = "❌ Preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mudar Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">

    <div class="bg-gray-800 p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-700">
        <h1 class="text-2xl font-bold mb-6 text-center text-blue-500">Alterar Acesso Admin</h1>

        <?php if ($mensagem): ?>
        <div class="bg-blue-500/20 text-blue-300 p-4 rounded-lg mb-6 text-center border border-blue-500/30">
            <?= $mensagem?>
        </div>
        <?php
endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-400 mb-2 font-medium">Novo Email</label>
                <input type="email" name="email" required
                    class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:border-blue-500 focus:outline-none text-white">
            </div>

            <div>
                <label class="block text-gray-400 mb-2 font-medium">Nova Senha</label>
                <input type="text" name="senha" required
                    class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:border-blue-500 focus:outline-none text-white">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 transition-colors p-4 rounded-lg font-bold uppercase tracking-wider">
                Atualizar Acesso
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="admin/index.php" class="text-gray-500 hover:text-white transition-colors text-sm">Voltar para
                Admin</a>
        </div>
    </div>

</body>

</html>