<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// Servir les fichiers statiques directement (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Router vers le bon fichier PHP selon l'URI
if (str_starts_with($uri, '/api/admin')) {
    require __DIR__ . '/api/admin.php';
} elseif (str_starts_with($uri, '/api/')) {
    require __DIR__ . '/api/index.php';
} elseif (str_starts_with($uri, '/dashboard/admin')) {
    require __DIR__ . '/dashboard/admin.php';
} elseif (str_starts_with($uri, '/dashboard/enseignant')) {
    require __DIR__ . '/dashboard/enseignant.php';
} elseif (str_starts_with($uri, '/dashboard/etudiant')) {
    require __DIR__ . '/dashboard/etudiant.php';
} elseif (str_starts_with($uri, '/dashboard/promoteur')) {
    require __DIR__ . '/dashboard/promoteur.php';
} elseif (str_starts_with($uri, '/certificat')) {
    require __DIR__ . '/certificat.php';
} else {
    require __DIR__ . '/index.php';
}
