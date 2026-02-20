<?php
// admin/gestao_midias.php
require_once 'secure.php';
$page_title = 'Gestão de Mídias';
require_once 'templates/header_admin.php';

// Busca todas as mídias
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS midias (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        titulo VARCHAR(255), 
        tipo ENUM('imagem', 'video'), 
        path VARCHAR(255), 
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        grupo_id VARCHAR(50) DEFAULT NULL
    )");

    // Tenta adicionar a coluna grupo_id caso ela não exista (para compatibilidade)
    try {
        $pdo->exec("ALTER TABLE midias ADD COLUMN grupo_id VARCHAR(50) DEFAULT NULL");
    }
    catch (PDOException $e) {
    // Coluna já existe, ignora
    }

    $all_midias = $pdo->query("SELECT * FROM midias ORDER BY data_criacao DESC")->fetchAll(PDO::FETCH_ASSOC);

    // Agrupa as mídias
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
}
catch (PDOException $e) {
    $midias = [];
    $_SESSION['admin_message'] = "Erro ao acessar mídias: " . $e->getMessage();
}
?>

<div class="w-full max-w-6xl mx-auto pb-20 px-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-white uppercase tracking-tighter">Gestão de Mídias</h1>
            <p class="text-admin-gray-400 text-sm">Organize fotos e vídeos para suas redes sociais.</p>
        </div>
        <button onclick="document.getElementById('modal-upload').classList.remove('hidden')"
            class="bg-white text-black font-black px-6 py-3 rounded-xl hover:scale-105 transition-all flex items-center justify-center gap-2">
            <i class="fas fa-cloud-upload-alt"></i>
            UPLOAD DE MÍDIA
        </button>
    </div>

    <?php if (isset($_SESSION['admin_message'])): ?>
    <div class="bg-admin-primary/20 text-admin-primary p-4 rounded-xl mb-8 text-center border border-admin-primary/30">
        <?= $_SESSION['admin_message']?>
    </div>
    <?php unset($_SESSION['admin_message']); ?>
    <?php
endif; ?>

    <?php if (empty($midias)): ?>
    <div class="admin-card rounded-2xl p-12 text-center border border-white/5">
        <i class="fas fa-photo-video text-6xl text-white/10 mb-4"></i>
        <p class="text-admin-gray-400 text-lg font-medium">Nenhuma mídia encontrada. Comece fazendo um upload!</p>
    </div>
    <?php
else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($midias as $grupo):
        $capa = $grupo['items'][0]; // Primeira mídia é a capa
        $total = count($grupo['items']);
        $is_video = $capa['tipo'] === 'video';
        $grupo_id = $grupo['grupo_id'];
?>
        <div class="admin-card rounded-2xl overflow-hidden group border border-white/5 hover:border-white/20 transition-all flex flex-col relative">
            
            <!-- Contador se houver mais de 1 -->
            <?php if ($total > 1): ?>
            <div class="absolute top-3 right-3 z-20 bg-black/60 backdrop-blur-md px-2 py-1 rounded-lg border border-white/10">
                <span class="text-xs font-bold text-white flex items-center gap-2">
                    <i class="fas fa-layer-group text-admin-primary"></i>
                    +<?= $total - 1?>
                </span>
            </div>
            <?php
        endif; ?>

            <div class="relative aspect-video bg-black flex items-center justify-center overflow-hidden">
                <?php if (!$is_video): ?>
                <img src="../<?= $capa['path']?>" alt="<?= htmlspecialchars($capa['titulo'])?>"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                <?php
        else: ?>
                <video class="w-full h-full object-cover" muted loop onmouseover="this.play()" onmouseout="this.pause();this.currentTime=0;">
                    <source src="../<?= $capa['path']?>" type="video/mp4">
                </video>
                <div class="absolute inset-0 flex items-center justify-center bg-black/40 pointer-events-none">
                    <i class="fas fa-play-circle text-4xl text-white group-hover:scale-110 transition-transform"></i>
                </div>
                <?php
        endif; ?>

                <!-- Overlay de Ações -->
                <div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-4">
                    
                    <div class="flex gap-3">
                        <!-- Botão Baixar (Single ou Zip) -->
                        <?php if ($total > 1): ?>
                            <a href="baixar_midia.php?grupo_id=<?= $grupo_id?>" 
                               class="w-12 h-12 bg-admin-primary text-black rounded-full flex items-center justify-center hover:scale-110 transition-all shadow-lg shadow-admin-primary/20"
                               title="Baixar Todas (ZIP)">
                                <i class="fas fa-file-archive text-lg"></i>
                            </a>
                        <?php
        else: ?>
                            <a href="../<?= $capa['path']?>" download
                               class="w-12 h-12 bg-white text-black rounded-full flex items-center justify-center hover:scale-110 transition-all shadow-lg"
                               title="Baixar">
                                <i class="fas fa-download text-lg"></i>
                            </a>
                        <?php
        endif; ?>

                        <!-- Botão Excluir (Todo o grupo) -->
                        <button onclick="confirmarExclusao('<?= $grupo_id?>')"
                            class="w-12 h-12 bg-red-500 text-white rounded-full flex items-center justify-center hover:scale-110 transition-all shadow-lg shadow-red-500/20"
                            title="Excluir Postagem">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </button>
                    </div>

                    <span class="text-white/60 text-xs font-medium uppercase tracking-widest">
                        <?= $total > 1 ? 'Baixar Pack Completo' : 'Baixar Mídia'?>
                    </span>

                </div>
            </div>

            <div class="p-4 bg-white/[0.02] flex flex-col flex-1">
                <div class="flex justify-between items-start gap-3">
                    <h3 class="text-white font-bold text-sm break-words whitespace-pre-wrap uppercase tracking-tight flex-1" id="titulo-<?= $grupo_id ?>">
                        <?= htmlspecialchars($grupo['titulo'])?>
                    </h3>
                    <button type="button" onclick="copiarTextoBtn('titulo-<?= $grupo_id ?>', this)" class="text-admin-gray-400 hover:text-white transition-colors pt-0.5 shrink-0" title="Copiar Texto">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-[10px] text-admin-gray-500 uppercase font-bold flex items-center gap-1">
                        <i class="fas <?= $is_video ? 'fa-video' : 'fa-image'?>"></i>
                        <?= $is_video ? 'Vídeo' : 'Imagem'?>
                        <?php if ($total > 1)
            echo " + " . ($total - 1) . " extras"; ?>
                    </span>
                    <span class="text-[10px] text-admin-gray-600">
                        <?= date('d/m/Y', strtotime($grupo['data_criacao']))?>
                    </span>
                </div>
            </div>
        </div>
        <?php
    endforeach; ?>
    </div>
    <?php
endif; ?>
</div>

<!-- Modal de Upload -->
<div id="modal-upload"
    class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="admin-card w-full max-w-lg rounded-2xl overflow-hidden border border-white/10">
        <div class="p-6 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
            <h2 class="text-xl font-black text-white uppercase tracking-tighter">Novo Upload</h2>
            <button onclick="document.getElementById('modal-upload').classList.add('hidden')"
                class="text-admin-gray-400 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="salvar_midia.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <div>
                <label class="block text-xs font-bold text-admin-gray-400 uppercase tracking-widest mb-3">Título da
                    Mídia</label>
                <input type="text" name="titulo" required placeholder="Ex: Campanha Verão 2024"
                    class="w-full p-4 bg-admin-gray-800 border border-admin-gray-700 rounded-xl text-white placeholder-admin-gray-500 focus:border-admin-primary focus:ring-1 focus:ring-admin-primary/20 outline-none">
            </div>

            <div class="space-y-3">
                <label class="block text-xs font-bold text-admin-gray-400 uppercase tracking-widest mb-3">Arquivos (Selecione um ou mais)</label>
                <div class="relative group">
                    <input type="file" name="arquivo[]" required accept="image/*,video/*" id="input-arquivo" multiple
                        class="hidden">
                    <label for="input-arquivo"
                        class="w-full py-10 border-2 border-dashed border-white/10 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:bg-white/5 hover:border-white/20 transition-all group-hover:border-admin-primary/50">
                        <i class="fas fa-images text-4xl text-admin-gray-600 group-hover:text-admin-primary transition-colors mb-3"></i>
                        <span class="text-sm font-bold text-admin-gray-400 group-hover:text-white"
                            id="label-texto">Clique para selecionar fotos/vídeos</span>
                        <span class="text-[10px] text-admin-gray-600 mt-1 uppercase">Suporta seleção múltipla</span>
                    </label>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-white text-black font-black py-5 rounded-2xl hover:scale-[1.02] active:scale-[0.98] transition-all uppercase tracking-tighter">
                INICIAR UPLOAD
            </button>
        </form>
    </div>
</div>

<script>
    document.getElementById('input-arquivo').onchange = function () {
        if (this.files.length > 0) {
            if (this.files.length === 1) {
                document.getElementById('label-texto').innerText = this.files[0].name;
            } else {
                document.getElementById('label-texto').innerText = this.files.length + " arquivos selecionados";
            }
            document.getElementById('label-texto').classList.add('text-admin-primary');
        }
    };

    function confirmarExclusao(grupo_id) {
        if (confirm("Tem certeza que deseja excluir esta postagem? Todas as mídias dela serão apagadas.")) {
            window.location.href = "excluir_midia.php?grupo_id=" + grupo_id;
        }
    }
</script>

<?php require_once 'templates/footer_admin.php'; ?>