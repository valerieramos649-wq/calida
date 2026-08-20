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

if (isset($_SESSION['registro_exitoso'])) {
    $success = $_SESSION['registro_exitoso'];
    unset($_SESSION['registro_exitoso']);
}

if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($inputUsuario === '' || $password === '') {
        $error = 'Completa todos los campos antes de continuar.';
    } else {
        $conexion = conexion();

        if (!$conexion) {
            $error = 'No se pudo conectar a la base de datos.';
        } else {
            $stmt = $conexion->prepare('SELECT * FROM usuario WHERE username = :username LIMIT 1');
            $stmt->execute(['username' => $inputUsuario]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['contraseña'])) {
                $_SESSION['usuario'] = [
                    'id' => (int) $user['id_usuario'],
                    'nombre' => $user['username'] ?? 'Usuario',
                    'rol' => $user['rol'] ?? 'Administrador'
                ];

                header('Location: dashboard.php');
                exit;
            }

            $error = 'Credenciales incorrectas. Verifica usuario y contraseña.';
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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h1>Joyería</h1>
                <p class="subtitulo">Inicia sesión para continuar</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alerta error">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="8" y1="4.5" x2="8" y2="9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="8" cy="11.5" r="0.8"/></svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success !== ''): ?>
                <div class="alerta ok">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M5 8.5L7 10.5L11 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label for="usuario">Usuario</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" id="usuario" name="usuario" placeholder="Escribe tu usuario" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="password" name="password" placeholder="Escribe tu contraseña" required>
                        <button type="button" class="toggle-password" data-target="password" aria-label="Mostrar contraseña">
                            <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <span>Iniciar sesión</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>

            <div class="login-footer">
                <span>¿No tienes cuenta?</span>
                <a href="register.php">Regístrate aquí</a>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function(button) {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                this.querySelector('.eye-open').style.display = isPassword ? 'none' : 'block';
                this.querySelector('.eye-closed').style.display = isPassword ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>