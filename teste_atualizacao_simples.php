<?php
// teste_atualizacao_simples.php - Teste Simples de Atualização
session_start();

// Simula um carrinho para teste
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [
        1 => [
            'id' => 1,
            'nome' => 'Produto Teste',
            'preco' => 29.90,
            'quantidade' => 1
        ]
    ];
}

// Processa requisição de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    // Limpa qualquer output anterior
    if (ob_get_level()) {
        ob_clean();
    }
    
    try {
        $produto_id = (int)($_POST['produto_id'] ?? 0);
        $quantidade = (int)($_POST['quantidade'] ?? 1);
        
        if ($produto_id > 0 && isset($_SESSION['carrinho'][$produto_id])) {
            $_SESSION['carrinho'][$produto_id]['quantidade'] = $quantidade;
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Quantidade atualizada para ' . $quantidade
            ]);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Produto não encontrado no carrinho'
            ]);
            exit();
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Erro: ' . $e->getMessage()
        ]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Simples de Atualização</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">🧪 Teste Simples de Atualização</h1>
        
        <div class="bg-gray-800 p-6 rounded-lg mb-6">
            <h2 class="text-xl font-bold mb-4">Carrinho Atual</h2>
            <pre class="text-green-400 text-sm overflow-x-auto"><?php print_r($_SESSION['carrinho']); ?></pre>
        </div>
        
        <div class="bg-gray-800 p-6 rounded-lg mb-6">
            <h2 class="text-xl font-bold mb-4">Teste de Atualização</h2>
            <form method="POST" class="space-y-4" onsubmit="return updateQuantity(this)">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="produto_id" value="1">
                
                <div>
                    <label class="block text-sm mb-2">Quantidade:</label>
                    <input type="number" name="quantidade" value="<?= $_SESSION['carrinho'][1]['quantidade'] ?>" 
                           min="1" max="99" 
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2 focus:border-blue-500 focus:outline-none" 
                           style="color: white !important; background-color: #1f2937 !important; border-color: #4b5563 !important;">
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">
                    Atualizar Quantidade
                </button>
            </form>
        </div>
        
        <div class="bg-gray-800 p-6 rounded-lg">
            <h2 class="text-xl font-bold mb-4">Log de Testes</h2>
            <div id="log" class="text-sm text-gray-300 space-y-2 max-h-96 overflow-y-auto">
                <p>Clique em "Atualizar Quantidade" para testar...</p>
            </div>
        </div>
    </div>

    <script>
    function updateQuantity(form) {
        const quantidade = form.querySelector('input[name="quantidade"]').value;
        
        // Validação
        if (quantidade < 1 || quantidade > 99) {
            alert('Quantidade deve ser entre 1 e 99');
            return false;
        }
        
        // Mostra loading
        const button = form.querySelector('button[type="submit"]');
        const originalText = button.textContent;
        button.textContent = 'Atualizando...';
        button.disabled = true;
        
        // Log
        const log = document.getElementById('log');
        log.innerHTML += '<p class="text-yellow-400">🔄 Enviando requisição...</p>';
        
        // Envia via AJAX
        const formData = new FormData(form);
        
        fetch('teste_atualizacao_simples.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            log.innerHTML += '<p class="text-blue-400">📡 Resposta recebida: ' + response.status + '</p>';
            
            // Verifica se a resposta é JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Se não for JSON, lê como texto para debug
                return response.text().then(text => {
                    log.innerHTML += '<p class="text-red-400">❌ Resposta não é JSON: ' + text.substring(0, 200) + '...</p>';
                    throw new Error('Resposta não é JSON');
                });
            }
        })
        .then(data => {
            log.innerHTML += '<p class="text-green-400">✅ Resposta JSON: ' + JSON.stringify(data) + '</p>';
            
            if (data.success) {
                log.innerHTML += '<p class="text-green-400">✅ ' + data.message + '</p>';
                // Recarrega a página para mostrar as mudanças
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                log.innerHTML += '<p class="text-red-400">❌ ' + data.message + '</p>';
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            log.innerHTML += '<p class="text-red-400">❌ Erro: ' + error.message + '</p>';
            button.textContent = originalText;
            button.disabled = false;
        });
        
        return false; // Previne o submit normal
    }
    </script>
</body>
</html>
