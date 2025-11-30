# KPI - Système de gestion sportive

KPI est un système de gestion pour les compétitions de kayak-polo, gérant les équipes, matchs, joueurs et statistiques.

## 🏗️ Architecture

Le projet combine plusieurs technologies :
- **Backend PHP** : ✅ PHP 8.4 (migration depuis PHP 7.4 terminée en novembre 2025) avec API REST
- **Bibliothèques PHP modernes** : mPDF v8.2+, OpenSpout v4.32.0, Smarty v4
- **Frontend moderne** : Nuxt 4 (Vue 3 + TypeScript + Tailwind CSS) dans `sources/app2/`
- **Applications legacy** : Vue.js dans `sources/app_dev/`, `app_live_dev/`, `app_wsm_dev/`
- **CMS** : WordPress pour le contenu éditorial (compatible PHP 8.4)
- **Infrastructure** : Docker Compose avec Traefik pour le reverse proxy
- **Base de données** : MySQL/MariaDB 10.4 (2 bases : KPI + WordPress)

## 🚀 Quick Start

### Prérequis
- Docker et Docker Compose
- Make
- Git

### Installation initiale

1. **Cloner le repository**
```bash
git clone https://github.com/FFCK/kpi.git
cd kpi
```

2. **Initialisation complète**
```bash
make init
```
Cette commande va :
- Créer le fichier `docker/.env` depuis `docker/.env.dist`
- Créer les fichiers `.env.development` et `.env.production` pour app2
- Créer les réseaux Docker nécessaires (`network_kpi`, `pma_network`, `traefiknetwork`)

3. **Configurer les variables d'environnement**

Éditer `docker/.env` et configurer :
```bash
APPLICATION_NAME=kpi              # Nom de l'application (change selon l'environnement)
USER_ID=1000
GROUP_ID=33
DB_ROOT_PASSWORD=votre_mot_de_passe
DB_USER=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
# ... etc
```

**Important** : Le `APPLICATION_NAME` définit le nom du réseau Docker (`network_${APPLICATION_NAME}`).
- **Développement local** : `APPLICATION_NAME=kpi` → réseau `network_kpi`
- **Pré-production** : `APPLICATION_NAME=kpi_preprod` → réseau `network_kpi_preprod`
- **Production** : `APPLICATION_NAME=kpi` ou `kpi_prod` selon votre configuration

4. **Démarrer l'environnement de développement**
```bash
make dev_up
```

5. **Installer les dépendances Node**
```bash
make npm_install_app2
```

6. **Lancer le serveur Nuxt**
```bash
make run_dev
```

L'application est accessible sur :
- **KPI** : http://kpi.localhost ou http://localhost:8003
- **App2 (Nuxt)** : http://localhost:3002
- **phpMyAdmin** : http://kpi-myadmin.localhost

### Configuration des hôtes locaux

Ajouter à `/etc/hosts` :
```
127.0.0.1 kpi.localhost kpi-8.localhost kpi-myadmin.localhost kpi-node.localhost
```

## 📋 Commandes Make principales

Utilisez `make help` pour voir toutes les commandes disponibles.

### Initialisation
```bash
make init              # Initialisation complète (recommandé)
make init_env          # Créer docker/.env
make init_env_app2     # Créer les .env de app2
make init_networks     # Créer les réseaux Docker
```

### Développement
```bash
make dev_up            # Démarrer les containers
make dev_down          # Arrêter les containers
make dev_restart       # Redémarrer les containers
make dev_logs          # Voir les logs en temps réel
make dev_status        # Statut des containers
```

### Pré-production
```bash
make preprod_up        # Démarrer la pré-production
make preprod_down      # Arrêter la pré-production
make preprod_logs      # Voir les logs
```

### Production
```bash
make prod_up           # Démarrer la production
make prod_down         # Arrêter la production
make prod_logs         # Voir les logs
```

### Nuxt (App2)
```bash
make run_dev           # Serveur dev Nuxt (port 3002)
make run_build         # Build pour production
make run_generate      # Génération statique
make run_lint          # Linter ESLint
```

### NPM
```bash
make npm_install_app2                    # Installer les dépendances
make npm_clean_app2                      # Nettoyer node_modules
make npm_add_app2 package=nom-package    # Ajouter un package
```

### Accès aux containers
```bash
make php_bash          # Shell PHP 8.4
make node_bash         # Shell Node/App2
make db_bash           # Shell MySQL
```

### Réseaux Docker
```bash
make networks_create   # Créer les réseaux nécessaires (utilise APPLICATION_NAME)
make networks_list     # Lister les réseaux du projet
make networks_clean    # Supprimer les réseaux (si non utilisés)
```

**Note** : Les réseaux sont créés en fonction de `APPLICATION_NAME` dans `docker/.env` :
- Réseau KPI : `network_${APPLICATION_NAME}`
- Réseau phpMyAdmin : `pma_network` (partagé)
- Réseau Traefik : `traefiknetwork` (partagé)

### WordPress
```bash
make wordpress_backup  # Créer une sauvegarde
```

## 🗂️ Structure du projet

```
kpi/
├── docker/                      # Configuration Docker
│   ├── compose.dev.yaml         # Docker Compose développement
│   ├── compose.preprod.yaml     # Docker Compose pré-production
│   ├── compose.prod.yaml        # Docker Compose production
│   ├── .env                     # Variables d'environnement (non versionné)
│   ├── .env.dist                # Template des variables
│   ├── wordpress/               # Contenu WordPress (non versionné)
│   └── config/                  # Dockerfiles et configurations
├── sources/
│   ├── app2/                    # Application Nuxt 4 (principale)
│   ├── app_dev/                 # Application Vue.js legacy
│   ├── app_live_dev/            # Application live Vue.js
│   ├── app_wsm_dev/             # Application WSM Vue.js
│   ├── api/                     # API REST PHP
│   ├── commun/                  # Classes PHP partagées
│   └── wordpress_archive/       # Archive WordPress
├── SQL/                         # Scripts SQL
├── DOC/                         # Documentation organisée
│   ├── README.md                # Index principal de la documentation
│   ├── user/                    # Documentation utilisateur (fonctionnalités)
│   │   ├── KPI_FUNCTIONALITY_INVENTORY.md
│   │   ├── MATCH_CONSISTENCY_STATS.md
│   │   └── CRON_DOCUMENTATION.md
│   └── developer/               # Documentation développeur
│       ├── guides/              # Guides de migration et infrastructure
│       ├── in-progress/         # Migrations en cours (status + plans)
│       ├── archive/             # Migrations terminées (PHP 8.4, mPDF, etc.)
│       ├── fixes/               # Corrections de bugs
│       ├── audits/              # Rapports d'audit de code
│       └── infrastructure/      # Docker, WordPress, configuration
├── Makefile                     # Commandes Make
├── CLAUDE.md                    # Documentation pour Claude Code
├── GEMINI.md                    # Documentation pour Gemini
└── README.md                    # Ce fichier
```

## 🔧 Configuration

### Fichiers d'environnement

**docker/.env** (non versionné)
- Configuration Docker principale
- Ports, chemins, credentials
- Créé depuis `docker/.env.dist`

**sources/app2/.env.development** (non versionné)
- Configuration Nuxt en développement
- Variables : `API_BASE_URL`, `BACKEND_BASE_URL`
- Utilisé automatiquement par `npm run dev`

**sources/app2/.env.production** (non versionné)
- Configuration Nuxt en production
- Utilisé pour `npm run build` et `npm run generate`

### WordPress

Le contenu WordPress est stocké dans `docker/wordpress/` et **n'est pas versionné** dans Git.

Configuration :
- Le chemin est défini par `HOST_WORDPRESS_PATH` dans `docker/.env`
- Les fichiers de config WordPress sont dans `docker/wordpress/wp-config.php*`
- Faire une sauvegarde avec `make wordpress_backup`

### Base de données

Deux bases de données MySQL/MariaDB :
1. **KPI** : Base principale de l'application
2. **WordPress** : Base pour le CMS

Accès via phpMyAdmin : http://kpi-myadmin.localhost

## 🌐 Environnements

### Développement
- Fichier : `docker/compose.dev.yaml`
- Ports exposés : 8003 (PHP 7.4), 8803 (PHP 8), 3002 (Nuxt)
- Hot reload activé
- Logs verbeux

### Pré-production
- Fichier : `docker/compose.preprod.yaml`
- Configuration proche de la production
- Pour tester avant déploiement

### Production
- Fichier : `docker/compose.prod.yaml`
- Optimisé pour les performances
- Utilise Traefik avec SSL/TLS

## 📦 Technologies utilisées

### Frontend
- **Nuxt 4** : Framework Vue.js avec rendu SSR/SSG
- **Vue 3** : Framework JavaScript réactif
- **TypeScript** : Typage statique
- **Tailwind CSS** : Framework CSS utility-first
- **Pinia** : Gestion d'état
- **Nuxt UI** : Composants UI

### Backend
- **PHP 8.4** : Langage serveur (migration PHP 7.4→8.4 terminée nov 2025)
- **mPDF v8.2+** : Génération PDF (remplacement de FPDF)
- **OpenSpout v4.32.0** : Export ODS/XLSX/CSV
- **Smarty v4** : Moteur de templates
- **MySQL/MariaDB 10.4** : Base de données
- **WordPress** : CMS (compatible PHP 8.4)

### Infrastructure
- **Docker** : Conteneurisation
- **Docker Compose** : Orchestration
- **Traefik** : Reverse proxy
- **Apache** : Serveur web

## 🔐 Sécurité

- Les fichiers `.env` ne sont **jamais** versionnés
- Les credentials sont dans les fichiers `.env`
- WordPress est isolé dans un volume séparé
- Traefik gère les certificats SSL en production

## 🐛 Dépannage

### Les containers ne démarrent pas
```bash
make networks_create   # Créer les réseaux manquants
make dev_status        # Vérifier le statut
make dev_logs          # Voir les erreurs
```

### Erreur "network not found"

Le nom du réseau dépend de `APPLICATION_NAME` dans `docker/.env`.

**Solution** :
```bash
# Vérifier le nom attendu
grep APPLICATION_NAME docker/.env

# Créer les réseaux avec le bon nom
make networks_create

# Vérifier que les réseaux sont créés
make networks_list
```

Si vous avez plusieurs environnements (dev, preprod, prod) sur le même serveur, utilisez des `APPLICATION_NAME` différents :
- Dev : `APPLICATION_NAME=kpi`
- Preprod : `APPLICATION_NAME=kpi_preprod`
- Prod : `APPLICATION_NAME=kpi_prod`

### Port déjà utilisé
Modifier les ports dans `docker/.env` :
```bash
DOCKER_SUFFIXE_PORT=03  # Changer ce suffixe
```

### Nuxt ne démarre pas
```bash
make npm_clean_app2    # Nettoyer
make npm_install_app2  # Réinstaller
make run_dev           # Relancer
```

## 📚 Documentation complémentaire

### Guides d'utilisation
- **[CLAUDE.md](CLAUDE.md)** : Guide complet des commandes pour Claude Code
- **[GEMINI.md](GEMINI.md)** : Guide pour Gemini

### Documentation complète
- **[DOC/](DOC/)** : Documentation organisée et structurée
  - **[DOC/user/](DOC/user/)** - Documentation utilisateur (fonctionnalités, guides)
    - [Inventaire des fonctionnalités](DOC/user/KPI_FUNCTIONALITY_INVENTORY.md)
    - [Statistiques de cohérence des matchs](DOC/user/MATCH_CONSISTENCY_STATS.md)
    - [Tâches cron automatiques](DOC/user/CRON_DOCUMENTATION.md)
  - **[DOC/developer/](DOC/developer/)** - Documentation développeur (technique)
    - **Guides** - Migration guides (FPDF → mPDF, OpenTBS → OpenSpout, Axios → fetch, etc.)
    - **En cours** - Migrations en cours (Bootstrap 5.3.8, Flatpickr, jQuery elimination)
    - **Archives** - ✅ Migrations terminées (PHP 8.4, mPDF, OpenSpout, Axios)
    - **Fixes** - Corrections de bugs et fixes PHP 8
    - **Audits** - Audits JavaScript, Bootstrap, code cleanup
    - **Infrastructure** - Docker, WordPress, configuration
  - Voir [DOC/README.md](DOC/README.md) pour l'index complet (60+ documents)
- **Makefile** : Toutes les commandes disponibles (`make help`)
- **Wiki GitHub** : https://github.com/FFCK/kpi/wiki

## 🔄 Workflow de développement

1. **Démarrer la journée**
```bash
make dev_up
make run_dev
```

2. **Développer**
- Modifier le code dans `sources/app2/`
- Hot reload automatique

3. **Tester**
```bash
make run_lint          # Vérifier le code
make run_build         # Tester le build
```

4. **Fin de journée**
```bash
make dev_down
```

## 🚢 Déploiement

### Configuration multi-environnements

Si vous déployez plusieurs environnements (dev, preprod, prod) sur le même serveur, vous devez configurer des `APPLICATION_NAME` différents pour éviter les conflits de réseaux et containers.

**1. Sur le serveur de pré-production :**

Éditer `docker/.env` :
```bash
APPLICATION_NAME=kpi_preprod    # Nom unique pour la préprod
```

Puis initialiser :
```bash
make init              # Crée network_kpi_preprod
```

**2. Sur le serveur de production :**

Éditer `docker/.env` :
```bash
APPLICATION_NAME=kpi_prod       # Nom unique pour la prod
```

Puis initialiser :
```bash
make init              # Crée network_kpi_prod
```

### Préparer pour la production
```bash
make run_build         # Build Nuxt
make run_generate      # Génération statique (si nécessaire)
```

### Déployer en pré-production
```bash
make preprod_up        # Tester en préprod
make preprod_logs      # Vérifier les logs
```

### Déployer en production
```bash
make prod_up           # Lancer en production
make prod_status       # Vérifier le statut
```

## 📝 CRON

Tâches planifiées :
- **Mise à jour des licenciés** : `commun/cron_maj_licencies.php` (quotidien)
- **Verrouillage des feuilles de présence** : `commun/cron_verrou_presences.php` (quotidien, J-6 avant événement)

## 🤝 Contribution

1. Créer une branche pour votre fonctionnalité
2. Développer et tester localement
3. Faire un build de production : `make run_build`
4. Créer une Pull Request

## 📄 Licence

Propriété de la FFCK (Fédération Française de Canoë-Kayak)

## 🆘 Support

Pour toute question ou problème :
- Consulter le wiki : https://github.com/FFCK/kpi/wiki
- Ouvrir une issue sur GitHub
