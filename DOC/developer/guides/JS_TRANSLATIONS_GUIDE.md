# Système de traductions JavaScript centralisé

## Vue d'ensemble

Ce document décrit le système de traductions JavaScript centralisé implémenté pour le backend Smarty du projet KPI.

## Architecture

### Fichiers principaux

1. **`sources/commun/js_translations_fr.json`** - Fichier JSON contenant toutes les traductions en français
2. **`sources/commun/js_translations_en.json`** - Fichier JSON contenant toutes les traductions en anglais
3. **`sources/commun/js_translations.php`** - Fichier PHP qui génère dynamiquement le JavaScript contenant les traductions selon la langue de session

### Fonctionnement

1. **Stockage des traductions** : Toutes les traductions JavaScript sont centralisées dans des fichiers JSON (un par langue)
2. **Génération dynamique** : Le fichier PHP lit la langue de session (`$_SESSION['lang']`) et charge le fichier JSON correspondant
3. **Chargement dans les templates** : Les templates Smarty incluent le fichier PHP comme un script JavaScript
4. **Disponibilité globale** : L'objet `langue` est disponible globalement dans tous les fichiers JavaScript

## Utilisation

### Dans les templates Smarty

Le fichier de traductions est chargé automatiquement dans les templates de base (`kppage.tpl`, `page.tpl`, `kppagewide.tpl`, `kppageleaflet.tpl`) :

```html
<script type='text/javascript' src='commun/js_translations.php'></script>
```

ou pour les pages admin :

```html
<script type='text/javascript' src='../commun/js_translations.php'></script>
```

### Dans les fichiers JavaScript

Les traductions sont accessibles via l'objet global `langue` :

```javascript
// Exemple d'utilisation
if (!confirm(langue['Confirmer_MAJ'])) {
    return false;
}

// Autre exemple
alert(langue['MAJ_impossible']);
```

**Note** : Il n'est plus nécessaire de définir les traductions localement dans chaque fichier JS.

## Ajout de nouvelles traductions

### Étape 1 : Ajouter la traduction dans les fichiers JSON

Éditez les deux fichiers JSON :

**`sources/commun/js_translations_fr.json`** :
```json
{
  ...
  "Nouvelle_cle": "Texte en français",
  ...
}
```

**`sources/commun/js_translations_en.json`** :
```json
{
  ...
  "Nouvelle_cle": "Text in English",
  ...
}
```

### Étape 2 : Utiliser la traduction

Dans votre fichier JavaScript :

```javascript
alert(langue['Nouvelle_cle']);
```

## Conventions de nommage

- Les clés utilisent le format `Snake_Case` avec majuscules
- Les clés sont descriptives et en anglais
- Les espaces sont remplacés par des underscores `_`

Exemples :
- `Confirmer_MAJ` - Confirmation de mise à jour
- `Nom_equipe_vide` - Message d'erreur pour nom d'équipe vide
- `Selection_competition` - Message de sélection de compétition

## Fichiers migrés

Les fichiers JavaScript suivants ont été migrés vers le système centralisé :

1. `GestionCalendrier.js`
2. `GestionClassement.js`
3. `GestionCompetition.js`
4. `GestionEquipe.js`
5. `GestionEquipeJoueur.js`
6. `GestionEvenement.js`
7. `GestionGroupe.js`
8. `GestionInstances.js`
9. `GestionJournee.js`
10. `GestionOperations.js`
11. `GestionRc.js`
12. `GestionStructure.js`

## Avantages du système centralisé

1. **Maintenance facilitée** : Toutes les traductions sont au même endroit
2. **Cohérence** : Les mêmes traductions sont utilisées partout
3. **Extensibilité** : Facile d'ajouter de nouvelles langues
4. **Performance** : Les traductions sont chargées une seule fois
5. **Réutilisabilité** : Les traductions peuvent être partagées entre différents fichiers JS

## Dépannage

### Les traductions ne s'affichent pas

1. Vérifier que le fichier `js_translations.php` est bien inclus dans le template
2. Vérifier que la session est démarrée et que `$_SESSION['lang']` est définie
3. Vérifier la console JavaScript pour des erreurs
4. Vérifier que les fichiers JSON sont bien formatés (JSON valide)

### Erreur "langue is not defined"

Cela signifie que le fichier `js_translations.php` n'a pas été chargé avant le fichier JavaScript qui l'utilise. Vérifier l'ordre de chargement des scripts dans le template.

### Les traductions sont en anglais au lieu du français

Vérifier la valeur de `$_SESSION['lang']`. Elle doit être 'fr' pour le français et 'en' pour l'anglais.

## Migration d'anciens fichiers

Si vous avez un ancien fichier JavaScript avec des traductions locales, voici comment le migrer :

### Avant (ancien système)

```javascript
jq = jQuery.noConflict()

var langue = []

if (lang == 'en') {
    langue['Confirmer_MAJ'] = 'Confirm update ?'
    langue['Nom_evt_vide'] = 'Event name is empty, unable to create'
} else {
    langue['Confirmer_MAJ'] = 'Confirmez-vous le changement ?'
    langue['Nom_evt_vide'] = 'Le Nom de l\'événement est vide, ajout impossible'
}

function validEvenement () {
    // ...
}
```

### Après (nouveau système)

```javascript
jq = jQuery.noConflict()

// Les traductions sont maintenant chargées depuis le fichier centralisé js_translations.php
// L'objet 'langue' est disponible globalement

function validEvenement () {
    // ...
}
```

**Important** : Assurez-vous d'ajouter toutes les traductions utilisées dans le fichier aux fichiers JSON centralisés avant de supprimer les définitions locales.

## Contact

Pour toute question ou problème concernant le système de traductions, veuillez consulter ce document ou contacter l'équipe de développement.
