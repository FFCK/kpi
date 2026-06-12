# Spécification - Page Pool Arbitres

## 1. Vue d'ensemble

La page **Pool Arbitres** permet de gérer un référentiel global d'arbitres, regroupés en groupes (typiquement des nations : FRA, GER, ESP…). Contrairement aux équipes ordinaires, le pool **n'est pas rattaché à une saison ni au contexte de travail** : il est accessible à tout moment, quel que soit le contexte sélectionné.

Ce pool alimente l'autocomplete d'attribution des arbitres dans la page Matchs (groupe « Pool Arbitres » de `/admin/games/autocomplete/referees`).

**Route** : `/referees-pool`

**Accès** :
- Profil ≤ 2 (Administrateur) uniquement — lecture et écriture.
- Entrée de menu : **Administration → Référentiels → Pool Arbitres**.

**Stockage** : les groupes et arbitres réutilisent les tables de compétition avec une « compétition » spéciale fixe :
- Groupe = ligne `kp_competition_equipe` avec `Code_compet = 'POOL'` et `Code_saison = '1000'`.
- Arbitre = ligne `kp_competition_equipe_joueur` rattachée à un groupe.
- Statut d'arbitrage = ligne `kp_arbitre` (par `Matric`).

**Pages PHP Legacy** : branche `POOL` de `GestionEquipe.php` et `GestionEquipeJoueur.php`.

---

## 2. Règles métier

### 2.1 Licenciés vs non-licenciés

Le matricule sépare deux catégories d'arbitres :

| Catégorie | Matricule | Identité | Niveau d'arbitrage | Rattachement groupe |
|-----------|-----------|----------|--------------------|---------------------|
| **Licencié** | `< 2 000 000` | Lecture seule (vient de l'import fédéral) | **Lecture seule** (géré par l'import des licenciés) | Ajout / retrait autorisés |
| **Non-licencié** | `≥ 2 000 000` | Éditable | Éditable | Ajout / retrait autorisés |

> ⚠️ Pour un licencié, l'interface ne permet QUE de l'ajouter à un groupe ou de le retirer. Ni son identité ni son niveau d'arbitrage ne sont modifiables ici. Le bouton d'édition est masqué et l'API renvoie **HTTP 403** sur toute tentative de PATCH d'un matricule `< 2 000 000`.

### 2.2 Codes d'arbitrage

Les codes valides et leurs drapeaux `kp_arbitre` (regional, interregional, national, international) + libellé stocké (`arbitre`, char(3)) :

| Code | regional | interregional | national | international | Libellé |
|------|----------|---------------|----------|--------------|---------|
| REG  | O | N | N | N | Reg |
| IR   | N | O | N | N | IR  |
| NAT  | N | N | O | N | Nat |
| INT  | N | N | O | O | Int |
| OTM  | N | N | N | N | OTM |
| JO   | N | N | N | N | JO  |

Le champ **niveau** (`kp_arbitre.niveau`, char(1)) complète le code (ex. `NAT-A`).

### 2.3 Statut dans le pool

Chaque arbitre a un **statut d'appartenance au pool**, indépendant de son niveau d'arbitrage, stocké dans `kp_competition_equipe_joueur.Capitaine` :

| Statut | Valeur | Sens |
|--------|--------|------|
| Arbitre | `A` | Actif dans le pool (valeur par défaut à l'ajout) |
| Inactif | `X` | Conservé dans le pool mais désactivé |

> Ce statut est modifiable **pour tous les arbitres, y compris les licenciés** : il reflète l'appartenance au pool, pas une donnée fédérale. Édition inline (liste déroulante Arbitre / Inactif) ; les lignes inactives sont grisées.

---

## 3. Fonctionnalités

### 3.1 Groupes

| # | Fonctionnalité | Profil | Notes |
|---|----------------|--------|-------|
| 1 | Liste des groupes (master-détail, dépliable) | ≤ 2 | Triés par libellé |
| 2 | Logo de groupe (nation) | ≤ 2 | Fallback `img/Nations/{XXX}.png` si pas de logo |
| 3 | Compteur d'arbitres par groupe | ≤ 2 | |
| 4 | Créer un groupe | ≤ 2 | Crée `kp_equipe` + `kp_competition_equipe`. Club par défaut = `ICF` |
| 5 | Renommer un groupe | ≤ 2 | Unicité du libellé au sein du pool |
| 6 | Supprimer un groupe | ≤ 2 | Détache (supprime) tous ses arbitres puis le groupe |
| 7 | Tout déplier / replier | ≤ 2 | |

### 3.2 Arbitres

| # | Fonctionnalité | Profil | Notes |
|---|----------------|--------|-------|
| 1 | Liste des arbitres d'un groupe | ≤ 2 | Triés par nom, prénom |
| 2 | Indicateur licencié (cadenas) | ≤ 2 | |
| 3 | Ajouter un licencié existant | ≤ 2 | Autocomplete sur `kp_licence` |
| 4 | Créer un arbitre non-licencié | ≤ 2 | Nom/prénom/sexe/naissance + statut. Matricule auto ≥ 2 000 000 |
| 5 | Éditer un non-licencié | ≤ 2 | Identité + statut d'arbitrage. Interdit pour les licenciés |
| 6 | Changer le statut pool (Arbitre / Inactif) | ≤ 2 | Inline, autorisé pour TOUS (licenciés inclus) — voir §2.3 |
| 7 | Retirer un arbitre du groupe | ≤ 2 | Supprime la ligne `kp_competition_equipe_joueur` |

---

## 4. API (Symfony / API Platform)

**Contrôleur** : `sources/api2/src/Controller/AdminRefereesPoolController.php`
**Sécurité** : `#[IsGranted('ROLE_ADMIN')]` (profil ≤ 2)

| Méthode | Route | Description |
|---------|-------|-------------|
| GET    | `/admin/referees-pool` | Liste des groupes + leurs arbitres |
| POST   | `/admin/referees-pool/groups` | Crée un groupe (`libelle`, `codeClub`) |
| PATCH  | `/admin/referees-pool/groups/{id}` | Renomme un groupe (`libelle`) |
| DELETE | `/admin/referees-pool/groups/{id}` | Supprime un groupe + ses arbitres |
| GET    | `/admin/referees-pool/search-licence?q=` | Recherche de licenciés (≥ 2 caractères) |
| POST   | `/admin/referees-pool/groups/{id}/referees` | Ajoute un arbitre (`mode: licence\|manual`) |
| PATCH  | `/admin/referees-pool/groups/{id}/referees/{matric}` | Édite un non-licencié (403 si licencié) |
| PATCH  | `/admin/referees-pool/groups/{id}/referees/{matric}/status` | Change le statut pool (`status: A\|X`), autorisé pour tous |
| DELETE | `/admin/referees-pool/groups/{id}/referees/{matric}` | Retire un arbitre du groupe |

### 4.1 Payloads d'ajout d'arbitre

```jsonc
// Licencié existant
{ "mode": "licence", "matric": 190975 }

// Création manuelle
{
  "mode": "manual",
  "nom": "DOE", "prenom": "John", "sexe": "M",
  "naissance": "1990-05-12",   // YYYY-MM-DD ou DD/MM/YYYY, optionnel
  "arbitre": "NAT",            // '' ou un code valide
  "niveau": "A"
}
```

### 4.2 Notes d'implémentation

- **Saison fixe** : `POOL_SAISON = '1000'`, `POOL_COMPET = 'POOL'`.
- La création manuelle insère une ligne « porteuse » dans `kp_licence` (avec `Origine` = saison active, comme `GestionEquipeJoueur::Add2()`).
- Le matricule des non-licenciés est `MAX(Matric ≥ 2 000 000) + 1`.
- La catégorie d'âge est calculée depuis la saison active et `kp_categorie`.
- Le statut d'arbitrage utilise `REPLACE INTO kp_arbitre`. Un `arbitre = ''` supprime la ligne ; `arbitre = null` (champ absent) la laisse inchangée.

---

## 5. Frontend (app4)

| Fichier | Rôle |
|---------|------|
| `sources/app4/pages/referees-pool/index.vue` | Page master-détail (groupes dépliables + arbitres) |
| `sources/app4/types/referees-pool.ts` | Types TypeScript |
| `sources/app4/components/admin/Header.vue` | Entrée de menu Référentiels (profil ≤ 2) |
| `sources/app4/i18n/locales/{fr,en}.json` | Clés `menu.referees_pool` et bloc `referees_pool_page.*` |

Composants réutilisés : `AdminModal` (création/renommage groupe, ajout/édition arbitre) et `AdminConfirmModal` (suppression groupe, retrait arbitre).

---

## 6. Migration depuis le legacy

| Legacy | app4 |
|--------|------|
| Sélection compétition `POOL` dans `GestionEquipe.php` | Page dédiée, pas de sélecteur |
| `GestionEquipe::Add/Tirage/Remove` (branche POOL) | CRUD groupes |
| `GestionEquipeJoueur::Add/Add2/Remove/Find` (POOL) | Ajout/édition/retrait arbitres |
| Saison `1000` codée en dur | Constante `POOL_SAISON` |

**Améliorations apportées** :
- Interface master-détail unique (plus de navigation entre deux pages).
- Protection explicite des licenciés (lecture seule garantie côté API).
- Permissions resserrées à profil ≤ 2 (référentiel global sensible).
