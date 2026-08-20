<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

if (($_SESSION['usuario']['rol'] ?? '') !== 'Administrador') {
    header('Location: Login.php?logout=1');
    exit;
}

$mensaje = '';
$tipoMensaje = 'error';
$conexion = conexion();

if (isset($_GET['eliminar']) && $conexion) {
    $id = (int) ($_GET['eliminar'] ?? 0);

    if ($id > 0) {
        $stmt = $conexion->prepare('DELETE FROM producto WHERE id_producto = :id');
        $stmt->execute(['id' => $id]);
        $mensaje = 'Producto eliminado correctamente.';
        $tipoMensaje = 'ok';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_producto'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $material = trim($_POST['material'] ?? '');
    $precio = (float) ($_POST['precio'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $categoriaId = empty($_POST['categoria_id']) ? null : (int) $_POST['categoria_id'];
    $proveedorId = empty($_POST['proveedor_id']) ? null : (int) $_POST['proveedor_id'];

    if ($nombre === '' || $precio < 0 || $stock < 0) {
        $mensaje = 'Completa los campos correctamente.';
        $tipoMensaje = 'error';
    } elseif ($conexion) {
        if ($id > 0) {
            $stmt = $conexion->prepare('UPDATE producto SET nombre_producto = :nombre, descripcion_producto = :descripcion, material = :material, precio = :precio, stock = :stock, categoria_id = :categoria_id, proveedor_id = :proveedor_id WHERE id_producto = :id');
            $stmt->execute([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'material' => $material,
                'precio' => $precio,
                'stock' => $stock,
                'categoria_id' => $categoriaId,
                'proveedor_id' => $proveedorId,
                'id' => $id,
            ]);
            $mensaje = 'Producto actualizado correctamente.';
        } else {
            $stmt = $conexion->prepare('INSERT INTO producto (nombre_producto, descripcion_producto, material, precio, stock, categoria_id, proveedor_id) VALUES (:nombre, :descripcion, :material, :precio, :stock, :categoria_id, :proveedor_id)');
            $stmt->execute([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'material' => $material,
                'precio' => $precio,
                'stock' => $stock,
                'categoria_id' => $categoriaId,
                'proveedor_id' => $proveedorId,
            ]);
            $mensaje = 'Producto creado correctamente.';
        }

        $tipoMensaje = 'ok';
    } else {
        $mensaje = 'No se pudo guardar el producto.';
        $tipoMensaje = 'error';
    }
}

$productoEditar = [
    'id_producto' => '',
    'nombre_producto' => '',
    'descripcion_producto' => '',
    'material' => '',
    'precio' => '',
    'stock' => '',
    'categoria_id' => '',
    'proveedor_id' => ''
];

if (isset($_GET['editar']) && $conexion) {
    $id = (int) $_GET['editar'];
    $stmt = $conexion->prepare('SELECT * FROM producto WHERE id_producto = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $productoEditar = $stmt->fetch() ?: $productoEditar;
}

$categorias = [];
$proveedores = [];
$productos = [];
if ($conexion) {
    $categorias = $conexion->query('SELECT * FROM categoria ORDER BY nombre_categoria ASC')->fetchAll();
    $proveedores = $conexion->query('SELECT * FROM proveedor ORDER BY nombre_proveedor ASC')->fetchAll();
    $productos = $conexion->query('SELECT p.*, c.nombre_categoria, pr.nombre_proveedor FROM producto p LEFT JOIN categoria c ON c.id_categoria = p.categoria_id LEFT JOIN proveedor pr ON pr.id_proveedor = p.proveedor_id ORDER BY p.id_producto DESC')->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Joyería</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="dash-body">
    <div class="dash-wrap">

        <nav class="dash-nav">
            <div class="dash-brand">Joyería</div>
            <div class="dash-user">
                <span class="dash-user-name"><?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
                <span class="dash-user-role"><?php echo htmlspecialchars($_SESSION['usuario']['rol']); ?></span>
                <a class="dash-logout" href="Login.php?logout=1">Salir</a>
            </div>
        </nav>

        <?php if ($mensaje !== ''): ?>
            <div class="alerta <?php echo htmlspecialchars($tipoMensaje); ?>"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="dash-stats">
            <div class="stat-box">
                <span class="stat-num"><?php echo count($productos); ?></span>
                <span class="stat-label">Productos</span>
            </div>
            <div class="stat-box">
                <span class="stat-num"><?php echo count($categorias); ?></span>
                <span class="stat-label">Categorías</span>
            </div>
            <div class="stat-box">
                <span class="stat-num"><?php echo count($proveedores); ?></span>
                <span class="stat-label">Proveedores</span>
            </div>
        </div>

        <div class="dash-grid">
            <main class="dash-main">
                <div class="dash-section-head">
                    <h2>Inventario</h2>
                    <span class="dash-count"><?php echo count($productos); ?> productos</span>
                </div>

                <?php if (empty($productos)): ?>
                    <div class="dash-empty">
                        <p>No hay productos registrados aun.</p>
                        <p class="dash-empty-sub">Agrega tu primer producto desde el formulario.</p>
                    </div>
                <?php else: ?>
                    <div class="dash-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Material</th>
                                    <th class="col-right">Precio</th>
                                    <th class="col-right">Stock</th>
                                    <th>Categoria</th>
                                    <th>Proveedor</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                    <tr>
                                        <td class="cell-name"><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                        <td><?php echo htmlspecialchars($producto['material'] ?? '—'); ?></td>
                                        <td class="col-right">$<?php echo number_format((float) $producto['precio'], 2, ',', '.'); ?></td>
                                        <td class="col-right">
                                            <?php if ((int) $producto['stock'] === 0): ?>
                                                <span class="tag tag-empty">0</span>
                                            <?php elseif ((int) $producto['stock'] <= 5): ?>
                                                <span class="tag tag-low"><?php echo (int) $producto['stock']; ?></span>
                                            <?php else: ?>
                                                <span class="tag tag-ok"><?php echo (int) $producto['stock']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($producto['nombre_categoria'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($producto['nombre_proveedor'] ?? '—'); ?></td>
                                        <td class="cell-actions">
                                            <a href="dashboard.php?editar=<?php echo (int) $producto['id_producto']; ?>" class="act-edit">Editar</a>
                                            <a href="dashboard.php?eliminar=<?php echo (int) $producto['id_producto']; ?>" class="act-del" onclick="return confirm('Eliminar este producto?');">Borrar</a>
                                        </td>
                                    </tr>
                                    <?php if (!empty($producto['descripcion_producto'])): ?>
                                        <tr class="tr-desc">
                                            <td colspan="7" class="cell-desc"><strong>Descripción:</strong> <?php echo htmlspecialchars($producto['descripcion_producto']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>

            <aside class="dash-side">
                <div class="dash-panel">
                    <h3><?php echo !empty($productoEditar['id_producto']) ? 'Editar producto' : 'Nuevo producto'; ?></h3>

                    <form method="POST" action="dashboard.php">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) ($productoEditar['id_producto'] ?? '')); ?>">

                        <div class="field">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars((string) ($productoEditar['nombre_producto'] ?? '')); ?>" placeholder="Anillo, pulsera..." required>
                        </div>

                        <div class="field">
                            <label for="descripcion">Descripcion</label>
                            <textarea id="descripcion" name="descripcion" rows="3" placeholder="Detalles del producto..."><?php echo htmlspecialchars((string) ($productoEditar['descripcion_producto'] ?? '')); ?></textarea>
                        </div>

                        <div class="field">
                            <label for="material">Material</label>
                            <input type="text" id="material" name="material" value="<?php echo htmlspecialchars((string) ($productoEditar['material'] ?? '')); ?>" placeholder="Oro, plata...">
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label for="precio">Precio</label>
                                <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars((string) ($productoEditar['precio'] ?? '0')); ?>" required>
                            </div>
                            <div class="field">
                                <label for="stock">Stock</label>
                                <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars((string) ($productoEditar['stock'] ?? '0')); ?>" required>
                            </div>
                        </div>

                        <div class="field">
                            <label for="categoria_id">Categoria</label>
                            <select id="categoria_id" name="categoria_id">
                                <option value="">Sin categoria</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo (int) $categoria['id_categoria']; ?>" <?php echo (isset($productoEditar['categoria_id']) && (int) $productoEditar['categoria_id'] === (int) $categoria['id_categoria']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="proveedor_id">Proveedor</label>
                            <select id="proveedor_id" name="proveedor_id">
                                <option value="">Sin proveedor</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <option value="<?php echo (int) $proveedor['id_proveedor']; ?>" <?php echo (isset($productoEditar['proveedor_id']) && (int) $productoEditar['proveedor_id'] === (int) $proveedor['id_proveedor']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($proveedor['nombre_proveedor']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" name="guardar_producto" value="1">
                            <?php echo !empty($productoEditar['id_producto']) ? 'Actualizar' : 'Guardar producto'; ?>
                        </button>
                        <?php if (!empty($productoEditar['id_producto'])): ?>
                            <a href="dashboard.php" class="dash-cancel">Cancelar edicion</a>
                        <?php endif; ?>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
