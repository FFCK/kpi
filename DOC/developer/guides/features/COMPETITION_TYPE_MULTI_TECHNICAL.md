# Type de Compétition MULTI - Documentation Technique

## Vue d'ensemble technique

Documentation technique pour les développeurs travaillant sur le type de compétition MULTI et l'éditeur de grille de points.

## Architecture

### Flux de données

```
GestionCompetition.php
    ↓ Configuration
    ├─→ points_grid (JSON)
    └─→ multi_competitions (JSON array)
         ↓
GestionClassement.php
    ↓ Calcul
    └─→ CalculClassementMulti()
         ├─→ Récupération classements publiés
         ├─→ Application grille de points
         └─→ Agrégation et tri
              ↓
FeuilleCltMulti.php / PdfCltMulti.php
    ↓ Génération PDF
    └─→ Affichage classement
```

### Éditeur de grille de points

```
GestionCompetition.tpl
    ↓ Bouton "Ouvrir l'éditeur"
    └─→ window.open('GestionGrillePoints.php')
         ↓
GestionGrillePoints.php
    ├─→ Load() : Parse JSON existant
    ├─→ Affiche formulaire dynamique
    └─→ GenerateJson : Génère nouveau JSON
         ↓
GestionGrillePoints.tpl
    └─→ window.opener.document.getElementById('pointsGrid').value
```

## Structure de données

### Table `kp_competition`

```sql
-- Champs ajoutés pour le type MULTI
points_grid TEXT DEFAULT NULL
    COMMENT 'Grille de points pour les compétitions MULTI (format JSON)',

multi_competitions TEXT DEFAULT NULL
    COMMENT 'Liste des codes de compétitions sources pour MULTI (format JSON array)'
```

### Format JSON de la grille de points

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

**Contraintes** :
- Les clés sont des chaînes (strings) même si numériques
- La clé `"default"` est optionnelle (0 si absente)
- Les valeurs doivent être des entiers positifs ou zéro

### Format JSON des compétitions sources

```json
["N1H", "NPOH", "N2H"]
```

**Contraintes** :
- Tableau de codes de compétitions (strings)
- Les codes doivent exister dans la même saison
- Pas de compétitions MULTI dans les sources

## Fichiers impliqués

### Backend PHP

#### Administration
- **`sources/admin/GestionCompetition.php`**
  - Configuration des compétitions MULTI
  - Gestion des champs `points_grid` et `multi_competitions`
  - Validation et stockage

- **`sources/admin/GestionGrillePoints.php`** ⭐ NOUVEAU
  - Éditeur graphique de grille de points
  - Parse JSON existant
  - Génère nouveau JSON
  - Pattern MyPageSecure standard

- **`sources/admin/GestionClassement.php`**
  - Fonction `CalculClassementMulti()`
  - Logique de calcul du classement
  - Récupération des classements publiés
  - Application de la grille de points

#### Génération PDF
- **`sources/admin/FeuilleCltMulti.php`**
  - PDF admin/provisoire
  - Utilise mPDF
  - Support FR/EN

- **`sources/PdfCltMulti.php`**
  - PDF public
  - QR Code inclus
  - Support FR/EN via paramètre ou config

### Templates Smarty

- **`sources/smarty/templates/GestionCompetition.tpl`**
  - Interface de configuration MULTI
  - Champs conditionnels (pointsGridRow, multiCompetitionsRow)
  - Bouton "Ouvrir l'éditeur de grille"
  - Fonction JavaScript `openGridEditor()`

- **`sources/smarty/templates/GestionGrillePoints.tpl`** ⭐ NOUVEAU
  - Interface de l'éditeur de grille
  - Génération dynamique des champs de saisie
  - Validation JavaScript
  - Communication avec fenêtre parente

- **`sources/smarty/templates/GestionClassement.tpl`**
  - Affichage du classement MULTI
  - Liens vers les PDF

- **`sources/smarty/templates/GestionDoc.tpl`**
  - Liste des documents PDF disponibles

### Internationalisation

**`sources/commun/MyLang.ini`**

Traductions disponibles pour l'éditeur de grille :

| Clé | Français | Anglais |
|-----|----------|---------|
| `Editeur_grille_points_MULTI` | Éditeur de grille de points (MULTI) | Points Grid Editor (MULTI) |
| `Configuration_grille` | Configuration de la grille | Grid Configuration |
| `Info_grille_points` | Configurez les points attribués... | Configure the points awarded... |
| `Nombre_positions` | Nombre de positions | Number of positions |
| `Points_par_position` | Points par position | Points per position |
| `Valeur_par_defaut` | Valeur par défaut | Default value |
| `Points_default` | Points par défaut | Default points |
| `Info_points_default` | Points attribués aux positions non spécifiées | Points awarded to unspecified positions |
| `Generer_JSON` | Générer le JSON | Generate JSON |
| `JSON_genere` | JSON généré | Generated JSON |
| `Copier_JSON` | Copier le JSON | Copy JSON |
| `Appliquer_au_formulaire` | Appliquer au formulaire | Apply to form |
| `Ouvrir_editeur_grille` | Ouvrir l'éditeur de grille | Open grid editor |
| `JSON_copie` | JSON copié dans le presse-papiers ! | JSON copied to clipboard! |
| `JSON_applique` | JSON appliqué au formulaire ! | JSON applied to form! |
| `Erreur_application` | Erreur : impossible d'appliquer le JSON au formulaire parent. | Error: unable to apply JSON to parent form. |

Traductions pour les PDF MULTI :

| Clé | Français | Anglais |
|-----|----------|---------|
| `Clt` | Clt | Pos |
| `Equipe` | Équipe | Team |
| `Pts` | Pts | Pts |
| `J` | J | Pld |
| `CLASSEMENT_PROVISOIRE` | CLASSEMENT PROVISOIRE | TEMPORARY RANKING |
| `CLASSEMENT_GENERAL` | CLASSEMENT GENERAL | OVERALL RANKING |

### Base de données

**`SQL/20251117_add_multi_competition_type.sql`**

Script de migration pour ajout du type MULTI :

```sql
-- Ajout des champs pour le type MULTI
ALTER TABLE kp_competition
ADD COLUMN points_grid TEXT DEFAULT NULL
    COMMENT 'Grille de points pour les compétitions MULTI (format JSON)',
ADD COLUMN multi_competitions TEXT DEFAULT NULL
    COMMENT 'Liste des codes de compétitions sources pour MULTI (format JSON array)';

-- Ajout du type MULTI dans la table des types de classement
INSERT INTO kp_type_classement (Code, Libelle, Ordre)
VALUES ('MULTI', 'Multi-Compétition', 6)
ON DUPLICATE KEY UPDATE Libelle = 'Multi-Compétition';
```

## Calcul du classement MULTI

### Algorithme (fonction `CalculClassementMulti()`)

```php
function CalculClassementMulti($codeCompet, $codeSaison) {
    // 1. Récupérer la configuration
    $compet = GetCompetition($codeCompet, $codeSaison);
    $points_grid = json_decode($compet['points_grid'], true);
    $multi_competitions = json_decode($compet['multi_competitions'], true);

    // 2. Récupérer les équipes inscrites
    $equipes = GetEquipesInscrites($codeCompet, $codeSaison);

    // 3. Pour chaque équipe
    foreach ($equipes as $equipe) {
        $totalPoints = 0;
        $nbCompetitions = 0;

        // 4. Pour chaque compétition source
        foreach ($multi_competitions as $sourceCode) {
            $sourceCompet = GetCompetition($sourceCode, $codeSaison);

            // 5. Trouver l'équipe dans la compétition source
            $classement = FindEquipeInCompetition(
                $equipe,
                $sourceCode,
                $codeSaison
            );

            if ($classement) {
                // 6. Récupérer le classement publié
                $position = GetPublishedRanking($classement, $sourceCompet);

                // 7. Appliquer la grille de points
                $points = ApplyPointsGrid($position, $points_grid);

                $totalPoints += $points;
                $nbCompetitions++;
            }
        }

        // 8. Mettre à jour l'équipe dans la compétition MULTI
        UpdateEquipeMulti(
            $equipe,
            $codeCompet,
            $codeSaison,
            $totalPoints * 100,  // Multiplication par 100 pour stockage
            $nbCompetitions
        );
    }

    // 9. Trier par points décroissants
    SortByPoints($codeCompet, $codeSaison);
}
```

### Détails de l'implémentation

**Récupération du classement publié** :
```php
// Pour type CHPT
if ($type == 'CHPT') {
    $position = $classement['Clt_publi'];
}
// Pour type CP
else if ($type == 'CP') {
    $position = $classement['CltNiveau_publi'];
}
```

**Application de la grille de points** :
```php
function ApplyPointsGrid($position, $grid) {
    // Recherche exacte
    if (isset($grid[strval($position)])) {
        return $grid[strval($position)];
    }
    // Valeur par défaut
    return $grid['default'] ?? 0;
}
```

**Identification des équipes** :
```php
// Recherche par Code_club OU Libelle
$sql = "SELECT * FROM kp_competition_equipe
        WHERE Code_compet = ?
        AND Code_saison = ?
        AND (Code_club = ? OR Libelle = ?)";
```

## Bonnes pratiques de développement

### JavaScript dans les templates Smarty

**⚠️ IMPORTANT** : Toujours utiliser des guillemets doubles pour les traductions

```smarty
{* ❌ INCORRECT (apostrophe casse la chaîne) *}
alert('{#Erreur_application#}');

{* ✅ CORRECT *}
alert("{#Erreur_application#}");
```

### JSON encoding en PHP

**Utiliser `stdClass()` pour les objets vides** :

```php
// ❌ INCORRECT (génère [] en JavaScript)
$gridData = [];

// ✅ CORRECT (génère {} en JavaScript)
$gridData = new stdClass();
```

### Modificateurs Smarty

```smarty
{* ✅ CORRECT *}
{$gridData|json_encode}

{* ❌ INCORRECT *}
{$gridData|@json_encode}
```

### Constructeur MyPageSecure

```php
class MaClasseAdmin extends MyPageSecure
{
    var $myBdd;

    function __construct()
    {
        // TOUJOURS appeler parent::__construct avec niveau de profil
        parent::__construct(10);

        $this->myBdd = new MyBdd();

        $this->SetTemplate("Titre", "Menu", false);
        $this->Load();
        $this->DisplayTemplate('NomTemplate');
    }
}
```

Voir aussi : [BEST_PRACTICES_JAVASCRIPT_SMARTY.md](../BEST_PRACTICES_JAVASCRIPT_SMARTY.md)

## Tests et validation

### Tests à effectuer

1. **Création de compétition MULTI**
   - Vérifier que les champs MULTI apparaissent
   - Vérifier que les autres champs sont masqués

2. **Éditeur de grille**
   - Ouvrir l'éditeur depuis une nouvelle compétition (JSON vide)
   - Ouvrir l'éditeur depuis une compétition existante (JSON chargé)
   - Modifier une grille existante
   - Générer et appliquer le JSON
   - Vérifier la fermeture automatique

3. **Sélection des compétitions sources**
   - Multi-sélection fonctionne
   - Sauvegarde correcte en JSON array
   - Chargement correct lors de l'édition

4. **Calcul du classement**
   - Avec 1 compétition source
   - Avec plusieurs compétitions sources
   - Avec équipes présentes dans toutes les sources
   - Avec équipes présentes dans certaines sources seulement
   - Avec équipes absentes de toutes les sources

5. **Génération PDF**
   - PDF admin en français
   - PDF public en français
   - PDF public en anglais (paramètre URL)
   - PDF avec `En_actif = 'O'` en anglais

### Validation des données

**Validation JSON de la grille** :
```php
$grid = json_decode($points_grid, true);
if (!is_array($grid)) {
    throw new Exception("Grille de points invalide");
}
```

**Validation des compétitions sources** :
```php
$sources = json_decode($multi_competitions, true);
if (!is_array($sources) || empty($sources)) {
    throw new Exception("Aucune compétition source sélectionnée");
}
```

## Migration

### Migration depuis version sans MULTI

**Étape 1 : Exécuter le script SQL**
```bash
mysql -u root -p kpi < SQL/20251117_add_multi_competition_type.sql
```

**Étape 2 : Vérifier les modifications**
```sql
-- Vérifier les nouveaux champs
DESCRIBE kp_competition;

-- Vérifier le nouveau type
SELECT * FROM kp_type_classement WHERE Code = 'MULTI';
```

**Étape 3 : Tester**
- Créer une compétition MULTI test
- Configurer la grille et les sources
- Calculer le classement
- Générer les PDF

### Compatibilité

**Versions PHP** : 8.0+
**Versions MySQL** : 5.7+
**Dépendances** :
- mPDF v8.2+
- Smarty v4+
- MyBdd.php (accès base de données)
- MyTools.php (fonctions utilitaires)

## Dépannage développeur

### Le JSON n'est pas sauvegardé

**Cause** : Purification HTML enlève le JSON
**Solution** : Récupérer directement depuis `$_POST` sans purification

```php
// ✅ CORRECT
$pointsGrid = isset($_POST['pointsGrid']) ? $_POST['pointsGrid'] : '';
```

### L'éditeur ne se charge pas

**Cause** : Cache Smarty
**Solution** : Vider le cache
```bash
rm -rf sources/smarty/templates_c/*
```

### Les traductions ne fonctionnent pas

**Cause** : MyLang_processed.ini non régénéré
**Solution** :
```bash
rm sources/commun/MyLang_processed.ini
# Régénération automatique au prochain chargement
```

### Erreur "gridData is not an object"

**Cause** : json_encode génère `[]` au lieu de `{}`
**Solution** : Utiliser `new stdClass()` au lieu de `[]`

## Références

### Documentation connexe
- [DOC/user/MULTI_COMPETITION_TYPE.md](../../user/MULTI_COMPETITION_TYPE.md) - Documentation utilisateur
- [BEST_PRACTICES_JAVASCRIPT_SMARTY.md](../BEST_PRACTICES_JAVASCRIPT_SMARTY.md) - Bonnes pratiques JS/Smarty

### Commits importants
- Commit initial du type MULTI
- Ajout de l'éditeur de grille de points
- Corrections JavaScript (apostrophes)
- Documentation fusionnée

---

**Version** : 2.0
**Date** : Décembre 2024
**Mainteneur** : Équipe de développement KPI
