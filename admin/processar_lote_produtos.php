<?php
// admin/processar_lote_produtos.php - Processador de Ações em Massa
require_once 'secure.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: gerenciar_produtos.php");
    exit();
}

$produtos_ids = $_POST['produtos'] ?? [];
$action = $_POST['bulk_action'] ?? '';

if (empty($produtos_ids)) {
    $_SESSION['admin_message'] = "Nenhum produto selecionado.";
    header("Location: gerenciar_produtos.php");
    exit();
}

try {
    $placeholders = implode(',', array_fill(0, count($produtos_ids), '?'));

    switch ($action) {
        case 'change_category':
            $cat_id = $_POST['bulk_category_id'] ?? null;
            if ($cat_id) {
                $stmt = $pdo->prepare("UPDATE produtos SET categoria_id = ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([$cat_id], $produtos_ids));
                $_SESSION['admin_message'] = count($produtos_ids) . " produtos movidos para a nova categoria.";
            }
            break;

        case 'adjust_price':
            $percent = floatval($_POST['bulk_price_adjustment'] ?? 0);
            if ($percent != 0) {
                $multiplier = 1 + ($percent / 100);
                $stmt = $pdo->prepare("UPDATE produtos SET preco = preco * ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([$multiplier], $produtos_ids));
                $_SESSION['admin_message'] = "Preços de " . count($produtos_ids) . " produtos ajustados em $percent%.";
            }
            break;

        case 'set_featured':
            $stmt = $pdo->prepare("UPDATE produtos SET destaque = 1 WHERE id IN ($placeholders)");
            $stmt->execute($produtos_ids);
            $_SESSION['admin_message'] = count($produtos_ids) . " produtos marcados como destaque.";
            break;

        case 'unset_featured':
            $stmt = $pdo->prepare("UPDATE produtos SET destaque = 0 WHERE id IN ($placeholders)");
            $stmt->execute($produtos_ids);
            $_SESSION['admin_message'] = count($produtos_ids) . " produtos removidos dos destaques.";
            break;

        case 'delete':
            // Deletar imagens primeiro
            $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id IN ($placeholders)");
            $stmt->execute($produtos_ids);
            $imgs = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($imgs as $img) {
                if ($img && file_exists("../$img"))
                    @unlink("../$img");
            }

            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id IN ($placeholders)");
            $stmt->execute($produtos_ids);
            $_SESSION['admin_message'] = count($produtos_ids) . " produtos excluídos permanentemente.";
            break;

        default:
            $_SESSION['admin_message'] = "Ação inválida.";
    }

}
catch (Exception $e) {
    $_SESSION['admin_message'] = "Erro ao processar lote: " . $e->getMessage();
}

header("Location: gerenciar_produtos.php");
exit();
