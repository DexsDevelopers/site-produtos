<?php
// politica-reembolso.php - Política de Reembolso da MACARIO BRAZIL
$page_title = 'Política de Reembolso';
require_once 'templates/header.php';
?>

<div class="pt-32 pb-20 px-6">
    <div class="max-w-3xl mx-auto bg-white/[0.02] border border-white/5 rounded-3xl p-8 md:p-12">
        <h1 class="text-4xl font-display font-bold text-white mb-8 text-center glow-text">Política de Reembolso</h1>
        
        <div class="space-y-6 text-brand-gray-text leading-relaxed">
            <p>Na <strong>MACARIO BRAZIL</strong>, o nosso compromisso é garantir a satisfação total de nossos clientes. Estabelecemos nossa Política de Reembolso de acordo com os direitos previstos no <strong>Código de Defesa do Consumidor (CDC)</strong> brasileiro para compras online.</p>
            
            <hr class="border-white/10 my-8">

            <h2 class="text-xl font-bold text-white mb-3">1. Direito de Arrependimento (7 Dias)</h2>
            <p>De acordo com o Artigo 49 do CDC, o consumidor pode desistir de qualquer compra realizada pela internet no prazo de até <strong>7 (sete) dias corridos</strong> a partir do recebimento do produto físico em sua residência (ou da liberação do acesso em caso de produto digital).</p>
            <p>Para obter o reembolso total nesses casos, o produto físico deve atender às seguintes condições:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>O produto deve ser devolvido em sua embalagem original completa.</li>
                <li>Não deve apresentar qualquer indício de uso, lavagem ou modificações.</li>
                <li>As etiquetas e tags da marca devem estar intactas.</li>
            </ul>

            <h2 class="text-xl font-bold text-white mb-3 mt-6">2. Reembolso de Produtos Digitais</h2>
            <p>O cancelamento e reembolso de produtos digitais (como e-books, cursos e infoprodutos) é garantido no prazo de 7 dias, desde que não tenha havido a realização de downloads em massa ou consumo abusivo de materiais do produto, caracterizando má-fé.</p>

            <h2 class="text-xl font-bold text-white mb-3 mt-6">3. Como Solicitar o Reembolso</h2>
            <p>Para solicitar o reembolso, entre em contato imediatamente com nossa Central de Atendimento pelo e-mail <a href="mailto:suporte@macariobrazil.com" class="text-white underline hover:text-gray-300">suporte@macariobrazil.com</a> ou pelo WhatsApp, informando o número do pedido e o motivo da solicitação.</p>

            <h2 class="text-xl font-bold text-white mb-3 mt-6">4. Prazos e Formas de Devolução de Valores</h2>
            <p>Assim que o produto devolvido for inspecionado e aprovado em nossa central de triagem, o reembolso será processado de acordo com a forma original de pagamento:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Pagamento via Pix:</strong> O estorno será realizado diretamente na conta corrente do comprador em até <strong>2 (dois) dias úteis</strong> após a aprovação da devolução.</li>
                <li><strong>Pagamento via Cartão de Crédito:</strong> A solicitação de estorno é enviada à administradora do cartão de crédito em até 3 dias úteis. O crédito real na fatura é de responsabilidade da bandeira do cartão e pode levar de 1 a 2 faturas subsequentes para aparecer, dependendo do vencimento.</li>
            </ul>

            <h2 class="text-xl font-bold text-white mb-3 mt-6">5. Custos de Devolução</h2>
            <p>No caso de arrependimento de compra ou devolução por defeitos de fábrica dentro do prazo de 7 dias, os custos de frete reverso de devolução do produto são inteiramente arcados pela MACARIO BRAZIL por meio de código de postagem reversa dos Correios.</p>
        </div>
        
        <div class="mt-12 text-center">
            <a href="index.php" class="inline-flex items-center gap-2 bg-white text-black px-8 py-3 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left"></i> Voltar à Loja
            </a>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
