<?php
// admin/processa_pedido_admin.php
require_once 'secure.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pedido_id']) && isset($_POST['status'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $novo_status = trim($_POST['status']);

    // Lista de status permitidos para seguranÃ§a
    $status_permitidos = ['Processando', 'Enviado', 'ConcluÃ­do', 'Cancelado'];

    if (in_array($novo_status, $status_permitidos)) {
        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
            $stmt->execute([$novo_status, $pedido_id]);
            $_SESSION['admin_message'] = "Status do pedido #$pedido_id atualizado com sucesso!";
        } catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao atualizar o status do pedido.";
        }
    } else {
        $_SESSION['admin_message'] = "Status invÃ¡lido selecionado.";
    }

    // Redireciona de volta para a pÃ¡gina de detalhes do pedido
    header("Location: pedido_detalhes_admin.php?id=" . $pedido_id);
    exit();
}

// Se algo der errado, volta para a lista de pedidos
header('Location: pedidos.php');
exit();
?>