/**
 * MACARIO BRAZIL — Main JavaScript
 * E-commerce Premium | Black & White
 */

(function () {
    'use strict';

    // ── Announcement Bar Rotation ──
    const announcementBar = document.getElementById('announcement-bar');
    if (announcementBar) {
        const slides = announcementBar.querySelectorAll('.announcement-slide');
        let currentSlide = 0;

        if (slides.length > 1) {
            slides.forEach((s, i) => {
                s.style.display = i === 0 ? 'flex' : 'none';
                s.style.opacity = i === 0 ? '1' : '0';
            });

            setInterval(() => {
                const prev = slides[currentSlide];
                currentSlide = (currentSlide + 1) % slides.length;
                const next = slides[currentSlide];

                prev.style.opacity = '0';
                setTimeout(() => {
                    prev.style.display = 'none';
                    next.style.display = 'flex';
                    requestAnimationFrame(() => {
                        next.style.opacity = '1';
                    });
                }, 300);
            }, 3500);
        }
    }

    // ── Navbar Scroll Effect ──
    const nav = document.getElementById('main-nav');
    if (nav) {
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            if (scrollY > 60) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
            lastScroll = scrollY;
        }, { passive: true });
    }

    // ── Mobile Menu ──
    const menuBtn = document.getElementById('menu-btn');
    const closeBtn = document.getElementById('close-menu-btn');
    const overlay = document.getElementById('mobile-overlay');
    const panel = document.getElementById('mobile-panel');

    function openMenu() {
        if (overlay && panel) {
            overlay.classList.add('active');
            panel.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeMenu() {
        if (overlay && panel) {
            overlay.classList.remove('active');
            panel.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (menuBtn) menuBtn.addEventListener('click', openMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    if (overlay) overlay.addEventListener('click', closeMenu);

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMenu();
    });

    // ── Nav Dropdown (Desktop) ──
    const dropdowns = document.querySelectorAll('.nav-dropdown');
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.nav-dropdown-trigger');
        const menu = dropdown.querySelector('.nav-dropdown-menu');

        if (trigger && menu) {
            let timeout;

            dropdown.addEventListener('mouseenter', () => {
                clearTimeout(timeout);
                menu.classList.add('active');
            });

            dropdown.addEventListener('mouseleave', () => {
                timeout = setTimeout(() => {
                    menu.classList.remove('active');
                }, 200);
            });
        }
    });

    // ── Scroll Reveal ──
    const reveals = document.querySelectorAll('.reveal');
    if (reveals.length > 0) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        reveals.forEach(el => revealObserver.observe(el));
    }

    // ── Smooth Scroll for anchor links ──
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ── Toast Notification ──
    window.showToast = function (message, icon = '✅') {
        const toast = document.getElementById('toast-notification');
        const toastMsg = document.getElementById('toast-message');
        const toastIcon = document.getElementById('toast-icon');

        if (toast && toastMsg && toastIcon) {
            toastIcon.textContent = icon;
            toastMsg.textContent = message;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    };

    // ── Lazy Load Images ──
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    if (lazyImages.length > 0) {
        const imgObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('loaded');
                    imgObserver.unobserve(entry.target);
                }
            });
        });

        lazyImages.forEach(img => {
            img.addEventListener('load', () => img.classList.add('loaded'));
            imgObserver.observe(img);
        });
    }

    // ── Product Card Hover Effect ──
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.willChange = 'transform, box-shadow';
        });
        card.addEventListener('mouseleave', function () {
            this.style.willChange = 'auto';
        });
    });

    // ── Add to Cart (AJAX) ──
    window.addToCart = function (productId, event) {
        if (event) event.preventDefault();

        fetch('adicionar_carrinho.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'produto_id=' + productId + '&quantidade=1'
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Produto adicionado ao carrinho!', '🛒');
                    // Update cart badge
                    const badges = document.querySelectorAll('#cart-count, #cart-count-mobile');
                    badges.forEach(badge => {
                        if (badge) {
                            badge.textContent = data.total || parseInt(badge.textContent || 0) + 1;
                        }
                    });
                } else {
                    showToast(data.message || 'Erro ao adicionar', '❌');
                }
            })
            .catch(() => {
                showToast('Erro de conexão', '❌');
            });
    };

    console.log('%c MACARIO BRAZIL ', 'background: #000; color: #fff; font-size: 14px; padding: 8px 16px; font-weight: bold; border-radius: 4px;');
})();
