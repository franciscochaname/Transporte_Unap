<?php
include '../config/config.php'; // Incluir el archivo de configuración

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $conn = getDBConnection(); // Obtener la conexión a la base de datos
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['rol'] = $user['rol'];
        header("Location: gestion_usuarios.php");
        exit();
    } else {
        $mensaje = "Usuario o contraseña incorrectos.";
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - UNAP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background: url('https://scontent-lim1-1.xx.fbcdn.net/v/t39.30808-6/280825943_5791269257568094_6195652975915651087_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=127cfc&_nc_ohc=xGEu1XprCGYQ7kNvgHfmHpT&_nc_ht=scontent-lim1-1.xx&oh=00_AYATYi3bBKlY-jPq7NOJjbVQ4lEO6-S62C2HTqw0xw78Kw&oe=66A3E747') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-section {
            background: white;
            padding: 2em;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
        }
        .login-section h2 {
            margin-bottom: 1em;
        }
        .login-section label {
            display: block;
            margin-bottom: 0.5em;
        }
        .login-section input {
            width: 100%;
            padding: 0.5em;
            margin-bottom: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-section button {
            width: 100%;
            padding: 0.7em;
            border: none;
            border-radius: 5px;
            background: #0078D4;
            color: white;
            font-size: 1em;
            cursor: pointer;
        }
        .login-section button:hover {
            background: #005ba1;
        }
        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 1em;
        }
        .forgot-password a {
            color: #0078D4;
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        .message {
            background: #ffdddd;
            color: #d8000c;
            padding: 0.7em;
            border-radius: 5px;
            margin-bottom: 1em;
            text-align: center;
        }
    </style>
</head>
<body>
    <main class="login-main">
        <section class="login-section">
            <h2>Iniciar sesión</h2>
            <?php if (isset($mensaje)): ?>
                <div class="message"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            <form method="post" action="" class="form-container">
                <label for="usuario">Correo electrónico, teléfono o Skype</label>
                <input type="text" name="usuario" id="usuario" required>
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required>
                <button type="submit">Siguiente</button>
                <div class="forgot-password">
                    <a href="forgot_password.php">¿No puede acceder a su cuenta?</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
