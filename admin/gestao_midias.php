<?php
// admin/gestao_midias.php - Premium Media Management
require_once 'secure.php';
$page_title = 'Gestão de Mídias';

// Filtros
$search = $_GET['search'] ?? '';
$tipo_filter = $_GET['tipo'] ?? '';

// Busca todas as mídias com filtros
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS midias (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        titulo VARCHAR(255), 
        tipo ENUM('imagem', 'video'), 
        path VARCHAR(255), 
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        grupo_id VARCHAR(50) DEFAULT NULL
    )");

    try { $pdo->exec("ALTER TABLE midias ADD COLUMN grupo_id VARCHAR(50) DEFAULT NULL"); } catch (PDOException $e) {}

    $sql = "SELECT * FROM midias WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND titulo LIKE :search";
        $params[':search'] = "%$search%";
    }
    if (!empty($tipo_filter)) {
        $sql .= " AND tipo = :tipo";
        $params[':tipo'] = $tipo_filter;
    }

    $sql .= " ORDER BY data_criacao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $all_midias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupa as mídias mantendo a lógica de posts agrupados
    $midias = [];
    foreach ($all_midias as $m) {
        $gid = $m['grupo_id'] ?: 'single_' . $m['id'];
        if (!isset($midias[$gid])) {
            $midias[$gid] = [
                'grupo_id' => $gid,
                'titulo' => $m['titulo'],
                'data_criacao' => $m['data_criacao'],
                'items' => []
            ];
        }
        $midias[$gid]['items'][] = $m;
    }

    // Statistcs
    $total_files = count($all_midias);
    $images_count = count(array_filter($all_midias, fn($m) => $m['tipo'] === 'imagem'));
    $videos_count = $total_files - $images_count;

} catch (PDOException $e) {
    $midias = [];
    $erro = $e->getMessage();
}

require_once 'templates/header_admin.php';
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black text-white uppercase tracking-tighter mb-2">Mídias Profissionais</h1>
            <p class="text-admin-gray-400 text-sm">Biblioteca centralizada de ativos visuais para marketing e redes.</p>
        </div>
        <button onclick="document.getElementById('modal-upload').classList.remove('hidden')"
            class="bg-white text-black font-black px-8 py-4 rounded-2xl hover:scale-105 transition-all flex items-center justify-center gap-3 shadow-xl shadow-white/5">
            <i class="fas fa-plus-circle"></i>
            FAZER UPLOAD
        </button>
    </div>

    <?php if (isset($_SESSION['admin_message'])): ?>
    <div class="bg-admin-primary/10 text-white p-4 rounded-xl text-center border border-admin-primary/20 text-sm animate-fade-in">
        <i class="fas fa-check-circle mr-2 text-admin-primary"></i> <?= $_SESSION['admin_message'] ?>
        <?php unset($_SESSION['admin_message']); ?>
    </div>
    <?php endif; ?>

    <!-- Stats & Filters -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Stats -->
        <div class="lg:col-span-4 grid grid-cols-3 gap-3">
            <div class="admin-card p-4 flex flex-col items-center justify-center bg-white/5 border border-white/5 rounded-2xl">
                <span class="text-lg font-black text-white"><?= $total_files ?></span>
                <span class="text-[9px] text-admin-gray-500 uppercase tracking-widest font-bold">Total</span>
            </div>
            <div class="admin-card p-4 flex flex-col items-center justify-center bg-white/5 border border-white/5 rounded-2xl">
                <span class="text-lg font-black text-admin-primary"><?= $images_count ?></span>
                <span class="text-[9px] text-admin-gray-500 uppercase tracking-widest font-bold">Fotos</span>
            </div>
            <div class="admin-card p-4 flex flex-col items-center justify-center bg-white/5 border border-white/5 rounded-2xl">
                <span class="text-lg font-black text-blue-400"><?= $videos_count ?></span>
                <span class="text-[9px] text-admin-gray-500 uppercase tracking-widest font-bold">Vídeos</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="lg:col-span-8">
            <form id="filter-form" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-admin-gray-500"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                        placeholder="Buscar pelo título da mídia..." 
                        class="w-full bg-admin-gray-800/40 border border-white/10 rounded-2xl py-4 pl-12 pr-4 text-sm text-white focus:border-white/30 outline-none transition-all">
                </div>
                <select name="tipo" onchange="this.form.submit()" 
                    class="bg-admin-gray-800/40 border border-white/10 rounded-2xl px-6 py-4 text-sm text-white focus:border-white/30 outline-none appearance-none cursor-pointer">
                    <option value="">Todos os tipos</option>
                    <option value="imagem" <?= $tipo_filter == 'imagem' ? 'selected' : '' ?>>Apenas Fotos</option>
                    <option value="video" <?= $tipo_filter == 'video' ? 'selected' : '' ?>>Apenas Vídeos</option>
                </select>
                <a href="gestao_midias.php" class="bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl px-6 flex items-center justify-center text-admin-gray-400 transition-all">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </form>
        </div>
    </div>

    <!-- Gallery Grid -->
    <?php if (empty($midias)): ?>
    <div class="admin-card rounded-3xl p-20 text-center border border-white/5 bg-white/[0.01]">
        <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-photo-video text-3xl text-admin-gray-600"></i>
        </div>
        <h3 class="text-white font-bold text-xl mb-2">Nenhum tesouro encontrado</h3>
        <p class="text-admin-gray-500 max-w-sm mx-auto">Ajuste seus filtros ou faça o upload de novos arquivos incríveis para sua biblioteca.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($midias as $grupo):
            $capa = $grupo['items'][0];
            $total = count($grupo['items']);
            $is_video = $capa['tipo'] === 'video';
            $grupo_id = $grupo['grupo_id'];
        ?>
        <div class="admin-card rounded-3xl overflow-hidden group border border-white/5 hover:border-white/20 hover:shadow-2xl hover:shadow-white/1 trash-transition flex flex-col relative bg-admin-gray-800/20 backdrop-blur-sm">
            
            <!-- Badge de Pack -->
            <?php if ($total > 1): ?>
            <div class="absolute top-4 right-4 z-20 bg-admin-primary px-3 py-1.5 rounded-full shadow-lg">
                <span class="text-[10px] font-black text-black flex items-center gap-1.5">
                    <i class="fas fa-layer-group"></i> PACK (<?= $total ?>)
                </span>
            </div>
            <?php endif; ?>

            <!-- Modal Preview Trigger Container -->
            <div class="relative aspect-[4/5] bg-black overflow-hidden cursor-pointer" onclick="openPreview('<?= $capa['path'] ?>', '<?= $capa['tipo'] ?>')">
                <?php if (!$is_video): ?>
                <img src="../<?= $capa['path'] ?>" alt="<?= htmlspecialchars($capa['titulo']) ?>"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                <?php else: ?>
                <video class="w-full h-full object-cover" muted loop onmouseover="this.play()" onmouseout="this.pause();this.currentTime=0;">
                    <source src="../<?= $capa['path'] ?>" type="video/mp4">
                </video>
                <div class="absolute inset-0 flex items-center justify-center bg-black/40 group-hover:bg-black/20 transition-all">
                    <i class="fas fa-play text-4xl text-white/50 group-hover:scale-125 group-hover:text-white transition-all drop-shadow-2xl"></i>
                </div>
                <?php endif; ?>

                <!-- Overlay Actions -->
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-6 gap-4">
                    <div class="flex gap-2">
                        <?php if ($total > 1): ?>
                            <a href="baixar_midia.php?grupo_id=<?= $grupo_id ?>" 
                               class="flex-1 bg-white text-black h-12 rounded-xl flex items-center justify-center font-bold text-xs uppercase tracking-tighter hover:bg-admin-primary transition-all">
                                <i class="fas fa-download mr-2"></i> BAIXAR PACK
                            </a>
                        <?php else: ?>
                            <a href="../<?= $capa['path'] ?>" download
                               class="flex-1 bg-white text-black h-12 rounded-xl flex items-center justify-center font-bold text-xs uppercase tracking-tighter hover:bg-admin-primary transition-all">
                                <i class="fas fa-download mr-2"></i> DOWNLOAD
                            </a>
                        <?php endif; ?>
                        
                        <button onclick="event.stopPropagation(); confirmarExclusao('<?= $grupo_id ?>')"
                            class="w-12 h-12 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-6 flex flex-col flex-1 border-t border-white/5">
                <div class="flex justify-between items-start gap-4 mb-4">
                    <h3 class="text-white font-bold text-sm line-clamp-2 uppercase tracking-tight leading-relaxed" id="titulo-<?= $grupo_id ?>">
                        <?= htmlspecialchars($grupo['titulo']) ?>
                    </h3>
                    <button type="button" onclick="copiarTextoBtn('titulo-<?= $grupo_id ?>', this)" 
                        class="w-8 h-8 rounded-lg bg-white/5 text-admin-gray-500 hover:text-white hover:bg-white/10 transition-all flex items-center justify-center shrink-0">
                        <i class="fas fa-copy text-xs"></i>
                    </button>
                </div>
                
                <div class="mt-auto flex items-center justify-between pt-4 border-t border-white/[0.03]">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full <?= $is_video ? 'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]' : 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]' ?>"></span>
                        <span class="text-[9px] text-admin-gray-500 uppercase font-black tracking-widest">
                            <?= $is_video ? 'Video Asset' : 'Photo Asset' ?>
                        </span>
                    </div>
                    <span class="text-[10px] text-admin-gray-600 font-mono">
                        <?= date('d M Y', strtotime($grupo['data_criacao'])) ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Preview (Ultra Premium) -->
<div id="modal-preview" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-12 hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/95 backdrop-blur-xl" onclick="closePreview()"></div>
    <div class="relative w-full max-w-5xl h-full flex flex-col">
        <button onclick="closePreview()" class="absolute -top-12 right-0 text-white hover:text-admin-primary text-2xl p-4 transition-colors">
            <i class="fas fa-times"></i>
        </button>
        <div class="flex-1 flex items-center justify-center relative bg-black/40 rounded-3xl overflow-hidden border border-white/5">
            <div id="preview-container" class="max-w-full max-h-full"></div>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div id="modal-upload" class="fixed inset-0 bg-black/90 backdrop-blur-md z-[100] flex items-center justify-center p-4 hidden">
    <div class="admin-card w-full max-w-xl rounded-[2.5rem] overflow-hidden border border-white/10 shadow-2xl animate-fade-in">
        <div class="p-8 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
            <div>
                <h2 class="text-2xl font-black text-white uppercase tracking-tighter">Exportar Midias</h2>
                <p class="text-admin-gray-500 text-xs">Selecione os melhores ângulos para seu catálogo.</p>
            </div>
            <button onclick="document.getElementById('modal-upload').classList.add('hidden')"
                class="w-12 h-12 rounded-2xl bg-white/5 text-admin-gray-400 hover:text-white hover:bg-white/10 transition-all flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="salvar_midia.php" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
            <div>
                <label class="block text-[10px] font-black text-admin-gray-500 uppercase tracking-[0.2em] mb-3 ml-1">Contexto ou Campanha</label>
                <input type="text" name="titulo" required placeholder="Ex: New Collection Spring/2026"
                    class="w-full p-5 bg-admin-gray-900/50 border border-white/10 rounded-2xl text-white placeholder-admin-gray-600 focus:border-white/30 outline-none transition-all">
            </div>

            <div class="space-y-4">
                <label class="block text-[10px] font-black text-admin-gray-500 uppercase tracking-[0.2em] mb-3 ml-1">Seleção Inteligente</label>
                <div class="relative group">
                    <input type="file" name="arquivo[]" required accept="image/*,video/*" id="input-arquivo" multiple class="hidden">
                    <label for="input-arquivo"
                        class="w-full py-16 border-2 border-dashed border-white/10 rounded-3xl flex flex-col items-center justify-center cursor-pointer hover:bg-white/[0.03] hover:border-admin-primary/40 transition-all group-hover:scale-[0.99]">
                        <div class="w-16 h-16 bg-admin-primary/10 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-cloud-arrow-up text-2xl text-admin-primary"></i>
                        </div>
                        <span class="text-sm font-bold text-white mb-2" id="label-texto">Arraste seus assets aqui</span>
                        <span class="text-[10px] text-admin-gray-500 uppercase tracking-widest font-black">Suporta Multi-seleção de Fotos e Vídeos</span>
                    </label>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-white text-black font-black py-6 rounded-[1.25rem] hover:scale-[1.02] active:scale-[0.98] transition-all uppercase tracking-widest text-xs shadow-xl shadow-white/5">
                INICIAR TRANSMISSÃO
            </button>
        </form>
    </div>
</div>

<script>
    const filterForm = document.getElementById('filter-form');
    const searchInput = filterForm.querySelector('input[name="search"]');
    let searchTimeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => filterForm.submit(), 800);
    });

    document.getElementById('input-arquivo').onchange = function () {
        const label = document.getElementById('label-texto');
        if (this.files.length > 0) {
            label.innerText = this.files.length === 1 ? this.files[0].name : `${this.files.length} Assets Selecionados`;
            label.className = "text-sm font-bold text-admin-primary";
        }
    };

    function confirmarExclusao(grupo_id) {
        if (confirm("ATENÇÃO: Deseja incinerar este registro? Esta ação removerá permanentemente todos os arquivos vinculados a esta postagem.")) {
            window.location.href = "excluir_midia.php?grupo_id=" + grupo_id;
        }
    }

    function openPreview(path, tipo) {
        const modal = document.getElementById('modal-preview');
        const container = document.getElementById('preview-container');
        container.innerHTML = tipo === 'video' 
            ? `<video src="../${path}" controls autoplay class="max-w-full max-h-[80vh] rounded-2xl"></video>`
            : `<img src="../${path}" class="max-w-full max-h-[80vh] rounded-2xl shadow-2xl object-contain">`;
        
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.add('opacity-100'), 10);
    }

    function closePreview() {
        const modal = document.getElementById('modal-preview');
        modal.classList.remove('opacity-100');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('preview-container').innerHTML = '';
        }, 300);
    }

    // Atalho ESC para fechar previews
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closePreview();
            document.getElementById('modal-upload').classList.add('hidden');
        }
    });
</script>

<style>
    @keyframes fade-in { 
        from { opacity: 0; transform: translateY(20px) scale(0.95); } 
        to { opacity: 1; transform: translateY(0) scale(1); } 
    }
    .animate-fade-in { animation: fade-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .trash-transition { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
</style>

<?php require_once 'templates/footer_admin.php'; ?>
