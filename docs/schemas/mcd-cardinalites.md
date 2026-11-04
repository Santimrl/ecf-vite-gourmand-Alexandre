# MCD Vite & Gourmand (cardinalites + verbes)

## Associations

| Verbe | Entite A | Cardinalite | Entite B | Cardinalite |
|-------|----------|-------------|-----------|-------------|
| possede | UTILISATEUR | 1 | COMMANDE | 0,n |
| concerne | MENU | 1 | COMMANDE | 0,n |
| compose | MENU | 0,n | PLAT | 0,n |
| contient | MENU | 1 | MENU_IMAGE | 0,n |
| declare | PLAT | 1 | ALLERGENE | 0,n |
| suit | COMMANDE | 1 | COMMANDE_SUIVI | 1,n |
| porte_sur | COMMANDE | 1 | AVIS | 0,1 |
| redige | UTILISATEUR | 1 | AVIS | 0,n |

## Notes

- Un utilisateur peut passer 0 a n commandes.
- Une commande concerne exactement 1 menu.
- Un menu contient 0 a n plats (table de liaison MENU_PLAT = relation n,n).
- Une commande terminee peut avoir 0 ou 1 avis.
- Les horaires sont une entite de parametrage (pas de lien fort obligatoire avec commande).

Schema SQL : `sql/schema.sql`  
Diagramme image : `docs/schemas/mcd.png`
