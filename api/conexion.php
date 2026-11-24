<?php
$config = is_file(__DIR__ . '/config.local.php')
    ? require __DIR__ . '/config.local.php'
    : [];

$host = $config['host'] ?? (getenv('DB_HOST') ?: 'localhost');
$db = $config['database'] ?? (getenv('DB_NAME') ?: 'once_de_barrio_conectado');
$user = $config['user'] ?? (getenv('DB_USER') ?: 'root');
$pass = $config['password'] ?? (getenv('DB_PASSWORD') ?: '');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo conectar a la base']);
    exit;
}
