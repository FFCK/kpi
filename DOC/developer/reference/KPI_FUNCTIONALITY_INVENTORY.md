# KPI - Inventaire Complet des Fonctionnalités

**Projet**: KPI (Kayak Polo Information)
**Date**: 31 octobre 2025
**Version**: 1.0
**URL Production**: https://kayak-polo.info

---

## 📋 Table des Matières

1. [Vue d'Ensemble](#vue-densemble)
2. [Partie Administration (Backend)](#partie-administration-backend)
3. [Partie Publique (Frontend)](#partie-publique-frontend)
4. [Incrustations Vidéo (Live Streaming)](#incrustations-vidéo-live-streaming)
5. [Génération PDF](#génération-pdf)
6. [API & Intégrations](#api--intégrations)
7. [Applications Vue.js/Nuxt](#applications-vuejsnuxt)
8. [Architecture Technique](#architecture-technique)

---

## 🎯 Vue d'Ensemble

### Description Projet

KPI est un **système complet de gestion sportive** pour le Kayak-Polo, comprenant :
- Gestion de compétitions (nationales et internationales)
- Gestion d'équipes, joueurs, arbitres
- Saisie scores en temps réel
- Live streaming avec incrustations vidéo
- Affichages publics sur écrans (scoreboards, classements)
- Génération PDF (feuilles de match, classements)
- Site public avec calendriers, résultats, statistiques

### Chiffres Clés

| Métrique | Valeur |
|----------|--------|
| **Fichiers PHP Backend** | ~100+ fichiers admin |
| **Fichiers Public** | ~50+ fichiers |
| **Templates Smarty** | 88 templates |
| **Applications Frontend** | 4 (Nuxt + 3 Vue.js) |
| **Base de données** | MySQL (2 bases : KPI + WordPress) |
| **Utilisateurs** | Clubs, fédérations, arbitres, public |

---

## 🔐 Partie Administration (Backend)

**Localisation** : `sources/admin/`
**Accès** : Authentification requise
**Rôle** : Gestion complète des compétitions

### 1. Authentification & Utilisateurs

#### Login / Logout
- **Login.php** - Page de connexion
- **Logout.php** - Déconnexion
- **GestionUtilisateur.php** - Gestion comptes utilisateurs
- **GestionParamUser.php** - Paramètres utilisateur

**Fonctionnalités** :
- Authentification par login/password
- Gestion des profils (admin, gestionnaire, arbitre)
- Tokens de session
- Historique connexions

---

### 2. Gestion Compétitions

#### Compétitions
- **GestionCompetition.php** - Création/modification compétitions
- **GestionCopieCompetition.php** - Duplication compétition
- **GestionEvenement.php** - Gestion événements
- **GestionSchema.php** - Schémas de compétition (poules, élimination directe)

**Fonctionnalités** :
- Création compétitions (championnat, tournoi, coupe)
- Configuration phases (poules, phases finales)
- Gestion calendrier
- Paramétrage règles de classement
- Copie/duplication compétitions

---

#### Journées & Sessions
- **GestionJournee.php** - Gestion journées de compétition
- **GestionCalendrier.php** - Calendrier compétitions
- **GestionParamJournee.php** - Paramètres journée (lieu, date, terrains)
- **Autocompl_session_journee.php** - Autocomplete sessions
- **Autocompl_refJournee.php** - Autocomplete journées

**Fonctionnalités** :
- Création/modification journées
- Affectation lieu et terrains
- Gestion horaires
- Validation journées

---

#### Classements
- **GestionClassement.php** - Gestion classements
- **GestionClassementInit.php** - Initialisation classements
- **FeuilleCltChpt.php** - PDF Classement championnat
- **FeuilleCltChptDetail.php** - PDF Classement détaillé
- **FeuilleCltNiveau.php** - PDF Classement par niveau
- **FeuilleCltNiveauDetail.php** - PDF Classement niveau détaillé
- **FeuilleCltNiveauJournee.php** - PDF Classement journée
- **FeuilleCltNiveauNiveau.php** - PDF Classements multi-niveaux
- **FeuilleCltNiveauPhase.php** - PDF Classement phase

**Fonctionnalités** :
- Calculs automatiques classements
- Gestion points (victoire, nul, défaite, forfait)
- Gestion goal-average
- Classements par phase/niveau/journée
- Export PDF classements
- **⭐ Consolidation des phases** (Coupe uniquement) :
  - Figer le classement de phases spécifiques
  - Empêcher le recalcul automatique des phases consolidées
  - Préserver les classements finalisés ou ajustés manuellement
  - Case à cocher par phase (administrateurs profile ≤ 4)
  - Champs de classement en lecture seule quand consolidé
  - Voir [CONSOLIDATION_PHASES_CLASSEMENT.md](CONSOLIDATION_PHASES_CLASSEMENT.md)

---

### 3. Gestion Équipes & Joueurs

#### Équipes
- **GestionEquipe.php** - Gestion équipes
- **GestionEquipeJoueur.php** - Affectation joueurs aux équipes
- **GestionMatchEquipeJoueur.php** - Composition équipes par match
- **Autocompl_equipe.php** - Autocomplete équipes
- **ajax_update_team.php** - Mise à jour équipe AJAX

**Fonctionnalités** :
- Création/modification équipes
- Affectation club
- Gestion compositions (titulaires/remplaçants)
- Historique équipes
- Validation compositions

---

#### Joueurs (Athlètes)
- **GestionAthlete.php** - Gestion joueurs/athlètes
- **RechercheLicence.php** - Recherche licences FFCK
- **RechercheLicenceIndi2.php** - Recherche licence individuelle
- **ImportPCE.php** - Import licences FFCK (PCE)
- **Autocompl_joueur.php** - Autocomplete joueur
- **Autocompl_joueur2.php** - Autocomplete joueur v2
- **Autocompl_joueur3.php** - Autocomplete joueur v3
- **Autocompl_getCompo.php** - Autocomplete composition
- **InitTitulaireJQ.php** - Init titulaires jQuery

**Fonctionnalités** :
- Gestion joueurs (nom, prénom, licence, date naissance)
- Import automatique licences FFCK (fichier PCE)
- Recherche licenciés FFCK
- Affectation à équipes
- Gestion surclassements (U21, etc.)
- Validation licences

---

### 4. Gestion Matchs

#### Saisie Scores

**Interface Classique**
- **FeuilleMarque2.php** - Interface saisie score v2
- **FeuilleMarque2stats.php** - Saisie avec stats détaillées

**Interface Moderne (v3)**
- **FeuilleMarque3.php** - Interface saisie score v3 (temps réel)
- **scoreboard.php** - Scoreboard connecté (WebSocket)
- **shotclock.php** - Chronomètre 30 secondes

**v2 API (Backend FeuilleMarque3)**
- **v2/FeuilleMarque2.php** - Backend v2
- **v2/evt_match.php** - Gestion événements match
- **v2/getChrono.php** - Get chronomètre
- **v2/setChrono.php** - Set chronomètre
- **v2/ajax_updateChrono.php** - Update chrono AJAX
- **v2/getEquipesMatch.php** - Get équipes match
- **v2/setEquipesMatch.php** - Set équipes match
- **v2/getNextGame.php** - Prochain match
- **v2/getShortGame.php** - Match résumé
- **v2/initPresents.php** - Init joueurs présents
- **v2/delJoueur.php** - Supprimer joueur
- **v2/saveArbitres.php** - Sauvegarder arbitres
- **v2/saveComments.php** - Sauvegarder commentaires
- **v2/saveNo.php** - Sauvegarder numéro
- **v2/saveOfficiel.php** - Sauvegarder officiel
- **v2/saveStatut.php** - Sauvegarder statut
- **v2/setPhaseMatch.php** - Set phase match
- **v2/setEvenementJournee.php** - Set événement journée
- **v2/autocompleteOfficiel.php** - Autocomplete officiel

**Fonctionnalités** :
- Saisie scores en temps réel
- Gestion périodes (3 périodes de 10 min)
- Chronomètre 30 secondes (shot clock)
- Événements match (buts, cartons, exclusions)
- Broadcast WebSocket (mise à jour temps réel)
- Validation scores
- Historique modifications

---

#### Feuilles de Match (PDF)
- **FeuilleListeMatchs.php** - PDF Liste matchs
- **FeuilleListeMatchsEN.php** - PDF Liste matchs EN
- **FeuilleMatchMulti.php** - PDF Multi-matchs
- **SelectFeuille.php** - Sélection feuille
- **FeuilleGroups.php** - PDF Groupes

**Fonctionnalités** :
- Génération feuilles de match (PDF)
- Support multilingue (FR/EN)
- Format A4 / A3
- QR codes pour apps mobiles
- Liste matchs par terrain

---

### 5. Gestion Arbitres & Officiels

#### Arbitres
- **Autocompl_arb.php** - Autocomplete arbitre
- **Autocompl_arb3.php** - Autocomplete arbitre v3

**Fonctionnalités** :
- Affectation arbitres aux matchs
- Gestion niveaux arbitres
- Historique arbitrages
- Activité arbitres (stats)

---

### 6. Gestion Présences & Cartons

#### Feuilles de Présence
- **FeuillePresence.php** - PDF Feuille présence
- **FeuillePresenceEN.php** - PDF Feuille présence EN
- **FeuillePresenceCat.php** - PDF Présence par catégorie
- **FeuillePresenceU21.php** - PDF Présence U21
- **FeuillePresencePhoto.php** - PDF Présence avec photos
- **FeuillePresencePhoto2.php** - PDF Présence photos v2
- **FeuillePresencePhotoRef.php** - PDF Présence photos référence
- **FeuillePresenceVisa.php** - PDF Présence visa

**Fonctionnalités** :
- Génération feuilles présence (PDF)
- Contrôle licences
- Photos joueurs
- Visa fédéral
- Verrouillage automatique (J-6)
- Déverrouillage (J+3)

---

#### Cartons & Sanctions
- **FeuilleCards.php** - PDF Cartons/sanctions
- **GestionRc.php** - Gestion responsabilités (cartons)

**Fonctionnalités** :
- Suivi cartons (vert, jaune, rouge)
- Calcul automatique sanctions (cumul)
- Notifications email
- Historique sanctions

---

### 7. Statistiques & Exports

#### Statistiques
- **GestionStats.php** - Gestion statistiques
- **FeuilleStats.php** - PDF Statistiques
- **FeuilleStatsEN.php** - PDF Stats EN

**Fonctionnalités** :
- Statistiques joueurs (buts, passes, exclusions)
- Statistiques équipes
- Statistiques compétitions
- **Licenciés par catégorie d'âge** - Répartition des licenciés FFCK ayant joué par sexe et catégorie (U16, U18, U23, U35, +35)
- Export multi-formats (PDF FR/EN, CSV)

---

#### Exports CSV/Excel
- **csv_activite_arbitres.php** - Export activité arbitres
- **csv_icf_import.php** - Import ICF (format international)
- **csv_player_list.php** - Export liste joueurs
- **csv_stats_export.php** - Export stats CSV
- **export_stats_csv.php** - Export stats (alias)
- **tableau_openspout.php** - Export OpenSpout (ODS/XLSX)

**Fonctionnalités** :
- Export ODS (LibreOffice)
- Export XLSX (Excel)
- Export CSV
- Import ICF (International Canoe Federation)
- Internationalisation (MyLang.ini)

---

### 8. Gestion Structure & Configuration

#### Structure Fédérale
- **GestionStructure.php** - Gestion structures (clubs, comités)
- **GestionInstances.php** - Gestion instances fédérales
- **FeuilleInstances.php** - PDF Instances
- **Autocompl_club.php** - Autocomplete club
- **Autocompl_club2.php** - Autocomplete club v2

**Fonctionnalités** :
- Gestion clubs
- Gestion comités régionaux
- Gestion instances (bureau, commissions)
- Hiérarchie structures

---

#### Configuration
- **GestionDoc.php** - Gestion documents
- **GestionJournal.php** - Journal modifications
- **GestionOperations.php** - Gestion opérations
- **GestionGroupe.php** - Gestion groupes utilisateurs
- **FeuilleControle.php** - PDF Contrôle
- **FeuilleControleEN.php** - PDF Contrôle EN

**Fonctionnalités** :
- Upload documents (règlements, comptes-rendus)
- Logs modifications
- Contrôle cohérence données
- Gestion droits utilisateurs

---

### 9. Outils & Utilitaires

#### AJAX & Autocomplétion
- **ajax_masquer.php** - Toggle bannière
- **ajax_update_team.php** - Update équipe
- **Autocompl_compet.php** - Autocomplete compétition
- **Autocompl_compet2.php** - Autocomplete compétition v2
- **Autocompl_ville.php** - Autocomplete ville

**Fonctionnalités** :
- Autocomplétion formulaires
- Updates AJAX temps réel
- Interface dynamique

---

#### jQuery & JavaScript
- **UpdateCellJQ.php** - Update cellule jQuery
- **VerrouCompetJQ.php** - Verrouillage compétition jQuery

**Fonctionnalités** :
- Édition inline (DataTables)
- Verrouillage compétitions
- Validation côté client

---

#### Uploads & Imports
- **upload.php** - Upload fichiers
- **xml_icf_import.php** - Import XML ICF
- **xmlparser.php** - Parser XML
- **pclzip.lib.php** - Bibliothèque ZIP

**Fonctionnalités** :
- Upload logos, photos
- Import données ICF (XML)
- Compression/décompression

---

#### Statuts & Gestion
- **v2/StatutCompet.php** - Statut compétition
- **v2/StatutJournee.php** - Statut journée
- **v2/StatutPeriode.php** - Statut période
- **v2/StatutSession.php** - Statut session

**Fonctionnalités** :
- Gestion statuts (brouillon, validé, terminé)
- Workflow validation
- Contrôle cohérence

---

### 10. Tests & Développement

- **test_bootstrap538.php** - Test Bootstrap 5.3.8
- **index.php** - Dashboard admin

---

## 🌐 Partie Publique (Frontend)

**Localisation** : `sources/` (racine)
**Accès** : Public (pas d'authentification)
**Rôle** : Consultation résultats, calendriers, statistiques

### 1. Pages Publiques (kp*)

#### Compétitions & Calendrier
- **kpcalendrier.php** - Calendrier compétitions
- **kpdetails.php** - Détails compétition/journée
- **kphistorique.php** - Historique compétition

**Fonctionnalités** :
- Affichage calendrier annuel
- Filtres par niveau, catégorie
- Historique compétitions (archives)

---

#### Classements & Résultats
- **kpclassement.php** - Classement compétition
- **kpclassements.php** - Classements multiples
- **kpphases.php** - Phases/poules compétition
- **kpchart.php** - Graphiques classements

**Fonctionnalités** :
- Classements temps réel
- Phases finales (tableaux élimination directe)
- Graphiques évolution classement
- Export PDF classements

---

#### Matchs
- **kpmatchs.php** - Liste matchs
- **kpterrains.php** - Matchs par terrain
- **kptv.php** - Affichage TV matchs
- **kptvscenario.php** - Scénario rotation TV

**Fonctionnalités** :
- Liste matchs (à venir, en cours, terminés)
- Affichage par terrain
- Mode TV (rotation automatique)
- Scores temps réel

---

#### Équipes & Clubs
- **kpequipes.php** - Fiche équipe
- **kpclubs.php** - Liste clubs
- **kplogos.php** - Logos clubs

**Fonctionnalités** :
- Palmarès équipe
- Composition équipe
- Statistiques équipe
- Logos clubs

---

#### Statistiques
- **kpstats.php** - Statistiques générales
- **kpqr.php** - QR codes

**Fonctionnalités** :
- Classements buteurs
- Classements passeurs
- Stats compétitions
- QR codes apps mobiles

---

#### Administration Publique
- **kpadmin.php** - Administration publique (?)

---

### 2. Affichages Écrans (frame_*)

**Usage** : Affichage sur écrans/téléviseurs (gymnases, salles)
**Format** : Généralement en iframe ou fullscreen
**Scénarios** : Rotation automatique entre pages

#### Affichages Compétition
- **frame_classement.php** - Classement sur écran
- **frame_phases.php** - Phases/poules sur écran
- **frame_matchs.php** - Matchs sur écran
- **frame_terrains.php** - Planning terrains sur écran
- **frame_details.php** - Détails journée sur écran

**Fonctionnalités** :
- Affichage plein écran
- Rotation automatique (scenarios)
- Rafraîchissement automatique
- Design optimisé grands écrans

---

#### Affichages Équipes & Stats
- **frame_equipes.php** - Liste équipes sur écran
- **frame_team.php** - Fiche équipe sur écran
- **frame_stats.php** - Statistiques sur écran
- **frame_chart.php** - Graphiques sur écran
- **frame_categories.php** - Catégories sur écran

**Fonctionnalités** :
- Compositions équipes
- Stats joueurs/équipes
- Graphiques dynamiques
- Catégories d'âge

---

#### Outils
- **frame_qr.php** - QR codes sur écran

---

## 📹 Incrustations Vidéo (Live Streaming)

**Localisation** : `sources/live/`
**Usage** : Incrustations pour streaming vidéo (OBS, vMix, etc.)
**Format** : Overlay HTML transparent (chroma key)

### 1. Scores & Résultats

#### Scores Génériques
- **score.php** - Score basique
- **score_e.php** - Score équipe (?)
- **score_o.php** - Score overlay (?)
- **score_s.php** - Score simple (?)
- **scoreHD.php** - Score HD

**Scores Club**
- **score_club.php** - Score club
- **score_club_e.php** - Score club équipe
- **score_club_o.php** - Score club overlay
- **score_club_s.php** - Score club simple

**Fonctionnalités** :
- Affichage score temps réel
- Design transparent (chroma key)
- Multi-variantes (selon besoin)
- Support HD

---

#### Équipes
- **teams.php** - Compositions équipes
- **teams_club.php** - Compositions clubs
- **liveteams.php** - Équipes live

**Fonctionnalités** :
- Affichage compositions
- Numéros + noms joueurs
- Mise à jour temps réel

---

#### Prochain Match
- **next_game.php** - Prochain match
- **next_game_club.php** - Prochain match club

**Fonctionnalités** :
- Affichage match suivant
- Heure, terrain, équipes
- Countdown (?)

---

### 2. Affichages TV

#### TV Multi-Matchs
- **tv.php** - TV principale
- **tv2.php** - TV secondaire
- **multi_score.php** - Multi-scores
- **multi_score2.php** - Multi-scores v2

**Fonctionnalités** :
- Affichage multi-matchs
- Grille scores
- Rotation automatique

---

#### Listes & Plannings
- **liste_matchHD.php** - Liste matchs HD
- **matchs.php** - Liste matchs
- **terrain.php** - Planning terrain

**Fonctionnalités** :
- Liste matchs du jour
- Planning par terrain
- Format HD optimisé

---

### 3. Présentations & Templates

#### Présentations
- **presentation.php** - Présentation générique
- **presentationHD.php** - Présentation HD

**Templates de Base**
- **base.php** - Template base
- **base_1.php** - Template base v1
- **page.php** - Page générique
- **schema.php** - Schéma layout (?)

**Fonctionnalités** :
- Templates réutilisables
- Design personnalisable
- Support HD

---

### 4. Gestion Scénarios

#### Scénarios & Cache
- **scenario.php** - Gestion scénarios rotation
- **create_cache.php** - Création cache
- **create_cache_match.php** - Cache match
- **force_cache_match.php** - Forcer cache match
- **cache_transfert.php** - Transfert cache

**AJAX Refresh**
- **ajax_refresh_scene.php** - Refresh scène
- **ajax_refresh_tv.php** - Refresh TV
- **ajax_refresh_voie.php** - Refresh voie
- **ajax_change_tv.php** - Changer TV
- **ajax_change_voie.php** - Changer voie

**Cache Pitch**
- **ajax_cache_event.php** - Cache événement
- **ajax_cache_pitch.php** - Cache terrain

**Fonctionnalités** :
- Scénarios rotation automatique
- Gestion cache pour performance
- Changement scènes AJAX
- Synchronisation multi-écrans

---

### 5. WebSocket & Events

#### WebSocket
- **event_ws.php** - Événements WebSocket
- **event.php** - Événements génériques
- **event_ably.php** - Événements Ably (?)

**Fonctionnalités** :
- Mise à jour temps réel
- Broadcast événements (buts, cartons)
- Synchronisation multi-clients

---

### 6. Outils & Tests

#### Utilities
- **splitter.php** - Splitter (?)
- **spliturl.php** - Split URL (?)
- **get_sec.php** - Get secondes (?)

**Tests**
- **test_curl.php** - Test CURL
- **test_ftp.php** - Test FTP
- **test_transfert.php** - Test transfert

**Fonctionnalités** :
- Tests connectivité
- Debug transferts
- Utilitaires divers

---

## 📄 Génération PDF (Public)

**Localisation** : `sources/` (racine)
**Préfixe** : `Pdf*`
**Usage** : PDFs destinés au public (impression, téléchargement)
**Bibliothèque** : mPDF v8.2+

### 1. Classements

#### Championnats
- **PdfCltChpt.php** - PDF Classement championnat
- **PdfCltChptDetail.php** - PDF Classement championnat détaillé

**Classements Niveaux**
- **PdfCltNiveau.php** - PDF Classement niveau
- **PdfCltNiveauDetail.php** - PDF Classement niveau détaillé
- **PdfCltNiveauJournee.php** - PDF Classement journée
- **PdfCltNiveauNiveau.php** - PDF Classements multi-niveaux
- **PdfCltNiveauPhase.php** - PDF Classement phase

**Fonctionnalités** :
- Export PDF classements
- Formats détaillés ou résumés
- Multi-niveaux, multi-phases
- Impression A4

---

### 2. Listes de Matchs

#### Formats Génériques
- **PdfListeMatchs.php** - PDF Liste matchs standard
- **PdfListeMatchsEN.php** - PDF Liste matchs EN

**Formats 4 Terrains**
- **PdfListeMatchs4Terrains.php** - PDF 4 terrains
- **PdfListeMatchs4TerrainsEn.php** - PDF 4 terrains EN
- **PdfListeMatchs4TerrainsEn2.php** - PDF 4 terrains EN v2
- **PdfListeMatchs4TerrainsEn3.php** - PDF 4 terrains EN v3
- **PdfListeMatchs4TerrainsEn4.php** - PDF 4 terrains EN v4

**Fonctionnalités** :
- Planning matchs par terrain
- Support multilingue (FR/EN)
- Formats 1, 2, 3, 4 terrains
- Optimisation A4/A3

---

### 3. Feuilles Multi-Matchs

- **PdfMatchMulti.php** - PDF Multi-matchs

**Fonctionnalités** :
- Plusieurs matchs par page
- Format condensé
- Impression optimisée

---

### 4. QR Codes

- **PdfQrCodes.php** - PDF QR codes multiples
- **PdfQrCodeApp.php** - PDF QR code application

**Fonctionnalités** :
- Génération QR codes
- Liens vers apps mobiles
- Liens vers pages web

---

## 🔌 API & Intégrations

**Localisation** : `sources/api/`
**Format** : JSON
**Usage** : Applications mobiles, intégrations tierces

### API REST

- **index.php** - Router API

**Endpoints** (à documenter plus en détail) :
- `/api/competitions` - Liste compétitions
- `/api/matchs` - Liste matchs
- `/api/classements` - Classements
- `/api/equipes` - Équipes
- `/api/joueurs` - Joueurs
- `/api/stats` - Statistiques

**Fonctionnalités** :
- Authentification par token
- Réponses JSON
- Support CORS
- Rate limiting (à implémenter)

---

### WordPress

**Localisation** : `sources/wordpress/`
**Usage** : Intégration site vitrine WordPress

**Fonctionnalités** :
- Page d'accueil (blog)
- Actualités
- Galerie photos (NextGen Gallery)
- Intégration widgets KPI

---

## 📱 Applications Vue.js/Nuxt

### 1. App2 (Nuxt 4) - Application Moderne

**Localisation** : `sources/app2/`
**Framework** : Nuxt 4 + Vue 3 + TypeScript
**Port Dev** : 3002
**URL** : `/app2`

**Fonctionnalités** :
- PWA (Progressive Web App)
- Interface moderne
- Tailwind CSS 4
- Pinia (state management)
- i18n (multilingue)
- Nuxt UI (composants)

**Statut** : ✅ En développement actif

---

### 2. App Dev (Vue 3) - Application Legacy Principale

**Localisation** : `sources/app_dev/`
**Framework** : Vue 3
**Usage** : Application principale historique

**Statut** : ⏸️ Maintenance

---

### 3. App Live Dev (Vue 3) - Scores Live

**Localisation** : `sources/app_live_dev/`
**Framework** : Vue 3
**Usage** : Affichage scores en direct
**Communication** : WebSocket avec app_wsm_dev

**Fonctionnalités** :
- Scores temps réel
- WebSocket client
- Multi-matchs
- Notifications

**Statut** : ⏸️ Maintenance

---

### 4. App WSM Dev (Vue 3) - Saisie Matchs

**Localisation** : `sources/app_wsm_dev/`
**Framework** : Vue 3
**Usage** : Saisie scores (Web Score Manager)
**Communication** : WebSocket broadcaster

**Fonctionnalités** :
- Saisie scores temps réel
- WebSocket serveur/broadcaster
- Événements match (buts, cartons)
- Chronomètre

**Statut** : ⏸️ Maintenance

---

## 🏗️ Architecture Technique

### Stack Technique

| Composant | Technologie | Version |
|-----------|-------------|---------|
| **Backend** | PHP | 8.4.13 (tests), 7.4.33 (prod) |
| **Base de données** | MySQL | 8.x |
| **Serveur Web** | Apache | 2.x |
| **Templates** | Smarty | 4.x |
| **Frontend Moderne** | Nuxt | 4.x |
| **Frontend Legacy** | Vue | 3.x |
| **CSS Framework** | Bootstrap | 5.3.8 |
| **CSS Modern** | Tailwind CSS | 4.x (app2) |
| **PDF** | mPDF | 8.2+ |
| **Excel/ODS** | OpenSpout | 4.32.0 |
| **Infrastructure** | Docker | Compose |

---

### Base de Données

**Tables principales** (extrait) :
- `kp_competition` - Compétitions
- `kp_journee` - Journées
- `kp_match` - Matchs
- `kp_equipe` - Équipes
- `kp_licencie` - Licenciés/joueurs
- `kp_arbitre` - Arbitres
- `kp_evt_match` - Événements match
- `kp_classement` - Classements
- `kp_compo` - Compositions équipes
- `kp_user` - Utilisateurs

**Total** : ~50+ tables

---

### Intégrations Externes

#### FFCK (Fédération Française de Canoë-Kayak)
- **Import PCE** - Fichier licences quotidien
- **URL** : `https://extranet.ffck.org/reportingExterne/getFichierPce/{YEAR}`
- **Format** : Fichier texte structuré
- **Sections** : [licencies], [juges_kap], [surclassements]
- **Fréquence** : Quotidienne (CRON 2h00)

---

#### WebSocket / Broadcasting
- **Usage** : Communication temps réel
- **Apps** : FeuilleMarque3 ↔ Live Scores
- **Technologie** : WebSocket natif ou Ably (?)

---

### CRON Jobs

**Fichiers** : `sources/commun/cron_*.php`

1. **cron_maj_licencies.php** - Import licences PCE (quotidien 2h)
2. **cron_verrou_presences.php** - Verrouillage présences (toutes les 6h)
   - Verrouillage : J-6
   - Déverrouillage : J+3

**Logs** :
- `/var/www/html/commun/log_cron.txt`
- `/var/www/html/commun/log_cards.txt` (sanctions)

---

## 📊 Statistiques Projet

### Volumétrie Code

| Type | Nombre |
|------|--------|
| **Fichiers PHP Admin** | ~100 fichiers |
| **Fichiers PHP Public** | ~50 fichiers |
| **Fichiers PHP Live** | ~50 fichiers |
| **Templates Smarty** | 88 templates |
| **Fichiers Vue/Nuxt** | ~32,000+ (node_modules inclus) |
| **Fichiers SQL** | ~30 migrations |

---

### Fonctionnalités par Module

| Module | Nombre Fonctionnalités |
|--------|------------------------|
| **Gestion Compétitions** | ~15 |
| **Gestion Matchs** | ~20 |
| **Gestion Équipes/Joueurs** | ~10 |
| **Génération PDF** | ~25 |
| **Affichages Publics** | ~15 |
| **Incrustations Vidéo** | ~30 |
| **Exports/Imports** | ~10 |
| **API** | ~10 endpoints |
| **Applications Frontend** | 4 apps |

**Total estimé** : **150+ fonctionnalités**

---

## 🎯 Use Cases Principaux

### 1. Gestionnaire de Compétition

**Workflow type** :
1. Création compétition (championnat/tournoi)
2. Paramétrage phases (poules, phases finales)
3. Création journées (dates, lieux, terrains)
4. Affectation équipes
5. Génération feuilles présence
6. Saisie scores (FeuilleMarque3)
7. Validation journée
8. Publication classements

---

### 2. Arbitre

**Workflow type** :
1. Consultation matchs affectés
2. Validation compositions équipes
3. Saisie score sur tablette (FeuilleMarque3)
4. Gestion cartons/exclusions
5. Validation feuille de match

---

### 3. Public

**Workflow type** :
1. Consultation calendrier (kpcalendrier.php)
2. Consultation matchs du jour (kpmatchs.php)
3. Suivi scores live (frame_matchs.php)
4. Consultation classements (kpclassement.php)
5. Téléchargement PDF (PdfClassement.php)

---

### 4. Réalisateur Vidéo (Streaming)

**Workflow type** :
1. Configuration OBS/vMix
2. Ajout sources HTML (live/score.php)
3. Chroma key (fond vert)
4. Scénarios rotation (live/scenario.php)
5. Broadcast match avec incrustations

---

## 📚 Documentation Connexe

### Migrations Techniques
- [PHP8_MIGRATION_SUMMARY.md](PHP8_MIGRATION_SUMMARY.md) - Migration PHP 8
- [MIGRATION_FPDF_MYPDF_SUCCESS.md](MIGRATION_FPDF_MYPDF_SUCCESS.md) - Migration PDF
- [MIGRATION_OPENTBS_TO_OPENSPOUT.md](MIGRATION_OPENTBS_TO_OPENSPOUT.md) - Migration Excel
- [BOOTSTRAP_MIGRATION_STATUS.md](BOOTSTRAP_MIGRATION_STATUS.md) - Migration Bootstrap

### Architecture
- [AUDIT_PHASE_0.md](AUDIT_PHASE_0.md) - Audit complet projet
- [CRON_DOCUMENTATION.md](CRON_DOCUMENTATION.md) - Documentation CRON

### Configuration
- [CLAUDE.md](../CLAUDE.md) - Guide développement
- [Makefile](../Makefile) - Commandes projet

---

## 🔗 URLs Production

**Site Principal** : https://kayak-polo.info
**WordPress** : https://kayak-polo.info/wordpress
**Admin** : https://kayak-polo.info/admin
**API** : https://kayak-polo.info/api

---

## ✅ Conclusion

### Points Forts

- ✅ **Système complet** - Gestion A à Z compétitions
- ✅ **Temps réel** - WebSocket, scores live
- ✅ **Multi-usage** - Admin, public, streaming, écrans
- ✅ **Multilingue** - FR/EN
- ✅ **Multi-formats** - Web, PDF, CSV, ODS, XLSX
- ✅ **Moderne** - Nuxt 4, Vue 3, PHP 8, Bootstrap 5

### Défis

- ⚠️ Code legacy (PHP, jQuery)
- ⚠️ 4 applications frontend (consolidation en cours)
- ⚠️ Documentation utilisateur limitée
- ⚠️ Tests automatisés à développer

### Évolution Future

1. **Court terme** : Finaliser migration PHP 8
2. **Moyen terme** : Consolidation app2 (Nuxt)
3. **Long terme** : Framework PHP moderne (Symfony/Laravel)

---

**Auteur** : Laurent Garrigue / Claude Code
**Date** : 31 octobre 2025
**Version** : 1.0
**Statut** : 📋 **Inventaire Complet**
