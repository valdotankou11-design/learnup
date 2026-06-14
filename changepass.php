<?php
require_once __DIR__ . '/config/db.php';

$nouveauMotDePasse = '22joelvaldo19010708'; // ← Remplacez ici

$hash = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);
$db = getDB();
$stmt = $db->prepare("UPDATE users SET mot_de_passe = ? WHERE email = 'admin@learnup.cm'");
$stmt->execute([$hash]);

echo "<h1 style='color:green'>✅ Mot de passe admin changé avec succès !</h1>";
echo "<p><a href='/'>Aller au site</a></p>";
