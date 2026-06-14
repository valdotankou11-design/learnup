<?php
/**
 * LearnUp — config/db.php
 * Configuration de la base de données et utilitaires session
 * Compatible Vercel (serverless PHP)
 */

// ── Paramètres DB ────────────────────────────────────────────────────────────
define('DB_HOST',    getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: 'localhost');
define('DB_NAME',    getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'learnup');
define('DB_USER',    getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root');
define('DB_PASS',    getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '');
define('DB_PORT',    getenv('MYSQLPORT')     ?: getenv('MYSQL_PORT')     ?: '3306');
define('DB_CHARSET', 'utf8mb4');

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
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['succes' => false, 'message' => 'Erreur de connexion à la base de données.']));
        }
    }
    return $pdo;
}

// ── Session (compatible Vercel /tmp) ─────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', '/tmp');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
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
        header('Location: /index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if ($role && $_SESSION['user_role'] !== $role) {
        header('Location: /dashboard/' . $_SESSION['user_role'] . '.php');
        exit;
    }
}

// ── Helpers ──────────────────────────────────────────────────────────────────
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

// ── Upload fichier (désactivé sur Vercel — système de fichiers éphémère) ─────
function uploaderFichier(array $fichier, string $type): ?string {
    $dossiers   = ['pdf' => '/uploads/pdfs/', 'video' => '/uploads/videos/'];
    $extensions = ['pdf' => ['pdf'], 'video' => ['mp4', 'webm', 'ogg', 'avi']];

    if ($fichier['error'] !== UPLOAD_ERR_OK) return null;

    $ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extensions[$type] ?? [])) return null;

    $nom    = genererCode(16) . '_' . time() . '.' . $ext;
    $chemin = __DIR__ . '/..' . $dossiers[$type] . $nom;

    if (!move_uploaded_file($fichier['tmp_name'], $chemin)) return null;

    return $dossiers[$type] . $nom;
}
