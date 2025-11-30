# Type de Compétition MULTI - Documentation Utilisateur

## Vue d'ensemble

Le type de compétition **MULTI** (Multi-Compétition) est un type de classement qui agrège les résultats de plusieurs compétitions sources pour créer un classement global unique. Ce type est particulièrement utile pour :

- **Circuits de compétitions** : Créer un classement général sur plusieurs étapes d'un circuit
- **Tournois multi-événements** : Agréger les résultats de plusieurs tournois pour établir un classement de saison
- **Championnats combinés** : Combiner les résultats de différentes compétitions pour un titre global

## Caractéristiques principales

### 1. Sélection des compétitions sources

Le type MULTI permet de sélectionner explicitement les compétitions dont les résultats seront agrégés :

- **Multi-sélection** : Interface multi-select permettant de choisir plusieurs compétitions
- **Compétitions organisées par section** : Les compétitions sources sont groupées par section pour faciliter la navigation
- **Flexibilité** : Possibilité de combiner des compétitions de différentes sections/groupes

### 2. Grille de points personnalisable

Une grille de points au format JSON définit les points attribués selon le classement dans chaque compétition source :

```json
{
  "1": 10,
  "2": 6,
  "3": 4,
  "4": 2,
  "default": 0
}
```

- **Format** : JSON avec clés numériques pour chaque position
- **Clé "default"** : Points attribués pour toutes les positions non spécifiées
- **Exemple** : Le 1er reçoit 10 points, le 2ème 6 points, etc.

### 3. Calcul du classement

Le classement MULTI est calculé en :

1. **Récupération des classements publiés** : Utilise `Clt_publi` (CHPT) ou `CltNiveau_publi` (CP) de chaque compétition source
2. **Attribution des points** : Applique la grille de points selon la position de chaque équipe dans chaque compétition
3. **Agrégation** : Somme les points de toutes les compétitions pour obtenir le total
4. **Multiplication par 100** : Le total est multiplié par 100 pour stockage (format standard KPI)
5. **Tri** : Classement par points décroissants

### 4. Affichage simplifié

Le classement MULTI affiche uniquement les colonnes essentielles :

- **Clt** : Position au classement
- **Équipe** : Nom de l'équipe
- **Pts** : Points totaux (affichés divisés par 100)
- **J** : Nombre de compétitions auxquelles l'équipe a participé

Les colonnes détaillées (G, N, P, F, +, -, Diff) ne sont pas affichées car non pertinentes pour ce type de classement.

## Configuration d'une compétition MULTI

### Étape 1 : Création de la compétition

1. Accéder à **GestionCompetition.php**
2. Créer une nouvelle compétition
3. Sélectionner **Type de classement** : `MULTI` (Multi-Compétition)

### Étape 2 : Configuration des compétitions sources

Dans le formulaire de compétition MULTI :

1. **Compétitions sources** :
   - Utiliser la liste déroulante multi-select
   - Maintenir `Ctrl` (Windows) ou `Cmd` (Mac) pour sélectionner plusieurs compétitions
   - Les compétitions sont groupées par section pour faciliter la navigation

2. **Grille de points** :
   - Saisir la grille au format JSON
   - Exemple : `{"1":10,"2":6,"3":4,"4":2,"default":0}`
   - Tester la validité du JSON avant d'enregistrer

### Étape 3 : Calcul du classement

1. Accéder à **GestionClassement.php**
2. Sélectionner la compétition MULTI
3. Cliquer sur **Calculer le classement**
4. Le système :
   - Récupère les classements publiés de chaque compétition source
   - Applique la grille de points
   - Calcule le total pour chaque équipe
   - Génère le classement final

### Étape 4 : Publication

1. Vérifier le classement provisoire
2. Cliquer sur **Publier le classement**
3. Le classement devient visible publiquement

## Génération des PDF

Les compétitions MULTI disposent de générateurs PDF dédiés :

### PDF Admin (Provisoire)

- **Fichier** : `FeuilleCltMulti.php`
- **Accès** : Via GestionClassement.php, section "Admin"
- **Contenu** : Classement provisoire avec colonnes Clt, Équipe, Pts, J
- **Langue** : Français par défaut, anglais si `En_actif = 'O'`

### PDF Public

- **Fichier** : `PdfCltMulti.php`
- **Accès** : Via GestionClassement.php, section "Public" (après publication)
- **Contenu** : Classement publié avec colonnes Clt, Équipe, Pts, J
- **QR Code** : Inclus pour accès direct depuis l'extérieur
- **Langue** : Français par défaut, anglais si `En_actif = 'O'` ou paramètre `?lang=en`

## Internationalisation

Les PDF MULTI supportent l'anglais automatiquement :

### Traductions disponibles

| Français | Anglais | Clé MyLang.ini |
|----------|---------|----------------|
| Clt | Pos | `Clt` |
| Équipe | Team | `Equipe` |
| Pts | Pts | `Pts` |
| J | Pld | `J` |
| CLASSEMENT PROVISOIRE | TEMPORARY RANKING | `CLASSEMENT_PROVISOIRE` |
| CLASSEMENT GENERAL | OVERALL RANKING | `CLASSEMENT_GENERAL` |

### Activation de l'anglais

**Méthode 1 : Configuration de la compétition**
- Dans GestionCompetition.php, cocher `En_actif = 'O'`
- Tous les PDF de cette compétition seront en anglais

**Méthode 2 : Paramètre URL (PDF public uniquement)**
- Ajouter `?lang=en` à l'URL du PDF
- Exemple : `PdfCltMulti.php?lang=en`

## Validation et transfert d'équipes

### Validation Numero unique

Lors de l'affectation/promotion/relégation d'équipes :

- Le système vérifie que le `Numero` de l'équipe n'existe pas déjà dans la compétition de destination
- Si un doublon est détecté, l'équipe n'est **pas transférée**
- Garantit l'intégrité des données et évite les conflits

## Structure de données

### Table `kp_competition`

Nouveaux champs pour le type MULTI :

```sql
-- Grille de points au format JSON
points_grid TEXT DEFAULT NULL COMMENT 'Grille de points pour les compétitions MULTI (format JSON)'

-- Liste des codes de compétitions sources
multi_competitions TEXT DEFAULT NULL COMMENT 'Liste des codes de compétitions sources pour MULTI (format JSON array)'
```

### Exemple de données

```sql
-- Compétition MULTI
Code_typeclt = 'MULTI'
points_grid = '{"1":10,"2":6,"3":4,"4":2,"default":0}'
multi_competitions = '["N1H","NPOH","N2H"]'
```

## Cas d'usage

### Exemple 1 : Circuit de 3 tournois

**Configuration** :
- Compétitions sources : Tournoi 1, Tournoi 2, Tournoi 3
- Grille de points : `{"1":25,"2":18,"3":15,"4":12,"5":10,"6":8,"7":6,"8":4,"9":2,"10":1,"default":0}`

**Résultat** :
- Une équipe 1ère dans 2 tournois et 3ème dans le dernier obtient : (25×2) + 15 = 65 points

### Exemple 2 : Championnat combiné N1/N2

**Configuration** :
- Compétitions sources : N1 Hommes, N1 Dames, N2 Hommes, N2 Dames
- Grille de points : `{"1":10,"2":6,"3":4,"4":2,"default":0}`

**Résultat** :
- Classement global combinant les 4 compétitions

## Limitations

1. **Pas de détails de matchs** : Le type MULTI n'affiche pas G, N, P, F, +, -, Diff
2. **Classements publiés uniquement** : Utilise les classements publiés (`Clt_publi`/`CltNiveau_publi`)
3. **Format JSON strict** : La grille de points doit être un JSON valide
4. **Pas de gestion de phases** : Contrairement au type CP, le MULTI ne gère pas les phases/journées

## Fichiers impliqués

### Backend PHP
- `sources/admin/GestionCompetition.php` : Configuration des compétitions MULTI
- `sources/admin/GestionClassement.php` : Calcul du classement MULTI (fonction `CalculClassementMulti()`)
- `sources/admin/FeuilleCltMulti.php` : Générateur PDF admin
- `sources/PdfCltMulti.php` : Générateur PDF public

### Templates Smarty
- `sources/smarty/templates/GestionCompetition.tpl` : Interface de configuration
- `sources/smarty/templates/GestionClassement.tpl` : Affichage du classement et liens PDF
- `sources/smarty/templates/GestionDoc.tpl` : Liste des documents PDF disponibles

### Internationalisation
- `sources/commun/MyLang.ini` : Traductions FR/EN/CN

### Base de données
- `SQL/20251117_add_multi_competition_type.sql` : Migration pour ajout du type MULTI

## Support et dépannage

### Problème : Le classement ne se calcule pas

**Causes possibles** :
1. Les compétitions sources n'ont pas de classement publié
2. La grille de points est invalide (JSON incorrect)
3. Aucune compétition source n'est sélectionnée

**Solution** :
1. Vérifier que toutes les compétitions sources ont un classement publié
2. Valider le JSON de la grille de points
3. S'assurer qu'au moins une compétition est sélectionnée

### Problème : Les points sont incorrects

**Causes possibles** :
1. La grille de points ne correspond pas aux attentes
2. Une compétition source a été modifiée après le calcul

**Solution** :
1. Vérifier la grille de points configurée
2. Recalculer le classement MULTI après toute modification des compétitions sources

### Problème : Le PDF ne s'affiche pas en anglais

**Causes possibles** :
1. `En_actif` n'est pas défini sur 'O' dans la compétition
2. Le paramètre `?lang=en` est manquant (PDF public)

**Solution** :
1. Vérifier la configuration `En_actif` dans GestionCompetition.php
2. Ajouter `?lang=en` à l'URL du PDF public

## Migration depuis les versions précédentes

Si vous avez créé des compétitions MULTI avant cette documentation :

1. Vérifier que la migration SQL a été exécutée : `20251117_add_multi_competition_type.sql`
2. Vérifier que les champs `points_grid` et `multi_competitions` existent dans la table `kp_competition`
3. Reconfigurer les compétitions MULTI existantes avec la nouvelle interface

---

**Version** : 1.0
**Date** : 23 novembre 2025
**Auteur** : Documentation générée pour KPI kayak-polo.info
