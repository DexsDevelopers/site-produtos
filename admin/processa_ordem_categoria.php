<?php
// admin/processa_ordem_categoria.php
require_once 'secure.php';

if (isset($_GET['id']) && isset($_GET['direcao'])) {
    $id_para_mover = (int)$_GET['id'];
    $direcao = $_GET['direcao'];

    try {
        $pdo->beginTransaction();

        // Pega a ordem atual da categoria que queremos mover
        $stmt = $pdo->prepare("SELECT ordem FROM categorias WHERE id = ?");
        $stmt->execute([$id_para_mover]);
        $ordem_atual = $stmt->fetchColumn();

        if ($direcao === 'up') {
            // Encontra a categoria imediatamente ACIMA (a que tem a maior ordem menor que a atual)
            $stmt = $pdo->prepare("SELECT id, ordem FROM categorias WHERE ordem < ? ORDER BY ordem DESC LIMIT 1");
            $stmt->execute([$ordem_atual]);
            $categoria_adjacente = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif ($direcao === 'down') {
            // Encontra a categoria imediatamente ABAIXO (a que tem a menor ordem maior que a atual)
            $stmt = $pdo->prepare("SELECT id, ordem FROM categorias WHERE ordem > ? ORDER BY ordem ASC LIMIT 1");
            $stmt->execute([$ordem_atual]);
            $categoria_adjacente = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Se uma categoria adjacente foi encontrada, troca as ordens
        if (isset($categoria_adjacente) && $categoria_adjacente) {
            // Atualiza a categoria adjacente para a ordem da categoria que estamos movendo
            $stmt_update1 = $pdo->prepare("UPDATE categorias SET ordem = ? WHERE id = ?");
            $stmt_update1->execute([$ordem_atual, $categoria_adjacente['id']]);

            // Atualiza a categoria que estamos movendo para a ordem da categoria adjacente
            $stmt_update2 = $pdo->prepare("UPDATE categorias SET ordem = ? WHERE id = ?");
            $stmt_update2->execute([$categoria_adjacente['ordem'], $id_para_mover]);
        }

        $pdo->commit();
        $_SESSION['admin_message'] = "Ordem das categorias atualizada.";

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['admin_message'] = "Erro ao reordenar categorias: " . $e->getMessage();
    }
}

header("Location: gerenciar_categorias.php");
exit();
?>