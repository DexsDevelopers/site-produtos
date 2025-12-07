<?php
// Sistema de Otimização de Performance PHP
class PerformanceOptimizer {
    private $cache_dir;
    private $compression_enabled;
    private $minification_enabled;
    
    public function __construct($cache_dir = 'cache/') {
        $this->cache_dir = $cache_dir;
        $this->compression_enabled = extension_loaded('zlib');
        $this->minification_enabled = true;
        
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Inicia otimizações de performance
     */
    public function startOptimizations() {
        // Compressão GZIP
        if ($this->compression_enabled && !ob_get_level()) {
            ob_start('ob_gzhandler');
        }
        
        // Headers de cache
        $this->setCacheHeaders();
        
        // Minificação de HTML
        if ($this->minification_enabled) {
            ob_start([$this, 'minifyHTML']);
        }
    }
    
    /**
     * Define headers de cache otimizados
     */
    private function setCacheHeaders() {
        if (!headers_sent()) {
            // Cache para recursos estáticos
            $cache_time = 3600 * 24 * 7; // 7 dias
            
            header('Cache-Control: public, max-age=' . $cache_time);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($_SERVER['SCRIPT_FILENAME'])) . ' GMT');
            
            // Headers de segurança
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
        }
    }
    
    /**
     * Minifica HTML
     */
    public function minifyHTML($buffer) {
        // Remove comentários HTML
        $buffer = preg_replace('/<!--(.|\s)*?-->/', '', $buffer);
        
        // Remove espaços em branco desnecessários
        $buffer = preg_replace('/\s+/', ' ', $buffer);
        $buffer = preg_replace('/>\s+</', '><', $buffer);
        
        // Remove quebras de linha
        $buffer = str_replace(["\r\n", "\r", "\n"], '', $buffer);
        
        return trim($buffer);
    }
    
    /**
     * Otimiza consultas SQL
     */
    public function optimizeQuery($query) {
        // Remove espaços extras
        $query = preg_replace('/\s+/', ' ', trim($query));
        
        // Adiciona LIMIT se não existir
        if (stripos($query, 'LIMIT') === false && stripos($query, 'SELECT') !== false) {
            $query .= ' LIMIT 100';
        }
        
        return $query;
    }
    
    /**
     * Sistema de cache de consultas
     */
    public function cacheQuery($key, $callback, $ttl = 3600) {
        $cache_file = $this->cache_dir . 'query_' . md5($key) . '.cache';
        
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $ttl) {
            return unserialize(file_get_contents($cache_file));
        }
        
        $result = $callback();
        file_put_contents($cache_file, serialize($result));
        
        return $result;
    }
    
    /**
     * Otimiza imagens automaticamente
     */
    public function optimizeImage($image_path, $max_width = 1200, $max_height = 1200, $quality = 85) {
        if (!file_exists($image_path)) {
            return $image_path;
        }
        
        $path_info = pathinfo($image_path);
        $cache_file = $this->cache_dir . 'img_' . md5($image_path . $max_width . $max_height . $quality) . '.' . $path_info['extension'];
        
        if (file_exists($cache_file) && filemtime($cache_file) > filemtime($image_path)) {
            return $cache_file;
        }
        
        $image_info = getimagesize($image_path);
        if (!$image_info) {
            return $image_path;
        }
        
        $original_width = $image_info[0];
        $original_height = $image_info[1];
        $mime_type = $image_info['mime'];
        
        if ($original_width <= $max_width && $original_height <= $max_height) {
            copy($image_path, $cache_file);
            return $cache_file;
        }
        
        $ratio = min($max_width / $original_width, $max_height / $original_height);
        $new_width = intval($original_width * $ratio);
        $new_height = intval($original_height * $ratio);
        
        $source_image = $this->createImageFromFile($image_path, $mime_type);
        if (!$source_image) {
            return $image_path;
        }
        
        $resized_image = imagecreatetruecolor($new_width, $new_height);
        
        if ($mime_type === 'image/png') {
            imagealphablending($resized_image, false);
            imagesavealpha($resized_image, true);
        }
        
        imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
        
        $this->saveOptimizedImage($resized_image, $cache_file, $mime_type, $quality);
        
        imagedestroy($source_image);
        imagedestroy($resized_image);
        
        return $cache_file;
    }
    
    private function createImageFromFile($file_path, $mime_type) {
        switch ($mime_type) {
            case 'image/jpeg': return imagecreatefromjpeg($file_path);
            case 'image/png': return imagecreatefrompng($file_path);
            case 'image/gif': return imagecreatefromgif($file_path);
            case 'image/webp': return imagecreatefromwebp($file_path);
            default: return false;
        }
    }
    
    private function saveOptimizedImage($image, $file_path, $mime_type, $quality) {
        switch ($mime_type) {
            case 'image/jpeg': return imagejpeg($image, $file_path, $quality);
            case 'image/png': return imagepng($image, $file_path, intval((100 - $quality) / 10));
            case 'image/gif': return imagegif($image, $file_path);
            case 'image/webp': return imagewebp($image, $file_path, $quality);
            default: return false;
        }
    }
    
    /**
     * Limpa cache antigo
     */
    public function cleanOldCache($max_age_days = 7) {
        $files = glob($this->cache_dir . '*');
        $max_age_seconds = $max_age_days * 24 * 60 * 60;
        
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $max_age_seconds) {
                unlink($file);
            }
        }
    }
    
    /**
     * Obtém estatísticas de performance
     */
    public function getPerformanceStats() {
        $memory_usage = memory_get_usage(true);
        $peak_memory = memory_get_peak_usage(true);
        $execution_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        
        $cache_files = glob($this->cache_dir . '*');
        $cache_size = 0;
        foreach ($cache_files as $file) {
            if (is_file($file)) {
                $cache_size += filesize($file);
            }
        }
        
        return [
            'memory_usage' => $this->formatBytes($memory_usage),
            'peak_memory' => $this->formatBytes($peak_memory),
            'execution_time' => round($execution_time, 4) . 's',
            'cache_files' => count($cache_files),
            'cache_size' => $this->formatBytes($cache_size),
            'compression_enabled' => $this->compression_enabled
        ];
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// Função helper global
function startPerformanceOptimizations() {
    static $optimizer = null;
    if ($optimizer === null) {
        $optimizer = new PerformanceOptimizer();
        $optimizer->startOptimizations();
    }
    return $optimizer;
}

// Inicializar otimizações automaticamente
startPerformanceOptimizations();
?>


