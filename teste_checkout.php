<?php
// teste_checkout.php - Teste de debug para checkout
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "Teste iniciado...<br>";

try {
    echo "1. Iniciando sessão...<br>";
    session_start();
    echo "✓ Sessão iniciada<br>";
    
    echo "2. Carregando config.php...<br>";
    require_once 'config.php';
    echo "✓ config.php carregado<br>";
    
    echo "3. Verificando carrinho...<br>";
    if (empty($_SESSION['carrinho'])) {
        echo "⚠ Carrinho vazio<br>";
    } else {
        echo "✓ Carrinho tem " . count($_SESSION['carrinho']) . " itens<br>";
    }
    
    echo "4. Carregando FileStorage...<br>";
    if (file_exists(__DIR__ . '/includes/file_storage.php')) {
        require_once __DIR__ . '/includes/file_storage.php';
        $fileStorage = new FileStorage();
        echo "✓ FileStorage carregado<br>";
    } else {
        echo "⚠ FileStorage não encontrado<br>";
    }
    
    echo "5. Carregando SumUpAPI...<br>";
    if (file_exists(__DIR__ . '/includes/sumup_api.php')) {
        require_once __DIR__ . '/includes/sumup_api.php';
        if (class_exists('SumUpAPI')) {
            $sumup = new SumUpAPI($pdo);
            echo "✓ SumUpAPI carregado<br>";
        } else {
            echo "⚠ Classe SumUpAPI não encontrada<br>";
        }
    } else {
        echo "⚠ sumup_api.php não encontrado<br>";
    }
    
    echo "<br><strong>Teste concluído com sucesso!</strong>";
    
} catch (Throwable $e) {
    echo "<br><strong style='color:red'>ERRO:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
}

