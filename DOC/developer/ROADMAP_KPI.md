# Roadmap KPI - Prochains Objectifs et Travaux

**Version** : 1.0
**Date** : Décembre 2025
**Statut** : Document de planification

---

## 🎯 Vue d'Ensemble

Ce document présente les prochains objectifs et travaux prévus pour le projet KPI, suite aux travaux de modernisation technique effectués entre octobre et décembre 2025.

**Contexte actuel** :
- ✅ Migration PHP 8.4 terminée
- ✅ Unification Bootstrap 5.3.8 terminée
- ✅ 7 nouvelles fonctionnalités déployées
- ✅ App2 (Nuxt 4) en production
- ✅ Feuille de match intégrée dans App2 (Décembre 2025)

**Prochaines étapes** : Consolidation, migration progressive vers une architecture moderne complète.

---

## 📋 Court Terme (Saison 2026)

### 1. Migration JWT pour App2

**Objectif** : Moderniser l'authentification App2 ↔ API2 avec JWT pour améliorer la sécurité.

**Contexte actuel** :
- App2 utilise un système de token simple (32 caractères hexadécimaux)
- Pas d'expiration automatique des tokens
- API2 dispose déjà de l'infrastructure JWT (lexik/jwt-authentication-bundle)
- App4 (admin) utilise déjà JWT avec succès

**Migration prévue** :
- Remplacer le token simple par JWT (JSON Web Token)
- Implémenter l'expiration automatique (TTL : 24h recommandé)
- Ajouter refresh token pour sessions longues
- Maintenir les fonctionnalités publiques (home, games, charts, team, about) sans authentification

**Bénéfices** :
- **Sécurité renforcée** : Expiration automatique, signature cryptographique, impossibilité de falsification
- **Performance** : Validation stateless sans requête DB (JWT contient user_id, profile, events, roles)
- **Standardisation** : Uniformisation avec App4, standard RFC 7519
- **Scalabilité** : Zéro charge DB pour valider chaque requête
- **Fonctionnalités avancées** : Auto-déconnexion après inactivité, sessions multiples, audit trail

**Architecture** :
```
📁 App2 Routes
├── 🌍 PUBLIQUES (sans JWT)
│   ├── / (home)
│   ├── /about
│   ├── /games (liste des matchs)
│   ├── /game/[id] (détail d'un match)
│   ├── /charts (graphiques)
│   ├── /team/[team] (équipe publique)
│   └── /login
│
└── 🔒 PROTÉGÉES (JWT requis)
    └── /scrutineering (gestion d'équipe, profile <= 3)
```

**Plan de migration** :
1. Phase 1 : Ajouter endpoint JWT dans API2 pour App2 (réutiliser infrastructure existante)
2. Phase 2 : Modifier App2 pour utiliser JWT au lieu du token simple
3. Phase 3 : Période de transition avec double système (token legacy + JWT)
4. Phase 4 : Dépréciation et suppression de l'ancien système

**Statut** : 📋 Planifié
**Priorité** : Haute (sécurité)
**Durée estimée** : 1-2 semaines

---

### 2. Déploiement et Stabilisation

**Objectif** : Assurer le bon fonctionnement de toutes les nouvelles fonctionnalités en production.

**Actions** :
- Tests utilisateurs pendant les compétitions
- Corrections de bugs au fil de l'eau
- Collecte de retours utilisateurs
- Optimisations de performance si nécessaire

**Durée estimée** : Tout au long de la saison 2026

---

### 3. Communication et Adoption

**Objectif** : Faire connaître les nouvelles fonctionnalités aux utilisateurs.

**Actions** :
- Communication progressive sur les nouvelles fonctionnalités
- Guides d'utilisation diffusés aux organisateurs
- Support et accompagnement des utilisateurs
- Recueil de feedbacks

**Durée estimée** : Tout au long de la saison 2026

---

### 4. Feuille de Match Numérique ✅

**Statut** : ✅ Implémenté (Décembre 2025)

**Fonctionnalité réalisée** :
- ✅ Accès via l'App mobile (app.kayak-polo.info)
- ✅ Page dédiée `/game/[id]` avec feuille de match complète
- ✅ Consultation des compositions, scores, événements avec timeline visuelle
- ✅ Export PDF disponible
- ✅ Rafraîchissement en temps réel
- ✅ Statuts enrichis (EN COURS avec période, TERMINÉ, Provisoire)
- ✅ Navigation vers fiches équipes

**Prochaines améliorations possibles** :
- Restriction d'accès (uniquement matchs verrouillés) si souhaité
- Notifications push pour les matchs suivis
- Signature numérique des capitaines/arbitres

---

## 🔄 Moyen Terme (2026-2027)

### 1. Migration WebSocket Manager vers Nuxt 4

**Objectif** : Moderniser l'application de gestion des scores en direct.

**Contexte actuel** :
- Application actuelle : Vue 3 (app_wsm_dev)
- Utilisée pour la saisie des scores en temps réel sur le terrain

**Migration prévue** :
- Refonte complète en Nuxt 4
- Amélioration de l'interface de saisie
- Meilleure synchronisation en temps réel
- Optimisation mobile (tablettes sur le bord terrain)

**Durée estimée** : 2 mois

---

### 2. Refonte de l'Admin en Nuxt 4 + API

**Objectif** : Moderniser complètement l'interface d'administration.

**Contexte actuel** :
- Interface admin actuelle : Smarty (PHP templates)
- 88 templates Smarty actifs
- Interface fonctionnelle mais ancienne

**Migration prévue** :
- Refonte complète en Nuxt 4
- Création d'une API REST moderne (Symfony/API Platform)
- Interface administrative moderne et responsive
- Meilleure expérience utilisateur
- Composants réutilisables

**Bénéfices** :
- Interface moderne et cohérente avec App2
- Maintenance facilitée
- Évolutions plus rapides
- Meilleure performance

**Durée estimée** : 4 à 6 mois

---

### 3. Refonte du Live Overlay en Nuxt 4

**Objectif** : Moderniser les incrustations vidéo pour les lives.

**Contexte actuel** :
- Application actuelle : Vue 3 (app_live_dev)
- Incrustations pour streaming (OBS, YouTube, Twitch)

**Migration prévue** :
- Refonte en Nuxt 4
- Nouvelles incrustations plus modernes
- Animations améliorées
- Personnalisation facilitée

**Durée estimée** : 2 mois

---

### 4. Refonte de la Navigation Publique en Nuxt 4 + API

**Objectif** : Moderniser le site public (kayak-polo.info).

**Contexte actuel** :
- Site public actuel : Mix PHP/Smarty
- Consultation des résultats, classements, calendriers

**Migration prévue** :
- Refonte complète en Nuxt 4
- Utilisation de l'API moderne
- Interface cohérente avec App2
- Amélioration de la navigation
- SEO optimisé

**Bénéfices** :
- Expérience utilisateur unifiée
- Performance améliorée
- Maintenance facilitée
- Évolutions plus rapides

**Durée estimée** : 4 à 6 mois

---

## 🚀 Long Terme (2027+)

### 1. Application Mobile Native

**Objectif** : Développer une vraie application mobile (iOS/Android).

**Fonctionnalités** :
- Consultation hors ligne complète
- Notifications push pour matchs favoris
- Géolocalisation (trouver les matchs à proximité)
- Mode sombre natif
- Performances optimisées

**Technologies envisagées** :
- React Native
- Flutter
- Ou Progressive Web App (PWA) avancée

**Durée estimée** : 3 à 4 mois

---

### 2. Améliorations Continues

**Objectif** : Améliorer continuellement selon les retours utilisateurs.

**Exemples d'améliorations** :
- Nouvelles statistiques avancées
- Comparaison d'équipes
- Historique multi-saisons
- Graphiques et visualisations
- Exports personnalisés
- Intégrations tierces (réseaux sociaux, etc.)

**Processus** :
- Collecte régulière de feedbacks
- Priorisation des demandes
- Itérations courtes (sprints de 2-4 semaines)

---

## 📊 Architecture Cible

### Vision à Long Terme

**Backend** :
- API REST moderne (Symfony + API Platform)
- Base de données optimisée
- Cache performant (Redis)
- Files d'attente (RabbitMQ ou équivalent)

**Frontend** :
- Toutes les apps en Nuxt 4
- Design system unifié
- Composants partagés
- Tests automatisés

**Infrastructure** :
- CI/CD complet (GitHub Actions)
- Monitoring avancé (Grafana, Prometheus)
- Logs centralisés
- Sauvegardes automatiques

---

## ⚙️ Méthodologie

### Approche Progressive

**Principe** : Migration progressive sans interruption de service (Strangler Fig Pattern).

**Stratégie** :
1. **API First** : Créer l'API moderne en parallèle de l'ancienne
2. **Migration par modules** : Migrer une fonctionnalité à la fois
3. **Tests continus** : Validation à chaque étape
4. **Rollback possible** : Possibilité de revenir en arrière si problème

### Planification

**Sprints courts** :
- Itérations de 2-4 semaines
- Objectifs clairs et mesurables
- Revues régulières

**Priorisation** :
- Fonctionnalités critiques en premier
- Impact utilisateur maximal
- Risques techniques évalués

---

## 🎯 Objectifs de Qualité

### Performance
- Temps de chargement < 2 secondes
- Réactivité interface < 100ms
- Optimisation mobile (3G/4G)

### Fiabilité
- Disponibilité > 99.5%
- Sauvegarde quotidienne automatique
- Plan de reprise d'activité (PRA)

### Sécurité
- Authentification renforcée
- HTTPS partout
- Protection contre les attaques courantes (XSS, CSRF, SQL injection)
- Mises à jour de sécurité régulières

### Maintenabilité
- Code documenté
- Tests automatisés (unitaires, intégration, E2E)
- Standards de code respectés
- Revues de code systématiques

---

## 📅 Timeline Indicative

| Période | Travaux Principaux |
|---------|-------------------|
| **2026 Q1** | Migration JWT App2, stabilisation |
| **2026 Q2-Q4** | Stabilisation, feuille de match numérique |
| **2026 Q4 - 2027 Q1** | Migration WebSocket Manager |
| **2027 Q1-Q2** | Refonte Admin (partie 1) |
| **2027 Q2-Q3** | Refonte Admin (partie 2) |
| **2027 Q3** | Refonte Live Overlay |
| **2027 Q4 - 2028 Q1** | Refonte Navigation Publique |
| **2028+** | Application mobile native, améliorations continues |

**Note** : Ces dates sont indicatives et peuvent évoluer selon les priorités et ressources disponibles.

---

## 💰 Ressources Nécessaires

### Développement
- 1-2 développeurs full-stack (Vue.js/Nuxt, PHP/Symfony)
- Temps partiel ou full-time selon les phases

### Infrastructure
- Serveur de production (~50-100€/mois)
- Serveur de staging/preprod (~30-50€/mois)
- Services tiers (monitoring, CI/CD) : Gratuit ou ~20€/mois

### Autres
- Tests utilisateurs (temps organisateurs/bénévoles)
- Documentation (temps rédaction)

---

## 📝 Notes et Considérations

### Points d'Attention

**Compatibilité** :
- Maintenir la compatibilité avec l'existant pendant la migration
- Support multi-navigateurs
- Responsive design obligatoire

**Documentation** :
- Documenter au fur et à mesure
- Guides utilisateur pour chaque nouvelle fonctionnalité
- Documentation technique pour les développeurs

**Formation** :
- Accompagnement des utilisateurs lors des changements
- Tutoriels et vidéos si nécessaire

### Risques Identifiés

**Techniques** :
- Complexité de la migration (backend legacy)
- Temps de développement sous-estimé
- Bugs de régression

**Organisationnels** :
- Disponibilité des développeurs
- Budget limité
- Résistance au changement des utilisateurs

**Atténuation** :
- Planification réaliste
- Tests réguliers
- Communication transparente
- Plan B pour chaque fonctionnalité critique

---

## 🎉 Vision Finale

**Horizon 2028** : Une plateforme moderne, performante et complète pour la gestion des compétitions de kayak-polo.

**Architecture** :
- Backend : API REST moderne (Symfony + API Platform)
- Frontend : Nuxt 4 partout (admin, public, apps)
- Mobile : Application native iOS/Android
- Infrastructure : Automatisée et monitorée

**Bénéfices** :
- Expérience utilisateur exceptionnelle
- Maintenance facilitée
- Évolutions rapides
- Pérennité garantie pour 5-10 ans

---

**Auteur** : Laurent Garrigue
**Date** : 27 décembre 2025
**Mise à jour** : À compléter au fil des avancées

**Note** : Ce document est un plan initial qui sera détaillé et affiné au fil du temps.
