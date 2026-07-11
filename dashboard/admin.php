<?php
/**
 * LearnUp — dashboard/admin.php
 * Interface administrateur complète
 */
require_once __DIR__ . '/../config/db.php';
exigerConnexion('admin');
$user = utilisateurCourant();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LearnUp — Administration</title>
  <link rel="stylesheet" href="/assets/css/style.css"/>
  <style>
    /* ── Admin spécifique ── */
    .admin-badge {
      font-size: 0.68rem;
      background: rgba(255,71,87,0.15);
      color: var(--rouge);
      padding: 2px 8px;
      border-radius: 6px;
      margin-left: 6px;
      font-weight: 600;
      letter-spacing: 0.05em;
    }
    .stat-card.danger .stat-icon { background: rgba(255,71,87,0.12); }
    .stat-card.danger .valeur    { color: var(--rouge); }

    .user-row td { vertical-align: middle; }
    .user-row:hover { background: rgba(108,99,255,0.04); }

    .role-badge {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 20px;
      font-size: 0.72rem;
      font-weight: 600;
    }
    .role-admin     { background: rgba(255,71,87,0.15);  color: var(--rouge); }
    .role-promoteur { background: rgba(255,140,66,0.15); color: var(--orange); }
    .role-enseignant{ background: rgba(0,212,170,0.15);  color: var(--menthe); }
    .role-etudiant  { background: rgba(108,99,255,0.12); color: var(--violet-cl); }

    .actif-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 5px;
    }
    .actif-dot.on  { background: var(--menthe); box-shadow: 0 0 6px var(--menthe); }
    .actif-dot.off { background: var(--rouge); }

    .filters {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
      margin-bottom: 20px;
    }
    .filters input, .filters select {
      background: var(--surface2);
      border: 1px solid var(--border);
      color: var(--texte);
      padding: 8px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      outline: none;
    }
    .filters input:focus, .filters select:focus {
      border-color: var(--violet);
    }

    .activite-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 12px 0;
      border-bottom: 1px solid var(--border);
    }
    .activite-item:last-child { border-bottom: none; }
    .activite-icon {
      width: 36px; height: 36px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }
    .activite-icon.inscription   { background: rgba(108,99,255,0.12); }
    .activite-icon.certificat    { background: rgba(255,140,66,0.12); }
    .activite-icon.nouveau_compte{ background: rgba(0,212,170,0.12); }

    .mini-chart {
      display: flex;
      align-items: flex-end;
      gap: 6px;
      height: 60px;
      margin-top: 8px;
    }
    .mini-bar {
      flex: 1;
      background: var(--violet-bg);
      border-radius: 4px 4px 0 0;
      transition: height 0.6s ease;
      min-width: 20px;
      position: relative;
      cursor: default;
    }
    .mini-bar:hover { background: var(--violet); }
    .mini-bar-label {
      font-size: 0.6rem;
      color: var(--texte3);
      text-align: center;
      margin-top: 4px;
    }

    .action-btns { display: flex; gap: 6px; flex-wrap: wrap; }
    .btn-icon {
      width: 30px; height: 30px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      transition: all 0.2s;
    }
    .btn-icon:hover { transform: scale(1.1); }
    .btn-icon.edit  { background: rgba(108,99,255,0.15); }
    .btn-icon.del   { background: rgba(255,71,87,0.12); }
    .btn-icon.toggle{ background: rgba(0,212,170,0.12); }
    .btn-icon.reset { background: rgba(255,140,66,0.12); }

    /* Modal grid */
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 24px;
    }
    .section-title h1 { margin-bottom: 2px; }
    .section-title p  { font-size: 0.85rem; }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="navbar-brand">Learn<span>Up</span><span class="admin-badge">⚙️ ADMIN</span></div>
  <ul class="navbar-nav">
    <li><a href="#" onclick="afficherSection('accueil')"      id="nav-accueil"      class="active">🏠 Dashboard</a></li>
    <li><a href="#" onclick="afficherSection('utilisateurs')" id="nav-utilisateurs">👥 Utilisateurs</a></li>
    <li><a href="#" onclick="afficherSection('modules')"      id="nav-modules">📦 Modules</a></li>
    <li><a href="#" onclick="afficherSection('cours')"        id="nav-cours">📚 Cours</a></li>
    <li><a href="#" onclick="afficherSection('certificats')"  id="nav-certificats">🏆 Certificats</a></li>
    <li><a href="#" onclick="afficherSection('activite')"     id="nav-activite">📋 Activité</a></li>
  </ul>
  <div class="navbar-user">
    <span style="font-size:0.85rem;color:var(--rouge)">⚙️ <?= htmlspecialchars($user['prenom']) ?></span>
    <button class="btn btn-outline btn-sm" onclick="seDeconnecter()">Déconnexion</button>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Administration</div>
      <a href="#" class="sidebar-link active" onclick="afficherSection('accueil')"      id="sl-accueil">      <span class="icon">🏠</span> Vue générale</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('utilisateurs')" id="sl-utilisateurs"> <span class="icon">👥</span> Utilisateurs</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('modules')"      id="sl-modules">      <span class="icon">📦</span> Modules</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('cours')"        id="sl-cours">        <span class="icon">📚</span> Cours</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('certificats')"  id="sl-certificats">  <span class="icon">🏆</span> Certificats</a>
      <a href="#" class="sidebar-link"        onclick="afficherSection('activite')"     id="sl-activite">     <span class="icon">📋</span> Activité récente</a>
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
        <h1>Vue générale ⚙️</h1>
        <p>Supervision complète de la plateforme LearnUp</p>
      </div>

      <div class="stats-grid" id="admin-stats-top" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));"></div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:24px;">
        <!-- Répartition utilisateurs -->
        <div class="card">
          <h3 style="font-size:1rem;margin-bottom:16px;">👥 Répartition des utilisateurs</h3>
          <div id="admin-repartition"></div>
        </div>
        <!-- Nouvelles inscriptions -->
        <div class="card">
          <h3 style="font-size:1rem;margin-bottom:6px;">📈 Inscriptions (6 derniers mois)</h3>
          <div class="mini-chart" id="admin-chart-inscriptions"></div>
          <div style="display:flex;gap:6px;margin-top:4px;" id="admin-chart-labels"></div>
        </div>
      </div>

      <div id="admin-promoteurs-attente" style="display:none;margin-top:24px;"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-top:20px;" id="admin-stats-bottom"></div>
    </section>

    <!-- ══ UTILISATEURS ══ -->
    <section id="section-utilisateurs" style="display:none;">
      <div class="section-header">
        <div class="section-title">
          <h1>Gestion des utilisateurs</h1>
          <p>Créer, modifier ou supprimer des comptes</p>
        </div>
        <button class="btn btn-primary" onclick="ouvrirModal('modal-creer-user')">➕ Nouvel utilisateur</button>
      </div>

      <div class="filters">
        <input type="text"   id="filtre-search" placeholder="🔍 Rechercher…" oninput="chargerUtilisateurs()" style="min-width:220px;"/>
        <select id="filtre-role" onchange="chargerUtilisateurs()">
          <option value="">Tous les rôles</option>
          <option value="etudiant">Étudiant</option>
          <option value="enseignant">Enseignant</option>
          <option value="promoteur">Promoteur</option>
          <option value="admin">Admin</option>
        </select>
        <select id="filtre-actif" onchange="chargerUtilisateurs()">
          <option value="">Tous les statuts</option>
          <option value="1">Actifs</option>
          <option value="0">Désactivés</option>
        </select>
        <span id="users-count" style="font-size:0.82rem;color:var(--texte3);margin-left:auto;"></span>
      </div>

      <div class="table-wrapper">
        <table id="table-utilisateurs">
          <thead>
            <tr>
              <th>#</th>
              <th>Nom</th>
              <th>E-mail</th>
              <th>Rôle</th>
              <th>Statut</th>
              <th>Inscrit le</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tbody-utilisateurs"></tbody>
        </table>
      </div>
    </section>

    <!-- ══ MODULES ══ -->
    <section id="section-modules" style="display:none;">
      <div class="section-header">
        <div class="section-title">
          <h1>Gestion des modules</h1>
          <p>Vue globale de tous les modules de la plateforme</p>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>#</th><th>Titre</th><th>Promoteur</th><th>Cours</th><th>Certificats</th><th>Créé le</th><th>Actions</th></tr>
          </thead>
          <tbody id="tbody-modules"></tbody>
        </table>
      </div>
    </section>

    <!-- ══ COURS ══ -->
    <section id="section-cours" style="display:none;">
      <div class="section-header">
        <div class="section-title">
          <h1>Gestion des cours</h1>
          <p>Tous les cours créés sur la plateforme</p>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>#</th><th>Titre</th><th>Module</th><th>Enseignant</th><th>Niveau</th><th>Leçons</th><th>Inscrits</th><th>Actions</th></tr>
          </thead>
          <tbody id="tbody-cours"></tbody>
        </table>
      </div>
    </section>

    <!-- ══ CERTIFICATS ══ -->
    <section id="section-certificats" style="display:none;">
      <div class="section-header">
        <div class="section-title">
          <h1>Certificats délivrés</h1>
          <p>Historique complet des certifications</p>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>#</th><th>Étudiant</th><th>Module</th><th>Délivré le</th><th>Code</th><th>Actions</th></tr>
          </thead>
          <tbody id="tbody-certificats"></tbody>
        </table>
      </div>
    </section>

    <!-- ══ ACTIVITÉ ══ -->
    <section id="section-activite" style="display:none;">
      <div class="section-header">
        <div class="section-title">
          <h1>Activité récente</h1>
          <p>Les dernières actions sur la plateforme</p>
        </div>
        <button class="btn btn-outline btn-sm" onclick="chargerActivite()">🔄 Actualiser</button>
      </div>
      <div class="card">
        <div id="liste-activite"></div>
      </div>
    </section>

  </main>
</div>

<!-- ══ MODAL : Créer utilisateur ══ -->
<div class="modal-overlay" id="modal-creer-user">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">➕ Nouvel utilisateur</h3>
      <button class="modal-close" onclick="fermerModal('modal-creer-user')">×</button>
    </div>
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Nom *</label>
        <input type="text" id="new-nom" class="form-control" placeholder="TANKOU"/>
      </div>
      <div class="form-group">
        <label class="form-label">Prénom *</label>
        <input type="text" id="new-prenom" class="form-control" placeholder="Joël Valdo"/>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">E-mail *</label>
      <input type="email" id="new-email" class="form-control" placeholder="user@learnup.cm"/>
    </div>
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Mot de passe *</label>
        <input type="password" id="new-mdp" class="form-control" placeholder="Min. 8 caractères"/>
      </div>
      <div class="form-group">
        <label class="form-label">Rôle *</label>
        <select id="new-role" class="form-control">
          <option value="etudiant">Étudiant</option>
          <option value="enseignant">Enseignant</option>
          <option value="promoteur">Promoteur</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>
    <div style="display:flex;gap:12px;margin-top:8px;">
      <button class="btn btn-primary" id="btn-creer-user" onclick="creerUtilisateur()">✅ Créer</button>
      <button class="btn btn-outline" onclick="fermerModal('modal-creer-user')">Annuler</button>
    </div>
  </div>
</div>

<!-- ══ MODAL : Changer rôle ══ -->
<div class="modal-overlay" id="modal-role">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">🔄 Changer le rôle</h3>
      <button class="modal-close" onclick="fermerModal('modal-role')">×</button>
    </div>
    <p style="margin-bottom:16px;" id="modal-role-nom"></p>
    <input type="hidden" id="modal-role-user-id"/>
    <div class="form-group">
      <label class="form-label">Nouveau rôle</label>
      <select id="modal-role-select" class="form-control">
        <option value="etudiant">Étudiant</option>
        <option value="enseignant">Enseignant</option>
        <option value="promoteur">Promoteur</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    <div style="display:flex;gap:12px;margin-top:8px;">
      <button class="btn btn-primary" onclick="confirmerChangerRole()">✅ Confirmer</button>
      <button class="btn btn-outline" onclick="fermerModal('modal-role')">Annuler</button>
    </div>
  </div>
</div>

<!-- ══ MODAL : Reset mot de passe ══ -->
<div class="modal-overlay" id="modal-reset-mdp">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">🔑 Réinitialiser le mot de passe</h3>
      <button class="modal-close" onclick="fermerModal('modal-reset-mdp')">×</button>
    </div>
    <p style="margin-bottom:16px;" id="modal-reset-nom"></p>
    <input type="hidden" id="modal-reset-user-id"/>
    <div class="form-group">
      <label class="form-label">Nouveau mot de passe</label>
      <input type="password" id="input-reset-mdp" class="form-control" placeholder="Min. 8 caractères"/>
    </div>
    <div style="display:flex;gap:12px;margin-top:8px;">
      <button class="btn btn-primary" onclick="confirmerResetMdp()">✅ Réinitialiser</button>
      <button class="btn btn-outline" onclick="fermerModal('modal-reset-mdp')">Annuler</button>
    </div>
  </div>
</div>

<script src="/assets/js/app.js"></script>
<script>
/* ══════════════════════════════════════════════════════════════
   UTILITAIRE AJAX → api/admin.php
   ══════════════════════════════════════════════════════════════ */
async function adminAjax(action, params = {}) {
  const body = new URLSearchParams({ action, ...params });
  try {
    const res = await fetch('/api/admin.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body,
    });
    return await res.json();
  } catch (e) {
    console.error('[Admin] Erreur:', e);
    return { succes: false, message: 'Erreur réseau.' };
  }
}

/* ══ Navigation ══ */
function afficherSection(id) {
  document.querySelectorAll('main section').forEach(s => s.style.display = 'none');
  document.getElementById('section-' + id).style.display = 'block';
  document.querySelectorAll('.sidebar-link, .navbar-nav a').forEach(a => a.classList.remove('active'));
  document.getElementById('sl-'  + id)?.classList.add('active');
  document.getElementById('nav-' + id)?.classList.add('active');
  const loaders = {
    accueil:       chargerAccueil,
    utilisateurs:  chargerUtilisateurs,
    modules:       chargerModules,
    cours:         chargerCours,
    certificats:   chargerCertificats,
    activite:      chargerActivite,
  };
  loaders[id]?.();
}

/* ══════════════════════════════════════════════════════════════
   ACCUEIL
   ══════════════════════════════════════════════════════════════ */
async function chargerAccueil() {
  const data = await adminAjax('admin_stats');
  if (!data.succes) return;
  const s = data.stats;

  // Stats du haut
  document.getElementById('admin-stats-top').innerHTML = `
    <div class="stat-card"><div class="stat-icon violet">👥</div><div class="stat-info"><div class="valeur">${s.total_users}</div><div class="label">Utilisateurs total</div></div></div>
    <div class="stat-card"><div class="stat-icon menthe">📦</div><div class="stat-info"><div class="valeur">${s.modules}</div><div class="label">Modules</div></div></div>
    <div class="stat-card"><div class="stat-icon orange">📚</div><div class="stat-info"><div class="valeur">${s.cours}</div><div class="label">Cours</div></div></div>
    <div class="stat-card"><div class="stat-icon violet">🏆</div><div class="stat-info"><div class="valeur">${s.certificats}</div><div class="label">Certificats</div></div></div>
    <div class="stat-card"><div class="stat-icon menthe">📝</div><div class="stat-info"><div class="valeur">${s.inscriptions}</div><div class="label">Inscriptions</div></div></div>
    <div class="stat-card"><div class="stat-icon orange">🆕</div><div class="stat-info"><div class="valeur">${s.nouveaux_30j}</div><div class="label">Nouveaux (30j)</div></div></div>
  `;

  // Répartition utilisateurs
  const roles = s.utilisateurs;
  const repartEl = document.getElementById('admin-repartition');
  const roleConfig = {
    etudiant:   { icon: '🎓', label: 'Étudiants',   cls: 'role-etudiant'  },
    enseignant: { icon: '👨‍🏫', label: 'Enseignants', cls: 'role-enseignant' },
    promoteur:  { icon: '🏛️', label: 'Promoteurs',   cls: 'role-promoteur'  },
    admin:      { icon: '⚙️', label: 'Admins',       cls: 'role-admin'     },
  };
  repartEl.innerHTML = Object.entries(roleConfig).map(([role, cfg]) => {
    const info = roles[role] || { total: 0, actifs: 0 };
    const pct  = s.total_users > 0 ? Math.round(info.total / s.total_users * 100) : 0;
    return `
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
        <span class="role-badge ${cfg.cls}">${cfg.icon} ${cfg.label}</span>
        <div style="flex:1;background:var(--surface2);border-radius:4px;height:6px;overflow:hidden;">
          <div style="height:100%;border-radius:4px;background:var(--violet);width:${pct}%;transition:width 0.6s ease;"></div>
        </div>
        <span style="font-size:0.8rem;color:var(--texte2);min-width:60px;text-align:right;">${info.total} (${pct}%)</span>
      </div>`;
  }).join('');

  // Mini chart inscriptions
  const mois = s.inscriptions_mois || [];
  if (mois.length) {
    const max = Math.max(...mois.map(m => m.nb), 1);
    const chartEl  = document.getElementById('admin-chart-inscriptions');
    const labelEl  = document.getElementById('admin-chart-labels');
    chartEl.innerHTML = mois.map(m => {
      const h = Math.round((m.nb / max) * 100);
      return `<div class="mini-bar" style="height:${Math.max(h, 4)}%" title="${m.mois} : ${m.nb} inscrit(s)"></div>`;
    }).join('');
    labelEl.innerHTML = mois.map(m => {
      const label = m.mois.substring(5); // MM
      return `<div style="flex:1;font-size:0.6rem;color:var(--texte3);text-align:center;">${label}</div>`;
    }).join('');
  }

  // Promoteurs en attente
  const promoData = await adminAjax('admin_promoteurs_attente');
  const promoEl = document.getElementById('admin-promoteurs-attente');
  if (promoData.succes && promoData.promoteurs.length > 0) {
    promoEl.style.display = 'block';
    promoEl.innerHTML = '<div class="card" style="margin-bottom:24px;border:1px solid rgba(255,165,0,0.3);background:rgba(255,165,0,0.05);">'
      + '<div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;"><span style="font-size:1.3rem;">⏳</span>'
      + '<h3 style="margin:0;color:var(--orange);">Promoteurs en attente (' + promoData.promoteurs.length + ')</h3></div>'
      + promoData.promoteurs.map(p => `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-top:1px solid var(--border);flex-wrap:wrap;gap:10px;">
          <div>
            <div style="font-weight:600;color:var(--blanc);">${escHtml(p.prenom)} ${escHtml(p.nom)}</div>
            <div style="font-size:0.82rem;color:var(--texte3);">${escHtml(p.email)}</div>
            <div style="font-size:0.75rem;color:var(--texte3);">Inscrit le ${new Date(p.cree_le).toLocaleDateString('fr-FR')}</div>
          </div>
          <div style="display:flex;gap:8px;">
            <button class="btn btn-sm" style="background:rgba(0,212,170,0.15);color:var(--menthe);border:1px solid rgba(0,212,170,0.3);" onclick="validerPromoteur(${p.id})">✅ Valider</button>
            <button class="btn btn-sm" style="background:rgba(255,80,80,0.15);color:#ff5050;border:1px solid rgba(255,80,80,0.3);" onclick="rejeterPromoteur(${p.id},'${escHtml(p.prenom)} ${escHtml(p.nom)}')">❌ Rejeter</button>
          </div>
        </div>`).join('')
      + '</div>';
  } else {
    promoEl.style.display = 'none';
  }

  // Stats supplémentaires
  document.getElementById('admin-stats-bottom').innerHTML = `
    <div class="card" style="padding:18px;">
      <div style="font-size:1.8rem;font-weight:800;color:var(--violet);">${s.lecons}</div>
      <div style="color:var(--texte2);font-size:0.85rem;margin-top:4px;">📖 Leçons créées</div>
    </div>
    <div class="card" style="padding:18px;">
      <div style="font-size:1.8rem;font-weight:800;color:var(--menthe);">${s.evaluations}</div>
      <div style="color:var(--texte2);font-size:0.85rem;margin-top:4px;">✏️ Évaluations</div>
    </div>
    <div class="card" style="padding:18px;">
      <div style="font-size:1.8rem;font-weight:800;color:var(--orange);">${s.resultats}</div>
      <div style="color:var(--texte2);font-size:0.85rem;margin-top:4px;">📊 Résultats enregistrés</div>
    </div>
  `;
}

/* ══════════════════════════════════════════════════════════════
   UTILISATEURS
   ══════════════════════════════════════════════════════════════ */
async function validerPromoteur(id) {
  const data = await adminAjax('admin_valider_promoteur', { user_id: id });
  if (data.succes) { toast(data.message, 'succes'); chargerAccueil(); }
  else toast(data.message, 'erreur');
}

async function rejeterPromoteur(id, nom) {
  confirmer(`⚠️ Rejeter et supprimer la demande de "${nom}" ?`, async () => {
    const data = await adminAjax('admin_rejeter_promoteur', { user_id: id });
    if (data.succes) { toast(data.message, 'succes'); chargerAccueil(); }
    else toast(data.message, 'erreur');
  });
}

async function chargerUtilisateurs() {
  const search = document.getElementById('filtre-search')?.value || '';
  const role   = document.getElementById('filtre-role')?.value || '';
  const actif  = document.getElementById('filtre-actif')?.value || '';

  const data = await adminAjax('admin_utilisateurs', { search, role, actif });
  const tbody = document.getElementById('tbody-utilisateurs');

  if (!data.succes) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--rouge);">Erreur de chargement.</td></tr>`;
    return;
  }

  const users = data.utilisateurs;
  document.getElementById('users-count').textContent = `${users.length} utilisateur(s)`;

  if (!users.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--texte3);">Aucun utilisateur trouvé.</td></tr>`;
    return;
  }

  tbody.innerHTML = users.map(u => `
    <tr class="user-row">
      <td style="color:var(--texte3);font-size:0.78rem;">#${u.id}</td>
      <td><strong style="color:var(--texte)">${escHtml(u.prenom)} ${escHtml(u.nom)}</strong> ${u.certifie ? `<svg viewBox="0 0 48 48" style="width:15px;height:15px;vertical-align:middle;margin-left:2px;" title="Compte certifié"><defs><linearGradient id="badgeCert${u.id}" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#6C63FF"/><stop offset="100%" stop-color="#00D4AA"/></linearGradient></defs><circle cx="24" cy="24" r="18" fill="url(#badgeCert${u.id})"/><path d="M15.5 24.5l5.2 5.2L33 18" fill="none" stroke="#FFFFFF" stroke-width="4.4" stroke-linecap="round" stroke-linejoin="round"/></svg>` : ''}</td>
      <td style="color:var(--texte3);font-size:0.82rem;">${escHtml(u.email)}</td>
      <td><span class="role-badge role-${u.role}">${u.role}</span></td>
      <td><span class="actif-dot ${u.actif ? 'on' : 'off'}"></span>${u.actif ? 'Actif' : 'Désactivé'}</td>
      <td style="font-size:0.78rem;color:var(--texte3);">${formatDate(u.cree_le)}</td>
      <td>
        <div class="action-btns">
          <button class="btn-icon toggle" title="${u.actif ? 'Désactiver' : 'Activer'}"
            onclick="toggleActif(${u.id}, ${u.actif ? 0 : 1})">${u.actif ? '🔒' : '🔓'}</button>
          <button class="btn-icon" title="${u.certifie ? 'Retirer la certification' : 'Certifier ce compte'}"
            onclick="toggleCertifie(${u.id}, ${u.certifie ? 0 : 1})" style="color:${u.certifie ? '#00D4AA' : 'var(--texte3)'};">${u.certifie ? '✔️' : '⭕'}</button>
          <button class="btn-icon edit"   title="Changer le rôle"
            onclick="ouvrirChangerRole(${u.id}, '${escHtml(u.prenom)} ${escHtml(u.nom)}', '${u.role}')">🔄</button>
          <button class="btn-icon reset"  title="Réinitialiser le mot de passe"
            onclick="ouvrirResetMdp(${u.id}, '${escHtml(u.prenom)} ${escHtml(u.nom)}')">🔑</button>
          <button class="btn-icon del"    title="Supprimer"
            onclick="supprimerUser(${u.id}, '${escHtml(u.prenom)} ${escHtml(u.nom)}')">🗑️</button>
        </div>
      </td>
    </tr>`).join('');
}

async function creerUtilisateur() {
  const btn = document.getElementById('btn-creer-user');
  btn.disabled = true;
  const data = await adminAjax('admin_creer_user', {
    nom:          document.getElementById('new-nom').value,
    prenom:       document.getElementById('new-prenom').value,
    email:        document.getElementById('new-email').value,
    mot_de_passe: document.getElementById('new-mdp').value,
    role:         document.getElementById('new-role').value,
  });
  btn.disabled = false;
  if (data.succes) {
    toast(data.message, 'succes');
    fermerModal('modal-creer-user');
    chargerUtilisateurs();
    ['new-nom','new-prenom','new-email','new-mdp'].forEach(id => document.getElementById(id).value = '');
  } else {
    toast(data.message, 'erreur');
  }
}

async function toggleActif(id, actif) {
  const data = await adminAjax('admin_activer_user', { user_id: id, actif });
  if (data.succes) { toast(data.message, 'succes'); chargerUtilisateurs(); }
  else toast(data.message, 'erreur');
}

async function toggleCertifie(id, certifie) {
  const data = await adminAjax('admin_toggle_certifie', { user_id: id, certifie });
  if (data.succes) { toast(data.message, 'succes'); chargerUtilisateurs(); }
  else toast(data.message, 'erreur');
}

function ouvrirChangerRole(id, nom, roleActuel) {
  document.getElementById('modal-role-user-id').value = id;
  document.getElementById('modal-role-nom').textContent = `Utilisateur : ${nom}`;
  document.getElementById('modal-role-select').value = roleActuel;
  ouvrirModal('modal-role');
}

async function confirmerChangerRole() {
  const id   = document.getElementById('modal-role-user-id').value;
  const role = document.getElementById('modal-role-select').value;
  const data = await adminAjax('admin_changer_role', { user_id: id, role });
  if (data.succes) {
    toast(data.message, 'succes');
    fermerModal('modal-role');
    chargerUtilisateurs();
  } else toast(data.message, 'erreur');
}

function ouvrirResetMdp(id, nom) {
  document.getElementById('modal-reset-user-id').value = id;
  document.getElementById('modal-reset-nom').textContent = `Utilisateur : ${nom}`;
  document.getElementById('input-reset-mdp').value = '';
  ouvrirModal('modal-reset-mdp');
}

async function confirmerResetMdp() {
  const id  = document.getElementById('modal-reset-user-id').value;
  const mdp = document.getElementById('input-reset-mdp').value;
  const data = await adminAjax('admin_reset_mdp', { user_id: id, mot_de_passe: mdp });
  if (data.succes) {
    toast(data.message, 'succes');
    fermerModal('modal-reset-mdp');
  } else toast(data.message, 'erreur');
}

async function supprimerUser(id, nom) {
  confirmer(`⚠️ Supprimer définitivement "${nom}" et toutes ses données ?`, async () => {
    const data = await adminAjax('admin_supprimer_user', { user_id: id });
    if (data.succes) { toast(data.message, 'succes'); chargerUtilisateurs(); }
    else toast(data.message, 'erreur');
  });
}

/* ══════════════════════════════════════════════════════════════
   MODULES
   ══════════════════════════════════════════════════════════════ */
async function chargerModules() {
  const data = await adminAjax('admin_modules');
  const tbody = document.getElementById('tbody-modules');
  if (!data.succes || !data.modules.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--texte3);">Aucun module.</td></tr>`;
    return;
  }
  tbody.innerHTML = data.modules.map(m => `
    <tr>
      <td style="color:var(--texte3);font-size:0.78rem;">#${m.id}</td>
      <td><strong style="color:var(--texte)">${escHtml(m.titre)}</strong></td>
      <td style="color:var(--texte3);font-size:0.82rem;">${escHtml(m.promoteur)}</td>
      <td><span class="badge badge-violet">${m.nb_cours}</span></td>
      <td><span class="badge badge-orange">${m.nb_certificats}</span></td>
      <td style="font-size:0.78rem;color:var(--texte3);">${formatDate(m.cree_le)}</td>
      <td>
        <button class="btn-icon del" title="Supprimer" onclick="supprimerModule(${m.id})">🗑️</button>
      </td>
    </tr>`).join('');
}

async function supprimerModule(id) {
  confirmer('⚠️ Supprimer ce module et tous ses cours associés ?', async () => {
    const data = await adminAjax('admin_supprimer_module', { module_id: id });
    if (data.succes) { toast(data.message, 'succes'); chargerModules(); }
    else toast(data.message, 'erreur');
  });
}

/* ══════════════════════════════════════════════════════════════
   COURS
   ══════════════════════════════════════════════════════════════ */
async function chargerCours() {
  const data = await adminAjax('admin_cours');
  const tbody = document.getElementById('tbody-cours');
  if (!data.succes || !data.cours.length) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--texte3);">Aucun cours.</td></tr>`;
    return;
  }
  const niveaux = { debutant: '🟢 Débutant', intermediaire: '🟡 Intermédiaire', avance: '🔴 Avancé' };
  tbody.innerHTML = data.cours.map(c => `
    <tr>
      <td style="color:var(--texte3);font-size:0.78rem;">#${c.id}</td>
      <td><strong style="color:var(--texte)">${escHtml(c.titre)}</strong></td>
      <td style="color:var(--texte3);font-size:0.82rem;">${escHtml(c.module_titre)}</td>
      <td style="color:var(--texte3);font-size:0.82rem;">${escHtml(c.enseignant)}</td>
      <td style="font-size:0.78rem;">${niveaux[c.niveau] || c.niveau}</td>
      <td><span class="badge badge-violet">${c.nb_lecons}</span></td>
      <td><span class="badge badge-menthe">${c.nb_inscrits}</span></td>
      <td><span class="actif-dot ${c.actif ? 'on' : 'off'}"></span>${c.actif ? 'Actif' : 'Supprimé'}</td>
      <td>
        ${!c.actif ? `<button class="btn-icon" title="Réactiver" onclick="reactiverCours(${c.id})" style="color:var(--menthe);">🔓</button>` : ''}
        <button class="btn-icon del" title="Supprimer définitivement" onclick="supprimerCours(${c.id})">🗑️</button>
      </td>
    </tr>`).join('');
}

async function supprimerCours(id) {
  confirmer('⚠️ Supprimer définitivement ce cours et toutes les données associées ?', async () => {
    const data = await adminAjax('admin_supprimer_cours', { cours_id: id });
    if (data.succes) { toast(data.message, 'succes'); chargerCours(); }
    else toast(data.message, 'erreur');
  });
}

async function reactiverCours(id) {
  const data = await adminAjax('admin_reactiver_cours', { cours_id: id });
  if (data.succes) { toast('Cours réactivé !', 'succes'); chargerCours(); }
  else toast(data.message, 'erreur');
}

/* ══════════════════════════════════════════════════════════════
   CERTIFICATS
   ══════════════════════════════════════════════════════════════ */
async function chargerCertificats() {
  const data = await adminAjax('admin_certificats');
  const tbody = document.getElementById('tbody-certificats');
  if (!data.succes || !data.certificats.length) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--texte3);">Aucun certificat délivré.</td></tr>`;
    return;
  }
  tbody.innerHTML = data.certificats.map(c => `
    <tr>
      <td style="color:var(--texte3);font-size:0.78rem;">#${c.id}</td>
      <td>
        <strong style="color:var(--texte)">${escHtml(c.etudiant)}</strong>
        <div style="font-size:0.75rem;color:var(--texte3);">${escHtml(c.etudiant_email)}</div>
      </td>
      <td>${escHtml(c.module_titre)}</td>
      <td style="font-size:0.78rem;color:var(--texte3);">${formatDate(c.delivre_le)}</td>
      <td><code style="color:var(--violet-cl);font-size:0.72rem;">${c.code_unique.substring(0,16).toUpperCase()}…</code></td>
      <td>
        <button class="btn-icon del" title="Révoquer" onclick="supprimerCertificat(${c.id})">🗑️</button>
      </td>
    </tr>`).join('');
}

async function supprimerCertificat(id) {
  confirmer('⚠️ Révoquer ce certificat ?', async () => {
    const data = await adminAjax('admin_supprimer_certificat', { cert_id: id });
    if (data.succes) { toast(data.message, 'succes'); chargerCertificats(); }
    else toast(data.message, 'erreur');
  });
}

/* ══════════════════════════════════════════════════════════════
   ACTIVITÉ RÉCENTE
   ══════════════════════════════════════════════════════════════ */
async function chargerActivite() {
  const data = await adminAjax('admin_activite');
  const el   = document.getElementById('liste-activite');
  if (!data.succes || !data.activite.length) {
    el.innerHTML = `<div class="empty-state"><div class="icon">📋</div><h3>Aucune activité</h3></div>`;
    return;
  }
  const icons = {
    inscription:    { icon: '📝', label: 'Inscrit au cours',     cls: 'inscription'    },
    certificat:     { icon: '🏆', label: 'Certificat délivré',   cls: 'certificat'     },
    nouveau_compte: { icon: '👤', label: 'Nouveau compte',        cls: 'nouveau_compte' },
  };
  el.innerHTML = data.activite.map(a => {
    const cfg = icons[a.type] || { icon: '❓', label: a.type, cls: '' };
    return `
      <div class="activite-item">
        <div class="activite-icon ${cfg.cls}">${cfg.icon}</div>
        <div style="flex:1;">
          <div style="font-size:0.88rem;color:var(--texte);">
            <strong>${escHtml(a.acteur)}</strong>
            <span style="color:var(--texte3);"> — ${cfg.label}</span>
          </div>
          <div style="font-size:0.78rem;color:var(--texte3);margin-top:2px;">${escHtml(a.cible)}</div>
        </div>
        <div style="font-size:0.75rem;color:var(--texte3);white-space:nowrap;">${formatDate(a.date_action)}</div>
      </div>`;
  }).join('');
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
