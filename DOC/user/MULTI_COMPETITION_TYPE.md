# Type de Compétition MULTI - Guide Utilisateur

## 📋 Vue d'ensemble

Le type de compétition **MULTI** (Multi-Compétition) permet de créer un classement général basé sur les résultats de plusieurs compétitions que vous sélectionnez. Ce type est particulièrement utile pour :

- **Circuits de compétitions** : Créer un classement général sur plusieurs étapes d'un circuit
- **Tournois multi-événements** : Agréger les résultats de plusieurs tournois pour établir un classement de saison
- **Championnats combinés** : Combiner les résultats de différentes compétitions pour un titre global

## 🎯 Caractéristiques principales

### Pas de matchs
Une compétition MULTI ne contient pas de matchs, uniquement un classement calculé automatiquement.

### Sélection des compétitions sources
Vous choisissez précisément quelles compétitions doivent être prises en compte via une interface multi-sélection :
- Compétitions organisées par section pour faciliter la navigation
- Possibilité de combiner des compétitions de différentes sections/groupes

### Grille de points personnalisable
Vous définissez combien de points sont attribués selon le classement dans chaque compétition source :
- 1er : X points
- 2ème : Y points
- 3ème : Z points
- Etc.
- Points par défaut pour les positions non spécifiées

### Calcul automatique
Le classement est calculé automatiquement en fonction des résultats **publiés** des compétitions sélectionnées.

## 🚀 Configuration d'une compétition MULTI

### Étape 1 : Créer la compétition

1. Connectez-vous à l'interface d'administration
2. Allez dans **Gestion des Compétitions**
3. Créez une nouvelle compétition
4. Sélectionnez le type **"Multi-Compétition"** dans la liste déroulante
5. Remplissez les informations de base (nom, saison, groupe, etc.)

### Étape 2 : Configurer la grille de points

Vous avez **deux options** pour configurer la grille de points :

#### ⭐ Option A : Éditeur graphique (Recommandé)

L'**Éditeur de Grille de Points** est un outil graphique qui facilite la configuration sans connaître le format JSON.

**Comment l'utiliser :**

1. Dans le formulaire de compétition, trouvez le champ **"Grille de points (MULTI)"**
2. Cliquez sur le bouton **"Ouvrir l'éditeur de grille"**
3. Une nouvelle fenêtre s'ouvre avec l'éditeur

**Dans l'éditeur :**

1. **Nombre de positions** : Indiquez combien de positions vous voulez configurer
   - Par exemple : `10` si vous voulez attribuer des points aux 10 premières places
   - Minimum : 1, Maximum : 50

2. **Points par position** : Pour chaque position, indiquez le nombre de points
   - Exemple :
     - 1ère place : `10` points
     - 2ème place : `6` points
     - 3ème place : `4` points
     - 4ème place : `3` points
     - 5ème place : `2` points
     - 6ème place : `1` point

3. **Points par défaut** : Définissez les points attribués aux positions non spécifiées
   - Généralement : `0` point

4. Cliquez sur **"Générer le JSON"**
5. Cliquez sur **"Appliquer au formulaire"** pour reporter le JSON
6. La fenêtre se ferme automatiquement
   - Vous pouvez aussi cliquer sur **"Fermer"** pour quitter sans appliquer les modifications

**Avantages** :
✅ Interface intuitive
✅ Pas besoin de connaître le format JSON
✅ Moins d'erreurs de saisie
✅ Modification facile d'une grille existante

#### Option B : Saisie manuelle du JSON

Si vous êtes à l'aise avec le format JSON, vous pouvez saisir directement la grille :

```json
{"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0}
```

- `"1"`: points pour le 1er classé
- `"2"`: points pour le 2ème classé
- `"default"`: points pour les classements non spécifiés

### Étape 3 : Sélectionner les compétitions sources

1. Dans le formulaire, trouvez le champ **"Compétitions sources (MULTI)"**
2. Sélectionnez les compétitions que vous souhaitez inclure (multi-sélection)
   - Maintenez `Ctrl` (Windows) ou `Cmd` (Mac) pour sélectionner plusieurs compétitions
3. Les compétitions disponibles sont toutes les compétitions de la même saison (hors compétitions MULTI)

**Important** : Seules les compétitions explicitement sélectionnées seront prises en compte.

### Étape 4 : Inscrire les équipes

#### ⭐ Méthode recommandée : Affectation - Promotion - Relégation

**Cette méthode simplifie grandement l'inscription des équipes** en copiant automatiquement les équipes depuis les compétitions sources vers la compétition MULTI.

**Procédure** :

1. Dans **Gestion Classement**, affichez le classement de la première compétition source
   - Exemple : *Tournoi Nord*

2. Cliquez sur **Affectation - Promotion - Relégation**

3. Dans l'interface d'affectation :
   - Choisissez la **saison** appropriée
   - Sélectionnez la **compétition cible** de type MULTI
     - Exemple : *Circuit Régional*

4. **Cochez les équipes** à prendre en compte dans le classement
   - Vous pouvez sélectionner toutes les équipes ou seulement certaines

5. Cliquez sur **"Affecter les équipes cochées"**
   - Les équipes sont automatiquement inscrites dans la compétition MULTI

6. **Répétez la procédure** pour chaque compétition source
   - Exemple : *Tournoi Sud*, *Tournoi Est*, etc.
   - Les équipes déjà inscrites ne seront pas dupliquées

**Avantages** :
✅ Gain de temps considérable
✅ Pas de risque d'erreur de saisie sur les noms d'équipes
✅ Garantit la cohérence des noms entre compétitions sources et MULTI
✅ Permet de sélectionner précisément quelles équipes inclure

#### Méthode alternative : Inscription manuelle

Vous pouvez aussi inscrire les équipes manuellement comme pour toute autre compétition :
1. Allez dans la gestion des équipes
2. Ajoutez les équipes participantes une par une

**Important** : Les équipes doivent être inscrites dans les compétitions sources avec le même nom ou code club pour être reconnues.

### Étape 5 : Calculer le classement

1. Assurez-vous que toutes les compétitions sélectionnées ont leurs classements calculés et **publiés**
2. Allez dans **Gestion des Classements**
3. Sélectionnez votre compétition MULTI
4. Cliquez sur **"Calculer le classement"**

Le système :
- Récupère les classements publiés de chaque compétition source
- Applique la grille de points selon le classement
- Somme tous les points obtenus par chaque équipe
- Génère le classement final

### Étape 6 : Publier le classement

1. Vérifiez le classement provisoire
2. Cliquez sur **"Publier le classement"**
3. Le classement devient visible publiquement

## 💡 Exemple pratique

### Scénario : Circuit régional de kayak-polo

Vous organisez un circuit régional composé de 3 tournois. Vous voulez créer un classement général où :
- Le 1er de chaque tournoi gagne 10 points
- Le 2ème gagne 6 points
- Le 3ème gagne 4 points
- Les autres ne gagnent pas de points

**Configuration dans l'éditeur de grille** :
```
Nombre de positions : 3
1ère place : 10
2ème place : 6
3ème place : 4
Points par défaut : 0
```

Le système génère : `{"1":10,"2":6,"3":4,"default":0}`

**Compétitions sources** :
- Tournoi Nord (code : REG1)
- Tournoi Sud (code : REG2)
- Tournoi Est (code : REG3)

**Résultats des tournois** :

| Équipe | Tournoi Nord | Tournoi Sud | Tournoi Est | **Total** |
|--------|--------------|-------------|-------------|-----------|
| Équipe A | 1er (10 pts) | 3ème (4 pts) | 2ème (6 pts) | **20 points** |
| Équipe B | 2ème (6 pts) | 1er (10 pts) | 1er (10 pts) | **26 points** |
| Équipe C | 3ème (4 pts) | 2ème (6 pts) | 3ème (4 pts) | **14 points** |

**Classement final MULTI** :
1. Équipe B - 26 points
2. Équipe A - 20 points
3. Équipe C - 14 points

## 📊 Types de classement multi-structure

Les compétitions MULTI offrent **5 types de classement différents** qui permettent d'agréger les résultats selon différentes structures :

### 🏆 Classement par équipe (par défaut)

Le classement standard où chaque équipe est classée individuellement.

**Caractéristiques** :
- Chaque équipe conserve son nom original
- Les points sont calculés individuellement pour chaque équipe
- C'est le mode de classement traditionnel

**Exemple** :
```
1. Team Kayak Paris A - 26 pts (3 participations)
2. Team Kayak Lyon B - 20 pts (3 participations)
3. Team Kayak Marseille - 14 pts (2 participations)
```

### 🏛️ Classement par club

Regroupe toutes les équipes d'un même club pour créer un classement de clubs.

**Caractéristiques** :
- Les équipes sont regroupées par `code_club`
- Les points de toutes les équipes du même club sont cumulés
- Le libellé du club remplace les noms d'équipes
- La colonne **J** indique le nombre total de participations de toutes les équipes du club

**Fonctionnement** :
1. Le système identifie toutes les équipes d'un même club dans les compétitions sources
2. Pour chaque équipe, la grille de points est appliquée selon son classement
3. Les points de toutes les équipes du club sont additionnés
4. Le nombre de participations (J) est également cumulé

**Exemple** :
```
Compétitions sources :
- Tournoi 1 : Kayak Paris A (1er = 10 pts), Kayak Paris B (3ème = 4 pts)
- Tournoi 2 : Kayak Paris A (2ème = 6 pts)

Classement par club :
1. Kayak Paris - 20 pts (3 participations)
   └─ 10 + 4 + 6 = 20 points
```

### 🗺️ Classement par Comité Départemental (CD)

Regroupe les équipes par comité départemental.

**Caractéristiques** :
- Regroupement par `code_comite_dep` du club
- Cumul des points de toutes les équipes du même département
- Affiche le nom du comité départemental

**Exemple** :
```
1. CD des Hauts-de-Seine - 45 pts (8 participations)
2. CD du Rhône - 38 pts (6 participations)
3. CD des Bouches-du-Rhône - 22 pts (4 participations)
```

### 🌍 Classement par Comité Régional (CR)

Regroupe les équipes par comité régional.

**Caractéristiques** :
- Regroupement par `code_comite_reg` via le CD du club
- Cumul des points de toutes les équipes de la même région
- Affiche le nom du comité régional

**Exemple** :
```
1. CR Île-de-France - 120 pts (15 participations)
2. CR Auvergne-Rhône-Alpes - 95 pts (12 participations)
3. CR Provence-Alpes-Côte d'Azur - 67 pts (9 participations)
```

### 🌐 Classement par nation

Regroupe les équipes par nation (particulièrement utile pour les compétitions internationales).

**Caractéristiques** :
- Regroupement par nation basé sur le code comité
- Traitement spécial pour la France (FRA)
- Affiche le nom de la nation ou du pays

**Logique spéciale pour la France** :
La nation **France (FRA)** regroupe :
1. **Toutes les équipes de clubs français** (`code_comite_reg != '98'`)
2. **L'équipe nationale France** (`code_comite_dep = 'FRA'`)

**Équipes nationales internationales** :
- Les équipes avec `code_comite_reg = '98'` ET `code_comite_dep != 'FRA'`
- Utilisent leur `code_comite_dep` comme code nation (code CIO)
- Exemples : ITA, GER, ESP, BEL, etc.

**Exemple de classement par nation** :
```
1. France (FRA) - 250 pts (25 participations)
   └─ Clubs français + Équipe nationale France
2. Italie (ITA) - 180 pts (18 participations)
3. Allemagne (GER) - 145 pts (15 participations)
4. Espagne (ESP) - 120 pts (12 participations)
```

## 🎯 Configuration du type de classement

### Comment sélectionner le type de classement

1. Lors de la création ou modification d'une compétition MULTI
2. Un champ **"Type de classement"** apparaît dans le formulaire
3. Sélectionnez le type souhaité parmi les 5 options :
   - Classement par équipe
   - Classement par club
   - Classement par comité départemental
   - Classement par comité régional
   - Classement par nation
4. Enregistrez la compétition

**Note** : Ce champ n'est visible que pour les compétitions de type MULTI.

### Critères de tri du classement

Pour tous les types de classement, le tri s'effectue selon **3 niveaux** :

1. **Points (Pts)** - Décroissant ⬇️
   - Critère principal : les structures avec le plus de points sont en tête

2. **Nombre de participations (J)** - Décroissant ⬇️
   - En cas d'égalité de points, priorité à la structure ayant le plus de participations
   - Favorise les structures qui ont participé à plus de compétitions

3. **Libellé** - Alphabétique ⬆️
   - En dernier recours, tri alphabétique par nom de structure

**Exemple de tri** :
```
Rang  Structure         Points  Participations
  1   Club Alpha          400        5        ← Plus de matchs
  2   Club Beta           400        3        ← Même points mais moins de matchs
  3   Club Gamma          350        6        ← Moins de points
  4   Club Delta          350        4        ← Même points que Gamma
```

### Attribution des rangs

Deux structures reçoivent le **même rang (Clt)** uniquement si elles ont :
- **Les mêmes points** (Pts) **ET**
- **Le même nombre de participations** (J)

**Exemple** :
```
Rang  Structure         Points  Participations
  1   Club A              400        5
  1   Club B              400        5        ← Même rang (points ET J égaux)
  3   Club C              400        3        ← Rang 3 (J différent)
```

## 📊 Affichage du classement

Le classement MULTI affiche les colonnes essentielles selon le type de classement :

- **Clt** : Position au classement
- **Équipe/Club/CD/CR/Nation** : Nom de la structure (selon le type de classement)
- **Pts** : Points totaux
- **J** : Nombre total de participations (compétitions jouées ou équipes participantes selon le type)

Les colonnes détaillées (G, N, P, F, +, -, Diff) ne sont pas affichées car non pertinentes pour ce type de classement.

**Adaptation du libellé de colonne** :
- Type "équipe" → Colonne "Équipe"
- Type "club" → Colonne "Club"
- Type "cd" → Colonne "Comité Départemental"
- Type "cr" → Colonne "Comité Régional"
- Type "nation" → Colonne "Nation"

## 📄 Génération des PDF

Les compétitions MULTI disposent de générateurs PDF dédiés :

### PDF Admin (Provisoire)
- Accès via **Gestion des Classements**, section "Admin"
- Contenu : Classement provisoire
- Langue : Français par défaut, anglais si configuré

### PDF Public
- Accès via **Gestion des Classements**, section "Public" (après publication)
- Contenu : Classement publié
- QR Code inclus pour accès direct
- Langue : Français par défaut, anglais si configuré ou paramètre `?lang=en`

## ❓ Questions fréquentes

### Que se passe-t-il si une équipe ne participe pas à un tournoi ?

L'équipe ne reçoit aucun point pour ce tournoi. Seuls les tournois auxquels l'équipe a participé sont comptabilisés. Le champ **J** (joué) indique le nombre de compétitions auxquelles l'équipe a participé.

### Comment modifier une grille de points existante ?

1. Ouvrez l'éditeur de grille
2. La grille actuelle se charge automatiquement
3. Modifiez les valeurs souhaitées
4. Générez et appliquez le nouveau JSON
5. Enregistrez la compétition

### Puis-je attribuer le même nombre de points à plusieurs positions ?

Oui ! Vous pouvez par exemple attribuer 5 points à la fois à la 2ème et à la 3ème place.

### Combien de positions maximum puis-je configurer ?

L'éditeur permet de configurer jusqu'à 50 positions différentes.

### Les points peuvent-ils être négatifs ?

Non, seuls les nombres positifs (0 ou plus) sont acceptés.

### Comment activer l'anglais pour les PDF ?

**Méthode 1 : Configuration de la compétition**
- Dans Gestion des Compétitions, cocher `En_actif = 'O'`
- Tous les PDF de cette compétition seront en anglais

**Méthode 2 : Paramètre URL (PDF public uniquement)**
- Ajouter `?lang=en` à l'URL du PDF

### Puis-je changer le type de classement après création ?

Oui ! Vous pouvez modifier le type de classement à tout moment :
1. Éditez la compétition MULTI
2. Changez le type de classement
3. Enregistrez
4. Recalculez le classement

**Important** : Pensez à recalculer le classement après avoir changé le type.

### Comment fonctionne le regroupement par club exactement ?

Le système :
1. Récupère toutes les équipes inscrites dans la compétition MULTI
2. Pour chaque équipe, cherche ses résultats dans les compétitions sources **par numéro d'équipe unique**
3. Applique la grille de points selon le classement obtenu
4. Regroupe les équipes qui ont le même `code_club`
5. Additionne leurs points et participations
6. Affiche le libellé du club au lieu des noms d'équipes

### Pourquoi certaines équipes françaises ne sont pas comptées pour FRA ?

Vérifiez que :
- Les équipes ont bien `code_comite_reg != '98'` (clubs français) OU
- L'équipe a `code_comite_dep = 'FRA'` (équipe nationale)

Si une équipe française n'apparaît pas dans le classement FRA, vérifiez sa configuration dans la base de données (table `kp_club`, `kp_cd`).

### Quelle est la différence entre J dans un classement par équipe vs par club ?

**Classement par équipe** :
- J = nombre de compétitions sources auxquelles **cette équipe** a participé

**Classement par club (ou CD/CR/nation)** :
- J = nombre total de participations de **toutes les équipes** de cette structure
- Exemple : Club A avec 2 équipes → Équipe 1 participe à 3 tournois, Équipe 2 à 2 tournois → J = 5

### En cas d'égalité de points, qui est classé premier ?

Le tri se fait dans cet ordre :
1. Points (Pts) décroissant
2. Participations (J) décroissant - **celui qui a participé le plus est devant**
3. Alphabétique sur le nom

Exemple : Si Club A et Club B ont 400 pts, mais Club A a J=5 et Club B a J=3, alors Club A sera classé devant.

### Peut-on avoir un classement par nation pour une compétition nationale française ?

Oui, mais ce n'est généralement pas pertinent. Le classement par nation est surtout utile pour les compétitions internationales où plusieurs pays participent. Pour une compétition nationale, préférez le classement par CR, CD ou club.

## 🚨 Dépannage

### Le classement ne se calcule pas

**Causes possibles** :
1. Les compétitions sources n'ont pas de classement publié
2. La grille de points est invalide (JSON incorrect)
3. Aucune compétition source n'est sélectionnée

**Solution** :
1. Vérifier que toutes les compétitions sources ont un classement **publié**
2. Vérifier que la grille de points est bien configurée (utiliser l'éditeur pour être sûr)
3. S'assurer qu'au moins une compétition est sélectionnée

### Les points sont incorrects

**Causes possibles** :
1. La grille de points ne correspond pas aux attentes
2. Une compétition source a été modifiée après le calcul

**Solution** :
1. Vérifier la grille de points configurée
2. Recalculer le classement MULTI après toute modification des compétitions sources

### Le PDF ne s'affiche pas en anglais

**Causes possibles** :
1. `En_actif` n'est pas défini sur 'O' dans la compétition
2. Le paramètre `?lang=en` est manquant (PDF public)

**Solution** :
1. Vérifier la configuration dans Gestion des Compétitions
2. Ajouter `?lang=en` à l'URL du PDF public

### Une équipe n'apparaît pas dans le classement

**Causes possibles** :
1. L'équipe n'est pas inscrite dans la compétition MULTI
2. L'équipe a un nom différent dans les compétitions sources
3. L'équipe n'a participé à aucune compétition source

**Solution** :
1. Vérifier l'inscription de l'équipe dans la compétition MULTI
2. S'assurer que le nom ou code club est cohérent entre les compétitions
3. Vérifier que l'équipe a bien participé à au moins une compétition source

### Le classement par structure affiche des résultats incorrects

**Causes possibles** :
1. Les codes club/CD/CR ne sont pas correctement renseignés dans la base
2. Le type de classement sélectionné ne correspond pas à l'attente
3. Les équipes n'ont pas le bon rattachement structurel

**Solution** :
1. Vérifier les codes dans les tables `kp_club`, `kp_cd`, `kp_cr`
2. S'assurer que le bon type de classement est sélectionné (équipe/club/CD/CR/nation)
3. Vérifier que chaque équipe a bien un code_club renseigné
4. Pour le classement par CD/CR : vérifier que les clubs ont leur code_comite_dep renseigné
5. Pour le classement par nation : vérifier les codes comité (voir documentation nation)

### Les points d'une structure semblent manquants

**Causes possibles** :
1. Certaines équipes de la structure n'ont pas le même code_club
2. Les équipes ne sont pas trouvées dans les compétitions sources (recherche par numéro)
3. Problème de rattachement CD/CR pour les classements départementaux/régionaux

**Solution** :
1. Vérifier que toutes les équipes d'un même club ont exactement le même `code_club`
2. S'assurer que les équipes sont bien inscrites avec leur `Numero` unique
3. Recalculer le classement après correction des données

## 📝 Remarques importantes

- **Permissions** : Seuls les utilisateurs avec les droits d'administration (profil ≤ 2) peuvent modifier la grille de points, les compétitions sources et le type de classement
- **Sauvegarde** : N'oubliez pas d'enregistrer la compétition après avoir configuré la grille, les compétitions sources et le type de classement
- **Classements publiés** : Le calcul utilise uniquement les classements **publiés**. Assurez-vous de publier les classements des compétitions sources avant de calculer le classement MULTI
- **Identification des équipes** : Les équipes sont identifiées par leur **numéro unique** (champ `Numero`) dans les compétitions sources
- **Codes structurels** : Pour les classements par club/CD/CR/nation, assurez-vous que les codes `code_club`, `code_comite_dep`, `code_comite_reg` sont correctement renseignés dans les tables `kp_club`, `kp_cd`, `kp_cr`
- **Recalcul nécessaire** : Après avoir changé le type de classement, pensez à recalculer le classement pour voir les modifications
- **Tri automatique** : Le classement est trié automatiquement par Points (DESC) puis Participations (DESC) puis Libellé (ASC)

## 🆘 Besoin d'aide ?

Si vous rencontrez un problème :
1. Vérifiez que la grille de points est bien définie
2. Vérifiez que les compétitions sources sont bien sélectionnées
3. Assurez-vous que les classements des compétitions sources sont publiés
4. Vérifiez la cohérence des noms d'équipes
5. Contactez votre administrateur système

---

**Version** : 3.0
**Date** : Décembre 2024
**Compatibilité** : KPI v8.4+
**Nouveautés v3.0** : Classements multi-structure (équipe, club, CD, CR, nation) avec tri par participations
