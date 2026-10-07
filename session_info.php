<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']) || !isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
    exit;
}

$usuario = $_SESSION['usuario'];
$correo = $_SESSION['correo'];

echo json_encode([
    'success' => true,
    'usuario' => $usuario,
    'correo' => $correo
]);
