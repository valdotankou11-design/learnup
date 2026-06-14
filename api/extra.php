<?php
/**
 * LearnUp — api/extra.php
 * Actions complémentaires (inclus depuis api/index.php ou appelé séparément)
 * - detail_evaluation (questions pour passer le quiz)
 * - lister_utilisateurs (promoteur)
 * - trouver_utilisateur (par email)
 * - lister_certificats_promoteur
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'detail_evaluation':         detailEvaluation();        break;
    case 'lister_utilisateurs':       listerUtilisateurs();      break;
    case 'trouver_utilisateur':       trouverUtilisateur();      break;
    case 'lister_certificats_promoteur': listerCertificatsPromoteur(); break;
    default: repondreJSON(['succes' => false, 'message' => 'Action inconnue.'], 400);
}

/* ══════════════════════════════════════════════════════════════
   Détail d'une évaluation (questions + réponses pour l'étudiant)
   ══════════════════════════════════════════════════════════════ */
function detailEvaluation(): void {
    $evalId = (int)($_POST['evaluation_id'] ?? 0);
    if (!$evalId) repondreJSON(['succes' => false, 'message' => 'evaluation_id manquant.']);

    $db = getDB();

    // Info de l'évaluation
    $stmt = $db->prepare('SELECT * FROM evaluations WHERE id = ? AND actif = 1');
    $stmt->execute([$evalId]);
    $eval = $stmt->fetch();
    if (!$eval) repondreJSON(['succes' => false, 'message' => 'Évaluation introuvable.']);

    // Questions
    $stmtQ = $db->prepare('SELECT * FROM questions WHERE evaluation_id = ? ORDER BY ordre ASC');
    $stmtQ->execute([$evalId]);
    $questions = $stmtQ->fetchAll();

    // Réponses par question (mélangées pour éviter la triche)
    foreach ($questions as &$q) {
        $stmtR = $db->prepare('SELECT id, texte, ordre FROM reponses WHERE question_id = ? ORDER BY RAND()');
        $stmtR->execute([$q['id']]);
        $q['reponses'] = $stmtR->fetchAll();
    }

    repondreJSON([
        'succes'    => true,
        'evaluation'=> $eval,
        'questions' => $questions,
        'duree_min' => $eval['duree_min'],
    ]);
}

/* ══════════════════════════════════════════════════════════════
   Lister tous les utilisateurs (promoteur uniquement)
   ══════════════════════════════════════════════════════════════ */
function listerUtilisateurs(): void {
    exigerConnexion('promoteur');
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT id, nom, prenom, email, role, actif, cree_le
        FROM users
        ORDER BY cree_le DESC
    ');
    $stmt->execute();
    repondreJSON(['succes' => true, 'utilisateurs' => $stmt->fetchAll()]);
}

/* ══════════════════════════════════════════════════════════════
   Trouver un utilisateur par email (pour attribuer un certif)
   ══════════════════════════════════════════════════════════════ */
function trouverUtilisateur(): void {
    exigerConnexion('promoteur');
    $email = trim($_POST['email'] ?? '');
    if (!$email) repondreJSON(['succes' => false, 'message' => 'E-mail requis.']);

    $db   = getDB();
    $stmt = $db->prepare('SELECT id, nom, prenom, email, role FROM users WHERE email = ? AND actif = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) repondreJSON(['succes' => false, 'message' => 'Utilisateur introuvable.']);
    if ($user['role'] !== 'etudiant') repondreJSON(['succes' => false, 'message' => 'Cet utilisateur n\'est pas un étudiant.']);

    repondreJSON(['succes' => true, 'user' => $user]);
}

/* ══════════════════════════════════════════════════════════════
   Lister les certificats délivrés (promoteur)
   ══════════════════════════════════════════════════════════════ */
function listerCertificatsPromoteur(): void {
    exigerConnexion('promoteur');
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT ce.*, m.titre AS module_titre, u.nom, u.prenom, u.email
        FROM certificats ce
        JOIN modules m ON m.id = ce.module_id
        JOIN users   u ON u.id = ce.etudiant_id
        JOIN modules mm ON mm.promoteur_id = ?
        WHERE ce.module_id = mm.id
        ORDER BY ce.delivre_le DESC
    ');
    $stmt->execute([$_SESSION['user_id']]);
    repondreJSON(['succes' => true, 'certificats' => $stmt->fetchAll()]);
}
