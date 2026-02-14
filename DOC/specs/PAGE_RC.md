# Spécification - Page Responsables de Compétition (RC)

**Version** : 1.2
**Date** : 2026-02-14
**Status** : Implemented
**Legacy PHP** : GestionRc.php + GestionRc.tpl + GestionRc.js

---

## 1. Vue d'ensemble

Page d'administration des Responsables de Compétition (RC). Un RC est une personne licenciée assignée à une compétition pour une saison donnée, avec un ordre de priorité. La page permet de gérer (ajouter, modifier, supprimer) les RC et de copier l'ensemble des RC d'une saison vers une autre.

**Route** : `/rc`

**Accès** :
- Profil ≤ 4 : Lecture seule
- Profil ≤ 2 : Ajout, Modification, Copie RC
- Profil ≤ 1 : Suppression

**Page PHP Legacy** : `GestionRc.php` + `GestionRc.tpl` + `GestionRc.js`

**Implémentation Nuxt** : `sources/app4/pages/rc/index.vue`

**Contexte de travail** : Oui — la saison provient du `workContextStore`. Le filtrage des compétitions utilise `CompetitionMultiSelect.vue`.

**Accès depuis** : Page Compétitions (`/competitions`) via le lien RC existant (icône `heroicons:users-solid`)

---

## 2. Fonctionnalités

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Liste des RC pour la saison du contexte | ≤ 4 | Essentielle | ✅ Conserver |
| 2 | Filtrage par compétitions (dropdown inline CompetitionMultiSelect dans la toolbar) | ≤ 4 | Amélioration | ✅ Remplace les 3 filtres legacy (niveau, type, compétition) |
| 3 | Recherche textuelle (nom, prénom, licence) | ≤ 4 | Amélioration | ✅ Ajouter |
| 4 | Ajout d'un RC (modal) | ≤ 2 | Essentielle | ✅ Conserver |
| 5 | Modification d'un RC (modal) | ≤ 2 | Essentielle | ✅ Conserver |
| 6 | Suppression d'un ou plusieurs RC | ≤ 1 | Essentielle | ✅ Conserver |
| 7 | Copie des RC d'une saison à une autre | ≤ 2 | Essentielle | ✅ Conserver |
| 8 | Lien vers la fiche athlète depuis le n° licence | ≤ 4 | Essentielle | ✅ Conserver |

### Améliorations par rapport au legacy

| # | Amélioration | Description |
|---|--------------|-------------|
| 1 | Filtre unifié dans la toolbar | Dropdown inline CompetitionMultiSelect dans la toolbar (slot `#before-search`), remplace 3 filtres séparés legacy |
| 2 | Modal au lieu de formulaire latéral | Formulaire d'ajout/édition dans une modal |
| 3 | Recherche textuelle | Recherche client-side dans le nom, prénom, n° licence |
| 4 | Suppression en masse | Sélection multiple avec checkbox + suppression groupée |
| 5 | Responsive | Table desktop → cartes mobile |
| 6 | Saison du contexte | Plus besoin de sélecteur de saison séparé |
| 7 | "- CNA -" modernisé | Valeur `null` en DB, affiché "National (sans compétition)" |
| 8 | Compétitions groupées par section | Sélecteur de compétition dans le formulaire groupé par section |

---

## 3. Structure de la Page

### 3.1 Vue Desktop

```
┌──────────────────────────────────────────────────────────────────────────────┐
│  Rappel contexte (WorkContextSummary)                                        │
├──────────────────────────────────────────────────────────────────────────────┤
│  Responsables de Compétition                                                 │
├──────────────────────────────────────────────────────────────────────────────┤
│  [🗑 Suppr.(N)]   [🔽 Compétitions (3)] [🔍 Recherche...] [📋 Copier] [+] │
│                     └──────────────────┐                                     │
│                     │ ☑ Toutes (12)    │  (dropdown flottant)                │
│                     │ ☑ N1H-A  ☑ N1F  │                                     │
│                     └──────────────────┘                                     │
├──────────────────────────────────────────────────────────────────────────────┤
│  │ ☐ │ Compétition            │ Ordre │ Nom Prénom      │ Licence │ Email  │ │
│  │───│────────────────────────│───────│─────────────────│─────────│────────│ │
│  │ ☐ │ National (sans compét.)│   1   │ DUPONT Jean     │  12345  │ j@e.fr │ │
│  │ ☐ │ N1H-A                  │   1   │ MARTIN Pierre   │  23456  │ p@e.fr │ │
│  │ ☐ │ N1H-A                  │   2   │ BERNARD Marie   │  34567  │ m@e.fr │ │
│  │ ☐ │ N1F                    │   1   │ LEROY Sophie    │  45678  │ s@e.fr │ │
│  │ ...                                                                      │
├──────────────────────────────────────────────────────────────────────────────┤
│  Total : 24 RC                                                               │
└──────────────────────────────────────────────────────────────────────────────┘
```

**Toolbar (une seule ligne) :**
- **Gauche** : bouton suppression en masse (si sélection)
- **Droite** : filtre compétitions (dropdown inline, slot `#before-search`) + champ de recherche + bouton "Copier les RC" (slot `#after-search`) + bouton "Ajouter"
- Le bouton "Copier les RC" ouvre une modal (profil ≤ 2), plus de section séparée en bas de page

**Actions par ligne :**
- Clic sur la ligne : ouvre la modal d'édition (profil ≤ 2)
- Checkbox : sélection pour suppression en masse (profil ≤ 1)
- Clic sur le n° licence : lien vers la fiche athlète (`/athletes?matric={matric}` ou legacy `GestionAthlete.php?Athlete={matric}`)

### 3.2 Vue Mobile (cartes)

```
┌──────────────────────────────────┐
│  Rappel contexte                 │
├──────────────────────────────────┤
│  Responsables de Compétition     │
├──────────────────────────────────┤
│  [🔽 Compétitions (3)]          │
│  [🔍 Recherche...]  [📋 Copier] │
│  [🗑 Suppr. (0)]   [+ Ajouter]  │
├──────────────────────────────────┤
│  ┌────────────────────────────┐  │
│  │ ☐ DUPONT Jean              │  │
│  │ National (sans compét.)    │  │
│  │ Ordre: 1 │ #12345          │  │
│  │ j@example.fr               │  │
│  └────────────────────────────┘  │
│  ┌────────────────────────────┐  │
│  │ ☐ MARTIN Pierre            │  │
│  │ N1H-A                      │  │
│  │ Ordre: 1 │ #23456          │  │
│  │ p@example.fr               │  │
│  └────────────────────────────┘  │
│  ...                             │
└──────────────────────────────────┘
```

---

## 4. Modal Ajout / Édition

### 4.1 Champs du formulaire

| Champ | Type | Requis | Validation | Description |
|-------|------|--------|------------|-------------|
| Recherche personne | Autocomplete | Oui | Min 2 caractères | Recherche par nom, prénom ou n° licence |
| N° Licence | Text (readonly) | Oui | Rempli par l'autocomplete | Matric de la personne sélectionnée |
| Nom affiché | Text (readonly) | - | Rempli par l'autocomplete | Prénom + Nom pour confirmation visuelle |
| Saison | Text (readonly) | Oui | Saison du contexte | Pré-rempli, non modifiable |
| Compétition | Select groupé | Non | null ou code valide | Compétitions du contexte groupées par section, avec option "National (sans compétition)" |
| Ordre | Number | Oui | Entier 1-99 | Ordre de priorité du RC |

### 4.2 Recherche personne (Autocomplete)

Utilise le composant partagé `AdminPlayerAutocomplete.vue` (dans `components/admin/`) qui appelle l'endpoint `GET /admin/operations/autocomplete/players?q={query}`.

**Composant :**
```vue
<AdminPlayerAutocomplete
  v-model="selectedPlayer"
  :placeholder="t('common.search_player_placeholder')"
/>
```

**Comportement :**
- Déclenché à partir de 2 caractères
- Debounce de 300ms
- Recherche mono-mot : par n° licence, nom ou prénom (LIKE)
- Recherche multi-mots : "nom prenom" ou "prenom nom" — split sur espace, croisement `(Nom LIKE word1 AND Prenom LIKE word2) OR (Nom LIKE word2 AND Prenom LIKE word1)`
- Affiche les résultats sous forme de dropdown : nom, prénom + label (club)
- La sélection émet `update:modelValue` avec l'objet `PlayerAutocomplete`

**Type partagé** (dans `types/index.ts`) :
```typescript
interface PlayerAutocomplete {
  matric: number
  nom: string
  prenom: string
  naissance: string | null
  numeroClub: string | null
  club: string | null
  label: string  // "NOM Prenom (12345) - Club Name"
}
```

### 4.3 Sélecteur de compétition groupé

Le sélecteur de compétition dans le formulaire affiche les compétitions du contexte de travail (saison courante), groupées par section (`kp_groupe.section`). Chaque groupe de section est un `<optgroup>`.

```html
<select>
  <option value="">National (sans compétition)</option>
  <optgroup label="International">
    <option value="ICF-A">ICF-A - ICF Poule A</option>
  </optgroup>
  <optgroup label="National">
    <option value="N1H-A">N1H-A - Nationale 1 Hommes (Poule A)</option>
    <option value="N1F">N1F - Nationale 1 Femmes</option>
  </optgroup>
  <optgroup label="Régional">
    <option value="REG1">REG1 - Régional 1</option>
  </optgroup>
</select>
```

**Données nécessaires :** Les compétitions sont déjà chargées dans `workContext.competitions` avec leur `codeRef` (groupe) et la section associée via `workContext.groups`.

Ce composant sera un nouveau composant réutilisable : `AdminCompetitionGroupedSelect.vue`.

### 4.4 Comportement du formulaire

**Mode Ajout :**
- Champ "Recherche" vide
- Champ "Ordre" pré-rempli à 1
- Champ "Saison" pré-rempli avec la saison du contexte
- Champ "Compétition" sur "National (sans compétition)" par défaut
- Bouton : "Ajouter"

**Mode Édition :**
- Champ "Recherche" pré-rempli avec le nom de la personne actuelle
- Champs Licence et Nom remplis
- Tous les champs pré-remplis avec les valeurs actuelles
- Boutons : "Enregistrer" + "Annuler"

---

## 5. Suppression

### 5.1 Suppression individuelle

Pas de bouton de suppression individuel par ligne. La suppression se fait uniquement via la sélection multiple + bouton de suppression en masse dans la toolbar.

### 5.2 Suppression en masse

1. Sélectionner un ou plusieurs RC via les checkboxes
2. Cliquer sur le bouton "Supprimer" dans la toolbar
3. Modal de confirmation (AdminConfirmModal) :
   - Titre : "Supprimer les RC"
   - Message : "Êtes-vous sûr de vouloir supprimer {count} responsable(s) de compétition ?"
4. Suppression via `DELETE /admin/rc` avec body `{ ids: [1, 2, 3] }`

### 5.3 Pas de vérification cascade

La table `kp_rc` n'a pas de dépendances enfant. La suppression est directe.

---

## 6. Copie des RC

### 6.1 Fonctionnalité

Copie tous les RC d'une saison source vers une saison cible. Les doublons (même Code_saison + Code_competition + Matric) sont ignorés.

### 6.2 Interface

Bouton "Copier les RC" intégré dans la toolbar (slot `#after-search`), visible uniquement pour profil ≤ 2. Le clic ouvre une modal de copie.

**Modal de copie :**
- **Saison source** : Select dropdown avec les saisons disponibles (courante + 2 précédentes)
- **Saison cible** : Select dropdown avec toutes les saisons (la saison source est désactivée)
- **Note d'avertissement** : "Cette opération copie tous les RC de la saison source vers la saison cible (les doublons sont ignorés)."
- **Boutons** : "Annuler" + "Copier" (désactivé si source ou cible vide)

### 6.3 Confirmation

Avant la copie, afficher un modal de confirmation :
- Titre : "Copier les RC"
- Message : "Copier tous les RC de la saison {source} vers la saison {cible} ?"

### 6.4 Résultat

Après la copie, afficher un toast de succès :
- "{copied} RC copiés ({skipped} ignorés)"

### 6.5 Endpoint

Réutilise l'endpoint existant : `POST /admin/operations/seasons/copy-rc`

```typescript
// Request
{ sourceCode: string, targetCode: string }

// Response
{ message: string, copied: number, skipped: number }
```

---

## 7. Endpoints API2

### 7.1 Lecture

| Méthode | Endpoint | Description | Paramètres |
|---------|----------|-------------|------------|
| GET | `/admin/rc` | Liste des RC pour une saison | `?season={code}` (requis), `?competitions={codes}` (optionnel, virgule-séparés) |

**Réponse GET /admin/rc :**
```json
{
  "items": [
    {
      "id": 1,
      "competitionCode": "N1H-A",
      "competitionLabel": "Nationale 1 Hommes - Poule A",
      "season": "2026",
      "ordre": 1,
      "matric": 12345,
      "nom": "DUPONT",
      "prenom": "Jean",
      "club": "3512",
      "email": "jean.dupont@example.fr"
    }
  ],
  "total": 24
}
```

**Notes :**
- `competitionCode` vaut `null` pour un RC national sans compétition (remplace "- CNA -")
- `competitionLabel` affiche "National (sans compétition)" quand `competitionCode` est `null`
- `email` provient de `kp_user.Mail` (jointure sur `kp_user.Code = kp_rc.Matric`)
- Les résultats sont triés par `Code_competition ASC, Ordre ASC`

### 7.2 Écriture

| Méthode | Endpoint | Description | Body |
|---------|----------|-------------|------|
| POST | `/admin/rc` | Ajouter un RC | `{ season, competitionCode, matric, ordre }` |
| PUT | `/admin/rc/{id}` | Modifier un RC | `{ competitionCode, matric, ordre }` |
| DELETE | `/admin/rc` | Supprimer des RC | `{ ids: [1, 2, 3] }` |

**POST /admin/rc :**
```typescript
// Request
{
  season: string          // Code saison (depuis le contexte)
  competitionCode: string | null  // null = RC national
  matric: number          // N° licence
  ordre: number           // Ordre de priorité
}

// Response 201
{
  id: number
  message: string
}
```

**PUT /admin/rc/{id} :**
```typescript
// Request
{
  competitionCode: string | null
  matric: number
  ordre: number
}

// Response 200
{
  message: string
}
```

**DELETE /admin/rc :**
```typescript
// Request
{
  ids: number[]
}

// Response 200
{
  deleted: number
  message: string
}
```

### 7.3 Endpoint existant réutilisé

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/admin/operations/autocomplete/players?q={query}` | Recherche joueurs par nom/licence |
| POST | `/admin/operations/seasons/copy-rc` | Copie RC entre saisons |

### 7.4 Codes de retour

| Code | Cas |
|------|-----|
| 200 | Succès (GET, PUT, DELETE) |
| 201 | Création réussie (POST) |
| 400 | Données invalides (matric manquant, ordre invalide) |
| 403 | Profil insuffisant |
| 404 | RC non trouvé (PUT) |
| 409 | Doublon : RC déjà existant (même saison + compétition + matric) |

### 7.5 Logique backend spécifique

**POST (Ajout) :**
1. Valider les champs (matric obligatoire, ordre entier positif)
2. Vérifier l'unicité (Code_saison + Code_competition + Matric)
3. Vérifier que le matric existe dans `kp_licence`
4. Insérer dans `kp_rc`
5. Logger dans le journal d'audit

**PUT (Modification) :**
1. Valider les champs
2. Vérifier l'unicité (exclure l'enregistrement courant)
3. Mettre à jour `kp_rc`
4. Transaction avec rollback si erreur
5. Logger dans le journal d'audit

**DELETE (Suppression) :**
1. Supprimer les enregistrements par IDs
2. Transaction avec rollback si erreur
3. Logger dans le journal d'audit

### 7.6 Migration de la valeur "- CNA -"

Le backend doit gérer la transition :
- **En lecture** : les enregistrements avec `Code_competition = '- CNA -'` sont retournés avec `competitionCode: null`
- **En écriture** : `competitionCode: null` est stocké comme `NULL` en DB (pas "- CNA -")
- **Migration de données** : un script SQL met à jour les valeurs existantes :

```sql
UPDATE kp_rc SET Code_competition = NULL WHERE Code_competition = '- CNA -';
```

Nécessite de modifier la colonne pour accepter NULL :

```sql
ALTER TABLE kp_rc MODIFY Code_competition varchar(10) DEFAULT NULL;
```

---

## 8. Schéma de données

### 8.1 Table `kp_rc`

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| Id | int(11) | Non | Clé primaire auto-incrémentée |
| Code_competition | varchar(10) | Oui | Code compétition (null = RC national sans compétition) |
| Code_saison | varchar(8) | Non | Code saison (ex: "2026") |
| Ordre | int(11) | Non | Ordre de priorité (1 = principal) |
| Matric | int(11) UNSIGNED | Non | N° licence (FK → kp_licence.Matric) |

### 8.2 Contraintes

```sql
ALTER TABLE kp_rc
  ADD CONSTRAINT fk_rc_matric FOREIGN KEY (Matric) REFERENCES kp_licence (Matric);
```

### 8.3 Requête principale

```sql
SELECT rc.Id, rc.Code_competition, rc.Code_saison, rc.Ordre, rc.Matric,
       lc.Nom, lc.Prenom, lc.Numero_club, u.Mail
FROM kp_rc rc
LEFT JOIN kp_licence lc ON rc.Matric = lc.Matric
LEFT JOIN kp_user u ON rc.Matric = u.Code
WHERE rc.Code_saison = ?
ORDER BY rc.Code_competition ASC, rc.Ordre ASC
```

### 8.4 Unicité logique

Un RC est unique par combinaison `(Code_saison, Code_competition, Matric)`. Cette contrainte est vérifiée côté backend avant insertion/modification.

---

## 9. Composants Vue

### 9.1 Structure des fichiers

```
sources/app4/pages/rc/
└── index.vue                          # ✅ Page principale (implémentée)

sources/app4/components/admin/
├── PlayerAutocomplete.vue             # ✅ Recherche joueur (partagé avec Présence)
├── CompetitionGroupedSelect.vue       # ✅ Sélecteur compétition groupé par section
├── CompetitionMultiSelect.vue         # Existant : filtre multi-compétition
├── Toolbar.vue                        # Existant : barre d'outils
├── Modal.vue                          # Existant : modal générique
├── ConfirmModal.vue                   # Existant : modal de confirmation
└── Pagination.vue                     # Existant : pagination
```

### 9.2 Composants réutilisés

| Composant | Usage |
|-----------|-------|
| `AdminWorkContextSummary` | Rappel contexte (saison + périmètre) |
| `AdminToolbar` | Barre d'outils : slots `#before-search` (filtre compétitions) et `#after-search` (bouton copier) |
| `AdminCompetitionMultiSelect` | Filtre multi-compétition (dans dropdown inline flottant, slot `#before-search` de la toolbar) |
| `AdminModal` | Modal ajout/édition de RC + modal copie RC |
| `AdminConfirmModal` | Confirmation de suppression |
| `AdminCardList` / `AdminCard` | Vue mobile en cartes |

### 9.3 Nouveau composant : AdminCompetitionGroupedSelect

Sélecteur `<select>` avec `<optgroup>` par section, affichant les compétitions du contexte de travail.

**Props :**
- `modelValue: string | null` — code compétition sélectionné (null = national)

**Events :**
- `update:modelValue` — émis lors du changement de sélection

**Données :** Utilise `workContext.competitions` et `workContext.groups` pour construire la structure groupée.

### 9.4 Dépendance au contexte de travail

Cette page dépend du `workContextStore` :
- **Saison** : détermine les RC affichés et la saison pour l'ajout
- **Périmètre (compétitions)** : utilisé par `CompetitionMultiSelect` pour filtrer les RC
- Le composant `WorkContextSummary` affiche le contexte en haut de page
- Le watch sur `workContext.initialized` et `workContext.season` déclenche le rechargement

---

## 10. Menu de Navigation

### 10.1 Emplacement

La page RC n'est **pas ajoutée au menu principal**. Elle est accessible uniquement depuis :
1. La page Compétitions (`/competitions`) via le lien RC existant (icône `heroicons:users-solid`)
2. Navigation directe par URL `/rc`

### 10.2 Lien depuis la page Compétitions

Le lien existant dans `competitions/index.vue` doit être mis à jour pour pointer vers la nouvelle page au lieu du legacy PHP :

```typescript
// Avant (legacy)
const getRcUrl = (competition: AdminCompetition) => {
  return `/admin/GestionRC.php?competition=${competition.code}`
}

// Après (app4)
const getRcUrl = (competition: AdminCompetition) => {
  return `/rc?competition=${competition.code}`
}
```

### 10.3 Paramètre URL `competition`

Quand la page RC est ouverte avec `?competition={code}` :
- Le filtre CompetitionMultiSelect est initialisé avec uniquement cette compétition cochée
- L'utilisateur peut ensuite modifier le filtre pour afficher d'autres compétitions

---

## 11. Traductions i18n

### 11.1 Clés françaises (`fr.json`)

```json
{
  "rc": {
    "title": "Responsables de Compétition",
    "add": "Ajouter un RC",
    "edit": "Modifier le RC",
    "no_national_competition": "National (sans compétition)",
    "field": {
      "competition": "Compétition",
      "ordre": "Ordre",
      "search_person": "Rechercher une personne",
      "search_placeholder": "Nom, prénom ou n° licence",
      "licence": "N° Licence",
      "name": "Nom",
      "email": "Email",
      "season": "Saison"
    },
    "delete_confirm_title": "Supprimer les RC",
    "delete_confirm_message": "Êtes-vous sûr de vouloir supprimer {count} responsable(s) de compétition ?",
    "copy_title": "Copier les RC",
    "copy_source": "Saison source",
    "copy_target": "Saison cible",
    "copy_button": "Copier les RC",
    "copy_help": "Cette opération copie tous les RC de la saison source vers la saison cible (les doublons sont ignorés).",
    "copy_confirm_message": "Copier tous les RC de la saison {source} vers la saison {target} ?",
    "copy_success": "{copied} RC copiés ({skipped} ignorés)",
    "copy_error": "Erreur lors de la copie des RC",
    "added": "RC ajouté.",
    "updated": "RC modifié.",
    "deleted": "{count} RC supprimé(s).",
    "duplicate_error": "Ce RC existe déjà pour cette compétition et cette saison.",
    "total": "Total : {count} RC",
    "no_results": "Aucun responsable de compétition trouvé.",
    "search": "Rechercher par nom, prénom ou licence"
  }
}
```

### 11.2 Clés anglaises (`en.json`)

```json
{
  "rc": {
    "title": "Competition Officials",
    "add": "Add an official",
    "edit": "Edit official",
    "no_national_competition": "National (no competition)",
    "field": {
      "competition": "Competition",
      "ordre": "Order",
      "search_person": "Search a person",
      "search_placeholder": "Name, first name or licence #",
      "licence": "Licence #",
      "name": "Name",
      "email": "Email",
      "season": "Season"
    },
    "delete_confirm_title": "Delete officials",
    "delete_confirm_message": "Are you sure you want to delete {count} competition official(s)?",
    "copy_title": "Copy officials",
    "copy_source": "Source season",
    "copy_target": "Target season",
    "copy_button": "Copy officials",
    "copy_help": "This operation copies all officials from the source season to the target season (duplicates are skipped).",
    "copy_confirm_message": "Copy all officials from season {source} to season {target}?",
    "copy_success": "{copied} officials copied ({skipped} skipped)",
    "copy_error": "Error copying officials",
    "added": "Official added.",
    "updated": "Official updated.",
    "deleted": "{count} official(s) deleted.",
    "duplicate_error": "This official already exists for this competition and season.",
    "total": "Total: {count} officials",
    "no_results": "No competition officials found.",
    "search": "Search by name, first name or licence"
  }
}
```

---

## 12. Sécurité

### 12.1 Contrôle d'accès

| Opération | Profil requis | Rôle Symfony |
|-----------|--------------|--------------|
| Lecture | ≤ 4 | ROLE_MODERATOR |
| Ajout | ≤ 2 | ROLE_ADMIN |
| Modification | ≤ 2 | ROLE_ADMIN |
| Suppression | ≤ 1 | ROLE_SUPER_ADMIN |
| Copie RC | ≤ 2 | ROLE_ADMIN |

### 12.2 Validation backend

- Matric obligatoire et existant dans `kp_licence`
- Ordre entier positif (1-99)
- Code_competition null ou existant dans `kp_competition` pour la saison
- Unicité (Code_saison, Code_competition, Matric)
- Saison existante dans `kp_saison`

### 12.3 Intégrité des données

- Transaction pour ajout/modification/suppression
- Rollback automatique en cas d'erreur
- Vérification d'unicité avant insertion

### 12.4 Journal d'audit

Toutes les opérations sont loguées dans `kp_journal` :

| Action | Journal |
|--------|---------|
| Ajout | "Ajout Rc" avec matric |
| Modification | "Modif Rc" avec Id |
| Suppression | "Suppression Rc" avec liste d'IDs |
| Copie | "Copie RC" avec détails source/cible/compteurs |

---

## 13. Notes de migration

### 13.1 Différences avec le legacy

| Aspect | Legacy (PHP) | App4 (Nuxt) |
|--------|-------------|-------------|
| Layout | 2 colonnes (table + formulaire latéral) | Table + Modal |
| Saison | Sélecteur de saison dédié | Saison du contexte de travail |
| Filtres | 3 filtres (niveau, type, compétition) + filtre client | Dropdown inline CompetitionMultiSelect dans la toolbar |
| Recherche | Pas de recherche textuelle | Recherche par nom/prénom/licence |
| "- CNA -" | Valeur littérale en DB | null en DB, "National (sans compétition)" en UI |
| Suppression | Individuelle uniquement | Sélection multiple + suppression en masse |
| Responsive | Non | Oui (table → cartes) |
| Copie RC | Formulaire latéral | Bouton dans la toolbar + modal dédiée |
| Compétition (formulaire) | Liste plate | Groupée par section |

### 13.2 Migration de données

```sql
-- Rendre la colonne nullable
ALTER TABLE kp_rc MODIFY Code_competition varchar(10) DEFAULT NULL;

-- Migrer les valeurs "- CNA -" vers NULL
UPDATE kp_rc SET Code_competition = NULL WHERE Code_competition = '- CNA -';
```

### 13.3 Dépendances

- `workContextStore` pour la saison et les compétitions
- Endpoint existant `/admin/operations/autocomplete/players` pour la recherche
- Endpoint existant `/admin/operations/seasons/copy-rc` pour la copie
- Nouveaux endpoints `/admin/rc` (CRUD) à créer dans API2
- Nouveau composant `AdminCompetitionGroupedSelect.vue` à créer

### 13.4 Endpoint API2 à créer

Nouveau controller : `AdminRcController.php` dans `sources/api2/src/Controller/`

Méthodes :
- `list()` — GET /admin/rc
- `create()` — POST /admin/rc
- `update()` — PUT /admin/rc/{id}
- `delete()` — DELETE /admin/rc

---

**Document créé le** : 09 février 2026
**Dernière mise à jour** : 14 février 2026
**Statut** : ✅ Implémenté (Team Mode)
**Auteur** : Claude Code
