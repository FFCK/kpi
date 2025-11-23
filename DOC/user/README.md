# Documentation Utilisateur

Cette section contient la documentation orientée utilisateurs finaux du système KPI.

## 📋 Guides et Fonctionnalités

### Inventaire des Fonctionnalités

- **[KPI_FUNCTIONALITY_INVENTORY.md](KPI_FUNCTIONALITY_INVENTORY.md)** - **Inventaire complet des fonctionnalités**
  - Toutes les fonctionnalités du système KPI
  - Description détaillée de chaque module
  - Permissions et rôles utilisateurs
  - ~7000 lignes de documentation
  - **Document de référence principal pour comprendre le système**

### Fonctionnalités Spécifiques

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

- **[CRON_DOCUMENTATION.md](CRON_DOCUMENTATION.md)** - **Tâches planifiées automatiques**
  - Documentation des tâches cron configurées
  - Mise à jour automatique des licences
  - Verrouillage des feuilles de présence
  - Planification et maintenance
  - Configuration et dépannage

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

1. **Découvrir le système** : Commencez par [KPI_FUNCTIONALITY_INVENTORY.md](KPI_FUNCTIONALITY_INVENTORY.md)
2. **Utiliser les statistiques de cohérence** : Consultez [MATCH_CONSISTENCY_STATS.md](MATCH_CONSISTENCY_STATS.md)
3. **Comprendre l'automatisation** : Lisez [CRON_DOCUMENTATION.md](CRON_DOCUMENTATION.md)

---

## 📚 Documentation Développeur

Pour la documentation technique, guides de migration, corrections et audits, consultez la [Documentation Développeur](../developer/).

---

**Note** : Cette documentation est volontairement concise et orientée utilisateurs. Pour les détails techniques d'implémentation, voir la documentation développeur.
