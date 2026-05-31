<?php
// politica-privacidade.php - Política de Privacidade da MACARIO BRAZIL
$page_title = 'Política de Privacidade';
require_once 'templates/header.php';
?>

<div class="pt-32 pb-20 px-6">
    <div class="max-w-3xl mx-auto bg-white/[0.02] border border-white/5 rounded-3xl p-8 md:p-12">
        <h1 class="text-4xl font-display font-bold text-white mb-8 text-center glow-text">Política de Privacidade</h1>
        
        <div class="space-y-6 text-brand-gray-text leading-relaxed">
            <p>A <strong>MACARIO BRAZIL</strong> valoriza a privacidade dos seus clientes e usuários. Esta Política de Privacidade descreve como coletamos, usamos, armazenamos e protegemos seus dados pessoais ao visitar nosso site ou realizar compras em nossa loja virtual, em total conformidade com a <strong>Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018)</strong>.</p>
            
            <hr class="border-white/10 my-8">

            <h2 class="text-xl font-bold text-white mb-3">1. Informações que Coletamos</h2>
            <p>Coletamos dados fornecidos por você ativamente para a execução de suas compras ou cadastro no site, bem como dados coletados automaticamente para fins de melhoria de usabilidade e anúncios:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Dados Cadastrais:</strong> Nome completo, CPF, e-mail, telefone/WhatsApp e endereço completo para faturamento e entrega de mercadorias.</li>
                <li><strong>Dados de Pagamento:</strong> Informações de cartões de crédito e transações Pix são processados diretamente de forma criptografada por nossos parceiros integradores de pagamentos (InfinitePay e PixGhost), não sendo armazenados em nossos servidores.</li>
                <li><strong>Dados de Navegação:</strong> Endereço IP, tipo de dispositivo, navegador, páginas visitadas e cookies de rastreamento para anúncios (Meta Pixel).</li>
            </ul>

            <h2 class="text-xl font-bold text-white mb-3 mt-6">2. Finalidade do Tratamento de Dados</h2>
            <p>Seus dados são tratados com as seguintes finalidades legítimas:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Processar, faturar e entregar os seus pedidos físicos ou digitais.</li>
                <li>Prestar suporte ao cliente em caso de trocas, devoluções ou dúvidas.</li>
                <li>Recuperar carrinhos de compras abandonados via e-mail ou WhatsApp.</li>
                <li>Exibir anúncios relevantes e personalizados nas redes da Meta (Facebook/Instagram) e Google.</li>
            </ul>

            <h2 class="text-xl font-bold text-white mb-3 mt-6">3. Compartilhamento de Dados</h2>
            <p>Seus dados pessoais só são compartilhados com terceiros parceiros estritamente necessários para a operação do negócio, tais como:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Empresas de transporte e logística (Correios/Transportadora) para a entrega dos produtos físicos.</li>
                <li>Instituições financeiras e gateways de pagamento para processamento de transações.</li>
                <li>Plataformas de publicidade e análise de tráfego (Meta Ads / Google Analytics).</li>
            </ul>

            <h2 class="text-xl font-bold text-white mb-3 mt-6">4. Seus Direitos (LGPD)</h2>
            <p>Conforme a legislação brasileira, você possui direitos assegurados de solicitar a qualquer momento:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>A confirmação e acesso aos dados pessoais que possuímos sobre você.</li>
                <li>A correção de dados incompletos, inexatos ou desatualizados.</li>
                <li>A eliminação ou exclusão completa dos seus dados pessoais cadastrados, exceto quando a retenção for exigida por obrigações legais de faturamento ou fiscais.</li>
            </ul>

            <h2 class="text-xl font-bold text-white mb-3 mt-6">5. Segurança da Informação</h2>
            <p>Adotamos medidas técnicas e administrativas avançadas para proteger seus dados contra acessos não autorizados ou divulgação indevida. O site conta com certificado de criptografia **SSL (HTTPS)** em todas as suas etapas e compressão de segurança contra requisições maliciosas.</p>

            <h2 class="text-xl font-bold text-white mb-3 mt-6">6. Contato do Encarregado de Proteção de Dados (DPO)</h2>
            <p>Para exercer seus direitos de privacidade ou esclarecer dúvidas sobre esta Política, entre em contato com nossa equipe de privacidade pelo WhatsApp ou enviando um e-mail para <a href="mailto:suporte@macariobrazil.com" class="text-white underline hover:text-gray-300">suporte@macariobrazil.com</a>.</p>
        </div>
        
        <div class="mt-12 text-center">
            <a href="index.php" class="inline-flex items-center gap-2 bg-white text-black px-8 py-3 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left"></i> Voltar à Loja
            </a>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
