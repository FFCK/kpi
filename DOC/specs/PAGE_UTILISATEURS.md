# Specification - Page Utilisateurs

## Statut : BROUILLON

## 1. Vue d'ensemble

Page d'administration des utilisateurs accredites du systeme KPI. Permet de lister, creer, modifier et supprimer des comptes utilisateurs avec leurs niveaux de profil et restrictions d'acces (saisons, competitions, evenements, clubs, journees).

**Route** : `/users`

**Acces** :
- Profil <= 4 : Consultation de la liste des utilisateurs
- Profil <= 3 : Creation et modification d'un utilisateur (restreint aux profils >= 5 pour profils 3-4)
- Profil <= 2 : Suppression d'un utilisateur
- Profil 1 : Modification de l'identite (nom) d'un utilisateur, attribution de profils 1-2
- Profils 5 a 10 : **Aucun acces** a la page

**Page PHP Legacy** : `GestionUtilisateur.php` + `GestionUtilisateur.tpl` + `GestionUtilisateur.js`

**Contexte de travail** : Non applicable (page globale, non liee a une saison/competition).

---

## 2. Fonctionnalites

### 2.1 Liste des utilisateurs

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Liste tabulaire des utilisateurs | <= 4 | Table HTML sans pagination | ✅ Conserver + ajouter pagination |
| 2 | Colonnes : identite, licence, fonction, profil, saisons, competitions, evt/journees, clubs | <= 4 | Affichage complet | ✅ Conserver (email et telephone masques dans le tableau pour eviter un export massif, visibles uniquement dans la modale de modification) |
| 3 | Recherche textuelle (nom, licence, email) | <= 4 | Champ "Surligner" | ✅ Remplacer par recherche avec filtrage API |
| 4 | Filtre par profil | <= 4 | Select dropdown "Profils" | ✅ Conserver |
| 5 | Filtre par saison (utilisateurs ayant acces a une saison) | <= 4 | Select dropdown "Saisons" | ✅ Conserver |
| 6 | Case a cocher par ligne pour selection | <= 2 | Checkbox par utilisateur | ✅ Conserver |
| 7 | Bouton modifier (icone crayon) | <= 3 | Icone b_edit.png | ✅ Conserver (ouvre modale) |
| 8 | Bouton supprimer (icone croix) | <= 2 | Icone b_drop.png | ✅ Conserver (modale confirmation) |
| 9 | Suppression en masse | <= 2 | Via checkboxes | ✅ Conserver |
| 10 | Lien mailto vers tous les utilisateurs affiches | <= 4 | `mailto:` avec tous les emails | ❌ Supprimer |
| 11 | Lien vers journal des activites | <= 2 | Lien GestionJournal.php | ✅ Conserver — lien vers page `/activity-log` (voir `PAGE_JOURNAL_ACTIVITE.md`) |
| 12 | Surlignage texte dans le tableau | <= 4 | Plugin jQuery highlight | ❌ Supprimer (remplace par recherche API) |

### 2.2 Creation / Modification d'un utilisateur

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Recherche autocomplete par nom/prenom/licence pour pre-remplir | <= 3 | Autocomplete jQuery vers Autocompl_joueur.php | ✅ Conserver (endpoint /admin/athletes/search) |
| 2 | Champ licence (code utilisateur, readonly apres creation) | <= 3 | Input readonly rempli par autocomplete | ✅ Conserver |
| 3 | Champ identite (nom complet) | 1 | Input text (readonly sauf profil 1) | ✅ Conserver (readonly sauf profil 1) |
| 4 | Champ email | <= 3 | Input text (obligatoire) | ✅ Conserver |
| 5 | Champ telephone | <= 3 | Input text | ✅ Conserver |
| 6 | Champ fonctions | <= 3 | Input text | ✅ Conserver |
| 7 | Selection du profil (dropdown) | <= 3 | Select 1-10 restreint par profil admin | ✅ Conserver (restrictions adaptees) |
| 8 | Champ mot de passe | <= 3 | Input password + checkbox "generer aleatoire" | ⏸ Remplacer par email de reinitialisation |
| 9 | Filtre classique saisons (multi-select) | <= 3 | Multi-select avec "Toutes les saisons" | ✅ Conserver |
| 10 | Filtre classique competitions (multi-select groupe par section) | <= 3 | Multi-select avec optgroup par section | ✅ Conserver — les libelles affiches sont ceux de la saison la plus recente pour chaque code competition |
| 11 | Filtre evenements (multi-select) | <= 2 | Multi-select de tous les evenements | ✅ Conserver |
| 12 | Filtre clubs (codes clubs separes par virgule) | <= 3 | Input text libre | ⏸ Remplacer par autocomplete multi-selection |
| 13 | Filtre journees (IDs de journees) | <= 3 | Input text libre | ✅ Conserver tel quel |
| 14 | Filtre "Aucun" (pas de restrictions) | 1 | Radio button | ❌ Supprimer (utiliser "Toutes les saisons" + "Toutes les competitions") |
| 15 | Filtre "Special" (requete SQL) | 1 | Radio button + input SQL | ❌ Supprimer |
| 16 | Email de confirmation a l'utilisateur | <= 3 | Checkbox + envoi avec mot de passe en clair | ⏸ Remplacer par email de reinitialisation mdp |
| 17 | Email de notification a l'administrateur | <= 3 | Envoi automatique a contact@ | ✅ Conserver — email envoye a contact@kayak-polo.info a chaque creation/modification |
| 18 | Lien vers documentation dans l'email | <= 3 | Checkbox piece jointe Manuel7.pdf | ⏸ Remplacer par un lien vers la documentation en ligne dans l'email de reinitialisation |
| 19 | Message complementaire / standard | <= 3 | Textarea + template message standard | ✅ Conserver — textarea dans la modale pour ajouter un message personnalise a l'email + bouton "Message standard" pre-rempli |
| 20 | Afficher/masquer le formulaire | <= 3 | Toggle jQuery | ⏸ Remplacer par modale |
| 21 | Gestion des mandats (profils supplementaires par perimetre) | <= 3 | Non existant | ✅ Nouveau — section mandats dans la modale (voir 3.1) |

### 2.3 Ameliorations par rapport au legacy

| # | Amelioration | Description |
|---|--------------|-------------|
| 1 | Pagination | Ajout de pagination (20 par page) au lieu d'afficher tous les utilisateurs |
| 2 | Formulaire en modale | Formulaire de creation/modification dans une modale au lieu du toggle inline |
| 3 | Autocomplete clubs | Multi-selection de clubs avec autocomplete au lieu de saisie libre de codes |
| 4 | Recherche serveur | Filtrage cote API au lieu de surlignage cote client |
| 5 | Hachage bcrypt | Nouveaux mots de passe haches en bcrypt au lieu de MD5 |
| 6 | Email de reinitialisation | Lien de reinitialisation securise au lieu d'envoyer le mot de passe en clair. Complexite minimale imposee (10 car., majuscules, minuscules, chiffres, speciaux) |
| 7 | Profil par defaut 7 | Un nouvel utilisateur est cree avec profil 7, saison en cours, propre club |
| 8 | Systeme de mandats | Possibilite d'attribuer des profils supplementaires par perimetre (saisons, competitions, clubs, journees). Selection du mandat actif apres connexion et changement sans deconnexion |

---

## 3. Decisions de conception

| # | Question | Decision |
|---|----------|----------|
| Q1 | Doit-on conserver le filtre "Special" (requete SQL directe) ? | **Non**. Seul le filtre classique (selection de saisons + competitions) est conserve. Le filtre "Special" est un risque de securite et n'est utilise que par le profil 1 qui a deja acces a tout. |
| Q2 | Comment gerer le filtre clubs ? | **Autocomplete multi-selection** via l'endpoint existant `/admin/clubs/search`. L'admin tape le nom ou code du club, selectionne dans la liste. Les clubs selectionnes sont affiches sous forme de tags. Les valeurs stockees restent des codes clubs separes par virgule dans `Limitation_equipe_club`. |
| Q3 | Comment gerer les mots de passe ? | **Migration progressive vers bcrypt**. Les nouveaux mots de passe crees via app4 sont haches en bcrypt. L'authentification supporte les deux formats : tentative bcrypt d'abord, puis fallback MD5 pour les mots de passe legacy. A la creation, un email avec lien de reinitialisation est envoye a l'utilisateur au lieu du mot de passe en clair. |
| Q4 | Quels profils un admin peut-il attribuer ? | Profil 1 : peut attribuer profils 1-10. Profil 2 : peut attribuer profils 3-10. Profils 3-4 : peuvent attribuer profils 5-10 uniquement. Un admin ne peut jamais attribuer un profil superieur ou egal au sien (sauf profil 1). |
| Q5 | Formulaire inline ou modale ? | **Modale**. Le formulaire de creation/modification s'ouvre dans une modale `AdminModal` (max-width 3xl pour accommoder les multi-selects). Coherent avec les autres pages app4. |
| Q6 | Faut-il conserver l'envoi d'email ? | **Oui**. A la creation, un email avec lien de reinitialisation de mot de passe est envoye a l'utilisateur (+ lien vers la documentation en ligne). A la modification, l'email n'est envoye que si l'admin le demande explicitement. Un message complementaire (textarea) peut etre ajoute, avec un bouton "Message standard" pre-rempli. L'email de notification a l'administrateur (contact@kayak-polo.info) est conserve a chaque creation/modification. |
| Q7 | Valeurs par defaut d'un nouvel utilisateur ? | **Profil 7**, limite a la **saison en cours**, filtre club pre-rempli avec le **club de la licence** si disponible (via la recherche autocomplete). |
| Q8 | Comment gerer les multi-profils (un utilisateur avec profil 7 national + profil 3 regional) ? | **Systeme de mandats.** L'utilisateur a un profil de base + zero ou plusieurs mandats avec chacun son propre profil et ses propres filtres. Voir section 3.1. |

### 3.1 Systeme de Mandats

**Probleme** : Un meme individu peut avoir besoin de droits differents selon le perimetre. Exemple : responsable d'equipe (profil 7) pour les competitions nationales de son club, ET delegue federal (profil 5) pour une journee specifique de la Coupe de France, ET responsable de division (profil 3) pour les championnats regionaux.

**Approche retenue : Profil de base + Mandats**

L'utilisateur conserve son profil de base dans `kp_user.Niveau` avec ses filtres classiques (saisons, competitions, clubs, journees, evenements). En complement, une table `kp_user_mandat` permet d'ajouter des **mandats** : chaque mandat possede son propre profil et ses propres filtres, exactement comme le profil principal.

**Exemple concret** :

| | Profil | Saisons | Competitions | Clubs | Journees |
|---|--------|---------|-------------|-------|----------|
| **Profil de base** | 7 (Resp. club) | 2026 | N1H, N2H | 7603 | — |
| **Mandat 1** : "Delegue CdF 2026" | 5 (Delegue) | 2026 | CF | — | 5775, 5777 |
| **Mandat 2** : "Resp. Regional PDL" | 3 (Resp. Division) | 2026, 2025 | R1H, R2H, R1F | — | — |

#### 3.1.1 Flux de connexion

1. L'utilisateur se connecte normalement (login/mot de passe)
2. Le JWT retourne contient la liste de ses mandats (le cas echeant)
3. **Si l'utilisateur a au moins un mandat** : une page intermediaire de selection s'affiche apres l'authentification
   - Option "Profil de base" : utilise le profil et les filtres de `kp_user`
   - Options mandats : chaque mandat avec son libelle (ex: "Delegue — Coupe de France 2026")
4. La selection envoie `POST /auth/switch-mandate` qui retourne un nouveau JWT avec le mandat actif
5. Si l'utilisateur n'a aucun mandat, la connexion se fait directement (comportement actuel)

#### 3.1.2 Changement de mandat (sans deconnexion)

- Dans le header (dropdown utilisateur), le mandat actif est affiche sous le nom de l'utilisateur
- Un bouton "Changer de mandat" permet de revenir a l'ecran de selection
- Le changement genere un nouveau JWT et rafraichit la page

#### 3.1.3 Resolution des permissions

Quand un mandat est actif, le systeme utilise **exclusivement** le profil et les filtres du mandat (pas de fusion avec le profil de base). L'utilisateur travaille dans un cadre isole et explicite.

Quand aucun mandat n'est actif (profil de base), le comportement est identique au systeme actuel.

#### 3.1.4 Impact sur les systemes existants

| Systeme | Impact |
|---------|--------|
| Legacy PHP | Aucun — utilise toujours le profil de base de `kp_user` |
| App2 | Aucun — utilise un systeme de tokens different |
| App4 | Ajout de la selection de mandat (login + header) |
| API2 | Ajout de la table `kp_user_mandat` + endpoints CRUD + endpoint switch |
| JWT | Extension du payload (mandates[], activeMandate, effectiveProfile, effectiveFilters) |

#### 3.1.5 Gestion des mandats dans la page Utilisateurs

Dans la modale de creation/modification d'un utilisateur, une section "Mandats" permet a l'admin de :
- Voir la liste des mandats existants
- Ajouter un mandat (libelle, profil, saisons, competitions, clubs, journees, evenements)
- Modifier ou supprimer un mandat existant
- Les memes restrictions de profil s'appliquent : un admin profil 3 ne peut pas creer un mandat avec un profil < 3

---

## 4. Structure de la Page

### 4.1 Desktop

```
+------------------------------------------------------------------+
| Utilisateurs                           [Journal des activites >]  |
+------------------------------------------------------------------+
| AdminToolbar                                                      |
| [Profil: v] [Saison: v]  [________Recherche________] [+ Ajouter] |
+------------------------------------------------------------------+
|                                                                    |
| +----------------------------------------------------------------+|
| | sel | Identite       | Fonction  | Profil | Saisons | Compet.  ||
| |     | (licence)      |           |        |         |          ||
| |-----|----------------|-----------|--------|---------|----------||
| | [ ] | Vignet Eric    | Webmaster | 1      | TOUTES  | TOUTES   ||
| |     | (63155)        |           |        |         |          ||
| |-----|----------------|-----------|--------|---------|----------||
| | [ ] | Dupont Jean    | Resp.     | 7      | 2026    | N1H,N2H  ||
| |     | (2001234)      |           | +2 mdt |         |          ||
| +----------------------------------------------------------------+|
|                                                                    |
| (suite: colonnes Evt/J, Clubs, Actions)                           |
| Note: email et telephone masques (visibles dans la modale)        |
| Note: "+N mdt" sous le profil indique le nombre de mandats        |
|                                                                    |
| AdminPagination                                                    |
| [< 1 2 3 >]                                    [20|50|100 par p] |
+------------------------------------------------------------------+
```

### 4.2 Mobile (Cards)

```
+----------------------------------+
| Utilisateurs                      |
+----------------------------------+
| [Profil v] [Saison v]            |
| [________Recherche________]      |
| [+ Ajouter]                      |
+----------------------------------+
| +------------------------------+ |
| | Vignet Eric          Profil 1| |
| | (63155)                      | |
| | Webmaster                    | |
| | Saisons: TOUTES              | |
| | Competitions: TOUTES         | |
| |            [Modifier] [Supp] | |
| +------------------------------+ |
| +------------------------------+ |
| | Dupont Jean    Profil 7 +2mdt| |
| | (2001234)                    | |
| | Resp. equipe                 | |
| | Saisons: 2026                | |
| | Competitions: N1H, N2H       | |
| |            [Modifier] [Supp] | |
| +------------------------------+ |
+----------------------------------+
```

---

## 5. Modale de creation / modification

### 5.1 Modale UserEditModal

**Composant** : `AdminUserEditModal.vue`
**Taille** : `max-width="3xl"` (large, pour les multi-selects)

### 5.2 Champs du formulaire

| Champ | Type | Requis | Validation | Colonne DB | Notes |
|-------|------|--------|------------|------------|-------|
| Recherche licence | Autocomplete | Non | Min 2 caracteres | - | Pre-remplit licence + identite + club. Endpoint: `/admin/athletes/search` |
| Licence (code) | Text (readonly apres creation) | Oui | Max 8 car., unique | `Code` | Rempli par autocomplete ou saisie manuelle |
| Identite | Text | Oui | Max 80 car. | `Identite` | Readonly sauf profil 1 |
| Email | Email | Oui | Format email valide, max 100 car. | `Mail` | |
| Telephone | Text | Non | Max 15 car. | `Tel` | |
| Fonctions | Text | Non | Max 100 car. | `Fonction` | Description libre du role |
| Profil | Select | Oui | Selon profil admin (voir Q4) | `Niveau` | Defaut: 7 |
| Filtre saisons | Multi-select | Non | - | `Filtre_saison` | Option "Toutes" en premier. Defaut: saison en cours |
| Filtre competitions | Multi-select groupe | Non | - | `Filtre_competition` | Groupe par section. Option "Toutes" en premier |
| Filtre evenements | Multi-select | Non | - | `Id_Evenement` | Liste des evenements. Profil <= 2 uniquement |
| Filtre clubs | Autocomplete multi | Non | Codes clubs valides | `Limitation_equipe_club` | Autocomplete `/admin/clubs/search`. Affiche tags. Defaut: club de la licence |
| Filtre journees | Text | Non | IDs numeriques, separes par virgule | `Filtre_journee` | Champ texte libre (comme legacy) |
| Envoyer email reinitialisation | Checkbox | Non | - | - | Coche par defaut a la creation. Envoie un email avec lien de reinitialisation mdp + lien documentation |
| Inclure lien documentation | Checkbox | Non | - | - | Inclut un lien vers la documentation en ligne dans l'email |
| Message complementaire | Textarea | Non | Max 2000 car. | - | Message personnalise ajoute a l'email. Bouton "Message standard" pre-remplit un texte type |
| **--- Section Mandats ---** | | | | | **Profil <= 3 uniquement. Voir section 3.1** |
| Liste des mandats | Tableau inline | Non | - | `kp_user_mandat` | Affiche les mandats existants avec actions modifier/supprimer |
| Ajouter un mandat | Formulaire expansion | Non | - | `kp_user_mandat` | Formulaire en expansion avec : libelle, profil, saisons, competitions, clubs, journees, evenements |
| Mandat — Libelle | Text | Oui* | Max 100 car. | `libelle` | Description lisible du mandat |
| Mandat — Profil | Select | Oui* | Selon profil admin | `niveau` | Memes restrictions que le profil principal |
| Mandat — Saisons | Multi-select | Non | - | `filtre_saison` | Meme format pipe que kp_user |
| Mandat — Competitions | Multi-select groupe | Non | - | `filtre_competition` | Meme format pipe que kp_user |
| Mandat — Clubs | Autocomplete multi | Non | Codes clubs valides | `limitation_equipe_club` | Meme autocomplete que le profil principal |
| Mandat — Journees | Text | Non | IDs numeriques | `filtre_journee` | Meme format virgule que kp_user |
| Mandat — Evenements | Multi-select | Non | - | `id_evenement` | Profil <= 2 uniquement |

### 5.3 Organisation du formulaire (dans la modale)

```
+-----------------------------------------------------------+
| Creer un utilisateur                              [X]     |
+-----------------------------------------------------------+
| Recherche : [_______nom, prenom ou licence________] 🔍   |
|                                                           |
| Licence*: [________]   Identite*: [________________]      |
| Email*:   [________________________]                      |
| Telephone:[________________]  Fonctions:[______________]  |
| Profil*:  [7 - Resp. club/equipe  v]                     |
|                                                           |
| --- Filtres d'acces ---                                   |
|                                                           |
| Saisons:           | Competitions:                        |
| +--------------+   | +------------------------------+    |
| | * Toutes     |   | | * Toutes les competitions    |    |
| | 2026 [x]     |   | | -- Internationales --        |    |
| | 2025         |   | |    CEC18...                  |    |
| | 2024         |   | | -- Nationales --             |    |
| | ...          |   | |    N1H, N2H...               |    |
| +--------------+   | +------------------------------+    |
|                                                           |
| Evenements:        (profil <= 2 uniquement)               |
| +----------------------------------------------------+   |
| | 227-Coupe Regionale AURA 2025                      |   |
| | 226-Coupe de France 2025                           |   |
| | ...                                                |   |
| +----------------------------------------------------+   |
|                                                           |
| Clubs: [_________autocomplete_______] [x CK LE HAVRE]    |
| Journees: [____IDs separes par virgule____]               |
|                                                           |
| --- Mandats (profil <= 3 uniquement) ---                  |
|                                                           |
| +-------------------------------------------------------+ |
| | Libelle              | Profil | Saisons | Comp. | Act.| |
| |----------------------|--------|---------|-------|-----| |
| | Delegue CdF 2026    | 5      | 2026    | CF    | [e] | |
| |                      |        |         |       | [x] | |
| | Resp. Regional PDL   | 3      | 2026,25 | R1H.. | [e] | |
| |                      |        |         |       | [x] | |
| +-------------------------------------------------------+ |
| [+ Ajouter un mandat]                                     |
|                                                           |
| (formulaire mandat en expansion au clic sur + ou [e])     |
| +-------------------------------------------------------+ |
| | Libelle*: [_____________________________]             | |
| | Profil*:  [5 - Delegue federal  v]                    | |
| | Saisons:  [multi-select]  Competitions: [multi-select]| |
| | Clubs:    [autocomplete]  Journees: [____IDs____]     | |
| | Evenements: [multi-select] (profil <= 2)              | |
| |                           [Annuler] [Valider mandat]  | |
| +-------------------------------------------------------+ |
|                                                           |
| --- Email ---                                             |
|                                                           |
| [x] Envoyer un email de reinitialisation du mot de passe  |
| [x] Inclure un lien vers la documentation                 |
| Message complementaire : [Message standard]               |
| +------------------------------------------------------+  |
| |                                                      |  |
| |                                                      |  |
| +------------------------------------------------------+  |
|                                                           |
|                        [Annuler]  [Enregistrer]           |
+-----------------------------------------------------------+
```

### 5.4 Comportement

- **Creation** : Le bouton "Ajouter" ouvre la modale vide avec les valeurs par defaut (profil 7, saison en cours)
- **Modification** : Le bouton "Modifier" charge les donnees de l'utilisateur et ouvre la modale pre-remplie
- **Autocomplete licence** : Recherche dans `/admin/athletes/search?q=...` (min 2 car.). A la selection, remplit automatiquement licence, identite, et club (si disponible)
- **Autocomplete clubs** : Recherche dans `/admin/clubs/search?q=...` (min 2 car.). Affiche les resultats en dropdown. Chaque club selectionne s'ajoute comme un tag. Clic sur le tag pour retirer
- **Profil par defaut** : 7 pour un nouvel utilisateur
- **Validation avant soumission** : Licence non vide, email non vide et valide, profil selectionne. Pour profils 7-8, filtre club obligatoire
- **Email de reinitialisation** : Si coche, l'API genere un token de reinitialisation et envoie un email a l'utilisateur avec un lien securise. L'email peut inclure un lien vers la documentation et un message complementaire. Le bouton "Message standard" pre-remplit la textarea avec un texte type (regles de gestion des feuilles de presence, etc.)
- **Email admin** : A chaque creation/modification, un email de notification est envoye a contact@kayak-polo.info avec le resume des actions effectuees
- **Lien journal** : Un lien en haut de page renvoie vers la page Journal des activites (`/activity-log`)
- **Mandats** (profil <= 3) : La section "Mandats" affiche la liste des mandats existants. Le bouton "Ajouter un mandat" ouvre un formulaire en expansion (accordion) avec les memes champs de filtres que le profil de base. Le bouton "Modifier" sur un mandat existant ouvre le meme formulaire pre-rempli. La suppression d'un mandat demande confirmation. Les mandats sont sauvegardes independamment de l'utilisateur via les endpoints `/admin/users/{code}/mandats`

---

## 6. Endpoints API

### 6.1 Endpoints existants reutilisables

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/admin/athletes/search?q=...` | Recherche licence autocomplete |
| GET | `/admin/clubs/search?q=...` | Recherche clubs autocomplete |
| GET | `/admin/filters/seasons` | Liste des saisons |
| GET | `/admin/filters/competitions?season=...` | Liste des competitions groupees par section |
| GET | `/admin/filters/events?season=...` | Liste des evenements |

### 6.2 Nouveaux endpoints a creer

#### 6.2.1 Liste des utilisateurs

**Methode** : GET
**Endpoint** : `/admin/users`
**Profil** : <= 4
**Description** : Liste paginee des utilisateurs avec filtres optionnels.

**Query Parameters** :
- `page` (optionnel, defaut: 1) — Numero de page
- `limit` (optionnel, defaut: 20, max: 100) — Nombre par page
- `search` (optionnel) — Recherche dans Code, Identite, Mail
- `profile` (optionnel) — Filtrer par niveau de profil
- `season` (optionnel) — Filtrer par saison autorisee (utilisateurs ayant cette saison dans Filtre_saison ou Filtre_saison vide)

**Response** :
```json
{
  "items": [
    {
      "code": "63155",
      "identite": "VIGNET Eric",
      "mail": "e.vignet@sfr.fr",
      "tel": "",
      "fonction": "Webmaster",
      "niveau": 1,
      "filtreSaison": "",
      "filtreCompetition": "",
      "idEvenement": "|208|206|197|188|145|",
      "filtreJournee": "",
      "limitClubs": "",
      "dateDebut": null,
      "dateFin": null
    }
  ],
  "total": 45,
  "page": 1,
  "limit": 20,
  "totalPages": 3
}
```

**Logique backend** :
1. Verifier profil <= 4
2. Filtrer les utilisateurs ayant un profil >= profil de l'admin courant (un admin ne voit que les utilisateurs de profil egal ou inferieur au sien — sauf profil 1 qui voit tout)
3. Appliquer les filtres optionnels (search, profile, season)
4. Joindre `kp_licence` pour obtenir le club (Numero_club) si besoin
5. Paginer les resultats

**Codes retour** :
| Code | Signification |
|------|---------------|
| 200 | Succes |
| 401 | Non authentifie |
| 403 | Profil insuffisant |

#### 6.2.2 Detail d'un utilisateur

**Methode** : GET
**Endpoint** : `/admin/users/{code}`
**Profil** : <= 4
**Description** : Recupere les informations completes d'un utilisateur.

**Response** :
```json
{
  "code": "63155",
  "identite": "VIGNET Eric",
  "mail": "e.vignet@sfr.fr",
  "tel": "",
  "fonction": "Webmaster",
  "niveau": 1,
  "filtreSaison": "",
  "filtreCompetition": "",
  "idEvenement": "|208|206|197|188|145|",
  "filtreJournee": "",
  "limitClubs": "",
  "dateDebut": null,
  "dateFin": null,
  "club": "7603",
  "clubLabel": "CK LE HAVRE"
}
```

**Codes retour** :
| Code | Signification |
|------|---------------|
| 200 | Succes |
| 403 | Profil insuffisant ou tentative d'acceder a un utilisateur de profil superieur |
| 404 | Utilisateur non trouve |

#### 6.2.3 Creer un utilisateur

**Methode** : POST
**Endpoint** : `/admin/users`
**Profil** : <= 3
**Description** : Cree un nouvel utilisateur.

**Request Body** :
```json
{
  "code": "2001234",
  "identite": "DUPONT Jean",
  "mail": "jean.dupont@email.com",
  "tel": "0612345678",
  "fonction": "Responsable equipe",
  "niveau": 7,
  "filtreSaison": "|2026|",
  "filtreCompetition": "|N1H|N2H|",
  "idEvenement": "",
  "filtreJournee": "",
  "limitClubs": "7603",
  "sendResetEmail": true,
  "includeDocLink": true,
  "complementaryMessage": ""
}
```

**Logique backend** :
1. Verifier profil <= 3
2. Verifier que le profil attribue respecte les restrictions (profils 3-4 ne peuvent pas attribuer < 5)
3. Verifier que le code n'existe pas deja dans `kp_user`
4. Generer un mot de passe aleatoire hache en bcrypt (l'utilisateur ne le connait pas)
5. Inserer dans `kp_user` avec `Type_filtre_competition = 2` (classique), `Filtre_competition_sql = ''`
6. Si `sendResetEmail = true`, generer un token de reinitialisation et envoyer un email (avec lien doc si `includeDocLink`, avec message complementaire si renseigne)
7. Envoyer l'email de notification a l'administrateur (contact@kayak-polo.info)
8. Journaliser l'action

**Codes retour** :
| Code | Signification |
|------|---------------|
| 201 | Utilisateur cree |
| 400 | Donnees invalides (champs manquants, email invalide) |
| 403 | Profil insuffisant ou tentative d'attribuer un profil non autorise |
| 409 | Code utilisateur deja existant |

#### 6.2.4 Modifier un utilisateur

**Methode** : PUT
**Endpoint** : `/admin/users/{code}`
**Profil** : <= 3
**Description** : Met a jour un utilisateur existant.

**Request Body** :
```json
{
  "identite": "DUPONT Jean",
  "mail": "jean.dupont@email.com",
  "tel": "0612345678",
  "fonction": "Responsable equipe",
  "niveau": 7,
  "filtreSaison": "|2026|2025|",
  "filtreCompetition": "|N1H|N2H|CF|",
  "idEvenement": "",
  "filtreJournee": "",
  "limitClubs": "7603,4404",
  "sendResetEmail": false,
  "includeDocLink": false,
  "complementaryMessage": ""
}
```

**Logique backend** :
1. Verifier profil <= 3
2. Verifier que l'utilisateur cible a un profil >= profil de l'admin (sauf profil 1)
3. Verifier que le nouveau profil attribue respecte les restrictions
4. Si profil admin > 1, ignorer la modification du champ `identite`
5. Si profil admin > 2, ignorer la modification du champ `idEvenement`
6. Mettre a jour `kp_user`, `Type_filtre_competition = 2`, `Filtre_competition_sql = ''`
7. Si `sendResetEmail = true`, generer un token de reinitialisation et envoyer un email (avec lien doc si `includeDocLink`, avec message complementaire si renseigne)
8. Envoyer l'email de notification a l'administrateur (contact@kayak-polo.info)
9. Journaliser l'action

**Codes retour** :
| Code | Signification |
|------|---------------|
| 200 | Utilisateur mis a jour |
| 400 | Donnees invalides |
| 403 | Profil insuffisant ou tentative de modifier un utilisateur de profil superieur |
| 404 | Utilisateur non trouve |

#### 6.2.5 Supprimer un utilisateur

**Methode** : DELETE
**Endpoint** : `/admin/users/{code}`
**Profil** : <= 2
**Description** : Supprime un utilisateur.

**Logique backend** :
1. Verifier profil <= 2
2. Verifier que l'utilisateur cible a un profil > profil de l'admin (on ne peut pas supprimer un pair)
3. Supprimer l'entree dans `kp_user`
4. Supprimer le token dans `kp_user_token` si existant
5. Journaliser l'action

**Codes retour** :
| Code | Signification |
|------|---------------|
| 204 | Utilisateur supprime |
| 403 | Profil insuffisant |
| 404 | Utilisateur non trouve |

#### 6.2.6 Suppression en masse

**Methode** : POST
**Endpoint** : `/admin/users/bulk-delete`
**Profil** : <= 2
**Description** : Supprime plusieurs utilisateurs.

**Request Body** :
```json
{
  "codes": ["2001234", "2005678"]
}
```

**Logique backend** :
1. Verifier profil <= 2
2. Pour chaque code, verifier que l'utilisateur cible a un profil > profil admin
3. Supprimer les entrees de `kp_user` et `kp_user_token`
4. Journaliser l'action
5. Retourner le nombre effectivement supprime

**Response** :
```json
{
  "deleted": 2
}
```

**Codes retour** :
| Code | Signification |
|------|---------------|
| 200 | Suppression effectuee |
| 403 | Profil insuffisant |

#### 6.2.7 Envoyer email de reinitialisation

**Methode** : POST
**Endpoint** : `/admin/users/{code}/reset-password`
**Profil** : <= 3
**Description** : Genere un token de reinitialisation et envoie un email a l'utilisateur.

**Logique backend** :
1. Verifier profil <= 3
2. Generer un token unique (64 caracteres hex)
3. Stocker le token avec une date d'expiration (ex: 48h) — a definir dans une table ou dans `kp_user_token`
4. Envoyer un email a l'adresse de l'utilisateur avec le lien de reinitialisation
5. Le lien pointe vers une page app4 `/reset-password?token=...`

**Note** : L'implementation complete du flux de reinitialisation (page reset, validation token, changement de mot de passe) est documentee ici mais pourra etre implementee en V2 si necessaire. En V1, un mot de passe aleatoire peut etre genere et communique a l'admin.

**Codes retour** :
| Code | Signification |
|------|---------------|
| 200 | Email envoye |
| 403 | Profil insuffisant |
| 404 | Utilisateur non trouve |

#### 6.2.8 Email de notification admin

A chaque creation ou modification d'un utilisateur, un email est envoye automatiquement a `contact@kayak-polo.info` contenant :
- Action effectuee (creation/modification)
- Identite, email, telephone de l'utilisateur
- Profil attribue
- Filtres configures (saisons, competitions, clubs, evenements)
- Message complementaire si renseigne
- Nom de l'administrateur ayant effectue l'action

#### 6.2.9 Page de reinitialisation du mot de passe

**Route app4** : `/reset-password?token=...`
**Acces** : Public (pas d'authentification requise)

**Endpoint validation token** :
- `POST /auth/reset-password` — Valide le token et change le mot de passe

**Request Body** :
```json
{
  "token": "a1b2c3d4...",
  "password": "NouveauMdp123!"
}
```

**Regles de complexite du mot de passe** :

| Critere | Regle |
|---------|-------|
| Longueur minimale | 10 caracteres |
| Majuscules | Au moins 1 lettre majuscule |
| Minuscules | Au moins 1 lettre minuscule |
| Chiffres | Au moins 1 chiffre |
| Caracteres speciaux | Au moins 1 caractere special (`!@#$%^&*()-_=+[]{}\\|;:'",.<>?/~`) |

**Validation** : Cote frontend (feedback en temps reel avec indicateur de force) ET cote backend (rejet si les regles ne sont pas respectees).

**Codes retour** :
| Code | Signification |
|------|---------------|
| 200 | Mot de passe modifie |
| 400 | Mot de passe ne respecte pas les regles de complexite |
| 401 | Token invalide ou expire |

#### 6.2.10 Liste des mandats d'un utilisateur

**Methode** : GET
**Endpoint** : `/admin/users/{code}/mandats`
**Profil** : <= 3
**Description** : Recupere la liste des mandats d'un utilisateur.

**Response** :
```json
{
  "mandats": [
    {
      "id": 1,
      "libelle": "Delegue CdF 2026",
      "niveau": 5,
      "filtreSaison": "|2026|",
      "filtreCompetition": "|CF|",
      "limitClubs": "",
      "filtreJournee": "5775,5777",
      "idEvenement": ""
    },
    {
      "id": 2,
      "libelle": "Resp. Regional PDL",
      "niveau": 3,
      "filtreSaison": "|2026|2025|",
      "filtreCompetition": "|R1H|R2H|R1F|",
      "limitClubs": "",
      "filtreJournee": "",
      "idEvenement": ""
    }
  ]
}
```

#### 6.2.11 Creer un mandat

**Methode** : POST
**Endpoint** : `/admin/users/{code}/mandats`
**Profil** : <= 3
**Description** : Ajoute un mandat a un utilisateur.

**Request Body** :
```json
{
  "libelle": "Delegue CdF 2026",
  "niveau": 5,
  "filtreSaison": "|2026|",
  "filtreCompetition": "|CF|",
  "limitClubs": "",
  "filtreJournee": "5775,5777",
  "idEvenement": ""
}
```

**Logique backend** :
1. Verifier profil <= 3
2. Verifier que le profil du mandat respecte les restrictions (profils 3-4 ne peuvent pas attribuer < 5)
3. Verifier que l'utilisateur cible existe
4. Inserer dans `kp_user_mandat`
5. Journaliser l'action

**Codes retour** :
| Code | Signification |
|------|---------------|
| 201 | Mandat cree |
| 400 | Donnees invalides (libelle manquant, niveau invalide) |
| 403 | Profil insuffisant ou tentative d'attribuer un profil non autorise |
| 404 | Utilisateur non trouve |

#### 6.2.12 Modifier un mandat

**Methode** : PUT
**Endpoint** : `/admin/users/{code}/mandats/{id}`
**Profil** : <= 3
**Description** : Modifie un mandat existant.

**Request Body** : Meme format que la creation (6.2.11).

**Codes retour** :
| Code | Signification |
|------|---------------|
| 200 | Mandat mis a jour |
| 400 | Donnees invalides |
| 403 | Profil insuffisant |
| 404 | Utilisateur ou mandat non trouve |

#### 6.2.13 Supprimer un mandat

**Methode** : DELETE
**Endpoint** : `/admin/users/{code}/mandats/{id}`
**Profil** : <= 3
**Description** : Supprime un mandat.

**Codes retour** :
| Code | Signification |
|------|---------------|
| 204 | Mandat supprime |
| 403 | Profil insuffisant |
| 404 | Utilisateur ou mandat non trouve |

#### 6.2.14 Liste des mandats de l'utilisateur connecte

**Methode** : GET
**Endpoint** : `/auth/mandates`
**Profil** : Authentifie
**Description** : Retourne la liste des mandats de l'utilisateur connecte. Utilise apres login pour l'ecran de selection.

**Response** :
```json
{
  "baseProfile": {
    "niveau": 7,
    "filters": {
      "seasons": ["2026"],
      "competitions": ["N1H", "N2H"],
      "clubs": ["7603"],
      "journees": null,
      "events": null
    }
  },
  "mandates": [
    {
      "id": 1,
      "libelle": "Delegue CdF 2026",
      "niveau": 5,
      "filters": {
        "seasons": ["2026"],
        "competitions": ["CF"],
        "clubs": null,
        "journees": [5775, 5777],
        "events": null
      }
    }
  ]
}
```

#### 6.2.15 Changer de mandat actif

**Methode** : POST
**Endpoint** : `/auth/switch-mandate`
**Profil** : Authentifie
**Description** : Change le mandat actif et retourne un nouveau JWT.

**Request Body** :
```json
{
  "mandateId": 1
}
```

`mandateId` = `null` pour revenir au profil de base.

**Response** :
```json
{
  "token": "eyJ...",
  "user": {
    "id": "2001234",
    "name": "DUPONT",
    "firstname": "Jean",
    "profile": 7,
    "effectiveProfile": 5,
    "activeMandate": {
      "id": 1,
      "libelle": "Delegue CdF 2026"
    },
    "effectiveFilters": {
      "seasons": ["2026"],
      "competitions": ["CF"],
      "clubs": null,
      "journees": [5775, 5777],
      "events": null
    },
    "mandates": [...]
  }
}
```

**Logique backend** :
1. Verifier que l'utilisateur est authentifie
2. Si `mandateId` != null, verifier que le mandat appartient a l'utilisateur
3. Generer un nouveau JWT avec `activeMandate`, `effectiveProfile` et `effectiveFilters` renseignes
4. Retourner le nouveau token

**Codes retour** :
| Code | Signification |
|------|---------------|
| 200 | Mandat change, nouveau token retourne |
| 400 | mandateId invalide |
| 404 | Mandat non trouve pour cet utilisateur |

---

## 7. Schema de donnees

### 7.1 Table kp_user

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| Code | varchar(8) | Non | PK — Code utilisateur (licence ou identifiant) |
| Pwd | varchar(255) | Non | Mot de passe hache (MD5 legacy ou bcrypt nouveau) |
| Identite | varchar(80) | Oui | Nom complet |
| Mail | varchar(100) | Non | Adresse email |
| Tel | varchar(15) | Non | Telephone |
| Fonction | varchar(100) | Non | Description du role/fonctions |
| Niveau | smallint(6) | Non | Niveau de profil (1-10) |
| Type_filtre_competition | smallint(6) | Non | Type de filtre (2=classique). Les valeurs 1 et 3 ne sont plus utilisees par app4 |
| Filtre_competition | mediumtext | Non | Codes competitions (format pipe : `\|N1H\|N2H\|`) |
| Filtre_saison | mediumtext | Non | Codes saisons (format pipe : `\|2025\|2024\|`) |
| Filtre_competition_sql | mediumtext | Non | Filtre SQL special (non utilise par app4) |
| Filtre_journee | mediumtext | Non | IDs de journees (format virgule : `5775,5777`) |
| Limitation_equipe_club | varchar(50) | Oui | Codes clubs (format virgule : `7603,4404`) |
| Id_Evenement | varchar(20) | Non | IDs evenements (format pipe : `\|208\|206\|`) |
| Date_debut | date | Oui | Date debut (export evenement, conserve pour compatibilite) |
| Date_fin | date | Oui | Date fin (export evenement, conserve pour compatibilite) |

### 7.2 Table kp_user_token

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| user | varchar(8) | Non | PK, FK → kp_user.Code |
| token | varchar(32) | Non | Token d'authentification (app2) |
| generated_at | datetime | Non | Date de generation |

### 7.3 Modification de la colonne Pwd

La colonne `Pwd` doit etre elargie de `varchar(40)` (MD5 = 32 car.) a `varchar(255)` pour supporter bcrypt (60 car.) avec marge.

```sql
ALTER TABLE kp_user MODIFY COLUMN Pwd varchar(255) NOT NULL DEFAULT '';
```

### 7.4 Table kp_user_mandat

Table des mandats (profils supplementaires par perimetre). Voir section 3.1.

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| id | int(11) | Non | PK, auto_increment |
| user_code | varchar(8) | Non | FK → kp_user.Code |
| libelle | varchar(100) | Non | Description lisible du mandat |
| niveau | smallint(6) | Non | Niveau de profil accorde dans ce mandat |
| filtre_saison | mediumtext | Non | Saisons (format pipe : `\|2026\|2025\|`) |
| filtre_competition | mediumtext | Non | Competitions (format pipe : `\|CF\|N1H\|`) |
| limitation_equipe_club | varchar(50) | Oui | Clubs (format virgule : `7603,4404`) |
| filtre_journee | mediumtext | Non | Journees (format virgule : `5775,5777`) |
| id_evenement | varchar(20) | Non | Evenements (format pipe : `\|208\|206\|`) |

```sql
CREATE TABLE kp_user_mandat (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_code varchar(8) NOT NULL,
  libelle varchar(100) NOT NULL,
  niveau smallint(6) NOT NULL,
  filtre_saison mediumtext NOT NULL DEFAULT '',
  filtre_competition mediumtext NOT NULL DEFAULT '',
  limitation_equipe_club varchar(50) DEFAULT NULL,
  filtre_journee mediumtext NOT NULL DEFAULT '',
  id_evenement varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY fk_mandat_user (user_code),
  CONSTRAINT fk_mandat_user FOREIGN KEY (user_code) REFERENCES kp_user (Code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
```

**Note** : Les colonnes de filtres reprennent exactement les memes noms et formats que `kp_user`. Cela permet de reutiliser la meme logique de parsing (methodes `getAllowed*()` de l'entite User).

---

## 8. Composants Vue

### 8.1 Structure des fichiers

```
sources/app4/
├── pages/
│   ├── users/
│   │   └── index.vue              # Page principale
│   └── select-mandate.vue         # Page selection mandat (post-login)
├── components/
│   └── admin/
│       ├── UserEditModal.vue      # Modale creation/modification
│       └── UserMandateForm.vue    # Formulaire d'un mandat (expansion/accordion)
└── types/
    └── users.ts                   # Types TypeScript (User + Mandate)
```

### 8.2 Composants reutilises

- `AdminToolbar` — Barre d'outils (recherche, ajout, suppression en masse)
- `AdminModal` — Modale generique
- `AdminConfirmModal` — Modale de confirmation de suppression
- `AdminPagination` — Pagination
- `AdminCard` / `AdminCardList` — Cartes mobile

### 8.3 Types TypeScript

```typescript
// types/users.ts

export interface UserListItem {
  code: string
  identite: string
  mail: string
  tel: string
  fonction: string
  niveau: number
  filtreSaison: string
  filtreCompetition: string
  idEvenement: string
  filtreJournee: string
  limitClubs: string
}

export interface UserDetail extends UserListItem {
  club: string | null
  clubLabel: string | null
  dateDebut: string | null
  dateFin: string | null
}

export interface UserForm {
  code: string
  identite: string
  mail: string
  tel: string
  fonction: string
  niveau: number
  filtreSaison: string
  filtreCompetition: string
  idEvenement: string
  filtreJournee: string
  limitClubs: string
  sendResetEmail: boolean
  includeDocLink: boolean
  complementaryMessage: string
}

export interface UsersResponse {
  items: UserListItem[]
  total: number
  page: number
  limit: number
  totalPages: number
}

// --- Mandats ---

export interface Mandate {
  id: number
  libelle: string
  niveau: number
  filtreSaison: string
  filtreCompetition: string
  limitClubs: string
  filtreJournee: string
  idEvenement: string
}

export interface MandateForm {
  libelle: string
  niveau: number
  filtreSaison: string
  filtreCompetition: string
  limitClubs: string
  filtreJournee: string
  idEvenement: string
}

export interface MandateFilters {
  seasons: string[] | null
  competitions: string[] | null
  clubs: string[] | null
  journees: number[] | null
  events: number[] | null
}

export interface MandateSummary {
  id: number
  libelle: string
  niveau: number
  filters: MandateFilters
}

// Etendu pour le JWT avec mandats
export interface AuthUser {
  id: string
  name: string
  firstname: string
  profile: number
  filters: MandateFilters
  mandates: MandateSummary[]
  activeMandate: { id: number; libelle: string } | null
  effectiveProfile: number
  effectiveFilters: MandateFilters
}
```

---

## 9. Menu de Navigation

| Label FR | Label EN | Route | Icone | Profil min |
|----------|----------|-------|-------|------------|
| Utilisateurs | Users | `/users` | `heroicons:users` | <= 4 |

**Position dans le menu** : Section "Administration", apres "Clubs" et avant "Operations".

**Note** : Le document `MENU_REORGANIZATION.md` indique actuellement `<= 3` pour cette page. Il devra etre mis a jour a `<= 4` car les profils 4 ont acces en consultation.

---

## 10. Traductions i18n

### 10.1 Cles francaises (`fr.json`)

```json
{
  "users": {
    "title": "Utilisateurs",
    "search_placeholder": "Rechercher par nom, licence ou email...",
    "add": "Ajouter un utilisateur",
    "filter_profile": "Profil",
    "filter_profile_all": "Tous les profils",
    "filter_season": "Saison",
    "filter_season_all": "Toutes les saisons",
    "table": {
      "identity": "Identite",
      "licence": "Licence",
      "email": "Email",
      "phone": "Telephone",
      "function": "Fonction",
      "profile": "Profil",
      "seasons": "Saisons",
      "seasons_all": "TOUTES",
      "competitions": "Competitions",
      "competitions_all": "TOUTES",
      "events_gamedays": "Evt/J",
      "clubs": "Clubs",
      "actions": "Actions"
    },
    "modal": {
      "title_create": "Creer un utilisateur",
      "title_edit": "Modifier l'utilisateur",
      "search_licence": "Recherche (nom, prenom ou licence)",
      "licence": "Licence",
      "identity": "Identite",
      "email": "Email",
      "phone": "Telephone",
      "function": "Fonctions",
      "profile": "Profil",
      "filters_title": "Filtres d'acces",
      "filter_seasons": "Saisons",
      "filter_seasons_all": "Toutes les saisons",
      "filter_competitions": "Competitions",
      "filter_competitions_all": "Toutes les competitions",
      "filter_events": "Evenements",
      "filter_clubs": "Clubs",
      "filter_clubs_placeholder": "Rechercher un club...",
      "filter_gamedays": "Journees (IDs)",
      "filter_gamedays_placeholder": "IDs separes par des virgules",
      "send_reset_email": "Envoyer un email de reinitialisation du mot de passe",
      "include_doc_link": "Inclure un lien vers la documentation",
      "complementary_message": "Message complementaire",
      "standard_message": "Message standard",
      "save": "Enregistrer",
      "cancel": "Annuler",
      "mandates_title": "Mandats",
      "mandates_empty": "Aucun mandat attribue",
      "mandate_add": "Ajouter un mandat",
      "mandate_label": "Libelle du mandat",
      "mandate_profile": "Profil du mandat",
      "mandate_validate": "Valider le mandat",
      "mandate_cancel": "Annuler",
      "mandate_confirm_delete": "Supprimer ce mandat ?"
    },
    "mandates": {
      "table_mandates": "+{count} mdt",
      "success_created": "Mandat ajoute",
      "success_updated": "Mandat mis a jour",
      "success_deleted": "Mandat supprime"
    },
    "activity_log": "Journal des activites",
    "profiles": {
      "1": "1 - Super Admin",
      "2": "2 - Bureau CNAKP",
      "3": "3 - Resp. Division",
      "4": "4 - Resp. Poule/Competition",
      "5": "5 - Delegue federal",
      "6": "6 - Organisateur journee",
      "7": "7 - Resp. club/equipe",
      "8": "8 - Consultation simple",
      "9": "9 - Table de marque",
      "10": "10 - (Inutilise)"
    },
    "confirm_delete": "Etes-vous sur de vouloir supprimer cet utilisateur ?",
    "confirm_bulk_delete": "Etes-vous sur de vouloir supprimer {count} utilisateur(s) ?",
    "success_created": "Utilisateur cree avec succes",
    "success_updated": "Utilisateur mis a jour",
    "success_deleted": "Utilisateur supprime",
    "success_bulk_deleted": "{count} utilisateur(s) supprime(s)",
    "success_reset_email": "Email de reinitialisation envoye",
    "error_code_exists": "Ce code utilisateur existe deja",
    "error_club_required": "Le filtre club est obligatoire pour les profils 7 et 8",
    "error_profile_restricted": "Vous ne pouvez pas attribuer ce profil",
    "validation_club_required": "Pour les profils 7 et 8, specifiez au moins un club",
    "reset_password": {
      "title": "Reinitialisation du mot de passe",
      "new_password": "Nouveau mot de passe",
      "confirm_password": "Confirmer le mot de passe",
      "submit": "Changer le mot de passe",
      "success": "Mot de passe modifie avec succes",
      "error_token_invalid": "Le lien de reinitialisation est invalide ou a expire",
      "error_mismatch": "Les mots de passe ne correspondent pas",
      "rules": "Le mot de passe doit contenir au moins :",
      "rule_length": "10 caracteres",
      "rule_uppercase": "1 lettre majuscule",
      "rule_lowercase": "1 lettre minuscule",
      "rule_digit": "1 chiffre",
      "rule_special": "1 caractere special (!@#$%...)"
    },
    "select_mandate": {
      "title": "Choisir un cadre de travail",
      "base_profile": "Profil de base",
      "continue": "Continuer"
    },
    "header": {
      "current_mandate": "Mandat actif",
      "base_profile": "Profil de base",
      "switch_mandate": "Changer de mandat"
    }
  }
}
```

### 10.2 Cles anglaises (`en.json`)

```json
{
  "users": {
    "title": "Users",
    "search_placeholder": "Search by name, licence or email...",
    "add": "Add user",
    "filter_profile": "Profile",
    "filter_profile_all": "All profiles",
    "filter_season": "Season",
    "filter_season_all": "All seasons",
    "table": {
      "identity": "Identity",
      "licence": "Licence",
      "email": "Email",
      "phone": "Phone",
      "function": "Function",
      "profile": "Profile",
      "seasons": "Seasons",
      "seasons_all": "ALL",
      "competitions": "Competitions",
      "competitions_all": "ALL",
      "events_gamedays": "Evt/GD",
      "clubs": "Clubs",
      "actions": "Actions"
    },
    "modal": {
      "title_create": "Create user",
      "title_edit": "Edit user",
      "search_licence": "Search (name, firstname or licence)",
      "licence": "Licence",
      "identity": "Identity",
      "email": "Email",
      "phone": "Phone",
      "function": "Functions",
      "profile": "Profile",
      "filters_title": "Access filters",
      "filter_seasons": "Seasons",
      "filter_seasons_all": "All seasons",
      "filter_competitions": "Competitions",
      "filter_competitions_all": "All competitions",
      "filter_events": "Events",
      "filter_clubs": "Clubs",
      "filter_clubs_placeholder": "Search for a club...",
      "filter_gamedays": "Gamedays (IDs)",
      "filter_gamedays_placeholder": "Comma-separated IDs",
      "send_reset_email": "Send password reset email",
      "include_doc_link": "Include a link to documentation",
      "complementary_message": "Complementary message",
      "standard_message": "Standard message",
      "save": "Save",
      "cancel": "Cancel",
      "mandates_title": "Mandates",
      "mandates_empty": "No mandates assigned",
      "mandate_add": "Add a mandate",
      "mandate_label": "Mandate label",
      "mandate_profile": "Mandate profile",
      "mandate_validate": "Validate mandate",
      "mandate_cancel": "Cancel",
      "mandate_confirm_delete": "Delete this mandate?"
    },
    "mandates": {
      "table_mandates": "+{count} mdt",
      "success_created": "Mandate added",
      "success_updated": "Mandate updated",
      "success_deleted": "Mandate deleted"
    },
    "activity_log": "Activity log",
    "profiles": {
      "1": "1 - Super Admin",
      "2": "2 - CNAKP Bureau",
      "3": "3 - Division Manager",
      "4": "4 - Pool/Competition Manager",
      "5": "5 - Federal Delegate",
      "6": "6 - Gameday Organizer",
      "7": "7 - Club/Team Manager",
      "8": "8 - Read Only",
      "9": "9 - Scoreboard",
      "10": "10 - (Unused)"
    },
    "confirm_delete": "Are you sure you want to delete this user?",
    "confirm_bulk_delete": "Are you sure you want to delete {count} user(s)?",
    "success_created": "User created successfully",
    "success_updated": "User updated",
    "success_deleted": "User deleted",
    "success_bulk_deleted": "{count} user(s) deleted",
    "success_reset_email": "Password reset email sent",
    "error_code_exists": "This user code already exists",
    "error_club_required": "Club filter is required for profiles 7 and 8",
    "error_profile_restricted": "You cannot assign this profile",
    "validation_club_required": "For profiles 7 and 8, specify at least one club",
    "reset_password": {
      "title": "Reset password",
      "new_password": "New password",
      "confirm_password": "Confirm password",
      "submit": "Change password",
      "success": "Password changed successfully",
      "error_token_invalid": "The reset link is invalid or has expired",
      "error_mismatch": "Passwords do not match",
      "rules": "Password must contain at least:",
      "rule_length": "10 characters",
      "rule_uppercase": "1 uppercase letter",
      "rule_lowercase": "1 lowercase letter",
      "rule_digit": "1 digit",
      "rule_special": "1 special character (!@#$%...)"
    },
    "select_mandate": {
      "title": "Choose a work context",
      "base_profile": "Base profile",
      "continue": "Continue"
    },
    "header": {
      "current_mandate": "Active mandate",
      "base_profile": "Base profile",
      "switch_mandate": "Switch mandate"
    }
  }
}
```

---

## 11. Securite

### 11.1 Controle d'acces

| Operation | Profil requis | Role Symfony | Restrictions supplementaires |
|-----------|--------------|--------------|------------------------------|
| Voir la liste | <= 4 | ROLE_COMPETITION | Ne voit que les utilisateurs de profil >= son propre profil (sauf profil 1) |
| Creer un utilisateur | <= 3 | ROLE_DIVISION | Profils 3-4 ne peuvent creer que des profils >= 5 |
| Modifier un utilisateur | <= 3 | ROLE_DIVISION | Profils 3-4 ne peuvent modifier que des profils >= 5. Seul profil 1 modifie l'identite. Seul profil <= 2 modifie les evenements |
| Supprimer un utilisateur | <= 2 | ROLE_ADMIN | Ne peut pas supprimer un utilisateur de profil <= son propre profil |
| Supprimer en masse | <= 2 | ROLE_ADMIN | Idem |
| Envoyer email reinitialisation | <= 3 | ROLE_DIVISION | |
| Voir les mandats d'un utilisateur | <= 3 | ROLE_DIVISION | |
| Creer/modifier un mandat | <= 3 | ROLE_DIVISION | Le profil du mandat respecte les memes restrictions que le profil principal (Q4) |
| Supprimer un mandat | <= 3 | ROLE_DIVISION | |
| Changer de mandat actif | Authentifie | ROLE_USER | Uniquement ses propres mandats |

### 11.2 Validation backend

- Verification du profil JWT sur chaque endpoint
- Verification croisee : un admin ne peut pas modifier un utilisateur de profil superieur au sien
- Verification croisee : un admin ne peut pas attribuer un profil superieur au sien (sauf profil 1)
- Validation des champs : email format, longueurs max, code unique
- Hachage bcrypt pour les nouveaux mots de passe
- Journalisation de toutes les operations (kp_journal)
- Verification mandats : un admin ne peut pas creer un mandat avec un profil superieur au sien
- Switch de mandat : verifier que le mandat appartient bien a l'utilisateur authentifie

### 11.3 Authentification duale (MD5 + bcrypt)

Pour supporter la migration progressive des mots de passe :

```php
// Dans AdminAuthController ou UserProvider
public function verifyPassword(User $user, string $plainPassword): bool
{
    $storedHash = $user->getPassword();

    // Essayer bcrypt d'abord (nouveaux mots de passe)
    if (password_verify($plainPassword, $storedHash)) {
        return true;
    }

    // Fallback MD5 (anciens mots de passe)
    if (md5($plainPassword) === $storedHash) {
        // Optionnel : migrer vers bcrypt
        $this->upgradePassword($user, $plainPassword);
        return true;
    }

    return false;
}

private function upgradePassword(User $user, string $plainPassword): void
{
    $bcryptHash = password_hash($plainPassword, PASSWORD_BCRYPT);
    // UPDATE kp_user SET Pwd = ? WHERE Code = ?
    $this->connection->executeStatement(
        'UPDATE kp_user SET Pwd = ? WHERE Code = ?',
        [$bcryptHash, $user->getUserIdentifier()]
    );
}
```

---

## 12. Notes de migration

### 12.1 Differences legacy vs app4

| Aspect | Legacy (PHP) | App4 (Nuxt) |
|--------|-------------|-------------|
| Formulaire | Toggle inline dans la page | Modale AdminModal |
| Filtre competitions | 3 modes (Aucun, Classique, Special) | Mode classique uniquement |
| Filtre clubs | Champ texte libre (codes clubs) | Autocomplete multi-selection |
| Pagination | Aucune (tous affiches) | 20 par page |
| Recherche | Surlignage cote client (jQuery) | Filtrage API cote serveur |
| Mot de passe | MD5, envoye en clair par email | bcrypt, email de reinitialisation |
| Email notification | Email utilisateur + email admin | Email reinitialisation + email admin conserve |
| Recherche licence | Autocomplete jQuery (Autocompl_joueur.php) | Autocomplete via /admin/athletes/search |
| Profil par defaut | 7 (dans le template) | 7 (pre-selection dans la modale) |
| Multi-profils | Comptes multiples (ex: 12345 + 12345b) | Systeme de mandats (table kp_user_mandat) |

### 12.2 Impact sur le legacy PHP

La page legacy `GestionUtilisateur.php` continue de fonctionner en parallele. La migration vers app4 est progressive. Les donnees partagent la meme table `kp_user`.

**Attention** : La modification de la colonne `Pwd` (varchar 40 → 255) est retrocompatible — le legacy PHP continue de lire/ecrire des hashs MD5. Les nouveaux hashs bcrypt ne seront pas reconnus par le legacy PHP. Il faudra mettre a jour le legacy si les deux systemes coexistent (ou desactiver la creation de mots de passe bcrypt tant que le legacy est actif).

### 12.3 Migration SQL

```sql
-- Elargir la colonne Pwd pour supporter bcrypt
ALTER TABLE kp_user MODIFY COLUMN Pwd varchar(255) NOT NULL DEFAULT '';

-- Table des mandats (profils supplementaires par perimetre)
CREATE TABLE kp_user_mandat (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_code varchar(8) NOT NULL,
  libelle varchar(100) NOT NULL,
  niveau smallint(6) NOT NULL,
  filtre_saison mediumtext NOT NULL DEFAULT '',
  filtre_competition mediumtext NOT NULL DEFAULT '',
  limitation_equipe_club varchar(50) DEFAULT NULL,
  filtre_journee mediumtext NOT NULL DEFAULT '',
  id_evenement varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY fk_mandat_user (user_code),
  CONSTRAINT fk_mandat_user FOREIGN KEY (user_code) REFERENCES kp_user (Code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
```

### 12.4 Mise a jour APP4_STRUCTURE.md

Apres implementation, mettre a jour la table des pages dans `DOC/developer/reference/APP4_STRUCTURE.md` :

```
| Utilisateurs | `/users` | Implementee (gestion utilisateurs + filtres) |
```

### 12.5 Mise a jour API2_ENDPOINTS.md

Apres implementation, ajouter les endpoints dans `DOC/developer/reference/API2_ENDPOINTS.md` :

```
### Admin Users
GET    /admin/users                        List users (paginated)
GET    /admin/users/{code}                 Get single user
POST   /admin/users                        Create user (profile <=3)
PUT    /admin/users/{code}                 Update user (profile <=3)
DELETE /admin/users/{code}                 Delete user (profile <=2)
POST   /admin/users/bulk-delete            Bulk delete users (profile <=2)
POST   /admin/users/{code}/reset-password  Send password reset email (profile <=3)

### Admin User Mandates
GET    /admin/users/{code}/mandats         List mandates for user (profile <=3)
POST   /admin/users/{code}/mandats         Create mandate (profile <=3)
PUT    /admin/users/{code}/mandats/{id}    Update mandate (profile <=3)
DELETE /admin/users/{code}/mandats/{id}    Delete mandate (profile <=3)

### Auth Mandates
GET    /auth/mandates                      List mandates for current user
POST   /auth/switch-mandate                Switch active mandate
```

### 12.6 Impact hors page Utilisateurs (systeme de mandats)

Le systeme de mandats impacte egalement des composants en dehors de la page Utilisateurs :

| Fichier / Composant | Modification |
|----------------------|-------------|
| `COMMON_ADMIN_SPECS.md` | Mettre a jour la section JWT (payload etendu avec mandates, activeMandate, effectiveProfile, effectiveFilters) |
| `components/admin/Header.vue` | Afficher le mandat actif + bouton "Changer de mandat" dans le dropdown utilisateur |
| `pages/select-mandate.vue` | Nouvelle page de selection de mandat (post-login) |
| `stores/authStore.ts` | Stocker le mandat actif, exposer effectiveProfile/effectiveFilters |
| `composables/useAuth.ts` | Gerer le switch de mandat (POST /auth/switch-mandate) |
| `pages/login.vue` | Apres login, rediriger vers select-mandate si mandats > 0 |
| `api2/AdminAuthController.php` | Ajouter endpoints GET /auth/mandates et POST /auth/switch-mandate |
| `api2/Entity/UserMandat.php` | Nouvelle entite Doctrine pour kp_user_mandat |
| Legacy PHP | **Aucun impact** — utilise toujours le profil de base de kp_user |

---

**Document cree le** : 2026-02-20
**Derniere mise a jour** : 2026-02-20
