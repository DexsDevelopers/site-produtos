<?php
// admin/adicionar_produto.php
require_once 'secure.php';
$page_title = 'Adicionar Produto';
require_once 'templates/header_admin.php';
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY ordem ASC')->fetchAll(PDO::FETCH_ASSOC);

// Busca grupos de tamanho
$grupos_tamanho = [];
try {
    $grupos_tamanho = $pdo->query("SELECT * FROM grupos_tamanho ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
}

// Busca tamanhos por grupo (para JS)
$tamanhos_json = [];
foreach ($grupos_tamanho as $g) {
    $stmt = $pdo->prepare("SELECT id, valor FROM tamanhos WHERE grupo_id = ? ORDER BY ordem ASC");
    $stmt->execute([$g['id']]);
    $tamanhos_json[$g['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Persistência de configurações básicas para cadastro em massa
$config = $_SESSION['last_product_config'] ?? [];
$last_tipo = $config['tipo'] ?? 'digital';
$last_categoria = $config['categoria_id'] ?? 0;
$last_grupo_tamanho = $config['grupo_tamanho_id'] ?? 0;
$last_tamanhos_selecionados = $config['tamanhos_selecionados'] ?? [];
$last_tamanhos_estoque = $config['tamanhos_estoque'] ?? [];
?>

<div class="w-full max-w-4xl mx-auto pb-20">
    <h1 class="text-3xl font-black text-white mb-8">Adicionar Novo Produto</h1>

    <div class="admin-card rounded-xl p-8">
        <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="bg-admin-primary/20 text-admin-primary p-4 rounded-lg mb-6 text-center">
            <?= $_SESSION['admin_message']?>
        </div>
        <?php unset($_SESSION['admin_message']); ?>
        <?php
endif; ?>

        <form action="salvar_produto.php" method="POST" enctype="multipart/form-data">
            <div class="space-y-6">
                <!-- Nome -->
                <div>
                    <label for="nome" class="block text-sm font-medium text-admin-gray-300 mb-2">Nome do Produto</label>
                    <input type="text" name="nome" required
                        class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none">
                </div>

                <!-- Descrições -->
                <div>
                    <label for="descricao_curta" class="block text-sm font-medium text-admin-gray-300 mb-2">Descrição
                        Curta (vitrine)</label>
                    <input type="text" name="descricao_curta" maxlength="100"
                        class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none">
                </div>
                <div>
                    <label for="descricao" class="block text-sm font-medium text-admin-gray-300 mb-2">Descrição
                        Completa</label>
                    <textarea name="descricao" rows="5"
                        class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none resize-vertical"></textarea>
                </div>

                <!-- Preços -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="preco" class="block text-sm font-medium text-admin-gray-300 mb-2">Preço (ex:
                            197.00)</label>
                        <input type="text" name="preco" required
                            class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none">
                    </div>
                    <div>
                        <label for="preco_antigo" class="block text-sm font-medium text-admin-gray-300 mb-2">Preço
                            Antigo (opcional)</label>
                        <input type="text" name="preco_antigo"
                            class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white placeholder-admin-gray-400 focus:border-admin-primary focus:ring-2 focus:ring-admin-primary/20 focus:outline-none">
                    </div>
                </div>

                <!-- Categoria -->
                <div>
                    <label for="categoria_id"
                        class="block text-sm font-medium text-admin-gray-300 mb-2">Categoria</label>
                    <select name="categoria_id" required
                        class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:border-admin-primary focus:outline-none">
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id']?>" <?= $last_categoria == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome'])?>
                        </option>
                        <?php
endforeach; ?>
                    </select>
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-sm font-medium text-admin-gray-300 mb-3">Tipo do Produto</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="tipo-card relative cursor-pointer">
                            <input type="radio" name="tipo" value="digital" <?= $last_tipo === 'digital' ? 'checked' : ''
    ?> class="sr-only peer">
                            <div
                                class="flex flex-col items-center gap-2 p-4 rounded-xl border border-white/10 bg-white/5 peer-checked:border-white peer-checked:bg-white/10 transition-all">
                                <i class="fas fa-cloud-download-alt text-xl text-blue-400"></i>
                                <span class="text-sm font-semibold text-white">Digital</span>
                                <span class="text-[10px] text-admin-gray-500 text-center">Software, Curso, E-book</span>
                            </div>
                        </label>
                        <label class="tipo-card relative cursor-pointer">
                            <input type="radio" name="tipo" value="fisico" <?= $last_tipo === 'fisico' ? 'checked' : '' ?>
                            class="sr-only peer">
                            <div
                                class="flex flex-col items-center gap-2 p-4 rounded-xl border border-white/10 bg-white/5 peer-checked:border-white peer-checked:bg-white/10 transition-all">
                                <i class="fas fa-tshirt text-xl text-green-400"></i>
                                <span class="text-sm font-semibold text-white">Físico</span>
                                <span class="text-[10px] text-admin-gray-500 text-center">Roupas, Tênis,
                                    Acessórios</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Tamanhos -->
                <div id="tamanhos-section" class="<?= $last_tipo === 'fisico' ? '' : 'hidden'?>">
                    <div class="p-5 rounded-xl border border-white/10 bg-white/[0.02] space-y-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-ruler-combined text-admin-gray-400"></i>
                            <label class="text-sm font-medium text-admin-gray-300">Grade de Tamanhos</label>
                        </div>

                        <select name="grupo_tamanho_id" id="grupo-tamanho-select"
                            class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white">
                            <option value="">Selecione o grupo de tamanhos</option>
                            <?php foreach ($grupos_tamanho as $gt): ?>
                            <option value="<?= $gt['id']?>" <?= $last_grupo_tamanho == $gt['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($gt['nome'])?>
                            </option>
                            <?php
endforeach; ?>
                        </select>

                        <div id="tamanhos-container" class="hidden space-y-4 pt-4 border-t border-white/5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-admin-gray-400 uppercase tracking-widest">Opções
                                    Disponíveis</label>
                                <div class="flex items-center gap-2 bg-white/5 p-1 rounded-lg border border-white/10">
                                    <input type="number" id="bulk-stock" placeholder="Qtd" min="0"
                                        class="w-14 p-1 bg-admin-gray-900 border-none rounded text-xs text-center text-white focus:ring-0">
                                    <button type="button" onclick="aplicarEstoqueEmMassa()"
                                        class="text-[9px] font-black uppercase bg-white text-black px-2 py-1 rounded hover:bg-gray-200">Aplicar</button>
                                </div>
                            </div>

                            <div id="tamanhos-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                <!-- JS -->
                            </div>

                            <div class="flex gap-4 pt-2">
                                <button type="button" onclick="selecionarTodos()"
                                    class="text-[10px] uppercase font-bold text-blue-400 hover:text-white transition-colors">Selecionar
                                    Todos</button>
                                <button type="button" onclick="deselecionarTodos()"
                                    class="text-[10px] uppercase font-bold text-admin-gray-500 hover:text-white transition-colors">Limpar
                                    Seleção</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Imagem -->
                <div>
                    <label for="imagem" class="block text-sm font-medium text-admin-gray-300 mb-2">Imagem do
                        Produto</label>
                    <input type="file" name="imagem" required accept="image/*"
                        class="w-full p-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-bold file:bg-white file:text-black hover:file:bg-gray-200">
                </div>

                <!-- Destaque -->
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="destaque" value="1"
                        class="w-5 h-5 bg-admin-gray-800 border-admin-gray-600 rounded text-admin-primary focus:ring-admin-primary/20">
                    <span
                        class="text-sm font-medium text-admin-gray-300 group-hover:text-white transition-colors">Exibir
                        em destaque na página inicial</span>
                </label>
            </div>

            <button type="submit" name="adicionar"
                class="w-full mt-10 bg-white text-black font-black text-lg py-5 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all uppercase tracking-tighter">
                Adicionar Produto ao Catálogo
            </button>
        </form>
    </div>
</div>

<script>
    const tamanhosPorGrupo = <?= json_encode($tamanhos_json)?>;
    const tamanhosSelecionados = <?= json_encode($last_tamanhos_selecionados)?>;
    const lastTamanhosEstoque = <?= json_encode($last_tamanhos_estoque)?>;

    // Toggle seção Físico/Digital
    document.querySelectorAll('input[name="tipo"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const section = document.getElementById('tamanhos-section');
            section.classList.toggle('hidden', this.value !== 'fisico');
        });
    });

    function renderTamanhos(grupoId) {
        const container = document.getElementById('tamanhos-container');
        const list = document.getElementById('tamanhos-list');

        if (!grupoId || !tamanhosPorGrupo[grupoId]) {
            container.classList.add('hidden');
            list.innerHTML = '';
            return;
        }

        container.classList.remove('hidden');
        list.innerHTML = '';

        tamanhosPorGrupo[grupoId].forEach(tam => {
            const isChecked = tamanhosSelecionados.length === 0 ||
                tamanhosSelecionados.includes(tam.id.toString()) ||
                tamanhosSelecionados.includes(parseInt(tam.id));

            const savedStock = lastTamanhosEstoque[tam.id] || 0;

            const div = document.createElement('div');
            div.className = 'flex items-center justify-between gap-3 p-3 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-all';
            div.innerHTML = `
                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                    <input type="checkbox" name="tamanhos_selecionados[]" value="${tam.id}" ${isChecked ? 'checked' : ''} 
                           class="w-5 h-5 bg-admin-gray-800 border-admin-gray-600 rounded text-admin-primary focus:ring-0">
                    <span class="text-sm font-bold text-white uppercase">${tam.valor}</span>
                </label>
                <div class="flex items-center gap-2 bg-admin-gray-900/50 p-1.5 px-3 rounded-lg border border-white/5">
                    <span class="text-[9px] font-black text-admin-gray-500 uppercase tracking-tighter">Estoque</span>
                    <input type="number" name="estoque_${tam.id}" value="${savedStock}" min="0" 
                           class="w-14 bg-transparent border-none p-0 text-sm font-bold text-white text-center focus:ring-0">
                </div>
            `;
            list.appendChild(div);
        });
    }

    const select = document.getElementById('grupo-tamanho-select');
    if (select) {
        select.addEventListener('change', (e) => renderTamanhos(e.target.value));
        if (select.value) renderTamanhos(select.value);
    }

    function selecionarTodos() {
        document.querySelectorAll('#tamanhos-list input[type="checkbox"]').forEach(cb => cb.checked = true);
    }

    function deselecionarTodos() {
        document.querySelectorAll('#tamanhos-list input[type="checkbox"]').forEach(cb => cb.checked = false);
    }

    function aplicarEstoqueEmMassa() {
        const val = document.getElementById('bulk-stock').value;
        if (val === '') return;
        document.querySelectorAll('#tamanhos-list div').forEach(div => {
            const cb = div.querySelector('input[type="checkbox"]');
            const input = div.querySelector('input[type="number"]');
            if (cb && cb.checked && input) input.value = val;
        });
    }
</script>

<?php require_once 'templates/footer_admin.php'; ?>