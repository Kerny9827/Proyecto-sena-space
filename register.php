<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>LOG-IN - Registro</title>

    <!-- Fonts -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700"
        rel="stylesheet">

    <!-- Styles -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <!-- QRCode -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>

    <style>

        body{
            background: linear-gradient(135deg,#2e8b57,#1e3c72);
            min-height:100vh;
            display:flex;
            align-items:center;
        }

        .register-card{
            border:none;
            border-radius:25px;
            overflow:hidden;
            box-shadow:0 15px 40px rgba(0,0,0,.2);
        }

        .left-panel{
            background:#f8f9fc;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:40px;
        }

        .logo-large{
            width:220px;
            animation:pulse 2s infinite;
        }

        @keyframes pulse{
            0%{transform:scale(1);}
            50%{transform:scale(1.05);}
            100%{transform:scale(1);}
        }

        .form-control,
        .custom-select{
            border-radius:50px !important;
            height:52px;
            padding-left:20px;
            border:2px solid #e5e7eb;
            transition:.3s;
        }

        .form-control:focus,
        .custom-select:focus{
            border-color:#2e8b57;
            box-shadow:0 0 10px rgba(46,139,87,.2);
        }

        .selected-box{
            background:#e8fff1;
            border-left:5px solid #2e8b57;
            padding:12px 18px;
            border-radius:10px;
            margin-top:10px;
            display:none;
            font-weight:600;
            color:#2e8b57;
        }

        .btn-register{
            border-radius:50px;
            height:50px;
            font-weight:bold;
            font-size:16px;
            background:linear-gradient(135deg,#2e8b57,#1e3c72);
            border:none;
            transition:.3s;
        }

        .btn-register:hover{
            transform:translateY(-2px);
            opacity:.95;
        }

        #qrContainer{
            display:none;
            margin-top:30px;
            text-align:center;
            animation:fadeIn .5s ease;
        }

        #qrContainer canvas{
            background:white;
            padding:15px;
            border-radius:20px;
            box-shadow:0 10px 25px rgba(0,0,0,.15);
        }

        .qr-info{
            margin-top:15px;
            font-size:14px;
            color:#555;
        }

        @keyframes fadeIn{
            from{
                opacity:0;
                transform:translateY(20px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

    </style>

</head>

<body>

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-10">

                <div class="card register-card">

                    <div class="card-body p-0">

                        <div class="row no-gutters">

                            <!-- LEFT -->
                            <div class="col-md-4 left-panel">

                                 <a class="small" href="Login.html">

                                    <img src="img/fondo.png"
                                        class="logo-large"
                                        alt="LOG-IN">

                                </a>

                            </div>

                            <!-- RIGHT -->
                            <div class="col-md-8">

                                <div class="p-5">

                                    <div class="text-center mb-4">

                                        <h1 class="h3 text-gray-900">
                                            Crear una cuenta
                                        </h1>

                                        <p class="text-muted">
                                            Registra tus datos para generar tu QR personal
                                        </p>

                                    </div>

                                    <form class="user" id="registerForm">

                                        <div class="form-group">

                                            <input type="text"
                                                name="registerName"
                                                class="form-control"
                                                id="registerName"
                                                placeholder="Nombre completo"
                                                required>

                                        </div>

                                        <div class="form-group">

                                            <input type="email"
                                                name="registerEmail"
                                                class="form-control"
                                                id="registerEmail"
                                                placeholder="Correo electrónico"
                                                required>

                                        </div>

                                        <div class="form-group">

                                            <input type="password"
                                                name="registerPassword"
                                                class="form-control"
                                                id="registerPassword"
                                                placeholder="Contraseña"
                                                required>

                                        </div>

                                        <div class="form-group">

                                            <input type="password"
                                                name="confirmPassword"
                                                class="form-control"
                                                id="confirmPassword"
                                                placeholder="Confirmar contraseña"
                                                required>

                                        </div>

                                        <!-- DOCUMENTO -->

                                        <div class="form-group">

                                            <select class="custom-select"
                                                id="tipoDocumento"
                                                required>

                                                <option value="" disabled selected>
                                                    Tipo de documento
                                                </option>

                                                <option value="Cédula">
                                                    Cédula
                                                </option>

                                                <option value="Tarjeta de identidad">
                                                    Tarjeta de identidad
                                                </option>

                                                <option value="Pasaporte">
                                                    Pasaporte
                                                </option>

                                            </select>

                                            <div id="selectedDocumento"
                                                class="selected-box">
                                            </div>

                                        </div>

                                        <!-- NUMERO -->

                                        <div class="form-group">

                                            <input type="text"
                                                class="form-control"
                                                id="Cedula"
                                                placeholder="Número de documento"
                                                required>

                                        </div>

                                        <!-- ROL -->

                                        <div class="form-group">

                                            <select class="custom-select"
                                                id="userType"
                                                required>

                                                <option value="" disabled selected>
                                                    Seleccione su rol
                                                </option>

                                                <option value="Aprendiz">
                                                    Aprendiz
                                                </option>

                                                <option value="Instructor">
                                                    Instructor
                                                </option>

                                                <option value="Seguridad">
                                                    Seguridad
                                                </option>

                                                <option value="Cafetería">
                                                    Cafetería
                                                </option>

                                                <option value="Visitante">
                                                    Visitante
                                                </option>

                                            </select>

                                            <div id="selectedUserType"
                                                class="selected-box">
                                            </div>

                                        </div>

                                        <button type="submit"
                                            class="btn btn-success btn-register btn-block">

                                            <i class="fas fa-user-plus"></i>
                                            Registrar cuenta

                                        </button>

                                    </form>

                                    <!-- QR -->

                                    <div id="qrContainer">

                                        <h4 class="mt-4">
                                            QR Personal Generado
                                        </h4>

                                        <canvas id="qrCanvas"></canvas>

                                        <div class="qr-info">

                                            Este QR contiene toda la información
                                            del usuario registrado.

                                        </div>

                                    </div>

                                    <hr>

                                    <div class="text-center">

                                          <a class="small" href="Login.html">

                                            ¿Ya tienes una cuenta? Inicia sesión

                                        </a>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- SCRIPT -->

    <script>

        // MOSTRAR OPCION SELECCIONADA

        const userType = document.getElementById("userType");
        const selectedUserType = document.getElementById("selectedUserType");

        userType.addEventListener("change", function () {

            selectedUserType.style.display = "block";

            selectedUserType.innerHTML =
                `<i class="fas fa-check-circle"></i>
                Rol seleccionado:
                <b>${this.value}</b>`;

        });

        const tipoDocumento = document.getElementById("tipoDocumento");
        const selectedDocumento = document.getElementById("selectedDocumento");

        tipoDocumento.addEventListener("change", function () {

            selectedDocumento.style.display = "block";

            selectedDocumento.innerHTML =
                `<i class="fas fa-id-card"></i>
                Documento seleccionado:
                <b>${this.value}</b>`;

        });

        // REGISTRO + QR

        document.getElementById("registerForm")
            .addEventListener("submit", function (e) {

            e.preventDefault();

            let name =
                document.getElementById("registerName").value.trim();

            let email =
                document.getElementById("registerEmail").value.trim();

            let password =
                document.getElementById("registerPassword").value;

            let confirmPassword =
                document.getElementById("confirmPassword").value;

            let role =
                document.getElementById("userType").value;

            let tipoDoc =
                document.getElementById("tipoDocumento").value;

            let cedula =
                document.getElementById("Cedula").value.trim();

            if (!name || !email || !password || !confirmPassword
                || !role || !tipoDoc || !cedula) {

                alert("Completa todos los campos.");
                return;

            }

            if (password !== confirmPassword) {

                alert("Las contraseñas no coinciden.");
                return;

            }

            // DATOS DEL QR

            const qrData = `
            LOG-IN USER
            ------------------------
            Nombre: ${name}
            Correo: ${email}
            Documento: ${tipoDoc}
            Número: ${cedula}
            Rol: ${role}
            Registro: ${new Date().toLocaleString()}
            `;

            // MOSTRAR QR

            document.getElementById("qrContainer")
                .style.display = "block";

            QRCode.toCanvas(
                document.getElementById("qrCanvas"),
                qrData,
                {
                    width: 280,
                    margin: 2
                }
            );

            // GUARDAR EN LOCALSTORAGE

            localStorage.setItem("userName", name);
            localStorage.setItem("userRole", role);
            localStorage.setItem("userQR", qrData);

            alert("Cuenta registrada correctamente.");

        });

    </script>

</body>

</html>