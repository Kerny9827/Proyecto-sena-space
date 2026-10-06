<?php
session_start();
// Validación de sesión activa
if (empty($_SESSION['usuario_id'])) { 
    header('Location: Login.html'); 
    exit; 
}

// Control de acceso por roles (Validación RBAC)
if (!in_array($_SESSION['rol_sistema'] ?? 'Usuario', ['Administrador', 'Almacen', 'Usuario'])) { 
    http_response_code(403); 
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><link href="css/sb-admin-2.min.css" rel="stylesheet"></head><body class="bg-light"><div class="container py-5"><div class="alert alert-danger"><h4>Acceso restringido</h4><p>No tienes permisos para ver este módulo.</p><a href="usuario.php" class="btn btn-primary">Volver</a></div></div></body></html>'; 
    exit; 
}

$nombre = htmlspecialchars($_SESSION['usuario'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="img/icono3.png">
    <title>Sena space - Módulo de Almacén</title>
    
    <!-- Dependencias CSS (SB Admin 2 / Custom Styles) -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">

        <!-- Inyección de layout lateral (Sidebar) -->
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                
                <!-- Contenedor principal del módulo -->
                <div class="container-fluid mt-4">
                    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-boxes"></i> Módulo de Solicitudes y Almacén</h1>
                    
                    <!-- Tarjeta métrica: Contador de estados pendientes -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Solicitudes Pendientes</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="kPend">0</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grilla / Tabla de control de stock y solicitudes -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Listado de Solicitudes de Almacén</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Recurso</th>
                                            <th>Fechas</th>
                                            <th>Cantidad</th>
                                            <th>Observación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <!-- Target DOM para inyección dinámica vía innerHTML (JS) -->
                                    <tbody id="tblSolicitudesAlmacen">
                                        <tr><td colspan="6" class="text-center">Cargando información...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de infraestructura JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        const $ = id => document.getElementById(id);
        let DATA = {};

        // [BACKEND ENDPOINT]: Requiere controlador que gestione la acción 'almacen_dashboard' y retorne JSON estruturado
        function cargarAlmacen() {
            api('almacen_dashboard', {}, r => {
                if (!r.success) {
                    console.warn(r.message || 'Error en respuesta de API');
                    return;
                }
                DATA = r.data || {};
                renderAlmacenData();
            });
        }

        // Parseo y renderizado de DOM para la tabla principal y métricas
        function renderAlmacenData() {
            const solicitudes = DATA.solicitudes || [];
            const pendientes = solicitudes.filter(x => x.estado === 'Pendiente');
            
            if($('kPend'))$('kPend').textContent = pendientes.length;

            const tbody = $('tblSolicitudesAlmacen');
            if (!tbody) return;

            tbody.innerHTML = pendientes.map(x => `
                <tr>
                    <td><b>${x.usuario_nombre || 'N/A'}</b><br><small>${x.usuario_correo || ''}</small></td>
                    <td><b>${x.recurso_nombre || 'N/A'}</b></td>
                    <td>${String(x.fecha_inicio || '').slice(0,10)} → ${String(x.fecha_devolucion || '').slice(0,10)}</td>
                    <td>${x.cantidad || 1}</td>
                    <td>${x.observacion || '—'}</td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="aprobar(${x.id})">Aprobar</button>
                        <button class="btn btn-danger btn-sm" onclick="rechazar(${x.id})">Rechazar</button>
                    </td>
                </tr>`).join('') || '<tr><td colspan="6" class="text-center">No hay solicitudes pendientes en almacén.</td></tr>';
        }

        // [BACKEND ENDPOINT]: Dispara la transacción SQL de aprobación y decremento de stock
        function aprobar(id) {
            const n = prompt('Observación de aprobación:', 'Préstamo aprobado.');
            if (n === null) return;
            
            api('aprobar_solicitud', { id, observacion: n }, r => { 
                alert(r.message || 'Transacción completada'); 
                cargarAlmacen(); 
            });
        }

        // [BACKEND ENDPOINT]: Dispara la actualización de estado a rechazado en base de datos
        function rechazar(id) {
            const n = prompt('Motivo del rechazo:', 'Solicitud rechazada.');
            if (n === null) return;
            
            api('rechazar_solicitud', { id, observacion: n }, r => { 
                alert(r.message || 'Transacción completada'); 
                cargarAlmacen(); 
            });
        }

        // Bootstrap inicial del ciclo de vida del módulo
        cargarAlmacen();
    </script>
</body>
</html>