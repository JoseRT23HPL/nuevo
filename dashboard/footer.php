        </main> <!-- Cierra mainContent -->
    </div>
    <!-- ===== SCRIPT DEL MENÚ HAMBURGUESA (CORREGIDO) ===== -->
<script>
(function() {
    'use strict';
    
    console.log('🍔 Script del menú cargado');
    
    function initMenu() {
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        console.log('Elementos encontrados:', {
            menuToggle: !!menuToggle,
            sidebar: !!sidebar,
            mainContent: !!mainContent
        });
        
        if (menuToggle && sidebar && mainContent) {
            console.log('✅ Inicializando menú...');
            
            // Remover clase collapsed por defecto si no hay preferencia
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === null) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            } else if (savedState === 'true') {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }
            
            // Función para toggle
            function toggleSidebar(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('🎯 Click en menú');
                
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
                
                console.log('Estado:', isCollapsed ? 'colapsado' : 'expandido');
            }
            
            // Remover listeners anteriores y agregar nuevo
            menuToggle.removeEventListener('click', toggleSidebar);
            menuToggle.addEventListener('click', toggleSidebar);
            
            console.log('✅ Menú listo!');
            return true;
        }
        return false;
    }
    
    // Intentar varias veces
    if (!initMenu()) {
        console.log('⏳ Esperando elementos...');
        let attempts = 0;
        const interval = setInterval(function() {
            attempts++;
            if (initMenu() || attempts > 20) {
                clearInterval(interval);
                if (attempts > 20) {
                    console.error('❌ No se pudo inicializar el menú');
                }
            }
        }, 100);
    }
})();
</script> <!-- Cierra el div class="flex" del header -->
</body>
</html>