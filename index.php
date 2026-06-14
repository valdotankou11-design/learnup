<?php
/**
 * LearnUp — index.php
 * Page d'accueil + connexion/inscription
 */
require_once __DIR__ . '/config/db.php';

// Rediriger si déjà connecté
if (utilisateurConnecte()) {
    header('Location: /dashboard/' . $_SESSION['user_role'] . '.php');
    exit;
}

$erreur  = '';
$succes  = '';
$onglet  = $_GET['onglet'] ?? 'connexion';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LearnUp — Apprendre sans limites</title>
  <link rel="stylesheet" href="/assets/css/style.css"/>
  <link rel="manifest" href="/manifest.json"/>
  <meta name="theme-color" content="#6C63FF"/>
  <meta name="apple-mobile-web-app-capable" content="yes"/>
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
  <link rel="apple-touch-icon" href="/assets/img/icon-192.png"/>
  <style>
    body {
      display: flex;
      min-height: 100vh;
      background: var(--bg);
    }

    /* ── Panneau gauche ── */
    .hero-panel {
      flex: 1;
      background: linear-gradient(135deg, #1A1D27 0%, #0F1117 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px 56px;
      position: relative;
      overflow: hidden;
    }

    .hero-panel::before {
      content: '';
      position: absolute;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(108,99,255,0.15) 0%, transparent 70%);
      top: -100px; left: -100px;
      pointer-events: none;
    }
    .hero-panel::after {
      content: '';
      position: absolute;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(0,212,170,0.08) 0%, transparent 70%);
      bottom: -80px; right: -80px;
      pointer-events: none;
    }

    .hero-logo {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 1.8rem;
      font-weight: 800;
      color: var(--blanc);
      margin-bottom: 56px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .hero-logo span { color: var(--violet); }

    .hero-titre {
      font-size: clamp(2rem, 3.5vw, 3rem);
      font-weight: 800;
      line-height: 1.15;
      color: var(--blanc);
      margin-bottom: 20px;
    }
    .hero-titre em {
      font-style: normal;
      background: linear-gradient(135deg, var(--violet), var(--menthe));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-desc {
      font-size: 1rem;
      color: var(--texte2);
      line-height: 1.7;
      max-width: 440px;
      margin-bottom: 48px;
    }

    .hero-stats {
      display: flex;
      gap: 32px;
      flex-wrap: wrap;
    }
    .hero-stat-val {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 1.6rem;
      font-weight: 800;
      color: var(--blanc);
    }
    .hero-stat-label {
      font-size: 0.8rem;
      color: var(--texte3);
      margin-top: 2px;
    }

    /* Floating cards décoratives */
    .floating-card {
      position: absolute;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.82rem;
      color: var(--texte2);
      box-shadow: var(--ombre);
      animation: flotter 4s ease-in-out infinite;
    }
    .floating-card:nth-child(2) { animation-delay: 1.5s; }
    .floating-card:nth-child(3) { animation-delay: 3s; }
    @keyframes flotter {
      0%,100% { transform: translateY(0); }
      50%      { transform: translateY(-8px); }
    }

    .fc-1 { bottom: 200px; right: 40px; }
    .fc-2 { bottom: 120px; right: 60px; }
    .fc-3 { bottom: 280px; right: 20px; }

    /* ── Panneau droit (form) ── */
    .auth-panel {
      width: 480px;
      min-width: 380px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 48px 40px;
      background: var(--surface);
      border-left: 1px solid var(--border);
    }

    .auth-titre {
      font-size: 1.6rem;
      font-weight: 800;
      margin-bottom: 6px;
    }
    .auth-sous-titre {
      font-size: 0.88rem;
      color: var(--texte2);
      margin-bottom: 32px;
    }

    /* Onglets */
    .auth-tabs {
      display: flex;
      background: var(--bg);
      border-radius: 10px;
      padding: 4px;
      margin-bottom: 28px;
    }
    .auth-tab {
      flex: 1;
      padding: 9px;
      text-align: center;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--texte2);
      border-radius: 8px;
      cursor: pointer;
      border: none;
      background: none;
      transition: all var(--transition);
    }
    .auth-tab.active {
      background: var(--surface2);
      color: var(--texte);
      box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }

    /* Séparateur */
    .separateur {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 20px 0;
      color: var(--texte3);
      font-size: 0.78rem;
    }
    .separateur::before, .separateur::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border);
    }

    .role-selector {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      margin-bottom: 20px;
    }
    .role-option {
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 12px 8px;
      text-align: center;
      cursor: pointer;
      transition: all var(--transition);
    }
    .role-option:hover, .role-option.selected {
      border-color: var(--violet);
      background: var(--violet-bg);
    }
    .role-option input { display: none; }
    .role-option .role-icon { font-size: 1.4rem; margin-bottom: 4px; }
    .role-option .role-nom {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--texte2);
    }

    @media (max-width: 900px) {
      body { flex-direction: column; }
      .hero-panel { padding: 40px 24px; min-height: auto; flex: none; }
      .auth-panel { width: 100%; min-width: 0; border-left: none; border-top: 1px solid var(--border); flex: none; }
      .floating-card { display: none; }
      .hero-stats { gap: 16px; }
      .hero-stat-val { font-size: 1.6rem; }
    }

    @media (max-width: 480px) {
      .hero-panel { padding: 28px 18px; }
      .auth-panel { padding: 28px 18px; }
      .hero-stats { gap: 12px; }
      .hero-stat-val { font-size: 1.3rem; }
      .btn-lg { padding: 14px 20px; font-size: 0.95rem; }
      .input-password-wrap .form-control { font-size: 0.95rem; }
    }
  </style>
</head>
<body>

<!-- ── Panneau héro ── -->
<div class="hero-panel">
  <div class="hero-logo">Learn<span>Up</span></div>

  <h1 class="hero-titre">
    Apprenez.<br/>
    Progressez.<br/>
    <em>Certifiez-vous.</em>
  </h1>

  <p class="hero-desc">
    LearnUp est la plateforme d'apprentissage en ligne qui connecte
    enseignants et étudiants autour de cours structurés, d'évaluations
    intelligentes et de certificats reconnus.
  </p>

  <div class="hero-stats">
    <div>
      <div class="hero-stat-val">3</div>
      <div class="hero-stat-label">Rôles</div>
    </div>
    <div>
      <div class="hero-stat-val">∞</div>
      <div class="hero-stat-label">Cours</div>
    </div>
    <div>
      <div class="hero-stat-val">100%</div>
      <div class="hero-stat-label">En ligne</div>
    </div>
  </div>

  <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08);">
    <p style="font-size:0.72rem; color:rgba(255,255,255,0.3); letter-spacing:0.04em; line-height:1.6;">
      &copy; 2026 <span style="color:rgba(255,255,255,0.55); font-weight:600;">SOH TANKOU JOËL VALDO</span><br/>
      Tous droits réservés &mdash; LearnUp est une création originale protégée.
    </p>
  </div>

  <!-- Cartes flottantes décoratives -->
  <div class="floating-card fc-1">
    <span>🎓</span>
    <div>
      <div style="font-weight:600;color:var(--texte)">Module validé</div>
      <div style="font-size:0.72rem">Développement Web</div>
    </div>
  </div>
  <div class="floating-card fc-2" style="animation-delay:2s">
    <span>📈</span>
    <div>
      <div style="font-weight:600;color:var(--texte)">Progression : 78%</div>
      <div style="font-size:0.72rem">JavaScript Avancé</div>
    </div>
  </div>
</div>

<!-- ── Panneau auth ── -->
<div class="auth-panel">
  <h2 class="auth-titre">Bienvenue 👋</h2>
  <p class="auth-sous-titre">Connectez-vous ou créez votre compte</p>

  <!-- Onglets -->
  <div class="auth-tabs">
    <button class="auth-tab <?= $onglet==='connexion'?'active':'' ?>"
            onclick="afficherOnglet('connexion')">Connexion</button>
    <button class="auth-tab <?= $onglet==='inscription'?'active':'' ?>"
            onclick="afficherOnglet('inscription')">Inscription</button>
  </div>

  <?php if ($erreur): ?>
    <div class="alerte alerte-erreur">⚠️ <?= nettoyer($erreur) ?></div>
  <?php endif; ?>
  <?php if ($succes): ?>
    <div class="alerte alerte-succes">✅ <?= nettoyer($succes) ?></div>
  <?php endif; ?>

  <!-- ── Formulaire Connexion ── -->
  <div id="form-connexion" class="<?= $onglet!=='connexion'?'hidden':'' ?>">
    <form id="form-login" onsubmit="seConnecter(event)">
      <div class="form-group">
        <label class="form-label">Adresse e-mail</label>
        <input type="email" name="email" class="form-control" placeholder="vous@exemple.com" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Mot de passe</label>
        <div class="input-password-wrap">
          <input type="password" name="mot_de_passe" id="mdp-login" class="form-control" placeholder="••••••••" required/>
          <button type="button" class="btn-eye" onclick="toggleMdp('mdp-login',this)" tabindex="-1">👁</button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full btn-lg" id="btn-login">
        Se connecter
      </button>
    </form>
  </div>

  <!-- ── Formulaire Inscription ── -->
  <div id="form-inscription" class="<?= $onglet!=='inscription'?'hidden':'' ?>">
    <form id="form-register" onsubmit="sInscrire(event)">
      <div class="form-group">
        <label class="form-label">Je suis...</label>
        <div class="role-selector">
          <label class="role-option selected" id="role-etudiant">
            <input type="radio" name="role" value="etudiant" checked/>
            <div class="role-icon">🎓</div>
            <div class="role-nom">Étudiant</div>
          </label>
          <label class="role-option" id="role-enseignant">
            <input type="radio" name="role" value="enseignant"/>
            <div class="role-icon">👨‍🏫</div>
            <div class="role-nom">Enseignant</div>
          </label>
          <label class="role-option" id="role-promoteur">
            <input type="radio" name="role" value="promoteur"/>
            <div class="role-icon">🏛️</div>
            <div class="role-nom">Promoteur</div>
          </label>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Nom</label>
          <input type="text" name="nom" class="form-control" placeholder="TANKOU" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Prénom</label>
          <input type="text" name="prenom" class="form-control" placeholder="Joël" required/>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" class="form-control" placeholder="vous@exemple.com" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Mot de passe</label>
        <div class="input-password-wrap">
          <input type="password" name="mot_de_passe" id="mdp-register" class="form-control" placeholder="Min. 8 caractères" minlength="8" required/>
          <button type="button" class="btn-eye" onclick="toggleMdp('mdp-register',this)" tabindex="-1">👁</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" id="btn-register">
        Créer mon compte
      </button>
    </form>
  </div>

  <div class="separateur">Comptes de démonstration</div>
  <div style="font-size:0.78rem;color:var(--texte3);text-align:center;line-height:1.8;">
    promoteur@learnup.cm / messi@learnup.cm / etudiant@learnup.cm<br/>
    <strong style="color:var(--texte2)">Mot de passe : learnup2026</strong>
  </div>
</div>

<style>.hidden { display: none !important; }</style>
<script src="/assets/js/app.js"></script>
<script>
function afficherOnglet(onglet) {
  document.getElementById('form-connexion').classList.toggle('hidden',  onglet !== 'connexion');
  document.getElementById('form-inscription').classList.toggle('hidden', onglet !== 'inscription');
  document.querySelectorAll('.auth-tab').forEach((t, i) => {
    t.classList.toggle('active', (i === 0 && onglet === 'connexion') || (i === 1 && onglet === 'inscription'));
  });
}

// Sélecteur de rôle
document.querySelectorAll('.role-option').forEach(opt => {
  opt.addEventListener('click', () => {
    document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
    opt.classList.add('selected');
    opt.querySelector('input').checked = true;
  });
});

async function seConnecter(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-login');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner"></div> Connexion...';

  const form = e.target;
  const data = await ajax('connexion', {
    email:        form.email.value,
    mot_de_passe: form.mot_de_passe.value,
  });

  if (data.succes) {
    toast('Connexion réussie ! Redirection...', 'succes');
    setTimeout(() => window.location.href = '/dashboard/' + data.role + '.php', 800);
  } else {
    toast(data.message || 'Identifiants incorrects.', 'erreur');
    btn.disabled = false;
    btn.textContent = 'Se connecter';
  }
}

async function sInscrire(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-register');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner"></div> Création...';

  const form = e.target;
  const data = await ajax('inscription', {
    nom:          form.nom.value,
    prenom:       form.prenom.value,
    email:        form.email.value,
    mot_de_passe: form.mot_de_passe.value,
    role:         form.role.value,
  });

  if (data.succes) {
    toast('Compte créé ! Connexion en cours...', 'succes');
    setTimeout(() => window.location.href = '/dashboard/' + data.role + '.php', 800);
  } else {
    toast(data.message || 'Erreur lors de l\'inscription.', 'erreur');
    btn.disabled = false;
    btn.textContent = 'Créer mon compte';
  }
}
</script>

<style>
.input-password-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.input-password-wrap .form-control {
  padding-right: 44px;
  width: 100%;
}
.btn-eye {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 0;
  opacity: 0.6;
  transition: opacity 0.2s;
}
.btn-eye:hover { opacity: 1; }
</style>
<script>
function toggleMdp(id, btn) {
  const input = document.getElementById(id);
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '🙈';
  } else {
    input.type = 'password';
    btn.textContent = '👁';
  }
}

// PWA Service Worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then(reg => console.log('SW enregistré', reg))
      .catch(err => console.log('SW erreur', err));
  });
}
</script>


<footer style="
  text-align: center;
  padding: 18px 20px;
  font-size: 0.78rem;
  color: rgba(255,255,255,0.35);
  background: #0F1117;
  border-top: 1px solid rgba(255,255,255,0.06);
  letter-spacing: 0.03em;
">
  &copy; 2026 <strong style="color:rgba(255,255,255,0.55)">SOH TANKOU JOËL VALDO</strong> &mdash; Tous droits réservés. LearnUp est une création originale protégée.
</footer>

</body>
</html>
