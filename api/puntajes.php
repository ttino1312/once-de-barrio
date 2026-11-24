<?php
require 'sesion.php';
require 'conexion.php';
require 'avisar_ranking.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    pedirAdmin();
    $stmt = $pdo->query('SELECT p.id, u.alias, p.puntos, p.creado_en FROM puntajes p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.creado_en DESC, p.id DESC LIMIT 50');
    responder($stmt->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    pedirAdmin();
    pedirCsrf();
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id || $id < 1) responder(['error' => 'Indicá un resultado válido'], 400);
    $stmt = $pdo->prepare('SELECT juego_id FROM puntajes WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    if (!$fila) responder(['error' => 'No existe ese resultado'], 404);
    $pdo->prepare('DELETE FROM puntajes WHERE id = ?')->execute([$id]);
    avisarRanking((int) $fila['juego_id']);
    responder(['ok' => true]);
}

responder(['error' => 'Los partidos se juegan desde el Mundial; no se aceptan goles enviados por el navegador'], 410);
