<?php
require 'sesion.php';
require 'conexion.php';
pedirMetodo('POST');
usuarioActual();
pedirCsrf();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$partidaId = (int) ($datos['partida_id'] ?? 0);
$plantelJugadorId = (int) ($datos['plantel_jugador_id'] ?? 0);
$slot = trim($datos['slot'] ?? '');
$slotsValidos = ['ARQ', 'LD', 'DFC1', 'DFC2', 'LI', 'MC1', 'MC2', 'MCO', 'ED', 'DC', 'EI'];

if ($partidaId <= 0 || $plantelJugadorId <= 0 || !in_array($slot, $slotsValidos, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Los datos del jugador o del lugar no son válidos']);
    exit;
}
comprobarDueno($pdo, $partidaId);
$stmt = $pdo->prepare('SELECT COUNT(*) FROM resultados WHERE partida_id = ?');
$stmt->execute([$partidaId]);
if ((int) $stmt->fetchColumn() > 0) responder(['error' => 'El once queda fijo cuando empieza el Mundial'], 409);

try {
    $sql = "INSERT INTO equipo_partida (partida_id, plantel_jugador_id, slot)
            VALUES (:partida, :jugador, :slot)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'partida' => $partidaId,
        'jugador' => $plantelJugadorId,
        'slot' => $slot
    ]);

    echo json_encode(['ok' => true]);
} catch (PDOException $error) {
    http_response_code(409);
    echo json_encode(['error' => 'Ese lugar está ocupado o el jugador ya fue elegido']);
}
