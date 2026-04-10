# Spec : Rendre les pages PDF legacy autonomes pour admin2

## Contexte

App4 (admin2) ouvre les PDFs legacy via des liens HTML `target="_blank"` avec des paramètres GET.
Les pages PHP legacy lisent principalement `$_SESSION` pour le contexte (compétition, saison).
Quand elles sont appelées depuis admin2, il n'y a pas de session PHP partagée → les PDFs ne fonctionnent pas.

## Problèmes identifiés

### 1. Mauvais noms de paramètres GET dans app4

App4 envoie `Code_saison` et `Code_competition` dans les URLs.
Les pages PHP qui ont un support GET attendent `S` et `Compet`.

**Fichier** : `sources/app4/pages/documents/index.vue` (fonction `pdfUrl()`)

### 2. La majorité des pages PDF n'acceptent aucun paramètre GET

Elles lisent uniquement `$_SESSION['codeCompet']` et `$myBdd->GetActiveSaison()`.

### 3. Bug lstJournee dans FeuilleListeMatchs

`lstJournee` vaut `0` par défaut → `explode(',', '0')` → `['0']` → `WHERE Id_journee IN (0)` → aucun résultat.
Quand `Compet` est passé en GET, il faut vider `arrayJournees` pour forcer la requête par compétition.

### 4. Debug var_dump/die dans FeuilleListeMatchs.php

Lignes 59-61 : `var_dump()` + `die()` bloquent la génération du PDF.

### 5. Lien cassé tableau_tbs.php

Le fichier s'appelle `tableau_openspout.php`, pas `tableau_tbs.php`.

## Modifications requises

### A. App4 — Corriger les noms de paramètres

**Fichier** : `sources/app4/pages/documents/index.vue`

Dans `pdfUrl()` (lignes 154-162) :
- `Code_saison` → `S`
- `Code_competition` → `Compet`

Dans le lien tableur (ligne 374) :
- `tableau_tbs.php` → `tableau_openspout.php`

### B. PHP — Ajouter le support GET `S` + `Compet`

Pattern à appliquer dans chaque fichier, après l'initialisation session :

```php
$codeCompet = utyGetSession('codeCompet', '');
$codeCompet = utyGetGet('Compet', $codeCompet);    // ← AJOUTER
$codeSaison = $myBdd->GetActiveSaison();
$codeSaison = utyGetGet('S', $codeSaison);          // ← AJOUTER
```

#### Fichiers sans aucun support GET (ajouter `Compet` + `S`) :

| # | Fichier | Ligne codeCompet | Ligne codeSaison |
|---|---------|-----------------|-----------------|
| 1 | `sources/admin/FeuilleGroups.php` | 17 | 19 |
| 2 | `sources/admin/FeuillePresence.php` | 33 | 34 |
| 3 | `sources/admin/FeuillePresenceEN.php` | 18 | 19 |
| 4 | `sources/admin/FeuillePresenceVisa.php` | 18 | 19 |
| 5 | `sources/admin/FeuillePresencePhoto.php` | 18 | 19 |
| 6 | `sources/admin/FeuilleCltChpt.php` | 18 | 20 |
| 7 | `sources/admin/FeuilleCltChptDetail.php` | 16 | 17 |
| 8 | `sources/admin/FeuilleCltNiveauJournee.php` | 17 | 18 |
| 9 | `sources/admin/FeuilleCltMulti.php` | 18 | 20 |
| 10 | `sources/admin/FeuillePresenceCat.php` | 19 | 20 |
| 11 | `sources/admin/FeuillePresenceU21.php` | 19 | 20 |
| 12 | `sources/admin/FeuilleCards.php` | 17 | 19 |
| 13 | `sources/admin/FeuilleMatchMulti.php` | — | — |
| 14 | `sources/admin/tableau_openspout.php` | — (session listMatch) | — |

#### Fichiers avec support GET partiel (harmoniser `codeCompet` → ajouter `Compet`) :

| # | Fichier | GET existant | Ajouter |
|---|---------|-------------|---------|
| 15 | `sources/admin/FeuilleCltNiveau.php` | `codeCompet` + `S` | `Compet` |
| 16 | `sources/admin/FeuilleCltNiveauDetail.php` | `codeCompet` + `S` | `Compet` |
| 17 | `sources/admin/FeuilleCltNiveauPhase.php` | `codeCompet` + `S` | `Compet` |
| 18 | `sources/admin/FeuilleCltNiveauNiveau.php` | `S` seulement | `Compet` |

Pour ces fichiers, ajouter `Compet` comme alias de `codeCompet` :
```php
$codeCompet = utyGetGet('codeCompet', $codeCompet);
$codeCompet = utyGetGet('Compet', $codeCompet);  // ← AJOUTER (alias pour admin2)
```

#### Fichiers déjà ok (FeuilleListeMatchs) — corriger le bug :

| # | Fichier | Correction |
|---|---------|-----------|
| 19 | `sources/admin/FeuilleListeMatchs.php` | Supprimer var_dump/die (L59-61) + vider arrayJournees quand Compet passé en GET |
| 20 | `sources/admin/FeuilleListeMatchsEN.php` | Vider arrayJournees quand Compet passé en GET |

Correction pour les deux (après ligne 56) :
```php
if ($laCompet != 0 && $laCompet != '*' && $laCompet != '') {
    $idEvenement = -1;
    $arrayJournees = [];  // ← AJOUTER : forcer requête par compétition
}
```

### C. Cas spécial : tableau_openspout.php

Ce fichier utilise `$_SESSION['listMatch']` pour la liste des matchs. App4 passe déjà `listMatch` en paramètre à `FeuilleMatchMulti.php`. Il faut ajouter le même support GET :

```php
$listMatch = utyGetSession('listMatch', '');
$listMatch = utyGetGet('listMatch', $listMatch);  // ← AJOUTER
```

Plus les paramètres S et Compet pour cohérence.

### D. Cas spécial : fichiers avec test POOL

Les fichiers `FeuillePresenceVisa.php`, `FeuillePresencePhoto.php`, `FeuillePresenceEN.php`, `FeuillePresencePhotoRef.php`, `FeuillePresencePhoto2.php` ont :
```php
$codeSaison = $codeCompet === 'POOL' ? 1000 : $myBdd->GetActiveSaison();
```

Le `utyGetGet('Compet')` doit être ajouté AVANT cette ligne pour que le test POOL fonctionne aussi en GET.

## Rétrocompatibilité

- Le legacy admin continue d'utiliser `$_SESSION` → aucun changement de comportement
- Les `utyGetGet()` ajoutés ne font que surcharger la valeur session si un paramètre GET est présent
- Les liens existants avec `codeCompet` continuent de fonctionner (alias ajouté, pas remplacé)

## Tests

1. **Depuis admin2** : Documents → saison 2024, compétition N1H → chaque lien PDF doit fonctionner
2. **Depuis legacy admin** : GestionJournee → cliquer sur les mêmes PDFs → toujours fonctionnel
3. **URL directe** : `https://kpi.localhost/admin/FeuilleGroups.php?S=2024&Compet=N1H` → PDF correct
