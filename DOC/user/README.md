# Documentation Utilisateur

Cette section contient la documentation orientée utilisateurs finaux du système KPI.

## 📋 Guides et Fonctionnalités

### 🎉 Nouveautés

- **[NOUVEAUTES.md](NOUVEAUTES.md)** - **Dernières fonctionnalités ajoutées**
  - Historique des nouveautés par date
  - Fonctionnalités récentes (Novembre 2025)
  - Documentation intégrée, Fusion licenciés, Stats cohérence matchs
  - Classement Multi-Compétitions, Event Cache Worker
  - Guide complet des améliorations récentes

### Fonctionnalités Principales

- **[EVENT_CACHE_MANAGER.md](EVENT_CACHE_MANAGER.md)** - **Event Cache Manager - Worker en arrière-plan**
  - Génération automatique des caches d'événements pour les incrustations vidéo
  - Fonctionnement 24/7 sans navigateur ouvert
  - Interface de contrôle et monitoring en temps réel
  - Guide d'utilisation pour organisateurs de tournois
  - **Statut**: ✅ En production

- **[IMAGE_UPLOAD_MANAGEMENT.md](IMAGE_UPLOAD_MANAGEMENT.md)** - **Upload et gestion d'images**
  - Téléchargement de logos de compétitions
  - Gestion des photos de joueurs
  - Formats supportés, bonnes pratiques et optimisation
  - Dépannage et conseils pratiques
  - **Statut**: ✅ En production

- **[TEAM_COMPOSITION_COPY.md](TEAM_COMPOSITION_COPY.md)** - **Copie de composition d'équipe**
  - Duplication rapide de la liste de joueurs entre équipes
  - Gain de temps pour les équipes multiples
  - Cas d'usage et workflow optimisé
  - Restrictions et sécurité
  - **Statut**: ✅ En production

- **[MATCH_DAY_BULK_OPERATIONS.md](MATCH_DAY_BULK_OPERATIONS.md)** - **Gestion Journée - Opérations de masse**
  - Actions groupées sur plusieurs matchs (publication, validation, suppression)
  - Gain de temps considérable pour les organisateurs
  - Guide détaillé des opérations disponibles
  - Droits d'accès et sécurité
  - **Statut**: ✅ En production

- **[MULTI_COMPETITION_TYPE.md](MULTI_COMPETITION_TYPE.md)** - **Type de compétition Multiple**
  - Gestion des compétitions avec plusieurs types
  - Configuration et utilisation
  - **Statut**: ✅ En production

### Fonctionnalités Spécifiques

- **[CONSOLIDATION_PHASES_CLASSEMENT.md](CONSOLIDATION_PHASES_CLASSEMENT.md)** - **Consolidation des phases de classement**
  - Permet de "figer" le classement de certaines phases dans les compétitions CP (Coupe)
  - Empêche le recalcul automatique des phases consolidées
  - Utile pour préserver les classements finalisés ou ajustés manuellement
  - Accessible via GestionClassement pour les administrateurs (profile ≤ 4)
  - Guide d'utilisation, cas d'usage et bonnes pratiques
  - **Statut**: ✅ En production

- **[MATCH_CONSISTENCY_STATS.md](MATCH_CONSISTENCY_STATS.md)** - **Statistiques de cohérence des matchs**
  - Détection automatique d'incohérences dans les plannings
  - 4 types d'incohérences détectées :
    - Arbitrage/match < 1h après événement
    - Surcharge journalière (>6 matchs)
    - Surcharge intensive (>3 matchs/4h)
    - Conflits de planning
  - Guide d'utilisation et interprétation
  - Disponible dans GestionStats
  - **Statut**: ✅ En production

### Administration Système

### Outils et Guides

- **[DOCVIEWER_GUIDE.md](DOCVIEWER_GUIDE.md)** - **Guide du visualiseur de documentation**
  - Accès et utilisation du visualiseur de documentation
  - Comment ajouter de nouvelles documentations
  - Format Markdown recommandé
  - Organisation et bonnes pratiques
  - Dépannage
  - **Statut**: ✅ En production

---

## 🎯 Par où commencer ?

1. **Découvrir les nouveautés** : Consultez [NOUVEAUTES.md](NOUVEAUTES.md)
2. **Comprendre le système complet** : Consultez l'[Inventaire des Fonctionnalités](../developer/reference/KPI_FUNCTIONALITY_INVENTORY.md) (documentation développeur)
3. **Fonctionnalités clés** :
   - **Organisateurs de tournois** : [Event Cache Manager](EVENT_CACHE_MANAGER.md), [Opérations de masse](MATCH_DAY_BULK_OPERATIONS.md)
   - **Gestionnaires d'équipes** : [Copie de composition](TEAM_COMPOSITION_COPY.md), [Upload d'images](IMAGE_UPLOAD_MANAGEMENT.md)
   - **Analystes** : [Statistiques de cohérence](MATCH_CONSISTENCY_STATS.md)

---

## 📚 Documentation Développeur

Pour la documentation technique, guides de migration, corrections et audits, consultez la [Documentation Développeur](../developer/).

---

**Note** : Cette documentation est volontairement concise et orientée utilisateurs. Pour les détails techniques d'implémentation, voir la documentation développeur.
