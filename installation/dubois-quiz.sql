-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : db
-- Généré le : dim. 28 mai 2023 à 20:37
-- Version du serveur : 8.0.30
-- Version de PHP : 8.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `quiz`
--

-- --------------------------------------------------------

--
-- Structure de la table `answer`
--

CREATE TABLE `answer` (
  `id` int NOT NULL,
  `question_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20230508094634', '2023-05-08 09:46:41', 27),
('DoctrineMigrations\\Version20230527125627', '2023-05-27 12:56:41', 72),
('DoctrineMigrations\\Version20230527135213', '2023-05-27 13:52:22', 30);

-- --------------------------------------------------------

--
-- Structure de la table `evaluation`
--

CREATE TABLE `evaluation` (
  `id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `author_id` int NOT NULL,
  `formation_id` int NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `starts_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `ends_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `is_locked` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `average_score` int DEFAULT NULL,
  `copy_count` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formation`
--

CREATE TABLE `formation` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formation`
--

INSERT INTO `formation` (`id`, `name`, `created_at`) VALUES
(43, 'BTS SIO SLAM', '2023-05-28 20:36:55'),
(44, 'BTS MUC', '2023-05-28 20:36:55');

-- --------------------------------------------------------

--
-- Structure de la table `question`
--

CREATE TABLE `question` (
  `id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_score` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `quiz`
--

CREATE TABLE `quiz` (
  `id` int NOT NULL,
  `author_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_score` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `student_answer`
--

CREATE TABLE `student_answer` (
  `id` int NOT NULL,
  `student_copy_id` int NOT NULL,
  `question_id` int NOT NULL,
  `choice_id` int DEFAULT NULL,
  `annotation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` int DEFAULT NULL,
  `answer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `student_copy`
--

CREATE TABLE `student_copy` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `evaluation_id` int NOT NULL,
  `can_share` tinyint(1) NOT NULL,
  `commentary` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` double DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL,
  `position` int DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `name`, `surname`) VALUES
(150, 'formateur1@quiz.com', '[\"ROLE_FORMATEUR\"]', '$2y$13$2EXJTl4gBn4BfPb4sg/oi.R1UMP1EXTpq1HZKgmEQPKEa8DtqW3l.', 'Kunze', 'Jose'),
(151, 'formateur2@quiz.com', '[\"ROLE_FORMATEUR\"]', '$2y$13$Fdslyo7ndLCsAd3Uo1sg9O2mYAYQM0ON6lezOf8Egtx3r3knWv6v2', 'Ledner', 'Reynold'),
(152, 'eleve1@quiz.com', '[\"ROLE_ELEVE\"]', '$2y$13$eIwov/eHBHoQELec5r/cAe.XWZR5flTGEX06garpBQ98IkpFwnUTO', 'Jacobs', 'Matilda'),
(153, 'eleve2@quiz.com', '[\"ROLE_ELEVE\"]', '$2y$13$rD5XOR08JDPj9u7BhlNXYuYS8ibutjZPu.eT5rqKvWklAmYZa1.FO', 'Sipes', 'Gloria'),
(154, 'eleve3@quiz.com', '[\"ROLE_ELEVE\"]', '$2y$13$NfMt9zKhBJL7dksWz10aNOlIb/3gEXebgV9CY/n8n4Z7Fp1Vj9V1u', 'Boyer', 'Larissa'),
(155, 'eleve4@quiz.info', '[\"ROLE_ELEVE\"]', '$2y$13$RmwRDAsTDB.6h9N9H7kjf.tcDLpmEv9Zp5tXFGAoX2pGPQq39ZD/i', 'Runolfsdottir', 'Ava');

-- --------------------------------------------------------

--
-- Structure de la table `user_formation`
--

CREATE TABLE `user_formation` (
  `user_id` int NOT NULL,
  `formation_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_formation`
--

INSERT INTO `user_formation` (`user_id`, `formation_id`) VALUES
(150, 43),
(151, 44),
(152, 44),
(153, 43),
(154, 43),
(155, 44);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DADD4A251E27F6BF` (`question_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1323A575853CD175` (`quiz_id`),
  ADD KEY `IDX_1323A575F675F31B` (`author_id`),
  ADD KEY `IDX_1323A5755200282E` (`formation_id`);

--
-- Index pour la table `formation`
--
ALTER TABLE `formation`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B6F7494E853CD175` (`quiz_id`);

--
-- Index pour la table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A412FA92F675F31B` (`author_id`);

--
-- Index pour la table `student_answer`
--
ALTER TABLE `student_answer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_54EB92A54543928A` (`student_copy_id`),
  ADD KEY `IDX_54EB92A51E27F6BF` (`question_id`),
  ADD KEY `IDX_54EB92A5998666D1` (`choice_id`);

--
-- Index pour la table `student_copy`
--
ALTER TABLE `student_copy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7299C05ACB944F1A` (`student_id`),
  ADD KEY `IDX_7299C05A456C5646` (`evaluation_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);

--
-- Index pour la table `user_formation`
--
ALTER TABLE `user_formation`
  ADD PRIMARY KEY (`user_id`,`formation_id`),
  ADD KEY `IDX_40A0AC5BA76ED395` (`user_id`),
  ADD KEY `IDX_40A0AC5B5200282E` (`formation_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `answer`
--
ALTER TABLE `answer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT pour la table `evaluation`
--
ALTER TABLE `evaluation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `formation`
--
ALTER TABLE `formation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT pour la table `question`
--
ALTER TABLE `question`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT pour la table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT pour la table `student_answer`
--
ALTER TABLE `student_answer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `student_copy`
--
ALTER TABLE `student_copy`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `FK_DADD4A251E27F6BF` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`);

--
-- Contraintes pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD CONSTRAINT `FK_1323A5755200282E` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`),
  ADD CONSTRAINT `FK_1323A575853CD175` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`),
  ADD CONSTRAINT `FK_1323A575F675F31B` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `FK_B6F7494E853CD175` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`);

--
-- Contraintes pour la table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `FK_A412FA92F675F31B` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `student_answer`
--
ALTER TABLE `student_answer`
  ADD CONSTRAINT `FK_54EB92A51E27F6BF` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`),
  ADD CONSTRAINT `FK_54EB92A54543928A` FOREIGN KEY (`student_copy_id`) REFERENCES `student_copy` (`id`),
  ADD CONSTRAINT `FK_54EB92A5998666D1` FOREIGN KEY (`choice_id`) REFERENCES `answer` (`id`);

--
-- Contraintes pour la table `student_copy`
--
ALTER TABLE `student_copy`
  ADD CONSTRAINT `FK_7299C05A456C5646` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluation` (`id`),
  ADD CONSTRAINT `FK_7299C05ACB944F1A` FOREIGN KEY (`student_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `user_formation`
--
ALTER TABLE `user_formation`
  ADD CONSTRAINT `FK_40A0AC5B5200282E` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_40A0AC5BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
