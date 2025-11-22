# WordPress - Migration old_prod → VPS

**Date**: 13 novembre 2025
**Contexte**: Migration WordPress depuis hébergeur PHP classique → VPS dockerisé
**Architecture**: WordPress intégré au container PHP KPI (non dockerisé séparément)
**Statut**: ✅ Guide complet

---

## 📋 Table des Matières

1. [Vue d'Ensemble](#vue-densemble)
2. [Prérequis](#prérequis)
3. [Migration old_prod → VPS](#migration-old_prod--vps)
4. [Synchronisation prod → preprod](#synchronisation-prod--preprod)
5. [Scripts d'Automatisation](#scripts-dautomatisation)
6. [Vérifications Post-Migration](#vérifications-post-migration)

---

## 🎯 Vue d'Ensemble

### Architecture Actuelle

**old_prod (Hébergeur PHP)**
- WordPress non dockerisé
- Fichiers dans `/home/user/public_html/wordpress/`
- Base de données MySQL hébergée
- Domaine : `https://kayak-polo.info`

**VPS (Docker)**
- Container PHP KPI unique (PHP 8.4)
- WordPress monté via volume : `docker/wordpress/` → `/var/www/html/wordpress`
- Base de données : Container MySQL dédié `dbwp`
- Traefik reverse proxy
- Environnements : dev, preprod, prod

### Schéma de Migration

```
old_prod (hébergeur)
    ↓
    ├─→ VPS preprod (test)
    └─→ VPS prod (production finale)
```

---

## ⚙️ Prérequis

### Sur old_prod (Hébergeur Source)

```bash
# Accès nécessaires
✅ Accès SSH ou FTP
✅ Accès phpMyAdmin ou mysqldump
✅ Identifiants base de données WordPress
```

### Sur VPS (Destination)

```bash
# Vérifier que tout fonctionne
make preprod_status   # Ou prod_status

# Variables .env configurées
✅ DBWP_ROOT_PASSWORD
✅ DBWP_NAME
✅ DBWP_USER
✅ DBWP_PASSWORD
✅ HOST_WORDPRESS_PATH=./wordpress/
✅ KPI_DOMAIN_NAME (preprod.kayak-polo.info ou kayak-polo.info)
```

---

## 🚀 Migration old_prod → VPS

### Étape 1 : Backup old_prod

#### 1.1 Export Fichiers WordPress

**Via SSH** :
```bash
# Connexion old_prod
ssh user@old_prod_host

# Créer archive WordPress
cd /home/user/public_html/
tar -czf wordpress_backup_$(date +%Y%m%d).tar.gz wordpress/

# Télécharger sur votre poste
scp user@old_prod_host:/home/user/public_html/wordpress_backup_*.tar.gz ~/Downloads/
```

**Via FTP** :
```bash
# Utiliser FileZilla ou équivalent
# Télécharger tout le dossier /wordpress/
# Estimation taille : 200 MB - 2 GB selon médias
```

**Taille estimée** :
- WordPress core : ~50 MB
- Plugins : ~50-200 MB
- Thèmes : ~20-50 MB
- Uploads (médias) : ~100 MB - 5 GB

#### 1.2 Export Base de Données

**Via phpMyAdmin** :
```sql
-- 1. Se connecter à phpMyAdmin old_prod
-- 2. Sélectionner base WordPress (ex: kpi_wordpress)
-- 3. Export → SQL → Exécuter
-- 4. Télécharger fichier .sql (~5-50 MB)
```

**Via mysqldump (SSH)** :
```bash
# Connexion old_prod
ssh user@old_prod_host

# Export BDD
mysqldump -u DB_USER -p DB_NAME > wordpress_db_backup_$(date +%Y%m%d).sql

# Télécharger
scp user@old_prod_host:/home/user/wordpress_db_backup_*.sql ~/Downloads/
```

**⚠️ Important** : Noter les valeurs dans `wp-config.php` :
```php
// old_prod/wordpress/wp-config.php
define('DB_NAME', 'kpi_wordpress');          // À noter
define('DB_USER', 'root');                    // À noter
define('DB_PASSWORD', 'xxxxx');               // À noter
define('DB_HOST', 'localhost');               // Sera 'dbwp' sur VPS
```

---

### Étape 2 : Préparer VPS Preprod

#### 2.1 Arrêter Preprod

```bash
cd /path/to/kpi_preprod/
make preprod_down
```

#### 2.2 Nettoyer Dossier WordPress Preprod (Optionnel)

```bash
# Backup WordPress preprod actuel (sécurité)
cd docker/
tar -czf wordpress_preprod_backup_$(date +%Y%m%d).tar.gz wordpress/

# Nettoyer (ou garder si migration incrémentale)
rm -rf wordpress/*
```

---

### Étape 3 : Transfert Fichiers WordPress

#### 3.1 Décompresser Archive old_prod

```bash
# Sur votre poste local
cd ~/Downloads/
tar -xzf wordpress_backup_20251113.tar.gz

# Vérifier contenu
ls -la wordpress/
# Doit contenir : wp-admin/ wp-content/ wp-includes/ wp-config.php index.php ...
```

#### 3.2 Copier vers VPS Preprod

**Via rsync (recommandé)** :
```bash
# Depuis votre poste
rsync -avz --progress \
    ~/Downloads/wordpress/ \
    user@vps_host:/path/to/kpi_preprod/docker/wordpress/

# Vérifier taille copiée
ssh user@vps_host "du -sh /path/to/kpi_preprod/docker/wordpress/"
```

**Via scp** :
```bash
# Copier archive puis décompresser sur VPS
scp wordpress_backup_20251113.tar.gz user@vps_host:/tmp/

# Sur VPS
ssh user@vps_host
cd /path/to/kpi_preprod/docker/
tar -xzf /tmp/wordpress_backup_20251113.tar.gz
```

---

### Étape 4 : Ajuster wp-config.php

#### 4.1 Éditer wp-config.php Preprod

```bash
# Sur VPS preprod
cd /path/to/kpi_preprod/docker/wordpress/
nano wp-config.php
```

#### 4.2 Modifier Connexion BDD

```php
// Remplacer les anciennes valeurs par :
define('DB_NAME', 'kpiwordpress');          // Nom BDD preprod (voir docker/.env)
define('DB_USER', 'root');                   // User preprod (voir docker/.env DBWP_USER)
define('DB_PASSWORD', 'root');               // Pass preprod (voir docker/.env DBWP_PASSWORD)
define('DB_HOST', 'dbwp_preprod');          // Container MySQL preprod
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
```

#### 4.3 Ajuster URLs WordPress

```php
// Ajouter AVANT la ligne "/* C'est tout, ne touchez pas à ce qui suit ! */"

define('WP_HOME', 'https://preprod.kayak-polo.info');
define('WP_SITEURL', 'https://preprod.kayak-polo.info/wordpress');

// Force HTTPS detection for WordPress behind reverse proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
$_SERVER['HTTPS'] = 'on';
```

#### 4.4 Désactiver Éditeur Fichiers (Sécurité)

```php
// Désactiver éditeur WordPress (sécurité)
define('DISALLOW_FILE_EDIT', true);
```

---

### Étape 5 : Import Base de Données

#### 5.1 Démarrer Container MySQL Preprod

```bash
cd /path/to/kpi_preprod/
make preprod_up

# Vérifier container MySQL
docker ps | grep dbwp_preprod
```

#### 5.2 Importer Dump SQL

```bash
# Copier dump SQL sur VPS si pas déjà fait
scp ~/Downloads/wordpress_db_backup_20251113.sql user@vps_host:/tmp/

# Sur VPS
cd /path/to/kpi_preprod/

# Importer dans container MySQL preprod
docker exec -i kpi_preprod_dbwp mysql -u root -proot kpiwordpress < /tmp/wordpress_db_backup_20251113.sql

# Vérifier import
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress -e "SHOW TABLES;"
# Doit afficher : wp_options, wp_posts, wp_users, wp_postmeta, etc.
```

---

### Étape 6 : Ajuster URLs dans Base de Données

Les URLs de old_prod (`https://kayak-polo.info`) doivent être remplacées par les URLs preprod (`https://preprod.kayak-polo.info`).

#### 6.1 Update URLs WordPress

```bash
# Sur VPS preprod
cd /path/to/kpi_preprod/

# Update URLs principales
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress -e "
UPDATE wp_options
SET option_value = 'https://preprod.kayak-polo.info'
WHERE option_name = 'home';

UPDATE wp_options
SET option_value = 'https://preprod.kayak-polo.info/wordpress'
WHERE option_name = 'siteurl';
"

# Vérifier
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress -e "
SELECT option_name, option_value
FROM wp_options
WHERE option_name IN ('home', 'siteurl');
"
```

#### 6.2 Remplacer URLs dans Contenu

```bash
# Remplacer URLs dans posts, pages, custom fields
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress -e "
-- Posts/Pages content
UPDATE wp_posts
SET post_content = REPLACE(post_content, 'https://kayak-polo.info', 'https://preprod.kayak-polo.info');

-- Posts/Pages GUIDs (optionnel, peut casser permalinks)
-- UPDATE wp_posts
-- SET guid = REPLACE(guid, 'https://kayak-polo.info', 'https://preprod.kayak-polo.info');

-- Custom Fields (postmeta)
UPDATE wp_postmeta
SET meta_value = REPLACE(meta_value, 'https://kayak-polo.info', 'https://preprod.kayak-polo.info');

-- Options diverses
UPDATE wp_options
SET option_value = REPLACE(option_value, 'https://kayak-polo.info', 'https://preprod.kayak-polo.info')
WHERE option_name NOT IN ('home', 'siteurl');
"
```

**⚠️ Attention GUID** : Ne modifier `guid` que si nécessaire (peut casser les flux RSS).

---

### Étape 7 : Redémarrer Preprod Complet

```bash
cd /path/to/kpi_preprod/
make preprod_restart

# Vérifier logs
make preprod_logs
# Attendre : "Apache/2.4.x configured -- resuming normal operations"

# Vérifier containers
docker ps | grep preprod
# Doit afficher : kpi_preprod_php, kpi_preprod_db, kpi_preprod_dbwp
```

---

### Étape 8 : Tests Preprod

#### 8.1 Accès WordPress

```bash
# Ouvrir navigateur
https://preprod.kayak-polo.info/wordpress/

# Vérifier :
✅ Page d'accueil s'affiche
✅ Images chargent (uploads/)
✅ Menu fonctionne
✅ Pages internes OK
```

#### 8.2 Login Admin

```bash
# Connexion admin
https://preprod.kayak-polo.info/wordpress/wp-admin

# Vérifier :
✅ Login fonctionne (même user/pass old_prod)
✅ Tableau de bord OK
✅ Plugins actifs
✅ Thème actif
✅ Médias bibliothèque OK
```

#### 8.3 Tests Fonctionnels

```bash
✅ Créer un article test
✅ Uploader une image test
✅ Modifier une page existante
✅ Vérifier permaliens
✅ Tester formulaires (si contact form)
✅ Vérifier liens vers pages PHP KPI (/kpcalendrier.php, etc.)
```

---

### Étape 9 : Migration Prod (Après Validation Preprod)

**⚠️ NE PAS FAIRE AVANT VALIDATION COMPLÈTE PREPROD**

#### 9.1 Répéter Étapes 2-8 pour Prod

```bash
# Remplacer tous les chemins preprod par prod :
/path/to/kpi_preprod/  →  /path/to/kpi_prod/
kpi_preprod_           →  kpi_
preprod.kayak-polo.info → kayak-polo.info
dbwp_preprod           →  dbwp
```

#### 9.2 DNS Update (Critique)

```bash
# 1. Vérifier que prod fonctionne en local via /etc/hosts
# 2. Mettre à jour DNS pour pointer vers VPS
# 3. Attendre propagation DNS (24-48h)
# 4. Tester avec outils externes (whatsmydns.net)
```

---

## 🔄 Synchronisation prod → preprod

### Objectif

Rafraîchir périodiquement preprod avec données prod (pour tests réalistes).

### Méthode Manuelle

#### Étape 1 : Backup Prod

```bash
cd /path/to/kpi_prod/

# Backup fichiers WordPress
tar -czf /tmp/wordpress_prod_$(date +%Y%m%d).tar.gz docker/wordpress/

# Export BDD prod
docker exec kpi_dbwp mysqldump -u root -proot kpiwordpress > /tmp/wordpress_prod_db_$(date +%Y%m%d).sql
```

#### Étape 2 : Arrêter Preprod

```bash
cd /path/to/kpi_preprod/
make preprod_down
```

#### Étape 3 : Backup Preprod (Sécurité)

```bash
tar -czf docker/wordpress_preprod_backup_$(date +%Y%m%d).tar.gz docker/wordpress/
```

#### Étape 4 : Copier Fichiers Prod → Preprod

```bash
# Copier fichiers WordPress
rsync -av --delete \
    --exclude 'wp-config.php' \
    --exclude '.htaccess' \
    --exclude 'wp-content/cache/' \
    /path/to/kpi_prod/docker/wordpress/ \
    /path/to/kpi_preprod/docker/wordpress/

# Restaurer wp-config.php preprod (ne pas écraser)
# (Normalement exclu ci-dessus)
```

#### Étape 5 : Importer BDD Prod → Preprod

```bash
cd /path/to/kpi_preprod/

# Démarrer MySQL preprod
docker compose -f docker/compose.preprod.yaml up -d dbwp_preprod

# Attendre démarrage
sleep 10

# Drop et recréer BDD preprod
docker exec kpi_preprod_dbwp mysql -u root -proot -e "
DROP DATABASE IF EXISTS kpiwordpress;
CREATE DATABASE kpiwordpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
"

# Importer dump prod
docker exec -i kpi_preprod_dbwp mysql -u root -proot kpiwordpress < /tmp/wordpress_prod_db_$(date +%Y%m%d).sql
```

#### Étape 6 : Ajuster URLs Preprod

```bash
# Remplacer URLs prod → preprod
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress -e "
UPDATE wp_options SET option_value = 'https://preprod.kayak-polo.info' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'https://preprod.kayak-polo.info/wordpress' WHERE option_name = 'siteurl';

UPDATE wp_posts SET post_content = REPLACE(post_content, 'https://kayak-polo.info', 'https://preprod.kayak-polo.info');
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'https://kayak-polo.info', 'https://preprod.kayak-polo.info');
"
```

#### Étape 7 : Redémarrer Preprod

```bash
cd /path/to/kpi_preprod/
make preprod_up
```

---

## 🤖 Scripts d'Automatisation

### Script sync_prod_to_preprod.sh

Voir fichier : [`scripts/sync_prod_to_preprod.sh`](../scripts/sync_prod_to_preprod.sh)

**Fonctionnalités** :
- Backup automatique preprod avant écrasement
- Copie fichiers prod → preprod (rsync)
- Export/Import BDD
- Ajustement automatique URLs
- Gestion erreurs
- Confirmation utilisateur

**Usage** :
```bash
# Éditer variables (si première utilisation)
nano scripts/sync_prod_to_preprod.sh
# Configurer PREPROD_PATH

# Exécuter
./scripts/sync_prod_to_preprod.sh

# Ou via chemin absolu
/path/to/kpi_prod/scripts/sync_prod_to_preprod.sh
```

---

## ✅ Vérifications Post-Migration

### Checklist Complète

#### Fichiers WordPress

```bash
- [ ] Dossier wp-admin/ présent
- [ ] Dossier wp-content/ présent
- [ ] Dossier wp-includes/ présent
- [ ] Fichier wp-config.php correctement configuré
- [ ] Fichier index.php présent
- [ ] Médias accessibles (wp-content/uploads/)
- [ ] Plugins présents (wp-content/plugins/)
- [ ] Thèmes présents (wp-content/themes/)
```

#### Base de Données

```bash
# Vérifier tables
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress -e "SHOW TABLES;"

- [ ] Table wp_options présente
- [ ] Table wp_posts présente
- [ ] Table wp_users présente
- [ ] Table wp_postmeta présente
- [ ] URLs correctes dans wp_options (home, siteurl)
- [ ] Contenu posts OK
```

#### Configuration

```bash
- [ ] wp-config.php : DB_HOST = 'dbwp_preprod' (ou 'dbwp' prod)
- [ ] wp-config.php : WP_HOME correct
- [ ] wp-config.php : WP_SITEURL correct
- [ ] wp-config.php : HTTPS forcé
- [ ] .env : HOST_WORDPRESS_PATH = ./wordpress/
- [ ] .env : DBWP_NAME correct
```

#### Tests Fonctionnels

```bash
- [ ] Accès page d'accueil : https://preprod.kayak-polo.info/wordpress/
- [ ] Login admin : https://preprod.kayak-polo.info/wordpress/wp-admin
- [ ] Chargement images OK
- [ ] Navigation menu OK
- [ ] Pages internes OK
- [ ] Liens vers PHP KPI OK (/kpcalendrier.php, etc.)
- [ ] Formulaires OK (contact, recherche)
- [ ] Plugins actifs
- [ ] Thème affiché correctement
```

#### Containers Docker

```bash
- [ ] Container PHP running
- [ ] Container dbwp running
- [ ] Volume wordpress/ monté correctement
- [ ] Logs containers sans erreurs
- [ ] Traefik routing OK
```

---

## 🔧 Dépannage

### Problème 1 : Page blanche WordPress

**Symptôme** : Page blanche, pas d'erreur affichée

**Solution** :
```bash
# Activer debug WordPress
nano docker/wordpress/wp-config.php

# Ajouter :
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

# Vérifier logs
docker logs kpi_preprod_php
tail -f docker/wordpress/wp-content/debug.log
```

### Problème 2 : Erreur "Error establishing database connection"

**Symptôme** : WordPress ne se connecte pas à la BDD

**Solution** :
```bash
# Vérifier container MySQL
docker ps | grep dbwp

# Tester connexion
docker exec kpi_preprod_dbwp mysql -u root -proot -e "SHOW DATABASES;"

# Vérifier wp-config.php
grep DB_HOST docker/wordpress/wp-config.php
# Doit être : define('DB_HOST', 'dbwp_preprod');

# Vérifier mot de passe
grep DBWP docker/.env
```

### Problème 3 : Redirections infinies

**Symptôme** : Boucle de redirections, page ne charge jamais

**Solution** :
```bash
# Vérifier URLs dans BDD
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress -e "
SELECT option_name, option_value FROM wp_options WHERE option_name IN ('home', 'siteurl');
"

# Corriger si besoin
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress -e "
UPDATE wp_options SET option_value = 'https://preprod.kayak-polo.info' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'https://preprod.kayak-polo.info/wordpress' WHERE option_name = 'siteurl';
"

# Vider cache WordPress
rm -rf docker/wordpress/wp-content/cache/*
```

### Problème 4 : Images 404

**Symptôme** : Images cassées, erreur 404 sur uploads/

**Solution** :
```bash
# Vérifier dossier uploads présent
ls -la docker/wordpress/wp-content/uploads/

# Vérifier permissions
chmod -R 755 docker/wordpress/wp-content/uploads/

# Vérifier URLs images dans BDD
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress -e "
SELECT guid FROM wp_posts WHERE post_type = 'attachment' LIMIT 5;
"
# Si URLs incorrectes, relancer UPDATE wp_posts
```

### Problème 5 : Login admin ne fonctionne pas

**Symptôme** : Identifiants refusés

**Solution** :
```bash
# Réinitialiser mot de passe admin
docker exec kpi_preprod_dbwp mysql -u root -proot kpiwordpress

# Dans MySQL :
UPDATE wp_users SET user_pass = MD5('nouveau_mot_de_passe') WHERE user_login = 'admin';
EXIT;

# Ou utiliser WP-CLI si disponible
docker exec -it kpi_preprod_php wp user update admin --user_pass=nouveau_mdp --allow-root
```

---

## 📊 Métriques Migration

### Temps Estimés

| Étape | Durée | Note |
|-------|-------|------|
| Backup old_prod | 10-30 min | Selon taille uploads |
| Transfert fichiers | 20-60 min | Selon bande passante |
| Import BDD | 5-15 min | Selon taille BDD |
| Configuration | 10-20 min | |
| Tests | 30-60 min | |
| **TOTAL** | **1h30 - 3h** | Migration complète |

### Volumétrie Typique

| Donnée | Taille | Impact |
|--------|--------|--------|
| WordPress core | ~50 MB | Rapide |
| Plugins | ~100 MB | Moyen |
| Thèmes | ~30 MB | Rapide |
| Uploads (médias) | 500 MB - 5 GB | **Critique** |
| Base de données | 10 MB - 100 MB | Rapide |

---

## 🎯 Recommandations

### Sécurité

1. **Toujours faire backup avant migration**
2. **Tester en preprod AVANT prod**
3. **Ne jamais commiter wp-config.php**
4. **Désactiver édition fichiers WordPress** (`DISALLOW_FILE_EDIT`)
5. **Utiliser mots de passe forts BDD**
6. **Limiter accès phpMyAdmin**

### Performance

1. **Activer cache WordPress** (plugin WP Super Cache)
2. **Optimiser images** (plugin Smush)
3. **Nettoyer BDD régulièrement** (wp-optimize)
4. **Monitoring logs Apache/PHP**

### Maintenance

1. **Synchroniser prod → preprod mensuellement**
2. **Backup hebdomadaire automatique**
3. **Tester mises à jour plugins en preprod d'abord**
4. **Vérifier espace disque régulièrement**

---

**Auteur** : Laurent Garrigue / Claude Code
**Date création** : 13 novembre 2025
**Version** : 1.0
**Statut** : ✅ Guide complet - WordPress intégré container PHP
