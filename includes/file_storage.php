<?php
// includes/file_storage.php - Sistema de Armazenamento em Arquivo JSON

class FileStorage {
    private $dataDir;
    private $productsFile;
    private $configFile;
    
    public function __construct() {
        $this->dataDir = __DIR__ . '/../data';
        $this->productsFile = $this->dataDir . '/produtos.json';
        $this->configFile = $this->dataDir . '/config.json';
        
        // Cria o diretório se não existir
        if (!is_dir($this->dataDir)) {
            if (!@mkdir($this->dataDir, 0755, true)) {
                // Se não conseguir criar, tenta criar no diretório atual
                $this->dataDir = __DIR__ . '/data';
                $this->productsFile = $this->dataDir . '/produtos.json';
                $this->configFile = $this->dataDir . '/config.json';
                if (!is_dir($this->dataDir)) {
                    @mkdir($this->dataDir, 0755, true);
                }
            }
        }
        
        // Inicializa arquivos se não existirem
        try {
            $this->initializeFiles();
        } catch (Exception $e) {
            error_log("Erro ao inicializar FileStorage: " . $e->getMessage());
        }
    }
    
    private function initializeFiles() {
        // Inicializa produtos.json se não existir
        if (!file_exists($this->productsFile)) {
            file_put_contents($this->productsFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        
        // Inicializa config.json se não existir
        if (!file_exists($this->configFile)) {
            $defaultConfig = [
                'chave_pix' => '',
                'nome_pix' => '',
                'cidade_pix' => '',
                'ultima_atualizacao' => date('Y-m-d H:i:s')
            ];
            file_put_contents($this->configFile, json_encode($defaultConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
    
    // ========== PRODUTOS ==========
    
    public function getProdutos($filtros = []) {
        $produtos = $this->loadProdutos();
        
        // Aplica filtros
        if (!empty($filtros['termo'])) {
            $termo = strtolower($filtros['termo']);
            $produtos = array_filter($produtos, function($produto) use ($termo) {
                return strpos(strtolower($produto['nome']), $termo) !== false ||
                       strpos(strtolower($produto['descricao_curta'] ?? ''), $termo) !== false ||
                       strpos(strtolower($produto['descricao'] ?? ''), $termo) !== false;
            });
        }
        
        if (!empty($filtros['categoria_id'])) {
            $produtos = array_filter($produtos, function($produto) use ($filtros) {
                return $produto['categoria_id'] == $filtros['categoria_id'];
            });
        }
        
        if (!empty($filtros['preco_min'])) {
            $produtos = array_filter($produtos, function($produto) use ($filtros) {
                return $produto['preco'] >= $filtros['preco_min'];
            });
        }
        
        if (!empty($filtros['preco_max'])) {
            $produtos = array_filter($produtos, function($produto) use ($filtros) {
                return $produto['preco'] <= $filtros['preco_max'];
            });
        }
        
        // Ordenação
        if (!empty($filtros['ordenar'])) {
            switch ($filtros['ordenar']) {
                case 'preco_asc':
                    usort($produtos, function($a, $b) { return $a['preco'] <=> $b['preco']; });
                    break;
                case 'preco_desc':
                    usort($produtos, function($a, $b) { return $b['preco'] <=> $a['preco']; });
                    break;
                case 'nome':
                    usort($produtos, function($a, $b) { return strcmp($a['nome'], $b['nome']); });
                    break;
            }
        }
        
        return array_values($produtos);
    }
    
    public function getProduto($id) {
        $produtos = $this->loadProdutos();
        foreach ($produtos as $produto) {
            if ($produto['id'] == $id) {
                return $produto;
            }
        }
        return null;
    }
    
    public function salvarProduto($produto) {
        $produtos = $this->loadProdutos();
        
        if (empty($produto['id'])) {
            // Novo produto
            $produto['id'] = $this->getNextId($produtos);
            $produto['data_cadastro'] = date('Y-m-d H:i:s');
            $produtos[] = $produto;
        } else {
            // Atualizar produto existente
            foreach ($produtos as $key => $p) {
                if ($p['id'] == $produto['id']) {
                    $produto['data_atualizacao'] = date('Y-m-d H:i:s');
                    $produtos[$key] = $produto;
                    break;
                }
            }
        }
        
        return $this->saveProdutos($produtos);
    }
    
    public function deletarProduto($id) {
        $produtos = $this->loadProdutos();
        $produtos = array_filter($produtos, function($produto) use ($id) {
            return $produto['id'] != $id;
        });
        return $this->saveProdutos(array_values($produtos));
    }
    
    private function loadProdutos() {
        if (!file_exists($this->productsFile)) {
            return [];
        }
        $content = file_get_contents($this->productsFile);
        return json_decode($content, true) ?: [];
    }
    
    private function saveProdutos($produtos) {
        return file_put_contents($this->productsFile, json_encode($produtos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }
    
    private function getNextId($produtos) {
        if (empty($produtos)) {
            return 1;
        }
        $ids = array_column($produtos, 'id');
        return max($ids) + 1;
    }
    
    // ========== CONFIGURAÇÕES (PIX) ==========
    
    public function getConfig() {
        if (!file_exists($this->configFile)) {
            return [
                'chave_pix' => '',
                'nome_pix' => '',
                'cidade_pix' => '',
                'ultima_atualizacao' => date('Y-m-d H:i:s')
            ];
        }
        $content = file_get_contents($this->configFile);
        return json_decode($content, true) ?: [];
    }
    
    public function salvarConfig($config) {
        $configAtual = $this->getConfig();
        $configAtual = array_merge($configAtual, $config);
        $configAtual['ultima_atualizacao'] = date('Y-m-d H:i:s');
        
        return file_put_contents($this->configFile, json_encode($configAtual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }
    
    public function getChavePix() {
        $config = $this->getConfig();
        return $config['chave_pix'] ?? '';
    }
    
    public function getNomePix() {
        $config = $this->getConfig();
        return $config['nome_pix'] ?? '';
    }
    
    public function getCidadePix() {
        $config = $this->getConfig();
        return $config['cidade_pix'] ?? '';
    }

    public function getInfiniteTag() {
        $config = $this->getConfig();
        return $config['infinite_tag'] ?? '';
    }
    
    // ========== CATEGORIAS ==========
    
    public function getCategorias() {
        $produtos = $this->loadProdutos();
        $categorias = [];
        
        foreach ($produtos as $produto) {
            if (!empty($produto['categoria_id']) && !empty($produto['categoria_nome'])) {
                $catId = $produto['categoria_id'];
                if (!isset($categorias[$catId])) {
                    $categorias[$catId] = [
                        'id' => $catId,
                        'nome' => $produto['categoria_nome'],
                        'ordem' => $produto['categoria_ordem'] ?? 0
                    ];
                }
            }
        }
        
        usort($categorias, function($a, $b) {
            return ($a['ordem'] ?? 0) <=> ($b['ordem'] ?? 0);
        });
        
        return array_values($categorias);
    }
    
    // ========== BANNERS ==========
    
    public function getBanners($tipo = null) {
        // Por enquanto, retorna array vazio
        // Você pode adicionar um arquivo banners.json se necessário
        return [];
    }
}

