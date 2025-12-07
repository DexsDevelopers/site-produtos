<?php
// includes/cache.php - Sistema de Cache Simples

class SimpleCache {
    private $cache_dir;
    private $default_ttl = 3600; // 1 hora em segundos
    
    public function __construct($cache_dir = 'cache/') {
        $this->cache_dir = $cache_dir;
        
        // Cria o diretório de cache se não existir
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Armazena dados no cache
     */
    public function set($key, $data, $ttl = null) {
        $ttl = $ttl ?? $this->default_ttl;
        $cache_file = $this->getCacheFile($key);
        
        $cache_data = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($cache_file, serialize($cache_data)) !== false;
    }
    
    /**
     * Recupera dados do cache
     */
    public function get($key) {
        $cache_file = $this->getCacheFile($key);
        
        if (!file_exists($cache_file)) {
            return null;
        }
        
        $cache_data = unserialize(file_get_contents($cache_file));
        
        if (!$cache_data || time() > $cache_data['expires']) {
            $this->delete($key);
            return null;
        }
        
        return $cache_data['data'];
    }
    
    /**
     * Verifica se uma chave existe no cache e não expirou
     */
    public function has($key) {
        return $this->get($key) !== null;
    }
    
    /**
     * Remove uma chave do cache
     */
    public function delete($key) {
        $cache_file = $this->getCacheFile($key);
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
        return true;
    }
    
    /**
     * Limpa todo o cache
     */
    public function clear() {
        $files = glob($this->cache_dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    /**
     * Gera o nome do arquivo de cache
     */
    private function getCacheFile($key) {
        return $this->cache_dir . md5($key) . '.cache';
    }
    
    /**
     * Limpa arquivos expirados
     */
    public function cleanExpired() {
        $files = glob($this->cache_dir . '*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $cache_data = unserialize(file_get_contents($file));
            if ($cache_data && time() > $cache_data['expires']) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}

// Instância global do cache
$cache = new SimpleCache();
?>
