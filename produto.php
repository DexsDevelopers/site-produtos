<?php
// produto.php - Página de Produto no Estilo Adsly
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once 'config.php';

// --- RASTREAMENTO AUTOMÁTICO DE AFILIAÇÃO ---
if (isset($_GET['ref'])) {
    require_once 'includes/affiliate_system.php';
    $affiliateSystem = new AffiliateSystem($pdo);

    $affiliate_code = $_GET['ref'];
    $product_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $result = $affiliateSystem->registerClick($affiliate_code, $product_id, $ip_address);

    if ($result['success']) {
        $_SESSION['affiliate_tracking'] = [
            'affiliate_code' => $affiliate_code,
            'click_id' => $result['click_id'],
            'timestamp' => time()
        ];
    }

    // Remover parâmetro ref da URL
    $params = $_GET;
    unset($params['ref']);
    $redirect_url = 'produto.php';
    if (!empty($params)) {
        $redirect_url .= '?' . http_build_query($params);
    }
    header('Location: ' . $redirect_url);
    exit();
}

$produto_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$produto_selecionado = null;

try {
    // Busca os dados do produto
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto_selecionado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o produto existe, busca as avaliações dele
    if ($produto_selecionado) {
        $stmt_avaliacoes = $pdo->prepare(
            "SELECT a.*, u.nome as nome_usuario 
             FROM avaliacoes a
             JOIN usuarios u ON a.usuario_id = u.id
             WHERE a.produto_id = ? 
             ORDER BY a.data_avaliacao DESC"
        );
        $stmt_avaliacoes->execute([$produto_id]);
        $avaliacoes = $stmt_avaliacoes->fetchAll(PDO::FETCH_ASSOC);

        // Calcula a média das notas
        $total_avaliacoes = count($avaliacoes);
        $soma_notas = 0;
        foreach ($avaliacoes as $avaliacao) {
            $soma_notas += $avaliacao['nota'];
        }
        $media_notas = ($total_avaliacoes > 0) ? round($soma_notas / $total_avaliacoes, 1) : 0;
    }
} catch (PDOException $e) {
    error_log("Erro PDO em produto.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    die("Erro ao carregar a página do produto. Verifique os logs para mais detalhes.");
} catch (Exception $e) {
    error_log("Erro geral em produto.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    die("Erro ao carregar a página do produto. Verifique os logs para mais detalhes.");
}

if (!$produto_selecionado) {
    header('Location: index.php');
    exit();
}

// Define meta tags específicas para o produto
$page_title = htmlspecialchars($produto_selecionado['nome']);
$page_description = htmlspecialchars($produto_selecionado['descricao_curta']);
$page_keywords = 'produto, ' . strtolower(str_replace(' ', ', ', $produto_selecionado['nome'])) . ', comprar, loja online';
$page_image = htmlspecialchars($produto_selecionado['imagem']);

// Verifica métodos de pagamento configurados
$metodos_pagamento = [];
try {
    if (isset($fileStorage) && is_object($fileStorage)) {
        // InfinitePay
        $infinite_tag = $fileStorage->getInfiniteTag();
        if (!empty($infinite_tag)) {
            $metodos_pagamento['infinitepay'] = [
                'url' => 'checkout_infinitepay.php',
                'btn_text' => 'Pagar com Cartão / PIX',
                'sub_text' => 'Via InfinitePay',
                'color' => 'from-green-600 to-green-700',
                'hover' => 'from-green-500 to-green-600',
                'icon' => 'fas fa-credit-card'
            ];
        }
        
        // PIX Manual
        $chave_pix = $fileStorage->getChavePix();
        if (!empty($chave_pix)) {
            $metodos_pagamento['pix'] = [
                'url' => 'checkout_pix.php',
                'btn_text' => 'Pagar com PIX Manual',
                'sub_text' => 'Transferência Direta',
                'color' => 'from-brand-red to-red-700',
                'hover' => 'from-red-500 to-brand-red',
                'icon' => 'fas fa-qrcode'
            ];
        }
    }
} catch (Exception $e) {
    error_log("Erro ao verificar métodos de pagamento: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($produto_selecionado['nome']) ?> - Minha Loja</title>

    <!-- SEO -->
    <meta name="description" content="<?= htmlspecialchars($produto_selecionado['descricao_curta']) ?>">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/modern.css">

    <style>
        /* Product Page Specific Styles */
        body {
            background-color: #050505;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        .product-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 1024px) {
            .product-grid {
                grid-template-columns: 1.2fr 1fr;
                /* Image larger than text */
                gap: 4rem;
                align-items: start;
            }

            .sticky-info {
                position: sticky;
                top: 100px;
            }
        }

        /* Ultra Glass Container */
        .glass-panel-product {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            border-left: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.4);
            border-radius: 24px;
            padding: 2rem;
        }

        /* Image Gallery */
        .main-image-wrapper {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            aspect-ratio: 4/3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Cover for immersive look */
            transition: transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .main-image-wrapper:hover img {
            transform: scale(1.05);
        }

        /* Typography */
        .product-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: clamp(2rem, 5vw, 3.5rem);
            line-height: 1.1;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #a0a0a0 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .price-tag {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #ff0000;
            text-shadow: 0 0 30px rgba(255, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .old-price {
            font-size: 1.2rem;
            color: #666;
            text-decoration: line-through;
            font-weight: 400;
        }

        /* Features List */
        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 0, 0, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff0000;
        }

        /* CTA Button */
        .pulse-btn {
            animation: pulse-shadow 2s infinite;
        }

        @keyframes pulse-shadow {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 0, 0, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
            }
        }

        /* Tab System */
        .tab-btn {
            background: transparent;
            border: none;
            color: #888;
            padding: 1rem 2rem;
            cursor: pointer;
            font-weight: 600;
            position: relative;
            transition: all 0.3s;
        }

        .tab-btn.active {
            color: white;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: #ff0000;
            box-shadow: 0 0 10px #ff0000;
        }
    </style>
</head>

<body>

    <?php require_once 'templates/header.php'; ?>

    <!-- Animated Background -->
    <div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div
            class="absolute w-[800px] h-[800px] bg-red-600/10 rounded-full blur-[120px] -top-20 -left-20 animate-pulse">
        </div>
        <div class="absolute w-[600px] h-[600px] bg-blue-600/5 rounded-full blur-[100px] bottom-0 right-0"></div>
    </div>

    <main class="pt-24 pb-16 px-4 md:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8 font-medium">
                <a href="index.php" class="hover:text-white transition-colors">Home</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-white"><?= htmlspecialchars($produto_selecionado['nome']) ?></span>
            </nav>

            <div class="product-grid">
                <!-- Left Column: Visuals -->
                <div class="space-y-6">
                    <div class="glass-panel-product p-2">
                        <div class="main-image-wrapper">
                            <img id="mainImage" src="<?= htmlspecialchars($produto_selecionado['imagem']) ?>"
                                alt="<?= htmlspecialchars($produto_selecionado['nome']) ?>"
                                class="w-full h-full object-cover rounded-xl shadow-2xl">
                        </div>
                    </div>

                    <!-- Feature Highlights Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="glass-panel-product p-4 flex flex-col items-center text-center gap-2 hover:bg-white/5 transition-colors">
                            <i class="fas fa-bolt text-2xl text-yellow-400"></i>
                            <span class="font-bold text-sm">Entrega Imediata</span>
                        </div>
                        <div
                            class="glass-panel-product p-4 flex flex-col items-center text-center gap-2 hover:bg-white/5 transition-colors">
                            <i class="fas fa-shield-alt text-2xl text-green-400"></i>
                            <span class="font-bold text-sm">Garantia Total</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Product Info -->
                <div class="sticky-info">
                    <div class="glass-panel-product relative overflow-hidden">
                        <!-- Glow Effect -->
                        <div
                            class="absolute top-0 right-0 w-64 h-64 bg-red-500/10 blur-[80px] rounded-full pointer-events-none">
                        </div>

                        <h1 class="product-title"><?= htmlspecialchars($produto_selecionado['nome']) ?></h1>

                        <!-- Rating -->
                        <div class="flex items-center gap-2 mb-6">
                            <div class="flex text-yellow-400 text-sm">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-gray-400 text-sm">(<?= $total_avaliacoes ?> avaliações)</span>
                        </div>

                        <!-- Price -->
                        <div class="price-tag mb-8">
                            R$ <?= number_format($produto_selecionado['preco'], 2, ',', '.') ?>
                            <?php if (isset($produto_selecionado['preco_antigo'])): ?>
                                <span class="old-price">R$
                                    <?= number_format($produto_selecionado['preco_antigo'], 2, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Description Short -->
                        <p class="text-gray-300 text-lg leading-relaxed mb-8 border-l-2 border-red-500 pl-4">
                            <?= htmlspecialchars($produto_selecionado['descricao_curta']) ?>
                        </p>

                        <!-- Action Buttons -->
                        <div class="space-y-4">
                            <?php if (!empty($metodos_pagamento)): ?>
                                <div class="grid grid-cols-1 gap-3">
                                    <?php foreach ($metodos_pagamento as $metodo): ?>
                                        <a href="<?= $metodo['url'] ?>?produto_id=<?= $produto_selecionado['id'] ?>&quantidade=1"
                                            class="pulse-btn w-full bg-gradient-to-r <?= $metodo['color'] ?> hover:<?= $metodo['hover'] ?> text-white font-bold py-4 rounded-xl shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex flex-col items-center justify-center text-lg leading-tight">
                                            <div class="flex items-center gap-3">
                                                <i class="<?= $metodo['icon'] ?>"></i>
                                                <?= htmlspecialchars($metodo['btn_text']) ?>
                                            </div>
                                            <span class="text-[10px] opacity-80 font-normal uppercase mt-0.5 tracking-wider"><?= $metodo['sub_text'] ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form id="add-to-cart-form" class="w-full">
                                <input type="hidden" name="produto_id" value="<?= $produto_selecionado['id'] ?>">
                                <button type="submit"
                                    class="w-full bg-white/5 hover:bg-white/10 border border-white/10 text-white font-semibold py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-cart-plus"></i>
                                    Adicionar ao Carrinho
                                </button>
                            </form>
                        </div>

                        <!-- Safety Info -->
                        <div class="mt-8 pt-6 border-t border-white/10 flex items-center gap-4 text-sm text-gray-400">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-lock text-green-400"></i>
                                Pagamento Seguro
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-headset text-blue-400"></i>
                                Suporte 24/7
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section (Description & Reviews) -->
            <div class="mt-16">
                <div class="flex border-b border-white/10 mb-8">
                    <button class="tab-btn active" onclick="switchTab('desc')">Descrição</button>
                    <button class="tab-btn" onclick="switchTab('reviews')">Avaliações</button>
                </div>

                <div id="tab-desc" class="glass-panel-product">
                    <div class="prose prose-invert max-w-none text-gray-300">
                        <?= nl2br(htmlspecialchars($produto_selecionado['descricao'])) ?>
                    </div>
                </div>

                <div id="tab-reviews" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if (!empty($avaliacoes)): ?>
                        <?php foreach ($avaliacoes as $avaliacao): ?>
                            <div class="glass-panel-product p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-r from-red-500 to-purple-500 flex items-center justify-center font-bold">
                                            <?= strtoupper(substr($avaliacao['nome_usuario'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-sm"><?= htmlspecialchars($avaliacao['nome_usuario']) ?>
                                            </h4>
                                            <div class="text-yellow-400 text-xs">
                                                <?php for ($i = 0; $i < $avaliacao['nota']; $i++)
                                                    echo '<i class="fas fa-star"></i>'; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($avaliacao['data_avaliacao'])) ?></span>
                                </div>
                                <p class="text-gray-300 text-sm"><?= htmlspecialchars($avaliacao['comentario']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-gray-500 py-8 col-span-full">
                            Nenhuma avaliação ainda. Seja o primeiro a avaliar!
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <?php require_once 'templates/footer.php'; ?>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.getElementById('tab-desc').classList.add('hidden');
            document.getElementById('tab-reviews').classList.add('hidden');

            // Show selected
            document.getElementById('tab-' + tabName).classList.remove('hidden');

            // Update buttons
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }

        function mostrarCartPopup(mensagem) {
            const popup = document.getElementById('cart-popup');
            const messageEl = document.getElementById('cart-popup-message');

            if (mensagem) {
                messageEl.textContent = mensagem;
            }

            popup.classList.add('show');

            // Fecha automaticamente após 4 segundos
            setTimeout(() => {
                fecharCartPopup();
            }, 4000);
        }

        function fecharCartPopup() {
            const popup = document.getElementById('cart-popup');
            popup.classList.remove('show');
        }

        // Fecha ao clicar fora do popup
        document.getElementById('cart-popup').addEventListener('click', function (e) {
            if (e.target === this) {
                fecharCartPopup();
            }
        });

        // Adiciona produto ao carrinho
        document.getElementById('add-to-cart-form').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const button = form.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;

            // Animação de loading
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';

            const formData = new FormData(form);

            fetch('adicionar_carrinho.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualiza contador do carrinho
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount) {
                            const currentCount = parseInt(cartCount.textContent) || 0;
                            cartCount.textContent = data.cart_count || (currentCount + 1);
                            cartCount.style.transform = 'scale(1.3)';
                            setTimeout(() => {
                                cartCount.style.transform = 'scale(1)';
                            }, 300);
                        }

                        // Mostra popup bonito
                        const mensagem = data.produto_nome
                            ? `${data.produto_nome} foi adicionado ao carrinho!`
                            : 'Produto adicionado ao carrinho com sucesso!';
                        mostrarCartPopup(mensagem);

                        // Feedback visual no botão
                        button.innerHTML = '<i class="fas fa-check"></i> Adicionado!';
                        button.style.background = 'linear-gradient(135deg, #10B981, #059669)';

                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.style.background = '';
                            button.disabled = false;
                        }, 2000);
                    } else {
                        alert(data.message || 'Erro ao adicionar produto ao carrinho.');
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao adicionar produto ao carrinho. Tente novamente.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
        });
    </script>

    <?php require_once 'templates/footer.php'; ?>