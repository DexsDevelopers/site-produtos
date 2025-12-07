<?php
// includes/payment_gateways.php - Sistema de Múltiplos Gateways de Pagamento

class PaymentGateways {
    private $pdo;
    private $gateways = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->initializeGateways();
    }
    
    // Inicializar gateways disponíveis
    private function initializeGateways() {
        $this->gateways = [
            'pagbank' => [
                'name' => 'PagBank',
                'icon' => 'fas fa-credit-card',
                'color' => '#00A651',
                'enabled' => true,
                'config' => [
                    'token' => 'SEU_TOKEN_PAGBANK',
                    'sandbox' => true
                ]
            ],
            'pagseguro' => [
                'name' => 'PagSeguro',
                'icon' => 'fas fa-shield-alt',
                'color' => '#FFC107',
                'enabled' => true,
                'config' => [
                    'email' => 'SEU_EMAIL_PAGSEGURO',
                    'token' => 'SEU_TOKEN_PAGSEGURO',
                    'sandbox' => true
                ]
            ],
            'mercadopago' => [
                'name' => 'Mercado Pago',
                'icon' => 'fab fa-cc-visa',
                'color' => '#009EE3',
                'enabled' => true,
                'config' => [
                    'public_key' => 'SUA_PUBLIC_KEY_MERCADOPAGO',
                    'access_token' => 'SEU_ACCESS_TOKEN_MERCADOPAGO',
                    'sandbox' => true
                ]
            ],
            'paypal' => [
                'name' => 'PayPal',
                'icon' => 'fab fa-paypal',
                'color' => '#0070BA',
                'enabled' => false,
                'config' => [
                    'client_id' => 'SEU_CLIENT_ID_PAYPAL',
                    'client_secret' => 'SEU_CLIENT_SECRET_PAYPAL',
                    'sandbox' => true
                ]
            ],
            'pix' => [
                'name' => 'PIX',
                'icon' => 'fas fa-qrcode',
                'color' => '#32BCAD',
                'enabled' => true,
                'config' => [
                    'chave_pix' => 'SUA_CHAVE_PIX',
                    'banco' => 'Banco do Brasil'
                ]
            ],
            'boleto' => [
                'name' => 'Boleto Bancário',
                'icon' => 'fas fa-file-invoice',
                'color' => '#6B7280',
                'enabled' => true,
                'config' => [
                    'banco' => 'Banco do Brasil',
                    'agencia' => '1234',
                    'conta' => '12345-6'
                ]
            ]
        ];
    }
    
    // Obter gateways disponíveis
    public function getAvailableGateways() {
        return array_filter($this->gateways, function($gateway) {
            return $gateway['enabled'];
        });
    }
    
    // Processar pagamento
    public function processPayment($gateway, $paymentData) {
        if (!isset($this->gateways[$gateway]) || !$this->gateways[$gateway]['enabled']) {
            return ['success' => false, 'message' => 'Gateway não disponível'];
        }
        
        switch ($gateway) {
            case 'pagbank':
                return $this->processPagBankPayment($paymentData);
            case 'pagseguro':
                return $this->processPagSeguroPayment($paymentData);
            case 'mercadopago':
                return $this->processMercadoPagoPayment($paymentData);
            case 'paypal':
                return $this->processPayPalPayment($paymentData);
            case 'pix':
                return $this->processPixPayment($paymentData);
            case 'boleto':
                return $this->processBoletoPayment($paymentData);
            default:
                return ['success' => false, 'message' => 'Gateway não suportado'];
        }
    }
    
    // Processar pagamento PagBank
    private function processPagBankPayment($data) {
        try {
            $config = $this->gateways['pagbank']['config'];
            
            // Dados do pagamento
            $paymentData = [
                'reference_id' => $data['order_id'],
                'description' => $data['description'],
                'amount' => [
                    'value' => number_format($data['amount'], 2, '.', ''),
                    'currency' => 'BRL'
                ],
                'payment_method' => [
                    'type' => $data['payment_method'],
                    'installments' => $data['installments'] ?? 1,
                    'capture' => true
                ],
                'customer' => [
                    'name' => $data['customer']['name'],
                    'email' => $data['customer']['email'],
                    'tax_id' => $data['customer']['tax_id']
                ]
            ];
            
            // Adicionar dados específicos do cartão se necessário
            if ($data['payment_method'] === 'CREDIT_CARD') {
                $paymentData['payment_method']['card'] = [
                    'number' => $data['card']['number'],
                    'exp_month' => $data['card']['exp_month'],
                    'exp_year' => $data['card']['exp_year'],
                    'security_code' => $data['card']['cvv'],
                    'holder' => [
                        'name' => $data['card']['holder_name']
                    ]
                ];
            }
            
            // Fazer requisição para API do PagBank
            $response = $this->makeApiRequest(
                'https://api.pagseguro.com/charges',
                $paymentData,
                $config['token']
            );
            
            if ($response['success']) {
                // Salvar transação no banco
                $this->saveTransaction($data['order_id'], 'pagbank', $response['data']);
                return ['success' => true, 'data' => $response['data']];
            } else {
                return ['success' => false, 'message' => $response['message']];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro no processamento: ' . $e->getMessage()];
        }
    }
    
    // Processar pagamento PagSeguro
    private function processPagSeguroPayment($data) {
        try {
            $config = $this->gateways['pagseguro']['config'];
            
            // Implementar lógica do PagSeguro
            $params = [
                'email' => $config['email'],
                'token' => $config['token'],
                'currency' => 'BRL',
                'itemId1' => $data['order_id'],
                'itemDescription1' => $data['description'],
                'itemAmount1' => number_format($data['amount'], 2, '.', ''),
                'itemQuantity1' => 1,
                'reference' => $data['order_id'],
                'senderName' => $data['customer']['name'],
                'senderEmail' => $data['customer']['email'],
                'senderCPF' => $data['customer']['tax_id']
            ];
            
            // Fazer requisição para API do PagSeguro
            $response = $this->makeApiRequest(
                'https://ws.pagseguro.uol.com.br/v2/checkout',
                $params,
                null,
                'POST',
                'application/x-www-form-urlencoded'
            );
            
            if ($response['success']) {
                $this->saveTransaction($data['order_id'], 'pagseguro', $response['data']);
                return ['success' => true, 'data' => $response['data']];
            } else {
                return ['success' => false, 'message' => $response['message']];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro no processamento: ' . $e->getMessage()];
        }
    }
    
    // Processar pagamento Mercado Pago
    private function processMercadoPagoPayment($data) {
        try {
            $config = $this->gateways['mercadopago']['config'];
            
            $paymentData = [
                'transaction_amount' => $data['amount'],
                'description' => $data['description'],
                'payment_method_id' => $data['payment_method'],
                'payer' => [
                    'email' => $data['customer']['email'],
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $data['customer']['tax_id']
                    ]
                ],
                'external_reference' => $data['order_id']
            ];
            
            $response = $this->makeApiRequest(
                'https://api.mercadopago.com/v1/payments',
                $paymentData,
                $config['access_token']
            );
            
            if ($response['success']) {
                $this->saveTransaction($data['order_id'], 'mercadopago', $response['data']);
                return ['success' => true, 'data' => $response['data']];
            } else {
                return ['success' => false, 'message' => $response['message']];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro no processamento: ' . $e->getMessage()];
        }
    }
    
    // Processar pagamento PayPal
    private function processPayPalPayment($data) {
        // Implementar lógica do PayPal
        return ['success' => false, 'message' => 'PayPal não implementado ainda'];
    }
    
    // Processar PIX
    private function processPixPayment($data) {
        try {
            $config = $this->gateways['pix']['config'];
            
            // Gerar PIX
            $pixData = [
                'chave' => $config['chave_pix'],
                'valor' => $data['amount'],
                'descricao' => $data['description'],
                'referencia' => $data['order_id']
            ];
            
            // Gerar QR Code e código PIX
            $pixCode = $this->generatePixCode($pixData);
            $qrCode = $this->generateQRCode($pixCode);
            
            $response = [
                'pix_code' => $pixCode,
                'qr_code' => $qrCode,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
            ];
            
            $this->saveTransaction($data['order_id'], 'pix', $response);
            return ['success' => true, 'data' => $response];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro no processamento: ' . $e->getMessage()];
        }
    }
    
    // Processar Boleto
    private function processBoletoPayment($data) {
        try {
            $config = $this->gateways['boleto']['config'];
            
            // Gerar boleto
            $boletoData = [
                'valor' => $data['amount'],
                'vencimento' => date('Y-m-d', strtotime('+3 days')),
                'nosso_numero' => $this->generateNossoNumero(),
                'sacado' => [
                    'nome' => $data['customer']['name'],
                    'cpf' => $data['customer']['tax_id']
                ],
                'instrucoes' => [
                    'Não receber após o vencimento',
                    'Multa de 2% após vencimento',
                    'Juros de 1% ao mês'
                ]
            ];
            
            $response = [
                'boleto_url' => $this->generateBoletoUrl($boletoData),
                'linha_digitavel' => $this->generateLinhaDigitavel($boletoData),
                'vencimento' => $boletoData['vencimento']
            ];
            
            $this->saveTransaction($data['order_id'], 'boleto', $response);
            return ['success' => true, 'data' => $response];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro no processamento: ' . $e->getMessage()];
        }
    }
    
    // Fazer requisição para API
    private function makeApiRequest($url, $data, $token = null, $method = 'POST', $contentType = 'application/json') {
        $ch = curl_init();
        
        $headers = [];
        if ($contentType === 'application/json') {
            $headers[] = 'Content-Type: application/json';
            $postData = json_encode($data);
        } else {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $postData = http_build_query($data);
        }
        
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => $method === 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'message' => 'Erro de conexão: ' . $error];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $decodedResponse];
        } else {
            return ['success' => false, 'message' => $decodedResponse['message'] ?? 'Erro na API'];
        }
    }
    
    // Salvar transação no banco
    private function saveTransaction($orderId, $gateway, $data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO transacoes_pagamento (order_id, gateway, status, dados, data_criacao) 
                VALUES (?, ?, 'pending', ?, NOW())
            ");
            $stmt->execute([$orderId, $gateway, json_encode($data)]);
        } catch (Exception $e) {
            error_log('Erro ao salvar transação: ' . $e->getMessage());
        }
    }
    
    // Gerar código PIX
    private function generatePixCode($data) {
        // Implementar geração de código PIX
        return '00020126360014BR.GOV.BCB.PIX0114' . $data['chave'] . '5204000053039865405' . 
               number_format($data['valor'], 2, '', '') . '5802BR5913' . 
               substr($data['descricao'], 0, 13) . '62070503***6304';
    }
    
    // Gerar QR Code
    private function generateQRCode($pixCode) {
        // Usar biblioteca para gerar QR Code
        return 'data:image/png;base64,' . base64_encode('QR_CODE_IMAGE_DATA');
    }
    
    // Gerar nosso número
    private function generateNossoNumero() {
        return str_pad(rand(1, 999999999), 9, '0', STR_PAD_LEFT);
    }
    
    // Gerar URL do boleto
    private function generateBoletoUrl($data) {
        return 'https://boleto.example.com/' . $data['nosso_numero'];
    }
    
    // Gerar linha digitável
    private function generateLinhaDigitavel($data) {
        return '12345.67890.12345.678901.23456.789012.3.45678901234567';
    }
    
    // Verificar status do pagamento
    public function checkPaymentStatus($orderId, $gateway) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT status, dados FROM transacoes_pagamento 
                WHERE order_id = ? AND gateway = ? 
                ORDER BY data_criacao DESC LIMIT 1
            ");
            $stmt->execute([$orderId, $gateway]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'success' => true,
                    'status' => $result['status'],
                    'data' => json_decode($result['dados'], true)
                ];
            } else {
                return ['success' => false, 'message' => 'Transação não encontrada'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao verificar status: ' . $e->getMessage()];
        }
    }
}
?>
