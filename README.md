# PROJET RESIDUE_ - E-commerce Plateform

## 📝 Description du projet
Ce projet est une plateforme e-commerce complète développée en **PHP, MySQL, HTML5, CSS3 et JavaScript Vanilla** (sans framework externe). À l'origine conçu pour répondre aux normes d'Accessibilité Web, il a évolué pour intégrer un backend complet et des fonctionnalités avancées d'e-commerce.

Le thème est **RESIDUE_**, une marque de vêtements streetwear au style brutaliste (*no waste, just taste.*). L'objectif est de concilier une esthétique forte (design brut, asymétrique) avec une expérience utilisateur optimale et des performances solides.

## 🚀 Fonctionnalités Clés

### Front-end / Utilisateur
* **Boutique Dynamique :** Affichage asynchrone des "Nouveautés" et des "Best Sellers" basé sur l'historique des ventes et des dates de publication.
* **Filtres et Recherche :** Recherche d'articles par mot-clé et catégories (Hoodies, Knitwear, Pantalons, Vestes, T-shirts, Accessoires).
* **Pages Produits Détaillées :** Galeries d'images avec effet de survol, sélecteur de tailles (XS à XL).
* **Espace Client :** Authentification sécurisée (hachage des mots de passe), gestion de profil (avatar, informations perso), consultation de la balance, affichage des factures et de l'historique de commandes.
* **Panier & Paiement :** Ajout au panier avec choix de tailles, système de checkout avec adresses de facturation/livraison, intégration de la balance virtuelle de l'utilisateur.

### Back-end / Administrateur
* **Tableau de Bord Administrateur :** Interface dédiée pour gérer la boutique.
* **Gestion des Articles :** Création, modification, et suppression (CRUD) d'articles avec titre, description, prix, et catégorie.
* **Gestion des Stocks :** Stock différencié par tailles (XS, S, M, L, XL).
* **Médias & Images :** Upload multiple d'images (format webp, jpg, png), système de glisser-déposer (Drag & Drop) pour réorganiser l'ordre des images de la galerie produit.

## 🛠️ Stack Technique
* **Back-end :** PHP 8+, MySQL / MariaDB (via MySQLi)
* **Front-end :** HTML5 (Sémantique et WAI-ARIA), CSS3 (Flexbox/Grid, variables CSS), JavaScript ES6 (Fetch/AJAX, DOM Manipulation, Drag-and-drop API)
* **Architecture :** MVC simplifié (séparation de la logique BDD via `includes/db.php` et de l'affichage)

## 🗄️ Structure de la Base de Données
Le projet utilise plusieurs tables relationnelles pour gérer la boutique :
* `user` : Utilisateurs et administrateurs (rôles, solde, infos de profil).
* `article` : Produits du catalogue.
* `stock` : Quantités disponibles découpées par taille.
* `image` : Gestion des galeries (marquage de l'image principale).
* `cart` : Panier temporaire des utilisateurs.
* `invoice` & `invoice_item` : Historique des commandes et facturation.

## 📷 Présentation

![](presentation.gif)

## ⚙️ Installation Globale

1. **Prérequis :** Serveur local (ex: XAMPP, WAMP, MAMP) comprenant Apache et MySQL.
2. **Cloner le projet :**
   ```bash
   git clone https://github.com/samuelblry/Residue_.git
   cd Residue_
   ```
3. **Placer le projet :** Dans le répertoire web de votre serveur local (`htdocs` ou `www`).
4. **Base de données :**
   - Créez une base de données MySQL nommée `residue_`.
   - Importez le fichier SQL fourni `residue_.sql` via phpMyAdmin ou en ligne de commande.
5. **Configuration Back-end :**
    - Créez un fichier `.env` à la racine du projet en vous basant sur l'exemple suivant :
      ```env
      DB_HOST=your_host
      DB_USER=your_user
      DB_PASS=your_password
      DB_NAME=your_db_name
      ```
    - Les paramètres de connexion sont automatiquement chargés via `includes/db.php`.

## ✅ Validation et Qualité
* **Sécurité :** Protections contre l'injection SQL (requêtes préparées) et les attaques XSS. Mots de passe chiffrés avec `password_hash()`.
* **Accessibilité :** W3C Validator, Lighthouse (Score cible > 90 en Accessibilité et Bonnes Pratiques).

## 👤 Auteur
Projet réalisé par **William Pons & Samuel Bouhnik-Loury**.
