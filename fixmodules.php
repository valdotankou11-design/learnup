<?php
require_once __DIR__ . '/config/db.php';
$db = getDB();
$db->exec("UPDATE modules SET actif=1");
$db->exec("UPDATE cours SET actif=1");
$count = $db->query("SELECT COUNT(*) FROM modules WHERE actif=1")->fetchColumn();
$countC = $db->query("SELECT COUNT(*) FROM cours WHERE actif=1")->fetchColumn();
echo "<h1 style='color:green'>✅ $count module(s) et $countC cours activés !</h1>";
echo "<p><a href='/'>Aller au site</a></p>";
