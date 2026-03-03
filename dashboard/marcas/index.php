<?php
include '../header.php';

// Datos de ejemplo para marcas de ferretería
$marcas = [
    [
        'id' => 1,
        'nombre' => 'Truper',
        'descripcion' => 'Líder en herramientas manuales y de ferretería en México',
        'activo' => true,
        'total_productos' => 156
    ],
    [
        'id' => 2,
        'nombre' => 'Pretul',
        'descripcion' => 'Herramientas y accesorios para el hogar y la industria',
        'activo' => true,
        'total_productos' => 98
    ],
    [
        'id' => 3,
        'nombre' => 'Volteck',
        'descripcion' => 'Herramientas eléctricas profesionales',
        'activo' => true,
        'total_productos' => 67
    ],
    [
        'id' => 4,
        'nombre' => 'Stanley',
        'descripcion' => 'Herramientas manuales, eléctricas y de almacenamiento',
        'activo' => true,
        'total_productos' => 112
    ],
    [
        'id' => 5,
        'nombre' => 'Comex',
        'descripcion' => 'Pinturas, recubrimientos y accesorios',
        'activo' => true,
        'total_productos' => 45
    ],
    [
        'id' => 6,
        'nombre' => 'Deacero',
        'descripcion' => 'Productos de acero para construcción e industria',
        'activo' => true,
        'total_productos' => 34
    ],
    [
        'id' => 7,
        'nombre' => 'Bosch',
        'descripcion' => 'Tecnología y herramientas de precisión',
        'activo' => true,
        'total_productos' => 78
    ],
    [
        'id' => 8,
        'nombre' => 'Makita',
        'descripcion' => 'Herramientas eléctricas y neumáticas',
        'activo' => false,
        'total_productos' => 52
    ],
    [
        'id' => 9,
        'nombre' => '3M',
        'descripcion' => 'Productos adhesivos, lijas y seguridad industrial',
        'activo' => true,
        'total_productos' => 41
    ],
    [
        'id' => 10,
        'nombre' => 'Cruz Azul',
        'descripcion' => 'Cemento y materiales para construcción',
        'activo' => true,
        'total_productos' => 12
    ]
];
?>

<!-- Header de Marcas -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-trademark" style="color: var(--primary);"></i>
            <h1>Marcas</h1>
        </div>
        <span class="pv-badge">FERREFÁCIL</span>
    </div>
    
    <div class="pv-header-right">
        <a href="/dashboard/marcas/nuevo.php" class="btn-header primary" style="text-decoration: none;">
            <i class="fas fa-plus"></i>
            Nueva Marca
        </a>
    </div>
</div>

<!-- Mensajes de alerta (ejemplos comentados) -->
<!-- 
<div class="alerta success">
    <i class="fas fa-check-circle"></i>
    <p>Marca eliminada correctamente</p>
</div>

<div class="alerta error">
    <i class="fas fa-exclamation-circle"></i>
    <p>No se puede eliminar la marca porque tiene productos asociados</p>
</div>
-->

<!-- Tabla de marcas -->
<div class="marcas-container">
    <div class="tabla-responsive">
        <table class="tabla-marcas">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-center">Productos</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($marcas as $marca): ?>
                <tr class="fila-marca">
                    <td class="col-id">#<?php echo str_pad($marca['id'], 2, '0', STR_PAD_LEFT); ?></td>
                    <td class="col-nombre">
                        <span class="marca-nombre"><?php echo $marca['nombre']; ?></span>
                    </td>
                    <td class="col-descripcion">
                        <?php if ($marca['descripcion']): ?>
                            <span class="marca-descripcion"><?php echo $marca['descripcion']; ?></span>
                        <?php else: ?>
                            <span class="sin-descripcion">Sin descripción</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge-productos"><?php echo $marca['total_productos']; ?></span>
                    </td>
                    <td class="text-center">
                        <?php if ($marca['activo']): ?>
                            <span class="estado-badge activo">Activo</span>
                        <?php else: ?>
                            <span class="estado-badge inactivo">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="/dashboard/marcas/editar.php?id=<?php echo $marca['id']; ?>" 
                               class="accion-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <?php if ($marca['total_productos'] == 0): ?>
                                <a href="?delete=<?php echo $marca['id']; ?>" 
                                   class="accion-icon eliminar" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Estás seguro de eliminar esta marca?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="accion-icon disabled" 
                                      title="No se puede eliminar (tiene <?php echo $marca['total_productos']; ?> productos)">
                                    <i class="fas fa-trash"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Resumen -->
    <div class="tabla-footer">
        <p class="resumen-info">
            <i class="fas fa-trademark"></i>
            Total: <strong><?php echo count($marcas); ?></strong> marcas
        </p>
    </div>
</div>

<!-- Empty state (comentado por si no hay marcas) -->
<!-- 
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-trademark"></i>
    </div>
    <h3>No hay marcas creadas</h3>
    <p>Comienza creando tu primera marca</p>
    <a href="/dashboard/marcas/nuevo.php" class="btn-header primary">
        <i class="fas fa-plus"></i>
        Nueva Marca
    </a>
</div>
-->

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

.fila-marca {
    animation: fadeInRow 0.3s ease-out forwards;
}

.fila-marca:nth-child(1) { animation-delay: 0.02s; }
.fila-marca:nth-child(2) { animation-delay: 0.04s; }
.fila-marca:nth-child(3) { animation-delay: 0.06s; }
.fila-marca:nth-child(4) { animation-delay: 0.08s; }
.fila-marca:nth-child(5) { animation-delay: 0.10s; }
.fila-marca:nth-child(6) { animation-delay: 0.12s; }
.fila-marca:nth-child(7) { animation-delay: 0.14s; }
.fila-marca:nth-child(8) { animation-delay: 0.16s; }
.fila-marca:nth-child(9) { animation-delay: 0.18s; }
.fila-marca:nth-child(10) { animation-delay: 0.20s; }
</style>

<?php include '../footer.php'; ?>