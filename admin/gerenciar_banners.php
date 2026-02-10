<?php
// admin/gerenciar_banners.php - Premium Design
require_once 'secure.php';
$page_title = 'Banners';
require_once 'templates/header_admin.php';

try {
    $banners = $pdo->query('SELECT * FROM banners ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    $banners = [];
}
?>

<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Banners</h1>
            <p class="text-admin-gray-400">Gerencie os destaques visuais da loja</p>
        </div>
    </div>

    <!-- Adicionar Banner Card -->
    <div class="admin-card p-6 md:p-8">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
            <i class="fas fa-plus-circle text-admin-gray-400 text-sm"></i> Novo Banner
        </h3>

        <form action="processa_banner.php" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Coluna 1 -->
                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Título
                            (Opcional)</label>
                        <input type="text" name="titulo" placeholder="Ex: Nova Coleção" class="w-full">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Subtítulo
                            (Opcional)</label>
                        <input type="text" name="subtitulo" placeholder="Ex: Descontos de até 50%" class="w-full">
                    </div>
                </div>

                <!-- Coluna 2 -->
                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Link
                            de Destino</label>
                        <input type="text" name="link" placeholder="Ex: categoria.php?id=1" class="w-full">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Tipo
                            de Banner</label>
                        <select name="tipo" required class="w-full">
                            <option value="principal">Banner Principal (Hero)</option>
                            <option value="secundario">Banner Secundário</option>
                        </select>
                    </div>
                </div>

                <!-- Upload -->
                <div class="md:col-span-2">
                    <label
                        class="block text-xs font-semibold text-admin-gray-400 uppercase tracking-wider mb-2">Imagem</label>
                    <div class="relative group">
                        <input type="file" name="imagem" required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="banner-input">
                        <div
                            class="w-full p-8 border-2 border-dashed border-admin-gray-600 rounded-xl bg-admin-gray-800/50 flex flex-col items-center justify-center gap-2 group-hover:border-white transition-colors">
                            <i
                                class="fas fa-cloud-upload-alt text-3xl text-admin-gray-400 group-hover:text-white transition-colors"></i>
                            <span class="text-sm text-admin-gray-400 group-hover:text-white">Clique ou arraste a imagem
                                aqui</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" name="adicionar"
                    class="btn btn-primary bg-white text-black hover:bg-gray-200 px-8">
                    Salvar Banner
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Banners -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($banners as $banner): ?>
        <div class="admin-card overflow-hidden group hover:border-white/30 transition-all">
            <!-- Imagem -->
            <div class="relative h-48 bg-admin-gray-800 overflow-hidden">
                <img src="../<?= htmlspecialchars($banner['imagem'])?>" alt="Banner"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                <div class="absolute bottom-4 left-4 right-4">
                    <span
                        class="inline-block px-2 py-1 bg-white/10 backdrop-blur-md rounded-md text-[10px] uppercase tracking-wide text-white mb-2 border border-white/10">
                        <?= htmlspecialchars($banner['tipo'])?>
                    </span>
                    <h4 class="text-white font-bold truncate">
                        <?= htmlspecialchars($banner['titulo'] ?? 'Sem Título')?>
                    </h4>
                </div>
            </div>

            <!-- Ações -->
            <div class="p-4 flex items-center justify-between border-t border-white/5 bg-admin-gray-800/30">
                <div class="text-sm text-admin-gray-500 truncate max-w-[150px]">
                    <?= htmlspecialchars($banner['link'] ?? '#')?>
                </div>
                <div class="flex gap-2">
                    <a href="editar_banner.php?id=<?= $banner['id']?>"
                        class="p-2 hover:bg-white/10 rounded-lg text-admin-primary transition-colors">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="processa_banner.php?deletar=<?= $banner['id']?>"
                        class="p-2 hover:bg-red-500/10 rounded-lg text-red-500 transition-colors"
                        onclick="return confirm('Excluir este banner?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php
endforeach; ?>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>