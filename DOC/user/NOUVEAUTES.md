# 🎉 Nouveautés KPI

Ce document liste les dernières fonctionnalités et améliorations ajoutées au système KPI, triées par date de mise en production.

---

## 📅 Décembre 2025

### 🔄 Copie en masse de compétitions entre saisons (Décembre 2025)

**Duplication rapide de plusieurs compétitions**
- Copie de plusieurs compétitions d'une saison vers une autre en une opération
- Copie automatique des journées avec ajustement des dates (même jour de semaine)
- Pour les compétitions à phases (CP) : copie optionnelle des matchs (mode complet ou minimal)
- Préparation d'une saison complète en 30 minutes au lieu de 3 jours

**Accès** : Administration > Opérations > Copier des compétitions (avec journées)

**⚠️ Attention** : Opération sensible réservée au profil 1 (Webmaster)

**Documentation complète** : [BULK_COMPETITION_COPY.md](BULK_COMPETITION_COPY.md)

---

## 📅 Novembre 2025

### 📚 Documentation intégrée (30/11/2025)

**Visualiseur de documentation markdown**
- Accès direct à la documentation depuis l'administration
- Documentation utilisateur accessible sans authentification
- Documentation développeur réservée au profil Webmaster (profil 1)
- Navigation intuitive par catégories et dossiers
- Conversion automatique markdown → HTML
- Liens internes fonctionnels entre documents

**Accès** : Administration > Opérations > Documentation KPI

---

### 🔄 Fusion automatique de licenciés non fédéraux (Novembre 2025)

**Détection et fusion des doublons**
- Fusion automatique des licenciés avec numéro > 2000000 (non FFCK)
- Critères de fusion : même Nom, Prénom et Club
- Algorithme intelligent de sélection du meilleur enregistrement basé sur :
  - Numéro ICF valide
  - Date de naissance renseignée
  - Qualification d'arbitre
  - Saison la plus récente
- Préservation des données les plus complètes
- Historique conservé dans les matchs et événements

**Accès** : Administration > Opérations > Fusion automatique de licenciés non fédéraux

**⚠️ Attention** : Opération sensible réservée au profil 1

---

### 📊 Statistiques de cohérence des matchs (Novembre 2025)

**Détection automatique des incohérences de planning**

Détecte 4 types d'anomalies dans la planification :

1. **Arbitrage < 1h après un match joué**
   - Détecte quand une équipe arbitre (principal ou secondaire) moins d'une heure après avoir joué

2. **Match < 1h après un arbitrage**
   - Détecte quand une équipe joue un match moins d'une heure après avoir arbitré

3. **Plus de 6 matchs par jour**
   - Identifie les équipes qui jouent plus de 6 matchs dans la même journée

4. **Plus de 3 matchs sur 4 heures**
   - Repère les équipes qui jouent plus de 3 matchs sur une fenêtre de 4 heures consécutives

**Accès** : Administration > Stats > Cohérence des matchs

**Documentation complète** : [MATCH_CONSISTENCY_STATS.md](MATCH_CONSISTENCY_STATS.md)

---

### 🏆 Classement Multi-Compétitions (Novembre 2025)

**Agrégation de résultats multi-compétitions**
- Nouveau type de compétition "MULTI"
- Consolidation automatique des résultats de plusieurs compétitions
- Calcul unifié des points, victoires, défaites, goal-average
- Gestion des doublons d'équipes
- Export et génération de documents

**Accès** : Administration > Gestion Compétition > Type "Multi-Compétitions"

**Documentation complète** : [MULTI_COMPETITION_TYPE.md](MULTI_COMPETITION_TYPE.md)

---

### 📋 Consolidation des phases dans les Tournois (Novembre 2025)

**Regroupement automatique des phases de classement**
- Agrégation des résultats de toutes les phases de type "Classement"
- Calcul global des points sur l'ensemble du tournoi
- Vue consolidée pour les compétitions multi-phases
- Génération de feuilles de classement global

**Accès** : Administration > Gestion Classement > Compétitions de type "Tournoi"

---

## 📅 Octobre 2025

### ⚙️ Event Cache Manager - Worker en arrière-plan (Octobre 2025)

**Gestion automatisée des caches d'événements**
- Worker PHP en arrière-plan pour la gestion des caches
- Traitement multi-événements en parallèle
- Ne nécessite plus de laisser le navigateur ouvert
- Surveillance et statut en temps réel
- Logs détaillés des opérations

**Accès** : Administration > Opérations > Worker Management > Event Cache Worker

**Avantages** :
- ✅ Traitement asynchrone
- ✅ Pas de timeout navigateur
- ✅ Gestion automatique des erreurs
- ✅ Monitoring en temps réel

---

### 🖼️ Upload et gestion d'images (Octobre 2025)

**Upload centralisé d'images**
- Upload de logos compétition (JPG, 1000x1000)
- Upload de bandeaux compétition (JPG, 2480x250)
- Upload de sponsors compétition (JPG, 2480x250)
- Upload de logos club (PNG, 200x200)
- Upload de logos nation (PNG, 200x200)
- Renommage d'images existantes
- Prévisualisation du nom de fichier avant upload
- Validation automatique des dimensions et formats

**Accès** : Administration > Opérations > Upload d'images

---

### 👥 Copie de composition d'équipe (Octobre 2025)

**Réutilisation des compositions précédentes**
- Copie de la composition d'une même équipe depuis une autre compétition
- Recherche automatique sur les 3 dernières saisons
- Gain de temps pour les compétitions récurrentes
- Conservation de l'ordre et des rôles des joueurs

**Accès** : Administration > Gestion Équipe Joueurs > "Copier la composition"

---

## 📅 Septembre 2025

### 📆 Calendrier annuel (Septembre 2025)

**Visualisation sur l'année du calendrier public des compétitions**
- Visualisation de tous les événements sur l'année
- Navigation par mois et par année
- Accès rapide aux détails d'un événement

**Accès** : Administration > Calendrier

---

### 🕐 Sélecteurs Date et Heure universels (Septembre 2025)

**Interface unifiée pour la saisie de dates et heures**

**Sélecteur de Date** (Flatpickr)
- Calendrier visuel intuitif
- Format français (jj/mm/aaaa) ou anglais (aaaa-mm-jj)
- Navigation rapide par mois/année
- Sélection de plages de dates
- Validation automatique
- Disponible sur tous les champs date du système

**Sélecteur d'Heure** (Flatpickr)
- Interface de sélection d'heure visuelle
- Format 24h
- Incréments configurables (1, 5, 10, 15, 30 minutes)
- Validation automatique
- Disponible sur tous les champs heure du système

**Avantages** :
- ✅ Plus d'erreurs de saisie
- ✅ Interface cohérente partout
- ✅ Gain de temps
- ✅ Mobile-friendly

---

## 📅 Août 2025

### ⚽ Gestion Journée - Opérations de masse sur les matchs (Août 2025)

**Modification groupée de matchs**

**1. Modification de date en masse**
- Sélection multiple de matchs
- Changement de date pour tous les matchs sélectionnés
- Confirmation avant application
- Historique des modifications

**2. Incrémentation d'heure**
- Sélection de matchs sur un même terrain
- Incrémentation de l'heure de +X minutes pour tous les matchs
- Utile pour décaler une série de matchs
- Prévisualisation des nouveaux horaires

**3. Modification de nom de poule**
- Renommage de poule dans tous les encodages existants
- Application automatique sur tous les matchs concernés
- Mise à jour des classements associés

**Accès** : Administration > Gestion Journée > Sélection multiple > Actions de masse

**Cas d'usage** :
- Décalage de planning suite à un retard
- Réorganisation de terrain
- Correction de nom de poule après création

---

### 🎯 Centralisation des opérations d'administration et de maintenance (Août 2025)

**Regroupement des opérations sensibles dans Gestion Opérations**

**Fonctionnalités déplacées vers Gestion Opérations** (profil 1 uniquement) :
- Upload d'images de compétition
- Renommage d'images
- Paramètres avancés de compétition
- Export/Import de données

**Gestion Compétition conserve** :
- Création/modification de compétition
- Affectation d'équipes
- Gestion des poules
- Configuration des phases
- Génération de calendrier

**Avantages** :
- ✅ Interface plus simple pour les utilisateurs standards
- ✅ Réduction des risques d'erreurs
- ✅ Fonctions sensibles protégées (profil 1)

---

## 🔗 Liens vers la documentation détaillée

- **[Inventaire complet des fonctionnalités](KPI_FUNCTIONALITY_INVENTORY.md)** - Liste exhaustive de toutes les fonctionnalités KPI
- **[Statistiques de cohérence des matchs](MATCH_CONSISTENCY_STATS.md)** - Documentation détaillée de la fonctionnalité
- **[Classement Multi-Compétitions](MULTI_COMPETITION_TYPE.md)** - Guide complet du type MULTI
- **[Guide du visualiseur de documentation](DOCVIEWER_GUIDE.md)** - Comment utiliser et enrichir la documentation

---

## 💡 Prochaines fonctionnalités

Les fonctionnalités suivantes sont en cours de développement ou planifiées :

- Migration complète vers API Platform (API v2)
- Amélioration des performances de génération de documents PDF
- Nouveau module de gestion des licences avec synchronisation FFCK
- Interface mobile responsive pour la gestion des matchs
- Notifications en temps réel pour les événements importants

---

## 📝 Historique des versions

- **Version 2024.11** - Documentation intégrée, Fusion automatique licenciés
- **Version 2024.10** - Event Cache Worker, Upload images, Multi-compétitions
- **Version 2024.09** - Calendrier annuel, Sélecteurs Date/Heure
- **Version 2024.08** - Gestion Journée masse, Consolidation tournois

---

**Dernière mise à jour** : 15 décembre 2025
