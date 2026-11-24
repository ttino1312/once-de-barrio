<?php
require 'sesion.php';
require 'conexion.php';
require 'reglas_torneo.php';
pedirMetodo('GET');
usuarioActual();

$partidaId = filter_input(INPUT_GET, 'partida_id', FILTER_VALIDATE_INT);
if (!$partidaId || $partidaId < 1) responder(['error' => 'Falta una partida válida'], 400);
comprobarDueno($pdo, $partidaId);

$stmt = $pdo->prepare('SELECT COUNT(*) AS cantidad, AVG(pj.valoracion) AS promedio FROM equipo_partida ep JOIN plantel_jugadores pj ON pj.id = ep.plantel_jugador_id WHERE ep.partida_id = ?');
$stmt->execute([$partidaId]);
$equipo = $stmt->fetch();
$rondas = rondasMundial();
$nombres = array_column($rondas, 'nombre');
$marcas = implode(',', array_fill(0, count($nombres), '?'));
$stmt = $pdo->prepare("SELECT ronda, rival, goles_favor, goles_contra FROM resultados WHERE partida_id = ? AND ronda IN ($marcas) ORDER BY id");
$stmt->execute([$partidaId, ...$nombres]);
$jugados = $stmt->fetchAll();
foreach ($jugados as &$partido) {
    $partido['goles_favor'] = (int) $partido['goles_favor'];
    $partido['goles_contra'] = (int) $partido['goles_contra'];
}
unset($partido);
$stmt = $pdo->prepare('SELECT puntos FROM puntajes WHERE partida_id = ?');
$stmt->execute([$partidaId]);
$puntaje = $stmt->fetchColumn();
$esViejo = $puntaje !== false && !$jugados;

responder([
    'rondas' => array_map(fn($ronda) => ['nombre' => $ronda['nombre'], 'rival' => $ronda['rival']], $rondas),
    'jugados' => $jugados,
    'siguiente' => $esViejo ? null : siguienteRonda($jugados),
    'estado' => $esViejo ? 'amistoso_anterior' : ((int) $equipo['cantidad'] < 11 ? 'armando' : estadoMundial($jugados)),
    'valoracion' => $equipo['promedio'] === null ? null : round((float) $equipo['promedio'], 1),
    'puntosGrupo' => puntosGrupo($jugados),
    'puntos' => $puntaje === false ? null : (int) $puntaje
]);
