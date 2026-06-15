<?php
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

echo "<pre>";
echo "HOST: $host\n";
echo "PORT: $port\n";
echo "DB: $db\n";
echo "USER: $user\n";
echo "PASS: " . (empty($pass) ? '❌ VIDE' : '✅ défini') . "\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ]);
    echo "\n✅ Connexion réussie !\n";
} catch (Exception $e) {
    echo "\n❌ Erreur : " . $e->getMessage() . "\n";
}
echo "</pre>";
