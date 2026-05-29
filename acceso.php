<?php
// Incluir conexión
include 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    mysqli_close($conexion);
    exit;
}

$identificador = trim($_POST['cedula'] ?? $_POST['id'] ?? $_POST['identificador'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');

if ($identificador === '' || $tipo === '') {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    mysqli_close($conexion);
    exit;
}

if (!preg_match('/^[0-9]+$/', $identificador)) {
    echo json_encode(['success' => false, 'message' => 'Identificador inválido']);
    mysqli_close($conexion);
    exit;
}

if (!in_array($tipo, ['ingreso', 'salida'], true)) {
    echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
    mysqli_close($conexion);
    exit;
}

$stmt_validate = mysqli_prepare($conexion, "SELECT cedula, nombre, tipo_usuario FROM admin WHERE cedula = ?");
if (!$stmt_validate) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta de validación']);
    mysqli_close($conexion);
    exit;
}

mysqli_stmt_bind_param($stmt_validate, "s", $identificador);
if (!mysqli_stmt_execute($stmt_validate)) {
    echo json_encode(['success' => false, 'message' => 'Error al ejecutar la validación']);
    mysqli_stmt_close($stmt_validate);
    mysqli_close($conexion);
    exit;
}

mysqli_stmt_store_result($stmt_validate);
if (mysqli_stmt_num_rows($stmt_validate) === 0) {
    echo json_encode(['success' => false, 'message' => 'Identificador no encontrado en la tabla admin']);
    mysqli_stmt_close($stmt_validate);
    mysqli_close($conexion);
    exit;
}

mysqli_stmt_bind_result($stmt_validate, $cedula_db, $nombre, $tipo_usuario);
mysqli_stmt_fetch($stmt_validate);
mysqli_stmt_close($stmt_validate);

$fecha_actual = date('Y-m-d H:i:s');

if ($tipo === 'ingreso') {
    $stmt = mysqli_prepare($conexion, "INSERT INTO acceso (cedula, rol, fecha_ingreso_complejo) VALUES (?, ?, ?)");
} else {
    $stmt = mysqli_prepare($conexion, "INSERT INTO acceso (cedula, rol, fecha_salida_complejo) VALUES (?, ?, ?)");
}

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar el registro de acceso']);
    mysqli_close($conexion);
    exit;
}

mysqli_stmt_bind_param($stmt, "sss", $cedula_db, $tipo_usuario, $fecha_actual);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true,
        'message' => ucfirst($tipo) . ' registrado correctamente para ' . $nombre,
        'data' => [
            'cedula' => $cedula_db,
            'nombre' => $nombre,
            'tipo_usuario' => $tipo_usuario
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar: ' . mysqli_error($conexion)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>