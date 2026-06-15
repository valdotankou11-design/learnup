<?php
/**
 * LearnUp — dashboard/enseignant.php
 */
require_once __DIR__ . '/../config/db.php';
exigerConnexion('enseignant');
$user = utilisateurCourant();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LearnUp — Espace Enseignant</title>
  <link rel="stylesheet" href="/assets/css/style.css"/>
</head>
<body>

<nav class="navbar">
  <div class="navbar-brand">Learn<span>Up</span> <span style="font-size:0.7rem;background:var(--violet-bg);color:var(--violet);padding:2px 8px;border-radius:6px;margin-left:6px;">Enseignant</span></div>
  <ul class="navbar-nav">
    <li><a href="#" onclick="afficherSection('accueil')"    id="nav-accueil"   class="active">🏠 Accueil</a></li>
    <li><a href="#" onclick="afficherSection('mes-cours')"  id="nav-mes-cours">📚 Mes cours</a></li>
    <li><a href="#" onclick="afficherSection('creer-cours')"id="nav-creer">➕ Créer un cours</a></li>
  </ul>
  <div class="navbar-user">
    <span style="font-size:0.85rem;color:var(--texte2)">👨‍🏫 <?= htmlspecialchars($user['prenom']) ?></span>
    <button class="btn btn-outline btn-sm" onclick="seDeconnecter()">Déconnexion</button>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="#" class="sidebar-link active" onclick="afficherSection('accueil')"    id="sl-accueil">   <span class="icon">🏠</span> Tableau de bord</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('mes-cours')"  id="sl-mes-cours"> <span class="icon">📚</span> Mes cours</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('creer-cours')"id="sl-creer">     <span class="icon">➕</span> Créer un cours</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Compte</div>
      <a href="#" class="sidebar-link" onclick="seDeconnecter()"><span class="icon">🚪</span> Déconnexion</a>
    </div>
  </aside>

  <main class="main-content">

    <!-- ══ ACCUEIL ══ -->
    <section id="section-accueil">
      <div style="margin-bottom:28px;">
        <h1>Bonjour, <?= htmlspecialchars($user['prenom']) ?> 👨‍🏫</h1>
        <p>Gérez vos cours, leçons et évaluations.</p>
      </div>
      <div class="stats-grid" id="stats-enseignant"></div>
      <h2 style="margin:28px 0 16px;font-size:1.2rem;">Mes cours récents</h2>
      <div id="cours-recents"></div>
    </section>

    <!-- ══ MES COURS ══ -->
    <section id="section-mes-cours" style="display:none;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div><h1>Mes cours</h1><p>Gérez le contenu de chaque cours</p></div>
        <button class="btn btn-primary" onclick="afficherSection('creer-cours')">➕ Nouveau cours</button>
      </div>
      <div id="liste-mes-cours"></div>
    </section>

    <!-- ══ CRÉER COURS ══ -->
    <section id="section-creer-cours" style="display:none;">
      <div style="margin-bottom:28px;">
        <h1>Créer un cours</h1>
        <p>Remplissez les informations de votre nouveau cours</p>
      </div>
      <div class="card" style="max-width:660px;">
        <form onsubmit="creerCours(event)">
          <div class="form-group">
            <label class="form-label">Titre du cours *</label>
            <input type="text" id="cours-titre" class="form-control" placeholder="Ex: Introduction à JavaScript" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea id="cours-desc" class="form-control" placeholder="Décrivez le contenu et les objectifs..." rows="4"></textarea>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
              <label class="form-label">Module *</label>
              <select id="cours-module" class="form-control" required>
                <option value="">Chargement...</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Niveau</label>
              <select id="cours-niveau" class="form-control">
                <option value="debutant">🌱 Débutant</option>
                <option value="intermediaire">🚀 Intermédiaire</option>
                <option value="avance">⚡ Avancé</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Durée estimée (heures)</label>
            <input type="number" id="cours-duree" class="form-control" placeholder="Ex: 10" min="1"/>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" id="btn-creer-cours">Créer le cours</button>
        </form>
      </div>
    </section>

  </main>
</div>

<!-- ══ MODAL : Gestion cours (leçons + évals) ══ -->
<div class="modal-overlay" id="modal-gestion">
  <div class="modal" style="max-width:780px;width:95vw;">
    <div class="modal-header">
      <h3 class="modal-titre" id="modal-gestion-titre">Gestion du cours</h3>
      <button class="modal-fermer" onclick="fermerModal('modal-gestion')">✕</button>
    </div>
    <div id="modal-gestion-contenu"></div>
  </div>
</div>

<!-- ══ MODAL : Ajouter leçon ══ -->
<div class="modal-overlay" id="modal-lecon">
  <div class="modal" style="max-width:580px;">
    <div class="modal-header">
      <h3 class="modal-titre">Ajouter une leçon</h3>
      <button class="modal-fermer" onclick="fermerModal('modal-lecon')">✕</button>
    </div>
    <form onsubmit="soumettreLecon(event)">
      <input type="hidden" id="lecon-cours-id"/>
      <div class="form-group">
        <label class="form-label">Titre de la leçon *</label>
        <input type="text" id="lecon-titre" class="form-control" placeholder="Ex: Les variables en JavaScript" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Type de contenu</label>
        <div style="display:flex;gap:12px;">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
            <input type="radio" name="lecon-type" value="pdf" checked onchange="toggleTypeLecon('pdf')"/> 📄 PDF
          </label>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
            <input type="radio" name="lecon-type" value="video" onchange="toggleTypeLecon('video')"/> 🎬 Vidéo
          </label>
        </div>
      </div>
      <div id="zone-pdf" class="form-group">
        <label class="form-label">Fichier PDF</label>
        <div class="form-upload" onclick="document.getElementById('input-pdf').click()">
          <input type="file" id="input-pdf" accept=".pdf"/>
          <div style="font-size:2rem;margin-bottom:8px;">📄</div>
          <div style="font-weight:600;color:var(--texte)">Cliquez pour choisir un PDF</div>
          <div style="font-size:0.78rem;color:var(--texte3);margin-top:4px;" id="pdf-nom">Format PDF uniquement</div>
        </div>
      </div>
      <div id="zone-video" class="form-group" style="display:none;">
        <label class="form-label">Source de la vidéo</label>
        <div style="display:flex;gap:10px;margin-bottom:12px;">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
            <input type="radio" name="video-source" value="url" checked onchange="toggleVideoSource('url')"/> 🔗 URL YouTube
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
            <input type="radio" name="video-source" value="fichier" onchange="toggleVideoSource('fichier')"/> 📁 Fichier vidéo
          </label>
        </div>
        <div id="zone-video-url">
          <input type="url" id="lecon-url-video" class="form-control" placeholder="https://www.youtube.com/watch?v=..."/>
        </div>
        <div id="zone-video-fichier" style="display:none;">
          <div class="form-upload" onclick="document.getElementById('input-video').click()">
            <input type="file" id="input-video" accept=".mp4,.webm,.ogg,.avi,.mov"/>
            <div style="font-size:2rem;margin-bottom:8px;">🎬</div>
            <div style="font-weight:600;color:var(--texte)">Cliquez pour choisir une vidéo</div>
            <div style="font-size:0.78rem;color:var(--texte3);margin-top:4px;" id="video-nom">MP4, WebM, AVI, MOV acceptés</div>
          </div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Ordre</label>
          <input type="number" id="lecon-ordre" class="form-control" value="1" min="1"/>
        </div>
        <div class="form-group">
          <label class="form-label">Durée (min)</label>
          <input type="number" id="lecon-duree" class="form-control" placeholder="30" min="1"/>
        </div>
      </div>
      <div style="display:flex;gap:12px;margin-top:8px;">
        <button type="submit" class="btn btn-primary" id="btn-soumettre-lecon">Ajouter la leçon</button>
        <button type="button" class="btn btn-outline" onclick="fermerModal('modal-lecon')">Annuler</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL : Créer évaluation ══ -->
<div class="modal-overlay" id="modal-eval">
  <div class="modal" style="max-width:680px;width:95vw;">
    <div class="modal-header">
      <h3 class="modal-titre">Créer une évaluation</h3>
      <button class="modal-fermer" onclick="fermerModal('modal-eval')">✕</button>
    </div>
    <form onsubmit="soumettreEvaluation(event)">
      <input type="hidden" id="eval-lecon-id"/>
      <input type="hidden" id="eval-id-courant"/>
      <div class="form-group">
        <label class="form-label">Titre de l'évaluation *</label>
        <input type="text" id="eval-titre" class="form-control" placeholder="Ex: Quiz Leçon 1" required/>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Note de passage (%)</label>
          <input type="number" id="eval-note-passage" class="form-control" value="50" min="0" max="100"/>
        </div>
        <div class="form-group">
          <label class="form-label">Durée (minutes)</label>
          <input type="number" id="eval-duree" class="form-control" value="30" min="5"/>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Créer et ajouter des questions →</button>
    </form>

    <!-- Questions -->
    <div id="zone-questions" style="display:none;margin-top:28px;border-top:1px solid var(--border);padding-top:20px;">
      <h4 style="margin-bottom:16px;">Questions de l'évaluation</h4>
      <div id="liste-questions-eval"></div>
      <button class="btn btn-outline" onclick="ouvrirFormulaireQuestion()">➕ Ajouter une question</button>

      <!-- Formulaire question inline -->
      <div id="form-question" style="display:none;background:var(--surface2);border-radius:12px;padding:20px;margin-top:16px;">
        <div class="form-group">
          <label class="form-label">Énoncé de la question</label>
          <textarea id="q-enonce" class="form-control" rows="3" placeholder="Posez votre question..."></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Points</label>
          <input type="number" id="q-points" class="form-control" value="1" min="1" style="max-width:120px;"/>
        </div>
        <div class="form-group">
          <label class="form-label">Réponses (cochez la bonne)</label>
          <div id="liste-rep-form"></div>
          <button type="button" class="btn btn-outline btn-sm" style="margin-top:8px;" onclick="ajouterChampReponse()">+ Ajouter une réponse</button>
        </div>
        <div style="display:flex;gap:10px;margin-top:12px;">
          <button class="btn btn-primary btn-sm" onclick="soumettreQuestion()">Enregistrer</button>
          <button class="btn btn-outline btn-sm" onclick="document.getElementById('form-question').style.display='none'">Annuler</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/assets/js/app.js"></script>
<script>
let coursIdCourant = null;
let evalIdCourant  = null;

/* ══ Navigation ══ */
function afficherSection(id) {
  document.querySelectorAll('main section').forEach(s => s.style.display = 'none');
  document.getElementById('section-' + id).style.display = 'block';
  document.querySelectorAll('.sidebar-link, .navbar-nav a').forEach(a => a.classList.remove('active'));
  document.getElementById('sl-'  + id)?.classList.add('active');
  document.getElementById('nav-' + id)?.classList.add('active');
  const loaders = {
    'accueil':    chargerAccueil,
    'mes-cours':  chargerMesCours,
    'creer-cours':chargerModulesPourSelect,
  };
  loaders[id]?.();
}

/* ══ Accueil ══ */
async function chargerAccueil() {
  const [statsR, coursR] = await Promise.all([ajax('stats_enseignant'), ajax('mes_cours')]);
  if (statsR.succes) {
    const s = statsR.stats;
    document.getElementById('stats-enseignant').innerHTML = `
      <div class="stat-card"><div class="stat-icon violet">📚</div><div class="stat-info"><div class="valeur">${s.cours}</div><div class="label">Cours créés</div></div></div>
      <div class="stat-card"><div class="stat-icon menthe">📖</div><div class="stat-info"><div class="valeur">${s.lecons}</div><div class="label">Leçons</div></div></div>
      <div class="stat-card"><div class="stat-icon orange">👥</div><div class="stat-info"><div class="valeur">${s.etudiants}</div><div class="label">Étudiants</div></div></div>`;
  }
  if (coursR.succes) {
    document.getElementById('cours-recents').innerHTML = coursR.cours.slice(0,3).map(c => carteCoursEnseignant(c)).join('') || '<div class="empty-state"><div class="icon">📚</div><h3>Aucun cours</h3><button class="btn btn-primary" onclick="afficherSection(\'creer-cours\')">Créer mon premier cours</button></div>';
  }
}

/* ══ Mes cours ══ */
async function chargerMesCours() {
  const data = await ajax('mes_cours');
  const el = document.getElementById('liste-mes-cours');
  if (!data.succes || !data.cours.length) {
    el.innerHTML = `<div class="empty-state"><div class="icon">📚</div><h3>Aucun cours</h3><button class="btn btn-primary" onclick="afficherSection('creer-cours')">Créer mon premier cours</button></div>`;
    return;
  }
  el.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">${data.cours.map(c => carteCoursEnseignant(c)).join('')}</div>`;
}

function carteCoursEnseignant(c) {
  return `
    <div class="card" style="cursor:default;">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;">
        <h3 style="font-size:1rem;">${escHtml(c.titre)}</h3>
        <span class="badge badge-violet">${c.nb_etudiants} étud.</span>
      </div>
      <p style="font-size:0.82rem;margin-bottom:14px;">${escHtml(c.description||'')}</p>
      <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;">
        <span class="badge badge-orange">${escHtml(c.module_titre)}</span>
        <span class="badge" style="background:var(--surface2);color:var(--texte2)">📖 ${c.nb_lecons} leçons</span>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button class="btn btn-primary btn-sm" onclick="ouvrirGestionCours(${c.id},'${escHtml(c.titre)}')">⚙️ Gérer</button>
        <button class="btn btn-outline btn-sm" onclick="voirEtudiants(${c.id})">👥 Étudiants</button>
      </div>
    </div>`;
}

/* ══ Créer cours ══ */
async function chargerModulesPourSelect() {
  const data = await ajax('lister_modules');
  const sel = document.getElementById('cours-module');
  if (!data.succes || !data.modules.length) {
    sel.innerHTML = '<option value="">Aucun module disponible</option>';
    return;
  }
  sel.innerHTML = '<option value="">Choisir un module...</option>' +
    data.modules.map(m => `<option value="${m.id}">${escHtml(m.titre)}</option>`).join('');
}

async function creerCours(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-creer-cours');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner" style="display:inline-block;width:16px;height:16px;"></div> Création...';

  const data = await ajax('creer_cours', {
    titre:       document.getElementById('cours-titre').value,
    description: document.getElementById('cours-desc').value,
    module_id:   document.getElementById('cours-module').value,
    niveau:      document.getElementById('cours-niveau').value,
    duree:       document.getElementById('cours-duree').value || 0,
  });

  if (data.succes) {
    toast('Cours créé ! Ajoutez maintenant vos leçons.', 'succes');
    afficherSection('mes-cours');
  } else {
    toast(data.message, 'erreur');
  }
  btn.disabled = false;
  btn.textContent = 'Créer le cours';
}

/* ══ Gestion cours (leçons + évals) ══ */
async function ouvrirGestionCours(coursId, titre) {
  coursIdCourant = coursId;
  document.getElementById('modal-gestion-titre').textContent = titre;
  document.getElementById('modal-gestion-contenu').innerHTML = '<div style="text-align:center;padding:20px"><div class="spinner" style="margin:auto"></div></div>';
  ouvrirModal('modal-gestion');
  await rafraichirGestionCours();
}

async function rafraichirGestionCours() {
  const data = await ajax('detail_cours', { cours_id: coursIdCourant });
  if (!data.succes) return;

  const el = document.getElementById('modal-gestion-contenu');
  el.innerHTML = `
    <div style="margin-bottom:16px;">
      <button class="btn btn-primary btn-sm" onclick="ouvrirModalLecon(${coursIdCourant})">➕ Ajouter une leçon</button>
    </div>
    ${data.lecons.length ? data.lecons.map((l, i) => `
      <div style="background:var(--surface2);border-radius:12px;padding:16px;margin-bottom:10px;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
          <span style="font-size:1.2rem;">${l.type==='pdf'?'📄':'🎬'}</span>
          <strong style="color:var(--texte);flex:1;">${i+1}. ${escHtml(l.titre)}</strong>
          <span class="badge badge-violet">${l.type.toUpperCase()}</span>
          ${l.evaluation_id
            ? `<span class="badge badge-menthe">✅ Éval. définie</span>`
            : `<button class="btn btn-outline btn-sm" onclick="ouvrirModalEval(${l.id})">➕ Ajouter éval.</button>`
          }
          <button class="btn btn-sm" style="background:rgba(255,80,80,0.15);color:#ff5050;border:1px solid rgba(255,80,80,0.3);" onclick="supprimerLecon(${l.id})">🗑 Supprimer</button>
        </div>
      </div>`).join('')
    : '<div class="empty-state"><div class="icon">📖</div><h3>Aucune leçon</h3><p>Ajoutez votre première leçon</p></div>'}`;
}

/* ══ Leçon ══ */
function ouvrirModalLecon(coursId) {
  document.getElementById('lecon-cours-id').value = coursId;
  document.getElementById('lecon-titre').value = '';
  document.getElementById('pdf-nom').textContent = 'Format PDF uniquement';
  ouvrirModal('modal-lecon');
}

function toggleTypeLecon(type) {
  document.getElementById('zone-pdf').style.display   = type === 'pdf'   ? 'block' : 'none';
  document.getElementById('zone-video').style.display = type === 'video' ? 'block' : 'none';
}

function toggleVideoSource(source) {
  document.getElementById('zone-video-url').style.display    = source === 'url'     ? 'block' : 'none';
  document.getElementById('zone-video-fichier').style.display = source === 'fichier' ? 'block' : 'none';
}

document.getElementById('input-video')?.addEventListener('change', function() {
  document.getElementById('video-nom').textContent = this.files[0]?.name || 'MP4, WebM, AVI, MOV acceptés';
});

document.getElementById('input-pdf')?.addEventListener('change', function() {
  document.getElementById('pdf-nom').textContent = this.files[0]?.name || 'Format PDF uniquement';
});

async function soumettreLecon(e) {
  e.preventDefault();
  const type    = document.querySelector('input[name="lecon-type"]:checked').value;
  const btn     = document.getElementById('btn-soumettre-lecon');
  btn.disabled  = true;
  btn.innerHTML = '<div class="spinner" style="display:inline-block;width:14px;height:14px;"></div>';

  const fd = new FormData();
  fd.append('cours_id', document.getElementById('lecon-cours-id').value);
  fd.append('titre',    document.getElementById('lecon-titre').value);
  fd.append('type',     type);
  fd.append('ordre',    document.getElementById('lecon-ordre').value);
  fd.append('duree',    document.getElementById('lecon-duree').value || 0);

  if (type === 'pdf') {
    const f = document.getElementById('input-pdf').files[0];
    if (f) fd.append('fichier', f);
  } else {
    const videoSource = document.querySelector('input[name="video-source"]:checked')?.value || 'url';
    if (videoSource === 'fichier') {
      const fv = document.getElementById('input-video').files[0];
      if (fv) fd.append('fichier', fv);
    } else {
      fd.append('url_video', document.getElementById('lecon-url-video').value);
    }
  }

  const data = await ajaxFormData('creer_lecon', fd);
  if (data.succes) {
    toast('Leçon ajoutée !', 'succes');
    fermerModal('modal-lecon');
    rafraichirGestionCours();
  } else {
    toast(data.message || 'Erreur.', 'erreur');
  }
  btn.disabled = false;
  btn.textContent = 'Ajouter la leçon';
}

/* ══ Évaluation ══ */
async function supprimerLecon(leconId) {
  if (!confirm('Supprimer cette leçon ? Cette action est irréversible.')) return;
  const data = await ajax('supprimer_lecon', { lecon_id: leconId });
  if (data.succes) {
    toast('Leçon supprimée.', 'succes');
    rafraichirGestionCours();
  } else {
    toast(data.message || 'Erreur.', 'erreur');
  }
}

function ouvrirModalEval(leconId) {
  evalIdCourant = null;
  document.getElementById('eval-lecon-id').value = leconId;
  document.getElementById('eval-titre').value = '';
  document.getElementById('zone-questions').style.display = 'none';
  ouvrirModal('modal-eval');
}

async function soumettreEvaluation(e) {
  e.preventDefault();
  const data = await ajax('creer_evaluation', {
    lecon_id:     document.getElementById('eval-lecon-id').value,
    titre:        document.getElementById('eval-titre').value,
    note_passage: document.getElementById('eval-note-passage').value,
    duree:        document.getElementById('eval-duree').value,
  });
  if (data.succes) {
    evalIdCourant = data.eval_id;
    document.getElementById('eval-id-courant').value = evalIdCourant;
    document.getElementById('zone-questions').style.display = 'block';
    toast('Évaluation créée ! Ajoutez vos questions.', 'succes');
  } else {
    toast(data.message, 'erreur');
  }
}

/* ══ Questions ══ */
let repCount = 0;
function ouvrirFormulaireQuestion() {
  document.getElementById('form-question').style.display = 'block';
  document.getElementById('q-enonce').value = '';
  document.getElementById('q-points').value = '1';
  repCount = 0;
  document.getElementById('liste-rep-form').innerHTML = '';
  ajouterChampReponse();
  ajouterChampReponse();
}

function ajouterChampReponse() {
  repCount++;
  const div = document.createElement('div');
  div.style.cssText = 'display:flex;align-items:center;gap:10px;margin-bottom:8px;';
  div.innerHTML = `
    <input type="checkbox" id="rep-correct-${repCount}" style="width:18px;height:18px;cursor:pointer;"/>
    <input type="text" class="form-control rep-texte" placeholder="Réponse ${repCount}" style="flex:1;"/>`;
  document.getElementById('liste-rep-form').appendChild(div);
}

async function soumettreQuestion() {
  const enonce = document.getElementById('q-enonce').value.trim();
  if (!enonce) { toast('Entrez un énoncé.', 'erreur'); return; }

  const reponses = [];
  document.querySelectorAll('#liste-rep-form .rep-texte').forEach((inp, i) => {
    const check = document.getElementById(`rep-correct-${i+1}`);
    if (inp.value.trim()) {
      reponses.push({ texte: inp.value.trim(), correcte: check?.checked || false });
    }
  });

  if (!reponses.some(r => r.correcte)) { toast('Cochez au moins une bonne réponse.', 'erreur'); return; }

  const data = await ajax('ajouter_question', {
    evaluation_id: evalIdCourant,
    enonce,
    type:   'qcm',
    points: document.getElementById('q-points').value,
    reponses: JSON.stringify(reponses),
  });

  if (data.succes) {
    toast('Question ajoutée !', 'succes');
    document.getElementById('form-question').style.display = 'none';
    const nb = document.querySelectorAll('#liste-questions-eval .q-item').length + 1;
    document.getElementById('liste-questions-eval').insertAdjacentHTML('beforeend',
      `<div class="q-item" style="background:var(--surface2);border-radius:10px;padding:12px 16px;margin-bottom:8px;font-size:0.88rem;color:var(--texte);">
        <strong>Q${nb}.</strong> ${escHtml(enonce)}
        <span style="color:var(--texte3);margin-left:8px;">(${reponses.length} réponses)</span>
      </div>`
    );
  } else {
    toast(data.message, 'erreur');
  }
}

/* ══ Étudiants ══ */
async function voirEtudiants(coursId) {
  const data = await ajax('etudiants_cours', { cours_id: coursId });
  if (!data.succes) return;

  const contenu = !data.etudiants.length
    ? '<div class="empty-state"><div class="icon">👥</div><h3>Aucun étudiant inscrit</h3></div>'
    : `<div class="table-wrapper"><table>
        <thead><tr><th>Nom</th><th>E-mail</th><th>Inscrit le</th><th>Note moy.</th></tr></thead>
        <tbody>${data.etudiants.map(e => `
          <tr>
            <td><strong style="color:var(--texte)">${escHtml(e.prenom)} ${escHtml(e.nom)}</strong></td>
            <td>${escHtml(e.email)}</td>
            <td>${formatDate(e.inscrit_le)}</td>
            <td>${e.note_moy ? `<span style="color:var(--menthe);font-weight:600">${Math.round(e.note_moy)}%</span>` : '—'}</td>
          </tr>`).join('')}
        </tbody></table></div>`;

  document.getElementById('modal-gestion-titre').textContent = 'Étudiants inscrits';
  document.getElementById('modal-gestion-contenu').innerHTML = contenu;
  ouvrirModal('modal-gestion');
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
