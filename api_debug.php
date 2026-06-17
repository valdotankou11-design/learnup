<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>API Debug</title></head>
<body>
<h2>Réponse brute de l'API :</h2>
<pre id="output" style="background:#111;color:#0f0;padding:20px;white-space:pre-wrap;word-break:break-all;"></pre>
<script>
fetch('/api/index.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
  body: 'action=connexion&email=test@test.com&mot_de_passe=test'
})
.then(r => r.text())
.then(txt => {
  document.getElementById('output').textContent = txt;
})
.catch(e => {
  document.getElementById('output').textContent = 'ERREUR FETCH: ' + e.message;
});
</script>
</body>
</html>
