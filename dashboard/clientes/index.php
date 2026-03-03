<?php
include '../header.php';

// Datos de ejemplo para clientes de ferretería
$clientes = [
    [
        'id' => 1,
        'tipo_documento' => 'DNI',
        'documento' => '12345678',
        'nombre' => 'Juan Carlos',
        'apellidos' => 'Pérez González',
        'email' => 'juan.perez@email.com',
        'telefono' => '555-1234',
        'direccion' => 'Av. Principal 123, Colonia Centro'
    ],
    [
        'id' => 2,
        'tipo_documento' => 'RUC',
        'documento' => '20123456789',
        'nombre' => 'María Fernanda',
        'apellidos' => 'López Martínez',
        'email' => 'maria.lopez@email.com',
        'telefono' => '555-5678',
        'direccion' => 'Calle 5 de Mayo 456, Colonia Reforma'
    ],
    [
        'id' => 3,
        'tipo_documento' => 'DNI',
        'documento' => '87654321',
        'nombre' => 'Carlos Alberto',
        'apellidos' => 'Rodríguez Sánchez',
        'email' => 'carlos.rodriguez@email.com',
        'telefono' => '555-9012',
        'direccion' => 'Av. Juárez 789, Colonia Centro'
    ],
    [
        'id' => 4,
        'tipo_documento' => 'DNI',
        'documento' => '45678912',
        'nombre' => 'Ana Sofía',
        'apellidos' => 'García Torres',
        'email' => '',
        'telefono' => '555-3456',
        'direccion' => 'Calle Hidalgo 234, Colonia Reforma'
    ],
    [
        'id' => 5,
        'tipo_documento' => 'RUC',
        'documento' => '20345678901',
        'nombre' => 'Constructora ABC',
        'apellidos' => '',
        'email' => 'ventas@constructoraabc.com',
        'telefono' => '555-7890',
        'direccion' => 'Av. Industrial 567, Parque Industrial'
    ],
    [
        'id' => 6,
        'tipo_documento' => 'DNI',
        'documento' => '78912345',
        'nombre' => 'Miguel Ángel',
        'apellidos' => 'Díaz Fernández',
        'email' => 'miguel.diaz@email.com',
        'telefono' => '',
        'direccion' => 'Calle Morelos 890, Colonia Centro'
    ]
];

// Búsqueda
$buscar = $_GET['buscar'] ?? '';

// Filtrar clientes por búsqueda
$clientes_filtrados = array_filter($clientes, function($c) use ($buscar) {
    if (empty($buscar)) return true;
    
    $buscar_lower = strtolower($buscar);
    return (
        strpos(strtolower($c['nombre']), $buscar_lower) !== false ||
        strpos(strtolower($c['apellidos'] ?? ''), $buscar_lower) !== false ||
        strpos($c['documento'], $buscar) !== false ||
        strpos(strtolower($c['email'] ?? ''), $buscar_lower) !== false ||
        strpos(strtolower($c['telefono'] ?? ''), $buscar_lower) !== false
    );
});

// Estadísticas
$total_clientes = count($clientes_filtrados);
$con_email = count(array_filter($clientes_filtrados, function($c) { return !empty($c['email']); }));
$con_telefono = count(array_filter($clientes_filtrados, function($c) { return !empty($c['telefono']); }));
?>

<!-- Header de Clientes -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-users" style="color: var(--primary);"></i>
            <h1>Clientes</h1>
        </div>
        <span class="pv-badge">CARTERA</span>
    </div>
    
    <div class="pv-header-right">
        <a href="/dashboard/clientes/nuevo.php" class="btn-header primary" style="text-decoration: none;">
            <i class="fas fa-user-plus"></i>
            Nuevo Cliente
        </a>
    </div>
</div>

<!-- Buscador -->
<div class="buscador-container" style="margin-bottom: 1.5rem; padding: 1.5rem;">
    <form method="GET" class="buscador-form">
        <div class="buscador-wrapper">
            <div class="buscador-input-wrapper">
                <i class="fas fa-search buscador-icon"></i>
                <input type="text" name="buscar" class="buscador-input" 
                       placeholder="Buscar por nombre, documento o email..." 
                       value="<?php echo htmlspecialchars($buscar); ?>">
            </div>
            <button type="submit" class="btn-buscar">
                <i class="fas fa-search"></i>
                Buscar
            </button>
            <?php if (!empty($buscar)): ?>
                <a href="/dashboard/clientes/index.php" class="btn-limpiar">
                    <i class="fas fa-times"></i>
                    Limpiar
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($buscar)): ?>
            <p class="resultado-busqueda">
                <i class="fas fa-info-circle"></i>
                Resultados para: <strong>"<?php echo htmlspecialchars($buscar); ?>"</strong>
            </p>
        <?php endif; ?>
    </form>
</div>

<!-- Tabla de clientes -->
<div class="clientes-container">
    <div class="tabla-responsive">
        <table class="tabla-clientes">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($clientes_filtrados) > 0): ?>
                    <?php foreach ($clientes_filtrados as $c): ?>
                    <tr class="fila-cliente">
                        <td class="col-documento">
                            <div class="documento-wrapper">
                                <span class="tipo-documento"><?php echo $c['tipo_documento']; ?></span>
                                <span class="documento-numero"><?php echo htmlspecialchars($c['documento']); ?></span>
                            </div>
                        </td>
                        <td class="col-nombre">
                            <span class="cliente-nombre">
                                <?php echo htmlspecialchars($c['nombre'] . ' ' . ($c['apellidos'] ?? '')); ?>
                            </span>
                        </td>
                        <td class="col-email">
                            <?php if (!empty($c['email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($c['email']); ?>" class="email-link">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($c['email']); ?>
                                </a>
                            <?php else: ?>
                                <span class="sin-info">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-telefono">
                            <?php if (!empty($c['telefono'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($c['telefono']); ?>" class="telefono-link">
                                    <i class="fas fa-phone-alt"></i>
                                    <?php echo htmlspecialchars($c['telefono']); ?>
                                </a>
                            <?php else: ?>
                                <span class="sin-info">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-acciones">
                            <div class="acciones-wrapper">
                                <a href="/dashboard/clientes/ver.php?id=<?php echo $c['id']; ?>" 
                                   class="accion-icon" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/dashboard/clientes/editar.php?id=<?php echo $c['id']; ?>" 
                                   class="accion-icon" title="Editar cliente">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/dashboard/clientes/historial.php?id=<?php echo $c['id']; ?>" 
                                   class="accion-icon" title="Historial de compras">
                                    <i class="fas fa-history"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state-row">
                            <div class="empty-state-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>No hay clientes registrados</h3>
                            <?php if (!empty($buscar)): ?>
                                <p>No se encontraron resultados para "<?php echo htmlspecialchars($buscar); ?>"</p>
                                <a href="/dashboard/clientes/index.php" class="btn-limpiar" style="margin-top: 1rem;">
                                    <i class="fas fa-times"></i>
                                    Limpiar búsqueda
                                </a>
                            <?php else: ?>
                                <p>Comienza registrando tu primer cliente</p>
                                <a href="/dashboard/clientes/nuevo.php" class="btn-primary" style="margin-top: 1rem;">
                                    <i class="fas fa-user-plus"></i>
                                    Nuevo Cliente
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Resumen -->
    <?php if (count($clientes_filtrados) > 0): ?>
    <div class="tabla-footer">
        <div class="resumen-wrapper">
            <p class="resumen-info">
                <i class="fas fa-users"></i>
                Total de clientes: <strong><?php echo $total_clientes; ?></strong>
            </p>
            <div class="resumen-stats">
                <span class="stat-badge">
                    <i class="fas fa-envelope" style="color: var(--primary);"></i>
                    <span><?php echo $con_email; ?> con email</span>
                </span>
                <span class="stat-badge">
                    <i class="fas fa-phone-alt" style="color: var(--success);"></i>
                    <span><?php echo $con_telefono; ?> con teléfono</span>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Animaciones para las filas */
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

.fila-cliente {
    animation: fadeInRow 0.3s ease-out forwards;
}

.fila-cliente:nth-child(1) { animation-delay: 0.02s; }
.fila-cliente:nth-child(2) { animation-delay: 0.04s; }
.fila-cliente:nth-child(3) { animation-delay: 0.06s; }
.fila-cliente:nth-child(4) { animation-delay: 0.08s; }
.fila-cliente:nth-child(5) { animation-delay: 0.10s; }
.fila-cliente:nth-child(6) { animation-delay: 0.12s; }
</style>

<?php include '../footer.php'; ?>