<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Olvidé mi Contraseña - Gestión de Transporte</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/tabla.css">
</head>
<body>
    <div class="login-container">
        <h2>Olvidé mi Contraseña</h2>
        <form action="forgot_password_handler.php" method="POST">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
            
            <button type="submit">Enviar</button>
        </form>
    </div>
</body>
</html>
