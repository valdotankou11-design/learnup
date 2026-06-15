<?php
// Fichier de correction BD — à supprimer après utilisation !
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Afficher l'état actuel
    echo "<h2>État actuel des modules :</h2><pre>";
    $rows = $pdo->query("SELECT id, titre, actif, promoteur_id FROM modules")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
    echo "</pre>";

    echo "<h2>État actuel des users (promoteurs) :</h2><pre>";
    $rows2 = $pdo->query("SELECT id, nom, prenom, email, role FROM users WHERE role='promoteur'")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows2);
    echo "</pre>";

    // Corriger le promoteur_id de tous les modules
    $stmt = $pdo->prepare("
        UPDATE modules
        SET promoteur_id = (SELECT id FROM users WHERE email = 'promoteur@learnup.cm' LIMIT 1)
        WHERE titre = 'Développement Web'
    ");
    $stmt->execute();
    $nb = $stmt->rowCount();

    echo "<h2 style='color:green'>✅ $nb module(s) corrigé(s) !</h2>";

    // Afficher l'état après correction
    echo "<h2>État après correction :</h2><pre>";
    $rows3 = $pdo->query("SELECT id, titre, actif, promoteur_id FROM modules")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows3);
    echo "</pre>";

    echo "<p style='color:red'><strong>Supprime ce fichier après utilisation !</strong></p>";
    echo "<p><a href='/'>Aller au site</a></p>";

} catch (Exception $e) {
    echo "<h1 style='color:red'>❌ Erreur</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
