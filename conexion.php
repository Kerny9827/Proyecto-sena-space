<?php
$servidor = "localhost";
$usuario  = "root";
$password = "";
$basedatos = "sena_space";

$conexion = mysqli_connect($servidor, $usuario, $password, $basedatos);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = trim($_POST["correo"]);
    $clave  = trim($_POST["password"]);

    $sql = "SELECT * FROM usuarios WHERE correo='$correo' AND password='$clave'";
    $resultado = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($resultado) > 0) {

        $fila = mysqli_fetch_assoc($resultado);

        session_start();
        $_SESSION["usuario"] = $fila["nombre"];
        $_SESSION["correo"] = $fila["correo"];

        header("Location: index.html");
        exit();

    } else {
        echo "<script>
                alert('❌ Correo o contraseña incorrectos');
                window.location='Login.html';
              </script>";
    }
}

mysqli_close($conexion);
?>