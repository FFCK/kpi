# Convention de Nommage - Documentation Markdown

**Date**: 2 décembre 2025
**Version**: 1.0

---

## 📋 Convention Générale

### Format de base

```
UPPERCASE_WITH_UNDERSCORES.md
```

**Règles** :
- Tout en MAJUSCULES
- Mots séparés par des underscores (_)
- Extension .md (Markdown)
- Langue anglaise pour les nouveaux fichiers (sauf exceptions)

---

## 📂 Par Type de Documentation

### Documentation Utilisateur (`DOC/user/`)

**Format** : `FEATURE_NAME.md` ou `FUNCTIONALITY_DESCRIPTION.md`

**Exemples** :
- ✅ `EVENT_CACHE_MANAGER.md`
- ✅ `IMAGE_UPLOAD_MANAGEMENT.md`
- ✅ `TEAM_COMPOSITION_COPY.md`
- ✅ `MATCH_DAY_BULK_OPERATIONS.md`
- ✅ `MATCH_CONSISTENCY_STATS.md`
- ✅ `MULTI_COMPETITION_TYPE.md`
- ✅ `CONSOLIDATION_PHASES_CLASSEMENT.md`
- ✅ `DOCVIEWER_GUIDE.md`

**Exception** :
- `NOUVEAUTES.md` (français) - historique, conservé tel quel
- `README.md` (index)

---

### Documentation Développeur (`DOC/developer/`)

#### Référence (`reference/`)

**Format** : `PROJECT_REFERENCE_NAME.md`

**Exemples** :
- ✅ `KPI_FUNCTIONALITY_INVENTORY.md`

#### Guides (`guides/`)

**Format** : Selon le sous-dossier

**Migrations** (`guides/migrations/`) :
- Format : `MIGRATION_FROM_TO.md` ou `FEATURE_MIGRATION_GUIDE.md`
- Exemples :
  - ✅ `MIGRATION_FPDF_TO_MPDF.md`
  - ✅ `MIGRATION_OPENTBS_TO_OPENSPOUT.md`
  - ✅ `FLATPICKR_MIGRATION_GUIDE.md`
  - ✅ `AXIOS_TO_FETCH_MIGRATION.md`

**Infrastructure** (`guides/infrastructure/`) :
- Format : `FEATURE_DESCRIPTION.md` ou `FEATURE_GUIDE.md`
- Exemples :
  - ✅ `MAKEFILE_MULTI_ENVIRONMENT.md`
  - ✅ `NPM_BACKEND_PRODUCTION_GUIDE.md`
  - ✅ `TOOLTIP_TESTING_GUIDE.md`

#### Travaux en cours (`in-progress/`)

**Statuts** (`in-progress/status/`) :
- Format : `FEATURE_MIGRATION_STATUS.md`
- Exemples :
  - ✅ `BOOTSTRAP_MIGRATION_STATUS.md`
  - ✅ `FLATPICKR_MIGRATION_STATUS.md`
  - ✅ `TOOLTIP_MIGRATION_STATUS.md`

**Plans** (`in-progress/plans/`) :
- Format : `FEATURE_PLAN.md` ou `FEATURE_STRATEGY.md`
- Exemples :
  - ✅ `JQUERY_ELIMINATION_STRATEGY.md`
  - ✅ `JS_LIBRARIES_CLEANUP_PLAN.md`
  - ✅ `PLAN_MIGRATION_BOOTSTRAP.md`

#### Archives (`archive/`)

**Migrations terminées** (`archive/completed-migrations/`) :
- Format : `MIGRATION_SUMMARY.md` ou `FEATURE_MIGRATION_COMPLETE.md`
- Exemples :
  - ✅ `PHP8_MIGRATION_COMPLETE.md`
  - ✅ `PHP8_MIGRATION_SUMMARY.md`
  - ✅ `MIGRATION_FPDF_MYPDF_SUCCESS.md`

**Phases terminées** (`archive/completed-phases/`) :
- Format : `FEATURE_PHASE_NUMBER_COMPLETE.md`
- Exemples :
  - ✅ `BOOTSTRAP_PHASE1_COMPLETE.md`
  - ✅ `BOOTSTRAP_PHASE2_COMPLETE.md`
  - ✅ `JS_CLEANUP_PHASE1_COMPLETE.md`

#### Corrections & Fixes (`fixes/`)

**Bugs** (`fixes/bugs/`) :
- Format : `BUG_SHORT_DESCRIPTION.md` ou `FIX_FEATURE_DESCRIPTION.md`
- Exemples :
  - ✅ `BUG_SQL_COMPET_ASTERISK.md`
  - ✅ `FIX_CSV_EXPORT_OPENSPOUT.md`
  - ✅ `FIX_MYPDF_OPEN_METHOD.md`

**Fonctionnalités** (`fixes/features/`) :
- Format : `FEATURE_NAME.md`
- Exemples :
  - ✅ `STAT_LICENCIES_CATEGORIE.md`
  - ✅ `CONSOLIDATION_PHASES_CLASSEMENT.md`

**PHP 8** (`fixes/php8/`) :
- Format : `PHP8_DESCRIPTION_FIXES.md` ou `PHP84_DESCRIPTION.md`
- Exemples :
  - ✅ `PHP84_DEPRECATED_FIXES.md`
  - ✅ `SMARTY_PHP8_FIXES.md`
  - ✅ `WORDPRESS_PHP84_MIGRATION.md`

**Docker** (`fixes/docker/`) :
- Format : `DOCKER_ENV_FIXES.md`
- Exemples :
  - ✅ `DOCKER_PROD_FIXES.md`

#### Audits (`audits/`)

**Format** : `AUDIT_DESCRIPTION.md` ou `FEATURE_AUDIT.md`

**Exemples** :
- ✅ `AUDIT_PHASE_0.md`
- ✅ `JS_LIBRARIES_AUDIT.md`
- ✅ `JS_LIBRARIES_USAGE_ANALYSIS.md`
- ✅ `BOOTSTRAP_PHASE3_INVENTORY.md`

#### Infrastructure (`infrastructure/`)

**Format** : Selon le sous-dossier et le type

**Exemples** :
- ✅ `DOCKERFILE_OPTIMIZATIONS.md`
- ✅ `PHP8_DOCKER_SWITCH.md`
- ✅ `WORDPRESS_MIGRATION_OLD_PROD_TO_VPS.md`
- ✅ `MAKEFILE_COMPOSER_UPDATES.md`

---

## 🎯 Préfixes Courants

| Préfixe | Utilisation | Exemple |
|---------|-------------|---------|
| `MIGRATION_` | Guide de migration | `MIGRATION_FPDF_TO_MPDF.md` |
| `FIX_` | Correctif spécifique | `FIX_CSV_EXPORT_OPENSPOUT.md` |
| `BUG_` | Documentation de bug | `BUG_SQL_COMPET_ASTERISK.md` |
| `AUDIT_` | Rapport d'audit | `AUDIT_PHASE_0.md` |
| `DOCKER_` | Infrastructure Docker | `DOCKER_PROD_FIXES.md` |
| `PHP8_` ou `PHP84_` | Correctifs PHP 8 | `PHP84_DEPRECATED_FIXES.md` |
| `PLAN_` | Plan d'action | `PLAN_MIGRATION_BOOTSTRAP.md` |
| _(aucun)_ | Documentation générale | `TEAM_COMPOSITION_COPY.md` |

---

## 🎨 Suffixes Courants

| Suffixe | Utilisation | Exemple |
|---------|-------------|---------|
| `_STATUS` | Statut en cours | `BOOTSTRAP_MIGRATION_STATUS.md` |
| `_COMPLETE` | Migration/phase terminée | `PHP8_MIGRATION_COMPLETE.md` |
| `_SUMMARY` | Résumé/synthèse | `PHP8_MIGRATION_SUMMARY.md` |
| `_GUIDE` | Guide d'utilisation | `FLATPICKR_MIGRATION_GUIDE.md` |
| `_PLAN` | Plan d'action | `JS_LIBRARIES_CLEANUP_PLAN.md` |
| `_STRATEGY` | Stratégie | `JQUERY_ELIMINATION_STRATEGY.md` |
| `_AUDIT` | Audit technique | `JS_LIBRARIES_AUDIT.md` |
| `_FIXES` | Ensemble de correctifs | `PHP84_DEPRECATED_FIXES.md` |
| _(aucun)_ | Documentation générale | `EVENT_CACHE_MANAGER.md` |

---

## ✅ Exemples de Bons Noms

**Documentation utilisateur** :
- ✅ `EVENT_CACHE_MANAGER.md` - Fonctionnalité claire
- ✅ `MATCH_DAY_BULK_OPERATIONS.md` - Descriptif précis
- ✅ `IMAGE_UPLOAD_MANAGEMENT.md` - Fonctionnalité complète

**Migration** :
- ✅ `MIGRATION_FPDF_TO_MPDF.md` - Source → Destination
- ✅ `FLATPICKR_MIGRATION_GUIDE.md` - Guide de migration vers Flatpickr
- ✅ `PHP8_MIGRATION_COMPLETE.md` - Migration terminée

**Statut** :
- ✅ `BOOTSTRAP_MIGRATION_STATUS.md` - Statut clair
- ✅ `TOOLTIP_MIGRATION_STATUS.md` - En cours

**Fixes** :
- ✅ `BUG_SQL_COMPET_ASTERISK.md` - Bug précis
- ✅ `FIX_MYPDF_OPEN_METHOD.md` - Correctif ciblé

---

## ❌ Exemples de Mauvais Noms

**À éviter** :
- ❌ `event-cache-manager.md` (minuscules)
- ❌ `EventCacheManager.md` (CamelCase)
- ❌ `event_cache_manager.md` (snake_case minuscules)
- ❌ `Event_Cache_Manager.md` (Title Case)
- ❌ `ecm.md` (acronyme sans contexte)
- ❌ `doc1.md` (nom générique)
- ❌ `temp.md` (temporaire)
- ❌ `new_feature.md` (trop vague)

---

## 🔄 Migration de Fichiers Existants

Si vous devez renommer un fichier existant :

1. **Créer le nouveau fichier** avec le bon nom
2. **Copier le contenu** de l'ancien vers le nouveau
3. **Mettre à jour toutes les références** dans les autres fichiers :
   - README.md
   - DOC/README.md
   - DOC/user/README.md
   - DOC/developer/README.md
   - CLAUDE.md
   - Autres documents qui y font référence
4. **Tester les liens** (vérifier que tous les liens fonctionnent)
5. **Supprimer l'ancien fichier** (après validation)
6. **Commiter les changements** avec un message clair

---

## 📝 Cas Particuliers

### Fichiers spéciaux

| Fichier | Convention | Raison |
|---------|-----------|--------|
| `README.md` | Exactement `README.md` | Convention GitHub/Git |
| `CLAUDE.md` | Exactement `CLAUDE.md` | Nom spécifique Claude Code |
| `GEMINI.md` | Exactement `GEMINI.md` | Nom spécifique Gemini |
| `NOUVEAUTES.md` | Français accepté | Historique, conservé tel quel |

### Fichiers multilingues

- **Préférence** : Anglais pour les nouveaux fichiers
- **Exception** : Documents existants en français (NOUVEAUTES.md, etc.)
- **Règle** : Ne pas traduire les fichiers existants sauf nécessité

### Acronymes

- **Préférence** : Écrire en entier (`EVENT_CACHE_MANAGER` plutôt que `ECM`)
- **Exception** : Acronymes très connus (PHP, SQL, API, etc.)
- **Règle** : Si acronyme utilisé, l'expliquer dans le document

---

## 🎯 Checklist pour Nouveau Document

Avant de créer un nouveau document markdown :

- [ ] Le nom est en UPPERCASE_WITH_UNDERSCORES
- [ ] Le nom est descriptif et clair
- [ ] Le nom suit les conventions du dossier (préfixe/suffixe)
- [ ] Le nom est en anglais (sauf exception)
- [ ] Le document sera référencé dans le README approprié
- [ ] Pas d'acronymes obscurs ou ambigus
- [ ] Extension .md présente

---

## 📚 Ressources

- [DOC/README.md](README.md) - Index principal de la documentation
- [DOC/user/README.md](user/README.md) - Documentation utilisateur
- [DOC/developer/README.md](developer/README.md) - Documentation développeur

---

**Note** : Cette convention a été établie le 2 décembre 2025 sur la base de l'analyse des 65+ documents existants dans le projet KPI.

**Mainteneur** : Laurent Garrigue / Claude Code
