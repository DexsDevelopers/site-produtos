<?php
// adicionar_avaliacoes_falsas.php - Script para adicionar avaliações falsas a todos os produtos
session_start();
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

// Nomes de usuários falsos
$nomes_usuarios = [
    'Maria Silva', 'João Santos', 'Ana Costa', 'Pedro Oliveira', 'Julia Ferreira',
    'Carlos Souza', 'Fernanda Lima', 'Ricardo Alves', 'Camila Rocha', 'Lucas Martins',
    'Beatriz Gomes', 'Rafael Dias', 'Larissa Barbosa', 'Gabriel Nunes', 'Isabela Ramos',
    'Thiago Carvalho', 'Mariana Azevedo', 'Felipe Correia', 'Amanda Teixeira', 'Bruno Monteiro'
];

// Comentários falsos realistas
$comentarios_positivos = [
    'Produto excelente! Superou minhas expectativas.',
    'Muito bom, recomendo! Qualidade top.',
    'Entrega rápida e produto de qualidade.',
    'Adorei! Vale muito a pena.',
    'Produto incrível, super satisfeito!',
    'Melhor compra que fiz! Recomendo.',
    'Qualidade excelente, nota 10!',
    'Superou todas as expectativas!',
    'Produto de primeira linha!',
    'Excelente custo-benefício!',
    'Muito satisfeito com a compra!',
    'Produto de alta qualidade!',
    'Recomendo para todos!',
    'Superou minhas expectativas!',
    'Vale cada centavo investido!'
];

$comentarios_medianos = [
    'Produto bom, mas poderia ser melhor.',
    'Atendeu minhas expectativas.',
    'Bom produto pelo preço.',
    'Razoável, nada de especial.',
    'Está ok, mas esperava mais.',
    'Produto decente, funciona bem.',
    'Bom custo-benefício.',
    'Satisfatório, mas não é perfeito.'
];

$comentarios_negativos = [
    'Produto não atendeu minhas expectativas.',
    'Esperava mais qualidade.',
    'Não recomendo, qualidade baixa.',
    'Produto deixou a desejar.'
];

// Busca todos os produtos
try {
    $stmt_produtos = $pdo->query("SELECT id FROM produtos");
    $produtos = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);
    
    $total_avaliacoes_adicionadas = 0;
    
    foreach ($produtos as $produto) {
        $produto_id = $produto['id'];
        
        // Verifica se já tem avaliações
        $stmt_check = $pdo->prepare("SELECT COUNT(*) as total FROM avaliacoes WHERE produto_id = ?");
        $stmt_check->execute([$produto_id]);
        $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Se já tem avaliações, pula
        if ($result['total'] > 0) {
            continue;
        }
        
        // Adiciona entre 3 e 8 avaliações por produto
        $num_avaliacoes = rand(3, 8);
        
        for ($i = 0; $i < $num_avaliacoes; $i++) {
            // Distribuição de notas: 70% 5 estrelas, 20% 4 estrelas, 7% 3 estrelas, 2% 2 estrelas, 1% 1 estrela
            $rand = rand(1, 100);
            if ($rand <= 70) {
                $nota = 5;
                $comentario = $comentarios_positivos[array_rand($comentarios_positivos)];
            } elseif ($rand <= 90) {
                $nota = 4;
                $comentario = $comentarios_positivos[array_rand($comentarios_positivos)];
            } elseif ($rand <= 97) {
                $nota = 3;
                $comentario = $comentarios_medianos[array_rand($comentarios_medianos)];
            } elseif ($rand <= 99) {
                $nota = 2;
                $comentario = $comentarios_medianos[array_rand($comentarios_medianos)];
            } else {
                $nota = 1;
                $comentario = $comentarios_negativos[array_rand($comentarios_negativos)];
            }
            
            // Seleciona um nome aleatório
            $nome_usuario = $nomes_usuarios[array_rand($nomes_usuarios)];
            
            // Data aleatória nos últimos 6 meses
            $dias_aleatorios = rand(0, 180);
            $data_avaliacao = date('Y-m-d H:i:s', strtotime("-$dias_aleatorios days"));
            
            // Verifica se existe um usuário com esse nome, se não, cria um temporário
            $stmt_user = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ? LIMIT 1");
            $stmt_user->execute([$nome_usuario]);
            $usuario_existente = $stmt_user->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario_existente) {
                $usuario_id = $usuario_existente['id'];
            } else {
                // Cria um usuário temporário para a avaliação
                // Usa um email único baseado no nome
                $email_temp = strtolower(str_replace(' ', '', $nome_usuario)) . rand(1000, 9999) . '@temp.com';
                
                // Tenta inserir com role, se não funcionar, tenta sem role
                try {
                    $stmt_create_user = $pdo->prepare("
                        INSERT INTO usuarios (nome, email, senha, role) 
                        VALUES (?, ?, ?, 'user')
                    ");
                    $stmt_create_user->execute([$nome_usuario, $email_temp, password_hash('temp123', PASSWORD_DEFAULT)]);
                } catch (PDOException $e) {
                    // Se a coluna role não existir, tenta sem ela
                    $stmt_create_user = $pdo->prepare("
                        INSERT INTO usuarios (nome, email, senha) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt_create_user->execute([$nome_usuario, $email_temp, password_hash('temp123', PASSWORD_DEFAULT)]);
                }
                $usuario_id = $pdo->lastInsertId();
            }
            
            // Insere a avaliação (tenta com status, se não existir, sem status)
            try {
                $stmt_insert = $pdo->prepare("
                    INSERT INTO avaliacoes (produto_id, usuario_id, nota, comentario, data_avaliacao, status) 
                    VALUES (?, ?, ?, ?, ?, 'aprovada')
                ");
                $stmt_insert->execute([$produto_id, $usuario_id, $nota, $comentario, $data_avaliacao]);
            } catch (PDOException $e) {
                // Se a coluna status não existir, tenta sem ela
                $stmt_insert = $pdo->prepare("
                    INSERT INTO avaliacoes (produto_id, usuario_id, nota, comentario, data_avaliacao) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt_insert->execute([$produto_id, $usuario_id, $nota, $comentario, $data_avaliacao]);
            }
            $total_avaliacoes_adicionadas++;
        }
        
        // Atualiza a média e total de avaliações do produto
        try {
            $stmt_media = $pdo->prepare("
                SELECT AVG(nota) as media, COUNT(*) as total
                FROM avaliacoes 
                WHERE produto_id = ? AND status = 'aprovada'
            ");
            $stmt_media->execute([$produto_id]);
        } catch (PDOException $e) {
            // Se a coluna status não existir, busca sem filtro
            $stmt_media = $pdo->prepare("
                SELECT AVG(nota) as media, COUNT(*) as total
                FROM avaliacoes 
                WHERE produto_id = ?
            ");
            $stmt_media->execute([$produto_id]);
        }
        
        $result_media = $stmt_media->fetch(PDO::FETCH_ASSOC);
        
        if ($result_media && $result_media['total'] > 0) {
            try {
                $stmt_update = $pdo->prepare("
                    UPDATE produtos 
                    SET media_avaliacoes = ?, total_avaliacoes = ?
                    WHERE id = ?
                ");
                $stmt_update->execute([
                    round($result_media['media'], 1),
                    $result_media['total'],
                    $produto_id
                ]);
            } catch (PDOException $e) {
                // Se as colunas não existirem, apenas continua
            }
        }
    }
    
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Adicionar Avaliações</title></head><body style='font-family: Arial; padding: 20px; background: #000; color: #fff;'>";
    echo "<h1 style='color: #ff0000;'>✅ Sucesso!</h1>";
    echo "<p>Foram adicionadas <strong style='color: #ff0000;'>$total_avaliacoes_adicionadas</strong> avaliações falsas aos produtos.</p>";
    echo "<p><a href='index.php' style='color: #ff0000; text-decoration: none; border: 1px solid #ff0000; padding: 10px 20px; display: inline-block; margin-top: 20px;'>← Voltar para a página inicial</a></p>";
    echo "</body></html>";
    
} catch (PDOException $e) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Erro</title></head><body style='font-family: Arial; padding: 20px; background: #000; color: #fff;'>";
    echo "<h1 style='color: #ff0000;'>❌ Erro</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='index.php' style='color: #ff0000;'>Voltar</a></p>";
    echo "</body></html>";
}
?>

