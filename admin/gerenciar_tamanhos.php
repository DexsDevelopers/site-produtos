<?php
// admin/gerenciar_tamanhos.php â€” Gerenciamento de Grupos de Tamanho
require_once 'secure.php';
$page_title = 'Tamanhos';
require_once 'templates/header_admin.php';

// Busca grupos com contagem de tamanhos
try {
    $grupos = $pdo->query("
        SELECT gt.*, COUNT(t.id) as total_tamanhos 
        FROM grupos_tamanho gt 
        LEFT JOIN tamanhos t ON t.grupo_id = gt.id 
        GROUP BY gt.id 
        ORDER BY gt.nome ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    $grupos = [];
    // Tabela nÃ£o existe â€” redireciona para setup
    header('Location: setup_tamanhos.php');
    exit;
}

// Busca tamanhos de cada grupo
$tamanhos_por_grupo = [];
foreach ($grupos as $grupo) {
    $stmt = $pdo->prepare("SELECT * FROM tamanhos WHERE grupo_id = ? ORDER BY ordem ASC");
    $stmt->execute([$grupo['id']]);
    $tamanhos_por_grupo[$grupo['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Grupos de Tamanho</h1>
            <p class="text-admin-gray-400">Gerencie os tamanhos predefinidos para produtos fÃ­sicos</p>
        </div>
    </div>

    <!-- InformaÃ§Ã£o -->
    <div class="admin-card rounded-xl p-5 border-l-4 border-blue-500">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
            <div>
                <p class="text-sm text-white/80 leading-relaxed">
                    Crie grupos de tamanho (ex: "TÃªnis", "Roupas") com os tamanhos predefinidos.
                    Depois, ao adicionar ou editar um produto fÃ­sico, basta selecionar o grupo e os tamanhos
                    disponÃ­veis.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form: Novo Grupo -->
        <div class="lg:col-span-1">
            <div class="space-y-6">
                <h2 class="text-xl font-bold text-white">Novo Grupo</h2>
                <div class="admin-card p-6 rounded-xl">
                    <form action="processa_tamanho.php" method="POST">
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">
                                    Nome do Grupo
                                </label>
                                <input type="text" name="nome_grupo" required placeholder="Ex: TÃªnis, Roupas..."
                                    class="w-full">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">
                                    DescriÃ§Ã£o (opcional)
                                </label>
                                <input type="text" name="descricao_grupo" placeholder="Ex: NumeraÃ§Ã£o para calÃ§ados"
                                    class="w-full">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">
                                    Tamanhos <span class="text-white/40">(separados por vÃ­rgula)</span>
                                </label>
                                <input type="text" name="tamanhos" required placeholder="Ex: 36,37,38,39,40,41,42"
                                    class="w-full">
                                <p class="text-xs text-admin-gray-500 mt-1">Separe os tamanhos por vÃ­rgula. Ex:
                                    PP,P,M,G,GG ou 36,37,38,39</p>
                            </div>
                            <button type="submit" name="criar_grupo"
                                class="btn btn-primary w-full bg-white text-black hover:bg-gray-200">
                                <i class="fas fa-plus mr-2"></i> Criar Grupo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Grupos -->
        <div class="lg:col-span-2">
            <h2 class="text-xl font-bold text-white mb-6">Grupos Existentes</h2>

            <?php if (empty($grupos)): ?>
            <div class="admin-card rounded-xl p-12 text-center">
                <i class="fas fa-ruler-combined text-4xl text-admin-gray-600 mb-4 block"></i>
                <p class="text-admin-gray-400 text-lg mb-2">Nenhum grupo de tamanho criado</p>
                <p class="text-admin-gray-500 text-sm">Crie seu primeiro grupo usando o formulÃ¡rio ao lado.</p>
            </div>
            <?php
else: ?>
            <div class="space-y-4">
                <?php foreach ($grupos as $grupo): ?>
                <div class="admin-card rounded-xl overflow-hidden" id="grupo-<?= $grupo['id']?>">
                    <!-- Grupo Header -->
                    <div
                        class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-ruler-combined text-white/70"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">
                                    <?= htmlspecialchars($grupo['nome'])?>
                                </h3>
                                <?php if (!empty($grupo['descricao'])): ?>
                                <p class="text-xs text-admin-gray-500">
                                    <?= htmlspecialchars($grupo['descricao'])?>
                                </p>
                                <?php
        endif; ?>
                            </div>
                            <span class="text-xs bg-white/10 text-white/60 px-2.5 py-1 rounded-full font-medium">
                                <?= $grupo['total_tamanhos']?> tamanhos
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="processa_tamanho.php?deletar_grupo=<?= $grupo['id']?>"
                                onclick="return confirm('Excluir o grupo e todos os tamanhos? Produtos com esse grupo perderÃ£o a configuraÃ§Ã£o de tamanhos.')"
                                class="text-red-400 hover:text-red-300 text-sm px-3 py-1 rounded-lg hover:bg-red-500/10 transition-colors">
                                <i class="fas fa-trash mr-1"></i> Excluir
                            </a>
                        </div>
                    </div>

                    <!-- Tamanhos -->
                    <div class="p-5">
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php if (!empty($tamanhos_por_grupo[$grupo['id']])): ?>
                            <?php foreach ($tamanhos_por_grupo[$grupo['id']] as $tamanho): ?>
                            <div
                                class="group relative inline-flex items-center gap-1 bg-white/5 border border-white/10 px-3 py-1.5 rounded-lg text-sm font-medium text-white">
                                <?= htmlspecialchars($tamanho['valor'])?>
                                <a href="processa_tamanho.php?deletar_tamanho=<?= $tamanho['id']?>&grupo_id=<?= $grupo['id']?>"
                                    class="text-red-400 hover:text-red-300 opacity-0 group-hover:opacity-100 transition-opacity ml-1"
                                    title="Remover tamanho">
                                    <i class="fas fa-times text-[10px]"></i>
                                </a>
                            </div>
                            <?php
            endforeach; ?>
                            <?php
        else: ?>
                            <p class="text-admin-gray-500 text-sm">Nenhum tamanho neste grupo.</p>
                            <?php
        endif; ?>
                        </div>

                        <!-- Adicionar tamanhos ao grupo -->
                        <form action="processa_tamanho.php" method="POST" class="flex gap-2">
                            <input type="hidden" name="grupo_id" value="<?= $grupo['id']?>">
                            <input type="text" name="novos_tamanhos" placeholder="Adicionar: 45,46,47"
                                class="flex-1 text-sm !py-2">
                            <button type="submit" name="adicionar_tamanhos"
                                class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
                                <i class="fas fa-plus mr-1"></i> Adicionar
                            </button>
                        </form>
                    </div>
                </div>
                <?php
    endforeach; ?>
            </div>
            <?php
endif; ?>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>