-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 18 déc. 2025 à 18:06
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `saim`
--
CREATE DATABASE IF NOT EXISTS `saim` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `saim`;

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

DROP TABLE IF EXISTS `cours`;
CREATE TABLE IF NOT EXISTS `cours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) DEFAULT NULL,
  `niveau` varchar(10) DEFAULT NULL,
  `annee` varchar(20) DEFAULT NULL,
  `filiere_id` int(11) DEFAULT NULL,
  `fichier_pdf` varchar(255) DEFAULT NULL,
  `matiere` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `filiere_id` (`filiere_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONS POUR LA TABLE `cours`:
--   `filiere_id`
--       `filiere` -> `id`
--

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id`, `titre`, `niveau`, `annee`, `filiere_id`, `fichier_pdf`, `matiere`) VALUES
(2, 'Maintenance et configuration informatique', 'l2', '2024_2025', NULL, '1765702363_Maintenance - methodes organisations - Copie.pdf', 'Maintenance et configuration informatique'),
(4, 'Administration et sécurité réseaux', 'l1', '2024_2025', NULL, '1765704987_adressage_IP.pdf', 'Administration et sécurité réseaux'),
(5, 'Concepts et mise en oeuvre réseaux', 'l2', '2025_2026', NULL, '1766054490_Les réseaux locaux.pdf', 'Concepts et mise en oeuvre réseaux'),
(6, 'Statistique 2', 'l2', '2025_2026', NULL, '1766054513_cours_stat_S4.pdf', 'Statistique 2'),
(7, 'Concepts et mise en oeuvre réseaux', 'l2', '2025_2026', NULL, '1766054541_Les_Rzseaux_informatiques_d_entreprise.pdf', 'Concepts et mise en oeuvre réseaux'),
(8, 'Concepts et mise en oeuvre réseaux', 'l2', '2025_2026', NULL, '1766054566_cours-rli.pdf', 'Concepts et mise en oeuvre réseaux');

-- --------------------------------------------------------

--
-- Structure de la table `eleves`
--

DROP TABLE IF EXISTS `eleves`;
CREATE TABLE IF NOT EXISTS `eleves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `niveau` varchar(60) DEFAULT NULL,
  `matricule` varchar(60) DEFAULT NULL,
  `pass` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONS POUR LA TABLE `eleves`:
--

--
-- Déchargement des données de la table `eleves`
--

INSERT INTO `eleves` (`id`, `nom`, `prenom`, `niveau`, `matricule`, `pass`) VALUES
(1, 'nabi', 'imrane', 'L2', 'i01c', '$2y$10$1RTqHzVqEMmyoMzDnDBrauVaN7LGsLkcx1TZx7lAjFKvh1.odGJ9O'),
(2, 'nabi', 'imrane', 'L2', 'i01c', '$2y$10$bCqglVAV.mUWipuHK76tIeqzkLg5o.6aWIlQva58fU7dNF0Az5FQm'),
(3, '', '', 'L1', 'i01c', '$2y$10$kAlZXg6oFmccSrgZZWGFYeL6LUqxm9mcb6f0oDgi1Rq/VzvZmfb4a'),
(4, '', '', 'L1', 'i01c', '$2y$10$w6V.0JgUpVHbw/92lgYuye/U.lIOAJ0ix9C/VlcCwQkhX1oAQNexG'),
(5, 'said', 'idarroussi', 'L2', 'i30c', '$2y$10$vWY5KwY5vhY23sXR1BMAwuGOei689jhYj4SiNAuSjlkA2/ed1QXyC'),
(6, 'admin', 'admin', 'L1', '', '$2y$10$V89nalcl8qrSFpVxvNDK.Oc3.hi01.VBC4XmwCD4XOs066NjqWsnC');

-- --------------------------------------------------------

--
-- Structure de la table `exercices`
--

DROP TABLE IF EXISTS `exercices`;
CREATE TABLE IF NOT EXISTS `exercices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) DEFAULT NULL,
  `niveau` varchar(10) DEFAULT NULL,
  `annee` varchar(20) DEFAULT NULL,
  `filiere_id` int(11) DEFAULT NULL,
  `fichier_pdf` varchar(255) DEFAULT NULL,
  `matiere` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `filiere_id` (`filiere_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONS POUR LA TABLE `exercices`:
--   `filiere_id`
--       `filiere` -> `id`
--

-- --------------------------------------------------------

--
-- Structure de la table `filiere`
--

DROP TABLE IF EXISTS `filiere`;
CREATE TABLE IF NOT EXISTS `filiere` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONS POUR LA TABLE `filiere`:
--

-- --------------------------------------------------------

--
-- Structure de la table `historique_download`
--

DROP TABLE IF EXISTS `historique_download`;
CREATE TABLE IF NOT EXISTS `historique_download` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) DEFAULT NULL,
  `fichier` varchar(255) DEFAULT NULL,
  `ip_user` varchar(50) DEFAULT NULL,
  `date_download` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONS POUR LA TABLE `historique_download`:
--

--
-- Déchargement des données de la table `historique_download`
--

INSERT INTO `historique_download` (`id`, `titre`, `fichier`, `ip_user`, `date_download`) VALUES
(1, 'Maintenance Informatique', '1765699940_Maintenance - methodes organisations - Copie.pdf', '127.0.0.1', '2025-12-14 09:44:08'),
(2, 'Maintenance Informatique', '1765699940_Maintenance - methodes organisations - Copie.pdf', '127.0.0.1', '2025-12-14 09:44:09'),
(3, 'Maintenance et configuration informatique', '1765702363_Maintenance - methodes organisations - Copie.pdf', '127.0.0.1', '2025-12-14 10:14:22'),
(4, 'Maintenance et configuration informatique', '1765702363_Maintenance - methodes organisations - Copie.pdf', '127.0.0.1', '2025-12-14 10:14:22'),
(5, 'Maintenance et configuration informatique', '1765702363_Maintenance - methodes organisations - Copie.pdf', '127.0.0.1', '2025-12-18 11:39:36'),
(6, 'Maintenance et configuration informatique', '1765702363_Maintenance - methodes organisations - Copie.pdf', '127.0.0.1', '2025-12-18 11:39:37'),
(7, 'Administration et sécurité réseaux', '1765704987_adressage_IP.pdf', '127.0.0.1', '2025-12-18 17:44:03'),
(8, 'Administration et sécurité réseaux', '1765704987_adressage_IP.pdf', '127.0.0.1', '2025-12-18 17:44:04');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cours`
--
ALTER TABLE `cours`
  ADD CONSTRAINT `cours_ibfk_1` FOREIGN KEY (`filiere_id`) REFERENCES `filiere` (`id`);

--
-- Contraintes pour la table `exercices`
--
ALTER TABLE `exercices`
  ADD CONSTRAINT `exercices_ibfk_1` FOREIGN KEY (`filiere_id`) REFERENCES `filiere` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
