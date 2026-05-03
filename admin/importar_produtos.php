<?php
// admin/importar_produtos.php — Importador de Produtos em Massa (Nuvemshop / genérico)
require_once 'secure.php';

// ── Helpers ─────────────────────────────────────────────────────────────────
function fetchUrl(string $url): string|false {
    if (!function_exists('curl_init')) {
        return @file_get_contents($url);
    }
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => ['Accept-Language: pt-BR,pt;q=0.9'],
    ]);
    $html     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($httpCode >= 200 && $httpCode < 400) ? $html : false;
}

function absoluteUrl(string $href, string $base): string {
    if (str_starts_with($href, 'http')) return $href;
    if (str_starts_with($href, '//'))   return 'https:' . $href;
    $p = parse_url($base);
    $origin = $p['scheme'] . '://' . $p['host'];
    return $origin . '/' . ltrim($href, '/');
}

function parseBrPrice(string $text): float {
    // "R$ 1.299,90" → 1299.90
    $t = preg_replace('/[^\d,.]/', '', $text);
    if (substr_count($t, ',') === 1 && substr_count($t, '.') >= 1) {
        $t = str_replace('.', '', $t);
        $t = str_replace(',', '.', $t);
    } elseif (substr_count($t, ',') === 1) {
        $t = str_replace(',', '.', $t);
    }
    return is_numeric($t) ? (float)$t : 0;
}

function bestImg(DOMElement $el, DOMXPath $x, string $base): string {
    // Priority 1: data-srcset (Nuvemshop lazy-load pattern)
    $ns = $x->query('./descendant-or-self::img/@data-srcset', $el);
    if ($ns->length && trim($ns->item(0)->nodeValue)) {
        $srcset = trim($ns->item(0)->nodeValue);
        $bestUrl = ''; $bestW = 0;
        foreach (explode(',', $srcset) as $part) {
            $bits = preg_split('/\s+/', trim($part));
            $w = isset($bits[1]) ? (int)$bits[1] : 0;
            if ($bits[0] && $w > $bestW) { $bestW = $w; $bestUrl = $bits[0]; }
        }
        if ($bestUrl) return absoluteUrl($bestUrl, $base);
    }
    // Priority 2: data-src, data-original, src
    foreach (['@data-src','@data-original','@src'] as $attr) {
        $n = $x->query('./descendant-or-self::img/' . $attr, $el);
        if ($n->length && trim($n->item(0)->nodeValue)) {
            $v = trim($n->item(0)->nodeValue);
            if ($v && !str_contains($v, 'data:') && !str_contains($v, 'blank') && !str_contains($v, 'placeholder')) {
                return absoluteUrl($v, $base);
            }
        }
    }
    return '';
}

function scrapeCategory(string $html, string $pageUrl): array {
    $base = parse_url($pageUrl, PHP_URL_SCHEME) . '://' . parse_url($pageUrl, PHP_URL_HOST);

    $dom = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    $x = new DOMXPath($dom);

    $products = [];

    // ── Strategy 1: Nuvemshop .js-item-product ──────────────────────────────
    $items = $x->query('//*[contains(@class,"js-item-product")]');
    if ($items->length > 0) {
        foreach ($items as $item) {
            $nameN  = $x->query('.//*[contains(@class,"item-name") or contains(@class,"product-name") or contains(@class,"js-item-name")]', $item);
            $priceN = $x->query('.//*[contains(@class,"item-price") or contains(@class,"price") or contains(@class,"js-item-price")]', $item);
            $linkN  = $x->query('.//a[contains(@href,"/produtos/") or contains(@href,"/products/")]', $item);
            $nome   = $nameN->length  ? trim($nameN->item(0)->textContent)  : '';
            $preco  = $priceN->length ? parseBrPrice($priceN->item(0)->textContent) : 0;
            $link   = $linkN->length  ? absoluteUrl($linkN->item(0)->getAttribute('href'), $base) : '';
            $img    = bestImg($item, $x, $base);
            if ($nome && $preco > 0) {
                $products[] = compact('nome','preco','img','link');
            }
        }
        if (!empty($products)) return $products;
    }

    // ── Strategy 2: article.product-item / .product-card / .product ─────────
    $items = $x->query(
        '//article[contains(@class,"product")] | ' .
        '//*[contains(@class,"product-card")] | ' .
        '//*[contains(@class,"product-item") and not(contains(@class,"js-item-product"))]'
    );
    if ($items->length > 0) {
        foreach ($items as $item) {
            $nameN  = $x->query('.//h2|.//h3|.//*[contains(@class,"name")]', $item);
            $priceN = $x->query('.//*[contains(@class,"price")]', $item);
            $linkN  = $x->query('.//a[@href]', $item);
            $nome   = $nameN->length  ? trim($nameN->item(0)->textContent)  : '';
            $preco  = $priceN->length ? parseBrPrice($priceN->item(0)->textContent) : 0;
            $link   = $linkN->length  ? absoluteUrl($linkN->item(0)->getAttribute('href'), $base) : '';
            $img    = bestImg($item, $x, $base);
            if ($nome && $preco > 0) {
                $products[] = compact('nome','preco','img','link');
            }
        }
        if (!empty($products)) return $products;
    }

    // ── Strategy 3: individual schema.org Product JSON-LD (Nuvemshop pattern) ──
    $scripts = $x->query('//script[@type="application/ld+json"]');
    $jsonProducts = [];
    foreach ($scripts as $s) {
        $data = json_decode($s->textContent, true);
        if (!$data) continue;

        // Individual Product (one per product card in Nuvemshop)
        if (($data['@type'] ?? '') === 'Product') {
            $nome  = $data['name'] ?? '';
            $preco = parseBrPrice((string)($data['offers']['price'] ?? $data['offers'][0]['price'] ?? 0));
            $img   = is_array($data['image'] ?? '') ? ($data['image'][0] ?? '') : ($data['image'] ?? '');
            $link  = $data['url'] ?? ($data['mainEntityOfPage']['@id'] ?? '');
            if ($nome && $preco > 0) {
                $jsonProducts[] = compact('nome','preco','img','link');
            }
            continue;
        }

        // ItemList fallback
        $list = ($data['@type'] ?? '') === 'ItemList' ? ($data['itemListElement'] ?? []) : [];
        if (empty($list) && isset($data['@graph'])) {
            foreach ($data['@graph'] as $n) {
                if (($n['@type'] ?? '') === 'Product') $list[] = ['item' => $n];
            }
        }
        foreach ($list as $entry) {
            $p = $entry['item'] ?? $entry;
            if (($p['@type'] ?? '') !== 'Product') continue;
            $nome  = $p['name'] ?? '';
            $preco = parseBrPrice((string)($p['offers']['price'] ?? $p['offers'][0]['price'] ?? 0));
            $img   = is_array($p['image'] ?? '') ? ($p['image'][0] ?? '') : ($p['image'] ?? '');
            $link  = $p['url'] ?? '';
            if ($nome && $preco > 0) {
                $jsonProducts[] = compact('nome','preco','img','link');
            }
        }
    }
    if (!empty($jsonProducts)) return $jsonProducts;

    return $products;
}

function downloadImg(string $url, string $destDir): string {
    if (empty($url)) return '';
    try {
        $ctx = stream_context_create(['http' => [
            'timeout'     => 15,
            'user_agent'  => 'Mozilla/5.0',
            'ignore_errors' => true,
        ], 'ssl' => ['verify_peer' => false]]);
        $data = @file_get_contents($url, false, $ctx);
        if (!$data) return '';
        $ext  = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) $ext = 'jpg';
        $name = uniqid('imp_', true) . '.' . $ext;
        $path = $destDir . $name;
        file_put_contents($path, $data);
        return 'assets/uploads/' . $name;
    } catch (Exception $e) { return ''; }
}

// ── State machine ────────────────────────────────────────────────────────────
$step  = $_POST['step'] ?? 'form';
$msg   = '';
$msgT  = 'error';
$scraped    = [];
$importados = 0;

// Categorias para dropdown
$categorias = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// ── STEP: Scrape preview (via URL fetch) ─────────────────────────────────────
if ($step === 'preview') {
    $url = trim($_POST['url'] ?? '');
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $msg  = 'URL inválida.';
        $step = 'form';
    } else {
        $html = fetchUrl($url);
        if (!$html) {
            $msg  = 'O servidor não conseguiu acessar a URL (bloqueio de hospedagem). Use a opção "Colar HTML" abaixo: abra a página no seu navegador → Ctrl+U → Ctrl+A → Ctrl+C → cole aqui.';
            $step = 'form';
        } else {
            $scraped = scrapeCategory($html, $url);
            if (empty($scraped)) {
                $msg  = 'Nenhum produto encontrado. Use a opção "Colar HTML" ou tente a URL de listagem de categoria (ex: /colecoes/tenis).';
                $step = 'form';
            }
        }
    }
}

// ── STEP: Scrape preview (via HTML paste) ────────────────────────────────────
if ($step === 'preview_html') {
    $html = trim($_POST['html_source'] ?? '');
    $url  = trim($_POST['base_url']    ?? 'https://sportchique.com.br');
    if (empty($html)) {
        $msg  = 'Cole o HTML da página antes de continuar.';
        $step = 'form';
    } else {
        $scraped = scrapeCategory($html, $url);
        $step    = 'preview';
        if (empty($scraped)) {
            $msg  = 'Nenhum produto encontrado no HTML colado. Certifique-se de copiar o código-fonte da página de categoria.';
            $step = 'form';
        }
    }
}

// ── STEP: Import ──────────────────────────────────────────────────────────────
if ($step === 'import') {
    $produtos_json = $_POST['produtos_json'] ?? '[]';
    $lista         = json_decode($produtos_json, true) ?: [];
    $selecionados  = $_POST['selecionados'] ?? [];
    $cat_id        = (int)($_POST['categoria_id'] ?? 0);
    $tipo          = ($_POST['tipo'] ?? 'fisico') === 'fisico' ? 'fisico' : 'digital';
    $destDir       = '../assets/uploads/';

    if (!$cat_id) {
        $msg  = 'Selecione uma categoria de destino.';
        $step = 'form';
    } else {
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao_curta, preco, imagem, categoria_id, tipo, destaque, frete_gratis) VALUES (?,?,?,?,?,?,0,1)");
        foreach ($lista as $i => $p) {
            if (!in_array((string)$i, $selecionados)) continue;
            $imgPath = downloadImg($p['img'], $destDir);
            try {
                $stmt->execute([$p['nome'], $p['nome'], $p['preco'], $imgPath, $cat_id, $tipo]);
                $importados++;
            } catch (Exception $e) {}
        }
        $msg  = "$importados produto(s) importado(s) com sucesso!";
        $msgT = 'success';
        $step = 'done';
    }
}

require_once 'templates/header_admin.php';
?>

<div class="w-full max-w-5xl mx-auto pb-20">

    <!-- ── Cabeçalho ── -->
    <div class="flex items-center gap-4 mb-8">
        <a href="gerenciar_produtos.php" class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-3xl font-black text-white">Importar Produtos em Massa</h1>
            <p class="text-admin-gray-400 text-sm mt-1">Raspa produtos de qualquer loja Nuvemshop, Shopify ou similar</p>
        </div>
    </div>

    <?php if ($msg): ?>
    <div class="mb-6 p-4 rounded-xl <?= $msgT === 'success' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30' ?>">
        <i class="fas <?= $msgT === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
        <?= htmlspecialchars($msg) ?>
        <?php if ($msgT === 'success'): ?>
        — <a href="gerenciar_produtos.php" class="underline">Ver produtos</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($step === 'form' || $step === 'done'): ?>
    <!-- ════ PASSO 1: Formulário ════ -->
    <div class="admin-card rounded-xl p-8">

        <!-- Abas -->
        <div class="flex border-b border-white/10 mb-6 gap-1">
            <button type="button" id="tab-url" onclick="switchTab('url')"
                    class="tab-btn px-5 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 border-admin-primary text-white bg-white/5">
                <i class="fas fa-link mr-2"></i>Buscar por URL
            </button>
            <button type="button" id="tab-html" onclick="switchTab('html')"
                    class="tab-btn px-5 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 border-transparent text-admin-gray-400 hover:text-white">
                <i class="fas fa-code mr-2"></i>Colar HTML
            </button>
        </div>

        <!-- Tab: URL -->
        <div id="pane-url">
            <p class="text-admin-gray-400 text-sm mb-5">Ex: <code class="text-admin-primary">https://sportchique.com.br/colecoes/tenis</code></p>
            <form method="POST" action="importar_produtos.php">
                <input type="hidden" name="step" value="preview">
                <label class="block text-sm font-medium text-admin-gray-300 mb-2">URL da categoria *</label>
                <input type="url" name="url" required placeholder="https://site.com.br/colecoes/categoria"
                       value="<?= htmlspecialchars($_POST['url'] ?? '') ?>"
                       class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-500 focus:border-admin-primary focus:outline-none">
                <button type="submit" class="mt-5 px-8 py-3 bg-admin-primary rounded-xl font-bold text-white hover:bg-admin-primary/80 transition-colors flex items-center gap-2">
                    <i class="fas fa-search"></i> Buscar Produtos
                </button>
            </form>
        </div>

        <!-- Tab: HTML Paste -->
        <div id="pane-html" class="hidden">
            <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 mb-5 text-sm text-yellow-300">
                <strong>Como usar:</strong> Abra a página de categoria no navegador →
                pressione <kbd class="bg-black/30 px-1.5 py-0.5 rounded text-xs">Ctrl+U</kbd> (Ver código-fonte) →
                <kbd class="bg-black/30 px-1.5 py-0.5 rounded text-xs">Ctrl+A</kbd> → <kbd class="bg-black/30 px-1.5 py-0.5 rounded text-xs">Ctrl+C</kbd> → cole aqui.
            </div>
            <form method="POST" action="importar_produtos.php">
                <input type="hidden" name="step" value="preview_html">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-admin-gray-300 mb-2">URL base (domínio do site) *</label>
                        <input type="url" name="base_url" required placeholder="https://sportchique.com.br"
                               value="<?= htmlspecialchars($_POST['base_url'] ?? '') ?>"
                               class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-500 focus:border-admin-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-admin-gray-300 mb-2">HTML da página de categoria *</label>
                        <textarea name="html_source" required rows="10" placeholder="Cole aqui o código-fonte completo da página (Ctrl+U no navegador)..."
                                  class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-500 focus:border-admin-primary focus:outline-none font-mono text-xs resize-y"></textarea>
                    </div>
                </div>
                <button type="submit" class="mt-5 px-8 py-3 bg-admin-primary rounded-xl font-bold text-white hover:bg-admin-primary/80 transition-colors flex items-center gap-2">
                    <i class="fas fa-magic"></i> Extrair Produtos
                </button>
            </form>
        </div>
    </div>

    <?php elseif ($step === 'preview' && !empty($scraped)): ?>
    <!-- ════ PASSO 2: Preview + seleção ════ -->
    <form method="POST" action="importar_produtos.php" id="import-form">
        <input type="hidden" name="step" value="import">
        <input type="hidden" name="produtos_json" value="<?= htmlspecialchars(json_encode($scraped)) ?>">

        <!-- Config de importação -->
        <div class="admin-card rounded-xl p-6 mb-6">
            <h2 class="text-lg font-bold text-white mb-4">Configurações de importação</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-admin-gray-300 mb-2">Categoria de destino *</label>
                    <select name="categoria_id" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:border-admin-primary focus:outline-none">
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-admin-gray-300 mb-2">Tipo de produto</label>
                    <select name="tipo" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:border-admin-primary focus:outline-none">
                        <option value="fisico" selected>Físico (roupas, tênis)</option>
                        <option value="digital">Digital (curso, e-book)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabela de produtos encontrados -->
        <div class="admin-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-white">
                    <?= count($scraped) ?> produto(s) encontrado(s)
                </h2>
                <div class="flex gap-3">
                    <button type="button" onclick="toggleAll(true)" class="text-xs px-3 py-1.5 rounded-lg bg-white/10 text-white hover:bg-white/20">
                        Selecionar todos
                    </button>
                    <button type="button" onclick="toggleAll(false)" class="text-xs px-3 py-1.5 rounded-lg bg-white/10 text-white hover:bg-white/20">
                        Desmarcar todos
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-admin-gray-400 text-xs uppercase">
                            <th class="pb-3 text-left w-8">#</th>
                            <th class="pb-3 text-left w-16">Foto</th>
                            <th class="pb-3 text-left">Nome</th>
                            <th class="pb-3 text-right w-28">Preço</th>
                            <th class="pb-3 text-center w-20">Importar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($scraped as $i => $p): ?>
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 text-admin-gray-500"><?= $i+1 ?></td>
                            <td class="py-3">
                                <?php if ($p['img']): ?>
                                <img src="<?= htmlspecialchars($p['img']) ?>" alt=""
                                     class="w-12 h-12 object-cover rounded-lg bg-admin-gray-800"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\'%3E%3Crect width=\'48\' height=\'48\' fill=\'%23333\'/%3E%3C/svg%3E'">
                                <?php else: ?>
                                <div class="w-12 h-12 rounded-lg bg-admin-gray-800 flex items-center justify-center text-admin-gray-600">
                                    <i class="fas fa-image"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-white font-medium">
                                <?= htmlspecialchars($p['nome']) ?>
                                <?php if ($p['link']): ?>
                                <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank"
                                   class="ml-2 text-admin-gray-500 hover:text-white text-xs" title="Ver original">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-right text-green-400 font-bold">
                                R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                            </td>
                            <td class="py-3 text-center">
                                <input type="checkbox" name="selecionados[]" value="<?= $i ?>"
                                       checked class="w-4 h-4 accent-admin-primary cursor-pointer">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between mt-6 pt-5 border-t border-white/10">
                <a href="importar_produtos.php" class="px-5 py-2.5 rounded-xl border border-white/10 text-white hover:bg-white/5 text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Voltar
                </a>
                <button type="submit" id="btn-import"
                        class="px-8 py-3 bg-admin-primary rounded-xl font-bold text-white hover:bg-admin-primary/80 transition-colors flex items-center gap-2">
                    <i class="fas fa-download"></i>
                    <span id="btn-import-txt">Importar Selecionados</span>
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>

</div>

<script>
function switchTab(tab) {
    ['url','html'].forEach(t => {
        document.getElementById('pane-' + t).classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('tab-' + t);
        if (t === tab) {
            btn.classList.add('border-admin-primary','text-white','bg-white/5');
            btn.classList.remove('border-transparent','text-admin-gray-400');
        } else {
            btn.classList.remove('border-admin-primary','text-white','bg-white/5');
            btn.classList.add('border-transparent','text-admin-gray-400');
        }
    });
}
// Auto-open HTML tab if error message mentions "Colar HTML"
document.addEventListener('DOMContentLoaded', () => {
    const errEl = document.querySelector('.bg-red-500\\/20');
    if (errEl && errEl.textContent.includes('Colar HTML')) switchTab('html');
});
function toggleAll(check) {
    document.querySelectorAll('input[name="selecionados[]"]').forEach(cb => cb.checked = check);
}
document.getElementById('import-form')?.addEventListener('submit', function() {
    const btn = document.getElementById('btn-import');
    btn.disabled = true;
    document.getElementById('btn-import-txt').textContent = 'Importando...';
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importando...';
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
