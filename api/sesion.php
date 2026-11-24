<?php
require_once __DIR__ . '/cors.php';

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('once_barrio_sesion');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

function responder($datos, $estado = 200) {
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function leerJson() {
    $datos = json_decode(file_get_contents('php://input'), true);
    if (!is_array($datos)) responder(['error' => 'Enviá un JSON válido'], 400);
    return $datos;
}

function pedirMetodo($metodo) {
    if ($_SERVER['REQUEST_METHOD'] !== $metodo) responder(['error' => 'Método no permitido'], 405);
}

function usuarioActual() {
    if (empty($_SESSION['usuario_id'])) responder(['error' => 'Iniciá sesión primero'], 401);
    return (int) $_SESSION['usuario_id'];
}

function pedirCsrf() {
    $recibido = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $recibido)) {
        responder(['error' => 'La sesión venció. Recargá la página.'], 403);
    }
}

function pedirAdmin() {
    usuarioActual();
    if (($_SESSION['rol'] ?? '') !== 'admin') responder(['error' => 'Solo puede hacerlo un administrador'], 403);
}

function comprobarDueno($pdo, $partidaId) {
    $stmt = $pdo->prepare('SELECT id FROM partidas WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$partidaId, usuarioActual()]);
    if (!$stmt->fetch()) responder(['error' => 'No encontré una partida tuya con ese número'], 404);
}
