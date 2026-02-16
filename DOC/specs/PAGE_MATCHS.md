# Spécification - Page Gestion des Matchs

## Statut : 📋 À IMPLÉMENTER

## 1. Vue d'ensemble

Page d'administration des matchs d'une compétition/saison. La page permet de lister, filtrer, créer, modifier et supprimer des matchs, avec édition inline de la plupart des champs, toggles d'état (publication, verrouillage, type, statut live, imprimé), et des opérations en masse avancées (affectation automatique, renumérotation, changement d'heure, etc.).

C'est la page la plus complexe de l'administration, avec 4 modes de rendu selon le profil utilisateur et l'état de validation des matchs.

**Route** : `/games`

**Accès** :
- Profil ≤ 10 : Lecture seule (consultation de la liste, liens PDF)
- Profil ≤ 9 : Saisie des scores uniquement (édition inline scores A/B)
- Profil ≤ 6 : Édition complète (inline, toggles, formulaire, actions en masse)
- Profil ≤ 4 : Verrouillage (toggle Validation)
- Profil ≤ 2 : Renumérotation, changement de date/heure/groupe en masse
- Profil = 1 : Feuille de marque V3

**Page PHP Legacy** : `GestionJournee.php` + `GestionJournee.tpl` + `GestionJournee.js`

**Implémentation Nuxt** : `sources/app4/pages/games/index.vue`

**Contexte de travail** : Utilise le `workContextStore` global (saison + périmètre compétitions) + filtres locaux (événement, compétition, tour, journée, date, terrain, tri, matchs non verrouillés)

**Accès depuis** : Page Journées (`/gamedays`) via le lien "Matchs" par journée, ou directement via le menu

---

## 2. Fonctionnalités

### 2.1 Fonctionnalités de la liste

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Liste des matchs avec filtres (événement, compétition, tour, journée, date, terrain, tri) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Filtre par événement (dropdown) | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Filtre par compétition (dropdown groupé par section, option "Toutes") | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | Filtre par tour/étape (1-5) | ≤ 10 | Essentielle | ✅ Conserver |
| 5 | Filtre par journée/phase/poule (dropdown dynamique selon compétition) | ≤ 10 | Essentielle | ✅ Conserver |
| 6 | Filtre par date (dropdown des dates distinctes des matchs) | ≤ 10 | Essentielle | ✅ Conserver |
| 7 | Filtre par terrain (1-8) | ≤ 10 | Essentielle | ✅ Conserver |
| 8 | Tri configurable (date/heure/terrain, compétition+date, compétition+phase, terrain+date, numéro) | ≤ 10 | Essentielle | ✅ Conserver |
| 9 | Checkbox "Matchs non verrouillés" (masque les matchs validés) | ≤ 10 | Essentielle | ✅ Conserver |
| 10 | Toggle publication (œil) par clic AJAX | ≤ 6 | Essentielle | ✅ Conserver |
| 11 | Toggle verrouillage/validation (cadenas) par clic AJAX | ≤ 4 | Essentielle | ✅ Conserver |
| 12 | Toggle type C/E (classement/élimination) par clic AJAX | ≤ 6 | Essentielle | ✅ Conserver |
| 13 | Toggle statut live (ATT→ON→END→ATT) par clic AJAX | ≤ 6 | Essentielle | ✅ Conserver |
| 14 | Toggle imprimé (O/N) par clic AJAX | ≤ 6 | Essentielle | ✅ Conserver |
| 15 | Édition inline : numéro, date, heure, terrain, code/libellé, scores, équipes, arbitres, phase/journée | ≤ 6 | Essentielle | ✅ Conserver |
| 16 | Colonnes conditionnelles : Phase+Code si `PhaseLibelle=1`, sinon Code+Lieu | ≤ 10 | Essentielle | ✅ Conserver |
| 17 | Affichage catégorie (Soustitre2 ou Code_competition) | ≤ 10 | Essentielle | ✅ Conserver |
| 18 | Score provisoire affiché quand statut ON ou END | ≤ 10 | Essentielle | ✅ Conserver |
| 19 | Coefficients A/B affichés si différents de 1/1 | ≤ 10 | Essentielle | ✅ Conserver |
| 20 | Lien composition équipe A/B par match | ≤ 10 | Essentielle | ✅ Conserver (lien vers page dédiée) |
| 21 | Lien édition match (formulaire via ParamMatch) | ≤ 6 | Essentielle | ✅ Conserver (modal) |
| 22 | Lien feuille de marque PDF individuel | ≤ 10 | Essentielle | ✅ Conserver (lien legacy) |
| 23 | Lien feuille de marque en ligne (V2) | ≤ 10 | Essentielle | ✅ Conserver (lien legacy) |
| 24 | Lien feuille de marque en ligne (V3) | = 1 | Spécifique | ✅ Conserver (lien legacy) |
| 25 | Suppression individuelle (icône corbeille) | ≤ 6 | Essentielle | ✅ Conserver |
| 26 | Sélection multiple (checkboxes) | ≤ 6 | Essentielle | ✅ Conserver |
| 27 | Compteur de matchs | ≤ 10 | Essentielle | ✅ Conserver |
| 28 | Surlignage/recherche textuelle dans le tableau | ≤ 10 | Utile | ✅ Conserver (intégré au champ recherche) |
| 29 | Surlignage 2ème couleur (profil ≤ 2) | ≤ 2 | Spécifique | ⏳ Différé |
| 30 | Verrouillage désactive l'édition inline de la ligne | ≤ 6 | Essentielle | ✅ Conserver |
| 31 | Classe `undefTeam` pour équipes non définies (Id < 1) | ≤ 10 | Essentielle | ✅ Conserver |
| 32 | Classe `pbArb` pour arbitres non identifiés (matricule = 0) | ≤ 10 | Essentielle | ✅ Conserver |

### 2.2 Fonctionnalités du formulaire (Ajout/Édition de match)

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 40 | Sélection journée/phase (obligatoire) | ≤ 6 | Essentielle | ✅ Conserver |
| 41 | Date du match (date picker) | ≤ 6 | Essentielle | ✅ Conserver |
| 42 | Heure du match (time picker HH:MM) | ≤ 6 | Essentielle | ✅ Conserver |
| 43 | Numéro du match | ≤ 6 | Essentielle | ✅ Conserver |
| 44 | Terrain | ≤ 6 | Essentielle | ✅ Conserver |
| 45 | Type C/E (toggle classement/élimination) | ≤ 6 | Essentielle | ✅ Conserver |
| 46 | Intervalle match (minutes, pour calcul automatique des horaires) | ≤ 6 | Essentielle | ✅ Conserver |
| 47 | Intitulé/codage (libellé avec notation bracket `[T1-T2-ARB1-ARB2]`) | ≤ 6 | Essentielle | ✅ Conserver |
| 48 | Équipe A (dropdown chargé dynamiquement selon journée) | ≤ 6 | Essentielle | ✅ Conserver |
| 49 | Équipe B (dropdown chargé dynamiquement selon journée) | ≤ 6 | Essentielle | ✅ Conserver |
| 50 | Coefficient A (défaut: 1) | ≤ 6 | Essentielle | ✅ Conserver |
| 51 | Coefficient B (défaut: 1) | ≤ 6 | Essentielle | ✅ Conserver |
| 52 | Arbitre 1 - principal (autocomplete + dropdown sélection rapide + dropdown équipe) | ≤ 6 | Essentielle | ✅ Conserver |
| 53 | Arbitre 2 - secondaire (autocomplete + dropdown sélection rapide + dropdown équipe) | ≤ 6 | Essentielle | ✅ Conserver |
| 54 | Bouton "Init Titulaires" compétition (remplit toutes les compositions d'équipe) | ≤ 6 | Essentielle | ✅ Conserver |
| 55 | Bouton "Init Titulaires" équipe A/B (remplit composition d'une équipe) | ≤ 6 | Essentielle | ✅ Conserver |
| 56 | Bouton "Init Titulaires" journée (remplit toutes les compos de la journée) | ≤ 6 | Essentielle | ✅ Conserver |
| 57 | Type automatique selon journée sélectionnée (hérite le type C/E de la journée) | ≤ 6 | Essentielle | ✅ Conserver |
| 58 | Lien vers pool d'arbitres (GestionEquipeJoueur) | ≤ 6 | Utile | ✅ Conserver (lien) |
| 59 | Validation formulaire : date requise, heure format HH:MM, journée obligatoire | ≤ 6 | Essentielle | ✅ Conserver |

### 2.3 Actions en masse (Sélection)

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 60 | Tout sélectionner / Tout désélectionner | ≤ 6 | Essentielle | ✅ Conserver |
| 61 | Suppression en masse | ≤ 6 | Essentielle | ✅ Conserver |
| 62 | Publication en masse (toggle) | ≤ 6 | Essentielle | ✅ Conserver |
| 63 | Verrouillage + Publication en masse | ≤ 4 | Essentielle | ✅ Conserver |
| 64 | Verrouillage en masse (toggle) | ≤ 4 | Essentielle | ✅ Conserver |
| 65 | Affectation automatique (parsing bracket notation `[T1-T2-ARB1-ARB2]`) | ≤ 6 | Essentielle | ✅ Conserver |
| 66 | Annulation affectation automatique (remet équipes/arbitres à null) | ≤ 6 | Essentielle | ✅ Conserver |
| 67 | Changement de poule/journée (déplace matchs vers autre journée) | ≤ 6 | Essentielle | ✅ Conserver |
| 68 | Renumérotation des matchs (à partir d'un numéro) | ≤ 2 | Essentielle | ✅ Conserver |
| 69 | Changement de date en masse | ≤ 2 | Essentielle | ✅ Conserver |
| 70 | Incrémentation d'heure (heure départ + intervalle) | ≤ 2 | Essentielle | ✅ Conserver |
| 71 | Remplacement de groupe/poule dans les codes bracket | ≤ 2 | Essentielle | ✅ Conserver |
| 72 | Toggle imprimé en masse | ≤ 6 | Essentielle | ✅ Conserver |
| 73 | Feuilles de marque PDF (sélection) | ≤ 10 | Essentielle | ✅ Conserver (lien legacy) |

### 2.4 Documents / Exports (zone "Tous les matchs")

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 80 | Liste des matchs PDF (FR) | ≤ 10 | Essentielle | ✅ Conserver (lien legacy) |
| 81 | Liste des matchs PDF (EN) | ≤ 10 | Essentielle | ✅ Conserver (lien legacy) |
| 82 | Feuilles de marque PDF (tous les matchs) | ≤ 10 | Essentielle | ✅ Conserver (lien legacy) |
| 83 | Export ODS (tableur) | ≤ 10 | Essentielle | ✅ Conserver (lien legacy) |
| 84 | Liste publique des matchs PDF (FR) | ≤ 10 | Essentielle | ✅ Conserver (lien legacy) |
| 85 | Liste publique des matchs PDF (EN) | ≤ 10 | Essentielle | ✅ Conserver (lien legacy) |
| 86 | Listes 4 terrains (Teams/Phases, pitches 1-4 / 5-8) | ≤ 2 | Spécifique | ✅ Conserver (lien legacy) |

### 2.5 Améliorations par rapport au legacy

| # | Amélioration | Description |
|---|--------------|-------------|
| 1 | Modal au lieu de formulaire collapsible | Le formulaire d'ajout/édition sera dans une modal au lieu d'un bloc pliable en haut de page |
| 2 | Recherche textuelle | Remplacement du surlignage par une vraie recherche/filtre côté client |
| 3 | Pagination | Le legacy n'a pas de pagination (peut afficher 200+ matchs) — ajouter pagination 50 par défaut |
| 4 | Actions en masse groupées | Menu dropdown au lieu de ~15 icônes alignées dans un fieldset |
| 5 | Intégration workContext | Utiliser le contexte de travail global pour saison/périmètre compétitions |
| 6 | Responsive mobile | Vue cartes pour mobile (impossible avec le tableau 18+ colonnes) |
| 7 | Toast notifications | Remplace les `alert()` JavaScript par des toasts Nuxt UI |
| 8 | Feedback optimiste | Les toggles changent visuellement immédiatement (rollback si erreur) |
| 9 | Matchs verrouillés mieux distingués | Fond coloré + icônes désactivées clairement au lieu de renommage de classes CSS |
| 10 | Date picker moderne | Composant Nuxt UI au lieu de Flatpickr |

---

## 3. Structure de la Page

### 3.1 Vue Desktop

```
┌──────────────────────────────────────────────────────────────────────────────────────┐
│  [Contexte de travail : Saison 2026 | National 1 Hommes (12 compétitions)]           │
├──────────────────────────────────────────────────────────────────────────────────────┤
│  Gestion des Matchs                                                                   │
├──────────────────────────────────────────────────────────────────────────────────────┤
│  [Événement: ▼ Tous]  [Compétition: ▼ N1H]  [Tour: ▼ Tous]  [Journée: ▼ Poule A]   │
│  [Date: ▼ Toutes]  [Terrain: ▼ Tous]  [Tri: ▼ Date/Heure]  [☐ Non verrouillés]     │
├──────────────────────────────────────────────────────────────────────────────────────┤
│  [🗑 Suppr.(N)] [⚡ Actions ▼]  [🔍 Recherche...        ]         [+ Ajouter]       │
│                   ├─ 👁 Publier                                                       │
│                   ├─ 🔒👁 Verrouiller + Publier                                      │
│                   ├─ 🔒 Verrouiller                                                   │
│                   ├─ 🤖 Affectation auto                                              │
│                   ├─ ↩️ Annuler affectation                                            │
│                   ├─ 🔄 Changer de poule                                              │
│                   ├─ 🔢 Renuméroter                                                   │
│                   ├─ 📅 Changer date                                                   │
│                   ├─ ⏰ Incrémenter heure                                              │
│                   ├─ 🏷 Remplacer groupe                                               │
│                   ├─ 🖨 Toggle imprimé                                                 │
│                   └─ 📄 Feuilles de marque PDF                                         │
├──────────────────────────────────────────────────────────────────────────────────────┤
│  Documents: [📄 Liste FR] [📄 Liste EN] [📄 Feuilles PDF] [📊 ODS] [📄 Pub FR/EN]  │
├──────────────────────────────────────────────────────────────────────────────────────┤
│ ☐│👁│ N° │   │Heure │Cat │Phase│Code │Type│Ter│ Équipe A    │ScA│🔒│ScB│ Équipe B   │
│──│──│────│───│──────│────│─────│─────│────│───│─────────────│───│──│───│────────────│
│ ☐│🟢│ 1  │✏📄│10:00 │N1H │Pl.A │[T1-T2]│≡ │ 1 │ Acigné      │ 5 │🔓│ 3 │ Strasbourg │
│  │  │    │   │      │    │     │      │   │   │ [👥 Compo]  │   │ATT│   │ [👥 Compo] │
│ ☐│🟢│ 2  │✏📄│10:40 │N1H │Pl.A │[T3-T4]│≡ │ 1 │ Fontenay    │   │🔓│   │ Lyon       │
│  │  │    │   │      │    │     │      │   │   │ [👥 Compo]  │   │ATT│   │ [👥 Compo] │
│  ...                                                                                  │
│                        suite des colonnes →                                           │
│  │ Arbitre 1          │ Arbitre 2          │🖨│ 🗑 │                                  │
│  │ DUPONT J. (Club)   │ MARTIN P. (Club)   │🖨O│ 🗑 │                                │
│  │                    │                    │🖨N│ 🗑 │                                  │
├──────────────────────────────────────────────────────────────────────────────────────┤
│  Nb matchs : 24    Pagination: [< 1 2 >]  Afficher: [50 ▼]                          │
└──────────────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Colonnes conditionnelles

**Condition `PhaseLibelle`** : déterminée automatiquement.

| Condition | Colonnes affichées |
|-----------|-------------------|
| `PhaseLibelle = 1` (compétition unique type CP, ou matchs avec Phase+Libelle non vides) | **Phase** + **Code** |
| `PhaseLibelle = 0` (défaut) | **Code** + **Lieu** |

### 3.3 Colonnes du tableau

| Colonne | Largeur | Éditable | Profil requis | Description |
|---------|---------|----------|---------------|-------------|
| ☐ Checkbox | 30px | - | ≤ 6 | Sélection pour actions en masse |
| 👁 Publication | 30px | Toggle | ≤ 6 | Icône œil (O=vert, N=gris) |
| N° | 40px | Inline (tel) | ≤ 6 | Numero_ordre |
| Actions | 80px | - | ≤ 6 | Icônes : ✏ Éditer, 📄 PDF, 📱 Feuille en ligne |
| Heure | 60px | Inline (time) | ≤ 6 | Date_match + Heure_match (date au-dessus, heure en-dessous) |
| Cat. | 40px | - | - | Soustitre2 ou Code_competition (lecture seule) |
| Phase * | 60px | Inline (select journée) | ≤ 6 | Phase de la journée (conditionnel) |
| Code * | 80px | Inline (text) | ≤ 6 | Libellé du match / Code bracket (conditionnel) |
| Lieu * | 60px | - | - | Lieu de la journée (conditionnel, lecture seule) |
| Type | 30px | Toggle | ≤ 6 | C (classement) / E (élimination) |
| Terrain | 30px | Inline (tel) | ≤ 6 | Numéro terrain |
| Équipe A | 120px | Inline (select AJAX) | ≤ 6 | Nom équipe + lien composition |
| Score A | 30px | Inline (tel) | ≤ 9 | Score équipe A |
| 🔒 Verrou | 30px | Toggle | ≤ 4 | Validation O/N + Statut (ATT/ON/END) + Score provisoire |
| Score B | 30px | Inline (tel) | ≤ 9 | Score équipe B |
| Équipe B | 120px | Inline (select AJAX) | ≤ 6 | Nom équipe + lien composition |
| Arbitre 1 | 100px | Inline (autocomplete) | ≤ 6 | Arbitre principal |
| Arbitre 2 | 100px | Inline (autocomplete) | ≤ 6 | Arbitre secondaire |
| 🖨 Imprimé | 30px | Toggle | ≤ 6 | Flag imprimé O/N + coefficients si ≠ 1/1 |
| 🗑 Suppr. | 30px | - | ≤ 6 | Suppression individuelle |

\* Colonnes conditionnelles selon `PhaseLibelle`.

### 3.4 Vue Mobile (cartes)

```
┌──────────────────────────────────┐
│  Gestion des Matchs               │
├──────────────────────────────────┤
│  [Événement: ▼]  [Compétition: ▼]│
│  [Journée: ▼]  [Date: ▼]         │
│  [Tri: ▼]  [+ Ajouter]           │
│  [🔍 Recherche...              ] │
├──────────────────────────────────┤
│  ┌────────────────────────────┐  │
│  │ Match #1 — N1H Poule A    │  │
│  │ 📅 11/01/2026 ⏰ 10:00    │  │
│  │ 🏟 Terrain 1  │ Type: ≡   │  │
│  │ [T1-T2]                   │  │
│  │ Acigné     5 - 3  Strasb. │  │
│  │ 🔒 Verrouillé │ 👁 Publié │  │
│  │ Arb: DUPONT / MARTIN      │  │
│  │ [✏ Éditer] [📄 PDF] [🗑]  │  │
│  └────────────────────────────┘  │
│  ...                              │
└──────────────────────────────────┘
```

---

## 4. Filtres

### 4.1 Filtre Événement

| Propriété | Valeur |
|-----------|--------|
| Source | `kp_evenement` ORDER BY Date_debut DESC, Libelle |
| Option par défaut | "* - Tous les événements" (valeur: -1) |
| Format | "{Id} - {Libelle}" |
| Persistance | localStorage |
| Impact | Filtre les journées via `kp_evenement_journee.Id_evenement` |

### 4.2 Filtre Compétition (dropdown groupé par section)

| Propriété | Valeur |
|-----------|--------|
| Type | Select simple (une seule compétition) |
| Source | `kp_competition` JOIN `kp_groupe` pour regroupement par section |
| Groupes | `<optgroup>` par section (Nationaux, Coupes, etc.) |
| Option par défaut | "* - Toutes les compétitions de l'événement" |
| Persistance | localStorage |
| Impact | Filtre les journées par `Code_competition` ; détermine `PhaseLibelle` |

**Construction des groupes :**
```sql
SELECT DISTINCT c.GroupOrder, c.Code, c.Libelle, c.Soustitre, c.Soustitre2,
       c.Titre_actif, g.id, g.section, g.ordre
FROM kp_competition c, kp_groupe g
WHERE c.Code_saison = ?
  AND c.Code_ref = g.Groupe
  AND c.Code_niveau LIKE ?
ORDER BY g.section, g.ordre, c.Code_tour, c.GroupOrder, c.Code
```

**Note** : Lorsqu'une compétition spécifique est sélectionnée, un bouton "Init Titulaires compétition" est disponible (profil ≤ 6) pour initialiser les compositions d'équipe de tous les matchs.

### 4.3 Filtre Tour/Étape

| Propriété | Valeur |
|-----------|--------|
| Options | Tous (vide), Tour 1, Tour 2, Tour 3, Tour 4, Tour 5 |
| Impact | `WHERE d.Etape = ?` sur les journées |
| Persistance | localStorage |

### 4.4 Filtre Journée/Phase/Poule

| Propriété | Valeur |
|-----------|--------|
| Source | Journées autorisées pour l'utilisateur, filtrées par compétition/événement |
| Format CP | "[{Id}] {Code_competition} ({Etape}) {Phase}" |
| Format autre | "[{Id}] {Code_competition} {Date_debut} {Lieu}" |
| Option par défaut | "--- Tous ---" (valeur: *) |
| Persistance | localStorage |
| Impact | Sélectionne une journée spécifique ou toutes |

**Note** : Ce filtre sert aussi de sélecteur pour le formulaire d'ajout (la journée sélectionnée dans ce filtre est utilisée pour la création de match).

### 4.5 Filtre Date

| Propriété | Valeur |
|-----------|--------|
| Source | Dates distinctes des matchs affichés |
| Format | Date formatée (FR: dd/mm/yyyy, EN: yyyy-mm-dd) |
| Option par défaut | "--- Tous ---" |
| Persistance | localStorage |

### 4.6 Filtre Terrain

| Propriété | Valeur |
|-----------|--------|
| Options | Tous (vide), Terrain 1 ... Terrain 8 |
| Impact | `WHERE a.Terrain = ?` |
| Persistance | localStorage |

### 4.7 Tri

| Option | Clé SQL | Description |
|--------|---------|-------------|
| Par date/heure/terrain | `a.Date_match, a.Heure_match, a.Terrain, a.Numero_ordre` | Défaut |
| Par compétition et date | `d.Code_competition, a.Date_match, a.Heure_match, a.Terrain, a.Numero_ordre` | |
| Par compétition et phase | `d.Code_competition, d.Niveau, d.Phase, a.Heure_match, a.Terrain, a.Numero_ordre` | |
| Par terrain et date | `a.Terrain, a.Date_match, a.Heure_match, a.Numero_ordre` | |
| Par numéro | `a.Numero_ordre, a.Date_match, a.Heure_match, a.Terrain` | |

### 4.8 Checkbox Matchs non verrouillés

| Propriété | Valeur |
|-----------|--------|
| Type | Checkbox |
| Comportement | Si coché, masque côté client les lignes dont `Validation = 'O'` |
| Persistance | Session/localStorage |
| Feedback | Le label est surligné quand actif |

### 4.9 Intégration avec le contexte de travail

**Décision** : Utiliser le `workContextStore` global pour la saison. Les filtres compétition, journée, tour, date, terrain sont tous **locaux** à la page car ils sont très spécifiques aux matchs (cascade événement→compétition→tour→journée→date→terrain).

---

## 5. Modes de rendu

Le rendu de chaque ligne de match dépend de deux critères : le **profil utilisateur** et l'**état de validation** du match.

### 5.1 Mode Éditable (profil ≤ 6, Validation ≠ 'O')

Mode complet avec :
- Tous les champs inline éditables (classe `directInput`)
- Tous les toggles actifs (publication, type, statut, imprimé, verrouillage)
- Icônes d'action complètes (éditer, PDF, feuille en ligne, supprimer)
- Lien composition équipe A/B

### 5.2 Mode Verrouillé (profil ≤ 6, Validation = 'O')

Mode lecture avec possibilité de déverrouiller :
- Champs inline désactivés (classe `directInputOff`)
- Toggle verrouillage actif (pour déverrouiller)
- Toggles type/statut/imprimé désactivés (classe `*Off`)
- Icônes d'action : éditer (mais pas de suppression), PDF, feuille en ligne
- Actions "showOff" au lieu de "showOn"

**Comportement dynamique** : Quand un match est verrouillé/déverrouillé par toggle, les classes CSS changent en temps réel (`directInput` ↔ `directInputOff`, `typeMatch` ↔ `typeMatchOff`, etc.)

### 5.3 Mode Scoreur (profil = 9, Validation ≠ 'O')

Mode saisie scores uniquement :
- Seuls Score A et Score B sont éditables en inline
- Publication, type, statut, imprimé : lecture seule
- Pas de suppression
- Lien PDF et feuille en ligne disponibles
- Lien composition équipe A/B disponible

### 5.4 Mode Lecture seule (profil > 9, ou profil 9 + Validation = 'O')

Mode consultation :
- Aucun champ éditable
- Pas de checkbox de sélection
- Publication affichée mais non cliquable
- Liens PDF disponibles
- Lien composition équipe A/B disponible

---

## 6. Édition inline

### 6.1 Types de cellules éditables

| Type | Classe CSS | Input généré | Taille | Sauvegarde | Endpoint |
|------|-----------|-------------|--------|------------|----------|
| **Texte** (Libelle) | `directInput text` | `<input type="text" size="12">` | 12 chars | Blur | `UpdateCellJQ` → inline API |
| **Numéro match** | `directInput numMatch` | `<input type="tel" size="1" maxlength="4">` | 4 digits | Blur | inline API |
| **Date** (FR) | `directInput date` | `<input type="text">` + Flatpickr `d/m/Y` | 8 chars | Flatpickr onClose | inline API |
| **Date** (EN) | `directInput dateEN` | `<input type="text">` + Flatpickr `Y-m-d` | 8 chars | Flatpickr onClose | inline API |
| **Heure** | `directInput heure` | `<input type="text">` + Flatpickr time `H:i` | 4 chars | Flatpickr onClose | inline API |
| **Terrain** | `directInput terrain` | `<input type="tel" size="2" maxlength="2">` | 2 digits | Blur | inline API |
| **Score** | `directInput score` | `<input type="tel" size="2" maxlength="2">` | 2 digits | Blur | inline API |
| **Équipe** | `directInput equipe` | `<select>` chargé AJAX + bouton Annuler | - | Blur select | setEquipesMatch API |
| **Phase/Journée** | `directInput phase` | `<select>` rempli depuis les journées + bouton Annuler | - | Blur select | setPhaseMatch API |
| **Arbitre** | `directInput arbitre` | `<input type="text">` + autocomplete + 3 boutons (Valider/Annuler/Vider) | 22 chars | Bouton Valider | saveArbitres API |

### 6.2 Comportement général

1. **Clic/Focus** sur un `<span class="directInput">` → le span est masqué, un input est créé juste avant
2. L'input est auto-sélectionné (`.select()`)
3. **Sauvegarde** : Blur (pour text/tel/select) ou Flatpickr onClose (pour date/heure)
4. Si la valeur a changé → appel AJAX vers `UpdateCellJQ.php` (legacy) / API2 inline endpoint
5. Le span est réaffiché avec la nouvelle valeur
6. **Touche Entrée** dans le tableau → force la validation de l'input en cours

### 6.3 Édition inline des équipes (détail)

1. Clic sur le nom d'équipe → crée un `<select id="selectZone">`
2. Charge les équipes via AJAX POST vers `v2/getEquipesMatch.php` avec `{idMatch, idJournee}`
3. L'équipe actuelle est pré-sélectionnée
4. Un bouton "Annuler" permet de fermer sans modifier
5. Blur du select → si équipe a changé → POST vers `v2/setEquipesMatch.php` avec `{idMatch, idEquipe, equipe: 'A'|'B'}`
6. Si `newIdEquipe == 0` → ajoute la classe `undefTeam`

### 6.4 Édition inline des arbitres (détail)

1. Clic sur le nom d'arbitre → crée un `<input id="inputZone2">` avec autocomplete
2. Autocomplete via `Autocompl_arb.php` avec params `{journee, sessionMatch}`
3. L'autocomplete retourne : `{matric, nom, prenom, libelle, arbitre, label, value}`
4. Format résultat : `"NOM Prénom (Club) [niveau]"` ou `"NOM Prénom [niveau]"` si pas de club
5. Trois boutons : **Valider** (sauve), **Annuler** (ferme), **Vider** (efface l'arbitre)
6. Sauvegarde via POST vers `v2/saveArbitres.php` avec `{idMatch, id: 'Arbitre_principal'|'Arbitre_secondaire', value: 'nomComplet|matricule'}`
7. Si matricule = 0 → ajoute la classe `pbArb` (arbitre non identifié)

### 6.5 Édition inline de la phase/journée (détail)

1. Clic sur la phase → crée un `<select>` rempli avec les journées du dropdown filtre
2. Chaque option a l'attribut `data-phase` de la journée
3. Blur → si journée changée → POST vers `v2/setPhaseMatch.php` avec `{idMatch, idPhase: newJourneeId}`
4. Déplace effectivement le match vers une autre journée

### 6.6 Endpoint AJAX inline

**Legacy** : `UpdateCellJQ.php` avec paramètres :
```
AjTableName=kp_match, AjWhere=Where Id = , AjTypeValeur={column}, AjValeur={value}, AjId={matchId}
```

**API2** :
```
PATCH /api2/admin/games/{id}/inline
  ?field={columnName}
  &value={newValue}
```

Champs autorisés en inline : `Numero_ordre`, `Date_match`, `Heure_match`, `Libelle`, `Terrain`, `ScoreA`, `ScoreB`.

---

## 7. Modal Ajout / Édition

### 7.1 Champs du formulaire

| Champ | Type | Requis | Validation | Description |
|-------|------|--------|------------|-------------|
| Journée/Phase | Select | **Oui** | Doit être sélectionnée | Dropdown des journées autorisées |
| Date | Date picker | **Oui** | Date valide | Date du match |
| Heure | Time picker | Non | Format HH:MM (confirmation si invalide) | Heure de début |
| N° match | Text (tel) | Non | Entier | Numéro d'ordre |
| Terrain | Text | Non | Max 12 chars | Numéro/nom du terrain |
| Type | Toggle C/E | Non | C ou E | Classement ou Élimination (hérité de la journée) |
| Intervalle | Text (tel) | Non | Entier (minutes) | Intervalle entre matchs (défaut: 40 min) |
| Intitulé/codage | Text | Non | Max 30 chars | Libellé avec notation bracket optionnelle |
| Équipe A | Select | Non | - | Équipes de la compétition/journée |
| Coefficient A | Text (tel) | Non | Entier (défaut: 1) | Coefficient de l'équipe A |
| Équipe B | Select | Non | - | Équipes de la compétition/journée |
| Coefficient B | Text (tel) | Non | Entier (défaut: 1) | Coefficient de l'équipe B |
| Arbitre 1 (principal) | Autocomplete + Select rapide + Select équipe | Non | - | Arbitre principal |
| Arbitre 2 (secondaire) | Autocomplete + Select rapide + Select équipe | Non | - | Arbitre secondaire |

### 7.2 Sélection des arbitres

Chaque champ arbitre dispose de 3 modes de saisie :

1. **Autocomplete** : Saisie libre avec suggestion (recherche dans `Autocompl_arb.php`)
2. **Dropdown "Principal"/"Secondaire"** : Sélection rapide parmi les arbitres connus de la journée (depuis `kp_arbitre`)
3. **Dropdown "Équipe"** : Sélection parmi les joueurs d'une équipe (pour remplacer le club entre parenthèses)

Le format stocké est : `"NOM Prénom (Club) [niveau]"` avec le matricule séparé.

### 7.3 Comportement du formulaire

- **Création** : Bouton "Ajouter" → valide les champs → POST
- **Édition** : Bouton "Modifier" (activé uniquement si un match est chargé via ParamMatch) → PUT
- **Annuler** : Remet le formulaire à l'état par défaut (Raz)
- Le type C/E change automatiquement quand on change de journée (hérite le type de la journée)
- Le focus sur les champs arbitre déclenche une vérification que la journée est sélectionnée

### 7.4 Validation

1. La date est **obligatoire** (alert si vide)
2. L'heure doit être au format HH:MM (confirmation si invalide, mais autorise à continuer)
3. La journée est **obligatoire** (alert si "*")

---

## 8. Actions individuelles (Toggles)

### 8.1 Publication

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Colonne | `Publication` |
| Valeurs | 'O' (publié/public) / 'N' (non publié/privé) |
| Feedback | Icône œil change immédiatement, fond de cellule coloré |
| Endpoint legacy | `v2/StatutPeriode.php` avec `TypeUpdate=Publication` |
| Endpoint API2 | `PATCH /admin/games/{id}/publication` |

### 8.2 Verrouillage / Validation

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 4 (toggle verrouillage en masse) / ≤ 6 (toggle individuel) |
| Colonne | `Validation` |
| Valeurs | 'O' (verrouillé/validé) / 'N' (déverrouillé) |
| Feedback | Icône cadenas change + **toute la ligne passe en mode verrouillé/déverrouillé** |
| Impact | Quand verrouillé : `directInput` → `directInputOff`, `typeMatch` → `typeMatchOff`, etc. |
| Endpoint API2 | `PATCH /admin/games/{id}/validation` |

### 8.3 Type C/E

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Colonne | `Type` |
| Valeurs | 'C' (classement) / 'E' (élimination) |
| Feedback | Icône type change (image typeC.png / typeE.png) |
| Endpoint API2 | `PATCH /admin/games/{id}/type` |

### 8.4 Statut Live

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Colonne | `Statut` |
| Cycle | ATT → ON → END → ATT |
| Feedback | Texte du statut change, score provisoire affiché/masqué |
| Confirmation | `confirm()` avant changement |
| Données liées | Affiche `Periode` et `ScoreDetailA - ScoreDetailB` quand ON ou END |
| Endpoint API2 | `PATCH /admin/games/{id}/statut` |

### 8.5 Imprimé

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Colonne | `Imprime` |
| Valeurs | 'O' (imprimé) / 'N' (non imprimé) |
| Feedback | Icône imprimante change |
| Endpoint API2 | `PATCH /admin/games/{id}/printed` |

### 8.6 Suppression individuelle

**Vérification préalable :**
1. Vérifier qu'il n'y a pas d'événements de match (`kp_match_detail`) → sinon erreur 409
2. Le match ne doit pas être validé (`Validation != 'O'`)

**Cascade de suppression :**
1. `DELETE FROM kp_match_joueur WHERE Id_match = ?` (joueurs du match)
2. `DELETE FROM kp_chrono WHERE IdMatch = ?` (données chronomètre)
3. `DELETE FROM kp_match WHERE Id = ? AND Validation != 'O'` (le match lui-même)

---

## 9. Actions en masse

Les actions en masse opèrent sur les matchs cochés. Elles sont regroupées dans un **menu dropdown** "Actions" dans la toolbar (amélioration par rapport aux ~15 icônes du legacy).

### 9.1 Suppression en masse

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Vérification | Même vérification que suppression individuelle pour chaque match |
| Confirmation | `confirm()` |
| Endpoint API2 | `DELETE /admin/games/bulk` avec body `{ "ids": [...] }` |

### 9.2 Publication en masse

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Comportement | Toggle : inverse la valeur Publication de chaque match sélectionné |
| Confirmation | `confirm()` |
| Endpoint API2 | `PATCH /admin/games/bulk/publication` avec body `{ "ids": [...] }` |

### 9.3 Verrouillage + Publication en masse

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 4 |
| Comportement | Met `Publication = 'O'` ET `Validation = 'O'` pour tous les matchs sélectionnés |
| Confirmation | `confirm()` |
| Endpoint API2 | `PATCH /admin/games/bulk/lock-publish` avec body `{ "ids": [...] }` |

### 9.4 Verrouillage en masse

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 4 |
| Comportement | Toggle : inverse la valeur Validation de chaque match sélectionné |
| Confirmation | `confirm()` |
| Endpoint API2 | `PATCH /admin/games/bulk/validation` avec body `{ "ids": [...] }` |

### 9.5 Affectation automatique (bracket parsing)

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Prérequis | Les matchs sélectionnés doivent avoir un Libelle avec notation bracket `[CODE1-CODE2-CODE3-CODE4]` |
| Confirmation | `confirm()` |
| Endpoint API2 | `POST /admin/games/bulk/auto-assign` avec body `{ "ids": [...] }` |

**Format bracket** : `[PART1-PART2-PART3-PART4]` où les séparateurs sont `-`, `/`, `*`, `,` ou `;`.

| Position | Affecte | Description |
|----------|---------|-------------|
| PART1 | Équipe A (`Id_equipeA`) | Code de l'équipe A |
| PART2 | Équipe B (`Id_equipeB`) | Code de l'équipe B |
| PART3 | Arbitre 1 (`Arbitre_principal`) | Code de l'arbitre 1 (optionnel) |
| PART4 | Arbitre 2 (`Arbitre_secondaire`) | Code de l'arbitre 2 (optionnel) |

**Codes supportés** :

| Préfixe | Signification | Exemple | Résolution |
|---------|---------------|---------|------------|
| **T** ou **D** | Tirage (Draw) | `T1` | Équipe avec `Tirage = 1` dans la compétition |
| **V**, **G**, **W** | Vainqueur (Winner) | `V2` | Vainqueur du match n°2 (ScoreA > ScoreB) |
| **P**, **L** | Perdant (Loser) | `P3` | Perdant du match n°3 (ScoreA < ScoreB) |
| **Lettre seule** | Rang dans poule | `1A` | 1er du classement poule A (via `kp_competition_equipe_journee.Clt`) |

**Gestion arbitres** : Si l'arbitre a déjà un matricule identifié (`Matric_arbitre_* != 0`), l'affectation est ignorée pour cet arbitre.

### 9.6 Annulation affectation automatique

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Comportement | Remet `Id_equipeA = NULL`, `Id_equipeB = NULL`, `Arbitre_principal = '-1'`, `Arbitre_secondaire = '-1'`, `Matric_arbitre_principal = 0`, `Matric_arbitre_secondaire = 0` + supprime les joueurs du match (`kp_match_joueur`) |
| Confirmation | `confirm()` |
| Endpoint API2 | `POST /admin/games/bulk/cancel-assign` avec body `{ "ids": [...] }` |

### 9.7 Changement de poule/journée

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Prérequis | Une journée cible doit être sélectionnée dans le filtre journée (pas "*") |
| Comportement | Déplace les matchs sélectionnés vers la journée cible (`UPDATE kp_match SET Id_journee = ?`) |
| Confirmation | `confirm()` |
| Endpoint API2 | `PATCH /admin/games/bulk/move` avec body `{ "ids": [...], "targetJourneeId": 123 }` |

### 9.8 Renumérotation

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 2 |
| Interface | Inline : champ "Renuméroter à partir de :" + boutons Confirmer/Annuler |
| Comportement | Numérote séquentiellement les matchs cochés à partir du numéro saisi |
| Respect verrouillage | Non (opère sur tous les matchs cochés) |
| Endpoint API2 | `PATCH /admin/games/bulk/renumber` avec body `{ "ids": [...], "startNumber": 1 }` |

### 9.9 Changement de date en masse

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 2 |
| Interface | Inline : champ date + boutons Confirmer/Annuler |
| Comportement | Change `Date_match` de tous les matchs cochés non verrouillés |
| Respect verrouillage | **Oui** — les matchs verrouillés sont ignorés |
| Endpoint API2 | `PATCH /admin/games/bulk/date` avec body `{ "ids": [...], "date": "2026-01-15" }` |

### 9.10 Incrémentation d'heure

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 2 |
| Interface | Inline : champ heure départ + champ intervalle (minutes) + boutons Confirmer/Annuler |
| Comportement | Le 1er match coché reçoit l'heure de départ, chaque match suivant += intervalle |
| Respect verrouillage | **Oui** — les matchs verrouillés sont ignorés |
| Défauts | Heure: 10:00, Intervalle: 40 min |
| Endpoint API2 | `PATCH /admin/games/bulk/time-increment` avec body `{ "ids": [...], "startTime": "10:00", "interval": 40 }` |

### 9.11 Remplacement de groupe/poule

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 2 |
| Interface | Inline : champ "Groupe à remplacer" + champ "Par" + boutons Confirmer/Annuler |
| Comportement | Dans le Libelle de chaque match coché, remplace le code de groupe dans la notation bracket |
| Validation | Uniquement lettres majuscules, les deux champs doivent être différents |
| Respect verrouillage | **Oui** — les matchs verrouillés sont ignorés |
| Exemple | Remplacer `A` par `X` : `[1A-2A-ARB1-ARB2]` → `[1X-2X-ARB1-ARB2]` |
| Endpoint API2 | `PATCH /admin/games/bulk/replace-group` avec body `{ "ids": [...], "oldGroup": "A", "newGroup": "X" }` |

### 9.12 Toggle imprimé en masse

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 6 |
| Comportement | Toggle le flag `Imprime` de chaque match coché |
| Endpoint API2 | Utilise les toggles individuels en boucle côté client |

### 9.13 Feuilles de marque PDF (sélection)

| Propriété | Valeur |
|-----------|--------|
| Profil | ≤ 10 |
| Comportement | Ouvre `FeuilleMatchMulti.php?listMatch={ids}` dans un nouvel onglet |
| Lien | Legacy PHP (conservé tel quel) |

---

## 10. Endpoints API2

### 10.1 Lecture

| Méthode | Endpoint | Description | Paramètres |
|---------|----------|-------------|------------|
| GET | `/admin/games` | Liste des matchs | `?season=`, `?competition=`, `?event=`, `?tour=`, `?journee=`, `?date=`, `?terrain=`, `?sort=`, `?unlocked=`, `?page=`, `?limit=`, `?search=` |
| GET | `/admin/games/{id}` | Détail d'un match | - |
| GET | `/admin/games/teams?journeeId=` | Équipes disponibles pour un match | Retourne les équipes de la compétition/journée |
| GET | `/admin/games/referees?journeeId=&matchId=` | Arbitres disponibles | Autocomplete arbitres |

**Réponse GET /admin/games :**
```json
{
  "games": [
    {
      "id": 12345,
      "idJournee": 8642,
      "numeroOrdre": 1,
      "dateMatch": "2026-01-11",
      "heureMatch": "10:00",
      "libelle": "[T1-T2-ARB1-ARB2]",
      "terrain": "1",
      "publication": "O",
      "validation": "N",
      "statut": "ATT",
      "type": "C",
      "periode": "",
      "scoreA": "",
      "scoreB": "",
      "scoreDetailA": "",
      "scoreDetailB": "",
      "imprime": "N",
      "coeffA": 1,
      "coeffB": 1,
      "idEquipeA": 456,
      "equipeA": "Acigné KP",
      "idEquipeB": 789,
      "equipeB": "Strasbourg EKP",
      "arbitrePrincipal": "DUPONT Jean (Club) A",
      "matricArbitrePrincipal": 1234567,
      "arbitreSecondaire": "",
      "matricArbitreSecondaire": 0,
      "codeCompetition": "N1H",
      "phase": "Poule A",
      "niveau": 1,
      "etape": 1,
      "lieu": "Corbeil-Essonnes",
      "libelleJournee": "Tournoi Pierre Bretenoux",
      "soustitre2": "U19H",
      "matchAutorisation": "O",
      "authorized": true
    }
  ],
  "total": 24,
  "page": 1,
  "totalPages": 1,
  "phaseLibelle": true,
  "dates": ["2026-01-11", "2026-01-12"]
}
```

### 10.2 Écriture

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| POST | `/admin/games` | Créer un match | ≤ 6 |
| PUT | `/admin/games/{id}` | Modifier un match (formulaire complet) | ≤ 6 |
| PATCH | `/admin/games/{id}/inline` | Mise à jour inline d'un champ | ≤ 6 (scores: ≤ 9) |
| PATCH | `/admin/games/{id}/publication` | Toggle publication | ≤ 6 |
| PATCH | `/admin/games/{id}/validation` | Toggle verrouillage | ≤ 4 |
| PATCH | `/admin/games/{id}/type` | Toggle type C/E | ≤ 6 |
| PATCH | `/admin/games/{id}/statut` | Cycle statut ATT→ON→END | ≤ 6 |
| PATCH | `/admin/games/{id}/printed` | Toggle imprimé | ≤ 6 |
| PATCH | `/admin/games/{id}/team` | Changer équipe A ou B | ≤ 6 |
| PATCH | `/admin/games/{id}/referee` | Changer arbitre 1 ou 2 | ≤ 6 |
| PATCH | `/admin/games/{id}/journee` | Déplacer vers autre journée | ≤ 6 |
| DELETE | `/admin/games/{id}` | Supprimer un match | ≤ 6 |

**Body POST /admin/games :**
```json
{
  "idJournee": 8642,
  "dateMatch": "2026-01-11",
  "heureMatch": "10:00",
  "numeroOrdre": 1,
  "terrain": "1",
  "type": "C",
  "libelle": "[T1-T2-ARB1-ARB2]",
  "idEquipeA": 456,
  "idEquipeB": 789,
  "coeffA": 1,
  "coeffB": 1,
  "arbitrePrincipal": "DUPONT Jean (Club) A",
  "matricArbitrePrincipal": 1234567,
  "arbitreSecondaire": "",
  "matricArbitreSecondaire": 0
}
```

**Body PATCH /admin/games/{id}/team :**
```json
{
  "team": "A",
  "idEquipe": 456
}
```

**Body PATCH /admin/games/{id}/referee :**
```json
{
  "position": "Arbitre_principal",
  "value": "DUPONT Jean (Club) A|1234567"
}
```

### 10.3 Actions en masse

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| PATCH | `/admin/games/bulk/publication` | Toggle publication en masse | ≤ 6 |
| PATCH | `/admin/games/bulk/validation` | Toggle verrouillage en masse | ≤ 4 |
| PATCH | `/admin/games/bulk/lock-publish` | Verrouiller + Publier en masse | ≤ 4 |
| POST | `/admin/games/bulk/auto-assign` | Affectation automatique (bracket parsing) | ≤ 6 |
| POST | `/admin/games/bulk/cancel-assign` | Annuler affectation automatique | ≤ 6 |
| PATCH | `/admin/games/bulk/move` | Déplacer vers autre journée | ≤ 6 |
| PATCH | `/admin/games/bulk/renumber` | Renuméroter les matchs | ≤ 2 |
| PATCH | `/admin/games/bulk/date` | Changer la date | ≤ 2 |
| PATCH | `/admin/games/bulk/time-increment` | Incrémenter l'heure | ≤ 2 |
| PATCH | `/admin/games/bulk/replace-group` | Remplacer groupe dans codes bracket | ≤ 2 |
| DELETE | `/admin/games/bulk` | Suppression en masse | ≤ 6 |

### 10.4 Init Titulaires

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| POST | `/admin/games/init-players` | Initialiser les joueurs des matchs d'une journée | ≤ 6 |
| POST | `/admin/games/{id}/init-players` | Initialiser les joueurs d'un match spécifique | ≤ 6 |

**Body POST /admin/games/init-players :**
```json
{
  "journeeId": 8642,
  "scope": "journee"
}
```

Ou pour une compétition entière : `{ "competitionCode": "N1H", "scope": "competition" }`
Ou pour une équipe : `{ "matchId": 12345, "team": "A", "scope": "team" }`

### 10.5 Autocomplete

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/admin/autocomplete/referees?q=&journee=&match=` | Arbitres (nom/prénom, min 2 chars) |

### 10.6 Codes de retour

| Code | Cas |
|------|-----|
| 200 | Succès (GET, PUT, PATCH) |
| 201 | Création réussie (POST) |
| 204 | Suppression réussie (DELETE) |
| 400 | Données invalides (date manquante, journée non sélectionnée) |
| 403 | Profil insuffisant |
| 404 | Match non trouvé |
| 409 | Conflit : événements de match existants (DELETE), ou match verrouillé (modification) |

---

## 11. Schéma de données

### 11.1 Table `kp_match`

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| Id | int(11) | Non | Clé primaire auto-incrémentée |
| Id_journee | int(11) | Non | FK → kp_journee.Id |
| Id_equipeA | int(11) | Oui | FK → kp_competition_equipe.Id |
| Id_equipeB | int(11) | Oui | FK → kp_competition_equipe.Id |
| Numero_ordre | smallint(6) | Oui | Numéro d'ordre du match |
| Date_match | date | Oui | Date du match |
| Heure_match | varchar(5) | Oui | Heure au format HH:MM |
| Libelle | varchar(30) | Oui | Code/libellé du match (notation bracket) |
| Terrain | varchar(12) | Oui | Numéro ou nom du terrain |
| Type | char(1) | Non | 'C' (classement) ou 'E' (élimination), défaut: 'C' |
| ScoreA | varchar(4) | Oui | Score final équipe A |
| ScoreB | varchar(4) | Oui | Score final équipe B |
| CoeffA | varchar(4) | Oui | Coefficient équipe A (défaut: '1') |
| CoeffB | varchar(4) | Oui | Coefficient équipe B (défaut: '1') |
| Arbitre_principal | varchar(60) | Oui | Nom complet de l'arbitre principal |
| Arbitre_secondaire | varchar(60) | Oui | Nom complet de l'arbitre secondaire |
| Matric_arbitre_principal | int(11) | Oui | Matricule de l'arbitre principal (0 = non identifié) |
| Matric_arbitre_secondaire | int(11) | Oui | Matricule de l'arbitre secondaire (0 = non identifié) |
| Publication | char(1) | Oui | 'O' = publié, 'N' = non publié |
| Validation | char(1) | Oui | 'O' = verrouillé/validé, 'N' = déverrouillé |
| Statut | varchar(5) | Oui | 'ATT' (attente), 'ON' (en cours), 'END' (terminé) |
| Periode | varchar(10) | Oui | Période en cours (visible quand Statut = ON) |
| ScoreDetailA | varchar(20) | Oui | Score détaillé provisoire A (affiché quand ON/END) |
| ScoreDetailB | varchar(20) | Oui | Score détaillé provisoire B (affiché quand ON/END) |
| Imprime | char(1) | Oui | 'O' = imprimé, 'N' = non imprimé |
| Code_uti | varchar(8) | Oui | Code utilisateur dernière modification |

### 11.2 Table `kp_competition_equipe` (référencée)

| Colonne | Type | Description |
|---------|------|-------------|
| Id | int(11) | Clé primaire (référencée par Id_equipeA/Id_equipeB) |
| Code_compet | varchar(12) | Code compétition |
| Code_saison | char(4) | Code saison |
| Libelle | varchar(60) | Nom de l'équipe |
| Tirage | smallint(6) | Numéro de tirage (pour affectation auto code T) |
| Poule | varchar(30) | Poule/groupe de l'équipe |

### 11.3 Table `kp_match_detail` (empêche suppression)

| Colonne | Type | Description |
|---------|------|-------------|
| Id | int(11) | Clé primaire |
| Id_match | int(11) | FK → kp_match.Id |
| ... | | Événements de match (buts, cartes, etc.) |

### 11.4 Table `kp_match_joueur` (supprimée en cascade)

| Colonne | Type | Description |
|---------|------|-------------|
| Id_match | int(11) | FK → kp_match.Id |
| Matric | varchar(10) | Matricule du joueur |
| Numero | smallint(6) | Numéro du joueur |
| Equipe | char(1) | 'A' ou 'B' |
| Capitaine | char(1) | 'C' = capitaine, 'X' = supprimé, 'A' = absent |

### 11.5 Table `kp_chrono` (supprimée en cascade)

| Colonne | Type | Description |
|---------|------|-------------|
| IdMatch | int(11) | FK → kp_match.Id |
| ... | | Données chronomètre/timer |

### 11.6 Contraintes de suppression

```sql
-- Avant suppression d'un match :
-- 1. VÉRIFIER : pas de kp_match_detail → sinon erreur 409
-- 2. SUPPRIMER : kp_match_joueur WHERE Id_match = ? AND match non verrouillé
-- 3. SUPPRIMER : kp_chrono WHERE IdMatch = ?
-- 4. SUPPRIMER : kp_match WHERE Id = ? AND Validation != 'O'
```

---

## 12. Composants Vue

### 12.1 Structure des fichiers

```
sources/app4/pages/games/
└── index.vue              # Page principale (liste + modals)
```

### 12.2 Composants réutilisés

| Composant | Usage |
|-----------|-------|
| `AdminWorkContextSummary` | Rappel contexte (saison + périmètre) |
| `AdminToolbar` | Barre de recherche + bouton Ajouter + menu Actions |
| `AdminModal` | Modal ajout/édition de match |
| `AdminConfirmModal` | Confirmation suppression et actions en masse |
| `AdminToggleButton` | Toggle publication (oeil), verrouillage (cadenas) |
| `AdminPagination` | Pagination de la liste |
| `AdminCardList` / `AdminCard` | Vue mobile en cartes |
| `AdminScrollToTop` | Bouton retour en haut |

### 12.3 Composants spécifiques à créer

| Composant | Description |
|-----------|-------------|
| `GameInlineCell` | Composant générique d'édition inline (span → input/select au clic) |
| `GameTeamSelect` | Sélecteur d'équipe inline avec chargement AJAX |
| `GameRefereeAutocomplete` | Autocomplete arbitre inline avec boutons Valider/Annuler/Vider |
| `GameBulkActionsMenu` | Menu dropdown des actions en masse |
| `GameFilters` | Barre de filtres enrichie (7 dropdowns + checkbox) |

### 12.4 Dépendance au contexte de travail

Cette page utilise :
- `workContextStore.season` pour la saison active
- `workContextStore.competitionCodes` pour le périmètre initial des compétitions
- Filtres locaux additionnels et très spécifiques : événement, compétition individuelle, tour, journée, date, terrain, tri, matchs non verrouillés

---

## 13. Traductions i18n

### 13.1 Clés françaises (`fr.json`)

```json
{
  "menu": {
    "games": "Matchs"
  },
  "games": {
    "title": "Gestion des Matchs",
    "add": "Ajouter un match",
    "edit": "Modifier le match",
    "delete_confirm_title": "Supprimer le match",
    "delete_confirm_message": "Êtes-vous sûr de vouloir supprimer le match #{id} ?",
    "delete_error_events": "Il reste des événements dans ce match ! Suppression impossible.",
    "delete_error_locked": "Ce match est verrouillé. Déverrouillez-le avant de le supprimer.",
    "confirm_update": "Confirmez-vous la modification ?",
    "confirm_affect": "Confirmez-vous l'affectation automatique ?",
    "confirm_delete": "Confirmez-vous la suppression ?",
    "confirm_status_change": "Confirmez-vous le changement de statut ?",
    "select_journee": "Sélectionnez une journée / une phase !",
    "select_competition": "Sélectionnez une compétition !",
    "select_team": "Sélectionnez une équipe !",
    "date_empty": "La date est obligatoire !",
    "time_invalid": "L'heure n'est pas au format HH:MM. Continuer quand même ?",
    "update_failed": "Mise à jour impossible",
    "click_to_edit": "Cliquez pour modifier",
    "referee_unidentified": "Arbitre non identifié",
    "team_undefined": "Équipe non définie",
    "all_events": "Tous les événements",
    "all_competitions": "Toutes les compétitions de l'événement",
    "all_rounds": "Tous",
    "all_journees": "Tous",
    "all_dates": "Toutes",
    "all_terrains": "Tous",
    "unlocked_only": "Matchs non verrouillés",
    "filter_event": "Événement",
    "filter_competition": "Compétition",
    "filter_round": "Tour",
    "filter_journee": "Journée / Phase / Poule",
    "filter_date_terrain": "Date / Terrain",
    "filter_sort": "Ordre de tri",
    "sort": {
      "date_time_terrain": "Par date, heure et terrain",
      "competition_date": "Par compétition et date",
      "competition_phase": "Par compétition et phase",
      "terrain_date": "Par terrain et date",
      "number": "Par numéro"
    },
    "field": {
      "number": "N°",
      "time": "Heure",
      "category": "Cat.",
      "phase": "Phase",
      "code": "Code",
      "location": "Lieu",
      "type": "Type",
      "terrain": "Terrain",
      "team_a": "Équipe A",
      "score_a": "Sc A",
      "lock": "Verrou",
      "score_b": "Sc B",
      "team_b": "Équipe B",
      "referee_1": "Arbitre 1",
      "referee_2": "Arbitre 2",
      "printed": "Imprimé",
      "date": "Date",
      "interval": "Intervalle (min)",
      "label_coding": "Intitulé / Codage",
      "game_number": "Match N°",
      "coefficient": "Coef."
    },
    "type_classification": "Match de classement",
    "type_elimination": "Match éliminatoire",
    "published": "Public",
    "unpublished": "Privé",
    "locked": "Verrouillé",
    "unlocked": "Déverrouillé",
    "status_waiting": "Match en attente",
    "status_ongoing": "En cours",
    "status_ended": "Match terminé",
    "provisional_score": "Score provisoire",
    "period": "Période",
    "composition": "Composition équipe",
    "scoresheet_pdf": "Feuille de marque (PDF)",
    "scoresheet_online": "Feuille de marque en ligne",
    "referee_pool": "Pool d'arbitres...",
    "referee_main": "Principal",
    "referee_secondary": "Secondaire",
    "referee_team": "Équipe",
    "show_form": "Afficher le formulaire",
    "hide_form": "Masquer le formulaire",
    "validate": "Valider",
    "cancel": "Annuler",
    "clear": "Vider",
    "init_players": "Initialiser les titulaires",
    "init_players_competition": "Initialiser titulaires (compétition)",
    "init_players_journee": "Initialiser titulaires (journée)",
    "init_players_team": "Initialiser titulaires (équipe)",
    "init_confirm": "Confirmer l'initialisation des titulaires ?",
    "nb_games": "Nb matchs",
    "selection": "Sélection",
    "all_games": "Tous les matchs",
    "bulk": {
      "actions": "Actions",
      "select_all": "Tout sélectionner",
      "deselect_all": "Tout désélectionner",
      "delete": "Supprimer",
      "publish": "Publier",
      "lock_publish": "Verrouiller + Publier",
      "lock": "Verrouiller",
      "auto_assign": "Affectation automatique",
      "cancel_assign": "Annuler l'affectation",
      "change_pool": "Changer de poule",
      "renumber": "Renuméroter les matchs",
      "renumber_from": "Renuméroter à partir de :",
      "change_date": "Changer la date",
      "new_date": "Nouvelle date :",
      "increment_time": "Incrémenter l'heure",
      "start_time": "Heure de départ :",
      "interval_min": "Intervalle (min) :",
      "replace_group": "Remplacer le groupe",
      "group_search": "Groupe à remplacer :",
      "group_replace": "Par :",
      "toggle_printed": "Toggle imprimé",
      "scoresheets_pdf": "Feuilles de marque PDF"
    },
    "documents": {
      "game_list_fr": "Liste des matchs (FR)",
      "game_list_en": "Game list (EN)",
      "scoresheets_all": "Feuilles de marque",
      "export_ods": "Export (ODS)",
      "public_list_fr": "Liste publique (FR)",
      "public_list_en": "Public list (EN)",
      "pitches_teams": "Terrains (Équipes)",
      "pitches_phases": "Terrains (Phases)"
    },
    "added": "Match créé.",
    "updated": "Match modifié.",
    "deleted": "Match supprimé.",
    "games_modified": "{count} matchs modifiés.",
    "no_game_selected": "Aucun match sélectionné",
    "group_letters_only": "Les noms de groupe doivent contenir uniquement des lettres majuscules",
    "groups_must_differ": "Les groupes doivent être différents",
    "group_not_found": "Aucun match modifié (groupe \"{group}\" non trouvé dans les codes)."
  }
}
```

### 13.2 Clés anglaises (`en.json`)

```json
{
  "menu": {
    "games": "games"
  },
  "games": {
    "title": "Game Management",
    "add": "Add a game",
    "edit": "Edit game",
    "delete_confirm_title": "Delete game",
    "delete_confirm_message": "Are you sure you want to delete game #{id}?",
    "delete_error_events": "Game events still exist! Cannot delete.",
    "delete_error_locked": "This game is locked. Unlock it before deleting.",
    "confirm_update": "Confirm modification?",
    "confirm_affect": "Confirm auto-assignment?",
    "confirm_delete": "Confirm deletion?",
    "confirm_status_change": "Confirm status change?",
    "select_journee": "Select a gameday / phase!",
    "select_competition": "Select a competition!",
    "select_team": "Select a team!",
    "date_empty": "Date is required!",
    "time_invalid": "Time is not in HH:MM format. Continue anyway?",
    "update_failed": "Update failed",
    "click_to_edit": "Click to edit",
    "referee_unidentified": "Unidentified referee",
    "team_undefined": "Undefined team",
    "all_events": "All events",
    "all_competitions": "All competitions for the event",
    "all_rounds": "All",
    "all_journees": "All",
    "all_dates": "All",
    "all_terrains": "All",
    "unlocked_only": "Unlocked games only",
    "filter_event": "Event",
    "filter_competition": "Competition",
    "filter_round": "Round",
    "filter_journee": "Gameday / Phase / Pool",
    "filter_date_terrain": "Date / Pitch",
    "filter_sort": "Sort order",
    "sort": {
      "date_time_terrain": "By date, time and pitch",
      "competition_date": "By competition and date",
      "competition_phase": "By competition and phase",
      "terrain_date": "By pitch and date",
      "number": "By number"
    },
    "field": {
      "number": "#",
      "time": "Time",
      "category": "Cat.",
      "phase": "Phase",
      "code": "Code",
      "location": "Location",
      "type": "Type",
      "terrain": "Pitch",
      "team_a": "Team A",
      "score_a": "Sc A",
      "lock": "Lock",
      "score_b": "Sc B",
      "team_b": "Team B",
      "referee_1": "Referee 1",
      "referee_2": "Referee 2",
      "printed": "Printed",
      "date": "Date",
      "interval": "Interval (min)",
      "label_coding": "Label / Coding",
      "game_number": "Game #",
      "coefficient": "Coeff."
    },
    "type_classification": "Classification game",
    "type_elimination": "Elimination game",
    "published": "Public",
    "unpublished": "Private",
    "locked": "Locked",
    "unlocked": "Unlocked",
    "status_waiting": "Waiting",
    "status_ongoing": "Ongoing",
    "status_ended": "Ended",
    "provisional_score": "Provisional score",
    "period": "Period",
    "composition": "Team composition",
    "scoresheet_pdf": "Score sheet (PDF)",
    "scoresheet_online": "Online score sheet",
    "referee_pool": "Referee pool...",
    "referee_main": "Main",
    "referee_secondary": "Secondary",
    "referee_team": "Team",
    "show_form": "Show form",
    "hide_form": "Hide form",
    "validate": "Validate",
    "cancel": "Cancel",
    "clear": "Clear",
    "init_players": "Initialize players",
    "init_players_competition": "Initialize players (competition)",
    "init_players_journee": "Initialize players (gameday)",
    "init_players_team": "Initialize players (team)",
    "init_confirm": "Confirm player initialization?",
    "nb_games": "Games",
    "selection": "Selection",
    "all_games": "All games",
    "bulk": {
      "actions": "Actions",
      "select_all": "Select all",
      "deselect_all": "Deselect all",
      "delete": "Delete",
      "publish": "Publish",
      "lock_publish": "Lock + Publish",
      "lock": "Lock",
      "auto_assign": "Auto-assign",
      "cancel_assign": "Cancel assignment",
      "change_pool": "Change pool",
      "renumber": "Renumber games",
      "renumber_from": "Renumber starting from:",
      "change_date": "Change date",
      "new_date": "New date:",
      "increment_time": "Increment time",
      "start_time": "Start time:",
      "interval_min": "Interval (min):",
      "replace_group": "Replace group",
      "group_search": "Group to replace:",
      "group_replace": "With:",
      "toggle_printed": "Toggle printed",
      "scoresheets_pdf": "Score sheets PDF"
    },
    "documents": {
      "game_list_fr": "Game list (FR)",
      "game_list_en": "Game list (EN)",
      "scoresheets_all": "Score sheets",
      "export_ods": "Export (ODS)",
      "public_list_fr": "Public list (FR)",
      "public_list_en": "Public list (EN)",
      "pitches_teams": "Pitches (Teams)",
      "pitches_phases": "Pitches (Phases)"
    },
    "added": "Game created.",
    "updated": "Game updated.",
    "deleted": "Game deleted.",
    "games_modified": "{count} games modified.",
    "no_game_selected": "No game selected",
    "group_letters_only": "Group names must contain only uppercase letters",
    "groups_must_differ": "Groups must be different",
    "group_not_found": "No game modified (group \"{group}\" not found in codes)."
  }
}
```


## 14. Sécurité et Migration

### 14.1 Contrôle d'accès

| Vérification | Responsable |
|-------------|------------|
| Authentification JWT | API2 (middleware) |
| Profil utilisateur (1-10) | API2 (chaque endpoint vérifie le profil minimum) |
| Filtre compétition (`Filtre_competition`) | API2 (filtre les journées/matchs accessibles) |
| Filtre journée (`Filtre_journee`) | API2 (filtre les journées accessibles) |
| Match autorisation (`MatchAutorisation`) | API2 (vérifie que le match appartient à une journée autorisée) |
| Validation empêche modification | API2 + Frontend (match verrouillé = lecture seule) |

### 14.2 Variable `MatchAutorisation`

En legacy, chaque match a un flag `MatchAutorisation` (calculé côté PHP) indiquant si l'utilisateur a le droit de modifier ce match. Ce flag est basé sur :
- Le filtre journée de l'utilisateur (`Filtre_journee`)
- Le filtre compétition de l'utilisateur (`Filtre_competition`)

En API2, ce contrôle est effectué côté serveur et le flag `authorized` est inclus dans la réponse.

### 14.3 Protection contre la suppression

- Un match avec des événements (`kp_match_detail`) ne peut **jamais** être supprimé → erreur 409
- Un match verrouillé (`Validation = 'O'`) ne peut **jamais** être supprimé → condition SQL `AND Validation != 'O'`
- La suppression cascade `kp_match_joueur` et `kp_chrono` mais **pas** `kp_match_detail`

### 14.4 Notes de migration

| Legacy | Nuxt/API2 | Note |
|--------|-----------|------|
| `GestionJournee.php` (switch Cmd) | Endpoints REST séparés | Chaque commande = un endpoint PATCH/POST/DELETE |
| `UpdateCellJQ.php` (inline) | `PATCH /admin/games/{id}/inline` | Endpoint dédié avec whitelist de champs |
| `v2/StatutPeriode.php` (toggles) | `PATCH /admin/games/{id}/{toggle}` | Un endpoint par toggle |
| `v2/getEquipesMatch.php` | `GET /admin/games/teams?journeeId=` | Retourne les équipes disponibles |
| `v2/setEquipesMatch.php` | `PATCH /admin/games/{id}/team` | Change l'équipe A ou B |
| `v2/setPhaseMatch.php` | `PATCH /admin/games/{id}/journee` | Déplace le match |
| `v2/saveArbitres.php` | `PATCH /admin/games/{id}/referee` | Change l'arbitre 1 ou 2 |
| `Autocompl_arb.php` | `GET /admin/autocomplete/referees` | Autocomplete arbitres |
| `InitTitulaireJQ.php` | `POST /admin/games/init-players` | Init compositions d'équipe |
| Formulaire collapsible (hideTr) | Modal | Cohérent avec page Journées |
| 15 icônes en fieldset | Menu dropdown "Actions" | UX modernisée |
| Pas de pagination | Pagination 50 par défaut | Performance |
| `alert()` JavaScript | Toast notifications | UX modernisée |
| Flatpickr + jQuery | Composants Nuxt UI natifs | Stack modernisée |
| Liens PDF (FeuilleMatch*.php, PdfListeMatchs*.php) | Conservés en liens legacy | Migration PDF ultérieure |
