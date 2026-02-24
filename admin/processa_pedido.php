<?php
// admin/processa_pedido.php
require_once 'secure.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'excluir_unico') {
        $id = (int)($_POST['pedido_id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->beginTransaction();
                // Deleta primeiro os itens do pedido (foreign key ou integridade)
                $stmt_itens = $pdo->prepare("DELETE FROM pedido_itens WHERE pedido_id = ?");
                $stmt_itens->execute([$id]);

                // Deleta o pedido
                $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
                $stmt->execute([$id]);

                $pdo->commit();
                header("Location: pedidos.php?msg=" . urlencode("Pedido #$id excluÃ­do com sucesso!"));
                exit;
            }
            catch (Exception $e) {
                if ($pdo->inTransaction())
                    $pdo->rollBack();
                header("Location: pedidos.php?erro=" . urlencode("Erro ao excluir pedido: " . $e->getMessage()));
                exit;
            }
        }
    }

    if ($action === 'excluir_em_massa') {
        $ids = $_POST['pedido_ids'] ?? [];
        if (!empty($ids) && is_array($ids)) {
            $ids = array_map('intval', $ids);
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';

            try {
                $pdo->beginTransaction();

                // Deleta os itens
                $stmt_itens = $pdo->prepare("DELETE FROM pedido_itens WHERE pedido_id IN ($placeholders)");
                $stmt_itens->execute($ids);

                // Deleta os pedidos
                $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id IN ($placeholders)");
                $stmt->execute($ids);

                $pdo->commit();
                header("Location: pedidos.php?msg=" . urlencode(count($ids) . " pedidos excluÃ­dos com sucesso!"));
                exit;
            }
            catch (Exception $e) {
                if ($pdo->inTransaction())
                    $pdo->rollBack();
                header("Location: pedidos.php?erro=" . urlencode("Erro na exclusÃ£o em massa: " . $e->getMessage()));
                exit;
            }
        }
    }
}

header("Location: pedidos.php");
exit;
