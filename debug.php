<?php
require_once __DIR__ . '/config/db.php';
$db = getDB();

$modules = $db->query("SELECT id, titre, actif FROM modules")->fetchAll();
$cours   = $db->query("SELECT c.id, c.titre, c.actif, c.module_id, m.titre as module, m.actif as module_actif FROM cours c JOIN modules m ON m.id=c.module_id")->fetchAll();

echo "<h2>Modules</h2><pre>" . print_r($modules, true) . "</pre>";
echo "<h2>Cours</h2><pre>" . print_r($cours, true) . "</pre>";
