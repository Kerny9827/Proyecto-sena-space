<?php
require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Login.html');
    exit;
}

$correo = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($correo === '' || $password === '') {
    echo "<script>alert('Debe ingresar correo y contraseña.'); window.location='Login.html';</script>";
    exit;
}

$sql = "SELECT `nombre`, `correo`, `contraseña` FROM `admin` WHERE `correo` = ?";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die('Error en la preparación de la consulta: ' . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, 's', $correo);
if (!mysqli_stmt_execute($stmt)) {
    die('Error en la ejecución de la consulta: ' . mysqli_stmt_error($stmt));
}

mysqli_stmt_bind_result($stmt, $nombre, $email, $storedPassword);

if (mysqli_stmt_fetch($stmt)) {
    $loginOk = false;
    $passwordText = (string) $storedPassword;

    if ($passwordText === $password) {
        $loginOk = true;
    } elseif (is_string($passwordText) && password_verify($password, $passwordText)) {
        $loginOk = true;
    }

    if ($loginOk) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $nombre ?: $email;
        $_SESSION['correo'] = $email;
        mysqli_stmt_close($stmt);
        mysqli_close($conexion);
        header('Location: index.html');
        exit;
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);

echo "<script>alert('Correo o contraseña incorrectos.'); window.location='Login.html';</script>";
