<?php
require 'sesion.php';
require 'conexion.php';
pedirMetodo('GET');
$juegoId = filter_input(INPUT_GET, 'juegoId', FILTER_VALIDATE_INT);
if (!$juegoId || $juegoId < 1) responder(['error' => 'Elegí un juego válido'], 400);
$juego = $pdo->prepare('SELECT id FROM juegos WHERE id = ?');
$juego->execute([$juegoId]);
if (!$juego->fetch()) responder(['error' => 'Ese juego no existe'], 404);

$sql = "SELECT p.usuario_id AS usuarioId, u.alias, p.puntos
        FROM puntajes p JOIN usuarios u ON u.id = p.usuario_id
        WHERE p.juego_id = :juego
          AND NOT EXISTS (
            SELECT 1 FROM puntajes otro
            WHERE otro.juego_id = p.juego_id AND otro.usuario_id = p.usuario_id
              AND (otro.puntos > p.puntos
                OR (otro.puntos = p.puntos AND otro.creado_en < p.creado_en)
                OR (otro.puntos = p.puntos AND otro.creado_en = p.creado_en AND otro.id < p.id))
          )
        ORDER BY p.puntos DESC, p.creado_en ASC, p.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['juego' => $juegoId]);
$ranking = $stmt->fetchAll();
foreach ($ranking as &$fila) {
    $fila['usuarioId'] = (int) $fila['usuarioId'];
    $fila['puntos'] = (int) $fila['puntos'];
}
responder($ranking);
