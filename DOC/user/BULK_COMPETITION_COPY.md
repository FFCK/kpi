# Copie en Masse de Compétitions entre Saisons

**Fonctionnalité** : Duplication de plusieurs compétitions d'une saison vers une autre avec leurs journées et matchs

---

## 📋 À quoi ça sert ?

La fonctionnalité de **copie en masse de compétitions** permet de **dupliquer plusieurs compétitions** d'une saison source vers une saison cible en une seule opération, avec :
- Les **journées** associées
- Pour les compétitions de type CP (phases) : **deux modes de copie**
  - **Mode complet** (par défaut) : toutes les journées + tous les matchs avec leurs encodages
  - **Mode minimal** : uniquement la première journée, sans les matchs
- Ajustement automatique des **dates** (même jour de la semaine, année suivante)

**Gain de temps** : Préparer la structure d'une nouvelle saison en quelques minutes au lieu de recréer manuellement toutes les compétitions.

**Flexibilité** : Choisissez entre une copie complète avec matchs ou une structure minimale selon vos besoins.

---

## 🎯 Cas d'usage typiques

### 1. Préparation de la nouvelle saison

**Situation** : Nouvelle saison 2025 à préparer, structure similaire à 2024

**Solution** :
1. Sélectionner toutes les compétitions de 2024
2. Les copier vers 2025
3. Ajuster les détails spécifiques (sponsors, logos, etc.)

**Résultat** : Structure complète créée en quelques minutes

### 2. Reconduction d'un championnat régional

**Situation** : Championnat régional avec même format chaque année (N1, N2, N3)

**Solution** :
1. Copier les compétitions de la saison précédente
2. Les dates des journées sont automatiquement ajustées
3. Ajouter les équipes inscrites pour la nouvelle saison

### 3. Duplication de compétitions avec phases (mode complet)

**Situation** : Compétition à phases multiples (poules + phases finales) à reproduire à l'identique

**Solution** :
1. ✅ **Cocher** "Copier les matchs des compétitions CP"
2. Copier la compétition avec toutes ses phases
3. Les matchs encodés sont dupliqués avec leurs libellés
4. Affecter les équipes par la suite

### 4. Création rapide d'une structure de base (mode minimal)

**Situation** : Nouvelle compétition CP dont vous ne connaissez pas encore la structure exacte des matchs

**Solution** :
1. ⬜ **Décocher** "Copier les matchs des compétitions CP"
2. Copier la compétition → seule la première journée est créée
3. Créer manuellement les journées et matchs selon le nouveau format souhaité

**Avantage** : Évite de copier une structure de 50 matchs que vous allez devoir supprimer

---

## 🚀 Comment utiliser la copie en masse

### Étape 1 : Accéder à la gestion des opérations

1. **Menu** : `Administration` → `Gestion Opérations`
2. **Section** : "Copier des compétitions (avec journées)"

### Étape 2 : Sélectionner les saisons

1. **Saison source** : Sélectionner la saison contenant les compétitions à copier
   - Par défaut : saison de travail active
   - Les compétitions se chargent automatiquement

2. **Saison cible** : Sélectionner la saison de destination
   - Par défaut : saison de travail active
   - Doit être différente de la saison source

### Étape 3 : Sélectionner les compétitions

1. **Liste des compétitions** : Select multiple groupé par sections
   - Les compétitions sont organisées par sections (National, Régional, etc.)
   - Format : `CODE - Libelle de la compétition`

2. **Sélection multiple** :
   - Maintenir `Ctrl` (Windows/Linux) ou `Cmd` (Mac)
   - Cliquer sur chaque compétition à copier
   - Possibilité de sélectionner des compétitions de différentes sections

### Étape 4 : Choisir les options de copie (compétitions CP)

**Case à cocher** : "Copier les matchs des compétitions CP (phases)"

Cette option contrôle le comportement de copie pour les compétitions de type CP (phases) :

✅ **Cochée (par défaut)** - Mode complet :
- Copie **toutes les journées** de la compétition
- Copie **tous les matchs** avec leurs encodages `[1A - 2B]`
- Utile pour reproduire une structure complète

⬜ **Décochée** - Mode minimal :
- Copie **UNIQUEMENT la première journée**
- **SANS les matchs**
- Utile pour créer une structure de base à compléter manuellement

**Quand décocher ?**
- Vous voulez créer une structure minimale rapidement
- Vous comptez ajouter les matchs manuellement
- La structure de matchs de l'année précédente ne convient pas
- Vous voulez juste une "coquille" de compétition à remplir

**Note** : Cette option n'affecte **QUE** les compétitions de type CP. Les autres types de compétitions copient toujours toutes leurs journées (sans matchs, car elles n'en ont pas).

### Étape 5 : Lancer la copie

1. **Bouton "Copier les compétitions"**
2. **Message de confirmation** :
   - Nombre de compétitions sélectionnées
   - Décalage en années (ex: +1 an)
   - Rappel des paramètres de copie
   - **Information sur le mode de copie CP** (avec ou sans matchs)

3. **Validation** : Cliquer sur "OK"

### Étape 6 : Vérification

1. **Message de résultat** :
   - Nombre de compétitions copiées
   - Nombre de journées créées
   - Nombre de matchs créés (pour les compétitions CP)
   - Nombre de compétitions ignorées (si doublons)

2. **Vérifier les compétitions** :
   - Accéder à la gestion des compétitions
   - Vérifier que les compétitions sont bien présentes
   - Contrôler les dates des journées

---

## ⚙️ Fonctionnement détaillé

### Ce qui est copié

✅ **Compétition** :
- Code de la compétition
- Libellé, sous-titres (Soustitre, Soustitre2)
- Niveau, type de classement
- Configuration (Web, BandeauLink, LogoLink, SponsorLink)
- Paramètres de compétition (Age min/max, Sexe, Nb équipes)
- Paramètres de classement (Qualifiés, Éliminés, Points, Mode de calcul)

✅ **Journées** :
- Nom et libellé
- Dates ajustées (même jour de semaine)
- Type, Phase, Niveau, Étape
- Nombre d'équipes

**Important** : Pour les compétitions CP, le nombre de journées copiées dépend de l'option choisie :
- ✅ **Option cochée** : TOUTES les journées sont copiées
- ⬜ **Option décochée** : UNIQUEMENT la première journée est copiée

✅ **Matchs (compétitions type CP - SI option cochée)** :
- Libellé (encodage du match, ex: `[1A - 2B]`)
- Type de match
- Terrain
- Numéro d'ordre
- Période
- Date et heure (ajustées)

**Note** : Si l'option "Copier les matchs des compétitions CP" est **décochée**, **aucun match n'est copié**, même pour les compétitions CP.

### Ce qui est réinitialisé

🔄 **Compétition** :
- **Statut** : `ATT` (en attente)
- **Publication** : Vide (non publique)
- **Verrou** : `N` (non verrouillée)
- **Commentaires** : Vidés
- **Dates de calcul/publication** : Réinitialisées
- **Utilisateurs** : Code_uti_calcul et Code_uti_publication vidés

🔄 **Journées** :
- **Lieu** : Vide
- **Organisateur** : Vide (Code, nom, adresse, CP, ville)
- **Délégué** : Vide
- **Chef Arbitre** : Vide
- **Validation** : `N` (non validée)
- **Publication** : Vide (non publiée)
- **Id_dupli** : NULL

🔄 **Matchs (CP uniquement)** :
- **Équipes A et B** : NULL (pas d'équipes affectées)
- **Couleurs A et B** : Vides
- **Scores** : NULL (ScoreA, ScoreB, ScoreDetailA, ScoreDetailB)
- **Coefficients** : NULL
- **Commentaires** : Vides
- **Arbitres** : Vides (principal, secondaire, matricules)
- **Officiels** : Vides (secrétaire, chronomètre, Timeshoot, lignes)
- **Statut** : `ATT` (non validé)
- **Publication** : Vide (non publié)

### Ce qui n'est PAS copié

❌ **Jamais copié** :
- **Équipes inscrites** : Aucune équipe n'est associée aux compétitions copiées
- **Compositions d'équipes** : Listes de joueurs
- **Résultats et classements** : Scores, statistiques
- **Matchs (compétitions non-CP)** : Seules les compétitions de type CP (phases) ont leurs matchs copiés

### Gestion des conflits

**Compétition déjà existante** :
- Si une compétition avec le même code existe déjà dans la saison cible
- Elle est **automatiquement ignorée**
- Un **avertissement** est affiché : `⚠️ Compétition XXX ignorée : existe déjà dans la saison YYYY`
- La copie continue pour les autres compétitions

### Ajustement des dates

**Principe** : Les dates sont ajustées pour **conserver le même jour de la semaine**

**Algorithme** :
1. Calculer le décalage en années : `saison_cible - saison_source`
2. Ajouter le décalage à la date source
3. Ajuster pour retrouver le même jour de la semaine (±3 jours max)

**Exemple** :
- Date source : Samedi 5 avril 2025
- Décalage : +1 an → 5 avril 2026 (dimanche)
- Ajustement : -1 jour → **Samedi 4 avril 2026** ✅

**Cas particuliers** :
- Dates vides (`0000-00-00`) : Conservées telles quelles
- Heures : Conservées identiques (seule la date change)

---

## 🔒 Restrictions et sécurité

### Droits d'accès

**Profil ≤ 6** :
- ✅ Peut copier des compétitions entre saisons
- ✅ Accès à la fonctionnalité dans "Gestion Opérations"

**Profil > 6** :
- ❌ Fonctionnalité non accessible

### Validations automatiques

Le système vérifie :
- ✅ **Saisons différentes** : Source et cible ne peuvent pas être identiques
- ✅ **Au moins une compétition sélectionnée**
- ✅ **Existence des compétitions source**
- ✅ **Pas de doublons** dans la saison cible (ignorés automatiquement)
- ✅ **Transaction SQL** : Rollback automatique en cas d'erreur

### Traçabilité

**Journal des modifications** :
- **Action** : "Copie Compétitions"
- **Détails** : Saison source → Saison cible
- **Résumé** : Nombre de compétitions copiées, journées, matchs, ignorées
- **Utilisateur** : Code utilisateur ayant effectué l'opération
- **Date** : Date et heure de l'opération

---

## ⚠️ Points d'attention

### Avant de copier

1. **Vérifier la saison source**
   - Les compétitions source sont-elles complètes ?
   - Les journées sont-elles correctement configurées ?
   - Les dates sont-elles cohérentes ?

2. **Vérifier la saison cible**
   - La saison existe-t-elle ?
   - Y a-t-il déjà des compétitions avec les mêmes codes ?
   - Êtes-vous sûr de vouloir copier vers cette saison ?

3. **Sélection des compétitions**
   - Vérifier que toutes les compétitions souhaitées sont sélectionnées
   - Ne pas sélectionner de compétitions inutiles

4. **Choix de l'option pour les compétitions CP**
   - ✅ **Cocher** si vous voulez copier toutes les journées et matchs (structure complète)
   - ⬜ **Décocher** si vous voulez juste une structure minimale (1ère journée uniquement, sans matchs)
   - Réfléchissez bien : décocher évite de copier des dizaines de matchs inutiles

### Après la copie

1. **Compétitions** :
   - ✅ Activer les compétitions (statut `ATT` → `PUBLIE`)
   - ✅ Mettre à jour les logos/sponsors si changement
   - ✅ Configurer la publication
   - ✅ Ajuster les paramètres si nécessaire

2. **Journées** :
   - ✅ Vérifier les dates ajustées
   - ✅ Compléter les lieux
   - ✅ Affecter les organisateurs
   - ✅ Nommer les délégués et chefs arbitres

3. **Matchs (CP uniquement)** :
   - **Si option cochée** (mode complet) :
     - ✅ Vérifier les encodages `[...]`
     - ✅ Affecter les équipes (manuellement ou automatiquement)
     - ✅ Ajuster les heures si nécessaire
     - ✅ Configurer les terrains
   - **Si option décochée** (mode minimal) :
     - ⚠️ **Aucun match copié** : vous devez créer manuellement :
       - Les journées manquantes (seule la 1ère a été copiée)
       - Tous les matchs
       - La planification complète

4. **Équipes** :
   - ✅ Inscrire les équipes pour la nouvelle saison
   - ✅ Affecter les équipes aux journées
   - ✅ Saisir les compositions

---

## 🐛 Problèmes courants

### "Les saisons source et cible doivent être différentes"

**Cause** : Vous avez sélectionné la même saison en source et cible

**Solution** :
- Modifier l'une des deux saisons
- Exemple : Source = 2024, Cible = 2025

### "Veuillez sélectionner au moins une compétition à copier"

**Cause** : Aucune compétition n'est sélectionnée dans la liste

**Solution** :
- Maintenir Ctrl/Cmd et cliquer sur les compétitions à copier
- Vérifier que les cases sont bien surlignées

### "⚠️ Compétition XXX ignorée : existe déjà dans la saison YYYY"

**Cause** : Une compétition avec le même code existe déjà dans la saison cible

**Explication** : Ce n'est **pas une erreur**, c'est un **comportement normal**

**Actions possibles** :
1. **Ne rien faire** : Si vous voulez conserver la compétition existante
2. **Supprimer la compétition existante** : Si vous voulez la remplacer par la copie
3. **Renommer la compétition existante** : Changer son code pour libérer le code

### "⚠️ Compétition XXX non trouvée dans la saison YYYY"

**Cause** : La compétition sélectionnée n'existe plus ou a été supprimée

**Solution** :
1. Recharger la page (F5)
2. Sélectionner uniquement les compétitions existantes
3. Contacter l'administrateur si le problème persiste

### "Erreur lors de la copie des compétitions"

**Cause** : Erreur technique (base de données, droits, etc.)

**Solution** :
1. Réessayer l'opération
2. Vérifier vos droits d'accès
3. Consulter le message d'erreur détaillé
4. Contacter l'administrateur avec le message d'erreur

### "Les compétitions ne se chargent pas dans la liste"

**Cause** : Problème de chargement Ajax ou aucune compétition dans la saison

**Solution** :
1. Vérifier qu'une saison source est bien sélectionnée
2. Attendre quelques secondes (message "Chargement...")
3. Vérifier qu'il y a bien des compétitions dans cette saison
4. Rafraîchir la page (F5)

---

## 💡 Conseils pratiques

### Optimiser votre workflow

1. **Préparation de saison**
   - Copier toutes les compétitions en une seule fois
   - Puis affiner compétition par compétition
   - Plus rapide que de créer manuellement

2. **Copie progressive**
   - Copier d'abord les compétitions nationales
   - Puis les compétitions régionales
   - Enfin les compétitions locales
   - Permet de tester et valider progressivement

3. **Test sur une compétition**
   - Commencer par copier **une seule** compétition
   - Vérifier que tout est correct (dates, journées, matchs)
   - Si OK, copier les autres en masse

4. **Utiliser les sections**
   - Les compétitions sont groupées par sections
   - Copier section par section
   - Plus facile de vérifier et valider

### Éviter les erreurs

- ✅ **Vérifier la saison source** avant de copier
- ✅ **Sélectionner avec attention** les compétitions
- ✅ **Lire le message de confirmation** (nombre de compétitions, années)
- ✅ **Vérifier après copie** que tout est correct
- ❌ Ne pas copier si les compétitions existent déjà (seront ignorées)
- ❌ Ne pas oublier d'ajuster les paramètres après copie

### Cas d'usage avancés

**Préparation complète d'une nouvelle saison** :
1. Copier toutes les compétitions de 2024 → 2025
2. Vérifier les dates des journées (ajustées automatiquement)
3. Mettre à jour les logos/sponsors de saison
4. Inscrire les équipes pour 2025
5. Affecter les équipes aux compétitions
6. Publier les compétitions au fur et à mesure

**Copie sélective pour certaines compétitions** :
1. Copier uniquement les compétitions nationales (N1, N2, N3)
2. Créer manuellement les compétitions régionales (format différent)
3. Copier ensuite les compétitions jeunes
4. Permet de mixer copie automatique et création manuelle

**Gestion des compétitions à phases (CP)** :
1. Copier la compétition (journées + matchs encodés)
2. Vérifier les encodages `[1A - 2B]` dans les matchs
3. Ajuster les poules si changement de structure
4. Utiliser l'affectation automatique pour affecter les équipes
5. Publier au fur et à mesure des phases

**Décalage de plusieurs années** :
- La copie fonctionne avec n'importe quel décalage
- Exemples : 2024 → 2025 (+1 an), 2024 → 2026 (+2 ans)
- Les dates sont toujours ajustées pour conserver le même jour de semaine

---

## 📊 Statistiques de gain de temps

**Exemples concrets de gains de temps** :

| Tâche | Méthode manuelle | Copie en masse | Gain |
|-------|------------------|----------------|------|
| Créer 1 compétition avec 10 journées | ~30 min | ~2 sec | **99%** |
| Créer 20 compétitions | ~10 heures | ~5 min | **99%** |
| Créer compétition CP (30 matchs) | ~2 heures | ~5 sec | **99%** |
| Préparer une saison complète | ~3 jours | ~30 min | **99%** |

**Pour une saison complète** (20 compétitions, 150 journées) :
- Temps manuel : ~3 jours de travail
- Temps avec copie en masse : ~30 minutes
- **Gain : 99%** de temps économisé

**Avantages supplémentaires** :
- ✅ Pas d'erreur de saisie
- ✅ Cohérence garantie avec la saison précédente
- ✅ Dates automatiquement ajustées
- ✅ Structure prête immédiatement

---

## 🔍 Détails techniques

### Endpoint Ajax

**Fichier** : `sources/admin/Ajax_competitions_by_saison.php`

**Fonction** : Charger dynamiquement les compétitions d'une saison

**Paramètres** :
- `saison` : Code de la saison (ex: `2025`)

**Retour** : JSON avec compétitions groupées par sections
```json
[
  {
    "label": "National",
    "options": [
      {"Code": "N1M", "Libelle": "N1 Hommes"},
      {"Code": "N1F", "Libelle": "N1 Femmes"}
    ]
  }
]
```

### Fonction PHP

**Fichier** : `sources/admin/GestionOperations.php`

**Fonction** : `CopyCompetitions()`

**Paramètres POST** :
- `saisonSourceCompet` : Code de la saison source
- `saisonCibleCompet` : Code de la saison cible
- `codesCompet[]` : Array des codes de compétitions à copier
- `copierMatchsCP` : `'on'` si checkbox cochée, `'off'` sinon (défaut : `'off'`)

**Fonction auxiliaire** : `adjustDateSameWeekday($dateStr, $yearOffset)`
- Ajuste une date pour conserver le même jour de semaine
- Décalage de ±3 jours maximum pour trouver le même jour

**Retour** : Messages dans `$this->m_arrayinfo`

### Fonction JavaScript

**Fichier** : `sources/js/GestionOperations.js`

**Fonctions** :
- `CopyCompetitions()` : Validation et soumission du formulaire
- `loadCompetitionsForSeason()` : Chargement Ajax des compétitions

**Événement** : `onchange` sur le select de saison source

---

## 📚 Documentation connexe

- [Gestion Compétition](MULTI_COMPETITION_TYPE.md) - Configuration des compétitions
- [Gestion Journée - Opérations de Masse](MATCH_DAY_BULK_OPERATIONS.md) - Actions groupées sur les matchs
- [Copie de Composition d'Équipe](TEAM_COMPOSITION_COPY.md) - Dupliquer les listes de joueurs

---

## 🎓 FAQ - Questions fréquentes

### Q1 : Les équipes sont-elles copiées ?

**R** : Non, les équipes ne sont **jamais** copiées. Vous devez inscrire les équipes après la copie.

### Q2 : Puis-je copier vers une saison antérieure ?

**R** : Oui, la copie fonctionne dans les deux sens (ex: 2025 → 2024 = -1 an).

### Q3 : Que se passe-t-il si je copie deux fois la même compétition ?

**R** : La deuxième copie sera **ignorée** avec un message d'avertissement. La compétition existante n'est **pas modifiée**.

### Q4 : Les matchs sont-ils copiés pour toutes les compétitions ?

**R** : Non, uniquement pour les compétitions de **type CP** (phases), et **UNIQUEMENT si la case "Copier les matchs des compétitions CP" est cochée**. Les autres types de compétitions n'ont que leurs journées copiées (ils n'ont pas de matchs).

### Q5 : Pourquoi décocher l'option "Copier les matchs des compétitions CP" ?

**R** : Décochez cette option si :
- Vous voulez créer une structure minimale (juste la compétition + première journée)
- Vous comptez créer les matchs manuellement avec un format différent
- Vous ne voulez pas copier une grosse structure de matchs qui ne convient plus
- Vous voulez gagner du temps en évitant de supprimer des dizaines de matchs inutiles

**Résultat** : Seule la **première journée** est copiée, **sans aucun match**.

### Q6 : Puis-je annuler une copie ?

**R** : Il n'y a pas de fonction "Annuler". Si vous avez copié par erreur, vous devez **supprimer manuellement** les compétitions créées.

### Q7 : Les dates sont-elles toujours exactement +1 an ?

**R** : Non, les dates sont ajustées pour **conserver le même jour de la semaine**. Exemple : samedi 5 avril 2025 → samedi 4 avril 2026 (même jour de semaine, mais pas exactement +365 jours).

### Q8 : Puis-je copier des compétitions d'une saison verrouillée ?

**R** : Oui, la saison **source** peut être verrouillée (lecture seule). Seule la saison **cible** doit être modifiable.

### Q9 : Combien de compétitions puis-je copier en une seule fois ?

**R** : Pas de limite technique, mais il est recommandé de ne pas dépasser 50 compétitions par opération pour éviter les problèmes de timeout.

### Q10 : Les sponsors et logos sont-ils copiés ?

**R** : Les **liens** (BandeauLink, LogoLink, SponsorLink) sont copiés, mais vous devrez peut-être les mettre à jour si les sponsors changent.

### Q11 : Comment savoir si une compétition a été ignorée ?

**R** : Un message d'avertissement s'affiche : `⚠️ Compétition XXX ignorée : existe déjà dans la saison YYYY`. Le compteur de compétitions ignorées est affiché dans le résumé final.

---

**Version** : 1.1
**Date** : Décembre 2025
**Dernière mise à jour** : 12/12/2025 - Ajout option copie matchs CP
**Public** : Administrateurs, gestionnaires de compétitions
**Auteur** : Laurent Garrigue / Claude Code
