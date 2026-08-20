<?php
session_start();

// Si no hay sesión, volver al login
if (!isset($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Usuario</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="contenedor">
        <div class="panel">
            <h2>Dashboard</h2>
            <p>Has iniciado sesión como <strong><?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></strong></p>
            <a class="btn-salir" href="Login.php?logout=1">Cerrar sesión</a>
        </div>
    </div>
</body>
</html>
