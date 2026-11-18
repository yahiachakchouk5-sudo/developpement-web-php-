-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 20 avr. 2026 à 15:01
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

--
-- Base de données : `univ_clubs`
--
CREATE DATABASE IF NOT EXISTS `univ_clubs`;
USE `univ_clubs`;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','étudiant') DEFAULT 'étudiant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `created_at`, `updated_at`) VALUES
(2, 'Dupont', 'Marc', 'marc.dupont@univ.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-04-15 10:26:34', '2026-04-15 14:11:43'),
(3, 'Martin', 'Sophie', 'sophie.martin@univ.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'étudiant', '2026-04-15 10:26:34', '2026-04-15 10:26:34'),
(4, 'Leroy', 'Thomas', 'thomas.leroy@univ.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'étudiant', '2026-04-15 10:26:34', '2026-04-15 10:26:34'),
(5, 'Petit', 'Julie', 'julie.petit@univ.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'étudiant', '2026-04-15 10:26:34', '2026-04-15 10:26:34'),
(6, 'Moreau', 'Lucas', 'lucas.moreau@univ.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'étudiant', '2026-04-15 10:26:34', '2026-04-15 10:26:34'),
(7, 'Garcia', 'Emma', 'emma.garcia@univ.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'étudiant', '2026-04-15 10:26:34', '2026-04-15 10:26:34'),
(8, 'Bernard', 'Hugo', 'hugo.bernard@univ.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'étudiant', '2026-04-15 10:26:34', '2026-04-15 10:26:34'),
(9, 'islem', 'benzaid', 'benzaidislem5@gmail.com', '$2y$10$t9jod/Ih.sU7GgKYM6ZYauguB8hjqlyoCIOxonJ6G.SFoCL8eu5L.', 'étudiant', '2026-04-15 14:29:35', '2026-04-15 14:29:35'),
(12, 'Admin', 'Système', 'admin@univ.edu', '$2y$10$qaVvucZc7.qeO8.XGqYbXOXXV02/4DeEre5HszSYoPKrHgvXp8fFC', 'admin', '2026-04-15 18:23:11', '2026-04-15 18:23:11');

-- --------------------------------------------------------

--
-- Structure de la table `clubs`
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `responsable_id` (`responsable_id`),
  CONSTRAINT `clubs_ibfk_1` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `clubs`
--

INSERT INTO `clubs` (`id`, `nom`, `description`, `responsable_id`, `created_at`, `updated_at`) VALUES
(1, 'Club Robotique', 'Conception, programmation Arduino/Raspberry et compétitions nationales.', 2, '2026-04-15 10:26:54', '2026-04-15 10:26:54'),
(2, 'Club Informatique', 'Développement web, IA, cybersécurité et ateliers open-source.', 3, '2026-04-15 10:26:54', '2026-04-15 10:26:54'),
(3, 'Club Théâtre', 'Arts de la scène, improvisation, mise en scène et classiques.', 4, '2026-04-15 10:26:54', '2026-04-15 10:26:54'),
(4, 'Club Musique', 'Pratique instrumentale, chorale, MAO et concerts étudiants.', 5, '2026-04-15 10:26:54', '2026-04-15 10:26:54'),
(5, 'Club Natation', 'Entraînements techniques, compétitions et initiation aquatique.', 6, '2026-04-15 10:26:54', '2026-04-15 10:26:54'),
(6, 'Club Mathématiques', 'Olympiades, résolution de problèmes et tutorat entre pairs.', 7, '2026-04-15 10:26:54', '2026-04-15 10:26:54'),
(7, 'Club Dessin', 'Ateliers de dessin, peinture, aquarelle et exposition des œuvres.', 8, '2026-04-15 10:26:54', '2026-04-15 10:26:54'),
(8, 'Club Cinéma', 'Analyse filmique, tournage de courts-métrages et projections.', 2, '2026-04-15 10:26:54', '2026-04-15 10:26:54'),
(10, 'IEEE', 'fgvk,osdf', 9, '2026-04-16 09:56:10', '2026-04-16 09:56:10');

-- --------------------------------------------------------

--
-- Structure de la table `club_members`
--

CREATE TABLE `club_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `motivation` text DEFAULT NULL,
  `date_adhesion` datetime DEFAULT NULL,
  `date_demande` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_req` (`club_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `club_members_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `club_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `club_members`
--

INSERT INTO `club_members` (`id`, `club_id`, `user_id`, `status`, `motivation`, `date_adhesion`, `date_demande`) VALUES
(1, 1, 6, 'accepted', NULL, '2026-04-16 11:39:22', '2026-04-15 11:27:21'),
(2, 1, 7, 'accepted', NULL, '2026-04-16 11:39:17', '2026-04-15 11:27:21'),
(3, 2, 8, 'rejected', NULL, '2026-03-15 09:00:00', '2026-04-15 11:27:21'),
(4, 3, 6, 'pending', NULL, '2026-04-01 11:00:00', '2026-04-15 11:27:21'),
(5, 4, 7, 'pending', NULL, '2026-04-05 16:00:00', '2026-04-15 11:27:21'),
(6, 5, 8, 'pending', NULL, '2026-04-10 10:30:00', '2026-04-15 11:27:21'),
(7, 6, 6, 'pending', NULL, '2026-04-12 13:00:00', '2026-04-15 11:27:21'),
(8, 7, 7, 'pending', NULL, '2026-04-14 09:00:00', '2026-04-15 11:27:21'),
(9, 8, 8, 'pending', NULL, '2026-04-15 11:00:00', '2026-04-15 11:27:21'),
(10, 4, 9, 'accepted', NULL, '2026-04-15 19:34:11', '2026-04-15 15:41:30'),
(11, 7, 12, 'accepted', NULL, '2026-04-16 10:47:25', '2026-04-15 19:27:41'),
(12, 3, 9, 'accepted', NULL, '2026-04-16 11:38:37', '2026-04-16 11:35:34'),
(15, 8, 9, 'pending', NULL, NULL, '2026-04-16 13:03:16'),
(16, 2, 9, 'pending', NULL, NULL, '2026-04-16 13:03:51');

-- --------------------------------------------------------

--
-- Structure de la table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('open','resolved') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `club_id`, `message`, `status`, `created_at`) VALUES
(1, 9, NULL, '[Avis] mauvais temps', 'resolved', '2026-04-15 18:41:19'),
(2, 9, NULL, '[Reclamation] un excellent site web il est tres pratique et facile a utiliser', 'resolved', '2026-04-16 08:45:04'),
(3, 9, NULL, '[Avis] bonjourrrrrrrrrrrrrrrrrrrrrrrr', 'resolved', '2026-04-16 12:04:20');

-- --------------------------------------------------------

--
-- Structure de la table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `club_id` int(11) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `lieu` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `club_id` (`club_id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `events`
--

INSERT INTO `events` (`id`, `club_id`, `titre`, `description`, `date_debut`, `date_fin`, `lieu`, `created_at`) VALUES
(1, 1, 'Hackathon Robotique 2026', '48h pour concevoir un robot autonome dans un labyrinthe.', '2026-05-10 09:00:00', '2026-05-12 17:00:00', 'Amphithéâtre A', '2026-04-15 10:29:24'),
(2, 2, 'Conférence IA & Éthique', 'Débat sur les enjeux sociétaux de l\'intelligence artificielle.', '2026-05-15 14:00:00', '2026-05-15 16:30:00', 'Salle Conférence B', '2026-04-15 10:29:24'),
(3, 3, 'Le Malade Imaginaire', 'Représentation de la comédie de Molière par les membres.', '2026-05-20 19:00:00', '2026-05-20 21:30:00', 'Théâtre Universitaire', '2026-04-15 10:29:24'),
(4, 4, 'Concert de Printemps', 'Showcase jazz, classique et musiques actuelles.', '2026-05-25 18:00:00', '2026-05-25 20:00:00', 'Cour Centrale', '2026-04-15 10:29:24'),
(5, 5, 'Tournoi Inter-Clubs Natation', 'Compétition de relais 4x50m ouverte à tous niveaux.', '2026-06-01 08:30:00', '2026-06-01 17:00:00', 'Piscine Olympique', '2026-04-15 10:29:24'),
(6, 6, 'Olympiades de Mathématiques', 'Concours de logique et algèbre avec lots à gagner.', '2026-06-05 09:00:00', '2026-06-05 12:00:00', 'Salle 304', '2026-04-15 10:29:24'),
(7, 7, 'Atelier Portrait', 'Techniques de dessin de portrait avec modèle vivant.', '2026-05-18 14:00:00', '2026-05-18 17:00:00', 'Atelier Arts', '2026-04-15 10:29:24'),
(8, 8, 'Projection Courts-Métrages', 'Sélection de films réalisés par les étudiants du club.', '2026-05-28 19:00:00', '2026-05-28 22:00:00', 'Cinéma Campus', '2026-04-15 10:29:24'),
(9, 10, 'erftgyhjkhgfd', '', '2026-05-03 19:57:00', '2026-04-03 15:57:00', 'sfax', '2026-04-18 14:57:48');

-- --------------------------------------------------------

--
-- Structure de la table `event_participants`
--

CREATE TABLE `event_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `participant_nom` varchar(100) DEFAULT NULL,
  `participant_contact` varchar(100) DEFAULT NULL,
  `is_club_member` tinyint(1) DEFAULT 0,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_part` (`event_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `event_participants_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `event_participants`
--

INSERT INTO `event_participants` (`id`, `event_id`, `user_id`, `participant_nom`, `participant_contact`, `is_club_member`, `status`, `date_inscription`) VALUES
(1, 1, 6, NULL, NULL, 0, 'pending', '2026-04-01 09:00:00'),
(2, 1, 7, NULL, NULL, 0, 'pending', '2026-04-02 10:30:00'),
(3, 2, 8, NULL, NULL, 0, 'pending', '2026-04-05 08:00:00'),
(4, 3, 6, NULL, NULL, 0, 'pending', '2026-04-10 13:00:00'),
(5, 4, 7, NULL, NULL, 0, 'pending', '2026-04-12 14:00:00'),
(6, 5, 8, NULL, NULL, 0, 'pending', '2026-04-14 07:00:00'),
(7, 6, 6, NULL, NULL, 0, 'pending', '2026-04-15 09:00:00'),
(8, 7, 7, NULL, NULL, 0, 'pending', '2026-04-16 08:30:00'),
(9, 8, 8, NULL, NULL, 0, 'pending', '2026-04-17 13:00:00'),
(10, 7, 9, NULL, NULL, 0, 'pending', '2026-04-15 14:42:40'),
(11, 4, 9, NULL, NULL, 0, 'pending', '2026-04-15 18:37:59'),
(13, 7, 12, NULL, NULL, 0, 'pending', '2026-04-15 19:02:04'),
(14, 2, 12, NULL, NULL, 0, 'pending', '2026-04-16 08:09:36'),
(16, 9, 9, NULL, NULL, 0, 'pending', '2026-04-18 15:04:57'),
(18, 2, 9, NULL, NULL, 0, 'pending', '2026-04-18 16:09:52'),
(20, 1, 9, 'islem benzaid', 'benzaidislem5@gmail.com', 0, 'accepted', '2026-04-18 16:46:19'),
(21, 3, 9, 'islem benzaid', 'benzaidislem5@gmail.com', 0, 'rejected', '2026-04-18 17:08:44');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notifications`
-- (Table is empty)

-- --------------------------------------------------------