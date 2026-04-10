# Migration des Documents PDF vers App4

## Inventaire Complet des Documents

### 1. ÉQUIPES (Team Documents)

| Document | Admin (Feuille) | Public (Pdf) | Description | Priorité |
|----------|-----------------|--------------|-------------|----------|
| Équipes engagées | `FeuilleGroups.php` | - | Liste des équipes par groupe | P2 |
| Présence FR | `FeuillePresence.php` | - | Feuilles de présence français | P1 |
| Présence EN | `FeuillePresenceEN.php` | - | Feuilles de présence anglais | P2 |
| Présence Visa | `FeuillePresenceVisa.php` | - | Présence avec espace visa | P3 |
| Présence Photo | `FeuillePresencePhoto.php` | - | Présence avec photos | P2 |
| Présence Photo 2 | `FeuillePresencePhoto2.php` | - | Variante présence photo | P3 |
| Présence Photo Ref | `FeuillePresencePhotoRef.php` | - | Référence photo | P3 |
| Présence Catégorie | `FeuillePresenceCat.php` | - | Présence par catégorie d'âge | P2 |
| Présence U21 | `FeuillePresenceU21.php` | - | Présence filtre U21 | P3 |

### 2. MATCHS (Match Documents)

| Document | Admin (Feuille) | Public (Pdf) | Description | Priorité |
|----------|-----------------|--------------|-------------|----------|
| Liste matchs FR | `FeuilleListeMatchs.php` | `PdfListeMatchs.php` | Planning des matchs | P1 |
| Liste matchs EN | `FeuilleListeMatchsEN.php` | `PdfListeMatchsEN.php` | Planning anglais | P2 |
| Liste 4 terrains | - | `PdfListeMatchs4Terrains.php` | Format 4 terrains | P3 |
| Liste 4 terrains EN | - | `PdfListeMatchs4TerrainsEn*.php` | Variantes EN (4 versions) | P3 |
| Feuilles de marque | `FeuilleMatchMulti.php` | `PdfMatchMulti.php` | Feuilles de match multiples | P1 |
| Feuille marque v2 | `FeuilleMarque2.php` | - | Version 2 feuille match | P3 |
| Feuille marque v3 | `FeuilleMarque3.php` | - | Version 3 feuille match | P3 |
| Feuille marque stats | `FeuilleMarque2stats.php` | - | Avec statistiques | P3 |

### 3. CLASSEMENTS (Rankings)

| Document | Admin (Feuille) | Public (Pdf) | Type Compét | Priorité |
|----------|-----------------|--------------|-------------|----------|
| **Type CHPT (Championnat)** |
| Classement général | `FeuilleCltChpt.php` | `PdfCltChpt.php` | CHPT | P1 |
| Détail par équipe | `FeuilleCltChptDetail.php` | `PdfCltChptDetail.php` | CHPT | P2 |
| Détail par journée | `FeuilleCltNiveauJournee.php` | `PdfCltNiveauJournee.php` | CHPT | P2 |
| **Type CP (Coupe/Phases)** |
| Classement général | `FeuilleCltNiveau.php` | `PdfCltNiveau.php` | CP | P1 |
| Détail par phase | `FeuilleCltNiveauPhase.php` | `PdfCltNiveauPhase.php` | CP | P2 |
| Détail par équipe | `FeuilleCltNiveauDetail.php` | `PdfCltNiveauDetail.php` | CP | P2 |
| Détail par niveau | `FeuilleCltNiveauNiveau.php` | `PdfCltNiveauNiveau.php` | CP | P3 |
| **Type MULTI** |
| Classement multi | `FeuilleCltMulti.php` | `PdfCltMulti.php` | MULTI | P2 |

### 4. STATISTIQUES (Stats PDF)

| Document | Admin (Feuille) | Description | Priorité |
|----------|-----------------|-------------|----------|
| Stats FR | `FeuilleStats.php` | 22 types de stats en français | P1 |
| Stats EN | `FeuilleStatsEN.php` | 22 types de stats en anglais | P2 |
| Cartons cumulés | `FeuilleCards.php` | Cumul cartons sur la saison | P2 |

### 5. LIENS / QR CODES

| Document | Public (Pdf) | Description | Priorité |
|----------|--------------|-------------|----------|
| QR Codes compétition | `PdfQrCodes.php` | QR codes accès direct | P2 |
| QR Code application | `PdfQrCodeApp.php` | QR code vers l'app | P3 |

### 6. CONTRÔLE / ADMIN

| Document | Admin (Feuille) | Description | Priorité |
|----------|-----------------|-------------|----------|
| Contrôle FR | `FeuilleControle.php` | Document de contrôle | P3 |
| Contrôle EN | `FeuilleControleEN.php` | Control document | P3 |
| Instances | `FeuilleInstances.php` | Liste des instances | P3 |

---

## Plan de Migration

### Phase 1 - Infrastructure (Semaine 1)
- [ ] Créer `AdminDocumentController.php` dans API2
- [ ] Installer une librairie PDF moderne (TCPDF ou Dompdf via Symfony)
- [ ] Créer le service `PdfGeneratorService.php`
- [ ] Créer `pages/documents/index.vue` dans app4

### Phase 2 - Premiers Documents (Semaine 2)
Documents prioritaires à migrer en premier :

1. **Liste des matchs FR** (`FeuilleListeMatchs.php`)
   - Le plus utilisé
   - Structure simple : tableau de matchs
   - Base pour la version EN

2. **Feuille de présence FR** (`FeuillePresence.php`)
   - Très utilisé avant les compétitions
   - Structure répétitive (une page par équipe)

3. **Classement général CHPT** (`FeuilleCltChpt.php`)
   - Document clé pour les championnats
   - Tableau simple avec calculs

4. **Classement général CP** (`FeuilleCltNiveau.php`)
   - Document clé pour les coupes
   - Similaire au CHPT

5. **Stats Buteurs** (extrait de `FeuilleStats.php`)
   - Commencer par un type de stat simple
   - Servira de modèle pour les autres

### Phase 3 - Documents Secondaires (Semaine 3-4)
- Versions EN des documents Phase 2
- Feuilles de marque
- Détails classements

### Phase 4 - Reste (Semaine 5+)
- Documents spécialisés
- QR codes
- Documents de contrôle

---

## Architecture Technique

### API2 - Endpoints

```
GET  /admin/documents/filters          # Saisons, compétitions, événements
GET  /admin/documents/competition/{code}/summary  # Résumé compétition
POST /admin/documents/generate         # Génère un PDF
     Body: { type: "matches-list", format: "pdf", lang: "fr", params: {...} }
```

### Types de Documents (enum)

```typescript
type DocumentType =
  // Équipes
  | 'teams-list'
  | 'presence-sheet'
  | 'presence-sheet-photo'
  | 'presence-sheet-visa'
  | 'presence-by-category'
  // Matchs
  | 'matches-list'
  | 'match-sheets'
  // Classements
  | 'ranking-chpt'
  | 'ranking-chpt-detail'
  | 'ranking-cp'
  | 'ranking-cp-phase'
  | 'ranking-multi'
  // Stats
  | 'stats-scorers'
  | 'stats-attack'
  | 'stats-defense'
  | 'stats-cards'
  | 'stats-fairplay'
  // etc.
```

### Structure Page Nuxt

```
pages/documents/index.vue
├── Filtres (saison, compétition, événement)
├── Résumé compétition (panneau droit)
└── Liste documents par catégorie
    ├── Équipes
    ├── Matchs
    ├── Classements
    ├── Statistiques
    └── Liens
```

---

## Statut Migration

| Document | Statut | Date | Notes |
|----------|--------|------|-------|
| Liste matchs FR | 🔴 À faire | - | Priorité 1 |
| Présence FR | 🔴 À faire | - | Priorité 1 |
| Classement CHPT | 🔴 À faire | - | Priorité 1 |
| Classement CP | 🔴 À faire | - | Priorité 1 |
| Stats Buteurs | 🔴 À faire | - | Priorité 1 |

Légende : 🔴 À faire | 🟡 En cours | 🟢 Terminé | ⚪ Legacy uniquement
