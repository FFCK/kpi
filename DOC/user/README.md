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

### Inventaire des Fonctionnalités

- **[KPI_FUNCTIONALITY_INVENTORY.md](KPI_FUNCTIONALITY_INVENTORY.md)** - **Inventaire complet des fonctionnalités**
  - Toutes les fonctionnalités du système KPI
  - Description détaillée de chaque module
  - Permissions et rôles utilisateurs
  - ~7000 lignes de documentation
  - **Document de référence principal pour comprendre le système**

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
2. **Découvrir le système** : Commencez par [KPI_FUNCTIONALITY_INVENTORY.md](KPI_FUNCTIONALITY_INVENTORY.md)
3. **Utiliser les statistiques de cohérence** : Consultez [MATCH_CONSISTENCY_STATS.md](MATCH_CONSISTENCY_STATS.md)

---

## 📚 Documentation Développeur

Pour la documentation technique, guides de migration, corrections et audits, consultez la [Documentation Développeur](../developer/).

---

**Note** : Cette documentation est volontairement concise et orientée utilisateurs. Pour les détails techniques d'implémentation, voir la documentation développeur.
