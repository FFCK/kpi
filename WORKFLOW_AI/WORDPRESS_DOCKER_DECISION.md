# WordPress - Décision Architecture : Container Dédié vs Intégré

**Date**: 13 novembre 2025
**Contexte**: Évaluation dockerisation WordPress dans container séparé
**Décision**: ❌ Annulée - Conservation architecture monolithique
**Statut**: Documentation

---

## 🎯 Contexte

### Proposition Initiale

Séparer WordPress dans son propre container Docker avec :
- Container WordPress dédié (image `wordpress:php8.3-apache`)
- Service séparé dans `docker-compose`
- Routing Traefik sur `/wordpress`
- Volume WordPress monté depuis `docker/wordpress/`

### Architecture Proposée

```
┌─────────────────┐
│   Traefik       │
└────────┬────────┘
         │
    ┌────┴─────┐
    │          │
┌───▼─────┐ ┌─▼──────────┐
│ KPI PHP │ │ WordPress  │
│  8.4    │ │  8.3       │
└─────────┘ └────────────┘
```

---

## ⚙️ Tests Effectués

### Modifications Appliquées

1. **docker/compose.dev.yaml** : Ajout service WordPress dédié
2. **docker/compose.preprod.yaml** : Ajout service WordPress dédié
3. **docker/compose.prod.yaml** : Ajout service WordPress dédié
4. **Traefik Routing** : PathPrefix `/wordpress` avec middleware StripPrefix
5. **wp-config.php** : Ajustement URLs `WP_HOME` et `WP_SITEURL`

### Problèmes Rencontrés

#### Problème 1 : Routing Traefik

**Symptôme** :
- WordPress à `/wordpress/` au lieu de la racine `/`
- Liens menu WordPress cassés (pointent vers `/wordpress/kpcalendrier.php` au lieu de `/kpcalendrier.php`)

**Cause** :
- WordPress génère des liens relatifs incluant le préfixe `/wordpress`
- Menus WordPress ne pointent plus vers pages PHP KPI correctement

#### Problème 2 : Complexité Routing

**Tentatives** :
- Middleware StripPrefix : retire `/wordpress` avant envoi au container
- Middleware AddPrefix : réajoute `/wordpress` dans redirections
- Redirections infinies et problèmes `/wp-admin` → `/wordpress/wp-admin`

**Résultat** :
- Configuration Traefik complexe et fragile
- Nécessite ajustements multiples dans WordPress et base de données

---

## ✅ Architecture Retenue : Monolithique

### Configuration Actuelle

```
┌─────────────────┐
│   Traefik       │
└────────┬────────┘
         │
    ┌────▼────────┐
    │  KPI PHP    │
    │   8.4       │
    │             │
    │ /var/www/   │
    │  ├─ sources │
    │  └─wordpress│
    └─────────────┘
```

### Avantages

| Critère | Container Séparé | Monolithique ✅ |
|---------|------------------|-----------------|
| **Simplicité** | ⚠️ Complexe (Traefik) | ✅ Simple |
| **Routing** | ⚠️ Middleware multiples | ✅ Apache natif |
| **URLs** | ⚠️ Préfixe `/wordpress` | ✅ Racine `/` |
| **Liens Menu** | ❌ Cassés | ✅ Fonctionnels |
| **Maintenance** | ⚠️ 2 containers | ✅ 1 container |
| **Performances** | ≈ Équivalent | ≈ Équivalent |
| **Isolation** | ✅ Totale | ⚠️ Partielle |

### Inconvénients Acceptés

| Problème | Impact | Mitigation |
|----------|--------|------------|
| **Pas d'isolation PHP** | WordPress et KPI partagent PHP 8.4 | ✅ Compatible |
| **Restart global** | Redémarrer container affecte WordPress et KPI | ⚠️ Mineur |
| **Versions PHP liées** | Même version PHP pour tout | ✅ Acceptable (PHP 8.4 compatible) |

---

## 🔧 Configuration Finale (Retenue)

### docker/compose.dev.yaml

```yaml
services:
    kpi:
        container_name: ${PHP_CONTAINER_NAME}
        image: php:8.4-apache
        volumes:
            - ../sources:/var/www/html
            - ${HOST_WORDPRESS_PATH}:/var/www/html/wordpress  # ← WordPress monté ici
        networks:
            - network_kpi
            - traefiknetwork
        labels:
            - "traefik.enable=true"
            - "traefik.http.routers.kpi.rule=Host(`${KPI_DOMAIN_NAME}`)"  # ← Tout le domaine
            - "traefik.http.routers.kpi.entrypoints=websecure"
            - "traefik.http.routers.kpi.tls=true"
```

**Pas de service WordPress séparé.**

### sources/index.php

```php
<?php
if(!isset($_SESSION)) {
    session_start();
}
include_once('commun/MyTools.php');

// Session mirror handling
if (utyGetGet('mirror', false)) {
    $mirror = utyGetGet('mirror', 0);
    $_SESSION['mirror'] = ($mirror == '1') ? '1' : '0';
}

// WordPress integration
define('WP_USE_THEMES', true);
require('./wordpress/wp-blog-header.php');
```

**WordPress chargé directement depuis le container PHP.**

### docker/wordpress/wp-config.php

```php
// Configuration BDD
define('DB_NAME', 'kpiwordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_HOST', 'dbwp');  // Container MySQL dédié WordPress

// URLs WordPress
define('WP_HOME', 'https://kpi.localhost');                    // Racine du site
define('WP_SITEURL', 'https://kpi.localhost/wordpress');       // Admin WordPress

// Force HTTPS
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
$_SERVER['HTTPS'] = 'on';
```

**Configuration standard sans complexité.**

---

## 📊 Raisons de la Décision

### Raison 1 : Expérience Utilisateur

**Problème critique** : Liens menu WordPress cassés

```
Situation actuelle (old_prod) :
├─ Menu WordPress → /kpcalendrier.php ✅ Fonctionne
└─ Menu WordPress → /kpmatchs.php ✅ Fonctionne

Avec container séparé :
├─ Menu WordPress → /wordpress/kpcalendrier.php ❌ 404
└─ Menu WordPress → /wordpress/kpmatchs.php ❌ 404
```

**Impact** : Navigation utilisateur cassée.

### Raison 2 : Simplicité Maintenance

| Tâche | Container Séparé | Monolithique |
|-------|------------------|--------------|
| **Mise à jour WordPress** | via WP Admin | via WP Admin |
| **Debug logs** | 2 containers à vérifier | 1 container |
| **Restart** | 2 services | 1 service |
| **Configuration** | Traefik + 2 compose | 1 compose |
| **Backup** | 2 volumes | 2 volumes (identique) |

### Raison 3 : Compatibilité

WordPress 6.x est **100% compatible PHP 8.4** :
- Pas besoin de version PHP différente
- Pas besoin d'isolation PHP
- Partage container acceptable

### Raison 4 : Migration old_prod → VPS

**Architecture old_prod (hébergeur)** :
```
Apache unique
├─ /public_html/              (KPI)
└─ /public_html/wordpress/    (WordPress)
```

**Architecture VPS retenue** : Reproduction fidèle
```
Container PHP Apache
├─ /var/www/html/            (KPI)
└─ /var/www/html/wordpress/  (WordPress)
```

**Avantage** : Migration directe sans refonte architecture.

---

## 🎯 Conclusion

### Décision Finale

**❌ Container WordPress dédié abandonné**

**✅ Conservation architecture monolithique (WordPress intégré container PHP KPI)**

### Justification

1. **Simplicité > Complexité** : Routing Traefik trop complexe pour bénéfice limité
2. **Expérience Utilisateur** : Liens menu WordPress fonctionnels
3. **Compatibilité** : Architecture similaire à old_prod facilite migration
4. **Isolation inutile** : PHP 8.4 compatible WordPress ET KPI
5. **Maintenance** : 1 container plus simple que 2

### Prochaines Étapes

1. ✅ **Conserver architecture actuelle**
2. ✅ **Documenter migration old_prod → VPS**
3. ✅ **Script synchronisation prod → preprod**
4. ⏭️ **Migration données old_prod → VPS preprod**
5. ⏭️ **Tests validation preprod**
6. ⏭️ **Migration prod finale**

---

## 📝 Documents de Référence

- **[WORDPRESS_MIGRATION_OLD_PROD_TO_VPS.md](WORDPRESS_MIGRATION_OLD_PROD_TO_VPS.md)** : Guide migration complet
- **[WORDPRESS_PHP8_FIXES.md](WORDPRESS_PHP8_FIXES.md)** : Correctifs PHP 8.4
- **[scripts/sync_prod_to_preprod.sh](../scripts/sync_prod_to_preprod.sh)** : Script synchronisation

---

**Auteur** : Laurent Garrigue / Claude Code
**Date** : 13 novembre 2025
**Statut** : ✅ Décision validée - Architecture monolithique retenue
