<?php
/**
 * LearnUp — api/admin.php
 * API dédiée à l'interface administrateur
 * Rôle requis : admin
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'admin_stats':              adminStats();              break;
    case 'admin_utilisateurs':       adminUtilisateurs();       break;
    case 'admin_activer_user':       adminActiverUser();        break;
    case 'admin_changer_role':       adminChangerRole();        break;
    case 'admin_supprimer_user':     adminSupprimerUser();      break;
    case 'admin_creer_user':         adminCreerUser();          break;
    case 'admin_modules':            adminModules();            break;
    case 'admin_cours':              adminCours();              break;
    case 'admin_certificats':        adminCertificats();        break;
    case 'admin_supprimer_module':   adminSupprimerModule();    break;
    case 'admin_supprimer_cours':    adminSupprimerCours();     break;
    case 'admin_supprimer_certificat': adminSupprimerCertificat(); break;
    case 'admin_activite':           adminActivite();           break;
    case 'admin_reset_mdp':          adminResetMdp();           break;
    default: repondreJSON(['succes' => false, 'message' => 'Action inconnue.'], 400);
}

/* ── Guard admin ───────────────────────────────────────────────────────────── */
function exigerAdmin(): void {
    if (!utilisateurConnecte() || $_SESSION['user_role'] !== 'admin') {
        repondreJSON(['succes' => false, 'message' => 'Accès refusé.'], 403);
    }
}

/* ══════════════════════════════════════════════════════════════
   STATS GLOBALES
   ══════════════════════════════════════════════════════════════ */
function adminStats(): void {
    exigerAdmin();
    $db = getDB();

    $stats = [];

    // Utilisateurs par rôle
    $stmt = $db->query("SELECT role, COUNT(*) AS nb, SUM(actif) AS actifs FROM users GROUP BY role");
    $parRole = [];
    foreach ($stmt->fetchAll() as $row) {
        $parRole[$row['role']] = ['total' => $row['nb'], 'actifs' => (int)$row['actifs']];
    }
    $stats['utilisateurs'] = $parRole;
    $stats['total_users']  = array_sum(array_column($parRole, 'total'));

    // Modules, cours, leçons
    $stats['modules']  = (int)$db->query("SELECT COUNT(*) FROM modules")->fetchColumn();
    $stats['cours']    = (int)$db->query("SELECT COUNT(*) FROM cours")->fetchColumn();
    $stats['lecons']   = (int)$db->query("SELECT COUNT(*) FROM lecons")->fetchColumn();

    // Inscriptions & certificats
    $stats['inscriptions'] = (int)$db->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();
    $stats['certificats']  = (int)$db->query("SELECT COUNT(*) FROM certificats")->fetchColumn();

    // Évaluations & résultats
    $stats['evaluations'] = (int)$db->query("SELECT COUNT(*) FROM evaluations")->fetchColumn();
    $stats['resultats']   = (int)$db->query("SELECT COUNT(*) FROM resultats_evaluations")->fetchColumn();

    // Nouveaux inscrits (30 derniers jours)
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE cree_le >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['nouveaux_30j'] = (int)$stmt->fetchColumn();

    // Inscriptions par mois (6 derniers mois)
    $stmt = $db->query("
        SELECT DATE_FORMAT(cree_le, '%Y-%m') AS mois, COUNT(*) AS nb
        FROM users
        WHERE cree_le >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY mois ORDER BY mois ASC
    ");
    $stats['inscriptions_mois'] = $stmt->fetchAll();

    repondreJSON(['succes' => true, 'stats' => $stats]);
}

/* ══════════════════════════════════════════════════════════════
   GESTION UTILISATEURS
   ══════════════════════════════════════════════════════════════ */
function adminUtilisateurs(): void {
    exigerAdmin();
    $db = getDB();

    $search = trim($_GET['search'] ?? $_POST['search'] ?? '');
    $role   = $_GET['role'] ?? $_POST['role'] ?? '';
    $actif  = $_GET['actif'] ?? $_POST['actif'] ?? '';

    $where  = ['1=1'];
    $params = [];

    if ($search) {
        $where[]  = '(nom LIKE ? OR prenom LIKE ? OR email LIKE ?)';
        $like     = "%$search%";
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    if ($role && in_array($role, ['etudiant', 'enseignant', 'promoteur', 'admin'])) {
        $where[]  = 'role = ?';
        $params[] = $role;
    }
    if ($actif !== '') {
        $where[]  = 'actif = ?';
        $params[] = (int)$actif;
    }

    $sql  = 'SELECT id, nom, prenom, email, role, actif, avatar, cree_le FROM users WHERE '
          . implode(' AND ', $where) . ' ORDER BY cree_le DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    repondreJSON(['succes' => true, 'utilisateurs' => $stmt->fetchAll()]);
}

function adminActiverUser(): void {
    exigerAdmin();
    $id    = (int)($_POST['user_id'] ?? 0);
    $actif = (int)($_POST['actif'] ?? 0);
    if (!$id) repondreJSON(['succes' => false, 'message' => 'ID manquant.']);
    // Ne pas désactiver son propre compte
    if ($id === (int)$_SESSION['user_id']) repondreJSON(['succes' => false, 'message' => 'Impossible de modifier votre propre compte.']);

    $db = getDB();
    $db->prepare('UPDATE users SET actif = ? WHERE id = ?')->execute([$actif, $id]);
    repondreJSON(['succes' => true, 'message' => $actif ? 'Compte activé.' : 'Compte désactivé.']);
}

function adminChangerRole(): void {
    exigerAdmin();
    $id   = (int)($_POST['user_id'] ?? 0);
    $role = $_POST['role'] ?? '';
    if (!$id || !in_array($role, ['etudiant', 'enseignant', 'promoteur', 'admin']))
        repondreJSON(['succes' => false, 'message' => 'Paramètres invalides.']);
    if ($id === (int)$_SESSION['user_id'])
        repondreJSON(['succes' => false, 'message' => 'Impossible de modifier votre propre rôle.']);

    $db = getDB();
    $db->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $id]);
    repondreJSON(['succes' => true, 'message' => 'Rôle mis à jour.']);
}

function adminSupprimerUser(): void {
    exigerAdmin();
    $id = (int)($_POST['user_id'] ?? 0);
    if (!$id) repondreJSON(['succes' => false, 'message' => 'ID manquant.']);
    if ($id === (int)$_SESSION['user_id'])
        repondreJSON(['succes' => false, 'message' => 'Impossible de supprimer votre propre compte.']);

    $db = getDB();
    $db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    repondreJSON(['succes' => true, 'message' => 'Utilisateur supprimé.']);
}

function adminCreerUser(): void {
    exigerAdmin();
    $nom   = trim($_POST['nom']          ?? '');
    $pren  = trim($_POST['prenom']       ?? '');
    $email = trim($_POST['email']        ?? '');
    $mdp   = $_POST['mot_de_passe']      ?? '';
    $role  = $_POST['role']              ?? 'etudiant';

    if (!$nom || !$pren || !$email || !$mdp)
        repondreJSON(['succes' => false, 'message' => 'Tous les champs sont requis.']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        repondreJSON(['succes' => false, 'message' => 'E-mail invalide.']);
    if (strlen($mdp) < 8)
        repondreJSON(['succes' => false, 'message' => 'Mot de passe trop court (min. 8 caractères).']);
    if (!in_array($role, ['etudiant', 'enseignant', 'promoteur', 'admin']))
        repondreJSON(['succes' => false, 'message' => 'Rôle invalide.']);

    $db   = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) repondreJSON(['succes' => false, 'message' => 'Cet e-mail est déjà utilisé.']);

    $hash = password_hash($mdp, PASSWORD_BCRYPT);
    $db->prepare('INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)')
       ->execute([$nom, $pren, $email, $hash, $role]);

    repondreJSON(['succes' => true, 'message' => 'Utilisateur créé avec succès.']);
}

function adminResetMdp(): void {
    exigerAdmin();
    $id  = (int)($_POST['user_id'] ?? 0);
    $mdp = $_POST['mot_de_passe'] ?? '';
    if (!$id || strlen($mdp) < 8)
        repondreJSON(['succes' => false, 'message' => 'Paramètres invalides (mot de passe min. 8 caractères).']);

    $db   = getDB();
    $hash = password_hash($mdp, PASSWORD_BCRYPT);
    $db->prepare('UPDATE users SET mot_de_passe = ? WHERE id = ?')->execute([$hash, $id]);
    repondreJSON(['succes' => true, 'message' => 'Mot de passe réinitialisé.']);
}

/* ══════════════════════════════════════════════════════════════
   MODULES
   ══════════════════════════════════════════════════════════════ */
function adminModules(): void {
    exigerAdmin();
    $db   = getDB();
    $stmt = $db->query("
        SELECT m.id, m.titre, m.description, m.actif, m.cree_le,
               CONCAT(u.prenom, ' ', u.nom) AS promoteur,
               COUNT(DISTINCT c.id) AS nb_cours,
               COUNT(DISTINCT ce.id) AS nb_certificats
        FROM modules m
        JOIN users u ON u.id = m.promoteur_id
        LEFT JOIN cours c ON c.module_id = m.id
        LEFT JOIN certificats ce ON ce.module_id = m.id
        GROUP BY m.id
        ORDER BY m.cree_le DESC
    ");
    repondreJSON(['succes' => true, 'modules' => $stmt->fetchAll()]);
}

function adminSupprimerModule(): void {
    exigerAdmin();
    $id = (int)($_POST['module_id'] ?? 0);
    if (!$id) repondreJSON(['succes' => false, 'message' => 'ID manquant.']);
    $db = getDB();
    $db->prepare('DELETE FROM modules WHERE id = ?')->execute([$id]);
    repondreJSON(['succes' => true, 'message' => 'Module supprimé.']);
}

/* ══════════════════════════════════════════════════════════════
   COURS
   ══════════════════════════════════════════════════════════════ */
function adminCours(): void {
    exigerAdmin();
    $db   = getDB();
    $stmt = $db->query("
        SELECT c.id, c.titre, c.niveau, c.duree_heures, c.actif, c.cree_le,
               m.titre AS module_titre,
               CONCAT(u.prenom, ' ', u.nom) AS enseignant,
               COUNT(DISTINCT l.id)  AS nb_lecons,
               COUNT(DISTINCT i.id)  AS nb_inscrits
        FROM cours c
        JOIN modules m ON m.id = c.module_id
        JOIN users u   ON u.id = c.enseignant_id
        LEFT JOIN lecons l ON l.cours_id = c.id
        LEFT JOIN inscriptions i ON i.cours_id = c.id
        GROUP BY c.id
        ORDER BY c.cree_le DESC
    ");
    repondreJSON(['succes' => true, 'cours' => $stmt->fetchAll()]);
}

function adminSupprimerCours(): void {
    exigerAdmin();
    $id = (int)($_POST['cours_id'] ?? 0);
    if (!$id) repondreJSON(['succes' => false, 'message' => 'ID manquant.']);
    $db = getDB();
    $db->prepare('DELETE FROM cours WHERE id = ?')->execute([$id]);
    repondreJSON(['succes' => true, 'message' => 'Cours supprimé.']);
}

/* ══════════════════════════════════════════════════════════════
   CERTIFICATS
   ══════════════════════════════════════════════════════════════ */
function adminCertificats(): void {
    exigerAdmin();
    $db   = getDB();
    $stmt = $db->query("
        SELECT ce.id, ce.code_unique, ce.delivre_le,
               m.titre AS module_titre,
               CONCAT(u.prenom, ' ', u.nom) AS etudiant,
               u.email AS etudiant_email
        FROM certificats ce
        JOIN modules m ON m.id = ce.module_id
        JOIN users u   ON u.id = ce.etudiant_id
        ORDER BY ce.delivre_le DESC
    ");
    repondreJSON(['succes' => true, 'certificats' => $stmt->fetchAll()]);
}

function adminSupprimerCertificat(): void {
    exigerAdmin();
    $id = (int)($_POST['cert_id'] ?? 0);
    if (!$id) repondreJSON(['succes' => false, 'message' => 'ID manquant.']);
    $db = getDB();
    $db->prepare('DELETE FROM certificats WHERE id = ?')->execute([$id]);
    repondreJSON(['succes' => true, 'message' => 'Certificat supprimé.']);
}

/* ══════════════════════════════════════════════════════════════
   ACTIVITÉ RÉCENTE
   ══════════════════════════════════════════════════════════════ */
function adminActivite(): void {
    exigerAdmin();
    $db = getDB();

    // Dernières inscriptions cours
    $ins = $db->query("
        SELECT 'inscription' AS type,
               CONCAT(u.prenom, ' ', u.nom) AS acteur,
               c.titre AS cible, i.inscrit_le AS date_action
        FROM inscriptions i
        JOIN users u ON u.id = i.etudiant_id
        JOIN cours c ON c.id = i.cours_id
        ORDER BY i.inscrit_le DESC LIMIT 10
    ")->fetchAll();

    // Derniers certificats
    $certs = $db->query("
        SELECT 'certificat' AS type,
               CONCAT(u.prenom, ' ', u.nom) AS acteur,
               m.titre AS cible, ce.delivre_le AS date_action
        FROM certificats ce
        JOIN users u    ON u.id = ce.etudiant_id
        JOIN modules m  ON m.id = ce.module_id
        ORDER BY ce.delivre_le DESC LIMIT 10
    ")->fetchAll();

    // Derniers comptes créés
    $users = $db->query("
        SELECT 'nouveau_compte' AS type,
               CONCAT(prenom, ' ', nom) AS acteur,
               role AS cible, cree_le AS date_action
        FROM users
        ORDER BY cree_le DESC LIMIT 10
    ")->fetchAll();

    // Fusionner et trier par date
    $activite = array_merge($ins, $certs, $users);
    usort($activite, fn($a, $b) => strtotime($b['date_action']) - strtotime($a['date_action']));
    $activite = array_slice($activite, 0, 25);

    repondreJSON(['succes' => true, 'activite' => $activite]);
}
