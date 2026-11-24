<?php
// El amistoso genera entre 0 y 4 goles propios y entre 0 y 3 del rival.
function puntosPartido($favor, $contra) {
    if (!is_int($favor) || !is_int($contra) || $favor < 0 || $favor > 4 || $contra < 0 || $contra > 3) {
        return null;
    }
    return $favor + ($favor > $contra ? 3 : ($favor === $contra ? 1 : 0));
}
