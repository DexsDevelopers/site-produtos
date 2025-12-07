<?php
// admin_login.php - Login direto para administrador
session_start();
require_once 'config.php';

// Se já estiver logado como admin, redireciona
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && $usuario['role'] === 'admin') {
            header('Location: admin/index.php');
            exit();
        }
    } catch (PDOException $e) {
        // Continua para o login
    }
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = "Email e senha são obrigatórios.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, nome, email, senha, role FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                if ($usuario['role'] === 'admin') {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['user_nome'] = $usuario['nome'];
                    header('Location: admin/index.php');
                    exit();
                } else {
                    $erro = "Acesso negado. Apenas administradores podem acessar esta área.";
                }
            } else {
                $erro = "Email ou senha inválidos.";
            }
        } catch (PDOException $e) {
            $erro = "Erro no servidor. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-red': '#FF3B5C',
                        'brand-red-dark': '#E91E63',
                        'brand-black': '#0A0A0A',
                        'brand-gray': { DEFAULT: '#1E293B', light: '#334155', text: '#94A3B8' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-black text-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto p-6">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black text-white mb-2">Admin Login</h1>
            <p class="text-brand-gray-text">Acesso ao painel administrativo</p>
        </div>

        <div class="bg-brand-gray/50 p-8 rounded-xl ring-1 ring-white/10">
            <?php if ($erro): ?>
                <div class="bg-red-500/20 text-red-300 p-4 rounded-lg mb-6 text-center">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Email</label>
                    <input type="email" name="email" required 
                           class="w-full bg-brand-gray-light border border-brand-gray text-white rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-brand-red"
                           placeholder="admin@loja.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-white mb-2">Senha</label>
                    <input type="password" name="senha" required 
                           class="w-full bg-brand-gray-light border border-brand-gray text-white rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-brand-red"
                           placeholder="admin123">
                </div>

                <button type="submit" 
                        class="w-full bg-brand-red hover:bg-brand-red-dark text-white font-bold py-3 px-4 rounded-lg transition-colors">
                    Entrar no Admin
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-brand-gray-text">
                    Credenciais padrão:<br>
                    <strong>Email:</strong> admin@loja.com<br>
                    <strong>Senha:</strong> admin123
                </p>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="index.php" class="text-brand-gray-text hover:text-white transition-colors">
                ← Voltar à Loja
            </a>
        </div>
    </div>
</body>
</html>
