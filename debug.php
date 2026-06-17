<?php
// debug.php — à supprimer après utilisation !
echo json_encode([
  'MYSQLHOST'     => getenv('MYSQLHOST'),
  'MYSQLPORT'     => getenv('MYSQLPORT'),
  'MYSQLDATABASE' => getenv('MYSQLDATABASE'),
  'MYSQLUSER'     => getenv('MYSQLUSER'),
  'MYSQLPASSWORD' => getenv('MYSQLPASSWORD') ? '***ok***' : 'MANQUANT',
], JSON_PRETTY_PRINT);
