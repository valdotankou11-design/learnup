<?php
/**
 * LearnUp — config/db.php
 * Configuration de la base de données et utilitaires session
 */

// ── Paramètres DB ────────────────────────────────────────────────────────────
define('DB_HOST',    getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: 'localhost');
define('DB_NAME',    getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'learnup');
define('DB_USER',    getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root');
define('DB_PASS',    getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '');
define('DB_PORT',    getenv('MYSQLPORT')     ?: getenv('MYSQL_PORT')     ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// ── Cloudinary ───────────────────────────────────────────────────────────────
define('CLOUDINARY_CLOUD',  'dlbskcpkg');
define('CLOUDINARY_KEY',    '376654279626337');
define('CLOUDINARY_SECRET', 'WV6oFRDI8jCUWZD6L4Iyg3p7_OI');

// ── Connexion PDO ────────────────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset='.DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['succes' => false, 'message' => 'Erreur de connexion à la base de données.']));
        }
    }
    return $pdo;
}

// ── Session ───────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', '86400');
    session_set_cookie_params([
        'lifetime' => 86400,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function utilisateurConnecte(): bool {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function utilisateurCourant(): ?array {
    if (!utilisateurConnecte()) return null;
    return [
        'id'     => $_SESSION['user_id'],
        'nom'    => $_SESSION['user_nom'],
        'prenom' => $_SESSION['user_prenom'],
        'email'  => $_SESSION['user_email'],
        'role'   => $_SESSION['user_role'],
        'avatar' => $_SESSION['user_avatar'] ?? null,
    ];
}

function exigerConnexion(string $role = ''): void {
    if (!utilisateurConnecte()) {
        repondreJSON(['succes' => false, 'message' => 'Non connecté.'], 401);
    }
    if ($role && $_SESSION['user_role'] !== $role) {
        repondreJSON(['succes' => false, 'message' => 'Accès refusé.'], 403);
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function repondreJSON(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function nettoyer(string $valeur): string {
    return htmlspecialchars(trim($valeur), ENT_QUOTES, 'UTF-8');
}

function genererCode(int $longueur = 32): string {
    return bin2hex(random_bytes($longueur / 2));
}

// ── Upload vers Cloudinary ────────────────────────────────────────────────────
function uploaderFichier(array $fichier, string $type): ?string {
    $extensions = [
        'pdf'   => ['pdf'],
        'video' => ['mp4', 'webm', 'ogg', 'avi', 'mov'],
    ];

    if ($fichier['error'] !== UPLOAD_ERR_OK) return null;

    $ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
    // Accepter aussi sans extension ou extension inconnue pour PDF
    if ($type === 'pdf' && !$ext) $ext = 'pdf';
    if (!in_array($ext, $extensions[$type] ?? [])) {
        // Vérifier le type MIME comme fallback
        $mime = mime_content_type($fichier['tmp_name']);
        if ($type === 'pdf' && $mime !== 'application/pdf') return null;
        if ($type === 'video' && !str_starts_with($mime, 'video/')) return null;
    }

    $timestamp    = time();
    $publicId     = 'learnup/' . $type . 's/' . genererCode(16) . '_' . $timestamp;
    $resourceType = ($type === 'pdf') ? 'raw' : 'video';

    // Signature
    $paramsToSign = ['public_id' => $publicId, 'timestamp' => $timestamp];
    ksort($paramsToSign);
    $strToSign = http_build_query($paramsToSign) . CLOUDINARY_SECRET;
    $signature = sha1($strToSign);

    // Upload via cURL
    $postFields = [
        'file'      => new CURLFile($fichier['tmp_name'], $fichier['type'], $fichier['name']),
        'public_id' => $publicId,
        'timestamp' => $timestamp,
        'api_key'   => CLOUDINARY_KEY,
        'signature' => $signature,
    ];

    $url = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD . '/' . $resourceType . '/upload';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response) return null;

    $data = json_decode($response, true);
    // Cloudinary retourne 200 ou 201
    if (isset($data['secure_url'])) return $data['secure_url'];
    if (isset($data['url'])) return $data['url'];
    return null;
}
