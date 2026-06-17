<?php
/**
 * LearnUp — dashboard/promoteur.php
 */
require_once __DIR__ . '/../config/db.php';
exigerConnexion('promoteur');
$user = utilisateurCourant();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LearnUp — Espace Promoteur</title>
  <link rel="stylesheet" href="/assets/css/style.css"/>
</head>
<body>

<nav class="navbar">
  <div class="navbar-brand">Learn<span>Up</span> <span style="font-size:0.7rem;background:rgba(255,140,66,0.15);color:var(--orange);padding:2px 8px;border-radius:6px;margin-left:6px;">Promoteur</span></div>
  <ul class="navbar-nav">
    <li><a href="#" onclick="afficherSection('accueil')"   id="nav-accueil"  class="active">🏠 Accueil</a></li>
    <li><a href="#" onclick="afficherSection('modules')"   id="nav-modules">📦 Modules</a></li>
    <li><a href="#" onclick="afficherSection('certificats')" id="nav-certificats">🏆 Certificats</a></li>
    <li><a href="#" onclick="afficherSection('utilisateurs')" id="nav-users">👥 Utilisateurs</a></li>
    <li><a href="#" onclick="afficherSection('suggestions')" id="nav-suggestions">💡 Suggestions</a></li>
  </ul>
  <div class="navbar-user">
    <span style="font-size:0.85rem;color:var(--texte2)">🏛️ <?= htmlspecialchars($user['prenom']) ?></span>
    <button class="btn btn-outline btn-sm" onclick="seDeconnecter()">Déconnexion</button>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="#" class="sidebar-link active" onclick="afficherSection('accueil')"      id="sl-accueil">     <span class="icon">🏠</span> Tableau de bord</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('modules')"      id="sl-modules">     <span class="icon">📦</span> Modules</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('certificats')"  id="sl-certificats"> <span class="icon">🏆</span> Certificats</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('utilisateurs')" id="sl-utilisateurs"><span class="icon">👥</span> Utilisateurs</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('suggestions')"  id="sl-suggestions"> <span class="icon">💡</span> Suggestions</a>
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
        <h1>Tableau de bord 🏛️</h1>
        <p>Vue d'ensemble de la plateforme LearnUp</p>
      </div>
      <div class="stats-grid" id="stats-promoteur"></div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:8px;">
        <div>
          <h2 style="font-size:1.1rem;margin-bottom:14px;">Modules récents</h2>
          <div id="modules-recents"></div>
        </div>
        <div>
          <h2 style="font-size:1.1rem;margin-bottom:14px;">Certificats récents</h2>
          <div id="certificats-recents"></div>
        </div>
      </div>
    </section>

    <!-- ══ MODULES ══ -->
    <section id="section-modules" style="display:none;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div><h1>Modules de cours</h1><p>Organisez les cours en modules thématiques</p></div>
        <button class="btn btn-primary" onclick="ouvrirModal('modal-module')">➕ Nouveau module</button>
      </div>
      <div id="liste-modules" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;"></div>
    </section>

    <!-- ══ CERTIFICATS ══ -->
    <section id="section-certificats" style="display:none;">
      <div style="margin-bottom:24px;">
        <h1>Gestion des certificats 🏆</h1>
        <p>Attribuez des certificats aux étudiants ayant validé un module</p>
      </div>
      <div id="liste-certifs-promoteur"></div>
    </section>

    <!-- ══ UTILISATEURS ══ -->
    <section id="section-utilisateurs" style="display:none;">
      <div style="margin-bottom:24px;">
        <h1>Utilisateurs</h1>
        <p>Tous les membres de la plateforme</p>
      </div>
      <div id="liste-utilisateurs"></div>
    </section>

    <!-- ══ SUGGESTIONS DE MODULES ══ -->
    <section id="section-suggestions" style="display:none;">
      <div style="margin-bottom:24px;">
        <h1>💡 Suggestions de modules</h1>
        <p>Propositions de nouveaux modules soumises par les enseignants</p>
      </div>
      <div id="liste-suggestions-promoteur"></div>
    </section>

  </main>
</div>

<!-- ══ MODAL : Créer module ══ -->
<div class="modal-overlay" id="modal-module">
  <div class="modal" style="max-width:520px;">
    <div class="modal-header">
      <h3 class="modal-titre">Nouveau module</h3>
      <button class="modal-fermer" onclick="fermerModal('modal-module')">✕</button>
    </div>
    <form onsubmit="creerModule(event)">
      <div class="form-group">
        <label class="form-label">Titre du module *</label>
        <input type="text" id="module-titre" class="form-control" placeholder="Ex: Développement Web" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea id="module-desc" class="form-control" rows="4" placeholder="Décrivez ce module..."></textarea>
      </div>
      <div style="display:flex;gap:12px;margin-top:8px;">
        <button type="submit" class="btn btn-primary" id="btn-creer-module">Créer le module</button>
        <button type="button" class="btn btn-outline" onclick="fermerModal('modal-module')">Annuler</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL : Attribuer certificat ══ -->
<div class="modal-overlay" id="modal-certificat">
  <div class="modal" style="max-width:500px;">
    <div class="modal-header">
      <h3 class="modal-titre">Attribuer un certificat</h3>
      <button class="modal-fermer" onclick="fermerModal('modal-certificat')">✕</button>
    </div>
    <div class="form-group">
      <label class="form-label">Module</label>
      <select id="cert-module" class="form-control"></select>
    </div>
    <div class="form-group">
      <label class="form-label">Étudiant (e-mail)</label>
      <input type="email" id="cert-etudiant-email" class="form-control" placeholder="etudiant@exemple.com"/>
    </div>
    <div style="display:flex;gap:12px;margin-top:8px;">
      <button class="btn btn-primary" onclick="attribuerCertificat()">🏆 Attribuer</button>
      <button class="btn btn-outline" onclick="fermerModal('modal-certificat')">Annuler</button>
    </div>
  </div>
</div>

<!-- ══ MODAL : Traiter une suggestion ══ -->
<div class="modal-overlay" id="modal-traitement">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header">
      <h3 id="modal-traitement-titre">Traiter la suggestion</h3>
      <button class="btn-close" onclick="fermerModal('modal-traitement')">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Commentaire pour l'enseignant (optionnel)</label>
        <textarea id="sug-commentaire" class="form-control" rows="3" placeholder="Expliquez votre décision…"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="fermerModal('modal-traitement')">Annuler</button>
      <button class="btn btn-primary" onclick="confirmerTraitement()">Confirmer</button>
    </div>
  </div>
</div>

<script src="/assets/js/app.js"></script>
<script>
/* ══ Navigation ══ */
function afficherSection(id) {
  document.querySelectorAll('main section').forEach(s => s.style.display = 'none');
  document.getElementById('section-' + id).style.display = 'block';
  document.querySelectorAll('.sidebar-link, .navbar-nav a').forEach(a => a.classList.remove('active'));
  document.getElementById('sl-'  + id)?.classList.add('active');
  document.getElementById('nav-' + id)?.classList.add('active');
  const loaders = {
    'accueil':      chargerAccueil,
    'modules':      chargerModules,
    'certificats':  chargerCertificats,
    'utilisateurs': chargerUtilisateurs,
    'suggestions':  chargerSuggestions,
  };
  loaders[id]?.();
}

/* ══ Accueil ══ */
async function chargerAccueil() {
  const [statsR, modulesR] = await Promise.all([ajax('stats_promoteur'), ajax('lister_modules')]);

  if (statsR.succes) {
    const s = statsR.stats;
    document.getElementById('stats-promoteur').innerHTML = `
      <div class="stat-card"><div class="stat-icon violet">📦</div><div class="stat-info"><div class="valeur">${s.modules}</div><div class="label">Modules</div></div></div>
      <div class="stat-card"><div class="stat-icon menthe">🏆</div><div class="stat-info"><div class="valeur">${s.certificats}</div><div class="label">Certificats délivrés</div></div></div>
      <div class="stat-card"><div class="stat-icon orange">👥</div><div class="stat-info"><div class="valeur">${s.etudiants}</div><div class="label">Étudiants actifs</div></div></div>`;
  }

  if (modulesR.succes) {
    const el = document.getElementById('modules-recents');
    el.innerHTML = modulesR.modules.slice(0,3).map(m => `
      <div class="card" style="margin-bottom:10px;padding:16px;">
        <strong style="color:var(--texte)">${escHtml(m.titre)}</strong>
        <div style="font-size:0.78rem;color:var(--texte3);margin-top:4px;">${m.nb_cours} cours</div>
      </div>`).join('') || '<div class="empty-state"><div class="icon">📦</div><h3>Aucun module</h3></div>';
  }

  document.getElementById('certificats-recents').innerHTML =
    `<div class="alerte alerte-info">ℹ️ Attribuez des certificats dans l'onglet <strong>Certificats</strong></div>`;
}

/* ══ Modules ══ */
async function chargerModules() {
  const data = await ajax('lister_modules');
  const el = document.getElementById('liste-modules');
  if (!data.succes || !data.modules.length) {
    el.innerHTML = `<div class="empty-state" style="grid-column:1/-1"><div class="icon">📦</div><h3>Aucun module</h3><button class="btn btn-primary" onclick="ouvrirModal('modal-module')">Créer un module</button></div>`;
    return;
  }
  el.innerHTML = data.modules.map(m => `
    <div class="card">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px;">
        <h3 style="font-size:1rem;">${escHtml(m.titre)}</h3>
        <span class="badge badge-violet">${m.nb_cours} cours</span>
      </div>
      <p style="font-size:0.82rem;margin-bottom:16px;">${escHtml(m.description||'')}</p>
      <div style="display:flex;gap:8px;">
        <button class="btn btn-outline btn-sm" onclick="ouvrirAttribuerCertificat(${m.id},'${escHtml(m.titre)}')">🏆 Certificat</button>
        <button class="btn btn-sm" style="background:rgba(255,71,87,0.1);color:var(--rouge);border:none;" onclick="supprimerModule(${m.id})">🗑️</button>
      </div>
    </div>`).join('');
}

async function creerModule(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-creer-module');
  btn.disabled = true;
  const data = await ajax('creer_module', {
    titre:       document.getElementById('module-titre').value,
    description: document.getElementById('module-desc').value,
  });
  if (data.succes) {
    toast('Module créé !', 'succes');
    fermerModal('modal-module');
    chargerModules();
  } else {
    toast(data.message, 'erreur');
  }
  btn.disabled = false;
}

async function supprimerModule(id) {
  confirmer('Supprimer ce module ? Les cours associés resteront disponibles.', async () => {
    const data = await ajax('supprimer_module', { module_id: id });
    if (data.succes) { toast('Module supprimé.', 'succes'); chargerModules(); }
  });
}

/* ══ Certificats ══ */
async function chargerCertificats() {
  const modulesR = await ajax('lister_modules');
  const el = document.getElementById('liste-certifs-promoteur');

  el.innerHTML = `
    <div class="card" style="margin-bottom:24px;">
      <h3 style="margin-bottom:16px;">Attribuer un certificat</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end;">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Module</label>
          <select id="cert-module-inline" class="form-control">
            ${(modulesR.modules||[]).map(m => `<option value="${m.id}">${escHtml(m.titre)}</option>`).join('')}
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">E-mail étudiant</label>
          <input type="email" id="cert-email-inline" class="form-control" placeholder="etudiant@exemple.com"/>
        </div>
        <button class="btn btn-primary" onclick="attribuerCertificatInline()">🏆 Attribuer</button>
      </div>
    </div>
    <div id="liste-certifs-table"></div>`;

  chargerListeCertificats();
}

async function chargerListeCertificats() {
  const data = await ajax('lister_certificats_promoteur');
  const el = document.getElementById('liste-certifs-table');
  if (!data.succes || !data.certificats?.length) {
    el.innerHTML = `<div class="empty-state"><div class="icon">🏆</div><h3>Aucun certificat délivré</h3></div>`;
    return;
  }
  el.innerHTML = `<div class="table-wrapper"><table>
    <thead><tr><th>Étudiant</th><th>Module</th><th>Délivré le</th><th>Code</th></tr></thead>
    <tbody>${data.certificats.map(c => `
      <tr>
        <td><strong style="color:var(--texte)">${escHtml(c.prenom)} ${escHtml(c.nom)}</strong></td>
        <td>${escHtml(c.module_titre)}</td>
        <td>${formatDate(c.delivre_le)}</td>
        <td><code style="color:var(--violet-cl);font-size:0.78rem">${c.code_unique.substring(0,14).toUpperCase()}...</code></td>
      </tr>`).join('')}
    </tbody></table></div>`;
}

async function attribuerCertificatInline() {
  const moduleId = document.getElementById('cert-module-inline').value;
  const email    = document.getElementById('cert-email-inline').value.trim();
  if (!email) { toast('Entrez un e-mail.', 'erreur'); return; }

  // Chercher l'étudiant par email
  const userR = await ajax('trouver_utilisateur', { email });
  if (!userR.succes) { toast('Étudiant introuvable.', 'erreur'); return; }

  const data = await ajax('attribuer_certificat', {
    etudiant_id: userR.user.id,
    module_id:   moduleId,
  });

  if (data.succes) { toast('Certificat attribué !', 'succes'); chargerListeCertificats(); }
  else toast(data.message, 'erreur');
}

/* ══ Utilisateurs ══ */
async function chargerUtilisateurs() {
  const data = await ajax('lister_utilisateurs');
  const el = document.getElementById('liste-utilisateurs');
  if (!data.succes || !data.utilisateurs?.length) {
    el.innerHTML = `<div class="empty-state"><div class="icon">👥</div><h3>Aucun utilisateur</h3></div>`;
    return;
  }
  el.innerHTML = `<div class="table-wrapper"><table>
    <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Inscrit le</th></tr></thead>
    <tbody>${data.utilisateurs.map(u => `
      <tr>
        <td><strong style="color:var(--texte)">${escHtml(u.prenom)} ${escHtml(u.nom)}</strong></td>
        <td style="color:var(--texte3)">${escHtml(u.email)}</td>
        <td><span class="badge ${u.role==='enseignant'?'badge-orange':u.role==='promoteur'?'badge-rouge':'badge-violet'}">${u.role}</span></td>
        <td>${formatDate(u.cree_le)}</td>
      </tr>`).join('')}
    </tbody></table></div>`;
}

async function seDeconnecter() {
  await ajax('deconnexion');
  window.location.href = '/';
}

/* ══ Suggestions de modules ══ */
async function chargerSuggestions() {
  const el = document.getElementById('liste-suggestions-promoteur');
  el.innerHTML = '<p style="color:var(--texte2)">Chargement…</p>';
  const data = await ajax('lister_suggestions');
  if (!data.succes || !data.suggestions.length) {
    el.innerHTML = `<div class="empty-state"><div class="icon">💡</div><h3>Aucune suggestion reçue</h3><p>Les enseignants n'ont pas encore soumis de suggestions.</p></div>`;
    return;
  }
  const badges = { en_attente: ['orange','⏳','En attente'], acceptee: ['menthe','✅','Acceptée'], refusee: ['rouge','❌','Refusée'] };
  el.innerHTML = data.suggestions.map(s => {
    const [couleur, icone, label] = badges[s.statut] || ['texte2','?','Inconnu'];
    const boutons = s.statut === 'en_attente' ? `
      <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
        <button class="btn btn-sm btn-primary" onclick="ouvrirTraitement(${s.id},'acceptee')">✅ Accepter</button>
        <button class="btn btn-sm btn-outline" style="color:var(--rouge);border-color:var(--rouge);" onclick="ouvrirTraitement(${s.id},'refusee')">❌ Refuser</button>
      </div>` : '';
    return `<div class="card" style="margin-bottom:16px;padding:20px;" id="sug-card-${s.id}">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="flex:1;">
          <div style="font-weight:700;font-size:1.05rem;margin-bottom:4px;">${escHtml(s.titre)}</div>
          <div style="font-size:0.85rem;color:var(--violet);margin-bottom:8px;">👤 ${escHtml(s.enseignant_prenom)} ${escHtml(s.enseignant_nom)}</div>
          ${s.description ? `<p style="color:var(--texte2);margin:4px 0;">${escHtml(s.description)}</p>` : ''}
          ${s.justification ? `<p style="color:var(--texte2);font-size:0.88rem;margin:4px 0;"><em>Justification : ${escHtml(s.justification)}</em></p>` : ''}
          ${s.commentaire ? `<p style="margin-top:8px;padding:8px 12px;background:rgba(108,99,255,0.08);border-radius:6px;font-size:0.9rem;"><strong>Votre réponse :</strong> ${escHtml(s.commentaire)}</p>` : ''}
          ${boutons}
        </div>
        <div style="text-align:right;flex-shrink:0;">
          <span class="badge badge-${couleur}">${icone} ${label}</span>
          <div style="font-size:0.8rem;color:var(--texte2);margin-top:6px;">${formatDate(s.cree_le)}</div>
        </div>
      </div>
    </div>`;
  }).join('');
}

let _sugId = null; let _sugStatut = null;
function ouvrirTraitement(id, statut) {
  _sugId = id; _sugStatut = statut;
  const titre = statut === 'acceptee' ? '✅ Accepter la suggestion' : '❌ Refuser la suggestion';
  document.getElementById('modal-traitement-titre').textContent = titre;
  document.getElementById('sug-commentaire').value = '';
  ouvrirModal('modal-traitement');
}
async function confirmerTraitement() {
  const commentaire = document.getElementById('sug-commentaire').value.trim();
  const data = await ajax('traiter_suggestion', { id: _sugId, statut: _sugStatut, commentaire });
  if (data.succes) {
    toast(data.message, 'succes');
    fermerModal('modal-traitement');
    chargerSuggestions();
  } else {
    toast(data.message || 'Erreur.', 'erreur');
  }
}

// Init
chargerAccueil();
</script>
</body>
</html>
