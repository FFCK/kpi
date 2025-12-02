# Éditeur de Grille de Points pour Compétitions MULTI

## 📋 Vue d'ensemble

L'**Éditeur de Grille de Points** est un outil graphique qui facilite la configuration des points attribués dans les compétitions de type MULTI. Il vous permet de définir simplement combien de points sont attribués à chaque position au classement (1er, 2ème, 3ème, etc.), sans avoir à écrire manuellement du code JSON.

## 🎯 Quand utiliser cet outil ?

Utilisez l'éditeur de grille lorsque vous créez ou modifiez une **compétition de type MULTI** (multi-compétition). Ce type de compétition permet de créer un classement général basé sur les résultats de plusieurs compétitions que vous sélectionnez.

**Exemples d'utilisation** :
- Créer un championnat régional basé sur plusieurs tournois locaux
- Établir un classement multi-étapes (circuit de compétitions)
- Combiner les résultats de plusieurs compétitions pour un classement final

## 🚀 Comment utiliser l'éditeur

### Étape 1 : Accéder à l'éditeur

1. Connectez-vous à l'interface d'administration
2. Allez dans **Gestion des Compétitions**
3. Créez ou modifiez une compétition de type **"Multi-Compétition"**
4. Dans le formulaire, trouvez le champ **"Grille de points (MULTI)"**
5. Cliquez sur le bouton **"Ouvrir l'éditeur de grille"**

Une nouvelle fenêtre s'ouvre avec l'éditeur de grille de points.

### Étape 2 : Configurer la grille

Dans la fenêtre de l'éditeur :

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
   - Utile si une équipe se classe au-delà des positions configurées

### Étape 3 : Générer et appliquer

1. Cliquez sur **"Générer le JSON"**
   - Le système crée automatiquement le code JSON nécessaire
   - Le résultat s'affiche dans une zone de texte

2. Cliquez sur **"Appliquer au formulaire"**
   - Le JSON est automatiquement copié dans le formulaire de la compétition
   - La fenêtre se ferme automatiquement

3. **Enregistrez** votre compétition dans le formulaire principal

## 💡 Exemple pratique

### Scénario : Circuit régional de kayak-polo

Vous organisez un circuit régional composé de 3 tournois. Vous voulez créer un classement général où :
- Le 1er de chaque tournoi gagne 10 points
- Le 2ème gagne 6 points
- Le 3ème gagne 4 points
- Les autres ne gagnent pas de points

**Configuration dans l'éditeur** :
```
Nombre de positions : 3
1ère place : 10
2ème place : 6
3ème place : 4
Points par défaut : 0
```

Après avoir cliqué sur "Générer le JSON", le système crée :
```json
{"1":10,"2":6,"3":4,"default":0}
```

Cliquez sur "Appliquer au formulaire" et enregistrez votre compétition !

## ✨ Avantages de l'éditeur

✅ **Simplicité** : Pas besoin de connaître le format JSON
✅ **Visuel** : Interface claire et intuitive
✅ **Rapidité** : Configuration en quelques clics
✅ **Fiabilité** : Pas d'erreur de syntaxe possible
✅ **Modification facile** : Charger et modifier une grille existante

## 🔧 Fonctionnalités avancées

### Modifier une grille existante

Si vous modifiez une compétition MULTI qui a déjà une grille de points :

1. Ouvrez l'éditeur de grille
2. La grille actuelle se charge automatiquement
3. Modifiez les valeurs souhaitées
4. Générez et appliquez le nouveau JSON

### Copier le JSON

Si vous voulez simplement copier le JSON sans l'appliquer :

1. Générez le JSON
2. Cliquez sur **"Copier le JSON"**
3. Le JSON est copié dans votre presse-papiers
4. Vous pouvez le coller ailleurs si nécessaire

## ❓ Questions fréquentes

### Que se passe-t-il si une équipe ne participe pas à un tournoi ?

L'équipe ne reçoit aucun point pour ce tournoi. Seuls les tournois auxquels l'équipe a participé sont comptabilisés.

### Puis-je attribuer le même nombre de points à plusieurs positions ?

Oui ! Vous pouvez par exemple attribuer 5 points à la fois à la 2ème et à la 3ème place.

### Combien de positions maximum puis-je configurer ?

L'éditeur permet de configurer jusqu'à 50 positions différentes.

### Les points peuvent-ils être négatifs ?

Non, seuls les nombres positifs (0 ou plus) sont acceptés.

### Comment voir le classement final ?

Une fois votre compétition MULTI configurée :
1. Allez dans **Gestion des Classements**
2. Sélectionnez votre compétition MULTI
3. Cliquez sur **"Calculer le classement"**

Le classement s'affiche avec le total de points de chaque équipe.

## 📝 Remarques importantes

- **Permissions** : Seuls les utilisateurs avec les droits d'administration (profil ≤ 2) peuvent modifier la grille de points
- **Sauvegarde** : N'oubliez pas d'enregistrer la compétition après avoir appliqué le JSON
- **Classements publiés** : Le calcul du classement MULTI utilise uniquement les classements **publiés** des compétitions sources

## 🆘 Besoin d'aide ?

Si vous rencontrez un problème :
1. Vérifiez que la grille de points est bien définie
2. Vérifiez que les compétitions sources sont bien sélectionnées
3. Assurez-vous que les classements des compétitions sources sont publiés
4. Contactez votre administrateur système

---

**Version** : 1.0
**Date** : Décembre 2024
**Compatibilité** : KPI v8.4+
