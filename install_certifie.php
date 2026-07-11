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

    // Vérifie si la colonne existe déjà (pour pouvoir rafraîchir la page sans erreur)
    $check = $pdo->query("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'certifie'
    ")->fetchColumn();

    if ($check > 0) {
        echo "<h1 style='color:orange'>⚠️ Déjà installé</h1>";
        echo "<p>La colonne <code>certifie</code> existe déjà sur la table <code>users</code>. Rien à faire.</p>";
        echo "<p>Vous pouvez supprimer ce fichier install_certifie.php</p>";
        echo "<p><a href='/'>Aller au site</a></p>";
        exit;
    }

    $pdo->exec("
        ALTER TABLE users
          ADD COLUMN certifie    TINYINT(1) NOT NULL DEFAULT 0 AFTER role,
          ADD COLUMN certifie_le DATETIME DEFAULT NULL         AFTER certifie
    ");

    echo "<h1 style='color:green'>✅ Colonnes certifie / certifie_le ajoutées avec succès !</h1>";
    echo "<p>Vous pouvez maintenant supprimer ce fichier install_certifie.php</p>";
    echo "<p><a href='/'>Aller au site</a></p>";
} catch (Exception $e) {
    echo "<h1 style='color:red'>❌ Erreur</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
