<?php
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: New_password.html');
    exit;
}

$token = trim($_POST['token'] ?? '');
$email = trim($_POST['email'] ?? '');
$newPassword = trim($_POST['newPassword'] ?? '');
$confirmPassword = trim($_POST['confirmPassword'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $token === '') {
    echo "<script>alert('No se encontró un enlace válido para restablecer la contraseña. Vuelve a intentarlo.'); window.location='Forgot-password.html';</script>";
    exit;
}

// Verificar token en la tabla password_resets
$check = mysqli_prepare($conexion, "SELECT expires_at FROM password_resets WHERE correo = ? AND token = ? LIMIT 1");
if (!$check) {
    die('Error en la preparación de la consulta: ' . mysqli_error($conexion));
}
mysqli_stmt_bind_param($check, 'ss', $email, $token);
if (!mysqli_stmt_execute($check)) {
    die('Error en la ejecución de la consulta: ' . mysqli_stmt_error($check));
}
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) === 0) {
    mysqli_stmt_close($check);
    mysqli_close($conexion);
    echo "<script>alert('Enlace inválido o expirado. Solicita un nuevo restablecimiento.'); window.location='Forgot-password.html';</script>";
    exit;
}
mysqli_stmt_bind_result($check, $expiresAt);
mysqli_stmt_fetch($check);
mysqli_stmt_close($check);

if (strtotime($expiresAt) < time()) {
    echo "<script>alert('El enlace ha expirado. Solicita un nuevo restablecimiento.'); window.location='Forgot-password.html';</script>";
    exit;
}

if ($newPassword === '' || $confirmPassword === '') {
    echo "<script>alert('Debe ingresar y confirmar la nueva contraseña.'); window.location='New_password.html';</script>";
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo "<script>alert('Las contraseñas no coinciden.'); window.location='New_password.html';</script>";
    exit;
}

if (strlen($newPassword) < 6) {
    echo "<script>alert('La contraseña debe tener al menos 6 caracteres.'); window.location='New_password.html';</script>";
    exit;
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conexion, "UPDATE admin SET `contraseña` = ? WHERE correo = ?");
if (!$stmt) {
    die('Error en la preparación de la consulta: ' . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, 'ss', $hashedPassword, $email);
if (!mysqli_stmt_execute($stmt)) {
    die('Error en la ejecución de la consulta: ' . mysqli_stmt_error($stmt));
}

if (mysqli_stmt_affected_rows($stmt) === 0) {
    echo "<script>alert('No se actualizó la contraseña. Verifica que el correo exista.'); window.location='Forgot-password.html';</script>";
} else {
    // Borrar token usado
    $del = mysqli_prepare($conexion, "DELETE FROM password_resets WHERE correo = ? AND token = ?");
    if ($del) {
        mysqli_stmt_bind_param($del, 'ss', $email, $token);
        mysqli_stmt_execute($del);
        mysqli_stmt_close($del);
    }
    echo "<script>alert('Contraseña actualizada correctamente.'); window.location='Login.html';</script>";
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>