<?php
// debug_suggestions.php — à supprimer après !
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h2>Contenu de suggestions_modules :</h2><pre>";
$rows = $pdo->query("SELECT * FROM suggestions_modules")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

echo "</pre><h2>JOIN avec users :</h2><pre>";
$rows2 = $pdo->query("
    SELECT s.*, u.nom, u.prenom, u.role
    FROM suggestions_modules s
    JOIN users u ON u.id = s.enseignant_id
")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows2);
echo "</pre>";
