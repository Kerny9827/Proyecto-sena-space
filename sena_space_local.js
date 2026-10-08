/*
============================================================
LOG-IN - CAPA LOCAL TEMPORAL
============================================================
Esta capa reemplaza temporalmente a recursos.php para la demo
frontend. Los datos se guardan en localStorage del navegador.

Cuando la persona encargada del PHP integre el backend, este
archivo puede reemplazarse por llamadas AJAX/fetch al API real.
============================================================
*/
(function (window) {
    'use strict';

    const STORAGE_KEY = 'senaSpaceLocalDataV2';

    function fechaActual() {
        return new Date().toISOString();
    }

    function datosIniciales() {
        const hoy = fechaActual();
        return {
            recursos: [
                { id: 1, nombre: 'Balón de fútbol', categoria: 'Deportes', tipo: 'Objeto', stock_total: 10, stock_minimo: 2, estado: 'Disponible', codigo: 'DEP-FUT-001', ubicacion: 'Almacén deportivo', descripcion: 'Balón para actividades y entrenamientos de fútbol.', fecha_creacion: hoy },
                { id: 2, nombre: 'Balón de básquet', categoria: 'Deportes', tipo: 'Objeto', stock_total: 8, stock_minimo: 2, estado: 'Disponible', codigo: 'DEP-BAS-001', ubicacion: 'Almacén deportivo', descripcion: 'Balón para actividades y entrenamientos de baloncesto.', fecha_creacion: hoy },
                { id: 3, nombre: 'Raquetas de tenis', categoria: 'Deportes', tipo: 'Objeto', stock_total: 6, stock_minimo: 1, estado: 'Disponible', codigo: 'DEP-TEN-001', ubicacion: 'Almacén deportivo', descripcion: 'Raquetas disponibles para prácticas de tenis.', fecha_creacion: hoy },
                { id: 4, nombre: 'Dominó', categoria: 'Juegos de mesa', tipo: 'Objeto', stock_total: 5, stock_minimo: 1, estado: 'Disponible', codigo: 'JDM-DOM-001', ubicacion: 'Sala de juegos', descripcion: 'Juego de dominó para actividades recreativas.', fecha_creacion: hoy },
                { id: 5, nombre: 'Billar', categoria: 'Juegos recreativos', tipo: 'Objeto', stock_total: 2, stock_minimo: 1, estado: 'Disponible', codigo: 'REC-BIL-001', ubicacion: 'Sala recreativa', descripcion: 'Mesa de billar para actividades recreativas.', fecha_creacion: hoy },
                { id: 6, nombre: 'Parqués', categoria: 'Juegos de mesa', tipo: 'Objeto', stock_total: 4, stock_minimo: 1, estado: 'Disponible', codigo: 'JDM-PAR-001', ubicacion: 'Sala de juegos', descripcion: 'Juego de parqués para actividades recreativas.', fecha_creacion: hoy },
                { id: 7, nombre: 'Ajedrez', categoria: 'Juegos de mesa', tipo: 'Objeto', stock_total: 6, stock_minimo: 1, estado: 'Disponible', codigo: 'JDM-AJE-001', ubicacion: 'Sala de juegos', descripcion: 'Juego de ajedrez para actividades recreativas y formativas.', fecha_creacion: hoy }
            ],
            solicitudes: [],
            usuarios: [],
            reposiciones: [],
            historial: []
        };
    }

    function cargar() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const data = normalizar(JSON.parse(raw));
                const cambio = completarDatosDemo(data);
                if (cambio) guardar(data);
                return data;
            }
        } catch (e) {
            console.error('No fue posible leer los datos locales:', e);
        }

        const inicial = datosIniciales();
        completarDatosDemo(inicial);
        guardar(inicial);
        return inicial;
    }

    function completarDatosDemo(data) {
        let cambio = false;
        const hoy = new Date();
        const fecha = n => { const d = new Date(hoy); d.setDate(d.getDate() + n); return d.toISOString().slice(0,10); };

        // Asegurar que los usuarios existentes tengan ficha y teléfono, sin borrar datos previos.
        const datosContacto = {
            '1001001001': { ficha:'2876543', telefono:'3001112233' },
            '1001001002': { ficha:'2876544', telefono:'3002223344' },
            '1001001003': { ficha:'2876545', telefono:'3003334455' },
            '900100001': { ficha:'ADMIN-001', telefono:'3009998877' }
        };
        (data.usuarios || []).forEach(u => {
            const base = datosContacto[String(u.cedula)] || {};
            if (!u.ficha && base.ficha) { u.ficha = base.ficha; cambio = true; }
            if (!u.telefono && base.telefono) { u.telefono = base.telefono; cambio = true; }
        });

        // Asegurar que SIEMPRE estén disponibles los 7 recursos de la demo.
        // Esto también repara instalaciones antiguas que solo tenían un recurso.
        const recursosBase = datosIniciales().recursos;
        recursosBase.forEach(base => {
            const existe = data.recursos.find(r => Number(r.id) === Number(base.id));
            if (!existe) {
                data.recursos.push({ ...base });
                cambio = true;
            }
        });

        if (!data.usuarios.length) {
            data.usuarios = [
                { cedula:'1001001001', nombre:'Laura Gómez', correo:'laura.gomez@sena.edu.co', ficha:'2876543', telefono:'3001112233', tipo_usuario:'Aprendiz', rol_sistema:'Usuario' },
                { cedula:'1001001002', nombre:'Carlos Rodríguez', correo:'carlos.rodriguez@sena.edu.co', ficha:'2876544', telefono:'3002223344', tipo_usuario:'Instructor', rol_sistema:'Usuario' },
                { cedula:'1001001003', nombre:'María Torres', correo:'maria.torres@sena.edu.co', ficha:'2876545', telefono:'3003334455', tipo_usuario:'Aprendiz', rol_sistema:'Usuario' },
                { cedula:'900100001', nombre:'Administrador LOG-IN', correo:'admin@sena.edu.co', ficha:'ADMIN-001', telefono:'3009998877', tipo_usuario:'Administrador', rol_sistema:'Administrador' }
            ];
            cambio = true;
        }

        if (!data.solicitudes.length) {
            const r = id => data.recursos.find(x => Number(x.id) === id);
            const ahora = new Date().toISOString();
            data.solicitudes = [
                { id:1, usuario_id:'1001001001', usuario_nombre:'Laura Gómez', usuario_correo:'laura.gomez@sena.edu.co', usuario_ficha:'2876543', usuario_telefono:'3001112233', recurso_id:1, recurso_nombre:r(1)?.nombre || 'Balón de fútbol', tipo:'Objeto', fecha_inicio:fecha(0), fecha_devolucion:fecha(3), cantidad:1, estado:'Pendiente', observacion:'Actividad deportiva del grupo.', fecha_creacion:ahora, fecha_aprobacion:null, fecha_devolucion_real:null, devolucion_solicitada:0, observacion_devolucion:'' },
                { id:2, usuario_id:'1001001002', usuario_nombre:'Carlos Rodríguez', usuario_correo:'carlos.rodriguez@sena.edu.co', usuario_ficha:'2876544', usuario_telefono:'3002223344', recurso_id:2, recurso_nombre:r(2)?.nombre || 'Balón de básquet', tipo:'Objeto', fecha_inicio:fecha(-1), fecha_devolucion:fecha(2), cantidad:2, estado:'Aprobado', observacion:'Práctica de baloncesto.', fecha_creacion:ahora, fecha_aprobacion:ahora, fecha_devolucion_real:null, devolucion_solicitada:1, observacion_devolucion:'Devolución solicitada por administración.', },
                { id:3, usuario_id:'1001001003', usuario_nombre:'María Torres', usuario_correo:'maria.torres@sena.edu.co', usuario_ficha:'2876545', usuario_telefono:'3003334455', recurso_id:3, recurso_nombre:r(3)?.nombre || 'Raquetas de tenis', tipo:'Objeto', fecha_inicio:fecha(-2), fecha_devolucion:fecha(1), cantidad:1, estado:'Aprobado', observacion:'Entrenamiento de tenis.', fecha_creacion:ahora, fecha_aprobacion:ahora, fecha_devolucion_real:null, devolucion_solicitada:0, observacion_devolucion:'' },
                { id:4, usuario_id:'1001001001', usuario_nombre:'Laura Gómez', usuario_correo:'laura.gomez@sena.edu.co', usuario_ficha:'2876543', usuario_telefono:'3001112233', recurso_id:4, recurso_nombre:r(4)?.nombre || 'Dominó', tipo:'Objeto', fecha_inicio:fecha(-6), fecha_devolucion:fecha(-3), cantidad:1, estado:'Devuelto', observacion:'Actividad recreativa.', fecha_creacion:ahora, fecha_aprobacion:ahora, fecha_devolucion_real:new Date(Date.now()-3*86400000).toISOString(), devolucion_solicitada:0, observacion_devolucion:'Recurso recibido y verificado.' }
            ];

            // El stock refleja los dos préstamos activos de ejemplo.
            const b = r(2); if (b) b.stock_total = Math.max(0, Number(b.stock_total) - 2);
            const t = r(3); if (t) t.stock_total = Math.max(0, Number(t.stock_total) - 1);
            cambio = true;
        }

        if (!data.reposiciones.length) {
            const billar = data.recursos.find(x => Number(x.id) === 5);
            data.reposiciones = [{ id:1, recurso_id:5, cantidad_solicitada:3, estado:'Solicitada', observacion:'Reposición preventiva para actividades recreativas.', fecha:fecha(0) }];
            if (billar) billar.stock_minimo = Math.max(Number(billar.stock_minimo || 1), 2);
            cambio = true;
        }

        if (!data.historial.length) {
            data.historial = [
                { id:1, fecha:fecha(-6)+'T09:00:00', actor:'Administrador LOG-IN', accion:'Nuevo recurso', detalle:'Se cargó el inventario inicial de recursos deportivos y recreativos.' },
                { id:2, fecha:fecha(-2)+'T10:30:00', actor:'Administrador LOG-IN', accion:'Aprobar solicitud', detalle:'Se aprobó el préstamo de 2 Balones de básquet.' },
                { id:3, fecha:fecha(-1)+'T14:15:00', actor:'Administrador LOG-IN', accion:'Solicitar devolución', detalle:'Se solicitó la devolución de 2 Balones de básquet.' },
                { id:4, fecha:fecha(-3)+'T16:00:00', actor:'Administrador LOG-IN', accion:'Registrar devolución', detalle:'Se registró la devolución de 1 Dominó.' }
            ];
            cambio = true;
        }

        return cambio;
    }

    function normalizar(data) {
        data = data || {};
        data.recursos = Array.isArray(data.recursos) ? data.recursos : [];
        data.solicitudes = Array.isArray(data.solicitudes) ? data.solicitudes : [];
        data.usuarios = Array.isArray(data.usuarios) ? data.usuarios : [];
        data.reposiciones = Array.isArray(data.reposiciones) ? data.reposiciones : [];
        data.historial = Array.isArray(data.historial) ? data.historial : [];
        return data;
    }

    function guardar(data) {
        data = normalizar(data);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        window.dispatchEvent(new CustomEvent('sena-space-local-updated'));
        return data;
    }

    function siguienteId(lista) {
        return lista.reduce((max, item) => Math.max(max, Number(item.id) || 0), 0) + 1;
    }

    function agregarHistorial(data, accion, detalle) {
        data.historial.unshift({
            id: siguienteId(data.historial),
            fecha: fechaActual(),
            actor: localStorage.getItem('userName') || 'Administrador LOG-IN',
            accion: accion,
            detalle: detalle
        });
    }

    function dashboard(data) {
        const devoluciones = data.solicitudes
            .filter(s => s.estado === 'Aprobado' || s.estado === 'Devuelto')
            .map(s => {
                const recurso = data.recursos.find(r => Number(r.id) === Number(s.recurso_id)) || {};
                const usuario = data.usuarios.find(u => String(u.cedula) === String(s.usuario_id)) || {};
                return {
                    ...s,
                    usuario_nombre: s.usuario_nombre || usuario.nombre || 'Usuario',
                    usuario_correo: s.usuario_correo || usuario.correo || '',
                    recurso_nombre: s.recurso_nombre || recurso.nombre || 'Recurso',
                    tipo: s.tipo || recurso.tipo || '',
                    fecha_prestamo: s.fecha_inicio,
                    estado: s.estado === 'Devuelto' ? 'Devuelto' : (Number(s.devolucion_solicitada) === 1 ? 'Solicitada' : 'Pendiente')
                };
            });

        return {
            recursos: data.recursos,
            solicitudes: data.solicitudes,
            devoluciones,
            usuarios: data.usuarios,
            historial: data.historial,
            reposiciones: data.reposiciones
        };
    }

    function respuesta(success, message, data) {
        return { success, message, data: data || {} };
    }

    function ejecutar(accion, datos) {
        const data = cargar();
        const id = Number(datos.id || 0);

        if (accion === 'admin_dashboard') {
            return respuesta(true, 'Datos locales cargados.', dashboard(data));
        }

        if (accion === 'listar_recursos') {
            return respuesta(true, 'Recursos locales cargados.', { recursos: data.recursos });
        }

        if (accion === 'guardar_recurso') {
            const nombre = String(datos.nombre || '').trim();
            const categoria = String(datos.categoria || '').trim();
            const tipo = datos.tipo === 'Servicio' ? 'Servicio' : 'Objeto';
            const stock = Math.max(0, Number(datos.stock_total) || 0);
            const minimo = Math.max(0, Number(datos.stock_minimo) || 0);

            if (!nombre || !categoria) {
                return respuesta(false, 'El nombre y la categoría son obligatorios.');
            }

            if (id > 0) {
                const recurso = data.recursos.find(r => Number(r.id) === id);
                if (!recurso) return respuesta(false, 'El recurso no existe.');

                recurso.nombre = nombre;
                recurso.categoria = categoria;
                recurso.tipo = tipo;
                recurso.stock_total = stock;
                recurso.stock_minimo = minimo;
                recurso.codigo = String(datos.codigo || recurso.codigo || '').trim();
                recurso.ubicacion = String(datos.ubicacion || recurso.ubicacion || '').trim();
                recurso.descripcion = String(datos.descripcion || recurso.descripcion || '').trim();

                // Al editar NO se reactiva automáticamente un recurso dado de baja.
                if (recurso.estado !== 'Baja') {
                    recurso.estado = stock === 0 ? 'Disponible' : (datos.estado || 'Disponible');
                }

                agregarHistorial(data, 'Editar recurso', `Se actualizó el recurso "${nombre}".`);
            } else {
                const nuevo = {
                    id: siguienteId(data.recursos),
                    nombre,
                    categoria,
                    tipo,
                    stock_total: stock,
                    stock_minimo: minimo,
                    estado: 'Disponible',
                    codigo: String(datos.codigo || '').trim(),
                    ubicacion: String(datos.ubicacion || '').trim(),
                    descripcion: String(datos.descripcion || '').trim(),
                    fecha_creacion: fechaActual()
                };
                data.recursos.push(nuevo);
                agregarHistorial(data, 'Nuevo recurso', `Se creó el recurso "${nombre}".`);
            }

            guardar(data);
            return respuesta(true, 'Recurso guardado correctamente.', { recursos: data.recursos });
        }

        if (accion === 'retirar_recurso') {
            const recurso = data.recursos.find(r => Number(r.id) === id);
            if (!recurso) return respuesta(false, 'El recurso no existe.');

            recurso.estado = 'Baja';
            agregarHistorial(data, 'Retirar recurso', `Se dio de baja el recurso "${recurso.nombre}".`);
            guardar(data);
            return respuesta(true, 'Recurso retirado correctamente.', { recursos: data.recursos });
        }

        if (accion === 'crear_solicitud') {
            const recurso = data.recursos.find(r => Number(r.id) === Number(datos.recurso_id));
            const cantidad = Math.max(1, Number(datos.cantidad) || 1);
            const nombre = String(datos.usuario_nombre || localStorage.getItem('userName') || 'Usuario').trim();
            const correo = String(datos.usuario_correo || localStorage.getItem('userEmail') || '').trim();
            const inicio = String(datos.fecha_inicio || '').trim();
            const devolucion = String(datos.fecha_devolucion || '').trim();

            if (!recurso) return respuesta(false, 'El recurso no existe.');
            if (recurso.estado === 'Baja') return respuesta(false, 'Este recurso está dado de baja y no se puede solicitar.');
            if (Number(recurso.stock_total) <= 0) return respuesta(false, 'Este recurso está agotado.');
            if (cantidad > Number(recurso.stock_total)) return respuesta(false, `Solo hay ${recurso.stock_total} unidad(es) disponibles.`);
            if (!inicio || !devolucion) return respuesta(false, 'Debes indicar la fecha de inicio y devolución.');
            if (devolucion < inicio) return respuesta(false, 'La fecha de devolución no puede ser anterior a la fecha de inicio.');

            const solicitud = {
                id: siguienteId(data.solicitudes),
                usuario_id: String(datos.usuario_id || correo || nombre),
                usuario_nombre: nombre,
                usuario_correo: correo,
                usuario_ficha: String(datos.usuario_ficha || localStorage.getItem('userFicha') || '').trim(),
                usuario_telefono: String(datos.usuario_telefono || localStorage.getItem('userTelefono') || '').trim(),
                recurso_id: recurso.id,
                recurso_nombre: recurso.nombre,
                tipo: recurso.tipo,
                fecha_inicio: inicio,
                fecha_devolucion: devolucion,
                cantidad,
                estado: 'Pendiente',
                observacion: String(datos.observacion || '').trim(),
                fecha_creacion: fechaActual(),
                fecha_aprobacion: null,
                fecha_devolucion_real: null,
                devolucion_solicitada: 0,
                observacion_devolucion: ''
            };

            data.solicitudes.push(solicitud);
            const usuario = data.usuarios.find(u => String(u.cedula) === String(solicitud.usuario_id) || String(u.correo || '').toLowerCase() === correo.toLowerCase() || String(u.nombre || '').trim().toLowerCase() === nombre.toLowerCase());
            if (usuario) {
                usuario.ficha = solicitud.usuario_ficha;
                usuario.telefono = solicitud.usuario_telefono;
            } else {
                data.usuarios.push({ cedula: solicitud.usuario_id, nombre, correo, ficha: solicitud.usuario_ficha, telefono: solicitud.usuario_telefono, tipo_usuario: 'Aprendiz', rol_sistema: 'Usuario' });
            }
            localStorage.setItem('userFicha', solicitud.usuario_ficha);
            localStorage.setItem('userTelefono', solicitud.usuario_telefono);
            agregarHistorial(data, 'Nueva solicitud', `${nombre} solicitó ${cantidad} unidad(es) de "${recurso.nombre}".`);
            guardar(data);
            return respuesta(true, 'Solicitud de préstamo enviada correctamente.', { solicitud, ...dashboard(data) });
        }

        if (accion === 'aprobar_solicitud') {
            const solicitud = data.solicitudes.find(s => Number(s.id) === id);
            if (!solicitud) return respuesta(false, 'La solicitud no existe.');
            if (solicitud.estado !== 'Pendiente') return respuesta(false, 'La solicitud ya fue procesada.');

            const recurso = data.recursos.find(r => Number(r.id) === Number(solicitud.recurso_id));
            const cantidad = Number(solicitud.cantidad) || 0;
            if (!recurso) return respuesta(false, 'El recurso no existe.');
            if (recurso.estado === 'Baja') return respuesta(false, 'El recurso está dado de baja.');
            if (Number(recurso.stock_total) < cantidad) return respuesta(false, 'No hay suficiente stock disponible.');

            recurso.stock_total = Number(recurso.stock_total) - cantidad;
            solicitud.estado = 'Aprobado';
            solicitud.fecha_aprobacion = fechaActual();
            solicitud.observacion = String(datos.observacion || solicitud.observacion || '');
            agregarHistorial(data, 'Aprobar solicitud', `Se aprobó la solicitud #${id} de "${recurso.nombre}".`);
            guardar(data);
            return respuesta(true, 'Solicitud aprobada correctamente.', dashboard(data));
        }

        if (accion === 'rechazar_solicitud') {
            const solicitud = data.solicitudes.find(s => Number(s.id) === id);
            if (!solicitud) return respuesta(false, 'La solicitud no existe.');
            if (solicitud.estado !== 'Pendiente') return respuesta(false, 'La solicitud ya fue procesada.');

            solicitud.estado = 'Rechazado';
            solicitud.observacion = String(datos.observacion || solicitud.observacion || '');
            agregarHistorial(data, 'Rechazar solicitud', `Se rechazó la solicitud #${id}.`);
            guardar(data);
            return respuesta(true, 'Solicitud rechazada correctamente.', dashboard(data));
        }

        if (accion === 'solicitar_devolucion') {
            const solicitud = data.solicitudes.find(s => Number(s.id) === id);
            if (!solicitud) return respuesta(false, 'La solicitud no existe.');
            if (solicitud.estado !== 'Aprobado') return respuesta(false, 'Solo se puede solicitar devolución de préstamos aprobados.');
            if (Number(solicitud.devolucion_solicitada) === 1) return respuesta(false, 'La devolución ya fue solicitada.');

            solicitud.devolucion_solicitada = 1;
            solicitud.observacion_devolucion = String(datos.observacion || 'Solicitud de devolución.');
            agregarHistorial(data, 'Solicitar devolución', `Se solicitó la devolución de la solicitud #${id}.`);
            guardar(data);
            return respuesta(true, 'Solicitud de devolución enviada correctamente.', dashboard(data));
        }

        if (accion === 'registrar_devolucion') {
            const solicitud = data.solicitudes.find(s => Number(s.id) === id);
            if (!solicitud) return respuesta(false, 'La solicitud no existe.');
            if (solicitud.estado !== 'Aprobado') return respuesta(false, 'La solicitud no está activa.');

            const recurso = data.recursos.find(r => Number(r.id) === Number(solicitud.recurso_id));
            if (recurso) recurso.stock_total = Number(recurso.stock_total) + (Number(solicitud.cantidad) || 0);

            solicitud.estado = 'Devuelto';
            solicitud.fecha_devolucion_real = fechaActual();
            solicitud.devolucion_solicitada = 0;
            agregarHistorial(data, 'Registrar devolución', `Se registró la devolución de la solicitud #${id}.`);
            guardar(data);
            return respuesta(true, 'Devolución registrada correctamente.', dashboard(data));
        }

        if (accion === 'solicitar_reposicion') {
            const recurso = data.recursos.find(r => Number(r.id) === Number(datos.recurso_id));
            const cantidad = Number(datos.cantidad) || 0;
            if (!recurso) return respuesta(false, 'El recurso no existe.');
            if (cantidad <= 0) return respuesta(false, 'La cantidad debe ser mayor que cero.');

            data.reposiciones.push({
                id: siguienteId(data.reposiciones),
                recurso_id: recurso.id,
                cantidad_solicitada: cantidad,
                estado: 'Solicitada',
                observacion: String(datos.observacion || ''),
                fecha: fechaActual()
            });
            agregarHistorial(data, 'Solicitar implementos', `Se solicitaron ${cantidad} implemento(s) para "${recurso.nombre}".`);
            guardar(data);
            return respuesta(true, 'Solicitud de implementos registrada correctamente.', dashboard(data));
        }

        if (accion === 'cambiar_rol') {
            const usuario = data.usuarios.find(u => String(u.cedula) === String(datos.cedula));
            if (!usuario) return respuesta(false, 'El usuario no existe en los datos locales.');
            usuario.rol_sistema = datos.rol === 'Administrador' ? 'Administrador' : 'Usuario';
            agregarHistorial(data, 'Cambiar rol', `Se cambió el rol de ${usuario.nombre} a ${usuario.rol_sistema}.`);
            guardar(data);
            return respuesta(true, 'Rol actualizado correctamente.', dashboard(data));
        }

        return respuesta(false, `Acción local no implementada: ${accion}`);
    }

    window.SenaSpaceLocal = {
        STORAGE_KEY,
        getData: cargar,
        saveData: guardar,
        execute: ejecutar,
        api: function (accion, datos = {}, callback = null) {
            // setTimeout mantiene el comportamiento asíncrono de una API real.
            setTimeout(function () {
                const resultado = ejecutar(accion, datos);
                if (typeof callback === 'function') callback(resultado);
            }, 0);
        }
    };
})(window);
