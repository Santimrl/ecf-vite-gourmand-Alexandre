<?php
class Commande {
    public $id;
    public $utilisateur_id;
    public $menu_id;
    public $nb_personnes;
    public $prix_menu;
    public $prix_livraison;
    public $total;
    public $statut;
    public $ville;
    public $km_hors_bordeaux;

    public function __construct($data = []) {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->utilisateur_id = isset($data['utilisateur_id']) ? (int)$data['utilisateur_id'] : null;
        $this->menu_id = isset($data['menu_id']) ? (int)$data['menu_id'] : null;
        $this->nb_personnes = isset($data['nb_personnes']) ? (int)$data['nb_personnes'] : 0;
        $this->prix_menu = isset($data['prix_menu']) ? (float)$data['prix_menu'] : 0;
        $this->prix_livraison = isset($data['prix_livraison']) ? (float)$data['prix_livraison'] : 0;
        $this->total = isset($data['total']) ? (float)$data['total'] : 0;
        $this->statut = $data['statut'] ?? 'en_attente';
        $this->ville = $data['ville'] ?? '';
        $this->km_hors_bordeaux = isset($data['km_hors_bordeaux']) ? (float)$data['km_hors_bordeaux'] : 0;
    }

    public static function depuisLigne($row) {
        return new self($row);
    }

    // Calcul livraison (regle sujet)
    public static function calculerLivraison($ville, $kmHorsBordeaux = 0) {
        $villeNorm = mb_strtolower(trim((string)$ville));
        if ($villeNorm === 'bordeaux') {
            return 0.0;
        }
        $km = max(0, (float)$kmHorsBordeaux);
        return round(LIVRAISON_BASE + ($km * LIVRAISON_PAR_KM), 2);
    }

    public function peutEtreModifieeParClient() {
        return $this->statut === 'en_attente';
    }
}
