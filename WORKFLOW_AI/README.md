# Documentation Technique KPI - WORKFLOW_AI

Ce dossier contient toute la documentation technique générée durant le développement et la migration du projet KPI.

---

## 📋 Index des Documents

### Migration PHP 8

- **[WORDPRESS_PHP8_FIXES.md](WORDPRESS_PHP8_FIXES.md)** ⭐ **NOUVEAU**
  - Correctifs WordPress et plugins pour PHP 8.4
  - NextGen Gallery, WordPress Core (pluggable.php, theme.php)
  - Script de réapplication automatique inclus
  - **Important** : Fichiers non versionnés, à réappliquer après mises à jour

- **[PHP8_GESTIONDOC_FIXES.md](PHP8_GESTIONDOC_FIXES.md)**
  - Corrections complètes pour GestionDoc.php en PHP 8
  - 7 corrections majeures incluant le fix critique du constructeur Smarty
  - Guide détaillé avec exemples de code

- **[SMARTY_PHP8_FIXES.md](SMARTY_PHP8_FIXES.md)**
  - Correctifs Smarty 2.6.18 pour PHP 8 (premiers patchs)
  - Remplacement de `create_function()`, fixes templates
  - Corrections PDO dans GestionDoc.php

### Migration PDF & Tableurs

- **[MIGRATION_OPENTBS_TO_OPENSPOUT.md](MIGRATION_OPENTBS_TO_OPENSPOUT.md)** ⭐ **NOUVEAU** (29 oct 2025)
  - Migration complète OpenTBS → OpenSpout v4.32.0
  - Export ODS/XLSX/CSV avec internationalisation
  - PHP 8.4+ compatible, 319 fichiers nettoyés
  - **Statut**: ✅ Production

- **[MIGRATION_FPDF_TO_MPDF.md](MIGRATION_FPDF_TO_MPDF.md)**
  - Plan complet de migration FPDF → mPDF
  - Analyse des incompatibilités et solutions

- **[MIGRATION_FPDF_MYPDF_SUCCESS.md](MIGRATION_FPDF_MYPDF_SUCCESS.md)**
  - Documentation du succès de la migration
  - Wrapper MyPDF créé pour compatibilité
  - **Statut**: ✅ Production (mPDF v8.2+)

- **[FIX_MYPDF_OPEN_METHOD.md](FIX_MYPDF_OPEN_METHOD.md)**
  - Correction méthode Open() de MyPDF
  - Gestion des appels hérités de FPDF

- **[MIGRATION_PDFMATCHMULTI_NOTES.md](MIGRATION_PDFMATCHMULTI_NOTES.md)**
  - Notes sur la migration du générateur de PDF multi-matchs

- **[PATTERN_8_IMAGES_ARRIERE_PLAN.md](PATTERN_8_IMAGES_ARRIERE_PLAN.md)**
  - Gestion des motifs de 8 images d'arrière-plan en PDF

### Migration & Architecture

- **[MIGRATION.md](MIGRATION.md)**
  - Guide général de migration PHP 7.4 → PHP 8
  - Plan de migration complet du projet

- **[README_MIGRATION.md](README_MIGRATION.md)**
  - Notes sur le processus de migration
  - État d'avancement et recommandations

### Audits & Nettoyage

- **[AUDIT_PHASE_0.md](AUDIT_PHASE_0.md)**
  - Audit initial du code (phase 0)
  - Identification des problèmes critiques

- **[AUDIT_SUMMARY.txt](AUDIT_SUMMARY.txt)**
  - Résumé textuel de l'audit
  - Statistiques et métriques

- **[CLEANUP_QUICK_WINS.md](CLEANUP_QUICK_WINS.md)**
  - Actions de nettoyage rapides
  - Quick wins identifiés lors de l'audit

### Bugs & Fixes

- **[FIX_CSV_EXPORT_OPENSPOUT.md](FIX_CSV_EXPORT_OPENSPOUT.md)** ⭐ **NOUVEAU** (29 oct 2025)
  - Fix messages "Deprecated" dans exports CSV (GestionStats)
  - Migration upload_csv.php → OpenSpout v4.32.0
  - Validation robuste, nom de fichier dynamique
  - **Statut**: ✅ Corrigé

- **[BUG_SQL_COMPET_ASTERISK.md](BUG_SQL_COMPET_ASTERISK.md)**
  - Documentation du bug SQL avec astérisque dans les compétitions
  - Solution et correctifs appliqués

### Docker & Infrastructure

- **[DOCKER_PROD_FIXES.md](DOCKER_PROD_FIXES.md)**
  - Corrections Docker pour environnement de production
  - Optimisations et bonnes pratiques

- **[DOCKERFILE_OPTIMIZATIONS.md](DOCKERFILE_OPTIMIZATIONS.md)**
  - Optimisations des Dockerfiles
  - Réduction de la taille des images, performances

### Configuration

- **[MAKEFILE_COMPOSER_UPDATES.md](MAKEFILE_COMPOSER_UPDATES.md)**
  - Mises à jour du Makefile pour Composer
  - Nouvelles commandes et améliorations

- **[MATOMO_CONFIG.md](MATOMO_CONFIG.md)**
  - Configuration de Matomo (analytics)
  - Intégration et paramétrage

- **[CRON_DOCUMENTATION.md](CRON_DOCUMENTATION.md)**
  - Documentation des tâches cron
  - Planification et maintenance

---

## 🎯 Documents par Priorité

### À lire en premier
1. **MIGRATION_OPENTBS_TO_OPENSPOUT.md** - Migration tableurs (export ODS/XLSX)
2. **PHP8_GESTIONDOC_FIXES.md** - Si vous travaillez sur la migration PHP 8
3. **MIGRATION.md** - Vue d'ensemble de la migration
4. **SMARTY_PHP8_FIXES.md** - Comprendre les premiers fixes Smarty

### Pour le développement
1. **AUDIT_PHASE_0.md** - Comprendre l'état du code
2. **CLEANUP_QUICK_WINS.md** - Améliorations rapides possibles

### Pour la maintenance
1. **CRON_DOCUMENTATION.md** - Tâches planifiées
2. **DOCKER_PROD_FIXES.md** - Gestion infrastructure

---

## 📊 Statistiques

- **Total documents**: 21 fichiers
- **Lignes de documentation**: ~6400+
- **Sujets couverts**: Migration PHP 8, PDF, Tableurs (ODS/XLSX), Docker, WordPress, Audits, Bugs
- **Date de création**: 2025-10-19 à 2025-10-29

---

## 🔄 Historique des Mises à Jour

### 2025-10-29
- ✅ Migration complète OpenTBS → OpenSpout v4.32.0
- ✅ Ajout MIGRATION_OPENTBS_TO_OPENSPOUT.md (documentation complète)
- ✅ Suppression 319 fichiers obsolètes (FPDF, OpenTBS)
- ✅ Internationalisation exports ODS avec MyLang.ini
- ✅ Fix export CSV GestionStats (warnings "Deprecated" PHP 8.4)
- ✅ Ajout FIX_CSV_EXPORT_OPENSPOUT.md
- ✅ Mise à jour AUDIT_PHASE_0.md (statut migrations)

### 2025-10-22
- ✅ Ajout WORDPRESS_PHP8_FIXES.md (correctifs WordPress + NextGen Gallery pour PHP 8.4)
- ✅ Script de réapplication automatique des correctifs WordPress
- ✅ Ajout PHP8_GESTIONDOC_FIXES.md (corrections complètes GestionDoc.php)
- ✅ Réorganisation documentation dans WORKFLOW_AI/
- ✅ Création de ce README.md d'index

### 2025-10-20
- ✅ SMARTY_PHP8_FIXES.md (premiers correctifs Smarty)
- ✅ Corrections create_function() et templates

### 2025-10-19
- ✅ Migration FPDF → mPDF réussie
- ✅ Documentation wrapper MyPDF

---

## 📝 Convention de Nommage

- **MIGRATION_*.md** : Guides de migration
- **FIX_*.md** : Documentation de correctifs spécifiques
- **BUG_*.md** : Documentation de bugs et résolutions
- **AUDIT_*.md** : Rapports d'audit de code
- **DOCKER_*.md** : Documentation infrastructure Docker
- **PHP8_*.md** : Corrections spécifiques PHP 8

---

## 🔗 Liens Utiles

- [CLAUDE.md](../CLAUDE.md) - Guide principal pour Claude Code
- [README.md](../README.md) - README principal du projet
- [Makefile](../Makefile) - Commandes de développement
- [SQL/](../SQL/) - Scripts de base de données

---

**Dernière mise à jour**: 2025-10-29
**Mainteneur**: Laurent Garrigue / Claude Code
