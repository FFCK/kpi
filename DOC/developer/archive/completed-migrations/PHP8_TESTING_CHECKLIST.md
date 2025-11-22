# Migration PHP 8 - Checklist de Tests Détaillée

**Date**: 31 octobre 2025
**Objectif**: Valider la compatibilité PHP 8.4 avant bascule production
**Environnement Test**: Container `kpi_php8` (PHP 8.4.13)

---

## 📊 Vue d'Ensemble

### Niveaux de Tests

| Niveau | Priorité | Durée | Critère Réussite |
|--------|----------|-------|------------------|
| **Tests Critiques** | 🔴 Bloquant | 30-45 min | 100% OK |
| **Tests Fonctionnels** | 🟡 Important | 1-2h | ≥95% OK |
| **Tests Intégration** | 🟢 Recommandé | 2-3h | ≥90% OK |
| **Tests Performance** | 🔵 Optionnel | 1h | Stable/Amélioré |

### Méthodologie

1. **Accès container PHP 8** : `make php8_bash` ou via `https://kpi8.localhost`
2. **Console JavaScript** : F12 > Console (vérifier erreurs)
3. **Logs PHP** : `docker logs -f kpi_php8`
4. **Screenshots** : Capturer avant/après si problème visuel

---

## 🔴 Niveau 1 : Tests Critiques (Bloquants)

### 1.1 Infrastructure PHP 8

#### ✅ Vérification Version PHP

```bash
# Dans le container
make php8_bash
php -v

# Output attendu:
# PHP 8.4.13 (cli) (built: Sep 29 2025 23:58:07) (NTS)
# Zend Engine v4.4.13
```

**Critère** : Version exacte = 8.4.13

**Status** : [ ] PASS [ ] FAIL

---

#### ✅ Extensions PHP Installées

```bash
# Vérifier extensions critiques
php -m

# Extensions requises:
# - pdo
# - pdo_mysql
# - mysqli
# - mbstring
# - gd
# - zip
# - opcache
```

**Test manuel** :
```bash
php -r "echo extension_loaded('gd') ? 'OK' : 'FAIL';" && echo
php -r "echo extension_loaded('zip') ? 'OK' : 'FAIL';" && echo
php -r "echo extension_loaded('pdo_mysql') ? 'OK' : 'FAIL';" && echo
```

**Critère** : Toutes extensions = `OK`

**Status** : [ ] PASS [ ] FAIL

---

#### ✅ Composer Opérationnel

```bash
make php8_bash
composer --version

# Output attendu:
# Composer version 2.x.x

# Test installation package
cd /var/www/html
composer show | grep mPDF
composer show | grep openspout
```

**Critère** :
- `mpdf/mpdf` installé (v8.2+)
- `openspout/openspout` installé (v4.32+)

**Status** : [ ] PASS [ ] FAIL

---

### 1.2 Page de Login

**URL** : `https://kpi8.localhost/`

#### ✅ Affichage Page Login

**Test** :
1. Ouvrir `https://kpi8.localhost/`
2. Vérifier affichage formulaire login
3. Console JavaScript (F12) : aucune erreur
4. Réseau (F12 > Network) : 200 OK

**Critère** :
- Page s'affiche correctement
- Formulaire visible (username, password, submit)
- Bootstrap 5 chargé
- Aucune erreur JS/PHP

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ Authentification

**Test** :
1. Saisir identifiants valides
2. Cliquer "Se connecter"
3. Vérifier redirection dashboard

**Critère** :
- Login réussit
- Session créée
- Redirection vers page admin

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 1.3 Page Backend (Dashboard)

**URL** : `https://kpi8.localhost/admin/`

#### ✅ Affichage Dashboard

**Test** :
1. Vérifier navbar Bootstrap 5
2. Vérifier menu principal
3. Vérifier blocs widgets
4. Console JavaScript propre

**Critère** :
- Layout correct
- Navbar fonctionnelle
- Dropdowns fonctionnent
- Aucune erreur JS

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 1.4 Pages Gestion Critiques

#### ✅ GestionAthlete.php

**URL** : `https://kpi8.localhost/admin/GestionAthlete.php`

**Test** :
1. Affichage liste athlètes
2. DataTables fonctionnel
3. Recherche fonctionne
4. Console JS : vérifier variable `masquer` définie

**Critère** :
- Liste s'affiche
- Pas d'erreur `ReferenceError: masquer is not defined`
- DataTables OK
- Actions (ajouter/modifier/supprimer) fonctionnent

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ GestionCompetition.php

**URL** : `https://kpi8.localhost/admin/GestionCompetition.php`

**Test** :
1. Liste compétitions affichée
2. Création nouvelle compétition
3. Modification compétition
4. Suppression compétition

**Critère** :
- CRUD complet fonctionne
- Aucune erreur SQL
- Validation formulaire OK

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ GestionMatch.php

**URL** : `https://kpi8.localhost/admin/GestionMatch.php`

**Test** :
1. Liste matchs affichée
2. Saisie score
3. Validation match
4. Génération feuille match (PDF)

**Critère** :
- Affichage matchs OK
- Saisie score fonctionne
- PDF généré (mPDF)
- Aucune erreur PHP

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 1.5 API REST

#### ✅ Endpoints Critiques

**Test API** :
```bash
# Test endpoint simple
curl https://kpi8.localhost/api/test.php

# Test endpoint compétitions
curl https://kpi8.localhost/api/competitions.php?saison=2025

# Test endpoint matchs
curl https://kpi8.localhost/api/matchs.php?compet=XXXXX
```

**Critère** :
- Réponse JSON valide
- Status HTTP 200
- Pas d'erreur PHP dans réponse

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 1.6 CRON Jobs

#### ✅ Import PCE (Licences FFCK)

**Fichier** : `sources/commun/cron_maj_licencies.php`

**Test** :
```bash
# Exécution manuelle
make php8_bash
cd /var/www/html/commun
php cron_maj_licencies.php

# Vérifier logs
cat log_cron.txt
```

**Critère** :
- Script s'exécute sans erreur
- Import licences réussit
- Log correct (date, nb insertions)

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ Verrouillage Présences

**Fichier** : `sources/commun/cron_verrou_presences.php`

**Test** :
```bash
make php8_bash
cd /var/www/html/commun
php cron_verrou_presences.php

# Vérifier logs
cat log_cron.txt
```

**Critère** :
- Script s'exécute sans erreur
- Verrouillage appliqué selon règles
- Log correct

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

## 🟡 Niveau 2 : Tests Fonctionnels

### 2.1 Génération PDF (mPDF)

#### ✅ Feuille de Match

**Test** :
1. Aller dans GestionMatch.php
2. Cliquer "Feuille de match" (PDF)
3. Télécharger PDF
4. Ouvrir et vérifier contenu

**Critère** :
- PDF généré sans erreur
- Contenu correct (équipes, scores, arbitres)
- UTF-8 correct (accents)
- Layout correct

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ Liste Présences

**Test** :
1. Génération PDF liste présences
2. Vérifier formatage
3. Vérifier données

**Critère** :
- PDF généré
- Données complètes
- Pas de warning mPDF

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 2.2 Exports ODS/XLSX (OpenSpout)

#### ✅ Export ODS

**Test** :
1. GestionStats.php
2. Cliquer "Export ODS"
3. Télécharger fichier
4. Ouvrir avec LibreOffice

**Critère** :
- Fichier .ods généré
- Contenu correct
- Format valide
- Pas de warning PHP 8

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ Export XLSX

**Test** :
1. GestionStats.php
2. Cliquer "Export XLSX"
3. Télécharger fichier
4. Ouvrir avec Excel/LibreOffice

**Critère** :
- Fichier .xlsx généré
- Contenu correct
- Format valide

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ Export CSV

**Test** :
1. Upload CSV (upload_csv.php)
2. Vérifier traitement
3. Pas de warning "Deprecated"

**Critère** :
- CSV importé correctement
- Aucun message deprecated PHP 8.4
- Validation robuste

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 2.3 Templates Smarty

#### ✅ kpphases.tpl

**URL** : `https://kpi8.localhost/kpphases.php?Compet=XXXXX`

**Test** :
1. Affichage phases/poules
2. Classements affichés
3. Matchs affichés
4. Pas d'erreur "Undefined array key"

**Critère** :
- Page s'affiche
- Données correctes
- Aucun warning PHP 8

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ kpterrains.php

**URL** : `https://kpi8.localhost/kpterrains.php?Compet=XXXXX`

**Test** :
1. Affichage terrains/planning
2. Matchs par terrain
3. Pas d'erreur `|| ''`

**Critère** :
- Page s'affiche
- Planning correct
- Fix `??` appliqué (ligne 345)

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ Autres Templates

**Pages à tester** :
- [ ] kpcalendrier.tpl
- [ ] kpclassements.tpl
- [ ] kpequipes.tpl
- [ ] kpmatchs.tpl
- [ ] kphistorique.tpl

**Critère** :
- Toutes les pages s'affichent
- Aucune erreur Smarty PHP 8

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 2.4 WordPress

#### ✅ WordPress Core

**URL** : `https://kpi8.localhost/wordpress/`

**Test** :
1. Ouvrir page WordPress
2. Vérifier affichage
3. Connexion admin WordPress
4. Vérifier dashboard

**Critère** :
- WordPress s'affiche
- Aucune erreur PHP 8.4
- Patches appliqués (pluggable.php, theme.php)

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ NextGen Gallery

**Test** :
1. Aller dans galerie photos
2. Affichage images
3. Lightbox fonctionne

**Critère** :
- Galerie affichée
- Patch PHP 8.4 appliqué
- Aucune erreur

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 2.5 JavaScript / Frontend

#### ✅ formTools.js

**Test** :
1. Ouvrir GestionAthlete.php
2. Console JavaScript (F12)
3. Vérifier variable `masquer` définie

**Critère** :
- Aucune `ReferenceError`
- Variable `masquer` initialisée (valeur 0 ou 1)
- Fonctionnalités masquer/afficher bannière OK

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

#### ✅ Bootstrap 5.3.8

**Test** :
1. Navbar fonctionnelle
2. Dropdowns s'ouvrent
3. Modals s'ouvrent/ferment
4. Tooltips/Popovers fonctionnent

**Critère** :
- Bootstrap 5.3.8 chargé (vérifier Network)
- Composants fonctionnent
- Responsive correct

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

## 🟢 Niveau 3 : Tests d'Intégration

### 3.1 Workflow Complet Compétition

**Scénario** : Créer compétition → Ajouter équipes → Créer matchs → Saisir scores → Générer classement

**Étapes** :
1. [ ] Créer nouvelle compétition (GestionCompetition.php)
2. [ ] Ajouter 4 équipes
3. [ ] Créer 6 matchs (poule)
4. [ ] Saisir scores pour chaque match
5. [ ] Valider matchs
6. [ ] Générer classement
7. [ ] Exporter classement (ODS)
8. [ ] Générer feuilles match (PDF)

**Critère** : Workflow complet sans erreur

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 3.2 Workflow Import PCE

**Scénario** : Import licences FFCK → Affectation arbitres → Validation

**Étapes** :
1. [ ] Exécuter CRON import PCE
2. [ ] Vérifier licenciés importés (table `kp_licencie`)
3. [ ] Vérifier arbitres importés (table `kp_arbitre`)
4. [ ] Affecter arbitre à un match
5. [ ] Valider affectation

**Critère** : Import et affectation réussis

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

### 3.3 Workflow Live Scores

**Scénario** : Saisie score → Publication live → Affichage public

**Étapes** :
1. [ ] Créer match
2. [ ] Saisir score en live (GestionMatch.php)
3. [ ] Valider score
4. [ ] Vérifier affichage public (kpmatchs.php)
5. [ ] Vérifier API live scores

**Critère** : Score publié et affiché en temps réel

**Status** : [ ] PASS [ ] FAIL

**Notes** : _______________________________________________

---

## 🔵 Niveau 4 : Tests de Performance (Optionnel)

### 4.1 Temps de Réponse

**Outils** : Chrome DevTools (Network), Apache Bench

**Pages à tester** :
```bash
# Page login
ab -n 100 -c 10 https://kpi8.localhost/

# Page GestionAthlete
ab -n 50 -c 5 https://kpi8.localhost/admin/GestionAthlete.php

# API endpoint
ab -n 100 -c 10 https://kpi8.localhost/api/competitions.php
```

**Critère** :
- Temps moyen ≤ 200ms (login)
- Temps moyen ≤ 500ms (pages gestion)
- Temps moyen ≤ 100ms (API)
- Performance stable ou améliorée vs PHP 7.4

**Status** : [ ] PASS [ ] FAIL [ ] N/A

**Notes** : _______________________________________________

---

### 4.2 Consommation Mémoire

**Test** :
```bash
# Vérifier mémoire container
docker stats kpi_php8

# Memory usage attendu: < 500MB au repos
```

**Critère** : Consommation mémoire stable

**Status** : [ ] PASS [ ] FAIL [ ] N/A

**Notes** : _______________________________________________

---

### 4.3 OPcache

**Test** :
```bash
make php8_bash
php -i | grep opcache

# Vérifier opcache.enable=1
```

**Critère** : OPcache activé et fonctionnel

**Status** : [ ] PASS [ ] FAIL [ ] N/A

**Notes** : _______________________________________________

---

## 📋 Récapitulatif Final

### Statistiques Tests

| Niveau | Total Tests | PASS | FAIL | Taux Réussite |
|--------|-------------|------|------|---------------|
| 🔴 **Critiques** | __ / 14 | __ | __ | __% |
| 🟡 **Fonctionnels** | __ / 15 | __ | __ | __% |
| 🟢 **Intégration** | __ / 3 | __ | __ | __% |
| 🔵 **Performance** | __ / 3 | __ | __ | __% |
| **TOTAL** | __ / 35 | __ | __ | __% |

---

### Critères de Validation

| Critère | Seuil | Status |
|---------|-------|--------|
| **Tests Critiques** | 100% PASS | [ ] ✅ [ ] ❌ |
| **Tests Fonctionnels** | ≥95% PASS | [ ] ✅ [ ] ❌ |
| **Tests Intégration** | ≥90% PASS | [ ] ✅ [ ] ❌ |
| **Aucun bug bloquant** | 0 | [ ] ✅ [ ] ❌ |

---

### Décision GO/NO-GO

**Date validation** : _____ / _____ / _____

**Testeur** : _________________________________

**Résultat global** :
- [ ] ✅ **GO** - Migration PHP 8 validée
- [ ] ❌ **NO-GO** - Corrections nécessaires

**Bugs bloquants identifiés** :
1. _________________________________________________
2. _________________________________________________
3. _________________________________________________

**Actions correctives** :
1. _________________________________________________
2. _________________________________________________
3. _________________________________________________

---

### Signatures

**Validé par** : _________________________________

**Date** : _____ / _____ / _____

**Prêt pour production** : [ ] OUI [ ] NON

---

## 📚 Documentation Connexe

- [PHP8_MIGRATION_SUMMARY.md](PHP8_MIGRATION_SUMMARY.md) - Synthèse migration
- [PHP8_DOCKER_SWITCH.md](PHP8_DOCKER_SWITCH.md) - Guide bascule Docker
- [AUDIT_PHASE_0.md](AUDIT_PHASE_0.md) - Audit complet projet

---

**Auteur**: Laurent Garrigue / Claude Code
**Date**: 31 octobre 2025
**Version**: 1.0
**Statut**: 📋 **CHECKLIST PRÊTE**
