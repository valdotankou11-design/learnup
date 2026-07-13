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

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS parametres (
            cle    VARCHAR(64)  NOT NULL PRIMARY KEY,
            valeur VARCHAR(255) NOT NULL
        )
    ");

    // Valeur par défaut : certification automatique DÉSACTIVÉE
    $pdo->prepare("INSERT IGNORE INTO parametres (cle, valeur) VALUES ('certification_auto_module', '0')")
        ->execute();

    echo "<h1 style='color:green'>✅ Table parametres créée / vérifiée avec succès !</h1>";
    echo "<p>Certification automatique des modules : désactivée par défaut (modifiable dans Admin → Certificats).</p>";
    echo "<p>Vous pouvez maintenant supprimer ce fichier install_parametres.php</p>";
    echo "<p><a href='/'>Aller au site</a></p>";
} catch (Exception $e) {
    echo "<h1 style='color:red'>❌ Erreur</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
