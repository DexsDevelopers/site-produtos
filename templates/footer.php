<?php
// templates/footer.php — MACARIO BRAZIL
?>
</main>

<!-- ── Newsletter ── -->
<section class="newsletter">
    <div class="container">
        <div class="newsletter-box">
            <h2>Receba nossas promoções!</h2>
            <p>Cadastre-se e fique por dentro das novidades e ofertas exclusivas.</p>
            <form class="newsletter-form"
                onsubmit="event.preventDefault(); this.querySelector('button').textContent='Cadastrado ✓'; this.querySelector('input').value='';">
                <input type="email" class="newsletter-input" placeholder="Seu melhor e-mail" required />
                <button type="submit" class="btn btn-primary">Inscrever</button>
            </form>
        </div>
    </div>
</section>

<!-- ── Marquee ── -->
<div class="marquee-section">
    <div class="marquee-track">
        <span class="marquee-item">MACARIO BRAZIL</span>
        <span class="marquee-dot"></span>
        <span class="marquee-item">STREETWEAR</span>
        <span class="marquee-dot"></span>
        <span class="marquee-item">SNEAKERS</span>
        <span class="marquee-dot"></span>
        <span class="marquee-item">ELETRÔNICOS</span>
        <span class="marquee-dot"></span>
        <span class="marquee-item">DIGITAL</span>
        <span class="marquee-dot"></span>
        <span class="marquee-item">MACARIO BRAZIL</span>
        <span class="marquee-dot"></span>
        <span class="marquee-item">STREETWEAR</span>
        <span class="marquee-dot"></span>
        <span class="marquee-item">SNEAKERS</span>
        <span class="marquee-dot"></span>
        <span class="marquee-item">ELETRÔNICOS</span>
        <span class="marquee-dot"></span>
        <span class="marquee-item">DIGITAL</span>
        <span class="marquee-dot"></span>
    </div>
</div>

<!-- ── Footer ── -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="index.php">
                    <img src="472402418_460144646946890_6218335060120212885_n.jpg" alt="MACARIO BRAZIL" />
                </a>
                <p>Levando estilo e cultura até a sua casa. Roupas, tênis, eletrônicos e produtos digitais com qualidade
                    e preço justo.</p>
            </div>
            <div class="footer-col">
                <h4>Navegação</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Início</a></li>
                    <li><a href="busca.php?todos=1">Catálogo</a></li>
                    <li><a href="suporte.php">Contato</a></li>
                    <li><a href="login.php">Minha Conta</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Categorias</h4>
                <ul class="footer-links">
                    <li><a href="busca.php?termo=roupas">Roupas</a></li>
                    <li><a href="busca.php?termo=tenis">Tênis</a></li>
                    <li><a href="busca.php?termo=eletronicos">Eletrônicos</a></li>
                    <li><a href="busca.php?termo=digital">Produtos Digitais</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Informações</h4>
                <ul class="footer-links">
                    <li><a href="#">Política de Privacidade</a></li>
                    <li><a href="#">Termos de Serviço</a></li>
                    <li><a href="#">Política de Reembolso</a></li>
                    <li><a href="#">Trocas e Devoluções</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2026 MACARIO BRAZIL. Todos os direitos reservados.</p>
            <div class="footer-socials">
                <a href="#" class="footer-social-link" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="footer-social-link" aria-label="TikTok">
                    <i class="fab fa-tiktok"></i>
                </a>
                <a href="#" class="footer-social-link" aria-label="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="#" class="footer-social-link" aria-label="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- Toast Notification -->
<div class="toast-notification" id="toast-notification">
    <div id="toast-icon" style="font-size:1.2rem;"></div>
    <div id="toast-message"></div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="assets/js/macario.js"></script>

</body>

</html>