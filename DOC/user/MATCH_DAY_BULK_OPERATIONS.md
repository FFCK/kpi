# Gestion Journée - Opérations de Masse sur les Matchs

**Fonctionnalité** : Actions groupées sur plusieurs matchs simultanément

---

## 📋 À quoi ça sert ?

La fonctionnalité d'**opérations de masse** permet d'effectuer certaines actions sur **plusieurs matchs en même temps**, au lieu de les traiter un par un.

**Gain de temps** : Au lieu de modifier 20 matchs individuellement, effectuez l'action en quelques clics.

---

## 🎯 Opérations disponibles

### 1. Publication multiple de matchs

**Action** : Rendre plusieurs matchs visibles sur le site public

**Utilisation** :
- Publier tous les matchs d'une journée en une seule fois
- Afficher le planning complet sur le site public
- Préparer l'affichage avant le début du tournoi

### 2. Validation multiple de matchs

**Action** : Verrouiller plusieurs matchs pour empêcher les modifications

**Utilisation** :
- Verrouiller tous les matchs terminés d'une journée
- Figer les résultats après validation
- Empêcher les modifications accidentelles

### 3. Publication ET validation simultanées

**Action** : Publier et verrouiller plusieurs matchs en une seule opération

**Utilisation** :
- Finaliser une journée de compétition
- Publier les résultats définitifs
- Clôturer un tournoi

### 4. Suppression multiple de matchs

**Action** : Supprimer plusieurs matchs d'un coup

**Utilisation** :
- Nettoyer des matchs créés par erreur
- Supprimer un planning incomplet
- Réinitialiser une journée

⚠️ **Attention** : Les matchs **validés** ne peuvent PAS être supprimés

### 5. Déplacement de matchs vers une autre journée

**Action** : Changer la journée d'affectation de plusieurs matchs

**Utilisation** :
- Reporter des matchs vers une autre date
- Réorganiser le planning
- Fusionner ou diviser des journées

### 6. Réinitialisation des équipes et arbitres

**Action** : Vider les équipes et arbitres de plusieurs matchs

**Utilisation** :
- Recommencer l'affectation des équipes
- Nettoyer un planning avant de le refaire
- Préparer des matchs "templates"

---

## 🚀 Comment utiliser les opérations de masse

### Accéder à la gestion des journées

1. **Menu** : `Administration` → `Gestion Journée`
2. **Sélectionner l'événement** et la **compétition**
3. **Choisir la journée** concernée

### Sélectionner les matchs

1. **Cocher les cases** à gauche de chaque match
   - Cliquer sur les matchs à traiter
   - Ou utiliser "Tout sélectionner" si disponible

2. **Vérifier la sélection**
   - Les matchs cochés sont mis en surbrillance
   - Le nombre de matchs sélectionnés est affiché

### Exécuter l'action

1. **Choisir l'action** dans le menu déroulant ou les boutons
   - "Publier les matchs sélectionnés"
   - "Verrouiller les matchs sélectionnés"
   - "Publier et verrouiller"
   - "Supprimer les matchs"
   - Etc.

2. **Confirmer l'action**
   - Une popup de confirmation apparaît
   - Vérifier le nombre de matchs concernés
   - Cliquer sur "OK" ou "Valider"

3. **Vérification**
   - Message de confirmation : "X matchs modifiés"
   - Vérifier visuellement les changements dans le tableau

---

## ⚙️ Fonctionnement détaillé

### Publication de matchs

**Effet** :
- Les matchs deviennent visibles sur le site public
- Les horaires, terrains et équipes sont affichés
- Le public peut consulter le planning

**Icône** : 🌐 ou mention "PUBLIC"

### Validation de matchs

**Effet** :
- Les matchs sont verrouillés en modification
- Les équipes, horaires et résultats ne peuvent plus être changés
- Protection contre les erreurs de manipulation

**Icône** : 🔒 ou cadenas

⚠️ **Important** : Seuls les administrateurs (profil ≤ 3) peuvent déverrouiller

### Suppression de matchs

**Conditions** :
- ✅ Le match ne doit PAS être validé
- ✅ Le match ne doit PAS avoir de score saisi
- ❌ Les matchs avec joueurs affectés sont supprimés (les compositions sont effacées)

**Effet** :
- Suppression définitive du match
- Suppression des compositions d'équipes associées
- Pas de fonction "Annuler" !

### Déplacement de matchs

**Conditions** :
- ✅ Le match ne doit PAS être validé
- ✅ La journée de destination doit exister

**Effet** :
- Le match change de journée
- L'ordre et le numéro peuvent être réaffectés
- Les équipes et arbitres restent inchangés

---

## 🔒 Restrictions et sécurité

### Droits d'accès

**Profil ≤ 3** (Administrateurs) :
- ✅ Toutes les opérations de masse
- ✅ Sur toutes les compétitions
- ✅ Même sur les matchs verrouillés (déverrouillage possible)

**Profil 4-7** (Gestionnaires) :
- ✅ Opérations de masse sur leurs compétitions uniquement
- ❌ Ne peuvent pas déverrouiller des matchs validés
- ❌ Bloqués si la compétition est verrouillée

**Profil > 7** (Utilisateurs basiques) :
- ❌ Pas d'accès aux opérations de masse

### Compétitions verrouillées

Si une compétition est **verrouillée** :
- ❌ Aucune opération de masse n'est possible
- ❌ Sauf pour les profils ≤ 3 (administrateurs)

---

## ⚠️ Points d'attention

### Avant d'exécuter une opération

1. **Vérifier la sélection**
   - Comptez le nombre de matchs cochés
   - Vérifiez que ce sont bien les bons matchs

2. **Comprendre les conséquences**
   - Publication = visible par le public
   - Validation = verrouillage
   - Suppression = DÉFINITIVE

3. **Sauvegarder si nécessaire**
   - Notez les informations importantes avant une suppression
   - Exportez le planning si besoin

### Après l'opération

1. **Vérifier le résultat**
   - Parcourir la liste des matchs
   - Vérifier les icônes (🌐, 🔒)
   - Consulter le site public si publication

2. **Journal des modifications**
   - Chaque opération est enregistrée
   - Consultable dans "Gestion Journal"

---

## 🐛 Problèmes courants

### "Compétition verrouillée"

**Cause** : La compétition est en mode lecture seule

**Solution** :
- Demander le déverrouillage à un administrateur
- Vérifier vos droits d'accès

### "Impossible de supprimer des matchs validés"

**Cause** : Un ou plusieurs matchs sélectionnés sont validés

**Solution** :
1. Décocher les matchs validés (icône 🔒)
2. Ou demander à un administrateur de les déverrouiller d'abord
3. Puis relancer la suppression

### "Aucun match sélectionné"

**Cause** : Vous avez cliqué sur l'action sans cocher de matchs

**Solution** :
- Cocher les matchs à traiter
- Puis relancer l'action

### "Erreur lors de l'opération"

**Cause** : Problème technique (base de données, droits, etc.)

**Solution** :
1. Réessayer l'opération
2. Vérifier vos droits d'accès
3. Contacter l'administrateur si le problème persiste

---

## 💡 Conseils pratiques

### Optimiser votre workflow

**Publication progressive** :
1. Créer tous les matchs d'une journée
2. Affecter les équipes
3. Publier tous les matchs en une fois
4. Vérifier sur le site public

**Validation après tournoi** :
1. Saisir tous les scores
2. Vérifier les résultats
3. Valider tous les matchs en une fois
4. Empêche les modifications accidentelles

**Réorganisation rapide** :
1. Déplacer plusieurs matchs vers une nouvelle journée
2. Ajuster les horaires si nécessaire
3. Republier

### Éviter les erreurs

- ✅ **Double vérification** : Toujours vérifier la sélection avant de valider
- ✅ **Test sur peu de matchs** : Testez sur 2-3 matchs avant de traiter toute la journée
- ✅ **Confirmation** : Lisez bien le message de confirmation avant de cliquer sur OK
- ❌ **Pas de précipitation** : Prenez le temps de vérifier, la suppression est définitive

### Cas d'usage avancés

**Préparer un tournoi complet** :
1. Créer tous les matchs (30-50 matchs)
2. Affecter les équipes par phase
3. Publier en masse tous les matchs de la phase de poules
4. Garder les phases finales non publiées
5. Publier au fur et à mesure de l'avancement

**Gestion de report météo** :
1. Sélectionner tous les matchs de la journée reportée
2. Les déplacer vers la nouvelle date
3. Ajuster les horaires individuellement si besoin
4. Republier pour informer le public

---

## 📊 Suivi des opérations

### Journalisation

Toutes les opérations de masse sont enregistrées dans le **journal** :
- Date et heure de l'opération
- Utilisateur ayant effectué l'action
- Type d'opération (publication, validation, suppression, etc.)
- Nombre de matchs affectés
- Détail des matchs modifiés

**Accès** : Menu `Administration` → `Gestion Journal`

### Annulation ?

⚠️ **Il n'y a pas de fonction "Annuler"** automatique !

Si vous avez fait une erreur :
- **Publication** : Retirer la publication manuellement
- **Validation** : Demander un déverrouillage à un administrateur
- **Suppression** : **IMPOSSIBLE** à annuler → Recréer les matchs
- **Déplacement** : Redéplacer vers la journée d'origine

---

## 🔗 Fonctionnalités liées

- **Gestion Journée** : Création et modification de journées
- **Gestion Match** : Modification individuelle de matchs
- **Gestion Classement** : Calcul automatique après validation des matchs
- **Feuilles de Match** : Impression des feuilles après publication

---

## 📈 Statistiques utiles

**Exemples de gains de temps** :

| Action | Individuel | En masse | Gain |
|--------|-----------|----------|------|
| Publier 20 matchs | ~10 min | ~30 sec | **95%** |
| Valider 30 matchs | ~15 min | ~1 min | **93%** |
| Déplacer 15 matchs | ~20 min | ~2 min | **90%** |

---

**Version** : 1.0
**Date** : Décembre 2025
**Public** : Gestionnaires de compétitions, organisateurs de tournois
