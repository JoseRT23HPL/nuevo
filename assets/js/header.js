// ===== HEADER.JS - Funcionalidades del header =====

// ===== VARIABLES GLOBALES =====
let fullscreenActivo = false;

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando header.js');
    inicializarFullscreen();
    inicializarDropdowns();
    inicializarBusquedaGlobal();
    inicializarSidebarLinks();
    verificarEstadoFullscreen();
});

// ===== FUNCIONES DE PANTALLA COMPLETA =====
function inicializarFullscreen() {
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', toggleFullscreen);
        
        // Verificar si había fullscreen antes
        const estabaFullscreen = sessionStorage.getItem('fullscreenActivo') === 'true' || 
                                 localStorage.getItem('fullscreenActivo') === 'true';
        
        if (estabaFullscreen && !document.fullscreenElement) {
            // Solo marcar visualmente, no activar automáticamente
            fullscreenBtn.classList.add('fullscreen-pendiente');
            fullscreenBtn.setAttribute('data-tooltip', 'Haz clic para restaurar pantalla completa');
            
            // Opcional: mostrar notificación
            mostrarNotificacion('Pantalla completa estaba activa. Haz clic en el botón para restaurar.', 'info');
        }
    }
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        entrarFullscreen();
    } else {
        salirFullscreen();
    }
}

function entrarFullscreen() {
    try {
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        if (fullscreenBtn) {
            fullscreenBtn.classList.remove('fullscreen-pendiente');
        }
        
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen()
                .then(() => {
                    console.log('✅ Modo fullscreen activado');
                    updateFullscreenIcon(true);
                    guardarEstadoFullscreen(true);
                })
                .catch(err => {
                    console.warn('❌ Error al activar fullscreen:', err);
                    mostrarNotificacion('No se pudo activar pantalla completa', 'error');
                });
        } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen();
            updateFullscreenIcon(true);
            guardarEstadoFullscreen(true);
        } else if (document.documentElement.msRequestFullscreen) {
            document.documentElement.msRequestFullscreen();
            updateFullscreenIcon(true);
            guardarEstadoFullscreen(true);
        }
    } catch (error) {
        console.error('Error en fullscreen:', error);
    }
}

function salirFullscreen() {
    try {
        if (document.exitFullscreen) {
            document.exitFullscreen()
                .then(() => {
                    console.log('✅ Modo fullscreen desactivado');
                    updateFullscreenIcon(false);
                    guardarEstadoFullscreen(false);
                })
                .catch(err => {
                    console.warn('❌ Error al salir de fullscreen:', err);
                });
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
            updateFullscreenIcon(false);
            guardarEstadoFullscreen(false);
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
            updateFullscreenIcon(false);
            guardarEstadoFullscreen(false);
        }
    } catch (error) {
        console.error('Error al salir de fullscreen:', error);
    }
}

function guardarEstadoFullscreen(activo) {
    fullscreenActivo = activo;
    localStorage.setItem('fullscreenActivo', activo ? 'true' : 'false');
    sessionStorage.setItem('fullscreenActivo', activo ? 'true' : 'false');
}

function updateFullscreenIcon(activo) {
    const icon = document.getElementById('fullscreenIcon');
    if (!icon) return;
    
    if (activo) {
        icon.classList.remove('fa-expand');
        icon.classList.add('fa-compress');
    } else {
        icon.classList.remove('fa-compress');
        icon.classList.add('fa-expand');
    }
}

// ===== DETECTAR CAMBIOS EN PANTALLA COMPLETA =====
document.addEventListener('fullscreenchange', handleFullscreenChange);
document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
document.addEventListener('mozfullscreenchange', handleFullscreenChange);
document.addEventListener('MSFullscreenChange', handleFullscreenChange);

function handleFullscreenChange() {
    const activo = !!document.fullscreenElement;
    updateFullscreenIcon(activo);
    guardarEstadoFullscreen(activo);
    
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    if (fullscreenBtn) {
        if (activo) {
            fullscreenBtn.classList.remove('fullscreen-pendiente');
        }
    }
}

// ===== VERIFICAR ESTADO AL CARGAR =====
function verificarEstadoFullscreen() {
    // Solo actualizar icono, no activar automáticamente
    const activo = !!document.fullscreenElement;
    updateFullscreenIcon(activo);
    
    console.log('📊 Estado de fullscreen al cargar:', activo ? 'Activo' : 'Inactivo');
    console.log('📦 localStorage:', localStorage.getItem('fullscreenActivo'));
    console.log('📦 sessionStorage:', sessionStorage.getItem('fullscreenActivo'));
}

// ===== INTERCEPTAR NAVEGACIÓN DEL SIDEBAR =====
function inicializarSidebarLinks() {
    const sidebarLinks = document.querySelectorAll('.sidebar a, .menu-item, .menu-link');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Guardar estado antes de navegar
            const fullscreenActivo = !!document.fullscreenElement;
            guardarEstadoFullscreen(fullscreenActivo);
        });
    });
}

// ===== GUARDAR ESTADO ANTES DE DESCARGAR LA PÁGINA =====
window.addEventListener('beforeunload', function() {
    const fullscreenActivo = !!document.fullscreenElement;
    guardarEstadoFullscreen(fullscreenActivo);
});

// ===== DROPDOWNS =====
function inicializarDropdowns() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            if (userDropdown) userDropdown.classList.remove('show');
        });
    }

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
            if (notificationDropdown) notificationDropdown.classList.remove('show');
        });
    }

    document.addEventListener('click', function() {
        if (notificationDropdown) notificationDropdown.classList.remove('show');
        if (userDropdown) userDropdown.classList.remove('show');
    });

    [notificationDropdown, userDropdown].forEach(dropdown => {
        if (dropdown) {
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
}

// ===== MARCAR TODAS LAS NOTIFICACIONES COMO LEÍDAS =====
function marcarTodasLeidas() {
    const notificationDropdown = document.getElementById('notificationDropdown');
    if (notificationDropdown) {
        notificationDropdown.classList.remove('show');
    }
    
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.style.display = 'none';
    }
    
    mostrarNotificacion('Notificaciones marcadas como leídas', 'success');
}

// ===== BÚSQUEDA GLOBAL =====
function inicializarBusquedaGlobal() {
    const searchInput = document.getElementById('globalSearch');
    
    if (searchInput) {
        const searchResults = document.createElement('div');
        searchResults.className = 'search-results';
        searchResults.style.display = 'none';
        searchInput.parentElement.appendChild(searchResults);
        
        let timeoutId;
        
        searchInput.addEventListener('input', function() {
            const termino = this.value.trim();
            
            clearTimeout(timeoutId);
            
            if (termino.length >= 2) {
                timeoutId = setTimeout(() => {
                    searchResults.innerHTML = `
                        <div class="search-result-item">
                            <i class="fas fa-box"></i>
                            <span>Buscando "${termino}"...</span>
                        </div>
                    `;
                    searchResults.style.display = 'block';
                }, 300);
            } else {
                searchResults.style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }
}

// ===== NOTIFICACIONES FLOTANTES =====
function mostrarNotificacion(mensaje, tipo = 'info') {
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion-flotante ${tipo}`;
    
    let icono = 'fa-info-circle';
    if (tipo === 'success') icono = 'fa-check-circle';
    if (tipo === 'error') icono = 'fa-exclamation-circle';
    if (tipo === 'warning') icono = 'fa-exclamation-triangle';
    
    notificacion.innerHTML = `
        <i class="fas ${icono}"></i>
        <span>${mensaje}</span>
    `;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => {
            notificacion.remove();
        }, 300);
    }, 3000);
}

// ===== EXPONER FUNCIONES GLOBALES =====
window.marcarTodasLeidas = marcarTodasLeidas;
window.mostrarNotificacion = mostrarNotificacion;

console.log('✅ header.js cargado correctamente');