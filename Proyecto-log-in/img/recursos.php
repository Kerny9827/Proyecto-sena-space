<?php
session_start();
require_once 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

<script>
const API = 'recursos.php';
let DATA = {};
let selectedId = null;
const $ = id => document.getElementById(id);


function load() {
    api('admin_dashboard', {}, r => {
        if (!r.success) {
            alert(r.message || 'Acceso denegado');
            return;
        }
        DATA = r.data || {};
        renderAll();
    });
}

document.querySelectorAll('#tabs a').forEach(a => {
    a.onclick = () => {
        document.querySelectorAll('#tabs a').forEach(x => x.classList.remove('active'));
        document.querySelectorAll('.view').forEach(x => x.classList.add('d-none'));
        a.classList.add('active');
        const el = $(a.dataset.target);
        if (el) el.classList.remove('d-none');
        renderAll();
    };
});

function renderAll() {
    const s = DATA.solicitudes || [];
    const r = DATA.recursos || [];
    const p = s.filter(x => x.estado === 'Pendiente');
    const a = s.filter(x => x.estado === 'Aprobado');
    const d = a.filter(x => String(x.devolucion_solicitada) === '1' || String(x.devolucion_solicitada) === 'true');
    const low = r.filter(x => Number(x.stock_total) <= Number(x.stock_minimo));

    $('kPend').textContent = p.length;
    $('kActive').textContent = a.length;
    $('kDev').textContent = d.length;
    $('kLow').textContent = low.length;

    $('attention').innerHTML = (p.length || d.length || low.length)
        ? `Hay <b>${p.length}</b> solicitudes por aprobar, <b>${d.length}</b> devoluciones y <b>${low.length}</b> recursos con stock bajo.`
        : 'No hay tareas críticas pendientes.';

    renderSolicitudes();
    renderPrestados();
    renderDevoluciones();
    renderInventario();
    renderUsuarios();
    renderHistorial();
}

function renderSolicitudes() {
    const q = (($('qSolicitudes') && $('qSolicitudes').value) || '').toLowerCase();
    const a = (DATA.solicitudes || []).filter(x =>
        x.estado === 'Pendiente' &&
        `${x.usuario_nombre} ${x.recurso_nombre} ${x.observacion}`.toLowerCase().includes(q)
    );

    $('tblSolicitudes').innerHTML = a.map(x => `
        <tr>
            <td><b>${x.usuario_nombre}</b><br><span class="r-mini">${x.usuario_correo || ''}</span></td>
            <td><b>${x.recurso_nombre}</b><br><span class="r-mini">${x.tipo}</span></td>
            <td>${String(x.fecha_inicio).slice(0,10)} → ${String(x.fecha_devolucion).slice(0,10)}</td>
            <td>${x.cantidad}</td>
            <td>${x.observacion || '—'}</td>
            <td>
                <button class="btn btn-success btn-sm" onclick="aprobar(${x.id})">Aprobar</button>
                <button class="btn btn-danger btn-sm" onclick="rechazar(${x.id})">Rechazar</button>
            </td>
        </tr>`).join('') ||
        '<tr><td colspan="6" class="r-empty">No hay solicitudes pendientes.</td></tr>';
}

function aprobar(id) {
    const n = prompt('Observación de aprobación:', 'Préstamo aprobado.');
    api('aprobar_solicitud', { id, observacion: n || '' }, r => { alert(r.message); load(); });
}

function rechazar(id) {
    const n = prompt('Motivo del rechazo:', 'Solicitud rechazada.');
    api('rechazar_solicitud', { id, observacion: n || '' }, r => { alert(r.message); load(); });
}

function renderPrestados() {
    const a = (DATA.solicitudes || []).filter(x => x.estado === 'Aprobado');

    $('tblPrestados').innerHTML = a.map(x => `
        <tr>
            <td>${x.usuario_nombre}</td>
            <td>${x.usuario_correo || '—'}</td>
            <td><b>${x.recurso_nombre}</b><br><span class="r-mini">${x.tipo} · ${x.cantidad}</span></td>
            <td>${String(x.fecha_inicio).slice(0,10)}</td>
            <td>${String(x.fecha_devolucion).slice(0,10)}</td>
            <td>
                <span class="r-status r-approved">Aprobado</span>
                ${String(x.devolucion_solicitada) === '1'
                    ? '<br><span class="r-status r-pending mt-1">Devolución solicitada</span>'
                    : ''}
            </td>
            <td>
                <button class="btn btn-warning btn-sm" onclick="devolucion(${x.id})">Solicitar devolución</button>
                <button class="btn btn-success btn-sm" onclick="confirmar(${x.id})">Confirmar</button>
            </td>
        </tr>`).join('') ||
        '<tr><td colspan="7" class="r-empty">No hay préstamos activos.</td></tr>';
}

function renderDevoluciones() {
    const a = (DATA.solicitudes || []).filter(x =>
        x.estado === 'Aprobado' &&
        (String(x.devolucion_solicitada) === '1' || String(x.devolucion_solicitada) === 'true')
    );

    $('tblDevoluciones').innerHTML = a.map(x => `
        <tr>
            <td>${x.usuario_nombre}<br><span class="r-mini">${x.usuario_correo || ''}</span></td>
            <td>${x.recurso_nombre}</td>
            <td>${String(x.fecha_devolucion).slice(0,10)}</td>
            <td><span class="r-status r-pending">Pendiente</span></td>
            <td>
                <button class="btn btn-warning btn-sm" onclick="devolucion(${x.id})">Reenviar</button>
                <button class="btn btn-success btn-sm" onclick="confirmar(${x.id})">Confirmar devolución</button>
            </td>
        </tr>`).join('') ||
        '<tr><td colspan="5" class="r-empty">No hay devoluciones solicitadas.</td></tr>';
}

function devolucion(id) {
    api('solicitar_devolucion', { id }, r => { alert(r.message); load(); });
}

function confirmar(id) {
    if (confirm('¿Confirmar que el objeto/servicio fue devuelto o cerrado?')) {
        api('registrar_devolucion', { id }, r => { alert(r.message); load(); });
    }
}

function renderInventario() {
    $('tblInventario').innerHTML = (DATA.recursos || []).map(x => {
        const low = Number(x.stock_total) <= Number(x.stock_minimo);
        const estadoClass = x.estado === 'Baja'
            ? 'r-rejected'
            : low ? 'r-pending' : 'r-approved';
        const estadoLabel = x.estado === 'Baja'
            ? 'Baja'
            : low ? 'Stock bajo' : 'Disponible';

        return `
            <tr>
                <td><b>${x.nombre}</b></td>
                <td>${x.categoria}</td>
                <td>${x.tipo}</td>
                <td><b>${x.stock_total}</b></td>
                <td>${x.stock_minimo}</td>
                <td><span class="r-status ${estadoClass}">${estadoLabel}</span></td>
                <td>
                    ${low
                        ? `<button class="btn btn-primary btn-sm" onclick="reposicion(${x.id})">Solicitar implementos</button>`
                        : '—'}
                </td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="editar(${x.id})">Editar</button>
                    <button class="btn btn-danger btn-sm" onclick="retirar(${x.id})">Retirar</button>
                </td>
            </tr>`;
    }).join('') ||
    '<tr><td colspan="8" class="r-empty">No hay recursos registrados.</td></tr>';
}

function nuevoRecurso() {
    selectedId = null;
    $('rid').value = '';
    $('rnombre').value = '';
    $('rcat').value = '';
    $('rtipo').value = 'Objeto';
    $('rstock').value = 1;
    $('rmin').value = 1;
    $('#modalRecurso').modal('show');
}

function editar(id) {
    const x = DATA.recursos.find(r => Number(r.id) === Number(id));
    if (!x) return;

    selectedId = id;
    $('rnombre').value = x.nombre;
    $('rcat').value = x.categoria;
    $('rtipo').value = x.tipo;
    $('rstock').value = x.stock_total;
    $('rmin').value = x.stock_minimo;
    $('#modalRecurso').modal('show');
}

function guardarRecurso() {
    api('guardar_recurso', {
        id: selectedId || '',
        nombre: $('rnombre').value,
        categoria: $('rcat').value,
        tipo: $('rtipo').value,
        stock_total: $('rstock').value,
        stock_minimo: $('rmin').value,
        estado: 'Disponible'
    }, r => {
        alert(r.message);
        if (r.success) {
            $('#modalRecurso').modal('hide');
            load();
        }
    });
}

function retirar(id) {
    if (confirm('¿Retirar este recurso?')) {
        api('retirar_recurso', { id }, r => { alert(r.message); load(); });
    }
}

function reposicion(id) {
    const q = prompt('Cantidad de implementos a solicitar:', '1');
    if (q) {
        api('solicitar_reposicion', {
            recurso_id: id,
            cantidad: q,
            observacion: 'Reposición por stock bajo'
        }, r => { alert(r.message); load(); });
    }
}

function renderUsuarios() {
    $('tblUsuarios').innerHTML = (DATA.usuarios || []).map(x => `
        <tr>
            <td><b>${x.nombre}</b></td>
            <td>${x.correo}</td>
            <td>${x.tipo_usuario || ''}</td>
            <td>
                <span class="r-status ${x.rol_sistema === 'Administrador' ? 'r-pending' : 'r-approved'}">
                    ${x.rol_sistema}
                </span>
            </td>
            <td>
                <button class="btn btn-primary btn-sm"
                    onclick="rol(${x.cedula},'${x.rol_sistema === 'Administrador' ? 'Usuario' : 'Administrador'}')">
                    Cambiar a ${x.rol_sistema === 'Administrador' ? 'Usuario' : 'Administrador'}
                </button>
            </td>
        </tr>`).join('') ||
        '<tr><td colspan="5" class="r-empty">No hay usuarios.</td></tr>';
}

function rol(cedula, nuevo) {
    if (confirm('¿Cambiar rol a ' + nuevo + '?')) {
        api('cambiar_rol', { cedula, rol: nuevo }, r => { alert(r.message); load(); });
    }
}

function renderHistorial() {
    $('tblHistorial').innerHTML = (DATA.historial || []).map(x => `
        <tr>
            <td>${x.fecha}</td>
            <td>${x.actor || '—'}</td>
            <td>${x.accion}</td>
            <td>${x.detalle}</td>
        </tr>`).join('') ||
        '<tr><td colspan="4" class="r-empty">No hay movimientos registrados.</td></tr>';
}

load();
setInterval(load, 5000);

const name = localStorage.getItem("userName");
if (name) {
    const userNameTop = document.getElementById("userNameTop");
    if (userNameTop) userNameTop.textContent = name;
}
</script>