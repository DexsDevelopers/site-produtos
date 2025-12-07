        </main>
    </div>
    
    <!-- JavaScript para funcionalidades do admin -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobile-overlay');
            
            if (mobileMenuBtn && sidebar) {
                mobileMenuBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    mobileOverlay.classList.toggle('hidden');
                });
                
                mobileOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    mobileOverlay.classList.add('hidden');
                });
            }
            
            // Auto-hide mobile menu on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('open');
                    mobileOverlay.classList.add('hidden');
                }
            });
            
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
        
        // Função para mostrar notificações
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300 ${
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
                    <span>${message}</span>
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
                    document.body.removeChild(notification);
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