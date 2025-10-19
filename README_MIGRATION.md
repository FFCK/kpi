# Documentation Migration KPI

**Projet**: KPI - Système de gestion Kayak-Polo
**Date audit**: 19 octobre 2025
**Version**: Phase 0 complétée

---

## 📚 Documentation disponible

### 1. [AUDIT_PHASE_0.md](AUDIT_PHASE_0.md) - 📊 Audit complet (45 KB)

**Contenu**: Analyse exhaustive du projet avant migration

**Sections principales**:
1. Vue d'ensemble projet
2. Architecture actuelle
3. Backend PHP (9,094 fichiers)
4. Frontend (4 applications)
5. Base de données (MySQL)
6. Infrastructure Docker
7. API REST
8. Dépendances et versions
9. Analyse risques (13 risques catalogués)
10. **Tâches CRON** (import PCE, verrous)
11. **Code legacy à nettoyer**
12. **Dépendances Node** (reclassification)
13. **Architecture applicative** (clarifications)
14. Recommandations migration

**À lire en priorité**: Sections 9 (Risques) et 14 (Recommandations)

---

### 2. [CLEANUP_QUICK_WINS.md](CLEANUP_QUICK_WINS.md) - 🧹 Actions rapides (6.4 KB)

**Contenu**: Actions de nettoyage immédiates sans risque

**Quick Wins (Risque ZÉRO)**:
1. ✅ Suppression code MySQLi commenté (-90 lignes)
2. ✅ Suppression anciennes versions FPDF (-500 KB)
3. ✅ Reclassification dépendances Node (app2)

**Actions à valider**:
4. ⚠️ Validation usage OpenTBS
5. ⚠️ Validation usage EasyTimer

**Temps total**: ~20 minutes
**Gains**: -90 lignes, -500 KB, conformité best practices

**Checklist d'exécution** incluse

---

### 3. [CRON_DOCUMENTATION.md](CRON_DOCUMENTATION.md) - ⏰ Tâches automatisées (9.3 KB)

**Contenu**: Documentation complète des tâches CRON

**Tâches documentées**:

#### Import licences PCE (FFCK)
- Fichier: `cron_maj_licencies.php`
- Fréquence: Quotidienne (2h00)
- Actions: Import licenciés, arbitres, surclassements
- Source: Extranet FFCK

#### Verrouillage présences
- Fichier: `cron_verrou_presences.php`
- Fréquence: Toutes les 6h
- Règles: Verrouillage J-6, déverrouillage J+3

**Inclus**:
- Configuration CRON recommandée
- Monitoring & alerting
- Procédures d'urgence
- Migration vers Symfony Commands (Phase 2)

---

### 4. [MIGRATION.md](MIGRATION.md) - 🚀 Plan initial (27 KB)

**Contenu**: Document original de planification migration

**Note**: Complété par AUDIT_PHASE_0.md (version enrichie)

---

## 🎯 Par où commencer ?

### Si vous êtes... DÉVELOPPEUR

**Lecture recommandée** (1h):
1. [AUDIT_PHASE_0.md](AUDIT_PHASE_0.md) - Section 2 (Architecture) + Section 9 (Risques)
2. [CLEANUP_QUICK_WINS.md](CLEANUP_QUICK_WINS.md) - Actions à exécuter
3. [CRON_DOCUMENTATION.md](CRON_DOCUMENTATION.md) - Automatisations critiques

**Actions immédiates**:
- Exécuter Quick Wins (20 min)
- Tester PHP 8.x sur container kpi8
- Lire code: MyBdd.php, API router.php

---

### Si vous êtes... TECH LEAD / ARCHITECT

**Lecture recommandée** (2h):
1. [AUDIT_PHASE_0.md](AUDIT_PHASE_0.md) - Complet
2. Focus: Section 9 (Risques), Section 14 (Recommandations)
3. [CRON_DOCUMENTATION.md](CRON_DOCUMENTATION.md) - Dépendances externes

**Décisions à prendre**:
- Choix framework backend (Symfony vs Laravel)
- Priorisation Phase 1 (voir ci-dessous)
- Budget & timeline validation

---

### Si vous êtes... DEVOPS / SYSADMIN

**Lecture recommandée** (1h):
1. [AUDIT_PHASE_0.md](AUDIT_PHASE_0.md) - Section 6 (Docker) + Section 10 (CRON)
2. [CRON_DOCUMENTATION.md](CRON_DOCUMENTATION.md) - Complet
3. [CLEANUP_QUICK_WINS.md](CLEANUP_QUICK_WINS.md) - Checklist backups

**Actions immédiates**:
- Documenter `crontab -l` production
- Configurer monitoring CRON
- Tester backups (BDD + code)
- Préparer environnement preprod

---

## 📋 Roadmap Migration

### Phase 1: SÉCURITÉ & STABILITÉ (0-3 mois) 🚨

**Objectifs**: Corriger risques critiques

**Actions prioritaires**:
1. ✅ Migration PHP 8.2+ (PRIORITÉ 1)
2. ✅ Sécurisation API WSM (PRIORITÉ 2)
3. ✅ SQL Strict Mode (PRIORITÉ 3)
4. ✅ Composer.json PHP
5. ✅ Monitoring & logs

**Livrables**:
- PHP 8.2 en production
- API sécurisée (auth sur routes /wsm/*)
- MySQL 8 compatible
- Observabilité de base

---

### Phase 2: MODERNISATION BACKEND (3-6 mois) 🔧

**Objectifs**: Architecture moderne, maintenabilité

**Actions**:
1. Framework PHP moderne (Symfony/Laravel)
2. Refactoring base de données
3. API REST moderne (OpenAPI)
4. Tests automatisés (PHPUnit)

**Livrables**:
- POC migration 1 module
- API documentée (Swagger)
- Tests coverage ≥60%
- Schéma DB normalisé

---

### Phase 3: CONSOLIDATION FRONTEND (6-12 mois) 🎨

**Objectifs**: Application unique, performance

**Actions**:
1. Finalisation app2 (Nuxt 4)
2. Optimisations (code splitting, lazy loading)
3. Tests frontend (Vitest, Playwright)
4. Design system (Nuxt UI)

**Livrables**:
- App2 complète (remplacement app_dev)
- Performance ≥90/100
- Tests E2E automatisés
- UI cohérente

**Note**: app_live_dev et app_wsm_dev maintenues en Vue 3 (hors périmètre)

---

### Phase 4: INFRASTRUCTURE & DEVOPS (Parallèle) ⚙️

**Objectifs**: Déploiement moderne, scalabilité

**Actions**:
1. CI/CD complet (GitHub Actions)
2. Conteneurisation optimisée (multi-stage)
3. Monitoring complet (Prometheus, Grafana)
4. Orchestration (Docker Swarm/K8s)

**Livrables**:
- Pipeline auto (tests → build → deploy)
- Images Docker <200MB
- Observabilité 360°
- High Availability prod

---

## ⚡ Actions immédiates (Semaine 1)

### Backups
```bash
# Base de données
mysqldump -u user -p kpi_db > backup_$(date +%Y%m%d).sql

# Code source
tar -czf kpi-backup-$(date +%Y%m%d).tar.gz /path/to/kpi

# Configuration
cp docker/.env docker/.env.backup
cp docker/MyParams.php docker/MyParams.php.backup
```

### Quick Wins
```bash
cd /home/laurent/Documents/dev/kpi

# 1. Supprimer MySQLi commenté (MyBdd.php lignes 76-165)
# 2. Supprimer anciennes FPDF
rm -rf sources/lib/fpdf/ sources/lib/fpdf-1.7/

# 3. Reclassifier deps Node (voir CLEANUP_QUICK_WINS.md)
cd sources/app2
# Éditer package.json
npm install
```

### Proposition Git tag pré-migration (optionnel)

**VOUS DÉCIDEZ** si/quand faire le commit/tag/push.

**Message suggéré** (à adapter selon vos besoins):
```bash
# 1. Vérifier l'état
git status

# 2. (Optionnel) Ajouter les changements validés
# git add sources/commun/MyBdd.php sources/app2/package.json

# 3. (Optionnel) Commit si vous le souhaitez
# git commit -m "Pre-migration cleanup - Phase 0"

# 4. (Optionnel) Tag si vous voulez marquer ce point
# git tag -a v1.0-pre-migration -m "État avant migration Phase 1"

# 5. Push UNIQUEMENT si vous le décidez
# git push origin php8
# git push origin v1.0-pre-migration
```

**Note**: Toutes ces commandes sont des **suggestions**, pas des instructions automatiques.

---

## 📊 Métriques actuelles

**Volumétrie**:
- Code PHP: 9,094 fichiers
- Code app2 (Nuxt): 32,158 fichiers
- SQL migrations: 30+ fichiers (1,383 lignes)
- Templates Smarty: 88 fichiers
- Dépendances lib/: 11 bibliothèques

**Technologies**:
- Backend: PHP 7.4 (⚠️ EOL), MySQL, Apache
- Frontend: Nuxt 4, Vue 3, Tailwind CSS 4
- Infra: Docker, Traefik, Node.js

**Risques identifiés**: 13 (4 critiques 🔴, 4 élevés 🟠, 4 modérés 🟡, 1 faible 🟢)

---

## 🔗 Ressources externes

### Documentation techniques
- [Nuxt 4](https://nuxt.com/docs)
- [Symfony 7](https://symfony.com/doc/current/index.html)
- [API Platform](https://api-platform.com/docs/)
- [Docker Compose](https://docs.docker.com/compose/)

### Outils recommandés
- [Composer](https://getcomposer.org/) (dépendances PHP)
- [PHPStan](https://phpstan.org/) (analyse statique)
- [Rector](https://getrector.com/) (refactoring auto)
- [Vitest](https://vitest.dev/) (tests Vue/Nuxt)

---

## 📞 Contacts & Support

**Projet**: KPI (Kayak Polo Information)
**URL prod**: https://kayak-polo.info
**Environnement**: Docker multi-conteneurs

**Dépendances externes**:
- **FFCK Extranet**: Import PCE quotidien
- **Broker WebSocket**: app_wsm_dev → app_live_dev

**Logs importants**:
- `/var/www/html/commun/log_cron.txt` (CRON)
- `/var/www/html/commun/log_cards.txt` (Sanctions)
- Apache logs (selon config)

---

## ✅ Checklist validation audit

Avant de démarrer la migration:

- [ ] Lecture AUDIT_PHASE_0.md complète
- [ ] Compréhension architecture (4 apps indépendantes)
- [ ] Identification risques critiques (PHP 7.4 EOL, API WSM)
- [ ] Validation Quick Wins (3 actions)
- [ ] Backups complets effectués
- [ ] Tests environnement actuel (PDFs, import PCE, apps)
- [ ] Configuration CRON documentée
- [ ] Preprod opérationnelle
- [ ] Go/No-Go migration Phase 1

---

**Date audit**: 19 octobre 2025
**Auditeur**: Claude Code (automated)
**Version**: 1.1 (enrichie CRON + nettoyage + clarifications)

**Prochaine étape**: Planification détaillée Phase 1 (Sprint 1)

---

## 🎉 Résumé exécutif (TL;DR)

**Projet**: Système gestion Kayak-Polo en production, ~9k fichiers PHP legacy + 4 apps modernes

**État**: JAUNE/ORANGE ⚠️
- ✅ Infrastructure Docker solide
- ✅ Nuxt 4 en place (app2)
- 🔴 PHP 7.4 EOL (sécurité)
- 🔴 API WSM non sécurisée
- 🔴 SQL mode permissif

**Plan**: Migration progressive 12-18 mois (4 phases)

**Action immédiate**: Quick Wins (20 min) + Phase 1 démarrage

**Faisabilité**: ✅ BONNE (architecture API REST, Docker ready, app2 moderne)

**Risque principal**: Volume code legacy (gestion prudente requise)

**Recommandation**: **GO** migration progressive selon roadmap
