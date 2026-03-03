<?php
include '../header.php';

// Datos de ejemplo para categorías de ferretería
$categorias = [
    [
        'id' => 1,
        'nombre' => 'Herramientas Manuales',
        'descripcion' => 'Martillos, desarmadores, llaves, alicates y más',
        'activo' => true,
        'total_productos' => 45
    ],
    [
        'id' => 2,
        'nombre' => 'Herramientas Eléctricas',
        'descripcion' => 'Taladros, esmeriles, sierras, lijadoras',
        'activo' => true,
        'total_productos' => 28
    ],
    [
        'id' => 3,
        'nombre' => 'Materiales de Construcción',
        'descripcion' => 'Cemento, varilla, block, arena, grava',
        'activo' => true,
        'total_productos' => 32
    ],
    [
        'id' => 4,
        'nombre' => 'Tubería y Conexiones',
        'descripcion' => 'Tubos PVC, conexiones, codos, adaptadores',
        'activo' => true,
        'total_productos' => 56
    ],
    [
        'id' => 5,
        'nombre' => 'Pinturas y Accesorios',
        'descripcion' => 'Pinturas vinílicas, esmaltes, thinner, brochas',
        'activo' => true,
        'total_productos' => 23
    ],
    [
        'id' => 6,
        'nombre' => 'Electricidad',
        'descripcion' => 'Cables, interruptores, contactos, fusibles',
        'activo' => false,
        'total_productos' => 19
    ],
    [
        'id' => 7,
        'nombre' => 'Ferretería General',
        'descripcion' => 'Clavos, tornillos, taquetes, pegamentos',
        'activo' => true,
        'total_productos' => 67
    ],
    [
        'id' => 8,
        'nombre' => 'Seguridad Industrial',
        'descripcion' => 'Cascos, guantes, lentes, arneses',
        'activo' => true,
        'total_productos' => 14
    ]
];
?>

<!-- Header de Categorías -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-tags" style="color: var(--primary);"></i>
            <h1>Categorías</h1>
        </div>
        <span class="pv-badge">FERREFÁCIL</span>
    </div>
    
    <div class="pv-header-right">
        <a href="/dashboard/categorias/nuevo.php" class="btn-header primary" style="text-decoration: none;">
            <i class="fas fa-plus"></i>
            Nueva Categoría
        </a>
    </div>
</div>

<!-- Mensajes de alerta (ejemplos comentados) -->
<!-- 
<div class="alerta success">
    <i class="fas fa-check-circle"></i>
    <p>Categoría eliminada correctamente</p>
</div>

<div class="alerta error">
    <i class="fas fa-exclamation-circle"></i>
    <p>No se puede eliminar la categoría porque tiene productos asociados</p>
</div>
-->

<!-- Tabla de categorías -->
<div class="categorias-container">
    <div class="tabla-responsive">
        <table class="tabla-categorias">
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
                <?php foreach ($categorias as $cat): ?>
                <tr class="fila-categoria">
                    <td class="col-id">#<?php echo str_pad($cat['id'], 2, '0', STR_PAD_LEFT); ?></td>
                    <td class="col-nombre">
                        <span class="categoria-nombre"><?php echo $cat['nombre']; ?></span>
                    </td>
                    <td class="col-descripcion">
                        <?php if ($cat['descripcion']): ?>
                            <span class="categoria-descripcion"><?php echo $cat['descripcion']; ?></span>
                        <?php else: ?>
                            <span class="sin-descripcion">Sin descripción</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge-productos"><?php echo $cat['total_productos']; ?></span>
                    </td>
                    <td class="text-center">
                        <?php if ($cat['activo']): ?>
                            <span class="estado-badge activo">Activo</span>
                        <?php else: ?>
                            <span class="estado-badge inactivo">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="/dashboard/categorias/editar.php?id=<?php echo $cat['id']; ?>" 
                               class="accion-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <?php if ($cat['total_productos'] == 0): ?>
                                <a href="?delete=<?php echo $cat['id']; ?>" 
                                   class="accion-icon eliminar" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="accion-icon disabled" 
                                      title="No se puede eliminar (tiene <?php echo $cat['total_productos']; ?> productos)">
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
            <i class="fas fa-tags"></i>
            Total: <strong><?php echo count($categorias); ?></strong> categorías
        </p>
    </div>
</div>

<!-- Empty state (comentado por si no hay categorías) -->
<!-- 
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-tags"></i>
    </div>
    <h3>No hay categorías creadas</h3>
    <p>Comienza creando tu primera categoría</p>
    <a href="/dashboard/categorias/nuevo.php" class="btn-header primary">
        <i class="fas fa-plus"></i>
        Nueva Categoría
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

.fila-categoria {
    animation: fadeInRow 0.3s ease-out forwards;
}

.fila-categoria:nth-child(1) { animation-delay: 0.02s; }
.fila-categoria:nth-child(2) { animation-delay: 0.04s; }
.fila-categoria:nth-child(3) { animation-delay: 0.06s; }
.fila-categoria:nth-child(4) { animation-delay: 0.08s; }
.fila-categoria:nth-child(5) { animation-delay: 0.10s; }
.fila-categoria:nth-child(6) { animation-delay: 0.12s; }
.fila-categoria:nth-child(7) { animation-delay: 0.14s; }
.fila-categoria:nth-child(8) { animation-delay: 0.16s; }
</style>

<?php include '../footer.php'; ?>