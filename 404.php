<?php
// 404.php - Página de Erro 404 Personalizada
http_response_code(404);

// Define meta tags para a página 404
$page_title = 'Página Não Encontrada';
$page_description = 'A página que você está procurando não foi encontrada. Verifique o endereço ou navegue pelo nosso site.';

require_once 'templates/header.php';
?>

<div class="w-full max-w-4xl mx-auto py-24 px-4">
    <div class="pt-16 text-center">
        <!-- Ícone de Erro -->
        <div class="mb-8">
            <svg class="mx-auto h-32 w-32 text-brand-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.636M15 6.5a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        
        <!-- Título -->
        <h1 class="text-6xl md:text-8xl font-black text-white mb-4">404</h1>
        <h2 class="text-2xl md:text-3xl font-bold text-brand-red mb-6">Página Não Encontrada</h2>
        
        <!-- Descrição -->
        <p class="text-lg text-brand-gray-text mb-8 max-w-2xl mx-auto">
            Ops! A página que você está procurando não existe ou foi movida. 
            Não se preocupe, vamos te ajudar a encontrar o que você precisa.
        </p>
        
        <!-- Botões de Ação -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
            <a href="index.php" class="bg-brand-red hover:bg-brand-red-dark text-white font-bold py-4 px-8 rounded-lg transition-all duration-300 transform hover:scale-105">
                Voltar ao Início
            </a>
            <a href="busca.php" class="bg-brand-gray-light hover:bg-brand-gray text-white font-bold py-4 px-8 rounded-lg transition-all duration-300">
                Buscar Produtos
            </a>
        </div>
        
        <!-- Sugestões de Produtos -->
        <div class="mt-16">
            <h3 class="text-2xl font-bold text-white mb-8">Que tal dar uma olhada nesses produtos?</h3>
            
            <?php
            try {
                // Busca produtos em destaque
                $produtos_destaque = $pdo->query("SELECT * FROM produtos ORDER BY RAND() LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($produtos_destaque)):
            ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <?php foreach ($produtos_destaque as $produto): ?>
                        <a href="produto.php?id=<?= $produto['id'] ?>" class="block group">
                            <div class="bg-brand-black border border-brand-gray-light rounded-xl overflow-hidden transition-all duration-300 hover:border-brand-red hover:shadow-xl hover:shadow-brand-red/10">
                                <div class="aspect-square overflow-hidden">
                                    <img src="<?= htmlspecialchars($produto['imagem']) ?>" 
                                         alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                         class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110"
                                         loading="lazy">
                                </div>
                                <div class="p-4">
                                    <h4 class="font-bold text-lg text-white truncate"><?= htmlspecialchars($produto['nome']) ?></h4>
                                    <p class="text-sm text-brand-gray-text mt-1 truncate"><?= htmlspecialchars($produto['descricao_curta']) ?></p>
                                    <p class="text-xl font-bold text-white mt-3"><?= formatarPreco($produto['preco']) ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php
            } catch (PDOException $e) {
                // Se houver erro, não mostra os produtos
            }
            ?>
        </div>
        
        <!-- Informações de Contato -->
        <div class="mt-16 bg-brand-gray/30 rounded-xl p-8">
            <h3 class="text-xl font-bold text-white mb-4">Precisa de Ajuda?</h3>
            <p class="text-brand-gray-text mb-6">
                Se você acredita que isso é um erro, entre em contato conosco.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="mailto:suporte@minhaloja.com" class="text-brand-red hover:text-brand-red-dark transition-colors">
                    📧 suporte@minhaloja.com
                </a>
                <span class="hidden sm:block text-brand-gray-text">|</span>
                <a href="tel:+5511999999999" class="text-brand-red hover:text-brand-red-dark transition-colors">
                    📞 (11) 99999-9999
                </a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
