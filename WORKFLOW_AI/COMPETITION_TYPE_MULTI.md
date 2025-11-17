# Type de compétition MULTI (Multi-Compétition)

## Vue d'ensemble

Le type de compétition **MULTI** permet de créer un classement agrégé basé sur les résultats d'autres compétitions d'un même groupe. Ce type est idéal pour :
- Créer un championnat général basé sur plusieurs compétitions régionales
- Établir un classement multi-étapes (circuit de compétitions)
- Combiner les résultats de plusieurs tournois

## Caractéristiques

- **Pas de matchs** : Une compétition MULTI ne contient pas de matchs, uniquement un classement
- **Calcul automatique** : Le classement est calculé automatiquement en fonction des résultats des autres compétitions du même groupe
- **Grille de points personnalisable** : Vous définissez combien de points sont attribués selon le classement dans chaque compétition
- **Exclusion des finales** : Les compétitions avec tour "Unique/Finale" (Code_tour = 10) sont automatiquement exclues du calcul

## Migration de la base de données

Avant d'utiliser le type MULTI, vous devez exécuter le script de migration SQL :

```bash
# Via Docker (environnement de développement)
docker exec -i kpi_db mysql -u root -proot kpi < SQL/20251117_add_multi_competition_type.sql

# Via phpMyAdmin
# Importer le fichier SQL/20251117_add_multi_competition_type.sql
```

Ce script ajoute le champ `points_grid` à la table `kp_competition`.

## Création d'une compétition MULTI

### 1. Créer la compétition

Dans l'interface d'administration (GestionCompetition.php) :
1. Créez une nouvelle compétition
2. Sélectionnez le type **"Multi-Compétition"** dans la liste déroulante
3. Définissez le groupe (Code_ref) - ce groupe doit contenir les compétitions dont vous voulez agréger les résultats
4. Remplissez les autres informations (nom, saison, etc.)

### 2. Configurer la grille de points

La grille de points doit être définie directement dans la base de données (pour le moment) :

```sql
UPDATE kp_competition
SET points_grid = '{"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0}'
WHERE Code = 'VOTRE_CODE_COMPET'
AND Code_saison = '2025';
```

**Format de la grille** (JSON) :
- `"1"`: points pour le 1er classé
- `"2"`: points pour le 2ème classé
- etc.
- `"default"`: points pour les classements non spécifiés (optionnel, 0 par défaut)

**Exemple** :
```json
{
  "1": 10,
  "2": 6,
  "3": 4,
  "4": 3,
  "5": 2,
  "6": 1,
  "default": 0
}
```

### 3. Inscrire les équipes

Inscrivez les équipes dans la compétition MULTI comme pour toute autre compétition :
1. Allez dans la gestion des équipes
2. Ajoutez les équipes participantes

**Important** : Les équipes doivent être inscrites dans les compétitions précédentes du même groupe avec le même nom ou code club pour être reconnues.

### 4. Calculer le classement

1. Assurez-vous que toutes les compétitions précédentes du même groupe ont leurs classements calculés
2. Allez dans GestionClassement.php
3. Sélectionnez la compétition MULTI
4. Cliquez sur "Calculer le classement"

## Fonctionnement du calcul

Le système effectue les étapes suivantes :

1. **Récupération des compétitions** : Identifie toutes les compétitions du même groupe (Code_ref) de la même saison, en excluant :
   - La compétition MULTI elle-même
   - Les compétitions avec Code_tour = 10 (Unique/Finale)
   - Les autres compétitions de type MULTI

2. **Pour chaque équipe inscrite** :
   - Recherche l'équipe dans chaque compétition précédente (par Code_club ou Libelle)
   - Récupère son classement (Clt) dans cette compétition
   - Applique la grille de points selon le classement
   - Somme tous les points obtenus

3. **Classement final** :
   - Les équipes sont classées par points décroissants
   - En cas d'égalité, ordre alphabétique du nom d'équipe
   - Le champ `J` (joué) contient le nombre de compétitions où l'équipe a participé
   - Le champ `Pts` contient le total des points

## Exemple concret

Imaginons un groupe "Régional Est" avec 3 compétitions :

**Compétitions du groupe** :
- REG1 (type CHPT) - Tournoi Nord
- REG2 (type CP) - Tournoi Sud
- REG3 (type MULTI) - Classement Régional

**Grille de points REG3** :
```json
{"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0}
```

**Résultats** :

Tournoi Nord (REG1) :
1. Équipe A
2. Équipe B
3. Équipe C

Tournoi Sud (REG2) :
1. Équipe B
2. Équipe C
3. Équipe A

**Calcul du classement MULTI (REG3)** :
- Équipe A : 10 (1er Nord) + 4 (3ème Sud) = **14 points** → 2ème
- Équipe B : 6 (2ème Nord) + 10 (1er Sud) = **16 points** → 1er
- Équipe C : 4 (3ème Nord) + 6 (2ème Sud) = **10 points** → 3ème

## Affichage du classement

Le classement MULTI s'affiche comme un classement normal dans :
- L'interface d'administration (GestionClassement.php)
- Les exports PDF/Excel
- Les pages publiques de classements

**Particularité** :
- Le champ `J` (joué) affiche le nombre de compétitions où l'équipe a participé
- Le champ `Pts` affiche le total des points MULTI (pas les points de matchs)
- Les autres champs (G, N, P, F, Plus, Moins, Diff) restent à 0

## Notes techniques

### Fichiers modifiés
- `SQL/20251117_add_multi_competition_type.sql` - Script de migration
- `sources/admin/GestionClassement.php` - Logique de calcul MULTI (fonction `CalculClassementMulti`)
- `sources/admin/GestionClassement.php` - Support du type MULTI dans les combos et fonctions

### Table de base de données
- **Table** : `kp_competition`
- **Nouveau champ** : `points_grid` (TEXT, NULL) - Grille de points au format JSON

### Identification des équipes
Le système identifie une équipe dans une compétition précédente par :
1. Code club exact (`Code_club`)
2. OU nom exact de l'équipe (`Libelle`)

**Important** : Assurez-vous que les noms d'équipes sont cohérents entre les compétitions du groupe.

## Limitations actuelles

1. **Configuration manuelle** : La grille de points doit être configurée via SQL (pas d'interface graphique pour le moment)
2. **Ordre des compétitions** : Toutes les compétitions du groupe sont traitées au même niveau (pas de priorité/pondération)
3. **Équipes non participantes** : Si une équipe n'a pas participé à une compétition du groupe, elle ne reçoit aucun point pour cette compétition

## Évolutions futures possibles

- Interface graphique pour configurer la grille de points
- Pondération des compétitions (certaines compétitions comptent plus que d'autres)
- Prise en compte du nombre de compétitions minimum requises
- Export spécifique pour afficher le détail des points par compétition
- Gestion des équipes forfait/absentes

## Support

Pour toute question ou problème :
1. Vérifiez que le script SQL a bien été exécuté
2. Vérifiez que la grille de points est bien définie (format JSON valide)
3. Vérifiez que les compétitions précédentes ont leurs classements calculés
4. Consultez les logs de calcul dans l'historique des actions
