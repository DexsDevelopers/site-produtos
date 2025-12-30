<?php
// includes/payment_helper.php - Helper para métodos de pagamento

require_once __DIR__ . '/sumup_api.php';

class PaymentHelper {
    private $sumup;
    
    public function __construct($pdo) {
        $this->sumup = new SumUpAPI($pdo);
    }
    
    /**
     * Obtém o URL do checkout baseado nos métodos habilitados
     * Retorna o primeiro método disponível ou null se nenhum estiver habilitado
     */
    public function getCheckoutUrl() {
        $payment_methods = $this->sumup->getPaymentMethods();
        
        // Prioridade: PIX Manual > PIX SumUp > Cartão SumUp
        if ($payment_methods['pix_manual_enabled']) {
            // Verifica se há chave PIX configurada
            require_once __DIR__ . '/file_storage.php';
            $fileStorage = new FileStorage();
            $chave_pix = $fileStorage->getChavePix();
            
            if (!empty($chave_pix)) {
                return 'checkout_pix.php';
            }
        }
        
        if ($payment_methods['pix_sumup_enabled'] && $this->sumup->isConfigured()) {
            return 'checkout_pix.php';
        }
        
        if ($payment_methods['cartao_sumup_enabled'] && $this->sumup->isConfigured()) {
            return 'checkout_pix.php';
        }
        
        return null;
    }
    
    /**
     * Verifica se há pelo menos um método de pagamento habilitado
     */
    public function hasPaymentMethod() {
        $payment_methods = $this->sumup->getPaymentMethods();
        
        if ($payment_methods['pix_manual_enabled']) {
            require_once __DIR__ . '/file_storage.php';
            $fileStorage = new FileStorage();
            $chave_pix = $fileStorage->getChavePix();
            if (!empty($chave_pix)) {
                return true;
            }
        }
        
        if ($payment_methods['pix_sumup_enabled'] && $this->sumup->isConfigured()) {
            return true;
        }
        
        if ($payment_methods['cartao_sumup_enabled'] && $this->sumup->isConfigured()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtém o texto do botão baseado nos métodos disponíveis
     */
    public function getCheckoutButtonText() {
        $payment_methods = $this->sumup->getPaymentMethods();
        $count = 0;
        
        if ($payment_methods['pix_manual_enabled']) {
            require_once __DIR__ . '/file_storage.php';
            $fileStorage = new FileStorage();
            if (!empty($fileStorage->getChavePix())) {
                $count++;
            }
        }
        
        if ($payment_methods['pix_sumup_enabled'] && $this->sumup->isConfigured()) {
            $count++;
        }
        
        if ($payment_methods['cartao_sumup_enabled'] && $this->sumup->isConfigured()) {
            $count++;
        }
        
        if ($count > 1) {
            return 'Finalizar Compra';
        } else if ($payment_methods['pix_manual_enabled']) {
            return 'Comprar Agora (PIX)';
        } else if ($payment_methods['pix_sumup_enabled']) {
            return 'Comprar Agora (PIX)';
        } else if ($payment_methods['cartao_sumup_enabled']) {
            return 'Comprar Agora (Cartão)';
        }
        
        return 'Finalizar Compra';
    }
}

