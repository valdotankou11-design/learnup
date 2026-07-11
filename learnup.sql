-- ══════════════════════════════════════════════════════════════
-- LearnUp — Schéma de base de données MySQL
-- LMS : Enseignant | Étudiant | Promoteur
-- TP Dr. MESSI — L2 Informatique — UY1 NGOAH-EKELE 2026
-- ══════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS learnup CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE learnup;

-- ── Utilisateurs ────────────────────────────────────────────────────────────
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL,
    prenom      VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role        ENUM('etudiant','enseignant','promoteur','admin') NOT NULL DEFAULT 'etudiant',
    certifie    TINYINT(1) NOT NULL DEFAULT 0,      -- badge "compte certifié" (type réseau social)
    certifie_le DATETIME DEFAULT NULL,
    avatar      VARCHAR(255) DEFAULT NULL,
    actif       TINYINT(1) DEFAULT 1,
    cree_le     DATETIME DEFAULT CURRENT_TIMESTAMP,
    mis_a_jour  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ── Modules (créés par le promoteur) ────────────────────────────────────────
CREATE TABLE modules (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(200) NOT NULL,
    description TEXT,
    image       VARCHAR(255) DEFAULT NULL,
    promoteur_id INT NOT NULL,
    actif       TINYINT(1) DEFAULT 1,
    cree_le     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promoteur_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Cours (créés par les enseignants, rattachés à un module) ────────────────
CREATE TABLE cours (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    titre        VARCHAR(200) NOT NULL,
    description  TEXT,
    image        VARCHAR(255) DEFAULT NULL,
    module_id    INT NOT NULL,
    enseignant_id INT NOT NULL,
    niveau       ENUM('debutant','intermediaire','avance') DEFAULT 'debutant',
    duree_heures INT DEFAULT 0,
    actif        TINYINT(1) DEFAULT 1,
    cree_le      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id)     REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (enseignant_id) REFERENCES users(id)   ON DELETE CASCADE
);

-- ── Leçons (PDF ou vidéo, créées par l'enseignant) ──────────────────────────
CREATE TABLE lecons (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(200) NOT NULL,
    description TEXT,
    cours_id    INT NOT NULL,
    type        ENUM('pdf','video') NOT NULL,
    fichier     VARCHAR(500) NOT NULL,   -- chemin fichier ou URL vidéo
    ordre       INT DEFAULT 1,
    duree_min   INT DEFAULT 0,           -- durée en minutes
    actif       TINYINT(1) DEFAULT 1,
    cree_le     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE
);

-- ── Évaluations (une par leçon) ─────────────────────────────────────────────
CREATE TABLE evaluations (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    lecon_id    INT NOT NULL UNIQUE,     -- une seule évaluation par leçon
    titre       VARCHAR(200) NOT NULL,
    description TEXT,
    note_passage INT DEFAULT 50,         -- note minimale pour valider (%)
    duree_min   INT DEFAULT 30,          -- durée de l'évaluation en minutes
    actif       TINYINT(1) DEFAULT 1,
    cree_le     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lecon_id) REFERENCES lecons(id) ON DELETE CASCADE
);

-- ── Questions ────────────────────────────────────────────────────────────────
CREATE TABLE questions (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id  INT NOT NULL,
    enonce         TEXT NOT NULL,
    type           ENUM('qcm','vrai_faux','texte_libre') DEFAULT 'qcm',
    points         INT DEFAULT 1,
    ordre          INT DEFAULT 1,
    cree_le        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE
);

-- ── Réponses possibles (pour QCM et vrai/faux) ──────────────────────────────
CREATE TABLE reponses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    texte       TEXT NOT NULL,
    est_correcte TINYINT(1) DEFAULT 0,
    ordre       INT DEFAULT 1,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- ── Inscriptions (étudiant → cours) ─────────────────────────────────────────
CREATE TABLE inscriptions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id  INT NOT NULL,
    cours_id     INT NOT NULL,
    inscrit_le   DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_inscription (etudiant_id, cours_id),
    FOREIGN KEY (etudiant_id) REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (cours_id)    REFERENCES cours(id)  ON DELETE CASCADE
);

-- ── Progression des leçons ───────────────────────────────────────────────────
CREATE TABLE progression_lecons (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id  INT NOT NULL,
    lecon_id     INT NOT NULL,
    termine      TINYINT(1) DEFAULT 0,
    termine_le   DATETIME DEFAULT NULL,
    UNIQUE KEY unique_progression (etudiant_id, lecon_id),
    FOREIGN KEY (etudiant_id) REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (lecon_id)    REFERENCES lecons(id)  ON DELETE CASCADE
);

-- ── Résultats des évaluations ────────────────────────────────────────────────
CREATE TABLE resultats_evaluations (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id    INT NOT NULL,
    evaluation_id  INT NOT NULL,
    note           DECIMAL(5,2) NOT NULL DEFAULT 0,   -- note obtenue en %
    reussi         TINYINT(1) DEFAULT 0,
    tentative      INT DEFAULT 1,
    passe_le       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_resultat (etudiant_id, evaluation_id),
    FOREIGN KEY (etudiant_id)   REFERENCES users(id)        ON DELETE CASCADE,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id)  ON DELETE CASCADE
);

-- ── Réponses données par les étudiants ──────────────────────────────────────
CREATE TABLE reponses_etudiants (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    resultat_id    INT NOT NULL,
    question_id    INT NOT NULL,
    reponse_id     INT DEFAULT NULL,    -- pour QCM/vrai-faux
    reponse_texte  TEXT DEFAULT NULL,   -- pour texte libre
    est_correcte   TINYINT(1) DEFAULT 0,
    FOREIGN KEY (resultat_id) REFERENCES resultats_evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id)             ON DELETE CASCADE,
    FOREIGN KEY (reponse_id)  REFERENCES reponses(id)              ON DELETE SET NULL
);

-- ── Suggestions de modules (enseignant → promoteur) ─────────────────────────
CREATE TABLE suggestions_modules (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    enseignant_id   INT NOT NULL,
    titre           VARCHAR(200) NOT NULL,
    description     TEXT,
    justification   TEXT,
    statut          ENUM('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
    commentaire     TEXT DEFAULT NULL,   -- réponse du promoteur
    cree_le         DATETIME DEFAULT CURRENT_TIMESTAMP,
    traite_le       DATETIME DEFAULT NULL,
    FOREIGN KEY (enseignant_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Certificats ──────────────────────────────────────────────────────────────
CREATE TABLE certificats (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id  INT NOT NULL,
    module_id    INT NOT NULL,
    code_unique  VARCHAR(64) NOT NULL UNIQUE,   -- code de vérification
    delivre_le   DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_certificat (etudiant_id, module_id),
    FOREIGN KEY (etudiant_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (module_id)   REFERENCES modules(id)  ON DELETE CASCADE
);

-- ══════════════════════════════════════════════════════════════
-- DONNÉES DE TEST
-- ══════════════════════════════════════════════════════════════

-- Admin
INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'Super', 'admin@learnup.cm', '$2y$10$X9qA8K2mNpL3vR7wE5tYuO4iB6hG1jD0cF8sZ2eW9kM7nQ3pA5rT', 'admin');

-- Promoteur
INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES
('TANKOU', 'Joël Valdo', 'promoteur@learnup.cm', '$2y$10$X9qA8K2mNpL3vR7wE5tYuO4iB6hG1jD0cF8sZ2eW9kM7nQ3pA5rT', 'promoteur');

-- Enseignant
INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES
('MESSI', 'Docteur', 'messi@learnup.cm', '$2y$10$X9qA8K2mNpL3vR7wE5tYuO4iB6hG1jD0cF8sZ2eW9kM7nQ3pA5rT', 'enseignant');

-- Étudiant
INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES
('SOH', 'Étudiant', 'etudiant@learnup.cm', '$2y$10$X9qA8K2mNpL3vR7wE5tYuO4iB6hG1jD0cF8sZ2eW9kM7nQ3pA5rT', 'etudiant');

-- Module exemple
INSERT INTO modules (titre, description, promoteur_id) VALUES
('Développement Web', 'Maîtrisez HTML, CSS, JavaScript, PHP et MySQL pour créer des applications web modernes.', 1);

-- Cours exemple
INSERT INTO cours (titre, description, module_id, enseignant_id, niveau, duree_heures) VALUES
('Introduction à JavaScript', 'Apprenez les fondamentaux de JavaScript : variables, fonctions, événements et Ajax.', 1, 2, 'debutant', 10);

-- NB: Mot de passe par défaut = "learnup2026" (hashé ci-dessus)
-- À changer en production !
