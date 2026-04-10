# Migration RC : "- CNA -" vers NULL

**Date** : 2026-02-09
**Type** : Migration de données + Adaptation code legacy
**Impact** : Pages RC (Legacy PHP + App4 Nuxt)

---

## Contexte

La valeur `"- CNA -"` (Centre National d'Arbitrage) était utilisée dans la table `kp_rc` pour représenter un Responsable de Compétition au niveau **national sans compétition spécifique**.

Cette valeur littérale posait plusieurs problèmes :
- Sémantiquement incorrecte (CNA n'est pas lié aux RC)
- Difficile à filtrer en SQL
- Incompatible avec les conventions modernes (NULL pour "non applicable")

---

## Solution

Remplacement de `"- CNA -"` par `NULL` dans la colonne `Code_competition` de la table `kp_rc`.

### Étape 1 : Migration SQL

Fichier : [SQL/migrations/2026-02-09_kp_rc_allow_null_competition.sql](../../../SQL/migrations/2026-02-09_kp_rc_allow_null_competition.sql)

```sql
-- Rendre la colonne nullable
ALTER TABLE kp_rc
  MODIFY COLUMN Code_competition varchar(10) DEFAULT NULL;

-- Migrer les valeurs "- CNA -" vers NULL
UPDATE kp_rc
  SET Code_competition = NULL
  WHERE Code_competition = '- CNA -';

-- Ajouter un commentaire
ALTER TABLE kp_rc
  MODIFY COLUMN Code_competition varchar(10) DEFAULT NULL
  COMMENT 'Code de la compétition (NULL = RC national sans compétition spécifique)';
```

**Exécution** :
```bash
mysql -u root -p kpi < SQL/migrations/2026-02-09_kp_rc_allow_null_competition.sql
```

---

### Étape 2 : Adaptation Legacy PHP

#### Fichier : `sources/admin/GestionRc.php`

**Changement ligne 109** :
```php
// AVANT
if (in_array($row['Code_competition'], $arrayCompetitionList) || $row['Code_competition'] == '- CNA -') {
    array_push($arrayRc, $row);
}

// APRÈS
// Inclure RC national (NULL) ou compétitions filtrées
if (in_array($row['Code_competition'], $arrayCompetitionList) || $row['Code_competition'] === null) {
    array_push($arrayRc, $row);
}
```

**Impact** :
- Utilisation de `=== null` au lieu de `== '- CNA -'`
- Le reste de la logique PHP reste inchangé (INSERT/UPDATE avec valeur vide `""` devient `NULL` automatiquement)

---

### Étape 3 : Adaptation Template Smarty

#### Fichier : `sources/smarty/templates/GestionRc.tpl`

**Changement 1 - Filtre (ligne 53)** :
```smarty
{* AVANT *}
<option value="- CNA -" {if 'CNA'==$filtreCompet}selected{/if}>- CNA -</option>

{* APRÈS *}
<option value="CNA" {if 'CNA'==$filtreCompet}selected{/if}>{#National_sans_competition#}</option>
```

**Note importante** : La valeur est `"CNA"` (et non `""` vide) pour éviter une collision avec l'option "Tous" qui utilise déjà `value=""`. Le JavaScript côté client utilise cette valeur pour filtrer les lignes avec `data-code="CNA"`.

**Changement 2 - Select formulaire (ligne 130)** :
```smarty
{* AVANT *}
<option value="- CNA -">- CNA -</option>

{* APRÈS *}
<option value="">{#National_sans_competition#}</option>
```

**Changement 3 - Affichage tableau (ligne 84)** :
```smarty
{* AVANT *}
<td>{$arrayRc[i].Code_competition}</td>

{* APRÈS *}
<td>{if $arrayRc[i].Code_competition}{$arrayRc[i].Code_competition}{else}{#National_sans_competition#}{/if}</td>
```

**Changement 4 - data-code pour filtre JavaScript (ligne 75)** :
```smarty
{* AVANT *}
<tr ... data-code="{$arrayRc[i].Code_competition}">

{* APRÈS *}
<tr ... data-code="{if $arrayRc[i].Code_competition}{$arrayRc[i].Code_competition}{else}CNA{/if}">
```

**Note** : Les RC nationaux (`Code_competition = NULL`) reçoivent `data-code="CNA"` pour permettre au filtre JavaScript de les identifier correctement.

**Impact** :
- Remplacement de `"- CNA -"` par `NULL` en base (via formulaire avec valeur vide `""`)
- Affichage du label traduit `{#National_sans_competition#}`
- Filtre JavaScript fonctionnel pour les RC nationaux (via `data-code="CNA"`)

---

### Étape 4 : Traductions i18n

#### Fichier : `sources/commun/MyLang.ini`

**Section [fr] - après ligne 492** :
```ini
National_sans_competition = "National (sans compétition)"
```

**Section [en] - après ligne 1269** :
```ini
National_sans_competition = "National (no competition)"
```

---

### Étape 5 : Nouvelle page App4 (Nuxt)

Les nouveaux composants App4 utilisent directement `NULL` :

**Fichiers** :
- `sources/app4/pages/rc/index.vue` - Page RC moderne
- `sources/app4/components/admin/CompetitionGroupedSelect.vue` - Sélecteur avec `value=""`
- `sources/api2/src/Controller/AdminRcController.php` - Backend API2 avec gestion `NULL`

**Code clé** :
```typescript
// Frontend - CompetitionGroupedSelect.vue
<option v-if="showNationalOption" value="">
  {{ t('rc.no_national_competition') }}
</option>

// Frontend - rc/index.vue
formData.value = {
  competitionCode: null, // null = RC national
  // ...
}

// Backend - AdminRcController.php
$competitionCode = $data['competitionCode'] ?? null; // null = RC national
```

**Traductions App4** (`sources/app4/i18n/locales/`) :
```json
{
  "rc": {
    "no_national_competition": "National (sans compétition)" // FR
    "no_national_competition": "National (no competition)"   // EN
  }
}
```

---

## Test de régression

### Vérifier la compatibilité

1. **Page legacy** (`GestionRc.php`) :
   ```
   https://kpi.localhost/admin/GestionRc.php
   ```
   - ✅ Affiche "National (sans compétition)" pour les RC nationaux
   - ✅ Le filtre fonctionne correctement
   - ✅ Ajout/modification avec sélection "National (sans compétition)" insère `NULL`

2. **Page App4** (`/rc`) :
   ```
   https://app.kpi.localhost/rc
   ```
   - ✅ Affiche "National (sans compétition)" pour `competitionCode: null`
   - ✅ Sélecteur groupé affiche l'option nationale
   - ✅ Création/modification fonctionne avec `NULL`

3. **Base de données** :
   ```sql
   SELECT Code_competition, COUNT(*)
   FROM kp_rc
   GROUP BY Code_competition;
   ```
   - ✅ Aucune valeur `"- CNA -"` présente
   - ✅ Les RC nationaux ont `NULL`

---

## Rollback (si nécessaire)

En cas de problème, restaurer l'ancien comportement :

```sql
-- Restaurer "- CNA -"
UPDATE kp_rc
  SET Code_competition = '- CNA -'
  WHERE Code_competition IS NULL;

-- Rendre la colonne NOT NULL (optionnel)
ALTER TABLE kp_rc
  MODIFY COLUMN Code_competition varchar(10) NOT NULL;
```

**⚠️ Attention** : Restaurer aussi les fichiers PHP/Smarty avant rollback SQL.

---

## Fichiers modifiés

| Fichier | Type | Changement |
|---------|------|------------|
| `SQL/migrations/2026-02-09_kp_rc_allow_null_competition.sql` | SQL | Migration ALTER TABLE + UPDATE |
| `sources/admin/GestionRc.php` | PHP | Condition `=== null` au lieu de `== '- CNA -'` |
| `sources/smarty/templates/GestionRc.tpl` | Smarty | Remplacement affichage et selects |
| `sources/commun/MyLang.ini` | i18n | Ajout clés `National_sans_competition` |
| `sources/app4/pages/rc/index.vue` | Vue/Nuxt | Page moderne avec gestion `NULL` |
| `sources/app4/components/admin/CompetitionGroupedSelect.vue` | Vue | Composant avec option `value=""` |
| `sources/app4/i18n/locales/fr.json` | i18n | Traductions App4 FR |
| `sources/app4/i18n/locales/en.json` | i18n | Traductions App4 EN |
| `sources/api2/src/Controller/AdminRcController.php` | PHP | Backend API2 avec `NULL` |
| `sources/api2/src/Entity/Rc.php` | PHP | Entité Doctrine avec `nullable: true` |

---

## Compatibilité

- ✅ **PHP 8.4** : Compatible (strict comparison `=== null`)
- ✅ **MariaDB 11.5** : Compatible (NULL standard SQL)
- ✅ **Nuxt 4** : Compatible (TypeScript `string | null`)
- ✅ **Doctrine DBAL** : Compatible (nullable columns)
- ✅ **Rétrocompatibilité** : Les anciennes données migrées automatiquement

---

## Notes

- La valeur `NULL` est plus sémantique que `"- CNA -"`
- Le formulaire envoie `""` (vide) qui devient `NULL` en base grâce au type de colonne
- Les deux systèmes (legacy PHP + App4 Nuxt) coexistent sans problème
- Les filtres SQL sont simplifiés : `WHERE Code_competition IS NULL OR Code_competition = ?`

---

**Validé par** : Migration SQL + Tests manuels
**Déployé en** : Dev (2026-02-09)
**Prochaines étapes** : Tests en preprod avant déploiement production
