<?php
/**
 * LearnUp — attente.php
 * Page affichée au promoteur après inscription,
 * en attente de validation par l'administrateur.
 */
require_once __DIR__ . '/config/db.php';

// Si l'utilisateur est déjà validé et connecté, on le redirige directement
if (utilisateurConnecte()) {
    header('Location: /dashboard/' . $_SESSION['user_role'] . '.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LearnUp — Candidature en attente</title>
  <link rel="stylesheet" href="/assets/css/style.css"/>
  <link rel="manifest" href="/manifest.json"/>
  <meta name="theme-color" content="#6C63FF"/>
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: var(--bg);
      padding: 20px;
    }

    .attente-card {
      background: var(--surface);
      border: 1px solid var(--bordure);
      border-radius: 20px;
      padding: 56px 48px;
      max-width: 520px;
      width: 100%;
      text-align: center;
      box-shadow: 0 8px 40px rgba(0,0,0,0.25);
    }

    .attente-icone {
      font-size: 4rem;
      margin-bottom: 24px;
      display: block;
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1);   opacity: 1;   }
      50%       { transform: scale(1.1); opacity: 0.8; }
    }

    .attente-titre {
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--texte);
      margin-bottom: 16px;
    }

    .attente-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(251, 191, 36, 0.12);
      color: #FBBF24;
      border: 1px solid rgba(251, 191, 36, 0.3);
      border-radius: 50px;
      padding: 6px 18px;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 24px;
    }

    .attente-badge::before {
      content: '';
      width: 8px; height: 8px;
      background: #FBBF24;
      border-radius: 50%;
      animation: clignoter 1.2s ease-in-out infinite;
    }

    @keyframes clignoter {
      0%, 100% { opacity: 1; }
      50%       { opacity: 0.2; }
    }

    .attente-description {
      color: var(--texte2);
      font-size: 0.97rem;
      line-height: 1.7;
      margin-bottom: 16px;
    }

    .attente-info {
      background: rgba(108, 99, 255, 0.08);
      border: 1px solid rgba(108, 99, 255, 0.2);
      border-radius: 12px;
      padding: 16px 20px;
      color: var(--texte2);
      font-size: 0.88rem;
      line-height: 1.6;
      margin-bottom: 36px;
      text-align: left;
    }

    .attente-info strong {
      color: var(--accent);
    }

    .btn-accueil {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 12px;
      padding: 14px 32px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: opacity 0.2s, transform 0.2s;
    }

    .btn-accueil:hover {
      opacity: 0.88;
      transform: translateY(-1px);
    }

    .learnup-logo {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--accent);
      margin-bottom: 32px;
      display: block;
    }
  </style>
</head>
<body>

  <div class="attente-card">

    <span class="learnup-logo">🎓 LearnUp</span>

    <span class="attente-icone">⏳</span>

    <h1 class="attente-titre">Candidature soumise</h1>

    <div class="attente-badge">Validation en attente</div>

    <p class="attente-description">
      Votre demande d'inscription en tant que <strong>Promoteur</strong> a bien été reçue.
      Un administrateur va examiner votre candidature dans les plus brefs délais.
    </p>

    <div class="attente-info">
      📧 <strong>Prochaine étape :</strong> Vous recevrez une notification dès que votre compte sera activé.
      Vous pourrez alors vous connecter et commencer à créer vos modules de formation.
    </div>

    <a href="/" class="btn-accueil">
      ← Retour à l'accueil
    </a>

  </div>

</body>
</html>
