# Spécification - Page Groupes

## Statut : ✅ IMPLÉMENTÉ (2026-02-09)

## 1. Vue d'ensemble

Page d'administration des groupes de compétitions. Les groupes organisent la hiérarchie des compétitions en catégories (ex : N1H = National 1 Hommes, N2F = National 2 Femmes, INT = International). Chaque compétition est rattachée à un groupe via la clé étrangère `kp_competition.Code_ref → kp_groupe.Groupe`.

**Route** : `/groups`

**Accès** :
- Profil ≤ 2 : Lecture, Ajout, Modification, Réordonnancement
- Profil = 1 : Suppression (Super Admin uniquement)

**Page PHP Legacy** : `GestionGroupe.php` + `GestionGroupe.tpl` + `GestionGroupe.js`

**Implémentation Nuxt** : `sources/app4/pages/groups/index.vue`

**Contexte de travail** : Non applicable (page globale, non liée à une saison ou compétition)

---

## 2. Fonctionnalités

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Liste des groupes avec tri par section et ordre | ≤ 2 | Essentielle | ✅ Conserver |
| 2 | Filtrage par section (International, National, etc.) | ≤ 2 | Amélioration | ✅ Ajouter |
| 3 | Recherche textuelle (code, libellé) | ≤ 2 | Amélioration | ✅ Ajouter |
| 4 | Ajout d'un groupe (modal) | ≤ 2 | Essentielle | ✅ Conserver |
| 5 | Modification d'un groupe (modal) | ≤ 2 | Essentielle | ✅ Conserver |
| 6 | Suppression d'un groupe (avec vérification cascade) | = 1 | Essentielle | ✅ Conserver |
| 7 | Réordonnancement (monter/descendre dans la même section) | ≤ 2 | Essentielle | ✅ Conserver + descendre |
| 8 | Avertissement lors du changement de code groupe | ≤ 2 | Essentielle | ✅ Conserver |

### Améliorations par rapport au legacy

| # | Amélioration | Description |
|---|--------------|-------------|
| 1 | Modal au lieu de formulaire latéral | Formulaire d'ajout/édition dans une modal au lieu d'un panneau droit |
| 2 | Filtre par section | Dropdown de filtre pour n'afficher qu'une section |
| 3 | Recherche | Recherche textuelle dans le code et le libellé |
| 4 | Descendre dans l'ordre | Legacy ne permet que de monter ; ajouter aussi descendre |
| 5 | Compteur de compétitions | Afficher le nombre de compétitions par groupe |

---

## 3. Structure de la Page

### 3.1 Vue Desktop

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  Gestion des Groupes                                                         │
├─────────────────────────────────────────────────────────────────────────────┤
│  [🔍 Recherche...          ] [Section: Toutes ▼]              [+ Ajouter]   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                               │
│  ── International ──────────────────────────────────────────────────────────  │
│  │ # │ Ordre │ Niveau │ Code  │ Libellé FR       │ Libellé EN       │ Comp│ │
│  │ 1 │ ↑ ↓ 1 │  INT   │ INT   │ International    │ International    │  3  │ │
│  │ 2 │ ↑ ↓ 2 │  INT   │ ICF   │ ICF              │ ICF              │  5  │ │
│                                                                               │
│  ── National ───────────────────────────────────────────────────────────────  │
│  │ 3 │ ↑ ↓ 1 │  NAT   │ N1H   │ Nationale 1 H    │ National 1 Men   │ 12 │ │
│  │ 4 │ ↑ ↓ 2 │  NAT   │ N1F   │ Nationale 1 F    │ National 1 Women │  8 │ │
│  │ 5 │ ↑ ↓ 3 │  NAT   │ N2H   │ Nationale 2 H    │ National 2 Men   │ 10 │ │
│  ...                                                                          │
│                                                                               │
│  ── Régional ───────────────────────────────────────────────────────────────  │
│  │ 8 │ ↑ ↓ 1 │  REG   │ REG   │ Régional         │ Regional         │  6 │ │
│  ...                                                                          │
│                                                                               │
│  Total : 15 groupes                                                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Actions par ligne :**
- Clic sur la ligne : ouvre la modal d'édition (profil ≤ 2)
- Boutons ↑/↓ : réordonnancer au sein de la section
- Bouton supprimer (icône corbeille) : visible uniquement profil = 1

### 3.2 Vue Mobile (cartes)

```
┌──────────────────────────────────┐
│  Gestion des Groupes             │
├──────────────────────────────────┤
│  [🔍 Recherche...              ] │
│  [Section: Toutes ▼] [+ Ajouter]│
├──────────────────────────────────┤
│  ── International ──             │
│  ┌────────────────────────────┐  │
│  │ INT - International        │  │
│  │ Niveau: INT │ Ordre: 1     │  │
│  │ EN: International          │  │
│  │ 3 compétitions    [↑][↓][🗑]│  │
│  └────────────────────────────┘  │
│  ┌────────────────────────────┐  │
│  │ ICF - ICF                  │  │
│  │ ...                        │  │
│  └────────────────────────────┘  │
│  ...                             │
└──────────────────────────────────┘
```

---

## 4. Modal Ajout / Édition

### 4.1 Champs du formulaire

| Champ | Type | Requis | Validation | Description |
|-------|------|--------|------------|-------------|
| Section | Select | Oui | Valeurs: 1,2,3,4,5,100 | Catégorie du groupe |
| Code niveau | Select | Oui | Valeurs: REG, NAT, INT | Niveau de compétition |
| Ordre | Number | Oui | Entier 1-99999 | Position d'affichage dans la section |
| Code groupe | Text | Oui | Max 10 caractères, unique | Identifiant unique (ex: N1H) |
| Libellé FR | Text | Oui | Max 40 caractères | Nom français du groupe |
| Libellé EN | Text | Non | Max 255 caractères | Nom anglais (optionnel) |

### 4.2 Options du select "Section"

| Valeur | Libellé FR | Libellé EN |
|--------|-----------|------------|
| 1 | International | International |
| 2 | National | National |
| 3 | Régional | Regional |
| 4 | Tournoi | Tournament |
| 5 | Continental | Continental |
| 100 | Divers | Miscellaneous |

### 4.3 Comportement du formulaire

**Mode Ajout :**
- Le champ "Ordre" est pré-rempli avec `max(ordre) + 1` de la section sélectionnée
- Le champ "Code groupe" est vide et éditable
- Bouton : "Ajouter"

**Mode Édition :**
- Tous les champs sont pré-remplis avec les valeurs actuelles
- Le champ "Code groupe" est éditable (avec avertissement si modifié)
- Si le code groupe est modifié : afficher un avertissement expliquant que toutes les compétitions référençant ce code seront mises à jour
- Boutons : "Enregistrer" + "Annuler"

### 4.4 Avertissement changement de code

Lorsque le code groupe est modifié en mode édition, afficher une alerte (type warning) dans la modal :

> ⚠️ **Attention** : Le changement du code groupe mettra à jour automatiquement toutes les compétitions référençant le code actuel "{oldCode}" vers le nouveau code "{newCode}".

L'utilisateur doit confirmer avant la sauvegarde.

---

## 5. Suppression

### 5.1 Vérification cascade

Avant suppression, le backend vérifie si des compétitions sont liées au groupe (`kp_competition.Code_ref = kp_groupe.Groupe`).

**Si des compétitions existent :**
- Retour HTTP 409 (Conflict)
- Message : "Impossible de supprimer le groupe {code}. {n} compétition(s) liée(s) : {liste des codes}."
- La modal affiche le message d'erreur

**Si aucune compétition :**
- Suppression effectuée
- Réordonnancement automatique des groupes restants dans la même section

### 5.2 Confirmation

Modal de confirmation standard (AdminConfirmModal) :
- Titre : "Supprimer le groupe"
- Message : "Êtes-vous sûr de vouloir supprimer le groupe {code} - {libellé} ?"

---

## 6. Réordonnancement

### 6.1 Comportement

- Les boutons ↑ et ↓ ne déplacent le groupe qu'au sein de sa section
- Le bouton ↑ est masqué pour le premier groupe de la section (ordre minimum)
- Le bouton ↓ est masqué pour le dernier groupe de la section (ordre maximum)
- L'opération échange les valeurs `ordre` entre les deux groupes adjacents

### 6.2 Endpoint

`PATCH /admin/groups/{id}/reorder` avec body `{ "direction": "up" | "down" }`

---

## 7. Endpoints API2

### 7.1 Lecture

| Méthode | Endpoint | Description | Paramètres |
|---------|----------|-------------|------------|
| GET | `/admin/groups` | Liste tous les groupes | `?section={id}` (optionnel) |

**Réponse GET /admin/groups :**
```json
{
  "groups": [
    {
      "id": 1,
      "section": 2,
      "sectionName": "National",
      "ordre": 1,
      "codeNiveau": "NAT",
      "groupe": "N1H",
      "libelle": "Nationale 1 Hommes",
      "libelleEn": "National 1 Men",
      "competitionCount": 12
    }
  ],
  "total": 15
}
```

### 7.2 Écriture

| Méthode | Endpoint | Description | Body |
|---------|----------|-------------|------|
| POST | `/admin/groups` | Ajouter un groupe | `{ section, ordre, codeNiveau, groupe, libelle, libelleEn }` |
| PUT | `/admin/groups/{id}` | Modifier un groupe | `{ section, ordre, codeNiveau, groupe, libelle, libelleEn }` |
| DELETE | `/admin/groups/{id}` | Supprimer un groupe | - |
| PATCH | `/admin/groups/{id}/reorder` | Réordonner | `{ "direction": "up" \| "down" }` |

### 7.3 Codes de retour

| Code | Cas |
|------|-----|
| 200 | Succès (GET, PUT, PATCH) |
| 201 | Création réussie (POST) |
| 204 | Suppression réussie (DELETE) |
| 400 | Données invalides (validation) |
| 403 | Profil insuffisant |
| 404 | Groupe non trouvé |
| 409 | Conflit : compétitions liées (DELETE) |
| 422 | Code groupe déjà existant (POST/PUT) |

### 7.4 Logique backend spécifique

**POST (Ajout) :**
1. Valider les champs
2. Vérifier l'unicité du code groupe
3. Incrémenter `ordre` de tous les groupes avec `ordre >= new_ordre` dans la même section
4. Insérer le nouveau groupe
5. Logger dans le journal d'audit

**PUT (Modification) :**
1. Valider les champs
2. Si le code groupe change :
   - Vérifier l'unicité du nouveau code
   - Désactiver temporairement les FK (`SET FOREIGN_KEY_CHECKS = 0`)
   - Mettre à jour `kp_competition.Code_ref` (ancien code → nouveau code)
   - Réactiver les FK
3. Mettre à jour le groupe
4. Transaction avec rollback si erreur
5. Logger dans le journal d'audit

**DELETE (Suppression) :**
1. Vérifier l'absence de compétitions liées
2. Si compétitions liées : retourner 409 avec la liste
3. Sinon : supprimer le groupe
4. Réordonner les groupes restants (décrémenter `ordre` des groupes suivants)
5. Transaction avec rollback si erreur
6. Logger dans le journal d'audit

---

## 8. Schéma de données

### 8.1 Table `kp_groupe`

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| id | int(11) | Non | Clé primaire auto-incrémentée |
| section | int(11) | Non | Catégorie (1=International, 2=National, 3=Régional, 4=Tournoi, 5=Continental, 100=Divers) |
| ordre | int(11) | Non | Ordre d'affichage dans la section |
| Code_niveau | char(3) | Non | Niveau : REG, NAT, INT (défaut: NAT) |
| Groupe | varchar(10) | Non | Code unique du groupe (ex: N1H) |
| Libelle | mediumtext | Non | Nom français |
| Libelle_en | varchar(255) | Oui | Nom anglais (ajouté 2026-01-19) |
| Calendar | text | Oui | Configuration calendrier (non utilisé dans l'UI) |

### 8.2 Contrainte de clé étrangère

```sql
ALTER TABLE kp_competition
  ADD CONSTRAINT fk_competitions_groupes
  FOREIGN KEY (Code_ref) REFERENCES kp_groupe (Groupe)
```

`kp_competition.Code_ref` (varchar 10) → `kp_groupe.Groupe`

---

## 9. Composants Vue

### 9.1 Structure des fichiers

```
sources/app4/pages/groups/
└── index.vue              # Page principale
```

### 9.2 Composants réutilisés

| Composant | Usage |
|-----------|-------|
| `AdminToolbar` | Barre de recherche + bouton Ajouter |
| `AdminModal` | Modal ajout/édition de groupe |
| `AdminConfirmModal` | Confirmation de suppression |

### 9.3 Pas de dépendance au contexte de travail

Cette page est **globale** : elle n'utilise pas `workContextStore` et ne dépend ni d'une saison ni d'un périmètre de compétition. Elle se comporte comme la page Événements (`/events`).

---

## 10. Menu de Navigation

### 10.1 Emplacement

Ajouter "Groupes" dans la section **Administration** du menu (`Header.vue`), entre "Clubs" et "Utilisateurs".

### 10.2 Définition

| Propriété | Valeur |
|-----------|--------|
| Label FR | Groupes |
| Label EN | Groups |
| Route | `/groups` |
| Icône | `heroicons:rectangle-group` |
| Profil min | ≤ 2 |

### 10.3 Section Administration mise à jour

| Menu | Route | Profile min |
|------|-------|-------------|
| Événements | `/events` | ≤ 2 |
| Athlètes | `/athletes` | ≤ 8 |
| Clubs | `/clubs` | ≤ 9 |
| **Groupes** | **`/groups`** | **≤ 2** |
| Utilisateurs | `/users` | ≤ 3 |
| Opérations | `/operations` | = 1 |

---

## 11. Traductions i18n

### 11.1 Clés françaises (`fr.json`)

```json
{
  "menu": {
    "groups": "Groupes"
  },
  "groups": {
    "title": "Gestion des Groupes",
    "add": "Ajouter un groupe",
    "edit": "Modifier le groupe",
    "delete_confirm_title": "Supprimer le groupe",
    "delete_confirm_message": "Êtes-vous sûr de vouloir supprimer le groupe {code} - {label} ?",
    "delete_error_competitions": "Impossible de supprimer le groupe {code}. {count} compétition(s) liée(s) : {list}.",
    "code_change_warning": "Le changement du code groupe mettra à jour automatiquement toutes les compétitions référençant le code actuel \"{oldCode}\" vers le nouveau code \"{newCode}\".",
    "field": {
      "section": "Section",
      "code_niveau": "Niveau",
      "ordre": "Ordre",
      "groupe": "Code groupe",
      "libelle": "Libellé FR",
      "libelle_en": "Libellé EN"
    },
    "section": {
      "1": "International",
      "2": "National",
      "3": "Régional",
      "4": "Tournoi",
      "5": "Continental",
      "100": "Divers"
    },
    "niveau": {
      "REG": "Régional",
      "NAT": "National",
      "INT": "International"
    },
    "all_sections": "Toutes les sections",
    "competition_count": "{count} compétition(s)",
    "total_groups": "Total : {count} groupe(s)",
    "added": "Groupe ajouté.",
    "updated": "Groupe modifié.",
    "deleted": "Groupe supprimé.",
    "reordered": "Ordre modifié."
  }
}
```

### 11.2 Clés anglaises (`en.json`)

```json
{
  "menu": {
    "groups": "Groups"
  },
  "groups": {
    "title": "Groups Management",
    "add": "Add a group",
    "edit": "Edit group",
    "delete_confirm_title": "Delete group",
    "delete_confirm_message": "Are you sure you want to delete group {code} - {label}?",
    "delete_error_competitions": "Cannot delete group {code}. {count} linked competition(s): {list}.",
    "code_change_warning": "Changing the group code will automatically update all competitions referencing the current code \"{oldCode}\" to the new code \"{newCode}\".",
    "field": {
      "section": "Section",
      "code_niveau": "Level",
      "ordre": "Order",
      "groupe": "Group code",
      "libelle": "Label FR",
      "libelle_en": "Label EN"
    },
    "section": {
      "1": "International",
      "2": "National",
      "3": "Regional",
      "4": "Tournament",
      "5": "Continental",
      "100": "Miscellaneous"
    },
    "niveau": {
      "REG": "Regional",
      "NAT": "National",
      "INT": "International"
    },
    "all_sections": "All sections",
    "competition_count": "{count} competition(s)",
    "total_groups": "Total: {count} group(s)",
    "added": "Group added.",
    "updated": "Group updated.",
    "deleted": "Group deleted.",
    "reordered": "Order updated."
  }
}
```

---

## 12. Sécurité

### 12.1 Contrôle d'accès

| Opération | Profil requis | Rôle Symfony |
|-----------|--------------|--------------|
| Lecture | ≤ 2 | ROLE_ADMIN |
| Ajout | ≤ 2 | ROLE_ADMIN |
| Modification | ≤ 2 | ROLE_ADMIN |
| Réordonnancement | ≤ 2 | ROLE_ADMIN |
| Suppression | = 1 | ROLE_SUPER_ADMIN |

### 12.2 Validation backend

- Code groupe unique (unicité vérifiée côté API)
- Section valide (1, 2, 3, 4, 5, 100)
- Code_niveau valide (REG, NAT, INT)
- Ordre entier positif
- Libellé non vide, max 40 caractères
- Libellé_en max 255 caractères

### 12.3 Intégrité des données

- Transaction pour modification de code groupe (mise à jour cascade `kp_competition`)
- Transaction pour suppression (suppression + réordonnancement)
- Vérification cascade avant suppression (FK `kp_competition.Code_ref`)

### 12.4 Journal d'audit

Toutes les opérations (ajout, modification, suppression, réordonnancement) sont loguées via `utyJournal()` dans la table `kp_journal`.

---

## 13. Notes de migration

### 13.1 Différences avec le legacy

| Aspect | Legacy (PHP) | App4 (Nuxt) |
|--------|-------------|-------------|
| Layout | 2 colonnes (table + formulaire) | Table + Modal |
| Réordonnancement | Monter uniquement | Monter + Descendre |
| Recherche | Aucune | Recherche textuelle |
| Filtre section | Aucun | Dropdown de filtre |
| Compteur compétitions | Non affiché | Affiché par groupe |
| Responsive | Non | Oui (table → cartes) |
| Validation code unique | Côté serveur uniquement | Client + Serveur |

### 13.2 Champ Calendar

Le champ `Calendar` de `kp_groupe` n'est **pas exposé** dans l'interface. Il n'est pas utilisé dans le legacy non plus (rempli à NULL pour tous les groupes). Il est conservé dans la base de données mais ignoré dans le formulaire.

### 13.3 Dépendances

- Aucune dépendance sur `workContextStore` (page globale)
- Endpoint API2 `AdminGroupsController` à créer
- Réutilise les composants admin existants

---

**Document créé le** : 09 février 2026
**Dernière mise à jour** : 09 février 2026
**Statut** : ✅ IMPLÉMENTÉ
**Auteur** : Claude Code
