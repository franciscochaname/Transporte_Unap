<?php
session_start();
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verificar en la tabla 'usuarios'
    $query = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Almacenar información en la sesión
        $_SESSION['username'] = $user['username'];
        $_SESSION['rol'] = $user['rol'];
        header("Location: gestion_usuarios.php");
        exit();
    } else {
        $error = "Nombre de usuario o contraseña incorrectos.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <form method="post" action="login_handler.php">
        <label for="username">Usuario:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit">Ingresar</button>
        <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    </form>
</body>
</html>
