# Documentation Développeur

Documentation technique complète pour le développement et la maintenance du projet KPI.

## 📂 Organisation

### [📚 Référence](reference/)
Documentation de référence complète du système.
- **[KPI_FUNCTIONALITY_INVENTORY.md](reference/KPI_FUNCTIONALITY_INVENTORY.md)** - Inventaire complet des fonctionnalités (~7000 lignes)

### [📖 Guides](guides/)
Guides de migration et documentation technique pour le développement.
- **[migrations/](guides/migrations/)** - Guides de migration (FPDF→mPDF, Smarty v4, OpenSpout, Axios→fetch, etc.)
- **[infrastructure/](guides/infrastructure/)** - Guides infrastructure (multi-environnement, NPM backend, tests)

### [⏳ Travaux en cours](in-progress/)
Projets et migrations actuellement en cours de réalisation.
- **[status/](in-progress/status/)** - Statut des migrations en cours (Bootstrap, Flatpickr, tooltips, masked input)
- **[plans/](in-progress/plans/)** - Plans d'action (élimination jQuery, nettoyage JS, Bootstrap)

### [✅ Archives](archive/)
Projets et migrations terminés pour référence historique.
- **[completed-migrations/](archive/completed-migrations/)** - Migrations terminées (PHP 8.4, mPDF, Axios, etc.)
- **[completed-phases/](archive/completed-phases/)** - Phases terminées (Bootstrap 1-3, nettoyage JS Phase 1)

### [🔧 Corrections & Fixes](fixes/)
Documentation des bugs et correctifs appliqués.
- **[bugs/](fixes/bugs/)** - Corrections de bugs spécifiques
- **[php8/](fixes/php8/)** - Correctifs pour compatibilité PHP 8.4
- **[docker/](fixes/docker/)** - Correctifs infrastructure Docker
- **[features/](fixes/features/)** - Nouvelles fonctionnalités (Consolidation classement, Stats licenciés, etc.)

### [🔍 Audits & Analyses](audits/)
Rapports d'audit de code et analyses techniques.
- Audit initial (Phase 0)
- Audit bibliothèques JavaScript
- Analyses Bootstrap, jQuery
- Actions de nettoyage identifiées

### [🏗️ Infrastructure](infrastructure/)
Documentation infrastructure et configuration.
- **[docker/](infrastructure/docker/)** - Optimisations Docker, bascule PHP 8, WordPress
- **[wordpress/](infrastructure/wordpress/)** - Migration WordPress VPS, patterns PDF
- **[configuration/](infrastructure/configuration/)** - Makefile, Matomo

---

## 🎯 Documents Prioritaires

### À lire en premier

1. **[archive/completed-migrations/PHP8_MIGRATION_COMPLETE.md](archive/completed-migrations/PHP8_MIGRATION_COMPLETE.md)** 🎉
   - **Migration PHP 8.4 TERMINÉE** (document final de référence)
   - Statut: ✅ 100% déployé en production
   - Métriques, configuration, timeline

2. **[archive/completed-migrations/PHP8_MIGRATION_SUMMARY.md](archive/completed-migrations/PHP8_MIGRATION_SUMMARY.md)** ⭐
   - **Synthèse technique complète** migration PHP 7.4 → 8.4
   - Document de référence technique
   - Timeline, métriques, checklist validation

3. **[audits/JS_LIBRARIES_AUDIT.md](audits/JS_LIBRARIES_AUDIT.md)**
   - État actuel des bibliothèques JavaScript
   - Identification des CVE et obsolescence
   - Plan d'action en 4 phases

4. **[guides/migrations/MIGRATION_OPENTBS_TO_OPENSPOUT.md](guides/migrations/MIGRATION_OPENTBS_TO_OPENSPOUT.md)**
   - Migration tableurs (OpenTBS → OpenSpout)
   - Export ODS/XLSX/CSV avec internationalisation
   - ✅ En production

5. **[in-progress/status/BOOTSTRAP_MIGRATION_STATUS.md](in-progress/status/BOOTSTRAP_MIGRATION_STATUS.md)**
   - Statut migration Bootstrap 5.3.8
   - Travaux en cours

---

## 🔄 Migrations en Cours

### Haute Priorité
- **Bootstrap 5.3.8** - ⏳ En cours (voir [status/BOOTSTRAP_MIGRATION_STATUS.md](in-progress/status/BOOTSTRAP_MIGRATION_STATUS.md))
- **Élimination jQuery** - ⏳ Planifié (voir [plans/JQUERY_ELIMINATION_STRATEGY.md](in-progress/plans/JQUERY_ELIMINATION_STRATEGY.md))

### Moyenne Priorité
- **Flatpickr** - ⏳ Planifié (voir [status/FLATPICKR_MIGRATION_STATUS.md](in-progress/status/FLATPICKR_MIGRATION_STATUS.md))
- **Tooltips** - ⏳ En cours (voir [status/TOOLTIP_MIGRATION_STATUS.md](in-progress/status/TOOLTIP_MIGRATION_STATUS.md))

---

## ✅ Migrations Terminées

### Majeures
- ✅ **PHP 8.4** (Nov 2025) - Migration complète, déployé en production
- ✅ **mPDF v8.2+** - Remplacement FPDF avec wrapper MyPDF
- ✅ **OpenSpout v4.32.0** - Remplacement OpenTBS pour exports tableurs
- ✅ **Smarty v4** - Migration template engine
- ✅ **Bootstrap Phase 1-3** - Consolidation vers Bootstrap 5.3.8
- ✅ **Axios → fetch()** - Élimination dépendance Axios (3 CVE éliminées)
- ✅ **Nettoyage JS Phase 1** - Suppression 5 fichiers jQuery obsolètes (60+ CVE)

---

## 📊 Métriques Globales

### Code & Documentation
- **Total documents**: 57+ fichiers
- **Lignes de documentation**: ~18000+
- **Période**: Oct 2025 - Nov 2025

### Migrations Complétées
- **PHP 8.4**: 100% terminé ✅
- **mPDF**: Production ✅
- **OpenSpout**: Production ✅
- **Bootstrap 5.x**: 14 fichiers migrés ✅
- **Axios→fetch**: 9 fichiers + 11 templates ✅
- **jQuery cleanup**: 5 fichiers supprimés ✅

### Sécurité
- **CVE éliminées**: 60+ (jQuery) + 3 (Axios)
- **Bibliothèques mises à jour**: mPDF, OpenSpout, Smarty, Bootstrap
- **PHP 7.4**: Complètement déprécié

---

## 🔗 Liens Utiles

- [Documentation Utilisateur](../user/) - Guides et fonctionnalités
- [README principal](../../README.md) - Documentation générale du projet
- [CLAUDE.md](../../CLAUDE.md) - Guide pour Claude Code
- [Makefile](../../Makefile) - Commandes de développement

---

## 📝 Convention de Nommage

- **MIGRATION_*.md** : Guides de migration
- **FIX_*.md** : Documentation de correctifs spécifiques
- **BUG_*.md** : Documentation de bugs et résolutions
- **AUDIT_*.md** : Rapports d'audit de code
- **DOCKER_*.md** : Documentation infrastructure Docker
- **PHP8_*.md** : Corrections spécifiques PHP 8
- ***_STATUS.md** : Statut de migrations en cours
- ***_COMPLETE.md** : Migrations ou phases terminées
- ***_SUMMARY.md** : Résumés et synthèses

---

**Dernière mise à jour**: 2025-11-22
**Mainteneur**: Laurent Garrigue / Claude Code
