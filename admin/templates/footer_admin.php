        </main>
    </div>
    
    <!-- JavaScript para funcionalidades do admin -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const bottomMenuBtn = document.getElementById('bottom-menu-btn');
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobile-overlay');
            
            function openMenu() {
                if (sidebar && mobileOverlay) {
                    sidebar.classList.add('menu-open');
                    mobileOverlay.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
            }
            
            function closeMenu() {
                if (sidebar && mobileOverlay) {
                    sidebar.classList.remove('menu-open');
                    mobileOverlay.classList.remove('show');
                    document.body.style.overflow = '';
                }
            }
            
            function toggleMenu() {
                if (sidebar && sidebar.classList.contains('menu-open')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            }
            
            // Botão do header mobile
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMenu();
                });
            }
            
            // Botão do bottom nav
            if (bottomMenuBtn) {
                bottomMenuBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMenu();
                });
            }
            
            // Fecha ao clicar no overlay
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', function() {
                    closeMenu();
                });
            }
            
            // Fecha menu ao clicar em link (mobile)
            if (window.innerWidth < 1024) {
                const navLinks = document.querySelectorAll('.admin-nav-item');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        setTimeout(function() {
                            closeMenu();
                        }, 100);
                    });
                });
            }
            
            // Auto-hide mobile menu on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    closeMenu();
                }
            });
            
            // Previne zoom duplo toque
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(event) {
                const now = Date.now();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
            
            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                    }
                });
            }, observerOptions);
            
            // Observe all cards
            document.querySelectorAll('.admin-card, .stat-card').forEach(card => {
                observer.observe(card);
            });
        });
        
        // Função para mostrar notificações (mobile-friendly)
        function showNotification(message, type) {
            type = type || 'info';
            const notification = document.createElement('div');
            const isMobile = window.innerWidth < 640;
            const positionClass = isMobile ? 'top-20 left-4 right-4' : 'top-4 right-4';
            let colorClass = 'bg-admin-primary text-white';
            if (type === 'success') {
                colorClass = 'bg-admin-success text-white';
            } else if (type === 'error') {
                colorClass = 'bg-admin-danger text-white';
            } else if (type === 'warning') {
                colorClass = 'bg-admin-warning text-white';
            }
            
            notification.className = 'fixed ' + positionClass + ' p-4 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300 ' + colorClass;
            
            let iconClass = 'fa-info-circle';
            if (type === 'success') {
                iconClass = 'fa-check-circle';
            } else if (type === 'error') {
                iconClass = 'fa-exclamation-circle';
            } else if (type === 'warning') {
                iconClass = 'fa-exclamation-triangle';
            }
            
            const textSizeClass = isMobile ? 'text-sm' : '';
            notification.innerHTML = '<div class="flex items-center gap-3">' +
                '<i class="fas ' + iconClass + '"></i>' +
                '<span class="' + textSizeClass + '">' + message + '</span>' +
                '</div>';
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
        
        // Função para confirmar ações
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
        
        // Função para formatar números
        function formatNumber(num) {
            return new Intl.NumberFormat('pt-BR').format(num);
        }
        
        // Função para formatar moeda
        function formatCurrency(num) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(num);
        }
    </script>
</body>
</html>