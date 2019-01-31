-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  mer. 30 jan. 2019 à 20:55
-- Version du serveur :  10.1.37-MariaDB
-- Version de PHP :  7.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `reunionit`
--

-- --------------------------------------------------------

--
-- Structure de la table `ext_log_entries`
--

CREATE TABLE `ext_log_entries` (
  `id` int(11) NOT NULL,
  `action` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logged_at` datetime NOT NULL,
  `object_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int(11) NOT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:array)',
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Structure de la table `ext_translations`
--

CREATE TABLE `ext_translations` (
  `id` int(11) NOT NULL,
  `locale` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `object_class` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foreign_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Structure de la table `migration_versions`
--

CREATE TABLE `migration_versions` (
  `version` varchar(14) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `migration_versions`
--

INSERT INTO `migration_versions` (`version`, `executed_at`) VALUES
('20181221114459', NULL),
('20190102111030', NULL),
('20190111160704', NULL),
('20190115113811', NULL),
('20190118202627', '2019-01-21 09:14:32'),
('20190119180707', '2019-01-21 09:14:32'),
('20190119220022', '2019-01-21 09:14:32'),
('20190119232841', '2019-01-21 09:14:32'),
('20190121095034', '2019-01-21 09:53:04');

-- --------------------------------------------------------

--
-- Structure de la table `room`
--

CREATE TABLE `room` (
  `id` int(11) NOT NULL,
  `capacity` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `features` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `room`
--

INSERT INTO `room` (`id`, `capacity`, `name`, `features`, `picture`, `deleted_at`) VALUES
(16, 10, 'Combava', 'a:2:{i:0;s:4:\"Wifi\";i:1;s:16:\"Chauffage au sol\";}', 'salle-16.jpeg', NULL),
(17, 7, 'Paille en queue', 'a:3:{i:0;s:4:\"Wifi\";i:2;s:10:\"Paperboard\";i:4;s:7:\"Estrade\";}', 'salle-17.jpeg', NULL),
(18, 8, 'Rougail', 'a:3:{i:0;s:4:\"Wifi\";i:1;s:16:\"Chauffage au sol\";i:2;s:10:\"Paperboard\";}', 'salle-18.jpeg', NULL),
(19, 14, 'Charrette', 'a:2:{i:0;s:4:\"Wifi\";i:1;s:16:\"Chauffage au sol\";}', 'salle-19.jpeg', NULL),
(20, 25, 'Saint Denis', 'a:3:{i:0;s:4:\"Wifi\";i:1;s:10:\"Paperboard\";i:2;s:7:\"Estrade\";}', 'salle-20.jpeg', NULL),
(21, 5, 'Piment', 'a:3:{i:0;s:4:\"Wifi\";i:1;s:10:\"Paperboard\";i:2;s:18:\"Balcon ou terrasse\";}', 'salle-21.jpeg', NULL),
(22, 5, 'Bambou', 'a:3:{i:1;s:16:\"Chauffage au sol\";i:2;s:18:\"Balcon ou terrasse\";i:0;s:16:\"Vidéoprojecteur\";}', 'salle-22.jpeg', NULL),
(23, 10, 'Corail', 'a:2:{i:0;s:4:\"Wifi\";i:1;s:7:\"Estrade\";}', 'salle-23.jpeg', NULL),
(24, 3, 'Mafate', 'a:4:{i:0;s:4:\"Wifi\";i:1;s:16:\"Chauffage au sol\";i:2;s:16:\"Vidéoprojecteur\";i:3;s:10:\"Paperboard\";}', 'salle-24.jpeg', NULL),
(25, 10, 'Piton des Neiges', 'a:3:{i:0;s:4:\"Wifi\";i:1;s:16:\"Chauffage au sol\";i:2;s:18:\"Balcon ou terrasse\";}', 'salle-25.jpeg', NULL),
(26, 3, 'Saint Gilles', 'a:3:{i:0;s:4:\"Wifi\";i:1;s:16:\"Vidéoprojecteur\";i:2;s:18:\"Balcon ou terrasse\";}', 'salle-26.jpeg', NULL),
(27, 35, 'Requin', 'a:1:{i:0;s:7:\"Estrade\";}', 'salle-27.jpeg', NULL),
(28, 10, 'Trois Bassins', 'a:2:{i:0;s:4:\"Wifi\";i:2;s:10:\"Paperboard\";}', 'salle-28.jpeg', NULL),
(29, 4, 'Dodo', 'a:2:{i:0;s:4:\"Wifi\";i:1;s:16:\"Chauffage au sol\";}', 'salle-29.jpeg', NULL),
(30, 10, 'Lagon', 'a:2:{i:0;s:4:\"Wifi\";i:1;s:16:\"Chauffage au sol\";}', 'salle-30.jpeg', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `unavailability`
--

CREATE TABLE `unavailability` (
  `id` int(11) NOT NULL,
  `organiser_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `object` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `unavailability`
--

INSERT INTO `unavailability` (`id`, `organiser_id`, `room_id`, `start_date`, `end_date`, `object`, `type`) VALUES
(1, 62, 29, '2019-01-09 08:00:00', '2019-01-11 20:00:00', 'Préparation des commandes', 0),
(2, 60, 29, '2019-02-11 14:00:00', '2019-02-11 17:00:00', 'Annonce des chiffres', 0),
(3, 50, 27, '2018-12-18 15:00:00', '2018-12-18 17:00:00', 'Préparation repas de Noël', 0),
(4, 48, 24, '2018-12-10 08:00:00', '2018-12-11 20:00:00', 'Formation logiciel', 0),
(9, 60, 24, '2019-01-10 14:00:00', '2019-01-10 15:30:00', 'Lancement du nouveau projet', 0),
(10, 63, 17, '2019-01-15 14:00:00', '2019-01-15 15:00:00', 'Accueil nouveaux clients', 0),
(13, 63, 17, '2019-01-07 10:00:00', '2019-01-07 11:00:00', 'Réunion du service Logistique', 0),
(16, 48, 17, '2018-12-10 10:00:00', '2018-12-10 20:00:00', 'Réception des fournisseurs', 0),
(19, 48, 29, '2018-12-07 11:00:00', '2018-12-07 12:00:00', 'Réunion de chantier', 0),
(31, 48, 24, '2019-01-14 08:00:00', '2019-01-15 20:00:00', 'Formation logiciel', 0),
(33, 63, 16, '2019-01-28 10:00:00', '2019-01-28 12:30:00', 'Brief client', 0),
(36, 48, 16, '2018-12-05 10:00:00', '2018-12-05 11:00:00', 'Réunion de production', 0),
(38, 63, 17, '2018-12-24 08:00:00', '2018-12-31 20:00:00', 'Fermé pour les vacances', 1),
(39, 50, 27, '2018-12-21 11:00:00', '2018-12-21 15:00:00', 'Repas de Noël', 0),
(45, 60, 22, '2019-01-21 17:30:00', '2019-01-21 19:00:00', 'Présentation CE', 0),
(46, 63, 22, '2019-01-02 10:00:00', '2019-01-02 10:30:00', 'Accueil de bonne année', 0),
(47, 63, 21, '2018-12-24 08:00:00', '2018-12-31 20:00:00', 'Fermé pour les vacances', 1),
(55, 63, 19, '2019-01-23 09:00:00', '2019-01-23 09:30:00', 'Debrief de dernière minute', 0),
(62, 58, 18, '2019-01-25 10:30:00', '2019-01-25 11:00:00', 'Point d\'avancement', 0),
(78, 51, 17, '2018-12-14 10:00:00', '2018-12-14 10:30:00', 'Brief client', 0),
(82, 53, 19, '2019-01-29 14:30:00', '2019-01-29 16:00:00', 'Présentation du client', 0),
(84, 57, 21, '2019-02-06 10:00:00', '2019-02-06 12:00:00', 'Entretiens', 0);

-- --------------------------------------------------------

--
-- Structure de la table `unavailability_user`
--

CREATE TABLE `unavailability_user` (
  `unavailability_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `unavailability_user`
--

INSERT INTO `unavailability_user` (`unavailability_id`, `user_id`) VALUES
(1, 52),
(1, 54),
(1, 57),
(1, 61),
(2, 49),
(2, 57),
(2, 59),
(4, 52),
(9, 51),
(9, 52),
(9, 58),
(9, 61),
(9, 62),
(10, 50),
(10, 54),
(10, 56),
(10, 60),
(13, 60),
(13, 65),
(16, 55),
(16, 56),
(16, 60),
(19, 53),
(19, 57),
(19, 60),
(31, 50),
(31, 54),
(31, 60),
(31, 62),
(31, 65),
(33, 50),
(33, 51),
(33, 60),
(36, 49),
(36, 55),
(36, 60),
(39, 63),
(45, 48),
(45, 50),
(45, 51),
(45, 58),
(45, 62),
(46, 48),
(46, 49),
(46, 51),
(46, 54),
(46, 60),
(47, 48),
(55, 51),
(55, 54),
(55, 58),
(55, 60),
(62, 60),
(62, 61),
(78, 51),
(78, 54),
(78, 57),
(78, 60),
(82, 54),
(82, 62),
(84, 48),
(84, 57);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `first_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `first_name`, `last_name`, `email`, `password`, `roles`, `deleted_at`) VALUES
(48, 'Michel', 'Grenier', 'michel.grenier@reunion.it', '$2y$10$VtOoWABBMVu7/jj7ecZLieYXEiEZw6VAIeHAMALdlI6pcba6e3ljm', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(49, 'Catherine', 'Hamel', 'catherine.hamel@reunion.it', '$2y$10$aSvFZI8WHjCwFUpOTa9raOah41mdbE.RSVHaPzevhP3o4mYnfGFfm', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(50, 'Charles', 'Delorme', 'charles.delorme@reunion.it', '$2y$10$.mjGeNDp3Wa1lLF7QV1dauz9QKrVmB2t8ufpvg231TwEW8GpTSwVO', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(51, 'Arthur', 'Cohen', 'arthur.cohen@reunion.it', '$2y$10$NTZ07ZYoGTrnhCwALpxltOPrW4I/vakZJ0SiqOP9oIJotwUltB2T6', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(52, 'Gabriel', 'Jourdan', 'gabriel.jourdan@reunion.it', '$2y$10$RfCW7aJlrbLpgJ/btbuJ7OINAvMMlZZBpwIZUM4aUjSIxz17FhOte', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(53, 'Lucas', 'Le Goff', 'lucas.le-goff@reunion.it', '$2y$10$vioF8ogaZedITPTnUL5b5O3RumTtqG/FuhfE7TeygfTPPJpX7Ub5C', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', '2019-01-30 20:53:53'),
(54, 'Thierry', 'Daniel', 'thierry.daniel@reunion.it', '$2y$10$cVw7HAq1AGuGL.fOXqPi6e2dzJ1Rchr1BEoylvOaLjtzHsLKTqs2S', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(55, 'Colette', 'Aubert', 'colette.aubert@reunion.it', '$2y$10$KuV0N0gTjYLbfk0rBZdGceRIi6eMayFUz8G.lqPuyn.YOlb/SEyqm', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(56, 'Cécile', 'Leclerc', 'cecile.leclerc@reunion.it', '$2y$10$NsPRi3ldKl3GY2dOyWuPWuEeYd7mBs6OnTXBG1ImlWoj98IsiN6h6', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(57, 'Patrick', 'Guichard', 'patrick.guichard@reunion.it', '$2y$10$3c9EEouWuDFZkK6limzaA.kg01Wa/w1SUwqYsSjbXiYA6fU8BGfQK', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(58, 'Lorraine', 'Aubert', 'lorraine.aubert@reunion.it', '$2y$10$uggpmDiFtOzaoEZFMw41tu8MXpLyq88I5wum5ghR/p9NU15XI1686', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(59, 'Marc', 'Raymond', 'marc.raymond@reunion.it', '$2y$10$BlktiTccTuAPhQk/i5CyIOckfybdRq6WqiYuNoqn92bmPqNOxs7Ha', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(60, 'Frédérique', 'Gallet', 'frederique.gallet@reunion.it', '$2y$10$gbCePfgOAiSwg2PlEWiPMeRnor8uWefE1f4wpHmR/1yrd0LLM2Tk.', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(61, 'Marc', 'Vallet', 'marc.vallet@reunion.it', '$2y$10$bt/fX/vD.f.mpHSgLhgyZOcF89erPkZhP2Hil01Cshaa0q7sn3iHu', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(62, 'Benoît', 'Lemonnier', 'benoit.lemonnier@reunion.it', '$2y$10$/6JNUkS1cc43mJlKjSJIMeaMbV6fN7JutyWb2Fhh/qx0P.5wp50X6', 'a:1:{i:0;s:13:\"ROLE_EMPLOYEE\";}', NULL),
(63, 'Margot', 'Hoareau', 'margot.hoareau@reunion.it', '$2y$10$yuxSf3Tcmg5a70FbV/xgLu9VMAm03dWKIOtXoCPeb93i/OlmFdRWi', 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}', NULL),
(65, 'Thibault', 'Truffert', 'thibault.truffert@reunion.it', '$2y$10$iAg4UOZ8Rc4.hXLuZBhdxuHtjMhzJa5fosgNnQdoW/bx7KWhnkk4K', 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ext_log_entries`
--
ALTER TABLE `ext_log_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `log_class_lookup_idx` (`object_class`(191)),
  ADD KEY `log_date_lookup_idx` (`logged_at`),
  ADD KEY `log_user_lookup_idx` (`username`(191)),
  ADD KEY `log_version_lookup_idx` (`object_id`,`object_class`(191),`version`);

--
-- Index pour la table `ext_translations`
--
ALTER TABLE `ext_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lookup_unique_idx` (`locale`,`object_class`,`field`,`foreign_key`),
  ADD KEY `translations_lookup_idx` (`locale`,`object_class`,`foreign_key`);

--
-- Index pour la table `migration_versions`
--
ALTER TABLE `migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `unavailability`
--
ALTER TABLE `unavailability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_F0016D1A0631C12` (`organiser_id`),
  ADD KEY `IDX_F0016D154177093` (`room_id`);

--
-- Index pour la table `unavailability_user`
--
ALTER TABLE `unavailability_user`
  ADD PRIMARY KEY (`unavailability_id`,`user_id`),
  ADD KEY `IDX_96C9E437F6922FEF` (`unavailability_id`),
  ADD KEY `IDX_96C9E437A76ED395` (`user_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ext_log_entries`
--
ALTER TABLE `ext_log_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ext_translations`
--
ALTER TABLE `ext_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `room`
--
ALTER TABLE `room`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pour la table `unavailability`
--
ALTER TABLE `unavailability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `unavailability`
--
ALTER TABLE `unavailability`
  ADD CONSTRAINT `FK_F0016D154177093` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`),
  ADD CONSTRAINT `FK_F0016D1A0631C12` FOREIGN KEY (`organiser_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `unavailability_user`
--
ALTER TABLE `unavailability_user`
  ADD CONSTRAINT `FK_96C9E437A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_96C9E437F6922FEF` FOREIGN KEY (`unavailability_id`) REFERENCES `unavailability` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
