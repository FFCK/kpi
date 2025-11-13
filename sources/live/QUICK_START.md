# 🚀 Quick Start - Event Worker

Ce guide vous permet de démarrer rapidement avec le nouveau système de worker pour la génération automatique des caches d'événements.

---

## ⚡ Démarrage rapide (3 étapes)

### 1️⃣ Installer la base de données

```bash
# Démarrer les conteneurs Docker
make dev_up

# Attendre que les conteneurs démarrent (30 secondes)

# Exécuter la migration SQL
docker exec -i kpi_db mysql -u root -p"${MYSQL_ROOT_PASSWORD}" kpi_db < SQL/20251111_create_event_worker_config.sql
```

Ou manuellement via phpMyAdmin :
- Ouvrir phpMyAdmin
- Sélectionner la base `kpi_db`
- Onglet "Import" > Choisir `SQL/20251111_create_event_worker_config.sql`
- Cliquer "Exécuter"

### 2️⃣ Créer le dossier de logs

```bash
mkdir -p sources/live/logs
chmod 755 sources/live/logs
```

### 3️⃣ Utiliser l'interface web

1. Ouvrir dans votre navigateur : `https://kpi.local/live/event.php` (ou votre domaine)
2. Sélectionner un événement
3. Configurer la date, l'heure et les paramètres
4. Cliquer sur **"▶ Start Worker"**
5. ✅ **Vous pouvez fermer le navigateur !** Le worker continue de tourner

---

## 🎯 Différences avec l'ancien système

| Ancien système | Nouveau système (Worker) |
|----------------|--------------------------|
| ❌ Navigateur obligatoire | ✅ Indépendant du navigateur |
| ❌ JavaScript setInterval() | ✅ Processus PHP serveur |
| ❌ Perd la session si l'onglet se ferme | ✅ Continue même si vous fermez tout |
| ⚠️ Difficile à monitorer | ✅ Interface de monitoring intégrée |
| ⚠️ Pas de logs centralisés | ✅ Logs accessibles via `make event_worker_logs` |

---

## 🔧 Commandes utiles

```bash
# Vérifier que le worker tourne
make event_worker_status

# Voir les logs en temps réel
make event_worker_logs

# Redémarrer le worker
make event_worker_restart

# Arrêter le worker
make event_worker_stop
```

---

## 📖 Documentation complète

Pour plus de détails, consultez : [`EVENT_WORKER_README.md`](EVENT_WORKER_README.md)

---

## ✅ Checklist de vérification

Avant d'utiliser le worker en production :

- [ ] Table `kp_event_worker_config` créée
- [ ] Dossier `sources/live/logs/` existant avec permissions 755
- [ ] Conteneurs Docker en cours d'exécution
- [ ] Accès à l'interface web `event.php` fonctionnel
- [ ] Test de démarrage/arrêt du worker
- [ ] Vérification des fichiers JSON générés dans `sources/live/cache/`

---

## 🚨 Dépannage rapide

### "Worker Status: Not configured"
→ Normal au premier démarrage. Configurez et cliquez sur "Start Worker"

### "Worker may not be running properly"
→ Vérifier : `make event_worker_status`
→ Redémarrer : `make event_worker_restart`

### Fichiers JSON non générés
→ Vérifier les logs : `make event_worker_logs`
→ Vérifier les permissions sur `sources/live/cache/`

### API renvoie une erreur
→ Vérifier que la table existe dans la base de données
→ Vérifier les logs PHP du container

---

## 💡 Cas d'usage

**Compétition sur une journée**
1. Configurer le worker 1h avant le début
2. Démarrer le worker
3. Les pages d'incrustation changent automatiquement de match
4. Monitorer via l'interface web si besoin
5. Arrêter le worker en fin de journée

**Compétition sur plusieurs jours**
1. Configurer le worker pour le premier jour
2. À la fin de la journée, cliquer sur "Pause"
3. Le lendemain, ajuster la date/heure et cliquer sur "Start Worker"
4. Répéter pour chaque jour

---

## 🎉 Prêt !

Vous êtes maintenant prêt à utiliser le système de worker pour vos événements !

N'hésitez pas à consulter la [documentation complète](EVENT_WORKER_README.md) pour en savoir plus.
