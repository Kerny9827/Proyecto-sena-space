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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LOG-IN · Recursos</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <link href="modulo_recursos.css" rel="stylesheet">
</head>
<body class="recursos-body">

    <header class="recursos-header">
        <div>
            <h1><i class="fas fa-box-open"></i> Sistema de Gestión de Recursos</h1>
            <small>Solicitud y seguimiento de objetos y servicios</small>
        </div>
        <div class="r-userbox">
            <div class="avatar"><?= strtoupper(substr($nombre, 0, 1)) ?></div>
            <div>
                <strong><?= $nombre ?></strong>
                <div class="small"><?= $correo ?></div>
            </div>
            <a href="logout.php" class="btn btn-sm btn-light ml-3">Salir</a>
        </div>
    </header>

    <div class="recursos-layout">
        <nav class="recursos-nav">
            <button class="active" onclick="showView('dashboard', this)">
                <i class="fas fa-home fa-fw"></i> Dashboard
            </button>
            <button onclick="showView('inventario', this)">
                <i class="fas fa-boxes fa-fw"></i> Inventario
            </button>
            <button onclick="showView('prestamos', this)">
                <i class="fas fa-hand-holding fa-fw"></i> Préstamos
            </button>
            <button onclick="showView('misSolicitudes', this)">
                <i class="fas fa-list fa-fw"></i> Mis solicitudes
            </button>
            <a href="home.html">
                <i class="fas fa-th-large fa-fw"></i> Módulo principal
            </a>
        </nav>

        <main class="recursos-main">
            <section id="dashboard" class="r-view active">
                <h2>Dashboard</h2>
                <p>Vista general de tus solicitudes y préstamos.</p>

                <div class="r-cards">
                    <div class="r-card">
                        <small>Solicitudes pendientes</small>
                        <strong id="kPend">0</strong>
                    </div>

                    <div class="r-card">
                        <small>Préstamos aprobados</small>
                        <strong id="kAprob">0</strong>
                    </div>

                    <div class="r-card">
                        <small>Activos</small>
                        <strong id="kActivos">0</strong>
                    </div>

                    <div class="r-card">
                        <small>Devoluciones solicitadas</small>
                        <strong id="kDev">0</strong>
                    </div>

                    <div class="r-card">
                        <small>Recursos disponibles</small>
                        <strong id="kRec">0</strong>
                    </div>
                </div>

                <div id="userAlert" class="r-alert">Cargando información…</div>

                <div class="r-panel">
                    <h5>Flujo del sistema</h5>
                    <p class="mb-0">Inventario → solicitud → verificación de disponibilidad → aprobación administrativa → préstamo → devolución/cierre.</p>
                </div>
            </section>

            <section id="inventario" class="r-view">
                <h2>Inventario disponible</h2>

                <div class="r-toolbar">
                    <input id="buscar" placeholder="Buscar recurso…" oninput="renderInventory()">
                    <select id="filterType" onchange="loadInventory()">
                        <option value="">Todos</option>
                        <option value="Objeto">Objetos</option>
                        <option value="Servicio">Servicios</option>
                    </select>
                </div>

                <div class="r-panel">
                    <table class="r-table">
                        <thead>
                            <tr>
                                <th>Recurso</th>
                                <th>Categoría</th>
                                <th>Tipo</th>
                                <th>Stock</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryTable"></tbody>
                    </table>
                </div>
            </section>

            <section id="prestamos" class="r-view">
                <h2>Solicitar objeto / servicio</h2>
                <p>La disponibilidad se verifica en el servidor antes de permitir el envío y vuelve a validarse al guardar.</p>

                <div class="r-panel">
                    <div class="r-form">
                        <div class="r-row">
                            <label>Tipo
                                <select id="type" onchange="loadResources()">
                                    <option value="Objeto">Objeto</option>
                                    <option value="Servicio">Servicio</option>
                                </select>
                            </label>

                            <label>Recurso
                                <select id="resource" onchange="checkAvailability()"></select>
                            </label>
                        </div>

                        <div class="r-row">
                            <label>Fecha inicio
                                <input type="date" id="start" onchange="checkAvailability()">
                            </label>

                            <label>Fecha devolución / cierre
                                <input type="date" id="end" onchange="checkAvailability()">
                            </label>
                        </div>

                        <div id="availability" class="r-alert">Selecciona fechas para consultar disponibilidad.</div>

                        <label>Cantidad
                            <input type="number" id="qty" min="1" value="1" disabled>
                        </label>

                        <label>Observaciones / motivo
                            <textarea id="reason" rows="3" placeholder="¿Para qué necesitas el objeto o servicio?" required></textarea>
                        </label>

                        <button id="send" class="r-btn r-primary" disabled onclick="createRequest()">
                            <i class="fas fa-paper-plane"></i> Enviar solicitud
                        </button>
                    </div>
                </div>
            </section>

            <section id="misSolicitudes" class="r-view">
                <h2>Mis solicitudes</h2>

                <div class="r-toolbar">
                    <button class="r-btn r-primary" onclick="loadMine()">
                        <i class="fas fa-sync"></i> Actualizar
                    </button>
                </div>

                <div class="r-panel">
                    <table class="r-table">
                        <thead>
                            <tr>
                                <th>Recurso</th>
                                <th>Fechas</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Observación</th>
                                <th>Devolución</th>
                            </tr>
                        </thead>
                        <tbody id="requestsTable"></tbody>
                    </table>
                </div>
            </section>

            <section id="activos" class="r-view">
                <h2>Mis préstamos activos</h2>

                <div class="r-panel">
                    <table class="r-table">
                        <thead>
                            <tr>
                                <th>Recurso</th>
                                <th>Inicio</th>
                                <th>Devolución</th>
                                <th>Estado</th>
                                <th>Aviso</th>
                            </tr>
                        </thead>
                        <tbody id="activeTable"></tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <script>
        const api = 'recursos_api.php';
        const $ = id => document.getElementById(id);
        const esc = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[m]));

        async function apiCall(accion, data = {}) {
            data.accion = accion;
            const r = await fetch(api, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            });
           
        }

        function showView(id, btn) {
            document.querySelectorAll('.r-view').forEach(x => x.classList.remove('active'));
            const el = $('#' + id);
            if (el) el.classList.add('active');
            document.querySelectorAll('.recursos-nav button').forEach(x => x.classList.remove('active'));
            if (btn) btn.classList.add('active');

            if (id === 'inventario') loadInventory();
            if (id === 'prestamos') loadResources();
            if (id === 'misSolicitudes' || id === 'activos' || id === 'dashboard') loadMine();
        }

        function renderInventory() {
            const q = (($('buscar') && $('buscar').value) || '').toLowerCase();
            const rows = (window.inventory || []).filter(x => (x.nombre + x.categoria + x.tipo).toLowerCase().includes(q));
            $('inventoryTable').innerHTML = rows.map(x => `
                <tr>
                    <td><b>${esc(x.nombre)}</b></td>
                    <td>${esc(x.categoria)}</td>
                    <td>${esc(x.tipo)}</td>
                    <td>${x.stock_total}</td>
                    <td><span class="r-status r-available">${esc(x.estado)}</span></td>
                </tr>`).join('') || '<tr><td colspan="5" class="r-empty">No hay recursos disponibles.</td></tr>';
        }

        async function loadResources() {
            try {
                const j = await apiCall('listar_recursos', { tipo: $('type').value });
                $('resource').innerHTML = j.data.map(x => `<option value="${x.id}">${esc(x.nombre)} — ${x.tipo === 'Servicio' ? 'Servicio' : 'Stock ' + x.stock_total}</option>`).join('');
                checkAvailability();
            } catch (e) {
                alert(e.message);
            }
        }

        async function checkAvailability() {
            const id = $('resource').value;
            const a = $('start').value;
            const b = $('end').value;
            $('qty').disabled = true;
            $('send').disabled = true;
            if (!a || !b) {
                $('availability').textContent = 'Selecciona fechas para consultar disponibilidad.';
                return;
            }
            if (a > b) {
                $('availability').innerHTML = '<b>⚠️ La fecha de devolución no puede ser anterior a la fecha de inicio.</b>';
                return;
            }
            try {
                const j = await apiCall('verificar_disponibilidad', { recurso_id: id, fecha_inicio: a, fecha_devolucion: b });
                const d = j.data;
                $('availability').innerHTML = `Stock total: <b>${d.stock_total}</b> · Reservado: <b>${d.ocupados}</b> · Disponible: <b>${d.disponibles}</b>`;
                if (d.disponibles > 0) {
                    $('qty').disabled = false;
                    $('qty').max = d.disponibles;
                    $('send').disabled = false;
                }
            } catch (e) {
                $('availability').textContent = e.message;
            }
        }

        async function createRequest() {
            try {
                const j = await apiCall('crear_solicitud', {
                    recurso_id: $('resource').value,
                    fecha_inicio: $('start').value,
                    fecha_devolucion: $('end').value,
                    cantidad: $('qty').value,
                    observacion: $('reason').value
                });
                alert(j.message);
                $('reason').value = '';
                await loadMine();
                showView('misSolicitudes', document.querySelectorAll('.recursos-nav button')[3]);
            } catch (e) {
                alert(e.message);
            }
        }

        async function loadMine() {
            try {
                const j = await apiCall('mis_solicitudes');
                const rows = j.data;
                $('kPend').textContent = rows.filter(x => x.estado === 'Pendiente').length;
                $('kAprob').textContent = rows.filter(x => x.estado === 'Aprobado').length;
                $('kActivos').textContent = rows.filter(x => x.estado === 'Aprobado').length;
                $('kDev').textContent = rows.filter(x => Number(x.devolucion_solicitada) === 1).length;

                const r = await apiCall('listar_recursos');
                $('kRec').textContent = r.data.length;

                $('userAlert').innerHTML = rows.some(x => Number(x.devolucion_solicitada) === 1 && x.estado === 'Aprobado')
                    ? '⚠️ El administrador ha solicitado la devolución/cierre de uno de tus préstamos.'
                    : 'ℹ️ Tus solicitudes se sincronizan con el módulo del administrador.';

                $('requestsTable').innerHTML = rows.map(x => `
                    <tr>
                        <td><b>${esc(x.recurso_nombre)}</b><div class="r-mini">${esc(x.tipo)}</div></td>
                        <td>${String(x.fecha_inicio).slice(0, 10)} → ${String(x.fecha_devolucion).slice(0, 10)}</td>
                        <td>${x.cantidad}</td>
                        <td><span class="r-status ${x.estado === 'Pendiente' ? 'r-pending' : x.estado === 'Aprobado' ? 'r-approved' : x.estado === 'Devuelto' ? 'r-returned' : 'r-rejected'}">${esc(x.estado)}</span></td>
                        <td>${esc(x.observacion || '—')}</td>
                        <td>${Number(x.devolucion_solicitada) === 1 ? '⚠️ Solicitada' : x.estado === 'Devuelto' ? '✅ Devuelto' : '—'}</td>
                    </tr>`).join('') || '<tr><td colspan="6" class="r-empty">No tienes solicitudes.</td></tr>';

                $('activeTable').innerHTML = rows.filter(x => x.estado === 'Aprobado').map(x => `
                    <tr>
                        <td>${esc(x.recurso_nombre)}</td>
                        <td>${String(x.fecha_inicio).slice(0, 10)}</td>
                        <td>${String(x.fecha_devolucion).slice(0, 10)}</td>
                        <td><span class="r-status r-approved">Activo</span></td>
                        <td>${Number(x.devolucion_solicitada) === 1 ? '⚠️ Administrador solicitó devolución' : 'En uso'}</td>
                    </tr>`).join('') || '<tr><td colspan="5" class="r-empty">No tienes préstamos activos.</td></tr>';

            } catch (e) {
                if (!String(e.message).includes('Sesión')) console.error(e);
            }
        }

        const t = new Date().toISOString().slice(0, 10);
        if ($('start')) $('start').min = t;
        if ($('end')) $('end').min = t;

        // Inicialización
        loadResources();
        loadInventory();
        loadMine();
        setInterval(loadMine, 5000);
    </script>

</body>
</html>
