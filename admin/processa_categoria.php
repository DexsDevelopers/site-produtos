<?php
// admin/processa_categoria.php
require_once 'secure.php';

// --- LÓGICA PARA ADICIONAR NOVA CATEGORIA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    if (!empty($nome)) {
        try {
            // Pega a maior ordem atual para colocar a nova categoria no final
            $stmt_ordem = $pdo->query("SELECT MAX(ordem) FROM categorias");
            $max_ordem = $stmt_ordem->fetchColumn();
            $nova_ordem = ($max_ordem !== false) ? $max_ordem + 1 : 0;

            $stmt = $pdo->prepare("INSERT INTO categorias (nome, ordem) VALUES (?, ?)");
            $stmt->execute([$nome, $nova_ordem]);
            $_SESSION['admin_message'] = "Categoria adicionada com sucesso!";
        }
        catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao adicionar categoria: " . $e->getMessage();
        }
    }
    else {
        $_SESSION['admin_message'] = "O nome da categoria não pode ser vazio.";
    }
}

// --- LÓGICA PARA DELETAR CATEGORIA ---
if (isset($_GET['deletar'])) {
    $id = (int)$_GET['deletar'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['admin_message'] = "Categoria deletada com sucesso!";
    }
    catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao deletar categoria: " . $e->getMessage();
    }
}

header("Location: gerenciar_categorias.php");
exit();
?>          $stmt_update->execute([$nova_ordem, $id_final]);
        }

        $pdo->commit();
        $_SESSION['admin_message'] = "Categoria deletada com sucesso!";
    }
    catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['admin_message'] = "Erro ao deletar categoria: " . $e->getMessage();
    }
}

header("Location: gerenciar_categorias.php");
exit();
?>