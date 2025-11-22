# Migration WordPress vers PHP 8.4

Ce document liste toutes les modifications nécessaires pour migrer WordPress de PHP 7.4 vers PHP 8.4.

## Date
16 novembre 2025

## Environnement concerné
- Développement : ✅ Complété
- Pré-production : ⏳ À appliquer
- Production : ⏳ À appliquer

---

## 1. Configuration wp-config.php

**Fichier** : `docker/wordpress/wp-config.php`

### Modifications à appliquer

#### 1.1 Forcer HTTPS (après la ligne 34, avant les defines MySQL)

```php
// Force HTTPS for all WordPress URLs
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $_SERVER['HTTPS'] = 'on';
}

define( 'WP_HOME', 'https://kpi.localhost' );
define( 'WP_SITEURL', 'https://kpi.localhost/wordpress' );
define( 'FORCE_SSL_ADMIN', true );
```

**⚠️ Important** : Adapter les URLs selon l'environnement :
- Dev : `https://kpi.localhost`
- Preprod : `https://kpi-preprod.localhost` (ou votre domaine)
- Prod : `https://kayak-polo.info`

#### 1.2 Suppression des warnings de dépréciation PHP 8.4 (après WP_DEBUG)

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false); // Hide errors from visitors

// Suppress deprecation warnings in logs (PHP 8.4 compatibility)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
```

---

## 2. Plugin WP Multilang - Correction PHP 8.4

**Fichier** : `docker/wordpress/wp-content/plugins/wp-multilang/includes/wpm-widget-block-editor.php`

### Problème
En PHP 8.4, l'utilisation de la syntaxe tableau `[]` sur un objet stdClass génère une erreur fatale :
```
Fatal error: Cannot use object of type stdClass as array
```

### Solution
Remplacer entièrement la fonction `wpm_translate_widget_block_in_editor` (lignes 40-68) :

```php
function wpm_translate_widget_block_in_editor($response, $widget, $request){
  if(is_object($response) && isset($response->data)){
    // PHP 8.4 compatibility: handle both array and stdClass object types
    $data = $response->data;

    if(is_array($data)){
      	// Handle array type - check each level is array before using array syntax
      	if(isset($data['instance']) && is_array($data['instance']) &&
      	   isset($data['instance']['raw']) && is_array($data['instance']['raw']) &&
      	   isset($data['instance']['raw']['content'])){
	    	$content = $data['instance']['raw']['content'];
	    	$content = wpm_translate_value($content);
	    	$response->data['instance']['raw']['content'] = $content;
	    }
    } elseif(is_object($data)){
      	// Handle stdClass objects - use property_exists() and object syntax
      	if(property_exists($data, 'instance') &&
      	   is_object($data->instance) &&
      	   property_exists($data->instance, 'raw') &&
      	   is_object($data->instance->raw) &&
      	   property_exists($data->instance->raw, 'content')){
	    	$content = $data->instance->raw->content;
	    	$content = wpm_translate_value($content);
	    	$response->data->instance->raw->content = $content;
	    }
    }
  }
  return $response;
}
```

### Explication technique
- **Ligne 43** : Copie `$response->data` dans `$data` pour éviter l'accès direct
- **Lignes 45-53** : Gestion du cas où `$data` est un tableau
  - Vérification `is_array()` à chaque niveau imbriqué avant d'utiliser `isset($var['key'])`
- **Lignes 54-64** : Gestion du cas où `$data` est un objet stdClass
  - Utilisation de `property_exists()` au lieu de `isset()` pour les objets
  - Utilisation de la syntaxe objet `->` au lieu de `[]`

---

## 3. Configuration Apache - CORS et .htaccess

**Fichier** : `docker/config/000-default.conf`

### Créer le fichier avec le contenu suivant :

```apache
ServerName kpi.localhost

<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        # Enable CORS for all requests
        Header always set Access-Control-Allow-Origin "*"
        Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS, PATCH"
        Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, Accept"
        Header always set Access-Control-Allow-Credentials "true"

        # Handle preflight OPTIONS requests
        RewriteEngine On
        RewriteCond %{REQUEST_METHOD} OPTIONS
        RewriteRule ^(.*)$ $1 [R=200,L]
    </Directory>

    # WordPress subdirectory configuration
    <Directory /var/www/html/wordpress>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        # CORS headers for WordPress
        Header always set Access-Control-Allow-Origin "*"
        Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS, PATCH"
        Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, Accept"
        Header always set Access-Control-Allow-Credentials "true"
    </Directory>
</VirtualHost>

<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>
```

**⚠️ Important** : Adapter `ServerName` selon l'environnement :
- Dev : `kpi.localhost`
- Preprod : `kpi-preprod.localhost` (ou votre domaine)
- Prod : `kayak-polo.info`

---

## 4. Dockerfile - Copie de la configuration Apache

**Fichier** : `docker/config/Dockerfile.dev.web` (ou `Dockerfile.preprod.web`, `Dockerfile.prod.web`)

### Ajouter après la ligne 40 (après php-error-logging.ini) :

```dockerfile
# Copy Apache vhost configuration with CORS and .htaccess support
COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf
RUN chmod 644 /etc/apache2/sites-enabled/000-default.conf
```

**⚠️ Note** : Cette modification doit être faite dans :
- `docker/config/Dockerfile.dev.web` (dev)
- `docker/config/Dockerfile.preprod.web` (preprod)
- `docker/config/Dockerfile.prod.web` (prod)

---

## 5. Configuration .htaccess - Routage WordPress

**Fichier** : `sources/.htaccess`

### Ajouter À LA FIN du fichier (après la ligne END iThemes Security) :

```apache
# WordPress routing from root to /wordpress/ subdirectory
RewriteEngine On

# Redirect /wp-login.php to /wordpress/wp-login.php
RewriteCond %{REQUEST_URI} ^/wp-login\.php$
RewriteRule ^wp-login\.php$ /wordpress/wp-login.php [L,QSA]

# Redirect /wp-admin/ to /wordpress/wp-admin/
RewriteCond %{REQUEST_URI} ^/wp-admin/
RewriteRule ^wp-admin/(.*)$ /wordpress/wp-admin/$1 [L,QSA]

# Rewrite /wp-json/* to WordPress REST API handler
RewriteCond %{REQUEST_URI} ^/wp-json/
RewriteRule ^wp-json/(.*)$ /wordpress/index.php?rest_route=/$1 [L,QSA]
```

**⚠️ Important** :
- Ces règles doivent être ajoutées **APRÈS** la section iThemes Security
- Ne PAS modifier les règles iThemes Security existantes

---

## 6. Must-Use Plugin - Fix REST API URLs

**Fichier** : `docker/wordpress/wp-content/mu-plugins/rest-api-root-path.php`

### Créer le fichier avec le contenu suivant :

```php
<?php
/**
 * Plugin Name: REST API Root Path Fix
 * Description: Makes WordPress REST API accessible from root path (/wp-json/) instead of /wordpress/wp-json/
 */

// Fix REST API URL in responses to use root path
add_filter('rest_url', function($url) {
    return str_replace('/wordpress/wp-json/', '/wp-json/', $url);
}, 10, 1);

add_filter('rest_url_prefix', function($prefix) {
    return 'wp-json';
}, 10, 1);

add_filter('rest_link', function($link) {
    return str_replace('/wordpress/wp-json/', '/wp-json/', $link);
}, 10, 1);
```

**📁 Emplacement** : Créer le dossier `mu-plugins` s'il n'existe pas

---

## 7. Script de désactivation des plugins (optionnel)

**Fichier** : `docker/disable_wordpress_plugin.sh`

### Utile pour désactiver tous les plugins en cas de problème :

```bash
#!/bin/bash

# WordPress database credentials from docker/.env
WORDPRESS_DB_NAME="kpiwordpress"
WORDPRESS_DB_USER="root"
WORDPRESS_DB_PASSWORD="root"
WORDPRESS_DB_HOST="dbwp"
CONTAINER_NAME="kpi_dbwp"

echo "Disabling all WordPress plugins via database..."

docker exec -i ${CONTAINER_NAME} mysql -u${WORDPRESS_DB_USER} -p"${WORDPRESS_DB_PASSWORD}" ${WORDPRESS_DB_NAME} <<EOF
UPDATE wp_options SET option_value = '' WHERE option_name = 'active_plugins';
SELECT option_value FROM wp_options WHERE option_name = 'active_plugins';
EOF

echo "All plugins have been disabled."
echo "You can now log into WordPress admin and re-enable compatible plugins one by one."
```

**🔧 Utilisation** :
```bash
chmod +x docker/disable_wordpress_plugin.sh
./docker/disable_wordpress_plugin.sh
```

---

## 8. Procédure de déploiement

### 8.1 Développement (déjà fait ✅)
```bash
# Modifier les fichiers selon les sections 1-6 ci-dessus
make dev_rebuild
```

### 8.2 Pré-production
```bash
# 1. Modifier les fichiers selon les sections 1-6
# 2. Adapter les URLs dans wp-config.php et 000-default.conf
# 3. Rebuild les conteneurs
make preprod_rebuild
```

### 8.3 Production
```bash
# 1. Modifier les fichiers selon les sections 1-6
# 2. Adapter les URLs dans wp-config.php et 000-default.conf
# 3. Backup WordPress avant migration
make wordpress_backup
# 4. Rebuild les conteneurs
make prod_rebuild
```

---

## 9. Vérifications post-migration

### ✅ Checklist
- [ ] WordPress admin accessible (`https://domaine/wp-admin/`)
- [ ] Login WordPress fonctionne
- [ ] Pas d'erreurs PHP dans les logs (`docker/wordpress/wp-content/debug.log`)
- [ ] REST API accessible (`https://domaine/wp-json/`)
- [ ] Éditeur de blocs Gutenberg fonctionne
- [ ] Widgets fonctionnent sans erreur
- [ ] Plugins compatibles PHP 8.4 activés

### 📋 Commandes de vérification

```bash
# Vérifier les logs PHP
docker exec kpi_php tail -f /var/www/html/wordpress/wp-content/debug.log

# Vérifier les logs Apache
make dev_logs  # ou preprod_logs / prod_logs

# Tester REST API
curl https://kpi.localhost/wp-json/
```

---

## 10. Plugins incompatibles identifiés

### ❌ À supprimer ou mettre à jour

1. **NextGEN Gallery Sidebar Widget** (`nextgen-gallery-sidebar-widget`)
   - Erreur : `ArgumentCountError: Too few arguments to function WP_Widget::__construct()`
   - Solution : Supprimer le plugin ou mettre à jour vers une version compatible PHP 8.4

2. **NextGEN Gallery** (core) - `nextgen-gallery`
   - **Erreurs multiples :**
     - Warnings de dépréciation PHP 8.4
     - Bloc `ngg-mrssw` incompatible avec l'éditeur de widgets moderne
     - Notice : `wp_enqueue_script() appelée incorrectement pour 'wp-editor'`
   - **Impact :** Le widget ne fonctionne pas dans l'éditeur de widgets Gutenberg
   - **Solution recommandée :**
     - **Option 1 (recommandée)** : Désactiver/Supprimer NextGEN Gallery et utiliser une alternative moderne (ex: Envira Gallery, FooGallery)
     - **Option 2** : Mettre à jour vers NextGEN Gallery 3.x (vérifier compatibilité PHP 8.4)
     - **Option 3 temporaire** : Utiliser l'éditeur de widgets classique via plugin "Classic Widgets"

3. **iThemes Security** (`better-wp-security`)
   - Warnings de dépréciation PHP 8.4
   - Solution : Mettre à jour vers Solid Security 9.x

### ✅ Plugins compatibles (après correctif)

- **WP Multilang** : Compatible après application du patch (section 2)
- **PWA** : Compatible
- Autres plugins standard : À tester individuellement

### 🔧 Solution temporaire pour NextGEN Gallery

Si vous devez absolument garder NextGEN Gallery temporairement :

1. **Installer Classic Widgets** :
```bash
# Via WP-CLI dans le conteneur
docker exec kpi_php wp plugin install classic-widgets --activate --path=/var/www/html/wordpress
```

2. **Ou désactiver l'éditeur de blocs pour les widgets** dans `wp-config.php` :
```php
// Désactiver l'éditeur de blocs pour les widgets (PHP 8.4 + NextGEN Gallery)
add_filter( 'use_widgets_block_editor', '__return_false' );
```

**⚠️ Attention** : Cette solution est temporaire. NextGEN Gallery n'est plus activement maintenu et devrait être remplacé par une alternative moderne compatible PHP 8.4.

---

## 11. Notes importantes

### 🔴 Différences entre environnements

Les fichiers suivants doivent avoir des URLs différentes selon l'environnement :

| Fichier | Variable | Dev | Preprod | Prod |
|---------|----------|-----|---------|------|
| `wp-config.php` | `WP_HOME` | `https://kpi.localhost` | `https://kpi-preprod.localhost` | `https://kayak-polo.info` |
| `wp-config.php` | `WP_SITEURL` | `https://kpi.localhost/wordpress` | `https://kpi-preprod.localhost/wordpress` | `https://kayak-polo.info/wordpress` |
| `000-default.conf` | `ServerName` | `kpi.localhost` | `kpi-preprod.localhost` | `kayak-polo.info` |

### 🔴 Fichiers à modifier par environnement

**Développement :**
- `docker/config/Dockerfile.dev.web`
- `docker/wordpress/wp-config.php` (avec URLs dev)

**Pré-production :**
- `docker/config/Dockerfile.preprod.web`
- `docker/wordpress/wp-config.php` (avec URLs preprod)
- `docker/config/000-default.conf` (créer une copie `000-default.preprod.conf` si besoin)

**Production :**
- `docker/config/Dockerfile.prod.web`
- `docker/wordpress/wp-config.php` (avec URLs prod)
- `docker/config/000-default.conf` (créer une copie `000-default.prod.conf` si besoin)

---

## 12. Rollback en cas de problème

Si la migration échoue :

```bash
# 1. Restaurer la backup WordPress
cd docker
tar -xzf wordpress_backup_YYYY-MM-DD_HH-MM-SS.tar.gz

# 2. Revenir à l'image PHP 7.4
# Modifier docker/compose.*.yaml :
# Changer php:8.4.13-apache-trixie par php:7.4-apache

# 3. Rebuild
make dev_rebuild  # ou preprod_rebuild / prod_rebuild
```

---

## Résumé des fichiers modifiés

```
docker/
├── config/
│   ├── 000-default.conf                    # NOUVEAU - Config Apache CORS
│   ├── Dockerfile.dev.web                  # MODIFIÉ - Copie 000-default.conf
│   ├── Dockerfile.preprod.web              # À MODIFIER
│   └── Dockerfile.prod.web                 # À MODIFIER
├── wordpress/
│   ├── wp-config.php                       # MODIFIÉ - HTTPS + error_reporting
│   └── wp-content/
│       ├── mu-plugins/
│       │   └── rest-api-root-path.php      # NOUVEAU - Fix REST API URLs
│       └── plugins/
│           └── wp-multilang/
│               └── includes/
│                   └── wpm-widget-block-editor.php  # MODIFIÉ - PHP 8.4 fix
└── disable_wordpress_plugin.sh              # NOUVEAU - Script désactivation

sources/
└── .htaccess                                # MODIFIÉ - Routage WordPress
```

---

## Auteur
Migration réalisée avec Claude Code le 16 novembre 2025

## Références
- [PHP 8.4 Migration Guide](https://www.php.net/manual/en/migration84.php)
- [WordPress PHP Compatibility](https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/)
