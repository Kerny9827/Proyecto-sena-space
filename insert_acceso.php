<?php
// Incluir conexión
include 'conexion.php';

// Verificar datos POST
if (isset($_POST['cedula']) && isset($_POST['tipo'])) {
    $cedula = intval($_POST['cedula']);
    $tipo = $_POST['tipo']; // 'ingreso' o 'salida'

    // Primero validar que la cédula existe en la tabla admin y obtener datos
    $stmt_validate = mysqli_prepare($conexion, "SELECT cedula, nombre, tipo_usuario FROM admin WHERE cedula = ?");
    mysqli_stmt_bind_param($stmt_validate, "i", $cedula);
    mysqli_stmt_execute($stmt_validate);
    mysqli_stmt_store_result($stmt_validate);

    if (mysqli_stmt_num_rows($stmt_validate) == 0) {
        echo json_encode(['success' => false, 'message' => 'Cédula no encontrada en la tabla admin']);
        mysqli_stmt_close($stmt_validate);
        mysqli_close($conexion);
        exit;
    }

    // Obtener los datos
    mysqli_stmt_bind_result($stmt_validate, $cedula_db, $nombre, $tipo_usuario);
    mysqli_stmt_fetch($stmt_validate);
    mysqli_stmt_close($stmt_validate);

    // Si validación pasa, proceder con el insert
    // Obtener fecha actual
    $fecha_actual = date('Y-m-d');

    if ($tipo == 'ingreso') {
        // Insertar ingreso al complejo
        $stmt = mysqli_prepare($conexion, "INSERT INTO acceso (cedula, rol, fecha_ingreso_complejo) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $cedula, $tipo_usuario, $fecha_actual);
    } elseif ($tipo == 'salida') {
        // Insertar salida del complejo
        $stmt = mysqli_prepare($conexion, "INSERT INTO acceso (cedula, rol, fecha_salida_complejo) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $cedula, $tipo_usuario, $fecha_actual);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
        mysqli_close($conexion);
        exit;
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true, 
            'message' => ucfirst($tipo) . ' registrado correctamente para ' . $nombre,
            'data' => [
                'cedula' => $cedula,
                'nombre' => $nombre,
                'tipo_usuario' => $tipo_usuario
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar: ' . mysqli_error($conexion)]);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}

// Cerrar conexión
mysqli_close($conexion);
?>