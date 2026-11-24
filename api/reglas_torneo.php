<?php

function rondasMundial() {
    return [
        ['nombre' => 'Grupo 1', 'rival' => 'Ecuador', 'fuerza' => 84],
        ['nombre' => 'Grupo 2', 'rival' => 'Ghana', 'fuerza' => 86],
        ['nombre' => 'Grupo 3', 'rival' => 'Croacia', 'fuerza' => 88],
        ['nombre' => 'Octavos', 'rival' => 'Dinamarca', 'fuerza' => 86],
        ['nombre' => 'Cuartos', 'rival' => 'Países Bajos', 'fuerza' => 88],
        ['nombre' => 'Semifinal', 'rival' => 'Francia', 'fuerza' => 90],
        ['nombre' => 'Final', 'rival' => 'Brasil', 'fuerza' => 92]
    ];
}

function puntosGrupo($resultados) {
    $puntos = 0;
    foreach (array_slice($resultados, 0, 3) as $partido) {
        if ($partido['goles_favor'] > $partido['goles_contra']) $puntos += 3;
        elseif ($partido['goles_favor'] == $partido['goles_contra']) $puntos++;
    }
    return $puntos;
}

function siguienteRonda($resultados) {
    $jugados = count($resultados);
    $rondas = rondasMundial();
    if ($jugados >= count($rondas)) return null;
    if ($jugados >= 3 && puntosGrupo($resultados) < 4) return null;
    if ($jugados > 3 && $resultados[$jugados - 1]['goles_favor'] <= $resultados[$jugados - 1]['goles_contra']) return null;
    return ['indice' => $jugados, ...$rondas[$jugados]];
}

function estadoMundial($resultados) {
    if (siguienteRonda($resultados)) return 'en_curso';
    return count($resultados) === 7 && $resultados[6]['goles_favor'] > $resultados[6]['goles_contra'] ? 'campeon' : 'eliminado';
}

function puntosMundial($resultados) {
    $puntos = 0;
    foreach ($resultados as $partido) {
        if ($partido['goles_favor'] > $partido['goles_contra']) $puntos += 3;
        elseif ($partido['goles_favor'] == $partido['goles_contra']) $puntos++;
    }
    return $puntos + (estadoMundial($resultados) === 'campeon' ? 10 : 0);
}

function probabilidadVictoria($calidad, $fuerzaRival) {
    return max(0.12, min(0.88, 0.5 + ($calidad - $fuerzaRival) * 0.055));
}

function simularPartido($calidad, $ronda, $azar = null) {
    $suerte = $azar ?? random_int(0, 999) / 1000;
    $chance = probabilidadVictoria($calidad, $ronda['fuerza']);
    $esGrupo = $ronda['indice'] < 3;
    $empate = $esGrupo && $suerte >= $chance * 0.82 && $suerte < $chance * 0.82 + 0.18;
    $gano = $suerte < ($esGrupo ? $chance * 0.82 : $chance);
    if ($empate) {
        $goles = random_int(0, 2);
        return ['goles_favor' => $goles, 'goles_contra' => $goles];
    }
    $ganador = random_int(1, 3);
    $perdedor = random_int(0, $ganador - 1);
    return $gano
        ? ['goles_favor' => $ganador, 'goles_contra' => $perdedor]
        : ['goles_favor' => $perdedor, 'goles_contra' => $ganador];
}
