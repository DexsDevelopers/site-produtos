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
            
            function toggleSidebar() {
                if (sidebar && mobileOverlay) {
                    sidebar.classList.toggle('open');
                    mobileOverlay.classList.toggle('hidden');
                    // Previne scroll do body quando menu está aberto
                    if (sidebar.classList.contains('open')) {
                        document.body.style.overflow = 'hidden';
                    } else {
                        document.body.style.overflow = '';
                    }
                }
            }
            
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleSidebar);
            }
            
            if (bottomMenuBtn) {
                bottomMenuBtn.addEventListener('click', toggleSidebar);
            }
            
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    mobileOverlay.classList.add('hidden');
                    document.body.style.overflow = '';
                });
            }
            
            // Fecha menu ao clicar em link (mobile)
            if (window.innerWidth < 1024) {
                const navLinks = document.querySelectorAll('.admin-nav-item');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        setTimeout(() => {
                            sidebar.classList.remove('open');
                            mobileOverlay.classList.add('hidden');
                            document.body.style.overflow = '';
                        }, 100);
                    });
                });
            }
            
            // Auto-hide mobile menu on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('open');
                    mobileOverlay.classList.add('hidden');
                    document.body.style.overflow = '';
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
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const isMobile = window.innerWidth < 640;
            notification.className = `fixed ${isMobile ? 'top-20 left-4 right-4' : 'top-4 right-4'} p-4 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300 ${
                type === 'success' ? 'bg-admin-success text-white' :
                type === 'error' ? 'bg-admin-danger text-white' :
                type === 'warning' ? 'bg-admin-warning text-white' :
                'bg-admin-primary text-white'
            }`;
            
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="fas ${
                        type === 'success' ? 'fa-check-circle' :
                        type === 'error' ? 'fa-exclamation-circle' :
                        type === 'warning' ? 'fa-exclamation-triangle' :
                        'fa-info-circle'
                    }"></i>
                    <span class="${isMobile ? 'text-sm' : ''}">${message}</span>
                </div>
            `;
            
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