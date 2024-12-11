<?php
function sanitizarEntrada($data) {
    // Elimina espacios en blanco al inicio y al final de la cadena
    $data = trim($data);
    // Elimina barras invertidas (\) de la cadena
    $data = stripslashes($data);
    // Convierte caracteres especiales en entidades HTML
    $data = htmlspecialchars($data);
    // Elimina caracteres comunes de inyección SQL
    $data = preg_replace('/[\'"\\\\;#%]/', '', $data); // Elimina comillas, barra invertida, punto y coma, y otros
    return $data; // Devuelve la cadena sanitizada
}

function validarDatos($nombre, $apellido, $correo, $apodo, $identificacion) {
    $errors = [];

    // Validación de nombre
    if (empty($nombre)) {
        $errors['nombre'] = "El nombre es obligatorio.";
    }

    // Validación de apellido
    if (empty($apellido)) {
        $errors['apellido'] = "El apellido es obligatorio.";
    }

    // Validación de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errors['correo'] = "El correo no es válido.";
    }

    // Validación de apodo (solo letras, números y guiones bajos, entre 1 y 50 caracteres)
    if (!preg_match('/^\w{1,50}$/', $apodo)) {
        $errors['apodo'] = "El apodo solo puede contener letras, números y guiones bajos, y debe tener entre 1 y 50 caracteres.";
    }

    // Validación de identificación (solo números)
    if (!preg_match('/^[0-9\-]+$/', $identificacion)) {
        $errors['identificacion'] = "La identificación debe contener solo números.";
    }

    return $errors; // Devuelve el array de errores si existen
}
?>
