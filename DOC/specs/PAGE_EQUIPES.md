# Spécification - Page Équipes

## 1. Vue d'ensemble

La page Équipes permet de gérer les équipes inscrites à une compétition : ajout (depuis l'historique ou création manuelle), édition des propriétés (logo, couleurs), gestion des poules et du tirage au sort, suppression, duplication depuis une autre compétition, et initialisation des titulaires.

**Route** : `/teams`

**Accès** :
- Profil ≤ 10 : Lecture seule
- Profil ≤ 6 : Édition inline (poule/tirage)
- Profil ≤ 4 : Tirage au sort, initialisation titulaires, verrouillage
- Profil ≤ 3 : Ajout/Suppression/Duplication (sauf si compétition verrouillée)
- Profil ≤ 2 : Édition propriétés (logo/couleurs), mise à jour logos, contrôle

**Verrouillage** : Lorsqu'une compétition est verrouillée (`verrou = true`), l'ajout et la suppression d'équipes sont interdits côté frontend (boutons masqués) ET côté backend (retour HTTP 403).

**Page PHP Legacy** : `GestionEquipe.php` + `GestionEquipe.tpl` + `GestionEquipe.js`

---

## 2. Fonctionnalités

### 2.1 Sélection de compétition

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Sélecteur de compétition (filtré par contexte de travail, persisté) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Badges info compétition (type, niveau, statut) | ≤ 10 | Utile | ✅ Conserver |
| 3 | Indicateur verrouillage compétition | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | Toggle verrouillage compétition | ≤ 4 | Essentielle | ✅ Conserver |
| 5 | Auto-sélection première compétition disponible | ≤ 10 | Essentielle | ✅ Conserver |
| 6 | Persistance de la sélection entre pages (Documents, Classements) | ≤ 10 | Essentielle | ✅ Conserver |

### 2.2 Liste des équipes

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Tableau des équipes groupé par poule | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Afficher logo + couleurs dans une colonne fusionnée | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Aperçu couleurs : carré unique (fond=color1, bordure épaisse=color2, texte "1"=colortext) | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | Afficher nombre de matchs (basé sur `kp_match.Validation = 'O'`) | ≤ 10 | Utile | ✅ Conserver |
| 5 | Afficher club de l'équipe | ≤ 10 | Essentielle | ✅ Conserver |
| 6 | Édition inline Poule (A-Z) | ≤ 6 | Essentielle | ✅ Conserver |
| 7 | Édition inline Tirage (0-99) | ≤ 6 | Essentielle | ✅ Conserver |
| 8 | Sélection multiple (checkboxes) — masqué si compétition verrouillée | ≤ 3 | Essentielle | ✅ Conserver |
| 9 | Compteur total d'équipes | ≤ 10 | Utile | ✅ Conserver |

### 2.3 Ajout d'équipes

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Ajout depuis historique (onglet principal, affiché en premier) | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Création manuelle (onglet secondaire) | ≤ 3 | Essentielle | ✅ Conserver |
| 3 | Sélection multiple depuis historique | ≤ 3 | Utile | ✅ Conserver |
| 4 | Filtres cascadés CR → CD → Club pour sélection club | ≤ 3 | Utile | ✅ Conserver |
| 5 | Autocomplete recherche club (nom/code) | ≤ 3 | Essentielle | ✅ Conserver (nouveau) |
| 6 | Copie de composition joueurs (depuis compétition précédente) | ≤ 3 | Spécialisé | ✅ Conserver |
| 7 | Attribution poule et tirage lors de l'ajout | ≤ 3 | Utile | ✅ Conserver |
| 8 | Séparation historique France / International | ≤ 3 | Utile | ✅ Conserver |
| 9 | **Bloqué si compétition verrouillée** (bouton masqué + vérification backend) | ≤ 3 | Essentielle | ✅ Conserver |

### 2.4 Modification d'équipes

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Modifier logo (chemin fichier) | ≤ 2 | Essentielle | ✅ Conserver |
| 2 | Modifier couleurs (color1, color2, colortext) | ≤ 2 | Essentielle | ✅ Conserver |
| 3 | Propager couleurs aux compétitions suivantes | ≤ 2 | Spécialisé | ✅ Conserver |
| 4 | Propager couleurs aux compétitions précédentes | ≤ 2 | Spécialisé | ✅ Conserver |
| 5 | Propager couleurs à toutes les équipes du club | ≤ 2 | Spécialisé | ✅ Conserver |
| 6 | Clic sur la cellule logo/couleurs ouvre la modal d'édition (pas de bouton crayon séparé) | ≤ 2 | Essentielle | ✅ Conserver |

### 2.5 Suppression

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Suppression individuelle — **bloquée si compétition verrouillée** | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Suppression en masse (bulk delete) — **bloquée si compétition verrouillée** | ≤ 3 | Essentielle | ✅ Conserver |
| 3 | Sélectionner tout / Désélectionner tout | ≤ 3 | Utile | ✅ Conserver |

### 2.6 Duplication

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Dupliquer équipes depuis une compétition source | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Mode "Remplacer et dupliquer" (vider puis copier) | ≤ 3 | Spécialisé | ✅ Conserver |
| 3 | Copie des compositions joueurs lors de la duplication | ≤ 3 | Spécialisé | ✅ Conserver |

### 2.7 Opérations spéciales

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Tirage au sort (attribution poule + ordre) | ≤ 4 | Essentielle | ✅ Conserver |
| 2 | Initialiser titulaires pour toute la compétition | ≤ 4 | Spécialisé | ✅ Conserver |
| 3 | Mise à jour automatique des logos (scan fichiers) | ≤ 2 | Spécialisé | ✅ Conserver |

### 2.8 Liens et documents

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Lien vers gestion joueurs (par équipe) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Feuille de présence FR (PDF) — via dropdown click-toggle | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Feuille de présence EN (PDF) | ≤ 10 | Utile | ✅ Conserver |
| 4 | Feuille de présence par catégorie (PDF) | ≤ 2 | Spécialisé | ✅ Conserver |
| 5 | Feuille de présence photo (PDF) | ≤ 10 | Utile | ✅ Conserver |
| 6 | Fiche contrôle (PDF) | ≤ 2 | Spécialisé | ✅ Conserver |

---

## 3. Structure de la Page

### 3.1 Vue Desktop

```
┌─────────────────────────────────────────────────────────────────────────┐
│  AdminWorkContextSummary                                                │
│  📅 Saison: 2026 │ 🔽 Périmètre: Groupe N1H (2 compétitions) [Modifier]│
├─────────────────────────────────────────────────────────────────────────┤
│  Header : Gestion des équipes                                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─ Sélecteur de compétition ──────────────────────────────────────────┐│
│  │ [▼ N1H - Nationale 1 Masculine ...]  NAT  CHPT  🟢ON  🔒Verrouillé ││
│  └─────────────────────────────────────────────────────────────────────┘│
│                                                                          │
│  ⚠️ Compétition verrouillée. Certaines modifications sont restreintes.   │
│                                                                          │
│  ┌─ Barre d'outils ───────────────────────────────────────────────────┐│
│  │ [+ Ajouter] [Dupliquer] [Init titulaires] [MAJ logos]              ││
│  │ [☑ Tout] [Supprimer sélection (3)]              Total: 12 équipes  ││
│  └─────────────────────────────────────────────────────────────────────┘│
│                                                                          │
│  ── Poule A ──────────────────────────────────────────────────────────  │
│  ┌───┬──────┬───┬───────┬──────────────────┬──────┬──────┬────────────┐│
│  │ ☐ │Poule │ # │Logo/🟦│ Équipe           │ Club │Matchs│  Actions   ││
│  ├───┼──────┼───┼───────┼──────────────────┼──────┼──────┼────────────┤│
│  │ ☐ │  A   │ 1 │ 🖼🟦  │ Acigné KP        │75001 │  6   │ 👥 📄      ││
│  │ ☐ │  A   │ 2 │ 🖼🟦  │ Strasbourg ASPTT │67003 │  6   │ 👥 📄 🗑️   ││
│  └───┴──────┴───┴───────┴──────────────────┴──────┴──────┴────────────┘│
│                                                                          │
│  TOTAL = 12 Équipes                                                      │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Vue Mobile

```
┌─────────────────────────────────────────────────┐
│  [☐] [🖼]  Acigné KP                   👥 🗑️   │
│       [🟦] Poule: A | #1 | 75001 | 6 Matchs    │
├─────────────────────────────────────────────────┤
│  [☐] [🖼]  Strasbourg ASPTT            👥 🗑️   │
│       [🟦] Poule: B | #2 | 67003 | 6 Matchs    │
└─────────────────────────────────────────────────┘
```

- Logo et aperçu couleurs empilés verticalement, cliquables pour ouvrir la modal d'édition
- Poule et Tirage éditables inline (clic sur le texte)
- Checkbox et actions compactes

### 3.3 Sélecteur de compétition

**Composant** : `<AdminCompetitionSingleSelect />` (partagé avec Documents et Classements)

Le sélecteur :
- Affiche les compétitions disponibles depuis le contexte de travail (`workContext.competitionCodes`)
- Auto-sélectionne automatiquement la première compétition si aucune sélection
- Persiste la sélection en localStorage (`kpi_admin_work_page_competition`)
- La sélection est partagée entre les pages Équipes, Documents et Classements
- La sélection est réinitialisée automatiquement lors d'un changement du contexte de travail

Affiche des badges à droite :
- Badge niveau (INT/NAT/REG) coloré
- Badge type (CHPT/CP/MULTI)
- Badge statut (ATT/ON/END) cliquable (profil ≤ 4)
- Indicateur verrouillage 🔒/🔓 cliquable (profil ≤ 4)

Lorsque la compétition est verrouillée, un bandeau d'alerte jaune s'affiche sous le sélecteur.

### 3.4 Barre d'outils

Boutons conditionnels selon profil ET état de verrouillage :
- **[+ Ajouter]** (profil ≤ 3 ET non verrouillé) → ouvre modal d'ajout
- **[Dupliquer]** (profil ≤ 3) → ouvre modal de duplication
- **[Init titulaires]** (profil ≤ 4) → confirmation puis action
- **[MAJ logos]** (profil ≤ 2) → confirmation puis action
- **[☑ Tout / ☐ Rien]** (profil ≤ 3 ET non verrouillé) → sélection/désélection
- **[🗑️ Supprimer sélection (N)]** (profil ≤ 3 ET non verrouillé) → suppression en masse
- **Total : N équipes** (lecture seule)

### 3.5 Colonnes du tableau

| Colonne | Description | Éditable | Profil |
|---------|-------------|----------|--------|
| ☐ | Checkbox sélection (masqué si verrouillé) | - | ≤ 3 |
| Poule | Lettre de poule (A-Z) | Inline | ≤ 6 |
| # Tirage | Numéro d'ordre (0-99) | Inline | ≤ 6 |
| Logo/Couleurs | Logo (25px) + aperçu couleurs côte à côte, cliquable | Clic → modal | ≤ 2 |
| Équipe | Libellé de l'équipe | - | - |
| Club | Code club | - | - |
| Matchs | Nombre de matchs joués (`Validation = 'O'`) | - | - |
| Actions | Boutons d'action | - | Variable |

### 3.6 Actions par ligne

| Action | Icône | Description | Profil |
|--------|-------|-------------|--------|
| Voir joueurs | 👥 | Navigue vers page gestion joueurs (legacy) | ≤ 10 |
| Feuille de présence | 📄 | Dropdown click-toggle (Teleport to body) avec liens PDF | ≤ 10 |
| Supprimer | 🗑️ | Supprime l'équipe (confirmation requise), masqué si verrouillé ou matchs > 0 | ≤ 3 |

**Note** : Le bouton "Modifier" (crayon) a été supprimé du tableau. L'édition des propriétés (logo/couleurs) se fait par clic sur la cellule logo/couleurs.

### 3.7 Dropdown feuille de présence

Le dropdown de la feuille de présence utilise un pattern **click-toggle avec Teleport** pour éviter les problèmes de clipping dans les conteneurs `overflow-hidden` :

1. Clic sur l'icône 📄 → toggle du dropdown
2. Le dropdown est téléporté dans `<body>` avec `position: fixed`
3. La position est calculée dynamiquement via `getBoundingClientRect()` du bouton
4. Fermeture : clic en dehors (event listener global) ou clic sur un lien
5. Contenu : Présence FR, Présence EN, Présence photo, Fiche contrôle (profil ≤ 2)

### 3.8 Groupement par poule

Les équipes sont groupées visuellement par poule avec un en-tête de séparation pour chaque poule (A, B, C...). Les équipes sans poule sont listées sous un en-tête "Sans poule". Tri au sein de chaque poule par Tirage puis Libelle.

---

## 4. Modals

### 4.1 Modal Ajout d'équipe

Deux onglets/modes dans la même modal, **l'historique est l'onglet par défaut** :

#### Onglet 1 (principal) : Depuis historique

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| Recherche équipe | autocomplete | Oui | Équipe existante dans kp_equipe |
| Poule | text(1) | Non | A-Z majuscule |
| Tirage | number | Non | 0-99 |
| Copier composition | checkbox + select | Non | Sélection compétition source |

**Liste historique** :
- Deux sections : 🇫🇷 France (code région ≠ 98) et 🌍 International (code région = 98)
- Recherche en temps réel (filtre texte)
- Sélection multiple autorisée (checkboxes)
- Non affiché pour compétitions de type POOL

#### Onglet 2 (secondaire) : Création manuelle

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| libelle | text(30) | Oui | Non vide |
| club | autocomplete + filtres | Oui | Club existant |
| poule | text(1) | Non | A-Z majuscule |
| tirage | number | Non | 0-99 |

**Filtres club** (section dépliable) :
| Champ | Type | Description |
|-------|------|-------------|
| Comité régional | select | Liste kp_cr, filtre les CD |
| Comité départemental | select | Cascadé depuis CR, filtre les clubs |
| Club | select / autocomplete | Cascadé depuis CD, ou recherche libre |

### 4.2 Modal Édition propriétés équipe

Ouverte par clic sur la cellule logo/couleurs dans le tableau (pas de bouton séparé).

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| Nom équipe | text (lecture seule) | - | - |
| Logo | text(50) | Non | Chemin fichier |
| Couleur principale (color1) | color picker | Non | Format hex |
| Couleur secondaire (color2) | color picker | Non | Format hex |
| Couleur texte (colortext) | color picker | Non | Format hex |
| Aperçu | preview carré 56px | - | Rendu visuel des couleurs + nom équipe |

**Aperçu couleurs** :
- Carré unique `w-14 h-14` : fond = color1, bordure = color2 (6px), texte "1" = colortext
- Nom de l'équipe affiché à côté du carré
- Labels des sélecteurs de couleur en `text-xs` pour éviter le retour à la ligne

**Options de propagation** (checkboxes) :
| Option | Description |
|--------|-------------|
| Compétitions suivantes | Appliquer aux prochaines compétitions de cette équipe |
| Compétitions précédentes | Appliquer aux compétitions passées de cette équipe |
| Toutes les équipes du club | Appliquer à toutes les équipes du même club |

### 4.3 Modal Duplication

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| Compétition source | select | Oui | Compétition existante de la même saison |
| Mode | radio | Oui | "Ajouter aux existantes" / "Remplacer (vider puis copier)" |
| Copier compositions | checkbox | Non | Copier aussi les joueurs |

### 4.4 Modal Confirmation suppression

Modals de confirmation standard (AdminConfirmModal) pour :
- Suppression individuelle
- Suppression en masse
- Initialisation titulaires
- Mise à jour logos
- Mode "Remplacer et dupliquer"

---

## 5. Aperçu des couleurs

L'aperçu des couleurs d'une équipe est un **carré unique** avec :

| Propriété | Tableau desktop | Tableau mobile | Modal édition |
|-----------|----------------|----------------|---------------|
| Taille | `w-7 h-7` | `w-6 h-6` | `w-14 h-14` |
| Bordure | 4px | 3px | 6px |
| Fond | color1 | color1 | color1 |
| Bordure | color2 | color2 | color2 |
| Texte | "1" en colortext | "1" en colortext | "1" en colortext |

Dans le tableau, le logo et l'aperçu couleurs sont côte à côte (desktop) ou empilés (mobile), dans une cellule unique cliquable (ouvre la modal d'édition, profil ≤ 2).

---

## 6. Endpoints API2

### 6.1 Lecture

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| GET | `/admin/competition-teams` | Liste des équipes d'une compétition | ≤ 10 |
| GET | `/admin/teams/search` | Autocomplete équipes historiques | ≤ 3 |
| GET | `/admin/teams/{numero}/compositions` | Compositions disponibles pour copie | ≤ 3 |
| GET | `/admin/clubs/search` | Autocomplete clubs (nom/code) | ≤ 3 |
| GET | `/admin/regional-committees` | Liste des comités régionaux | ≤ 3 |
| GET | `/admin/departmental-committees` | Liste des comités départementaux | ≤ 3 |
| GET | `/admin/clubs` | Liste des clubs (filtrée par CR/CD) | ≤ 3 |

### 6.2 Écriture

| Méthode | Endpoint | Description | Profil | Verrouillage |
|---------|----------|-------------|--------|--------------|
| POST | `/admin/competition-teams` | Ajouter équipe(s) | ≤ 3 | ❌ Bloqué si verrouillé |
| DELETE | `/admin/competition-teams/{id}` | Supprimer une équipe | ≤ 3 | ❌ Bloqué si verrouillé |
| POST | `/admin/competition-teams/bulk-delete` | Suppression en masse | ≤ 3 | ❌ Bloqué si verrouillé |
| PATCH | `/admin/competition-teams/{id}/pool-draw` | Modifier poule et tirage | ≤ 6 | - |
| PATCH | `/admin/competition-teams/{id}/colors` | Modifier logo et couleurs | ≤ 2 | - |
| POST | `/admin/competition-teams/duplicate` | Dupliquer depuis compétition source | ≤ 3 | - |
| POST | `/admin/competition-teams/update-logos` | Mise à jour automatique des logos | ≤ 2 | - |
| POST | `/admin/competition-teams/init-starters` | Initialiser les titulaires | ≤ 4 | - |
| PATCH | `/admin/competition-teams/toggle-lock` | Basculer le verrouillage | ≤ 4 | - |

### 6.3 Paramètres de requête

**GET /admin/competition-teams**

| Param | Type | Requis | Description |
|-------|------|--------|-------------|
| season | string | Oui | Code saison |
| competition | string | Oui | Code compétition |

Réponse :
```json
{
  "teams": [
    {
      "id": 12345,
      "libelle": "Acigné KP",
      "codeClub": "75001",
      "clubLibelle": "CK Acigné",
      "numero": 456,
      "poule": "A",
      "tirage": 1,
      "logo": "KIP/logo/3512-logo.png",
      "color1": "#FF0000",
      "color2": "#0000FF",
      "colortext": "#FFFFFF",
      "nbMatchs": 6
    }
  ],
  "competition": {
    "code": "N1H",
    "libelle": "Nationale 1 Masculine",
    "codeNiveau": "NAT",
    "codeTypeclt": "CHPT",
    "statut": "ON",
    "verrou": true
  },
  "total": 12
}
```

**GET /admin/teams/search**

| Param | Type | Requis | Description |
|-------|------|--------|-------------|
| q | string | Oui | Terme de recherche (min 2 caractères) |
| limit | number | Non | Max résultats (défaut: 20) |

Réponse :
```json
[
  {
    "numero": 456,
    "libelle": "Acigné KP",
    "codeClub": "75001",
    "clubLibelle": "CK Acigné",
    "international": false
  }
]
```

**GET /admin/teams/{numero}/compositions**

| Param | Type | Requis | Description |
|-------|------|--------|-------------|
| season | string | Oui | Saison en cours |

Réponse :
```json
[
  {
    "season": "2025",
    "competition": "N1H",
    "competitionLibelle": "Nationale 1 Masculine",
    "playerCount": 12
  }
]
```

**POST /admin/competition-teams**

⚠️ Retourne HTTP 403 si la compétition est verrouillée.

Body (depuis historique — mode par défaut) :
```json
{
  "season": "2026",
  "competition": "N1H",
  "mode": "history",
  "teamNumbers": [456, 789],
  "poule": "A",
  "tirage": 0,
  "copyComposition": {
    "season": "2025",
    "competition": "N1H"
  }
}
```

Body (création manuelle) :
```json
{
  "season": "2026",
  "competition": "N1H",
  "mode": "manual",
  "libelle": "Nouvelle Équipe",
  "codeClub": "75001",
  "poule": "A",
  "tirage": 5
}
```

**DELETE /admin/competition-teams/{id}**

⚠️ Retourne HTTP 403 si la compétition est verrouillée.
⚠️ Retourne HTTP 409 si l'équipe a des matchs validés.

**POST /admin/competition-teams/bulk-delete**

⚠️ Retourne HTTP 403 si la compétition est verrouillée.

Body :
```json
{
  "ids": [123, 456],
  "season": "2026",
  "competition": "N1H"
}
```

**POST /admin/competition-teams/duplicate**

Body :
```json
{
  "season": "2026",
  "targetCompetition": "N1H",
  "sourceCompetition": "N1H-2025",
  "sourceSeason": "2025",
  "mode": "append",
  "copyPlayers": true
}
```

**PATCH /admin/competition-teams/{id}/pool-draw**

Body :
```json
{
  "poule": "B",
  "tirage": 3
}
```

**PATCH /admin/competition-teams/{id}/colors**

Body :
```json
{
  "logo": "KIP/logo/3512-logo.png",
  "color1": "#FF0000",
  "color2": "#0000FF",
  "colortext": "#FFFFFF",
  "propagateNext": true,
  "propagatePrevious": false,
  "propagateClub": false
}
```

**PATCH /admin/competition-teams/toggle-lock**

Body :
```json
{
  "season": "2026",
  "competition": "N1H"
}
```

Réponse :
```json
{
  "verrou": true
}
```

---

## 7. Schéma de données

### 7.1 Table kp_equipe (équipes historiques)

| Colonne | Type | Description |
|---------|------|-------------|
| Numero | smallint(6) | PK, auto-increment |
| Libelle | varchar(30) | Nom de l'équipe |
| Code_club | varchar(6) | FK → kp_club(Code) |
| color1 | varchar(30) | Couleur principale (hex) |
| color2 | varchar(30) | Couleur secondaire (hex) |
| colortext | varchar(30) | Couleur texte (hex) |
| logo | varchar(50) | Chemin du logo |

### 7.2 Table kp_competition_equipe (équipes par compétition)

| Colonne | Type | Description |
|---------|------|-------------|
| Id | int(10) unsigned | PK, auto-increment |
| Code_compet | varchar(12) | FK → kp_competition(Code) |
| Code_saison | char(4) | FK → kp_competition(Code_saison) |
| Libelle | varchar(40) | Nom de l'équipe dans la compétition |
| Code_club | varchar(6) | FK → kp_club(Code) |
| Numero | smallint(6) | FK → kp_equipe(Numero) |
| Poule | varchar(3) | Lettre de poule (A, B, etc.) |
| Tirage | tinyint(4) | Numéro tirage au sort |
| logo | varchar(50) | Logo de l'équipe (chemin relatif dans `img/`) |
| color1 | varchar(30) | Couleur principale |
| color2 | varchar(30) | Couleur secondaire |
| colortext | varchar(30) | Couleur texte |
| Id_dupli | int(11) | Référence de duplication |
| Pts, Clt, J, G, N, P, F, Plus, Moins, Diff | smallint | Stats classement (calculé) |
| PtsNiveau, CltNiveau | double/smallint | Classement ajusté |
| *_publi | smallint/double | Stats publiées (miroir des stats) |

### 7.3 Table kp_competition_equipe_joueur (joueurs par équipe)

| Colonne | Type | Description |
|---------|------|-------------|
| Id_equipe | int(10) unsigned | PK, FK → kp_competition_equipe(Id) |
| Matric | int(11) unsigned | PK, FK → kp_licence(Matric) |
| Nom | varchar(30) | Nom du joueur |
| Prenom | varchar(30) | Prénom du joueur |
| Sexe | char(1) | Genre (M/F) |
| Categ | varchar(8) | Code catégorie |
| Numero | smallint(6) | Numéro de maillot |
| Capitaine | char(1) | Capitaine ('-' = non) |

### 7.4 Table kp_club (clubs)

| Colonne | Type | Description |
|---------|------|-------------|
| Code | varchar(6) | PK, Code club |
| Libelle | varchar(100) | Nom du club |
| Officiel | char(1) | Statut officiel |
| Code_comite_dep | varchar(6) | FK → kp_cd(Code) |
| Coord | varchar(50) | Contact |
| Postal | varchar(100) | Adresse |
| www | varchar(60) | Site web |
| email | varchar(60) | Email |

### 7.5 Table kp_match (matchs — colonnes clés)

| Colonne | Type | Description |
|---------|------|-------------|
| Id_equipeA | int(10) unsigned | FK → kp_competition_equipe(Id) |
| Id_equipeB | int(10) unsigned | FK → kp_competition_equipe(Id) |
| Validation | char(1) | `'O'` = validé, `'N'` = non validé |

**Note** : Le décompte des matchs joués utilise `WHERE m.Validation = 'O'` (pas `Statut`).

### 7.6 Tables auxiliaires

- **kp_cr** : Comités régionaux (Code PK, Libelle, Region)
- **kp_cd** : Comités départementaux (Code PK, Libelle, Code_comite_reg FK → kp_cr)
- **kp_licence** : Licenciés (Matric PK, Naissance, etc.) — pour recalcul catégories
- **kp_categorie** : Catégories d'âge (Annee_deb, Annee_fin, Code_categ, Sexe)

---

## 8. Composants Vue

### 8.1 Structure des fichiers

```
sources/app4/pages/teams/
├── index.vue                        # Page principale (~1700 lignes)

sources/app4/components/admin/
├── CompetitionSingleSelect.vue      # Sélecteur partagé avec Documents et Classements

sources/app4/types/
├── teams.ts                         # Types TypeScript pour les équipes
```

**Note** : La page est auto-suffisante dans `index.vue` (comme les autres pages app4 existantes). Le composant `CompetitionSingleSelect` est extrait et partagé entre les pages Équipes, Documents et Classements pour garantir la cohérence et la persistance de la sélection.

### 8.2 Types TypeScript

```typescript
// types/teams.ts

export interface CompetitionTeam {
  id: number
  libelle: string
  codeClub: string
  clubLibelle: string
  numero: number
  poule: string
  tirage: number
  logo: string | null
  color1: string | null
  color2: string | null
  colortext: string | null
  nbMatchs: number
}

export interface CompetitionTeamInfo {
  code: string
  libelle: string
  codeNiveau: 'INT' | 'NAT' | 'REG'
  codeTypeclt: 'CHPT' | 'CP' | 'MULTI'
  statut: 'ATT' | 'ON' | 'END'
  verrou: boolean
}

export interface CompetitionTeamsResponse {
  teams: CompetitionTeam[]
  competition: CompetitionTeamInfo
  total: number
}

export interface HistoricalTeam {
  numero: number
  libelle: string
  codeClub: string
  clubLibelle: string
  international: boolean
}

export interface TeamComposition {
  season: string
  competition: string
  competitionLibelle: string
  playerCount: number
}

export interface TeamAddFormData {
  mode: 'manual' | 'history'
  // Manual mode
  libelle: string
  codeClub: string
  // History mode
  teamNumbers: number[]
  // Common
  poule: string
  tirage: number
  // Copy composition
  copyComposition: {
    season: string
    competition: string
  } | null
}

export interface TeamColorsFormData {
  logo: string
  color1: string
  color2: string
  colortext: string
  propagateNext: boolean
  propagatePrevious: boolean
  propagateClub: boolean
}

export interface DuplicateFormData {
  sourceCompetition: string
  sourceSeason: string
  mode: 'append' | 'replace'
  copyPlayers: boolean
}

export interface RegionalCommittee {
  code: string
  libelle: string
}

export interface DepartmentalCommittee {
  code: string
  libelle: string
  codeComiteReg: string
}

export interface Club {
  code: string
  libelle: string
  codeComiteDep: string
}
```

### 8.3 Traductions i18n

Les clés i18n sont dans la section `teams_page` (et non `teams`) des fichiers de localisation :

```json
{
  "teams_page": {
    "title": "Gestion des équipes",
    "select_competition": "Sélectionner une compétition",
    "total": "Total : {count} équipe(s)",
    "pool_header": "Poule {letter}",
    "no_pool": "Sans poule",
    "empty": "Aucune équipe inscrite dans cette compétition",
    "no_competition": "Veuillez sélectionner une compétition",
    "add": "Ajouter",
    "duplicate": "Dupliquer",
    "init_starters": "Init. titulaires",
    "update_logos": "MAJ logos",
    "delete_selected": "Supprimer la sélection",
    "select_all": "Tout sélectionner",
    "deselect_all": "Tout désélectionner",
    "draw": "Tirage au sort",
    "columns": {
      "poule": "Poule",
      "tirage": "Tirage",
      "logo": "Logo",
      "equipe": "Équipe",
      "couleurs": "Couleurs",
      "club": "Club",
      "matchs": "Matchs",
      "actions": "Actions"
    },
    "add_modal": {
      "title": "Ajouter une équipe",
      "tab_history": "Depuis l'historique",
      "tab_manual": "Création manuelle",
      "libelle": "Nom de l'équipe",
      "libelle_placeholder": "Ex: Mon Équipe KP",
      "club": "Club",
      "poule": "Poule",
      "poule_placeholder": "A-Z",
      "tirage": "Tirage",
      "search_team": "Rechercher une équipe...",
      "search_club": "Rechercher un club...",
      "france": "France",
      "international": "International",
      "copy_composition": "Copier la composition joueurs",
      "select_source": "Depuis la compétition",
      "no_compositions": "Aucune composition disponible",
      "filter_cr": "Comité régional",
      "filter_cd": "Comité départemental",
      "filter_club": "Club",
      "all": "Tous",
      "selected_teams": "{count} équipe(s) sélectionnée(s)",
      "players": "{count} joueur(s)"
    },
    "edit_modal": {
      "title": "Propriétés de l'équipe",
      "logo": "Logo (chemin fichier)",
      "logo_placeholder": "Ex: 75001-logo.png",
      "color1": "Couleur principale",
      "color2": "Couleur secondaire",
      "colortext": "Couleur texte",
      "preview": "Aperçu",
      "propagate_next": "Appliquer aux compétitions suivantes",
      "propagate_previous": "Appliquer aux compétitions précédentes",
      "propagate_club": "Appliquer à toutes les équipes du club"
    },
    "duplicate_modal": {
      "title": "Dupliquer les équipes",
      "source_competition": "Compétition source",
      "source_competition_placeholder": "Sélectionner une compétition",
      "mode_append": "Ajouter aux équipes existantes",
      "mode_replace": "Vider et remplacer",
      "copy_players": "Copier aussi les compositions joueurs",
      "warning_replace": "Attention : toutes les équipes actuelles seront supprimées !"
    },
    "confirm_delete": "Supprimer l'équipe \"{name}\" ?",
    "confirm_delete_multiple": "Supprimer {count} équipe(s) sélectionnée(s) ?",
    "confirm_init_starters": "Initialiser les titulaires pour toute la compétition ? La compétition sera automatiquement verrouillée.",
    "confirm_update_logos": "Scanner et mettre à jour les logos de toutes les équipes ?",
    "success_added": "Équipe(s) ajoutée(s) avec succès",
    "success_updated": "Équipe modifiée avec succès",
    "success_deleted": "Équipe(s) supprimée(s) avec succès",
    "success_duplicated": "Équipes dupliquées avec succès",
    "success_init_starters": "Titulaires initialisés avec succès",
    "success_update_logos": "Logos mis à jour avec succès",
    "error_load": "Erreur lors du chargement des équipes",
    "error_save": "Erreur lors de l'enregistrement",
    "error_delete": "Erreur lors de la suppression",
    "players": "Joueurs",
    "presence_sheet": "Feuille de présence",
    "presence_sheet_en": "Feuille de présence (EN)",
    "presence_sheet_cat": "Présence par catégorie",
    "presence_sheet_photo": "Présence avec photo",
    "control_sheet": "Fiche contrôle",
    "locked": "Compétition verrouillée",
    "unlocked": "Compétition déverrouillée",
    "competition_locked_notice": "Cette compétition est verrouillée. Certaines modifications sont restreintes.",
    "cannot_delete_has_matches": "Impossible de supprimer : l'équipe a des matchs joués"
  }
}
```

---

## 9. Édition inline

L'édition inline des champs Poule et Tirage directement dans le tableau (desktop et mobile) :

1. **Click** sur la cellule Poule ou Tirage → la cellule passe en mode édition (input)
2. **Mobile** : le texte "Poule: A | #1" est aussi cliquable pour passer en mode édition
3. **Validation** :
   - Poule : une lettre majuscule A-Z, ou vide
   - Tirage : nombre entier 0-99
4. **Sauvegarde** : `PATCH /admin/competition-teams/{id}/pool-draw` sur blur ou Enter
5. **Annulation** : Escape restaure la valeur précédente
6. **Feedback** : toast success/error après sauvegarde
7. **Focus** : utilise `nextTick` + `document.getElementById()` pour trouver l'input (desktop ou mobile)

---

## 10. Logos

### 10.1 Construction du chemin

Le frontend construit l'URL du logo avec une double stratégie de fallback :

```javascript
const getLogoUrl = (team) => {
  // 1. Si le champ logo est renseigné en base → chemin relatif dans img/
  if (team.logo) return `${legacyBase}/img/${team.logo}`
  // 2. Sinon, convention legacy : img/KIP/logo/{Code_club}-logo.png
  if (team.codeClub) return `${legacyBase}/img/KIP/logo/${team.codeClub}-logo.png`
  // 3. Pas de logo
  return null
}
```

**Note** : Le champ `logo` en base contient un chemin relatif (ex: `KIP/logo/3512-logo.png`), ne pas ajouter le préfixe `KIP/logo/` lors de la construction de l'URL.

### 10.2 Fichiers de logo

Les logos sont stockés dans :
- `img/KIP/logo/{code}-logo.png` (clubs français)
- `img/Nations/{code}.png` (équipes nationales)

La fonctionnalité "MAJ logos" scanne ces répertoires et met à jour les chemins en base. Les fichiers de cache JSON sont générés dans `live/cache/logos/`.

---

## 11. Documents PDF (liens legacy)

Les documents PDF sont générés par le backend legacy. La page app4 fournit des liens vers ces fichiers via un dropdown click-toggle :

| Document | URL Legacy | Paramètres |
|----------|-----------|------------|
| Feuille de présence FR | `FeuillePresence.php` | `?equipe={id}` ou `?S={season}&Compet={code}` |
| Feuille de présence EN | `FeuillePresenceEN.php` | idem |
| Feuille de présence catégorie | `FeuillePresenceCat.php` | idem |
| Feuille de présence photo | `FeuillePresencePhoto.php` | idem |
| Fiche contrôle | `FeuillePresenceVisa.php` | idem |
| Liste des équipes | `FeuilleGroups.php` | `?S={season}&Compet={code}` |

Les URLs sont construites avec `legacyBase` (config.public.legacyBaseUrl) comme sur la page Documents.

---

## 12. Sécurité

### 12.1 Validation côté serveur

- Vérification que la compétition appartient à la saison demandée
- **Vérification du verrouillage** : ajout et suppression bloqués si `Verrou = 1` (HTTP 403)
- Vérification que l'utilisateur a accès à cette compétition (filtres)
- Code club doit exister dans kp_club
- Numéro d'équipe historique doit exister dans kp_equipe
- Poule : 1 caractère A-Z ou vide
- Tirage : entier 0-99
- Suppression impossible si l'équipe a des matchs validés (`Validation = 'O'`, nbMatchs > 0)

### 12.2 Audit

Toutes les actions sont journalisées dans kp_journal :
- "Ajout equipe" (création)
- "Suppression equipes" (suppression)
- "Duplication equipes" (duplication)
- "Tirage au sort" (modification poule/tirage)
- "Update logo equipes" (mise à jour logos)
- "Init titulaires" (initialisation titulaires)

---

## 13. Notes techniques

### 13.1 MariaDB LIMIT

Avec Doctrine DBAL sur MariaDB, les paramètres `LIMIT` ne peuvent pas être bindés via `?`. Le driver MariaDB traite les paramètres LIMIT bindés comme des chaînes, provoquant des erreurs SQL. Solution :

```php
// INCORRECT — provoque une erreur SQL
$sql = "SELECT ... LIMIT ?";
$stmt->executeQuery([$limit]);

// CORRECT — cast et interpolation directe
$sql = "SELECT ... LIMIT " . (int) $limit;
$stmt->executeQuery([]);
```

### 13.2 Décompte des matchs

La colonne `kp_match.Validation` (valeurs `'O'` / `'N'`) détermine si un match est validé. **Ne pas utiliser** la colonne `Statut` qui a une sémantique différente.

### 13.3 Page joueurs (GestionEquipeJoueur.php)

Le lien "Voir joueurs" naviguera vers la future page `/players?team={id}` quand elle sera migrée. En attendant, le lien pointera vers la page legacy `GestionEquipeJoueur.php?idEquipe={id}`.

### 13.4 Compétition POOL

Le legacy supporte une compétition spéciale "POOL" pour les arbitres. L'historique des équipes n'est pas affiché pour ce type. Ce comportement est à conserver.

### 13.5 Catégories joueurs

Lors de la copie de composition, les catégories des joueurs sont recalculées en fonction de l'année de naissance et des tranches définies dans kp_categorie. Le backend gère ce recalcul automatiquement.

---

## 14. Persistance de la Sélection de Compétition

### 14.1 Mécanisme

La sélection de compétition sur cette page est **persistée en localStorage** et **partagée** avec les pages Documents et Classements via le store `workContextStore`.

**Clé localStorage** : `kpi_admin_work_page_competition`

**State store** :
```typescript
// stores/workContextStore.ts
interface WorkContextState {
  // ... autres propriétés
  pageCompetitionCode: string  // Compétition sélectionnée pour pages mono-compétition
}
```

**Getter store** :
```typescript
pageCompetition(): Competition | undefined {
  if (!this.pageCompetitionCode) return undefined
  return this.competitions.find(c => c.code === this.pageCompetitionCode)
}
```

### 14.2 Auto-sélection

Le composant `AdminCompetitionSingleSelect` :
- Auto-sélectionne automatiquement la première compétition disponible si aucune sélection
- Utilise un watcher avec `immediate: true` pour garantir qu'une compétition est toujours sélectionnée
- Réinitialise la sélection si la compétition choisie n'est plus disponible dans le nouveau contexte

```typescript
watch(
  () => workContext.competitionCodes,
  (codes) => {
    if (!codes.length) {
      if (workContext.pageCompetitionCode) {
        workContext.setPageCompetition('')
        emit('change', '')
      }
      return
    }
    // Si la sélection actuelle est toujours valide, la conserver
    if (workContext.pageCompetitionCode && codes.includes(workContext.pageCompetitionCode)) {
      return
    }
    // Sinon, auto-sélectionner la première compétition
    workContext.setPageCompetition(codes[0])
    emit('change', codes[0])
  },
  { immediate: true },
)
```

### 14.3 Réinitialisation

La sélection de compétition est automatiquement réinitialisée lors d'un **changement du contexte de travail** (saison ou périmètre).

Toutes les actions du store qui modifient le périmètre appellent `resetPageCompetition()` :
- `selectAll()`
- `selectCompetitions(codes)`
- `selectSection(sectionId)`
- `selectGroup(groupCode)`
- `selectEvent(eventId)`

```typescript
// Action store
resetPageCompetition() {
  this.pageCompetitionCode = ''
  localStorage.removeItem(STORAGE_KEYS.pageCompetitionCode)
}
```

### 14.4 Pages concernées

Les pages suivantes partagent cette sélection persistée :
1. **Équipes** (`/teams`)
2. **Documents** (`/documents`)
3. **Classements** (`/rankings`)

Toutes utilisent le même composant `<AdminCompetitionSingleSelect />` et lisent/écrivent `workContext.pageCompetitionCode`.

---

**Document créé le** : 2026-02-04
**Dernière mise à jour** : 2026-02-08
**Statut** : ✅ Implémenté — frontend (app4) + backend (api2) + sélection persistée
