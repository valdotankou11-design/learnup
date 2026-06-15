<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier'])) {
    $f    = $_FILES['fichier'];
    $type = $_POST['type'] ?? 'pdf';

    echo "<h2>Infos fichier :</h2><pre>";
    echo "Nom : " . $f['name'] . "\n";
    echo "Type MIME : " . $f['type'] . "\n";
    echo "Taille : " . $f['size'] . " octets\n";
    echo "Erreur : " . $f['error'] . "\n";
    echo "Extension : " . strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) . "\n";
    echo "</pre>";

    // Utiliser directement la fonction uploaderFichier de db.php
    echo "<h2>Test upload via uploaderFichier() :</h2>";
    $url = uploaderFichier($f, $type);
    if ($url) {
        echo "<p style='color:green;font-size:1.2em'>✅ SUCCÈS !<br>URL : <a href='$url' target='_blank'>$url</a></p>";
    } else {
        echo "<p style='color:red;font-size:1.2em'>❌ ÉCHEC — uploaderFichier() a retourné null</p>";
    }
} else {
    echo '<form method="POST" enctype="multipart/form-data" style="padding:20px;font-family:sans-serif;">
        <h2>Test upload Cloudinary</h2>
        <p><strong>Type :</strong><br>
        <label><input type="radio" name="type" value="pdf" checked> 📄 PDF</label>&nbsp;&nbsp;
        <label><input type="radio" name="type" value="video"> 🎬 Vidéo</label></p>
        <p><input type="file" name="fichier"></p>
        <button type="submit" style="padding:10px 20px;background:#6C63FF;color:white;border:none;border-radius:8px;cursor:pointer;">
            Tester upload
        </button>
    </form>';
}
