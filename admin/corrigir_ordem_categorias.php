<?php
// admin/corrigir_ordem_categorias.php
require_once 'secure.php';

try {
    $pdo->beginTransaction();

    // Busca todas as categorias ordenadas pela ordem atual e depois pelo ID
    $stmt = $pdo->query("SELECT id FROM categorias ORDER BY ordem ASC, id ASC");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nova_ordem = 0;
    $stmt_update = $pdo->prepare("UPDATE categorias SET ordem = ? WHERE id = ?");

    foreach ($categorias as $categoria) {
        $stmt_update->execute([$nova_ordem, $categoria['id']]);
        $nova_ordem++;
    }

    $pdo->commit();
    $_SESSION['admin_message'] = "Ordem das categorias corrigida com sucesso! " . count($categorias) . " categorias reorganizadas.";

}
catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['admin_message'] = "Erro ao corrigir ordem: " . $e->getMessage();
}

header("Location: gerenciar_categorias.php");
exit();
?>