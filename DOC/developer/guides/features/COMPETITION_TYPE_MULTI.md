# Type de compétition MULTI (Multi-Compétition)

## Vue d'ensemble

Le type de compétition **MULTI** permet de créer un classement agrégé basé sur les résultats d'autres compétitions que vous sélectionnez explicitement. Ce type est idéal pour :
- Créer un championnat général basé sur plusieurs compétitions régionales
- Établir un classement multi-étapes (circuit de compétitions)
- Combiner les résultats de plusieurs tournois

## Caractéristiques

- **Pas de matchs** : Une compétition MULTI ne contient pas de matchs, uniquement un classement
- **Sélection explicite** : Vous choisissez précisément quelles compétitions doivent être prises en compte via une interface multi-sélection
- **Calcul automatique** : Le classement est calculé automatiquement en fonction des résultats des compétitions sélectionnées
- **Grille de points personnalisable** : Vous définissez combien de points sont attribués selon le classement dans chaque compétition

## Migration de la base de données

Avant d'utiliser le type MULTI, vous devez exécuter le script de migration SQL :

```bash
# Via Docker (environnement de développement)
docker exec -i kpi_db mysql -u root -proot kpi < SQL/20251117_add_multi_competition_type.sql

# Via phpMyAdmin
# Importer le fichier SQL/20251117_add_multi_competition_type.sql
```

Ce script ajoute deux champs à la table `kp_competition` :
- `points_grid` : Grille de points au format JSON
- `multi_competitions` : Liste des codes de compétitions sources au format JSON array

## Création d'une compétition MULTI

### 1. Créer la compétition

Dans l'interface d'administration (GestionCompetition.php) :
1. Créez une nouvelle compétition
2. Sélectionnez le type **"Multi-Compétition"** dans la liste déroulante
3. Remplissez les informations de base (nom, saison, groupe, etc.)

### 2. Configurer la grille de points

La grille de points peut être configurée directement dans l'interface d'administration :

1. Éditez la compétition MULTI dans GestionCompetition.php
2. Le champ "Grille de points (MULTI)" apparaît automatiquement
3. Saisissez la grille au format JSON (exemple : `{"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0}`)

**Note** : Le champ est en lecture seule pour les utilisateurs avec profil > 2 (pour les profils avec moins de droits).

**Alternative SQL** : Vous pouvez aussi modifier directement dans la base de données :

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

### 3. Sélectionner les compétitions sources

La sélection des compétitions est configurée directement dans l'interface d'administration :

1. Éditez la compétition MULTI dans GestionCompetition.php
2. Le champ "Compétitions sources (MULTI)" apparaît automatiquement
3. Sélectionnez les compétitions que vous souhaitez inclure dans le calcul (multi-sélection)
4. Les compétitions disponibles sont toutes les compétitions de la même saison (hors compétitions MULTI)

**Important** :
- Seules les compétitions explicitement sélectionnées seront prises en compte dans le calcul
- Vous pouvez sélectionner des compétitions de n'importe quel groupe (Code_ref)
- Les compétitions peuvent être de types différents (CHPT, CP)

**Alternative SQL** : Vous pouvez aussi modifier directement dans la base de données :

```sql
UPDATE kp_competition
SET multi_competitions = '["REG1","REG2","REG3"]'
WHERE Code = 'VOTRE_CODE_COMPET'
AND Code_saison = '2025';
```

### 4. Inscrire les équipes

Inscrivez les équipes dans la compétition MULTI comme pour toute autre compétition :
1. Allez dans la gestion des équipes
2. Ajoutez les équipes participantes

**Important** : Les équipes doivent être inscrites dans les compétitions sélectionnées avec le même nom ou code club pour être reconnues.

### 5. Calculer le classement

1. Assurez-vous que toutes les compétitions sélectionnées ont leurs classements calculés et publiés
2. Allez dans GestionClassement.php
3. Sélectionnez la compétition MULTI
4. Cliquez sur "Calculer le classement"

## Fonctionnement du calcul

Le système effectue les étapes suivantes :

1. **Récupération des compétitions** : Utilise la liste des compétitions explicitement sélectionnées dans le champ `multi_competitions` (format JSON array)
   - Exemple : `["REG1","REG2","REG3"]`
   - Seules les compétitions listées sont prises en compte
   - Les compétitions peuvent provenir de n'importe quel groupe (Code_ref)

2. **Pour chaque équipe inscrite** :
   - Recherche l'équipe dans chaque compétition sélectionnée (par Code_club ou Libelle)
   - **Récupère son classement PUBLIÉ** :
     - Pour les compétitions CHPT : utilise `Clt_publi` (classement championnat publié)
     - Pour les compétitions CP : utilise `CltNiveau_publi` (classement niveau publié)
   - Applique la grille de points selon le classement
   - Somme tous les points obtenus

3. **Classement final** :
   - Les équipes sont classées par points décroissants
   - En cas d'égalité, ordre alphabétique du nom d'équipe
   - Le champ `J` (joué) contient le nombre de compétitions où l'équipe a participé
   - Le champ `Pts` contient le total des points

**Important** : Seuls les classements **publiés** sont pris en compte. Assurez-vous de publier les classements des compétitions sélectionnées avant de calculer le classement MULTI.

## Exemple concret

Imaginons 3 compétitions pour la saison 2025 :

**Compétitions disponibles** :
- REG1 (type CHPT) - Tournoi Nord
- REG2 (type CP) - Tournoi Sud
- REG3 (type MULTI) - Classement Régional

**Configuration REG3** :
- **Compétitions sélectionnées** : `["REG1","REG2"]` (les deux tournois)
- **Grille de points** :
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
- `sources/admin/GestionCompetition.php` - Support du type MULTI et gestion des champs
- `sources/smarty/templates/GestionCompetition.tpl` - Interface de configuration MULTI

### Table de base de données
- **Table** : `kp_competition`
- **Nouveaux champs** :
  - `points_grid` (TEXT, NULL) - Grille de points au format JSON
  - `multi_competitions` (TEXT, NULL) - Liste des codes de compétitions sources au format JSON array

### Identification des équipes
Le système identifie une équipe dans une compétition sélectionnée par :
1. Code club exact (`Code_club`)
2. OU nom exact de l'équipe (`Libelle`)

**Important** : Assurez-vous que les noms d'équipes sont cohérents entre les compétitions sélectionnées.

## Améliorations apportées

1. ✅ **Sélection explicite** : Les compétitions sources sont explicitement sélectionnées via une interface multi-sélection
2. ✅ **Interface graphique** : La grille de points et la sélection de compétitions sont configurables dans GestionCompetition.php
3. ✅ **Contrôle d'accès** : Les champs sont en lecture seule pour les profils > 2
4. ✅ **Classements publiés** : Le calcul utilise les classements publiés (Clt_publi / CltNiveau_publi) au lieu des classements de travail
5. ✅ **Visibilité conditionnelle** : Les champs spécifiques MULTI n'apparaissent que si le type de compétition est MULTI
6. ✅ **Flexibilité** : Les compétitions peuvent provenir de groupes différents

## Limitations actuelles

1. **Ordre des compétitions** : Toutes les compétitions sélectionnées sont traitées au même niveau (pas de priorité/pondération)
2. **Équipes non participantes** : Si une équipe n'a pas participé à une compétition sélectionnée, elle ne reçoit aucun point pour cette compétition

## Évolutions futures possibles

- Pondération des compétitions (certaines compétitions comptent plus que d'autres)
- Prise en compte du nombre de compétitions minimum requises
- Export spécifique pour afficher le détail des points par compétition
- Gestion des équipes forfait/absentes
- Validation automatique de la grille de points (format JSON)

## Support

Pour toute question ou problème :
1. Vérifiez que le script SQL a bien été exécuté
2. Vérifiez que la grille de points est bien définie (format JSON valide)
3. Vérifiez que les compétitions sources sont bien sélectionnées (champ multi_competitions)
4. Vérifiez que les compétitions sélectionnées ont leurs classements calculés et publiés
5. Consultez les logs de calcul dans l'historique des actions
