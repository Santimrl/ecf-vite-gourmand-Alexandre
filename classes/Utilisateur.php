<?php
// Petite classe metier (POO simple etudiant)
class Utilisateur {
    public $id;
    public $nom;
    public $prenom;
    public $email;
    public $gsm;
    public $adresse;
    public $role;
    public $actif;

    public function __construct($data = []) {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->nom = $data['nom'] ?? '';
        $this->prenom = $data['prenom'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->gsm = $data['gsm'] ?? '';
        $this->adresse = $data['adresse'] ?? '';
        $this->role = $data['role'] ?? 'utilisateur';
        $this->actif = isset($data['actif']) ? (int)$data['actif'] : 1;
    }

    public static function depuisLigne($row) {
        return new self($row);
    }

    public function nomComplet() {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function estAdmin() {
        return $this->role === 'admin';
    }

    public function estEmployeOuAdmin() {
        return in_array($this->role, ['employe', 'admin'], true);
    }

    public function toSessionArray() {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'gsm' => $this->gsm,
            'adresse' => $this->adresse,
            'role' => $this->role,
        ];
    }
}
