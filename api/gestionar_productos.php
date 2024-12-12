<?php
session_start();

// Verifica si el usuario ha iniciado sesión y es admin
if (!isset($_SESSION['usuario']) || !isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header('Location: login.php');
    exit;
}

require_once('../Conexion/Conexion.php');
require_once 'funciones.php';
require_once '../Clases/Productos.php';
require_once '../Clases/Categoria.php';

$conexion = (new Conexion())->obtenerConexion();
$productosModel = new Productos($conexion);
$categoriaModel = new Categoria($conexion);

// Handle AJAX delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id_producto = intval($_POST['id_producto']);
    if ($productosModel->eliminarProducto($id_producto)) {
        echo json_encode(['success' => true, 'message' => 'Producto eliminado con éxito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto.']);
    }
    exit;
}

// Fetch categories from the database
$categorias = [];
try {
    
    $categorias = $categoriaModel->obtenerCategorias();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_producto = sanitizarEntrada($_POST['nombre_producto']);
    $descripcion = sanitizarEntrada($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $id_categoria = intval($_POST['id_categoria']);
    $imagen_producto = sanitizarEntrada($_POST['imagen_producto']);

    try {
        
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'actualizar' && isset($_POST['id_producto'])) {
                $id_producto = intval($_POST['id_producto']);
                if ($productosModel->actualizarProducto($id_producto, $nombre_producto, $descripcion, $precio, $stock, $id_categoria, $imagen_producto)) {
                    $mensaje = "Producto actualizado con éxito.";
                } else {
                    $error = "Error al actualizar el producto.";
                }
            } elseif ($_POST['action'] === 'guardar') {
                if ($productosModel->agregarProducto($nombre_producto, $descripcion, $precio, $stock, $id_categoria, $imagen_producto)) {
                    $mensaje = "Producto guardado con éxito.";
                } else {
                    $error = "Error al guardar el producto.";
                }
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch products from the database
$productos = [];
try {
    
    $productos = $productosModel->obtenerTodosLosProductos();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $id_producto = intval($_GET['delete_id']);
    if ($productosModel->eliminarProducto($id_producto)) {
        $mensaje = "Producto eliminado con éxito.";
    } else {
        $error = "Error al eliminar el producto.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        nav {
            background-color: #7C3AED;
        }
        h1, h2 {
            color: #7C3AED;
            font-weight: 300;
        }
        .btn {
            background-color: #7C3AED;
            margin: 5px;
        }
        .btn:hover {
            background-color: #6D28D9;
        }
        .btn-floating {
            background-color: #7C3AED;
        }
        .btn-floating:hover {
            background-color: #6D28D9;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card .card-content {
            padding: 24px;
        }
        .card .card-image img {
            max-height: 200px;
            object-fit: cover;
        }
        .input-field input:focus, .input-field textarea:focus {
            border-bottom: 1px solid #7C3AED !important;
            box-shadow: 0 1px 0 0 #7C3AED !important;
        }
        .input-field input:focus + label, .input-field textarea:focus + label {
            color: #7C3AED !important;
        }
        .select-wrapper input.select-dropdown:focus {
            border-bottom: 1px solid #7C3AED;
        }
        .dropdown-content li>a, .dropdown-content li>span {
            color: #7C3AED;
        }
        .mensaje {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
        }
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        #mensaje {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 9999;
            display: none;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-wrapper">
            <div class="container">
                <a href="perfil.php" class="brand-logo">Mi Tienda</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <li><a href="perfil.php"><i class="material-icons left">arrow_back</i>Volver al Perfil</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="center-align">Gestión de Productos</h1>
        
        <div id="mensaje" class="card-panel"></div>

        <?php if (isset($mensaje)): ?>
            <div class="mensaje exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <form id="productoForm" class="col s12 m8 offset-m2" action="gestionar_productos.php" method="POST">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Formulario de Producto</span>
                        <input type="hidden" name="id_producto" id="id_producto">
                        <div class="input-field">
                            <input type="text" name="nombre_producto" id="nombre_producto" required>
                            <label for="nombre_producto">Nombre del Producto</label>
                        </div>
                        <div class="input-field">
                            <textarea name="descripcion" id="descripcion" class="materialize-textarea"></textarea>
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
                            <label>Categoría</label>
                        </div>
                        <div class="input-field">
                            <input type="text" name="imagen_producto" id="imagen_producto">
                            <label for="imagen_producto">URL de la Imagen</label>
                        </div>
                        <div class="card-action">
                            <button type="submit" name="action" value="guardar" class="btn waves-effect waves-light">
                                Guardar
                                <i class="material-icons right">save</i>
                            </button>
                            <button type="submit" name="action" value="actualizar" class="btn waves-effect waves-light">
                                Actualizar
                                <i class="material-icons right">edit</i>
                            </button>
                            <button type="button" class="btn waves-effect waves-light" onclick="limpiarFormulario()">
                                Limpiar
                                <i class="material-icons right">clear</i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <h2 class="center-align">Lista de Productos</h2>
        <div id="productos-container" class="row">
            <?php foreach ($productos as $producto): ?>
                <div class="col s12 m6 l4 producto-card" data-id="<?php echo $producto['id_producto']; ?>">
                    <div class="card">
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($producto['imagen_producto']); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                            <a class="btn-floating halfway-fab waves-effect waves-light red delete-button">
                                <i class="material-icons">delete</i>
                            </a>
                        </div>
                        <div class="card-content">
                            <span class="card-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                            <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                            <p><strong>Precio:</strong> $<?php echo htmlspecialchars($producto['precio']); ?></p>
                            <p><strong>Stock:</strong> <?php echo htmlspecialchars($producto['stock']); ?></p>
                        </div>
                        <div class="card-action">
                            <a href="#" class="btn edit-button" 
                               data-id="<?php echo htmlspecialchars($producto['id_producto']); ?>"
                               data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" 
                               data-descripcion="<?php echo htmlspecialchars($producto['descripcion']); ?>" 
                               data-precio="<?php echo htmlspecialchars($producto['precio']); ?>" 
                               data-stock="<?php echo htmlspecialchars($producto['stock']); ?>" 
                               data-id_categoria="<?php echo htmlspecialchars($producto['id_categoria']); ?>" 
                               data-imagen="<?php echo htmlspecialchars($producto['imagen_producto']); ?>">
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems);
            
            var editButtons = document.querySelectorAll('.edit-button');
            editButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    document.getElementById('id_producto').value = this.getAttribute('data-id');
                    document.getElementById('nombre_producto').value = this.getAttribute('data-nombre');
                    document.getElementById('descripcion').value = this.getAttribute('data-descripcion');
                    document.getElementById('precio').value = this.getAttribute('data-precio');
                    document.getElementById('stock').value = this.getAttribute('data-stock');
                    document.getElementById('imagen_producto').value = this.getAttribute('data-imagen');
                    document.getElementById('id_categoria').value = this.getAttribute('data-id_categoria');
                    M.FormSelect.init(document.querySelectorAll('select'));
                    M.updateTextFields();
                    
                    document.querySelector('html, body').scrollTop = 0;
                });
            });

            // New delete functionality
            document.querySelectorAll('.delete-button').forEach(function(button) {
                button.addEventListener('click', function() {
                    var productCard = this.closest('.producto-card');
                    var productId = productCard.dataset.id;
                    
                    if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                        fetch('gestionar_productos.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=delete&id_producto=' + productId
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                productCard.remove();
                                mostrarMensaje(data.message, 'green');
                            } else {
                                mostrarMensaje(data.message, 'red');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            mostrarMensaje('Error al eliminar el producto.', 'red');
                        });
                    }
                });
            });
        });

        function limpiarFormulario() {
            document.getElementById('productoForm').reset();
            document.getElementById('id_producto').value = '';
            M.FormSelect.init(document.querySelectorAll('select'));
            M.updateTextFields();
        }

        function mostrarMensaje(mensaje, color) {
            var mensajeElement = document.getElementById('mensaje');
            mensajeElement.textContent = mensaje;
            mensajeElement.className = 'card-panel ' + color + ' white-text';
            mensajeElement.style.display = 'block';
            setTimeout(function() {
                mensajeElement.style.display = 'none';
            }, 3000);
        }

        function confirmarEliminar(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                window.location.href = 'gestionar_productos.php?delete_id=' + id;
            }
        }
    </script>
</body>
</html>

