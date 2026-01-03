# Documentation KPI - Index Principal

Cette documentation est organisée en deux sections principales :

## 📘 [Documentation Utilisateur](user/)

Documentation orientée utilisateurs finaux et fonctionnalités du système.

### Fonctionnalités Principales
- **[EVENT_CACHE_MANAGER.md](user/EVENT_CACHE_MANAGER.md)** - Event Cache Manager - Worker en arrière-plan pour incrustations vidéo
- **[IMAGE_UPLOAD_MANAGEMENT.md](user/IMAGE_UPLOAD_MANAGEMENT.md)** - Upload et gestion d'images (logos, photos)
- **[TEAM_COMPOSITION_COPY.md](user/TEAM_COMPOSITION_COPY.md)** - Copie de composition d'équipe entre compétitions
- **[MATCH_DAY_BULK_OPERATIONS.md](user/MATCH_DAY_BULK_OPERATIONS.md)** - Opérations de masse sur les matchs (publication, validation, suppression)
- **[BULK_COMPETITION_COPY.md](user/BULK_COMPETITION_COPY.md)** - Copie en masse de compétitions entre saisons (avec journées et matchs)

### Fonctionnalités Spécifiques
- **[MATCH_CONSISTENCY_STATS.md](user/MATCH_CONSISTENCY_STATS.md)** - Statistiques de cohérence des matchs
- **[CONSOLIDATION_PHASES_CLASSEMENT.md](user/CONSOLIDATION_PHASES_CLASSEMENT.md)** - Consolidation des phases de classement
- **[MULTI_COMPETITION_TYPE.md](user/MULTI_COMPETITION_TYPE.md)** - Type de compétition MULTI (agrégation multi-compétitions)
- **[DOCVIEWER_GUIDE.md](user/DOCVIEWER_GUIDE.md)** - Guide du visualiseur de documentation
- **[NOUVEAUTES.md](user/NOUVEAUTES.md)** - Dernières nouveautés et fonctionnalités ajoutées
- **[CRON_DOCUMENTATION.md](user/CRON_DOCUMENTATION.md)** - Tâches planifiées automatiques

Voir [user/README.md](user/README.md) pour plus de détails.

## 💻 [Documentation Développeur](developer/)

Documentation technique pour le développement et la maintenance du projet.

### [Référence](developer/reference/)
- **[KPI_FUNCTIONALITY_INVENTORY.md](developer/reference/KPI_FUNCTIONALITY_INVENTORY.md)** - Inventaire complet des fonctionnalités (~7000 lignes)
- **[APP2_TECHNICAL_ARCHITECTURE.md](developer/reference/APP2_TECHNICAL_ARCHITECTURE.md)** - Architecture technique complète de l'application web (stack, PWA, gestion erreurs, API)
- **[API2_ENDPOINTS.md](developer/reference/API2_ENDPOINTS.md)** - Documentation complète API2 (Symfony 7.3 + API Platform 4.2)

### [Guides](developer/guides/)

#### [Migrations](developer/guides/migrations/)
- **[CONSOLIDATION_TRADUCTIONS.md](developer/guides/migrations/CONSOLIDATION_TRADUCTIONS.md)** - Consolidation fichiers de traductions (MyLang.conf + MyLang.ini)
- **[MIGRATION_FPDF_TO_MPDF.md](developer/guides/migrations/MIGRATION_FPDF_TO_MPDF.md)** - Migration FPDF → mPDF
- **[MIGRATION_SMARTY_V4.md](developer/guides/migrations/MIGRATION_SMARTY_V4.md)** - Migration Smarty v4
- **[MIGRATION_OPENTBS_TO_OPENSPOUT.md](developer/guides/migrations/MIGRATION_OPENTBS_TO_OPENSPOUT.md)** - Migration OpenTBS → OpenSpout
- **[FLATPICKR_MIGRATION_GUIDE.md](developer/guides/migrations/FLATPICKR_MIGRATION_GUIDE.md)** - Migration dhtmlgoodies → Flatpickr
- **[AUTOCOMPLETE_MIGRATION_GUIDE.md](developer/guides/migrations/AUTOCOMPLETE_MIGRATION_GUIDE.md)** - Migration autocomplete
- **[MIGRATION_AXIOS_FETCH_GUIDE.md](developer/guides/migrations/MIGRATION_AXIOS_FETCH_GUIDE.md)** - Migration Axios → fetch()
- **[AXIOS_TO_FETCH_MIGRATION.md](developer/guides/migrations/AXIOS_TO_FETCH_MIGRATION.md)** - Analyse technique Axios → fetch()
- **[QRCODE_MIGRATION.md](developer/guides/migrations/QRCODE_MIGRATION.md)** - Migration QR Code
- **[MIGRATION_PDFMATCHMULTI_NOTES.md](developer/guides/migrations/MIGRATION_PDFMATCHMULTI_NOTES.md)** - Notes migration PDF multi-matchs

#### [Infrastructure](developer/infrastructure/)
- **[NGINX_STATIC_APP_DEPLOYMENT.md](developer/infrastructure/NGINX_STATIC_APP_DEPLOYMENT.md)** - ✅ Déploiement app2/app3 via Nginx (SSG, builds dev/prod, containers temporaires)
- **[CORS_CONFIGURATION.md](developer/infrastructure/CORS_CONFIGURATION.md)** - ✅ Configuration CORS globale via PHP auto-prepend (tous endpoints)
- **[CACHE_BUSTING_STRATEGY.md](developer/infrastructure/CACHE_BUSTING_STRATEGY.md)** - ✅ Stratégie cache busting avec buildId timestamp (app2/app3)
- **[MAKEFILE_MULTI_ENVIRONMENT.md](developer/guides/infrastructure/MAKEFILE_MULTI_ENVIRONMENT.md)** - Gestion multi-environnements (dev, preprod, prod)
- **[NPM_BACKEND_PRODUCTION_GUIDE.md](developer/guides/infrastructure/NPM_BACKEND_PRODUCTION_GUIDE.md)** - NPM pour backend PHP
- **[TOOLTIP_TESTING_GUIDE.md](developer/guides/infrastructure/TOOLTIP_TESTING_GUIDE.md)** - Guide de test tooltips

#### [Bonnes Pratiques](developer/guides/)
- **[BEST_PRACTICES_JAVASCRIPT_SMARTY.md](developer/guides/BEST_PRACTICES_JAVASCRIPT_SMARTY.md)** - Bonnes pratiques JavaScript & Smarty (traductions, JSON, constructeurs)

#### [Fonctionnalités](developer/guides/features/)
- **[COMPETITION_TYPE_MULTI.md](developer/guides/features/COMPETITION_TYPE_MULTI.md)** - Documentation développeur type MULTI (héritée, voir version technique ci-dessous)
- **[COMPETITION_TYPE_MULTI_TECHNICAL.md](developer/guides/features/COMPETITION_TYPE_MULTI_TECHNICAL.md)** - Documentation technique complète compétitions MULTI et éditeur de grille

### [Travaux en cours](developer/in-progress/)

#### [Statuts des migrations](developer/in-progress/status/)
- **[BOOTSTRAP_MIGRATION_STATUS.md](developer/in-progress/status/BOOTSTRAP_MIGRATION_STATUS.md)** - ⏳ Migration Bootstrap 5.3.8
- **[FLATPICKR_MIGRATION_STATUS.md](developer/in-progress/status/FLATPICKR_MIGRATION_STATUS.md)** - ⏳ Migration Flatpickr
- **[TOOLTIP_MIGRATION_STATUS.md](developer/in-progress/status/TOOLTIP_MIGRATION_STATUS.md)** - ⏳ Migration tooltips
- **[MASKED_INPUT_MIGRATION_STATUS.md](developer/in-progress/status/MASKED_INPUT_MIGRATION_STATUS.md)** - ⏳ Migration masked input

#### [Plans d'action](developer/in-progress/plans/)
- **[JQUERY_ELIMINATION_STRATEGY.md](developer/in-progress/plans/JQUERY_ELIMINATION_STRATEGY.md)** - Stratégie élimination jQuery
- **[JS_LIBRARIES_CLEANUP_PLAN.md](developer/in-progress/plans/JS_LIBRARIES_CLEANUP_PLAN.md)** - Plan nettoyage bibliothèques JS
- **[PLAN_MIGRATION_BOOTSTRAP.md](developer/in-progress/plans/PLAN_MIGRATION_BOOTSTRAP.md)** - Plan migration Bootstrap
- **[NEXT_STEPS_AUTOCOMPLETE.md](developer/in-progress/plans/NEXT_STEPS_AUTOCOMPLETE.md)** - Prochaines étapes autocomplete

### [Archives](developer/archive/)

#### [Migrations terminées](developer/archive/completed-migrations/)
- **[PHP8_MIGRATION_COMPLETE.md](developer/archive/completed-migrations/PHP8_MIGRATION_COMPLETE.md)** - ✅ Migration PHP 8.4 TERMINÉE
- **[PHP8_MIGRATION_SUMMARY.md](developer/archive/completed-migrations/PHP8_MIGRATION_SUMMARY.md)** - ✅ Synthèse migration PHP 8.4
- **[MIGRATION_FPDF_MYPDF_SUCCESS.md](developer/archive/completed-migrations/MIGRATION_FPDF_MYPDF_SUCCESS.md)** - ✅ Succès migration mPDF
- **[AUTOCOMPLETE_MIGRATION_SUMMARY.md](developer/archive/completed-migrations/AUTOCOMPLETE_MIGRATION_SUMMARY.md)** - ✅ Synthèse migration autocomplete
- **[MIGRATIONS_SUMMARY.md](developer/archive/completed-migrations/MIGRATIONS_SUMMARY.md)** - ✅ Résumé général des migrations
- **[AXIOS_MIGRATION_TEMPLATES_UPDATE.md](developer/archive/completed-migrations/AXIOS_MIGRATION_TEMPLATES_UPDATE.md)** - ✅ Mise à jour templates Axios
- **[MIGRATION.md](developer/archive/completed-migrations/MIGRATION.md)** - ✅ Guide général migration (historique)
- **[README_MIGRATION.md](developer/archive/completed-migrations/README_MIGRATION.md)** - ✅ Notes migration (historique)
- **[PHP8_TESTING_CHECKLIST.md](developer/archive/completed-migrations/PHP8_TESTING_CHECKLIST.md)** - ✅ Checklist tests PHP 8

#### [Phases terminées](developer/archive/completed-phases/)
- **[BOOTSTRAP_PHASE1_COMPLETE.md](developer/archive/completed-phases/BOOTSTRAP_PHASE1_COMPLETE.md)** - ✅ Bootstrap Phase 1
- **[BOOTSTRAP_PHASE2_COMPLETE.md](developer/archive/completed-phases/BOOTSTRAP_PHASE2_COMPLETE.md)** - ✅ Bootstrap Phase 2
- **[BOOTSTRAP_PHASE3_COMPLETE.md](developer/archive/completed-phases/BOOTSTRAP_PHASE3_COMPLETE.md)** - ✅ Bootstrap Phase 3
- **[JS_CLEANUP_PHASE1_COMPLETE.md](developer/archive/completed-phases/JS_CLEANUP_PHASE1_COMPLETE.md)** - ✅ Nettoyage JS Phase 1

### [Corrections & Fixes](developer/fixes/)

#### [Bugs](developer/fixes/bugs/)
- **[BUG_SQL_COMPET_ASTERISK.md](developer/fixes/bugs/BUG_SQL_COMPET_ASTERISK.md)** - Bug SQL avec astérisque
- **[FIX_CSV_EXPORT_OPENSPOUT.md](developer/fixes/bugs/FIX_CSV_EXPORT_OPENSPOUT.md)** - Fix export CSV OpenSpout
- **[FIX_MYPDF_OPEN_METHOD.md](developer/fixes/bugs/FIX_MYPDF_OPEN_METHOD.md)** - Fix méthode Open() MyPDF

#### [Fonctionnalités](developer/fixes/features/)
- **[STAT_LICENCIES_CATEGORIE.md](developer/fixes/features/STAT_LICENCIES_CATEGORIE.md)** - ✅ Statistique licenciés FFCK par catégorie d'âge

#### [Correctifs PHP 8](developer/fixes/php8/)
- **[PHP84_DEPRECATED_FIXES.md](developer/fixes/php8/PHP84_DEPRECATED_FIXES.md)** - Correctifs deprecated PHP 8.4
- **[SMARTY_PHP8_FIXES.md](developer/fixes/php8/SMARTY_PHP8_FIXES.md)** - Correctifs Smarty pour PHP 8
- **[WORDPRESS_PHP8_FIXES.md](developer/fixes/php8/WORDPRESS_PHP8_FIXES.md)** - Correctifs WordPress pour PHP 8
- **[WORDPRESS_PHP84_MIGRATION.md](developer/fixes/php8/WORDPRESS_PHP84_MIGRATION.md)** - Migration WordPress PHP 8.4
- **[PHP8_GESTIONDOC_FIXES.md](developer/fixes/php8/PHP8_GESTIONDOC_FIXES.md)** - Correctifs GestionDoc.php

#### [Correctifs Docker](developer/fixes/docker/)
- **[DOCKER_PROD_FIXES.md](developer/fixes/docker/DOCKER_PROD_FIXES.md)** - Correctifs Docker production

### [Audits & Analyses](developer/audits/)

- **[AUDIT_PHASE_0.md](developer/audits/AUDIT_PHASE_0.md)** - Audit initial du code (phase 0)
- **[AUDIT_SUMMARY.txt](developer/audits/AUDIT_SUMMARY.txt)** - Résumé textuel de l'audit
- **[JS_LIBRARIES_AUDIT.md](developer/audits/JS_LIBRARIES_AUDIT.md)** - Audit complet bibliothèques JavaScript
- **[JS_LIBRARIES_USAGE_ANALYSIS.md](developer/audits/JS_LIBRARIES_USAGE_ANALYSIS.md)** - Analyse détaillée usage JS
- **[BOOTSTRAP_PHASE3_INVENTORY.md](developer/audits/BOOTSTRAP_PHASE3_INVENTORY.md)** - Inventaire Bootstrap 3
- **[PHASE5_JQUERY_SELECTORS_ANALYSIS.md](developer/audits/PHASE5_JQUERY_SELECTORS_ANALYSIS.md)** - Analyse sélecteurs jQuery
- **[CLEANUP_QUICK_WINS.md](developer/audits/CLEANUP_QUICK_WINS.md)** - Actions nettoyage rapides

### [Infrastructure](developer/infrastructure/)

#### [Docker](developer/infrastructure/docker/)
- **[DOCKERFILE_OPTIMIZATIONS.md](developer/infrastructure/docker/DOCKERFILE_OPTIMIZATIONS.md)** - Optimisations Dockerfiles
- **[PHP8_DOCKER_SWITCH.md](developer/infrastructure/docker/PHP8_DOCKER_SWITCH.md)** - Bascule Docker vers PHP 8
- **[WORDPRESS_DOCKER_DECISION.md](developer/infrastructure/docker/WORDPRESS_DOCKER_DECISION.md)** - Décision architecture WordPress

#### [WordPress](developer/infrastructure/wordpress/)
- **[WORDPRESS_MIGRATION_OLD_PROD_TO_VPS.md](developer/infrastructure/wordpress/WORDPRESS_MIGRATION_OLD_PROD_TO_VPS.md)** - Migration WordPress vers VPS
- **[PATTERN_8_IMAGES_ARRIERE_PLAN.md](developer/infrastructure/wordpress/PATTERN_8_IMAGES_ARRIERE_PLAN.md)** - Motifs images PDF

#### [Configuration](developer/infrastructure/configuration/)
- **[MAKEFILE_COMPOSER_UPDATES.md](developer/infrastructure/configuration/MAKEFILE_COMPOSER_UPDATES.md)** - Mises à jour Makefile Composer
- **[MATOMO_CONFIG.md](developer/infrastructure/configuration/MATOMO_CONFIG.md)** - Configuration Matomo

---

## 📊 Statistiques

- **Total documents**: 68+ fichiers
- **Documentation utilisateur**: 10 fichiers
- **Documentation développeur**: 60+ fichiers (dont 3 références)
- **Lignes de documentation**: ~24000+
- **Dernière mise à jour**: 2026-01-03

---

## 🔗 Liens Rapides

- [README.md principal](../README.md) - Documentation générale du projet
- [CLAUDE.md](../CLAUDE.md) - Guide pour Claude Code
- [GEMINI.md](../GEMINI.md) - Guide pour Gemini
- [Makefile](../Makefile) - Commandes de développement

---

**Organisation**: Documentation organisée pour distinguer clairement les guides utilisateurs (brefs et fonctionnels) des documentations développeur (techniques, détaillées, avec todo lists et archives).
