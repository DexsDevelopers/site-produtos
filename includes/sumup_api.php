<?php
// includes/sumup_api.php - Integração com API SumUp

class SumUpAPI {
    private $api_key;
    private $merchant_code;
    private $api_url = 'https://api.sumup.com/v0.1';
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadCredentials();
    }
    
    /**
     * Carrega credenciais da SumUp do banco de dados
     */
    private function loadCredentials() {
        try {
            // Garante que a tabela config existe antes de consultar
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    config_key VARCHAR(255) UNIQUE NOT NULL,
                    config_value TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_config_key (config_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            $stmt = $this->pdo->prepare("SELECT config_value FROM config WHERE config_key = ?");
            
            $stmt->execute(['sumup_api_key']);
            $api_key_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->api_key = $api_key_row ? $api_key_row['config_value'] : '';
            
            $stmt->execute(['sumup_merchant_code']);
            $merchant_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->merchant_code = $merchant_row ? $merchant_row['config_value'] : '';
        } catch (PDOException $e) {
            error_log("Erro ao carregar credenciais SumUp: " . $e->getMessage());
            $this->api_key = '';
            $this->merchant_code = '';
        }
    }
    
    /**
     * Verifica se a SumUp está configurada
     */
    public function isConfigured() {
        return !empty($this->api_key) && !empty($this->merchant_code);
    }
    
    /**
     * Cria um checkout na SumUp
     * @param float $amount Valor do pagamento
     * @param string $currency Moeda (BRL, USD, etc)
     * @param string $checkout_reference Referência única do checkout
     * @param array $customer Dados do cliente (nome, email, telefone)
     * @return array Resultado da criação do checkout
     */
    public function createCheckout($amount, $currency = 'BRL', $checkout_reference = null, $customer = []) {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SumUp não está configurada. Configure as credenciais no painel administrativo.'
            ];
        }
        
        // Gera referência única se não fornecida
        if (empty($checkout_reference)) {
            $checkout_reference = 'CHECKOUT_' . time() . '_' . uniqid();
        }
        
        // Prepara dados do checkout conforme documentação SumUp
        // POST https://api.sumup.com/v0.1/checkouts
        $data = [
            'amount' => (float)$amount,
            'currency' => $currency,
            'checkout_reference' => $checkout_reference,
            'merchant_code' => $this->merchant_code
        ];
        
        // Adiciona dados do cliente se fornecidos
        if (!empty($customer['email'])) {
            $data['customer_email'] = $customer['email'];
        }
        if (!empty($customer['name'])) {
            $data['customer_name'] = $customer['name'];
        }
        if (!empty($customer['phone'])) {
            $data['customer_phone'] = $customer['phone'];
        }
        
        // Faz requisição para criar checkout
        $response = $this->makeRequest('POST', '/checkouts', $data);
        
        if ($response['success'] && isset($response['data']['id'])) {
            // Salva checkout no banco para rastreamento
            $this->saveCheckout($checkout_reference, $response['data']['id'], $amount, $customer);
            
            return [
                'success' => true,
                'checkout_id' => $response['data']['id'],
                'checkout_reference' => $checkout_reference,
                'redirect_url' => $response['data']['redirect_url'] ?? null,
                'data' => $response['data']
            ];
        }
        
        return [
            'success' => false,
            'message' => $response['message'] ?? 'Erro ao criar checkout na SumUp'
        ];
    }
    
    /**
     * Verifica status de um checkout
     * @param string $checkout_id ID do checkout na SumUp
     * @return array Status do checkout
     */
    public function getCheckoutStatus($checkout_id) {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SumUp não está configurada'
            ];
        }
        
        $response = $this->makeRequest('GET', '/checkouts/' . $checkout_id);
        
        if ($response['success']) {
            return [
                'success' => true,
                'status' => $response['data']['status'] ?? 'unknown',
                'data' => $response['data']
            ];
        }
        
        return [
            'success' => false,
            'message' => $response['message'] ?? 'Erro ao verificar status do checkout'
        ];
    }
    
    /**
     * Faz requisição HTTP para a API SumUp
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->api_url . $endpoint;
        
        $ch = curl_init();
        
        $headers = [
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'Erro de conexão: ' . $error
            ];
        }
        
        $response_data = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            return [
                'success' => true,
                'data' => $response_data
            ];
        }
        
        return [
            'success' => false,
            'message' => $response_data['message'] ?? 'Erro na requisição à API SumUp',
            'http_code' => $http_code,
            'data' => $response_data
        ];
    }
    
    /**
     * Salva checkout no banco de dados para rastreamento
     */
    private function saveCheckout($checkout_reference, $checkout_id, $amount, $customer) {
        try {
            // Verifica se a tabela existe, se não, cria
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS sumup_checkouts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    checkout_reference VARCHAR(255) UNIQUE NOT NULL,
                    checkout_id VARCHAR(255) NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    customer_name VARCHAR(255),
                    customer_email VARCHAR(255),
                    customer_phone VARCHAR(50),
                    status VARCHAR(50) DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_checkout_reference (checkout_reference),
                    INDEX idx_checkout_id (checkout_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            $stmt = $this->pdo->prepare("
                INSERT INTO sumup_checkouts 
                (checkout_reference, checkout_id, amount, customer_name, customer_email, customer_phone, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
                ON DUPLICATE KEY UPDATE
                    checkout_id = VALUES(checkout_id),
                    amount = VALUES(amount),
                    customer_name = VALUES(customer_name),
                    customer_email = VALUES(customer_email),
                    customer_phone = VALUES(customer_phone),
                    updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->execute([
                $checkout_reference,
                $checkout_id,
                $amount,
                $customer['name'] ?? null,
                $customer['email'] ?? null,
                $customer['phone'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao salvar checkout SumUp: " . $e->getMessage());
        }
    }
    
    /**
     * Atualiza status de um checkout
     */
    public function updateCheckoutStatus($checkout_reference, $status) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE sumup_checkouts 
                SET status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE checkout_reference = ?
            ");
            $stmt->execute([$status, $checkout_reference]);
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status do checkout: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Salva configurações da SumUp
     */
    public function saveCredentials($api_key, $merchant_code, $api_key_public = '') {
        try {
            // Cria tabela de configurações se não existir
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    config_key VARCHAR(255) UNIQUE NOT NULL,
                    config_value TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_config_key (config_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            // Salva API Key Privada
            $stmt = $this->pdo->prepare("
                INSERT INTO config (config_key, config_value)
                VALUES ('sumup_api_key', ?)
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$api_key]);
            
            // Salva API Key Pública
            $stmt = $this->pdo->prepare("
                INSERT INTO config (config_key, config_value)
                VALUES ('sumup_api_key_public', ?)
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$api_key_public]);
            
            // Salva Merchant Code
            $stmt = $this->pdo->prepare("
                INSERT INTO config (config_key, config_value)
                VALUES ('sumup_merchant_code', ?)
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$merchant_code]);
            
            // Atualiza propriedades
            $this->api_key = $api_key;
            $this->merchant_code = $merchant_code;
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao salvar credenciais SumUp: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém credenciais salvas
     */
    public function getCredentials() {
        try {
            // Garante que a tabela existe
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    config_key VARCHAR(255) UNIQUE NOT NULL,
                    config_value TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_config_key (config_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            $stmt = $this->pdo->prepare("SELECT config_value FROM config WHERE config_key = ?");
            
            $stmt->execute(['sumup_api_key']);
            $api_key_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $api_key = $api_key_row ? $api_key_row['config_value'] : '';
            
            $stmt->execute(['sumup_api_key_public']);
            $api_key_public_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $api_key_public = $api_key_public_row ? $api_key_public_row['config_value'] : '';
            
            $stmt->execute(['sumup_merchant_code']);
            $merchant_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $merchant_code = $merchant_row ? $merchant_row['config_value'] : '';
            
            return [
                'api_key' => $api_key,
                'api_key_public' => $api_key_public,
                'merchant_code' => $merchant_code
            ];
        } catch (PDOException $e) {
            error_log("Erro ao obter credenciais SumUp: " . $e->getMessage());
            return [
                'api_key' => '',
                'api_key_public' => '',
                'merchant_code' => ''
            ];
        }
    }
    
    /**
     * Obtém API Key pública para uso no frontend
     */
    public function getPublicKey() {
        $credenciais = $this->getCredentials();
        return $credenciais['api_key_public'] ?? '';
    }
    
    /**
     * Salva configurações de métodos de pagamento
     */
    public function savePaymentMethods($pix_manual_enabled, $pix_sumup_enabled, $cartao_sumup_enabled) {
        try {
            // Cria tabela de configurações se não existir
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    config_key VARCHAR(255) UNIQUE NOT NULL,
                    config_value TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_config_key (config_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            // Salva configurações
            $stmt = $this->pdo->prepare("
                INSERT INTO config (config_key, config_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->execute(['payment_pix_manual_enabled', $pix_manual_enabled ? '1' : '0']);
            $stmt->execute(['payment_pix_sumup_enabled', $pix_sumup_enabled ? '1' : '0']);
            $stmt->execute(['payment_cartao_sumup_enabled', $cartao_sumup_enabled ? '1' : '0']);
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao salvar métodos de pagamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém configurações de métodos de pagamento
     */
    public function getPaymentMethods() {
        try {
            // Garante que a tabela config existe
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    config_key VARCHAR(255) UNIQUE NOT NULL,
                    config_value TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_config_key (config_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            $stmt = $this->pdo->prepare("SELECT config_value FROM config WHERE config_key = ?");
            
            $stmt->execute(['payment_pix_manual_enabled']);
            $pix_manual_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $pix_manual_enabled = $pix_manual_row && $pix_manual_row['config_value'] === '1';
            
            $stmt->execute(['payment_pix_sumup_enabled']);
            $pix_sumup_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $pix_sumup_enabled = $pix_sumup_row && $pix_sumup_row['config_value'] === '1';
            
            $stmt->execute(['payment_cartao_sumup_enabled']);
            $cartao_sumup_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cartao_sumup_enabled = $cartao_sumup_row && $cartao_sumup_row['config_value'] === '1';
            
            // Se não houver configurações, define padrões
            if (!$pix_manual_row && !$pix_sumup_row && !$cartao_sumup_row) {
                $pix_manual_enabled = true; // PIX manual ativado por padrão
                $pix_sumup_enabled = false;
                $cartao_sumup_enabled = false;
            }
            
            return [
                'pix_manual_enabled' => $pix_manual_enabled,
                'pix_sumup_enabled' => $pix_sumup_enabled,
                'cartao_sumup_enabled' => $cartao_sumup_enabled
            ];
        } catch (PDOException $e) {
            error_log("Erro ao obter métodos de pagamento: " . $e->getMessage());
            // Retorna padrões em caso de erro
            return [
                'pix_manual_enabled' => true,
                'pix_sumup_enabled' => false,
                'cartao_sumup_enabled' => false
            ];
        }
    }
    
    /**
     * Cria checkout PIX via SumUp
     */
    public function createPixCheckout($amount, $currency = 'BRL', $checkout_reference = null, $customer = []) {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SumUp não está configurada.'
            ];
        }
        
        // Gera referência única se não fornecida
        if (empty($checkout_reference)) {
            $checkout_reference = 'PIX_' . time() . '_' . uniqid();
        }
        
        // Prepara dados do checkout PIX conforme documentação SumUp
        // POST https://api.sumup.com/v0.1/checkouts
        // Para PIX, a SumUp retorna o código em um objeto 'pix' com 'artefacts'
        $data = [
            'amount' => (float)$amount,
            'currency' => $currency,
            'checkout_reference' => $checkout_reference,
            'merchant_code' => $this->merchant_code
        ];
        
        // Adiciona dados do cliente se fornecidos
        if (!empty($customer['email'])) {
            $data['customer_email'] = $customer['email'];
        }
        if (!empty($customer['name'])) {
            $data['customer_name'] = $customer['name'];
        }
        if (!empty($customer['phone'])) {
            $data['customer_phone'] = $customer['phone'];
        }
        
        // Faz requisição para criar checkout PIX
        $response = $this->makeRequest('POST', '/checkouts', $data);
        
        if ($response['success'] && isset($response['data']['id'])) {
            $checkout_id = $response['data']['id'];
            
            // Salva checkout no banco para rastreamento
            $this->saveCheckout($checkout_reference, $checkout_id, $amount, $customer);
            
            // Tenta obter detalhes completos do checkout (pode conter código PIX)
            // Nota: O objeto pix pode não estar na resposta inicial, mas sim nos detalhes
            $details_response = $this->getCheckoutStatus($checkout_id);
            $checkout_details = $details_response['success'] ? $details_response['data'] : $response['data'];
            
            // Se não encontrou nos detalhes, tenta na resposta original também
            if (!isset($checkout_details['pix']) && isset($response['data']['pix'])) {
                $checkout_details['pix'] = $response['data']['pix'];
            }
            
            // Log para debug - mostra toda a estrutura da resposta
            error_log("SumUp Checkout Details completa: " . json_encode($checkout_details, JSON_PRETTY_PRINT));
            error_log("SumUp Response original: " . json_encode($response['data'], JSON_PRETTY_PRINT));
            
            // A SumUp retorna o código PIX em um objeto 'pix' com 'artefacts'
            // Cada artefato tem: name (barcode/code), content_type, location, content
            $pix_code = null;
            $pix_qr_code = null;
            
            // Busca no objeto pix da resposta (pode estar em diferentes níveis)
            $pix_data = $checkout_details['pix'] ?? 
                       $checkout_details['payment_methods']['pix'] ?? 
                       $response['data']['pix'] ?? 
                       $response['data']['payment_methods']['pix'] ?? 
                       null;
            
            // Log específico para objeto pix
            if ($pix_data) {
                error_log("SumUp PIX Data encontrado: " . json_encode($pix_data, JSON_PRETTY_PRINT));
            } else {
                error_log("SumUp PIX Data NÃO encontrado. Chaves disponíveis: " . implode(', ', array_keys($checkout_details)));
            }
            
            if ($pix_data && isset($pix_data['artefacts']) && is_array($pix_data['artefacts'])) {
                foreach ($pix_data['artefacts'] as $artefact) {
                    $name = $artefact['name'] ?? '';
                    $content_type = $artefact['content_type'] ?? '';
                    $location = $artefact['location'] ?? null;
                    $content = $artefact['content'] ?? null;
                    
                    // Artefato "code" contém o código PIX em texto
                    if ($name === 'code' && $content_type === 'text/plain') {
                        // Prefere 'content' se disponível, senão busca em 'location'
                        if ($content) {
                            $pix_code = $content;
                        } elseif ($location) {
                            // Se não tiver content, faz requisição para obter do location
                            // O location pode ser uma URL completa ou um path relativo
                            if (strpos($location, 'http') === 0) {
                                // URL completa - faz requisição direta
                                $ch = curl_init();
                                curl_setopt_array($ch, [
                                    CURLOPT_URL => $location,
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_HTTPHEADER => [
                                        'Authorization: Bearer ' . $this->api_key
                                    ],
                                    CURLOPT_SSL_VERIFYPEER => true,
                                    CURLOPT_TIMEOUT => 30
                                ]);
                                $pix_response = curl_exec($ch);
                                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                curl_close($ch);
                                
                                if ($http_code >= 200 && $http_code < 300) {
                                    $pix_code = trim($pix_response);
                                }
                            } else {
                                // Path relativo - usa makeRequest
                                $pix_content_response = $this->makeRequest('GET', $location);
                                if ($pix_content_response['success']) {
                                    $pix_code = is_string($pix_content_response['data']) 
                                        ? $pix_content_response['data'] 
                                        : ($pix_content_response['data']['content'] ?? $pix_content_response['data']['code'] ?? null);
                                }
                            }
                        }
                    }
                    
                    // Artefato "barcode" contém a imagem do QR Code
                    if ($name === 'barcode' && strpos($content_type, 'image/') === 0) {
                        $pix_qr_code = $location ?? $content ?? null;
                    }
                }
            }
            
            // Log para debug
            if ($pix_data) {
                error_log("SumUp PIX Artefacts encontrados: " . json_encode($pix_data['artefacts'] ?? []));
            }
            
            // Tenta obter redirect_url de diferentes lugares
            $redirect_url = $checkout_details['redirect_url'] ?? 
                          $checkout_details['checkout_url'] ?? 
                          $checkout_details['url'] ?? 
                          $checkout_details['links']['checkout_url'] ?? 
                          $checkout_details['links']['redirect_url'] ?? 
                          $response['data']['redirect_url'] ?? 
                          $response['data']['checkout_url'] ?? 
                          $response['data']['url'] ?? 
                          $response['data']['links']['checkout_url'] ?? 
                          $response['data']['links']['redirect_url'] ?? 
                          null;
            
            // Se não encontrou redirect_url, verifica se há links na resposta
            if (!$redirect_url && isset($checkout_details['links']) && is_array($checkout_details['links'])) {
                foreach ($checkout_details['links'] as $link) {
                    if (isset($link['rel']) && ($link['rel'] === 'checkout' || $link['rel'] === 'redirect')) {
                        $redirect_url = $link['href'] ?? null;
                        if ($redirect_url) break;
                    }
                }
            }
            
            // Se ainda não encontrou, tenta usar o formato correto da SumUp
            // A SumUp usa: https://me.sumup.com/checkout/{checkout_id} ou similar
            if (!$redirect_url && $checkout_id) {
                // Não construímos URL manualmente - a SumUp deve fornecer
                // Se não forneceu, pode ser que PIX não esteja disponível via API
                error_log("SumUp não retornou redirect_url para checkout_id: " . $checkout_id);
            }
            
            return [
                'success' => true,
                'checkout_id' => $checkout_id,
                'checkout_reference' => $checkout_reference,
                'redirect_url' => $redirect_url,
                'pix_code' => $pix_code,
                'pix_qr_code' => $pix_qr_code,
                'data' => $checkout_details,
                'raw_response' => $response['data'] // Mantém resposta original para debug
            ];
        }
        
        return [
            'success' => false,
            'message' => $response['message'] ?? 'Erro ao criar checkout PIX na SumUp'
        ];
    }
}

