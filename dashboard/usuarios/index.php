<?php
require_once '../../config.php';
requiereAuth();

// Solo super_admin puede acceder a esta página
if (!hasRole('super_admin')) {
    header('Location: ' . url('dashboard/index.php'));
    exit;
}

$conn = getDB();

// Procesar acciones (activar/desactivar)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // No permitir desactivar al propio usuario
    if ($id == $_SESSION['user_id']) {
        $_SESSION['error'] = "No puedes modificar tu propio usuario";
    } else {
        if ($action === 'desactivar') {
            $stmt = $conn->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Usuario desactivado correctamente";
            } else {
                $_SESSION['error'] = "Error al desactivar el usuario";
            }
        } elseif ($action === 'activar') {
            $stmt = $conn->prepare("UPDATE usuarios SET activo = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Usuario activado correctamente";
            } else {
                $_SESSION['error'] = "Error al activar el usuario";
            }
        }
    }
    
    header('Location: ' . url('dashboard/usuarios/index.php'));
    exit;
}

// Obtener todos los usuarios
$sql = "SELECT * FROM usuarios ORDER BY 
        CASE 
            WHEN rol = 'super_admin' THEN 1
            WHEN rol = 'admin' THEN 2
            ELSE 3
        END, nombre ASC";
$result = $conn->query($sql);

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

// Estadísticas por rol
$stats = [
    'super_admin' => 0,
    'admin' => 0,
    'cajero' => 0,
    'total' => count($usuarios)
];

foreach ($usuarios as $u) {
    if ($u['rol'] === 'super_admin') $stats['super_admin']++;
    elseif ($u['rol'] === 'admin') $stats['admin']++;
    elseif ($u['rol'] === 'cajero') $stats['cajero']++;
}

// Mostrar mensajes de sesión
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

include '../header.php';
?>

<!-- Header de la página -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-users-cog"></i>
            <h1>Usuarios del Sistema</h1>
        </div>
        <span class="pv-badge">ADMINISTRACIÓN</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/usuarios/nuevo.php'); ?>" class="btn-header primary">
            <i class="fas fa-user-plus"></i>
            Nuevo Usuario
        </a>
    </div>
</div>

<!-- Subtítulo -->
<p class="page-subtitle">Gestiona los usuarios y sus permisos en el sistema</p>

<!-- Mensajes de alerta -->
<?php if (isset($error)): ?>
<div class="alerta error">
    <i class="fas fa-exclamation-circle"></i>
    <p><?php echo h($error); ?></p>
</div>
<?php endif; ?>

<?php if (isset($success)): ?>
<div class="alerta success">
    <i class="fas fa-check-circle"></i>
    <p><?php echo h($success); ?></p>
</div>
<?php endif; ?>

<!-- Tabla de usuarios -->
<div class="clientes-container" style="margin-top: 1.5rem;">
    <div class="tabla-responsive">
        <table class="tabla-clientes tabla-usuarios">
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
                <?php if (count($usuarios) > 0): ?>
                    <?php foreach ($usuarios as $index => $u): ?>
                    <tr class="fila-usuario">
                        <td class="col-id">#<?php echo str_pad($u['id'], 2, '0', STR_PAD_LEFT); ?></td>
                        <td class="col-usuario">
                            <span class="usuario-username"><?php echo h($u['username']); ?></span>
                        </td>
                        <td class="col-nombre-completo"><?php echo h($u['nombre']); ?></td>
                        <td class="col-email">
                            <?php if (!empty($u['email'])): ?>
                                <a href="mailto:<?php echo h($u['email']); ?>" class="email-link">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo h($u['email']); ?>
                                </a>
                            <?php else: ?>
                                <span class="sin-info">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($u['rol'] === 'super_admin'): ?>
                                <span class="rol-badge super-admin">
                                    <i class="fas fa-crown"></i>
                                    Super Admin
                                </span>
                            <?php elseif ($u['rol'] === 'admin'): ?>
                                <span class="rol-badge admin">
                                    <i class="fas fa-user-tie"></i>
                                    Administrador
                                </span>
                            <?php else: ?>
                                <span class="rol-badge cajero">
                                    <i class="fas fa-cash-register"></i>
                                    Cajero
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($u['activo']): ?>
                                <span class="estado-badge activo">
                                    <span class="estado-punto"></span>
                                    Activo
                                </span>
                            <?php else: ?>
                                <span class="estado-badge inactivo">
                                    <span class="estado-punto inactivo"></span>
                                    Inactivo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="col-fecha">
                            <?php 
                            if ($u['ultimo_acceso']) {
                                echo date('d/m/Y H:i', strtotime($u['ultimo_acceso']));
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td class="col-acciones">
                            <div class="acciones-wrapper">
                                <a href="<?php echo url('dashboard/usuarios/ver.php?id=' . $u['id']); ?>" 
                                   class="accion-icon" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($u['rol'] !== 'super_admin' || $u['id'] === $_SESSION['user_id']): ?>
                                    <a href="<?php echo url('dashboard/usuarios/editar.php?id=' . $u['id']); ?>" 
                                       class="accion-icon" title="Editar usuario">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="accion-icon disabled" title="No se puede editar">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($u['id'] != $_SESSION['user_id'] && $u['rol'] !== 'super_admin'): ?>
                                    <?php if ($u['activo']): ?>
                                        <a href="?action=desactivar&id=<?php echo $u['id']; ?>" 
                                           class="accion-icon desactivar" 
                                           title="Desactivar usuario"
                                           onclick="return confirm('¿Estás seguro de desactivar este usuario?')">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=activar&id=<?php echo $u['id']; ?>" 
                                           class="accion-icon activar" 
                                           title="Activar usuario"
                                           onclick="return confirm('¿Estás seguro de activar este usuario?')">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-state-row">
                            <div class="empty-state-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>No hay usuarios registrados</h3>
                            <p>Comienza creando el primer usuario</p>
                            <a href="<?php echo url('dashboard/usuarios/nuevo.php'); ?>" class="btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-user-plus"></i>
                                Nuevo Usuario
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Resumen -->
    <?php if (count($usuarios) > 0): ?>
    <div class="tabla-footer">
        <div class="resumen-wrapper">
            <p class="resumen-info">
                <i class="fas fa-users"></i>
                Total de usuarios: <strong><?php echo $stats['total']; ?></strong>
            </p>
            <div class="resumen-stats">
                <span class="stat-badge">
                    <i class="fas fa-crown" style="color: #8b5cf6;"></i>
                    <span>Super Admin: <?php echo $stats['super_admin']; ?></span>
                </span>
                <span class="stat-badge">
                    <i class="fas fa-user-tie" style="color: var(--primary);"></i>
                    <span>Administradores: <?php echo $stats['admin']; ?></span>
                </span>
                <span class="stat-badge">
                    <i class="fas fa-cash-register" style="color: var(--success);"></i>
                    <span>Cajeros: <?php echo $stats['cajero']; ?></span>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* ===== ESTILOS ESPECÍFICOS PARA USUARIOS ===== */

/* Subtítulo */
.page-subtitle {
    color: var(--gray-500);
    margin-top: -0.5rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

/* Columna ID */
.col-id {
    font-family: monospace;
    color: var(--gray-500);
    font-size: 0.8rem;
    font-weight: 500;
    width: 50px;
}

/* Columna Usuario */
.col-usuario {
    font-weight: 500;
}

.usuario-username {
    font-weight: 600;
    color: var(--gray-800);
}

/* Columna Nombre Completo */
.col-nombre-completo {
    color: var(--gray-600);
    font-size: 0.85rem;
}

/* Columna Fecha */
.col-fecha {
    font-size: 0.8rem;
    color: var(--gray-500);
    white-space: nowrap;
}

/* Badges de Rol */
.rol-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-lg);
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}

.rol-badge.super-admin {
    background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
    color: #8b5cf6;
    border: 1px solid #c084fc;
}

.rol-badge.admin {
    background: linear-gradient(135deg, var(--info-light) 0%, #bfdbfe 100%);
    color: var(--info);
    border: 1px solid #93c5fd;
}

.rol-badge.cajero {
    background: linear-gradient(135deg, var(--success-light) 0%, #a7f3d0 100%);
    color: var(--success);
    border: 1px solid #6ee7b7;
}

.rol-badge i {
    font-size: 0.7rem;
}

/* Badges de Estado */
.estado-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-lg);
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}

.estado-badge.activo {
    background: linear-gradient(135deg, var(--success-light) 0%, #a7f3d0 100%);
    color: var(--success);
    border: 1px solid #6ee7b7;
}

.estado-badge.inactivo {
    background: linear-gradient(135deg, var(--danger-light) 0%, #fee2e2 100%);
    color: var(--danger);
    border: 1px solid #fecaca;
}

.estado-punto {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    display: inline-block;
    background-color: var(--success);
}

.estado-punto.inactivo {
    background-color: var(--danger);
}

/* Iconos de acciones específicos */
.accion-icon.desactivar:hover {
    background: linear-gradient(135deg, var(--warning) 0%, #f59e0b 100%);
    color: white;
}

.accion-icon.activar:hover {
    background: linear-gradient(135deg, var(--success) 0%, #0d9488 100%);
    color: white;
}

.accion-icon.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.accion-icon.disabled:hover {
    background: none;
    color: var(--gray-400);
    transform: none;
}

/* Animación de filas */
@keyframes fadeInRow {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fila-usuario {
    animation: fadeInRow 0.3s ease-out forwards;
}

.fila-usuario:nth-child(1) { animation-delay: 0.02s; }
.fila-usuario:nth-child(2) { animation-delay: 0.04s; }
.fila-usuario:nth-child(3) { animation-delay: 0.06s; }
.fila-usuario:nth-child(4) { animation-delay: 0.08s; }
.fila-usuario:nth-child(5) { animation-delay: 0.10s; }
.fila-usuario:nth-child(6) { animation-delay: 0.12s; }

/* Responsive */
@media (max-width: 768px) {
    .tabla-usuarios {
        min-width: 1000px;
    }
}
</style>

<script>
function mostrarAlerta(tipo, mensaje) {
    const errorAlert = document.getElementById('errorMensaje');
    const successAlert = document.getElementById('successMensaje');
    
    if (errorAlert) errorAlert.style.display = 'none';
    if (successAlert) successAlert.style.display = 'none';
    
    if (tipo === 'error' && errorAlert) {
        errorAlert.querySelector('p').textContent = mensaje;
        errorAlert.style.display = 'flex';
        setTimeout(() => { errorAlert.style.display = 'none'; }, 5000);
    } else if (tipo === 'success' && successAlert) {
        successAlert.querySelector('p').textContent = mensaje;
        successAlert.style.display = 'flex';
        setTimeout(() => { successAlert.style.display = 'none'; }, 5000);
    }
}
</script>

<?php include '../footer.php'; ?>