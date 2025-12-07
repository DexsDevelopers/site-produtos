<?php
// cache_otimizado.php - Sistema de Cache Otimizado
class CacheOtimizado {
    private $cache_dir;
    private $cache_time;
    
    public function __construct($cache_dir = 'cache/', $cache_time = 3600) {
        $this->cache_dir = $cache_dir;
        $this->cache_time = $cache_time;
        
        // Cria diretório de cache se não existir
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    public function get($key) {
        $filename = $this->cache_dir . md5($key) . '.cache';
        
        if (file_exists($filename) && (time() - filemtime($filename)) < $this->cache_time) {
            return unserialize(file_get_contents($filename));
        }
        
        return false;
    }
    
    public function set($key, $data) {
        $filename = $this->cache_dir . md5($key) . '.cache';
        return file_put_contents($filename, serialize($data));
    }
    
    public function delete($key) {
        $filename = $this->cache_dir . md5($key) . '.cache';
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return false;
    }
    
    public function clear() {
        $files = glob($this->cache_dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    public function getStats() {
        $files = glob($this->cache_dir . '*.cache');
        $total_size = 0;
        $total_files = count($files);
        
        foreach ($files as $file) {
            $total_size += filesize($file);
        }
        
        return [
            'total_files' => $total_files,
            'total_size' => $total_size,
            'total_size_mb' => round($total_size / 1024 / 1024, 2)
        ];
    }
}

// Função para obter dados com cache
function getCachedData($key, $callback, $cache_time = 3600) {
    $cache = new CacheOtimizado('cache/', $cache_time);
    
    $data = $cache->get($key);
    
    if ($data === false) {
        $data = $callback();
        $cache->set($key, $data);
    }
    
    return $data;
}

// Função para limpar cache
function clearCache() {
    $cache = new CacheOtimizado();
    return $cache->clear();
}

// Função para obter estatísticas do cache
function getCacheStats() {
    $cache = new CacheOtimizado();
    return $cache->getStats();
}
?>
