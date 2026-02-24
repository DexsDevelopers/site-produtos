<?php
// admin/processa_tamanho.php â€” Processar aÃ§Ãµes de tamanhos
require_once 'secure.php';

// --- CRIAR NOVO GRUPO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_grupo'])) {
    $nome = trim($_POST['nome_grupo']);
    $descricao = trim($_POST['descricao_grupo'] ?? '');
    $tamanhos_str = trim($_POST['tamanhos']);

    if (empty($nome) || empty($tamanhos_str)) {
        $_SESSION['admin_message'] = "Nome do grupo e tamanhos sÃ£o obrigatÃ³rios.";
        header('Location: gerenciar_tamanhos.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO grupos_tamanho (nome, descricao) VALUES (?, ?)");
        $stmt->execute([$nome, $descricao ?: null]);
        $grupo_id = $pdo->lastInsertId();

        $tamanhos = array_map('trim', explode(',', $tamanhos_str));
        $tamanhos = array_filter($tamanhos); // remove vazios

        $stmt = $pdo->prepare("INSERT INTO tamanhos (grupo_id, valor, ordem) VALUES (?, ?, ?)");
        foreach ($tamanhos as $i => $valor) {
            $stmt->execute([$grupo_id, $valor, $i]);
        }

        $_SESSION['admin_message'] = "Grupo '$nome' criado com " . count($tamanhos) . " tamanhos!";
    }
    catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao criar grupo: " . $e->getMessage();
    }

    header('Location: gerenciar_tamanhos.php');
    exit;
}

// --- ADICIONAR TAMANHOS A GRUPO EXISTENTE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_tamanhos'])) {
    $grupo_id = (int)$_POST['grupo_id'];
    $novos_tamanhos_str = trim($_POST['novos_tamanhos']);

    if (empty($novos_tamanhos_str) || $grupo_id <= 0) {
        header('Location: gerenciar_tamanhos.php');
        exit;
    }

    try {
        // Busca a maior ordem atual
        $max_ordem = $pdo->prepare("SELECT COALESCE(MAX(ordem), 0) FROM tamanhos WHERE grupo_id = ?");
        $max_ordem->execute([$grupo_id]);
        $ordem = $max_ordem->fetchColumn() + 1;

        $tamanhos = array_map('trim', explode(',', $novos_tamanhos_str));
        $tamanhos = array_filter($tamanhos);

        $stmt = $pdo->prepare("INSERT INTO tamanhos (grupo_id, valor, ordem) VALUES (?, ?, ?)");
        $adicionados = 0;
        foreach ($tamanhos as $valor) {
            // Verifica se jÃ¡ existe
            $check = $pdo->prepare("SELECT COUNT(*) FROM tamanhos WHERE grupo_id = ? AND valor = ?");
            $check->execute([$grupo_id, $valor]);
            if ($check->fetchColumn() == 0) {
                $stmt->execute([$grupo_id, $valor, $ordem++]);
                $adicionados++;
            }
        }

        $_SESSION['admin_message'] = "$adicionados tamanho(s) adicionado(s)!";
    }
    catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro: " . $e->getMessage();
    }

    header('Location: gerenciar_tamanhos.php');
    exit;
}

// --- DELETAR TAMANHO INDIVIDUAL ---
if (isset($_GET['deletar_tamanho'])) {
    $tamanho_id = (int)$_GET['deletar_tamanho'];

    try {
        $stmt = $pdo->prepare("DELETE FROM tamanhos WHERE id = ?");
        $stmt->execute([$tamanho_id]);
        $_SESSION['admin_message'] = "Tamanho removido!";
    }
    catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao remover: " . $e->getMessage();
    }

    header('Location: gerenciar_tamanhos.php');
    exit;
}

// --- DELETAR GRUPO INTEIRO ---
if (isset($_GET['deletar_grupo'])) {
    $grupo_id = (int)$_GET['deletar_grupo'];

    try {
        // Limpa referÃªncia nos produtos
        $pdo->prepare("UPDATE produtos SET grupo_tamanho_id = NULL WHERE grupo_tamanho_id = ?")->execute([$grupo_id]);
        // Deleta grupo (cascade deleta os tamanhos)
        $pdo->prepare("DELETE FROM grupos_tamanho WHERE id = ?")->execute([$grupo_id]);
        $_SESSION['admin_message'] = "Grupo excluÃ­do com sucesso!";
    }
    catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao excluir grupo: " . $e->getMessage();
    }

    header('Location: gerenciar_tamanhos.php');
    exit;
}

header('Location: gerenciar_tamanhos.php');
exit;