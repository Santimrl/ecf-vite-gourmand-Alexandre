-- donnees de test

-- comptes : seed_passwords.php

INSERT INTO horaires (jour, ouverture, fermeture, ferme) VALUES
('lundi', '09:00:00', '18:00:00', 0),
('mardi', '09:00:00', '18:00:00', 0),
('mercredi', '09:00:00', '18:00:00', 0),
('jeudi', '09:00:00', '18:00:00', 0),
('vendredi', '09:00:00', '19:00:00', 0),
('samedi', '10:00:00', '16:00:00', 0),
('dimanche', NULL, NULL, 1);

INSERT INTO plats (nom, type_plat, description) VALUES
('Velouté de potiron', 'entree', 'Entrée chaude de saison'),
('Saumon gravlax', 'entree', 'Saumon mariné maison'),
('Bœuf bourguignon', 'plat', 'Mijoté traditionnel'),
('Risotto champignons', 'plat', 'Version végétarienne'),
('Tarte citron', 'dessert', 'Dessert acidulé'),
('Mousse chocolat', 'dessert', 'Classique gourmand');

INSERT INTO allergenes_plat (plat_id, libelle) VALUES
(1, 'lactose'),
(2, 'poisson'),
(3, 'gluten'),
(4, 'lactose'),
(5, 'gluten'),
(5, 'oeuf'),
(6, 'lactose'),
(6, 'oeuf');

INSERT INTO menus (titre, description, theme, regime, nb_personnes_min, prix_min, conditions_menu, stock, actif) VALUES
('Menu Classique Bordelais', 'Notre menu signature pour vos repas d''entreprise et familles.', 'classique', 'classique', 4, 120.00, 'Commander au moins 5 jours avant la prestation. Stockage au frais si plats livrés en avance.', 8, 1),
('Menu Noël Festif', 'Menu de fêtes avec produits de saison.', 'Noel', 'classique', 6, 210.00, 'Commander au moins 2 semaines avant. Pas de modification 72h avant.', 5, 1),
('Menu Végétarien Douceur', 'Sans viande, riche en légumes locaux.', 'classique', 'vegetarien', 2, 70.00, 'Commander au moins 3 jours avant.', 10, 1),
('Menu Pâques Printemps', 'Suggestion de saison pour Pâques.', 'Paques', 'classique', 4, 140.00, 'Commander 10 jours avant.', 4, 1);

INSERT INTO menu_plat (menu_id, plat_id) VALUES
(1, 1), (1, 3), (1, 5),
(2, 2), (2, 3), (2, 6),
(3, 1), (3, 4), (3, 5),
(4, 2), (4, 3), (4, 5);

INSERT INTO menu_images (menu_id, chemin) VALUES
(1, 'assets/img/menu-classique.jpg'),
(2, 'assets/img/menu-noel.jpg'),
(3, 'assets/img/menu-vege.jpg'),
(4, 'assets/img/menu-paques.jpg');
