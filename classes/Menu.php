<?php
class Menu {
    public $id;
    public $titre;
    public $description;
    public $theme;
    public $regime;
    public $nb_personnes_min;
    public $prix_min;
    public $stock;
    public $actif;

    public function __construct($data = []) {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->titre = $data['titre'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->theme = $data['theme'] ?? '';
        $this->regime = $data['regime'] ?? '';
        $this->nb_personnes_min = isset($data['nb_personnes_min']) ? (int)$data['nb_personnes_min'] : 1;
        $this->prix_min = isset($data['prix_min']) ? (float)$data['prix_min'] : 0;
        $this->stock = isset($data['stock']) ? (int)$data['stock'] : 0;
        $this->actif = isset($data['actif']) ? (int)$data['actif'] : 1;
    }

    public static function depuisLigne($row) {
        return new self($row);
    }

    public function estCommandable() {
        return $this->actif === 1 && $this->stock > 0;
    }

    // Calcul metier : prix menu avec reduction eventuelle
    public function calculerPrixMenu($nbPersonnes) {
        $nb = max((int)$nbPersonnes, $this->nb_personnes_min);
        $prix = $this->prix_min;
        // -10% si nb >= minimum + 5 (regle sujet)
        if ($nb >= $this->nb_personnes_min + 5) {
            $prix = $prix * 0.9;
        }
        return round($prix, 2);
    }
}
