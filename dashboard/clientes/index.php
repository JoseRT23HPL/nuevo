<?php
// file: /dashboard/usuarios/index.php - VERSIÓN LIMPIA
include '../header.php';
?>

<!-- HEADER UNIFICADO -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-users"></i>
            <h1>Usuarios del Sistema</h1>
        </div>
        <span class="pv-badge">ADMINISTRACIÓN</span>
    </div>
    
    <div class="pv-header-right">
        <a href="nuevo.php" class="btn-header primary">
            <i class="fas fa-user-plus"></i>
            Nuevo Usuario
        </a>
    </div>
</div>

<!-- Subtítulo -->
<p class="page-subtitle">Gestiona los usuarios y sus permisos en el sistema</p>

<!-- Mensajes de alerta (ejemplos estáticos) -->
<div class="alerta error" style="display: none;" id="errorMensaje">
    <i class="fas fa-exclamation-circle"></i>
    <p>Error al procesar la solicitud</p>
</div>

<div class="alerta success" style="display: none;" id="successMensaje">
    <i class="fas fa-check-circle"></i>
    <p>Operación realizada correctamente</p>
</div>

<!-- Tabla de usuarios -->
<div class="clientes-container">
    <div class="tabla-responsive">
        <table class="tabla-usuarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th class="text-center">Rol</th>
                    <th class="text-center">Estado</th>
                    <th>Último acceso</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Usuario Super Admin -->
                <tr class="fila-usuario">
                    <td class="col-id">#01</td>
                    <td class="col-usuario">
                        <span class="usuario-username">admin</span>
                    </td>
                    <td class="col-nombre-completo">Administrador del Sistema</td>
                    <td class="col-email">
                        <a href="mailto:admin@ejemplo.com" class="email-link">
                            <i class="fas fa-envelope"></i>
                            admin@ejemplo.com
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="rol-badge super-admin">
                            <i class="fas fa-crown"></i>
                            Super Admin
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="estado-badge activo">
                            <span class="estado-punto activo"></span>
                            Activo
                        </span>
                    </td>
                    <td class="col-fecha">14/03/2025 10:30</td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="ver.php?id=1" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="editar.php?id=1" class="accion-icon" title="Editar usuario">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                
                <!-- Usuario Administrador -->
                <tr class="fila-usuario">
                    <td class="col-id">#02</td>
                    <td class="col-usuario">
                        <span class="usuario-username">jperez</span>
                    </td>
                    <td class="col-nombre-completo">Juan Pérez García</td>
                    <td class="col-email">
                        <a href="mailto:juan.perez@ejemplo.com" class="email-link">
                            <i class="fas fa-envelope"></i>
                            juan.perez@ejemplo.com
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="rol-badge admin">
                            <i class="fas fa-user-tie"></i>
                            Administrador
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="estado-badge activo">
                            <span class="estado-punto activo"></span>
                            Activo
                        </span>
                    </td>
                    <td class="col-fecha">14/03/2025 09:15</td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="ver.php?id=2" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="editar.php?id=2" class="accion-icon" title="Editar usuario">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="accion-icon desactivar" title="Desactivar usuario"
                               onclick="return confirm('¿Desactivar este usuario?')">
                                <i class="fas fa-ban"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                
                <!-- Usuario Cajero (Activo) -->
                <tr class="fila-usuario">
                    <td class="col-id">#03</td>
                    <td class="col-usuario">
                        <span class="usuario-username">mgarcia</span>
                    </td>
                    <td class="col-nombre-completo">María García López</td>
                    <td class="col-email">
                        <a href="mailto:maria.garcia@ejemplo.com" class="email-link">
                            <i class="fas fa-envelope"></i>
                            maria.garcia@ejemplo.com
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="rol-badge cajero">
                            <i class="fas fa-cash-register"></i>
                            Cajero
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="estado-badge activo">
                            <span class="estado-punto activo"></span>
                            Activo
                        </span>
                    </td>
                    <td class="col-fecha">14/03/2025 08:45</td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="ver.php?id=3" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="editar.php?id=3" class="accion-icon" title="Editar usuario">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="accion-icon desactivar" title="Desactivar usuario"
                               onclick="return confirm('¿Desactivar este usuario?')">
                                <i class="fas fa-ban"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                
                <!-- Usuario Cajero (Inactivo) -->
                <tr class="fila-usuario">
                    <td class="col-id">#04</td>
                    <td class="col-usuario">
                        <span class="usuario-username">lrodriguez</span>
                    </td>
                    <td class="col-nombre-completo">Luis Rodríguez Sánchez</td>
                    <td class="col-email">
                        <a href="mailto:luis.rodriguez@ejemplo.com" class="email-link">
                            <i class="fas fa-envelope"></i>
                            luis.rodriguez@ejemplo.com
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="rol-badge cajero">
                            <i class="fas fa-cash-register"></i>
                            Cajero
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="estado-badge inactivo">
                            <span class="estado-punto inactivo"></span>
                            Inactivo
                        </span>
                    </td>
                    <td class="col-fecha">10/03/2025 15:20</td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="ver.php?id=4" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="editar.php?id=4" class="accion-icon" title="Editar usuario">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="accion-icon activar" title="Activar usuario"
                               onclick="return confirm('¿Activar este usuario?')">
                                <i class="fas fa-check-circle"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Resumen -->
    <div class="tabla-footer">
        <div class="resumen-wrapper">
            <p class="resumen-info">
                <i class="fas fa-users"></i>
                Total de usuarios: <strong>4</strong>
            </p>
            <div class="resumen-stats">
                <span class="stat-badge">
                    <i class="fas fa-crown" style="color: #8b5cf6;"></i>
                    <span>Super Admin: 1</span>
                </span>
                <span class="stat-badge">
                    <i class="fas fa-user-tie" style="color: var(--primary);"></i>
                    <span>Administradores: 1</span>
                </span>
                <span class="stat-badge">
                    <i class="fas fa-cash-register" style="color: var(--success);"></i>
                    <span>Cajeros: 2</span>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones para mostrar alertas
function mostrarAlerta(tipo, mensaje) {
    const errorAlert = document.getElementById('errorMensaje');
    const successAlert = document.getElementById('successMensaje');
    
    errorAlert.style.display = 'none';
    successAlert.style.display = 'none';
    
    if (tipo === 'error') {
        errorAlert.querySelector('p').textContent = mensaje;
        errorAlert.style.display = 'flex';
        setTimeout(() => { errorAlert.style.display = 'none'; }, 5000);
    } else if (tipo === 'success') {
        successAlert.querySelector('p').textContent = mensaje;
        successAlert.style.display = 'flex';
        setTimeout(() => { successAlert.style.display = 'none'; }, 5000);
    }
}
</script>

<?php include '../footer.php'; ?>