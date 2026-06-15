<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// Servir les fichiers statiques directement
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Router vers index.php par défaut
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require __DIR__ . '/index.php';
