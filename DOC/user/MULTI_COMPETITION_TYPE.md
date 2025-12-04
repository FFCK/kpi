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

## 📊 Affichage du classement

Le classement MULTI affiche uniquement les colonnes essentielles :

- **Clt** : Position au classement
- **Équipe** : Nom de l'équipe
- **Pts** : Points totaux
- **J** : Nombre de compétitions auxquelles l'équipe a participé

Les colonnes détaillées (G, N, P, F, +, -, Diff) ne sont pas affichées car non pertinentes pour ce type de classement.

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

## 📝 Remarques importantes

- **Permissions** : Seuls les utilisateurs avec les droits d'administration (profil ≤ 2) peuvent modifier la grille de points et les compétitions sources
- **Sauvegarde** : N'oubliez pas d'enregistrer la compétition après avoir configuré la grille et les compétitions sources
- **Classements publiés** : Le calcul utilise uniquement les classements **publiés**. Assurez-vous de publier les classements des compétitions sources avant de calculer le classement MULTI
- **Noms cohérents** : Les équipes doivent avoir le même nom ou code club dans toutes les compétitions sources pour être reconnues

## 🆘 Besoin d'aide ?

Si vous rencontrez un problème :
1. Vérifiez que la grille de points est bien définie
2. Vérifiez que les compétitions sources sont bien sélectionnées
3. Assurez-vous que les classements des compétitions sources sont publiés
4. Vérifiez la cohérence des noms d'équipes
5. Contactez votre administrateur système

---

**Version** : 2.0
**Date** : Décembre 2024
**Compatibilité** : KPI v8.4+
