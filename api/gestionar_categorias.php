<?php
session_start();

// Verifica si el usuario ha iniciado sesión y es admin
if (!isset($_SESSION['usuario']) || !isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header('Location: login.php');
    exit;
}

require_once('../Conexion/Conexion.php');
require_once 'funciones.php';
require_once '../Clases/Categoria.php';

$conexion = (new Conexion())->obtenerConexion();
$categoriaModel = new Categoria($conexion);

// Handle AJAX delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id_categoria = intval($_POST['id_categoria']);
    if ($categoriaModel->eliminarCategoria($id_categoria)) {
        echo json_encode(['success' => true, 'message' => 'Categoría eliminada con éxito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la categoría.']);
    }
    exit;
}

// Handle form submission for adding or updating categories
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_categoria = sanitizarEntrada($_POST['nombre_categoria']);
    $descripcion_categoria = isset($_POST['descripcion']) ? sanitizarEntrada($_POST['descripcion']) : ''; // Default to empty string if not set

    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'actualizar' && isset($_POST['id_categoria'])) {
                $id_categoria = intval($_POST['id_categoria']);
                if ($categoriaModel->actualizarCategoria($id_categoria, $nombre_categoria, $descripcion_categoria)) { // Update this line
                    $mensaje = "Categoría actualizada con éxito.";
                } else {
                    $error = "Error al actualizar la categoría.";
                }
            } elseif ($_POST['action'] === 'guardar') {
                if ($categoriaModel->agregarCategoria($nombre_categoria, $descripcion_categoria)) { // Update this line if adding category also needs description
                    $mensaje = "Categoría guardada con éxito.";
                } else {
                    $error = "Error al guardar la categoría.";
                }
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}



// Fetch categories from the database
$categorias = [];
try {
    $categorias = $categoriaModel->obtenerCategorias();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías</title>
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
        <h1 class="center-align">Gestión de Categorías</h1>
        
        <?php if (isset($mensaje)): ?>
            <div class="mensaje exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <form id="categoriaForm" class="col s12 m8 offset-m2" action="gestionar_categorias.php" method="POST">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Formulario de Categoría</span>
                        <input type="hidden" name="id_categoria" id="id_categoria">
                        <div class="input-field">
                            <input type="text" name="nombre_categoria" id="nombre_categoria" required>
                            <label for="nombre_categoria">Nombre de la Categoría</label>
                        </div>
                        <div class="input-field">
                            <textarea name="descripcion" id="descripcion" class="materialize-textarea"></textarea>
                            <label for="descripcion">Descripción de la Categoría</label>
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

        <h2 class="center-align">Lista de Categorías</h2>
        <div id="categorias-container" class="row">
            <?php foreach ($categorias as $categoria): ?>
                <div class="col s12 m6 l4 categoria-card" data-id="<?php echo $categoria['id_categoria']; ?>">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title"><strong><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></strong></span>
                            <span class="card-title"><?php echo htmlspecialchars($categoria['descripcion']); ?></span>
                        </div>
                        <div class="card-action">
                            <a href="#" class="btn edit-button" 
                            data-id="<?php echo htmlspecialchars($categoria['id_categoria']); ?>" 
                            data-nombre="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>"
                            data-descripcion="<?php echo htmlspecialchars($categoria['descripcion']); ?>"> <!-- Added description -->
                                Editar
                            </a>
                            <a href="#" class="btn red delete-button">
                                Eliminar
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
        // Initialize Materialize select elements
        var elems = document.querySelectorAll('select');
        M.FormSelect.init(elems);

        // Edit category functionality
        var editButtons = document.querySelectorAll('.edit-button');
        editButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Populate the form fields with the category data
                document.getElementById('id_categoria').value = this.getAttribute('data-id');
                document.getElementById('nombre_categoria').value = this.getAttribute('data-nombre');
                document.getElementById('descripcion').value = this.getAttribute('data-descripcion'); // Include description
                
                // Update text fields
                M.updateTextFields();
                
                // Scroll to the top of the page
                document.querySelector('html, body').scrollTop = 0;
            });
        });

        // Delete category functionality
        document.querySelectorAll('.delete-button').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                var categoryCard = this.closest('.categoria-card');
                var categoryId = categoryCard.dataset.id;

                if (confirm('¿Estás seguro de que deseas eliminar esta categoría?')) {
                    fetch('gestionar_categorias.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete&id_categoria=${categoryId}` // Using template literals
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            categoryCard.remove(); // Remove category card from DOM
                            mostrarMensaje(data.message, 'green'); // Show success message
                        } else {
                            mostrarMensaje(data.message, 'red'); // Show error message
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        mostrarMensaje('Error al eliminar la categoría.', 'red'); // Show error message
                    });
                }
            });
        });
    });

    // Clear the form fields
    function limpiarFormulario() {
        document.getElementById('id_categoria').value = '';
        document.getElementById('nombre_categoria').value = '';
        document.getElementById('descripcion').value = ''; // Clear description field
        M.updateTextFields(); // Update text fields for Materialize
    }

    // Display messages to the user
    function mostrarMensaje(mensaje, color) {
        const mensajeDiv = document.getElementById('mensaje');
        mensajeDiv.textContent = mensaje;
        mensajeDiv.style.backgroundColor = color;
        mensajeDiv.style.display = 'block'; // Show the message
        setTimeout(() => {
            mensajeDiv.style.display = 'none'; // Hide after 3 seconds
        }, 3000);
    }
</script>

</body>
</html>
