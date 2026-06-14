<?php
/**
 * LearnUp — api/index.php
 * Point d'entrée unique pour toutes les requêtes Ajax
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    // Auth
    case 'connexion':         connexion();         break;
    case 'inscription':       inscription();       break;
    case 'deconnexion':       deconnexion();       break;

    // Promoteur
    case 'creer_module':      creerModule();       break;
    case 'lister_modules':    listerModules();     break;
    case 'supprimer_module':  supprimerModule();   break;
    case 'attribuer_certificat': attribuerCertificat(); break;
    case 'stats_promoteur':   statsPromoteur();    break;

    // Enseignant
    case 'creer_cours':       creerCours();        break;
    case 'mes_cours':         mesCours();          break;
    case 'creer_lecon':       creerLecon();        break;
    case 'creer_evaluation':  creerEvaluation();   break;
    case 'ajouter_question':  ajouterQuestion();   break;
    case 'stats_enseignant':  statsEnseignant();   break;
    case 'etudiants_cours':   etudiantsCours();    break;

    // Étudiant
    case 'cours_disponibles': coursDisponibles();  break;
    case 'sinscrire_cours':   sInscrireCours();    break;
    case 'mes_cours_etudiant':mesCoursEtudiant();  break;
    case 'detail_cours':      detailCours();       break;
    case 'marquer_lecon':     marquerLecon();      break;
    case 'passer_evaluation': passerEvaluation();  break;
    case 'progression_cours': progressionCours();  break;
    case 'mes_certificats':   mesCertificats();    break;
    case 'stats_etudiant':    statsEtudiant();     break;

    // Actions supplémentaires (extra.php)
    case 'detail_evaluation':         include __DIR__.'/extra.php'; exit;
    case 'lister_utilisateurs':       include __DIR__.'/extra.php'; exit;
    case 'trouver_utilisateur':       include __DIR__.'/extra.php'; exit;
    case 'lister_certificats_promoteur': include __DIR__.'/extra.php'; exit;

    default: repondreJSON(['succes' => false, 'message' => 'Action inconnue.'], 400);
}

/* ══════════════════════════════════════════════════════════════
   AUTH
   ══════════════════════════════════════════════════════════════ */
function connexion(): void {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if (!$email || !$mdp) repondreJSON(['succes' => false, 'message' => 'Champs requis.']);

    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND actif = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($mdp, $user['mot_de_passe']))
        repondreJSON(['succes' => false, 'message' => 'Identifiants incorrects.']);

    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_nom']    = $user['nom'];
    $_SESSION['user_prenom'] = $user['prenom'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_role']   = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'];

    repondreJSON(['succes' => true, 'role' => $user['role'], 'nom' => $user['prenom']]);
}

function inscription(): void {
    $nom  = trim($_POST['nom']          ?? '');
    $pren = trim($_POST['prenom']       ?? '');
    $email= trim($_POST['email']        ?? '');
    $mdp  = $_POST['mot_de_passe']      ?? '';
    $role = $_POST['role']              ?? 'etudiant';

    if (!$nom || !$pren || !$email || !$mdp)
        repondreJSON(['succes' => false, 'message' => 'Tous les champs sont requis.']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        repondreJSON(['succes' => false, 'message' => 'E-mail invalide.']);
    if (strlen($mdp) < 8)
        repondreJSON(['succes' => false, 'message' => 'Mot de passe trop court (min. 8 caractères).']);
    if (!in_array($role, ['etudiant','enseignant','promoteur']))
        repondreJSON(['succes' => false, 'message' => 'Rôle invalide.']);

    $db   = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) repondreJSON(['succes' => false, 'message' => 'Cet e-mail est déjà utilisé.']);

    $hash = password_hash($mdp, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (nom,prenom,email,mot_de_passe,role) VALUES (?,?,?,?,?)');
    $stmt->execute([$nom, $pren, $email, $hash, $role]);

    $id = $db->lastInsertId();
    $_SESSION['user_id']     = $id;
    $_SESSION['user_nom']    = $nom;
    $_SESSION['user_prenom'] = $pren;
    $_SESSION['user_email']  = $email;
    $_SESSION['user_role']   = $role;

    repondreJSON(['succes' => true, 'role' => $role]);
}

function deconnexion(): void {
    session_destroy();
    repondreJSON(['succes' => true]);
}

/* ══════════════════════════════════════════════════════════════
   PROMOTEUR
   ══════════════════════════════════════════════════════════════ */
function creerModule(): void {
    exigerConnexion('promoteur');
    $titre = trim($_POST['titre'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    if (!$titre) repondreJSON(['succes' => false, 'message' => 'Titre requis.']);

    $db   = getDB();
    $stmt = $db->prepare('INSERT INTO modules (titre,description,promoteur_id) VALUES (?,?,?)');
    $stmt->execute([$titre, $desc, $_SESSION['user_id']]);
    repondreJSON(['succes' => true, 'module_id' => $db->lastInsertId()]);
}

function listerModules(): void {
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT m.*, u.nom, u.prenom,
               (SELECT COUNT(*) FROM cours WHERE module_id = m.id) AS nb_cours
        FROM modules m
        JOIN users u ON u.id = m.promoteur_id
        WHERE m.actif = 1
        ORDER BY m.cree_le DESC
    ');
    $stmt->execute();
    repondreJSON(['succes' => true, 'modules' => $stmt->fetchAll()]);
}

function supprimerModule(): void {
    exigerConnexion('promoteur');
    $id = (int)($_POST['module_id'] ?? 0);
    $db = getDB();
    $db->prepare('UPDATE modules SET actif=0 WHERE id=? AND promoteur_id=?')
       ->execute([$id, $_SESSION['user_id']]);
    repondreJSON(['succes' => true]);
}

function attribuerCertificat(): void {
    exigerConnexion('promoteur');
    $etudiantId = (int)($_POST['etudiant_id'] ?? 0);
    $moduleId   = (int)($_POST['module_id']   ?? 0);
    if (!$etudiantId || !$moduleId)
        repondreJSON(['succes' => false, 'message' => 'Paramètres manquants.']);

    $db   = getDB();
    $code = genererCode(32);

    try {
        $db->prepare('INSERT INTO certificats (etudiant_id,module_id,code_unique) VALUES (?,?,?)')
           ->execute([$etudiantId, $moduleId, $code]);
        repondreJSON(['succes' => true, 'code' => $code]);
    } catch (\Exception $e) {
        repondreJSON(['succes' => false, 'message' => 'Certificat déjà délivré.']);
    }
}

function statsPromoteur(): void {
    exigerConnexion('promoteur');
    $db  = getDB();
    $pid = $_SESSION['user_id'];

    $modules = $db->prepare('SELECT COUNT(*) FROM modules WHERE promoteur_id=? AND actif=1');
    $modules->execute([$pid]);

    $certifs = $db->prepare('SELECT COUNT(*) FROM certificats c JOIN modules m ON m.id=c.module_id WHERE m.promoteur_id=?');
    $certifs->execute([$pid]);

    $etudiants = $db->prepare('
        SELECT COUNT(DISTINCT i.etudiant_id)
        FROM inscriptions i JOIN cours co ON co.id=i.cours_id
        JOIN modules m ON m.id=co.module_id WHERE m.promoteur_id=?
    ');
    $etudiants->execute([$pid]);

    repondreJSON(['succes' => true, 'stats' => [
        'modules'   => $modules->fetchColumn(),
        'certificats'=> $certifs->fetchColumn(),
        'etudiants' => $etudiants->fetchColumn(),
    ]]);
}

/* ══════════════════════════════════════════════════════════════
   ENSEIGNANT
   ══════════════════════════════════════════════════════════════ */
function creerCours(): void {
    exigerConnexion('enseignant');
    $titre    = trim($_POST['titre']      ?? '');
    $desc     = trim($_POST['description']?? '');
    $moduleId = (int)($_POST['module_id'] ?? 0);
    $niveau   = $_POST['niveau']          ?? 'debutant';
    $duree    = (int)($_POST['duree']     ?? 0);

    if (!$titre || !$moduleId)
        repondreJSON(['succes' => false, 'message' => 'Titre et module requis.']);

    $db   = getDB();
    $stmt = $db->prepare('INSERT INTO cours (titre,description,module_id,enseignant_id,niveau,duree_heures) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$titre, $desc, $moduleId, $_SESSION['user_id'], $niveau, $duree]);
    repondreJSON(['succes' => true, 'cours_id' => $db->lastInsertId()]);
}

function mesCours(): void {
    exigerConnexion('enseignant');
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT c.*, m.titre AS module_titre,
               (SELECT COUNT(*) FROM lecons WHERE cours_id=c.id AND actif=1) AS nb_lecons,
               (SELECT COUNT(*) FROM inscriptions WHERE cours_id=c.id) AS nb_etudiants
        FROM cours c JOIN modules m ON m.id=c.module_id
        WHERE c.enseignant_id=? AND c.actif=1
        ORDER BY c.cree_le DESC
    ');
    $stmt->execute([$_SESSION['user_id']]);
    repondreJSON(['succes' => true, 'cours' => $stmt->fetchAll()]);
}

function creerLecon(): void {
    exigerConnexion('enseignant');
    $coursId = (int)($_POST['cours_id'] ?? 0);
    $titre   = trim($_POST['titre']     ?? '');
    $type    = $_POST['type']            ?? 'pdf';
    $ordre   = (int)($_POST['ordre']    ?? 1);
    $duree   = (int)($_POST['duree']    ?? 0);

    if (!$coursId || !$titre)
        repondreJSON(['succes' => false, 'message' => 'Paramètres manquants.']);

    // Gérer l'upload
    $fichier = null;
    if ($type === 'video' && !empty($_POST['url_video'])) {
        $fichier = trim($_POST['url_video']);
    } elseif (isset($_FILES['fichier'])) {
        $fichier = uploaderFichier($_FILES['fichier'], $type);
        if (!$fichier) repondreJSON(['succes' => false, 'message' => 'Fichier invalide.']);
    }

    if (!$fichier) repondreJSON(['succes' => false, 'message' => 'Fichier ou URL requis.']);

    $db   = getDB();
    $stmt = $db->prepare('INSERT INTO lecons (titre,cours_id,type,fichier,ordre,duree_min) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$titre, $coursId, $type, $fichier, $ordre, $duree]);
    repondreJSON(['succes' => true, 'lecon_id' => $db->lastInsertId()]);
}

function creerEvaluation(): void {
    exigerConnexion('enseignant');
    $leconId     = (int)($_POST['lecon_id']      ?? 0);
    $titre       = trim($_POST['titre']           ?? '');
    $notePassage = (int)($_POST['note_passage']   ?? 50);
    $duree       = (int)($_POST['duree']          ?? 30);

    if (!$leconId || !$titre)
        repondreJSON(['succes' => false, 'message' => 'Paramètres manquants.']);

    $db   = getDB();
    $stmt = $db->prepare('INSERT INTO evaluations (lecon_id,titre,note_passage,duree_min) VALUES (?,?,?,?)
                          ON DUPLICATE KEY UPDATE titre=VALUES(titre),note_passage=VALUES(note_passage),duree_min=VALUES(duree_min)');
    $stmt->execute([$leconId, $titre, $notePassage, $duree]);
    repondreJSON(['succes' => true, 'eval_id' => $db->lastInsertId()]);
}

function ajouterQuestion(): void {
    exigerConnexion('enseignant');
    $evalId  = (int)($_POST['evaluation_id'] ?? 0);
    $enonce  = trim($_POST['enonce']         ?? '');
    $type    = $_POST['type']                ?? 'qcm';
    $points  = (int)($_POST['points']        ?? 1);
    $reponses= json_decode($_POST['reponses'] ?? '[]', true);

    if (!$evalId || !$enonce)
        repondreJSON(['succes' => false, 'message' => 'Paramètres manquants.']);

    $db   = getDB();
    $stmt = $db->prepare('INSERT INTO questions (evaluation_id,enonce,type,points) VALUES (?,?,?,?)');
    $stmt->execute([$evalId, $enonce, $type, $points]);
    $qId = $db->lastInsertId();

    // Insérer les réponses
    if (is_array($reponses)) {
        $stmtR = $db->prepare('INSERT INTO reponses (question_id,texte,est_correcte,ordre) VALUES (?,?,?,?)');
        foreach ($reponses as $i => $r) {
            $stmtR->execute([$qId, $r['texte'], $r['correcte'] ? 1 : 0, $i+1]);
        }
    }

    repondreJSON(['succes' => true, 'question_id' => $qId]);
}

function statsEnseignant(): void {
    exigerConnexion('enseignant');
    $db  = getDB();
    $eid = $_SESSION['user_id'];

    $cours    = $db->prepare('SELECT COUNT(*) FROM cours WHERE enseignant_id=? AND actif=1');
    $cours->execute([$eid]);

    $lecons   = $db->prepare('SELECT COUNT(*) FROM lecons l JOIN cours c ON c.id=l.cours_id WHERE c.enseignant_id=? AND l.actif=1');
    $lecons->execute([$eid]);

    $etudiants= $db->prepare('SELECT COUNT(DISTINCT i.etudiant_id) FROM inscriptions i JOIN cours c ON c.id=i.cours_id WHERE c.enseignant_id=?');
    $etudiants->execute([$eid]);

    repondreJSON(['succes' => true, 'stats' => [
        'cours'     => $cours->fetchColumn(),
        'lecons'    => $lecons->fetchColumn(),
        'etudiants' => $etudiants->fetchColumn(),
    ]]);
}

function etudiantsCours(): void {
    exigerConnexion('enseignant');
    $coursId = (int)($_POST['cours_id'] ?? 0);
    $db      = getDB();
    $stmt    = $db->prepare('
        SELECT u.id, u.nom, u.prenom, u.email, i.inscrit_le,
               (SELECT AVG(re.note) FROM resultats_evaluations re
                JOIN evaluations ev ON ev.id=re.evaluation_id
                JOIN lecons l ON l.id=ev.lecon_id
                WHERE l.cours_id=? AND re.etudiant_id=u.id) AS note_moy
        FROM inscriptions i JOIN users u ON u.id=i.etudiant_id
        WHERE i.cours_id=?
        ORDER BY i.inscrit_le DESC
    ');
    $stmt->execute([$coursId, $coursId]);
    repondreJSON(['succes' => true, 'etudiants' => $stmt->fetchAll()]);
}

/* ══════════════════════════════════════════════════════════════
   ÉTUDIANT
   ══════════════════════════════════════════════════════════════ */
function coursDisponibles(): void {
    $db   = getDB();
    $uid  = $_SESSION['user_id'] ?? 0;
    $stmt = $db->prepare('
        SELECT c.*, m.titre AS module_titre,
               CONCAT(u.prenom,' ',u.nom) AS enseignant,
               (SELECT COUNT(*) FROM lecons WHERE cours_id=c.id AND actif=1) AS nb_lecons,
               (SELECT COUNT(*) FROM inscriptions WHERE cours_id=c.id AND etudiant_id=?) AS inscrit
        FROM cours c
        JOIN modules m ON m.id=c.module_id
        JOIN users u   ON u.id=c.enseignant_id
        WHERE c.actif=1 AND m.actif=1
        ORDER BY c.cree_le DESC
    ');
    $stmt->execute([$uid]);
    repondreJSON(['succes' => true, 'cours' => $stmt->fetchAll()]);
}

function sInscrireCours(): void {
    exigerConnexion('etudiant');
    $coursId = (int)($_POST['cours_id'] ?? 0);
    if (!$coursId) repondreJSON(['succes' => false, 'message' => 'Cours introuvable.']);

    $db = getDB();
    try {
        $db->prepare('INSERT INTO inscriptions (etudiant_id,cours_id) VALUES (?,?)')
           ->execute([$_SESSION['user_id'], $coursId]);
        repondreJSON(['succes' => true]);
    } catch (\Exception $e) {
        repondreJSON(['succes' => false, 'message' => 'Déjà inscrit.']);
    }
}

function mesCoursEtudiant(): void {
    exigerConnexion('etudiant');
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT c.*, m.titre AS module_titre,
               CONCAT(u.prenom,' ',u.nom) AS enseignant,
               (SELECT COUNT(*) FROM lecons WHERE cours_id=c.id AND actif=1) AS nb_lecons,
               (SELECT COUNT(*) FROM progression_lecons pl
                JOIN lecons l ON l.id=pl.lecon_id
                WHERE l.cours_id=c.id AND pl.etudiant_id=? AND pl.termine=1) AS lecons_terminees
        FROM inscriptions i
        JOIN cours c ON c.id=i.cours_id
        JOIN modules m ON m.id=c.module_id
        JOIN users u ON u.id=c.enseignant_id
        WHERE i.etudiant_id=? AND c.actif=1
        ORDER BY i.inscrit_le DESC
    ');
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    repondreJSON(['succes' => true, 'cours' => $stmt->fetchAll()]);
}

function detailCours(): void {
    $coursId = (int)($_POST['cours_id'] ?? 0);
    $uid     = $_SESSION['user_id'] ?? 0;
    $db      = getDB();

    $stmt = $db->prepare('
        SELECT l.*,
               (SELECT id FROM evaluations WHERE lecon_id=l.id AND actif=1 LIMIT 1) AS evaluation_id,
               (SELECT titre FROM evaluations WHERE lecon_id=l.id AND actif=1 LIMIT 1) AS evaluation_titre,
               (SELECT note FROM resultats_evaluations WHERE evaluation_id=(SELECT id FROM evaluations WHERE lecon_id=l.id LIMIT 1) AND etudiant_id=?) AS ma_note,
               (SELECT termine FROM progression_lecons WHERE lecon_id=l.id AND etudiant_id=?) AS terminee
        FROM lecons l WHERE l.cours_id=? AND l.actif=1 ORDER BY l.ordre ASC
    ');
    $stmt->execute([$uid, $uid, $coursId]);
    repondreJSON(['succes' => true, 'lecons' => $stmt->fetchAll()]);
}

function marquerLecon(): void {
    exigerConnexion('etudiant');
    $leconId = (int)($_POST['lecon_id'] ?? 0);
    $db      = getDB();

    $db->prepare('INSERT INTO progression_lecons (etudiant_id,lecon_id,termine,termine_le)
                  VALUES (?,?,1,NOW())
                  ON DUPLICATE KEY UPDATE termine=1, termine_le=NOW()')
       ->execute([$_SESSION['user_id'], $leconId]);

    repondreJSON(['succes' => true]);
}

function passerEvaluation(): void {
    exigerConnexion('etudiant');
    $evalId  = (int)($_POST['evaluation_id'] ?? 0);
    $reponses= json_decode($_POST['reponses'] ?? '{}', true);
    $uid     = $_SESSION['user_id'];

    $db = getDB();

    // Récupérer les questions et leurs bonnes réponses
    $stmtQ = $db->prepare('SELECT q.*, r.id AS rep_id, r.est_correcte FROM questions q LEFT JOIN reponses r ON r.question_id=q.id WHERE q.evaluation_id=?');
    $stmtQ->execute([$evalId]);
    $rows = $stmtQ->fetchAll();

    // Organiser par question
    $questions = [];
    foreach ($rows as $r) {
        $qid = $r['id'];
        if (!isset($questions[$qid])) {
            $questions[$qid] = ['points' => $r['points'], 'correctes' => []];
        }
        if ($r['est_correcte']) $questions[$qid]['correctes'][] = $r['rep_id'];
    }

    // Calculer la note
    $totalPoints = 0;
    $pointsObtenus = 0;
    $details = [];

    foreach ($questions as $qid => $q) {
        $totalPoints += $q['points'];
        $repEtudiant = $reponses[$qid] ?? null;
        $correct = false;

        if ($repEtudiant !== null) {
            $repId = (int)$repEtudiant;
            $correct = in_array($repId, $q['correctes']);
            if ($correct) $pointsObtenus += $q['points'];
        }

        $details[] = ['question_id' => $qid, 'reponse_id' => $repEtudiant, 'correcte' => $correct];
    }

    $note = $totalPoints > 0 ? round(($pointsObtenus / $totalPoints) * 100, 2) : 0;

    // Récupérer la note de passage
    $evalInfo = $db->prepare('SELECT note_passage FROM evaluations WHERE id=?');
    $evalInfo->execute([$evalId]);
    $notePassage = $evalInfo->fetchColumn() ?: 50;
    $reussi = $note >= $notePassage;

    // Sauvegarder le résultat
    $db->prepare('INSERT INTO resultats_evaluations (etudiant_id,evaluation_id,note,reussi)
                  VALUES (?,?,?,?)
                  ON DUPLICATE KEY UPDATE note=VALUES(note), reussi=VALUES(reussi), tentative=tentative+1, passe_le=NOW()')
       ->execute([$uid, $evalId, $note, $reussi ? 1 : 0]);

    $resId = $db->lastInsertId() ?: $db->query("SELECT id FROM resultats_evaluations WHERE etudiant_id=$uid AND evaluation_id=$evalId")->fetchColumn();

    // Sauvegarder les réponses détaillées
    foreach ($details as $d) {
        $db->prepare('INSERT INTO reponses_etudiants (resultat_id,question_id,reponse_id,est_correcte) VALUES (?,?,?,?)
                      ON DUPLICATE KEY UPDATE reponse_id=VALUES(reponse_id),est_correcte=VALUES(est_correcte)')
           ->execute([$resId, $d['question_id'], $d['reponse_id'], $d['correcte'] ? 1 : 0]);
    }

    repondreJSON(['succes' => true, 'note' => $note, 'reussi' => $reussi, 'note_passage' => $notePassage]);
}

function progressionCours(): void {
    exigerConnexion('etudiant');
    $coursId = (int)($_POST['cours_id'] ?? 0);
    $uid     = $_SESSION['user_id'];
    $db      = getDB();

    $total = $db->prepare('SELECT COUNT(*) FROM lecons WHERE cours_id=? AND actif=1');
    $total->execute([$coursId]);
    $nb = (int)$total->fetchColumn();

    $faites = $db->prepare('SELECT COUNT(*) FROM progression_lecons pl JOIN lecons l ON l.id=pl.lecon_id WHERE l.cours_id=? AND pl.etudiant_id=? AND pl.termine=1');
    $faites->execute([$coursId, $uid]);
    $done = (int)$faites->fetchColumn();

    $pct = $nb > 0 ? round(($done / $nb) * 100) : 0;

    repondreJSON(['succes' => true, 'total' => $nb, 'terminees' => $done, 'pct' => $pct]);
}

function mesCertificats(): void {
    exigerConnexion('etudiant');
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT ce.*, m.titre AS module_titre, m.description AS module_desc
        FROM certificats ce JOIN modules m ON m.id=ce.module_id
        WHERE ce.etudiant_id=?
        ORDER BY ce.delivre_le DESC
    ');
    $stmt->execute([$_SESSION['user_id']]);
    repondreJSON(['succes' => true, 'certificats' => $stmt->fetchAll()]);
}

function statsEtudiant(): void {
    exigerConnexion('etudiant');
    $uid = $_SESSION['user_id'];
    $db  = getDB();

    $cours = $db->prepare('SELECT COUNT(*) FROM inscriptions WHERE etudiant_id=?');
    $cours->execute([$uid]);

    $terminees = $db->prepare('SELECT COUNT(*) FROM progression_lecons WHERE etudiant_id=? AND termine=1');
    $terminees->execute([$uid]);

    $noteMoy = $db->prepare('SELECT AVG(note) FROM resultats_evaluations WHERE etudiant_id=?');
    $noteMoy->execute([$uid]);

    $certifs = $db->prepare('SELECT COUNT(*) FROM certificats WHERE etudiant_id=?');
    $certifs->execute([$uid]);

    repondreJSON(['succes' => true, 'stats' => [
        'cours'      => $cours->fetchColumn(),
        'lecons'     => $terminees->fetchColumn(),
        'note_moy'   => round($noteMoy->fetchColumn() ?? 0, 1),
        'certificats'=> $certifs->fetchColumn(),
    ]]);
}
