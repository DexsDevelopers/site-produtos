<?php
// admin/editar_banner.php - Editor Avançado de Banners
require_once 'secure.php';
require_once 'templates/header_admin.php';

$banner_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$banner = null;

if ($banner_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$banner_id]);
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$banner) {
            $_SESSION['admin_message'] = "Banner não encontrado!";
            header("Location: gerenciar_banners.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao buscar banner: " . $e->getMessage();
        header("Location: gerenciar_banners.php");
        exit();
    }
}
?>

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">
                <?= $banner ? 'Editar Banner' : 'Novo Banner' ?>
            </h1>
            <p class="text-admin-gray-400 mt-2">
                <?= $banner ? 'Modifique os dados do banner' : 'Crie um novo banner para destacar produtos ou promoções' ?>
            </p>
        </div>
        <a href="gerenciar_banners.php" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Formulário -->
        <div class="lg:col-span-2">
            <div class="admin-card rounded-xl p-8">
                <form action="processa_banner.php" method="POST" enctype="multipart/form-data" id="bannerForm">
                    <input type="hidden" name="banner_id" value="<?= $banner_id ?>">
                    
                    <!-- Informações Básicas -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold text-white mb-4">Informações Básicas</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="titulo" class="block text-sm font-medium text-admin-gray-300 mb-2">
                                    Título
                                </label>
                                <input type="text" 
                                       name="titulo" 
                                       id="titulo"
                                       value="<?= htmlspecialchars($banner['titulo'] ?? '') ?>"
                                       class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                                       placeholder="Título do banner">
                            </div>

                            <div>
                                <label for="subtitulo" class="block text-sm font-medium text-admin-gray-300 mb-2">
                                    Subtítulo
                                </label>
                                <input type="text" 
                                       name="subtitulo" 
                                       id="subtitulo"
                                       value="<?= htmlspecialchars($banner['subtitulo'] ?? '') ?>"
                                       class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                                       placeholder="Subtítulo do banner">
                            </div>
                        </div>

                        <div>
                            <label for="link" class="block text-sm font-medium text-admin-gray-300 mb-2">
                                Link do Botão
                            </label>
                            <input type="url" 
                                   name="link" 
                                   id="link"
                                   value="<?= htmlspecialchars($banner['link'] ?? '') ?>"
                                   class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                                   placeholder="https://exemplo.com">
                        </div>

                        <div>
                            <label for="texto_botao" class="block text-sm font-medium text-admin-gray-300 mb-2">
                                Texto do Botão
                            </label>
                            <input type="text" 
                                   name="texto_botao" 
                                   id="texto_botao"
                                   value="<?= htmlspecialchars($banner['texto_botao'] ?? 'Saiba Mais') ?>"
                                   class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                                   placeholder="Saiba Mais">
                        </div>
                    </div>

                    <!-- Configurações -->
                    <div class="mt-8 pt-6 border-t border-admin-gray-700">
                        <h3 class="text-xl font-semibold text-white mb-4">Configurações</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-admin-gray-300 mb-2">
                                    Tipo de Banner *
                                </label>
                                <select name="tipo" 
                                        id="tipo"
                                        required
                                        class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all">
                                    <option value="principal" <?= ($banner['tipo'] ?? '') === 'principal' ? 'selected' : '' ?>>Principal (Grande)</option>
                                    <option value="categoria" <?= ($banner['tipo'] ?? '') === 'categoria' ? 'selected' : '' ?>>Categoria (Menor)</option>
                                    <option value="promocao" <?= ($banner['tipo'] ?? '') === 'promocao' ? 'selected' : '' ?>>Promoção</option>
                                    <option value="destaque" <?= ($banner['tipo'] ?? '') === 'destaque' ? 'selected' : '' ?>>Destaque</option>
                                </select>
                            </div>

                            <div>
                                <label for="posicao" class="block text-sm font-medium text-admin-gray-300 mb-2">
                                    Posição
                                </label>
                                <input type="number" 
                                       name="posicao" 
                                       id="posicao"
                                       value="<?= $banner['posicao'] ?? 0 ?>"
                                       min="0"
                                       class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                                       placeholder="0">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" 
                                           name="ativo" 
                                           value="1"
                                           <?= ($banner['ativo'] ?? 1) ? 'checked' : '' ?>
                                           class="w-5 h-5 text-admin-primary bg-admin-gray-800 border-admin-gray-600 rounded focus:ring-admin-primary focus:ring-2">
                                    <span class="text-sm font-medium text-admin-gray-300">Banner Ativo</span>
                                </label>
                            </div>

                            <div>
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" 
                                           name="nova_aba" 
                                           value="1"
                                           <?= ($banner['nova_aba'] ?? 0) ? 'checked' : '' ?>
                                           class="w-5 h-5 text-admin-primary bg-admin-gray-800 border-admin-gray-600 rounded focus:ring-admin-primary focus:ring-2">
                                    <span class="text-sm font-medium text-admin-gray-300">Abrir em Nova Aba</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Imagem -->
                    <div class="mt-8 pt-6 border-t border-admin-gray-700">
                        <h3 class="text-xl font-semibold text-white mb-4">Imagem</h3>
                        
                        <div>
                            <label for="imagem" class="block text-sm font-medium text-admin-gray-300 mb-2">
                                <?= $banner ? 'Nova Imagem (opcional)' : 'Imagem do Banner *' ?>
                            </label>
                            <input type="file" 
                                   name="imagem" 
                                   id="imagem"
                                   <?= !$banner ? 'required' : '' ?>
                                   accept="image/*"
                                   class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-admin-primary file:text-white hover:file:bg-blue-600 transition-all">
                            
                            <?php if ($banner && $banner['imagem']): ?>
                                <div class="mt-4">
                                    <p class="text-sm text-admin-gray-400 mb-2">Imagem atual:</p>
                                    <img src="../<?= htmlspecialchars($banner['imagem']) ?>" 
                                         alt="Banner atual" 
                                         class="max-w-xs h-32 object-cover rounded-lg">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-admin-gray-700">
                        <a href="gerenciar_banners.php" class="btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" name="<?= $banner ? 'editar' : 'adicionar' ?>" class="btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            <?= $banner ? 'Salvar Alterações' : 'Criar Banner' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview -->
        <div class="lg:col-span-1">
            <div class="admin-card rounded-xl p-6 sticky top-8">
                <h3 class="text-xl font-semibold text-white mb-4">Preview</h3>
                
                <div id="bannerPreview" class="bg-admin-gray-800 rounded-lg p-4 border border-admin-gray-600">
                    <div class="text-center text-admin-gray-400">
                        <i class="fas fa-image text-4xl mb-2"></i>
                        <p>Preview aparecerá aqui</p>
                    </div>
                </div>

                <!-- Dicas -->
                <div class="mt-6 space-y-4">
                    <div class="bg-admin-primary/10 border border-admin-primary/20 rounded-lg p-4">
                        <h4 class="font-semibold text-admin-primary mb-2">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Dicas
                        </h4>
                        <ul class="text-sm text-admin-gray-300 space-y-1">
                            <li>• Banners principais: 1200x600px</li>
                            <li>• Banners categoria: 300x300px</li>
                            <li>• Use imagens de alta qualidade</li>
                            <li>• Mantenha textos legíveis</li>
                        </ul>
                    </div>

                    <div class="bg-admin-success/10 border border-admin-success/20 rounded-lg p-4">
                        <h4 class="font-semibold text-admin-success mb-2">
                            <i class="fas fa-check-circle mr-2"></i>
                            Status
                        </h4>
                        <div class="text-sm text-admin-gray-300">
                            <p>Banner: <span id="statusText" class="text-admin-success">Ativo</span></p>
                            <p>Tipo: <span id="tipoText">Principal</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview em tempo real
function updatePreview() {
    const titulo = document.getElementById('titulo').value;
    const subtitulo = document.getElementById('subtitulo').value;
    const textoBotao = document.getElementById('texto_botao').value;
    const tipo = document.getElementById('tipo').value;
    const ativo = document.querySelector('input[name="ativo"]').checked;
    
    const preview = document.getElementById('bannerPreview');
    const statusText = document.getElementById('statusText');
    const tipoText = document.getElementById('tipoText');
    
    // Atualizar status
    statusText.textContent = ativo ? 'Ativo' : 'Inativo';
    statusText.className = ativo ? 'text-admin-success' : 'text-admin-error';
    
    // Atualizar tipo
    const tipos = {
        'principal': 'Principal',
        'categoria': 'Categoria',
        'promocao': 'Promoção',
        'destaque': 'Destaque'
    };
    tipoText.textContent = tipos[tipo] || 'Principal';
    
    // Atualizar preview
    if (titulo || subtitulo || textoBotao) {
        preview.innerHTML = `
            <div class="bg-gradient-to-r from-admin-primary to-admin-secondary rounded-lg p-6 text-white">
                ${titulo ? `<h3 class="text-xl font-bold mb-2">${titulo}</h3>` : ''}
                ${subtitulo ? `<p class="text-sm opacity-90 mb-4">${subtitulo}</p>` : ''}
                ${textoBotao ? `<button class="bg-white text-admin-primary px-4 py-2 rounded-lg text-sm font-semibold">${textoBotao}</button>` : ''}
            </div>
        `;
    } else {
        preview.innerHTML = `
            <div class="text-center text-admin-gray-400">
                <i class="fas fa-image text-4xl mb-2"></i>
                <p>Preview aparecerá aqui</p>
            </div>
        `;
    }
}

// Event listeners
document.getElementById('titulo').addEventListener('input', updatePreview);
document.getElementById('subtitulo').addEventListener('input', updatePreview);
document.getElementById('texto_botao').addEventListener('input', updatePreview);
document.getElementById('tipo').addEventListener('change', updatePreview);
document.querySelector('input[name="ativo"]').addEventListener('change', updatePreview);

// Preview de imagem
document.getElementById('imagem').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('bannerPreview');
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="w-full h-48 object-cover rounded-lg">
            `;
        };
        reader.readAsDataURL(file);
    }
});

// Validação do formulário
document.getElementById('bannerForm').addEventListener('submit', function(e) {
    const tipo = document.getElementById('tipo').value;
    const imagem = document.getElementById('imagem').files[0];
    
    if (!<?= $banner ? 'false' : 'true' ?> && !imagem) {
        e.preventDefault();
        alert('A imagem é obrigatória para novos banners!');
        return;
    }
    
    if (!tipo) {
        e.preventDefault();
        alert('Selecione o tipo do banner!');
        return;
    }
});

// Inicializar preview
updatePreview();
</script>

<?php require_once 'templates/footer_admin.php'; ?>

