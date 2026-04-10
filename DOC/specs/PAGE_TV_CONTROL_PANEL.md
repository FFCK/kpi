# Spécification - Page TV Control Panel

## Statut : 📋 À IMPLÉMENTER

## 1. Vue d'ensemble

Page d'administration du contrôle des affichages TV destinés au live streaming (incrustations/overlays) et aux écrans d'information pour compétiteurs et public. Permet de sélectionner un événement, configurer et activer des présentations sur des channels (voies) qui sont consultés par les pages d'affichage via un système de polling JSON.

La page se compose de deux onglets principaux :
- **Channels** : Contrôle en temps réel des channels d'affichage (activation de présentations)
- **Scénarios** : Définition de séquences automatisées de présentations (rotation d'URLs avec délais)

**Route** : `/tv`

**Accès** : Profil ≤ 2 (Admin)

**Pages PHP Legacy** :
- `kptv.php` + `kptv.tpl` + `kptv.js` (contrôle principal)
- `kptvscenario.php` + `kptvscenario.tpl` + `kptvscenario.js` (édition scénarios)

**Implémentation Nuxt** : `sources/app4/pages/tv/index.vue`

**Contexte de travail** : N'utilise PAS le `workContextStore` global. L'événement et la saison sont gérés localement sur la page, car le contexte TV est indépendant du périmètre administratif classique.

---

## 2. Concepts clés

### 2.1 Channels (Voies)

Un **channel** (voie) est un numéro identifiant un écran ou une couche d'incrustation. Chaque channel pointe vers une URL qui est affichée par la page de rendu (`live/tv2.php`).

| Plage | Usage |
|-------|-------|
| 1-4 | Terrains (Pitch 1-4) |
| 5-40 | Channels libres |
| 41-50 | Tests |

Le contenu d'un channel est modifié via un appel AJAX qui :
1. Met à jour l'URL dans la table `kp_tv`
2. Écrit un fichier cache JSON `/live/cache/voie_{n}.json`

Les pages d'affichage (TV) consultent ce fichier cache par polling et se rechargent quand l'URL change.

### 2.2 Scénarios

Un **scénario** est un groupe de 9 channels (scènes) qui fonctionnent en rotation automatique. Chaque scène a une URL et un délai d'affichage en millisecondes.

| Plage | Scénario |
|-------|----------|
| 101-109 | Scénario 1 |
| 201-209 | Scénario 2 |
| 301-309 | Scénario 3 |
| 401-409 | Scénario 4 |
| 501-509 | Scénario 5 (TV 1) |
| 601-609 | Scénario 6 (TV 2) |
| 701-709 | Scénario 7 (TV 3) |
| 801-809 | Scénario 8 (TV 4) |
| 901-909 | Scénario 9 |

### 2.3 Présentations

Une **présentation** est un type de contenu affiché sur un channel. Chaque présentation nécessite des paramètres spécifiques.

| Catégorie | Présentations | Paramètres requis |
|-----------|---------------|-------------------|
| **Général** | `empty`, `voie`, `logo` | Aucun (CSS uniquement) |
| **Avant match** | `match`, `match2`, `list_team`, `list_coachs`, `team`, `referee`, `player`, `coach` | Match, Team (A/B), Numéro joueur |
| **Match en cours (nations)** | `score`, `score_o`, `score_e`, `score_s`, `teams`, `next_game` | Pitch, Speaker |
| **Match en cours (clubs)** | `score_club`, `score_club_o`, `score_club_e`, `score_club_s`, `teams_club`, `next_game_club`, `liveteams` | Pitch, Speaker |
| **Match en cours (WS)** | `live` | Pitch, Zone, Mode |
| **Présentation match** | `match_score` | Match, Animate |
| **Après match** | `final_ranking`, `podium` | Competition, Start/Animate |
| **Écrans d'affichage** | `multi_score`, `frame_categories`, `frame_terrains`, `frame_chart`, `frame_phases`, `frame_details`, `frame_team`, `frame_stats`, `frame_classement`, `frame_qr` | Competition, Pitchs, Round, etc. |
| **Site/Mobile** | `frame_matchs` | Competition, Navbar |
| **API** | `api_players`, `api_stats` | Competitions, Format, Option |
| **Cache** | `force_cache_match` | Match |
| **Debug** | `player_pictures` | Aucun |

### 2.4 Labels personnalisés

Chaque channel et scénario peut recevoir un **label personnalisé** stocké en base de données. Cela permet de nommer les channels selon l'événement en cours (ex: "Pitch 1 - Score", "Écran hall", "Streaming overlay 1").

---

## 3. Fonctionnalités

### 3.1 Onglet Channels - Barre globale

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Sélection événement (dropdown des événements publiés) | ≤ 2 | Essentielle | ✅ Conserver |
| 2 | Filtre par date (dropdown des dates de matchs de l'événement) | ≤ 2 | Essentielle | ✅ Conserver |
| 3 | Sélection du style CSS (thème graphique des overlays) | ≤ 2 | Essentielle | ✅ Conserver |
| 4 | Sélection langue (EN/FR) | ≤ 2 | Essentielle | ✅ Conserver |
| 5 | Sauvegarde automatique des filtres globaux en session/localStorage | ≤ 2 | Essentielle | ✅ Conserver |

### 3.2 Onglet Channels - Panneaux de contrôle

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 10 | Affichage dynamique de N panneaux de contrôle (ajout/retrait à la demande) | ≤ 2 | Essentielle | ✅ Nouveau |
| 11 | Bouton "+" pour ajouter un panneau de contrôle | ≤ 2 | Essentielle | ✅ Nouveau |
| 12 | Bouton "×" pour retirer un panneau de contrôle | ≤ 2 | Essentielle | ✅ Nouveau |
| 13 | Sélection du channel (1-50 + scénarios 101-909) dans chaque panneau | ≤ 2 | Essentielle | ✅ Conserver |
| 14 | Labels personnalisés affichés dans le dropdown de channels | ≤ 2 | Essentielle | ✅ Nouveau |
| 15 | Sélection de la présentation (dropdown groupé par catégorie) | ≤ 2 | Essentielle | ✅ Conserver |
| 16 | Paramètres conditionnels selon la présentation sélectionnée | ≤ 2 | Essentielle | ✅ Conserver |
| 17 | Sélection compétition (dropdown des compétitions de l'événement) | ≤ 2 | Essentielle | ✅ Conserver |
| 18 | Sélection match (dropdown filtré par compétition + date) | ≤ 2 | Essentielle | ✅ Conserver |
| 19 | Sélection équipe A/B | ≤ 2 | Essentielle | ✅ Conserver |
| 20 | Sélection équipe (dropdown des équipes de l'événement) | ≤ 2 | Essentielle | ✅ Conserver |
| 21 | Sélection numéro joueur (0-21) | ≤ 2 | Essentielle | ✅ Conserver |
| 22 | Boutons rapides numéros joueurs (grille de boutons 0-21) | ≤ 2 | Utile | ✅ Conserver |
| 23 | Sélection pitch (1-8) | ≤ 2 | Essentielle | ✅ Conserver |
| 24 | Liste de pitchs (texte libre, ex: "1,2,3,4") | ≤ 2 | Essentielle | ✅ Conserver |
| 25 | Sélection médaille (Bronze/Silver/Gold) | ≤ 2 | Essentielle | ✅ Conserver |
| 26 | Sélection zone (inter/club) | ≤ 2 | Essentielle | ✅ Conserver |
| 27 | Sélection mode (full/only/event/static) | ≤ 2 | Essentielle | ✅ Conserver |
| 28 | Sélection round (All/1-8) | ≤ 2 | Essentielle | ✅ Conserver |
| 29 | Start/Animate/Speaker/Count | ≤ 2 | Essentielle | ✅ Conserver |
| 30 | First game / Game count (pour frame_categories) | ≤ 2 | Essentielle | ✅ Conserver |
| 31 | Navbar toggle (pour frame_matchs) | ≤ 2 | Essentielle | ✅ Conserver |
| 32 | Competitions (texte libre, ex: "CMH,CMF" pour API) | ≤ 2 | Essentielle | ✅ Conserver |
| 33 | Format (json/csv pour API) | ≤ 2 | Essentielle | ✅ Conserver |
| 34 | Option (filtre joueurs pour API) | ≤ 2 | Essentielle | ✅ Conserver |

### 3.3 Onglet Channels - Actions par panneau

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 40 | Bouton **Activer** : active la présentation sur le channel (écrit en DB + cache JSON) | ≤ 2 | Essentielle | ✅ Conserver |
| 41 | Bouton **Blank** : envoie une page vide sur le channel | ≤ 2 | Essentielle | ✅ Conserver |
| 42 | Bouton **URL** : génère et affiche l'URL sans l'activer (pour copier/partager) | ≤ 2 | Essentielle | ✅ Conserver |
| 43 | Champ URL en lecture seule affichant l'URL générée | ≤ 2 | Essentielle | ✅ Conserver |
| 44 | Bouton **Contrôle** : ouvre la page TV du channel dans un nouvel onglet | ≤ 2 | Essentielle | ✅ Conserver |
| 45 | Bouton **Report** : ouvre le PDF de feuille de marque du match sélectionné | ≤ 2 | Utile | ✅ Conserver |
| 46 | Vignette de prévisualisation (image statique de la présentation sélectionnée) | ≤ 2 | Utile | ✅ Conserver |
| 47 | Feedback visuel (toast) après activation | ≤ 2 | Essentielle | ✅ Nouveau |

### 3.4 Onglet Scénarios

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 50 | Sélection du scénario à éditer (dropdown : Scénario 1-9) | ≤ 2 | Essentielle | ✅ Conserver |
| 51 | Tableau des 9 scènes du scénario sélectionné | ≤ 2 | Essentielle | ✅ Conserver |
| 52 | Colonne Channel (numéro de voie, lecture seule) | ≤ 2 | Essentielle | ✅ Conserver |
| 53 | Colonne URL (champ texte éditable, URL de la présentation) | ≤ 2 | Essentielle | ✅ Conserver |
| 54 | Colonne Delay (durée d'affichage en millisecondes) | ≤ 2 | Essentielle | ✅ Conserver |
| 55 | Bouton **Update** : sauvegarde les URLs et délais du scénario | ≤ 2 | Essentielle | ✅ Conserver |
| 56 | Bouton **Test** : active le scénario pour visualisation | ≤ 2 | Essentielle | ✅ Conserver |
| 57 | Feedback de sauvegarde (toast de confirmation) | ≤ 2 | Essentielle | ✅ Nouveau |

### 3.5 Gestion des labels

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 60 | Modal/section de gestion des labels de channels | ≤ 2 | Essentielle | ✅ Nouveau |
| 61 | Attribution d'un label texte à chaque channel (1-50) | ≤ 2 | Essentielle | ✅ Nouveau |
| 62 | Attribution d'un label texte à chaque scénario (1-9) | ≤ 2 | Essentielle | ✅ Nouveau |
| 63 | Sauvegarde des labels en base de données | ≤ 2 | Essentielle | ✅ Nouveau |
| 64 | Affichage des labels dans les dropdowns de channels | ≤ 2 | Essentielle | ✅ Nouveau |
| 65 | Labels par défaut pour les channels 1-4 ("Pitch 1" à "Pitch 4") | ≤ 2 | Essentielle | ✅ Nouveau |

---

## 4. Structure de la page

### 4.1 Layout Desktop

```
┌─────────────────────────────────────────────────────────────────────────┐
│ TV Control Panel                                                        │
├─────────────────────────────────────────────────────────────────────────┤
│ [Onglet: Channels] [Onglet: Scénarios]              [⚙ Labels]         │
├─────────────────────────────────────────────────────────────────────────┤
│ BARRE GLOBALE                                                           │
│ Événement: [dropdown ▼]  Date: [dropdown ▼]  Style: [dropdown ▼]       │
│ Langue: [EN ▼]                                                          │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│ ┌─ Panneau Channel 1 ──────────────────────────────────────────── [×] ┐ │
│ │ Channel: [41 - Tests ▼]  Présentation: [Game (Category & teams) ▼] │ │
│ │ Competition: [ECM ▼]  Match: [#101 Pitch.1 09:30 DEN-NED ▼]       │ │
│ │                                                                     │ │
│ │ [Contrôle] [Report] [URL]  [________________________URL_________]  │ │
│ │                                               [Blank] [Activer]    │ │
│ │                                                         [🖼 img]   │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
│                                                                         │
│ ┌─ Panneau Channel 2 ──────────────────────────────────────────── [×] ┐ │
│ │ Channel: [__ ▼]  Présentation: [__ ▼]                              │ │
│ │ ...                                                                 │ │
│ └─────────────────────────────────────────────────────────────────────┘ │
│                                                                         │
│                        [+ Ajouter un panneau]                           │
│                                                                         │
├─────────────────────────────────────────────────────────────────────────┤
│ Liens rapides: [Event cache generator] [Split URL] [Scenario Live]      │
└─────────────────────────────────────────────────────────────────────────┘
```

### 4.2 Layout Onglet Scénarios

```
┌─────────────────────────────────────────────────────────────────────────┐
│ [Onglet: Channels] [Onglet: Scénarios]              [⚙ Labels]         │
├─────────────────────────────────────────────────────────────────────────┤
│ Scénario: [Scénario 1 - TV Principale ▼]     [Rafraîchir]              │
├───────────┬─────────────────────────────────────────────┬───────────────┤
│ Channel   │ URL                                         │ Delay (ms)    │
├───────────┼─────────────────────────────────────────────┼───────────────┤
│ 101       │ [live/tv2.php?show=list_team&saison=1&...]  │ [15000]       │
│ 102       │ [frame_chart.php?event=149&lang=en&...]     │ [15000]       │
│ 103       │ [                                        ]  │ [15000]       │
│ 104       │ [                                        ]  │ [8000]        │
│ 105       │ [                                        ]  │ [8000]        │
│ 106       │ [                                        ]  │ [8000]        │
│ 107       │ [                                        ]  │ [10000]       │
│ 108       │ [                                        ]  │ [10000]       │
│ 109       │ [                                        ]  │ [10000]       │
├───────────┴─────────────────────────────────────────────┴───────────────┤
│                    [Update]              [Test]                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### 4.3 Layout Mobile

```
┌───────────────────────────────┐
│ TV Control Panel              │
├───────────────────────────────┤
│ [Channels] [Scénarios] [⚙]   │
├───────────────────────────────┤
│ Événement: [dropdown ▼]      │
│ Date: [dropdown ▼]           │
│ Style: [dropdown ▼]          │
│ Lang: [EN ▼]                 │
├───────────────────────────────┤
│ ┌─ Channel 1 ──────── [×] ─┐ │
│ │ Ch: [41 ▼]               │ │
│ │ Prés: [Game ▼]           │ │
│ │ Compet: [ECM ▼]          │ │
│ │ Match: [#101 DEN-NED ▼]  │ │
│ │                           │ │
│ │ [Ctrl] [Report] [URL]    │ │
│ │ [URL_________________]   │ │
│ │ [Blank]    [Activer]     │ │
│ └───────────────────────────┘ │
│                               │
│      [+ Ajouter panneau]     │
└───────────────────────────────┘
```

### 4.4 Modal Labels

```
┌─────────────────────────────────────────────────┐
│ Labels des Channels et Scénarios          [×]   │
├─────────────────────────────────────────────────┤
│ CHANNELS                                        │
│ ┌────────┬──────────────────────────────────┐   │
│ │ Ch. 1  │ [Pitch 1 - Score live        ]   │   │
│ │ Ch. 2  │ [Pitch 2 - Score live        ]   │   │
│ │ Ch. 3  │ [Pitch 3 - Score live        ]   │   │
│ │ Ch. 4  │ [Pitch 4 - Score live        ]   │   │
│ │ Ch. 5  │ [Écran Hall                  ]   │   │
│ │ ...    │ ...                               │   │
│ │ Ch. 50 │ [                            ]   │   │
│ └────────┴──────────────────────────────────┘   │
│                                                 │
│ SCÉNARIOS                                       │
│ ┌────────┬──────────────────────────────────┐   │
│ │ Sc. 1  │ [TV Principale              ]   │   │
│ │ Sc. 2  │ [Écran cafétéria             ]   │   │
│ │ ...    │ ...                               │   │
│ │ Sc. 9  │ [                            ]   │   │
│ └────────┴──────────────────────────────────┘   │
│                                                 │
│                              [Annuler] [Sauver] │
└─────────────────────────────────────────────────┘
```

---

## 5. Détail des données

### 5.1 Types TypeScript

```typescript
/** Configuration d'un channel TV */
interface TvChannel {
  voie: number           // Numéro de voie (1-50, 101-909)
  url: string            // URL actuellement affichée
  intervalle: number     // Intervalle de polling en ms
}

/** Label personnalisé pour un channel ou scénario */
interface TvLabel {
  id: number
  type: 'channel' | 'scenario'  // Type de l'élément labellisé
  number: number                 // Numéro de channel (1-50) ou scénario (1-9)
  label: string                  // Label personnalisé
}

/** Panneau de contrôle (état local UI) */
interface ControlPanel {
  id: string                     // Identifiant unique (uuid)
  channel: number | null         // Channel sélectionné
  presentation: string           // Présentation sélectionnée
  competition: string            // Code compétition sélectionné
  match: number | null           // ID match sélectionné
  team: 'A' | 'B'               // Équipe sélectionnée
  teamSelect: number | null      // ID équipe sélectionnée (pour frame_team)
  number: number                 // Numéro joueur (0-21)
  pitch: number                  // Pitch (1-8)
  pitchs: string                 // Liste de pitchs (ex: "1,2,3,4")
  medal: 'BRONZE' | 'SILVER' | 'GOLD'
  zone: 'inter' | 'club'
  mode: 'full' | 'only' | 'event' | 'static'
  round: string                  // '*' ou '1'-'8'
  start: number                  // Offset pour ranking (0, 10, 20, 30)
  animate: boolean
  speaker: number                // 0, 1, 2
  count: number                  // Nombre de scores (1-4)
  lnStart: number                // Premier match (pour frame_categories)
  lnLen: number                  // Nombre de matchs
  competList: string             // Liste de compétitions (texte libre)
  format: 'json' | 'csv'
  option: number                 // 0, 1, 2 (filtre joueurs API)
  navGroup: boolean              // Afficher navbar
  generatedUrl: string           // URL générée (lecture seule)
}

/** Scène d'un scénario */
interface ScenarioScene {
  voie: number           // Numéro de voie (ex: 101, 102...)
  url: string            // URL de la présentation
  intervalle: number     // Durée d'affichage en ms
}

/** Événement pour le dropdown */
interface TvEvent {
  id: number
  libelle: string
  lieu: string
}

/** Match pour le dropdown */
interface TvMatch {
  id: number
  numeroOrdre: number
  terrain: string
  heureMatch: string
  equipeA: string
  equipeB: string
  phase: string
  codeCompetition: string
  codeSaison: string
}

/** Équipe pour le dropdown */
interface TvTeam {
  idEquipe: number
  libelleEquipe: string
}

/** Options de présentation */
interface PresentationOption {
  value: string
  label: string
  group: string
  requiredParams: string[]  // Liste des paramètres nécessaires
}
```

### 5.2 Liste des présentations avec paramètres requis

```typescript
const PRESENTATIONS: PresentationOption[] = [
  // Général
  { value: 'empty', label: 'Empty page', group: 'general', requiredParams: [] },
  { value: 'voie', label: 'Channel', group: 'general', requiredParams: [] },
  { value: 'logo', label: 'Logo', group: 'general', requiredParams: [] },

  // Avant match
  { value: 'match', label: 'Game (Category & teams)', group: 'before_game', requiredParams: ['competition', 'match'] },
  { value: 'match2', label: 'Game (Team colors)', group: 'before_game', requiredParams: ['competition', 'match'] },
  { value: 'list_team', label: 'Players list', group: 'before_game', requiredParams: ['competition', 'match', 'team'] },
  { value: 'list_coachs', label: 'Coaches list', group: 'before_game', requiredParams: ['competition', 'match', 'team'] },
  { value: 'team', label: 'Team name', group: 'before_game', requiredParams: ['competition', 'match', 'team'] },
  { value: 'referee', label: 'Referees', group: 'before_game', requiredParams: ['competition', 'match'] },
  { value: 'player', label: 'Player name', group: 'before_game', requiredParams: ['competition', 'match', 'team', 'number'] },
  { value: 'coach', label: 'Coach name', group: 'before_game', requiredParams: ['competition', 'match', 'team', 'number'] },

  // Match en cours (nations)
  { value: 'score', label: 'Live score (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'score_o', label: 'Score only (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'score_e', label: 'Events only (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'score_s', label: 'Static events (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'teams', label: 'Game & score (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'next_game', label: 'Next game (nations)', group: 'running_nations', requiredParams: ['pitch'] },

  // Match en cours (clubs)
  { value: 'score_club', label: 'Live score (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'score_club_o', label: 'Score only (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'score_club_e', label: 'Events only (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'score_club_s', label: 'Static events (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'teams_club', label: 'Game & score (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'next_game_club', label: 'Next game (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'liveteams', label: 'Teams only (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },

  // Match en cours (WebSocket)
  { value: 'live', label: 'Live score WebSocket', group: 'running_ws', requiredParams: ['pitch', 'zone', 'mode'] },

  // Présentation match
  { value: 'match_score', label: 'Game & score', group: 'match_presentation', requiredParams: ['competition', 'match', 'animate'] },

  // Après match
  { value: 'final_ranking', label: 'Final ranking', group: 'after_game', requiredParams: ['competition', 'start'] },
  { value: 'podium', label: 'Podium', group: 'after_game', requiredParams: ['competition', 'animate'] },

  // Écrans d'affichage
  { value: 'multi_score', label: 'Multi score', group: 'screen', requiredParams: ['count', 'speaker'] },
  { value: 'frame_categories', label: 'Cat. games', group: 'screen', requiredParams: ['competition', 'pitchs', 'lnStart', 'lnLen'] },
  { value: 'frame_terrains', label: 'Pitch games', group: 'screen', requiredParams: ['competition', 'pitchs'] },
  { value: 'frame_chart', label: 'Progress', group: 'screen', requiredParams: ['competition', 'round'] },
  { value: 'frame_phases', label: 'Phases', group: 'screen', requiredParams: ['competition', 'round'] },
  { value: 'frame_details', label: 'Details', group: 'screen', requiredParams: ['competition'] },
  { value: 'frame_team', label: 'Team details', group: 'screen', requiredParams: ['competition', 'teamSelect'] },
  { value: 'frame_stats', label: 'Stats', group: 'screen', requiredParams: ['competition'] },
  { value: 'frame_classement', label: 'Ranking', group: 'screen', requiredParams: ['competition'] },
  { value: 'frame_qr', label: 'QrCodes', group: 'screen', requiredParams: ['competition'] },

  // Site/Mobile
  { value: 'frame_matchs', label: 'Games', group: 'web', requiredParams: ['competition', 'navGroup'] },

  // API
  { value: 'api_players', label: 'Players', group: 'api', requiredParams: ['competList', 'format', 'option'] },
  { value: 'api_stats', label: 'Stats', group: 'api', requiredParams: ['competList', 'format', 'option'] },

  // Cache
  { value: 'force_cache_match', label: 'Force cache match', group: 'cache', requiredParams: ['competition', 'match'] },

  // Debug
  { value: 'player_pictures', label: 'Player Pictures', group: 'debug', requiredParams: [] },
]
```

### 5.3 Styles CSS disponibles

```typescript
const TV_STYLES = [
  { value: 'avranches2025', label: 'Avranches 2025' },
  { value: 'avranches2025b', label: 'Avranches 2025 Magenta' },
  { value: 'deqing2024', label: 'Deqing 2024' },
  { value: 'cna2022', label: 'CNA 2022' },
  { value: 'saintomer2022', label: 'SaintOmer 2022' },
  { value: 'saintomer2022b', label: 'SaintOmer 2022 Magenta' },
  { value: 'welland2018', label: 'Welland 2018' },
  { value: 'saintomer2017', label: 'SaintOmer 2017' },
  { value: 'thury2014', label: 'Thury 2014' },
  { value: 'usnational', label: 'US National' },
  { value: 'cna', label: 'CNA KP' },
  { value: 'simply', label: 'Simple' },
]
```

---

## 6. API Endpoints

### 6.1 Endpoints existants (conservés tels quels)

Ces endpoints legacy sont appelés directement par le frontend et ne passent PAS par l'API2 Symfony. Ils sont conservés car ils font partie du système d'affichage live existant.

#### Changement de channel
```
POST /ajax_change_tv.php
```
- Paramètres : `show`, `voie`, `match`, `team`, `number`, `medal`, `saison`, `competition`, `start`, `anime`, `css`, `lang`
- Construit l'URL complète `live/tv2.php?...` et la stocke en DB + cache JSON
- Retourne : message de confirmation texte

#### Changement de voie directe
```
POST /live/ajax_change_voie.php
```
- Paramètres : `voie` (number), `url` (string encodée avec pipes `|QU|`, `|AM|`, `|HA|`)
- Met à jour `kp_tv` et écrit le cache JSON
- Retourne : message de confirmation texte

#### Rafraîchissement voie
```
GET /live/ajax_refresh_voie.php?voie={n}
```
- Retourne l'URL actuelle du channel depuis la DB

#### Rafraîchissement scène
```
GET /live/ajax_refresh_scene.php?voie={n}
```
- Logique de rotation : trouve la prochaine scène non-vide du scénario
- Retourne l'URL et l'intervalle

### 6.2 Nouveaux endpoints API2

#### Liste des événements publiés (pour dropdown)
```
GET /api2/admin/tv/events
```
**Profil requis** : ≤ 2

**Réponse** :
```json
[
  {
    "id": 222,
    "libelle": "ECA European Championships",
    "lieu": "Avranches (FRA)",
    "dateDebut": "2025-07-15",
    "dateFin": "2025-07-20"
  }
]
```

**Logique backend** : `SELECT * FROM kp_evenement WHERE Publication = 'O' ORDER BY Date_debut DESC`

#### Matchs d'un événement (pour dropdown)
```
GET /api2/admin/tv/matches?eventId={id}&date={date}&competition={code}
```
**Profil requis** : ≤ 2

**Paramètres** :
| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| eventId | number | Oui | ID de l'événement |
| date | string | Non | Filtre par date (YYYY-MM-DD) |
| competition | string | Non | Filtre par code compétition |

**Réponse** :
```json
{
  "matches": [
    {
      "id": 79290575,
      "numeroOrdre": 101,
      "terrain": "1",
      "heureMatch": "09:30",
      "dateMatch": "2025-07-15",
      "equipeA": "DEN Men",
      "equipeB": "NED Men",
      "idEquipeA": 12345,
      "idEquipeB": 12346,
      "phase": "Group MA",
      "codeCompetition": "ECM",
      "codeSaison": "2025"
    }
  ],
  "competitions": ["ECM", "ECF", "ECU21M", "ECU21F"],
  "dates": ["2025-07-15", "2025-07-16", "2025-07-17"],
  "teams": [
    { "idEquipe": 12345, "libelleEquipe": "DEN Men" },
    { "idEquipe": 12346, "libelleEquipe": "NED Men" }
  ],
  "season": "2025"
}
```

**Logique backend** : Jointure `kp_evenement_journee` → `kp_journee` → `kp_match` → `kp_competition_equipe` (même requête que le legacy `kptv.php`)

#### Activation d'un channel
```
POST /api2/admin/tv/activate
```
**Profil requis** : ≤ 2

**Body** :
```json
{
  "voie": 1,
  "url": "live/tv2.php?show=match&match=79290575&css=avranches2025&lang=en"
}
```

**Réponse** :
```json
{
  "success": true,
  "voie": 1,
  "url": "live/tv2.php?show=match&match=79290575&css=avranches2025&lang=en"
}
```

**Logique backend** :
1. `UPDATE kp_tv SET Url = :url WHERE Voie = :voie`
2. Écriture du fichier cache JSON `/live/cache/voie_{voie}.json`

#### Envoi d'une page vide
```
POST /api2/admin/tv/blank
```
**Profil requis** : ≤ 2

**Body** :
```json
{
  "voie": 1,
  "css": "avranches2025"
}
```

**Logique** : Équivalent de `activate` avec `url = "live/tv2.php?show=empty&css={css}"`

#### Labels - Liste
```
GET /api2/admin/tv/labels
```
**Profil requis** : ≤ 2

**Réponse** :
```json
{
  "channels": [
    { "number": 1, "label": "Pitch 1 - Score live" },
    { "number": 2, "label": "Pitch 2 - Score live" },
    { "number": 5, "label": "Écran Hall" }
  ],
  "scenarios": [
    { "number": 1, "label": "TV Principale" },
    { "number": 2, "label": "Écran cafétéria" }
  ]
}
```

#### Labels - Mise à jour
```
PUT /api2/admin/tv/labels
```
**Profil requis** : ≤ 2

**Body** :
```json
{
  "channels": [
    { "number": 1, "label": "Pitch 1 - Score live" },
    { "number": 5, "label": "Écran Hall" }
  ],
  "scenarios": [
    { "number": 1, "label": "TV Principale" }
  ]
}
```

**Réponse** :
```json
{
  "success": true
}
```

#### Scénario - Lecture
```
GET /api2/admin/tv/scenario/{scenarioNumber}
```
**Profil requis** : ≤ 2

**Paramètre** : `scenarioNumber` (1-9)

**Réponse** :
```json
{
  "scenario": 1,
  "scenes": [
    { "voie": 101, "url": "live/tv2.php?show=list_team&saison=1&...", "intervalle": 15000 },
    { "voie": 102, "url": "frame_chart.php?event=149&...", "intervalle": 15000 },
    { "voie": 103, "url": "", "intervalle": 15000 },
    { "voie": 104, "url": "", "intervalle": 8000 },
    { "voie": 105, "url": "", "intervalle": 8000 },
    { "voie": 106, "url": "", "intervalle": 8000 },
    { "voie": 107, "url": "", "intervalle": 10000 },
    { "voie": 108, "url": "", "intervalle": 10000 },
    { "voie": 109, "url": "", "intervalle": 10000 }
  ]
}
```

**Logique backend** : `SELECT * FROM kp_tv WHERE Voie > {scenario * 100} AND Voie < {scenario * 100 + 100} ORDER BY Voie`

#### Scénario - Mise à jour
```
PUT /api2/admin/tv/scenario/{scenarioNumber}
```
**Profil requis** : ≤ 2

**Body** :
```json
{
  "scenes": [
    { "voie": 101, "url": "live/tv2.php?show=list_team&...", "intervalle": 15000 },
    { "voie": 102, "url": "frame_chart.php?...", "intervalle": 15000 },
    { "voie": 103, "url": "", "intervalle": 15000 },
    { "voie": 104, "url": "", "intervalle": 8000 },
    { "voie": 105, "url": "", "intervalle": 8000 },
    { "voie": 106, "url": "", "intervalle": 8000 },
    { "voie": 107, "url": "", "intervalle": 10000 },
    { "voie": 108, "url": "", "intervalle": 10000 },
    { "voie": 109, "url": "", "intervalle": 10000 }
  ]
}
```

**Logique backend** : Pour chaque scène, met à jour la DB et écrit le fichier cache JSON (même logique que `kptvscenario.php::Update()`)

---

## 7. Base de données

### 7.1 Table existante : `kp_tv`

```sql
CREATE TABLE `kp_tv` (
  `Voie` int(11) NOT NULL,          -- Numéro de channel (PK)
  `Url` varchar(1024) NOT NULL,     -- URL courante
  `intervalle` int(11) NOT NULL DEFAULT 10000  -- Polling interval en ms
) ENGINE=InnoDB;

ALTER TABLE `kp_tv` ADD PRIMARY KEY (`Voie`);
```

### 7.2 Nouvelle table : `kp_tv_label`

```sql
CREATE TABLE `kp_tv_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('channel','scenario') NOT NULL,     -- Type d'élément
  `number` int(11) NOT NULL,                       -- Numéro channel (1-50) ou scénario (1-9)
  `label` varchar(100) NOT NULL DEFAULT '',        -- Label personnalisé
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_number` (`type`, `number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 7.3 Fichiers cache JSON

**Emplacement** : `/live/cache/voie_{voie}.json`

**Format** :
```json
{
  "voie": 1,
  "url": "live/tv2.php?show=match&match=79290575&css=avranches2025",
  "intervalle": 10000,
  "timestamp": "20260304143022"
}
```

Ces fichiers sont écrits par le backend lors de chaque activation et lus par les pages d'affichage TV via polling.

---

## 8. Composants Vue

### 8.1 Arborescence

```
sources/app4/pages/tv/
└── index.vue                          # Page principale avec onglets

sources/app4/components/admin/tv/
├── GlobalBar.vue                      # Barre globale (événement, date, style, langue)
├── ChannelPanel.vue                   # Panneau de contrôle d'un channel
├── PresentationSelector.vue           # Dropdown groupé des présentations
├── ChannelSelector.vue                # Dropdown des channels avec labels
├── ConditionalParams.vue              # Paramètres conditionnels selon présentation
├── PlayerNumberGrid.vue               # Grille de boutons rapides joueurs (0-21)
├── ScenarioEditor.vue                 # Onglet d'édition des scénarios
├── LabelsModal.vue                    # Modal de gestion des labels
└── PresentationPreview.vue            # Vignette de prévisualisation
```

### 8.2 Logique d'affichage conditionnel des paramètres

Quand une présentation est sélectionnée, seuls les paramètres nécessaires sont affichés. La correspondance est définie dans la liste `PRESENTATIONS[].requiredParams`.

**Mapping paramètre → composant UI** :

| Paramètre | Composant/Champ |
|-----------|-----------------|
| `competition` | Dropdown des compétitions de l'événement |
| `match` | Dropdown des matchs (filtré par compétition + date) |
| `team` | Sélecteur A/B |
| `teamSelect` | Dropdown des équipes de l'événement |
| `number` | Dropdown 0-21 + grille de boutons rapides |
| `pitch` | Dropdown 1-8 |
| `pitchs` | Champ texte libre |
| `medal` | Dropdown Bronze/Silver/Gold |
| `zone` | Dropdown inter/club |
| `mode` | Dropdown full/only/event/static |
| `round` | Dropdown All/1-8 |
| `start` | Dropdown 1-10/11-20/21-30/31-40 |
| `animate` | Dropdown No/Yes |
| `speaker` | Dropdown 0/1/2 |
| `count` | Dropdown 1-4 |
| `lnStart` | Champ texte |
| `lnLen` | Champ texte |
| `competList` | Champ texte libre |
| `format` | Dropdown json/csv |
| `option` | Dropdown options joueurs |
| `navGroup` | Dropdown no/yes |

---

## 9. Construction des URLs

### 9.1 Présentations via `Go()` (AJAX vers `ajax_change_tv.php`)

Ces présentations passent par la fonction `Go()` qui envoie les paramètres à `ajax_change_tv.php`. Le backend construit l'URL complète `live/tv2.php?...`.

| Présentation | Paramètres `Go()` |
|--------------|-------------------|
| `match`, `match2` | `show={pres}&voie={ch}&match={m}` |
| `match_score` | `show=match_score&voie={ch}&match={m}&anime={a}` |
| `list_team`, `list_coachs` | `show={pres}&voie={ch}&match={m}&team={t}` |
| `team` | `show=team&voie={ch}&match={m}&team={t}` |
| `referee` | `show=referee&voie={ch}&match={m}` |
| `player`, `coach` | `show={pres}&voie={ch}&match={m}&team={t}&number={n}` |
| `player_medal` | `show=player_medal&voie={ch}&match={m}&team={t}&number={n}&medal={med}` |
| `team_medal` | `show=team_medal&voie={ch}&match={m}&team={t}&medal={med}` |
| `list_medals` | `show=list_medals&voie={ch}&saison={s}&competition={c}` |
| `final_ranking` | `show=final_ranking&voie={ch}&saison={s}&competition={c}&start={st}` |
| `podium` | `show=podium&voie={ch}&saison={s}&competition={c}&anime={a}` |
| `empty`, `reset` | `show={pres}&voie={ch}&css={css}` |

### 9.2 Présentations via `ChangeVoie()` (URL directe)

Ces présentations construisent l'URL côté frontend et appellent `ChangeVoie()` pour la stocker directement.

| Présentation | URL construite |
|--------------|---------------|
| `empty`, `voie`, `logo`, `player_pictures` | `live/tv2.php?show={pres}&css={css}` |
| `score`, `score_o`, `score_e`, `score_s` | `live/score{_suffix}.php?event={evt}&terrain={pitch}&css={css}&speaker={sp}` |
| `score_club`, `score_club_o/e/s` | `live/score_club{_suffix}.php?event={evt}&terrain={pitch}&css={css}&speaker={sp}` |
| `live` | `app_live/#/{evt}/{pitch}/score/{zone}/{mode}/{css}/en/` |
| `teams`, `teams_club` | `live/teams{_club}.php?event={evt}&terrain={pitch}&css={css}&anime={a}` |
| `next_game`, `next_game_club` | `live/next_game{_club}.php?event={evt}&terrain={pitch}&css={css}&anime={a}` |
| `liveteams` | `live/liveteams.php?event={evt}&terrain={pitch}&speaker={sp}` |
| `multi_score` | `live/multi_score.php?event={evt}&count={c}&speaker={sp}&refresh=10` |
| `frame_terrains` | `frame_terrains.php?event={evt}&lang=en&Saison={s}&Compet={c}&terrains={pitchs}&filtreJour={date}&Css={css}` |
| `frame_phases` | `frame_phases.php?event={evt}&lang=en&Saison={s}&Compet={c}&Round={r}&Css={css}` |
| `frame_categories` | `frame_categories.php?event={evt}&lang=en&Saison={s}&Compet={c}&terrains={pitchs}&filtreJour={date}&Css={css}&start={st}&len={ln}` |
| `frame_chart` | `frame_chart.php?event={evt}&lang=en&Saison={s}&Compet={c}&Round={r}&Css={css}` |
| `frame_details` | `frame_details.php?event={evt}&lang=en&Saison={s}&Compet={c}&Round={r}&Css={css}` |
| `frame_team` | `frame_team.php?event={evt}&lang=en&Saison={s}&Compet={c}&Team={team}&Round={r}&Css={css}` |
| `frame_stats` | `frame_stats.php?event={evt}&lang=en&Saison={s}&Compet={c}&Css={css}` |
| `frame_classement` | `frame_classement.php?event={evt}&lang=en&Saison={s}&Compet={c}&Css={css}` |
| `frame_qr` | `frame_qr.php?event={evt}&lang=en&Saison={s}&Compet={c}&Css={css}` |
| `frame_matchs` | `frame_matchs.php?event={evt}&lang=en&Saison={s}&Compet={c}&Team={team}&Round={r}&Css={css}&navGroup={ng}` |
| `api_players` | `api_players.php?saison={s}&competitions={compets}&format={fmt}` |
| `api_stats` | `api_stats.php?saison={s}&competitions={compets}&all={opt}&format={fmt}` |
| `force_cache_match` | `live/force_cache_match.php?match={m}` (AJAX simple, pas de ChangeVoie) |

---

## 10. Traductions i18n

### 10.1 Français (fr.json)

```json
{
  "tv": {
    "title": "Contrôle TV",
    "tabs": {
      "channels": "Channels",
      "scenarios": "Scénarios"
    },
    "global": {
      "event": "Événement",
      "date": "Date",
      "style": "Style",
      "language": "Langue",
      "all_dates": "Toutes",
      "select": "Sélectionnez"
    },
    "panel": {
      "channel": "Channel",
      "presentation": "Présentation",
      "competition": "Compétition",
      "match": "Match",
      "team": "Équipe",
      "player": "Joueur",
      "pitch": "Terrain",
      "pitchs": "Terrains",
      "medal": "Médaille",
      "zone": "Zone",
      "mode": "Mode",
      "round": "Tour",
      "start": "Début",
      "animate": "Animer",
      "speaker": "Speaker",
      "count": "Nombre",
      "first_game": "Premier match",
      "game_count": "Nombre de matchs",
      "competitions": "Compétitions",
      "format": "Format",
      "option": "Option",
      "navbar": "Barre de navigation"
    },
    "actions": {
      "activate": "Activer",
      "blank": "Vide",
      "url": "URL",
      "control": "Contrôle",
      "report": "Rapport",
      "add_panel": "Ajouter un panneau",
      "remove_panel": "Retirer"
    },
    "presentations": {
      "groups": {
        "general": "Général",
        "before_game": "Avant match",
        "running_nations": "Match en cours (nations)",
        "running_clubs": "Match en cours (clubs)",
        "running_ws": "Match en cours (WS)",
        "match_presentation": "Présentation match",
        "after_game": "Après match",
        "screen": "Écran d'affichage",
        "web": "Site / Mobile",
        "api": "API",
        "cache": "Cache",
        "debug": "Debug"
      }
    },
    "scenario": {
      "title": "Scénario",
      "channel": "Channel",
      "url": "URL",
      "delay": "Délai (ms)",
      "update": "Mettre à jour",
      "test": "Tester",
      "refresh": "Rafraîchir",
      "updated": "Scénario {scenario} mis à jour"
    },
    "labels": {
      "title": "Labels des channels et scénarios",
      "channels": "Channels",
      "scenarios": "Scénarios",
      "save": "Sauvegarder",
      "cancel": "Annuler",
      "saved": "Labels sauvegardés",
      "manage": "Gérer les labels"
    },
    "messages": {
      "activated": "Channel {channel} activé",
      "blanked": "Channel {channel} vidé",
      "select_event": "Sélectionnez un événement",
      "select_channel": "Sélectionnez un channel",
      "select_presentation": "Sélectionnez une présentation"
    },
    "links": {
      "event_cache": "Event cache generator",
      "split_url": "Split URL",
      "scenario_live": "Scenario Live"
    },
    "speaker_options": {
      "no": "Non",
      "yes": "Oui",
      "maybe": "Peut-être"
    },
    "option_players": {
      "with_stats": "Joueurs avec stats",
      "all": "Tous les joueurs",
      "without_stats": "Tous sans stats"
    }
  }
}
```

### 10.2 Anglais (en.json)

```json
{
  "tv": {
    "title": "TV Control",
    "tabs": {
      "channels": "Channels",
      "scenarios": "Scenarios"
    },
    "global": {
      "event": "Event",
      "date": "Date",
      "style": "Style",
      "language": "Language",
      "all_dates": "All",
      "select": "Select"
    },
    "panel": {
      "channel": "Channel",
      "presentation": "Presentation",
      "competition": "Competition",
      "match": "Game",
      "team": "Team",
      "player": "Player",
      "pitch": "Pitch",
      "pitchs": "Pitches",
      "medal": "Medal",
      "zone": "Zone",
      "mode": "Mode",
      "round": "Round",
      "start": "Start",
      "animate": "Animate",
      "speaker": "Speaker",
      "count": "Count",
      "first_game": "First game",
      "game_count": "Game count",
      "competitions": "Competitions",
      "format": "Format",
      "option": "Option",
      "navbar": "Navbar"
    },
    "actions": {
      "activate": "Activate",
      "blank": "Blank",
      "url": "URL",
      "control": "Control",
      "report": "Report",
      "add_panel": "Add panel",
      "remove_panel": "Remove"
    },
    "presentations": {
      "groups": {
        "general": "General",
        "before_game": "Before game inlays",
        "running_nations": "Running game (nations)",
        "running_clubs": "Running game (clubs)",
        "running_ws": "Live game (WS)",
        "match_presentation": "Game presentation",
        "after_game": "After game inlays",
        "screen": "Screen display",
        "web": "Website / Smartphone",
        "api": "API",
        "cache": "Cache build",
        "debug": "Debug"
      }
    },
    "scenario": {
      "title": "Scenario",
      "channel": "Channel",
      "url": "URL",
      "delay": "Delay (ms)",
      "update": "Update",
      "test": "Test",
      "refresh": "Refresh",
      "updated": "Scenario {scenario} updated"
    },
    "labels": {
      "title": "Channel and scenario labels",
      "channels": "Channels",
      "scenarios": "Scenarios",
      "save": "Save",
      "cancel": "Cancel",
      "saved": "Labels saved",
      "manage": "Manage labels"
    },
    "messages": {
      "activated": "Channel {channel} activated",
      "blanked": "Channel {channel} blanked",
      "select_event": "Select an event",
      "select_channel": "Select a channel",
      "select_presentation": "Select a presentation"
    },
    "links": {
      "event_cache": "Event cache generator",
      "split_url": "Split URL",
      "scenario_live": "Scenario Live"
    },
    "speaker_options": {
      "no": "No",
      "yes": "Yes",
      "maybe": "Maybe"
    },
    "option_players": {
      "with_stats": "Players with stats",
      "all": "All players",
      "without_stats": "All without stats"
    }
  }
}
```

---

## 11. Sécurité

| Contrôle | Détail |
|----------|--------|
| Authentification | JWT requis (middleware `auth`) |
| Autorisation | Profil ≤ 2 pour toutes les actions |
| Validation channel | Vérifier que `voie` est dans les plages autorisées (1-50, 101-909) |
| Validation URL | Pas de validation stricte (les URLs sont des chemins relatifs internes) |
| Écriture fichiers | Les fichiers cache JSON sont écrits dans un répertoire dédié (`/live/cache/`) |
| XSS | Les URLs sont affichées en lecture seule dans des champs `<input readonly>` |

---

## 12. Notes de migration

### 12.1 Différences avec le legacy

| Aspect | Legacy (PHP/Smarty) | App4 (Nuxt) |
|--------|---------------------|-------------|
| Nombre de panneaux | Fixe (4 articles) | Dynamique (ajout/retrait) |
| Scénarios | Page séparée (`kptvscenario.php`) | Onglet intégré dans la même page |
| Labels channels | Aucun | Nouveau : stockage en DB avec modal de gestion |
| Persistance filtres | Session PHP | localStorage |
| Communication serveur | jQuery AJAX + Smarty form submit | `useApi()` composable |
| Gestion état | Variables de session PHP × 4 | État réactif Vue + localStorage |
| Vignettes | Images statiques dans `img/presentations/` | Même système, images existantes |
| Liens rapides | Liens vers pages PHP séparées | Conservés (liens legacy) |

### 12.2 Éléments conservés sans modification

- Le système de polling JSON (fichiers cache `/live/cache/voie_*.json`)
- Les pages d'affichage TV (`live/tv2.php`, `live/score*.php`, `frame_*.php`, etc.)
- Les scripts de rendu côté affichage (`live/js/voie_ax.js`, `live/js/scenario.js`)
- Les fichiers CSS de thèmes (`live/css/`)
- Les images de prévisualisation (`img/presentations/`)
- La table `kp_tv` (structure inchangée)

### 12.3 Points d'attention

1. **Encodage des URLs** : Le legacy utilise un encodage pipes (`|QU|`, `|AM|`, `|HA|`) pour transmettre les URLs via AJAX. L'API2 devra gérer ce même encodage si les endpoints legacy sont conservés, ou utiliser un encodage JSON standard si les nouveaux endpoints sont utilisés.

2. **Chemins relatifs** : Les URLs stockées dans `kp_tv` sont des chemins relatifs au document root (`live/tv2.php?...`, `frame_chart.php?...`). Cela doit être préservé car les pages d'affichage s'y attendent.

3. **Force cache match** : Cette action est un appel AJAX simple (pas de ChangeVoie), elle régénère le cache d'un match spécifique.

4. **Saison** : La saison est déduite des matchs de l'événement sélectionné (champ `Code_saison` de la journée), pas du workContextStore.

---

**Document créé le** : 2026-03-04
**Dernière mise à jour** : 2026-03-04
