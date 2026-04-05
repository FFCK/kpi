# Statistique de Cohérence des Matchs

## 📋 Vue d'ensemble

La fonctionnalité **Cohérence des matchs** permet de détecter automatiquement les problèmes de planification pour les équipes participantes sur une saison et des compétitions sélectionnées.

**Fichiers concernés :**
- `sources/admin/GestionStats.php` (lignes 855-1116)
- `sources/smarty/templates/GestionStats.tpl`

**Commit :** f43d7d1 - "Ajout de la statistique de cohérence des matchs"

---

## 🎯 Fonctionnalités

### Types d'incohérences détectées

1. **Arbitrage < 1h après match joué**
   - Détecte quand une équipe doit arbitrer (en tant qu'arbitre principal ou secondaire) moins d'une heure après avoir joué un match

2. **Match < 1h après arbitrage**
   - Détecte quand une équipe doit jouer un match moins d'une heure après avoir arbitré un match

3. **Plus de 6 matchs par jour (International)**
   - Identifie les équipes qui jouent plus de 6 matchs dans la même journée, pour les compétitions de niveau International (`Code_niveau = 'INT'`)

4. **Plus de 3 matchs sur 4 heures**
   - Repère les équipes qui jouent plus de 3 matchs sur une fenêtre de 4 heures consécutives (toutes compétitions confondues)

5. **Match < 1h après le match précédent (National)**
   - Détecte quand une équipe doit jouer un match moins d'une heure après avoir commencé un autre match, pour les compétitions de niveau National (`Code_niveau = 'NAT'`)

6. **Plus de 2 matchs sur 3 heures (National)**
   - Repère les équipes qui jouent plus de 2 matchs sur une fenêtre de 3 heures consécutives, pour les compétitions de niveau National (`Code_niveau = 'NAT'`)

7. **Plus de 4 matchs par jour (Championnat)**
   - Identifie les équipes qui jouent plus de 4 matchs dans la même journée, pour les compétitions de type Championnat (`Code_typeclt = 'CHPT'`)

> **Note multi-compétitions** : Les calculs portent sur l'ensemble des matchs d'une équipe, y compris si elle est engagée dans plusieurs compétitions se déroulant sur le même week-end et le même lieu.

---

## 🚀 Utilisation

### Accès à la fonctionnalité

1. Se connecter à l'interface d'administration
2. Accéder à **GestionStats.php**
3. Sélectionner une **saison**
4. Sélectionner une ou **plusieurs compétitions** (CTRL+clic pour sélection multiple)
5. Dans le menu déroulant **"Statistiques"**, choisir **"Cohérence des matchs"**
6. Cliquer sur **"MAJ"** pour lancer l'analyse

### Interface utilisateur

**Affichage des résultats :**
- **Titre** : Affiche le nombre total d'incohérences détectées
- **Tableau** avec les colonnes :
  - `#` : Numéro de ligne
  - `Type d'incohérence` : Type de problème détecté (en gras)
  - `Équipe` : Nom de l'équipe concernée
  - `Compétition` : Code de la compétition
  - `Date` : Date de l'incohérence
  - `Lieu` : Lieu du match/arbitrage
  - `Détails` : Description précise avec horaires et intervalles

**Message de confirmation :**
- Si aucune incohérence n'est détectée, un message vert s'affiche : ✓ Aucune incohérence détectée

---

## 🔧 Détails techniques

### Algorithme de détection

1. **Récupération des données**
   - Chargement de tous les matchs de la saison/compétition avec dates, heures, équipes et arbitres
   - Requête SQL avec jointures sur `kp_match`, `kp_journee`, `kp_competition`, `kp_competition_equipe`
   - Les champs `Code_niveau` et `Code_typeclt` de `kp_competition` sont récupérés pour chaque match

2. **Construction de la timeline**
   - Création d'un tableau d'événements pour chaque équipe
   - Deux types d'événements : `match` (joué) et `arbitrage` (arbitré)
   - Tri chronologique des événements par date/heure

3. **Extraction des équipes d'arbitres**
   - Pattern regex : `/\(([^)]+)\)/` pour extraire le nom de l'équipe
   - Format attendu : "Nom Prénom (Équipe)"
   - Recherche intelligente dans les équipes de la compétition

4. **Analyse des incohérences**
   - Parcours séquentiel de la timeline de chaque équipe
   - Vérification des intervalles temporels entre événements
   - Évitement des doublons (une seule alerte par jour pour "Plus de 6 matchs/jour")

### Calculs de temps

```php
// Intervalle entre deux événements
$diff_minutes = ($datetime_evt - $datetime_prev) / 60;

// Fenêtre glissante de 4 heures
$datetime_limit = $datetime_evt + (4 * 3600);
```

### Format de données

**Structure d'un événement :**
```php
array(
    'type' => 'match' | 'arbitrage',
    'datetime' => 'YYYY-MM-DD HH:MM:SS',
    'equipe' => 'Nom de l\'équipe',
    'match_id' => 123,
    'competition' => 'Code compétition',
    'lieu' => 'Ville',
    'role' => 'Équipe A' | 'Équipe B' | 'Arbitre principal' | 'Arbitre secondaire',
    'adversaire' => 'Nom équipe adverse' (pour type=match),
    'match' => 'Équipe A vs Équipe B' (pour type=arbitrage)
)
```

**Structure d'une incohérence :**
```php
array(
    'type' => 'Arbitrage < 1h après match' | 'Match < 1h après arbitrage' | ...,
    'equipe' => 'Nom de l\'équipe',
    'competition' => 'Code compétition',
    'date' => 'DD/MM/YYYY',
    'heure_match' => 'HH:MM',
    'heure_arbitrage' => 'HH:MM',
    'details' => 'Description complète avec horaires et intervalle',
    'lieu' => 'Ville'
)
```

---

## 📊 Cas d'usage

### Exemples d'incohérences détectées

**Exemple 1 : Arbitrage après match**
```
Type: Arbitrage < 1h après match
Équipe: AS Kayak Lyon
Date: 15/03/2025
Détails: Match Équipe A vs Bordeaux CK à 10:00,
         puis Arbitre principal à 10:45 (45 min)
```

**Exemple 2 : Surcharge journalière**
```
Type: Plus de 6 matchs/jour
Équipe: Paris Kayak Polo
Date: 22/04/2025
Détails: 8 matchs joués le 22/04/2025
```

**Exemple 3 : Période intensive**
```
Type: Plus de 3 matchs/4h
Équipe: Toulouse KP
Date: 10/05/2025
Détails: 4 matchs de 14:00 à 17:30
```

---

## ⚠️ Limitations et notes

1. **Extraction des arbitres**
   - Fonctionne uniquement si le format "Nom Prénom (Équipe)" est respecté
   - Si le nom de l'équipe ne peut pas être extrait, l'arbitrage n'est pas comptabilisé

2. **Durée des matchs**
   - L'analyse se base sur l'heure de début des matchs
   - La durée réelle des matchs n'est pas prise en compte (hypothèse : ~20-30 min)

3. **Permissions**
   - La statistique est visible uniquement pour les utilisateurs avec `profile <= 6`

4. **Performance**
   - Pour de très grandes saisons (>1000 matchs), le temps de calcul peut atteindre plusieurs secondes
   - Optimisations possibles via mise en cache ou indexation

---

## 🔄 Évolutions possibles

### Améliorations suggérées

1. **Export CSV**
   - Ajouter la possibilité d'exporter les incohérences en CSV
   - Modifier `export_stats_csv.php` pour supporter le cas `CoherenceMatchs`

2. **Filtres additionnels**
   - Filtrer par type d'incohérence
   - Filtrer par équipe spécifique
   - Afficher uniquement les incohérences critiques (< 30 min)

3. **Alertes automatiques**
   - Envoi d'email aux responsables lors de la détection d'incohérences
   - Dashboard avec compteur en temps réel

4. **Prise en compte de la durée réelle**
   - Utiliser `Heure_fin` si disponible
   - Calculer la durée moyenne des matchs par compétition

5. **Visualisation graphique**
   - Timeline visuelle avec code couleur
   - Graphique de charge par équipe et par jour

---

## 📝 Maintenance

### Tests recommandés

Avant chaque mise en production, vérifier :

1. ✅ Sélection de compétitions multiples fonctionne
2. ✅ Calcul correct des intervalles de temps
3. ✅ Pas de doublons dans les résultats
4. ✅ Gestion des cas limites (équipe sans match, sans arbitrage)
5. ✅ Format des dates (FR vs EN selon la langue)
6. ✅ Performance avec >500 matchs

### Base de données

**Tables utilisées :**
- `kp_match` : matchs avec dates, heures, arbitres
- `kp_journee` : journées et compétitions
- `kp_competition` : niveau (`Code_niveau`) et type de classement (`Code_typeclt`) de la compétition
- `kp_competition_equipe` : équipes participantes

**Colonnes critiques :**
- `kp_match.Date_match` : ne doit pas être NULL
- `kp_match.Heure_match` : ne doit pas être NULL
- `kp_match.Arbitre_principal` / `Arbitre_secondaire` : format texte libre
- `kp_competition.Code_niveau` : `INT` (International), `NAT` (National), `REG` (Régional)
- `kp_competition.Code_typeclt` : `CHPT` (Championnat), `CP` (autre type)

---

## 📞 Support

Pour toute question ou bug concernant cette fonctionnalité :
- Vérifier les logs PHP pour les erreurs SQL
- Tester avec une saison/compétition contenant peu de matchs
- Vérifier le format des noms d'arbitres dans la base de données

**Date de création :** 2025-11-22
**Version :** 1.0
**Auteur :** Claude Code
