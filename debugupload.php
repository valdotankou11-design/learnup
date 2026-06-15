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
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    echo "Extension : $ext\n";
    echo "Type sélectionné : $type\n";
    echo "</pre>";

    $resourceType = ($type === 'pdf') ? 'raw' : 'video';
    $timestamp    = time();
    $publicId     = 'learnup/' . $type . 's/test_' . $timestamp;

    $paramsToSign = ['public_id' => $publicId, 'timestamp' => $timestamp];
    ksort($paramsToSign);
    $strToSign = http_build_query($paramsToSign) . CLOUDINARY_SECRET;
    $signature = sha1($strToSign);

    echo "<h2>Cloudinary :</h2><pre>";
    echo "Resource type : $resourceType\n";
    echo "Signature : $signature\n";
    echo "</pre>";

    $postFields = [
        'file'      => new CURLFile($f['tmp_name'], $f['type'], $f['name']),
        'public_id' => $publicId,
        'timestamp' => $timestamp,
        'api_key'   => CLOUDINARY_KEY,
        'signature' => $signature,
    ];

    $url = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD . '/' . $resourceType . '/upload';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "<h2>Réponse Cloudinary :</h2><pre>";
    echo "HTTP Code : $httpCode\n";
    if ($curlError) echo "cURL Error : $curlError\n";
    $data = json_decode($response, true);
    if (isset($data['secure_url'])) {
        echo "✅ SUCCÈS ! URL : " . $data['secure_url'] . "\n";
    } else {
        echo "❌ ÉCHEC\n";
        echo "Réponse brute : $response\n";
    }
    echo "</pre>";

} else {
    echo '<form method="POST" enctype="multipart/form-data">
        <p><strong>Type :</strong><br>
        <label><input type="radio" name="type" value="pdf" checked> PDF</label>&nbsp;&nbsp;
        <label><input type="radio" name="type" value="video"> Vidéo</label></p>
        <p><input type="file" name="fichier"></p>
        <button type="submit">Tester upload Cloudinary</button>
    </form>';
}
