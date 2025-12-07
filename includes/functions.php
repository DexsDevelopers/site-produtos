<?php
// includes/functions.php - Funções Auxiliares da Loja

/**
 * Formata um preço para exibição
 */
function formatarPrecoExibicao($preco) {
    if (!is_numeric($preco)) { 
        return 'R$ 0,00'; 
    }
    return 'R$ ' . number_format((float)$preco, 2, ',', '.');
}

/**
 * Formata um preço para APIs de pagamento
 */
function formatarPrecoAPI($preco) {
    if (!is_numeric($preco)) { 
        return '0.00'; 
    }
    return number_format((float)$preco, 2, '.', '');
}

/**
 * Gera uma URL amigável a partir de um texto
 */
function gerarSlug($texto) {
    $texto = strtolower(trim($texto));
    $texto = preg_replace('/[^a-z0-9-]/', '-', $texto);
    $texto = preg_replace('/-+/', '-', $texto);
    return trim($texto, '-');
}

/**
 * Valida se um email é válido
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida se uma senha é forte o suficiente
 */
function validarSenha($senha) {
    return strlen($senha) >= 6;
}

/**
 * Gera um token CSRF
 */
function gerarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica se um token CSRF é válido
 */
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitiza dados de entrada
 */
function sanitizarEntrada($dados) {
    if (is_array($dados)) {
        return array_map('sanitizarEntrada', $dados);
    }
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}

/**
 * Redireciona com mensagem
 */
function redirecionarComMensagem($url, $tipo, $mensagem) {
    $_SESSION[$tipo . '_message'] = $mensagem;
    header("Location: $url");
    exit();
}

/**
 * Exibe e remove uma mensagem da sessão
 */
function exibirMensagem($tipo) {
    if (isset($_SESSION[$tipo . '_message'])) {
        $mensagem = $_SESSION[$tipo . '_message'];
        unset($_SESSION[$tipo . '_message']);
        return $mensagem;
    }
    return null;
}

/**
 * Gera um hash seguro para senhas
 */
function hashSenha($senha) {
    return password_hash($senha, PASSWORD_DEFAULT);
}

/**
 * Verifica se uma senha confere com o hash
 */
function verificarSenha($senha, $hash) {
    return password_verify($senha, $hash);
}

/**
 * Gera um nome único para upload de arquivos
 */
function gerarNomeUnico($arquivo_original) {
    $extensao = pathinfo($arquivo_original, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extensao;
}

/**
 * Valida se um arquivo é uma imagem válida
 */
function validarImagem($arquivo) {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $tamanho_maximo = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($arquivo['type'], $tipos_permitidos)) {
        return false;
    }
    
    if ($arquivo['size'] > $tamanho_maximo) {
        return false;
    }
    
    return true;
}

/**
 * Calcula o total de itens no carrinho
 */
function calcularTotalCarrinho() {
    if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
        return ['itens' => 0, 'preco' => 0];
    }
    
    $total_itens = 0;
    $total_preco = 0;
    
    foreach ($_SESSION['carrinho'] as $item) {
        $total_itens += $item['quantidade'];
        $total_preco += $item['preco'] * $item['quantidade'];
    }
    
    return ['itens' => $total_itens, 'preco' => $total_preco];
}

/**
 * Formata data para exibição
 */
function formatarData($data, $formato = 'd/m/Y H:i') {
    if (is_string($data)) {
        $data = new DateTime($data);
    }
    return $data->format($formato);
}

/**
 * Gera breadcrumbs para navegação
 */
function gerarBreadcrumbs($pagina_atual, $itens = []) {
    $breadcrumbs = '<nav class="flex items-center space-x-2 text-sm text-brand-gray-text mb-6">';
    $breadcrumbs .= '<a href="index.php" class="hover:text-brand-red transition-colors">Início</a>';
    
    foreach ($itens as $item) {
        $breadcrumbs .= '<span class="text-gray-500">/</span>';
        if (isset($item['url'])) {
            $breadcrumbs .= '<a href="' . $item['url'] . '" class="hover:text-brand-red transition-colors">' . $item['nome'] . '</a>';
        } else {
            $breadcrumbs .= '<span class="text-white">' . $item['nome'] . '</span>';
        }
    }
    
    $breadcrumbs .= '</nav>';
    return $breadcrumbs;
}

/**
 * Limita o texto a um número específico de caracteres
 */
function limitarTexto($texto, $limite = 100, $sufixo = '...') {
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    return substr($texto, 0, $limite) . $sufixo;
}

/**
 * Gera uma cor aleatória para avatars
 */
function gerarCorAvatar($nome) {
    $cores = [
        '#E53E3E', '#DD6B20', '#D69E2E', '#38A169', 
        '#319795', '#3182CE', '#553C9A', '#D53F8C'
    ];
    
    $indice = crc32($nome) % count($cores);
    return $cores[$indice];
}

/**
 * Verifica se o usuário está logado
 */
function usuarioLogado() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtém dados do usuário logado
 */
function obterUsuarioLogado() {
    if (!usuarioLogado()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'nome' => $_SESSION['user_nome'] ?? '',
        'email' => $_SESSION['user_email'] ?? ''
    ];
}

/**
 * Força login do usuário
 */
function exigirLogin($redirecionar_para = 'login.php') {
    if (!usuarioLogado()) {
        redirecionarComMensagem($redirecionar_para, 'error', 'Você precisa estar logado para acessar esta página.');
    }
}

/**
 * Gera um código de verificação
 */
function gerarCodigoVerificacao($tamanho = 6) {
    return str_pad(random_int(0, pow(10, $tamanho) - 1), $tamanho, '0', STR_PAD_LEFT);
}

/**
 * Envia email (função básica - em produção use PHPMailer ou similar)
 */
function enviarEmail($para, $assunto, $mensagem, $de = 'noreply@minhaloja.com') {
    $headers = "From: $de\r\n";
    $headers .= "Reply-To: $de\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($para, $assunto, $mensagem, $headers);
}

/**
 * Log de atividades do sistema
 */
function logAtividade($usuario_id, $acao, $detalhes = '') {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'usuario_id' => $usuario_id,
        'acao' => $acao,
        'detalhes' => $detalhes,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    $log_file = 'logs/atividades.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}
?>
