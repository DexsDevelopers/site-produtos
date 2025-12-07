<?php
// admin/gerenciar_banners.php
require_once 'secure.php';
require_once 'templates/header_admin.php';

// Busca todos os banners existentes
$banners = $pdo->query('SELECT * FROM banners ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-white">Gerenciar Banners</h2>
        <a href="editar_banner.php" class="bg-brand-red hover:bg-brand-red-dark text-white font-bold py-2 px-4 rounded-lg text-sm">
            <i class="fas fa-plus mr-1"></i>
            Novo Banner
        </a>
    </div>

    <div class="bg-brand-gray-light p-6 rounded-lg mb-8">
        <h3 class="text-xl font-semibold text-white mb-4">Adicionar Novo Banner</h3>
        <form action="processa_banner.php" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="titulo" class="block text-sm font-medium text-brand-gray-text">Título (opcional)</label>
                    <input type="text" name="titulo" class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray text-white">
                </div>
                <div>
                    <label for="subtitulo" class="block text-sm font-medium text-brand-gray-text">Subtítulo (opcional)</label>
                    <input type="text" name="subtitulo" class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray text-white">
                </div>
                <div>
                    <label for="link" class="block text-sm font-medium text-brand-gray-text">Link do Botão (opcional)</label>
                    <input type="text" name="link" class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray text-white">
                </div>
                <div>
                    <label for="tipo" class="block text-sm font-medium text-brand-gray-text">Tipo de Banner</label>
                    <select name="tipo" required class="w-full mt-1 p-3 bg-brand-gray rounded-lg border border-brand-gray text-white">
                        <option value="principal">Principal (Grande)</option>
                        <option value="categoria">Categoria (Menor)</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="imagem" class="block text-sm font-medium text-brand-gray-text">Imagem do Banner</label>
                    <input type="file" name="imagem" required class="w-full mt-1 p-2 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-red file:text-white hover:file:bg-brand-red-dark">
                </div>
            </div>
            <button type="submit" name="adicionar" class="w-full md:w-auto mt-4 bg-brand-red hover:bg-brand-red-dark text-white font-bold py-3 px-6 rounded-lg">
                Salvar Banner
            </button>
        </form>
    </div>

    <div class="bg-brand-gray-light p-6 rounded-lg">
        <?php
        if (isset($_SESSION['admin_message'])) {
            $message_type = $_SESSION['admin_message_type'] ?? 'info';
            $bg_color = $message_type === 'success' ? 'bg-green-500/20 text-green-300' : 
                       ($message_type === 'error' ? 'bg-red-500/20 text-red-300' : 'bg-blue-500/20 text-blue-300');
            echo '<div class="' . $bg_color . ' p-3 rounded-lg mb-4 text-center">' . $_SESSION['admin_message'] . '</div>';
            unset($_SESSION['admin_message']);
            unset($_SESSION['admin_message_type']);
        }
        ?>
         <table class="w-full text-left text-sm">
            <thead class="bg-brand-black text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">Imagem</th>
                    <th class="px-4 py-3">Título</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banners as $banner): ?>
                <tr class="border-b border-brand-gray">
                    <td class="px-4 py-3"><img src="../<?= htmlspecialchars($banner['imagem']) ?>" alt="Banner" class="h-16 object-cover rounded"></td>
                    <td class="px-4 py-3 text-white"><?= htmlspecialchars($banner['titulo']) ?></td>
                    <td class="px-4 py-3 text-white"><?= htmlspecialchars($banner['tipo']) ?></td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="editar_banner.php?id=<?= $banner['id'] ?>" class="font-medium text-blue-500 hover:underline">
                                Editar
                            </a>
                            <a href="processa_banner.php?deletar=<?= $banner['id'] ?>" class="font-medium text-red-500 hover:underline" onclick="return confirm('Tem certeza que deseja deletar este banner?');">
                                Deletar
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'templates/footer_admin.php';
?>