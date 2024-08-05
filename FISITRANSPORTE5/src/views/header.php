<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Transporte</title>
    <link rel="stylesheet" href="css/header-sidebar.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/contenido-principal.css">
    <link rel="stylesheet" href="css/cajas-contenido.css">
    <link rel="stylesheet" href="css/tabla.css">
    <link rel="stylesheet" href="css/botones.css">
    <link rel="stylesheet" href="css/contenedor-busqueda.css">
    <link rel="stylesheet" href="css/contenedor-pagina-principal.css">
    <link rel="stylesheet" href="css/tarjetas-dashboard.css">
    <link rel="stylesheet" href="css/grupo-inputs.css">
    <link rel="stylesheet" href="css/boton-login.css">
    <link rel="stylesheet" href="css/mensaje-error.css">
    <link rel="stylesheet" href="css/boton-logout.css">
    <link rel="stylesheet" href="css/contenedor-formulario.css">
    <link rel="stylesheet" href="css/mensaje.css">
    <link rel="stylesheet" href="css/formulario-busqueda.css">
    <link rel="stylesheet" href="css/boton-regreso.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
    <header>
        <div class="header-content">
            <button id="sidebarToggle" class="sidebar-toggle">&#9776;</button>
            <img src="https://enlinea.unapiquitos.edu.pe/unap/descargas/descargas/logo_UNAP_color.jpg" alt="UNAP">
            <button class="logout-button" onclick="openLogoutModal()">Cerrar Sesión</button>
        </div>
    </header>

    <div id="sidebar" class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <?php if ($_SESSION['rol'] == 'Administrador'): ?>
                    <li class="nav-item"><a class="nav-link" href="gestion_usuarios.php">Gestión de Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestion_unidades.php">Gestión de Unidades Móviles</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestion_personal.php">Gestión del Personal</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestion_horarios.php">Gestión de Horarios</a></li>
                    <li class="nav-item"><a class="nav-link" href="programacion_itinerarios.php">Programación de itinerarios</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestion_solicitudes.php">Gestión de Solicitudes de Alquiler</a></li>

                    <li class="nav-item"><a class="nav-link" href="inventario_refacciones.php">Inventario de Refacciones</a></li>
                    <li class="nav-item"><a class="nav-link" href="reporte_mantenimiento.php">Reporte de Mantenimiento</a></li>

                    <li class="nav-item"><a class="nav-link" href="registro_mantenimiento.php">Registro de Mantenimiento</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Reporte General</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <div id="logoutModal" class="modal fade">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">¿Estás seguro?</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <p>¿Realmente deseas cerrar sesión?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Cerrar Sesión</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        function openLogoutModal() {
            $('#logoutModal').modal('show');
            document.getElementById('confirmLogoutBtn').onclick = function () {
                window.location.href = 'logout.php';
            };
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
