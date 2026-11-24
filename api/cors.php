<?php
// Solo el juego y el visor pueden leer la API desde otro origen.
$origen = $_SERVER['HTTP_ORIGIN'] ?? '';
$permitidos = ['http://localhost:5173', 'http://127.0.0.1:5173', 'http://localhost:5174', 'http://127.0.0.1:5174'];
$extra = getenv('FRONTEND_ORIGIN');
if ($extra) $permitidos[] = rtrim($extra, '/');
if ($origen && in_array($origen, $permitidos, true)) {
    header('Access-Control-Allow-Origin: ' . $origen);
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}
