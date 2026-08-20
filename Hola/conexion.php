<?php

function conexion()
{
    $host = 'localhost';
    $database = 'Joyeria';
    $user = 'root';
    $password = '';

    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;
    } catch (PDOException $e) {
        error_log('Error de conexión: ' . $e->getMessage());
        return null;
    }
}
?>
