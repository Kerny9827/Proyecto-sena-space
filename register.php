<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Register.html');
    exit;
}

$nombre = trim($_POST['registerName'] ?? '');
$correo = trim($_POST['registerEmail'] ?? '');
$password = $_POST['registerPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$tipoDocumento = trim($_POST['tipo_documento'] ?? '');
$cedula = trim($_POST['cedula'] ?? '');
$userType = trim($_POST['userType'] ?? '');

if ($nombre === '' || $correo === '' || $password === '' || $confirmPassword === '' || $tipoDocumento === '' || $cedula === '' || $userType === '') {
    echo "<script>alert('Por favor completa todos los campos.'); window.location='Register.html';</script>";
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Ingresa un correo válido.'); window.location='Register.html';</script>";
    exit;
}

if ($password !== $confirmPassword) {
    echo "<script>alert('Las contraseñas no coinciden.'); window.location='Register.html';</script>";
    exit;
}   

if (!is_numeric($cedula)) {
    echo "<script>alert('La cédula debe contener solo números.'); window.location='Register.html';</script>";
    exit;
}

$cedulaInt = (int) $cedula;

$sql = 'SELECT 1 FROM `admin` WHERE `correo` = ? LIMIT 1';
$stmt = mysqli_prepare($conexion, $sql);
if (!$stmt) {
    die('Error en la consulta de verificación: ' . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, 's', $correo);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    echo "<script>alert('Este correo ya está registrado.'); window.location='Register.html';</script>";
    exit;
}

mysqli_stmt_close($stmt);

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$sql = 'INSERT INTO `admin` (`cedula`, `correo`, `nombre`, `tipo_usuario`, `tipo_documento`, `contraseña`) VALUES (?, ?, ?, ?, ?, ?)';
$stmt = mysqli_prepare($conexion, $sql);
if (!$stmt) {
    die('Error en la preparación del registro: ' . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, 'isssss', $cedulaInt, $correo, $nombre, $userType, $tipoDocumento, $hashedPassword);

if (!mysqli_stmt_execute($stmt)) {
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    echo "<script>alert('Error al registrar: $error'); window.location='Register.html';</script>";
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);

echo "<script>alert('Registro exitoso. Ahora puedes iniciar sesión.'); window.location='Login.html';</script>";