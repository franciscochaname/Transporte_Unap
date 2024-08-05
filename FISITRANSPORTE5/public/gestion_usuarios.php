<?php


session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include '../config/config.php'; // Incluir el archivo de configuración

$mensaje = ""; // Variable para almacenar mensajes
$mensaje_tipo = ""; // Variable para almacenar el tipo de mensaje (éxito o error)

// Función para verificar si el nombre de usuario ya existe
function checkUsernameExists($conn, $username, $usuario_id = null) {
    $query = "SELECT COUNT(*) FROM usuarios WHERE username = ?";
    if ($usuario_id) {
        $query .= " AND id != ?";
    }
    $stmt = $conn->prepare($query);
    if ($usuario_id) {
        $stmt->bind_param("si", $username, $usuario_id);
    } else {
        $stmt->bind_param("s", $username);
    }
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// Función para verificar si el propietario ya tiene una cuenta
function checkPropietarioExists($conn, $propietario_nombre, $usuario_id = null) {
    $query = "SELECT COUNT(*) FROM usuarios WHERE propietario_nombre = ?";
    if ($usuario_id) {
        $query .= " AND id != ?";
    }
    $stmt = $conn->prepare($query);
    if ($usuario_id) {
        $stmt->bind_param("si", $propietario_nombre, $usuario_id);
    } else {
        $stmt->bind_param("s", $propietario_nombre);
    }
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// Insertar o editar USUARIO en la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection(); // Obtener la conexión a la base de datos

    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
    $rol = $_POST['rol'];
    $propietario_nombre = isset($_POST['propietario_nombre']) ? $_POST['propietario_nombre'] : null;
    $personal_id = isset($_POST['personal_id']) ? $_POST['personal_id'] : null;
    $accion = $_POST['accion'];
    $usuario_id = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;

    // Verificar si el nombre de usuario ya existe
    if (checkUsernameExists($conn, $username, $usuario_id)) {
        $mensaje = "El nombre de usuario ya existe. Por favor, elija otro.";
        $mensaje_tipo = "error";
    } else if ($rol == 'Administrador' && !empty($propietario_nombre) && checkPropietarioExists($conn, $propietario_nombre, $usuario_id)) {
        $mensaje = "El propietario seleccionado ya tiene una cuenta. Por favor, elija otro.";
        $mensaje_tipo = "error";
    } else if ($rol == 'Mécanico' && !empty($personal_id) && checkPropietarioExists($conn, $propietario_nombre, $usuario_id)) {
        $mensaje = "El propietario seleccionado ya tiene una cuenta. Por favor, elija otro.";
        $mensaje_tipo = "error";
    } else {
        // Obtener nombre del propietario si es rol "Mécanico"
        if ($rol == 'Mécanico' && !empty($personal_id)) {
            $stmt = $conn->prepare("SELECT CONCAT(nombre, ' ', apellido) AS nombre_completo FROM personal WHERE id = ?");
            $stmt->bind_param("i", $personal_id);
            $stmt->execute();
            $stmt->bind_result($nombre_completo);
            $stmt->fetch();
            $stmt->close();
            $propietario_nombre = $nombre_completo;
        }

        // Asignar "NO ASIGNADO" si no se proporciona nombre del propietario
        if ($rol == 'Administrador' && empty($propietario_nombre)) {
            $propietario_nombre = "NO ASIGNADO";
        }

        // Asignar "NO ASIGNADO" si no se selecciona un personal para rol Mecánico
        if ($rol == 'Mécanico' && empty($personal_id)) {
            $propietario_nombre = "NO ASIGNADO";
            $personal_id = null;
        }

        if ($accion == 'editar') {
            // Editar usuario existente
            if ($password) {
                $stmt = $conn->prepare("UPDATE usuarios SET username = ?, password = ?, rol = ?, propietario_nombre = ?, personal_id = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $username, $password, $rol, $propietario_nombre, $personal_id, $usuario_id);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET username = ?, rol = ?, propietario_nombre = ?, personal_id = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $rol, $propietario_nombre, $personal_id, $usuario_id);
            }

            if ($stmt->execute()) {
                $mensaje = "Usuario actualizado exitosamente.";
                $mensaje_tipo = "éxito";
            } else {
                $mensaje = "Error al actualizar el usuario: " . $stmt->error;
                $mensaje_tipo = "error";
            }
        } else {
            // Insertar un nuevo usuario
            $fecha_actual = date("Y-m-d H:i:s");
            if ($password) {
                $stmt = $conn->prepare("INSERT INTO usuarios (username, password, rol, estadoCuenta, fecha, propietario_nombre, personal_id) VALUES (?, ?, ?, 1, ?, ?, ?)");
                $stmt->bind_param("sssssi", $username, $password, $rol, $fecha_actual, $propietario_nombre, $personal_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO usuarios (username, rol, estadoCuenta, fecha, propietario_nombre, personal_id) VALUES (?, ?, 1, ?, ?, ?)");
                $stmt->bind_param("sssssi", $username, $rol, $fecha_actual, $propietario_nombre, $personal_id);
            }

            if ($stmt->execute()) {
                $mensaje = "Usuario agregado exitosamente.";
                $mensaje_tipo = "éxito";
            } else {
                $mensaje = "Error al agregar el usuario: " . $stmt->error;
                $mensaje_tipo = "error";
            }
        }

        $stmt->close();
    }

    $conn->close();
}

// Eliminar usuario de la base de datos
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    $conn = getDBConnection();
    $usuario_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);

    if ($stmt->execute()) {
        $mensaje = "Usuario eliminado exitosamente.";
        $mensaje_tipo = "éxito";
    } else {
        $mensaje = "Error al eliminar el usuario: " . $stmt->error;
        $mensaje_tipo = "error";
    }

    $stmt->close();
    $conn->close();
}

// Cambiar estado de cuenta de un usuario
if (isset($_GET['accion']) && $_GET['accion'] == 'cambiar_estado' && isset($_GET['id'])) {
    $conn = getDBConnection();
    $usuario_id = $_GET['id'];

    // Obtener el estado actual
    $stmt = $conn->prepare("SELECT estadoCuenta FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($estadoCuenta);
    $stmt->fetch();
    $stmt->close();

    // Cambiar el estado
    $nuevo_estado = $estadoCuenta == 1 ? 0 : 1;
    $stmt = $conn->prepare("UPDATE usuarios SET estadoCuenta = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuevo_estado, $usuario_id);
    if ($stmt->execute()) {
        $mensaje = "Estado de cuenta actualizado exitosamente.";
        $mensaje_tipo = "éxito";
    } else {
        $mensaje = "Error al actualizar el estado de cuenta: " . $stmt->error;
        $mensaje_tipo = "error";
    }

    $stmt->close();
    $conn->close();
}

// Obtener usuarios de la base de datos
$conn = getDBConnection();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT u.*, u.propietario_nombre AS propietario_nombre FROM usuarios u";
$result = $conn->query($sql);
$usuarios = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
$conn->close();

// Obtener mecánicos para el menú desplegable de propietarios
$conn = getDBConnection();
$mecanicos_sql = "SELECT id, nombre, apellido, dni FROM personal WHERE rol = 'mecanico'";
$mecanicos_result = $conn->query($mecanicos_sql);
$mecanicos = [];
if ($mecanicos_result->num_rows > 0) {
    while ($row = $mecanicos_result->fetch_assoc()) {
        $mecanicos[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/boton.css">
    <link rel="stylesheet" href="css/gestion_unidades.css">
    <link rel="stylesheet" href="css/gestion_usuarios.css">
    <script src="js/gestion_usuarios.js"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</head>
<body>
    <?php include '../src/views/header.php'; ?>
    <div class="d-flex">
        <!-- Contenido principal -->
        <div class="container-fluid mt-4" id="main-content">
            <main>
                <section class="section">
                    <h2 class="text-center mb-4">Gestión de Usuarios</h2>
                    <?php
                    if (!empty($mensaje)) {
                        $clase_mensaje = $mensaje_tipo == "éxito" ? "mensaje-exito" : "mensaje-error";
                        echo '<p class="text-center ' . $clase_mensaje . '">' . $mensaje . '</p>';
                    }
                    ?>
                    <div class="d-flex justify-content-between mb-4">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userModal" onclick="openAddModal()">
                            Agregar Usuario
                        </button>
                        <input type="text" id="search" onkeyup="filterTable()" class="form-control w-50" placeholder="Filtrar registros">
                    </div>

                    <!-- Modal de Bootstrap para agregar/editar usuario -->
                    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="userModalLabel">Agregar Usuario</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Formulario para agregar/editar usuario -->
                                    <form action="gestion_usuarios.php" method="POST" id="userForm">
                                        <input type="hidden" id="accion" name="accion" value="agregar">
                                        <input type="hidden" id="usuario_id" name="usuario_id">

                                        <!-- USERNAME -->
                                        <div class="form-group">
                                            <input type="text" id="username" name="username" class="form-control" maxlength="20" required placeholder="Username">
                                        </div>
                                        <!-- PASSWORD -->
                                        <div class="form-group">
                                            <input type="password" id="password" name="password" class="form-control" maxlength="8" placeholder="Password">
                                        </div>
                                        <!-- ROL -->
                                        <div class="form-group">
                                            <select id="rol" name="rol" class="form-control" required onchange="togglePropietario(this.value)">
                                                <option value="" selected>Selecciona un rol</option>
                                                <option value="Administrador">Administrador</option>
                                                <option value="Mécanico">Mécanico</option>
                                            </select>
                                        </div>
                                        <!-- PROPIETARIO NOMBRE -->
                                        <div class="form-group" id="propietarioNombreContainer" style="display: none;">
                                            <label for="propietario_nombre">Nombre del Propietario:</label>
                                            <input type="text" id="propietario_nombre" name="propietario_nombre" class="form-control" placeholder="Nombre del Propietario">
                                        </div>
                                        <!-- PROPIETARIO -->
                                        <div class="form-group" id="propietarioContainer" style="display: none;">
                                            <label for="propietario_id">Propietario:</label>
                                            <select id="personal_id" name="personal_id" class="form-control">
                                                <option value="" selected>Selecciona un propietario</option>
                                                <?php foreach ($mecanicos as $mecanico): ?>
                                                    <option value="<?php echo $mecanico['id']; ?>">
                                                        <?php echo htmlspecialchars($mecanico['nombre'] . ' ' . $mecanico['apellido'] . ' - ' . $mecanico['dni']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($usuarios)): ?>
                        <p class="no-results text-center">No se encontró similitud</p>
                    <?php else: ?>
                        <table class="table table-striped" id="userTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Username</th>
                                    <th>Rol</th>
                                    <th>Propietario</th>
                                    <th>Estado de Cuenta</th>
                                    <th>Último Login</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['propietario_nombre']); ?></td>
                                        <td>
                                            <?php if ($usuario['propietario_nombre'] !== 'SYSTEM'): ?>
                                                <span class="btn-toggle <?php echo $usuario['estadoCuenta'] == 1 ? 'habilitado' : 'deshabilitado'; ?>" onclick="toggleEstadoCuenta(<?php echo $usuario['id']; ?>)">
                                                    <?php echo $usuario['estadoCuenta'] == 1 ? 'Habilitado' : 'Deshabilitado'; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="btn-toggle disabled <?php echo $usuario['estadoCuenta'] == 1 ? 'habilitado' : 'deshabilitado'; ?>">
                                                    <?php echo $usuario['estadoCuenta'] == 1 ? 'Habilitado' : 'Deshabilitado'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($usuario['ultimo_login']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['fecha']); ?></td>
                                        <td>
                                            <?php if ($usuario['propietario_nombre'] !== 'SYSTEM'): ?>
                                                <button onclick="openEditModal('<?php echo $usuario['id']; ?>', '<?php echo htmlspecialchars($usuario['username']); ?>', '<?php echo htmlspecialchars($usuario['rol']); ?>', '<?php echo htmlspecialchars($usuario['personal_id']); ?>', '<?php echo htmlspecialchars($usuario['propietario_nombre']); ?>')" class="btn btn-warning btn-sm">Editar</button>
                                                <button onclick="openDeleteModal('<?php echo $usuario['id']; ?>')" class="btn btn-danger btn-sm">Eliminar</button>
                                            <?php else: ?>
                                                <button class="btn btn-warning btn-sm disabled">Editar</button>
                                                <button class="btn btn-danger btn-sm disabled">Eliminar</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>
    <?php include '../src/views/footer.php'; ?>

    <!-- Modal de confirmación de eliminación -->
    <div id="deleteModal" class="modal fade">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">¿Estás seguro?</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <p>¿Realmente deseas eliminar este registro? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

  
</body>
</html>
