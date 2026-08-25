-- =========================================================================
-- Schéma MySQL Complet pour TGTravail (Plateforme Togolaise de Recrutement)
-- =========================================================================

CREATE DATABASE IF NOT EXISTS `tgtravail` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tgtravail`;

-- 1. Table des Utilisateurs
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('candidat', 'recruteur', 'admin') NOT NULL DEFAULT 'candidat',
  `telephone` VARCHAR(30) NULL,
  `avatar` VARCHAR(255) NULL,
  `statut_compte` ENUM('actif', 'suspendu', 'en_attente') NOT NULL DEFAULT 'actif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table des Entreprises
CREATE TABLE IF NOT EXISTS `companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nom` VARCHAR(150) NOT NULL,
  `secteur` VARCHAR(100) NOT NULL,
  `ville` VARCHAR(100) NOT NULL DEFAULT 'Lomé',
  `adresse` VARCHAR(255) NULL,
  `telephone` VARCHAR(30) NULL,
  `email` VARCHAR(191) NULL,
  `logo` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `rccm` VARCHAR(100) NULL,
  `verifie` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table des Profils Candidats
CREATE TABLE IF NOT EXISTS `candidate_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `titre_professionnel` VARCHAR(150) NOT NULL DEFAULT 'Développeuse Web Fullstack',
  `bio` TEXT NULL,
  `ville` VARCHAR(100) NOT NULL DEFAULT 'Lomé',
  `experience_annees` INT NOT NULL DEFAULT 3,
  `disponibilite` VARCHAR(50) NOT NULL DEFAULT 'Immédiate',
  `type_contrat_souhaite` VARCHAR(50) NOT NULL DEFAULT 'CDI',
  `pretention_salariale` INT NOT NULL DEFAULT 200000,
  `competences` TEXT NULL,
  `cv_file` VARCHAR(255) NULL,
  `completion_pct` INT NOT NULL DEFAULT 80,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table des Offres d'Emploi
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `titre` VARCHAR(191) NOT NULL,
  `categorie` VARCHAR(100) NOT NULL,
  `type_contrat` VARCHAR(50) NOT NULL,
  `lieu` VARCHAR(100) NOT NULL DEFAULT 'Lomé',
  `mode_travail` VARCHAR(50) NOT NULL DEFAULT 'Sur site',
  `experience_requise` VARCHAR(50) NOT NULL DEFAULT '2-4 ans',
  `salaire_min` INT NOT NULL DEFAULT 150000,
  `salaire_max` INT NOT NULL DEFAULT 250000,
  `description` TEXT NOT NULL,
  `missions` TEXT NULL,
  `profil_recherche` TEXT NULL,
  `competences_requises` TEXT NULL,
  `date_limite` DATE NULL,
  `pack` ENUM('simple', 'alaune') NOT NULL DEFAULT 'simple',
  `statut` ENUM('active', 'en_attente', 'refusee', 'expiree') NOT NULL DEFAULT 'active',
  `motif_rejet` TEXT NULL,
  `vues_count` INT NOT NULL DEFAULT 0,
  `candidatures_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table des Candidatures
CREATE TABLE IF NOT EXISTS `applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NOT NULL,
  `candidate_id` INT NOT NULL,
  `lettre_motivation` TEXT NULL,
  `cv_url` VARCHAR(255) NULL,
  `pretention_salariale` INT NULL,
  `disponibilite` VARCHAR(50) NULL,
  `statut` ENUM('nouveau', 'evaluation', 'entretien', 'embauche', 'refuse') NOT NULL DEFAULT 'nouveau',
  `date_entretien` DATETIME NULL,
  `lieu_entretien` VARCHAR(191) NULL,
  `type_entretien` VARCHAR(50) NULL,
  `note_recruteur` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`candidate_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table des Conversations & Messages
CREATE TABLE IF NOT EXISTS `conversations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user1_id` INT NOT NULL,
  `user2_id` INT NOT NULL,
  `company_id` INT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user1_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user2_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `sender_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Table des Offres Favoris
CREATE TABLE IF NOT EXISTS `saved_jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `job_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_job_unique` (`user_id`, `job_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Table des Transactions Mobile Money
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `job_id` INT NULL,
  `montant` INT NOT NULL,
  `operateur` ENUM('tmoney', 'flooz') NOT NULL,
  `telephone` VARCHAR(30) NOT NULL,
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `statut` ENUM('reussi', 'en_cours', 'echoue') NOT NULL DEFAULT 'reussi',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Table des Signalements
CREATE TABLE IF NOT EXISTS `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NOT NULL,
  `reporter_user_id` INT NOT NULL,
  `motif` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `statut` ENUM('en_attente', 'traite', 'rejete') NOT NULL DEFAULT 'en_attente',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reporter_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Table des Alertes Emploi
CREATE TABLE IF NOT EXISTS `job_alerts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `mots_cles` VARCHAR(255) NULL,
  `categorie` VARCHAR(100) NULL,
  `type_contrat` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Table des Notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `job_id` INT NULL,
  `message` VARCHAR(255) NOT NULL,
  `lu` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================================
-- JEU DE DONNÉES INITIAL
-- =========================================================================


INSERT INTO `users` (`id`, `nom`, `email`, `password`, `role`, `telephone`, `avatar`) VALUES
(1, 'Komi AGBENYEDZI', 'candidat1@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'candidat', '+228 90 12 34 56', 'https://ui-avatars.com/api/?name=Komi+A&background=random'),
(2, 'Recrutement Togocom', 'rh@togocom.tg', '$2y$10$abcdefghijklmnopqrstuv', 'recruteur', '+228 22 53 44 44', 'https://ui-avatars.com/api/?name=Togocom&background=random'),
(3, 'Recrutement Canal+', 'rh@canalplus.tg', '$2y$10$abcdefghijklmnopqrstuv', 'recruteur', '+228 22 23 23 23', 'https://ui-avatars.com/api/?name=Canal&background=random'),
(4, 'Affiwa DOSSEH', 'candidat2@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'candidat', '+228 91 11 22 33', 'https://ui-avatars.com/api/?name=Affiwa+D&background=random'),
(5, 'Recrutement Ecobank', 'rh@ecobank.com', '$2y$10$abcdefghijklmnopqrstuv', 'recruteur', '+228 22 21 03 03', 'https://ui-avatars.com/api/?name=Ecobank&background=random'),
(6, 'Recrutement Gozem', 'rh@gozem.co', '$2y$10$abcdefghijklmnopqrstuv', 'recruteur', '+228 92 00 00 00', 'https://ui-avatars.com/api/?name=Gozem&background=random'),
(7, 'Recrutement Asky', 'jobs@flyasky.com', '$2y$10$abcdefghijklmnopqrstuv', 'recruteur', '+228 22 23 05 10', 'https://ui-avatars.com/api/?name=Asky&background=random');

INSERT INTO `candidate_profiles` (`user_id`, `titre_professionnel`, `bio`, `ville`, `experience_annees`, `disponibilite`, `type_contrat_souhaite`, `pretention_salariale`, `competences`, `completion_pct`) VALUES
(1, 'Ingénieur Télécom', 'Je suis passionné par les réseaux et la fibre optique.', 'Lomé', 3, 'Immédiate', 'CDI', 300000, 'Fibre optique,Réseaux,Cisco', 85),
(4, 'Responsable Marketing', 'Experte en marketing digital et communication.', 'Lomé', 5, 'Sous 1 mois', 'CDI', 400000, 'Marketing Digital,SEO,Communication', 90);

INSERT INTO `companies` (`id`, `user_id`, `nom`, `secteur`, `ville`, `adresse`, `telephone`, `email`, `rccm`, `verifie`) VALUES
(1, 2, 'Togocom', 'Télécoms', 'Lomé', 'Place de la réconciliation', '+228 22 53 44 44', 'contact@togocom.tg', 'TG-LOM-2019-B-1234', 1),
(2, 3, 'Canal+ Togo', 'Média & Divertissement', 'Lomé', 'Avenue Pdt Eyadéma', '+228 22 23 23 23', 'contact@canalplus.tg', 'TG-LOM-2015-B-0890', 1),
(3, 5, 'Ecobank Togo', 'Banque & Finance', 'Lomé', 'Avenue du 24 Janvier', '+228 22 21 03 03', 'togo@ecobank.com', 'TG-LOM-1988-B-0001', 1),
(4, 6, 'Gozem', 'Technologie & Transport', 'Lomé', 'Quartier OGD', '+228 92 00 00 00', 'hello@gozem.co', 'TG-LOM-2018-B-5555', 1),
(5, 7, 'Asky Airlines', 'Transport Aérien', 'Lomé', 'Aéroport International Gnassingbé Eyadéma', '+228 22 23 05 10', 'info@flyasky.com', 'TG-LOM-2010-B-2222', 1);

INSERT INTO `jobs` (`id`, `company_id`, `titre`, `categorie`, `type_contrat`, `lieu`, `mode_travail`, `experience_requise`, `salaire_min`, `salaire_max`, `description`, `missions`, `profil_recherche`, `competences_requises`, `date_limite`, `pack`, `statut`, `vues_count`, `candidatures_count`) VALUES
(1, 1, 'Ingénieur Réseaux et Télécoms', 'Informatique', 'CDI', 'Lomé', 'Présentiel', '3-5 ans', 300000, 500000, 'Rejoignez la première entreprise de télécommunication au Togo pour maintenir et développer notre réseau.', '- Maintenir les équipements réseaux
- Déployer la fibre optique
- Assurer le support N2', 'De formation ingénieur, vous avez une solide expérience.', 'Cisco,Fibre Optique,TCP/IP', DATE_ADD(NOW(), INTERVAL 30 DAY), 'alaune', 'active', 120, 1),
(2, 3, 'Conseiller Clientèle', 'Service Client', 'CDD', 'Lomé', 'Présentiel', '1-2 ans', 150000, 200000, 'Nous recrutons des conseillers pour nos agences bancaires à travers le pays.', '- Accueillir les clients
- Proposer des services bancaires
- Gérer les réclamations', 'Souriant et rigoureux, niveau BAC+2.', 'Accueil,Vente,Relation Client', DATE_ADD(NOW(), INTERVAL 15 DAY), 'simple', 'active', 85, 0),
(3, 2, 'Responsable Marketing Digital', 'Marketing', 'CDI', 'Lomé', 'Hybride', '3-5 ans', 250000, 400000, 'Pilotez la stratégie digitale de nos bouquets et offres en ligne.', '- Création de campagnes
- Gestion des réseaux sociaux
- Analyse des KPIs', 'Créatif et analytique.', 'SEO,Social Media,Analytics', DATE_ADD(NOW(), INTERVAL 45 DAY), 'alaune', 'active', 340, 1),
(4, 4, 'Développeur Mobile iOS/Android', 'Informatique', 'CDI', 'Lomé', 'Télétravail', '2-4 ans', 350000, 600000, 'Participez à la refonte de notre super-app.', '- Développer de nouvelles features
- Optimiser les performances
- Corriger les bugs', 'Vous maîtrisez Flutter ou React Native.', 'Flutter,React Native,Mobile', DATE_ADD(NOW(), INTERVAL 20 DAY), 'alaune', 'active', 250, 0),
(5, 5, 'Agent de comptoir', 'Accueil & Tourisme', 'CDI', 'Lomé', 'Présentiel', '1-2 ans', 150000, 180000, 'Assistez nos passagers à l\'aéroport de Lomé.', '- Enregistrement des bagages
- Vérification des documents
- Accueil', 'Maîtrise de l\'anglais et du français.', 'Accueil,Anglais,Amadeus', DATE_ADD(NOW(), INTERVAL 10 DAY), 'simple', 'active', 500, 0);

INSERT INTO `applications` (`id`, `job_id`, `candidate_id`, `lettre_motivation`, `pretention_salariale`, `disponibilite`, `statut`, `created_at`) VALUES
(1, 1, 1, 'Je suis passionné par vos offres internet et je voudrais contribuer à leur déploiement.', 350000, 'Immédiate', 'nouveau', NOW()),
(2, 3, 4, 'Je suis la candidate idéale pour piloter votre marketing.', 380000, 'Sous 1 mois', 'evaluation', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `conversations` (`id`, `user1_id`, `user2_id`, `company_id`) VALUES
(1, 1, 2, 1);

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `message`, `created_at`) VALUES
(1, 1, 2, 'Bonjour Komi, votre profil correspond. Pouvons-nous nous appeler demain ?', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 1, 1, 'Bonjour, oui avec grand plaisir.', DATE_SUB(NOW(), INTERVAL 10 MINUTE));
