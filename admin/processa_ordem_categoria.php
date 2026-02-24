<?php
// admin/processa_ordem_categoria.php
require_once 'secure.php';

if (isset($_GET['id']) && isset($_GET['direcao'])) {
    $id_para_mover = (int)$_GET['id'];
    $direcao = $_GET['direcao'];

    try {
        $pdo->beginTransaction();

        // 1. Pega todas as categorias no estado atual (ordenadas por ordem e ID)
        $stmt = $pdo->query("SELECT id FROM categorias ORDER BY ordem ASC, id ASC");
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 2. Encontra a posição da categoria que queremos mover
        $index = array_search($id_para_mover, $ids);

        if ($index !== false) {
            if ($direcao === 'up' && $index > 0) {
                // Troca com o anterior
                $temp = $ids[$index - 1];
                $ids[$index - 1] = $ids[$index];
                $ids[$index] = $temp;
            }
            elseif ($direcao === 'down' && $index < count($ids) - 1) {
                // Troca com o próximo
                $temp = $ids[$index + 1];
                $ids[$index + 1] = $ids[$index];
                $ids[$index] = $temp;
            }

            // 3. Atualiza TODAS as categorias com a nova ordem consecutiva
            $stmt_update = $pdo->prepare("UPDATE categorias SET ordem = ? WHERE id = ?");
            foreach ($ids as $nova_ordem => $id_final) {
                $stmt_update->execute([$nova_ordem, $id_final]);
            }
        }

        $pdo->commit();
        $_SESSION['admin_message'] = "Ordem das categorias atualizada.";

    }
    catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['admin_message'] = "Erro ao reordenar categorias: " . $e->getMessage();
    }
}

header("Location: gerenciar_categorias.php");
exit();
?>