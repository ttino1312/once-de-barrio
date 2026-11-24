<?php
require __DIR__ . '/../api/reglas_torneo.php';

function comprobar($condicion, $mensaje) {
    if (!$condicion) throw new Exception($mensaje);
}

$victorias = array_fill(0, 7, ['goles_favor' => 2, 'goles_contra' => 0]);
$eliminado = array_slice($victorias, 0, 3);
$eliminado[] = ['goles_favor' => 0, 'goles_contra' => 1];
comprobar(count(rondasMundial()) === 7, 'El Mundial tiene siete partidos');
comprobar(siguienteRonda([])['nombre'] === 'Grupo 1', 'Se empieza por grupos');
comprobar(siguienteRonda([['goles_favor'=>1,'goles_contra'=>0], ['goles_favor'=>1,'goles_contra'=>1], ['goles_favor'=>0,'goles_contra'=>1]])['nombre'] === 'Octavos', 'Cuatro puntos clasifican');
comprobar(siguienteRonda([['goles_favor'=>0,'goles_contra'=>1], ['goles_favor'=>0,'goles_contra'=>1], ['goles_favor'=>0,'goles_contra'=>1]]) === null, 'Sin puntos se termina');
comprobar(estadoMundial($victorias) === 'campeon', 'La final ganada da el título');
comprobar(estadoMundial($eliminado) === 'eliminado', 'Perder octavos elimina');
comprobar(puntosMundial($victorias) === 31, 'Siete triunfos y título suman 31');
comprobar(puntosMundial($eliminado) === 9, 'La derrota no suma');
comprobar(probabilidadVictoria(92, 84) > probabilidadVictoria(84, 92), 'Un equipo mejor tiene más chances');
$triunfo = simularPartido(92, ['indice'=>3,'fuerza'=>86], 0);
$derrota = simularPartido(84, ['indice'=>3,'fuerza'=>92], 0.99);
comprobar($triunfo['goles_favor'] > $triunfo['goles_contra'], 'Una suerte favorable da una victoria');
comprobar($derrota['goles_favor'] < $derrota['goles_contra'], 'Una suerte desfavorable da una derrota');
echo "Torneo: OK\n";
