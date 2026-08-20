<?php
session_start();
require_once 'conexion.php';

$error = '';
$success = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: Login.php');
    exit;
}

// Si ya hay una sesión activa, redirigir al dashboard
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($inputUsuario === '' || $password === '') {
        $error = 'Completa todos los campos.';
    } else {
        $conexion = conexion();

        if ($conexion) {
            $stmt = $conexion->prepare('SELECT * FROM usuarios WHERE nombre = :input LIMIT 1');
            $stmt->execute(['input' => $inputUsuario]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['usuario'] = [
                    'nombre' => $user['nombre'] ?? 'Usuario'
                ];

                $success = 'Inicio de sesión correcto.';
                header('Location: dashboard.php');
                exit;
            }

            $error = 'Credenciales incorrectas.';
        } else {
            $usuarioDemo = 'admin';
            $passwordDemo = '123456';

            if ($inputUsuario === $usuarioDemo && $password === $passwordDemo) {
                $_SESSION['usuario'] = [
                    'nombre' => 'Administrador'
                ];

                $success = 'Inicio de sesión correcto.';
                header('Location: dashboard.php');
                exit;
            }

            $error = 'Credenciales incorrectas.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joyería | Inicio de Sesión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="contenedor">
        <?php if (isset($_SESSION['usuario'])): ?>
            <div class="panel">
                <h2>Bienvenido</h2>
                <p>Has iniciado sesión como <strong><?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></strong></p>
                <a class="btn-salir" href="?logout=1">Cerrar sesión</a>
            </div>
        <?php else: ?>
            <h1>Joyería</h1>
            <p class="subtitulo">Inicia sesión para continuar</p>

            <?php if ($error !== ''): ?>
                <div class="alerta error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success !== ''): ?>
                <div class="alerta ok"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="usuario">Nombre de usuario</label>
                <input type="text" id="usuario" name="usuario" placeholder="Nombre de usuario" required>

                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="********" required>

                <button type="submit">Entrar</button>
            </form>

            <p class="info">Credenciales de prueba: admin / 123456</p>
        <?php endif; ?>
    </div>
</body>
</html>