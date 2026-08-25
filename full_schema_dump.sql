CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `lettre_motivation` text,
  `video_pitch` varchar(255) DEFAULT NULL,
  `cv_url` varchar(255) DEFAULT NULL,
  `pretention_salariale` int(11) DEFAULT NULL,
  `disponibilite` varchar(50) DEFAULT NULL,
  `statut` enum('nouveau','evaluation','entretien','embauche','refuse','en_attente','vue','retenu','accepte') DEFAULT 'nouveau',
  `date_entretien` datetime DEFAULT NULL,
  `lieu_entretien` varchar(191) DEFAULT NULL,
  `type_entretien` varchar(50) DEFAULT NULL,
  `note_recruteur` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `candidate_id` (`candidate_id`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `titre_professionnel` varchar(150) NOT NULL DEFAULT 'Développeuse Web Fullstack',
  `bio` text,
  `ville` varchar(100) NOT NULL DEFAULT 'Lomé',
  `experience_annees` int(11) NOT NULL DEFAULT '3',
  `disponibilite` varchar(50) NOT NULL DEFAULT 'Immédiate',
  `type_contrat_souhaite` varchar(50) NOT NULL DEFAULT 'CDI',
  `pretention_salariale` int(11) NOT NULL DEFAULT '200000',
  `competences` text,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `cv_file` varchar(255) DEFAULT NULL,
  `completion_pct` int(11) NOT NULL DEFAULT '80',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `subscription_plan` enum('gratuit','premium') DEFAULT 'gratuit',
  `subscription_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `candidate_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `secteur` varchar(100) NOT NULL,
  `ville` varchar(100) NOT NULL DEFAULT 'Lomé',
  `adresse` varchar(255) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text,
  `rccm` varchar(100) DEFAULT NULL,
  `verifie` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `site_web` varchar(255) DEFAULT NULL,
  `type_entite` enum('entreprise','particulier') DEFAULT 'entreprise',
  `cni_document` varchar(255) DEFAULT NULL,
  `statut_validation` enum('en_attente','valide','rejete','a_completer') DEFAULT 'en_attente',
  `motif_rejet` text,
  `subscription_plan` enum('gratuit','basique','premium') DEFAULT 'gratuit',
  `subscription_end` datetime DEFAULT NULL,
  `job_credits` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `conseils` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `temps_lecture` int(11) NOT NULL DEFAULT '5',
  `icone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '?',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user1_id` (`user1_id`),
  KEY `user2_id` (`user2_id`),
  CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cv_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `html_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `css_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recruiter_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `date_time` datetime NOT NULL,
  `meet_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('planned','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'planned',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recruiter_id` (`recruiter_id`),
  KEY `candidate_id` (`candidate_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`recruiter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interviews_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interviews_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mots_cles` varchar(255) DEFAULT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `type_contrat` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `job_alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `titre` varchar(191) NOT NULL,
  `categorie` varchar(100) NOT NULL,
  `type_contrat` varchar(50) NOT NULL,
  `lieu` varchar(100) NOT NULL DEFAULT 'Lomé',
  `mode_travail` varchar(50) NOT NULL DEFAULT 'Sur site',
  `experience_requise` varchar(50) NOT NULL DEFAULT '2-4 ans',
  `salaire_min` int(11) NOT NULL DEFAULT '150000',
  `salaire_max` int(11) NOT NULL DEFAULT '250000',
  `description` text NOT NULL,
  `missions` text,
  `profil_recherche` text,
  `competences_requises` text,
  `date_limite` date DEFAULT NULL,
  `pack` enum('simple','alaune') NOT NULL DEFAULT 'simple',
  `statut` enum('active','en_attente','refusee','expiree','ouverte','suspendue') DEFAULT 'active',
  `motif_rejet` text,
  `vues_count` int(11) NOT NULL DEFAULT '0',
  `candidatures_count` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `boosted_until` datetime DEFAULT NULL,
  `featured_until` datetime DEFAULT NULL,
  `push_sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `reporter_user_id` int(11) NOT NULL,
  `motif` varchar(100) NOT NULL,
  `details` text,
  `statut` enum('en_attente','traite','rejete') NOT NULL DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statut_traitement` enum('nouveau','en_cours','resolu') DEFAULT 'nouveau',
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `reporter_user_id` (`reporter_user_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reviewer_id` (`reviewer_id`),
  KEY `reviewee_id` (`reviewee_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `saved_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_job_unique` (`user_id`,`job_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `saved_jobs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `saved_jobs_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `signalements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `motif` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('en attente','traite') COLLATE utf8mb4_unicode_ci DEFAULT 'en attente',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `signalements_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `signalements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sujet` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `statut` enum('nouveau','en_cours','resolu') NOT NULL DEFAULT 'nouveau',
  `reponse` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `montant` int(11) NOT NULL,
  `operateur` enum('tmoney','flooz') NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `statut` enum('reussi','en_cours','echoue') NOT NULL DEFAULT 'reussi',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('candidat','recruteur','admin') NOT NULL DEFAULT 'candidat',
  `admin_level` enum('superadmin','moderator') DEFAULT 'superadmin',
  `telephone` varchar(30) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `statut_compte` enum('actif','suspendu','en_attente') NOT NULL DEFAULT 'actif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

