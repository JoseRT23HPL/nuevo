<?php
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
        <a href="/dashboard/productos/nuevo.php" class="btn-header primary" style="text-decoration: none;">
            <i class="fas fa-plus"></i>
            Nuevo Producto
        </a>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="filtros-container">
    <form method="GET" class="filtros-form">
        <!-- Búsqueda -->
        <div class="buscador-container" style="margin-bottom: 1rem;">
            <i class="fas fa-search buscador-icon" style="left: 1.2rem;"></i>
            <input type="text" name="search" class="buscador-input" style="padding-left: 2.8rem;" 
                   placeholder="Buscar por nombre, código o descripción..." value="">
        </div>
        
        <!-- Filtros -->
        <div class="filtros-grid" style="grid-template-columns: repeat(5, 1fr);">
            <select name="categoria" class="filtro-select">
                <option value="">📂 Todas las categorías</option>
                <option value="1">Herramientas</option>
                <option value="2">Materiales</option>
                <option value="3">Pinturas</option>
                <option value="4">Electricidad</option>
            </select>
            
            <select name="marca" class="filtro-select">
                <option value="">🏷️ Todas las marcas</option>
                <option value="1">Truper</option>
                <option value="2">Pretul</option>
                <option value="3">Volteck</option>
                <option value="4">Stanley</option>
            </select>
            
            <select name="stock" class="filtro-select">
                <option value="">📦 Todos los stocks</option>
                <option value="bajo">⚠️ Stock bajo</option>
                <option value="agotado">❌ Agotados</option>
            </select>
            
            <button type="submit" class="btn-filtro btn-primary">
                <i class="fas fa-filter"></i>
                Filtrar
            </button>
            
            <a href="/dashboard/productos/index.php" class="btn-filtro btn-secondary" style="text-decoration: none; text-align: center;">
                <i class="fas fa-times"></i>
                Limpiar
            </a>
        </div>
        
        <!-- Resultados de búsqueda -->
        <div class="resultados-info" style="margin-top: 1rem;">
            <i class="fas fa-info-circle"></i>
            Se encontraron <span class="highlight">24</span> productos
            con los filtros aplicados
        </div>
    </form>
</div>

<!-- Tabla de productos -->
<div class="tabla-container">
    <div class="tabla-responsive">
        <table class="tabla-productos">
            <thead>
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
                <!-- Producto 1 -->
                <tr class="fila-producto">
                    <td class="col-codigo">
                        <div class="codigo-wrapper">
                            <span class="codigo-sku">SKU: TRP-001-24</span>
                            <span class="codigo-barras">750123456789</span>
                        </div>
                    </td>
                    <td class="col-producto">
                        <div class="producto-info">
                            <span class="producto-nombre">Martillo de Uña 16oz con Mango de Madera</span>
                            <span class="producto-descripcion">Cabeza forjada, mango de madera de fresno</span>
                        </div>
                    </td>
                    <td class="col-categoria">
                        <span class="categoria-nombre">Herramientas</span>
                    </td>
                    <td class="col-marca">
                        <span class="marca-nombre">Truper</span>
                    </td>
                    <td class="col-precio">
                        <span class="precio-valor">$185.00</span>
                    </td>
                    <td class="col-stock">
                        <div class="stock-wrapper">
                            <div class="stock-badge stock-ok">
                                <span class="stock-actual">45</span>
                                <span class="stock-separador">/</span>
                                <span class="stock-minimo">10</span>
                            </div>
                        </div>
                    </td>
                    <td class="col-estado">
                        <span class="estado-badge activo">Activo</span>
                    </td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="#" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="accion-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="accion-icon" title="Ajustar stock">
                                <i class="fas fa-cubes"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                
                <!-- Producto 2 -->
                <tr class="fila-producto">
                    <td class="col-codigo">
                        <div class="codigo-wrapper">
                            <span class="codigo-sku">SKU: VAR-3/8-12</span>
                            <span class="codigo-barras">750123456788</span>
                        </div>
                    </td>
                    <td class="col-producto">
                        <div class="producto-info">
                            <span class="producto-nombre">Varilla Corrugada 3/8" x 12m</span>
                            <span class="producto-descripcion">Grado 60, para construcción</span>
                        </div>
                    </td>
                    <td class="col-categoria">
                        <span class="categoria-nombre">Materiales</span>
                    </td>
                    <td class="col-marca">
                        <span class="marca-nombre">Deacero</span>
                    </td>
                    <td class="col-precio">
                        <span class="precio-valor">$245.00</span>
                    </td>
                    <td class="col-stock">
                        <div class="stock-wrapper">
                            <div class="stock-badge stock-bajo">
                                <span class="stock-actual">3</span>
                                <span class="stock-separador">/</span>
                                <span class="stock-minimo">5</span>
                            </div>
                        </div>
                    </td>
                    <td class="col-estado">
                        <span class="estado-badge activo">Activo</span>
                    </td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="#" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="accion-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="accion-icon" title="Ajustar stock">
                                <i class="fas fa-cubes"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                
                <!-- Producto 3 -->
                <tr class="fila-producto">
                    <td class="col-codigo">
                        <div class="codigo-wrapper">
                            <span class="codigo-sku">SKU: CEM-GRIS-50</span>
                            <span class="codigo-barras">750123456787</span>
                        </div>
                    </td>
                    <td class="col-producto">
                        <div class="producto-info">
                            <span class="producto-nombre">Cemento Portland Gris 50kg</span>
                            <span class="producto-descripcion">Cruz Azul, resistencia 30</span>
                        </div>
                    </td>
                    <td class="col-categoria">
                        <span class="categoria-nombre">Materiales</span>
                    </td>
                    <td class="col-marca">
                        <span class="marca-nombre">Cruz Azul</span>
                    </td>
                    <td class="col-precio">
                        <span class="precio-valor">$185.00</span>
                    </td>
                    <td class="col-stock">
                        <div class="stock-wrapper">
                            <div class="stock-badge stock-cero">
                                <span class="stock-actual">0</span>
                                <span class="stock-separador">/</span>
                                <span class="stock-minimo">10</span>
                            </div>
                        </div>
                    </td>
                    <td class="col-estado">
                        <span class="estado-badge activo">Activo</span>
                    </td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="#" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="accion-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="accion-icon" title="Ajustar stock">
                                <i class="fas fa-cubes"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                
                <!-- Producto 4 -->
                <tr class="fila-producto">
                    <td class="col-codigo">
                        <div class="codigo-wrapper">
                            <span class="codigo-sku">SKU: PIN-BLA-20L</span>
                            <span class="codigo-barras">750123456786</span>
                        </div>
                    </td>
                    <td class="col-producto">
                        <div class="producto-info">
                            <span class="producto-nombre">Pintura Blanca 20L</span>
                            <span class="producto-descripcion">Vinílica, mate, uso interior/exterior</span>
                        </div>
                    </td>
                    <td class="col-categoria">
                        <span class="categoria-nombre">Pinturas</span>
                    </td>
                    <td class="col-marca">
                        <span class="marca-nombre">Comex</span>
                    </td>
                    <td class="col-precio">
                        <span class="precio-valor">$850.00</span>
                    </td>
                    <td class="col-stock">
                        <div class="stock-wrapper">
                            <div class="stock-badge stock-ok">
                                <span class="stock-actual">12</span>
                                <span class="stock-separador">/</span>
                                <span class="stock-minimo">8</span>
                            </div>
                        </div>
                    </td>
                    <td class="col-estado">
                        <span class="estado-badge activo">Activo</span>
                    </td>
                    <td class="col-acciones">
                        <div class="acciones-wrapper">
                            <a href="#" class="accion-icon" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="accion-icon" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="accion-icon" title="Ajustar stock">
                                <i class="fas fa-cubes"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <div class="paginacion-container">
        <p class="paginacion-info">
            Mostrando <span class="font-semibold">1</span> - <span class="font-semibold">8</span> 
            de <span class="font-semibold">24</span> productos
        </p>
        
        <div class="paginacion-controles">
            <a href="#" class="btn-paginacion prev">
                <i class="fas fa-chevron-left"></i>
                Anterior
            </a>
            
            <div class="paginacion-numeros">
                <a href="#" class="pagina-numero active">1</a>
                <a href="#" class="pagina-numero">2</a>
                <a href="#" class="pagina-numero">3</a>
                <span class="pagina-separador">...</span>
                <a href="#" class="pagina-numero">5</a>
            </div>
            
            <a href="#" class="btn-paginacion next">
                Siguiente
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>
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

.fila-producto {
    animation: fadeInRow 0.3s ease-out forwards;
}

.fila-producto:nth-child(1) { animation-delay: 0.05s; }
.fila-producto:nth-child(2) { animation-delay: 0.1s; }
.fila-producto:nth-child(3) { animation-delay: 0.15s; }
.fila-producto:nth-child(4) { animation-delay: 0.2s; }
</style>

<?php include '../footer.php'; ?>