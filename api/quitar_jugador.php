<?php
require 'sesion.php';
require 'conexion.php';
pedirMetodo('POST');
usuarioActual();
pedirCsrf();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$partidaId = (int) ($datos['partida_id'] ?? 0);
$slot = trim($datos['slot'] ?? '');
$slotsValidos = ['ARQ', 'LD', 'DFC1', 'DFC2', 'LI', 'MC1', 'MC2', 'MCO', 'ED', 'DC', 'EI'];

if ($partidaId <= 0 || !in_array($slot, $slotsValidos, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'La partida o el lugar no son válidos']);
    exit;
}
comprobarDueno($pdo, $partidaId);
$stmt = $pdo->prepare('SELECT COUNT(*) FROM resultados WHERE partida_id = ?');
$stmt->execute([$partidaId]);
if ((int) $stmt->fetchColumn() > 0) responder(['error' => 'El once queda fijo cuando empieza el Mundial'], 409);

$sql = 'DELETE FROM equipo_partida WHERE partida_id = :partida AND slot = :slot';
$stmt = $pdo->prepare($sql);
$stmt->execute(['partida' => $partidaId, 'slot' => $slot]);

echo json_encode(['ok' => true]);
