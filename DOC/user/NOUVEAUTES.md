# 🎉 Nouveautés KPI

Ce document liste les dernières fonctionnalités et améliorations ajoutées au système KPI, triées par date de mise en production.

---

## 📅 Mai 2026

### 📊 Statistiques de surclassements (App4) (16/05/2026)

**Nouveau type d'export dans les statistiques de compétition**

Un nouvel onglet **"Surclassements"** est disponible dans la page Statistiques (Administration > Documents > Statistiques) :

- Liste de tous les surclassements valides à partir de la saison sélectionnée
- Pour chaque joueur : catégorie de la saison, catégorie de surclassement, saison et date d'attribution
- Export identique aux autres types de statistiques (Excel/ODS)

Ce nouvel export facilite le contrôle des autorisations de surclassement lors des compétitions nationales.

**Accès** : Administration > Documents > Statistiques > Onglet "Surclassements"

---

### 🔑 Réinitialisation de mot de passe par email (App4) (14/05/2026)

**Envoi d'un email de réinitialisation depuis la fiche utilisateur**

Les administrateurs peuvent désormais déclencher l'envoi d'un email de réinitialisation de mot de passe directement depuis la fiche d'un utilisateur :

- Bouton "Envoyer un lien de réinitialisation" dans le formulaire d'édition
- L'utilisateur reçoit un lien temporaire par email pour définir un nouveau mot de passe
- Le lien est valide pour une durée limitée (token 64 caractères)
- Aucune copie du mot de passe n'est nécessaire pour l'administrateur

> Nécessite que l'adresse email soit renseignée dans la fiche utilisateur.

**Accès** : Administration > Utilisateurs > Modifier un utilisateur > Bouton "Envoyer un lien de réinitialisation"

---

### 👥 Gestion des utilisateurs — nouvelles règles de droits (App4)

**Sécurisation et clarification des permissions sur la page Utilisateurs**

#### Création d'utilisateurs

Les responsables de division (profil 3) peuvent désormais créer des utilisateurs de profil 4 (Resp. Poule/Compétition), en plus des profils 5 à 10 déjà accessibles.

#### Modification des utilisateurs — mode "mandats uniquement" pour les profils 3 et 4

Les profils 3 (Resp. Division) et 4 (Resp. Poule/Compétition) ne peuvent plus modifier les champs de base d'un utilisateur (profil, saisons autorisées, compétitions, clubs). Lorsqu'ils ouvrent la fiche d'un utilisateur, seule la section **Mandats** est disponible :

- **Ajouter un mandat** à un utilisateur existant
- **Supprimer un mandat** existant

Un bandeau d'information dans le formulaire indique clairement cette restriction.

Cette évolution élimine le risque qu'un administrateur attribue à un utilisateur des droits plus larges que les siens (ex. accès à toutes les saisons ou à des compétitions hors de son périmètre).

#### Ajout d'une saison en masse sur les mandats existants

Dans la vue **Mandats** (mode "Vue mandats"), après avoir sélectionné des périmètres d'accès, le bouton **"Ajouter une saison"** permet d'ajouter une saison à plusieurs mandats ou profils de base en une seule opération.

> Cette action est limitée aux saisons faisant partie du périmètre de l'administrateur connecté.

---

## 📅 Avril 2026

### 🗺️ Guide de navigation illustré sur la page d'accueil (App2) (01/04/2026)

**Aide visuelle pour découvrir les fonctionnalités de l'application**

Un guide illustré apparaît sur la page d'accueil après la sélection d'un événement ou d'une compétition :

- Capture d'écran annotée (adaptée PC ou smartphone) montrant les zones cliquables
- **À gauche** : cliquer sur un nom d'équipe pour accéder à sa fiche détaillée (matchs, résultats, progression)
- **À droite** : cliquer sur un score ou un numéro de match pour ouvrir la feuille de match (composition, buts, événements)
- Marquée "Exemple illustratif" pour éviter toute confusion avec de vrais matchs

### 🔗 Numéro de match cliquable dans la liste des matchs (App2) (01/04/2026)

**Navigation directe vers la feuille de match depuis le numéro de match**

- Le numéro de match (`#345`) est désormais cliquable dans la liste des matchs (`/games`) et dans la fiche équipe (`/team`)
- Comportement identique au clic sur le score : ouvre la feuille de match détaillée
- Disponible uniquement pour les matchs en cours ou terminés (comme pour le score)

---

### 🔍 Recherche avancée des licenciés (App4) (26/04/2026)

**Filtres enrichis sur la page Licenciés**

La page de gestion des licenciés (Administration > Licenciés) propose maintenant des filtres avancés :

- Filtrage par **club** (liste déroulante avec recherche)
- Filtrage par **comité départemental** ou **comité régional**
- Filtrage par **catégorie d'âge**
- Combinaison possible de plusieurs filtres simultanément
- Compteur de résultats mis à jour en temps réel

Ces améliorations permettent de retrouver rapidement un athlète dans une base nationale de plusieurs milliers de licenciés.

**Accès** : Administration > Licenciés > Filtres avancés

---

### ⚙️ Event Cache Manager intégré à App4 (26/04/2026)

**Gestion du worker de cache directement depuis l'application d'administration**

Le gestionnaire de cache d'événements (worker PHP) est désormais accessible depuis App4 :

- Démarrage/arrêt/pause du worker depuis l'interface
- Visualisation du statut en temps réel
- Configuration : décalage de démarrage, nombre de terrains, intervalle de rafraîchissement
- Historique des exécutions et messages d'erreur

**Accès** : Administration > Événements > Gestionnaire de cache

---

## 📅 Mars 2026

### 🏐 Vérification de la composition d'équipe (Administration) (27/02/2026)

**Contrôle des joueurs inscrits avant compétition**

Un nouveau rapport de vérification permet de contrôler la composition des équipes inscrites :

- Vérification de la validité des licences (licence à jour pour la saison)
- Contrôle des certificats médicaux
- Validation de la pagaie couleur
- Contrôle des surclassements requis
- Export PDF ou ODS pour chaque équipe

Le rapport distingue les anomalies bloquantes des avertissements non bloquants.

**Accès** : Administration > Documents > Statistiques > Vérification des équipes

---

### 🔗 Association Événements / Compétitions avec contexte de travail (App4) (27/02/2026)

**Navigation contextuelle dans l'administration**

App4 propose désormais un **sélecteur de contexte de travail** persistent dans toutes les pages :

- Sélection d'un événement ET/OU d'une compétition active
- Toutes les pages (journées, matchs, classements, documents) filtrent automatiquement selon ce contexte
- Le contexte est mémorisé entre les pages pendant la session
- Sélecteur affiché dans l'en-tête de toutes les pages d'administration

Cela remplace la sélection individuelle sur chaque page, réduisant le nombre de clics pour les opérations répétitives lors d'un événement.

**Accès** : En-tête App4 > Sélecteur d'événement / Compétition

---

## 📅 Janvier 2026

### 🏆 Sélection par Compétition/Groupe (App2) (18/01/2026)

**Nouveau mode de navigation dans l'application web**

L'application web KPI propose maintenant deux modes de sélection :

**Mode "Compétitions"** (nouveau, par défaut) :
- Navigation par catégorie (N1H, N1F, N2H, etc.)
- Sélection de la saison souhaitée
- Accès direct à tous les matchs d'une catégorie sur la saison
- URL de partage directe : `app.kayak-polo.info/group/{saison}/{code}`
- Exemple : `app.kayak-polo.info/group/2026/N1H` pour la Nationale 1 Hommes 2026

**Mode "Événements"** (existant) :
- Navigation par événement ponctuel (tournoi, journée de championnat)
- Sélection d'un événement spécifique
- Accès aux matchs de cet événement uniquement

**Avantages du mode Compétitions** :
- ✅ Vue globale sur toute la saison
- ✅ Suivi de l'évolution du classement
- ✅ Partage facile d'un lien vers une catégorie
- ✅ Idéal pour suivre un championnat complet

**Accès** : [app.kayak-polo.info](https://app.kayak-polo.info) > Boutons "Compétitions" / "Événements"

---

### 🌐 Traduction des Groupes/Catégories (18/01/2026)

**Support multilingue pour les noms de catégories**

- Nouveau champ "Libellé anglais" dans la gestion des groupes (admin)
- Affichage automatique en anglais dans App2 quand l'utilisateur choisit l'anglais
- Exemple : "Nationale 1 Hommes" → "National 1 Men"

**Accès** : Administration > Gestion Groupe > Modifier > Champ "Libellé EN"

---

### 🔒 Sécurisation du changement de code Groupe (18/01/2026)

**Protection contre les modifications accidentelles de codes**

- Confirmation obligatoire avant toute modification du code d'un groupe
- Message d'avertissement explicite indiquant l'impact sur les compétitions
- Mise à jour automatique des compétitions référençant le groupe modifié
- Rollback automatique en cas d'erreur

**Impact** : Toutes les compétitions utilisant l'ancien code sont automatiquement mises à jour avec le nouveau code.

**Accès** : Administration > Gestion Groupe > Modifier > Champ "Code"

⚠️ **Attention** : Opération sensible - vérifiez bien le nouveau code avant de confirmer

---

## 📅 Décembre 2025

### 📱 Application Web KPI (App2) - Nouvelle version (Décembre 2025)

**Application web moderne pour tous vos appareils**

**Interface modernisée** :
- Interface Nuxt 4 avec navigation intuitive
- Consultation rapide : matchs, résultats, classements
- Filtres avancés : catégories, dates, équipes, arbitres
- **QR Code de partage** : Partagez facilement l'événement (copie de lien au clic)

**Feuille de Match Intégrée (Nouveau !)** :
- **Consultation détaillée** de chaque match directement dans l'app
- **En-tête complet** : compétition, phase, numéro, terrain, date/heure, arbitres
- **Score en temps réel** : affichage LCD stylisé, mi-temps, logos d'équipes
- **Statuts enrichis** :
  - "EN COURS - 1ère période" / "EN COURS - 2ème période" pour les matchs en direct
  - "TERMINÉ (Provisoire)" pour les matchs non validés
- **Compositions d'équipes** : joueurs, numéros, capitaines, coach
- **Statistiques individuelles** : buts, cartons verts/jaunes/rouges par joueur
- **Chronologie visuelle** : timeline des événements avec horodatage et période
- **Actions** : rafraîchissement en direct, téléchargement PDF, navigation vers fiches équipes

**Fiches équipes détaillées** :
  - Matchs précédents et prochains matchs
  - Progression dans la compétition
  - Position au classement
  - Statistiques des joueurs

**Contrôle du matériel** (utilisateurs habilités) :
  - Validation des kayaks, casques, gilets, pagaies, équipements
  - Commentaires et historique des contrôles

**Mode Hors Ligne (Nouveau !)** :
- Détection automatique de connectivité
- Badge orange et notifications lors de perte/récupération de connexion
- Vérification automatique de nouvelle version disponible
- Accès aux dernières données consultées sans connexion

**Responsive** : fonctionne sur mobile, tablette et ordinateur

**Accès** : [app.kayak-polo.info](https://app.kayak-polo.info)

**Documentation complète** : [APP2_APPLICATION_WEB.md](APP2_APPLICATION_WEB.md)

---

### 🔒 Verrouillage de Phases de Classement (Décembre 2025)

**Consolidation et gel des classements de phases terminées**
- Verrouillage définitif d'une phase de classement terminée (ex: phase de poules)
- Évite toute modification accidentelle des classements publiés
- Garantit la cohérence lors du passage aux phases finales
- Protège l'historique des classements

**Accès** : Administration > Gestion Classement > Consolidation Phases

**⚠️ Attention** : Opération sensible - vérifiez que la phase est bien terminée avant consolidation

**Documentation complète** : [CONSOLIDATION_PHASES_CLASSEMENT.md](CONSOLIDATION_PHASES_CLASSEMENT.md)

---

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
- **[Application Web KPI (App2)](APP2_APPLICATION_WEB.md)** - Guide complet de l'application web moderne
- **[Roadmap KPI](../developer/guides/ROADMAP_KPI.md)** - Prochains objectifs et travaux planifiés (2026-2028)

---

## 💡 Prochaines fonctionnalités

Pour connaître les prochains objectifs et travaux planifiés, consultez la **[Roadmap KPI](../developer/guides/ROADMAP_KPI.md)**.

**Court terme (2026)** :
- Stabilisation et déploiement des nouvelles fonctionnalités
- Feuille de match numérique accessible via App2

**Moyen terme (2026-2027)** :
- Migration WebSocket Manager vers Nuxt 4
- Refonte de l'Admin en Nuxt 4 + API moderne
- Refonte du Live Overlay en Nuxt 4
- Refonte de la navigation publique en Nuxt 4

**Long terme (2027+)** :
- Application mobile native (iOS/Android)
- Améliorations continues selon retours utilisateurs

---

## 📝 Historique des versions

- **Version 2026.05** - Stats surclassements, Réinitialisation pwd par email, Droits mandats
- **Version 2026.04** - Recherche licenciés avancée, Event Cache Manager App4, Guide App2
- **Version 2026.03** - Vérification composition équipe, Contexte de travail App4
- **Version 2026.01** - Sélection par compétition/groupe, Traduction des groupes
- **Version 2025.12** - App2 v2.0, Verrouillage phases, Copie compétitions en masse
- **Version 2025.11** - Documentation intégrée, Fusion automatique licenciés
- **Version 2025.10** - Event Cache Worker, Upload images, Multi-compétitions
- **Version 2025.09** - Calendrier annuel, Sélecteurs Date/Heure
- **Version 2025.08** - Gestion Journée masse, Consolidation tournois

---

**Dernière mise à jour** : 16 mai 2026
