# Statistique : Licenciés FFCK par Catégorie d'Âge

**Date de création** : 29 novembre 2024
**Statut** : ✅ Implémenté
**Auteur** : Claude Code
**Type** : Nouvelle fonctionnalité

---

## 📋 Description

Nouvelle statistique dans GestionStats permettant d'afficher la répartition des licenciés FFCK ayant effectivement joué dans les compétitions sélectionnées, par **sexe** et **catégorie d'âge**.

## 🎯 Objectif

Fournir des données précises sur la participation effective des licenciés par catégorie d'âge pour :
- Analyser la répartition hommes/femmes
- Suivre l'évolution des pratiquants par tranche d'âge
- Comparer les données entre différentes saisons
- Alimenter les rapports d'activité de la FFCK

## 📊 Catégories d'Âge

Le calcul de l'âge se fait **au 1er janvier de l'année civile** (saison sélectionnée).

### Hommes
- **U16** : moins de 16 ans (minimes)
- **U18** : 16-17 ans (cadets)
- **U23** : 18-22 ans (juniors)
- **U35** : 23-34 ans (seniors)
- **+35** : 35 ans et plus (vétérans)
- **Total** : Total hommes

### Femmes
- **U16** : moins de 16 ans (minimes)
- **U18** : 16-17 ans (cadets)
- **U23** : 18-22 ans (juniors)
- **U35** : 23-34 ans (seniors)
- **+35** : 35 ans et plus (vétérans)
- **Total** : Total femmes

### Total général
Somme de tous les licenciés (hommes + femmes)

## 🔍 Critères de Sélection

### Licences comptabilisées
- **Licences FFCK uniquement** : `Matric < 2000000`
- **Joueurs ayant effectivement joué** : présents dans `kp_match_joueur`
- **Entraîneurs/coachs inclus** : code 'E' inclus dans le décompte
- **Exclusions** :
  - Arbitres (code 'A') exclus
  - Joueurs exclus (code 'X') exclus

### Filtres utilisateur
- **Saison** : Saison sélectionnée dans l'interface
- **Compétitions** : Compétitions sélectionnées par l'utilisateur (toutes compétitions possibles, pas uniquement nationales)

## 📁 Fichiers Modifiés

### Backend PHP

#### 1. GestionStats.php
**Localisation** : `sources/admin/GestionStats.php` (lignes 855-972)

**Requête SQL** :
```sql
SELECT
    'KAP' AS code_activite,
    COUNT(DISTINCT CASE WHEN l.Sexe = 'M' AND ? - YEAR(l.Naissance) < 16 THEN l.Matric END) AS hommes_u16,
    -- ... (10 paramètres ? pour les calculs d'âge)
    COUNT(DISTINCT l.Matric) AS total_activite
FROM kp_journee j
INNER JOIN kp_match m ON m.Id_journee = j.Id
INNER JOIN kp_match_joueur mj ON mj.Id_match = m.Id
INNER JOIN kp_licence l ON l.Matric = mj.Matric
WHERE j.Code_competition IN (...)
    AND j.Code_saison = ?
    AND l.Matric < 2000000
    AND mj.Capitaine NOT IN ('A','X')
```

**Paramètres** :
- 10× `$codeSaison` (pour les calculs d'âge dans les CASE)
- Array `$Compets` (compétitions sélectionnées)
- 1× `$codeSaison` (filtre saison)

#### 2. FeuilleStats.php (Export PDF FR)
**Localisation** : `sources/admin/FeuilleStats.php` (lignes 664-699 et 1266-1321)

**Caractéristiques PDF** :
- Mode **paysage** (L) pour afficher toutes les colonnes
- Largeur totale : 277mm (format A4 paysage)
- Titre : "Licenciés FFCK ayant joué par catégorie d'âge - Saison {saison}"
- 15 colonnes au total

#### 3. FeuilleStatsEN.php (Export PDF EN)
**Localisation** : `sources/admin/FeuilleStatsEN.php` (lignes 684-720 et 1246-1301)

**Caractéristiques** :
- Même format que version FR
- Titre : "Licensed FFCK Players by Age Category - Season {saison}"
- Labels en anglais (M/W au lieu de H/F)

### Templates Smarty

#### 4. GestionStats.tpl
**Localisation** : `sources/smarty/templates/GestionStats.tpl`

**Modifications** :
- Ligne 46 : Titre de la statistique
- Lignes 220-235 : En-têtes de colonnes (15 colonnes)
- Lignes 548-567 : Affichage des données
- Lignes 642-644 : Option dans le menu déroulant

**Affichage** :
- Saison en **gras**
- Totaux (H Total, F Total, TOTAL) en **gras**
- Valeurs numériques centrées

## 🎨 Interface Utilisateur

### Menu Statistiques
Nouvelle option : **"Licenciés nationaux par catégorie"**

Position : Après "Joueurs & Entraîneurs", avant "Cohérence des matchs"

Accessible uniquement aux profils ≤ 6 (staff)

### Exports disponibles
1. **PDF Français** : Bouton "PDF FR"
2. **PDF Anglais** : Bouton "PDF EN"
3. **CSV** : Via l'icône CSV (utilise les mêmes données)

## 🔧 Détails Techniques

### Calcul de l'âge
```php
// Âge au 1er janvier de la saison
$age = $codeSaison - YEAR(l.Naissance)
```

### Optimisation requête
- Utilisation de `COUNT(DISTINCT ...)` pour éviter les doublons
- `INNER JOIN` pour meilleures performances
- Paramètres préparés pour sécurité SQL

### Gestion des données vides
- Si aucun résultat : `$row['saison'] = $codeSaison` ajouté manuellement
- Template affiche une ligne même si totaux = 0

## 📈 Utilisation

### Cas d'usage typiques

1. **Rapport annuel d'activité** :
   - Sélectionner toutes les compétitions nationales (N%, CF%)
   - Exporter en PDF pour inclusion dans le rapport

2. **Analyse d'une compétition spécifique** :
   - Sélectionner une seule compétition
   - Voir la répartition des participants

3. **Comparaison entre saisons** :
   - Changer de saison
   - Comparer les totaux

## ✅ Tests Effectués

- ✅ Affichage dans GestionStats.php
- ✅ Export PDF FR (mode paysage)
- ✅ Export PDF EN (mode paysage)
- ✅ Filtre par saison
- ✅ Filtre par compétitions
- ✅ Inclusion des entraîneurs (code 'E')
- ✅ Exclusion des arbitres (code 'A')
- ✅ Calcul de l'âge au 1er janvier
- ✅ Totaux en gras

## 🐛 Problèmes Résolus

### 1. Différence avec "Joueurs & Coachs"
**Problème initial** : Écart de 5 licenciés (682 vs 677)

**Cause** :
- "Joueurs & Coachs" compte les **inscrits** (`kp_competition_equipe_joueur`)
- Cette stat compte ceux qui ont **effectivement joué** (`kp_match_joueur`)

**Résultat** : Comportement normal et attendu

### 2. Filtrage initial trop restrictif
**Problème** : Requête initiale filtrait uniquement sur compétitions nationales (N%, CF%)

**Solution** : Utilisation des compétitions sélectionnées par l'utilisateur

### 3. Exclusion des entraîneurs
**Problème** : Filtre initial `NOT IN ('E','A','X')` excluait les entraîneurs

**Solution** : Filtre corrigé en `NOT IN ('A','X')` pour inclure les entraîneurs

## 📝 Notes

- La colonne "Activité" affiche toujours "KAP" (Kayak-Polo)
- Cette valeur permet de comparer avec d'autres activités FFCK à l'avenir
- Le format de sortie est compatible avec les exports FFCK existants

## 🔄 Évolutions Futures Possibles

- [ ] Ajouter d'autres activités FFCK (Slalom, Marathon, etc.)
- [ ] Export CSV dédié avec format spécifique FFCK
- [ ] Graphiques de répartition (camemberts, histogrammes)
- [ ] Comparaison multi-saisons sur un même PDF
- [ ] Filtrage par région/ligue

---

**Références** :
- Code compétition : `kp_competition.Code`
- Capitaine : `kp_match_joueur.Capitaine` (E=Entraîneur, A=Arbitre, X=Exclu)
- Matric : `kp_licence.Matric` (< 2000000 = FFCK)
