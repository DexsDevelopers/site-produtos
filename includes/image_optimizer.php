<?php
// Sistema de Otimização de Imagens
class ImageOptimizer {
    private $upload_dir;
    private $cache_dir;
    private $max_width;
    private $max_height;
    private $quality;
    
    public function __construct($upload_dir = 'assets/uploads/', $cache_dir = 'assets/cache/images/') {
        $this->upload_dir = $upload_dir;
        $this->cache_dir = $cache_dir;
        $this->max_width = 1200;
        $this->max_height = 1200;
        $this->quality = 85;
        
        // Criar diretórios se não existirem
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Otimiza uma imagem e retorna a URL otimizada
     */
    public function optimizeImage($image_path, $width = null, $height = null, $quality = null) {
        if (!file_exists($image_path)) {
            return $image_path;
        }
        
        $width = $width ?: $this->max_width;
        $height = $height ?: $this->max_height;
        $quality = $quality ?: $this->quality;
        
        // Gerar nome do arquivo cache
        $path_info = pathinfo($image_path);
        $cache_filename = md5($image_path . $width . $height . $quality) . '.' . $path_info['extension'];
        $cache_path = $this->cache_dir . $cache_filename;
        
        // Se já existe cache válido, retornar
        if (file_exists($cache_path) && filemtime($cache_path) > filemtime($image_path)) {
            return $cache_path;
        }
        
        // Obter informações da imagem original
        $image_info = getimagesize($image_path);
        if (!$image_info) {
            return $image_path;
        }
        
        $original_width = $image_info[0];
        $original_height = $image_info[1];
        $mime_type = $image_info['mime'];
        
        // Se a imagem já é menor que o tamanho desejado, apenas copiar
        if ($original_width <= $width && $original_height <= $height) {
            copy($image_path, $cache_path);
            return $cache_path;
        }
        
        // Calcular novas dimensões mantendo proporção
        $ratio = min($width / $original_width, $height / $original_height);
        $new_width = intval($original_width * $ratio);
        $new_height = intval($original_height * $ratio);
        
        // Criar imagem redimensionada
        $source_image = $this->createImageFromFile($image_path, $mime_type);
        if (!$source_image) {
            return $image_path;
        }
        
        $resized_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preservar transparência para PNG
        if ($mime_type === 'image/png') {
            imagealphablending($resized_image, false);
            imagesavealpha($resized_image, true);
            $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
            imagefill($resized_image, 0, 0, $transparent);
        }
        
        imagecopyresampled(
            $resized_image, $source_image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $original_width, $original_height
        );
        
        // Salvar imagem otimizada
        $this->saveOptimizedImage($resized_image, $cache_path, $mime_type, $quality);
        
        // Limpar memória
        imagedestroy($source_image);
        imagedestroy($resized_image);
        
        return $cache_path;
    }
    
    /**
     * Cria imagem a partir do arquivo
     */
    private function createImageFromFile($file_path, $mime_type) {
        switch ($mime_type) {
            case 'image/jpeg':
                return imagecreatefromjpeg($file_path);
            case 'image/png':
                return imagecreatefrompng($file_path);
            case 'image/gif':
                return imagecreatefromgif($file_path);
            case 'image/webp':
                return imagecreatefromwebp($file_path);
            default:
                return false;
        }
    }
    
    /**
     * Salva imagem otimizada
     */
    private function saveOptimizedImage($image, $file_path, $mime_type, $quality) {
        switch ($mime_type) {
            case 'image/jpeg':
                return imagejpeg($image, $file_path, $quality);
            case 'image/png':
                return imagepng($image, $file_path, intval((100 - $quality) / 10));
            case 'image/gif':
                return imagegif($image, $file_path);
            case 'image/webp':
                return imagewebp($image, $file_path, $quality);
            default:
                return false;
        }
    }
    
    /**
     * Gera diferentes tamanhos de imagem para responsividade
     */
    public function generateResponsiveImages($image_path) {
        $sizes = [
            'small' => [400, 400],
            'medium' => [800, 800],
            'large' => [1200, 1200]
        ];
        
        $responsive_images = [];
        
        foreach ($sizes as $size_name => $dimensions) {
            $optimized_path = $this->optimizeImage($image_path, $dimensions[0], $dimensions[1]);
            $responsive_images[$size_name] = $optimized_path;
        }
        
        return $responsive_images;
    }
    
    /**
     * Limpa cache de imagens antigas
     */
    public function cleanOldCache($max_age_days = 30) {
        $files = glob($this->cache_dir . '*');
        $max_age_seconds = $max_age_days * 24 * 60 * 60;
        
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $max_age_seconds) {
                unlink($file);
            }
        }
    }
    
    /**
     * Obtém estatísticas do cache
     */
    public function getCacheStats() {
        $files = glob($this->cache_dir . '*');
        $total_size = 0;
        $total_files = count($files);
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $total_size += filesize($file);
            }
        }
        
        return [
            'total_files' => $total_files,
            'total_size' => $total_size,
            'total_size_mb' => round($total_size / 1024 / 1024, 2)
        ];
    }
}

// Função helper para usar o otimizador
function getOptimizedImage($image_path, $width = null, $height = null, $quality = null) {
    static $optimizer = null;
    
    if ($optimizer === null) {
        $optimizer = new ImageOptimizer();
    }
    
    return $optimizer->optimizeImage($image_path, $width, $height, $quality);
}

// Função para gerar imagens responsivas
function getResponsiveImages($image_path) {
    static $optimizer = null;
    
    if ($optimizer === null) {
        $optimizer = new ImageOptimizer();
    }
    
    return $optimizer->generateResponsiveImages($image_path);
}

// Limpeza automática do cache (executar periodicamente)
function cleanImageCache() {
    $optimizer = new ImageOptimizer();
    $optimizer->cleanOldCache();
}
?>