<?php
session_start();
include '../config/config.php'; // Asegúrate de ajustar la ruta según tu estructura de directorios

// Manejo del inicio de sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare("SELECT id, password, rol FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password, $rol);
    $stmt->fetch();

    // Verificar la contraseña
    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $usuario;
        $_SESSION['rol'] = $rol;
        header("Location: gestion_usuarios.php");
        exit();
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido al Sistema</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <script src="js/index.js"></script>
</head>
<body>
    <div class="background-image">
        <div class="overlay"></div>
        <div class="content-wrapper">
            <div class="welcome-message">
                <h2>Bienvenido al Sistema</h2>
                <p>Puede realizar una solicitud de alquiler de unidades móviles o puede iniciar sesión para acceder al área administrativa.</p>
                <button onclick="goToSolicitudAlquiler()">Solicitar Alquiler</button>
            </div>
            <div class="container">
                <header>
                    <h1>Iniciar Sesión</h1>
                </header>
                <main>
                    <form method="post" action="" class="form-container" onsubmit="return validarFormularioLogin()">
                        <input type="text" name="usuario" id="usuario" required placeholder="Ingrese su usuario" maxlength="20">
                        <input type="password" name="password" id="password" required placeholder="Ingrese su contraseña" maxlength="8">
                        <button type="submit">Acceder</button>
                        <a href="#" class="forgot-password" onclick="openModal()">¿No puede acceder a su cuenta?</a>
                    </form>
                </main>
            </div>
        </div>
    </div>

    <!-- Ventana Modal -->
    <div id="modalRecuperarCuenta" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Recuperar cuenta</h2>
            <form method="post" action="recuperar_cuenta.php" class="form-container" onsubmit="return validarFormularioRecuperar()">
                <input type="text" name="usuario_recuperar" id="usuario_recuperar" required placeholder="Usuario" maxlength="20">
                <input type="email" name="correo_recuperar" id="correo_recuperar" required placeholder="Correo Electrónico">
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>
</body>
</html>

