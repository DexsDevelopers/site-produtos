<?php
// admin/salvar_produto.php (COM SISTEMA DE TAMANHOS)
require_once 'secure.php';

// --- LÓGICA PARA ADICIONAR UM NOVO PRODUTO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar'])) {

    $nome = trim($_POST['nome']);
    $descricao_curta = trim($_POST['descricao_curta']);
    $descricao = trim($_POST['descricao']);
    $preco = trim($_POST['preco']);
    $preco_antigo = !empty(trim($_POST['preco_antigo'])) ? trim($_POST['preco_antigo']) : null;
    $categoria_id = (int)$_POST['categoria_id'];
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $tipo = ($_POST['tipo'] ?? 'digital') === 'fisico' ? 'fisico' : 'digital';
    $grupo_tamanho_id = !empty($_POST['grupo_tamanho_id']) ? (int)$_POST['grupo_tamanho_id'] : null;
    $tamanhos_selecionados = $_POST['tamanhos_selecionados'] ?? [];

    // Se for digital, limpa dados de tamanho
    if ($tipo === 'digital') {
        $grupo_tamanho_id = null;
        $tamanhos_selecionados = [];
    }

    if (empty($nome) || empty($preco) || empty($categoria_id) || !isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== 0) {
        $_SESSION['admin_message'] = "Nome, Preço, Categoria e Imagem são obrigatórios.";
        header("Location: adicionar_produto.php");
        exit();
    }

    $target_dir = "../assets/uploads/";
    $file_extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('produto_', true) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file)) {
        $imagem_path = "assets/uploads/" . $new_filename;
        try {
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao_curta, descricao, preco, preco_antigo, imagem, categoria_id, destaque, tipo, grupo_tamanho_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $descricao_curta, $descricao, $preco, $preco_antigo, $imagem_path, $categoria_id, $destaque, $tipo, $grupo_tamanho_id]);
            $novo_produto_id = $pdo->lastInsertId();

            // Salvar tamanhos selecionados
            if ($tipo === 'fisico' && !empty($tamanhos_selecionados)) {
                $stmt_tam = $pdo->prepare("INSERT INTO produto_tamanhos (produto_id, tamanho_id, estoque) VALUES (?, ?, ?)");
                foreach ($tamanhos_selecionados as $tam_id) {
                    $estoque = (int)($_POST['estoque_' . $tam_id] ?? 0);
                    $stmt_tam->execute([$novo_produto_id, (int)$tam_id, $estoque]);
                }
            }

            $_SESSION['admin_message'] = "Produto adicionado com sucesso!";

            // Salva apenas configurações de estrutura para o próximo cadastro
            $_SESSION['last_product_config'] = [
                'tipo' => $tipo,
                'categoria_id' => $categoria_id,
                'grupo_tamanho_id' => $grupo_tamanho_id,
                'tamanhos_selecionados' => $tamanhos_selecionados
            ];

        }
        catch (PDOException $e) {
            $_SESSION['admin_message'] = "Erro ao salvar o produto: " . $e->getMessage();
        }
    }
    else {
        $_SESSION['admin_message'] = "Erro ao fazer upload da imagem.";
    }
    header("Location: adicionar_produto.php?success=1");
    exit();
}

// --- LÓGICA PARA EDITAR UM PRODUTO EXISTENTE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {

    $id = (int)$_POST['id'];
    $nome = trim($_POST['nome']);
    $descricao_curta = trim($_POST['descricao_curta']);
    $descricao = trim($_POST['descricao']);
    $preco = trim($_POST['preco']);
    $preco_antigo = !empty(trim($_POST['preco_antigo'])) ? trim($_POST['preco_antigo']) : null;
    $categoria_id = (int)$_POST['categoria_id'];
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $tipo = ($_POST['tipo'] ?? 'digital') === 'fisico' ? 'fisico' : 'digital';
    $grupo_tamanho_id = !empty($_POST['grupo_tamanho_id']) ? (int)$_POST['grupo_tamanho_id'] : null;
    $tamanhos_selecionados = $_POST['tamanhos_selecionados'] ?? [];

    // Se for digital, limpa dados de tamanho
    if ($tipo === 'digital') {
        $grupo_tamanho_id = null;
        $tamanhos_selecionados = [];
    }

    if (empty($nome) || empty($preco) || empty($id) || empty($categoria_id)) {
        $_SESSION['admin_message'] = "Nome, Preço e Categoria são obrigatórios.";
        header("Location: editar_produto.php?id=$id");
        exit();
    }

    $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto_atual = $stmt->fetch(PDO::FETCH_ASSOC);
    $imagem_path = $produto_atual['imagem'];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $target_dir = "../assets/uploads/";
        $file_extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('produto_', true) . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file)) {
            if (!empty($imagem_path) && file_exists('../' . $imagem_path)) {
                unlink('../' . $imagem_path);
            }
            $imagem_path = "assets/uploads/" . $new_filename;
        }
    }

    try {
        $sql = "UPDATE produtos SET nome = :nome, descricao_curta = :desc_curta, descricao = :desc, preco = :preco, preco_antigo = :preco_antigo, imagem = :img, categoria_id = :cat_id, destaque = :destaque, tipo = :tipo, grupo_tamanho_id = :grupo_tam WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':desc_curta', $descricao_curta);
        $stmt->bindParam(':desc', $descricao);
        $stmt->bindParam(':preco', $preco);
        $stmt->bindParam(':preco_antigo', $preco_antigo);
        $stmt->bindParam(':img', $imagem_path);
        $stmt->bindParam(':cat_id', $categoria_id, PDO::PARAM_INT);
        $stmt->bindParam(':destaque', $destaque, PDO::PARAM_INT);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':grupo_tam', $grupo_tamanho_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        // Atualizar tamanhos: remove antigos e insere novos
        $pdo->prepare("DELETE FROM produto_tamanhos WHERE produto_id = ?")->execute([$id]);
        if ($tipo === 'fisico' && !empty($tamanhos_selecionados)) {
            $stmt_tam = $pdo->prepare("INSERT INTO produto_tamanhos (produto_id, tamanho_id, estoque) VALUES (?, ?, ?)");
            foreach ($tamanhos_selecionados as $tam_id) {
                $estoque = (int)($_POST['estoque_' . $tam_id] ?? 0);
                $stmt_tam->execute([$id, (int)$tam_id, $estoque]);
            }
        }

        $_SESSION['admin_message'] = "Produto atualizado com sucesso!";
    }
    catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao atualizar o produto: " . $e->getMessage();
    }
    header("Location: editar_produto.php?id=$id");
    exit();
}

header("Location: index.php");
exit();
?>