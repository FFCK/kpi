# Configuration Matomo Analytics - KPI

Ce document explique comment configurer le tracking Matomo pour le projet KPI.

## 📋 Vue d'ensemble

Le projet KPI utilise Matomo Analytics avec une configuration paramétrable permettant de définir différents Site IDs selon l'environnement (production, pré-production, développement) et selon la partie de l'application (publique ou administration).

## ⚙️ Configuration

### Fichier de configuration : `sources/commun/MyParams.php`

Trois constantes contrôlent le tracking Matomo :

```php
// Matomo Analytics
// Site ID pour la partie publique (frontend)
define('MATOMO_SITE_ID_PUBLIC', '1');

// Site ID pour la partie administration (backend)
define('MATOMO_SITE_ID_ADMIN', '2');

// URL du serveur Matomo
define('MATOMO_SERVER_URL', 'https://matomo.kayak-polo.info/');
```

### Modèle : `sources/commun/MyParams.php.modele`

Le fichier modèle contient les mêmes constantes avec des valeurs par défaut.

## 🎯 Configuration par environnement

### Production

Dans `sources/commun/MyParams.php` :

```php
define('MATOMO_SITE_ID_PUBLIC', '1');    // Site ID partie publique
define('MATOMO_SITE_ID_ADMIN', '2');     // Site ID partie admin
define('MATOMO_SERVER_URL', 'https://matomo.kayak-polo.info/');
```

### Pré-production

Dans `sources/commun/MyParams.php` :

```php
define('MATOMO_SITE_ID_PUBLIC', '3');    // Site ID unique pour préprod
define('MATOMO_SITE_ID_ADMIN', '3');     // Même ID ou ID séparé
define('MATOMO_SERVER_URL', 'https://matomo.kayak-polo.info/');
```

**Avantage** : Les statistiques de pré-production sont séparées de celles de production.

### Développement local

Dans `sources/commun/MyParams.php` :

```php
define('MATOMO_SITE_ID_PUBLIC', '4');    // Site ID pour développement
define('MATOMO_SITE_ID_ADMIN', '4');     // Ou même ID que public
define('MATOMO_SERVER_URL', 'https://matomo.kayak-polo.info/');
```

**Ou pour désactiver complètement** :

```php
// Laisser les constantes vides ou commenter les inclusions du script
```

## 📁 Templates mis à jour

### Templates partie publique (MATOMO_SITE_ID_PUBLIC)

Ces templates utilisent le Site ID pour la partie publique :

- `sources/smarty/templates/kppage.tpl`
- `sources/smarty/templates/kppageleaflet.tpl`
- `sources/smarty/templates/frame_page.tpl`
- `sources/smarty/templates/footer_ex.tpl` (section `{if $bPublic}`)

### Templates partie administration (MATOMO_SITE_ID_ADMIN)

Ces templates utilisent le Site ID pour l'administration :

- `sources/smarty/templates/pagelogin.tpl`
- `sources/smarty/templates/page.tpl`
- `sources/smarty/templates/pageMap.tpl`
- `sources/smarty/templates/kppagewide.tpl`
- `sources/smarty/templates/footer_ex.tpl` (section `{else}`)

## 🔧 Fonction helper PHP

Une fonction helper est disponible dans `sources/commun/MyTools.php` :

```php
/**
 * Génère le script Matomo Analytics
 *
 * @param string $type Type de site : 'public' pour la partie publique, 'admin' pour l'administration
 * @return string Le code JavaScript Matomo prêt à être inséré
 */
function utyGetMatomoScript($type = 'public')
```

### Utilisation dans les templates PHP

**Pour la partie publique :**
```php
<?php echo utyGetMatomoScript('public'); ?>
```

**Pour la partie administration :**
```php
<?php echo utyGetMatomoScript('admin'); ?>
```

## 📝 Syntaxe dans les templates Smarty

Les templates utilisent la syntaxe Smarty pour accéder aux constantes PHP :

```smarty
{literal}
<script>
var _paq = window._paq = window._paq || [];
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function() {
    var u="{/literal}{$smarty.const.MATOMO_SERVER_URL}{literal}";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '{/literal}{$smarty.const.MATOMO_SITE_ID_PUBLIC}{literal}']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
})();
</script>
{/literal}
```

**Note importante :** Les blocs `{/literal}...{literal}` permettent d'insérer les variables PHP au milieu du code JavaScript littéral.

## 🚀 Déploiement

### Lors du déploiement en pré-production

1. Éditer `sources/commun/MyParams.php` sur le serveur de pré-production
2. Définir les Site IDs appropriés :
   ```php
   define('MATOMO_SITE_ID_PUBLIC', '3');
   define('MATOMO_SITE_ID_ADMIN', '3');
   ```
3. Vider le cache Smarty :
   ```bash
   rm -f sources/smarty/templates_c/*.php
   ```
4. Tester l'application

### Lors du déploiement en production

1. Vérifier que `sources/commun/MyParams.php` contient :
   ```php
   define('MATOMO_SITE_ID_PUBLIC', '1');
   define('MATOMO_SITE_ID_ADMIN', '2');
   define('MATOMO_SERVER_URL', 'https://matomo.kayak-polo.info/');
   ```
2. Vider le cache Smarty :
   ```bash
   rm -f sources/smarty/templates_c/*.php
   ```
3. Déployer normalement

## 🔍 Vérification

### Vérifier la configuration actuelle

```bash
# Afficher les constantes configurées
grep "MATOMO_SITE_ID\|MATOMO_SERVER_URL" sources/commun/MyParams.php
```

### Vérifier que tous les templates utilisent les constantes

```bash
# Ne doit rien retourner (pas de Site ID en dur)
grep -r "setSiteId.*'[0-9]'" sources/smarty/templates/*.tpl

# Doit afficher 9 occurrences
grep -r "MATOMO_SITE_ID" sources/smarty/templates/*.tpl | wc -l
```

### Tester dans le navigateur

1. Ouvrir la console développeur (F12)
2. Aller sur l'onglet "Network"
3. Filtrer sur "matomo"
4. Naviguer sur le site
5. Vérifier que les requêtes vers Matomo contiennent le bon `idsite`

**Exemple d'URL Matomo :**
```
https://matomo.kayak-polo.info/matomo.php?idsite=1&rec=1&...
```

## 📊 Avantages de cette configuration

1. **Centralisée** : Toute la configuration Matomo est dans `MyParams.php`
2. **Flexible** : Différents Site IDs par environnement (prod/preprod/dev)
3. **Séparée** : Site ID différents pour public et admin
4. **Simple** : Modification en un seul endroit
5. **Maintenable** : Pas de valeurs en dur dans les templates
6. **Sécurisée** : `MyParams.php` n'est pas versionné (dans `.gitignore`)

## 🛠️ Maintenance

### Ajouter un nouveau template avec Matomo

Si vous créez un nouveau template qui doit inclure Matomo :

**Pour la partie publique :**
```smarty
{literal}
<!-- Matomo -->
<script>
var _paq = window._paq = window._paq || [];
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function() {
    var u="{/literal}{$smarty.const.MATOMO_SERVER_URL}{literal}";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '{/literal}{$smarty.const.MATOMO_SITE_ID_PUBLIC}{literal}']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
})();
</script>
<!-- End Matomo Code -->
{/literal}
```

**Pour la partie admin :**
Remplacer `MATOMO_SITE_ID_PUBLIC` par `MATOMO_SITE_ID_ADMIN`.

### Changer l'URL du serveur Matomo

Si le serveur Matomo change d'URL :

1. Éditer `sources/commun/MyParams.php`
2. Modifier uniquement la constante `MATOMO_SERVER_URL`
3. Vider le cache Smarty
4. Tous les templates seront automatiquement mis à jour

### Créer un nouveau site dans Matomo

1. Se connecter à Matomo (https://matomo.kayak-polo.info)
2. Administration → Sites web → Ajouter un nouveau site
3. Récupérer le Site ID (par exemple : 5)
4. Mettre à jour `sources/commun/MyParams.php` avec le nouveau Site ID

## 📚 Ressources

- [Documentation Matomo](https://matomo.org/docs/)
- [Matomo JavaScript Tracking Client](https://developer.matomo.org/api-reference/tracking-javascript)
- [Guide d'installation Matomo](https://matomo.org/docs/installation/)

## 🆘 Dépannage

### Le tracking ne fonctionne pas

1. Vérifier que les constantes sont bien définies dans `MyParams.php`
2. Vérifier que le cache Smarty est vidé
3. Vérifier dans la console navigateur qu'il n'y a pas d'erreur JavaScript
4. Vérifier que l'URL Matomo est accessible
5. Vérifier que le Site ID existe dans Matomo

### Les statistiques vont dans le mauvais site

1. Vérifier les valeurs de `MATOMO_SITE_ID_PUBLIC` et `MATOMO_SITE_ID_ADMIN`
2. Vérifier quel template est utilisé (public ou admin)
3. Vider le cache Smarty
4. Tester à nouveau

### Erreur "Matomo non configuré"

Si vous voyez `<!-- Matomo non configuré -->` dans le HTML :

1. Vérifier que `sources/commun/MyParams.php` existe et contient les constantes
2. Vérifier que `MyTools.php` est bien inclus
3. Vérifier les permissions des fichiers

---

**Dernière mise à jour** : 2025-01-19
**Auteur** : Équipe KPI
