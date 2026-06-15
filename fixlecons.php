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

    // Afficher l'état actuel des leçons
    echo "<h2>État actuel des leçons :</h2><pre>";
    $rows = $pdo->query("SELECT id, titre, actif, cours_id FROM lecons ORDER BY cours_id")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
    echo "</pre>";

    // Corriger les leçons dont actif est NULL → mettre à 1
    $stmt = $pdo->prepare("UPDATE lecons SET actif = 1 WHERE actif IS NULL");
    $stmt->execute();
    echo "<h2 style='color:green'>✅ " . $stmt->rowCount() . " leçon(s) avec actif=NULL corrigée(s) à 1</h2>";

    // Afficher l'état après correction
    echo "<h2>État après correction :</h2><pre>";
    $rows2 = $pdo->query("SELECT id, titre, actif, cours_id FROM lecons ORDER BY cours_id")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows2);
    echo "</pre>";

    echo "<p style='color:red'><strong>Supprime ce fichier après utilisation !</strong></p>";
    echo "<p><a href='/'>Aller au site</a></p>";

} catch (Exception $e) {
    echo "<h1 style='color:red'>❌ Erreur</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
