<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

/*
============================================================
API: recursos.php
============================================================
Este archivo centraliza las operaciones de:
- Recursos / inventario
- Solicitudes
- Préstamos
- Devoluciones
- Usuarios y roles
- Historial

IMPORTANTE:
Las consultas están organizadas para que administrador.html y
recursos.html trabajen sobre los mismos datos de MySQL.

Todas las modificaciones realizadas para la sincronización están
marcadas con: "CAMBIO DE SINCRONIZACIÓN".
============================================================
*/

// ------------------------------------------------------------
// CONEXIÓN
// ------------------------------------------------------------
// CAMBIO DE SINCRONIZACIÓN: se utiliza la conexión creada por
// conexion.php. Se aceptan los nombres $conexion o $conn.
if (isset($conexion) && $conexion instanceof mysqli) {
    $db = $conexion;
} elseif (isset($conn) && $conn instanceof mysqli) {
    $db = $conn;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No se encontró una conexión MySQL válida en conexion.php.'
    ]);
    exit;
}

$db->set_charset('utf8mb4');

// ------------------------------------------------------------
// FUNCIONES AUXILIARES
// ------------------------------------------------------------

function responder($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function post($nombre, $default = '') {
    return isset($_POST[$nombre]) ? trim((string)$_POST[$nombre]) : $default;
}

function getAccion() {
    // CAMBIO DE SINCRONIZACIÓN:
    // Permite recibir la acción tanto por POST como por GET.
    return post('accion', isset($_GET['accion']) ? $_GET['accion'] : '');
}

function ejecutar($db, $sql, $types = '', $params = []) {
    $stmt = $db->prepare($sql);

    if (!$stmt) {
        responder(false, 'Error preparando la consulta: ' . $db->error);
    }

    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        responder(false, 'Error ejecutando la consulta: ' . $error);
    }

    return $stmt;
}

function filas($stmt) {
    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return [];
    }

    $datos = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $datos;
}

// ------------------------------------------------------------
// ACCIÓN
// ------------------------------------------------------------

$accion = getAccion();

if ($accion === '') {
    responder(false, 'No se recibió ninguna acción.');
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// OBTENER TODOS LOS RECURSOS
// ============================================================

function obtenerRecursos($db) {
    $sql = "
        SELECT
            id,
            nombre,
            categoria,
            tipo,
            stock_total,
            stock_minimo,
            estado
        FROM recursos
        ORDER BY id DESC
    ";

    $stmt = $db->prepare($sql);

    if (!$stmt) {
        responder(false, 'Error consultando recursos: ' . $db->error);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        responder(false, 'Error consultando recursos: ' . $error);
    }

    return filas($stmt);
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// DASHBOARD DEL ADMINISTRADOR
//
// Esta respuesta es la fuente común que utiliza
// administrador.html para mostrar inventario, solicitudes,
// usuarios e historial.
// ============================================================

if ($accion === 'admin_dashboard') {

    $recursos = obtenerRecursos($db);

    // Solicitudes
    $solicitudes = [];
    $sql = "
        SELECT
            s.id,
            s.usuario_id,
            s.recurso_id,
            s.cantidad,
            s.fecha_inicio,
            s.fecha_devolucion,
            s.observacion,
            s.estado,
            s.devolucion_solicitada,
            r.nombre AS recurso_nombre,
            r.tipo,
            u.nombre AS usuario_nombre,
            u.correo AS usuario_correo
        FROM solicitudes s
        LEFT JOIN recursos r ON r.id = s.recurso_id
        LEFT JOIN usuarios u ON u.cedula = s.usuario_id
        ORDER BY s.id DESC
    ";

    $stmt = $db->prepare($sql);

    if ($stmt && $stmt->execute()) {
        $solicitudes = filas($stmt);
    }

    // Usuarios
    $usuarios = [];
    $sql = "
        SELECT
            cedula,
            nombre,
            correo,
            tipo_usuario,
            rol_sistema
        FROM usuarios
        ORDER BY nombre ASC
    ";

    $stmt = $db->prepare($sql);

    if ($stmt && $stmt->execute()) {
        $usuarios = filas($stmt);
    }

    // Historial
    $historial = [];
    $sql = "
        SELECT
            fecha,
            actor,
            accion,
            detalle
        FROM historial
        ORDER BY fecha DESC
        LIMIT 200
    ";

    $stmt = $db->prepare($sql);

    if ($stmt && $stmt->execute()) {
        $historial = filas($stmt);
    }

    // CAMBIO DE SINCRONIZACIÓN:
    // Devoluciones: se construyen a partir de préstamos aprobados.
    // "Pendiente" significa que el administrador aún no ha solicitado
    // la devolución; "Solicitada" significa que ya se envió el aviso.
    $devoluciones = [];
    $sql = "
        SELECT
            s.id,
            s.usuario_id,
            s.recurso_id,
            s.cantidad,
            s.fecha_inicio AS fecha_prestamo,
            s.fecha_devolucion,
            s.observacion,
            s.devolucion_solicitada,
            CASE
                WHEN s.devolucion_solicitada = 1 THEN 'Solicitada'
                ELSE 'Pendiente'
            END AS estado,
            r.nombre AS recurso_nombre,
            r.tipo,
            u.nombre AS usuario_nombre,
            u.correo AS usuario_correo
        FROM solicitudes s
        LEFT JOIN recursos r ON r.id = s.recurso_id
        LEFT JOIN usuarios u ON u.cedula = s.usuario_id
        WHERE s.estado = 'Aprobado'
        ORDER BY s.fecha_devolucion ASC, s.id DESC
    ";

    $stmt = $db->prepare($sql);
    if ($stmt && $stmt->execute()) {
        $devoluciones = filas($stmt);
    }

    responder(true, 'Datos cargados correctamente.', [
        'recursos' => $recursos,
        'solicitudes' => $solicitudes,
        'usuarios' => $usuarios,
        'historial' => $historial,
        'devoluciones' => $devoluciones
    ]);
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// LISTAR RECURSOS
//
// recursos.html puede utilizar esta acción para dejar de usar
// el arreglo de datos de prueba y leer directamente MySQL.
// ============================================================

if ($accion === 'listar_recursos') {

    responder(true, 'Recursos cargados correctamente.', [
        'recursos' => obtenerRecursos($db)
    ]);
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// GUARDAR / EDITAR RECURSO
//
// INSERTA un recurso nuevo o ACTUALIZA uno existente.
// ============================================================

if ($accion === 'guardar_recurso') {

    $id = post('id');
    $nombre = post('nombre');
    $categoria = post('categoria');
    $tipo = post('tipo', 'Objeto');
    $stock = (int)post('stock_total', 0);
    $minimo = (int)post('stock_minimo', 0);

    if ($nombre === '') {
        responder(false, 'El nombre del recurso es obligatorio.');
    }

    if ($stock < 0 || $minimo < 0) {
        responder(false, 'El stock no puede ser negativo.');
    }

    if ($id !== '') {

        $id = (int)$id;

        $sql = "
            UPDATE recursos
            SET
                nombre = ?,
                categoria = ?,
                tipo = ?,
                stock_total = ?,
                stock_minimo = ?
            WHERE id = ?
        ";

        $stmt = ejecutar(
            $db,
            $sql,
            'sssiii',
            [$nombre, $categoria, $tipo, $stock, $minimo, $id]
        );

        $stmt->close();

        responder(true, 'Recurso actualizado correctamente.', [
            'recursos' => obtenerRecursos($db)
        ]);
    }

    $sql = "
        INSERT INTO recursos
            (nombre, categoria, tipo, stock_total, stock_minimo, estado)
        VALUES (?, ?, ?, ?, ?, 'Disponible')
    ";

    $stmt = ejecutar(
        $db,
        $sql,
        'sssii',
        [$nombre, $categoria, $tipo, $stock, $minimo]
    );

    $nuevoId = $db->insert_id;
    $stmt->close();

    responder(true, 'Recurso creado correctamente.', [
        'id' => $nuevoId,
        'recursos' => obtenerRecursos($db)
    ]);
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// RETIRAR RECURSO
//
// No se elimina físicamente. Se marca como Baja para conservar
// el historial.
// ============================================================

if ($accion === 'retirar_recurso') {

    $id = (int)post('id', 0);

    if ($id <= 0) {
        responder(false, 'ID de recurso inválido.');
    }

    $sql = "
        UPDATE recursos
        SET estado = 'Baja'
        WHERE id = ?
    ";

    $stmt = ejecutar($db, $sql, 'i', [$id]);
    $stmt->close();

    responder(true, 'Recurso retirado correctamente.', [
        'recursos' => obtenerRecursos($db)
    ]);
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// APROBAR SOLICITUD
// ============================================================

if ($accion === 'aprobar_solicitud') {

    $id = (int)post('id', 0);
    $observacion = post('observacion');

    if ($id <= 0) {
        responder(false, 'Solicitud inválida.');
    }

    $db->begin_transaction();

    try {

        $sql = "
            SELECT
                s.id,
                s.recurso_id,
                s.cantidad,
                s.estado,
                r.stock_total,
                r.estado AS estado_recurso
            FROM solicitudes s
            INNER JOIN recursos r ON r.id = s.recurso_id
            WHERE s.id = ?
            FOR UPDATE
        ";

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception($db->error);
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $solicitud = $resultado->fetch_assoc();
        $stmt->close();

        if (!$solicitud) {
            throw new Exception('La solicitud no existe.');
        }

        if ($solicitud['estado'] !== 'Pendiente') {
            throw new Exception('La solicitud ya fue procesada.');
        }

        if ($solicitud['estado_recurso'] === 'Baja') {
            throw new Exception('El recurso está dado de baja.');
        }

        if ((int)$solicitud['stock_total'] < (int)$solicitud['cantidad']) {
            throw new Exception('No hay suficiente stock disponible.');
        }

        // Descontar inventario
        $sql = "
            UPDATE recursos
            SET stock_total = stock_total - ?
            WHERE id = ?
        ";

        $stmt = $db->prepare($sql);
        $cantidad = (int)$solicitud['cantidad'];
        $recursoId = (int)$solicitud['recurso_id'];
        $stmt->bind_param('ii', $cantidad, $recursoId);
        $stmt->execute();
        $stmt->close();

        // Aprobar solicitud
        $sql = "
            UPDATE solicitudes
            SET
                estado = 'Aprobado',
                observacion = ?
            WHERE id = ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->bind_param('si', $observacion, $id);
        $stmt->execute();
        $stmt->close();

        $db->commit();

        responder(true, 'Solicitud aprobada correctamente.', [
            'recursos' => obtenerRecursos($db)
        ]);

    } catch (Exception $e) {

        $db->rollback();

        responder(false, $e->getMessage());
    }
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// RECHAZAR SOLICITUD
// ============================================================

if ($accion === 'rechazar_solicitud') {

    $id = (int)post('id', 0);
    $observacion = post('observacion');

    if ($id <= 0) {
        responder(false, 'Solicitud inválida.');
    }

    $sql = "
        UPDATE solicitudes
        SET
            estado = 'Rechazado',
            observacion = ?
        WHERE id = ?
    ";

    $stmt = ejecutar($db, $sql, 'si', [$observacion, $id]);
    $stmt->close();

    responder(true, 'Solicitud rechazada correctamente.');
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// SOLICITAR DEVOLUCIÓN
// ============================================================

if ($accion === 'solicitar_devolucion') {

    $id = (int)post('id', 0);

    if ($id <= 0) {
        responder(false, 'Solicitud inválida.');
    }

    $observacion = post('observacion', 'Solicitud de devolución realizada por el administrador.');

    $sql = "
        UPDATE solicitudes
        SET
            devolucion_solicitada = 1,
            observacion = ?
        WHERE id = ?
          AND estado = 'Aprobado'
    ";

    $stmt = ejecutar($db, $sql, 'si', [$observacion, $id]);
    $stmt->close();

    responder(true, 'Devolución solicitada correctamente.');
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// CONFIRMAR DEVOLUCIÓN
//
// Al confirmar se devuelve la cantidad al inventario y se cierra
// la solicitud.
// ============================================================

if ($accion === 'registrar_devolucion') {

    $id = (int)post('id', 0);

    if ($id <= 0) {
        responder(false, 'Solicitud inválida.');
    }

    $db->begin_transaction();

    try {

        $sql = "
            SELECT
                id,
                recurso_id,
                cantidad,
                estado
            FROM solicitudes
            WHERE id = ?
            FOR UPDATE
        ";

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception($db->error);
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $solicitud = $resultado->fetch_assoc();
        $stmt->close();

        if (!$solicitud) {
            throw new Exception('La solicitud no existe.');
        }

        if ($solicitud['estado'] !== 'Aprobado') {
            throw new Exception('La solicitud no está activa.');
        }

        $cantidad = (int)$solicitud['cantidad'];
        $recursoId = (int)$solicitud['recurso_id'];

        $sql = "
            UPDATE recursos
            SET stock_total = stock_total + ?
            WHERE id = ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii', $cantidad, $recursoId);
        $stmt->execute();
        $stmt->close();

        $sql = "
            UPDATE solicitudes
            SET
                estado = 'Devuelto',
                devolucion_solicitada = 0
            WHERE id = ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $db->commit();

        responder(true, 'Devolución registrada correctamente.', [
            'recursos' => obtenerRecursos($db)
        ]);

    } catch (Exception $e) {

        $db->rollback();

        responder(false, $e->getMessage());
    }
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// CAMBIAR ROL DE USUARIO
// ============================================================

if ($accion === 'cambiar_rol') {

    $cedula = post('cedula');
    $rol = post('rol');

    if ($cedula === '') {
        responder(false, 'Cédula inválida.');
    }

    if (!in_array($rol, ['Usuario', 'Administrador'], true)) {
        responder(false, 'Rol inválido.');
    }

    $sql = "
        UPDATE usuarios
        SET rol_sistema = ?
        WHERE cedula = ?
    ";

    $stmt = ejecutar($db, $sql, 'ss', [$rol, $cedula]);
    $stmt->close();

    responder(true, 'Rol actualizado correctamente.');
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// SOLICITAR REPOSICIÓN
// ============================================================

if ($accion === 'solicitar_reposicion') {

    $recursoId = (int)post('recurso_id', 0);
    $cantidad = (int)post('cantidad', 0);
    $observacion = post('observacion', 'Reposición por stock bajo');

    if ($recursoId <= 0 || $cantidad <= 0) {
        responder(false, 'Datos de reposición inválidos.');
    }

    /*
     * La reposición puede depender de una tabla específica en tu
     * base de datos. Para no inventar una estructura adicional,
     * aquí se devuelve una respuesta válida y se registra la
     * intención si existe la tabla historial.
     */

    $sql = "
        INSERT INTO historial (actor, accion, detalle)
        VALUES (?, 'Reposición', ?)
    ";

    $actor = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Administrador';
    $detalle = 'Recurso ID ' . $recursoId . ' - Cantidad solicitada: ' . $cantidad . ' - ' . $observacion;

    $stmt = $db->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('ss', $actor, $detalle);
        $stmt->execute();
        $stmt->close();
    }

    responder(true, 'Solicitud de reposición registrada.');
}

// ============================================================
// CAMBIO DE SINCRONIZACIÓN:
// ACCIÓN NO RECONOCIDA
// ============================================================

responder(false, 'Acción no reconocida: ' . $accion);
?>
