<?php
require __DIR__ . '/../api/reglas.php';

function comprobar($condicion, $mensaje) {
    if (!$condicion) {
        throw new Exception($mensaje);
    }
}

comprobar(puntosPartido(4, 2) === 7, 'Una victoria con cuatro goles vale siete puntos');
comprobar(puntosPartido(0, 0) === 1, 'Un empate sin goles vale un punto');
comprobar(puntosPartido(1, 3) === 1, 'Una derrota suma solamente los goles a favor');
comprobar(puntosPartido(5, 0) === null, 'Cinco goles propios están fuera del rango del juego');
comprobar(puntosPartido(-1, 0) === null, 'No se aceptan goles negativos');
echo "Reglas: OK\n";
