<?php
// track_affiliate.php - Rastrear cliques de afiliados
session_start();
require_once 'config.php';
require_once 'includes/affiliate_system.php';

// Verificar se há código de afiliado na URL
$affiliate_code = $_GET['ref'] ?? null;

if ($affiliate_code) {
    $affiliateSystem = new AffiliateSystem($pdo);
    
    // Registrar clique
    $product_id = $_GET['id'] ?? null; // Se estiver em uma página de produto
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    $result = $affiliateSystem->registerClick($affiliate_code, $product_id, $ip_address);
    
    if ($result['success']) {
        // Salvar na sessão para tracking posterior
        $_SESSION['affiliate_tracking'] = [
            'affiliate_code' => $affiliate_code,
            'click_id' => $result['click_id'],
            'timestamp' => time()
        ];
    }
}

// Redirecionar para a página original sem o parâmetro ref
$redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
if (strpos($redirect_url, '?') !== false) {
    $redirect_url = strtok($redirect_url, '?');
}

// Remover parâmetros de afiliado da URL
$params = $_GET;
unset($params['ref']);

if (!empty($params)) {
    $redirect_url .= '?' . http_build_query($params);
}

header('Location: ' . $redirect_url);
exit();
?>
