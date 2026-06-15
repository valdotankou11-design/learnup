<?php
// Fichier de test — à supprimer après utilisation !
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Compter les admins actuels
    $nbAdmins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    echo "<h2>Nombre d'admins en base : <strong>$nbAdmins</strong></h2>";

    // Simuler la vérification sans rien supprimer
    if ($nbAdmins <= 1) {
        echo "<h2 style='color:green'>✅ Protection OK — La suppression du dernier admin serait bloquée.</h2>";
    } else {
        echo "<h2 style='color:orange'>⚠️ Il y a $nbAdmins admins — la suppression d'un seul serait autorisée.</h2>";
    }

    // Afficher les comptes admin
    echo "<h2>Comptes admin :</h2><pre>";
    $admins = $pdo->query("SELECT id, nom, prenom, email FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_ASSOC);
    print_r($admins);
    echo "</pre>";

    echo "<p style='color:red'><strong>Supprime ce fichier après utilisation !</strong></p>";
    echo "<p><a href='/'>Aller au site</a></p>";

} catch (Exception $e) {
    echo "<h1 style='color:red'>❌ Erreur</h1><p>" . $e->getMessage() . "</p>";
}
