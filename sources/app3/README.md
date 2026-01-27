# KPI App3 - Match Sheet

Application de gestion de feuille de marque en temps réel pour le Kayak Polo, construite avec Nuxt 4.

## 🎯 Fonctionnalités

- ✅ Création de match de toute pièce (équipes, joueurs, coachs)
- ✅ Chargement de match existant depuis le backend
- ✅ Gestion de match en temps réel
  - Chronomètre principal avec contrôles +/- (par 1, 10, 60 secondes)
  - Shot clock (time-shoot)
  - Gestion des périodes (M1, M2, P1, P2, TB)
  - Statuts de match (En attente, En cours, Terminé)
- ✅ Gestion des joueurs
  - Maximum 10 joueurs par équipe
  - Maximum 3 entraîneurs/coachs par équipe
  - Ajout/suppression dynamique
- ✅ Événements de match
  - Buts (avec incrémentation automatique du score)
  - Cartons (vert, jaune, rouge, rouge définitif)
  - Liste complète des événements avec possibilité de suppression
- ✅ Broadcast
  - BroadcastChannel API pour communication avec scoreboard.php et shotclock.php
  - Ouverture automatique des fenêtres de diffusion
  - Synchronisation en temps réel
- ✅ WebSocket (optionnel)
  - Support WebSocket si configuré pour le match
  - Reconnexion automatique
  - Diffusion des données vers les clients connectés
- ✅ Stockage offline
  - Dexie (IndexedDB) pour sauvegardes locales
  - Historique des matchs récents
  - Mode hors ligne
- ✅ PWA
  - Progressive Web App
  - Installation sur mobile/tablette
  - Fonctionne hors ligne
- ✅ Internationalisation
  - Français, Anglais, Chinois
  - Changement de langue dynamique
- ✅ Verrouillage de match
  - Protection contre les modifications accidentelles
  - Mode lecture seule

## 🚀 Stack Technique

- **Framework**: Nuxt 4
- **UI**: Vue 3 + TypeScript + Tailwind CSS
- **State Management**: Pinia
- **Offline Storage**: Dexie (IndexedDB)
- **Timer**: easytimer.js
- **PWA**: @vite-pwa/nuxt
- **i18n**: @nuxtjs/i18n
- **Broadcast**: BroadcastChannel API native
- **WebSocket**: WebSocket API native

## 📦 Installation

### Prérequis
- Docker et Docker Compose installés
- Traefik configuré (pour le domaine app3.localhost)

### Configuration

1. **Initialiser les réseaux Docker** (si ce n'est pas déjà fait) :
```bash
make docker_networks_create
```

2. **Vérifier le fichier `.env`** dans `docker/`:
```bash
# Assurez-vous que APP3_DOMAIN_NAME est défini
APP3_DOMAIN_NAME=app3.localhost
```

3. **Démarrer les containers Docker** :
```bash
# Démarrer l'environnement de développement
make docker_dev_up
```

4. **Installer les dépendances NPM** :
```bash
# Via Makefile (recommandé)
make app3_npm_install
```

## 🛠️ Développement

### Démarrage

```bash
# Démarrer le serveur de développement (port 3003)
make app3_dev
```

L'application sera accessible sur :
- **Via Docker avec Traefik** : `https://app3.localhost` (recommandé)
- **Accès direct** : `http://localhost:3003`

### Commandes disponibles

```bash
# Installation
make app3_npm_install           # Installer les dépendances
make app3_npm_clean             # Nettoyer node_modules

# Développement
make app3_dev               # Serveur dev (port 3003)
make app3_build             # Build production
make app3_generate_prod          # Génération statique
make app3_lint              # ESLint

# Ajout de packages
make app3_npm_add package=uuid
make app3_npm_add_dev package=eslint

# Shell
make app3_bash                 # Ouvrir un shell dans le container
```

### Sans Docker (développement local)

Si vous préférez développer sans Docker :

```bash
cd sources/app3
npm install
npm run dev
```

**Note** : Le développement avec Docker est recommandé pour bénéficier de Traefik et du domaine app3.localhost.

## 🏗️ Build Production

```bash
# Build pour la production
make app3_build

# Générer le site statique
make app3_generate_prod
```

Les fichiers générés seront dans `.output/` (build) ou `.output/public/` (generate).

## 📱 Utilisation

### 1. Créer un nouveau match

- Remplir le formulaire avec les noms des équipes, date, heure, terrain
- Choisir le type de match (Classement ou Élimination)
- Cliquer sur "Créer un match"

### 2. Charger un match existant

- Entrer l'ID du match
- Cliquer sur "Charger"
- Le match sera chargé depuis l'API backend et sauvegardé localement

### 3. Gérer le match

#### Ajouter des joueurs
- Cliquer sur le bouton "+" dans la section de l'équipe
- Remplir le formulaire (numéro, nom, prénom, statut)
- Les joueurs apparaissent dans la liste

#### Contrôler le chrono
- **Start**: Démarre le chrono principal et le shot clock
- **Pause**: Met en pause les deux chronos
- **Reset**: Remet le chrono principal à la durée de la période actuelle
- **+/- 1/10/60**: Ajuste le temps par secondes

#### Ajouter des événements
1. Cliquer sur un joueur pour le sélectionner
2. Cliquer sur le type d'événement (But, Carton...)
3. L'événement est ajouté avec le temps actuel du chrono
4. Le score est automatiquement mis à jour pour les buts

#### Broadcast
- **Ouvrir le tableau de bord**: Ouvre scoreboard.php dans une nouvelle fenêtre
- **Ouvrir le time-shoot**: Ouvre shotclock.php dans une nouvelle fenêtre
- **Actualiser la diffusion**: Force l'envoi de toutes les données via BroadcastChannel
- **WebSocket**: Connecter/Déconnecter le WebSocket si configuré

### 4. Verrouiller le match

- Cliquer sur le bouton 🔓/🔒 en haut à droite
- En mode verrouillé:
  - Impossible d'ajouter/supprimer des joueurs
  - Impossible d'ajouter/supprimer des événements
  - Impossible de changer de période/statut
  - Le chrono reste utilisable

## 🔌 API Backend

L'application utilise les endpoints API suivants:

```
GET /api/match/{id}        - Charger un match existant
POST /api/match            - Créer un nouveau match
PUT /api/match/{id}        - Mettre à jour un match
GET /api/match/{id}/events - Récupérer les événements d'un match
POST /api/match/{id}/event - Ajouter un événement
```

## 📡 BroadcastChannel

L'application utilise le BroadcastChannel `kpi_channel` pour communiquer avec:

- `scoreboard.php`: Affichage du score et du temps
- `shotclock.php`: Affichage du shot clock

Messages envoyés:
- `timer`: Temps du chrono principal
- `shotclock`: Temps du shot clock
- `timer_status`: État du chrono (run/stop)
- `period`: Période actuelle
- `teams`: Noms des équipes
- `scores`: Scores des équipes
- `penA`/`penB`: Pénalités

## 🌐 WebSocket

Si configuré dans le match (via `websocketConfig`), l'application se connecte automatiquement au serveur WebSocket pour diffuser:

- Score en temps réel
- Temps du chrono
- Shot clock
- Période
- Pénalités

Format des messages WebSocket:
```json
{
  "p": "eventId_terrain",
  "t": "type",
  "v": "value"
}
```

## 🗄️ Stockage Local

Les matchs sont automatiquement sauvegardés dans IndexedDB via Dexie:

- `matches`: Stockage des matchs avec tous leurs détails
- `preferences`: Préférences utilisateur

Les 5 derniers matchs sont affichés sur la page d'accueil pour un accès rapide.

## 🎨 Personnalisation

### Couleurs des équipes
Les couleurs des équipes peuvent être définies dans le backend et seront affichées automatiquement.

### Durées des périodes
Les durées sont définies dans `stores/matchStore.ts`:
```typescript
periodDurations: {
  M1: 600, // 10 minutes
  M2: 600, // 10 minutes
  P1: 180, // 3 minutes
  P2: 180, // 3 minutes
  TB: 180  // 3 minutes
}
```

### Shot clock par défaut
Défini dans `composables/useTimer.ts`: 60 secondes

## 🐛 Dépannage

### Le chrono ne démarre pas
- Vérifier que easytimer.js est bien installé
- Vérifier la console pour les erreurs JavaScript

### Le broadcast ne fonctionne pas
- Vérifier que scoreboard.php et shotclock.php sont ouverts dans des fenêtres du même navigateur
- Vérifier que le BroadcastChannel est supporté (Chrome, Firefox, Edge modernes)

### Le WebSocket ne se connecte pas
- Vérifier que `websocketConfig.enabled` est à `true` dans le match
- Vérifier l'URL du serveur WebSocket dans `composables/useWebSocket.ts`
- Vérifier la console réseau

## 📝 TODO

- [ ] Ajouter les motifs de cartons (avec modal de sélection)
- [ ] Implémenter la gestion des pénalités avec décompte
- [ ] Ajouter l'export PDF de la feuille de match
- [ ] Intégrer les statistiques de match
- [ ] Ajouter la synchronisation automatique avec le backend
- [ ] Implémenter l'édition des événements existants
- [ ] Ajouter les commentaires officiels
- [ ] Supporter les drapeaux de pays pour les équipes

## 📄 Licence

Propriétaire - KPI / Kayak Polo Information

## 👥 Auteurs

- Application développée pour le système KPI
- Basée sur FeuilleMarque3.php existant
