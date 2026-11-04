# ECF — Vite & Gourmand

Site web pour un traiteur fictif à Bordeaux (Julie & José) : menus, filtres, commandes, compte client, espace employé, admin.

Projet rendu dans le cadre de l’**ECF Développeur Web et Web Mobile** (Studi).

## Liens

| Quoi | Lien |
|------|------|
| GitHub | https://github.com/Santimrl/ecf-vite-gourmand-Alexandre |
| Site en ligne | https://studi-vitegourmand.alwaysdata.net |
| Trello | https://trello.com/b/LJqwMB2L/board-ecf-vite-gourmand |

## Stack

- Front : HTML5, CSS, Bootstrap 5, JavaScript (`fetch` filtres + validation formulaires)
- Back : PHP 8 + PDO + petites classes metier (`classes/`)
- BDD relationnelle : MySQL
- BDD non relationnelle : **MongoDB** (stats admin) via Docker ; fallback JSON si pas d’extension mongo
- Conteneurisation : **Docker Compose** (`Dockerfile` + `docker-compose.yml`)

## Install avec Docker (recommande pour la reeval)

1. Installer [Docker Desktop](https://www.docker.com/products/docker-desktop/)
2. Dans le dossier du projet :

```bash
docker compose up --build
```

3. Ouvrir http://localhost:8080
4. Seeds (une fois) : `/seed_passwords.php` puis `/seed_avis_demo.php`
5. Admin → Stats : doit afficher **MongoDB**

Services :
- `web` : PHP 8.2 + Apache + extension mongodb
- `db` : MySQL 8 (port hote 3308)
- `mongo` : MongoDB 7 (port 27017)

## Pourquoi Alwaysdata (et pas Fly.io / Heroku / Vercel) ?

L’énoncé cite Fly.io, Heroku, Azure, Vercel **en exemples**. Ce n’est pas une liste obligatoire : il faut une **URL publique qui marche**.

J’ai choisi **Alwaysdata** (offre Free) parce que :

- PHP + **MySQL** sont inclus (même stack qu’en local)
- simple pour un projet étudiant (FTP + phpMyAdmin)
- Heroku n’est plus vraiment gratuit ; Vercel est surtout front/Node

En local / reeval j’utilise aussi **Docker** (PHP + MySQL + Mongo) pour un environnement reproductible.

URL prod : https://studi-vitegourmand.alwaysdata.net

## Install local (USB Webserver — alternative)

1. Allumer `usbwebserver.exe` (MySQL port **3307**, `root` / `usbw`)
2. Importer `sql/schema.sql` puis `sql/seed.sql` dans phpMyAdmin
3. Vérifier `config.php` (hôte local, port 3307)
4. Double-clic `start-serveur.bat` → http://localhost:8081  
   (le PHP du USB est trop vieux : le `.bat` lance PHP 8.2 à part)
5. Mettre `SEED_ENABLED` à `true` dans `config.php`, puis :
   - ouvrir `/seed_passwords.php`
   - optionnel : `/seed_avis_demo.php`
6. Remettre `SEED_ENABLED` à `false`

## Déploiement Alwaysdata (résumé)

1. Compte Free + site PHP, adresse `xxx.alwaysdata.net`, racine `www/`
2. Créer une base MySQL (ex. `studi-vitegourmand_bdd`)
3. Uploader le code dans `www/` (FTP / FileZilla)
4. Adapter `config.php` (APP_URL en **https**, host MySQL Alwaysdata, nom BDD, user, mdp, port **3306**)
5. Dans phpMyAdmin Alwaysdata : importer `sql/schema.sql` **sans** les lignes `CREATE DATABASE` / `USE` (ou utiliser des versions nettoyées), puis le seed menus/horaires
6. Activer `SEED_ENABLED`, lancer les seeds une fois, **remettre à false**
7. Activer le **certificat SSL** sur le site (Alwaysdata → Certificats SSL / onglet SSL du site) pour éviter l’avertissement « non sécurisé / dangereux » dans le navigateur

Fichiers SQL utiles pour Alwaysdata (sans `CREATE DATABASE`) : voir aussi le dossier de rendu local si besoin ; en repo les fichiers `sql/schema.sql` et `sql/seed.sql` restent la référence — sur Alwaysdata, sélectionner d’abord ta base puis retirer les 2 lignes `CREATE DATABASE` / `USE` avant import.

## Comptes démo

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | jose@vite-gourmand.fr | Admin123! |
| Employé | julie@vite-gourmand.fr | Employe123! |
| Client | client@test.fr | User1234! |

Autres clients avis (mdp `User1234!`) : marie.leroy@test.fr, thomas.bernard@test.fr, sophie.roux@test.fr, karim.moreau@test.fr, claire.dupont@test.fr

## Sécurité des scripts seed

`seed_passwords.php` et `seed_avis_demo.php` ne tournent **que** si `SEED_ENABLED` vaut `true` dans `config.php`.  
Sinon → erreur 403. Après la démo / le rendu, laisser à `false` (ou supprimer les fichiers sur le serveur).

## Documents

- `docs/charte_graphique.pdf`
- `docs/manuel_utilisation.pdf`
- `docs/documentation_technique.pdf`
- `docs/gestion_projet.pdf`
- `docs/schemas/` (MCD, use-case, sequence)
- `docs/schemas/mcd-cardinalites.md` (verbes + cardinalites)

## Architecture simple (POO)

- `classes/Utilisateur.php`, `Menu.php`, `Commande.php` : objets metier
- `classes/Repositories.php` : acces donnees qui renvoient des objets
- Vues PHP = affichage ; calculs livraison/prix dans les classes

## Git

- `main` : version stable / livrable
- `develop` : travail en cours
- branches `feature/...` + **Pull Request** vers `develop` / `main`

## How to run (EN)

```bash
docker compose up --build
# open http://localhost:8080
# seed demo accounts via /seed_passwords.php then disable SEED_ENABLED
```

MySQL + Mongo start with Compose. App config uses environment variables (`DB_*`, `MONGO_URI`).

Les mails en local sont aussi écrits dans `data/mails.log`.
