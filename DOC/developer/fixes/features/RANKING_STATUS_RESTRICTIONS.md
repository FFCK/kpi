# Restrictions sur le Classement selon le Statut de Compétition

**Date**: 2 décembre 2024
**Auteur**: Claude (IA)
**Branche**: `claude/disable-ranking-inactive-011iKYJ3mho6zkT5zihibzED`
**Fichiers modifiés**:
- `sources/smarty/templates/GestionClassement.tpl`
- `sources/js/GestionClassement.js`

## 📋 Résumé

Implémentation de restrictions sur les opérations de classement lorsque le statut de la compétition n'est pas "EN COURS" (ON). Les opérations sont désormais limitées aux compétitions actives, empêchant les modifications accidentelles sur des compétitions en attente (ATT) ou terminées (END).

## 🎯 Objectif

Éviter les erreurs de manipulation sur les classements de compétitions qui ne sont pas en cours, en :
- Masquant les boutons d'action quand la compétition n'est pas active
- Désactivant les modifications manuelles des champs de classement
- Bloquant les actions côté JavaScript avec validation du statut
- Rechargeant automatiquement la page après changement de statut

## 🔧 Fonctionnalités Implémentées

### 1. Restrictions basées sur le Statut de Compétition

Les opérations suivantes sont **désactivées** lorsque `Statut != 'ON'` :

#### 1.1 Boutons masqués (Template)
- ❌ **Recalculer le classement** (`computeClt()`)
- ❌ **Publier nouveau classement** (`publicationClt()`)
- ❌ **Supprimer classement public** (`depublicationClt()`)

#### 1.2 Champs de modification manuelle désactivés
- ❌ Tous les champs avec la classe `.directInput` :
  - Classement (Clt)
  - Points (Pts)
  - Matchs joués (J)
  - Victoires/Nuls/Défaites (G/N/P)
  - Forfaits (F)
  - Buts pour/contre/différence (Plus/Moins/Diff)

#### 1.3 Protection JavaScript
Toutes les fonctions critiques vérifient le statut avant exécution :
```javascript
var statutCompet = jq('#CompetStatut').val();
if (statutCompet !== 'ON') {
    alert(langue['Statut_competition_inactif'] || 'Cette action n\'est disponible que pour les compétitions en cours (statut ON).');
    return false;
}
```

### 2. Masquage des Liens PDF pour Compétitions MULTI

Les liens vers les PDF de matchs sont masqués pour les compétitions de type **Multi-Compétition** :
- `FeuilleListeMatchs.php` (Admin)
- `PdfListeMatchs.php` (Public)

**Condition** : `{if $typeCompetition != 'Multi-Compétition'}`

### 3. Rechargement Automatique après Changement de Statut

**Comportement** :
- Lorsqu'un utilisateur clique sur le badge de statut (`.statutCompet`)
- La requête AJAX met à jour le statut dans la base de données
- Si la mise à jour réussit (`data == 'OK'`), la page se recharge automatiquement

**Avantage** : Les boutons et champs sont immédiatement mis à jour selon le nouveau statut, sans nécessiter un rafraîchissement manuel.

**Code** :
```javascript
jq(".statutCompet").click(function() {
    // ... code de changement de statut ...
    jq.post('v2/StatutCompet.php', {...},
        function(data) {
            if(data == 'OK'){
                // Rechargement de la page pour mettre à jour les boutons et champs selon le nouveau statut
                location.reload();
            } else {
                laCompet.html(statut);
                alert(langue['MAJ_impossible'] + ' : ' + data);
            }
        },
        'text'
    );
});
```

### 4. Correction de l'Affichage du Type de Compétition

**Avant** : Les compétitions MULTI affichaient "Tournoi à élimination" (CP_type)
**Après** : Affichage correct "Multi-Compétition" (MULTI_type)

**Code** :
```smarty
{if $typeCompetition=='Championnat'}
    {#CHPT_type#}
{elseif $typeCompetition=='Multi-Compétition'}
    {#MULTI_type#}
{else}
    {#CP_type#}
{/if}
```

## 📝 Détails Techniques

### Transmission du Statut au JavaScript

Un champ caché a été ajouté au formulaire pour rendre le statut accessible au JavaScript :

```html
<input type='hidden' name='CompetStatut' id='CompetStatut' Value='{$compet.Statut|default:''}' />
```

### Conditions Smarty dans le Template

#### Bouton Recalculer
```smarty
{if ($profile <= 6 or $profile == 9) && $AuthModif == 'O' && $compet.Statut == 'ON'}
    <input class="bigbutton" type="button" onclick="computeClt();" name="Calculer" value="{#Recalculer#}">
{/if}
```

#### Champs directInput (Classement général)
```smarty
{if $profile <= 4 && $AuthModif == 'O' && $compet.Statut == 'ON'}
    <span class='directInput' Id="Clt-{$arrayEquipe[i].Id}" ...>
{else}
    {$arrayEquipe[i].Clt}
{/if}
```

#### Champs directInput (Phases/Journées)
```smarty
{if $profile <= 4 && $AuthModif == 'O' && $consolidation != 'O' && $compet.Statut == 'ON'}
    <span class='directInput' Id="Clt-{$arrayEquipe_journee[i].Id}-{$arrayEquipe_journee[i].Id_journee}" ...>
{else}
    {$arrayEquipe_journee[i].Clt}
{/if}
```

## 🔐 Sécurité

### Validation Multi-Niveaux

1. **Template (Smarty)** : Masquage des éléments UI selon le statut
2. **JavaScript** : Validation avant soumission des formulaires
3. **PHP** (recommandé) : Validation côté serveur dans les fichiers de traitement

⚠️ **Note** : La validation côté serveur n'a pas été implémentée dans cette version. Il est recommandé d'ajouter des vérifications dans :
- `GestionClassement.php` (méthodes `DoClassement`, `PublicationClassement`, `DePublicationClassement`)
- `UpdateCellJQ.php` (validation avant mise à jour des cellules)

## 📊 Statuts de Compétition

| Statut | Code | Description | Actions autorisées |
|--------|------|-------------|-------------------|
| En attente | `ATT` | Compétition planifiée mais pas encore démarrée | ❌ Aucune modification |
| En cours | `ON` | Compétition active | ✅ Toutes les actions |
| Terminée | `END` | Compétition clôturée | ❌ Aucune modification |

### Cycle de Statut

```
ATT (En attente) → ON (En cours) → END (Terminée) → ATT (Réinitialisation)
```

Clic sur le badge de statut fait tourner le cycle.

## 🧪 Tests Recommandés

### Test 1 : Vérification de l'Affichage
1. Sélectionner une compétition avec statut `ATT`
2. Vérifier que les boutons "Recalculer", "Publier", "Supprimer" sont masqués
3. Vérifier que les champs directInput ne sont pas cliquables

### Test 2 : Protection JavaScript
1. Sélectionner une compétition avec statut `ATT`
2. Ouvrir la console développeur et tenter `computeClt()`
3. Vérifier qu'une alerte bloque l'action

### Test 3 : Rechargement après Changement de Statut
1. Sélectionner une compétition avec statut `ATT`
2. Cliquer sur le badge de statut pour passer à `ON`
3. Vérifier que la page se recharge automatiquement
4. Vérifier que les boutons apparaissent après rechargement

### Test 4 : Compétitions MULTI
1. Sélectionner une compétition de type Multi-Compétition
2. Vérifier que les liens "Matchs" (PDF) sont masqués
3. Vérifier que le type affiché est "Multi-Compétition"

### Test 5 : Modification Manuelle
1. Sélectionner une compétition avec statut `ON`
2. Cliquer sur un champ directInput (ex: Clt)
3. Modifier la valeur et valider
4. Changer le statut à `END`
5. Vérifier qu'on ne peut plus cliquer sur les champs directInput

## 🐛 Problèmes Connus

Aucun problème connu à ce jour.

## 🔮 Améliorations Futures

1. **Validation côté serveur** : Ajouter des vérifications dans les contrôleurs PHP
2. **Logs d'audit** : Enregistrer les tentatives de modification sur compétitions inactives
3. **Messages personnalisés** : Différencier les messages selon le statut (ATT vs END)
4. **Permissions granulaires** : Permettre certaines actions aux super-admins même si statut != ON
5. **Indicateur visuel** : Ajouter une bannière en haut de page indiquant le statut

## 📚 Références

- Fichier principal : `sources/admin/GestionClassement.php`
- Template : `sources/smarty/templates/GestionClassement.tpl`
- JavaScript : `sources/js/GestionClassement.js`
- Traductions : `sources/commun/MyLang.ini` (clé `Statut_competition_inactif` à ajouter)
- Changement de statut : `sources/admin/v2/StatutCompet.php`

## 📝 Commits

- **Initial commit** : `fed822b` - "Disable ranking operations when competition status is not 'ON'"
- **Page reload** : À venir - "Reload page after competition status change"

---

**Dernière mise à jour** : 2 décembre 2024
