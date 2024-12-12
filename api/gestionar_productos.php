<?php
session_start();

// Verifica si el usuario ha iniciado sesión y es admin
if (!isset($_SESSION['usuario']) || !isset($_SESSION['isAdmin'])) {
    header('Location: login.php');
    exit;
}

require_once('../Conexion/Conexion.php'); // Include database connection
require_once 'funciones.php'; // Include any necessary functions
require_once '../Clases/Productos.php'; // Include the Productos class
require_once '../Clases/Categoria.php'; // Include the Categoria class

// Fetch categories from the database
$categorias = [];
try {
    $conexion = (new Conexion())->obtenerConexion();
    $categoriaModel = new Categoria($conexion);
    $categorias = $categoriaModel->obtenerCategorias(); // Fetch categories
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_producto = $_POST['nombre_producto'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_categoria = $_POST['id_categoria'] ?? null;
    $imagen_producto = $_POST['imagen_producto'] ?? null;

    try {
        $conexion = (new Conexion())->obtenerConexion();
        $productosModel = new Productos($conexion);

        // Check if the action is to update or save
        if (isset($_POST['action'])) { // Verificar si 'action' está definida
            if ($_POST['action'] === 'actualizar' && isset($_POST['id_producto'])) {
                // Update existing product
                $id_producto = $_POST['id_producto'];
                if ($productosModel->actualizarProducto($id_producto, $nombre_producto, $descripcion, $precio, $stock, $id_categoria, $imagen_producto)) {
                    header('Location: gestionar_productos.php');
                    exit;
                } else {
                    $error = "Error al actualizar el producto.";
                }
            } elseif ($_POST['action'] === 'guardar') {
                // Insert new product
                if ($productosModel->agregarProducto($nombre_producto, $descripcion, $precio, $stock, $id_categoria, $imagen_producto)) {
                    header('Location: gestionar_productos.php');
                    exit;
                } else {
                    $error = "Error al guardar el producto.";
                }
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}



// Fetch products from the database using the Productos class
$productos = [];
try {
    $conexion = (new Conexion())->obtenerConexion();
    $productosModel = new Productos($conexion);
    $productos = $productosModel->obtenerTodosLosProductos(); // Use the method from the Productos class
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $id_producto = $_GET['delete_id'];
    if ($productosModel->eliminarProducto($id_producto)) {
        header('Location: gestionar_productos.php');
        exit;
    } else {
        $error = "Error al eliminar el producto.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
        }
        nav {
            background-color: #7C3AED;
        }
        h1 {
            color: #7C3AED;
            text-align: center;
        }
        .error {
            color: red;
            text-align: center;
        }

        .btn {
        background-color: #7C3AED !important;
        }

        .btn:hover {
            background-color: #5c29a8 !important;
        }

        .volver-icono {
        display: inline-block;
        margin: 10px;
        color: white; /* Cambiar a blanco para que contraste con el fondo */
        background-color: #7C3AED; /* Mismo color de fondo que el botón */
        font-size: 1rem;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .volver-icono:hover {
        background-color: #5c29a8; /* Color al pasar el mouse */
    }
    </style>
</head>
<body>
    <nav>
        <div class="container">
            <a href="perfil.php" class="brand-logo">Mi Tienda</a>
        </div>
    </nav>

    <div class="container">
    <a href="Perfil.php" class="btn volver-icono">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
        <h1>Gestión de Productos</h1>
        
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="gestionar_productos.php" method="POST">
            <input type="hidden" name="id_producto" id="id_producto"> <!-- Hidden field for product ID -->
            <div class="input-field">
                <input type="text" name="nombre_producto" id="nombre_producto" required>
                <label for="nombre_producto">Nombre del Producto</label>
            </div>
            <div class="input-field">
                <textarea name="descripcion" class="materialize-textarea" id="descripcion"></textarea>
                <label for="descripcion">Descripción</label>
            </div>
            <div class="input-field">
                <input type="number" name="precio" id="precio" step="0.01" required>
                <label for="precio">Precio</label>
            </div>
            <div class="input-field">
                <input type="number" name="stock" id="stock" required>
                <label for="stock">Stock</label>
            </div>
            <div class="input-field">
                <select name="id_categoria" id="id_categoria" required>
                    <option value="" disabled selected>Seleccione una categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>">
                            <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>ID Categoría</label>
            </div>
            <div class="input-field">
                <input type="text" name="imagen_producto" id="imagen_producto">
                <label for="imagen_producto">URL de la Imagen (opcional)</label>
            </div>
            <button type="submit" name="action" value="guardar" class="btn waves-effect waves-light">Guardar Producto</button>
            <button type="submit" name="action" value="actualizar" class="btn waves-effect waves-light">Actualizar Producto</button>
        </form>


        <h2 class="center-align">Lista de Productos</h2>
        <table class="highlight centered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Imagen</th>
                    <th>Acciones</th>  
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                        <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($producto['precio']); ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($producto['imagen_producto']); ?>" alt="Imagen de <?php echo htmlspecialchars($producto['nombre_producto']); ?>" style="width: 100px; height: auto;">
                        </td>
                        <td>
                            <a href="#" 
                               class="btn edit-button" 
                               data-id="<?php echo htmlspecialchars($producto['id_producto']); ?>"
                               data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" 
                               data-descripcion="<?php echo htmlspecialchars($producto['descripcion']); ?>" 
                               data-precio="<?php echo htmlspecialchars($producto['precio']); ?>" 
                               data-stock="<?php echo htmlspecialchars($producto['stock']); ?>" 
                               data-id_categoria="<?php echo htmlspecialchars($producto['id_categoria']); ?>" 
                               data-imagen="<?php echo htmlspecialchars($producto['imagen_producto']); ?>">
                               Editar
                            </a>
                            <a href="gestionar_productos.php?delete_id=<?php echo $producto['id_producto']; ?>" class="btn red" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems);
            
            // Add event listeners to the edit buttons
            var editButtons = document.querySelectorAll('.edit-button');
            editButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent default anchor behavior
                    
                    // Fill the form with the product data
                    document.getElementById('id_producto').value = this.getAttribute('data-id');
                    document.getElementById('nombre_producto').value = this.getAttribute('data-nombre');
                    document.getElementById('descripcion').value = this.getAttribute('data-descripcion');
                    document.getElementById('precio').value = this.getAttribute('data-precio');
                    document.getElementById('stock').value = this.getAttribute('data-stock');
                    document.getElementById('imagen_producto').value = this.getAttribute('data-imagen');
                    document.getElementById('id_categoria').value = this.getAttribute('data-id_categoria');
                    M.FormSelect.init(document.querySelectorAll('select')); // Reinitialize select after setting value
                });
            });
        });
    </script>
</body>
</html>
