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
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $midias = $pdo->query("SELECT * FROM midias ORDER BY data_criacao DESC")->fetchAll(PDO::FETCH_ASSOC);
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
        <?php foreach ($midias as $midia): ?>
        <div
            class="admin-card rounded-2xl overflow-hidden group border border-white/5 hover:border-white/20 transition-all flex flex-col">
            <div class="relative aspect-video bg-black flex items-center justify-center overflow-hidden">
                <?php if ($midia['tipo'] === 'imagem'): ?>
                <img src="../<?= $midia['path']?>" alt="<?= htmlspecialchars($midia['titulo'])?>"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                <?php
        else: ?>
                <video class="w-full h-full object-cover">
                    <source src="../<?= $midia['path']?>" type="video/mp4">
                </video>
                <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                    <i class="fas fa-play-circle text-4xl text-white group-hover:scale-110 transition-transform"></i>
                </div>
                <?php
        endif; ?>

                <!-- Overlay de Ações -->
                <div
                    class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                    <a href="../<?= $midia['path']?>" download
                        class="w-10 h-10 bg-white text-black rounded-full flex items-center justify-center hover:scale-110 transition-all"
                        title="Baixar">
                        <i class="fas fa-download"></i>
                    </a>
                    <button onclick="confirmarExclusao(<?= $midia['id']?>)"
                        class="w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center hover:scale-110 transition-all"
                        title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <div class="p-4 bg-white/[0.02]">
                <h3 class="text-white font-bold text-sm truncate uppercase tracking-tight">
                    <?= htmlspecialchars($midia['titulo'])?>
                </h3>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-[10px] text-admin-gray-500 uppercase font-bold flex items-center gap-1">
                        <i class="fas <?= $midia['tipo'] === 'imagem' ? 'fa-image' : 'fa-video'?>"></i>
                        <?= $midia['tipo'] === 'imagem' ? 'Foto' : 'Vídeo'?>
                    </span>
                    <span class="text-[10px] text-admin-gray-600">
                        <?= date('d/m/Y', strtotime($midia['data_criacao']))?>
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
                <label class="block text-xs font-bold text-admin-gray-400 uppercase tracking-widest mb-3">Arquivo (Foto
                    ou Vídeo)</label>
                <div class="relative group">
                    <input type="file" name="arquivo" required accept="image/*,video/*" id="input-arquivo"
                        class="hidden">
                    <label for="input-arquivo"
                        class="w-full py-10 border-2 border-dashed border-white/10 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:bg-white/5 hover:border-white/20 transition-all group-hover:border-admin-primary/50">
                        <i
                            class="fas fa-cloud-upload-alt text-4xl text-admin-gray-600 group-hover:text-admin-primary transition-colors mb-3"></i>
                        <span class="text-sm font-bold text-admin-gray-400 group-hover:text-white"
                            id="label-texto">Clique para selecionar</span>
                        <span class="text-[10px] text-admin-gray-600 mt-1 uppercase">Imagens ou vídeos (MP4)</span>
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
        if (this.files[0]) {
            document.getElementById('label-texto').innerText = this.files[0].name;
            document.getElementById('label-texto').classList.add('text-admin-primary');
        }
    };

    function confirmarExclusao(id) {
        if (confirm("Tem certeza que deseja excluir esta mídia? Esta ação não pode ser desfeita.")) {
            window.location.href = "excluir_midia.php?id=" + id;
        }
    }
</script>

<?php require_once 'templates/footer_admin.php'; ?>