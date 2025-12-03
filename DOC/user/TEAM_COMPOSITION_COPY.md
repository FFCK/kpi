# Copie de Composition d'Équipe

**Fonctionnalité** : Duplication rapide de la liste de joueurs d'une équipe vers une autre

---

## 📋 À quoi ça sert ?

La fonctionnalité de **copie de composition** permet de **dupliquer la liste complète des joueurs** d'une équipe (équipe source) vers une autre équipe (équipe cible) en un seul clic.

**Gain de temps** : Au lieu de saisir manuellement chaque joueur, copiez la composition en quelques secondes.

---

## 🎯 Cas d'usage typiques

### 1. Équipe participante à plusieurs compétitions

**Situation** : Votre club participe au championnat N1 et à la Coupe de France avec la même équipe.

**Solution** :
1. Saisir la composition dans la première compétition (championnat)
2. Copier la composition vers la deuxième compétition (coupe)
3. Ajuster si nécessaire (joueurs différents, numéros)

### 2. Équipes féminines et masculines d'un même club

**Situation** : Deux équipes du même club avec des compositions similaires.

**Solution** :
1. Saisir la première composition
2. Copier vers la seconde équipe
3. Modifier les joueurs différents

### 3. Journées successives d'un championnat

**Situation** : Composition identique d'une journée à l'autre.

**Solution** :
1. Utiliser la composition de la journée précédente
2. La copier pour la journée suivante
3. Ajuster les changements éventuels

---

## 🚀 Comment utiliser la copie de composition

### Étape 1 : Accéder à la gestion des équipes

1. **Menu** : `Administration` → `Gestion Équipe` ou `Gestion Équipes/Joueurs`
2. **Sélectionner la compétition** concernée
3. **Choisir l'équipe cible** (celle qui va recevoir la composition)

### Étape 2 : Lancer la copie

1. **Bouton "Copier composition"** ou icône similaire
2. **Sélectionner l'équipe source** dans la liste déroulante
3. **Cliquer sur "Copier"** ou "Valider"

### Étape 3 : Vérification

1. **Message de confirmation** : "X joueur(s) copié(s) avec succès"
2. **Vérifier la liste** des joueurs dans l'équipe cible
3. **Ajuster si nécessaire** (numéros, capitaine, etc.)

---

## ⚙️ Fonctionnement détaillé

### Ce qui est copié

✅ **Informations copiées** :
- Matricule du joueur
- Nom et prénom
- Sexe
- Catégorie d'âge
- Numéro de maillot
- Statut capitaine

### Ce qui n'est PAS copié

❌ **Informations non copiées** :
- Scores et statistiques
- Présences aux matchs
- Cartons et sanctions
- Historique des matchs joués

### Comportement de la copie

- **Remplacement complet** : Les joueurs existants dans l'équipe cible sont **supprimés** et remplacés par ceux de l'équipe source
- **Transaction sécurisée** : Si une erreur se produit, aucun changement n'est appliqué (rollback)
- **Journalisation** : L'opération est enregistrée dans le journal des modifications

---

## 🔒 Restrictions et sécurité

### Droits d'accès

**Profil ≤ 3** :
- ✅ Peut copier des compositions sur toutes les compétitions
- ✅ Même si la compétition est verrouillée

**Profil > 3** :
- ✅ Peut copier uniquement sur les équipes de leurs clubs
- ❌ Bloqué si la compétition est verrouillée
- ❌ Ne peut pas copier vers un club dont il n'a pas la gestion

### Vérifications automatiques

Le système vérifie :
- ✅ **Équipe source existe** et contient des joueurs
- ✅ **Équipe cible existe** et n'est pas verrouillée
- ✅ **Droits d'accès** de l'utilisateur
- ✅ **Saison active** et cohérence des données

---

## ⚠️ Points d'attention

### Avant de copier

1. **Vérifier l'équipe source** : Assurez-vous que la composition est correcte
2. **Équipe cible** : Les joueurs existants seront **supprimés** !
3. **Sauvegarde** : Si vous avez des doutes, notez la composition actuelle avant de copier

### Après la copie

1. **Vérifier les numéros** : Les numéros de maillot peuvent être à ajuster
2. **Vérifier le capitaine** : Le capitaine de l'équipe source est copié
3. **Licences** : Vérifier que les joueurs sont bien licenciés pour cette compétition

---

## 🐛 Problèmes courants

### "Compétition verrouillée ou vous n'avez pas les droits"

**Cause** :
- La compétition est verrouillée (en cours ou terminée)
- Vous n'avez pas les droits sur le club de l'équipe cible

**Solution** :
- Demander le déverrouillage à un administrateur
- Vérifier que vous avez les droits sur ce club

### "Équipe source non trouvée"

**Cause** : L'équipe source n'existe pas ou n'a pas de joueurs

**Solution** :
- Vérifier que vous avez bien sélectionné une équipe avec des joueurs
- Saisir d'abord la composition de l'équipe source

### "Erreur lors de la copie"

**Cause** : Problème technique (base de données, etc.)

**Solution** :
- Réessayer l'opération
- Vérifier que la saison est active
- Contacter l'administrateur si le problème persiste

---

## 💡 Conseils pratiques

### Optimiser votre workflow

1. **Créer une équipe "template"**
   - Créer une équipe avec la composition de base
   - La copier vers toutes les autres compétitions

2. **Utiliser en début de saison**
   - Saisir la composition complète une fois
   - Copier vers toutes les compétitions du club

3. **Copier puis ajuster**
   - Copier la composition proche
   - Modifier uniquement les joueurs différents
   - Plus rapide que de tout ressaisir

### Éviter les erreurs

- ✅ Toujours **vérifier après la copie**
- ✅ **Ajuster les numéros** si nécessaire (doublons possibles)
- ✅ **Vérifier les catégories** (U21, U23, etc.)
- ❌ Ne pas copier si les règlements sont différents (nombre de joueurs, etc.)

---

## 📊 Suivi de l'opération

### Journal des modifications

Chaque copie est enregistrée dans le **journal des modifications** :
- Date et heure de la copie
- Utilisateur ayant effectué l'opération
- Équipe source et équipe cible
- Nombre de joueurs copiés

### Annulation

⚠️ **Attention** : Il n'y a **pas de fonction "Annuler"** !

Si vous avez copié par erreur :
1. Refaire une copie depuis la bonne équipe source
2. Ou ressaisir manuellement la composition
3. Ou demander à un administrateur de restaurer depuis le journal

---

## 🔗 Fonctionnalités liées

- **Gestion des équipes** : Création et modification d'équipes
- **Gestion des joueurs** : Import de licences, recherche de licenciés
- **Compositions de match** : Définir les titulaires et remplaçants par match

---

**Version** : 1.0
**Date** : Décembre 2025
**Public** : Gestionnaires de clubs, responsables d'équipes
