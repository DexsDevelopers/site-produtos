<?php
// includes/theme_manager.php - Gerenciador de Temas Dark/Light

class ThemeManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Salvar preferência de tema do usuário
    public function salvarTemaUsuario($usuario_id, $tema) {
        if (!$usuario_id) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO preferencias_usuario (usuario_id, tema_preferido, data_atualizacao) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE tema_preferido = VALUES(tema_preferido), data_atualizacao = NOW()
        ");
        return $stmt->execute([$usuario_id, $tema]);
    }
    
    // Carregar preferência de tema do usuário
    public function carregarTemaUsuario($usuario_id) {
        if (!$usuario_id) {
            return 'auto'; // Tema automático por padrão
        }
        
        $stmt = $this->pdo->prepare("SELECT tema_preferido FROM preferencias_usuario WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado['tema_preferido'] : 'auto';
    }
    
    // Detectar preferência do sistema
    public function detectarTemaSistema() {
        if (isset($_COOKIE['theme'])) {
            return $_COOKIE['theme'];
        }
        
        // Verificar preferência do navegador
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($acceptHeader, 'prefers-color-scheme') !== false) {
            return 'auto';
        }
        
        return 'dark'; // Tema padrão
    }
    
    // Aplicar tema
    public function aplicarTema($tema) {
        if ($tema === 'auto') {
            return 'auto';
        }
        
        return in_array($tema, ['light', 'dark']) ? $tema : 'dark';
    }
    
    // Obter classes CSS do tema
    public function getClassesTema($tema) {
        $classes = [];
        
        switch ($tema) {
            case 'light':
                $classes = [
                    'body' => 'theme-light',
                    'bg' => 'bg-gray-50',
                    'text' => 'text-gray-900',
                    'card' => 'bg-white border-gray-200',
                    'input' => 'bg-white border-gray-300 text-gray-900',
                    'button' => 'bg-blue-600 hover:bg-blue-700 text-white'
                ];
                break;
                
            case 'dark':
                $classes = [
                    'body' => 'theme-dark',
                    'bg' => 'bg-gray-900',
                    'text' => 'text-white',
                    'card' => 'bg-gray-800 border-gray-700',
                    'input' => 'bg-gray-700 border-gray-600 text-white',
                    'button' => 'bg-blue-600 hover:bg-blue-700 text-white'
                ];
                break;
                
            case 'auto':
            default:
                $classes = [
                    'body' => 'theme-auto',
                    'bg' => 'bg-gray-50 dark:bg-gray-900',
                    'text' => 'text-gray-900 dark:text-white',
                    'card' => 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700',
                    'input' => 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white',
                    'button' => 'bg-blue-600 hover:bg-blue-700 text-white'
                ];
                break;
        }
        
        return $classes;
    }
    
    // Obter configuração do Tailwind para tema
    public function getTailwindConfig($tema) {
        $config = [
            'darkMode' => 'class',
            'theme' => [
                'extend' => [
                    'colors' => [
                        'primary' => [
                            50 => '#eff6ff',
                            100 => '#dbeafe',
                            200 => '#bfdbfe',
                            300 => '#93c5fd',
                            400 => '#60a5fa',
                            500 => '#3b82f6',
                            600 => '#2563eb',
                            700 => '#1d4ed8',
                            800 => '#1e40af',
                            900 => '#1e3a8a',
                        ]
                    ]
                ]
            ]
        ];
        
        if ($tema === 'light') {
            $config['theme']['extend']['colors']['background'] = '#ffffff';
            $config['theme']['extend']['colors']['foreground'] = '#000000';
        } elseif ($tema === 'dark') {
            $config['theme']['extend']['colors']['background'] = '#0f172a';
            $config['theme']['extend']['colors']['foreground'] = '#ffffff';
        }
        
        return $config;
    }
}
?>
