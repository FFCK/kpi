# Specification - Page Statistiques

## 1. Vue d'ensemble

La page Statistiques permet de consulter et exporter des statistiques variees sur les competitions, joueurs, equipes, arbitres, etc. Les donnees sont filtrees par saison et par un ensemble de competitions selectionnable, avec un systeme de parametrage par modale.

**Route** : `/stats`
**Route secondaire** : `/stats/:type/:saison/:competition` (pre-remplit les parametres et redirige vers `/stats`)

**Acces** :
- Profil <= 9 : Acces complet
- Certains types de statistiques sont restreints aux profils <= 6

**Page PHP Legacy** : Aucune (page native Nuxt)

---

## 2. Specificites par rapport aux autres pages

### 2.1 Contexte de travail

La page Statistiques utilise le contexte de travail (`workContextStore`) avec des specificites :

| Aspect | Comportement Stats | Comportement autres pages |
|--------|-------------------|--------------------------|
| **Rappel du contexte** | Barre de contexte au-dessus du titre (identique) | Identique |
| **Saison** | Modifiable dans la modale de parametrage | Non modifiable (lecture seule depuis le contexte) |
| **Competitions** | Multi-selection dans la modale, filtrees par le contexte | Filtrage automatique ou mono-selection |
| **Persistance** | `statsStore` sauvegarde les parametres entre visites | Pas de persistance specifique |

### 2.2 Filtrage des competitions par le contexte

- Les competitions proposees dans la modale sont **limitees aux competitions du contexte de travail**
- Si aucun contexte n'est defini, toutes les competitions de la saison sont proposees
- Quand la saison est changee dans la modale, les competitions sont rechargees depuis l'API puis re-filtrees par le contexte
- Les optgroups sont reconstruits a partir des sections du `workContextStore` (et non de l'API stats) pour garantir les bons libelles de section
- Les groupes vides apres filtrage sont masques

---

## 3. Fonctionnalites

### 3.1 Barre de contexte

| # | Fonctionnalite | Description |
|---|----------------|-------------|
| 1 | Affichage saison | Saison du contexte de travail |
| 2 | Affichage perimetre | Section, groupe, competition ou evenement selectionne |
| 3 | Nombre de competitions | Nombre de competitions dans le perimetre |
| 4 | Bouton "Modifier" | Lien vers la page d'accueil pour changer le contexte |
| 5 | Alerte sans contexte | Message d'avertissement si aucun perimetre defini |

### 3.2 Resume des parametres

Barre blanche affichant les parametres actuels :
- Type de statistique
- Saison (peut differer du contexte si modifiee dans la modale)
- Competitions selectionnees (resume ou nombre)
- Limite
- Boutons d'export (Excel, PDF)
- Bouton "Changer" pour ouvrir la modale

### 3.3 Modale de parametrage

| # | Parametre | Type | Description |
|---|-----------|------|-------------|
| 1 | Type de statistique | Select | Liste des types disponibles (filtres par profil) |
| 2 | Saison | Select | Toutes les saisons disponibles (modifiable) |
| 3 | Limite | Input numerique +/- | Nombre max de resultats (1-500, defaut 30) |
| 4 | Competitions | Multi-select avec optgroups | Competitions filtrees par le contexte de travail |

**Comportement de la modale** :
- Les valeurs sont copiees dans des variables temporaires a l'ouverture
- "Annuler" ferme la modale sans appliquer les modifications
- "Appliquer" valide les changements et relance le chargement des donnees
- Le changement de saison recharge les competitions et reinitialise la selection

### 3.4 Types de statistiques

| Type | Label | Restreint | Description |
|------|-------|-----------|-------------|
| Buteurs | Buteurs | Non | Classement des meilleurs buteurs individuels |
| Attaque | Attaque | Non | Classement des equipes par buts marques |
| Defense | Defense | Non | Classement des equipes par buts encaisses |
| Cartons | Cartons (joueurs) | Non | Cartons par joueur (vert, jaune, rouge, rouge def.) |
| CartonsEquipe | Cartons (equipes) | Non | Cartons par equipe |
| CartonsCompetition | Cartons (competitions) | Non | Synthese par competition |
| Fairplay | Fairplay (joueurs) | Non | Score fairplay individuel |
| FairplayEquipe | Fairplay (equipes) | Non | Score fairplay par equipe |
| Arbitrage | Arbitrage (arbitres) | Non | Matchs arbitres par personne |
| ArbitrageEquipe | Arbitrage (equipes) | Non | Matchs arbitres par equipe |
| CJouees | Competitions jouees (clubs) | Non | Matchs joues par competiteur, groupes par club |
| CJouees2 | Competitions jouees (equipes) | Non | Matchs joues par competiteur, groupes par equipe |
| CJouees3 | Irregularites | Non | Licences ancienne saison, pagaies, certificats |
| CJoueesN | Competitions nationales | Non | Filtre Championnats de France et nationaux |
| CJoueesCF | Coupe de France | Non | Filtre Coupes de France |
| OfficielsJournees | Officiels (journees) | Oui (<=6) | Journees avec officiels designes |
| OfficielsMatchs | Officiels (matchs) | Oui (<=6) | Matchs avec tous les officiels |
| ListeArbitres | Liste des arbitres | Non | Tous les arbitres FFCK |
| ListeEquipes | Liste des equipes | Non | Equipes inscrites avec details |
| ListeJoueurs | Liste des joueurs | Non | Joueurs inscrits (hors entraineurs) |
| ListeJoueurs2 | Liste joueurs & coachs | Non | Joueurs ET entraineurs inscrits |
| LicenciesNationaux | Licencies nationaux | Non | Repartition H/F par categorie d'age |
| CoherenceMatchs | Coherence matchs | Non | Controle de coherence temporelle des matchs |

### 3.5 Tableau de resultats

| # | Fonctionnalite | Description |
|---|----------------|-------------|
| 1 | Colonnes dynamiques | Les colonnes affichees dependent du type de statistique |
| 2 | Colonne classement (#) | Affichee pour les types Buteurs, Cartons, Fairplay, Arbitrage |
| 3 | Formatage numerique | Colonnes numeriques alignees a droite, police mono |
| 4 | Formatage dates | Dates formatees en format francais (dd/mm/yyyy) |
| 5 | Compteur de resultats | Nombre total de resultats affiche |

### 3.6 Vue mobile

- Cards au lieu du tableau desktop
- Chaque carte affiche le nom/prenom en en-tete avec le rang si applicable
- Les colonnes sont listees en paires label/valeur
- Les colonnes nom/prenom sont exclues du contenu (deja en en-tete)

### 3.7 Exports

| # | Format | Description |
|---|--------|-------------|
| 1 | Excel (XLSX) | Export avec labels traduits et formatage |
| 2 | PDF | Export avec labels traduits, titre, et mise en page |

Les exports incluent les parametres actuels (saison, type, limite, labels traduits, timezone, locale).

---

## 4. API Endpoints

| Endpoint | Methode | Description |
|----------|---------|-------------|
| `/admin/stats/filters` | GET | Charge saisons, competitions (optgroups), types de stats |
| `/admin/stats/data` | GET | Charge les donnees statistiques |
| `/admin/stats/export/xlsx` | GET | Export Excel |
| `/admin/stats/export/pdf` | GET | Export PDF |

### Parametres `/admin/stats/filters`

| Parametre | Type | Description |
|-----------|------|-------------|
| `season` | string | Saison pour charger les competitions |

### Parametres `/admin/stats/data`

| Parametre | Type | Description |
|-----------|------|-------------|
| `season` | string | Saison |
| `type` | string | Type de statistique |
| `limit` | number | Nombre max de resultats |
| `competitions` | string[] | Codes des competitions selectionnees |

---

## 5. Stores utilises

### statsStore

Persiste les parametres de la page entre les visites (saison, type de stat, competitions, limite).

### workContextStore

Fournit le contexte de travail (saison + perimetre) pour filtrer les competitions disponibles. Initialise au montage de la page.

---

## 6. Wireframe

```
+---------------------------------------------------------------------+
| [Contexte] Saison: 2026 | Perimetre: Groupe N1H (5 competitions)   |
|                                                       [Modifier]    |
+---------------------------------------------------------------------+
| Statistiques                                                        |
+---------------------------------------------------------------------+
| Type: Buteurs | Saison: 2026 | Competitions: N1H-A, N1H-B |        |
| Limite: 30                    [Excel] [PDF] [Changer]               |
+---------------------------------------------------------------------+
| Classement des meilleurs buteurs...              42 resultat(s)     |
+---------------------------------------------------------------------+
| #  | Competition | Nom    | Prenom | Equipe   | Buts              |
|----|-------------|--------|--------|----------|-------------------|
| 1  | N1H-A       | Dupont | Jean   | Paris KC | 15                |
| 2  | N1H-B       | Martin | Pierre | Lyon KP  | 12                |
| ...                                                                 |
+---------------------------------------------------------------------+
```

### Modale de parametrage

```
+---------------------------------------------+
| Parametres                              [X] |
+---------------------------------------------+
| Type de statistique:                        |
| [Buteurs                              v]    |
| Classement des meilleurs buteurs...         |
|                                             |
| Saison:              Limite:                |
| [2026          v]    [-] [30] [+]           |
|                                             |
| Competitions:                               |
| +-- Championnat National ---------------+  |
| | [ ] N1H-A - Championnat N1 Hommes A   |  |
| | [x] N1H-B - Championnat N1 Hommes B   |  |
| +-- Coupe de France --------------------+  |
| | [ ] CF-H - Coupe de France Hommes     |  |
| +---------------------------------------+  |
| Ctrl/Cmd pour selection multiple            |
|                                             |
|                    [Annuler] [Appliquer]     |
+---------------------------------------------+
```

---

**Document cree le** : 2026-02-04
**Derniere mise a jour** : 2026-02-04
**Statut** : Implementee
