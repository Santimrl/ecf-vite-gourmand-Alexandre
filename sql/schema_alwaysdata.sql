-- schema bdd vite_gourmand


SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS commande_suivi;
DROP TABLE IF EXISTS commandes;
DROP TABLE IF EXISTS menu_plat;
DROP TABLE IF EXISTS allergenes_plat;
DROP TABLE IF EXISTS plats;
DROP TABLE IF EXISTS menu_images;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS horaires;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS contact_messages;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  gsm VARCHAR(30) NOT NULL,
  adresse TEXT NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,
  role ENUM('utilisateur','employe','admin') NOT NULL DEFAULT 'utilisateur',
  actif TINYINT(1) NOT NULL DEFAULT 1,
  reset_token VARCHAR(64) NULL,
  reset_expire DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE menus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  theme ENUM('Noel','Paques','classique','evenement') NOT NULL DEFAULT 'classique',
  regime ENUM('vegetarien','vegan','classique') NOT NULL DEFAULT 'classique',
  nb_personnes_min INT NOT NULL DEFAULT 2,
  prix_min DECIMAL(10,2) NOT NULL,
  conditions_menu TEXT NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE menu_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  menu_id INT NOT NULL,
  chemin VARCHAR(255) NOT NULL,
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(150) NOT NULL,
  type_plat ENUM('entree','plat','dessert') NOT NULL,
  description TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE allergenes_plat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plat_id INT NOT NULL,
  libelle VARCHAR(100) NOT NULL,
  FOREIGN KEY (plat_id) REFERENCES plats(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE menu_plat (
  menu_id INT NOT NULL,
  plat_id INT NOT NULL,
  PRIMARY KEY (menu_id, plat_id),
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
  FOREIGN KEY (plat_id) REFERENCES plats(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE commandes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  menu_id INT NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  gsm VARCHAR(30) NOT NULL,
  adresse_prestation TEXT NOT NULL,
  date_prestation DATE NOT NULL,
  heure_prestation TIME NOT NULL,
  lieu VARCHAR(255) NOT NULL,
  ville VARCHAR(100) NOT NULL,
  km_hors_bordeaux DECIMAL(6,2) NOT NULL DEFAULT 0,
  nb_personnes INT NOT NULL,
  prix_menu DECIMAL(10,2) NOT NULL,
  prix_livraison DECIMAL(10,2) NOT NULL DEFAULT 0,
  reduction_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  statut ENUM('en_attente','accepte','en_preparation','en_livraison','livre','attente_materiel','terminee','annulee') NOT NULL DEFAULT 'en_attente',
  motif_annulation TEXT NULL,
  mode_contact_annulation VARCHAR(50) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
  FOREIGN KEY (menu_id) REFERENCES menus(id)
) ENGINE=InnoDB;

CREATE TABLE commande_suivi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  commande_id INT NOT NULL,
  statut VARCHAR(50) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE avis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  commande_id INT NOT NULL UNIQUE,
  utilisateur_id INT NOT NULL,
  note TINYINT NOT NULL,
  commentaire TEXT NOT NULL,
  valide TINYINT(1) NULL DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
) ENGINE=InnoDB;

CREATE TABLE horaires (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jour ENUM('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL UNIQUE,
  ouverture TIME NULL,
  fermeture TIME NULL,
  ferme TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  email VARCHAR(190) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
