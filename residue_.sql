-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 24 fév. 2026 à 14:08
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
-- Base de données : `residue_`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `publish_date` datetime DEFAULT current_timestamp(),
  `author_id` int(11) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'unclassified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `name`, `description`, `price`, `publish_date`, `author_id`, `category`) VALUES
(1, 'THE LAST DUEL KNIT', '100% cotton, matières épaisses, coupe oversize et carrée, logo brodé.', 99.95, '2026-02-23 14:25:19', 1, 'unclassified'),
(2, 'MINERAL DYE HOODIE', '100% coton lourd (450gsm). Coupe boxy et oversize. Délavage industriel inspiré des façades de Lisbonne. Finitions brutes et logo brodé ton sur ton.', 120.00, '2026-02-23 15:19:37', 1, 'unclassified'),
(3, 'INDUSTRIAL BUCKLE BELT', 'Ceinture en cuir vegan texturé motif léopard sombre. Boucle métallique massive signature RESIDUE_ inspirée de l\'architecture brutaliste.', 65.00, '2026-02-23 15:31:12', 1, 'unclassified'),
(4, 'NIGHTFALL ZIP HOODIE', 'Hoodie zippé bleu nuit avec détails texturés/strass. Coupe boxy et épaules tombantes. 100% coton lourd (450gsm), parfait pour la superposition.', 135.00, '2026-02-23 15:31:12', 1, 'unclassified'),
(5, 'RAW DENIM WIDE PANT', 'Jeans brut coupe extra-large. Surpiqûres contrastantes rouges et revers imprimés. Toile denim japonaise rigide pour une silhouette architecturale et structurée.', 150.00, '2026-02-23 15:31:12', 1, 'unclassified'),
(6, 'FADED SHADOW T-SHIRT', 'T-shirt oversize noir avec effet de délavage \"shadow\" au centre et sur les coutures. Coton épais 280gsm, tombé lourd et col ras du cou resserré.', 55.00, '2026-02-23 15:31:12', 1, 'unclassified'),
(7, 'HEAVYWEIGHT SWEATPANT', 'Jogging oversize en coton ultra-lourd (500gsm). Effet délavé industriel, taille élastique à cordon de serrage, chevilles resserrées et poches profondes. Coupe ample pensée pour le confort et l\'esthétique urbaine.', 95.00, '2026-02-23 15:38:31', 1, 'unclassified'),
(8, 'STRUCTURED WIDE PANT', 'Pantalon large structuré en toile de coton épaisse. Découpes géométriques, poches utilitaires dissimulées et finitions brutes. Conçu pour résister au paysage urbain tout en gardant une silhouette fluide.', 145.00, '2026-02-23 15:44:03', 1, 'unclassified');

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `image`
--

CREATE TABLE `image` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `image`
--

INSERT INTO `image` (`id`, `article_id`, `url`, `is_main`) VALUES
(2, 1, 'img/knit.webp', 1),
(3, 1, 'img/knitPorte.webp', 0),
(4, 1, 'img/knitPorteDos.webp', 0),
(5, 1, 'img/knitDetailBroderie.webp', 0),
(6, 2, 'img/cc1.webp', 1),
(7, 2, 'img/cc2.webp', 0),
(8, 2, 'img/cc3.jpg', 0),
(9, 2, 'img/cc4.jpg', 0),
(10, 2, 'img/cc5.webp', 0),
(11, 2, 'img/cc6.webp', 0),
(12, 2, 'img/cc7.webp', 0),
(13, 2, 'img/cc8.webp', 0),
(14, 3, 'img/ceinture.webp', 1),
(15, 3, 'img/ceinturePorte.webp', 0),
(16, 4, 'img/hoodieZip.webp', 1),
(17, 4, 'img/hoodieZipPorte.webp', 0),
(18, 5, 'img/pant.webp', 1),
(19, 5, 'img/pantPorte.webp', 0),
(20, 6, 'img/tshirt.webp', 1),
(21, 6, 'img/tshirtPorte.webp', 0),
(22, 7, 'img/cp1.webp', 1),
(23, 7, 'img/cp2.webp', 0),
(24, 7, 'img/cp3.webp', 0),
(25, 7, 'img/cp4.webp', 0),
(26, 7, 'img/cp5.webp', 0),
(27, 7, 'img/cp6.webp', 0),
(28, 7, 'img/cp7.webp', 0),
(29, 8, 'img/sc1.webp', 1),
(30, 8, 'img/sc2.webp', 0),
(31, 8, 'img/sc3.jpg', 0),
(32, 8, 'img/sc4.jpg', 0),
(33, 8, 'img/sc5.webp', 0),
(34, 8, 'img/sc6.webp', 0),
(35, 8, 'img/sc7.jpg', 0);

-- --------------------------------------------------------

--
-- Structure de la table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `billing_address` varchar(255) NOT NULL,
  `billing_city` varchar(100) NOT NULL,
  `billing_zipcode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `article_id`, `quantity`) VALUES
(1, 1, 25),
(2, 2, 30),
(3, 3, 20),
(4, 4, 15),
(5, 5, 25),
(6, 6, 40),
(7, 7, 40),
(8, 8, 35);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `profile_pic` varchar(255) DEFAULT 'default.jpg',
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `balance`, `profile_pic`, `role`) VALUES
(1, 'admin', '$2y$10$lWYTdv1tsDMvgifbHt/Jd.VEz70bPcWFnay3tLPQ4rKKu3YWcjh4q', 'admin@residue.fr', 0.00, 'default.jpg', 'admin');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Index pour la table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `image`
--
ALTER TABLE `image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `image`
--
ALTER TABLE `image`
  ADD CONSTRAINT `image_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
