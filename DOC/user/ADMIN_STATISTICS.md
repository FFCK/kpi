# Statistiques - Nouvelle Interface Administration

## Accès

- **URL** : `https://kpi.localhost/admin2/stats` (dev) ou `https://kayak-polo.info/admin2/stats` (prod)
- **Profil requis** : ≤ 9 (tous les administrateurs)
- **Profils restreints** : Certaines statistiques sont réservées aux profils ≤ 6

## Fonctionnalités

### Types de Statistiques Disponibles

| Catégorie | Type | Description |
|-----------|------|-------------|
| **Performance** | Buteurs | Classement des meilleurs buteurs individuels |
| | Attaque | Équipes par buts marqués |
| | Défense | Équipes par buts encaissés (moins = mieux) |
| **Discipline** | Cartons (joueurs) | Cartons par joueur : vert, jaune, rouge, rouge définitif |
| | Cartons (équipes) | Cartons par équipe |
| | Cartons (compétitions) | Synthèse par compétition |
| | Fairplay (joueurs) | Score fairplay individuel (moins = mieux) |
| | Fairplay (équipes) | Score fairplay par équipe |
| **Arbitrage** | Arbitrage (arbitres) | Matchs arbitrés par personne |
| | Arbitrage (équipes) | Matchs arbitrés par équipe |
| **Compétitions jouées** | Par clubs | Matchs joués groupés par club actuel |
| | Par équipes | Matchs joués groupés par équipe de compétition |
| | Champ. nationaux | Filtré sur codes N* |
| | Coupe de France | Filtré sur codes CF* |
| **Officiels** | Journées | Officiels désignés par journée |
| | Matchs | Officiels par match |
| **Listes** | Arbitres | Liste des arbitres FFCK |
| | Équipes | Équipes inscrites avec clubs |
| | Joueurs | Joueurs inscrits (hors entraîneurs) |
| | Joueurs & coachs | Joueurs ET entraîneurs |
| **Analyses** | Irrégularités | Licences anciennes, pagaies manquantes... (profil ≤ 6) |
| | Licenciés nationaux | Répartition H/F par catégorie d'âge (profil ≤ 6) |
| | Cohérence matchs | Détection d'incohérences de planning (profil ≤ 6) |

### Paramètres de Recherche

1. **Type de statistique** : Sélectionner le type souhaité
2. **Saison** : Choisir la saison (la saison active est sélectionnée par défaut)
3. **Compétitions** : Sélectionner une ou plusieurs compétitions (Ctrl+clic pour sélection multiple)
4. **Limite** : Nombre maximum de résultats (1-500)

### Exports

Deux formats d'export sont disponibles :

#### Export Excel (XLSX)
- Fichier compatible Microsoft Excel, LibreOffice Calc
- Colonnes avec largeur automatique
- En-têtes en gras
- Colonne de classement (#) pour les stats avec ranking

#### Export PDF
- Document formaté avec :
  - **En-tête** : Logo KPI + titre + kayak-polo.info
  - **Corps** : Tableau des données avec numéro d'ordre si applicable
  - **Pied de page** : Date/heure d'export (heure locale) + numéro de page

Les exports utilisent la langue de l'interface (français ou anglais).

## Interface

### Barre de Paramètres

Affiche un résumé des paramètres actuels :
- Type de statistique sélectionné
- Saison
- Compétitions (avec tooltip si > 3 compétitions)
- Limite
- Boutons d'export (Excel, PDF)
- Bouton "Changer" pour modifier les paramètres

### Description de la Statistique

Sous la barre de paramètres, une description explique ce que mesure la statistique sélectionnée.

### Tableau de Résultats

- Affichage en tableau pleine largeur sur desktop
- Affichage en cartes sur mobile
- Colonnes numériques alignées à droite
- Colonne de classement (#) pour certains types (Buteurs, Cartons, Fairplay, Arbitrage)
- Formatage des dates au format local

### Modal de Paramétrage

Ouverte via le bouton "Changer", permet de modifier :
- Type de statistique (avec description)
- Saison (recharge les compétitions automatiquement)
- Limite (avec boutons +/-)
- Compétitions (groupées par section)

## Raccourcis et Astuces

- **Sélection multiple** : Maintenir Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs compétitions
- **Tout sélectionner** : Les compétitions sont groupées par section (National, Coupe de France, Régional, etc.)
- **Persistance** : Les paramètres sont sauvegardés et restaurés à la prochaine visite

## Différences avec l'Ancienne Interface (GestionStats)

| Fonctionnalité | Ancienne | Nouvelle |
|----------------|----------|----------|
| Interface | PHP/Smarty | Nuxt 4 / Vue 3 |
| Responsive | Non | Oui (mobile-first) |
| Exports traduits | Non | Oui (FR/EN) |
| En-tête/pied PDF | Non | Oui (logo, date, pages) |
| Sauvegarde paramètres | Non | Oui |
| Description stats | Non | Oui |
| Sélection compétitions | Liste simple | Groupes avec sections |

## Support

En cas de problème :
1. Vérifier que vous êtes connecté avec un profil autorisé
2. Vérifier qu'au moins une compétition est sélectionnée
3. Rafraîchir la page si les données ne s'affichent pas

---

**Document créé le** : 2026-01-26
**Statut** : ✅ Fonctionnalité en production
