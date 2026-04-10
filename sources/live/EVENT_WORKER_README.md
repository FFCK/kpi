# Event Worker - Génération automatique des caches d'événements

## 📋 Vue d'ensemble

Le système **Event Worker** permet de générer automatiquement les fichiers cache JSON pour les événements **sans dépendre du navigateur**. Le processus tourne en arrière-plan sur le serveur et peut être contrôlé via une interface web ou des commandes Make.

### Avantages

✅ **Indépendant du navigateur** - Pas besoin de laisser un onglet ouvert
✅ **Fonctionnement continu** - Le worker tourne 24/7 si nécessaire
✅ **Contrôle à distance** - Interface web pour démarrer/arrêter/configurer
✅ **Monitoring en temps réel** - Vérification du statut et des logs
✅ **Redémarrage automatique** - Avec Docker, le worker redémarre après un crash

---

## 🚀 Installation

### 1. Créer la table de configuration

Exécutez la migration SQL pour créer la table de configuration :

```bash
# Depuis le container MySQL
docker exec -ti kpi_db sh
mysql -u root -p kpi_db < /var/www/html/SQL/20251111_create_event_worker_config.sql
exit
```

Ou via phpMyAdmin, importez le fichier : `SQL/20251111_create_event_worker_config.sql`

### 2. Vérifier les permissions

Assurez-vous que le dossier de logs est accessible :

```bash
mkdir -p sources/live/logs
chmod 755 sources/live/logs
```

---

## 💻 Utilisation

### Via l'interface web (Recommandé)

1. **Accéder à l'interface** : `https://votre-domaine.com/live/event.php`

2. **Configurer l'événement** :
   - Sélectionner l'événement
   - Choisir la date et l'heure de départ
   - Définir le warm-up, le nombre de terrains et le délai de rafraîchissement

3. **Démarrer le worker** :
   - Cliquer sur **"▶ Start Worker"**
   - Le worker démarre en arrière-plan
   - L'interface affiche le statut en temps réel

4. **Contrôler le worker** :
   - **⏸ Pause** : Met le worker en pause (sans perdre la config)
   - **▶ Resume** : Reprend après une pause
   - **⏹ Stop** : Arrête complètement le worker

5. **Monitoring** :
   - Le statut est rafraîchi toutes les 5 secondes
   - L'onglet affiche les matchs en cours et à venir
   - Vous pouvez fermer le navigateur, le worker continue !

### Via les commandes Make

```bash
# Démarrer le worker
make backend_worker_start

# Vérifier le statut
make backend_worker_status

# Consulter les logs en temps réel
make backend_worker_logs

# Redémarrer le worker
make backend_worker_restart

# Arrêter le worker
make backend_worker_stop
```

---

## 📊 Architecture

```
┌─────────────────────┐
│  Interface Web      │  ← Navigateur
│  (event.php)        │
└──────────┬──────────┘
           │ HTTP
           ▼
┌─────────────────────┐
│  API REST           │
│  (api_worker.php)   │
└──────────┬──────────┘
           │ MySQL
           ▼
┌─────────────────────┐
│  Base de données    │
│  (kp_event_worker_  │
│   config)           │
└──────────┬──────────┘
           │ Polling
           ▼
┌─────────────────────┐
│  Worker Process     │  ← Processus PHP en arrière-plan
│  (event_worker.php) │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Fichiers JSON      │  ← Cache généré
│  (cache/*.json)     │
└─────────────────────┘
```

### Composants

1. **event.php** : Interface web avec boutons de contrôle et monitoring
2. **event.js** : JavaScript pour gérer l'interface et appeler l'API
3. **api_worker.php** : API REST pour contrôler le worker (start/stop/status/pause/resume)
4. **event_worker.php** : Processus PHP CLI qui tourne en arrière-plan
5. **kp_event_worker_config** : Table MySQL qui stocke la configuration et le statut

---

## 🔧 Fonctionnement technique

### Cycle de vie du worker

1. **Démarrage** :
   - L'utilisateur clique sur "Start Worker" dans l'interface
   - L'API enregistre la configuration dans la table `kp_event_worker_config`
   - Le processus `event_worker.php` lit la config et démarre la boucle

2. **Exécution** :
   - Toutes les X secondes (délai configuré), le worker :
     - Calcule l'heure simulée (temps de départ + temps écoulé)
     - Appelle la logique existante (`CacheMatch::Event`)
     - Génère les fichiers JSON (`eventXXX_pitchXXX.json`)
     - Enregistre un heartbeat dans la base

3. **Arrêt** :
   - L'utilisateur clique sur "Stop Worker"
   - L'API met à jour le statut en base : `status = 'stopped'`
   - Le worker détecte le changement et s'arrête proprement

### Calcul du temps simulé

Le worker simule l'écoulement du temps à partir d'une heure de départ :

```php
$elapsedSeconds = microtime(true) - $startTime;
$currentSimulatedTime = $initialTime + $elapsedSeconds;
```

Cela permet de lancer un événement "en avance" ou de reprendre après une pause.

---

## 📁 Fichiers générés

Le worker génère les mêmes fichiers JSON que l'ancien système :

```
sources/live/cache/
├── event86_pitch1.json     # Terrain 1
├── event86_pitch2.json     # Terrain 2
├── event86_pitch3.json     # Terrain 3
├── event86_pitch4.json     # Terrain 4
└── ...
```

Chaque fichier contient :
```json
{
  "id_match": 12345,
  "pitch": 1,
  "id_next": 12346
}
```

Ces fichiers sont utilisés par les pages d'incrustation pour afficher automatiquement le bon match.

---

## 🐛 Dépannage

### Le worker ne démarre pas

**Vérifier les logs** :
```bash
make backend_worker_logs
```

**Causes possibles** :
- Table `kp_event_worker_config` non créée → Exécuter la migration SQL
- Permissions insuffisantes sur `sources/live/logs/` → `chmod 755`
- Erreur de connexion à la base de données → Vérifier les credentials

### Le worker s'arrête tout seul

**Vérifier le statut dans l'interface** :
- Si un message d'erreur est affiché, corriger le problème
- Vérifier les logs pour plus de détails

**Causes possibles** :
- Configuration incorrecte (ID événement invalide)
- Problème de base de données
- Container Docker redémarré

### L'interface affiche "Worker may not be running properly"

Cela signifie que le heartbeat n'a pas été mis à jour depuis plus de 3× le délai configuré.

**Actions** :
1. Vérifier que le processus tourne : `make backend_worker_status`
2. Si arrêté, redémarrer : `make backend_worker_restart`
3. Vérifier les logs pour voir l'erreur

### Les fichiers JSON ne sont pas générés

**Vérifier** :
1. Le worker tourne : `make backend_worker_status`
2. La configuration est correcte dans l'interface
3. Le statut est "running" (pas "paused" ou "stopped")
4. Les permissions sur `sources/live/cache/`

---

## 🔐 Sécurité

### Recommandations

- ✅ Protéger l'accès à `event.php` et `api_worker.php` (authentification)
- ✅ Valider les paramètres dans l'API
- ✅ Limiter les droits du processus worker
- ✅ Ne pas exposer les logs publiquement

### Accès restreint

Ajoutez une authentification dans `event.php` :

```php
// En début de fichier
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied');
}
```

---

## 📈 Évolutions possibles

- [ ] Interface de scheduling (démarrer/arrêter à des horaires précis)
- [ ] Support multi-événements (plusieurs workers simultanés)
- [ ] Notifications (email/Slack quand un worker s'arrête)
- [ ] Historique des exécutions
- [ ] API pour intégration avec d'autres systèmes

---

## 📞 Support

Pour toute question ou problème :
1. Consulter les logs : `make backend_worker_logs`
2. Vérifier le statut : `make backend_worker_status`
3. Redémarrer le worker : `make backend_worker_restart`

---

**Développé pour le projet KPI - FFCK**
Version 1.0 - Novembre 2025
