<?php
require 'sesion.php';
pedirMetodo('GET');
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
responder(['csrf' => $_SESSION['csrf']]);
