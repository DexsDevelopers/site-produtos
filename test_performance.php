<?php
// test_performance.php - Teste de Performance do Site
require_once 'config_optimized.php';
require_once 'includes/performance_optimizer.php';

// Iniciar medição de tempo
$start_time = microtime(true);
$start_memory = memory_get_usage();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Teste de Performance - Site Otimizado</title>
    <link rel="stylesheet" href="assets/css/optimized.css">
    <style>
        .performance-dashboard {
            background: rgba(30, 41, 59, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem;
            color: white;
        }

        .metric {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .metric:last-child {
            border-bottom: none;
        }

        .metric-label {
            font-weight: 600;
            color: #94A3B8;
        }

        .metric-value {
            font-weight: 700;
            color: #FF3B5C;
        }

        .status-good {
            color: #10B981;
        }

        .status-warning {
            color: #F59E0B;
        }

        .status-error {
            color: #EF4444;
        }

        .test-section {
            margin: 2rem 0;
            padding: 1.5rem;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .test-button {
            background: linear-gradient(135deg, #FF3B5C, #E91E63);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0.5rem;
        }

        .test-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 59, 92, 0.4);
        }
    </style>
</head>

<body>
    <div class="performance-dashboard">
        <h1 class="text-3xl font-bold mb-6 text-center">🚀 Dashboard de Performance</h1>

        <?php
        // Teste de consultas ao banco
        $db_start = microtime(true);
        $banners = $pdo->query("SELECT COUNT(*) as total FROM banners WHERE ativo = 1")->fetch();
        $produtos = $pdo->query("SELECT COUNT(*) as total FROM produtos")->fetch();
        $categorias = $pdo->query("SELECT COUNT(*) as total FROM categorias")->fetch();
        $db_time = microtime(true) - $db_start;

        // Teste de cache
        $cache_start = microtime(true);
        $cache_test = getCachedData('test_performance', function () {
            return ['test' => 'data', 'timestamp' => time()];
        }, 60);
        $cache_time = microtime(true) - $cache_start;

        // Teste de otimização de imagem
        $image_start = microtime(true);
        $test_image = 'assets/uploads/produto_6881b8f5e5a1f7.72668877.png';
        if (file_exists($test_image)) {
            $optimized_image = optimizeImage($test_image, 400, 400, 80);
        }
        $image_time = microtime(true) - $image_start;

        // Métricas finais
        $end_time = microtime(true);
        $end_memory = memory_get_usage();
        $execution_time = $end_time - $start_time;
        $memory_used = $end_memory - $start_memory;
        $peak_memory = memory_get_peak_usage();

        // Estatísticas do cache
        $cache_stats = $cache->getStats();
        ?>

        <div class="test-section">
            <h2 class="text-xl font-bold mb-4">📊 Métricas de Performance</h2>

            <div class="metric">
                <span class="metric-label">Tempo de Execução:</span>
                <span
                    class="metric-value <?= $execution_time < 0.5 ? 'status-good' : ($execution_time < 1 ? 'status-warning' : 'status-error') ?>">
                    <?= round($execution_time, 4) ?>s
                </span>
            </div>

            <div class="metric">
                <span class="metric-label">Memória Usada:</span>
                <span
                    class="metric-value <?= $memory_used < 1024 * 1024 ? 'status-good' : ($memory_used < 5 * 1024 * 1024 ? 'status-warning' : 'status-error') ?>">
                    <?= round($memory_used / 1024 / 1024, 2) ?> MB
                </span>
            </div>

            <div class="metric">
                <span class="metric-label">Pico de Memória:</span>
                <span
                    class="metric-value <?= $peak_memory < 5 * 1024 * 1024 ? 'status-good' : ($peak_memory < 10 * 1024 * 1024 ? 'status-warning' : 'status-error') ?>">
                    <?= round($peak_memory / 1024 / 1024, 2) ?> MB
                </span>
            </div>

            <div class="metric">
                <span class="metric-label">Tempo de Consulta DB:</span>
                <span
                    class="metric-value <?= $db_time < 0.1 ? 'status-good' : ($db_time < 0.3 ? 'status-warning' : 'status-error') ?>">
                    <?= round($db_time, 4) ?>s
                </span>
            </div>

            <div class="metric">
                <span class="metric-label">Tempo de Cache:</span>
                <span
                    class="metric-value <?= $cache_time < 0.01 ? 'status-good' : ($cache_time < 0.05 ? 'status-warning' : 'status-error') ?>">
                    <?= round($cache_time, 4) ?>s
                </span>
            </div>

            <div class="metric">
                <span class="metric-label">Tempo de Otimização de Imagem:</span>
                <span
                    class="metric-value <?= $image_time < 0.1 ? 'status-good' : ($image_time < 0.5 ? 'status-warning' : 'status-error') ?>">
                    <?= round($image_time, 4) ?>s
                </span>
            </div>
        </div>

        <div class="test-section">
            <h2 class="text-xl font-bold mb-4">🗄️ Estatísticas do Banco de Dados</h2>

            <div class="metric">
                <span class="metric-label">Banners Ativos:</span>
                <span class="metric-value"><?= $banners['total'] ?></span>
            </div>

            <div class="metric">
                <span class="metric-label">Total de Produtos:</span>
                <span class="metric-value"><?= $produtos['total'] ?></span>
            </div>

            <div class="metric">
                <span class="metric-label">Categorias:</span>
                <span class="metric-value"><?= $categorias['total'] ?></span>
            </div>
        </div>

        <div class="test-section">
            <h2 class="text-xl font-bold mb-4">💾 Sistema de Cache</h2>

            <div class="metric">
                <span class="metric-label">Arquivos em Cache:</span>
                <span class="metric-value"><?= $cache_stats['total_files'] ?></span>
            </div>

            <div class="metric">
                <span class="metric-label">Tamanho do Cache:</span>
                <span class="metric-value"><?= $cache_stats['total_size_mb'] ?> MB</span>
            </div>
        </div>

        <div class="test-section">
            <h2 class="text-xl font-bold mb-4">🧪 Testes de Performance</h2>

            <button class="test-button" onclick="testDatabase()">Testar Banco de Dados</button>
            <button class="test-button" onclick="testCache()">Testar Cache</button>
            <button class="test-button" onclick="testImages()">Testar Imagens</button>
            <button class="test-button" onclick="clearCache()">Limpar Cache</button>

            <div id="test-results" class="mt-4 p-4 bg-gray-800 rounded-lg hidden">
                <h3 class="font-bold mb-2">Resultados dos Testes:</h3>
                <div id="test-output"></div>
            </div>
        </div>

        <div class="test-section">
            <h2 class="text-xl font-bold mb-4">📈 Recomendações</h2>

            <?php
            $recommendations = [];

            if ($execution_time > 1) {
                $recommendations[] = "⚠️ Tempo de execução alto. Considere otimizar consultas ou usar mais cache.";
            }

            if ($memory_used > 5 * 1024 * 1024) {
                $recommendations[] = "⚠️ Uso de memória alto. Considere otimizar imagens ou reduzir dados carregados.";
            }

            if ($db_time > 0.3) {
                $recommendations[] = "⚠️ Consultas ao banco lentas. Considere adicionar índices ou otimizar queries.";
            }

            if (empty($recommendations)) {
                $recommendations[] = "✅ Performance excelente! O site está otimizado.";
            }

            foreach ($recommendations as $rec) {
                echo "<div class='metric'><span class='metric-label'>$rec</span></div>";
            }
            ?>
        </div>
    </div>

    <script>
        async function testDatabase() {
            showTestResults('Testando banco de dados...');
            const start = performance.now();

            try {
                const response = await fetch('test_db_performance.php');
                const data = await response.json();
                const end = performance.now();

                showTestResults(`✅ Banco de dados testado em ${(end - start).toFixed(2)}ms<br>
                    Consultas: ${data.queries}<br>
                    Tempo total: ${data.time}ms`);
            } catch (error) {
                showTestResults('❌ Erro ao testar banco de dados: ' + error.message);
            }
        }

        async function testCache() {
            showTestResults('Testando sistema de cache...');
            const start = performance.now();

            try {
                const response = await fetch('test_cache_performance.php');
                const data = await response.json();
                const end = performance.now();

                showTestResults(`✅ Cache testado em ${(end - start).toFixed(2)}ms<br>
                    Arquivos: ${data.files}<br>
                    Tamanho: ${data.size}MB`);
            } catch (error) {
                showTestResults('❌ Erro ao testar cache: ' + error.message);
            }
        }

        async function testImages() {
            showTestResults('Testando otimização de imagens...');
            const start = performance.now();

            try {
                const response = await fetch('test_image_performance.php');
                const data = await response.json();
                const end = performance.now();

                showTestResults(`✅ Imagens testadas em ${(end - start).toFixed(2)}ms<br>
                    Imagens processadas: ${data.images}<br>
                    Tempo total: ${data.time}ms`);
            } catch (error) {
                showTestResults('❌ Erro ao testar imagens: ' + error.message);
            }
        }

        async function clearCache() {
            showTestResults('Limpando cache...');

            try {
                const response = await fetch('clear_cache.php');
                const data = await response.json();

                if (data.success) {
                    showTestResults('✅ Cache limpo com sucesso!');
                } else {
                    showTestResults('❌ Erro ao limpar cache: ' + data.message);
                }
            } catch (error) {
                showTestResults('❌ Erro ao limpar cache: ' + error.message);
            }
        }

        function showTestResults(message) {
            document.getElementById('test-results').classList.remove('hidden');
            document.getElementById('test-output').innerHTML = message;
        }
    </script>
</body>

</html>