<?php
// debug_atualizacao.php - Debug Detalhado da Atualização
session_start();
require_once 'config.php';

// Simula um carrinho para teste
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [
        1 => [
            'id' => 1,
            'nome' => 'Produto Teste',
            'preco' => 29.90,
            'imagem' => 'produto_teste.jpg',
            'checkout_link' => 'https://pagbank.com/teste',
            'quantidade' => 1
        ]
    ];
}

// Processa requisição de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $produto_id = (int)($_POST['produto_id'] ?? 0);
        $quantidade = (int)($_POST['quantidade'] ?? 1);
        
        // Log dos dados recebidos
        error_log("DEBUG: produto_id = $produto_id, quantidade = $quantidade");
        error_log("DEBUG: carrinho antes = " . print_r($_SESSION['carrinho'], true));
        
        if ($produto_id > 0 && isset($_SESSION['carrinho'][$produto_id])) {
            $_SESSION['carrinho'][$produto_id]['quantidade'] = $quantidade;
            
            error_log("DEBUG: carrinho depois = " . print_r($_SESSION['carrinho'], true));
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Quantidade atualizada para ' . $quantidade,
                'debug' => [
                    'produto_id' => $produto_id,
                    'quantidade' => $quantidade,
                    'carrinho' => $_SESSION['carrinho']
                ]
            ]);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Produto não encontrado no carrinho',
                'debug' => [
                    'produto_id' => $produto_id,
                    'quantidade' => $quantidade,
                    'carrinho' => $_SESSION['carrinho']
                ]
            ]);
            exit();
        }
    } catch (Exception $e) {
        error_log("DEBUG: Erro = " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Erro: ' . $e->getMessage(),
            'debug' => [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]
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
    <title>Debug Atualização</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">🔍 Debug Detalhado da Atualização</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Carrinho Atual -->
            <div class="bg-gray-800 p-6 rounded-lg">
                <h2 class="text-xl font-bold mb-4">Carrinho Atual</h2>
                <pre class="text-green-400 text-sm overflow-x-auto"><?php print_r($_SESSION['carrinho']); ?></pre>
            </div>
            
            <!-- Teste de Atualização -->
            <div class="bg-gray-800 p-6 rounded-lg">
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
        </div>
        
        <!-- Log de Testes -->
        <div class="bg-gray-800 p-6 rounded-lg mt-6">
            <h2 class="text-xl font-bold mb-4">Log de Testes</h2>
            <div id="log" class="text-sm text-gray-300 space-y-2 max-h-96 overflow-y-auto">
                <p>Clique em "Atualizar Quantidade" para testar...</p>
            </div>
        </div>
        
        <!-- Informações do Sistema -->
        <div class="bg-gray-800 p-6 rounded-lg mt-6">
            <h2 class="text-xl font-bold mb-4">Informações do Sistema</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <strong>PHP Version:</strong> <?= PHP_VERSION ?><br>
                    <strong>Session ID:</strong> <?= session_id() ?><br>
                    <strong>Request Method:</strong> <?= $_SERVER['REQUEST_METHOD'] ?>
                </div>
                <div>
                    <strong>POST Data:</strong><br>
                    <pre class="text-green-400"><?php print_r($_POST); ?></pre>
                </div>
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
        log.innerHTML += '<p class="text-gray-400">Dados: produto_id=1, quantidade=' + quantidade + '</p>';
        
        // Envia via AJAX
        const formData = new FormData(form);
        
        fetch('debug_atualizacao.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            log.innerHTML += '<p class="text-blue-400">📡 Resposta recebida: ' + response.status + '</p>';
            if (!response.ok) {
                throw new Error('Erro de rede: ' + response.status);
            }
            return response.json();
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
                if (data.debug) {
                    log.innerHTML += '<p class="text-gray-400">Debug: ' + JSON.stringify(data.debug) + '</p>';
                }
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
