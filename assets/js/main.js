// ===== FUNCIONES GLOBALES =====
const App = {
    init() {
        console.log('App iniciada'); // Para verificar que carga
        this.initSidebarToggle(); // Mover al principio para prioridad
        this.initDropdowns();
        this.initFullscreen();
        this.initGlobalSearch();
        this.initScrollIndicator();
    },

    // ===== FUNCIÓN CORREGIDA: TOGGLE DEL SIDEBAR =====
    initSidebarToggle() {
        console.log('Inicializando sidebar toggle'); // Debug
        
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        // Verificar que existan los elementos
        console.log('menuToggle:', menuToggle);
        console.log('sidebar:', sidebar);
        console.log('mainContent:', mainContent);
        
        if (menuToggle && sidebar && mainContent) {
            // Verificar si hay preferencia guardada
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            console.log('isCollapsed:', isCollapsed); // Debug
            
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }
            
            // Remover event listeners anteriores para evitar duplicados
            menuToggle.removeEventListener('click', this.toggleSidebar);
            
            // Agregar event listener
            menuToggle.addEventListener('click', (e) => {
                e.preventDefault(); // Prevenir comportamiento por defecto
                e.stopPropagation(); // Detener propagación
                
                console.log('Click en menú hamburguesa'); // Debug
                
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                
                // Guardar preferencia
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                
                console.log('Sidebar collapsed:', sidebar.classList.contains('collapsed')); // Debug
                
                // Disparar evento para que otros componentes se ajusten si es necesario
                window.dispatchEvent(new CustomEvent('sidebarToggle', {
                    detail: { collapsed: sidebar.classList.contains('collapsed') }
                }));
            });
            
            console.log('Event listener agregado correctamente'); // Debug
        } else {
            console.error('No se encontraron los elementos del sidebar');
            console.log('IDs disponibles:', {
                menuToggle: !!menuToggle,
                sidebar: !!sidebar,
                mainContent: !!mainContent
            });
        }
    },

    // Dropdowns
    initDropdowns() {
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        
        if (notificationBtn && notificationDropdown) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationDropdown.classList.toggle('show');
                if (userDropdown) userDropdown.classList.remove('show');
            });
        }

        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
                if (notificationDropdown) notificationDropdown.classList.remove('show');
            });
        }

        document.addEventListener('click', () => {
            if (notificationDropdown) notificationDropdown.classList.remove('show');
            if (userDropdown) userDropdown.classList.remove('show');
        });

        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    },

    // Pantalla completa
    initFullscreen() {
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const fullscreenIcon = document.getElementById('fullscreenIcon');

        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                    if (fullscreenIcon) {
                        fullscreenIcon.classList.remove('fa-expand');
                        fullscreenIcon.classList.add('fa-compress');
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                        if (fullscreenIcon) {
                            fullscreenIcon.classList.remove('fa-compress');
                            fullscreenIcon.classList.add('fa-expand');
                        }
                    }
                }
            });
        }
    },

    // Búsqueda global
    initGlobalSearch() {
        const searchInput = document.getElementById('globalSearch');
        if (searchInput) {
            let timeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(timeout);
                const query = e.target.value.trim();
                
                timeout = setTimeout(() => {
                    if (query.length > 2) {
                        console.log('Buscando:', query);
                    }
                }, 500);
            });
        }
    },

    // Indicador de scroll
    initScrollIndicator() {
        const scrollIndicator = document.getElementById('scrollIndicator');
        const dashboardContent = document.getElementById('dashboardContent');
        
        if (scrollIndicator && dashboardContent) {
            scrollIndicator.addEventListener('click', function() {
                dashboardContent.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            });
            
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    scrollIndicator.style.opacity = '0';
                    scrollIndicator.style.visibility = 'hidden';
                } else {
                    scrollIndicator.style.opacity = '1';
                    scrollIndicator.style.visibility = 'visible';
                }
            });
        }
    },

    // Mostrar modal global
    showModal(title, message, type = 'info', onConfirm = null) {
        if (confirm(`${title}\n${message}`)) {
            if (onConfirm) onConfirm();
        }
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM cargado, iniciando App...');
    App.init();
});

// También inicializar si el DOM ya está cargado
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => App.init());
} else {
    console.log('DOM ya cargado, iniciando inmediatamente...');
    App.init();
}