# Consolidation des Phases de Classement

## Description

La fonctionnalité de **consolidation des phases** permet aux administrateurs de "figer" le classement de certaines phases d'une compétition de type **Coupe (CP)**, empêchant leur recalcul lors de la mise à jour du classement général de la compétition.

## Cas d'usage

Cette fonctionnalité est particulièrement utile dans les situations suivantes :

1. **Phases terminées dont les résultats sont définitifs** : Une fois une phase de poule terminée et validée, vous pouvez la consolider pour éviter qu'elle soit recalculée par erreur.

2. **Phases avec résultats particuliers** : Lorsqu'une phase a des résultats qui ont été ajustés manuellement (ex: décisions du comité de discipline, forfaits, etc.), la consolidation garantit que ces ajustements ne seront pas écrasés.

3. **Performance** : Sur les grandes compétitions avec de nombreuses phases, consolider les phases terminées accélère le recalcul du classement.

4. **Traçabilité** : La consolidation permet de garantir qu'un classement publié ne sera plus modifié, même si de nouveaux matchs sont ajoutés à la compétition.

## Utilisation

### Accès à la fonctionnalité

- **Page** : Gestion du Classement (`GestionClassement.php`)
- **Type de compétition** : Coupe (CP) uniquement
- **Droits requis** : Profile ≤ 4 (Administrateur)

### Consolider une phase

1. Accédez à la page **Classement** d'une compétition de type CP
2. Dans la section **Déroulement**, localisez la phase que vous souhaitez consolider
3. À gauche du nom de la phase, vous verrez :
   - Une **case à cocher** pour la consolidation
   - Le libellé **"Phase consolidée"**
4. **Cochez la case** pour consolider la phase
5. La page se recharge automatiquement
6. Les champs de classement deviennent **non modifiables** (lecture seule)

### Déconsolider une phase

1. Localisez la phase consolidée (case cochée)
2. **Décochez la case**
3. La page se recharge automatiquement
4. Les champs de classement redeviennent **modifiables**

### Indicateurs visuels

#### Phase consolidée
- ✅ Case à cocher **cochée**
- 🔒 Champs de classement en **lecture seule** (Clt, Pts, Plus, Moins, Diff)
- 📝 Libellé "Phase consolidée" affiché

#### Phase non consolidée
- ☐ Case à cocher **non cochée**
- ✏️ Champs de classement **modifiables** (classe `directInput`)
- 📝 Libellé "Phase consolidée" affiché (mais case non cochée)

### Droits insuffisants

Si vous n'avez pas les droits suffisants (profile > 4), vous verrez :
- Aucune case à cocher pour les phases non consolidées
- Pour les phases déjà consolidées : case cochée et disabled avec le libellé

## Comportement lors du recalcul

### Phases consolidées

Lors d'un **recalcul du classement** (bouton "Recalculer") :

✅ **Préservées** :
- Les données de classement de la phase ne sont **PAS réinitialisées**
- Les matchs de la phase ne sont **PAS pris en compte** dans le recalcul
- Les classements (Clt, Pts, J, G, N, P, F, Plus, Moins, Diff) restent **inchangés**

### Phases non consolidées

❌ **Recalculées** :
- Les données de classement sont réinitialisées
- Les matchs validés sont pris en compte dans le nouveau calcul
- Le classement est mis à jour selon les résultats des matchs

## Exemples d'utilisation

### Exemple 1 : Tournoi avec phases de qualification terminées

**Contexte** : Tournoi avec 2 phases de qualification (A et B) et une phase finale en cours.

**Action** :
1. Phase A terminée → **consolider**
2. Phase B terminée → **consolider**
3. Phase finale en cours → **ne pas consolider**

**Résultat** :
- Les classements des phases A et B sont figés
- Seule la phase finale sera recalculée lors des mises à jour
- Gain de temps lors du recalcul

### Exemple 2 : Ajustement manuel suite à une décision

**Contexte** : Une équipe a été disqualifiée après coup, nécessitant un ajustement manuel des points.

**Action** :
1. Ajuster manuellement les points dans la phase concernée
2. **Consolider la phase** pour éviter qu'elle soit recalculée
3. Publier le nouveau classement

**Résultat** :
- L'ajustement manuel est préservé
- Les futurs recalculs ne modifieront pas cette phase

## Restrictions et limitations

### Restrictions

- ✋ **Modification manuelle impossible** sur une phase consolidée
- ✋ **Profile requis** : Seuls les utilisateurs avec profile ≤ 4 peuvent consolider/déconsolider
- ✋ **Type de compétition** : Uniquement pour les compétitions de type **CP (Coupe)**

### Limitations techniques

- 🔄 La page se **recharge automatiquement** après chaque consolidation/déconsolidation
- 🔒 Les champs deviennent en lecture seule **uniquement après rechargement**
- ⚠️ Vérifiez que les **matchs verrouillés** de la phase sont corrects avant de consolider

## Bonnes pratiques

### ✅ À faire

1. **Vérifier avant de consolider**
   - Assurez-vous que tous les matchs de la phase sont validés (verrouillés)
   - Vérifiez que le classement est correct
   - Contrôlez les points, différences de buts, etc.

2. **Consolider progressivement**
   - Consolidez les phases au fur et à mesure qu'elles se terminent
   - Ne consolidez pas trop tôt (risque de devoir déconsolider)

3. **Documenter les ajustements**
   - Si vous consolidez après un ajustement manuel, documentez la raison
   - Utilisez le journal de la compétition si disponible

### ❌ À éviter

1. **Ne pas consolider trop tôt**
   - Attendez que la phase soit complètement terminée
   - Vérifiez qu'aucun match en attente n'est prévu

2. **Ne pas oublier de publier**
   - La consolidation ne publie PAS automatiquement le classement
   - Pensez à utiliser le bouton "Publier nouveau classement" après consolidation

3. **Attention aux dépendances**
   - Si des phases suivantes dépendent du classement d'une phase consolidée, vérifiez la cohérence

## Questions fréquentes (FAQ)

### Q : Puis-je modifier manuellement une phase consolidée ?
**R** : Non, les champs de classement (Clt, Pts, Plus, Moins, Diff) sont en lecture seule. Vous devez d'abord déconsolider la phase, faire vos modifications, puis la reconsolider.

### Q : Que se passe-t-il si je déconsolide une phase puis la reconsolide ?
**R** : Les données ne changent pas si vous ne recalculez pas entre temps. Si vous recalculez après avoir déconsolidé, la phase sera recalculée avec les matchs validés.

### Q : La consolidation affecte-t-elle le classement public ?
**R** : Non, la consolidation n'affecte que le recalcul du classement. Pour publier, utilisez le bouton "Publier nouveau classement" comme d'habitude.

### Q : Puis-je consolider plusieurs phases en même temps ?
**R** : Oui, cochez les cases des différentes phases. Chaque clic recharge la page, mais toutes les consolidations sont indépendantes.

### Q : La consolidation est-elle réversible ?
**R** : Oui, totalement. Décochez simplement la case pour déconsolider la phase. Les données consolidées sont préservées.

### Q : Que se passe-t-il si j'ajoute un nouveau match dans une phase consolidée ?
**R** : Le nouveau match ne sera pas pris en compte dans le calcul du classement tant que la phase reste consolidée. Déconsolidez, recalculez, puis reconsolidez si nécessaire.

## Support technique

Pour toute question ou problème concernant cette fonctionnalité, contactez l'équipe de développement ou consultez la documentation technique dans `DOC/developer/fixes/features/CONSOLIDATION_PHASES_CLASSEMENT.md`.

---

**Version** : 1.0
**Date** : 2025-01-23
**Auteur** : Équipe de développement KPI
