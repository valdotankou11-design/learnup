<?php
/**
 * LearnUp — dashboard/etudiant.php
 */
require_once __DIR__ . '/../config/db.php';
exigerConnexion('etudiant');
$user = utilisateurCourant();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LearnUp — Mon espace</title>
  <link rel="stylesheet" href="/assets/css/style.css"/>
</head>
<body>

<!-- ── Navbar ── -->
<nav class="navbar">
  <div class="navbar-brand">Learn<span>Up</span></div>
  <ul class="navbar-nav">
    <li><a href="#" onclick="afficherSection('accueil')"    class="active" id="nav-accueil">🏠 Accueil</a></li>
    <li><a href="#" onclick="afficherSection('catalogue')"  id="nav-catalogue">📚 Catalogue</a></li>
    <li><a href="#" onclick="afficherSection('mes-cours')"  id="nav-mes-cours">🎯 Mes cours</a></li>
    <li><a href="#" onclick="afficherSection('certificats')"id="nav-certificats">🏆 Certificats</a></li>
  </ul>
  <div class="navbar-user">
    <span style="font-size:0.85rem;color:var(--texte2)">👋 <?= htmlspecialchars($user['prenom']) ?></span>
    <button class="btn btn-outline btn-sm" onclick="seDeconnecter()">Déconnexion</button>
  </div>
</nav>

<!-- ── Layout ── -->
<div class="dashboard-layout">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="#" class="sidebar-link active" onclick="afficherSection('accueil')"     id="sl-accueil">    <span class="icon">🏠</span> Accueil</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('catalogue')"   id="sl-catalogue">  <span class="icon">📚</span> Catalogue</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('mes-cours')"   id="sl-mes-cours">  <span class="icon">🎯</span> Mes cours</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('certificats')" id="sl-certificats"><span class="icon">🏆</span> Certificats</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Compte</div>
      <a href="#" class="sidebar-link" onclick="seDeconnecter()"><span class="icon">🚪</span> Déconnexion</a>
    </div>
  </aside>

  <!-- Contenu principal -->
  <main class="main-content">

    <!-- ══ SECTION ACCUEIL ══ -->
    <section id="section-accueil">
      <div style="margin-bottom:28px;">
        <h1>Bonjour, <?= htmlspecialchars($user['prenom']) ?> 👋</h1>
        <p>Continuez votre apprentissage là où vous vous êtes arrêté.</p>
      </div>

      <!-- Stats -->
      <div class="stats-grid" id="stats-etudiant"></div>

      <!-- Cours en cours -->
      <h2 style="margin-bottom:16px;font-size:1.2rem;">Mes cours en cours</h2>
      <div id="cours-en-cours" class="grid-cours"></div>
    </section>

    <!-- ══ SECTION CATALOGUE ══ -->
    <section id="section-catalogue" style="display:none;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
          <h1>Catalogue des cours</h1>
          <p>Découvrez tous les cours disponibles</p>
        </div>
        <input type="text" class="form-control" placeholder="🔍 Rechercher un cours..."
               style="max-width:280px;" oninput="filtrerCours(this.value)" id="input-recherche"/>
      </div>
      <div id="liste-catalogue" class="grid-cours"></div>
    </section>

    <!-- ══ SECTION MES COURS ══ -->
    <section id="section-mes-cours" style="display:none;">
      <div style="margin-bottom:24px;">
        <h1>Mes cours</h1>
        <p>Suivez votre progression leçon par leçon</p>
      </div>
      <div id="liste-mes-cours" class="grid-cours"></div>
    </section>

    <!-- ══ SECTION CERTIFICATS ══ -->
    <section id="section-certificats" style="display:none;">
      <div style="margin-bottom:24px;">
        <h1>Mes certificats 🏆</h1>
        <p>Vos modules validés</p>
      </div>
      <div id="liste-certificats"></div>
    </section>

  </main>
</div>

<!-- ══ MODAL : Cours (leçons) ══ -->
<div class="modal-overlay" id="modal-cours">
  <div class="modal" style="max-width:700px;">
    <div class="modal-header">
      <h3 class="modal-titre" id="modal-cours-titre">Cours</h3>
      <button class="modal-fermer" onclick="fermerModal('modal-cours')">✕</button>
    </div>
    <div id="modal-cours-contenu"></div>
  </div>
</div>

<!-- ══ MODAL : Leçon (PDF / vidéo) ══ -->
<div class="modal-overlay" id="modal-lecon">
  <div class="modal" style="max-width:800px;width:95vw;">
    <div class="modal-header">
      <h3 class="modal-titre" id="modal-lecon-titre">Leçon</h3>
      <button class="modal-fermer" onclick="fermerModal('modal-lecon')">✕</button>
    </div>
    <div id="modal-lecon-contenu"></div>
  </div>
</div>

<!-- ══ MODAL : Évaluation ══ -->
<div class="modal-overlay" id="modal-eval">
  <div class="modal" style="max-width:700px;width:95vw;">
    <div class="modal-header">
      <h3 class="modal-titre" id="modal-eval-titre">Évaluation</h3>
      <div style="display:flex;align-items:center;gap:16px;">
        <div class="timer-eval" id="timer-eval">⏱ --:--</div>
        <button class="modal-fermer" onclick="fermerModal('modal-eval')">✕</button>
      </div>
    </div>
    <div id="modal-eval-contenu"></div>
  </div>
</div>

<!-- ══ MODAL : Résultat ══ -->
<div class="modal-overlay" id="modal-resultat">
  <div class="modal" style="max-width:480px;text-align:center;">
    <div id="modal-resultat-contenu"></div>
  </div>
</div>

<style>
.grid-cours {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}
.lecon-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px;
  border: 1px solid var(--border);
  border-radius: 12px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: all var(--transition);
}
.lecon-item:hover { border-color: var(--violet); background: var(--violet-bg); }
.lecon-item.terminee { border-color: rgba(0,212,170,0.3); }
.lecon-icone {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
  background: var(--surface2);
}
.lecon-terminee-badge {
  margin-left: auto;
  font-size: 0.75rem;
  color: var(--menthe);
  font-weight: 600;
}
</style>

<script src="/assets/js/app.js"></script>
<script>
let tousLesCours = [];
let evalTimerInterval = null;
let evalReponses = {};
let evalIdCourant = null;

/* ══ Navigation ══ */
function afficherSection(id) {
  document.querySelectorAll('main section').forEach(s => s.style.display = 'none');
  document.getElementById('section-' + id).style.display = 'block';

  document.querySelectorAll('.sidebar-link, .navbar-nav a').forEach(a => a.classList.remove('active'));
  document.getElementById('sl-'  + id)?.classList.add('active');
  document.getElementById('nav-' + id)?.classList.add('active');

  const loaders = {
    'accueil':      chargerAccueil,
    'catalogue':    chargerCatalogue,
    'mes-cours':    chargerMesCours,
    'certificats':  chargerCertificats,
  };
  loaders[id]?.();
}

/* ══ Accueil ══ */
async function chargerAccueil() {
  const [statsR, coursR] = await Promise.all([
    ajax('stats_etudiant'),
    ajax('mes_cours_etudiant'),
  ]);

  if (statsR.succes) {
    const s = statsR.stats;
    document.getElementById('stats-etudiant').innerHTML = `
      <div class="stat-card"><div class="stat-icon violet">📚</div><div class="stat-info"><div class="valeur">${s.cours}</div><div class="label">Cours suivis</div></div></div>
      <div class="stat-card"><div class="stat-icon menthe">✅</div><div class="stat-info"><div class="valeur">${s.lecons}</div><div class="label">Leçons terminées</div></div></div>
      <div class="stat-card"><div class="stat-icon orange">📊</div><div class="stat-info"><div class="valeur">${s.note_moy}%</div><div class="label">Note moyenne</div></div></div>
      <div class="stat-card"><div class="stat-icon violet">🏆</div><div class="stat-info"><div class="valeur">${s.certificats}</div><div class="label">Certificats</div></div></div>`;
  }

  if (coursR.succes) {
    const el = document.getElementById('cours-en-cours');
    if (!coursR.cours.length) {
      el.innerHTML = `<div class="empty-state"><div class="icon">📚</div><h3>Aucun cours en cours</h3><p>Explorez le catalogue pour commencer</p><button class="btn btn-primary" onclick="afficherSection('catalogue')">Voir le catalogue</button></div>`;
    } else {
      el.innerHTML = coursR.cours.map(c => carteCoursEtudiant(c)).join('');
    }
  }
}

/* ══ Catalogue ══ */
async function chargerCatalogue() {
  const data = await ajax('cours_disponibles');
  if (!data.succes) return;
  tousLesCours = data.cours;
  afficherCatalogue(tousLesCours);
}

function afficherCatalogue(liste) {
  const el = document.getElementById('liste-catalogue');
  if (!liste.length) {
    el.innerHTML = `<div class="empty-state"><div class="icon">🔍</div><h3>Aucun cours trouvé</h3></div>`;
    return;
  }
  el.innerHTML = liste.map(c => `
    <div class="card-cours">
      <div class="card-cours-image">${emojiCours(c.niveau)}</div>
      <div class="card-cours-body">
        <div class="card-cours-titre">${escHtml(c.titre)}</div>
        <div class="card-cours-desc">${escHtml(c.description||'')}</div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;">
          <span class="badge badge-violet">${escHtml(c.module_titre)}</span>
          <span class="badge badge-orange">${niveauLabel(c.niveau)}</span>
          <span class="badge" style="background:var(--surface2);color:var(--texte2)">📖 ${c.nb_lecons} leçons</span>
        </div>
        <div style="font-size:0.8rem;color:var(--texte3);margin-bottom:14px;">👨‍🏫 ${escHtml(c.enseignant)}</div>
        ${c.inscrit
          ? `<button class="btn btn-outline btn-full" onclick="voirCours(${c.id},'${escHtml(c.titre)}')">Continuer →</button>`
          : `<button class="btn btn-primary btn-full" onclick="sInscrire(${c.id})">S'inscrire gratuitement</button>`
        }
      </div>
    </div>`).join('');
}

function filtrerCours(q) {
  const r = q.toLowerCase();
  afficherCatalogue(tousLesCours.filter(c =>
    c.titre.toLowerCase().includes(r) ||
    (c.description||'').toLowerCase().includes(r) ||
    c.module_titre.toLowerCase().includes(r)
  ));
}

async function sInscrire(coursId) {
  const data = await ajax('sinscrire_cours', { cours_id: coursId });
  if (data.succes) { toast('Inscrit avec succès !', 'succes'); chargerCatalogue(); }
  else toast(data.message, 'erreur');
}

/* ══ Mes cours ══ */
async function chargerMesCours() {
  const data = await ajax('mes_cours_etudiant');
  if (!data.succes) return;
  const el = document.getElementById('liste-mes-cours');
  if (!data.cours.length) {
    el.innerHTML = `<div class="empty-state"><div class="icon">🎯</div><h3>Pas encore inscrit</h3><p>Parcourez le catalogue et inscrivez-vous !</p><button class="btn btn-primary" onclick="afficherSection('catalogue')">Explorer</button></div>`;
    return;
  }
  el.innerHTML = data.cours.map(c => carteCoursEtudiant(c)).join('');
}

function carteCoursEtudiant(c) {
  const pct = c.nb_lecons > 0 ? Math.round((c.lecons_terminees / c.nb_lecons) * 100) : 0;
  return `
    <div class="card-cours" onclick="voirCours(${c.id},'${escHtml(c.titre)}')">
      <div class="card-cours-image">${emojiCours(c.niveau)}</div>
      <div class="card-cours-body">
        <div class="card-cours-titre">${escHtml(c.titre)}</div>
        <div class="card-cours-desc">${escHtml(c.description||'')}</div>
        <div style="margin-bottom:8px;">
          <div style="display:flex;justify-content:space-between;font-size:0.78rem;color:var(--texte2);margin-bottom:4px;">
            <span>Progression</span><span>${pct}%</span>
          </div>
          <div class="progress-bar"><div class="progress-fill" data-pct="${pct}" style="width:${pct}%"></div></div>
        </div>
        <div style="font-size:0.78rem;color:var(--texte3)">📖 ${c.lecons_terminees||0}/${c.nb_lecons} leçons</div>
      </div>
    </div>`;
}

/* ══ Voir un cours (leçons) ══ */
async function voirCours(coursId, titre) {
  document.getElementById('modal-cours-titre').textContent = titre;
  document.getElementById('modal-cours-contenu').innerHTML = '<div style="text-align:center;padding:30px"><div class="spinner" style="margin:auto"></div></div>';
  ouvrirModal('modal-cours');

  const data = await ajax('detail_cours', { cours_id: coursId });
  if (!data.succes) return;

  const el = document.getElementById('modal-cours-contenu');
  if (!data.lecons.length) {
    el.innerHTML = '<div class="empty-state"><div class="icon">📂</div><h3>Aucune leçon disponible</h3></div>';
    return;
  }

  el.innerHTML = data.lecons.map((l, i) => `
    <div class="lecon-item ${l.terminee ? 'terminee' : ''}" onclick="ouvrirLecon(${l.id}, '${escHtml(l.titre)}', '${l.type}', '${escHtml(l.fichier)}', ${l.evaluation_id||'null'}, '${escHtml(l.evaluation_titre||'')}', ${l.terminee ? 'true' : 'false'})">
      <div class="lecon-icone">${l.type === 'pdf' ? '📄' : '🎬'}</div>
      <div style="flex:1;">
        <div style="font-weight:600;color:var(--texte);font-size:0.9rem;">${i+1}. ${escHtml(l.titre)}</div>
        <div style="font-size:0.76rem;color:var(--texte3);margin-top:2px;">
          ${l.type.toUpperCase()} ${l.duree_min ? '· ' + l.duree_min + ' min' : ''}
          ${l.evaluation_titre ? ' · 📝 ' + escHtml(l.evaluation_titre) : ''}
        </div>
        ${l.ma_note !== null ? `<div style="font-size:0.75rem;color:var(--menthe);margin-top:3px;">Note: ${l.ma_note}%</div>` : ''}
      </div>
      <div class="lecon-terminee-badge">${l.terminee ? '✅' : ''}</div>
    </div>`).join('');
}

/* ══ Ouvrir une leçon ══ */
let leconEnCours = null; // { id, type, evalId, evalTitre, terminee }

// Charge l'API YouTube IFrame une seule fois (nécessaire pour détecter la fin réelle de la vidéo)
let ytApiPromise = null;
function chargerYouTubeAPI() {
  if (window.YT && window.YT.Player) return Promise.resolve();
  if (ytApiPromise) return ytApiPromise;
  ytApiPromise = new Promise(resolve => {
    const precedent = window.onYouTubeIframeAPIReady;
    window.onYouTubeIframeAPIReady = () => { if (precedent) precedent(); resolve(); };
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(tag);
  });
  return ytApiPromise;
}

function ouvrirLecon(leconId, titre, type, fichier, evalId, evalTitre, terminee) {
  document.getElementById('modal-lecon-titre').textContent = titre;
  const el = document.getElementById('modal-lecon-contenu');
  leconEnCours = { id: leconId, type, evalId, evalTitre, terminee: !!terminee };

  let contenu = '';
  let estVideoYt = false, idYt = null;

  if (type === 'pdf') {
    // URL absolue (le fichier peut être une URL Cloudinary ou un chemin relatif)
    const urlAbsolue = /^https?:\/\//i.test(fichier) ? fichier : (location.origin + fichier);
    // On passe par Google Docs Viewer : un <iframe> pointant directement sur le
    // PDF ne s'affiche pas correctement sur mobile / quand le fichier est servi
    // en "raw" (pas d'aperçu inline, juste un téléchargement). Le viewer Google
    // fonctionne tant que l'URL est publique.
    const viewerUrl = 'https://docs.google.com/viewer?embedded=true&url=' + encodeURIComponent(urlAbsolue);
    contenu = `
      <iframe src="${viewerUrl}" style="width:100%;height:500px;border:none;border-radius:8px;background:#fff"></iframe>
      <div style="text-align:center;margin-top:10px;">
        <a href="${escHtml(urlAbsolue)}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">📄 Ouvrir / télécharger le PDF</a>
      </div>`;
  } else {
    // Vidéo (URL YouTube ou fichier)
    estVideoYt = fichier.includes('youtube') || fichier.includes('youtu.be');
    if (estVideoYt) {
      idYt = fichier.split('v=')[1]?.split('&')[0] || fichier.split('/').pop();
      contenu = `<div id="yt-player-${leconId}"></div>`;
    } else {
      contenu = `<video id="video-${leconId}" controls style="width:100%;border-radius:8px;max-height:400px;"><source src="${escHtml(fichier)}"/>Votre navigateur ne supporte pas la vidéo.</video>`;
    }
    if (!terminee) {
      contenu += `<p style="margin-top:8px;font-size:.9em;opacity:.8;">▶️ Regardez la vidéo jusqu'à la fin pour débloquer l'évaluation.</p>`;
    }
  }

  const evalVerrouillee = (type === 'video' && !terminee);
  let boutonEval = '';
  if (evalId) {
    boutonEval = evalVerrouillee
      ? `<button class="btn btn-primary" id="btn-eval-${leconId}" disabled style="opacity:.5;cursor:not-allowed;" title="Terminez la vidéo pour débloquer l'évaluation">🔒 Terminez la vidéo pour débloquer l'évaluation</button>`
      : `<button class="btn btn-primary" id="btn-eval-${leconId}" onclick="ouvrirEvaluation(${evalId},'${escHtml(evalTitre)}')">📝 Passer l'évaluation</button>`;
  }

  // Le bouton manuel "Marquer comme terminée" ne concerne que les PDF :
  // pour les vidéos, la validation se fait automatiquement à la fin de la lecture.
  const boutonManuel = (type === 'pdf')
    ? `<button class="btn btn-menthe" onclick="terminerLecon(${leconId})">✅ Marquer comme terminée</button>`
    : '';

  contenu += `
    <div style="display:flex;gap:12px;margin-top:16px;flex-wrap:wrap;">
      ${boutonManuel}
      ${boutonEval}
    </div>`;

  el.innerHTML = contenu;
  ouvrirModal('modal-lecon');

  // Attacher la détection de fin de vidéo réelle
  if (type === 'video' && !terminee) {
    if (estVideoYt) {
      chargerYouTubeAPI().then(() => {
        new YT.Player('yt-player-' + leconId, {
          videoId: idYt,
          playerVars: { rel: 0 },
          events: {
            onStateChange: (e) => { if (e.data === YT.PlayerState.ENDED) leconVideoTerminee(leconId); }
          }
        });
      });
    } else {
      const videoEl = document.getElementById('video-' + leconId);
      if (videoEl) videoEl.addEventListener('ended', () => leconVideoTerminee(leconId));
    }
  } else if (type === 'video' && estVideoYt) {
    // Vidéo déjà validée : simple lecteur embarqué, pas besoin de suivre la fin
    chargerYouTubeAPI().then(() => {
      new YT.Player('yt-player-' + leconId, { videoId: idYt, playerVars: { rel: 0 } });
    });
  }
}

// Appelé automatiquement quand la vidéo arrive réellement à sa fin
async function leconVideoTerminee(leconId) {
  if (leconEnCours && leconEnCours.terminee) return; // déjà fait, on évite les doubles appels
  const data = await ajax('marquer_lecon', { lecon_id: leconId });
  if (!data.succes) return;
  toast('Vidéo terminée, leçon validée ✅', 'succes');

  if (leconEnCours && leconEnCours.id === leconId) {
    leconEnCours.terminee = true;
    if (leconEnCours.evalId) {
      const btn = document.getElementById('btn-eval-' + leconId);
      if (btn) {
        btn.disabled = false;
        btn.style.opacity = '';
        btn.style.cursor = '';
        btn.title = '';
        btn.textContent = '📝 Passer l\'évaluation';
        btn.onclick = () => ouvrirEvaluation(leconEnCours.evalId, leconEnCours.evalTitre);
      }
    }
  }
}

// Utilisé uniquement pour les leçons PDF (pas de suivi de lecture possible)
async function terminerLecon(leconId) {
  const data = await ajax('marquer_lecon', { lecon_id: leconId });
  if (!data.succes) return;
  toast('Leçon marquée comme terminée ✅', 'succes');
}

/* ══ Évaluation ══ */
async function ouvrirEvaluation(evalId, titre) {
  evalIdCourant = evalId;
  evalReponses  = {};
  document.getElementById('modal-eval-titre').textContent = titre;
  document.getElementById('modal-eval-contenu').innerHTML = '<div style="text-align:center;padding:30px"><div class="spinner" style="margin:auto"></div></div>';
  fermerModal('modal-lecon');
  ouvrirModal('modal-eval');

  const data = await ajax('detail_evaluation', { evaluation_id: evalId });
  if (!data.succes) { toast('Évaluation introuvable', 'erreur'); return; }

  // Timer
  let secondes = (data.duree_min || 30) * 60;
  clearInterval(evalTimerInterval);
  evalTimerInterval = setInterval(() => {
    secondes--;
    const m = Math.floor(secondes / 60);
    const s = secondes % 60;
    const timerEl = document.getElementById('timer-eval');
    if (timerEl) {
      timerEl.textContent = `⏱ ${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
      timerEl.classList.toggle('urgent', secondes < 60);
    }
    if (secondes <= 0) { clearInterval(evalTimerInterval); soumettreEvaluation(); }
  }, 1000);

  const el = document.getElementById('modal-eval-contenu');
  el.innerHTML = (data.questions || []).map((q, i) => `
    <div class="question-card">
      <div class="question-numero">Question ${i+1} — ${q.points} point${q.points>1?'s':''}</div>
      <div class="question-enonce">${escHtml(q.enonce)}</div>
      ${(q.reponses || []).map(r => `
        <label class="reponse-option" id="rep-${q.id}-${r.id}" onclick="selectionnerReponse(${q.id},${r.id},this)">
          <input type="radio" name="q${q.id}" value="${r.id}"/>
          <div class="reponse-indicateur">○</div>
          <span style="font-size:0.9rem;color:var(--texte)">${escHtml(r.texte)}</span>
        </label>`).join('')}
    </div>`).join('') +
    `<button class="btn btn-primary btn-lg btn-full" style="margin-top:8px;" onclick="soumettreEvaluation()">
      Soumettre mes réponses
    </button>`;
}

function selectionnerReponse(questionId, reponseId, el) {
  // Désélectionner les autres
  document.querySelectorAll(`[id^="rep-${questionId}-"]`).forEach(o => {
    o.classList.remove('selectionnee');
    o.querySelector('.reponse-indicateur').textContent = '○';
  });
  el.classList.add('selectionnee');
  el.querySelector('.reponse-indicateur').textContent = '●';
  evalReponses[questionId] = reponseId;
}

async function soumettreEvaluation() {
  clearInterval(evalTimerInterval);
  const data = await ajax('passer_evaluation', {
    evaluation_id: evalIdCourant,
    reponses: JSON.stringify(evalReponses),
  });

  fermerModal('modal-eval');
  if (!data.succes) { toast(data.message, 'erreur'); return; }

  const emoji  = data.reussi ? '🎉' : '😔';
  const couleur= data.reussi ? 'var(--menthe)' : 'var(--rouge)';
  const msg    = data.reussi ? 'Félicitations, vous avez réussi !' : `Note de passage : ${data.note_passage}%`;

  document.getElementById('modal-resultat-contenu').innerHTML = `
    <div style="font-size:4rem;margin-bottom:16px;">${emoji}</div>
    <h2 style="margin-bottom:8px;">${data.reussi ? 'Réussi !' : 'Pas encore...'}</h2>
    <div style="font-size:2.5rem;font-weight:800;color:${couleur};margin:16px 0;">${data.note}%</div>
    <p style="margin-bottom:24px;">${msg}</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <button class="btn btn-primary" onclick="fermerModal('modal-resultat')">Continuer</button>
    </div>`;
  ouvrirModal('modal-resultat');
}

/* ══ Certificats ══ */
async function chargerCertificats() {
  const data = await ajax('mes_certificats');
  const el = document.getElementById('liste-certificats');
  if (!data.succes || !data.certificats.length) {
    el.innerHTML = `<div class="empty-state"><div class="icon">🏆</div><h3>Aucun certificat pour l'instant</h3><p>Validez des modules pour obtenir vos certificats</p></div>`;
    return;
  }
  el.innerHTML = data.certificats.map(c => `
    <div class="certificat-card" style="margin-bottom:20px;">
      <div style="position:relative;z-index:1;">
        <div style="font-size:0.75rem;letter-spacing:0.15em;text-transform:uppercase;color:var(--violet);margin-bottom:12px;">Certificat de validation</div>
        <div style="font-size:2rem;margin-bottom:12px;">🏆</div>
        <h2 style="margin-bottom:8px;">${escHtml(c.module_titre)}</h2>
        <p style="margin-bottom:16px;">${escHtml(c.module_desc||'')}</p>
        <div style="font-size:0.78rem;color:var(--texte3);">
          Délivré le ${formatDate(c.delivre_le)} · Code : <strong style="color:var(--violet-cl)">${c.code_unique.substring(0,12).toUpperCase()}...</strong>
        </div>
        <button class="btn btn-outline btn-sm" style="margin-top:16px;" onclick="imprimerCertificat('${c.code_unique}')">🖨️ Télécharger</button>
      </div>
    </div>`).join('');
}

function imprimerCertificat(code) {
  window.open('/certificat.php?code=' + code, '_blank');
}

/* ══ Utilitaires ══ */
function emojiCours(niveau) {
  const map = { debutant: '🌱', intermediaire: '🚀', avance: '⚡' };
  return map[niveau] || '📚';
}
function niveauLabel(n) {
  const map = { debutant: '🌱 Débutant', intermediaire: '🚀 Intermédiaire', avance: '⚡ Avancé' };
  return map[n] || n;
}

async function seDeconnecter() {
  await ajax('deconnexion');
  window.location.href = '/';
}

// Init
chargerAccueil();
</script>
</body>
</html>
