<?php
require 'sesion.php';
require 'conexion.php';
require 'reglas_torneo.php';
require 'avisar_ranking.php';
pedirMetodo('POST');
$usuarioId = usuarioActual();
pedirCsrf();
$datos = leerJson();
$partidaId = filter_var($datos['partida_id'] ?? null, FILTER_VALIDATE_INT);
$indice = filter_var($datos['indice'] ?? null, FILTER_VALIDATE_INT);
if (!$partidaId || $indice === false || $indice === null || $indice < 0 || $indice > 6) responder(['error' => 'La ronda no es válida'], 400);

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT usuario_id, juego_id FROM partidas WHERE id = ? FOR UPDATE');
    $stmt->execute([$partidaId]);
    $partida = $stmt->fetch();
    if (!$partida || (int) $partida['usuario_id'] !== $usuarioId) responder(['error' => 'No encontré una partida tuya con ese número'], 404);

    $stmt = $pdo->prepare('SELECT COUNT(*) AS cantidad, AVG(pj.valoracion) AS promedio FROM equipo_partida ep JOIN plantel_jugadores pj ON pj.id = ep.plantel_jugador_id WHERE ep.partida_id = ?');
    $stmt->execute([$partidaId]);
    $equipo = $stmt->fetch();
    if ((int) $equipo['cantidad'] !== 11) responder(['error' => 'Completá el once antes de jugar'], 400);

    $stmt = $pdo->prepare('SELECT id FROM puntajes WHERE partida_id = ?');
    $stmt->execute([$partidaId]);
    if ($stmt->fetch()) responder(['error' => 'Esta partida ya terminó'], 409);

    $rondas = rondasMundial();
    $nombres = array_column($rondas, 'nombre');
    $marcas = implode(',', array_fill(0, count($nombres), '?'));
    $stmt = $pdo->prepare("SELECT ronda, goles_favor, goles_contra FROM resultados WHERE partida_id = ? AND ronda IN ($marcas) ORDER BY id");
    $stmt->execute([$partidaId, ...$nombres]);
    $jugados = $stmt->fetchAll();
    $ronda = siguienteRonda($jugados);
    if (!$ronda || $ronda['indice'] !== $indice) responder(['error' => 'Esa ronda ya se jugó o el Mundial terminó'], 409);

    $resultado = simularPartido((float) $equipo['promedio'], $ronda);
    $stmt = $pdo->prepare('INSERT INTO resultados (partida_id, ronda, rival, goles_favor, goles_contra) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$partidaId, $ronda['nombre'], $ronda['rival'], $resultado['goles_favor'], $resultado['goles_contra']]);
    $jugados[] = $resultado;
    $termino = siguienteRonda($jugados) === null;
    $puntos = null;
    if ($termino) {
        $puntos = puntosMundial($jugados);
        $stmt = $pdo->prepare('INSERT INTO puntajes (partida_id, usuario_id, juego_id, puntos) VALUES (?, ?, ?, ?)');
        $stmt->execute([$partidaId, $usuarioId, (int) $partida['juego_id'], $puntos]);
    }
    $pdo->commit();
    if ($termino) avisarRanking((int) $partida['juego_id']);
    responder(['ronda' => $ronda['nombre'], 'rival' => $ronda['rival'], ...$resultado, 'termino' => $termino, 'puntos' => $puntos], 201);
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    responder(['error' => 'No se pudo guardar el partido'], 500);
}
