<?php
session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: Login.html');
    exit;
}

$nombre = htmlspecialchars($_SESSION['usuario'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$correo = htmlspecialchars($_SESSION['correo'] ?? '', ENT_QUOTES, 'UTF-8');
$rol = htmlspecialchars($_SESSION['rol_sistema'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
?>
