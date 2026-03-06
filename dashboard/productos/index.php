<?php
require_once '../../config.php';
requiereAuth();

$conn = getDB();

// ===== CONFIGURACIÓN PARA GRANDES VOLÚMENES =====
$limite = 500; // 500 productos por página (ideal para 10,000+)
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;

// Obtener filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$marca_id = isset($_GET['marca']) ? (int)$_GET['marca'] : 0;
$stock_filtro = isset($_GET['stock']) ? $_GET['stock'] : '';

// ===== INDICADOR DE CARGA PARA EL USUARIO =====
$tiempo_inicio = microtime(true);

// Construir condiciones WHERE
$condiciones = ["p.activo = 1"];
$params = [];
$types = "";

// Búsqueda por texto
if (!empty($search)) {
    $condiciones[] = "(p.nombre LIKE ? OR p.sku LIKE ? OR p.codigo_barras LIKE ? OR p.descripcion LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

// Filtro por categoría
if ($categoria_id > 0) {
    $condiciones[] = "p.id_categoria = ?";
    $params[] = $categoria_id;
    $types .= "i";
}

// Filtro por marca
if ($marca_id > 0) {
    $condiciones[] = "p.id_marca = ?";
    $params[] = $marca_id;
    $types .= "i";
}

// Filtro por stock
if ($stock_filtro == 'bajo') {
    $condiciones[] = "p.stock_actual <= p.stock_minimo AND p.stock_actual > 0";
} elseif ($stock_filtro == 'agotado') {
    $condiciones[] = "p.stock_actual = 0";
}

$where = implode(" AND ", $condiciones);

// ===== CONTAR TOTAL (con índices para velocidad) =====
$count_sql = "SELECT COUNT(*) as total FROM productos p WHERE $where";
$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_productos = $count_stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_productos / $limite);

// ===== OBTENER PRODUCTOS (solo los campos necesarios) =====
// NOTA: Seleccionamos SOLO los campos que vamos a mostrar
$sql = "
    SELECT p.id, p.sku, p.codigo_barras, p.nombre, p.descripcion, 
           p.precio_venta, p.stock_actual, p.stock_minimo, p.activo,
           c.nombre as categoria_nombre,
           m.nombre as marca_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id
    LEFT JOIN marcas m ON p.id_marca = m.id
    WHERE $where
    ORDER BY p.fecha_creacion DESC
    LIMIT ? OFFSET ?
";

$params_paginacion = $params;
$params_paginacion[] = $limite;
$params_paginacion[] = $offset;
$types_paginacion = $types . "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types_paginacion, ...$params_paginacion);
$stmt->execute();
$productos = $stmt->get_result();

$tiempo_fin = microtime(true);
$tiempo_carga = round(($tiempo_fin - $tiempo_inicio) * 1000, 2);

// Obtener categorías y marcas para filtros (solo ID y nombre)
$categorias = $conn->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
$marcas = $conn->query("SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre");

include '../header.php';
?>

<!-- Header de Productos -->
<div class="pv-header">
    <div class="pv-header-left">
        <div class="pv-logo">
            <i class="fas fa-box" style="color: var(--primary);"></i>
            <h1>Productos</h1>
        </div>
        <span class="pv-badge">CATÁLOGO</span>
    </div>
    
    <div class="pv-header-right">
        <a href="<?php echo url('dashboard/productos/nuevo.php'); ?>" class="btn-header primary" style="text-decoration: none;">
            <i class="fas fa-plus"></i>
            Nuevo Producto
        </a>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="filtros-container">
    <form method="GET" class="filtros-form" id="filtrosForm">
        <!-- Búsqueda -->
        <div class="buscador-container" style="margin-bottom: 1rem; position: relative;">
            <i class="fas fa-search buscador-icon" style="left: 1.2rem;"></i>
            <input type="text" name="search" class="buscador-input" style="padding-left: 2.8rem; padding-right: 2.5rem;" 
                   placeholder="Buscar por nombre, SKU, código o descripción..." 
                   value="<?php echo h($search); ?>" id="searchInput" autocomplete="off">
            <?php if (!empty($search)): ?>
                <button type="button" class="clear-search" onclick="window.location.href='<?php echo url('dashboard/productos/index.php'); ?>'">
                    <i class="fas fa-times"></i>
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Filtros -->
        <div class="filtros-grid" style="grid-template-columns: repeat(5, 1fr);">
            <select name="categoria" class="filtro-select" onchange="this.form.submit()">
                <option value="">📂 Todas las categorías</option>
                <?php while ($cat = $categorias->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoria_id == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo h($cat['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <select name="marca" class="filtro-select" onchange="this.form.submit()">
                <option value="">🏷️ Todas las marcas</option>
                <?php while ($marca = $marcas->fetch_assoc()): ?>
                    <option value="<?php echo $marca['id']; ?>" <?php echo $marca_id == $marca['id'] ? 'selected' : ''; ?>>
                        <?php echo h($marca['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <select name="stock" class="filtro-select" onchange="this.form.submit()">
                <option value="">📦 Todos los stocks</option>
                <option value="bajo" <?php echo $stock_filtro == 'bajo' ? 'selected' : ''; ?>>⚠️ Stock bajo</option>
                <option value="agotado" <?php echo $stock_filtro == 'agotado' ? 'selected' : ''; ?>>❌ Agotados</option>
            </select>
            
            <button type="submit" class="btn-filtro btn-primary">
                <i class="fas fa-filter"></i>
                Filtrar
            </button>
            
            <a href="<?php echo url('dashboard/productos/index.php'); ?>" class="btn-filtro btn-secondary" style="text-decoration: none; text-align: center;">
                <i class="fas fa-times"></i>
                Limpiar
            </a>
        </div>
        
        <!-- Resultados de búsqueda con indicador de tiempo -->
        <div class="resultados-info" style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <i class="fas fa-info-circle"></i>
                Se encontraron <span class="highlight"><?php echo number_format($total_productos); ?></span> productos
                <?php if (!empty($search)): ?>
                    para "<strong><?php echo h($search); ?></strong>"
                <?php endif; ?>
                <?php if ($categoria_id > 0 || $marca_id > 0 || !empty($stock_filtro)): ?>
                    con los filtros aplicados
                <?php endif; ?>
                <span class="tiempo-carga" style="font-size: 0.8rem; color: var(--gray-400); margin-left: 1rem;">
                    ⚡ <?php echo $tiempo_carga; ?> ms
                </span>
            </div>
            
            <?php if ($total_productos > 0): ?>
                <div class="resultados-pagina">
                    Mostrando <?php echo number_format($offset + 1); ?> - <?php echo number_format(min($offset + $limite, $total_productos)); ?> 
                    de <?php echo number_format($total_productos); ?>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Tabla de productos con scroll para 500 filas -->
<div class="tabla-container" style="max-height: 70vh; overflow-y: auto;">
    <div class="tabla-responsive">
        <table class="tabla-productos" style="width: 100%;">
            <thead style="position: sticky; top: 0; background: var(--gray-100); z-index: 10;">
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th class="text-right">Precio</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($productos->num_rows > 0): ?>
                    <?php while ($p = $productos->fetch_assoc()): ?>
                    <tr class="fila-producto">
                        <td class="col-codigo">
                            <div class="codigo-wrapper">
                                <span class="codigo-sku">SKU: <?php echo h($p['sku']); ?></span>
                                <span class="codigo-barras"><?php echo $p['codigo_barras'] ?: '—'; ?></span>
                            </div>
                        </td>
                        <td class="col-producto">
                            <div class="producto-info">
                                <span class="producto-nombre"><?php echo h($p['nombre']); ?></span>
                                <?php if (!empty($p['descripcion'])): ?>
                                    <span class="producto-descripcion"><?php echo h(substr($p['descripcion'], 0, 60)) . '...'; ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="col-categoria">
                            <span class="categoria-nombre"><?php echo $p['categoria_nombre'] ?: '—'; ?></span>
                        </td>
                        <td class="col-marca">
                            <span class="marca-nombre"><?php echo $p['marca_nombre'] ?: '—'; ?></span>
                        </td>
                        <td class="col-precio">
                            <span class="precio-valor"><?php echo formatoPrecio($p['precio_venta']); ?></span>
                        </td>
                        <td class="col-stock">
                            <div class="stock-wrapper">
                                <?php
                                $stock_class = 'stock-ok';
                                if ($p['stock_actual'] <= 0) {
                                    $stock_class = 'stock-cero';
                                } elseif ($p['stock_actual'] <= $p['stock_minimo']) {
                                    $stock_class = 'stock-bajo';
                                }
                                ?>
                                <div class="stock-badge <?php echo $stock_class; ?>">
                                    <span class="stock-actual"><?php echo $p['stock_actual']; ?></span>
                                    <span class="stock-separador">/</span>
                                    <span class="stock-minimo"><?php echo $p['stock_minimo']; ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="col-estado">
                            <span class="estado-badge <?php echo $p['activo'] ? 'activo' : 'inactivo'; ?>">
                                <?php echo $p['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td class="col-acciones">
                            <div class="acciones-wrapper">
                                <a href="<?php echo url('dashboard/productos/ver.php?id=' . $p['id']); ?>" class="accion-icon" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo url('dashboard/productos/editar.php?id=' . $p['id']); ?>" class="accion-icon" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo url('dashboard/productos/ajustar_stock.php?id=' . $p['id']); ?>" class="accion-icon" title="Ajustar stock">
                                    <i class="fas fa-cubes"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-state-row">
                            <div class="empty-state-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h3>No se encontraron productos</h3>
                            <p>Intenta con otros filtros o crea un nuevo producto</p>
                            <a href="<?php echo url('dashboard/productos/nuevo.php'); ?>" class="btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i>
                                Nuevo Producto
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginación (fuera del scroll) -->
<?php if ($total_paginas > 1): ?>
<div class="paginacion-container" style="margin-top: 1rem;">
    <div class="paginacion-controles" style="justify-content: center;">
        <?php if ($pagina > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" class="btn-paginacion prev">
                <i class="fas fa-chevron-left"></i>
                Anterior
            </a>
        <?php endif; ?>
        
        <div class="paginacion-numeros">
            <?php
            $inicio = max(1, $pagina - 2);
            $fin = min($total_paginas, $pagina + 2);
            
            for ($i = $inicio; $i <= $fin; $i++):
            ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                   class="pagina-numero <?php echo $i == $pagina ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($fin < $total_paginas): ?>
                <span class="pagina-separador">...</span>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>" class="pagina-numero">
                    <?php echo $total_paginas; ?>
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ($pagina < $total_paginas): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" class="btn-paginacion next">
                Siguiente
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<style>
/* Header fijo en la tabla */
.tabla-productos thead th {
    position: sticky;
    top: 0;
    background: var(--gray-100);
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Scroll personalizado para la tabla */
.tabla-container::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.tabla-container::-webkit-scrollbar-track {
    background: var(--gray-100);
}

.tabla-container::-webkit-scrollbar-thumb {
    background: var(--gray-400);
    border-radius: 4px;
}

.tabla-container::-webkit-scrollbar-thumb:hover {
    background: var(--gray-500);
}

/* Animación optimizada para muchas filas */
.fila-producto {
    animation: fadeInRow 0.2s ease-out forwards;
}

@keyframes fadeInRow {
    from {
        opacity: 0.5;
    }
    to {
        opacity: 1;
    }
}

/* Para 500 filas, procesar en lotes */
.fila-producto:nth-child(n) { 
    animation-duration: 0.1s; 
}
</style>

<?php include '../footer.php'; ?>