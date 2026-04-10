# Spécification - Page Documents

## 1. Vue d'ensemble

La page Documents permet de générer et télécharger des documents PDF relatifs aux compétitions et événements.

**Route** : `/documents`

**Accès** : Authentifié (tous profils, avec restrictions selon filtres utilisateur)

---

## 2. Fonctionnalités

### 2.1 Filtrage

La page utilise le **contexte de travail** (`workContextStore`) pour la saison et le périmètre de compétitions. La barre de rappel du contexte est affichée au-dessus du titre.

#### Mode Compétition
- Saison : issue du contexte de travail (lecture seule)
- Sélecteur Compétition : dropdown simple filtré par les compétitions du contexte, auto-sélection de la première compétition
- Pour : Équipes, Matchs, Classements, Statistiques

#### Mode Événement
- Saison : issue du contexte de travail (lecture seule)
- Sélecteur Événement : dropdown simple
- Pour : Documents événementiels (matchs par événement, QR codes événement)

### 2.2 Catégories de Documents

Les documents sont organisés en cartes par catégorie :

| Catégorie | Icône | Description | Profil min |
|-----------|-------|-------------|------------|
| Équipes | 👥 | Listes et feuilles de présence | 10 |
| Matchs | ⚽ | Planning et feuilles de marque | 10 |
| Classements | 🏆 | Classements selon type compétition | 10 |
| Statistiques | 📊 | Stats diverses (lien vers page Stats) | 10 |
| Événement | 📅 | Documents liés à un événement | ≤ 2 (Bureau CNAKP) |
| Contrôle | 🔍 | Documents de vérification | ≤ 6 (Organisateur) |

### 2.3 Comportement des Liens

- **Documents PDF Legacy** : Ouvrent dans un nouvel onglet vers `/admin/FeuilleXxx.php` ou `/PdfXxx.php`
- **Page Statistiques** : Naviguent vers `/stats/:type/:saison/:competition` dans app4
- **Documents non disponibles** : Affichés grisés avec mention "Bientôt disponible"

### 2.4 Synthèse de compétition

Affichée entre les filtres et la grille de documents, quand une compétition est sélectionnée. Carte blanche avec 3 zones :

#### Zone A — Images
- Bandeau, logo et sponsor de la compétition (si actifs et disponibles)
- Images servies depuis le backend legacy (`legacyBase + bandeauLink/logoLink/sponsorLink`)
- Masquées automatiquement en cas de 404 (handler `@error`)

#### Zone B — Données clés
- Titre de la compétition + sous-titre (lieu)
- Badges : niveau (INT/NAT/REG), type (CP/CHPT/MULTI)
- Compteurs : nombre d'équipes, de phases, de matchs
- Qualifiés / éliminés (CP uniquement, si > 0)

#### Zone C — Structure des phases
Visualisation simplifiée de l'organisation de la compétition, sans équipes ni scores :

- **CP** : Colonnes par étape (comme le Schéma de compétition), chaque phase affichée avec son nom, badge type (C/E) et nombre de matchs
- **CHPT** : Liste horizontale des journées avec nombre de matchs
- **MULTI** : Message « Compétition multi-compétitions »

**Sources de données** : Deux appels API en parallèle :
1. `GET /admin/competitions/{code}?season=...` — données, compteurs, images
2. `GET /admin/schema?season=...&competition=...` — structure des phases

**Composant** : `DocumentsCompetitionSummary.vue`

### 2.5 Navigation depuis la page Compétitions

Le code de chaque compétition dans la liste (page `/competitions`) est un lien cliquable qui :
1. Sélectionne la compétition dans le `workContextStore` via `setPageCompetition()`
2. Réinitialise le filtre événement/groupe si la compétition n'en fait pas partie
3. Navigue vers `/documents`

---

## 3. Structure de la Page

```
┌─────────────────────────────────────────────────────────────┐
│  Contexte : Saison 2026 | Groupe N1H (2 compétitions)       │
│                                                [Modifier]   │
├─────────────────────────────────────────────────────────────┤
│  Header : Documents                                          │
├─────────────────────────────────────────────────────────────┤
│  Filtres :                                                   │
│  ┌──────────────────────────────────────┐                   │
│  │ Compétition: N1H - Nationale 1 Masc. │  (du contexte)   │
│  └──────────────────────────────────────┘                   │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─ Synthèse compétition ──────────────────────────────────┐│
│  │  [Bandeau]  [Logo]  [Sponsor]                           ││
│  │  ─────────────────────────────────────────────────────  ││
│  │  Nationale 1 Masculine        INT  CHPT  12 éq  4 ph   ││
│  │  ─────────────────────────────────────────────────────  ││
│  │  ┌─Étape 1─┐ ┌─Étape 2─┐ ┌─Étape 3─┐ ┌─Étape 4─┐     ││
│  │  │C Group A│ │E Repech. │ │E QF      │ │E SF      │     ││
│  │  │C Group B│ │E Cl. 9-12│ │E Cl. 5-8 │ │E Final   │     ││
│  │  │C Group C│ │          │ │          │ │E 3e place│     ││
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘     ││
│  └─────────────────────────────────────────────────────────┘│
│                                                              │
│  ┌─────────────────────┐  ┌─────────────────────┐           │
│  │ 👥 ÉQUIPES          │  │ ⚽ MATCHS            │           │
│  │                     │  │                     │           │
│  │ • Équipes engagées  │  │ • Liste des matchs  │           │
│  │ • Présence FR       │  │ • Feuilles de marque│           │
│  │ • Présence EN       │  │ • Export OpenOffice │           │
│  │ • Présence Photo    │  │                     │           │
│  │ • Présence Visa     │  │                     │           │
│  └─────────────────────┘  └─────────────────────┘           │
│                                                              │
│  ┌─────────────────────┐  ┌─────────────────────┐           │
│  │ 🏆 CLASSEMENTS      │  │ 📊 STATISTIQUES     │           │
│  │                     │  │                     │           │
│  │ • Classement général│  │ • Buteurs →         │           │
│  │ • Détail par équipe │  │ • Attaque →         │           │
│  │ • Détail par phase  │  │ • Défense →         │           │
│  │   (selon type)      │  │ • Cartons →         │           │
│  └─────────────────────┘  │ • Fairplay →        │           │
│                           │ • Arbitrage →       │           │
│  ┌─────────────────────┐  └─────────────────────┘           │
│  │ 📅 ÉVÉNEMENT        │                                    │
│  │   (profil ≤ 2)      │  ┌─────────────────────┐           │
│  │                     │  │ 🔍 CONTRÔLE         │           │
│  │ • Matchs événement  │  │   (profil ≤ 6)      │           │
│  │ • QR Codes          │  │                     │           │
│  │ • QR Code App       │  │ • Présence catégorie│           │
│  └─────────────────────┘  │ • Présence U21      │           │
│                           │ • Irrégularités     │           │
│                           └─────────────────────┘           │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. Liste des Documents

### 4.1 Équipes (Mode Compétition)

| Document | Lien Legacy | Langue | Profil Min |
|----------|-------------|--------|------------|
| Équipes engagées | `FeuilleGroups.php` | FR | 10 |
| Feuilles de présence | `FeuillePresence.php` | FR | 10 |
| Feuilles de présence | `FeuillePresenceEN.php` | EN | 10 |
| Présence avec visa | `FeuillePresenceVisa.php` | FR | 10 |
| Présence avec photo | `FeuillePresencePhoto.php` | EN | 10 |

### 4.2 Matchs (Mode Compétition)

| Document | Lien Legacy Admin | Lien Legacy Public | Langue | Profil Min |
|----------|-------------------|-------------------|--------|------------|
| Liste des matchs | `FeuilleListeMatchs.php` | `PdfListeMatchs.php` | FR | 10 |
| Liste des matchs | `FeuilleListeMatchsEN.php` | `PdfListeMatchsEN.php` | EN | 10 |
| Export OpenOffice | `tableau_tbs.php` | - | - | 10 |
| Feuilles de marque | `FeuilleMatchMulti.php` | `PdfMatchMulti.php` | Auto | 10 |

### 4.3 Classements (Mode Compétition)

Documents affichés selon `Code_typeclt` de la compétition :

#### Type CHPT (Championnat)

| Document | Lien Legacy Admin | Lien Legacy Public | Profil Min |
|----------|-------------------|-------------------|------------|
| Classement général | `FeuilleCltChpt.php` | `PdfCltChpt.php` | 10 |
| Détail par équipe | `FeuilleCltChptDetail.php` | `PdfCltChptDetail.php` | 10 |
| Détail par journée | `FeuilleCltNiveauJournee.php` | `PdfCltNiveauJournee.php` | 10 |

#### Type CP (Coupe/Phases)

| Document | Lien Legacy Admin | Lien Legacy Public | Profil Min |
|----------|-------------------|-------------------|------------|
| Classement général | `FeuilleCltNiveau.php` | `PdfCltNiveau.php` | 10 |
| Détail par phase | `FeuilleCltNiveauPhase.php` | `PdfCltNiveauPhase.php` | 10 |
| Détail par équipe | `FeuilleCltNiveauDetail.php` | `PdfCltNiveauDetail.php` | 10 |

#### Type MULTI

| Document | Lien Legacy Admin | Lien Legacy Public | Profil Min |
|----------|-------------------|-------------------|------------|
| Classement multi | `FeuilleCltMulti.php` | `PdfCltMulti.php` | 10 |

### 4.4 Statistiques (Mode Compétition)

Liens vers la page Stats de app4 avec route dynamique :

| Statistique | Route App4 |
|-------------|------------|
| Buteurs | `/stats/Buteurs/:saison/:competition` |
| Attaque | `/stats/Attaque/:saison/:competition` |
| Défense | `/stats/Defense/:saison/:competition` |
| Cartons (joueurs) | `/stats/Cartons/:saison/:competition` |
| Cartons (équipes) | `/stats/CartonsEquipe/:saison/:competition` |
| Fairplay (joueurs) | `/stats/Fairplay/:saison/:competition` |
| Fairplay (équipes) | `/stats/FairplayEquipe/:saison/:competition` |
| Arbitrage (arbitres) | `/stats/Arbitrage/:saison/:competition` |
| Arbitrage (équipes) | `/stats/ArbitrageEquipe/:saison/:competition` |

### 4.5 Événement (Mode Événement)

**Profil requis** : ≤ 2 (Bureau CNAKP)

| Document | Lien Legacy Admin | Lien Legacy Public | Langue |
|----------|-------------------|-------------------|--------|
| Matchs événement | `FeuilleListeMatchs.php?idEvenement=X` | `PdfListeMatchs.php?idEvenement=X` | FR |
| Matchs événement | `FeuilleListeMatchsEN.php?idEvenement=X` | `PdfListeMatchsEN.php?idEvenement=X` | EN |
| QR Codes | `PdfQrCodes.php?Evt=X` | - | - |
| QR Code App | `PdfQrCodeApp.php?Evt=X` | - | - |

### 4.6 Contrôle (Mode Compétition)

| Document | Lien Legacy | Langue | Profil min | Description |
|----------|-------------|--------|------------|-------------|
| Présence par catégorie | `FeuillePresenceCat.php` | FR | ≤ 6 | Vérification catégories d'âge |
| Présence U21 | `FeuillePresenceU21.php` | FR | ≤ 6 | Filtre joueurs U21 |
| Compétitions jouées (club) | `/stats/CJouees/:saison/:competition` | - | ≤ 6 | Via page Stats |
| Compétitions jouées (équipe) | `/stats/CJouees2/:saison/:competition` | - | ≤ 6 | Via page Stats |
| Irrégularités | `/stats/CJouees3/:saison/:competition` | - | ≤ 6 | Via page Stats (profil ≤ 6) |
| Licenciés nationaux | `/stats/LicenciesNationaux/:saison/:competition` | - | ≤ 6 | Via page Stats (profil ≤ 6) |
| Cohérence matchs | `/stats/CoherenceMatchs/:saison/:competition` | - | ≤ 6 | Via page Stats (profil ≤ 6) |
| Cartons cumulés | `FeuilleCards.php` | FR/EN | ≤ 6 | Cumul saison |

---

## 5. Logique de Langue des Documents

### 5.1 Algorithme

```typescript
function getDocumentUrl(doc: Document, competition: Competition, userLang: string): string {
  // Déterminer la langue du document
  let lang: 'fr' | 'en' = 'fr'

  if (doc.hasEnVersion) {
    // Priorité 1: En_actif de la compétition
    if (competition?.enActif === 'O') {
      lang = 'en'
    }
    // Priorité 2: Langue de l'utilisateur
    else if (userLang === 'en') {
      lang = 'en'
    }
  }

  // Retourner l'URL appropriée
  return lang === 'en' ? doc.urlEn : doc.urlFr
}
```

### 5.2 Affichage

- Si document disponible en 2 langues : afficher les deux liens (FR | EN)
- Si document en une seule langue : afficher uniquement ce lien
- La langue "recommandée" (selon En_actif ou user) peut être mise en évidence

---

## 6. API Endpoints Nécessaires

### 6.1 Endpoint Filtres (existant à étendre)

```
GET /api2/admin/filters/competitions?season=2025
```

Doit retourner `enActif` et `codeTypeclt` pour chaque compétition :

```json
{
  "competitions": [
    {
      "code": "N1M",
      "libelle": "Nationale 1 Masculine",
      "section": 1,
      "sectionLabel": "Nationaux",
      "enActif": "N",
      "codeTypeclt": "CHPT"
    }
  ]
}
```

### 6.2 Endpoint Détail Compétition (existant, pour la synthèse)

```
GET /api2/admin/competitions/{code}?season=2025
```

Retourne les données complètes de la compétition incluant compteurs, images et flags d'activation.

### 6.3 Endpoint Schéma (existant, pour la structure des phases)

```
GET /api2/admin/schema?season=2025&competition=N1M
```

Retourne la structure complète du schéma de compétition. Seules les métadonnées des phases sont utilisées par la synthèse (nom, type, étape, nombre de matchs).

### 6.4 Endpoint Liste Matchs (pour feuilles de marque)

```
GET /api2/admin/documents/match-ids?season=2025&competition=N1M
```

Retourne la liste des IDs de matchs pour construire l'URL `FeuilleMatchMulti.php?listMatch=1,2,3...`

```json
{
  "matchIds": [1234, 1235, 1236, ...]
}
```

---

## 7. Composants Vue

### 7.1 Structure des Fichiers

```
pages/documents/
└── index.vue

components/documents/
├── DocumentsCompetitionSummary.vue  # Synthèse compétition (images, données, phases)
├── DocumentCard.vue                 # Carte de catégorie
├── DocumentLink.vue                 # Lien vers document
├── FilterBar.vue                    # Barre de filtres (saison/compet ou saison/event)
└── CategoryGrid.vue                 # Grille de cartes
```

### 7.2 Props DocumentLink

```typescript
interface DocumentLinkProps {
  label: string              // "Feuilles de présence"
  urlFr?: string             // URL version française
  urlEn?: string             // URL version anglaise
  routeFr?: string           // Route app4 française
  routeEn?: string           // Route app4 anglaise
  icon?: string              // Icône optionnelle
  disabled?: boolean         // Grisé si non disponible
  minProfile?: number        // Profil minimum requis
  openInNewTab?: boolean     // true pour PDFs legacy
}
```

---

## 8. Route Stats Dynamique

### 8.1 Nouvelle Route

```
/stats/:type/:saison/:competition
```

### 8.2 Comportement

- Charge directement la page Stats avec les filtres pré-remplis
- Le type de stat est pré-sélectionné
- L'utilisateur peut modifier les filtres
- Export PDF disponible via liens vers `FeuilleStats.php` / `FeuilleStatsEN.php`

### 8.3 Fichier Route

```
pages/stats/
├── index.vue                        # Page principale Stats
└── [type]/[saison]/[competition].vue  # Route dynamique
```

---

## 9. Maquette UI

### 9.1 Desktop (≥1024px)

- Grille 3 colonnes de cartes
- Filtres en ligne horizontale

### 9.2 Tablet (768px-1023px)

- Grille 2 colonnes de cartes
- Filtres en ligne horizontale

### 9.3 Mobile (<768px)

- Grille 1 colonne de cartes
- Filtres empilés verticalement

---

## 10. Checklist Implémentation

### Backend (API2)

- [ ] Étendre `User` entity avec `filtreSaison`
- [ ] Étendre `UserProvider` pour charger `Filtre_saison`
- [ ] Étendre token JWT avec filtres utilisateur
- [ ] Créer/modifier endpoint `/admin/filters/competitions` avec `enActif` et `codeTypeclt`
- [ ] Créer endpoint `/admin/documents/match-ids`
- [ ] Appliquer filtres utilisateur sur tous les endpoints

### Frontend (App4)

- [ ] Créer `pages/documents/index.vue`
- [ ] Créer composants `DocumentCard`, `DocumentLink`, `FilterBar`
- [ ] Créer composable `useDocumentLanguage`
- [ ] Créer route dynamique `/stats/[type]/[saison]/[competition].vue`
- [ ] Modifier `pages/stats/index.vue` pour supporter pré-remplissage
- [ ] Ajouter entrée menu "Documents"
- [ ] Gérer restrictions par profil
- [ ] Gérer filtres utilisateur dans les sélecteurs
- [ ] Tests manuels des liens PDF legacy
