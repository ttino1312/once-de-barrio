<?php

function avisarRanking($juegoId) {
    $opciones = ['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode(['tipo' => 'rankingActualizado', 'juegoId' => $juegoId]),
        'timeout' => 0.4,
        'ignore_errors' => true
    ]];
    @file_get_contents('http://127.0.0.1:4101/avisar', false, stream_context_create($opciones));
}
