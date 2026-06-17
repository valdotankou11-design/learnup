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
        CREATE TABLE IF NOT EXISTS suggestions_modules (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            enseignant_id   INT NOT NULL,
            titre           VARCHAR(200) NOT NULL,
            description     TEXT,
            justification   TEXT,
            statut          ENUM('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
            commentaire     TEXT DEFAULT NULL,
            cree_le         DATETIME DEFAULT CURRENT_TIMESTAMP,
            traite_le       DATETIME DEFAULT NULL,
            FOREIGN KEY (enseignant_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    echo "<h1 style='color:green'>✅ Table suggestions_modules créée avec succès !</h1>";
    echo "<p>Vous pouvez maintenant supprimer ce fichier install_suggestions.php</p>";
    echo "<p><a href='/'>Aller au site</a></p>";
} catch (Exception $e) {
    echo "<h1 style='color:red'>❌ Erreur</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
