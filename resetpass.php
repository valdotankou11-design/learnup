<?php
require_once __DIR__ . '/config/db.php';

$nouveauHash = password_hash('learnup2026', PASSWORD_DEFAULT);

$db = getDB();
$stmt = $db->prepare("UPDATE users SET mot_de_passe = ?");
$stmt->execute([$nouveauHash]);

echo "<h1 style='color:green'>✅ Mots de passe réinitialisés !</h1>";
echo "<p>Hash généré : " . $nouveauHash . "</p>";
echo "<p>Tous les comptes ont maintenant le mot de passe : <strong>learnup2026</strong></p>";
echo "<p><a href='/'>Aller au site</a></p>";
