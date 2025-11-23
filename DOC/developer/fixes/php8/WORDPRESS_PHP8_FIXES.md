# Correctifs WordPress pour PHP 8.4

> **⚠️ IMPORTANT** : Ces modifications concernent des fichiers WordPress non versionnés (`docker/wordpress/`).
> Elles seront **écrasées** lors des mises à jour de WordPress ou des plugins.
> Ce document permet de les réappliquer si nécessaire.

## Date des modifications
**2025-10-22**

## Contexte
Le projet utilise **PHP 8.4** (conteneur `kpi_php8`), mais WordPress et certains plugins ne sont pas encore totalement compatibles avec cette version. Les modifications suivantes corrigent les erreurs et warnings PHP 8.4.

---

## 1. Plugin NextGen Gallery - Dynamic Properties

### Fichier
`docker/wordpress/wp-content/plugins/nextgen-gallery/src/DataMapper/Model.php`

### Erreur
```
Deprecated: Creation of dynamic property Imagely\NGG\DataTypes\DisplayType::$format_content is deprecated
```

### Modification
**Ligne 5** - Ajout de l'attribut `#[\AllowDynamicProperties]`

```php
<?php

namespace Imagely\NGG\DataMapper;

#[\AllowDynamicProperties]  // ← AJOUT
abstract class Model {

	use Validation;
```

### Explication
En PHP 8.2+, la création dynamique de propriétés (ligne 16 : `$this->$key = $value;`) génère une erreur deprecated. L'attribut `#[\AllowDynamicProperties]` autorise explicitement ce comportement pour cette classe et toutes ses classes enfants.

### Comment réappliquer
```bash
# Ouvrir le fichier
nano docker/wordpress/wp-content/plugins/nextgen-gallery/src/DataMapper/Model.php

# Ajouter après la ligne "namespace Imagely\NGG\DataMapper;"
#[\AllowDynamicProperties]
```

---

## 2. Plugin Rank Math SEO - Undefined array keys

### Fichier
`docker/wordpress/wp-content/plugins/seo-by-rank-math/includes/helpers/class-attachment.php`

### Erreurs
```
Warning: Undefined array key "scheme" in class-attachment.php on line 93
Warning: Undefined array key "host" in class-attachment.php on line 93
```

### Modification
**Ligne 93** - Protection avec opérateur null coalescing

```php
// AVANT
return $parsed_url['scheme'] . '://' . $parsed_url['host'] . $img;

// APRÈS
return ( $parsed_url['scheme'] ?? 'https' ) . '://' . ( $parsed_url['host'] ?? '' ) . $img;
```

### Explication
La fonction `wp_parse_url()` (alias de `parse_url()`) peut retourner un tableau sans les clés 'scheme' et 'host' dans certains cas. Valeur par défaut : 'https' pour le schéma, chaîne vide pour l'hôte.

### Comment réappliquer
```bash
# Ouvrir le fichier
nano docker/wordpress/wp-content/plugins/seo-by-rank-math/includes/helpers/class-attachment.php

# Chercher la ligne 93
# Remplacer par :
return ( $parsed_url['scheme'] ?? 'https' ) . '://' . ( $parsed_url['host'] ?? '' ) . $img;
```

---

## 3. WordPress Core - Undefined array key "host"

### Fichier 1
`docker/wordpress/wp-includes/pluggable.php`

### Erreurs
```
Warning: Undefined array key "host" in pluggable.php on line 1640
Warning: Undefined array key "host" in pluggable.php on line 1642
Deprecated: strtolower(): Passing null to parameter #1 ($string) of type string is deprecated
```

### Modifications
**Fonction** : `wp_safe_redirect()`

**Ligne 1640** - Protection avec opérateur null coalescing
```php
// AVANT
$allowed_hosts = (array) apply_filters( 'allowed_redirect_hosts', array( $wpp['host'] ), isset( $lp['host'] ) ? $lp['host'] : '' );

// APRÈS
$allowed_hosts = (array) apply_filters( 'allowed_redirect_hosts', array( $wpp['host'] ?? '' ), isset( $lp['host'] ) ? $lp['host'] : '' );
```

**Ligne 1642** - Protection avec opérateur null coalescing
```php
// AVANT
if ( isset( $lp['host'] ) && ( ! in_array( $lp['host'], $allowed_hosts, true ) && strtolower( $wpp['host'] ) !== $lp['host'] ) ) {

// APRÈS
if ( isset( $lp['host'] ) && ( ! in_array( $lp['host'], $allowed_hosts, true ) && strtolower( $wpp['host'] ?? '' ) !== $lp['host'] ) ) {
```

### Comment réappliquer
```bash
# Ouvrir le fichier
nano docker/wordpress/wp-includes/pluggable.php

# Chercher la ligne 1640 et 1642 (fonction wp_safe_redirect)
# Remplacer $wpp['host'] par $wpp['host'] ?? ''
```

---

### Fichier 2
`docker/wordpress/wp-includes/theme.php`

### Erreurs
```
Warning: Undefined array key "host" in theme.php on line 3734
Deprecated: strtolower(): Passing null to parameter #1 ($string) of type string is deprecated
```

### Modification
**Fonction** : `_wp_customize_loader_settings()`

**Ligne 3734** - Protection avec opérateur null coalescing (2 occurrences dans le fichier)
```php
// AVANT
$cross_domain = ( strtolower( $admin_origin['host'] ) !== strtolower( $home_origin['host'] ) );

// APRÈS
$cross_domain = ( strtolower( $admin_origin['host'] ?? '' ) !== strtolower( $home_origin['host'] ?? '' ) );
```

### Comment réappliquer
```bash
# Ouvrir le fichier
nano docker/wordpress/wp-includes/theme.php

# Chercher la ligne 3734 (fonction _wp_customize_loader_settings)
# Il y a 2 occurrences de cette ligne dans le fichier
# Remplacer $admin_origin['host'] par $admin_origin['host'] ?? ''
# Remplacer $home_origin['host'] par $home_origin['host'] ?? ''
```

---

## Script de réapplication automatique

Pour réappliquer tous les correctifs automatiquement :

```bash
#!/bin/bash
# Script : docker/wordpress/apply_php8_fixes.sh

WORDPRESS_DIR="docker/wordpress"

echo "=== Application des correctifs PHP 8.4 pour WordPress ==="

# 1. NextGen Gallery - Dynamic Properties
echo "1. Correctif NextGen Gallery..."
FILE1="$WORDPRESS_DIR/wp-content/plugins/nextgen-gallery/src/DataMapper/Model.php"
if [ -f "$FILE1" ]; then
    if ! grep -q "#\[\\\\AllowDynamicProperties\]" "$FILE1"; then
        sed -i '/^namespace Imagely\\NGG\\DataMapper;$/a\\\n#[\\AllowDynamicProperties]' "$FILE1"
        echo "   ✓ NextGen Gallery corrigé"
    else
        echo "   → Déjà corrigé"
    fi
else
    echo "   ⚠ Plugin non trouvé"
fi

# 2. WordPress Core - pluggable.php
echo "2. Correctif pluggable.php..."
FILE2="$WORDPRESS_DIR/wp-includes/pluggable.php"
if [ -f "$FILE2" ]; then
    sed -i "s/\$wpp\['host'\]/\$wpp['host'] ?? ''/g" "$FILE2"
    echo "   ✓ pluggable.php corrigé"
else
    echo "   ✗ Fichier non trouvé"
fi

# 3. WordPress Core - theme.php
echo "3. Correctif theme.php..."
FILE3="$WORDPRESS_DIR/wp-includes/theme.php"
if [ -f "$FILE3" ]; then
    sed -i "s/\$admin_origin\['host'\]/\$admin_origin['host'] ?? ''/g" "$FILE3"
    sed -i "s/\$home_origin\['host'\]/\$home_origin['host'] ?? ''/g" "$FILE3"
    echo "   ✓ theme.php corrigé"
else
    echo "   ✗ Fichier non trouvé"
fi

echo ""
echo "=== Correctifs appliqués avec succès ==="
```

### Utilisation du script
```bash
chmod +x docker/wordpress/apply_php8_fixes.sh
./docker/wordpress/apply_php8_fixes.sh
```

---

## Versions testées

- **PHP** : 8.4.13
- **WordPress** : Version actuelle du projet
- **NextGen Gallery** : Version installée au moment du correctif
- **Rank Math SEO** : Version installée au moment du correctif

---

## Problèmes connus restants

Aucun problème PHP 8.4 connu à ce jour après application de ces correctifs.

---

## Liens utiles

- [PHP 8.2 Deprecated Dynamic Properties](https://wiki.php.net/rfc/deprecate_dynamic_properties)
- [WordPress PHP 8 Compatibility](https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/)
- [NextGen Gallery Support](https://www.imagely.com/wordpress-gallery-plugin/)

---

## Notes pour les mises à jour

### Avant de mettre à jour WordPress
1. Vérifier la compatibilité PHP 8.4 dans les notes de version
2. Sauvegarder les fichiers modifiés
3. Préparer le script de réapplication

### Après mise à jour WordPress
1. Réappliquer les correctifs avec le script
2. Tester les pages WordPress
3. Vérifier les logs d'erreurs PHP

### Avant de mettre à jour NextGen Gallery
1. Vérifier si le plugin a ajouté le support PHP 8.2+
2. Consulter le changelog du plugin
3. Si non corrigé, préparer la réapplication du correctif

---

*Document créé le 2025-10-22 - Projet KPI*
