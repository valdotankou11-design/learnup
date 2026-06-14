<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier'])) {
    $f = $_FILES['fichier'];
    echo "<h2>Infos fichier reçu :</h2>";
    echo "<pre>";
    echo "Nom : " . $f['name'] . "\n";
    echo "Type : " . $f['type'] . "\n";
    echo "Taille : " . $f['size'] . " octets\n";
    echo "Erreur : " . $f['error'] . "\n";
    echo "Tmp : " . $f['tmp_name'] . "\n";
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    echo "Extension : " . $ext . "\n";
    if ($f['tmp_name']) {
        $mime = mime_content_type($f['tmp_name']);
        echo "MIME détecté : " . $mime . "\n";
    }
    echo "</pre>";

    // Test Cloudinary
    echo "<h2>Test Cloudinary :</h2>";
    $result = uploaderFichier($f, 'pdf');
    if ($result) {
        echo "<p style='color:green'>✅ Upload réussi : $result</p>";
    } else {
        echo "<p style='color:red'>❌ Upload échoué</p>";
    }
} else {
    echo '<form method="POST" enctype="multipart/form-data">
        <input type="file" name="fichier" accept=".pdf">
        <button type="submit">Tester upload PDF</button>
    </form>';
}
