<?php
require_once '../config/config.php';

function createAdminUser() {
    $conn = getDBConnection();
    
    $username = 'Chaname';
    $password = password_hash('chaname', PASSWORD_DEFAULT);  // Hash de la contraseña
    $rol = 'administrador';

    $stmt = $conn->prepare("INSERT INTO usuarios (username, password, rol) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $rol);

    if ($stmt->execute()) {
        echo "Usuario administrador creado exitosamente.";
    } else {
        echo "Error al crear el usuario administrador: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

createAdminUser();
?>
