<?php
/**
 * LearnUp — certificat.php
 * Page publique de vérification et d'affichage d'un certificat
 */
require_once __DIR__ . '/config/db.php';

$code = trim($_GET['code'] ?? '');
$certificat = null;

if ($code) {
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT ce.*, m.titre AS module_titre, m.description AS module_desc,
               u.nom, u.prenom, u.email,
               p.nom AS promo_nom, p.prenom AS promo_prenom, p.email AS promo_email
        FROM certificats ce
        JOIN modules m ON m.id  = ce.module_id
        JOIN users   u ON u.id  = ce.etudiant_id
        JOIN users   p ON p.id  = m.promoteur_id
        WHERE ce.code_unique = ?
    ');
    $stmt->execute([$code]);
    $certificat = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LearnUp — Certificat</title>
  <link rel="stylesheet" href="/assets/css/style.css"/>
  <style>
    body { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:100vh; padding:30px 16px; }

    .cert-wrapper {
      max-width: 800px;
      width: 100%;
    }

    .cert-card {
      background: linear-gradient(135deg, #1A1D27 0%, #0F1117 100%);
      border: 3px solid var(--violet);
      border-radius: 24px;
      padding: 56px 60px;
      text-align: center;
      position: relative;
      overflow: hidden;
      box-shadow: 0 0 60px rgba(108,99,255,0.2), 0 20px 60px rgba(0,0,0,0.6);
    }

    /* Décoration coins */
    .cert-card::before, .cert-card::after {
      content: '';
      position: absolute;
      width: 300px; height: 300px;
      border-radius: 50%;
      pointer-events: none;
    }
    .cert-card::before {
      background: radial-gradient(circle, rgba(108,99,255,0.12) 0%, transparent 70%);
      top: -100px; left: -100px;
    }
    .cert-card::after {
      background: radial-gradient(circle, rgba(0,212,170,0.08) 0%, transparent 70%);
      bottom: -100px; right: -100px;
    }

    .cert-logo {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--blanc);
      margin-bottom: 32px;
      position: relative;
      z-index: 1;
    }
    .cert-logo span { color: var(--violet); }

    .cert-label {
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--texte3);
      margin-bottom: 20px;
      position: relative;
      z-index: 1;
    }

    .cert-titre-module {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: clamp(1.6rem, 4vw, 2.4rem);
      font-weight: 800;
      color: var(--blanc);
      margin-bottom: 32px;
      position: relative;
      z-index: 1;
    }

    .cert-separateur {
      width: 80px;
      height: 3px;
      background: linear-gradient(90deg, var(--violet), var(--menthe));
      border-radius: 2px;
      margin: 0 auto 32px;
    }

    .cert-attribue-a {
      font-size: 0.85rem;
      color: var(--texte2);
      margin-bottom: 10px;
      position: relative;
      z-index: 1;
    }

    .cert-nom {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: clamp(1.8rem, 5vw, 2.8rem);
      font-weight: 900;
      background: linear-gradient(135deg, var(--violet-cl), var(--menthe));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 32px;
      position: relative;
      z-index: 1;
    }

    .cert-desc {
      font-size: 0.9rem;
      color: var(--texte2);
      max-width: 480px;
      margin: 0 auto 36px;
      line-height: 1.7;
      position: relative;
      z-index: 1;
    }

    .cert-footer {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 20px;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
      border-top: 1px solid var(--border);
      padding-top: 28px;
      margin-top: 8px;
    }

    .cert-date-bloc { text-align: left; }
    .cert-date-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--texte3); }
    .cert-date-val   { font-size: 0.9rem; font-weight: 600; color: var(--texte); margin-top: 4px; }

    .cert-code-bloc { text-align: right; }
    .cert-code-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--texte3); }
    .cert-code-val   { font-size: 0.72rem; color: var(--violet-cl); font-family: monospace; margin-top: 4px; word-break: break-all; }

    .cert-medaille {
      font-size: 5rem;
      margin-bottom: 20px;
      position: relative;
      z-index: 1;
      animation: pulse-medaille 2s ease-in-out infinite;
    }
    @keyframes pulse-medaille {
      0%,100% { transform: scale(1);    }
      50%      { transform: scale(1.06); }
    }

    /* État : certificat invalide */
    .cert-invalide {
      text-align: center;
      padding: 60px 40px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 20px;
    }

    /* Boutons */
    .cert-actions {
      display: flex;
      justify-content: center;
      gap: 14px;
      margin-top: 28px;
      flex-wrap: wrap;
    }

    /* Forcer l'impression des couleurs/dégradés (sinon Chrome/Android
       les ignore si "Graphiques d'arrière-plan" n'est pas coché) */
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      color-adjust: exact !important;
    }

    @media print {
      body { background: #0F1117; }
      .cert-card {
        border-color: #6C63FF;
        background: linear-gradient(135deg, #1A1D27 0%, #0F1117 100%) !important;
        box-shadow: none;
      }
      .cert-actions, nav { display: none !important; }
      .cert-nom {
        -webkit-text-fill-color: #8B84FF;
        background: linear-gradient(135deg, #8B84FF, #00D4AA) !important;
        -webkit-background-clip: text !important;
        background-clip: text !important;
        color: #8B84FF;
      }
    }

    @media (max-width: 600px) {
      .cert-card { padding: 36px 24px; }
      .cert-footer { flex-direction: column; align-items: flex-start; }
      .cert-code-bloc { text-align: left; }
    }
  </style>
</head>
<body>

<?php if (!$code): ?>
  <!-- Formulaire de vérification -->
  <div class="cert-wrapper">
    <div style="text-align:center;margin-bottom:32px;">
      <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.6rem;font-weight:800;color:var(--blanc);margin-bottom:8px;">Learn<span style="color:var(--violet)">Up</span></div>
      <h1 style="margin-bottom:8px;">Vérifier un certificat</h1>
      <p>Entrez le code unique du certificat pour l'afficher</p>
    </div>
    <div class="card" style="max-width:480px;margin:0 auto;">
      <form method="GET">
        <div class="form-group">
          <label class="form-label">Code du certificat</label>
          <input type="text" name="code" class="form-control" placeholder="Entrez le code complet..." required style="font-family:monospace;letter-spacing:0.06em;"/>
        </div>
        <button type="submit" class="btn btn-primary btn-full">🔍 Vérifier</button>
      </form>
    </div>
    <div style="text-align:center;margin-top:20px;">
      <a href="/" style="font-size:0.85rem;color:var(--texte2)">← Retour à l'accueil</a>
    </div>
  </div>

<?php elseif (!$certificat): ?>
  <!-- Certificat non trouvé -->
  <div class="cert-wrapper">
    <div class="cert-invalide">
      <div style="font-size:3rem;margin-bottom:16px;">❌</div>
      <h2 style="margin-bottom:8px;">Certificat introuvable</h2>
      <p style="margin-bottom:24px;">Le code <code style="color:var(--rouge)"><?= htmlspecialchars($code) ?></code> ne correspond à aucun certificat valide.</p>
      <a href="/certificat.php" class="btn btn-outline">Réessayer</a>
    </div>
  </div>

<?php else: ?>
  <!-- ══ CERTIFICAT VALIDE ══ -->
  <div class="cert-wrapper">
    <div class="cert-card" id="cert-a-imprimer">

      <!-- Logo -->
      <div class="cert-logo">Learn<span>Up</span></div>

      <!-- Médaille -->
      <div class="cert-medaille">🏆</div>

      <!-- Label -->
      <div class="cert-label">Certificat de validation de module</div>

      <!-- Séparateur -->
      <div class="cert-separateur"></div>

      <!-- Attribué à -->
      <div class="cert-attribue-a">Ce certificat est attribué à</div>
      <div class="cert-nom">
        <?= htmlspecialchars($certificat['prenom']) ?> <?= htmlspecialchars($certificat['nom']) ?>
      </div>

      <!-- Module -->
      <div class="cert-attribue-a" style="margin-bottom:6px;">pour avoir validé le module</div>
      <div class="cert-titre-module"><?= htmlspecialchars($certificat['module_titre']) ?></div>

      <?php if ($certificat['module_desc']): ?>
        <p class="cert-desc"><?= htmlspecialchars($certificat['module_desc']) ?></p>
      <?php endif; ?>

      <!-- Promoteur -->
      <div style="margin-bottom:28px;padding:16px 20px;background:rgba(108,99,255,0.08);border:1px solid rgba(108,99,255,0.2);border-radius:12px;position:relative;z-index:1;">
        <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--texte3);margin-bottom:6px;">Délivré par</div>
        <div style="font-weight:700;color:var(--blanc);font-size:0.95rem;"><?= htmlspecialchars($certificat['promo_prenom']) ?> <?= htmlspecialchars($certificat['promo_nom']) ?></div>
        <div style="font-size:0.82rem;color:var(--violet-cl);margin-top:2px;"><?= htmlspecialchars($certificat['promo_email']) ?></div>
      </div>

      <!-- Pied de page -->
      <div class="cert-footer">
        <div class="cert-date-bloc">
          <div class="cert-date-label">Date de délivrance</div>
          <div class="cert-date-val">
            <?= date('d F Y', strtotime($certificat['delivre_le'])) ?>
          </div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:1.8rem;margin-bottom:4px;">✅</div>
          <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--menthe);">Certifié valide</div>
        </div>
        <div class="cert-code-bloc">
          <div class="cert-code-label">Code de vérification</div>
          <div class="cert-code-val"><?= htmlspecialchars($certificat['code_unique']) ?></div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="cert-actions">
      <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimer / PDF</button>
      <a href="/" class="btn btn-outline">← Retour à l'accueil</a>
    </div>

    <!-- Badge vérification -->
    <div style="text-align:center;margin-top:20px;font-size:0.78rem;color:var(--texte3);">
      ✅ Ce certificat a été vérifié et est authentique · LearnUp <?= date('Y') ?>
    </div>
  </div>
<?php endif; ?>

</body>
</html>
