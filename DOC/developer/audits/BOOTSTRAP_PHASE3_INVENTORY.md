# Bootstrap Phase 3 - Inventaire Complet des Dépendances

**Date**: 29 octobre 2025
**Objectif**: Identifier TOUS les fichiers impactés par la migration Bootstrap 3.x → 5.3.8

---

## Résumé Exécutif

### Fichiers Principaux Bootstrap 3 (identifiés Phase 0)
7 templates Smarty + 1 fichier PHP live = **8 fichiers à migrer**

### Fichiers Dépendants (nouveaux)
- **Templates de contenu**: 40+ templates utilisant classes Bootstrap 3
- **Templates header/footer**: 5 fichiers inclus
- **Classes Bootstrap 3 utilisées**: col-xs-, col-sm-, hidden-xs, panel, glyphicon

---

## 1. Fichiers Principaux (Templates de Base)

### 1.1 Templates Smarty Admin (sources/smarty/templates/)

| Fichier | Bootstrap 3 | Utilisation | Critique |
|---------|-------------|-------------|----------|
| **pagelogin.tpl** | Ligne 13: `js/bootstrap/css/bootstrap.min.css`<br>Ligne 28: `js/bootstrap/js/bootstrap.min.js` | Login page (admin) | ⚠️ **CRITIQUE** |
| **kppage.tpl** | Ligne 77: `js/bootstrap/js/bootstrap.min.js` | Page standard backend | ⚠️ **CRITIQUE** |
| **fppage.tpl** | Ligne 24: `js/bootstrap/css/bootstrap.min.css`<br>Ligne 51: `js/bootstrap/js/bootstrap.min.js` | Full page frontend | Important |
| **frame_page.tpl** | Ligne 51: `js/bootstrap/js/bootstrap.min.js` | Frame page | Important |
| **kppagewide.tpl** | Ligne 54: `js/bootstrap/js/bootstrap.min.js` | Wide page layout | Important |
| **kppageleaflet.tpl** | Ligne 88: `js/bootstrap/js/bootstrap.min.js` | Leaflet map page | Moyen |

**Note**: `kppage.tpl` charge Bootstrap JS mais pas le CSS (utilise wordpress_material_style.css à la place)

### 1.2 Fichiers PHP Live (sources/live/)

| Fichier | Bootstrap 3 | Utilisation | Critique |
|---------|-------------|-------------|----------|
| **tv.php** | Ligne 35: `css/bootstrap.min.css` | Écran TV public | Important |

**Note**: tv_old.php et tv_new.php n'existent pas (seul tv.php existe)

---

## 2. Fichiers Dépendants (Inclusions)

### 2.1 Templates Inclus par les Templates de Base

#### Inclus par kppage.tpl, kppagewide.tpl, kppageleaflet.tpl

**kpheader.tpl** (lignes 5-32):
```smarty
<div class="container-fluid hidden-xs">  <!-- Bootstrap 3: hidden-xs -->
  <div class="row">
    <div class="col-xs-12 banner">        <!-- Bootstrap 3: col-xs-12 -->
```
- Classes Bootstrap 3: `hidden-xs`, `col-xs-9`, `col-xs-12`
- Inclut: `kpmain_menu.tpl`

**kpheaderwide.tpl** (ligne 14-31):
```smarty
<div class="container header-contents">
  <div class="row">
```
- Classes Bootstrap 3: `row`, `container`
- Commenté (beaucoup de code en commentaire Smarty)

**kpfooter.tpl**:
- Classes Bootstrap 3 à vérifier

**kpmain_menu.tpl**:
- Classes Bootstrap 3 à vérifier (navbar?)

#### Inclus par fppage.tpl et frame_page.tpl

**Templates de contenu dynamiques** via `{include file="$contenutemplate.tpl"}`:
- Plus de 40 templates utilisent des classes Bootstrap 3
- Voir section 2.2 ci-dessous

### 2.2 Templates de Contenu Utilisant Bootstrap 3

**Liste des templates** (utilisant `col-xs-`, `col-sm-`, `hidden-xs`, `panel`, `glyphicon`):

#### Frontend Public (frame_*.tpl)
1. frame_categories.tpl
2. frame_chart.tpl
3. frame_classement.tpl
4. frame_details.tpl
5. frame_equipes.tpl
6. frame_matchs.tpl
7. frame_navgroup.tpl
8. frame_phases.tpl
9. frame_qr.tpl
10. frame_stats.tpl
11. frame_team.tpl
12. frame_terrains.tpl

#### Frontend Public (kp*.tpl)
13. kpcalendrier.tpl
14. kpchart.tpl
15. kpclassements.tpl
16. kpclassement.tpl
17. kpclubs.tpl
18. kpcompetition.tpl
19. kpdetails.tpl
20. kpequipes.tpl
21. kpheader.tpl (déjà listé)
22. kpmatchs.tpl
23. kpnavgroup.tpl
24. kpstats.tpl
25. kpteam.tpl

#### Frontend Public (fp*.tpl)
26. fpmatchs_tab1.tpl
27. fpmatchs.tpl

#### Backend Admin (Gestion*.tpl)
28. GestionAthlete.tpl
29. GestionCalendrier.tpl
30. GestionClassement.tpl
31. GestionCompetition.tpl
32. GestionEquipe.tpl
33. GestionEquipeJoueur.tpl
34. GestionEvenement.tpl
35. GestionGroupe.tpl
36. GestionJournee.tpl
37. GestionRc.tpl
38. GestionSchema.tpl
39. (+ autres templates Gestion*.tpl à vérifier)

**Total estimé**: 40+ templates de contenu

---

## 3. Classes Bootstrap 3 Utilisées

### 3.1 Système de Grille (Breaking Changes BS3→BS5)

#### Bootstrap 3
```html
<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
<div class="hidden-xs visible-sm">
```

#### Bootstrap 5
```html
<div class="col-12 col-sm-6 col-md-4 col-lg-3">
<div class="d-none d-sm-block">
```

**Changements requis**:
- `col-xs-*` → `col-*` (xs est maintenant par défaut)
- `hidden-xs` → `d-none d-sm-block`
- `hidden-sm` → `d-sm-none d-md-block`
- `visible-*` → `d-*-block`

### 3.2 Composants (Breaking Changes)

#### Panels → Cards
```html
<!-- Bootstrap 3 -->
<div class="panel panel-default">
  <div class="panel-heading">Titre</div>
  <div class="panel-body">Contenu</div>
</div>

<!-- Bootstrap 5 -->
<div class="card">
  <div class="card-header">Titre</div>
  <div class="card-body">Contenu</div>
</div>
```

#### Glyphicons → Font Awesome
```html
<!-- Bootstrap 3 -->
<span class="glyphicon glyphicon-user"></span>

<!-- Bootstrap 5 (utilise Font Awesome dans ce projet) -->
<i class="fa fa-user"></i>
```

**Note**: Le projet utilise déjà Font Awesome, donc les glyphicons doivent être migrés vers FA.

### 3.3 Utilities

| Bootstrap 3 | Bootstrap 5 | Usage |
|-------------|-------------|-------|
| `.pull-left` | `.float-start` | Float left |
| `.pull-right` | `.float-end` | Float right |
| `.center-block` | `.mx-auto` | Center block |
| `.hidden` | `.d-none` | Hide element |
| `.text-left` | `.text-start` | Text align left |
| `.text-right` | `.text-end` | Text align right |

### 3.4 JavaScript (Breaking Changes)

#### jQuery Dependency
- **Bootstrap 3**: Nécessite jQuery
- **Bootstrap 5**: Vanilla JS (pas de jQuery)

**Impact**: Les templates utilisent jQuery 1.11.2 et 3.5.1
- `pagelogin.tpl` ligne 27: jQuery 1.11.2
- `kppage.tpl` ligne 73: jQuery 3.5.1

**Solution**: Bootstrap 5 fonctionne avec ou sans jQuery (compatible)

#### Data Attributes
```javascript
// Bootstrap 3
data-toggle="modal"
data-target="#myModal"

// Bootstrap 5
data-bs-toggle="modal"
data-bs-target="#myModal"
```

**Changements requis**: Tous les attributs `data-*` → `data-bs-*`

---

## 4. Stratégie de Migration Phase 3

### 4.1 Ordre de Migration Recommandé

#### Étape 1: Templates de Base (bas risque)
1. ✅ tv.php (fichier standalone, peu de dépendances)
2. ✅ fppage.tpl (frontend public, moins critique)
3. ✅ frame_page.tpl (frontend public, moins critique)

#### Étape 2: Templates Backend (risque moyen)
4. ✅ kppagewide.tpl (wide layout, moins utilisé)
5. ✅ kppageleaflet.tpl (leaflet maps, usage spécifique)

#### Étape 3: Templates Critiques (haut risque)
6. ⚠️ kppage.tpl (page principale backend, TRÈS UTILISÉ)
7. ⚠️ pagelogin.tpl (login page, CRITIQUE)

### 4.2 Pour Chaque Migration

**Avant de migrer un template de base**:

1. **Identifier tous les templates de contenu inclus**
   ```bash
   grep -r "contenutemplate.*NomTemplate" sources/
   ```

2. **Chercher les classes Bootstrap 3 dans ces templates**
   ```bash
   grep -n "col-xs-\|hidden-xs\|panel\|glyphicon" template.tpl
   ```

3. **Migrer d'abord les templates de contenu**, puis le template de base

4. **Créer backups systématiques**
   ```bash
   cp template.tpl template.tpl.bs3.bak
   ```

5. **Tester dans le navigateur** après chaque migration

### 4.3 Script de Migration Automatique (Partiel)

**Attention**: La migration automatique ne peut traiter que les cas simples. Les classes complexes nécessitent une révision manuelle.

#### Remplacements Automatiques Sûrs

```bash
# Grille de base
sed -i 's/col-xs-/col-/g' template.tpl

# Visibility utilities (simples)
sed -i 's/hidden-xs/d-none d-sm-block/g' template.tpl
sed -i 's/hidden-sm/d-sm-none d-md-block/g' template.tpl

# Float utilities
sed -i 's/pull-left/float-start/g' template.tpl
sed -i 's/pull-right/float-end/g' template.tpl

# Text utilities
sed -i 's/text-left/text-start/g' template.tpl
sed -i 's/text-right/text-end/g' template.tpl

# Data attributes
sed -i 's/data-toggle/data-bs-toggle/g' template.tpl
sed -i 's/data-target/data-bs-target/g' template.tpl
sed -i 's/data-dismiss/data-bs-dismiss/g' template.tpl
```

#### Remplacements Manuels Requis

**Panels → Cards**: Nécessite révision structure HTML
**Glyphicons → Font Awesome**: Nécessite changement d'icônes
**Navbars**: Changements structurels importants
**Modals, Dropdowns**: Vérifier attributs data-bs-*

### 4.4 Tests de Validation

**Pour chaque template migré**:

1. **Validation visuelle**:
   - [ ] Layout correct (grille responsive)
   - [ ] Composants affichés correctement
   - [ ] Icônes visibles
   - [ ] Pas d'éléments cassés

2. **Validation responsive**:
   - [ ] Mobile (col-* fonctionne)
   - [ ] Tablet (col-sm-*, col-md-*)
   - [ ] Desktop (col-lg-*, col-xl-*)
   - [ ] Visibility classes (d-none, d-sm-block, etc.)

3. **Validation JavaScript**:
   - [ ] Modals s'ouvrent/ferment
   - [ ] Dropdowns fonctionnent
   - [ ] Tooltips affichés
   - [ ] Pas d'erreurs console

4. **Validation fonctionnelle**:
   - [ ] Formulaires fonctionnent
   - [ ] Tables DataTables OK
   - [ ] Calendriers OK
   - [ ] Cartes Leaflet OK

---

## 5. Risques et Précautions

### 5.1 Risques Identifiés

| Risque | Impact | Probabilité | Mitigation |
|--------|--------|-------------|------------|
| **Login page cassée** | 🔴 Critique | Moyen | Tester en environnement dev, backup obligatoire |
| **Backend inutilisable** | 🔴 Critique | Faible | Migrer kppage.tpl en dernier, tests exhaustifs |
| **Layouts cassés** | 🟡 Moyen | Élevé | Migration progressive template par template |
| **JavaScript errors** | 🟡 Moyen | Moyen | Tests console browser, vérifier data-bs-* |
| **Responsive cassé** | 🟢 Faible | Moyen | Tests multi-devices |
| **Icônes manquantes** | 🟢 Faible | Élevé | Vérifier Font Awesome déjà installé |

### 5.2 Précautions

1. **Backups systématiques**:
   ```bash
   # Backup avant Phase 3
   tar -czf backup_smarty_templates_bs3.tar.gz sources/smarty/templates/*.tpl
   ```

2. **Tests en environnement dev**:
   - Ne PAS tester directement en production
   - Utiliser `make docker_dev_up` pour environnement développement

3. **Migration progressive**:
   - 1 template à la fois
   - Validation après chaque migration
   - Commit Git après chaque étape réussie

4. **Documentation**:
   - Noter tous les changements manuels
   - Documenter les problèmes rencontrés
   - Créer guide de migration pour futurs templates

---

## 6. Estimation Temps et Effort

### 6.1 Estimation par Fichier

| Fichier | Complexité | Classes BS3 | Temps estimé |
|---------|------------|-------------|--------------|
| tv.php | Faible | 1 lien CSS | 10 min |
| fppage.tpl | Faible | 2 liens | 10 min |
| frame_page.tpl | Faible | 1 lien JS | 10 min |
| kppagewide.tpl | Moyen | 1 lien JS + templates inclus | 30 min |
| kppageleaflet.tpl | Moyen | 1 lien JS + Leaflet | 30 min |
| kppage.tpl | Élevé | 1 lien JS + nombreux templates | 1-2h |
| pagelogin.tpl | Élevé | 2 liens + tests critiques | 1-2h |

**Total templates de base**: 3-5 heures

### 6.2 Templates de Contenu (40+ fichiers)

**Approche recommandée**: Migration à la demande (lazy migration)

- Migrer les templates de contenu uniquement quand nécessaire
- Priorité: templates les plus utilisés (Gestion*.tpl)
- Bootstrap 3 et 5 peuvent coexister temporairement

**Si migration complète souhaitée**:
- 40 templates × 15 min moyenne = **10 heures**
- Plus tests et validation = **15-20 heures total**

### 6.3 Total Phase 3

**Migration minimale** (7 templates de base uniquement):
- Migration: 3-5 heures
- Tests: 2-3 heures
- Documentation: 1 heure
- **Total: 6-9 heures**

**Migration complète** (avec templates de contenu):
- Migration: 15-20 heures
- Tests: 5-7 heures
- Documentation: 2 heures
- **Total: 22-29 heures**

---

## 7. Checklist Phase 3

### Préparation
- [ ] Backup complet templates Smarty
- [ ] Commit Git avant début migration
- [ ] Environnement dev opérationnel
- [ ] Tests navigateurs préparés (Chrome, Firefox, Safari)

### Migration Templates de Base (ordre recommandé)
- [ ] tv.php (+ tests)
- [ ] fppage.tpl (+ tests)
- [ ] frame_page.tpl (+ tests)
- [ ] kppagewide.tpl (+ tests)
- [ ] kppageleaflet.tpl (+ tests)
- [ ] kppage.tpl (+ tests exhaustifs)
- [ ] pagelogin.tpl (+ tests critiques login)

### Migration Templates Inclus
- [ ] kpheader.tpl (classes Bootstrap 3)
- [ ] kpheaderwide.tpl (vérification)
- [ ] kpfooter.tpl (vérification)
- [ ] kpmain_menu.tpl (navbar?)

### Migration Templates de Contenu (optionnel)
- [ ] frame_*.tpl (12 fichiers)
- [ ] kp*.tpl (13 fichiers)
- [ ] fp*.tpl (2 fichiers)
- [ ] Gestion*.tpl (9+ fichiers)

### Nettoyage
- [ ] Supprimer backups .bs3.bak après validation
- [ ] Supprimer js/bootstrap/ (Bootstrap 3.4.1)
- [ ] Supprimer js/bootstrap-3.3.1/
- [ ] Commit Git final Phase 3

### Documentation
- [ ] Créer BOOTSTRAP_PHASE3_COMPLETE.md
- [ ] Mettre à jour WORKFLOW_AI/README.md
- [ ] Documenter problèmes rencontrés

---

## 8. Rollback Phase 3

### Script de Restauration

```bash
#!/bin/bash
# restore_bootstrap3_backups.sh

TEMPLATES_DIR="/home/laurent/Documents/dev/kpi/sources/smarty/templates"
LIVE_DIR="/home/laurent/Documents/dev/kpi/sources/live"

echo "Restauration des backups Bootstrap 3..."

# Restaurer templates Smarty
for backup in "$TEMPLATES_DIR"/*.bs3.bak; do
    if [ -f "$backup" ]; then
        original="${backup%.bs3.bak}"
        cp "$backup" "$original"
        echo "✓ Restauré: $(basename $original)"
    fi
done

# Restaurer fichiers live/
for backup in "$LIVE_DIR"/*.bs3.bak; do
    if [ -f "$backup" ]; then
        original="${backup%.bs3.bak}"
        cp "$backup" "$original"
        echo "✓ Restauré: $(basename $original)"
    fi
done

echo "Restauration terminée"
```

### Commande Manuelle

```bash
# Restaurer un fichier spécifique
cp pagelogin.tpl.bs3.bak pagelogin.tpl
```

---

## 9. Ressources

### Documentation Bootstrap
- [Bootstrap 3.4 Documentation](https://getbootstrap.com/docs/3.4/)
- [Bootstrap 5.3 Documentation](https://getbootstrap.com/docs/5.3/)
- [Migration Guide 3.x → 5.x](https://getbootstrap.com/docs/5.3/migration/)

### Outils
- [Bootstrap Migration Tool](https://github.com/coliff/bootstrap-4-migration-tool) (BS3→BS4, adaptable BS5)
- [Bootstrap 5 Cheat Sheet](https://bootstrap-cheatsheet.themeselection.com/)

### Projets KPI
- [PLAN_MIGRATION_BOOTSTRAP.md](PLAN_MIGRATION_BOOTSTRAP.md) - Plan complet Phase 0
- [BOOTSTRAP_PHASE1_COMPLETE.md](BOOTSTRAP_PHASE1_COMPLETE.md) - Installation BS 5.3.8
- [BOOTSTRAP_PHASE2_COMPLETE.md](BOOTSTRAP_PHASE2_COMPLETE.md) - Migration BS 5.x → 5.3.8

---

## 10. Conclusion

La Phase 3 est plus complexe que la Phase 2 en raison de :

1. **Breaking changes importants** entre Bootstrap 3 et 5
2. **40+ templates de contenu** utilisant classes Bootstrap 3
3. **Templates critiques** (login, backend principal)
4. **Migration jQuery** (optionnelle mais recommandée)

**Recommandation**:
- Migration progressive template par template
- Prioriser les templates de base (7 fichiers)
- Reporter migration templates de contenu si temps limité
- Tests exhaustifs pour templates critiques

**Bénéfices attendus**:
- ✅ Uniformisation complète Bootstrap 5.3.8
- ✅ Suppression définitive Bootstrap 3.x
- ✅ Maintenance simplifiée (1 version unique)
- ✅ Performance améliorée
- ✅ Support navigateurs modernes

---

**Auteur**: Laurent Garrigue / Claude Code
**Date**: 29 octobre 2025
**Version**: 1.0
**Statut**: 📋 Inventaire complet - Prêt pour Phase 3
