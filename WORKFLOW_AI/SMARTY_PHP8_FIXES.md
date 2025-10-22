# Smarty Template Engine - PHP 8 Compatibility Fixes

**Date**: 2025-10-20
**Context**: Migration PHP 7.4 → PHP 8.4
**Current Smarty Version**: 2.6.18 (2008)
**Status**: ✅ Patché pour PHP 8 (solutions temporaires)

---

## 🎯 Résumé

Smarty 2.6.18 n'est **pas compatible PHP 8** et nécessite des correctifs pour fonctionner. Trois problèmes majeurs ont été identifiés et corrigés.

---

## 🐛 Problèmes Identifiés et Corrections

### 1. `create_function()` Supprimée en PHP 8.0

**Fichier**: `sources/Smarty-Lib/Smarty_Compiler.class.php:274`

**Erreur**:
```
PHP Fatal error: Call to undefined function create_function()
```

**Cause**: `create_function()` était dépréciée en PHP 7.2 et supprimée en PHP 8.0

**Solution appliquée** (lignes 273-278):

```php
// AVANT (PHP < 8.0)
$source_content = preg_replace_callback($search, create_function('$matches', "return '"
    . $this->_quote_replace($this->left_delimiter) . 'php'
    . "' . str_repeat(\"\n\", substr_count('\$matches[1]', \"\n\")) .'"
    . $this->_quote_replace($this->right_delimiter)
    . "';"), $source_content);

// APRÈS (PHP 8 compatible)
$left_delim = $this->_quote_replace($this->left_delimiter);
$right_delim = $this->_quote_replace($this->right_delimiter);
$source_content = preg_replace_callback($search, function($matches) use ($left_delim, $right_delim) {
    return $left_delim . 'php' . str_repeat("\n", substr_count($matches[1], "\n")) . $right_delim;
}, $source_content);
```

**Type**: ✅ Patch définitif, pas d'effets de bord

---

### 2. Undefined Array Keys dans Templates

**Fichiers**: Templates Smarty (`page.tpl`, `header.tpl`, `main_menu.tpl`)

**Erreurs**:
```
PHP Warning: Undefined array key "css_supp" in page.tpl
PHP Warning: Undefined array key "bMirror" in header.tpl
PHP Warning: Undefined array key "headerSubTitle" in main_menu.tpl
```

**Cause**: PHP 8 génère des warnings pour les clés de tableaux non définies (auparavant des notices)

**Solutions appliquées**:

#### page.tpl (lignes 40-45, 74-79)
```smarty
{* AVANT *}
{assign var=temp value="css/$css_supp.css"}
{if $css_supp && is_file($temp)}
  <link type="text/css" rel="stylesheet" href="css/{$css_supp}.css">
{/if}

{* APRÈS *}
{if isset($css_supp)}
  {assign var=temp value="css/$css_supp.css"}
  {if $css_supp && is_file($temp)}
    <link type="text/css" rel="stylesheet" href="css/{$css_supp}.css">
  {/if}
{/if}
```

#### header.tpl (ligne 7)
```smarty
{* AVANT *}
{if $bMirror == 1}
  <br>
  <span class='vert'>Base Mirror</span>
{/if}

{* APRÈS *}
{if isset($bMirror) && $bMirror == 1}
  <br>
  <span class='vert'>Base Mirror</span>
{/if}
```

#### main_menu.tpl (ligne 37)
```smarty
{* AVANT *}
{if $headerSubTitle}
  {assign var="headerSubTitle0" value=$headerSubTitle|replace:' ':'_'}
  <span class='repere'>></span>
  <span class='repere'>{$smarty.config.$headerSubTitle0|default:$headerSubTitle}</span>
{/if}

{* APRÈS *}
{if isset($headerSubTitle) && $headerSubTitle}
  {assign var="headerSubTitle0" value=$headerSubTitle|replace:' ':'_'}
  <span class='repere'>></span>
  <span class='repere'>{$smarty.config.$headerSubTitle0|default:$headerSubTitle}</span>
{/if}
```

**Type**: ✅ Corrections définitives, bonnes pratiques PHP 8

**Action requise**: Supprimer cache Smarty après modification des templates
```bash
rm -f sources/smarty/templates_c/*.php
```

---

### 3. PDO Parameter Mismatch (bonus fix)

**Fichier**: `sources/admin/GestionDoc.php:211`

**Erreur**:
```
PHP Fatal error: PDOException: SQLSTATE[HY093]: Invalid parameter number
```

**Cause**: Requête SQL avec valeurs concaténées mais `execute()` avec paramètres nommés

**Solution appliquée** (lignes 204-214):

```php
// AVANT (bug - paramètres non utilisés)
$sql = "SELECT m.Id, m.Numero_ordre, m.Validation, m.Publication
    FROM kp_journee j, kp_match m
    WHERE j.Code_saison = '".$codeSaison."'
    AND j.Code_competition = '".$codeCompet."'
    AND j.Id = m.Id_journee
    ORDER BY m.Numero_ordre ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array(
    ':Code_competition' => $codeCompet,
    ':Code_saison' => $codeSaison
));

// APRÈS (correct - paramètres liés)
$sql = "SELECT m.Id, m.Numero_ordre, m.Validation, m.Publication
    FROM kp_journee j, kp_match m
    WHERE j.Code_saison = :Code_saison
    AND j.Code_competition = :Code_competition
    AND j.Id = m.Id_journee
    ORDER BY m.Numero_ordre ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array(
    ':Code_competition' => $codeCompet,
    ':Code_saison' => $codeSaison
));
```

**Type**: ✅ Correction définitive + sécurité (évite SQL injection)

---

## 📊 Tests Validés

- ✅ **PHP 7.4.33**: Aucune erreur, templates rendus correctement
- ✅ **PHP 8.4.13**: Aucune erreur, templates rendus correctement
- ✅ **Templates compilés**: Régénérés automatiquement après corrections
- ✅ **Pages admin**: GestionDoc.php, GestionClassement.php fonctionnels

---

## ⚠️ Limitations des Correctifs

Ces correctifs permettent à Smarty 2.6.18 de **survivre** en PHP 8, mais :

1. **Performance**: Smarty 2.x est lent comparé à v5
2. **Sécurité**: Plus de mises à jour de sécurité depuis 2008
3. **Fonctionnalités**: Manque les améliorations des 15 dernières années
4. **Maintenance**: Peut nécessiter d'autres patches à l'avenir

---

## 🚀 Recommandation : Migration vers Smarty 5

### Pourquoi Smarty 5 ?

| Aspect | Smarty 2.6.18 (2008) | Smarty 5.6.0 (2024) |
|--------|---------------------|---------------------|
| **PHP 7.4** | ⚠️ Avec patches | ✅ Natif |
| **PHP 8.4** | ⚠️ Avec patches | ✅ Natif |
| **Maintenance** | ❌ Abandonné | ✅ Actif |
| **Performance** | 🐌 Lent | ⚡ Rapide |
| **Sécurité** | ❌ Non maintenu | ✅ Maintenu |
| **Installation** | Manuel | ✅ Composer |

### Compatibilité PHP

- **Smarty 5.x** : PHP 7.2 → PHP 8.4 ✅
- **Smarty 4.x** : PHP 7.1+ ✅
- **Smarty 3.x** : PHP 5.2+ (ancien)

### Installation Recommandée

```bash
# Via Composer (recommandé)
composer require smarty/smarty:^5.6

# Ou téléchargement
# https://github.com/smarty-php/smarty/releases/tag/v5.6.0
```

### Migration Smarty 2 → 5

**Compatibilité syntaxe** : Bonne (90% des templates fonctionnent sans modification)

**Points d'attention** :
1. **Changement namespace** : `Smarty` devient `Smarty\Smarty`
2. **Méthodes dépréciées** : `register_function()` → `registerPlugin()`
3. **Configuration** : Certains chemins changent
4. **PHP tags** : `{php}...{/php}` déprécié (utiliser plugins)

**Effort estimé** : 2-4 heures (selon nombre de templates custom)

**Documentation** : https://smarty-php.github.io/smarty/stable/

---

## 📋 Plan d'Action

### Immédiat (Fait ✅)
1. ✅ Patch `create_function()` dans Smarty_Compiler
2. ✅ Ajout `isset()` dans templates
3. ✅ Correction requête PDO GestionDoc.php
4. ✅ Tests PHP 7.4 et PHP 8.4

### Court terme (Recommandé)
1. ⏭️ Planifier migration Smarty 2 → Smarty 5
2. ⏭️ Créer branche de test pour migration Smarty
3. ⏭️ Tester templates complexes avec Smarty 5
4. ⏭️ Mettre à jour documentation projet

### Long terme
1. ⏭️ Migrer tous les templates vers Smarty 5
2. ⏭️ Supprimer patches temporaires
3. ⏭️ Utiliser Composer pour gestion dépendances
4. ⏭️ Profiter des nouvelles fonctionnalités Smarty 5

---

## 📚 Liens Utiles

- **Site officiel** : https://www.smarty.net/
- **GitHub** : https://github.com/smarty-php/smarty
- **Packagist** : https://packagist.org/packages/smarty/smarty
- **Documentation v5** : https://smarty-php.github.io/smarty/stable/
- **Migration Guide** : https://smarty-php.github.io/smarty/stable/designers/language-upgrading/

---

## 🎓 Leçons Apprises

### Bonnes Pratiques

1. ✅ Toujours tester `isset()` avant d'utiliser une variable optionnelle
2. ✅ Utiliser paramètres liés PDO (jamais de concaténation SQL)
3. ✅ Mettre à jour les dépendances critiques régulièrement
4. ✅ Préférer Composer aux installations manuelles

### Pièges à Éviter

1. ❌ Garder des versions 15+ ans non maintenues
2. ❌ Supposer que les warnings PHP 8 sont "juste des warnings"
3. ❌ Ignorer les dépréciations PHP (elles deviennent des erreurs)
4. ❌ Négliger les tests de compatibilité PHP lors des upgrades

---

## 📝 Fichiers Modifiés

### Smarty Core
- `sources/Smarty-Lib/Smarty_Compiler.class.php` (ligne 274)

### Templates Smarty
- `sources/smarty/templates/page.tpl` (lignes 40-45, 74-79)
- `sources/smarty/templates/header.tpl` (ligne 7)
- `sources/smarty/templates/main_menu.tpl` (ligne 37)

### Application PHP
- `sources/admin/GestionDoc.php` (lignes 204-214)

### Cache
- `sources/smarty/templates_c/*.php` (supprimés pour recompilation)

---

**Auteur** : Laurent Garrigue / Claude Code
**Date** : 2025-10-20
**Smarty Version** : 2.6.18 → patchée pour PHP 8
**Recommandation** : ⚠️ Migrer vers Smarty 5.6.0 dès que possible
**Statut** : ✅ Fonctionnel PHP 7.4 et PHP 8.4 (avec patches)
