# Spécification - Page Gestion du Classement

## 1. Vue d'ensemble

La page Classement permet de gérer les classements d'une compétition : calcul automatique depuis les résultats des matchs, modification manuelle des valeurs, publication du classement pour l'affichage public, consolidation des phases (CP), génération de PDFs, et affectation/transfert d'équipes vers une autre compétition.

La page s'organise en **deux onglets** : le **Classement calculé** (provisoire, de travail) et le **Classement publié** (copie figée pour l'affichage public).

**Route** : `/rankings`

**Accès** :
- Profil ≤ 10 : Lecture seule (consultation, PDFs publics)
- Profil ≤ 6 : Recalcul du classement, checkbox "matchs non verrouillés"
- Profil ≤ 4 : Modification manuelle inline, publication, consolidation phases, affectation équipes, classement initial
- Profil ≤ 3 : Dé-publication, changement de statut compétition, transfert équipes

**Restriction par statut** : Lorsque la compétition n'est pas au statut `ON` (en cours), les opérations suivantes sont **interdites** :
- Recalcul du classement
- Modification manuelle des valeurs
- Publication / dé-publication
- Consolidation / déconsolidation des phases

**Page PHP Legacy** : `GestionClassement.php` + `GestionClassement.tpl` + `GestionClassement.js`

---

## 2. Fonctionnalités

### 2.1 Sélection de compétition

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Sélecteur de compétition unique (filtré par contexte de travail, persisté) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Badges info compétition (niveau INT/NAT/REG, type CHPT/CP/MULTI, statut ATT/ON/END) | ≤ 10 | Utile | ✅ Conserver |
| 3 | Sélecteur du type de classement (Championnat, Tournoi, Multi) | ≤ 3 | Essentielle | ✅ Conserver |
| 4 | Badge statut cliquable pour changer le statut (ATT→ON→END→ATT) | ≤ 3 | Essentielle | ✅ Conserver |
| 5 | Auto-sélection première compétition disponible | ≤ 10 | Essentielle | ✅ Conserver |
| 6 | Persistance de la sélection partagée avec pages Équipes et Documents | ≤ 10 | Essentielle | ✅ Conserver |
| 7 | Affichage du goal-average applicable (général ou particulier) | ≤ 10 | Utile | ✅ Conserver |

### 2.2 Onglet Classement calculé

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Tableau du classement général (toutes compétitions) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Tableau déroulement par phase (CP uniquement) | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Modification manuelle inline des valeurs (Clt, Pts, J, G, N, P, F, +, -, Diff) | ≤ 4 | Essentielle | ✅ Conserver |
| 4 | Consolidation de phase (checkbox par phase, CP uniquement) | ≤ 4 | Essentielle | ✅ Conserver |
| 5 | Suppression d'une équipe d'une phase (si 0 match joué) | ≤ 4 | Spécialisé | ✅ Conserver |
| 6 | Affichage drapeaux pays (compétitions internationales, Code_niveau = INT) | ≤ 10 | Utile | ✅ Conserver |
| 7 | Indication qualifiés (▲ vert) et éliminés (▼ rouge) | ≤ 10 | Utile | ✅ Conserver |
| 8 | Affichage vainqueur/perdant par phase éliminatoire (Type E) | ≤ 10 | Utile | ✅ Conserver |

### 2.3 Onglet Classement publié

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Tableau du classement général publié (lecture seule) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Tableau déroulement publié par phase (CP uniquement, lecture seule) | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Affichage drapeaux pays (compétitions internationales) | ≤ 10 | Utile | ✅ Conserver |
| 4 | Indication qualifiés/éliminés | ≤ 10 | Utile | ✅ Conserver |
| 5 | Affichage vainqueur/perdant par phase éliminatoire | ≤ 10 | Utile | ✅ Conserver |

### 2.4 Actions sur le classement

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Recalculer le classement (statut ON requis) | ≤ 6 | Essentielle | ✅ Conserver |
| 2 | Checkbox "Inclure les matchs non verrouillés" (persistée) | ≤ 6 | Essentielle | ✅ Conserver |
| 3 | Publier le classement (copie calcul → publié, statut ON requis) | ≤ 4 | Essentielle | ✅ Conserver |
| 4 | Supprimer le classement publié (RAZ, statut ON requis) | ≤ 3 | Spécialisé | ✅ Conserver |
| 5 | Accéder au classement initial (CHPT uniquement) | ≤ 6 | Spécialisé | ✅ Conserver |

### 2.5 Informations de calcul et publication

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Date/heure du dernier calcul + auteur | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Mode de calcul (tous matchs / verrouillés uniquement) | ≤ 10 | Utile | ✅ Conserver |
| 3 | Date/heure du calcul publié + date de publication + auteur | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | Alerte si classement calculé ≠ classement publié | ≤ 10 | Essentielle | ✅ Conserver |
| 5 | Indication "Classement non calculé" / "Classement non publié" | ≤ 10 | Utile | ✅ Conserver |

### 2.6 Liens PDF

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | PDF Classement général (admin provisoire) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | PDF Classement général (public) | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | PDF Déroulement (admin, CP uniquement) | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | PDF Déroulement (public, CP uniquement) | ≤ 10 | Essentielle | ✅ Conserver |
| 5 | PDF Détail par équipe (admin, sauf MULTI) | ≤ 10 | Essentielle | ✅ Conserver |
| 6 | PDF Détail par équipe (public, sauf MULTI) | ≤ 10 | Essentielle | ✅ Conserver |
| 7 | PDF Matchs (admin, sauf MULTI) | ≤ 10 | Essentielle | ✅ Conserver |
| 8 | PDF Matchs (public, sauf MULTI) | ≤ 10 | Essentielle | ✅ Conserver |

**Liens PDF par type de compétition (legacy PHP)** :

| Type | Admin | Public |
|------|-------|--------|
| CHPT | `FeuilleCltChpt.php`, `FeuilleCltChptDetail.php`, `FeuilleCltNiveauJournee.php` | `PdfCltChpt.php`, `PdfCltChptDetail.php`, `PdfCltNiveauJournee.php` |
| CP | `FeuilleCltNiveau.php`, `FeuilleCltNiveauPhase.php`, `FeuilleCltNiveauDetail.php` | `PdfCltNiveau.php`, `PdfCltNiveauPhase.php`, `PdfCltNiveauDetail.php` |
| MULTI | `FeuilleCltMulti.php` | `PdfCltMulti.php` |
| Matchs | `FeuilleListeMatchs.php?Compet={code}` | `PdfListeMatchs.php?Compet={code}` |

### 2.7 Affectation / Transfert d'équipes

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Sélection multiple d'équipes (checkboxes dans les tableaux) | ≤ 4 | Essentielle | ✅ Conserver |
| 2 | Sélection saison de destination | ≤ 4 | Essentielle | ✅ Conserver |
| 3 | Sélection compétition de destination (filtrée par saison) | ≤ 4 | Essentielle | ✅ Conserver |
| 4 | Transfert avec copie de la feuille de présence (joueurs) | ≤ 4 | Essentielle | ✅ Conserver |
| 5 | Recalcul des catégories d'âge lors du transfert | ≤ 4 | Essentielle | ✅ Conserver |
| 6 | Protection : équipe déjà présente dans la destination (même Numero) non dupliquée | ≤ 4 | Essentielle | ✅ Conserver |

---

## 3. Types de classement

### 3.1 Championnat (CHPT)

- **Classement général** : Tableau unique trié par Pts DESC, Diff DESC, Plus DESC
- **Colonnes** : Clt, Équipe, Pts (÷100), J, G, N, P, F, +, -, Diff
- **Barème points** : Configurable par compétition (format `4-2-1-0` = victoire-nul-défaite-forfait × 100)
- **Goal-average** : Général (par défaut) ou Particulier (head-to-head entre équipes à égalité)
- **Classement initial** : Valeurs de départ ajoutées au calcul (voir section 11)
- **Pas de déroulement** par phase

### 3.2 Coupe / Tournoi (CP)

- **Classement général** : Tableau trié par CltNiveau DESC, Diff DESC
- **Colonnes** : Clt, Équipe, J (CP n'affiche pas Pts/G/N/P/F/+/-/Diff dans le général)
- **Déroulement** : Tableau par phase avec deux types de rendu :
  - **Type C (Classement)** : Tableau avec Clt, Pts (÷100), J, G, N, P, F, +, -, Diff par phase
  - **Type E (Élimination)** : Liste vainqueur/perdant par phase
- **Points pondérés** : `PtsNiveau = 64^niveau × (4|3|2|1)` selon résultat
- **Consolidation** : Phases verrouillables (exclues du recalcul)

### 3.3 Multi-Compétition (MULTI)

- **Classement général** : Tableau trié par Pts DESC
- **Colonnes** : Clt, Structure (équipe/club/CD/CR/nation), Pts, J (nombre de participations)
- **Points** : Définis par grille JSON `{"1":10, "2":6, "3":4, "default":0}`
- **Agrégation** : Somme des points obtenus dans les compétitions sources
- **Types de structure** :
  - `team` (défaut) : Par numéro d'équipe
  - `club` : Par code club (toutes équipes du même club)
  - `cd` : Par comité départemental
  - `cr` : Par comité régional
  - `nation` : Par pays (CIO code pour internationaux, 'FRA' pour clubs français)
- **Pas de déroulement** par phase
- **Pas de lien PDF matchs** (les matchs sont dans les compétitions sources)

---

## 4. Structure de la Page

### 4.1 Vue Desktop

```
┌─────────────────────────────────────────────────────────────────────────┐
│  AdminWorkContextSummary                                                │
│  📅 Saison: 2026 │ 🔽 Périmètre: Groupe N1H (2 compétitions) [Modifier]│
├─────────────────────────────────────────────────────────────────────────┤
│  Header : Gestion du Classement                                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─ Sélecteur de compétition ──────────────────────────────────────────┐│
│  │ [▼ ECM - ECA European Championships Men]  INT  CP  🟢ON             ││
│  └─────────────────────────────────────────────────────────────────────┘│
│                                                                          │
│  ┌─ Type de classement ───────────────────────────────────────────────┐ │
│  │ [▼ Tournoi à élimination]   Goal-average : Général                 │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ┌─ Onglets ──────────────────────────────────────────────────────────┐ │
│  │ [📊 Classement calculé]  [📢 Classement publié]                     │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ═══ Onglet "Classement calculé" ═════════════════════════════════════  │
│                                                                          │
│  ┌─ Infos calcul ────────────────────────────────────────────────────┐ │
│  │ Calcul: 02/12/2025 à 04:49 (par Laurent Garigue)                  │ │
│  │ ☑ Inclure les matchs non verrouillés                               │ │
│  │ [Recalculer]                                                        │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ── Classement général ──────────────────────────────────────────────  │
│  ┌───┬───┬────┬──────────────────┬───┐                                  │
│  │ ☐ │ ▲ │Clt │ Équipe           │ J │   (CP : colonnes réduites)       │
│  ├───┼───┼────┼──────────────────┼───┤                                  │
│  │ ☐ │ 🇪🇸│  1 │ ESP Men          │ 9 │                                  │
│  │ ☐ │ 🇮🇹│  2 │ ITA Men          │ 9 │                                  │
│  │ ☐ │   │  3 │ FRA Men          │ 7 │                                  │
│  └───┴───┴────┴──────────────────┴───┘                                  │
│                                                                          │
│  ── Déroulement (CP uniquement) ─────────────────────────────────────  │
│  ┌────────────────────────────────────────────────────────────────────┐ │
│  │ ☑ Phase consolidée  │  Final (Enschede)                            │ │
│  ├───┬────┬──────────────────┬────┬───┬───┬───┬───┬───┬───┬───┬─────┤ │
│  │ 🗑│Clt │ Équipe           │Pts │ J │ G │ N │ P │ F │ + │ - │Diff │ │
│  ├───┼────┼──────────────────┼────┼───┼───┼───┼───┼───┼───┼───┼─────┤ │
│  │   │ 1  │ ESP Men          │ 9  │ 3 │ 3 │ 0 │ 0 │ 0 │ 13│ 5 │  8 │ │
│  │   │ 2  │ ITA Men          │ 5  │ 3 │ 1 │ 2 │ 0 │ 0 │ 8 │ 5 │  3 │ │
│  └───┴────┴──────────────────┴────┴───┴───┴───┴───┴───┴───┴───┴─────┘ │
│  ┌────────────────────────────────────────────────────────────────────┐ │
│  │ Phase éliminatoire : Semi-final (Enschede)                         │ │
│  ├────────────────────────────────────────────────────────────────────┤ │
│  │ Vainqueur    ESP Men                                               │ │
│  │ Perdant      SUI Men                                               │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ┌─ Actions ─────────────────────────────────────────────────────────┐ │
│  │ [Publier le classement]                                             │ │
│  │ [Classement initial...] (CHPT uniquement)                           │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ┌─ PDFs Admin (provisoire) ─────────────────────────────────────────┐ │
│  │ 📄 Classement général  📄 Déroulement  📄 Détail/équipe  📄 Matchs │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ═══ Onglet "Classement publié" ══════════════════════════════════════  │
│                                                                          │
│  ┌─ Infos publication ───────────────────────────────────────────────┐ │
│  │ Calcul: 02/12/2025 à 04:49                                        │ │
│  │ Publication: 02/12/2025 à 05:57 (par Laurent Garigue)              │ │
│  │ ⚠️ Le classement publié est différent du classement calculé        │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ── Classement général publié ───────────────────────────────────────  │
│  ┌───┬────┬──────────────────┬───┐                                      │
│  │ ▲ │Clt │ Équipe           │ J │                                      │
│  ├───┼────┼──────────────────┼───┤                                      │
│  │ 🇪🇸│  1 │ ESP Men          │ 9 │                                      │
│  │ 🇮🇹│  2 │ ITA Men          │ 9 │                                      │
│  └───┴────┴──────────────────┴───┘                                      │
│                                                                          │
│  ── Déroulement publié (CP uniquement) ──────────────────────────────  │
│  (même format que calculé, lecture seule)                                │
│                                                                          │
│  ┌─ Actions ─────────────────────────────────────────────────────────┐ │
│  │ [Supprimer le classement publié] (profil ≤ 3)                       │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ┌─ PDFs Public ─────────────────────────────────────────────────────┐ │
│  │ 📄 Classement général  📄 Déroulement  📄 Détail/équipe  📄 Matchs │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
│  ═══ Section Affectation (sous les onglets) ══════════════════════════  │
│                                                                          │
│  ┌─ Affectation / Promotion / Relégation ────────────────────────────┐ │
│  │ Affecter vers saison :      [▼ 2025]                                │ │
│  │ Affecter vers compétition : [▼ ECA European Championships Men]      │ │
│  │ [Affecter les équipes cochées]                                      │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### 4.2 Vue Mobile

```
┌─────────────────────────────────────────────────┐
│  📅 2026 │ Groupe N1H │ [Modifier]               │
├─────────────────────────────────────────────────┤
│  Gestion du Classement                           │
├─────────────────────────────────────────────────┤
│  [▼ ECM - ECA European ...]  INT CP 🟢ON        │
│  [▼ Tournoi à élimination]                       │
├─────────────────────────────────────────────────┤
│  [📊 Calculé] [📢 Publié]                        │
├─────────────────────────────────────────────────┤
│  Calcul: 02/12/2025 (Laurent Garigue)            │
│  ☑ Matchs non verrouillés  [Recalculer]         │
├─────────────────────────────────────────────────┤
│  AdminCardList (cartes empilées)                 │
│  ┌─ ESP Men ────────────────────────────┐        │
│  │  🇪🇸 Clt: 1 │ J: 9                   │        │
│  └──────────────────────────────────────┘        │
│  ┌─ ITA Men ────────────────────────────┐        │
│  │  🇮🇹 Clt: 2 │ J: 9                   │        │
│  └──────────────────────────────────────┘        │
└─────────────────────────────────────────────────┘
```

### 4.3 Sélecteur de compétition

**Composant** : `<AdminCompetitionSingleSelect />` (partagé avec Équipes et Documents)

Le sélecteur :
- Affiche les compétitions disponibles depuis le contexte de travail (`workContext.competitionCodes`)
- Auto-sélectionne la première compétition si aucune sélection
- Persiste la sélection en localStorage (`kpi_admin_work_page_competition`)
- La sélection est partagée entre les pages Équipes, Documents et Classements

Affiche des badges à droite :
- Badge niveau (INT/NAT/REG) coloré
- Badge type (CHPT/CP/MULTI)
- Badge statut (ATT/ON/END) cliquable (profil ≤ 3)

### 4.4 Sélecteur du type de classement

**Visible** : Profil ≤ 3 uniquement (les autres voient le type par défaut de la compétition)

- Dropdown avec les 3 types : Championnat, Tournoi à élimination, Multi-Compétition
- Le type par défaut est celui de la compétition (`Code_typeclt` : CHPT, CP, MULTI)
- Changer le type modifie l'affichage mais pas la compétition elle-même
- Affiche également le goal-average (général ou particulier) en lecture seule

### 4.5 Colonnes du classement général

#### CHPT (Championnat)

| Colonne | Description | Éditable inline | Profil édition |
|---------|-------------|-----------------|----------------|
| ☐ | Checkbox sélection | - | ≤ 4 |
| 🏳️ | Drapeau pays (INT uniquement) | - | - |
| Clt | Classement | Oui (statut ON) | ≤ 4 |
| Équipe | Libellé | - | - |
| Pts | Points (÷100) | Oui (statut ON) | ≤ 4 |
| J | Matchs joués | Oui (statut ON) | ≤ 4 |
| G | Victoires | Oui (statut ON) | ≤ 4 |
| N | Nuls | Oui (statut ON) | ≤ 4 |
| P | Défaites | Oui (statut ON) | ≤ 4 |
| F | Forfaits | Oui (statut ON) | ≤ 4 |
| + | Buts pour | Oui (statut ON) | ≤ 4 |
| - | Buts contre | Oui (statut ON) | ≤ 4 |
| Diff | Différence | Oui (statut ON) | ≤ 4 |

#### CP (Coupe/Tournoi)

| Colonne | Description | Éditable inline |
|---------|-------------|-----------------|
| ☐ | Checkbox sélection | - |
| 🏳️ | Drapeau pays (INT uniquement) | - |
| Clt | Classement (CltNiveau) | Oui (statut ON, profil ≤ 4) |
| Équipe | Libellé | - |
| J | Matchs joués | Oui (statut ON, profil ≤ 4) |

**Note** : En CP, le classement général n'affiche pas les colonnes Pts/G/N/P/F/+/-/Diff. Ces colonnes apparaissent dans le déroulement par phase.

#### MULTI

| Colonne | Description | Éditable inline |
|---------|-------------|-----------------|
| ☐ | Checkbox sélection | - |
| 🏳️ | Drapeau pays (INT uniquement) | - |
| Clt | Classement | Oui (statut ON, profil ≤ 4) |
| Structure | Libellé (équipe/club/CD/CR/nation selon `ranking_structure_type`) | - |
| Pts | Points (÷100) | Oui (statut ON, profil ≤ 4) |
| J | Participations | Oui (statut ON, profil ≤ 4) |

### 4.6 Colonnes du déroulement par phase (CP uniquement)

#### Phase de type C (Classement/Poule)

En-tête de phase avec :
- Checkbox consolidation (profil ≤ 4, statut ON)
- Label "Phase consolidée"
- Nom de la phase + lieu

| Colonne | Description | Éditable inline |
|---------|-------------|-----------------|
| 🗑️ | Supprimer de la phase (si J=0) | Clic (profil ≤ 4) |
| Clt | Classement dans la phase | Oui (sauf si consolidé) |
| Équipe | Libellé | - |
| Pts | Points (÷100) | Oui (sauf si consolidé) |
| J | Matchs joués | - |
| G | Victoires | - |
| N | Nuls | - |
| P | Défaites | - |
| F | Forfaits | - |
| + | Buts pour | Oui (sauf si consolidé) |
| - | Buts contre | Oui (sauf si consolidé) |
| Diff | Différence | Oui (sauf si consolidé) |

**Champs éditables inline** : Clt, Pts, +, -, Diff uniquement (pas G/N/P/F/J)

#### Phase de type E (Élimination)

| Colonne | Description |
|---------|-------------|
| Résultat | "Vainqueur" (gras) / "Perdant" (italique) / liste simple |
| Équipe | Libellé |

Si G > 0 → Vainqueur (gras). Si P > 0 → Perdant (italique). Sinon → simple participant avec option de suppression.

### 4.7 Indicateurs qualifiés / éliminés

- **Qualifiés** : Les N premières équipes (N = `kp_competition.Qualifies`) affichent ▲ vert
- **Éliminés** : Les M dernières équipes (M = `kp_competition.Elimines`) affichent ▼ rouge
- Applicable au classement général (calculé et publié)

---

## 5. Consolidation des phases (CP uniquement)

### 5.1 Comportement

- Chaque phase de type C dans le déroulement peut être consolidée/déconsolidée
- **Phase consolidée** :
  - Checkbox cochée + libellé "Phase consolidée"
  - Champs de classement en lecture seule (pas d'édition inline)
  - Lors du recalcul : données **non réinitialisées**, matchs **non pris en compte**
- **Phase non consolidée** :
  - Checkbox décochée
  - Champs de classement éditables inline
  - Lors du recalcul : données réinitialisées et recalculées

### 5.2 Droits

- Profil ≤ 4 : Peut consolider/déconsolider
- Profil > 4 : Voit la checkbox désactivée (cochée si consolidé)
- Statut compétition doit être `ON` pour modifier la consolidation

### 5.3 Interaction

1. Clic sur la checkbox → appel API PATCH
2. Mise à jour immédiate de l'interface (pas de rechargement complet)
3. Les champs de la phase passent en lecture seule / éditables

---

## 6. Calcul du classement (algorithme)

### 6.1 Étapes du calcul

1. **RAZ** : Remise à zéro de toutes les valeurs calculées (`kp_competition_equipe`, `kp_competition_equipe_niveau`, `kp_competition_equipe_journee` — sauf phases consolidées)
2. **Initialisation** : Copie des valeurs initiales depuis `kp_competition_equipe_init` (Pts × 100)
3. **Traitement des matchs** : Pour chaque match (`kp_match`) validé (`Validation = 'O'` ou tous si checkbox cochée), hors phases consolidées :
   - Calcul des points selon le barème de la compétition
   - Application des coefficients (`CoeffA`, `CoeffB`)
   - Mise à jour cumulée de `kp_competition_equipe`, `kp_competition_equipe_journee`, `kp_competition_equipe_niveau`
4. **Attribution des classements** :
   - CHPT : Tri par Pts DESC, Diff DESC, Plus DESC → attribution Clt séquentiel
   - CP : Tri par PtsNiveau DESC, Diff DESC → attribution CltNiveau séquentiel
   - MULTI : Agrégation par structure puis tri par Pts DESC
5. **Gestion des égalités** : Si goal-average particulier (CHPT), recalcul head-to-head entre équipes à égalité de points
6. **Métadonnées** : Mise à jour `Date_calcul`, `Mode_calcul`, `Code_uti_calcul` sur `kp_competition`

### 6.2 Barème de points

Format : `"V-N-D-F"` (ex : `"4-2-1-0"`) stocké dans `kp_competition.Points`

- Points stockés × 100 dans la base (ex : victoire = 400)
- Points Niveau (CP) : `64^niveau × (4 victoire | 3 nul | 2 défaite | 1 forfait)`

### 6.3 Cas spéciaux

- **Forfait** (score contenant `F`) : Seul le vainqueur reçoit des points
- **Score invalide** (`?`, vide) : Match ignoré pour CHPT, traité pour CP (PtsNiveau)
- **Double forfait** : Les deux équipes reçoient les points de forfait
- **Coefficient** : Si `CoeffA` ou `CoeffB` = 0, remplacé par 1.0

---

## 7. Publication du classement

### 7.1 Publication

1. Copie des champs calculés vers les champs `_publi` dans :
   - `kp_competition_equipe` : 12 colonnes (`Pts_publi`, `Clt_publi`, `J_publi`, etc.)
   - `kp_competition_equipe_journee` : mêmes colonnes par phase
   - `kp_competition_equipe_niveau` : mêmes colonnes par niveau
2. Mise à jour métadonnées : `Date_publication`, `Date_publication_calcul`, `Code_uti_publication`, `Mode_publication_calcul`

### 7.2 Dé-publication

1. RAZ des champs `_publi` dans `kp_competition_equipe` (`Clt_publi = 0`, `CltNiveau_publi = 0`)
2. Suppression de toutes les lignes `kp_competition_equipe_journee` (champs publi)
3. Suppression de toutes les lignes `kp_competition_equipe_niveau` (champs publi)
4. RAZ des métadonnées de publication

---

## 8. Transfert d'équipes

### 8.1 Fonctionnement

1. L'utilisateur coche les équipes à transférer dans le tableau de classement
2. Sélectionne la saison et la compétition de destination
3. Clic sur "Affecter les équipes cochées"
4. Confirmation requise

### 8.2 Logique de transfert

- Crée les équipes dans la compétition destination (si pas déjà présentes par `Numero`)
- Copie les joueurs (`kp_competition_equipe_joueur`) avec recalcul des catégories d'âge
- Enregistre le lien `Id_dupli` entre l'ancienne et la nouvelle équipe
- Journalisation de l'opération

### 8.3 Validations

- Compétition source ≠ compétition destination
- Saison destination renseignée
- Au moins une équipe sélectionnée

---

## 9. Endpoints API2

### 9.1 Classement - Lecture

```
GET /admin/rankings
```

**Query Parameters** :
- `season` (required) : Code saison
- `competition` (required) : Code compétition
- `type` (optional) : Type de classement forcé (`CHPT`, `CP`, `MULTI`). Par défaut : type de la compétition

**Profil** : ≤ 10

**Réponse** :
```json
{
  "competition": {
    "code": "ECM",
    "codeSaison": "2025",
    "libelle": "ECA European Championships Men",
    "codeTypeclt": "CP",
    "codeNiveau": "INT",
    "statut": "ON",
    "qualifies": 3,
    "elimines": 0,
    "points": "4-2-1-0",
    "goalaverage": "gen",
    "rankingStructureType": "team",
    "dateCalcul": "2025-12-02T04:49:00+00:00",
    "modeCalcul": "tous",
    "codeUtiCalcul": "ADMIN",
    "userNameCalcul": "Laurent Garigue",
    "datePublication": "2025-12-02T05:57:00+00:00",
    "datePublicationCalcul": "2025-12-02T04:49:00+00:00",
    "codeUtiPublication": "ADMIN",
    "userNamePublication": "Laurent Garigue",
    "modePublicationCalcul": "tous"
  },
  "types": [
    { "code": "CHPT", "label": "Championnat", "selected": false },
    { "code": "CP", "label": "Tournoi à élimination", "selected": true },
    { "code": "MULTI", "label": "Multi-Compétition", "selected": false }
  ],
  "ranking": [
    {
      "id": 1234,
      "libelle": "ESP Men",
      "codeClub": "ESP",
      "codeComiteDep": "ESP",
      "clt": 1,
      "pts": 900,
      "j": 9, "g": 7, "n": 1, "p": 1, "f": 0,
      "plus": 53, "moins": 24, "diff": 29,
      "ptsNiveau": 1234.5,
      "cltNiveau": 1,
      "cltPubli": 1,
      "ptsPubli": 900,
      "jPubli": 9, "gPubli": 7, "nPubli": 1, "pPubli": 1, "fPubli": 0,
      "plusPubli": 53, "moinsPubli": 24, "diffPubli": 29,
      "ptsNiveauPubli": 1234.5,
      "cltNiveauPubli": 1
    }
  ],
  "phases": [
    {
      "idJournee": 456,
      "phase": "Final",
      "lieu": "Enschede",
      "type": "C",
      "niveau": 6,
      "consolidation": true,
      "teams": [
        {
          "id": 1234,
          "libelle": "ESP Men",
          "clt": 1, "pts": 900,
          "j": 3, "g": 3, "n": 0, "p": 0, "f": 0,
          "plus": 13, "moins": 5, "diff": 8,
          "cltPubli": 1, "ptsPubli": 900,
          "jPubli": 3, "gPubli": 3, "nPubli": 0, "pPubli": 0, "fPubli": 0,
          "plusPubli": 13, "moinsPubli": 5, "diffPubli": 8
        }
      ]
    },
    {
      "idJournee": 457,
      "phase": "Semi-final",
      "lieu": "Enschede",
      "type": "E",
      "niveau": 5,
      "consolidation": false,
      "teams": [
        {
          "id": 1234,
          "libelle": "ESP Men",
          "j": 1, "g": 1, "p": 0
        }
      ]
    }
  ]
}
```

### 9.2 Recalculer le classement

```
POST /admin/rankings/compute
```

**Profil** : ≤ 6 (ou profil 9)

**Body** :
```json
{
  "season": "2025",
  "competition": "ECM",
  "includeUnlocked": true
}
```

**Réponse** : `200 OK` avec le classement recalculé (même format que GET)

**Validations** :
- Compétition au statut `ON` obligatoire (sinon 403)

### 9.3 Publier le classement

```
POST /admin/rankings/publish
```

**Profil** : ≤ 4

**Body** :
```json
{
  "season": "2025",
  "competition": "ECM"
}
```

**Réponse** : `200 OK`

**Validations** :
- Compétition au statut `ON` obligatoire (sinon 403)

### 9.4 Supprimer le classement publié

```
DELETE /admin/rankings/publish
```

**Profil** : ≤ 3

**Body** :
```json
{
  "season": "2025",
  "competition": "ECM"
}
```

**Réponse** : `204 No Content`

**Validations** :
- Compétition au statut `ON` obligatoire (sinon 403)

### 9.5 Modifier une valeur inline

```
PATCH /admin/rankings/{teamId}/inline
```

**Profil** : ≤ 4

**Body** :
```json
{
  "field": "Pts",
  "value": 900,
  "journeeId": null
}
```

- Si `journeeId` est fourni, modifie `kp_competition_equipe_journee` ; sinon `kp_competition_equipe`
- `field` : `Clt`, `Pts`, `J`, `G`, `N`, `P`, `F`, `Plus`, `Moins`, `Diff`, `CltNiveau`, `PtsNiveau`
- `Pts` est envoyé × 100 (le frontend multiplie)

**Réponse** : `200 OK`

**Validations** :
- Compétition au statut `ON` obligatoire
- Phase non consolidée (si `journeeId`)

### 9.6 Consolider / Déconsolider une phase

```
PATCH /admin/rankings/consolidation/{journeeId}
```

**Profil** : ≤ 4

**Body** :
```json
{
  "consolidation": true
}
```

**Réponse** : `200 OK`

**Validations** :
- Compétition au statut `ON` obligatoire

### 9.7 Supprimer une équipe d'une phase

```
DELETE /admin/rankings/phase-team/{journeeId}/{teamId}
```

**Profil** : ≤ 4

**Réponse** : `204 No Content`

**Validations** :
- L'équipe ne doit avoir aucun match joué dans cette phase (`J = 0`)
- Compétition au statut `ON` obligatoire

### 9.8 Transférer des équipes

```
POST /admin/rankings/transfer
```

**Profil** : ≤ 4

**Body** :
```json
{
  "teamIds": [1234, 5678],
  "targetSeason": "2026",
  "targetCompetition": "ECM"
}
```

**Réponse** :
```json
{
  "transferred": 2,
  "skipped": 0,
  "details": [
    { "teamId": 1234, "libelle": "ESP Men", "status": "created", "newId": 9876 },
    { "teamId": 5678, "libelle": "ITA Men", "status": "created", "newId": 9877 }
  ]
}
```

**Validations** :
- Compétition source ≠ compétition destination
- Saison et compétition destination valides
- Au moins une équipe sélectionnée

### 9.9 Compétitions de destination (pour le transfert)

```
GET /admin/rankings/transfer-competitions
```

**Query Parameters** :
- `season` (required) : Code saison destination

**Profil** : ≤ 4

**Réponse** :
```json
[
  { "code": "ECM", "libelle": "ECA European Championships Men" },
  { "code": "N1H", "libelle": "Nationale 1 Hommes" }
]
```

---

## 10. Schéma Base de Données

### 10.1 kp_competition (champs classement)

| Colonne | Type | Description |
|---------|------|-------------|
| `Code_typeclt` | varchar(8) | Type de classement : `CHPT`, `CP`, `MULTI` |
| `Qualifies` | int | Nombre d'équipes qualifiées (indicateur vert) |
| `Elimines` | int | Nombre d'équipes éliminées (indicateur rouge) |
| `Points` | varchar(7) | Barème de points (ex: `4-2-1-0`) |
| `goalaverage` | varchar(4) | `gen` (général) ou `part` (particulier) |
| `Date_calcul` | datetime | Date du dernier calcul |
| `Mode_calcul` | varchar(4) | `tous` ou `verr` |
| `Code_uti_calcul` | varchar(8) | Auteur du calcul |
| `Date_publication` | datetime | Date de la dernière publication |
| `Date_publication_calcul` | datetime | Date du calcul utilisé pour la publication |
| `Mode_publication_calcul` | varchar(4) | Mode du calcul publié |
| `Code_uti_publication` | varchar(8) | Auteur de la publication |
| `ranking_structure_type` | varchar(10) | Structure MULTI : `team`/`club`/`cd`/`cr`/`nation` |
| `points_grid` | text | Grille de points MULTI (JSON) |
| `multi_competitions` | text | Compétitions sources MULTI (JSON array) |

### 10.2 kp_competition_equipe (champs classement)

| Colonne | Type | Description |
|---------|------|-------------|
| `Clt` | smallint | Classement calculé |
| `Pts` | smallint | Points calculés (× 100) |
| `J`, `G`, `N`, `P`, `F` | smallint | Matchs, Victoires, Nuls, Défaites, Forfaits |
| `Plus`, `Moins`, `Diff` | smallint | Buts pour, contre, différence |
| `PtsNiveau` | double | Points pondérés par niveau (CP) |
| `CltNiveau` | smallint | Classement par niveau (CP) |
| `Clt_publi` ... `CltNiveau_publi` | — | Mêmes colonnes avec suffixe `_publi` pour le classement publié |

### 10.3 kp_competition_equipe_journee

Classement par phase/journée. Même structure que ci-dessus avec clé composite `(Id, Id_journee)`.

### 10.4 kp_competition_equipe_niveau

Classement par niveau. Même structure avec clé composite `(Id, Niveau)`.

### 10.5 kp_competition_equipe_init

| Colonne | Type | Description |
|---------|------|-------------|
| `Id` | int | FK vers `kp_competition_equipe.Id` |
| `Pts` | smallint | Points initiaux (sera × 100 lors du calcul) |
| `Clt` | smallint | Classement initial |
| `J`, `G`, `N`, `P`, `F` | smallint | Valeurs initiales |
| `Plus`, `Moins`, `Diff` | smallint | Valeurs initiales |

### 10.6 kp_journee (champ consolidation)

| Colonne | Type | Description |
|---------|------|-------------|
| `Consolidation` | varchar(1) | `O` = phase consolidée, `NULL` = non consolidée |

---

## 11. Page Classement Initial

### 11.1 Vue d'ensemble

La page Classement Initial permet d'initialiser un classement de départ pour une compétition de type **CHPT** (Championnat). Les valeurs initiales sont ajoutées au calcul du classement général lors du recalcul.

**Route** : `/rankings/initial`

**Accès** :
- Profil ≤ 3 : Modification des valeurs
- Profil ≤ 3 : Accès depuis la page Classement (bouton "Classement initial")

**Page PHP Legacy** : `GestionClassementInit.php`

### 11.2 Fonctionnalités

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Tableau de toutes les équipes avec valeurs initiales | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Édition inline de toutes les valeurs (Clt, Pts, J, G, N, P, F, +, -, Diff) | ≤ 3 | Essentielle | ✅ Conserver |
| 3 | Bouton "Recharger" (rafraîchir les données) | ≤ 3 | Utile | ✅ Conserver |
| 4 | Bouton "Remise à zéro" (RAZ de toutes les valeurs) | ≤ 3 | Essentielle | ✅ Conserver |

### 11.3 Structure de la page

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Header : Classement Initial {codeCompetition}                          │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  [Recharger] [Remise à zéro]                                             │
│                                                                          │
│  ┌────┬──────────────────┬────┬───┬───┬───┬───┬───┬───┬───┬─────┐      │
│  │Clt │ Équipe           │Pts │ J │ G │ N │ P │ F │ + │ - │ +/- │      │
│  ├────┼──────────────────┼────┼───┼───┼───┼───┼───┼───┼───┼─────┤      │
│  │ 0  │ SWE Men          │ 0  │ 0 │ 0 │ 0 │ 0 │ 0 │ 0 │ 0 │  0  │      │
│  │ 0  │ POL Men          │ 0  │ 0 │ 0 │ 0 │ 0 │ 0 │ 0 │ 0 │  0  │      │
│  │ 0  │ BEL Men          │ 0  │ 0 │ 0 │ 0 │ 0 │ 0 │ 0 │ 0 │  0  │      │
│  └────┴──────────────────┴────┴───┴───┴───┴───┴───┴───┴───┴─────┘      │
│                                                                          │
│  Toutes les valeurs sont éditables inline (classe .editable-cell)        │
│  Les Pts sont affichés en valeur réelle (non × 100)                      │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### 11.4 Comportement

- À l'ouverture, crée automatiquement les enregistrements `kp_competition_equipe_init` pour toutes les équipes de la compétition (si absents)
- Tri par : Clt, Pts DESC, Diff DESC
- L'édition inline sauvegarde immédiatement via API
- Les Pts sont stockés en valeur réelle dans `kp_competition_equipe_init` (pas × 100). La multiplication par 100 est faite lors du calcul du classement dans `InitClassementCompetitionEquipe()`
- "Remise à zéro" : Supprime toutes les lignes de `kp_competition_equipe_init` pour la compétition, puis les recrée à zéro

### 11.5 Endpoints API2

#### Lire le classement initial

```
GET /admin/rankings/initial
```

**Query Parameters** :
- `season` (required) : Code saison
- `competition` (required) : Code compétition

**Profil** : ≤ 6

**Réponse** :
```json
{
  "competition": "ECM",
  "season": "2025",
  "teams": [
    {
      "id": 1234,
      "libelle": "SWE Men",
      "clt": 0, "pts": 0,
      "j": 0, "g": 0, "n": 0, "p": 0, "f": 0,
      "plus": 0, "moins": 0, "diff": 0
    }
  ]
}
```

#### Modifier une valeur inline

```
PATCH /admin/rankings/initial/{teamId}
```

**Profil** : ≤ 6

**Body** :
```json
{
  "field": "Pts",
  "value": 5
}
```

- `field` : `Clt`, `Pts`, `J`, `G`, `N`, `P`, `F`, `Plus`, `Moins`, `Diff`
- `value` : valeur numérique entière

**Réponse** : `200 OK`

#### Remise à zéro

```
POST /admin/rankings/initial/reset
```

**Profil** : ≤ 6

**Body** :
```json
{
  "season": "2025",
  "competition": "ECM"
}
```

**Réponse** : `200 OK` avec les données remises à zéro

---

## 12. Types TypeScript

```typescript
// Types pour la page Classement
export interface RankingCompetitionInfo {
  code: string
  codeSaison: string
  libelle: string
  codeTypeclt: 'CHPT' | 'CP' | 'MULTI'
  codeNiveau: 'INT' | 'NAT' | 'REG'
  statut: 'ATT' | 'ON' | 'END'
  qualifies: number
  elimines: number
  points: string                    // ex: "4-2-1-0"
  goalaverage: 'gen' | string       // 'gen' = général, autre = particulier
  rankingStructureType: 'team' | 'club' | 'cd' | 'cr' | 'nation'
  dateCalcul: string | null
  modeCalcul: string | null         // 'tous' | 'verr' | null
  codeUtiCalcul: string
  userNameCalcul: string
  datePublication: string | null
  datePublicationCalcul: string | null
  codeUtiPublication: string
  userNamePublication: string
  modePublicationCalcul: string | null
}

export interface RankingType {
  code: 'CHPT' | 'CP' | 'MULTI'
  label: string
  selected: boolean
}

export interface RankingTeam {
  id: number
  libelle: string
  codeClub: string
  codeComiteDep: string             // Pour drapeau pays (INT)
  // Classement calculé
  clt: number
  pts: number                       // × 100
  j: number
  g: number
  n: number
  p: number
  f: number
  plus: number
  moins: number
  diff: number
  ptsNiveau: number
  cltNiveau: number
  // Classement publié
  cltPubli: number
  ptsPubli: number
  jPubli: number
  gPubli: number
  nPubli: number
  pPubli: number
  fPubli: number
  plusPubli: number
  moinsPubli: number
  diffPubli: number
  ptsNiveauPubli: number
  cltNiveauPubli: number
}

export interface RankingPhase {
  idJournee: number
  phase: string
  lieu: string
  type: 'C' | 'E'                   // C = classement, E = élimination
  niveau: number
  consolidation: boolean
  teams: RankingPhaseTeam[]
}

export interface RankingPhaseTeam {
  id: number
  libelle: string
  // Calculé
  clt: number
  pts: number
  j: number
  g: number
  n: number
  p: number
  f: number
  plus: number
  moins: number
  diff: number
  // Publié
  cltPubli: number
  ptsPubli: number
  jPubli: number
  gPubli: number
  nPubli: number
  pPubli: number
  fPubli: number
  plusPubli: number
  moinsPubli: number
  diffPubli: number
}

export interface RankingResponse {
  competition: RankingCompetitionInfo
  types: RankingType[]
  ranking: RankingTeam[]
  phases: RankingPhase[]             // Vide si CHPT ou MULTI
}

// Classement initial
export interface InitialRankingTeam {
  id: number
  libelle: string
  clt: number
  pts: number                       // Valeur réelle (non × 100)
  j: number
  g: number
  n: number
  p: number
  f: number
  plus: number
  moins: number
  diff: number
}

// Transfert
export interface TransferRequest {
  teamIds: number[]
  targetSeason: string
  targetCompetition: string
}

export interface TransferResult {
  transferred: number
  skipped: number
  details: {
    teamId: number
    libelle: string
    status: 'created' | 'skipped'
    newId?: number
  }[]
}
```

---

## 13. Traductions i18n

```json
{
  "rankings": {
    "title": "Gestion du Classement",
    "tabs": {
      "computed": "Classement calculé",
      "published": "Classement publié"
    },
    "type": {
      "label": "Type de classement",
      "CHPT": "Championnat",
      "CP": "Tournoi à élimination",
      "MULTI": "Multi-Compétition"
    },
    "goalaverage": {
      "label": "Goal-average",
      "gen": "Général",
      "part": "Particulier"
    },
    "table": {
      "rank": "Clt",
      "team": "Équipe",
      "club": "Club",
      "cd": "Comité départemental",
      "cr": "Comité régional",
      "nation": "Nation",
      "pts": "Pts",
      "j": "J",
      "g": "G",
      "n": "N",
      "p": "P",
      "f": "F",
      "plus": "+",
      "moins": "-",
      "diff": "Diff"
    },
    "qualified": "Qualifié",
    "eliminated": "Éliminé",
    "winner": "Vainqueur",
    "loser": "Perdant",
    "compute": {
      "title": "Calcul du classement",
      "date": "Calcul",
      "by": "par",
      "include_unlocked": "Inclure les matchs non verrouillés",
      "button": "Recalculer",
      "not_computed": "Classement non calculé",
      "manual": "Classement manuel"
    },
    "publish": {
      "title": "Classement public",
      "date_compute": "Calcul",
      "date_publish": "Publication",
      "button": "Publier le classement",
      "unpublish": "Supprimer le classement publié",
      "not_published": "Classement non publié",
      "different": "Attention : le classement publié est différent du classement calculé"
    },
    "phases": {
      "title": "Déroulement",
      "consolidated": "Phase consolidée",
      "consolidate": "Consolider la phase"
    },
    "transfer": {
      "title": "Affectation - Promotion - Relégation",
      "target_season": "Affecter vers saison",
      "target_competition": "Affecter vers compétition",
      "button": "Affecter les équipes cochées",
      "nothing_selected": "Aucune équipe sélectionnée",
      "select_competition": "Sélectionnez une compétition cible",
      "select_season": "Sélectionnez une saison cible",
      "success": "{count} équipe(s) transférée(s)"
    },
    "pdf": {
      "admin": "Admin",
      "public": "Public",
      "general": "Classement général",
      "progress": "Déroulement",
      "detail": "Détail par équipe",
      "matches": "Matchs"
    },
    "status_restriction": "Les modifications ne sont disponibles que pour les compétitions en cours (statut ON)",
    "initial": {
      "title": "Classement initial",
      "button": "Classement initial...",
      "reload": "Recharger",
      "reset": "Remise à zéro",
      "reset_confirm": "Remettre à zéro toutes les valeurs initiales ?"
    },
    "confirm_publish": "Publier le classement calculé comme classement public ?",
    "confirm_unpublish": "Supprimer le classement public ?",
    "confirm_compute": "Recalculer le classement de la compétition ?"
  }
}
```

---

## 14. Sécurité

### 14.1 Contrôles d'accès

| Action | Profil requis | Statut requis |
|--------|---------------|---------------|
| Consulter le classement | ≤ 10 | — |
| Recalculer | ≤ 6 (ou 9) | ON |
| Modifier inline | ≤ 4 | ON |
| Publier | ≤ 4 | ON |
| Dé-publier | ≤ 3 | ON |
| Consolider/déconsolider | ≤ 4 | ON |
| Transférer des équipes | ≤ 4 | — |
| Changer le statut compétition | ≤ 3 | — |
| Accéder au classement initial | ≤ 3 | — |
| Modifier le classement initial | ≤ 3 | — |
| Changer le type de classement | ≤ 3 | — |

### 14.2 Validations backend

- Vérification du statut compétition (`ON`) avant toute opération de modification
- Vérification que la phase n'est pas consolidée avant modification inline des valeurs de phase
- Vérification que l'équipe n'a pas de matchs joués (`J=0`) avant suppression d'une phase
- Vérification que la compétition source ≠ destination pour les transferts
- Journalisation de toutes les opérations (calcul, publication, dé-publication, transfert)

---

## 15. Notes techniques

### 15.1 Points × 100

Les points de classement sont stockés multipliés par 100 dans la base (`Pts` colonne). L'affichage divise par 100. Cela permet de gérer les demi-points (ex: 2.5 points stockés comme 250) sans problème de virgule flottante. L'édition inline doit multiplier la saisie par 100 avant envoi à l'API.

### 15.2 Drapeaux pays

Pour les compétitions internationales (`Code_niveau = 'INT'`), afficher le drapeau du pays. Le code pays est dans `kp_club.Code_comite_dep` qui correspond au code CIO (ISO 3166-1 alpha-3) pour les équipes internationales (kp_cr.Code = '98').

### 15.3 Liens PDF legacy

Les liens PDF pointent vers les pages PHP legacy. Ils s'ouvrent dans un nouvel onglet (`target="_blank"`). La session PHP est partagée via le cookie, donc pas d'authentification supplémentaire nécessaire.

### 15.4 Édition inline

Utiliser le pattern `.editable-cell` défini dans `assets/css/admin.css` pour les cellules modifiables. Au clic, afficher un input numérique (`type="tel"`, `maxlength="3"`). Validation par Enter ou blur. Annulation par Escape.

### 15.5 Persistance du mode calcul

La checkbox "Inclure les matchs non verrouillés" doit être persistée en localStorage (clé `kpi_admin_ranking_include_unlocked`).

---

**Document créé le** : 2026-02-21
**Dernière mise à jour** : 2026-02-21
**Statut** : 📋 À implémenter (backend API2 + frontend App4)
