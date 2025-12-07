<?php
// notificacao_pagbank.php
// Este arquivo recebe notificações automáticas da PagBank sobre mudanças de status de transação.

require_once 'config.php';

// Para depuração, vamos criar um arquivo de log para registrar as notificações recebidas.
// O servidor precisa ter permissão de escrita nesta pasta.
$log_file = 'pagbank_log.txt';
$log_message = "========================================\n";
$log_message .= "Notificação recebida em: " . date("Y-m-d H:i:s") . "\n";
$log_message .= "IP de Origem: " . $_SERVER['REMOTE_ADDR'] . "\n";

// A PagBank envia os dados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['notificationCode'])) {
    
    $notificationCode = $_POST['notificationCode'];
    $notificationType = $_POST['notificationType']; // Sempre será 'transaction'

    $log_message .= "Código de Notificação: " . $notificationCode . "\n";
    $log_message .= "Tipo de Notificação: " . $notificationType . "\n";

    // Monta a URL para consultar os detalhes da notificação no ambiente correto
    $url_notificacao = (PAGSEGURO_SANDBOX ? 
        'https://ws.sandbox.pagseguro.uol.com.br/v3/transactions/notifications/' : 
        'https://ws.pagseguro.uol.com.br/v3/transactions/notifications/') . $notificationCode . '?email=' . PAGSEGURO_EMAIL . '&token=' . PAGSEGURO_TOKEN;

    // Usa cURL para consultar a PagBank e pegar os detalhes da notificação
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_notificacao);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response_xml_string = curl_exec($ch);
    curl_close($ch);

    $log_message .= "URL Consultada: " . $url_notificacao . "\n";
    $log_message .= "Resposta da API de Notificação: " . $response_xml_string . "\n";

    if ($response_xml_string) {
        try {
            $xml = new SimpleXMLElement($response_xml_string);
            
            $pedido_id = (int)$xml->reference;
            $status_code = (int)$xml->status;
            $novo_status = 'Indefinido';

            // Traduz o código de status da PagBank para o status da nossa loja
            // Referência de códigos: https://dev.pagseguro.uol.com.br/reference/api-notification#transaction-status
            switch ($status_code) {
                case 1: $novo_status = 'Aguardando Pagamento'; break;
                case 2: $novo_status = 'Em análise'; break;
                case 3: $novo_status = 'Pago'; break;
                case 4: $novo_status = 'Disponível'; break;
                case 5: $novo_status = 'Em disputa'; break;
                case 6: $novo_status = 'Devolvida'; break;
                case 7: $novo_status = 'Cancelado'; break;
            }

            $log_message .= "Pedido ID: $pedido_id | Status Code da PagBank: $status_code | Novo Status para o DB: $novo_status \n";

            // Atualiza o status do pedido no nosso banco de dados
            if ($pedido_id > 0) {
                $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
                $stmt->execute([$novo_status, $pedido_id]);
                $log_message .= "Banco de dados atualizado com sucesso para o pedido #$pedido_id.\n";
            }
        } catch (Exception $e) {
            $log_message .= "ERRO AO PROCESSAR XML DA NOTIFICAÇÃO: " . $e->getMessage() . "\n";
        }
    }
} else {
    $log_message .= "Nenhum código de notificação recebido via POST.\n";
}

// Salva tudo no arquivo de log para podermos verificar o que aconteceu
file_put_contents($log_file, $log_message, FILE_APPEND);

// Responde ao servidor da PagBank com um "OK" para que eles saibam que recebemos
http_response_code(200);
?>