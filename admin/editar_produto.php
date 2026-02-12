<?php
// admin/editar_produto.php
require_once 'secure.php';
$page_title = 'Editar Produto';
require_once 'templates/header_admin.php';
$produto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produto = null;
if ($produto_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { die("Erro ao buscar produto: " . $e->getMessage()); }
}
if (!$produto) { $_SESSION['admin_message'] = "Produto não encontrado."; header("Location: index.php"); exit(); }
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY ordem ASC')->fetchAll(PDO::FETCH_ASSOC);

// Tipo do produto (default: digital)
$tipo_produto = $produto['tipo'] ?? 'digital';
$grupo_tamanho_id = $produto['grupo_tamanho_id'] ?? null;

// Busca grupos de tamanho
$grupos_tamanho = [];
try {
    $grupos_tamanho = $pdo->query("SELECT * FROM grupos_tamanho ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Busca tamanhos por grupo (para JS)
$tamanhos_json = [];
foreach ($grupos_tamanho as $g) {
    $stmt = $pdo->prepare("SELECT id, valor FROM tamanhos WHERE grupo_id = ? ORDER BY ordem ASC");
    $stmt->execute([$g['id']]);
    $tamanhos_json[$g['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Busca tamanhos já selecionados e seus estoques para este produto
$tamanhos_estoque = [];
try {
    $stmt = $pdo->prepare("SELECT tamanho_id, estoque FROM produto_tamanhos WHERE produto_id = ?");
    $stmt->execute([$produto_id]);
    $tamanhos_estoque = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {}
$tamanhos_selecionados = array_keys($tamanhos_estoque);
?>
<div class="w-full max-w-4xl mx-auto">
    <h1 class="text-3xl font-black text-white mb-8">Editando: <?= htmlspecialchars($produto['nome']) ?></h1>
    <div class="admin-card rounded-xl p-8">
        <?php if (isset($_SESSION['admin_message'])) { echo '<div class="bg-admin-primary/20 text-admin-primary p-4 rounded-lg mb-6 text-center">' . $_SESSION['admin_message'] . '</div>'; unset($_SESSION['admin_message']); } ?>
        <form action="salvar_produto.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $produto['id'] ?>">
            <div class="space-y-6">
                <div><label for="nome" class="block text-sm font-medium text-admin-gray-300 mb-2">Nome do Produto</label><input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                <div><label for="descricao_curta" class="block text-sm font-medium text-admin-gray-300 mb-2">Descrição Curta</label><input type="text" name="descricao_curta" maxlength="100" value="<?= htmlspecialchars($produto['descricao_curta']) ?>" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                <div><label for="descricao" class="block text-sm font-medium text-admin-gray-300 mb-2">Descrição Completa</label><textarea name="descricao" rows="5" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none resize-vertical"><?= htmlspecialchars($produto['descricao']) ?></textarea></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label for="preco" class="block text-sm font-medium text-admin-gray-300 mb-2">Preço</label><input type="text" name="preco" value="<?= htmlspecialchars($produto['preco']) ?>" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                    <div><label for="preco_antigo" class="block text-sm font-medium text-admin-gray-300 mb-2">Preço Antigo</label><input type="text" name="preco_antigo" value="<?= htmlspecialchars($produto['preco_antigo']) ?>" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none"></div>
                </div>
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-admin-gray-300 mb-2">Categoria</label>
                    <select name="categoria_id" required class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none">
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $categoria): ?><option value="<?= $categoria['id'] ?>" <?= ($produto['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nome']) ?></option><?php endforeach; ?>
                    </select>
                </div>

                <!-- ═══ TIPO DO PRODUTO ═══ -->
                <div>
                    <label class="block text-sm font-medium text-admin-gray-300 mb-3">Tipo do Produto</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="tipo-card relative cursor-pointer">
                            <input type="radio" name="tipo" value="digital" <?= $tipo_produto === 'digital' ? 'checked' : '' ?> class="sr-only peer">
                            <div class="flex flex-col items-center gap-2 p-4 rounded-xl border border-white/10 bg-white/5 peer-checked:border-white peer-checked:bg-white/10 transition-all">
                                <i class="fas fa-cloud-download-alt text-xl text-blue-400"></i>
                                <span class="text-sm font-semibold text-white">Digital</span>
                                <span class="text-[10px] text-admin-gray-500 text-center">Streamings, cursos, e-books</span>
                            </div>
                        </label>
                        <label class="tipo-card relative cursor-pointer">
                            <input type="radio" name="tipo" value="fisico" <?= $tipo_produto === 'fisico' ? 'checked' : '' ?> class="sr-only peer">
                            <div class="flex flex-col items-center gap-2 p-4 rounded-xl border border-white/10 bg-white/5 peer-checked:border-white peer-checked:bg-white/10 transition-all">
                                <i class="fas fa-tshirt text-xl text-green-400"></i>
                                <span class="text-sm font-semibold text-white">Físico</span>
                                <span class="text-[10px] text-admin-gray-500 text-center">Roupas, tênis, acessórios</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- ═══ SELEÇÃO DE TAMANHOS ═══ -->
                <div id="tamanhos-section" class="<?= $tipo_produto === 'fisico' ? '' : 'hidden' ?>">
                    <div class="p-5 rounded-xl border border-white/10 bg-white/[0.02] space-y-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-ruler-combined text-admin-gray-400"></i>
                            <label class="text-sm font-medium text-admin-gray-300">Grupo de Tamanhos</label>
                        </div>

                        <?php if (empty($grupos_tamanho)): ?>
                        <div class="text-center py-4">
                            <p class="text-admin-gray-500 text-sm mb-2">Nenhum grupo de tamanho cadastrado.</p>
                            <a href="gerenciar_tamanhos.php" class="text-blue-400 hover:text-blue-300 text-sm font-medium">
                                <i class="fas fa-plus mr-1"></i> Criar Grupo de Tamanhos
                            </a>
                        </div>
                        <?php else: ?>
                        <select name="grupo_tamanho_id" id="grupo-tamanho-select"
                            class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:border-admin-primary focus:outline-none">
                            <option value="">Selecione o grupo de tamanhos</option>
                            <?php foreach ($grupos_tamanho as $gt): ?>
                            <option value="<?= $gt['id'] ?>" <?= ($grupo_tamanho_id == $gt['id']) ? 'selected' : '' ?>><?= htmlspecialchars($gt['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <div id="tamanhos-checkboxes" class="<?= $grupo_tamanho_id ? '' : 'hidden' ?>">
                            <label class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-3">
                                Selecione os tamanhos e defina o estoque
                            </label>
                            <div id="tamanhos-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                <!-- Preenchido via JS -->
                            </div>
                            <div class="flex gap-2 mt-4 pt-3 border-t border-white/5">
                                <button type="button" onclick="selecionarTodos()" class="text-xs text-blue-400 hover:text-blue-300 font-medium">
                                    Marcar Todos
                                </button>
                                <span class="text-admin-gray-600">|</span>
                                <button type="button" onclick="deselecionarTodos()" class="text-xs text-admin-gray-500 hover:text-white font-medium">
                                    Desmarcar Todos
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="imagem" class="block text-sm font-medium text-admin-gray-300 mb-2">Nova Imagem (opcional)</label>
                    <input type="file" name="imagem" accept="image/*" class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-admin-primary file:text-white hover:file:bg-blue-600 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none">
                    <p class="text-xs text-admin-gray-400 mt-2">Imagem atual: <img src="../<?= htmlspecialchars($produto['imagem']) ?>" class="h-10 inline-block rounded"></p>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="destaque" id="destaque" value="1" <?= $produto['destaque'] ? 'checked' : '' ?> class="w-5 h-5 bg-admin-gray-800 border-admin-gray-600 rounded text-admin-primary focus:ring-admin-primary/20">
                    <label for="destaque" class="text-sm font-medium text-admin-gray-300">Marcar como Destaque na Home</label>
                </div>
            </div>
            <button type="submit" name="editar" class="w-full mt-8 bg-admin-primary hover:bg-blue-600 text-white font-bold text-lg py-4 rounded-lg transition-colors">Salvar Alterações</button>
        </form>
    </div>
</div>

<script>
const tamanhosPorGrupo = <?= json_encode($tamanhos_json) ?>;
const tamanhosEstoque = <?= json_encode($tamanhos_estoque) ?>;
const tamanhosSelecionados = <?= json_encode($tamanhos_selecionados) ?>;

// Toggle seção de tamanhos
document.querySelectorAll('input[name="tipo"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const section = document.getElementById('tamanhos-section');
        if (this.value === 'fisico') {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    });
});

// Renderizar tamanhos para um grupo
function renderTamanhos(grupoId) {
    const container = document.getElementById('tamanhos-checkboxes');
    const list = document.getElementById('tamanhos-list');
    
    if (!grupoId || !tamanhosPorGrupo[grupoId]) {
        container.classList.add('hidden');
        list.innerHTML = '';
        return;
    }

    container.classList.remove('hidden');
    list.innerHTML = '';
    
    tamanhosPorGrupo[grupoId].forEach(tam => {
        const isChecked = tamanhosSelecionados.includes(tam.id) || tamanhosSelecionados.includes(String(tam.id));
        const estoque = tamanhosEstoque[tam.id] || 0;
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 p-2 rounded-lg bg-white/5 border border-white/10 hover:border-white/20 transition-all';
        div.innerHTML = `
            <label class="flex items-center gap-2 cursor-pointer flex-1">
                <input type="checkbox" name="tamanhos_selecionados[]" value="${tam.id}" ${isChecked ? 'checked' : ''} 
                       class="w-4 h-4 bg-admin-gray-800 border-admin-gray-600 rounded text-admin-primary focus:ring-admin-primary/20">
                <span class="text-sm font-medium text-white">${tam.valor}</span>
            </label>
            <div class="flex items-center gap-1">
                <span class="text-[10px] text-admin-gray-500 uppercase font-bold">Estoque:</span>
                <input type="number" name="estoque_${tam.id}" value="${estoque}" min="0" 
                       class="w-16 p-1 bg-admin-gray-900 border border-admin-gray-700 rounded text-xs text-white text-center focus:border-admin-primary focus:outline-none">
            </div>
        `;
        list.appendChild(div);
    });
}

// Quando grupo de tamanho muda
const grupoSelect = document.getElementById('grupo-tamanho-select');
if (grupoSelect) {
    grupoSelect.addEventListener('change', function() {
        renderTamanhos(this.value);
    });
    // Renderizar tamanhos iniciais se grupo já está selecionado
    if (grupoSelect.value) {
        renderTamanhos(grupoSelect.value);
    }
}

function selecionarTodos() {
    document.querySelectorAll('#tamanhos-list input[type="checkbox"]').forEach(cb => cb.checked = true);
}

function deselecionarTodos() {
    document.querySelectorAll('#tamanhos-list input[type="checkbox"]').forEach(cb => cb.checked = false);
}
</script>

<?php require_once 'templates/footer_admin.php'; ?>