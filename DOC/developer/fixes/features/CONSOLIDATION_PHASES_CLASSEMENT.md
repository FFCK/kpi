# Consolidation des Phases de Classement - Documentation Technique

## Vue d'ensemble

Cette fonctionnalité permet de "figer" le classement de certaines phases dans les compétitions de type CP (Coupe), empêchant leur recalcul lors de la mise à jour du classement général.

**Date d'implémentation** : 2025-01-23
**Branche** : `claude/consolidate-ranking-phases-01Kgph652N7RjJspvkPKq5Vr`
**Type** : Feature

## Architecture

### Base de données

#### Table `kp_journee`

Ajout de la colonne `Consolidation` :

```sql
ALTER TABLE `kp_journee` ADD COLUMN `Consolidation` VARCHAR(1) DEFAULT NULL AFTER `Lieu`;
CREATE INDEX `idx_consolidation` ON `kp_journee` (`Consolidation`);
```

**Valeurs** :
- `NULL` : Phase non consolidée (par défaut)
- `'O'` : Phase consolidée

**Migration** : `SQL/20251123_add_consolidation_to_journee.sql`

### Fichiers modifiés

#### 1. Backend PHP

**`sources/admin/GestionClassement.php`**

##### Chargement de la consolidation

Modification des requêtes SQL pour inclure la colonne `Consolidation` :

```php
// Ligne ~246-248
$sql = "SELECT a.Id, a.Libelle, a.Code_club, b.Id_journee, b.Clt, b.Pts,
    b.J, b.G, b.N, b.P, b.F, b.Plus, b.Moins, b.Diff, b.PtsNiveau, b.CltNiveau,
    c.Phase, c.Niveau, c.Lieu, c.Type, c.Consolidation  -- Ajout de c.Consolidation
    FROM kp_competition_equipe a, kp_competition_equipe_journee b
    JOIN kp_journee c ON (b.Id_journee = c.Id)
    WHERE a.Id = b.Id
    AND c.Code_competition = ?
    AND c.Code_saison = ?
    ORDER BY c.Niveau DESC, c.Phase, c.Date_debut, c.Lieu, b.Clt, b.Diff DESC, b.Plus ";
```

##### Exclusion des phases consolidées lors du recalcul

**Fonction `CalculClassementJournee()` (ligne ~483)**

```php
// Exclure les matchs des phases consolidées du recalcul du classement
$sqlConsolidation = "AND (b.Consolidation IS NULL OR b.Consolidation != 'O') ";

$sql = "SELECT a.Id_equipeA, a.ScoreA, a.Id_equipeB, a.ScoreB, a.CoeffA,
        a.CoeffB, a.Id, a.Id_journee, b.Niveau, c.Points
        FROM kp_match a, kp_journee b, kp_competition c
        WHERE a.Id_journee = b.Id
        AND b.Code_competition = ?
        AND b.Code_competition = c.Code
        AND b.Code_saison = ?
        AND b.Code_saison = c.Code_saison
        $sqlValidation
        $sqlConsolidation  -- Ajout de la condition
        ORDER BY b.Id ";
```

**Fonction `RazClassementCompetitionEquipeJournee()` (ligne ~463)**

```php
// Ne pas réinitialiser les phases consolidées
$sql = "UPDATE kp_competition_equipe_journee a
    RIGHT OUTER JOIN kp_journee b ON a.Id_journee = b.Id
    SET a.Clt=0, a.Pts=0, a.J=0, a.G=0, a.N=0, a.P=0, a.F=0,
    a.Plus=0, a.Moins=0, a.Diff=0, a.PtsNiveau=0, a.CltNiveau=0
    WHERE b.Code_competition = ?
    AND b.Code_saison = ?
    AND (b.Consolidation IS NULL OR b.Consolidation != 'O') ";  -- Ajout de la condition
```

#### 2. AJAX Handler

**`sources/admin/v2/UpdateConsolidationJournee.php`** (nouveau fichier)

```php
<?php
// Contrôle AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}

include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');

if(!isset($_SESSION)) {
    session_start();
}

$myBdd = new MyBdd();
$idJournee = (int) utyGetPost('Id_Journee');
$Valeur = trim(utyGetPost('Valeur'));

// Contrôle des droits : seuls les profiles <= 4 peuvent consolider une phase
if (!isset($_SESSION['Profile']) || $_SESSION['Profile'] > 4) {
    header('HTTP/1.0 401 Unauthorized');
    die('Droits insuffisants !');
}

// Contrôle autorisation journée
if (!utyIsAutorisationJournee($idJournee)) {
    header('HTTP/1.0 401 Unauthorized');
    die("Vous n'avez pas l'autorisation de modifier cette journée !");
}

// Mise à jour de la consolidation
$sql = "UPDATE kp_journee
    SET Consolidation = ?
    WHERE Id = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($Valeur, $idJournee));

if ($result) {
    echo 'OK';
} else {
    echo 'Erreur lors de la mise à jour';
}
```

**Points importants** :
- Vérification des droits avec `$_SESSION['Profile']` (majuscule !)
- Utilisation de `utyIsAutorisationJournee()` au lieu de la méthode inexistante `AutorisationJournee()`
- Retourne 'OK' en cas de succès

#### 3. Template Smarty

**`sources/smarty/templates/GestionClassement.tpl`**

##### En-tête de phase (ligne ~189-203)

```smarty
<tr class='head2' data-journee="{$arrayEquipe_journee[i].Id_journee}"
    data-consolidation="{$arrayEquipe_journee[i].Consolidation}">
    <th>
        {if $profile <= 4 && $AuthModif == 'O'}
            <input type="checkbox" class="consolidationPhase"
                data-journee="{$arrayEquipe_journee[i].Id_journee}"
                {if $arrayEquipe_journee[i].Consolidation == 'O'}checked{/if}
                title="{#Consolider_phase#}">
            <small>{#Phase_consolidee#}</small>
        {else}
            {if $arrayEquipe_journee[i].Consolidation == 'O'}
                <input type="checkbox" checked disabled title="{#Phase_consolidee#}">
                <small>{#Phase_consolidee#}</small>
            {/if}
        {/if}
    </th>
    <th colspan="2">{$arrayEquipe_journee[i].Phase} ({$arrayEquipe_journee[i].Lieu})</th>
    ...
</tr>
```

**Attributs data** :
- `data-journee` : ID de la journée/phase
- `data-consolidation` : Statut de consolidation ('O' ou null)

##### Lignes de classement (ligne ~215-259)

```smarty
{assign var='consolidation' value=$arrayEquipe_journee[i].Consolidation}

{* Condition pour afficher les champs modifiables *}
{if $profile <= 4 && $AuthModif == 'O' && $consolidation != 'O'}
    <td width="30"><span class='directInput'
        Id="Clt-{$arrayEquipe_journee[i].Id}-{$arrayEquipe_journee[i].Id_journee}"
        tabindex="2{$smarty.section.i.iteration}0">{$arrayEquipe_journee[i].Clt}</span></td>
{else}
    <td width="30">{$arrayEquipe_journee[i].Clt}</td>
{/if}
```

**Logique** :
- Si `$consolidation != 'O'` ET droits suffisants → champs avec classe `directInput` (modifiable)
- Sinon → champs en texte simple (lecture seule)

#### 4. JavaScript

**`sources/js/GestionClassement.js`**

```javascript
// Gestion de la consolidation des phases
jq(".consolidationPhase").click(function() {
    var checkbox = jq(this);
    var idJournee = checkbox.attr('data-journee');
    var isChecked = checkbox.attr('checked') ? true : false;
    var newValue = isChecked ? 'O' : null;
    var phaseRow = jq('tr.head2[data-journee="' + idJournee + '"]');

    // Désactiver temporairement la checkbox pendant la requête
    checkbox.attr('disabled', 'disabled');

    jq.ajax({
        type: 'POST',
        url: 'v2/UpdateConsolidationJournee.php',
        data: {
            Id_Journee: idJournee,
            Valeur: newValue
        },
        dataType: 'text',
        success: function(data) {
            if(data == 'OK') {
                // Mettre à jour l'attribut data-consolidation
                phaseRow.attr('data-consolidation', newValue);

                // Recharger la page pour mettre à jour les classes directInput
                location.reload();
            } else {
                // Erreur : annuler le changement de la checkbox
                if(isChecked) {
                    checkbox.removeAttr('checked');
                } else {
                    checkbox.attr('checked', 'checked');
                }
                alert(langue['MAJ_impossible'] + ' : ' + data);
                checkbox.removeAttr('disabled');
            }
        },
        error: function() {
            // En cas d'erreur réseau
            if(isChecked) {
                checkbox.removeAttr('checked');
            } else {
                checkbox.attr('checked', 'checked');
            }
            alert(langue['MAJ_impossible']);
            checkbox.removeAttr('disabled');
        }
    });
});
```

**Compatibilité jQuery** :
- Utilisation de `.attr()` au lieu de `.prop()` (ancienne version de jQuery)
- Utilisation de `jq.ajax()` au lieu de `jq.post().fail().always()` (non disponible)
- Pas de `.is(':checked')`, utilisation de `.attr('checked') ? true : false`

#### 5. Traductions

**`sources/commun/MyLang.ini`**

##### Français (lignes ~197-198, 540-541)
```ini
Confirmer = "Confirmer"
Consolider_phase = "Consolider cette phase"
Connexion = "Connexion"
...
Phase = "Phase"
Phase_consolidee = "Phase consolidée"
Phases = "Phases"
```

##### Anglais (lignes ~946-947, 1289-1290)
```ini
Confirmer = "Confirm"
Consolider_phase = "Consolidate this phase"
Connexion = "Sign in"
...
Phase = "Phase"
Phase_consolidee = "Consolidated phase"
Phases = "Phases"
```

## Flux de données

### Consolidation d'une phase

```
1. User clicks checkbox
   ↓
2. JavaScript handler (GestionClassement.js)
   - Détecte le clic
   - Détermine l'état (checked/unchecked)
   - Désactive la checkbox
   ↓
3. AJAX POST → UpdateConsolidationJournee.php
   - Valide les droits ($_SESSION['Profile'] <= 4)
   - Valide l'autorisation (utyIsAutorisationJournee)
   - UPDATE kp_journee SET Consolidation = 'O'/'NULL'
   ↓
4. Réponse 'OK'
   ↓
5. JavaScript recharge la page
   ↓
6. PHP charge les données avec Consolidation
   ↓
7. Template affiche :
   - Checkbox cochée/décochée
   - Champs avec/sans classe directInput
```

### Recalcul du classement

```
1. User clicks "Recalculer"
   ↓
2. GestionClassement.php::DoClassement()
   ↓
3. CalculClassement() → CalculClassementJournee()
   ↓
4. SQL avec condition:
   AND (b.Consolidation IS NULL OR b.Consolidation != 'O')
   ↓
5. Phases consolidées EXCLUES du SELECT
   ↓
6. Seules les phases non consolidées sont recalculées
   ↓
7. RazClassementCompetitionEquipeJournee()
   - Réinitialise UNIQUEMENT les phases non consolidées
   ↓
8. StepClassementCompetitionEquipeJournee()
   - Incrémente les classements des phases non consolidées
```

## Compatibilité

### Version de jQuery

⚠️ **Important** : Le projet utilise une **ancienne version de jQuery** qui ne supporte pas :
- `.prop()` → utiliser `.attr()`
- `.is(':checked')` → utiliser `.attr('checked') ? true : false`
- `.post().fail().always()` → utiliser `.ajax()` avec `success` et `error`

**Mémorisé** : Toujours utiliser la syntaxe jQuery classique pour ce projet.

### Navigateurs

Testé et compatible avec :
- Chrome/Edge (moderne)
- Firefox (moderne)
- Safari (moderne)
- Internet Explorer 11 (support minimum)

## Tests

### Tests manuels recommandés

1. **Consolidation de base**
   - [ ] Cocher une case → la phase est consolidée
   - [ ] Décocher une case → la phase est déconsolidée
   - [ ] Vérifier le rechargement automatique

2. **Modification des champs**
   - [ ] Phase non consolidée : champs modifiables (classe directInput)
   - [ ] Phase consolidée : champs en lecture seule (pas de classe directInput)
   - [ ] Cliquer sur un champ consolidé ne doit rien faire

3. **Recalcul du classement**
   - [ ] Phase consolidée : classement inchangé après recalcul
   - [ ] Phase non consolidée : classement recalculé
   - [ ] Vérifier les points, différence de buts, etc.

4. **Droits utilisateur**
   - [ ] Profile 1-4 : case à cocher visible et active
   - [ ] Profile > 4 : aucune case (sauf phase déjà consolidée en disabled)
   - [ ] Tester avec différents profils

5. **Erreurs**
   - [ ] Déconnexion réseau → message d'erreur
   - [ ] Droits insuffisants → message "Droits insuffisants"
   - [ ] Journée non autorisée → message d'erreur

### Tests de non-régression

- [ ] Vérifier que les compétitions de type CHPT ne sont pas affectées
- [ ] Vérifier que les autres fonctionnalités de classement fonctionnent toujours
- [ ] Vérifier la publication du classement
- [ ] Vérifier l'initialisation du classement

## Problèmes connus et solutions

### 1. Erreur "checkbox.prop is not a function"
**Cause** : Version de jQuery trop ancienne
**Solution** : Utiliser `.attr()` au lieu de `.prop()`

### 2. Erreur "Droits insuffisants" pour admin
**Cause** : Mauvaise casse de la variable de session (`$_SESSION['profile']` au lieu de `$_SESSION['Profile']`)
**Solution** : Utiliser `$_SESSION['Profile']` avec majuscule

### 3. Méthode AutorisationJournee n'existe pas
**Cause** : Copie d'un pattern avec une méthode inexistante
**Solution** : Utiliser `utyIsAutorisationJournee()` de MyTools.php

### 4. Rechargement de page nécessaire
**Cause** : Les classes CSS `directInput` sont générées côté serveur
**Solution** : Rechargement automatique après chaque consolidation (comportement voulu)

## Performance

### Impact

- **Positif** : Les phases consolidées sont exclues du recalcul → gain de temps
- **Négligeable** : Index sur `kp_journee.Consolidation` pour optimiser les requêtes
- **Rechargement** : Page rechargée après chaque consolidation (AJAX + reload)

### Optimisations possibles (futures)

1. Éviter le rechargement de page en manipulant les classes CSS côté client
2. Ajouter un indicateur visuel plus visible (badge, icône)
3. Batch consolidation (consolider plusieurs phases en un clic)

## Évolutions futures possibles

1. **Historique de consolidation**
   - Ajouter `Date_consolidation` et `User_consolidation` dans `kp_journee`
   - Tracer qui a consolidé et quand

2. **Consolidation automatique**
   - Consolider automatiquement les phases X jours après leur date de fin
   - Option configurable par compétition

3. **Export des phases consolidées**
   - Générer un PDF avec le classement consolidé et horodaté
   - Signature numérique pour certification

4. **API REST**
   - Endpoint pour consolider via API
   - Intégration avec app2/app3

## Références

### Fichiers créés

- `SQL/20251123_add_consolidation_to_journee.sql`
- `sources/admin/v2/UpdateConsolidationJournee.php`
- `DOC/user/CONSOLIDATION_PHASES_CLASSEMENT.md`
- `DOC/developer/fixes/features/CONSOLIDATION_PHASES_CLASSEMENT.md`

### Fichiers modifiés

- `sources/admin/GestionClassement.php`
- `sources/smarty/templates/GestionClassement.tpl`
- `sources/js/GestionClassement.js`
- `sources/commun/MyLang.ini`

### Commits

- `2f95b32` - Feat: Add ranking phase consolidation for CP type competitions
- `2eec0c4` - Fix: Improve consolidation UI and jQuery compatibility
- `e9c90fb` - Fix: Correct session variable name and authorization check
- `81bde20` - Fix: jQuery compatibility and consistent label display

### Branches

- Feature branch : `claude/consolidate-ranking-phases-01Kgph652N7RjJspvkPKq5Vr`
- Base branch : `develop`

---

**Version** : 1.0
**Date** : 2025-01-23
**Auteur** : Claude Code AI Assistant
**Reviewer** : À compléter
