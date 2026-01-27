<?php

// Start sesji jeśli nie jest już zastarted
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);


Routing::run($path);