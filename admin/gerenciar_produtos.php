<?php
// admin/gerenciar_produtos.php - Listagem de Produtos Premium com Edição em Massa
require_once "secure.php";
$page_title = "Meus Produtos";

// Filtros
$search = $_GET["search"] ?? "";
$categoria_id = $_GET["categoria_id"] ?? "";
$ordem = $_GET["ordem"] ?? "recente";

// Busca categorias para o filtro e edição em massa
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Monta SQL com filtros
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.nome LIKE :search OR p.id = :id_search)";
    $params[":search"] = "%$search%";
    $params[":id_search"] = $search;
}

if (!empty($categoria_id)) {
    $sql .= " AND p.categoria_id = :cat_id";
    $params[":cat_id"] = $categoria_id;
}

switch ($ordem) {
    case "recente": $sql .= " ORDER BY p.id DESC"; break;
    case "antigo":  $sql .= " ORDER BY p.id ASC"; break;
    case "preco_alto": $sql .= " ORDER BY p.preco DESC"; break;
    case "preco_baixo": $sql .= " ORDER BY p.preco ASC"; break;
    default: $sql .= " ORDER BY p.id DESC";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Stats
    $total_produtos = count($produtos);
    $total_destaques = count(array_filter($produtos, fn($p) => $p["destaque"] == 1));
} catch (Exception $e) {
    $produtos = [];
}

require_once "templates/header_admin.php";
?>

<div class="space-y-6 pb-24">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Produtos</h1>
            <p class="text-admin-gray-400">Gerencie seu catálogo completo e realize ações em lote</p>
        </div>
        <a href="adicionar_produto.php"
            class="w-full sm:w-auto px-6 py-3 bg-white text-black font-bold rounded-xl hover:opacity-90 transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
            <i class="fas fa-plus"></i> NOVO PRODUTO
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="admin-card p-4 flex items-center gap-4 bg-white/5 border border-white/5 rounded-2xl">
            <div class="w-10 h-10 rounded-xl bg-admin-primary/10 flex items-center justify-center text-admin-primary">
                <i class="fas fa-box"></i>
            </div>
            <div>
                <p class="text-[10px] text-admin-gray-500 font-bold uppercase tracking-widest">Total</p>
                <p class="text-lg font-bold text-white"><?= $total_produtos ?></p>
            </div>
        </div>
        <div class="admin-card p-4 flex items-center gap-4 bg-white/5 border border-white/5 rounded-2xl">
            <div class="w-10 h-10 rounded-xl bg-yellow-500/10 flex items-center justify-center text-yellow-500">
                <i class="fas fa-star"></i>
            </div>
            <div>
                <p class="text-[10px] text-admin-gray-500 font-bold uppercase tracking-widest">Destaques</p>
                <p class="text-lg font-bold text-white"><?= $total_destaques ?></p>
            </div>
        </div>
    </div>

    <!-- Barra de Ferramentas / Filtros -->
    <div class="admin-card p-4 bg-admin-gray-800/40 border border-white/5 rounded-2xl flex flex-col lg:flex-row gap-4">
        <form method="GET" class="flex-1 relative" id="filter-form">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-admin-gray-500"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                placeholder="Buscar por nome ou ID..." 
                class="w-full bg-admin-gray-900 border border-white/10 rounded-xl py-3 pl-12 pr-4 text-sm text-white focus:border-white/30 focus:outline-none transition-all">
            
            <input type="hidden" name="categoria_id" value="<?= htmlspecialchars($categoria_id) ?>">
            <input type="hidden" name="ordem" value="<?= htmlspecialchars($ordem) ?>">
        </form>

        <div class="flex flex-wrap gap-2">
            <select onchange="window.location.href = atualizarParametroUrl(&quot;categoria_id&quot;, this.value)"
                class="bg-admin-gray-900 border border-white/10 rounded-xl py-3 px-4 text-xs text-white focus:border-white/30 focus:outline-none cursor-pointer">
                <option value="">Todas Categorias</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat["id"] ?>" <?= $categoria_id == $cat["id"] ? "selected" : "" ?>>
                        <?= htmlspecialchars($cat["nome"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select onchange="window.location.href = atualizarParametroUrl(&quot;ordem&quot;, this.value)"
                class="bg-admin-gray-900 border border-white/10 rounded-xl py-3 px-4 text-xs text-white focus:border-white/30 focus:outline-none cursor-pointer">
                <option value="recente" <?= $ordem == "recente" ? "selected" : "" ?>>Mais Recentes</option>
                <option value="antigo" <?= $ordem == "antigo" ? "selected" : "" ?>>Mais Antigos</option>
                <option value="preco_alto" <?= $ordem == "preco_alto" ? "selected" : "" ?>>Preço (Maior)</option>
                <option value="preco_baixo" <?= $ordem == "preco_baixo" ? "selected" : "" ?>>Preço (Menor)</option>
            </select>

            <a href="gerenciar_produtos.php" class="w-11 h-11 flex items-center justify-center bg-white/5 border border-white/10 rounded-xl text-admin-gray-400 hover:text-white transition-all">
                <i class="fas fa-sync-alt"></i>
            </a>
        </div>
    </div>

    <!-- Tabela Responsiva / Cards -->
    <form id="bulk-form" action="processar_lote_produtos.php" method="POST">
        <div class="admin-card overflow-hidden bg-admin-gray-800/20 border border-white/5 rounded-2xl relative">
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-6 py-4 text-left w-10">
                                <input type="checkbox" id="select-all" class="rounded border-white/10 bg-white/5 text-admin-primary focus:ring-0 focus:ring-offset-0 transition-all cursor-pointer">
                            </th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Produto</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Categoria</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Preço</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Frete</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Destaque</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-admin-gray-500 uppercase tracking-widest">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (!empty($produtos)): ?>
                        <?php foreach ($produtos as $produto): ?>
                        <tr class="group hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox" name="produtos[]" value="<?= $produto["id"] ?>" class="product-checkbox rounded border-white/10 bg-white/5 text-admin-primary focus:ring-0 focus:ring-offset-0 transition-all cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-xl bg-admin-gray-800 flex-shrink-0 border border-white/10 overflow-hidden relative group-hover:border-white/20 transition-all">
                                        <?php if (!empty($produto["imagem"])): ?>
                                        <img class="h-full w-full object-cover transition-transform group-hover:scale-110"
                                            src="../<?= htmlspecialchars($produto["imagem"])?>" alt="">
                                        <?php else: ?>
                                        <div class="h-full w-full flex items-center justify-center text-admin-gray-700"><i class="fas fa-image"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-bold text-white truncate max-w-xs transition-colors group-hover:text-admin-primary">
                                            <?= htmlspecialchars($produto["nome"])?>
                                        </div>
                                        <div class="text-[10px] text-admin-gray-600 font-mono mt-0.5">ID: <?= $produto["id"]?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-bold bg-white/5 text-admin-gray-400 border border-white/5 uppercase tracking-wider">
                                    <?= htmlspecialchars($produto["categoria_nome"] ?? "Sem Categoria")?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-black text-white"><?= formatarPreco($produto["preco"]) ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($produto["frete_gratis"]): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[8px] font-black uppercase bg-green-500/10 text-green-500 border border-green-500/20">Grátis</span>
                                <?php else: ?>
                                    <span class="text-[8px] text-admin-gray-600 font-bold uppercase">Pago</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="toggle_destaque_produto.php?id=<?= $produto["id"]?>&destaque=<?= $produto["destaque"] ? 0 : 1?>"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[9px] font-bold uppercase tracking-widest transition-all <?= $produto["destaque"] ? "bg-yellow-500/10 text-yellow-500 border border-yellow-500/20" : "bg-admin-gray-800 text-admin-gray-600 border border-white/5"?>">
                                    <i class="fas <?= $produto["destaque"] ? "fa-star" : "fa-star"?>"></i>
                                    <?= $produto["destaque"] ? "Ativo" : "Não"?>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="editar_produto.php?id=<?= $produto["id"]?>"
                                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/5 text-admin-gray-400 hover:bg-white hover:text-black transition-all">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <a href="deletar_produto.php?id=<?= $produto["id"]?>" onclick="return confirm(&quot;Excluir este produto?&quot;)"
                                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-500/5 text-red-500/50 hover:bg-red-500 hover:text-white transition-all">
                                        <i class="fas fa-trash text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Layout: Cards -->
            <div class="md:hidden divide-y divide-white/5">
                <?php if (!empty($produtos)): ?>
                <?php foreach ($produtos as $produto): ?>
                <div class="p-4 space-y-4">
                    <div class="flex gap-4">
                        <div class="flex items-center pr-2">
                           <input type="checkbox" name="produtos[]" value="<?= $produto["id"] ?>" class="product-checkbox rounded border-white/10 bg-white/5 text-admin-primary focus:ring-0 cursor-pointer">
                        </div>
                        <div class="h-20 w-20 rounded-2xl bg-admin-gray-800 border border-white/10 overflow-hidden flex-shrink-0">
                            <?php if (!empty($produto["imagem"])): ?>
                            <img class="h-full w-full object-cover" src="../<?= htmlspecialchars($produto["imagem"])?>" alt="">
                            <?php else: ?>
                            <div class="h-full w-full flex items-center justify-center text-admin-gray-700"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="text-white font-bold text-sm leading-tight mb-1"><?= htmlspecialchars($produto["nome"])?></h3>
                            <p class="text-[10px] text-admin-gray-500 mb-2 uppercase tracking-tight"><?= htmlspecialchars($produto["categoria_nome"] ?? "Sem Categoria")?></p>
                            <div class="flex items-center gap-2">
                                <span class="text-white font-black text-base"><?= formatarPreco($produto["preco"]) ?></span>
                                <?php if ($produto["frete_gratis"]): ?>
                                    <span class="px-1.5 py-0.5 rounded bg-green-500/10 text-green-500 text-[8px] font-black uppercase">F. Grátis</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bulk Action Bar -->
        <div id="bulk-bar" class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[90%] max-w-4xl bg-white text-black p-4 rounded-2xl shadow-2xl flex flex-wrap items-center justify-between gap-4 z-50 transition-all duration-500 translate-y-32 opacity-0">
            <div class="flex items-center gap-4">
                <span class="bg-black text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-tighter" id="bulk-count">0 SELECIONADOS</span>
                <div class="h-8 w-px bg-black/10 hidden sm:block"></div>
                <div class="flex gap-2">
                    <select name="bulk_action" id="bulk_action_select" class="bg-transparent border-0 font-bold text-xs focus:ring-0 cursor-pointer">
                        <option value="">AÇÕES EM MASSA...</option>
                        <option value="change_category">Alterar Categoria</option>
                        <option value="adjust_price">Ajustar Preço (%)</option>
                        <option value="adjust_price_fixed">Ajustar Preço (R$)</option>
                        <option value="set_price_fixed">Definir Novo Preço (R$)</option>
                        <option value="set_free_shipping">Ativar Frete Grátis</option>
                        <option value="unset_free_shipping">Remover Frete Grátis</option>
                        <option value="set_featured">Marcar como Destaque</option>
                        <option value="unset_featured">Remover Destaque</option>
                        <option value="delete">Excluir Permanente</option>
                    </select>

                    <!-- Container para campos extras (categoria, preço, etc) -->
                    <div id="bulk_extra_fields" class="flex items-center">
                        <!-- Será preenchido via JS -->
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <button type="button" onclick="cancelSelection()" class="text-[10px] font-bold uppercase tracking-widest hover:underline px-4">Cancelar</button>
                <button type="submit" class="bg-black text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:opacity-90 active:scale-95 transition-all">
                    Executar Lote
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function atualizarParametroUrl(param, value) {
    const url = new URL(window.location.href);
    if (value) url.searchParams.set(param, value);
    else url.searchParams.delete(param);
    return url.pathname + url.search;
}

document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.querySelector("input[name=&quot;search&quot;]");
    const filterForm = document.getElementById("filter-form");
    const selectAll = document.getElementById("select-all");
    const checkboxes = document.querySelectorAll(".product-checkbox");
    const bulkBar = document.getElementById("bulk-bar");
    const bulkCount = document.getElementById("bulk-count");
    const bulkActionSelect = document.getElementById("bulk_action_select");
    const bulkExtraFields = document.getElementById("bulk_extra_fields");

    // Debounce na busca
    let timeout = null;
    searchInput.addEventListener("input", function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => filterForm.submit(), 600);
    });

    if (searchInput.value) {
        searchInput.focus();
        const val = searchInput.value; searchInput.value = ""; searchInput.value = val;
    }

    // Seleção em lote
    function updateBulkBar() {
        const checked = document.querySelectorAll(".product-checkbox:checked");
        if (checked.length > 0) {
            bulkBar.classList.remove("translate-y-32", "opacity-0");
            bulkCount.innerText = `${checked.length} SELECIONADOS`;
        } else {
            bulkBar.classList.add("translate-y-32", "opacity-0");
        }
    }

    selectAll.addEventListener("change", function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener("change", updateBulkBar);
    });

    // Campos extras para ações
    bulkActionSelect.addEventListener("change", function() {
        bulkExtraFields.innerHTML = "";
        const action = this.value;

        if (action === "change_category") {
            const select = document.createElement("select");
            select.name = "bulk_category_id";
            select.required = true;
            select.className = "bg-black/5 border-0 rounded-lg text-xs font-bold focus:ring-0 px-3 py-2 cursor-pointer";
            select.innerHTML = "<option value=&quot;&quot;>Escolha...</option>";
            <?php foreach($categorias as $cat): ?>
            select.innerHTML += "<option value=&quot;<?= $cat["id"] ?>&quot;><?= htmlspecialchars($cat["nome"]) ?></option>";
            <?php endforeach; ?>
            bulkExtraFields.appendChild(select);
        } else if (action === "adjust_price") {
            const input = document.createElement("input");
            input.type = "number";
            input.name = "bulk_price_adjustment";
            input.placeholder = "Ex: 10 ou -15";
            input.required = true;
            input.className = "bg-black/5 border-0 rounded-lg text-xs font-bold focus:ring-0 px-3 py-2 w-24";
            bulkExtraFields.appendChild(input);
            const span = document.createElement("span");
            span.innerText = "%";
            span.className = "ml-1 font-bold text-xs";
            bulkExtraFields.appendChild(span);
        } else if (action === "adjust_price_fixed") {
            const input = document.createElement("input");
            input.type = "number";
            input.step = "0.01";
            input.name = "bulk_price_adjustment_fixed";
            input.placeholder = "Ex: 50.00 ou -20.00";
            input.required = true;
            input.className = "bg-black/5 border-0 rounded-lg text-xs font-bold focus:ring-0 px-3 py-2 w-32";
            bulkExtraFields.appendChild(input);
            const span = document.createElement("span");
            span.innerText = "R$";
            span.className = "ml-1 font-bold text-xs";
            bulkExtraFields.prepend(span);
        } else if (action === "set_price_fixed") {
            const input = document.createElement("input");
            input.type = "number";
            input.step = "0.01";
            input.name = "bulk_price_set_fixed";
            input.placeholder = "Ex: 299.90";
            input.required = true;
            input.className = "bg-black/5 border-0 rounded-lg text-xs font-bold focus:ring-0 px-3 py-2 w-32";
            bulkExtraFields.appendChild(input);
            const span = document.createElement("span");
            span.innerText = "R$";
            span.className = "ml-1 font-bold text-xs";
            bulkExtraFields.prepend(span);
        }
    });

    window.cancelSelection = function() {
        checkboxes.forEach(cb => cb.checked = false);
        selectAll.checked = false;
        updateBulkBar();
    };

    document.getElementById("bulk-form").onsubmit = function(e) {
        if (!bulkActionSelect.value) {
            alert("Selecione uma ação para executar.");
            return false;
        }
        if (bulkActionSelect.value === "delete" && !confirm("ATENÇÃO: Deseja realmente excluir todos os produtos selecionados? Esta ação é irreversível.")) {
            return false;
        }
    };
});
</script>

<?php require_once "templates/footer_admin.php"; ?>
