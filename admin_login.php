<?php
// admin_login.php - Login Admin Premium
session_start();
require_once 'config.php';

// MANUTENÇÃO: Cria tabela de sessões se não existir
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (token_hash)
    )");
}
catch (Exception $e) {
}

// AUTO-LOGIN: Verifica Cookie "Lembrar de Mim"
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    try {
        $token_hash = hash('sha256', $_COOKIE['remember_token']);
        $stmt = $pdo->prepare("SELECT u.id, u.nome, u.role FROM user_sessions s JOIN usuarios u ON s.user_id = u.id WHERE s.token_hash = ? AND s.expires_at > NOW()");
        $stmt->execute([$token_hash]);
        $user_cookie = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_cookie && $user_cookie['role'] === 'admin') {
            $_SESSION['user_id'] = $user_cookie['id'];
            $_SESSION['user_nome'] = $user_cookie['nome'];
            header('Location: admin/index.php');
            exit();
        }
    }
    catch (Exception $e) {
    }
}

// Se já estiver logado (por sessão), redireciona
if (isset($_SESSION['user_id'])) {
    header('Location: admin/index.php');
    exit();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    }
    else {
        try {
            $stmt = $pdo->prepare("SELECT id, nome, email, senha, role FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                if ($usuario['role'] === 'admin') {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['user_nome'] = $usuario['nome'];

                    // LEMBRAR DE MIM
                    if (isset($_POST['remember'])) {
                        try {
                            $token = bin2hex(random_bytes(32));
                            $token_hash = hash('sha256', $token);
                            $expires = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 dias

                            $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
                            $stmt->execute([$usuario['id'], $token_hash, $expires]);

                            // Cookie seguro (apenas HTTPS se disponível)
                            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
                            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', $secure, true);
                        }
                        catch (Exception $e) {
                        }
                    }

                    header('Location: admin/index.php');
                    exit();
                }
                else {
                    $erro = "Acesso restrito.";
                }
            }
            else {
                $erro = "Credenciais inválidas.";
            }
        }
        catch (PDOException $e) {
            $erro = "Erro no servidor.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — MACARIO BRAZIL</title>
    <!-- CSS Customizado (Macario Design System) -->
    <link rel="stylesheet" href="assets/css/admin_macario.css?v=<?= time()?>">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            background-image:
                radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                linear-gradient(135deg, #000000 0%, #0a0a0a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: #b3b3b3;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #fff;
            outline: none;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #fff;
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="text-center mb-8">
            <div
                style="width: 50px; height: 50px; background: #fff; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i class="fas fa-shield-alt text-2xl" style="color: #000;"></i>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 5px;">Admin Login
            </h1>
            <p style="color: #666; font-size: 0.9rem;">Acesso restrito ao painel</p>
        </div>

        <?php if ($erro): ?>
        <div
            style="background: rgba(239, 68, 68, 0.15); color: #ef4444; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 0.9rem;">
            <?= htmlspecialchars($erro)?>
        </div>
        <?php
endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="admin@macario.com" required>
            </div>

            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="input-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                <input type="checkbox" name="remember" id="remember"
                    style="width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;">
                <label for="remember"
                    style="margin: 0; cursor: pointer; color: #ccc; font-size: 0.9rem; text-transform: none; letter-spacing: normal;">Lembrar
                    de mim</label>
            </div>

            <button type="submit" class="btn-submit">
                Entrar
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px;">
            <a href="index.php" style="color: #666; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;">
                ← Voltar para a Loja
            </a>
        </div>
    </div>
</body>

</html>