<?php
// processa_conta.php
session_start();
require_once 'config.php';

// Segurança: se o usuário não estiver logado, não pode processar nada.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// --- LÓGICA PARA ATUALIZAR O PERFIL (NOME, E-MAIL, WHATSAPP E ENDEREÇO) ---
if (isset($_POST['atualizar_perfil'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $whatsapp = trim($_POST['whatsapp']);
    $cep = trim($_POST['cep']);
    $endereco = trim($_POST['endereco']);
    $numero = trim($_POST['numero']);
    $complemento = trim($_POST['complemento']);
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);

    // Validações
    if (empty($nome) || empty($email)) {
        $_SESSION['error_message'] = "Nome e e-mail não podem ser vazios.";
        header('Location: minha_conta.php');
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Formato de e-mail inválido.";
        header('Location: minha_conta.php');
        exit();
    }

    // Verifica se o novo e-mail já pertence a outro usuário
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "Este e-mail já está em uso por outra conta.";
        header('Location: minha_conta.php');
        exit();
    }

    // Se tudo estiver certo, atualiza no banco de dados
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, whatsapp = ?, cep = ?, endereco = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ? WHERE id = ?");
        $stmt->execute([$nome, $email, $whatsapp, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $user_id]);
        $_SESSION['user_nome'] = $nome;
        $_SESSION['success_message'] = "Perfil e endereço atualizados com sucesso!";
    }
    catch (PDOException $e) {
        $_SESSION['error_message'] = "Erro ao atualizar o perfil. Tente novamente.";
        error_log("Erro update perfil: " . $e->getMessage());
    }
}


// --- LÓGICA PARA ALTERAR A SENHA (AGORA FUNCIONAL) ---
if (isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_nova_senha = $_POST['confirmar_nova_senha'];

    // 1. Validações básicas
    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_nova_senha)) {
        $_SESSION['error_message'] = "Todos os campos de senha são obrigatórios.";
        header('Location: minha_conta.php');
        exit();
    }
    if ($nova_senha !== $confirmar_nova_senha) {
        $_SESSION['error_message'] = "A nova senha e a confirmação não coincidem.";
        header('Location: minha_conta.php');
        exit();
    }
    if (strlen($nova_senha) < 6) {
        $_SESSION['error_message'] = "A nova senha deve ter pelo menos 6 caracteres.";
        header('Location: minha_conta.php');
        exit();
    }

    // 2. Verifica se a senha atual está correta
    try {
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha_atual, $usuario['senha'])) {
            // Senha atual está correta, prossegue para a atualização

            // 3. Criptografa a nova senha
            $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

            // 4. Atualiza a senha no banco de dados
            $update_stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $update_stmt->execute([$nova_senha_hash, $user_id]);

            $_SESSION['success_message'] = "Senha alterada com sucesso!";
        }
        else {
            // Senha atual incorreta
            $_SESSION['error_message'] = "A senha atual está incorreta.";
        }
    }
    catch (PDOException $e) {
        $_SESSION['error_message'] = "Erro ao verificar a senha. Tente novamente.";
    }
}


// Redireciona de volta para a página da conta
header('Location: minha_conta.php');
exit();
?>