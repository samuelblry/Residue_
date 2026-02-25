-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 25 fév. 2026 à 18:19
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
(1, 'THE LAST DUEL KNIT', '100% cotton, matières épaisses, coupe oversize et carrée, logo brodé.', 99.95, '2026-02-23 14:25:19', NULL, 'knitwear'),
(2, 'MINERAL DYE HOODIE', '100% coton lourd (450gsm). Coupe boxy et oversize. Délavage industriel inspiré des façades de Lisbonne. Finitions brutes et logo brodé ton sur ton.', 120.00, '2026-02-23 15:19:37', NULL, 'hoodies'),
(3, 'INDUSTRIAL BUCKLE BELT', 'Ceinture en cuir vegan texturé motif léopard sombre. Boucle métallique massive signature RESIDUE_ inspirée de l\'architecture brutaliste.', 65.00, '2026-02-23 15:31:12', NULL, 'accessoires'),
(4, 'NIGHTFALL ZIP HOODIE', 'Hoodie zippé bleu nuit avec détails texturés/strass. Coupe boxy et épaules tombantes. 100% coton lourd (450gsm), parfait pour la superposition.', 135.00, '2026-02-23 15:31:12', NULL, 'hoodies'),
(5, 'RAW DENIM WIDE PANT', 'Jeans brut coupe extra-large. Surpiqûres contrastantes rouges et revers imprimés. Toile denim japonaise rigide pour une silhouette architecturale et structurée.', 150.00, '2026-02-23 15:31:12', NULL, 'pantalons'),
(6, 'FADED SHADOW T-SHIRT', 'T-shirt oversize noir avec effet de délavage \"shadow\" au centre et sur les coutures. Coton épais 280gsm, tombé lourd et col ras du cou resserré.', 55.00, '2026-02-23 15:31:12', NULL, 't-shirts'),
(7, 'HEAVYWEIGHT SWEATPANT', 'Jogging oversize en coton ultra-lourd (500gsm). Effet délavé industriel, taille élastique à cordon de serrage, chevilles resserrées et poches profondes. Coupe ample pensée pour le confort et l\'esthétique urbaine.', 95.00, '2026-02-23 15:38:31', NULL, 'pantalons'),
(8, 'STRUCTURED WIDE PANT', 'Pantalon large structuré en toile de coton épaisse. Découpes géométriques, poches utilitaires dissimulées et finitions brutes. Conçu pour résister au paysage urbain tout en gardant une silhouette fluide.', 145.00, '2026-02-23 15:44:03', NULL, 'pantalons'),
(10, 'Camel Jacket ', 'Veste chaude est super belle.', 10000.00, '2026-02-24 15:15:32', 3, 'vestes'),
(11, 'Racing Jacket', 'Veste en cuir.', 5000.00, '2026-02-24 15:29:06', 4, 'vestes'),
(19, 'edfgrhn', 'fgbhn', 5863.00, '2026-02-25 10:54:42', NULL, 'Vestes'),
(20, 'Veste en maille rayée', 'Cotton recyclé', 99.00, '2026-02-25 16:22:47', 6, 'Vestes');

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `size` varchar(10) DEFAULT 'M'
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
(2, 1, 'img/articles/knit.webp', 1),
(3, 1, 'img/articles/knitPorte.webp', 0),
(4, 1, 'img/articles/knitPorteDos.webp', 0),
(5, 1, 'img/articles/knitDetailBroderie.webp', 0),
(6, 2, 'img/articles/cc1.webp', 1),
(7, 2, 'img/articles/cc2.webp', 0),
(8, 2, 'img/articles/cc3.jpg', 0),
(9, 2, 'img/articles/cc4.jpg', 0),
(10, 2, 'img/articles/cc5.webp', 0),
(11, 2, 'img/articles/cc6.webp', 0),
(12, 2, 'img/articles/cc7.webp', 0),
(13, 2, 'img/articles/cc8.webp', 0),
(14, 3, 'img/articles/ceinture.webp', 1),
(15, 3, 'img/articles/ceinturePorte.webp', 0),
(16, 4, 'img/articles/hoodieZip.webp', 1),
(17, 4, 'img/articles/hoodieZipPorte.webp', 0),
(18, 5, 'img/articles/pant.webp', 1),
(19, 5, 'img/articles/pantPorte.webp', 0),
(20, 6, 'img/articles/tshirt.webp', 1),
(21, 6, 'img/articles/tshirtPorte.webp', 0),
(22, 7, 'img/articles/cp1.webp', 1),
(23, 7, 'img/articles/cp2.webp', 0),
(24, 7, 'img/articles/cp3.webp', 0),
(25, 7, 'img/articles/cp4.webp', 0),
(26, 7, 'img/articles/cp5.webp', 0),
(27, 7, 'img/articles/cp6.webp', 0),
(28, 7, 'img/articles/cp7.webp', 0),
(29, 8, 'img/articles/sc1.webp', 1),
(30, 8, 'img/articles/sc2.webp', 0),
(31, 8, 'img/articles/sc3.jpg', 0),
(32, 8, 'img/articles/sc4.jpg', 0),
(33, 8, 'img/articles/sc5.webp', 0),
(34, 8, 'img/articles/sc6.webp', 0),
(35, 8, 'img/articles/sc7.jpg', 0),
(37, 10, 'img/articles/art_10_699db2845a440.jpg', 1),
(38, 10, 'img/articles/art_10_699db2845a903.webp', 0),
(39, 10, 'img/articles/art_10_699db2845ae19.webp', 0),
(40, 11, 'img/articles/art_11_699db5b27b380.png', 0),
(41, 11, 'img/articles/art_11_699db5b27bcac.jpg', 0),
(42, 11, 'img/articles/art_11_699db5b27c56a.jpg', 0),
(43, 11, 'img/articles/art_11_699db5b27da79.webp', 0),
(44, 11, 'img/articles/art_11_699db5b27e298.webp', 1),
(83, 19, 'img/articles/art_19_699ec6e297de6.jpg', 1),
(84, 19, 'img/articles/art_19_699ec6e297655.jpg', 0),
(85, 19, 'img/articles/art_19_699ec6e297071.jpg', 0),
(86, 19, 'img/articles/art_19_699ec6e2965ac.jpg', 0),
(87, 19, 'img/articles/art_19_699ec6e296bf8.jpg', 0),
(88, 20, 'img/articles/art_20_699f13c72eca7.jpg', 1),
(89, 20, 'img/articles/art_20_699f13c72f308.jpg', 0),
(90, 20, 'img/articles/art_20_699f13c730341.jpg', 0);

-- --------------------------------------------------------

--
-- Structure de la table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `billing_firstname` varchar(100) DEFAULT NULL,
  `billing_lastname` varchar(100) DEFAULT NULL,
  `billing_address` varchar(255) NOT NULL,
  `billing_city` varchar(100) NOT NULL,
  `billing_zipcode` varchar(10) NOT NULL,
  `billing_country` varchar(100) DEFAULT NULL,
  `shipping_firstname` varchar(100) DEFAULT NULL,
  `shipping_lastname` varchar(100) DEFAULT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `shipping_zipcode` varchar(20) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `additional_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `invoice`
--

INSERT INTO `invoice` (`id`, `user_id`, `transaction_date`, `amount`, `billing_firstname`, `billing_lastname`, `billing_address`, `billing_city`, `billing_zipcode`, `billing_country`, `shipping_firstname`, `shipping_lastname`, `shipping_address`, `shipping_zipcode`, `shipping_city`, `shipping_country`, `additional_instructions`) VALUES
(3, 4, '2026-02-24 16:40:06', 5215.00, NULL, NULL, 'test chemin test', 'test', '06test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 4, '2026-02-24 16:42:05', 15744.95, NULL, NULL, 'test chemin test', 'test', '06210', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 3, '2026-02-24 16:55:19', 195.00, NULL, NULL, 'test chemin test', 'test', '06210', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 6, '2026-02-25 16:43:05', 65.00, NULL, NULL, 'chemin de la pinede', 'antibes', '06600', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 6, '2026-02-25 16:49:47', 207.99, NULL, NULL, 'chemin de la pinede', 'antibes', '06600', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 6, '2026-02-25 16:50:34', 154.99, NULL, NULL, 'chemin de la pinede', 'antibes', '06600', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 6, '2026-02-25 16:57:06', 64.99, NULL, NULL, 'chemin de la pinede', 'antibes', '06600', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 6, '2026-02-25 17:15:58', 104.99, 'Samuel', 'Bouhnik-Loury', 'chemin de la pinede', 'antibes', '06600', 'France', 'Samuel', 'Bouhnik-Loury', 'chemin de la pinede', '06600', 'antibes', 'France', 'non'),
(14, 6, '2026-02-25 17:50:31', 144.99, 'Samuel', 'Bouhnik-Loury', 'chemin de la pinede', 'antibes', '06600', 'France', 'Samuel', 'Bouhnik-Loury', 'chemin de la pinede', '06600', 'antibes', 'France', 'dfgthy');

-- --------------------------------------------------------

--
-- Structure de la table `invoice_item`
--

CREATE TABLE `invoice_item` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `invoice_item`
--

INSERT INTO `invoice_item` (`id`, `invoice_id`, `article_id`, `quantity`, `price`) VALUES
(1, 3, 11, 1, 5000.00),
(2, 3, 2, 1, 120.00),
(3, 3, 7, 1, 95.00),
(4, 4, 5, 1, 150.00),
(5, 4, 4, 1, 135.00),
(6, 4, 3, 1, 65.00),
(7, 4, 1, 1, 99.95),
(8, 4, 6, 1, 55.00),
(9, 4, 11, 1, 5000.00),
(10, 4, 10, 1, 10000.00),
(11, 4, 8, 1, 145.00),
(12, 4, 7, 1, 95.00),
(13, 5, 3, 3, 65.00),
(17, 9, 3, 1, 65.00),
(18, 10, 20, 2, 99.00),
(19, 11, 8, 1, 145.00),
(20, 12, 6, 1, 55.00),
(21, 13, 7, 1, 95.00),
(22, 14, 4, 1, 135.00);

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quant_xs` int(11) DEFAULT 0,
  `quant_s` int(11) DEFAULT 0,
  `quant_m` int(11) DEFAULT 0,
  `quant_l` int(11) DEFAULT 0,
  `quant_xl` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `article_id`, `quant_xs`, `quant_s`, `quant_m`, `quant_l`, `quant_xl`) VALUES
(1, 1, 5, 6, 0, 5, 0),
(2, 2, 40, 4, 7, 0, 7),
(3, 3, 10, 8, 0, 66, 0),
(4, 4, 7, 0, 0, 7, 2),
(5, 5, 0, 22, 88, 0, 8),
(6, 6, 22, 0, 0, 0, 1),
(7, 7, 8, 13, 0, 3, 12),
(8, 8, 1, 5, 0, 0, 3),
(10, 19, 1, 18, 17, 2, 25),
(11, 20, 5, 10, 15, 10, 5);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `profile_pic` varchar(255) DEFAULT 'default.jpg',
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `prenom`, `nom`, `password`, `email`, `balance`, `profile_pic`, `role`) VALUES
(3, 'admin', NULL, NULL, '$2y$10$fT0rdbB7.abn.e0ar2fCtuQ18QTl.QlAVuONJADbIYCLob9mJwCEm', 'admin@residue.fr', 111065.00, 'default.jpg', 'admin'),
(4, 'test', NULL, NULL, '$2y$10$IaJDyq2ri6y3Xg2/wzJX2u79rxD3LL7wl5C3pmt/9XSHjgTHivSIe', 'test@test.fr', 164190.05, 'default.jpg', 'user'),
(6, 'samuelbl', 'Samuel', 'BL', '$2y$10$extP2OkN3hxau4zFWBFEX.YahXVNDea/01ZKV5hXYY1Sl.XQd7f3K', 'samuel@residue.fr', 100058.03, 'default.jpg', 'user'),
(7, 'jordansch', 'Jordan', 'SCH', '$2y$10$zvD60euXH50.4PpNKI1gdeIR0vThh1ghCPdlssZo56vCwqanh96TS', 'jordan@residue.fr', 0.00, 'user_7_699f11a9cb715.jpg', 'user');

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
-- Index pour la table `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `article_id` (`article_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `image`
--
ALTER TABLE `image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT pour la table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `invoice_item`
--
ALTER TABLE `invoice_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Contraintes pour la table `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD CONSTRAINT `invoice_item_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_item_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
