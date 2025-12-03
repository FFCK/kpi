# Gestion Journée - Opérations de Masse sur les Matchs

**Fonctionnalité** : Actions groupées sur plusieurs matchs simultanément

---

## 📋 À quoi ça sert ?

La fonctionnalité d'**opérations de masse** permet d'effectuer certaines actions sur **plusieurs matchs en même temps**, au lieu de les traiter un par un.

**Gain de temps** : Au lieu de modifier 20 matchs individuellement, effectuez l'action en quelques clics.

---

## 🎯 Opérations disponibles (ordre des boutons)

Les opérations sont présentées dans l'ordre où elles apparaissent dans l'interface :

### 1. 🗑️ Suppression multiple de matchs

**Bouton** : Icône poubelle
**Profil requis** : ≤ 6

**Action** : Supprimer plusieurs matchs d'un coup

**Utilisation** :
- Nettoyer des matchs créés par erreur
- Supprimer un planning incomplet
- Réinitialiser une journée

⚠️ **Attention** :
- Les matchs **validés** ne peuvent PAS être supprimés
- Les matchs avec scores ne peuvent PAS être supprimés
- La suppression est **définitive** (pas d'annulation possible)

---

### 2. 🌐 Publication multiple de matchs

**Bouton** : Icône œil
**Profil requis** : ≤ 6

**Action** : Rendre plusieurs matchs visibles sur le site public

**Utilisation** :
- Publier tous les matchs d'une journée en une seule fois
- Afficher le planning complet sur le site public
- Préparer l'affichage avant le début du tournoi

**Effet** :
- Les matchs deviennent visibles sur [kayak-polo.info](https://kayak-polo.info)
- Le public peut consulter horaires, terrains et équipes
- Les matchs apparaissent dans les plannings publics

---

### 3. 🔓🌐 Verrouillage + Publication simultanés

**Bouton** : Icône œil + verrou
**Profil requis** : ≤ 4 (Administrateurs uniquement)

**Action** : Publier ET verrouiller plusieurs matchs en une seule opération

**Utilisation** :
- Finaliser une journée de compétition
- Publier les résultats définitifs
- Clôturer un tournoi

**Effet** :
- Les matchs sont publiés (visibles publiquement)
- Les matchs sont verrouillés (plus de modifications possibles)
- Gain de temps par rapport à 2 opérations séparées

---

### 4. 🔒 Verrouillage multiple de matchs

**Bouton** : Icône cadenas
**Profil requis** : ≤ 4 (Administrateurs uniquement)

**Action** : Verrouiller plusieurs matchs pour empêcher les modifications

**Utilisation** :
- Verrouiller tous les matchs terminés d'une journée
- Figer les résultats après validation
- Empêcher les modifications accidentelles

**Effet** :
- Les équipes, horaires et résultats ne peuvent plus être changés
- Protection contre les erreurs de manipulation
- Seuls les profils ≤ 3 peuvent déverrouiller

---

### 5. 🎯 Affectation automatique des équipes et arbitres

**Bouton** : Icône AffectAuto
**Profil requis** : ≤ 6

**Action** : Affecte automatiquement les équipes et arbitres selon le codage

**Principe** : Utilise les codes entre crochets `[...]` dans le libellé du match pour affecter automatiquement :
- Les équipes (ex: `1A`, `2B` = 1er et 2e de la poule A/B)
- Les équipes par tirage (ex: `T1`, `D2` = équipe tirée 1 ou 2)
- Les vainqueurs/perdants de matchs précédents (ex: `V12`, `P15` = Vainqueur/Perdant du match 12/15)
- Les arbitres (même logique)

**Format du code** : `[EquipeA - EquipeB / Arbitre1 - Arbitre2]`

**Exemples** :
- `[1A - 2B]` = 1er de poule A contre 2e de poule B
- `[V12 - V15 / P12]` = Vainqueur match 12 vs Vainqueur match 15, arbitré par Perdant match 12
- `[T1 - T2]` = Équipes tirées 1 et 2

**Conditions** :
- Les matchs ne doivent PAS être validés
- Les matchs ne doivent PAS avoir de score
- Le codage doit être correct (entre crochets)

---

### 6. ❌ Désaffectation automatique (Annulation affectation)

**Bouton** : Icône AnnulAuto
**Profil requis** : ≤ 6

**Action** : Vide les équipes et arbitres de plusieurs matchs

**Utilisation** :
- Recommencer l'affectation des équipes
- Nettoyer un planning avant de le refaire
- Préparer des matchs "templates"
- Corriger une affectation automatique erronée

**Effet** :
- Les équipes A et B sont vidées (NULL)
- Les arbitres principal et secondaire sont réinitialisés
- Les compositions d'équipes sont supprimées

**Conditions** :
- Les matchs ne doivent PAS être validés
- Les matchs ne doivent PAS avoir de score

---

### 7. 📅 Changement de journée (Changer de poule)

**Bouton** : Icône Chang
**Profil requis** : ≤ 6

**Action** : Déplacer plusieurs matchs vers une autre journée

**Utilisation** :
- Reporter des matchs vers une autre date
- Réorganiser le planning
- Fusionner ou diviser des journées
- Corriger des erreurs d'affectation de journée

**Conditions** :
- Les matchs ne doivent PAS être validés
- Une journée de destination doit être sélectionnée dans la liste déroulante

**Effet** :
- Le match change de journée
- Les équipes et arbitres restent inchangés
- Le numéro d'ordre peut être réaffecté

---

### 8. 📄 Génération des feuilles de match (PDF)

**Bouton** : Icône PDF
**Profil requis** : Aucun (accessible à tous)

**Action** : Génère un PDF contenant toutes les feuilles de match sélectionnées

**Utilisation** :
- Imprimer les feuilles de match pour les arbitres
- Préparer la documentation avant le tournoi
- Archiver les feuilles de match

**Format** : PDF multi-pages (une page par match)

**Contenu** :
- Intitulé du match et codage
- Équipes A et B
- Compositions d'équipes (si renseignées)
- Arbitres
- Horaire et terrain
- Espace pour saisie des scores

**Note** : Ouvre dans un nouvel onglet, ne modifie pas les matchs

---

### 9. ✅ Marquage comme "Imprimé"

**Bouton** : Icône imprimeO
**Profil requis** : ≤ 6

**Action** : Marque plusieurs matchs comme imprimés

**Utilisation** :
- Suivre l'état d'impression des feuilles de match
- Savoir quels matchs ont déjà été imprimés et distribués
- Éviter les doublons d'impression

**Effet** :
- Un indicateur visuel (icône verte) apparaît sur le match
- Aucun impact fonctionnel (juste un indicateur)
- Peut être annulé en cliquant à nouveau

**Workflow typique** :
1. Sélectionner les matchs à imprimer
2. Cliquer sur le bouton PDF pour générer les feuilles
3. Imprimer le PDF
4. Cliquer sur "Imprimé" pour marquer les matchs

---

### 10. 🔢 Renumérotation des matchs

**Bouton** : Icône numMatchs
**Profil requis** : ≤ 2 (Super-administrateurs uniquement)

**Action** : Renuméroter plusieurs matchs à partir d'un numéro de départ

**Utilisation** :
- Corriger la numérotation après suppressions/ajouts
- Réorganiser l'ordre des matchs
- Uniformiser la numérotation

**Fonctionnement** :
1. Sélectionner les matchs à renuméroter (dans l'ordre souhaité)
2. Cliquer sur le bouton
3. Saisir le **numéro de départ** (ex: 1, 10, 50)
4. Les matchs sont numérotés séquentiellement à partir de ce numéro

**Exemple** :
- Matchs sélectionnés : 5, 12, 18, 22
- Numéro de départ : 10
- Résultat : Les matchs deviennent 10, 11, 12, 13

⚠️ **Attention** : Les matchs validés ne peuvent pas être renumérotés

---

### 11. 📅 Changement de date des matchs

**Bouton** : Icône calendrier
**Profil requis** : ≤ 2 (Super-administrateurs uniquement)

**Action** : Change la date de plusieurs matchs en une seule fois

**Utilisation** :
- Reporter un tournoi suite à un problème (météo, salle indisponible, etc.)
- Corriger une erreur de date sur plusieurs matchs
- Uniformiser la date d'une journée

**Fonctionnement** :
1. Sélectionner les matchs concernés
2. Cliquer sur le bouton
3. Choisir la **nouvelle date** dans le calendrier
4. Valider

**Effet** :
- Tous les matchs sélectionnés passent à la nouvelle date
- Les heures restent inchangées
- Les matchs verrouillés sont ignorés

**Note** : Utilise un calendrier interactif pour la sélection de la date

---

### 12. ⏰ Incrémentation de l'heure des matchs

**Bouton** : Icône horloge
**Profil requis** : ≤ 2 (Super-administrateurs uniquement)

**Action** : Définit les heures des matchs avec un intervalle automatique

**Principe** : Permet de planifier rapidement une série de matchs avec un intervalle fixe entre chaque

**Fonctionnement** :
1. Sélectionner les matchs (dans l'ordre chronologique souhaité)
2. Cliquer sur le bouton
3. Définir l'**heure de départ** (ex: 10:00)
4. Définir l'**intervalle** en minutes (ex: 40 min)
5. Valider

⚠️ **RECOMMANDATION IMPORTANTE** :
**Filtrez par terrain avant d'agir sur les heures** ! Si vous avez plusieurs terrains avec des matchs simultanés, ne sélectionnez QUE les matchs d'un seul terrain à la fois. Sinon, tous les matchs de tous les terrains auront les mêmes horaires, ce qui créera des conflits de planning.

**Exemple** :
- 5 matchs sélectionnés
- Heure de départ : 10:00
- Intervalle : 40 minutes
- Résultat :
  - Match 1 : 10:00
  - Match 2 : 10:40
  - Match 3 : 11:20
  - Match 4 : 12:00
  - Match 5 : 12:40

**Cas d'usage** :
- Planifier rapidement une matinée ou après-midi de matchs
- Recaler un planning suite à un retard
- Uniformiser les intervalles entre matchs

⚠️ **Attention** : Les matchs verrouillés sont ignorés

---

### 13. 🔄 Remplacement de nom de poule/groupe

**Bouton** : Icône refresh
**Profil requis** : ≤ 2 (Super-administrateurs uniquement)

**Action** : Remplace le nom de groupe/poule dans les codes de matchs

**Principe** : Modifie automatiquement les codes d'affectation `[...]` en remplaçant une lettre de groupe par une autre

**Utilisation** :
- Corriger une erreur de nommage de poules (A au lieu de X)
- Réorganiser les poules suite à un changement de structure
- Uniformiser les noms de groupes

**Fonctionnement** :
1. Sélectionner les matchs concernés
2. Cliquer sur le bouton
3. Saisir le **groupe à remplacer** (ex: A)
4. Saisir le **nouveau groupe** (ex: X)
5. Valider

**Exemple** :
- Code actuel : `[1A - 2A / 3B]`
- Remplacer : A → X
- Code modifié : `[1X - 2X / 3B]`

**Règles** :
- Seules les lettres MAJUSCULES sont acceptées
- Le remplacement est sensible à la casse
- Seuls les codes entre crochets `[...]` sont traités
- Les matchs verrouillés sont ignorés

**Note** : Très utile en cas de changement de structure de compétition après création des matchs

---

## 🚀 Comment utiliser les opérations de masse

### Accéder à la gestion des journées

1. **Menu** : `Administration` → `Gestion Journée`
2. **Sélectionner l'événement** et la **compétition**
3. **Choisir la journée** concernée (optionnel, peut rester sur "Tous")

### Sélectionner les matchs

1. **Cocher les cases** à gauche de chaque match
   - Cliquer individuellement sur les matchs à traiter
   - Ou utiliser "Tout sélectionner" (coche tous les matchs affichés)
   - Ou utiliser "Aucun" (décoche tous les matchs)

2. **Vérifier la sélection**
   - Les cases des matchs sélectionnés sont cochées
   - Comptez visuellement le nombre de matchs sélectionnés

### Exécuter l'action

1. **Cliquer sur le bouton** correspondant à l'action souhaitée
   - Les boutons sont dans la barre d'outils au-dessus du tableau
   - Passez la souris sur les icônes pour voir les info-bulles

2. **Confirmer l'action** (pour certaines opérations)
   - Une popup de confirmation apparaît
   - Vérifier le nombre de matchs concernés
   - Cliquer sur "OK" pour valider ou "Annuler" pour abandonner

3. **Vérification du résultat**
   - Message de confirmation : "X matchs modifiés"
   - Vérifier visuellement les changements dans le tableau
   - Rafraîchir la page si nécessaire

---

## 🔒 Restrictions et sécurité

### Droits d'accès par profil

**Profils ≤ 2** (Super-administrateurs) :
- ✅ **Toutes** les opérations de masse
- ✅ Opérations avancées (renumérotation, changement date/heure, remplacement groupe)

**Profils 3-4** (Administrateurs) :
- ✅ Suppression, publication, validation
- ✅ Affectation/désaffectation automatique
- ✅ Changement de journée
- ✅ Génération PDF, marquage imprimé
- ❌ Renumérotation, changement date/heure, remplacement groupe

**Profils 5-6** (Gestionnaires) :
- ✅ Suppression, publication
- ✅ Affectation/désaffectation automatique
- ✅ Changement de journée
- ✅ Génération PDF, marquage imprimé
- ❌ Verrouillage
- ❌ Opérations avancées

**Profils > 6** (Utilisateurs) :
- ❌ Aucune opération de masse
- ✅ Génération PDF uniquement (consultation)

### Matchs verrouillés

Si un match est **verrouillé** (icône cadenas fermé) :
- ❌ Aucune opération de masse ne peut le modifier
- ❌ Les opérations de masse l'ignorent automatiquement
- ✅ Seuls les profils ≤ 3 peuvent déverrouiller

### Matchs avec score

Si un match a **un score saisi** :
- ❌ Ne peut pas être supprimé
- ❌ Ne peut pas être affecté/désaffecté automatiquement
- ✅ Peut être publié, verrouillé, déplacé

---

## ⚠️ Points d'attention

### Avant d'exécuter une opération

1. **Vérifier la sélection**
   - Comptez le nombre de matchs cochés
   - Assurez-vous que ce sont bien les bons matchs
   - Décochez les matchs non concernés

2. **Comprendre les conséquences**
   - Publication = visible par le public sur le site
   - Validation = verrouillage définitif (déverrouillage difficile)
   - Suppression = **DÉFINITIVE** (pas d'annulation possible)
   - Affectation auto = remplace les équipes/arbitres existants
   - Désaffectation = **vide complètement** les équipes et arbitres

3. **Sauvegarder si nécessaire**
   - Notez les informations importantes avant une suppression
   - Exportez le planning si besoin (PDF ou capture d'écran)
   - Faites un test sur 1-2 matchs avant de traiter toute la journée

### Après l'opération

1. **Vérifier le résultat**
   - Parcourir la liste des matchs modifiés
   - Vérifier les icônes (🌐 publication, 🔒 verrouillage, ✅ imprimé)
   - Consulter le site public si publication
   - Rafraîchir la page si les changements ne sont pas visibles

2. **Journal des modifications**
   - Chaque opération est enregistrée dans le journal système
   - Consultable dans "Gestion Journal"
   - Permet de tracer qui a fait quoi et quand

---

## 🐛 Problèmes courants

### "Compétition verrouillée"

**Cause** : La compétition est en mode lecture seule

**Solution** :
- Demander le déverrouillage à un administrateur (profil ≤ 3)
- Vérifier vos droits d'accès sur cette compétition

### "Impossible de supprimer des matchs validés"

**Cause** : Un ou plusieurs matchs sélectionnés sont validés (verrouillés)

**Solution** :
1. Décocher les matchs validés (icône 🔒 fermé)
2. Ou demander à un administrateur de les déverrouiller d'abord
3. Puis relancer la suppression

### "Aucun match sélectionné"

**Cause** : Vous avez cliqué sur l'action sans cocher de matchs

**Solution** :
- Cocher les matchs à traiter
- Puis relancer l'action

### "Erreur lors de l'affectation automatique"

**Causes possibles** :
- Code entre crochets `[...]` incorrect ou absent
- Équipe ou arbitre introuvable (tirage, classement)
- Match déjà validé ou avec score

**Solution** :
1. Vérifier le format des codes : `[EquipeA - EquipeB / Arb1 - Arb2]`
2. Vérifier que les équipes/poules existent
3. Vérifier que les matchs ne sont pas validés
4. Consulter le message d'erreur pour plus de détails

### "Les changements ne s'affichent pas"

**Cause** : Cache du navigateur

**Solution** :
1. Rafraîchir la page (F5 ou Ctrl+R)
2. Vider le cache du navigateur (Ctrl+Shift+R)
3. Si le problème persiste, fermer et rouvrir le navigateur

---

## 💡 Conseils pratiques

### Optimiser votre workflow

**Publication progressive** :
1. Créer tous les matchs d'une journée
2. Affecter les équipes (manuellement ou automatiquement)
3. Vérifier les affectations
4. **Publier tous les matchs en une fois**
5. Vérifier sur le site public

**Validation après tournoi** :
1. Saisir tous les scores
2. Vérifier les résultats et classements
3. Corriger les erreurs éventuelles
4. **Valider tous les matchs en une fois**
5. Empêche les modifications accidentelles ultérieures

**Planification rapide avec incrémentation d'heure** :
1. Créer tous les matchs d'une session
2. **Filtrer par terrain** (si plusieurs terrains)
3. Les sélectionner dans l'ordre chronologique (un terrain à la fois)
4. Utiliser l'incrémentation d'heure (10:00, intervalle 40 min)
5. Répéter pour chaque terrain
6. Résultat : planning automatique en quelques secondes

**Réorganisation rapide** :
1. Créer les matchs avec des codes génériques `[1A-2A]`
2. Si changement de structure (A→X), utiliser le remplacement de groupe
3. Tous les codes sont mis à jour automatiquement

### Éviter les erreurs

- ✅ **Double vérification** : Toujours vérifier la sélection avant de valider
- ✅ **Test sur peu de matchs** : Testez sur 2-3 matchs avant de traiter toute la journée
- ✅ **Confirmation** : Lisez bien le message de confirmation avant de cliquer sur OK
- ✅ **Filtres** : Utilisez les filtres (journée, terrain, date) pour afficher uniquement les matchs concernés
- ❌ **Pas de précipitation** : Prenez le temps de vérifier, la suppression est définitive

### Cas d'usage avancés

**Préparer un tournoi complet** :
1. Créer tous les matchs (30-50 matchs) avec codes d'affectation
2. Affecter les équipes par phase avec l'affectation automatique
3. Planifier les heures avec l'incrémentation automatique
4. Publier en masse tous les matchs de la phase de poules
5. Garder les phases finales non publiées
6. Publier au fur et à mesure de l'avancement

**Gestion de report météo** :
1. Sélectionner tous les matchs de la journée reportée
2. **Changer la date** vers la nouvelle date
3. **Ajuster les heures** si nécessaire (incrémentation)
4. Republier pour informer le public des changements

**Correction d'erreur de structure** :
1. Erreur détectée : les poules A et B doivent s'appeler X et Y
2. Sélectionner tous les matchs concernés
3. **Remplacer A → X** en une opération
4. **Remplacer B → Y** en une opération
5. Tous les codes `[...]` sont mis à jour automatiquement

---

## 📊 Statistiques de gain de temps

**Exemples concrets de gains de temps** :

| Action | Méthode manuelle | Opération de masse | Gain |
|--------|------------------|---------------------|------|
| Publier 20 matchs | ~10 min (30s × 20) | ~30 sec | **95%** |
| Valider 30 matchs | ~15 min (30s × 30) | ~1 min | **93%** |
| Déplacer 15 matchs | ~20 min (80s × 15) | ~2 min | **90%** |
| Planifier 25 heures | ~25 min (60s × 25) | ~1 min | **96%** |
| Renuméroter 30 matchs | ~30 min (60s × 30) | ~1 min | **97%** |

**Pour un tournoi de 50 matchs** :
- Temps manuel total : ~2h30
- Temps avec opérations de masse : ~10 min
- **Gain : 93%** de temps économisé

---

## 📚 Documentation connexe

- [Gestion Compétition](MULTI_COMPETITION_TYPE.md) - Configuration des compétitions
- [Event Cache Manager](EVENT_CACHE_MANAGER.md) - Worker pour incrustations vidéo
- [Copie de composition](TEAM_COMPOSITION_COPY.md) - Dupliquer les listes de joueurs

---

**Version** : 2.0
**Date** : Décembre 2025
**Public** : Gestionnaires de compétitions, organisateurs de tournois, administrateurs
