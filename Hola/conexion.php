<?php

function conexion()
{
    $host = 'localhost';
    $database = 'joyeria';
    $user = 'root';
    $password = '';

    $dsn = "mysql:host={$host};dbname={$database}";

    try {
        return new PDO($dsn, $user, $password);
    } catch (PDOException $e) {
        return null;
    }
}
?>
