<?php
session_start();

// Limpiar el carrito si la sesión existe
if (isset($_SESSION['carrito'])) {
    unset($_SESSION['carrito']);
}

// Responder con un estado 200 (OK)
http_response_code(200);
