<?php
// includes/advanced_cache.php - Sistema de Cache Avançado

class AdvancedCache {
    private $cacheDir;
    private $defaultTTL;
    private $compression;
    
    public function __construct($cacheDir = 'cache/', $defaultTTL = 3600, $compression = true) {
        $this->cacheDir = rtrim($cacheDir, '/') . '/';
        $this->defaultTTL = $defaultTTL;
        $this->compression = $compression;
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    // Obter dados do cache
    public function get($key) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = file_get_contents($filename);
        if ($data === false) {
            return null;
        }
        
        if ($this->compression) {
            $data = gzuncompress($data);
        }
        
        $cacheData = unserialize($data);
        
        // Verificar se expirou
        if ($cacheData['expires'] < time()) {
            $this->delete($key);
            return null;
        }
        
        return $cacheData['data'];
    }
    
    // Salvar dados no cache
    public function set($key, $data, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTTL;
        $filename = $this->getFilename($key);
        
        $cacheData = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        $serialized = serialize($cacheData);
        
        if ($this->compression) {
            $serialized = gzcompress($serialized, 6);
        }
        
        return file_put_contents($filename, $serialized, LOCK_EX) !== false;
    }
    
    // Deletar do cache
    public function delete($key) {
        $filename = $this->getFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }
    
    // Limpar cache expirado
    public function cleanExpired() {
        $files = glob($this->cacheDir . '*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = file_get_contents($file);
            if ($this->compression) {
                $data = gzuncompress($data);
            }
            
            $cacheData = unserialize($data);
            if ($cacheData['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    // Limpar todo o cache
    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    // Obter estatísticas
    public function getStats() {
        $files = glob($this->cacheDir . '*.cache');
        $totalFiles = count($files);
        $totalSize = 0;
        $expiredFiles = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $data = file_get_contents($file);
            if ($this->compression) {
                $data = gzuncompress($data);
            }
            
            $cacheData = unserialize($data);
            if ($cacheData['expires'] < time()) {
                $expiredFiles++;
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'expired_files' => $expiredFiles,
            'active_files' => $totalFiles - $expiredFiles
        ];
    }
    
    // Obter nome do arquivo
    private function getFilename($key) {
        return $this->cacheDir . md5($key) . '.cache';
    }
}

// Cache de consultas de banco
class DatabaseCache {
    private $pdo;
    private $cache;
    
    public function __construct($pdo, $cache) {
        $this->pdo = $pdo;
        $this->cache = $cache;
    }
    
    // Executar query com cache
    public function query($sql, $params = [], $ttl = 3600) {
        $cacheKey = 'db_' . md5($sql . serialize($params));
        
        // Tentar obter do cache
        $result = $this->cache->get($cacheKey);
        if ($result !== null) {
            return $result;
        }
        
        // Executar query
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Salvar no cache
        $this->cache->set($cacheKey, $result, $ttl);
        
        return $result;
    }
    
    // Query única com cache
    public function queryOne($sql, $params = [], $ttl = 3600) {
        $cacheKey = 'db_one_' . md5($sql . serialize($params));
        
        $result = $this->cache->get($cacheKey);
        if ($result !== null) {
            return $result;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->cache->set($cacheKey, $result, $ttl);
        
        return $result;
    }
}

// Cache de páginas
class PageCache {
    private $cache;
    private $enabled;
    
    public function __construct($cache, $enabled = true) {
        $this->cache = $cache;
        $this->enabled = $enabled;
    }
    
    // Obter página do cache
    public function getPage($key) {
        if (!$this->enabled) return null;
        
        return $this->cache->get('page_' . $key);
    }
    
    // Salvar página no cache
    public function setPage($key, $content, $ttl = 1800) {
        if (!$this->enabled) return false;
        
        return $this->cache->set('page_' . $key, $content, $ttl);
    }
    
    // Gerar chave da página
    public function generateKey($uri, $params = []) {
        return md5($uri . serialize($params));
    }
}

// Cache de imagens
class ImageCache {
    private $cacheDir;
    private $maxWidth;
    private $maxHeight;
    private $quality;
    
    public function __construct($cacheDir = 'cache/images/', $maxWidth = 800, $maxHeight = 600, $quality = 85) {
        $this->cacheDir = rtrim($cacheDir, '/') . '/';
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->quality = $quality;
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    // Obter imagem otimizada
    public function getOptimizedImage($imagePath, $width = null, $height = null) {
        $width = $width ?? $this->maxWidth;
        $height = $height ?? $this->maxHeight;
        
        $cacheKey = md5($imagePath . $width . $height);
        $cacheFile = $this->cacheDir . $cacheKey . '.jpg';
        
        if (file_exists($cacheFile)) {
            return $cacheFile;
        }
        
        return $this->optimizeImage($imagePath, $width, $height, $cacheFile);
    }
    
    // Otimizar imagem
    private function optimizeImage($imagePath, $width, $height, $outputPath) {
        if (!file_exists($imagePath)) {
            return null;
        }
        
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return null;
        }
        
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Calcular novas dimensões mantendo proporção
        $ratio = min($width / $originalWidth, $height / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);
        
        // Criar imagem original
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($imagePath);
                break;
            default:
                return null;
        }
        
        // Criar nova imagem
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparência para PNG
        if ($mimeType === 'image/png') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
        }
        
        // Redimensionar
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Salvar imagem otimizada
        imagejpeg($destination, $outputPath, $this->quality);
        
        // Limpar memória
        imagedestroy($source);
        imagedestroy($destination);
        
        return $outputPath;
    }
}
?>
