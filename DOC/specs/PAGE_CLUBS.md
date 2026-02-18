# Specification - Page Clubs

## Statut : VALIDE

## 1. Vue d'ensemble

Page d'administration des structures (comites regionaux, comites departementaux, clubs) avec carte interactive. La page legacy combine une carte Leaflet/OpenStreetMap localisant les clubs pratiquant le kayak-polo et des formulaires de gestion (ajout CD, ajout club, mise a jour coordonnees club).

**Route** : `/clubs`

**Acces** :
- Profil <= 10 : Consultation de la carte et liste des clubs (lecture seule)
- Profil <= 2 : Mise a jour des coordonnees d'un club (adresse, GPS, site, email)
- Profil <= 2 : Ajout d'un comite departemental/pays, ajout d'un club/structure

**Page PHP Legacy** : `GestionStructure.php` + `GestionStructure.tpl` + `GestionStructure.js`

**Contexte de travail** : Non applicable (page globale, non liee a une saison).

---

## 2. Fonctionnalites

### 2.1 Consultation / Carte

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Carte interactive des clubs (Leaflet/OpenStreetMap) | <= 10 | Carte 620x550 centree sur la France | ✅ Conserver - pleine largeur responsive |
| 2 | Marqueurs bleus sur la carte pour chaque club avec coordonnees GPS | <= 10 | Marqueurs avec popup nom du club | ✅ Conserver |
| 3 | Geocodage par adresse (recherche Nominatim) | <= 10 | Champ texte + bouton Localiser | ✅ Conserver |
| 4 | Clic sur marqueur : selectionne le club et remplit le formulaire | <= 2 | Remplit coord/postal/www/email | ✅ Conserver |
| 5 | Liste des clubs nationaux (ayant au moins une equipe) | <= 10 | Select dropdown | ✅ Remplacer par autocomplete |
| 6 | Liste des clubs internationaux | <= 2 | Select dropdown (verification existence) | ✅ Remplacer par autocomplete |

### 2.2 Mise a jour d'un club

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Selectionner un club (dropdown) | <= 2 | Select avec tous les clubs | ✅ Remplacer par autocomplete |
| 2 | Modifier adresse postale | <= 2 | Input text (maxlength 100) | ✅ Conserver |
| 3 | Modifier site internet | <= 2 | Input text (maxlength 60) | ✅ Conserver |
| 4 | Modifier adresse email | <= 2 | Input text (maxlength 40) | ✅ Conserver |
| 5 | Modifier coordonnees GPS (lat,long) | <= 2 | Input text + champ hidden coord2 | ✅ Simplifier (un seul champ) |
| 6 | Bouton Mettre a jour | <= 2 | Submit formulaire | ✅ Conserver (PATCH API) |

### 2.3 Ajout d'un comite departemental / pays

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Selectionner le comite regional d'appartenance | <= 2 | Select dropdown kp_cr | ✅ Remplacer par autocomplete |
| 2 | Saisir code CD (maxlength 5) | <= 2 | Input text | ✅ Conserver |
| 3 | Saisir libelle CD (maxlength 50) | <= 2 | Input text | ✅ Conserver |
| 4 | Bouton Ajouter | <= 2 | Submit formulaire | ✅ Conserver (POST API) |

### 2.4 Ajout d'un club / structure

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Selectionner le comite departemental / pays | <= 2 | Select dropdown kp_cd | ✅ Remplacer par autocomplete |
| 2 | Verification structures internationales existantes | <= 2 | Select dropdown pour verifier | ✅ Remplacer par autocomplete + verification en temps reel |
| 3 | Saisir code club (maxlength 5) | <= 2 | Input text | ✅ Conserver |
| 4 | Saisir libelle club (maxlength 50, uppercase) | <= 2 | Input text + auto-uppercase | ✅ Conserver |
| 5 | Saisir adresse postale | <= 2 | Input text (maxlength 100) | ✅ Conserver |
| 6 | Saisir adresse internet | <= 2 | Input text (maxlength 60) | ✅ Conserver |
| 7 | Saisir adresse email | <= 2 | Input text (maxlength 40) | ✅ Conserver |
| 8 | Saisir coordonnees GPS | <= 2 | Input text (maxlength 60) | ✅ Conserver |
| 9 | Creer une nouvelle equipe pour ce club | <= 2 | Input text + autocomplete (maxlength 40) | ✅ Conserver |
| 10 | Affecter l'equipe a une competition (checkbox) | <= 2 | Checkbox avec code competition | ❌ Supprime (utiliser page Equipes) |
| 11 | Bouton Ajouter | <= 2 | Submit formulaire | ✅ Conserver (POST API) |

### 2.5 Ameliorations par rapport au legacy

| # | Amelioration | Description |
|---|--------------|-------------|
| 1 | Autocomplete au lieu de selects | Remplacer les selects clubs/CR/CD par des champs autocomplete avec recherche en temps reel |
| 2 | Carte responsive | Carte pleine largeur adaptative au lieu de taille fixe 620x550 |
| 3 | Layout reorganise | Carte en haut, formulaires en dessous (ou layout adaptatif desktop/mobile) |
| 4 | Liste tabulaire des clubs | Ajouter un mode liste/tableau des clubs avec recherche textuelle |
| 5 | Validation en temps reel | Verification code club unique avant soumission |
| 6 | Formulaires en modals | Ajout CD et ajout club dans des modals separees |
| 7 | Toast notifications | Retour visuel moderne au lieu d'alerts JavaScript |
| 8 | Suppression champ coord2 | Le legacy maintient `coord` (affichage) et `coord2` (terrain) separement. Simplifier en un seul champ GPS |

---

## 3. Decisions de conception

| # | Question | Decision |
|---|----------|----------|
| Q1 | Affectation equipe a une competition lors de la creation du club | **Supprimee**. L'equipe peut etre ajoutee ensuite depuis la page Equipes `/teams`. |
| Q2 | Droits specifiques utilisateurs (229824, 115989) | **Attribues aux profils 1 et 2**. Les exceptions hardcodees sont supprimees ; ces fonctionnalites sont accessibles a tous les profils <= 2 (ROLE_ADMIN). |
| Q3 | Mode "Pool" (equipe affectee a code_saison=1000) | **Conserve**. Le mode Pool correspond a la gestion du pool d'arbitres : arbitres disponibles hors equipe, a travers toutes les saisons. Non gere dans cette page (fonctionnalite specifique arbitres). |
| Q4 | Mise a jour des clubs (lien json-clubs.php) | **Deplace vers la page Operations** (`/operations`). |
| Q5 | Carte interactive - scope | **Tous les clubs avec coordonnees GPS** sont affiches, qu'ils aient une equipe inscrite ou non. |

---

## 4. Structure de la Page

### 4.1 Vue Desktop

```
+---------------------------------------------------------------------------+
|  Gestion des Clubs                                                         |
+---------------------------------------------------------------------------+
| [Recherche club...     ]  [Filtre CR ▼]  [Filtre CD ▼]                    |
+---------------------------------------------------------------------------+
|                                                                             |
|  +--- Carte Leaflet (pleine largeur, hauteur ~500px) -------------------+  |
|  |                                                                       |  |
|  |   [marqueurs clubs sur OpenStreetMap]                                 |  |
|  |                                                                       |  |
|  +-----------------------------------------------------------------------+  |
|  [Adresse, Ville, Pays...                          ] [Localiser]           |
|                                                                             |
|  +--- Panneau club selectionne (ou tableau des clubs) ------------------+  |
|  |  Club : [Autocomplete recherche club...          ]                    |  |
|  |  Code : 1234  |  Libelle : KAYAK CLUB DE PARIS                       |  |
|  |  Adresse : [                                   ]                      |  |
|  |  Site web : [                                  ]                      |  |
|  |  Email : [                                     ]                      |  |
|  |  Coordonnees GPS : [                           ]                      |  |
|  |                                    [Mettre a jour]                    |  |
|  +-----------------------------------------------------------------------+  |
|                                                                             |
|  +--- Actions Admin (profil <= 2) -------------------------------------+  |
|  |  [+ Ajouter un CD/Pays]    [+ Ajouter un Club]                       |  |
|  +-----------------------------------------------------------------------+  |
+---------------------------------------------------------------------------+
```

### 4.2 Vue Mobile

```
+----------------------------------+
|  Gestion des Clubs               |
+----------------------------------+
| [Recherche club...             ] |
+----------------------------------+
|  +--- Carte (pleine largeur) --+ |
|  |  [OpenStreetMap]             | |
|  +------------------------------+ |
|  [Localiser une adresse...]      |
+----------------------------------+
|  Club : [Autocomplete...]        |
|  Adresse : [                   ] |
|  Site web : [                  ] |
|  Email : [                     ] |
|  GPS : [                       ] |
|           [Mettre a jour]        |
+----------------------------------+
| [+ Ajouter CD] [+ Ajouter Club] |
+----------------------------------+
```

---

## 5. Modal Ajout Comite Departemental

### 5.1 Champs

| Champ | Type | Requis | Validation | Description |
|-------|------|--------|------------|-------------|
| Comite Regional | Autocomplete | Oui | Doit exister dans kp_cr | CR d'appartenance |
| Code | Text | Oui | Max 5 caracteres, unique dans kp_cd | Code du nouveau CD |
| Libelle | Text | Oui | Max 50 caracteres, non vide | Nom du CD/pays |

### 5.2 Comportement

- Le champ Comite Regional est un autocomplete sur `/admin/regional-committees`
- A la soumission : POST `/admin/departmental-committees`
- En cas de code duplique : erreur 422
- Confirmation toast en cas de succes

---

## 6. Modal Ajout Club

### 6.1 Champs

| Champ | Type | Requis | Validation | Description |
|-------|------|--------|------------|-------------|
| Comite Departemental | Autocomplete | Oui | Doit exister dans kp_cd | CD d'appartenance |
| Code | Text | Oui | Max 5 caracteres, unique dans kp_club | Code du nouveau club |
| Libelle | Text | Oui | Max 50 caracteres, non vide, uppercase | Nom du club |
| Adresse postale | Text | Non | Max 100 caracteres | Adresse |
| Site internet | Text | Non | Max 60 caracteres | URL du site |
| Adresse email | Text | Non | Max 40 caracteres | Email de contact |
| Coordonnees GPS | Text | Non | Max 60 caracteres, format "lat, long" | Position GPS |
| Nouvelle equipe | Text | Non | Max 40 caracteres | Nom de l'equipe a creer (optionnel) |

### 6.2 Comportement

- Le champ CD est un autocomplete sur `/admin/departmental-committees`
- Le libelle est automatiquement converti en majuscules
- Le champ "Nouvelle equipe" est optionnel : si rempli, une entree `kp_equipe` est creee avec le club
- L'autocomplete du nom d'equipe utilise l'endpoint existant (`Autocompl_equipe.php` → a migrer en API2)
- Verification que le code club n'existe pas deja (validation cote client + API)
- A la soumission : POST `/admin/clubs`
- Toast de confirmation en cas de succes

### 6.3 Aide au nommage des equipes

Tooltip ou texte d'aide rappelant les conventions de nommage :
- Nom d'equipe en minuscule, premiere lettre en majuscule
- Espace avant le numero d'ordre et avant la categorie
- Numero d'ordre obligatoire en chiffre romain : I II III IV
- Categorie feminine avec " F" (" Ladies" ou " Women" pour les etrangeres)
- Categorie jeunes avec " JF" ou " JH"
- Categorie -21 ans avec " -21" (" U21" pour les etrangeres)
- Exemples : Acigne II, Acigne I F, Acigne JH, Belgium U21 Women

---

## 7. Mise a jour d'un club

### 7.1 Selection du club

- Autocomplete sur `/admin/clubs/search?q=...` (endpoint existant)
- Affiche code + libelle
- A la selection : les champs du formulaire sont pre-remplis avec les donnees du club
- Sur la carte : le marqueur du club est mis en surbrillance et la carte est centree dessus

### 7.2 Champs editables

| Champ | Type | Validation | Colonne DB |
|-------|------|------------|------------|
| Adresse postale | Text | Max 100 caracteres | kp_club.Postal |
| Site internet | Text | Max 60 caracteres | kp_club.www |
| Adresse email | Text | Max 40 caracteres | kp_club.email |
| Coordonnees GPS | Text | Max 60 caracteres, format "lat, long" | kp_club.Coord |

### 7.3 Champ coord2

Le legacy maintient deux champs : `Coord` (coordonnees club, affiche dans le formulaire) et `Coord2` (coordonnees terrain, champ hidden). Dans le legacy, `Coord2` est toujours synchronise avec la valeur du formulaire `coord2` qui reprend la valeur de `Coord`.

**Decision** : Ne conserver qu'un seul champ GPS (`Coord`). Mettre `Coord2` a la meme valeur que `Coord` pour compatibilite descendante avec le code legacy qui lit `Coord2`.

### 7.4 Interaction carte ↔ formulaire

- Selection d'un club dans l'autocomplete → centre la carte sur son marqueur, ouvre le popup
- Clic sur un marqueur de la carte → selectionne le club dans l'autocomplete, remplit le formulaire
- Apres mise a jour reussie → met a jour le marqueur sur la carte si les coordonnees GPS ont change

---

## 8. Carte Interactive

### 8.1 Configuration

- Bibliotheque : Leaflet (deja utilise dans le legacy)
- Tuiles : OpenStreetMap (`https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`)
- Centre par defaut : France (46.85, 1.75), zoom 5
- Icone marqueur club : bleue (`Map-Marker-Ball-Right-Azure-icon.png`)
- Icone marqueur recherche : rouge/bronze (`Map-Marker-Ball-Left-Bronze-icon.png`)

### 8.2 Chargement des marqueurs

- Appel API `/admin/clubs/map` au chargement de la page
- Retourne tous les clubs ayant des coordonnees GPS (qu'ils aient une equipe ou non)
- Chaque marqueur affiche un popup avec le nom du club au clic

### 8.3 Geocodage

- Service Nominatim (OpenStreetMap) pour la recherche par adresse
- Le resultat est affiche par un marqueur rouge temporaire sur la carte
- Les coordonnees trouvees peuvent etre copiees dans le formulaire

### 8.4 Integration Nuxt

Leaflet sera charge via un composant client-only (`<ClientOnly>`) car il necessite l'acces au DOM. Utiliser le package `leaflet` npm.

---

## 9. Endpoints API2

### 9.1 Endpoints existants (dans AdminTeamsController)

| Methode | Endpoint | Description | Parametres |
|---------|----------|-------------|------------|
| GET | `/admin/clubs/search` | Autocomplete clubs | `?q=` (min 2 chars), `?limit=` |
| GET | `/admin/regional-committees` | Liste comites regionaux | - |
| GET | `/admin/departmental-committees` | Liste comites departementaux | `?cr=` (filtre par CR) |
| GET | `/admin/clubs` | Liste clubs | `?cd=` (filtre par CD) |

### 9.2 Nouveaux endpoints a creer

| Methode | Endpoint | Description | Body / Params | Profil |
|---------|----------|-------------|---------------|--------|
| GET | `/admin/clubs/map` | Clubs avec coordonnees GPS pour la carte | - | <= 10 |
| GET | `/admin/clubs/{code}` | Detail d'un club | - | <= 10 |
| PATCH | `/admin/clubs/{code}` | Mettre a jour un club (coord, postal, www, email) | `{ postal, www, email, coord }` | <= 2 |
| POST | `/admin/clubs` | Creer un club + optionnellement une equipe | voir 9.3 | <= 2 |
| POST | `/admin/departmental-committees` | Creer un CD | `{ code, libelle, codeComiteReg }` | <= 2 |

### 9.3 Body POST /admin/clubs

```json
{
  "code": "1234",
  "libelle": "KAYAK CLUB DE PARIS",
  "codeComiteDep": "075",
  "postal": "12 rue de Paris - 75001 Paris",
  "www": "http://www.kcparis.fr",
  "email": "contact@kcparis.fr",
  "coord": "48.856614, 2.3522219",
  "equipe": {
    "libelle": "KCP I"
  }
}
```

Le champ `equipe` est optionnel. Si present, une entree `kp_equipe` est creee avec le club.

### 9.4 Reponse GET /admin/clubs/map

```json
{
  "clubs": [
    {
      "code": "1234",
      "libelle": "KAYAK CLUB DE PARIS",
      "coord": "48.856614, 2.3522219",
      "postal": "12 rue de Paris - 75001 Paris",
      "www": "http://www.kcparis.fr",
      "email": "contact@kcparis.fr"
    }
  ]
}
```

### 9.5 Reponse GET /admin/clubs/{code}

```json
{
  "code": "1234",
  "libelle": "KAYAK CLUB DE PARIS",
  "codeComiteDep": "075",
  "libelleComiteDep": "Paris",
  "coord": "48.856614, 2.3522219",
  "coord2": "48.856614, 2.3522219",
  "postal": "12 rue de Paris - 75001 Paris",
  "www": "http://www.kcparis.fr",
  "email": "contact@kcparis.fr"
}
```

### 9.6 Codes de retour

| Code | Cas |
|------|-----|
| 200 | Succes (GET, PATCH) |
| 201 | Creation reussie (POST) |
| 400 | Donnees invalides |
| 403 | Profil insuffisant |
| 404 | Club / CD non trouve |
| 409 | Code club deja existant (POST) |
| 422 | Validation echouee (code CD duplique, CR inexistant, etc.) |

### 9.7 Logique backend specifique

**POST /admin/clubs :**
1. Valider les champs (code non vide, libelle non vide, CD existant)
2. Verifier l'unicite du code club dans `kp_club`
3. Transaction : inserer le club
4. Si `equipe.libelle` fourni :
   - Inserer dans `kp_equipe` (Code_club = nouveau code, Libelle = equipe.libelle)
5. Commit transaction
6. Logger dans le journal d'audit

**POST /admin/departmental-committees :**
1. Valider les champs
2. Verifier l'unicite du code dans `kp_cd`
3. Verifier que le code CR existe dans `kp_cr`
4. Inserer dans `kp_cd`
5. Logger dans le journal d'audit

**PATCH /admin/clubs/{code} :**
1. Verifier que le club existe
2. Mettre a jour les champs : Coord, Coord2 (= Coord), Postal, www, email
3. Logger dans le journal d'audit

**GET /admin/clubs/map :**
1. SELECT c.Code, c.Libelle, c.Coord, c.Postal, c.www, c.email FROM kp_club c WHERE c.Coord IS NOT NULL AND c.Coord != '' ORDER BY c.Code
2. Retourner la liste

---

## 10. Schema de donnees

### 10.1 Table `kp_club`

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| Code | varchar(6) | Non | Cle primaire, code unique du club |
| Libelle | varchar(100) | Non | Nom du club |
| Officiel | char(1) | Oui | Officiel (O/N) |
| Reserve | varchar(20) | Oui | Reserve |
| Code_comite_dep | varchar(6) | Non | FK vers kp_cd.Code |
| Coord | varchar(50) | Oui | Coordonnees GPS "lat, long" |
| Postal | varchar(100) | Oui | Adresse postale |
| Coord2 | varchar(60) | Oui | Coordonnees terrain |
| www | varchar(60) | Oui | Site internet |
| email | varchar(60) | Oui | Adresse email |

### 10.2 Table `kp_cd` (Comite Departemental)

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| Code | varchar(6) | Non | Cle primaire |
| Libelle | varchar(100) | Non | Nom |
| Officiel | char(1) | Oui | Officiel (O/N) |
| Reserve | varchar(20) | Oui | Reserve |
| Code_comite_reg | varchar(6) | Non | FK vers kp_cr.Code |

### 10.3 Table `kp_cr` (Comite Regional)

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| Code | varchar(6) | Non | Cle primaire |
| Libelle | varchar(100) | Non | Nom |
| Officiel | char(1) | Oui | Officiel (O/N) |
| Reserve | varchar(20) | Oui | Reserve |

### 10.4 Table `kp_equipe`

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| Numero | smallint(6) | Non | Cle primaire auto-increment |
| Libelle | varchar(30) | Non | Nom de l'equipe |
| Code_club | varchar(6) | Non | FK vers kp_club.Code |
| color1 | varchar(30) | Oui | Couleur primaire |
| color2 | varchar(30) | Oui | Couleur secondaire |
| colortext | varchar(30) | Oui | Couleur du texte |
| logo | varchar(50) | Oui | Chemin logo |

### 10.5 Contraintes de cles etrangeres

```sql
kp_club.Code_comite_dep → kp_cd.Code
kp_cd.Code_comite_reg → kp_cr.Code
kp_equipe.Code_club → kp_club.Code
```

---

## 11. Composants Vue

### 11.1 Structure des fichiers

```
sources/app4/pages/clubs/
└── index.vue              # Page principale

sources/app4/components/admin/
├── ClubMap.client.vue     # Carte Leaflet (client-only)
└── ClubForm.vue           # Formulaire mise a jour club (optionnel)
```

### 11.2 Composants reutilises

| Composant | Usage |
|-----------|-------|
| `AdminToolbar` | Barre de recherche |
| `AdminModal` | Modals ajout CD / ajout Club |
| `AdminConfirmModal` | Confirmation si necessaire |

### 11.3 Dependances

- `leaflet` : Carte interactive (npm package)
- `<ClientOnly>` ou suffixe `.client.vue` pour le composant carte (pas de SSR)

### 11.4 Pas de dependance au contexte de travail

Cette page est **globale** : elle n'utilise pas `workContextStore` et ne depend ni d'une saison ni d'un perimetre de competition.

---

## 12. Menu de Navigation

### 12.1 Emplacement existant

La page Clubs est deja dans le menu sous "Clubs" dans la section Administration.

### 12.2 Definition

| Propriete | Valeur |
|-----------|--------|
| Label FR | Clubs |
| Label EN | Clubs |
| Route | `/clubs` |
| Icone | `heroicons:building-office-2` |
| Profil min | <= 10 |

---

## 13. Traductions i18n

### 13.1 Cles francaises (`fr.json`)

```json
{
  "clubs": {
    "title": "Gestion des Clubs",
    "search_placeholder": "Rechercher un club (nom ou code)...",
    "map": {
      "title": "Carte des clubs",
      "search_address": "Adresse, Ville, Pays",
      "locate": "Localiser",
      "no_club_on_map": "Si votre club n'apparait pas sur la carte, transmettez ses coordonnees a contact@kayak-polo.info.",
      "geocode_failed": "Echec du geocodage. Adresse non trouvee."
    },
    "update": {
      "title": "Modifier un club",
      "select_club": "Rechercher un club...",
      "postal": "Adresse postale",
      "postal_hint": "ex: 27 rue du Bout du Monde - 99000 La Fin",
      "www": "Site internet",
      "www_hint": "ex: http://www.monsite.fr",
      "email": "Adresse email",
      "email_hint": "Eviter si possible les adresses personnelles",
      "coord": "Coordonnees GPS lat,long",
      "coord_hint": "ex: 48.856614, 2.3522219",
      "submit": "Mettre a jour",
      "select_first": "Selectionnez un club pour le modifier.",
      "success": "Club mis a jour.",
      "error": "Erreur lors de la mise a jour."
    },
    "add_cd": {
      "title": "Ajouter un Comite Departemental / Pays",
      "comite_reg": "Comite Regional d'appartenance",
      "comite_reg_placeholder": "Rechercher un comite regional...",
      "code": "Code",
      "libelle": "Nom du comite departemental / pays",
      "submit": "Ajouter",
      "success": "Comite departemental ajoute.",
      "error_no_cr": "Selectionnez un Comite Regional.",
      "error_empty": "Le nom ou le code est vide."
    },
    "add_club": {
      "title": "Ajouter un Club / une Structure",
      "comite_dep": "Comite Departemental / Pays d'appartenance",
      "comite_dep_placeholder": "Rechercher un CD / pays...",
      "check_existing": "Verifier que la structure n'existe pas deja",
      "code": "Code",
      "libelle": "Nom du club / structure",
      "postal": "Adresse postale",
      "www": "Adresse Internet",
      "email": "Adresse email",
      "coord": "Coordonnees GPS",
      "equipe": "Nouvelle equipe (optionnel)",
      "equipe_help": "Respectez le formalisme : Nom en minuscule (1ere lettre majuscule), espace avant numero romain (I, II, III), espace avant categorie (F, JH, JF, -21). Ex: Acigne II, Acigne I F",
      "submit": "Ajouter",
      "success": "Club ajoute.",
      "error_no_cd": "Selectionnez un Comite Departemental.",
      "error_empty": "Le nom ou le code du club est vide.",
      "error_duplicate": "Ce code club existe deja."
    }
  }
}
```

### 13.2 Cles anglaises (`en.json`)

```json
{
  "clubs": {
    "title": "Clubs Management",
    "search_placeholder": "Search club (name or code)...",
    "map": {
      "title": "Clubs map",
      "search_address": "Address, City, Country",
      "locate": "Locate",
      "no_club_on_map": "If your club is not on the map, send its coordinates to contact@kayak-polo.info.",
      "geocode_failed": "Geocoding failed. Address not found."
    },
    "update": {
      "title": "Update a club",
      "select_club": "Search a club...",
      "postal": "Postal address",
      "postal_hint": "e.g.: 27 rue du Bout du Monde - 99000 La Fin",
      "www": "Website",
      "www_hint": "e.g.: http://www.mysite.com",
      "email": "Email address",
      "email_hint": "Avoid personal email addresses if possible",
      "coord": "GPS coordinates lat,long",
      "coord_hint": "e.g.: 48.856614, 2.3522219",
      "submit": "Update",
      "select_first": "Select a club to edit.",
      "success": "Club updated.",
      "error": "Error updating club."
    },
    "add_cd": {
      "title": "Add a Departmental Committee / Country",
      "comite_reg": "Regional Committee",
      "comite_reg_placeholder": "Search a regional committee...",
      "code": "Code",
      "libelle": "Departmental committee / country name",
      "submit": "Add",
      "success": "Departmental committee added.",
      "error_no_cr": "Select a Regional Committee.",
      "error_empty": "Name or code is empty."
    },
    "add_club": {
      "title": "Add a Club / Organization",
      "comite_dep": "Departmental Committee / Country",
      "comite_dep_placeholder": "Search a committee / country...",
      "check_existing": "Check that the organization does not already exist",
      "code": "Code",
      "libelle": "Club / organization name",
      "postal": "Postal address",
      "www": "Website",
      "email": "Email address",
      "coord": "GPS coordinates",
      "equipe": "New team (optional)",
      "equipe_help": "Follow naming conventions: Name in lowercase (1st letter uppercase), space before Roman numeral (I, II, III), space before category (F, JH, JF, -21). E.g.: Acigne II, Acigne I F",
      "submit": "Add",
      "success": "Club added.",
      "error_no_cd": "Select a Departmental Committee.",
      "error_empty": "Club name or code is empty.",
      "error_duplicate": "This club code already exists."
    }
  }
}
```

---

## 14. Securite

### 14.1 Controle d'acces

| Operation | Profil requis | Role Symfony |
|-----------|--------------|--------------|
| Consultation carte + liste | <= 10 | ROLE_USER |
| Mise a jour club (coord, postal, www, email) | <= 2 | ROLE_ADMIN |
| Ajout comite departemental | <= 2 | ROLE_ADMIN |
| Ajout club + equipe | <= 2 | ROLE_ADMIN |

### 14.2 Validation backend

- Code club : unique, max 6 caracteres
- Code CD : unique, max 6 caracteres, CR existant
- Libelles : non vides, longueurs maximales respectees
- Coordonnees GPS : format libre (validation optionnelle du format "lat, long")

### 14.3 Journal d'audit

Toutes les operations (ajout CD, ajout club, mise a jour club) sont loguees via `utyJournal()` dans la table `kp_journal`.

---

## 15. Notes de migration

### 15.1 Differences avec le legacy

| Aspect | Legacy (PHP) | App4 (Nuxt) |
|--------|-------------|-------------|
| Layout | 2 colonnes (carte + formulaire) | Carte pleine largeur + formulaire en dessous / modals |
| Selects clubs/CR/CD | Selects HTML charges au demarrage | Autocomplete avec recherche API |
| Ajout CD/Club | Formulaires inline | Modals |
| Carte | Taille fixe 620x550 | Responsive pleine largeur |
| Retour utilisateur | alert() JavaScript | Toast notifications |
| Affectation equipe | Via session codeCompet | Supprimee (utiliser page Equipes) |
| Droits hardcodes | Exceptions utilisateurs 229824/115989 | Supprimees, fonctionnalites attribuees aux profils <= 2 |
| Lien json-clubs.php | Lien direct | Deplace vers la page Operations |
| Scope carte | Clubs avec equipe uniquement | Tous les clubs avec coordonnees GPS |

### 15.2 Migration Leaflet

Le legacy charge Leaflet via CDN dans le template PHP. Dans app4 :
- Installer `leaflet` via npm : `make app4_npm_add package=leaflet`
- Creer un composant `.client.vue` pour eviter le SSR
- Les icones de marqueurs personnalisees sont dans `sources/img/` → copier dans `app4/public/` ou referencer depuis le dossier parent

### 15.3 Endpoints existants reutilisables

Les endpoints suivants existent deja dans `AdminTeamsController` et peuvent etre reutilises directement :
- `GET /admin/clubs/search` : Autocomplete clubs
- `GET /admin/regional-committees` : Liste CR
- `GET /admin/departmental-committees` : Liste CD
- `GET /admin/clubs` : Liste clubs filtree par CD

### 15.4 Endpoints a creer

- `GET /admin/clubs/map` : Clubs avec coordonnees pour la carte
- `GET /admin/clubs/{code}` : Detail d'un club
- `PATCH /admin/clubs/{code}` : Mise a jour coordonnees/contact
- `POST /admin/clubs` : Creation club + equipe optionnelle
- `POST /admin/departmental-committees` : Creation CD

---

**Document cree le** : 18 fevrier 2026
**Derniere mise a jour** : 18 fevrier 2026
**Statut** : VALIDE
**Auteur** : Claude Code
