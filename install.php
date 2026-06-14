<?php
// Fichier d'installation — à supprimer après utilisation !
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents(__DIR__ . '/learnup.sql');
    
    // Supprimer les lignes CREATE DATABASE et USE
    $sql = preg_replace('/CREATE DATABASE.*?;\n/i', '', $sql);
    $sql = preg_replace('/USE .*?;\n/i', '', $sql);
    
    // Exécuter requête par requête
    $pdo->exec($sql);
    
    echo "<h1 style='color:green'>✅ Base de données importée avec succès !</h1>";
    echo "<p>Vous pouvez maintenant supprimer ce fichier install.php</p>";
    echo "<p><a href='/'>Aller au site</a></p>";
} catch (Exception $e) {
    echo "<h1 style='color:red'>❌ Erreur</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
