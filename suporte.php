<?php
$page_title = 'Suporte';
require_once 'templates/header.php';
?>

<div class="pt-32 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl md:text-5xl font-display font-bold text-white mb-6 text-center">Como podemos ajudar?</h1>
        <p class="text-xl text-brand-gray-text text-center mb-16">
            Nossa equipe de suporte está pronta para resolver qualquer questão.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
            <!-- Canal 1: Produtos Digitais -->
            <div class="p-8 border border-white/10 rounded-2xl bg-white/5 hover:border-white/30 transition-all">
                <i class="fas fa-laptop-code text-3xl text-brand-red mb-6"></i>
                <h3 class="text-2xl font-bold text-white mb-4">Produtos Digitais</h3>
                <p class="text-brand-gray-text mb-6">
                    Problemas com acesso, download ou licenças? Resolvemos a maioria dos casos em menos de 2 horas.
                </p>
                <ul class="space-y-3 mb-8 text-sm text-gray-400">
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Reenvio de acesso imediato</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Tutoriais de instalação</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Suporte técnico especializado</li>
                </ul>
                <a href="https://wa.me/5511999999999?text=Preciso%20de%20ajuda%20com%20produto%20digital"
                    target="_blank"
                    class="block w-full text-center bg-white text-black font-bold py-3 rounded-xl hover:bg-gray-200 transition-colors">
                    Falar no WhatsApp
                </a>
            </div>

            <!-- Canal 2: Produtos Físicos (Novo) -->
            <div class="p-8 border border-white/10 rounded-2xl bg-white/5 hover:border-white/30 transition-all">
                <i class="fas fa-box-open text-3xl text-blue-400 mb-6"></i>
                <h3 class="text-2xl font-bold text-white mb-4">Rastreio e Envio</h3>
                <p class="text-brand-gray-text mb-6">
                    Dúvidas sobre entrega, trocas ou devoluções de produtos físicos? Estamos aqui para garantir que seu
                    pedido chegue perfeito.
                </p>
                <ul class="space-y-3 mb-8 text-sm text-gray-400">
                    <li><i class="fas fa-check text-blue-400 mr-2"></i> Rastreamento em tempo real</li>
                    <li><i class="fas fa-check text-blue-400 mr-2"></i> Política de troca facilitada</li>
                    <li><i class="fas fa-check text-blue-400 mr-2"></i> Seguro contra extravio</li>
                </ul>
                <a href="https://wa.me/5511999999999?text=Preciso%20de%20ajuda%20com%20entrega" target="_blank"
                    class="block w-full text-center bg-transparent border border-white text-white font-bold py-3 rounded-xl hover:bg-white hover:text-black transition-colors">
                    Suporte de Entregas
                </a>
            </div>
        </div>

        <div class="bg-brand-gray-light rounded-2xl p-8 md:p-12 text-center">
            <h2 class="text-2xl font-bold text-white mb-4">Perguntas Frequentes (FAQ)</h2>

            <div class="space-y-4 text-left max-w-2xl mx-auto mt-8">
                <div class="border-b border-white/10 pb-4">
                    <h4 class="font-bold text-white mb-2">Como recebo meu código de rastreio?</h4>
                    <p class="text-brand-gray-text text-sm">Assim que o pedido for despachado, você receberá o código
                        automaticamente por e-mail e poderá vê-lo em "Meus Pedidos".</p>
                </div>
                <div class="border-b border-white/10 pb-4">
                    <h4 class="font-bold text-white mb-2">Qual o prazo de reembolso?</h4>
                    <p class="text-brand-gray-text text-sm">Para produtos digitais não acessados, o reembolso é em até 7
                        dias. Para físicos, 7 dias após o recebimento (conforme CDC).</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-2">Vocês enviam para todo o Brasil?</h4>
                    <p class="text-brand-gray-text text-sm">Sim, enviamos produtos físicos via Correios e
                        Transportadoras para todo o território nacional com seguro.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>