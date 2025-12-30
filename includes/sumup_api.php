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
        
        // Prepara dados do checkout
        $data = [
            'merchant_code' => $this->merchant_code,
            'amount' => (float)$amount,
            'currency' => $currency,
            'checkout_reference' => $checkout_reference
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
    public function saveCredentials($api_key, $merchant_code) {
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
            
            // Salva API Key
            $stmt = $this->pdo->prepare("
                INSERT INTO config (config_key, config_value)
                VALUES ('sumup_api_key', ?)
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$api_key]);
            
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
            $stmt = $this->pdo->prepare("SELECT config_value FROM config WHERE config_key = ?");
            
            $stmt->execute(['sumup_api_key']);
            $api_key_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $api_key = $api_key_row ? $api_key_row['config_value'] : '';
            
            $stmt->execute(['sumup_merchant_code']);
            $merchant_row = $stmt->fetch(PDO::FETCH_ASSOC);
            $merchant_code = $merchant_row ? $merchant_row['config_value'] : '';
            
            return [
                'api_key' => $api_key,
                'merchant_code' => $merchant_code
            ];
        } catch (PDOException $e) {
            error_log("Erro ao obter credenciais SumUp: " . $e->getMessage());
            return [
                'api_key' => '',
                'merchant_code' => ''
            ];
        }
    }
}

