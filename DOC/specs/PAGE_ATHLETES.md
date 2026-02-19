# Specification - Page Athletes

## Statut : BROUILLON

## 1. Vue d'ensemble

Page de consultation et gestion des athletes (joueurs, arbitres, officiels). Permet de rechercher un athlete par nom/prenom/licence, d'afficher ses informations generales (identite, club, licence, certificats, pagaies, arbitrage), et de consulter ses participations pour une saison donnee (feuilles de presence, officiels, matchs joues).

**Route** : `/athletes`

**Acces** :
- Profil <= 10 : Consultation athlete (lecture seule)
- Profil <= 6 : Recherche avancee
- Profil <= 2 : Modification des informations athlete (identite, club, arbitrage)

**Page PHP Legacy** : `GestionAthlete.php` + `GestionAthlete.tpl` + `GestionAthlete.js`

**Contexte de travail** : Partiellement utilise. La saison du contexte de travail est utilisee par defaut pour les participations, mais l'utilisateur peut changer de saison independamment.

---

## 2. Fonctionnalites

### 2.1 Recherche d'un athlete

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Recherche par nom, prenom ou numero de licence (autocomplete) | <= 10 | Input text + autocomplete jQuery (Autocompl_joueur3.php) | ✅ Conserver - migrer vers endpoint API2 |
| 2 | Recherche avancee (iframe RechercheLicenceIndi2) | <= 6 | Iframe togglable avec formulaire multi-criteres | ❌ Supprime - remplacer par filtres dans l'autocomplete |
| 3 | Bouton "Mis a jour le" (affichage date) | <= 10 | Bouton submit | ❌ Supprime (inutile dans app4) |

### 2.2 Informations generales de l'athlete

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Numero de licence (Matric) | <= 10 | Affichage en titre | ✅ Conserver |
| 2 | Identite : Nom, Prenom, Sexe, Date de naissance | <= 10 | Nom en majuscules, prenom capitalise | ✅ Conserver |
| 3 | Numero ICF (Reserve) | <= 10 | Affiche si Matric > 2000000 et Reserve non null | ✅ Conserver |
| 4 | Surclassement (date) | <= 10 | Affiche si surclassement existe pour la saison | ✅ Conserver |
| 5 | Club : code, libelle, CD, CR | <= 10 | Code + libelle club, CD, CR | ✅ Conserver |
| 6 | Derniere saison (Origine) | <= 10 | Annee de la derniere licence | ✅ Conserver |
| 7 | Pagaie couleur : Eau vive, Mer, Eau calme | <= 10 | Couleurs (Noire, Rouge, etc.) | ✅ Conserver |
| 8 | Certificats : APS (Loisir), CK (Competition) | <= 10 | Etat OUI/NON | ✅ Conserver |
| 9 | Arbitrage : niveau, saison, livret | <= 10 | Niveau (Reg/Nat/Int/OTM/JO) + lettre (A/B/C/S) + saison + livret | ✅ Conserver |

### 2.3 Modification d'un athlete (profil <= 2)

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Modifier nom | <= 2 | Input text | ✅ Conserver |
| 2 | Modifier prenom | <= 2 | Input text | ✅ Conserver |
| 3 | Modifier sexe (M/F) | <= 2 | Select | ✅ Conserver |
| 4 | Modifier date de naissance | <= 2 | Input text + calendrier | ✅ Conserver (datepicker) |
| 5 | Modifier derniere saison | <= 2 | Input tel (4 chars) | ✅ Conserver |
| 6 | Modifier numero ICF | <= 2 | Input tel | ✅ Conserver |
| 7 | Modifier qualification arbitrage (Reg/Nat/Int/OTM/JO/-) | <= 2 | Select | ✅ Conserver |
| 8 | Modifier niveau arbitrage (A/B/C/S/-) | <= 2 | Select | ✅ Conserver |
| 9 | Changer de club (autocomplete) | <= 2 | Autocomplete club → remplit aussi CD et CR | ✅ Conserver |
| 10 | Restriction : Matric > 2000000 uniquement | <= 2 | Condition PHP | ✅ Conserver (seuls les athletes non-federaux sont modifiables) |

### 2.4 Participations par saison

Selecteur de saison permettant de changer la saison des participations (par defaut : saison du contexte de travail).

#### 2.4.1 Feuilles de presence

| # | Colonne | Source | Description |
|---|---------|--------|-------------|
| 1 | Competition | kp_competition_equipe.Code_compet | Code de la competition |
| 2 | Equipe | kp_competition_equipe.Libelle | Nom de l'equipe |
| 3 | # | kp_competition_equipe_joueur.Numero | Numero du joueur |
| 4 | Role | kp_competition_equipe_joueur.Capitaine | C=Capitaine, E=Entraineur, A=Arbitre, X=Inactif, -=Joueur |
| 5 | Categorie | kp_competition_equipe_joueur.Categ | Categorie d'age |

**Filtre** : Exclut les competitions avec Code_compet = 'POOL'.

#### 2.4.2 Officiels (Arbitrage + Table de marque)

Tableau unifie regroupant toutes les fonctions officielles de l'athlete : arbitrage (principal/secondaire) et table de marque (secretaire, chronometreur, timekeeper, juge de ligne).

Les arbitrages sont retrouves via Matric_arbitre_principal / Matric_arbitre_secondaire (colonnes numeriques). Les officiels de table de marque sont retrouves par recherche du Matric dans les champs texte (format legacy : le matric est entre parentheses, ex: "Dupont Jean (123456)").

| # | Colonne | Source | Description |
|---|---------|--------|-------------|
| 1 | Date | kp_match.Date_match | Date du match (format court) |
| 2 | Heure | kp_match.Heure_match | Heure du match |
| 3 | Competition | kp_journee.Code_competition | Code competition |
| 4 | Match | kp_match.Numero_ordre | Numero d'ordre du match (lien vers feuille de match) |
| 5 | Arb. Prin. | Calcule | Coche/icone si Matric_arbitre_principal = athlete |
| 6 | Arb. Sec. | Calcule | Coche/icone si Matric_arbitre_secondaire = athlete |
| 7 | Secretaire | Calcule | Coche/icone si Secretaire LIKE %(matric)% |
| 8 | Chrono | Calcule | Coche/icone si Chronometre LIKE %(matric)% |
| 9 | T.S | Calcule | Coche/icone si Timeshoot LIKE %(matric)% |
| 10 | Lignes | Calcule | Coche/icone si Ligne1 ou Ligne2 LIKE %(matric)% |

**Tri** : Date decroissante, puis heure decroissante.

**Style** : Les matchs sans score valide (ScoreA/B vide ou '?') sont affiches en italique.

**Note** : Chaque ligne du tableau correspond a un match unique. Un athlete peut cumuler plusieurs roles sur le meme match (ex: arbitre principal + secretaire). L'API effectue une seule requete unifiee (UNION ou OR) pour recuperer tous les matchs ou l'athlete a une fonction officielle.

#### 2.4.3 Matchs joues

Matchs ou l'athlete a participe comme joueur (present dans kp_match_joueur, Capitaine != 'X').

| # | Colonne | Source | Description |
|---|---------|--------|-------------|
| 1 | Date | kp_match.Date_match | Date du match (format court) |
| 2 | Competition | kp_journee.Code_competition | Code competition |
| 3 | Match | kp_match.Numero_ordre | Numero d'ordre (lien vers feuille de match) |
| 4 | Equipes | ceA.Libelle - ceB.Libelle | Equipe de l'athlete en gras |
| 5 | Score | (ScoreA-ScoreB) | Score de l'equipe de l'athlete en gras |
| 6 | # | kp_match_joueur.Numero | Numero de maillot |
| 7 | Role | kp_match_joueur.Capitaine | C=Cap, E=Entraineur, A=Arbitre |
| 8 | Buts | COUNT(kp_match_detail.Id_evt_match = 'B') | Nombre de buts |
| 9 | Vert | COUNT(kp_match_detail.Id_evt_match = 'V') | Cartons verts |
| 10 | Jaune | COUNT(kp_match_detail.Id_evt_match = 'J') | Cartons jaunes |
| 11 | Rouge | COUNT(kp_match_detail.Id_evt_match = 'R') | Cartons rouges |
| 12 | Rouge def. | COUNT(kp_match_detail.Id_evt_match = 'D') | Cartons rouges definitifs |
| 13 | Tir | COUNT(kp_match_detail.Id_evt_match = 'T') | Tirs |
| 14 | Arret | COUNT(kp_match_detail.Id_evt_match = 'A') | Arrets |

**Tri** : Date decroissante, puis heure decroissante.

**Style** :
- Matchs sans score valide : ligne grisee, en italique
- Buts : fond gris, texte en gras si > 0
- Cartons verts : fond vert
- Cartons jaunes : fond jaune
- Cartons rouges / rouges definitifs : fond rouge
- Tirs/Arrets : fond gris

### 2.5 Ameliorations par rapport au legacy

| # | Amelioration | Description |
|---|--------------|-------------|
| 1 | Suppression iframe recherche avancee | Remplacee par autocomplete enrichi avec recherche full-text |
| 2 | Layout responsive | Cartes/sections empilees en mobile au lieu de layout table 2 colonnes |
| 3 | Formulaire edition en modal/accordion | Au lieu d'un formulaire inline toujours visible |
| 4 | Toast notifications | Au lieu d'alert() JavaScript |
| 5 | Liens feuille de match | Lien externe ou modal au lieu de target="_blank" PHP |
| 6 | Couleurs cartons semantiques | Utilisation de badges Tailwind colores au lieu de classes CSS brutes |
| 7 | Onglets pour les participations | Organisation en tabs (Feuilles de presence, Officiels, Matchs) au lieu de tableaux cote a cote |
| 8 | Fusion arbitrage + table de marque | Un seul tableau "Officiels" au lieu de 2 tableaux separes dans le legacy |

---

## 3. Decisions de conception

| # | Question | Decision |
|---|----------|----------|
| Q1 | Recherche avancee (iframe legacy) | **Supprimee**. L'autocomplete API2 suffit pour rechercher par nom/prenom/licence. Un endpoint de recherche enrichi avec filtres optionnels (club, sexe) peut etre ajoute si necessaire. |
| Q2 | Restriction modification Matric > 2000000 | **Conservee**. Les athletes avec Matric <= 2000000 sont des licencies federaux dont les donnees viennent du fichier PCE ; ils ne doivent pas etre modifies manuellement. |
| Q3 | Champ Capitaine dans les matchs | Les valeurs sont : C (Capitaine), E (Entraineur), A (Arbitre), X (Exclu/Inactif), - (Joueur). La valeur X exclut le joueur des matchs joues. |
| Q4 | Arbitrage + Table de marque (OTM) | **Fusionnes en un seul tableau "Officiels"**. Le legacy les separait car les requetes SQL etaient distinctes (colonnes numeriques pour arbitrage vs LIKE texte pour OTM). L'API2 unifie les deux sources et retourne un tableau unique avec toutes les fonctions officielles par match. |
| Q5 | Layout des participations | **3 onglets/tabs** (Presence, Officiels, Matchs) au lieu du layout legacy (2 colonnes : gauche = presence+arbitrage+OTM, droite = matchs). Plus lisible et mieux adapte au mobile. |
| Q6 | Lien feuille de match | Les numeros de match (Numero_ordre) sont des liens cliquables. Pour les profils <= 3, un lien vers la feuille de match est affiche (futur : route app4 ou ouverture legacy). |

---

## 4. Structure de la Page

### 4.1 Vue Desktop

```
+---------------------------------------------------------------------------+
|  [Contexte de travail : Saison 2026 | Perimetre | Modifier]               |
+---------------------------------------------------------------------------+
|  Gestion des Athletes                                                      |
+---------------------------------------------------------------------------+
|  [Rechercher un athlete (nom, prenom, licence)...                       ] |
+---------------------------------------------------------------------------+
|                                                                            |
|  +--- Fiche Athlete ------------------------------------------------+    |
|  |                                                                    |    |
|  |  Licence n° 63155  VIGNET Eric (M) 06/10/1972                     |    |
|  |  ICF #12345 | Surclasse le 15/09/2025                             |    |
|  |                                                                    |    |
|  |  +--- Info Cards (grille 4 colonnes) -------------------------+   |    |
|  |  | Club            | Pagaie         | Certificats | Arbitrage |   |    |
|  |  | 7603 CK LE HAVRE| Eau vive: Noire| APS: OUI    | Niveau:   |   |    |
|  |  | 7600 CD CK...   | Mer: Rouge     | CK: NON     | Nat C     |   |    |
|  |  | CR08 CR NORM..  | Eau calme:Rouge|             | Saison:2026|   |    |
|  |  | Dern. saison:   |                |             | Livret:    |   |    |
|  |  |   2026          |                |             |  2024-NATC |   |    |
|  |  +------------------------------------------------------------+   |    |
|  |                                                                    |    |
|  |  [Modifier] (profil <= 2, Matric > 2000000)                       |    |
|  +--------------------------------------------------------------------+    |
|                                                                            |
|  +--- Participations ------------------------------------------------+    |
|  |  Saison: [2025 ▼]                                                  |    |
|  |                                                                    |    |
|  |  [Presence] [Officiels] [Matchs]                <- Onglets        |    |
|  |                                                                    |    |
|  |  +--- Contenu onglet actif (ex: Matchs) ----------------------+   |    |
|  |  | Date | Comp  | Match | Equipes          | Score | # | B|V|J|R|D|T|A||
|  |  | 05/10| NDWN3 | 29    | Le Havre I-Vann. | (3-3) |n6 | 2| | | | | | ||
|  |  | 05/10| NDWN3 | 27    | Le Havre I-Caen  | (2-5) |n6 |  | | | | | | ||
|  |  | ...                                                         |   |    |
|  |  +-------------------------------------------------------------+   |    |
|  +--------------------------------------------------------------------+    |
+---------------------------------------------------------------------------+
```

### 4.2 Vue Mobile

```
+----------------------------------+
|  Gestion des Athletes            |
+----------------------------------+
| [Rechercher un athlete...]       |
+----------------------------------+
|  Licence n° 63155                |
|  VIGNET Eric (M) 06/10/1972     |
+----------------------------------+
|  Club                            |
|  7603 - CK LE HAVRE             |
|  7600 CD CK DE LA SEINE MAR.   |
|  CR08 CR NORMANDIE CK           |
|  Derniere saison : 2026         |
+----------------------------------+
|  Pagaie eau calme                |
|  EV: Noire | Mer: Rouge         |
|  EC: Rouge                       |
+----------------------------------+
|  Certificats                     |
|  APS (Loisir): OUI              |
|  CK (Competition): NON          |
+----------------------------------+
|  Arbitrage                       |
|  Niveau: Nat C                   |
|  Saison: 2026 | Livret: 2024-NAT|
+----------------------------------+
|  [Modifier]                      |
+----------------------------------+
|  Saison: [2025 ▼]               |
|  [Presence][Officiels][Matchs]  |
|  (contenu onglet actif)         |
|  ...cartes empilees en mobile... |
+----------------------------------+
```

---

## 5. Modal Modification Athlete

### 5.1 Champs

| Champ | Type | Requis | Validation | Colonne DB |
|-------|------|--------|------------|------------|
| Nom | Text | Oui | Non vide, converti en majuscules | kp_licence.Nom |
| Prenom | Text | Oui | Non vide, converti en majuscules | kp_licence.Prenom |
| Sexe | Select (M/F) | Oui | M ou F | kp_licence.Sexe |
| Date de naissance | Date | Oui | Format date valide | kp_licence.Naissance |
| Derniere saison | Text | Oui | 4 caracteres numeriques | kp_licence.Origine |
| Numero ICF | Number | Non | Entier positif ou vide | kp_licence.Reserve |
| Qualification arbitrage | Select | Non | Valeurs: -, Reg, IR, Nat, Int, OTM, JO | kp_arbitre.arbitre |
| Niveau arbitrage | Select | Non | Valeurs: -, A, B, C, S | kp_arbitre.niveau |
| Nouveau club | Autocomplete | Non | Code club existant dans kp_club | kp_licence.Numero_club |

### 5.2 Comportement

- Accessible uniquement si profil <= 2 ET Matric > 2000000
- Le champ "Nouveau club" est un autocomplete sur `/admin/clubs/search` ; a la selection, les champs CD et CR sont automatiquement remplis
- A la soumission : PUT `/admin/athletes/{matric}`
- Transaction : mise a jour kp_licence + kp_competition_equipe_joueur (Nom, Prenom, Sexe) + REPLACE INTO kp_arbitre
- Confirmation toast en cas de succes
- Si qualification arbitrage = '-' : les champs niveau et saison arbitrage sont vides

### 5.3 Logique de mise a jour arbitrage

La mise a jour de la table `kp_arbitre` utilise `REPLACE INTO` (upsert) :

| Qualification | regional | interregional | national | international | arbitre |
|---------------|----------|---------------|----------|---------------|---------|
| Reg | O | N | N | N | Reg |
| IR | N | O | N | N | IR |
| Nat | N | N | O | N | Nat |
| Int | N | N | O | O | Int |
| OTM | N | N | O | N | OTM |
| JO | N | N | O | N | JO |
| - (aucun) | N | N | N | N | (vide) |

---

## 6. Endpoints API2

### 6.1 Endpoints existants reutilisables

| Methode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/admin/clubs/search` | Autocomplete clubs (pour changement de club) |
| GET | `/admin/operations/autocomplete/players` | Recherche joueurs autocomplete |

### 6.2 Nouveaux endpoints a creer

| Methode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| GET | `/admin/athletes/search` | Recherche athlete par nom/prenom/licence (autocomplete) | <= 10 |
| GET | `/admin/athletes/{matric}` | Fiche complete d'un athlete (infos generales) | <= 10 |
| GET | `/admin/athletes/{matric}/participations` | Participations d'un athlete pour une saison | <= 10 |
| PUT | `/admin/athletes/{matric}` | Modifier un athlete (identite + arbitrage) | <= 2 |

### 6.3 GET /admin/athletes/search

**Query Parameters** :
- `q` (requis, min 2 chars) : Terme de recherche (nom, prenom ou matric)
- `limit` (optionnel, defaut 20, max 50)

**Reponse** :
```json
[
  {
    "matric": 63155,
    "nom": "VIGNET",
    "prenom": "Eric",
    "sexe": "M",
    "naissance": "1972-10-06",
    "club": "CK LE HAVRE",
    "codeClub": "7603",
    "label": "VIGNET Eric (63155) - CK LE HAVRE"
  }
]
```

**Logique** : Recherche dans kp_licence.Nom, kp_licence.Prenom (LIKE %q%) et kp_licence.Matric (= q si numerique). Jointure avec kp_club pour le nom du club.

### 6.4 GET /admin/athletes/{matric}

**Reponse** :
```json
{
  "matric": 63155,
  "nom": "VIGNET",
  "prenom": "Eric",
  "sexe": "M",
  "naissance": "1972-10-06",
  "icf": 12345,
  "origine": "2026",
  "club": {
    "code": "7603",
    "libelle": "CK LE HAVRE"
  },
  "comiteDep": {
    "code": "7600",
    "libelle": "CD CK DE LA SEINE MARITIME"
  },
  "comiteReg": {
    "code": "CR08",
    "libelle": "CR NORMANDIE CK"
  },
  "pagaie": {
    "eauVive": "Noire",
    "mer": "Rouge",
    "eauCalme": "Rouge"
  },
  "certificats": {
    "aps": "OUI",
    "ck": "NON"
  },
  "arbitrage": {
    "qualification": "Nat",
    "niveau": "C",
    "saison": "2026",
    "livret": "2024-NATC"
  },
  "surclassement": "2025-09-15",
  "editable": true
}
```

**Notes** :
- `pagaie` : Les valeurs viennent de kp_licence.Pagaie_EVI, Pagaie_MER, Pagaie_ECA. Ce sont des codes (ex: "NO", "RO", "BL") traduits en labels cote API.
- `certificats.aps` = kp_licence.Etat_certificat_APS, `certificats.ck` = kp_licence.Etat_certificat_CK
- `surclassement` : jointure kp_surclassement pour la saison en cours (ou null)
- `editable` : true si Matric > 2000000

### 6.5 GET /admin/athletes/{matric}/participations

**Query Parameters** :
- `season` (requis) : Code saison

**Reponse** :
```json
{
  "season": "2025",
  "presences": [
    {
      "competition": "NDWN3",
      "equipe": "Le Havre I",
      "numero": 6,
      "capitaine": "-",
      "categorie": "V4"
    }
  ],
  "officiels": [
    {
      "date": "2025-10-05",
      "heure": "11:40",
      "competition": "NDWN3",
      "matchId": 12345,
      "matchNumero": 30,
      "arbitrePrincipal": true,
      "arbitreSecondaire": false,
      "secretaire": false,
      "chronometreur": false,
      "timekeeper": false,
      "ligne": false,
      "scoreValide": true
    }
  ],
  "matchs": [
    {
      "date": "2025-10-05",
      "competition": "NDWN3",
      "matchId": 12345,
      "matchNumero": 29,
      "equipeA": "Le Havre I",
      "equipeB": "Vannes",
      "scoreA": "3",
      "scoreB": "3",
      "equipe": "A",
      "numero": 6,
      "capitaine": "-",
      "buts": 2,
      "verts": 0,
      "jaunes": 0,
      "rouges": 0,
      "rougesDefinitifs": 0,
      "tirs": 0,
      "arrets": 0,
      "scoreValide": true
    }
  ]
}
```

### 6.6 PUT /admin/athletes/{matric}

**Profil requis** : <= 2

**Restriction** : Matric > 2000000 (sinon 403)

**Body** :
```json
{
  "nom": "VIGNET",
  "prenom": "ERIC",
  "sexe": "M",
  "naissance": "1972-10-06",
  "origine": "2026",
  "icf": 12345,
  "arbitrage": {
    "qualification": "Nat",
    "niveau": "C"
  },
  "codeClub": "7603"
}
```

**Logique backend** :
1. Verifier Matric > 2000000 (sinon 403 "Modification interdite")
2. Transaction :
   a. UPDATE kp_licence (Origine, Nom, Prenom, Sexe, Naissance, Reserve)
   b. Si codeClub fourni : UPDATE kp_licence (Numero_club, Numero_comite_dept, Numero_comite_reg) en cherchant le CD et CR du club
   c. UPDATE kp_competition_equipe_joueur (Nom, Prenom, Sexe) WHERE Matric = ?
   d. REPLACE INTO kp_arbitre selon la qualification
3. Commit
4. Logger dans kp_journal

### 6.7 Codes de retour

| Code | Cas |
|------|-----|
| 200 | Succes (GET, PUT) |
| 400 | Donnees invalides |
| 403 | Profil insuffisant ou Matric <= 2000000 |
| 404 | Athlete non trouve |

---

## 7. Schema de donnees

### 7.1 Table `kp_licence` (principale)

| Colonne | Type | Description |
|---------|------|-------------|
| Matric | int(11) unsigned | Cle primaire, numero de licence |
| Origine | varchar(6) | Derniere saison (ex: "2026") |
| Nom | varchar(30) | Nom de famille |
| Prenom | varchar(30) | Prenom |
| Sexe | char(1) | M ou F |
| Naissance | date | Date de naissance |
| Club | varchar(100) | Nom du club (legacy, obsolete) |
| Numero_club | varchar(6) | FK vers kp_club.Code |
| Comite_dept | varchar(100) | Nom CD (legacy, obsolete) |
| Numero_comite_dept | varchar(6) | FK vers kp_cd.Code |
| Comite_reg | varchar(100) | Nom CR (legacy, obsolete) |
| Numero_comite_reg | varchar(6) | FK vers kp_cr.Code |
| Etat | varchar(20) | Etat licence |
| Pagaie_EVI | varchar(10) | Pagaie eau vive (code couleur) |
| Pagaie_MER | varchar(10) | Pagaie mer (code couleur) |
| Pagaie_ECA | varchar(10) | Pagaie eau calme (code couleur) |
| Etat_certificat_APS | char(3) | Certificat APS (OUI/NON) |
| Etat_certificat_CK | char(3) | Certificat competition (OUI/NON) |
| Reserve | int(11) | Numero ICF (null si non applicable) |

### 7.2 Table `kp_arbitre`

| Colonne | Type | Description |
|---------|------|-------------|
| Matric | int(10) unsigned | FK vers kp_licence.Matric |
| regional | char(1) | Arbitre regional (O/N) |
| interregional | char(1) | Arbitre interregional (O/N) |
| national | char(1) | Arbitre national (O/N) |
| international | char(1) | Arbitre international (O/N) |
| arbitre | char(3) | Qualification (Reg/Nat/Int/OTM/JO) |
| livret | varchar(25) | Livret d'arbitrage |
| niveau | char(1) | Niveau (A/B/C/S) |
| saison | varchar(4) | Saison de la qualification |

### 7.3 Table `kp_surclassement`

| Colonne | Type | Description |
|---------|------|-------------|
| Matric | int(11) | FK vers kp_licence.Matric |
| Saison | varchar(6) | Code saison |
| Cat | varchar(5) | Categorie |
| Date | date | Date du surclassement |

### 7.4 Tables liees aux participations

- `kp_competition_equipe_joueur` : Feuilles de presence (Id_equipe, Matric, Numero, Capitaine, Categ)
- `kp_competition_equipe` : Equipes de competition (Id, Code_compet, Code_saison, Libelle)
- `kp_match` : Matchs (Id, Id_journee, Matric_arbitre_principal, Matric_arbitre_secondaire, Secretaire, Chronometre, Timeshoot, Ligne1, Ligne2, etc.)
- `kp_match_joueur` : Joueurs d'un match (Id_match, Matric, Numero, Equipe, Capitaine)
- `kp_match_detail` : Evenements du match (Id_match, Competiteur, Id_evt_match: B/V/J/R/D/T/A)
- `kp_journee` : Journees (Id, Code_competition, Code_saison)

---

## 8. Composants Vue

### 8.1 Structure des fichiers

```
sources/app4/pages/athletes/
└── index.vue              # Page principale

sources/app4/components/admin/
└── AthleteEditModal.vue   # Modal modification athlete (optionnel, peut etre inline)
```

### 8.2 Composants reutilises

| Composant | Usage |
|-----------|-------|
| `AdminToolbar` | Barre de recherche athlete (slot unique, pas de bulk delete ni add) |
| `AdminModal` | Modal modification athlete |

### 8.3 Pas de dependance stricte au contexte de travail

La page utilise `workContextStore.season` comme valeur par defaut de la saison des participations, mais l'utilisateur peut changer cette saison via un selecteur dedie. La page ne filtre PAS par competition/section/groupe du contexte.

---

## 9. Menu de Navigation

### 9.1 Emplacement

La page Athletes est dans le menu sous "Athletes" dans la section Administration.

### 9.2 Definition

| Propriete | Valeur |
|-----------|--------|
| Label FR | Athletes |
| Label EN | Athletes |
| Route | `/athletes` |
| Icone | `heroicons:user-group` |
| Profil min | <= 10 |

---

## 10. Traductions i18n

### 10.1 Cles francaises (`fr.json`)

```json
{
  "athletes": {
    "title": "Gestion des Athletes",
    "search_placeholder": "Rechercher un athlete (nom, prenom, licence)...",
    "no_athlete_selected": "Recherchez un athlete pour afficher sa fiche.",
    "licence": "Licence n°",
    "icf_number": "ICF #",
    "surclasse": "Surclasse le",
    "club": {
      "title": "Club",
      "last_season": "Derniere saison"
    },
    "pagaie": {
      "title": "Pagaie",
      "eau_vive": "Eau vive",
      "mer": "Mer",
      "eau_calme": "Eau calme"
    },
    "certificats": {
      "title": "Certificats",
      "aps": "APS (Loisir)",
      "ck": "CK (Competition)"
    },
    "arbitrage": {
      "title": "Arbitrage",
      "niveau": "Niveau",
      "saison": "Saison",
      "livret": "Livret",
      "qualification": {
        "Reg": "Regional",
        "IR": "Interregional",
        "Nat": "National",
        "Int": "International",
        "OTM": "Officiel table de marque",
        "JO": "Jeune officiel"
      }
    },
    "edit": {
      "title": "Modifier l'athlete",
      "nom": "Nom",
      "prenom": "Prenom",
      "sexe": "Sexe",
      "naissance": "Date de naissance",
      "derniere_saison": "Derniere saison",
      "icf": "Numero ICF",
      "arb_qualification": "Qualification arbitrage",
      "arb_niveau": "Niveau arbitrage",
      "new_club": "Nouveau club",
      "new_club_placeholder": "Rechercher un club...",
      "submit": "Modifier",
      "confirm": "Modifier cet athlete ?",
      "success": "Modification effectuee.",
      "error": "Erreur lors de la modification.",
      "forbidden": "Modification interdite pour cet athlete."
    },
    "participations": {
      "season": "Saison",
      "presence": {
        "title": "Feuilles de presence",
        "competition": "Comp",
        "equipe": "Equipe",
        "numero": "#",
        "categorie": "Categorie",
        "empty": "Aucune feuille de presence pour cette saison."
      },
      "officiels": {
        "title": "Officiels",
        "date": "Date",
        "heure": "Heure",
        "competition": "Comp",
        "match": "Match",
        "arb_principal": "Arb. Prin.",
        "arb_secondaire": "Arb. Sec.",
        "secretaire": "Sec",
        "chronometreur": "Chrono",
        "timekeeper": "T.S",
        "ligne": "Lignes",
        "empty": "Aucune fonction officielle pour cette saison."
      },
      "matchs": {
        "title": "Matchs",
        "date": "Date",
        "competition": "Competition",
        "match": "Match",
        "equipes": "Equipes",
        "score": "Score",
        "numero": "#",
        "buts": "Buts",
        "vert": "Vert",
        "jaune": "Jaune",
        "rouge": "Rouge",
        "rouge_def": "Rouge D.",
        "tir": "Tir",
        "arret": "Arret",
        "empty": "Aucun match pour cette saison."
      }
    },
    "roles": {
      "C": "Cap",
      "E": "Entraineur",
      "A": "Arbitre",
      "X": "Inactif",
      "-": ""
    }
  }
}
```

### 10.2 Cles anglaises (`en.json`)

```json
{
  "athletes": {
    "title": "Athletes Management",
    "search_placeholder": "Search athlete (name, first name, licence)...",
    "no_athlete_selected": "Search for an athlete to view their profile.",
    "licence": "Licence #",
    "icf_number": "ICF #",
    "surclasse": "Upgraded on",
    "club": {
      "title": "Club",
      "last_season": "Last season"
    },
    "pagaie": {
      "title": "Paddle",
      "eau_vive": "White water",
      "mer": "Sea",
      "eau_calme": "Flat water"
    },
    "certificats": {
      "title": "Certificates",
      "aps": "APS (Leisure)",
      "ck": "CK (Competition)"
    },
    "arbitrage": {
      "title": "Refereeing",
      "niveau": "Level",
      "saison": "Season",
      "livret": "Booklet",
      "qualification": {
        "Reg": "Regional",
        "IR": "Interregional",
        "Nat": "National",
        "Int": "International",
        "OTM": "Game official",
        "JO": "Young official"
      }
    },
    "edit": {
      "title": "Edit athlete",
      "nom": "Last name",
      "prenom": "First name",
      "sexe": "Gender",
      "naissance": "Date of birth",
      "derniere_saison": "Last season",
      "icf": "ICF number",
      "arb_qualification": "Referee qualification",
      "arb_niveau": "Referee level",
      "new_club": "New club",
      "new_club_placeholder": "Search a club...",
      "submit": "Update",
      "confirm": "Update this athlete?",
      "success": "Update successful.",
      "error": "Error updating athlete.",
      "forbidden": "Modification forbidden for this athlete."
    },
    "participations": {
      "season": "Season",
      "presence": {
        "title": "Presence sheets",
        "competition": "Comp",
        "equipe": "Team",
        "numero": "#",
        "categorie": "Category",
        "empty": "No presence sheet for this season."
      },
      "officiels": {
        "title": "Officials",
        "date": "Date",
        "heure": "Time",
        "competition": "Comp",
        "match": "Match",
        "arb_principal": "Main Ref.",
        "arb_secondaire": "Sec. Ref.",
        "secretaire": "Sec",
        "chronometreur": "Timer",
        "timekeeper": "T.S",
        "ligne": "Lines",
        "empty": "No official role for this season."
      },
      "matchs": {
        "title": "Matches",
        "date": "Date",
        "competition": "Competition",
        "match": "Match",
        "equipes": "Teams",
        "score": "Score",
        "numero": "#",
        "buts": "Goals",
        "vert": "Green",
        "jaune": "Yellow",
        "rouge": "Red",
        "rouge_def": "Red D.",
        "tir": "Shot",
        "arret": "Save",
        "empty": "No match for this season."
      }
    },
    "roles": {
      "C": "Cap",
      "E": "Coach",
      "A": "Ref.",
      "X": "Unavailable",
      "-": ""
    }
  }
}
```

---

## 11. Securite

### 11.1 Controle d'acces

| Operation | Profil requis | Role Symfony |
|-----------|--------------|--------------|
| Consultation fiche athlete | <= 10 | ROLE_USER |
| Recherche avancee | <= 6 | ROLE_ORGANIZER |
| Modification athlete | <= 2 | ROLE_ADMIN |

### 11.2 Validation backend

- Matric > 2000000 pour les modifications (athletes non-federaux uniquement)
- Code club existant dans kp_club si changement de club
- Qualification arbitrage : valeur parmi Reg, IR, Nat, Int, OTM, JO, vide
- Niveau arbitrage : valeur parmi A, B, C, S, vide
- Transaction pour la mise a jour (atomicite licence + joueurs + arbitre)

### 11.3 Journal d'audit

Les modifications d'athlete sont loguees dans `kp_journal`.

---

## 12. Notes de migration

### 12.1 Differences avec le legacy

| Aspect | Legacy (PHP) | App4 (Nuxt) |
|--------|-------------|-------------|
| Recherche | Autocomplete jQuery + iframe recherche avancee | Autocomplete API2 uniquement |
| Layout participations | 2 colonnes (gauche: presence+arbitrage+OTM, droite: matchs) | 3 onglets (Presence, Officiels, Matchs) |
| Arbitrage + OTM | 2 tableaux separes (Arbitrage + Officiels des matchs) | 1 seul tableau "Officiels" unifie |
| Modification | Formulaire inline toujours visible | Modal (bouton Modifier) |
| Feuille de match | Lien target="_blank" vers FeuilleMatchMulti.php | Lien vers page legacy ou futur composant |
| Retour utilisateur | alert() JavaScript | Toast notifications |
| Recherche avancee | Iframe RechercheLicenceIndi2.php | Supprimee |
| Dates | Format dd/mm (substr) | Format localise via i18n |
| Pagaie couleurs | Codes dans config Smarty | Labels traduits cote API |

### 12.2 Requetes SQL a migrer en API2

1. **Recherche athlete** : Nouvelle requete (LIKE sur Nom/Prenom/Matric dans kp_licence JOIN kp_club)
2. **Fiche athlete** : kp_licence JOIN kp_club, kp_cd, kp_cr LEFT JOIN kp_surclassement + kp_arbitre
3. **Feuilles de presence** : kp_competition_equipe_joueur JOIN kp_competition_equipe WHERE Code_compet != 'POOL'
4. **Officiels (unifie)** : kp_match JOIN kp_journee WHERE Matric_arbitre_principal OR Matric_arbitre_secondaire OR Secretaire/Chronometre/Timeshoot/Ligne1/Ligne2 LIKE %(matric)% — requete unique avec UNION ou conditions OR, dedoublonnee par match Id
6. **Matchs joues** : kp_match_joueur JOIN kp_match JOIN kp_journee JOIN kp_competition_equipe LEFT JOIN kp_match_detail GROUP BY m.Id

### 12.3 Endpoints existants reutilisables

- `GET /admin/clubs/search` : Autocomplete clubs (changement de club)
- `GET /admin/operations/autocomplete/players` : Peut servir de base pour la recherche athlete

### 12.4 Nouveaux endpoints a creer

- `GET /admin/athletes/search` : Recherche autocomplete athlete
- `GET /admin/athletes/{matric}` : Fiche complete athlete
- `GET /admin/athletes/{matric}/participations` : Toutes les participations pour une saison
- `PUT /admin/athletes/{matric}` : Modification athlete

---

**Document cree le** : 19 fevrier 2026
**Derniere mise a jour** : 19 fevrier 2026
**Statut** : BROUILLON
**Auteur** : Claude Code
