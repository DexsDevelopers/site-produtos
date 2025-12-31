<?php
// admin/editar_categoria.php - Editor Avançado de Categorias
require_once 'secure.php';
require_once 'templates/header_admin.php';

$categoria_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$categoria = null;

if ($categoria_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->execute([$categoria_id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$categoria) {
            $_SESSION['admin_message'] = "Categoria não encontrada!";
            header("Location: gerenciar_categorias.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['admin_message'] = "Erro ao buscar categoria: " . $e->getMessage();
        header("Location: gerenciar_categorias.php");
        exit();
    }
}
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">
                <?= $categoria ? 'Editar Categoria' : 'Nova Categoria' ?>
            </h1>
            <p class="text-admin-gray-400 mt-2">
                <?= $categoria ? 'Modifique os dados da categoria' : 'Crie uma nova categoria para organizar seus produtos' ?>
            </p>
        </div>
        <a href="gerenciar_categorias.php" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar
        </a>
    </div>

    <!-- Formulário -->
    <div class="admin-card rounded-xl p-8">
        <form action="processa_categoria.php" method="POST" id="categoriaForm">
            <input type="hidden" name="categoria_id" value="<?= $categoria_id ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Informações Básicas -->
                <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-white mb-4">Informações Básicas</h3>
                    
                    <div>
                        <label for="nome" class="block text-sm font-medium text-admin-gray-300 mb-2">
                            Nome da Categoria *
                        </label>
                        <input type="text" 
                               name="nome" 
                               id="nome"
                               value="<?= htmlspecialchars($categoria['nome'] ?? '') ?>"
                               required 
                               class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                               placeholder="Ex: Eletrônicos, Roupas, Casa...">
                        <p class="text-xs text-admin-gray-400 mt-1">Nome que aparecerá na loja</p>
                    </div>

                    <div>
                        <label for="descricao" class="block text-sm font-medium text-admin-gray-300 mb-2">
                            Descrição
                        </label>
                        <textarea name="descricao" 
                                  id="descricao"
                                  rows="4"
                                  class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                                  placeholder="Descreva esta categoria..."><?= htmlspecialchars($categoria['descricao'] ?? '') ?></textarea>
                        <p class="text-xs text-admin-gray-400 mt-1">Descrição opcional da categoria</p>
                    </div>

                    <div>
                        <label for="ordem" class="block text-sm font-medium text-admin-gray-300 mb-2">
                            Ordem de Exibição
                        </label>
                        <input type="number" 
                               name="ordem" 
                               id="ordem"
                               value="<?= $categoria['ordem'] ?? 0 ?>"
                               min="0"
                               class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                               placeholder="0">
                        <p class="text-xs text-admin-gray-400 mt-1">Número menor = aparece primeiro</p>
                    </div>
                </div>

                <!-- Configurações Avançadas -->
                <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-white mb-4">Configurações</h3>
                    
                    <div>
                        <label for="icone" class="block text-sm font-medium text-admin-gray-300 mb-2">
                            Ícone (FontAwesome)
                        </label>
                        <div class="flex gap-2">
                            <input type="text" 
                                   name="icone" 
                                   id="icone"
                                   value="<?= htmlspecialchars($categoria['icone'] ?? 'fas fa-tag') ?>"
                                   class="flex-1 px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                                   placeholder="fas fa-tag">
                            <button type="button" 
                                    onclick="showIconPicker()"
                                    class="px-4 py-3 bg-admin-gray-700 hover:bg-admin-gray-600 text-white rounded-lg transition-colors">
                                <i class="fas fa-palette"></i>
                            </button>
                        </div>
                        <p class="text-xs text-admin-gray-400 mt-1">Classe do ícone FontAwesome</p>
                    </div>

                    <div>
                        <label for="cor" class="block text-sm font-medium text-admin-gray-300 mb-2">
                            Cor da Categoria
                        </label>
                        <div class="flex gap-2">
                            <input type="color" 
                                   name="cor" 
                                   id="cor"
                                   value="<?= $categoria['cor'] ?? '#FF3B5C' ?>"
                                   class="w-16 h-12 bg-admin-gray-800 border border-admin-gray-600 rounded-lg cursor-pointer">
                            <input type="text" 
                                   id="cor_text"
                                   value="<?= $categoria['cor'] ?? '#FF3B5C' ?>"
                                   class="flex-1 px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                                   placeholder="#FF3B5C">
                        </div>
                        <p class="text-xs text-admin-gray-400 mt-1">Cor que representará esta categoria</p>
                    </div>

                    <div>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   name="ativa" 
                                   value="1"
                                   <?= ($categoria['ativa'] ?? 1) ? 'checked' : '' ?>
                                   class="w-5 h-5 text-admin-primary bg-admin-gray-800 border-admin-gray-600 rounded focus:ring-admin-primary focus:ring-2">
                            <span class="text-sm font-medium text-admin-gray-300">Categoria Ativa</span>
                        </label>
                        <p class="text-xs text-admin-gray-400 mt-1">Categorias inativas não aparecem na loja</p>
                    </div>

                    <div>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   name="destaque" 
                                   value="1"
                                   <?= ($categoria['destaque'] ?? 0) ? 'checked' : '' ?>
                                   class="w-5 h-5 text-admin-primary bg-admin-gray-800 border-admin-gray-600 rounded focus:ring-admin-primary focus:ring-2">
                            <span class="text-sm font-medium text-admin-gray-300">Categoria em Destaque</span>
                        </label>
                        <p class="text-xs text-admin-gray-400 mt-1">Aparece em posição destacada na home</p>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="mt-8 pt-6 border-t border-admin-gray-700">
                <h3 class="text-xl font-semibold text-white mb-4">SEO</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_title" class="block text-sm font-medium text-admin-gray-300 mb-2">
                            Meta Title
                        </label>
                        <input type="text" 
                               name="meta_title" 
                               id="meta_title"
                               value="<?= htmlspecialchars($categoria['meta_title'] ?? '') ?>"
                               maxlength="60"
                               class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                               placeholder="Título para SEO">
                        <p class="text-xs text-admin-gray-400 mt-1">Máximo 60 caracteres</p>
                    </div>

                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-admin-gray-300 mb-2">
                            Meta Description
                        </label>
                        <textarea name="meta_description" 
                                  id="meta_description"
                                  rows="3"
                                  maxlength="160"
                                  class="w-full px-4 py-3 bg-admin-gray-800 border border-admin-gray-600 rounded-lg text-white focus:ring-2 focus:ring-admin-primary focus:border-transparent transition-all"
                                  placeholder="Descrição para SEO"><?= htmlspecialchars($categoria['meta_description'] ?? '') ?></textarea>
                        <p class="text-xs text-admin-gray-400 mt-1">Máximo 160 caracteres</p>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-admin-gray-700">
                <a href="gerenciar_categorias.php" class="btn-secondary">
                    Cancelar
                </a>
                <button type="submit" name="<?= $categoria ? 'editar' : 'adicionar' ?>" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    <?= $categoria ? 'Salvar Alterações' : 'Criar Categoria' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Seleção de Ícones -->
<div id="iconModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" onclick="closeIconPickerOnOverlay(event)">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-admin-gray-800 rounded-xl p-6 max-w-2xl w-full max-h-96 overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-white">Selecionar Ícone</h3>
                <button type="button" id="closeIconBtn" class="text-admin-gray-400 hover:text-white hover:bg-admin-gray-700 p-2 rounded-lg transition-all">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-8 gap-2" id="iconGrid">
                <!-- Ícones serão carregados via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Ícones disponíveis
const icons = [
    'fas fa-tag', 'fas fa-tags', 'fas fa-box', 'fas fa-shopping-bag',
    'fas fa-tshirt', 'fas fa-shoe-prints', 'fas fa-home', 'fas fa-car',
    'fas fa-laptop', 'fas fa-mobile-alt', 'fas fa-headphones', 'fas fa-camera',
    'fas fa-gamepad', 'fas fa-book', 'fas fa-utensils', 'fas fa-dumbbell',
    'fas fa-heart', 'fas fa-gift', 'fas fa-star', 'fas fa-fire',
    'fas fa-bolt', 'fas fa-sun', 'fas fa-moon', 'fas fa-cloud',
    'fas fa-leaf', 'fas fa-tree', 'fas fa-seedling', 'fas fa-paw',
    'fas fa-plane', 'fas fa-ship', 'fas fa-train', 'fas fa-bicycle'
];

function showIconPicker() {
    const modal = document.getElementById('iconModal');
    const grid = document.getElementById('iconGrid');
    
    grid.innerHTML = '';
    
    icons.forEach(icon => {
        const button = document.createElement('button');
        button.className = 'p-3 text-center hover:bg-admin-gray-700 rounded-lg transition-colors';
        button.innerHTML = `<i class="${icon} text-xl text-white"></i>`;
        button.onclick = () => selectIcon(icon);
        grid.appendChild(button);
    });
    
    modal.classList.remove('hidden');
}

function closeIconPicker() {
    const modal = document.getElementById('iconModal');
    if (modal) {
        modal.classList.add('hidden');
        console.log('Modal fechado');
    }
}

function closeIconPickerOnOverlay(event) {
    if (event.target === event.currentTarget) {
        closeIconPicker();
    }
}

// Adicionar event listener para o botão X
document.addEventListener('DOMContentLoaded', function() {
    const closeBtn = document.getElementById('closeIconBtn');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeIconPicker();
        });
    }
    
    // Fechar com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeIconPicker();
        }
    });
});

function selectIcon(icon) {
    document.getElementById('icone').value = icon;
    closeIconPicker();
}

// Sincronizar cor
document.getElementById('cor').addEventListener('input', function() {
    document.getElementById('cor_text').value = this.value;
});

document.getElementById('cor_text').addEventListener('input', function() {
    document.getElementById('cor').value = this.value;
});

// Validação do formulário
document.getElementById('categoriaForm').addEventListener('submit', function(e) {
    const nome = document.getElementById('nome').value.trim();
    if (!nome) {
        e.preventDefault();
        alert('O nome da categoria é obrigatório!');
        return;
    }
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>

