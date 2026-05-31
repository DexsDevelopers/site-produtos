<?php
// includes/meta_capi.php - Integração Server-Side Meta Conversions API (CAPI)

class MetaCAPI {
    private $pdo;
    private $pixelId = '';
    private $accessToken = '';
    private $apiVersion = 'v18.0';

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadConfig();
    }

    private function loadConfig() {
        try {
            $stmt = $this->pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('meta_pixel_id', 'meta_capi_token')");
            $config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $this->pixelId = $config['meta_pixel_id'] ?? '';
            $this->accessToken = $config['meta_capi_token'] ?? '';
        } catch (Exception $e) {
            // Ignore database errors
        }
    }

    public function isConfigured() {
        return !empty($this->pixelId) && !empty($this->accessToken);
    }

    /**
     * Envia evento para a API de Conversões do Facebook
     */
    public function sendEvent($eventName, $eventId, $customData = [], $userData = []) {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Meta CAPI não configurado'];
        }

        $hashedUserData = [];

        // Captura IP e User Agent reais
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        $hashedUserData['client_ip_address'] = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
        $hashedUserData['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Criptografia SHA256 dos campos de dados pessoais do usuário
        foreach ($userData as $key => $val) {
            if (empty($val)) continue;
            
            // Campos que precisam de hash SHA256 conforme API do Facebook
            if (in_array($key, ['em', 'ph', 'fn', 'ln', 'ct', 'st', 'zp', 'country'])) {
                $cleanVal = strtolower(trim($val));
                if ($key === 'ph') {
                    // telefone precisa estar no formato E.164 (ex: 5551999999999)
                    $cleanVal = preg_replace('/\D/', '', $cleanVal);
                    if (strlen($cleanVal) === 11 && substr($cleanVal, 0, 2) !== '55') {
                        $cleanVal = '55' . $cleanVal;
                    }
                }
                $hashedUserData[$key] = hash('sha256', $cleanVal);
            } else {
                $hashedUserData[$key] = $val;
            }
        }

        $payload = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'event_time' => time(),
                    'event_id' => $eventId,
                    'event_source_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/'),
                    'action_source' => 'website',
                    'user_data' => $hashedUserData,
                    'custom_data' => $customData
                ]
            ]
        ];

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->pixelId}/events?access_token=" . urlencode($this->accessToken);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 10
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("Meta CAPI cURL Error: " . $err);
            return ['success' => false, 'message' => $err];
        }

        $resDecoded = json_decode($response, true);
        if (isset($resDecoded['error'])) {
            error_log("Meta CAPI API Error: " . json_encode($resDecoded['error']));
            return ['success' => false, 'error' => $resDecoded['error']];
        }

        return ['success' => true, 'response' => $resDecoded];
    }
}
