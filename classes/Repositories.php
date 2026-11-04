<?php
// Acces donnees simple : renvoie des objets metier (pas seulement des tableaux)

require_once __DIR__ . '/Utilisateur.php';
require_once __DIR__ . '/Menu.php';
require_once __DIR__ . '/Commande.php';

class UtilisateurRepository {
    public static function trouverParEmail($email) {
        $stmt = db()->prepare('SELECT * FROM utilisateurs WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? Utilisateur::depuisLigne($row) : null;
    }

    public static function trouverParId($id) {
        $stmt = db()->prepare('SELECT * FROM utilisateurs WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();
        return $row ? Utilisateur::depuisLigne($row) : null;
    }
}

class MenuRepository {
    public static function trouverActifParId($id) {
        $stmt = db()->prepare('SELECT * FROM menus WHERE id = ? AND actif = 1 LIMIT 1');
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();
        return $row ? Menu::depuisLigne($row) : null;
    }
}

class CommandeRepository {
    public static function trouverParId($id) {
        $stmt = db()->prepare('SELECT * FROM commandes WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();
        return $row ? Commande::depuisLigne($row) : null;
    }
}
