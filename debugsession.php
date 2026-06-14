<?php
require_once __DIR__ . '/config/db.php';

echo "<h2>Session :</h2><pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Cours dans la BD :</h2><pre>";
$db = getDB();
$cours = $db->query("
    SELECT c.id, c.titre, c.actif, m.titre as module, m.actif as module_actif 
    FROM cours c 
    JOIN modules m ON m.id = c.module_id
")->fetchAll();
print_r($cours);
echo "</pre>";

echo "<h2>Résultat de cours_disponibles :</h2><pre>";
$uid = $_SESSION['user_id'] ?? 0;
echo "user_id en session : $uid\n";
$stmt = $db->prepare('
    SELECT c.*, m.titre AS module_titre,
           CONCAT(u.prenom," ",u.nom) AS enseignant,
           (SELECT COUNT(*) FROM lecons WHERE cours_id=c.id AND actif=1) AS nb_lecons,
           (SELECT COUNT(*) FROM inscriptions WHERE cours_id=c.id AND etudiant_id=?) AS inscrit
    FROM cours c
    JOIN modules m ON m.id=c.module_id
    JOIN users u   ON u.id=c.enseignant_id
    WHERE c.actif=1 AND m.actif=1
    ORDER BY c.cree_le DESC
');
$stmt->execute([$uid]);
$result = $stmt->fetchAll();
echo "Nombre de cours trouvés : " . count($result) . "\n";
print_r($result);
echo "</pre>";
