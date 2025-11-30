# Migration PHP 8 - Guide de Bascule Docker

**Date**: 31 octobre 2025
**Objectif**: Basculer le container principal de PHP 7.4.33 vers PHP 8.4.13
**Statut**: 📋 **GUIDE PRÊT** - En attente d'exécution

---

## 📊 Vue d'Ensemble

### Configuration Actuelle

| Container | PHP Version | Port | Domaine | Status |
|-----------|-------------|------|---------|--------|
| **kpi** | 7.4.33 | 80 | `${KPI_DOMAIN_NAME}` | 🔴 Production |
| **kpi8** | 8.4.13 | 88 | `${KPI_DOMAIN_NAME_8}` | ✅ Tests |

### Configuration Cible

| Container | PHP Version | Port | Domaine | Status |
|-----------|-------------|------|---------|--------|
| **kpi** | 8.4.13 | 80 | `${KPI_DOMAIN_NAME}` | ✅ Production PHP 8 |
| ~~**kpi8**~~ | ~~8.4.13~~ | ~~88~~ | ~~Supprimé~~ | ❌ Obsolète |

---

## 🎯 Stratégie de Migration

### Option 1 : Bascule Directe (RECOMMANDÉ)

**Principe** : Modifier le container `kpi` pour utiliser PHP 8.4

**Avantages** :
- ✅ Pas de changement d'URL/domaine
- ✅ Rollback simple (restaurer .env)
- ✅ Moins de configuration réseau

**Inconvénients** :
- ⚠️ Downtime pendant rebuild (~2 min)
- ⚠️ Nécessite tests finaux avant bascule

**Recommandation** : **À privilégier pour production**

---

### Option 2 : Swap Containers

**Principe** : Inverser les rôles de `kpi` et `kpi8`

**Avantages** :
- ✅ Rollback instantané (swap inverse)
- ✅ Zéro downtime (avec load balancer)

**Inconvénients** :
- ⚠️ Configuration Traefik à modifier
- ⚠️ Logs dupliqués temporairement
- ⚠️ Plus complexe à maintenir

**Recommandation** : **À éviter sauf contrainte downtime**

---

## 📋 Procédure Option 1 : Bascule Directe

### Phase 1 : Préparation (J-7)

#### 1.1 Vérification Tests Complets

```bash
# Se connecter au container PHP 8
make php8_bash

# Vérifier version
php -v
# Output attendu: PHP 8.4.13

# Tester modules critiques manuellement
# - Import PCE
# - Génération PDF
# - Exports ODS/XLSX
# - Pages Smarty
# - API REST
```

**Checklist complète** : [PHP8_TESTING_CHECKLIST.md](PHP8_TESTING_CHECKLIST.md)

---

#### 1.2 Backup Complet

```bash
# Backup base de données
docker exec kpi_db mysqldump -u root -p${DB_ROOT_PASSWORD} kpi_db > backup_prod_$(date +%Y%m%d_%H%M%S).sql

# Backup code (optionnel si Git à jour)
tar -czf kpi_backup_$(date +%Y%m%d_%H%M%S).tar.gz \
    /home/laurent/Documents/dev/kpi/sources \
    /home/laurent/Documents/dev/kpi/docker/.env \
    /home/laurent/Documents/dev/kpi/docker/MyParams.php \
    /home/laurent/Documents/dev/kpi/docker/MyConfig.php

# Backup configuration Docker
cp docker/.env docker/.env.backup_$(date +%Y%m%d)
```

---

#### 1.3 Documentation État Actuel

```bash
# Vérifier variables .env actuelles
cd docker
grep "BASE_IMAGE_PHP" .env

# Output attendu:
# BASE_IMAGE_PHP=php:7.4-apache
# BASE_IMAGE_PHP_8=php:8.4-apache
```

---

### Phase 2 : Modification Configuration (J-1)

#### 2.1 Modifier docker/.env

**Fichier** : `docker/.env`

**Avant** :
```bash
# PHP 7.4 Container
BASE_IMAGE_PHP=php:7.4-apache
PHP_CONTAINER_NAME=kpi_php

# PHP 8.4 Container (tests)
BASE_IMAGE_PHP_8=php:8.4-apache
```

**Après** :
```bash
# PHP 8.4 Container (PRODUCTION)
BASE_IMAGE_PHP=php:8.4-apache
PHP_CONTAINER_NAME=kpi_php

# PHP 8.4 Ancienne variable (gardée pour référence)
# BASE_IMAGE_PHP_8=php:8.4-apache
```

**⚠️ IMPORTANT** : Une seule ligne à modifier : `BASE_IMAGE_PHP`

---

#### 2.2 Modifier compose.dev.yaml (Optionnel)

**Fichier** : `docker/compose.dev.yaml`

**Option A** : Supprimer le container `kpi8` (recommandé après validation)

```yaml
# Commenter ou supprimer toute la section kpi8 (lignes 38-72)
# services:
#     kpi:
#         ...
#     # kpi8:  <-- Supprimer cette section complète
#     #     ...
#     db:
#         ...
```

**Option B** : Garder `kpi8` comme rollback temporaire

Laisser la configuration intacte pour rollback rapide (1-2 semaines).

**Recommandation** : **Option B** pendant période de stabilisation

---

### Phase 3 : Déploiement (J-Day)

#### 3.1 Stop Containers

```bash
cd /home/laurent/Documents/dev/kpi

# Arrêter tous les containers
make dev_down

# Vérifier arrêt
docker ps | grep kpi
# Output: (vide)
```

---

#### 3.2 Rebuild avec PHP 8

```bash
# Rebuild avec nouvelle configuration
make dev_rebuild

# Attendre fin du build (~2-3 minutes)
# ...
```

**Logs attendus** :
```
Building kpi
[+] Building 45.2s (12/12) FINISHED
 => [internal] load build definition from Dockerfile.dev.web
 => => transferring dockerfile: 2.14kB
 => [internal] load .dockerignore
 => CACHED [1/7] FROM php:8.4-apache
 ...
```

---

#### 3.3 Vérification Post-Déploiement

```bash
# Vérifier containers actifs
make dev_status

# Output attendu:
# CONTAINER ID   IMAGE              STATUS         PORTS                  NAMES
# xxxxx          kpi-kpi            Up 2 minutes   0.0.0.0:80->80/tcp     kpi_php
# xxxxx          mysql:8            Up 2 minutes   3306/tcp               kpi_db
# ...

# Vérifier version PHP
make php_bash
php -v

# Output attendu:
# PHP 8.4.13 (cli) (built: Sep 29 2025 23:58:07) (NTS)
```

---

#### 3.4 Tests Fonctionnels Rapides

**Tests critiques (5-10 minutes)** :

1. **Page Login**
   ```
   URL: https://kpi.localhost/
   Test: Connexion admin réussie
   ```

2. **Page Backend**
   ```
   URL: https://kpi.localhost/admin/
   Test: Dashboard s'affiche correctement
   ```

3. **GestionAthlete**
   ```
   URL: https://kpi.localhost/admin/GestionAthlete.php
   Test: Liste athlètes, pas d'erreur JS
   ```

4. **API Test**
   ```bash
   curl https://kpi.localhost/api/test.php
   # Vérifier réponse JSON
   ```

5. **Console JavaScript**
   ```
   F12 > Console
   Test: Aucune erreur ReferenceError ou TypeError
   ```

**Checklist complète** : [PHP8_TESTING_CHECKLIST.md](PHP8_TESTING_CHECKLIST.md)

---

### Phase 4 : Rollback (Si Nécessaire)

#### 4.1 Rollback Rapide (.env)

```bash
# Restaurer .env original
cd docker
cp .env.backup_YYYYMMDD .env

# Rebuild avec PHP 7.4
make dev_rebuild

# Vérifier version
make php_bash
php -v
# Output: PHP 7.4.33
```

**Durée rollback** : ~3-5 minutes

---

#### 4.2 Rollback Base de Données (Si Corruption)

```bash
# Arrêter containers
make dev_down

# Restaurer BDD
docker exec -i kpi_db mysql -u root -p${DB_ROOT_PASSWORD} kpi_db < backup_prod_YYYYMMDD_HHMMSS.sql

# Redémarrer
make dev_up
```

**Durée rollback BDD** : ~10-15 minutes (selon taille)

---

## 🚀 Déploiement Production

### Différences Développement vs Production

| Aspect | Développement | Production |
|--------|---------------|------------|
| **Fichier compose** | `compose.dev.yaml` | `compose.prod.yaml` |
| **Commande** | `make dev_rebuild` | `make prod_rebuild` |
| **Domaine** | `kpi.localhost` | `kayak-polo.info` |
| **HTTPS** | Auto-signé | Let's Encrypt |
| **Logs** | Verbose | Erreurs uniquement |

### Procédure Production

```bash
# 1. Fenêtre de maintenance
# Planifier: Dimanche 2h-6h AM (trafic minimal)

# 2. Backup production
ssh user@production-server
cd /path/to/kpi
make prod_down
# Backup BDD + code (voir Phase 2.1)

# 3. Modifier docker/.env
nano docker/.env
# BASE_IMAGE_PHP=php:8.4-apache

# 4. Rebuild
make prod_rebuild

# 5. Tests post-deploy
# Exécuter checklist complète

# 6. Monitoring 24h
# Surveiller logs, erreurs, performance
```

---

## 📊 Checklist Complète Bascule

### Avant Migration

- [ ] Tous les tests passent sur `kpi8` (PHP 8.4)
- [ ] Backup BDD complet effectué
- [ ] Backup code source effectué
- [ ] Backup configuration Docker (.env, MyParams, MyConfig)
- [ ] Documentation rollback lue et comprise
- [ ] Fenêtre de maintenance planifiée (si prod)
- [ ] Équipe informée de la migration

### Pendant Migration

- [ ] Variables .env modifiées (`BASE_IMAGE_PHP`)
- [ ] Containers stoppés (`make dev_down`)
- [ ] Rebuild effectué (`make dev_rebuild`)
- [ ] Version PHP 8.4 confirmée (`php -v`)
- [ ] Containers démarrés correctement
- [ ] Logs sans erreurs critiques

### Après Migration

- [ ] Page Login fonctionnelle
- [ ] Page Backend accessible
- [ ] GestionAthlete sans erreur JS
- [ ] API REST fonctionnelle
- [ ] CRON jobs testés (import PCE)
- [ ] Génération PDF validée
- [ ] Exports ODS/XLSX validés
- [ ] Console JavaScript propre
- [ ] Monitoring actif 24-48h

### Validation Finale (J+7)

- [ ] Aucun bug critique remonté
- [ ] Performance stable ou améliorée
- [ ] Logs propres (warnings acceptables)
- [ ] Rollback non nécessaire
- [ ] Container `kpi8` peut être supprimé
- [ ] Documentation à jour

---

## 🔧 Commandes Utiles

### Vérification Version PHP

```bash
# Dans le container
make php_bash
php -v

# Depuis l'hôte
docker exec kpi_php php -v
```

### Logs en Temps Réel

```bash
# Tous les containers
make dev_logs

# Container PHP uniquement
docker logs -f kpi_php

# Dernières 100 lignes
docker logs --tail 100 kpi_php
```

### Rebuild Sans Cache

```bash
# Force rebuild complet
docker compose -f docker/compose.dev.yaml build --no-cache kpi
make dev_up
```

### Comparaison PHP 7.4 vs 8.4

```bash
# PHP 7.4
docker exec kpi_php php -i | grep "PHP Version"

# PHP 8.4
docker exec kpi_php8 php -i | grep "PHP Version"
```

---

## 🚨 Problèmes Courants et Solutions

### 1. Container ne démarre pas

**Symptôme** :
```
Error: OCI runtime create failed
```

**Solution** :
```bash
# Vérifier logs build
docker logs kpi_php

# Reconstruire sans cache
make dev_rebuild --no-cache
```

---

### 2. Permission denied

**Symptôme** :
```
Permission denied: /var/www/html/...
```

**Solution** :
```bash
# Vérifier USER_ID dans .env
grep USER_ID docker/.env

# Reconstruire avec bon user
make dev_rebuild
```

---

### 3. Extensions PHP manquantes

**Symptôme** :
```
Call to undefined function imagecreatetruecolor()
```

**Solution** :
```bash
# Vérifier extensions installées
make php_bash
php -m | grep gd

# Si manquante, vérifier Dockerfile.dev.web
# Ligne 23: docker-php-ext-install ... gd
```

---

### 4. Composer non trouvé

**Symptôme** :
```
composer: command not found
```

**Solution** :
```bash
# Vérifier installation Composer
make php_bash
which composer

# Réinstaller si nécessaire (dans Dockerfile)
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
```

---

## 📚 Documentation Connexe

### Migration PHP 8
1. [PHP8_MIGRATION_SUMMARY.md](PHP8_MIGRATION_SUMMARY.md) - Synthèse complète
2. [PHP8_TESTING_CHECKLIST.md](PHP8_TESTING_CHECKLIST.md) - Tests détaillés
3. [PHP8_ROLLBACK_PROCEDURE.md](PHP8_ROLLBACK_PROCEDURE.md) - Procédure rollback

### Migrations Bibliothèques
4. [MIGRATION_FPDF_MYPDF_SUCCESS.md](MIGRATION_FPDF_MYPDF_SUCCESS.md)
5. [MIGRATION_OPENTBS_TO_OPENSPOUT.md](MIGRATION_OPENTBS_TO_OPENSPOUT.md)
6. [MIGRATION_SMARTY_V4.md](MIGRATION_SMARTY_V4.md)

### Correctifs PHP 8
7. [PHP8_GESTIONDOC_FIXES.md](PHP8_GESTIONDOC_FIXES.md)
8. [WORDPRESS_PHP8_FIXES.md](WORDPRESS_PHP8_FIXES.md)
9. [SMARTY_PHP8_FIXES.md](SMARTY_PHP8_FIXES.md)

---

## ✅ Conclusion

### Résumé

La bascule Docker PHP 7.4 → PHP 8.4 est **simple et réversible** :
1. Modifier 1 ligne dans `docker/.env`
2. Rebuild containers (3 minutes)
3. Tester fonctionnalités critiques
4. Rollback en 3 minutes si problème

### Timeline Recommandée

| Phase | Durée | Actions |
|-------|-------|---------|
| **J-7** | 1 jour | Tests finaux sur `kpi8` |
| **J-1** | 2h | Backups + préparation |
| **J-Day** | 30 min | Bascule + tests rapides |
| **J+1 à J+7** | 1 semaine | Monitoring intensif |
| **J+30** | - | Suppression `kpi8` (optionnel) |

### Recommandation Finale

**GO pour bascule** après validation complète checklist tests.

**Risque** : ⚠️ **FAIBLE** (rollback 3 min, backups complets)

**Impact** : ✅ **POSITIF** (performance, sécurité, pérennité)

---

**Auteur**: Laurent Garrigue / Claude Code
**Date**: 31 octobre 2025
**Version**: 1.0
**Statut**: 📋 **GUIDE PRÊT**
