# Event Cache Manager - Worker en Arrière-plan

**Fonctionnalité** : Génération automatique des caches d'événements pour les incrustations vidéo en direct

---

## 📋 À quoi ça sert ?

Le **Event Cache Manager** génère automatiquement les fichiers nécessaires pour afficher les bons matchs sur les incrustations vidéo (live streaming) **sans avoir besoin de laisser un navigateur ouvert**.

Un processus tourne en arrière-plan sur le serveur et met à jour les informations des matchs en temps réel.

---

## ✨ Avantages

- ✅ **Fonctionne 24/7** - Le worker tourne en continu
- ✅ **Pas besoin de navigateur** - Plus besoin de laisser un onglet ouvert
- ✅ **Contrôle à distance** - Interface web simple pour démarrer/arrêter
- ✅ **Monitoring en temps réel** - Suivi du statut et des matchs en cours
- ✅ **Redémarrage automatique** - Le système redémarre automatiquement en cas de problème

---

## 🚀 Comment utiliser

### 1. Accéder à l'interface

Rendez-vous sur : `https://votre-domaine.com/live/event.php`

### 2. Configurer l'événement

1. **Sélectionner l'événement** dans la liste déroulante
2. **Choisir la date et l'heure de départ** du tournoi
3. **Définir les paramètres** :
   - Warm-up (temps avant le premier match)
   - Nombre de terrains
   - Délai de rafraîchissement (en secondes)

### 3. Démarrer le worker

1. Cliquer sur le bouton **"▶ Start Worker"**
2. Le worker démarre en arrière-plan
3. L'interface affiche le statut en temps réel

### 4. Contrôler le worker

- **⏸ Pause** : Met en pause (sans perdre la configuration)
- **▶ Resume** : Reprend après une pause
- **⏹ Stop** : Arrête complètement le worker

### 5. Monitoring

- Le statut est automatiquement rafraîchi toutes les 5 secondes
- Les matchs en cours et à venir sont affichés
- Vous pouvez **fermer le navigateur**, le worker continue de fonctionner !

---

## 📊 Que fait le worker ?

Le worker calcule automatiquement :
- **Quel match est en cours** sur chaque terrain
- **Quel est le prochain match** à afficher
- **Les fichiers JSON** nécessaires aux incrustations vidéo

Ces fichiers sont utilisés par les pages d'incrustation (`score.php`, `teams.php`, etc.) pour afficher automatiquement les bonnes informations.

---

## 🔧 Cas d'usage typique

**Situation** : Vous organisez un tournoi avec live streaming sur YouTube

**Sans Event Cache Manager** :
- ❌ Vous devez laisser un ordinateur avec un navigateur ouvert
- ❌ Si le navigateur plante, les incrustations ne sont plus à jour
- ❌ Vous devez être devant l'ordinateur pour surveiller

**Avec Event Cache Manager** :
- ✅ Vous configurez l'événement une fois
- ✅ Le serveur s'occupe de tout automatiquement
- ✅ Vous pouvez surveiller depuis n'importe où via l'interface web
- ✅ Les incrustations sont toujours à jour

---

## 🐛 Que faire si ça ne marche pas ?

### Le worker ne démarre pas

1. Vérifiez que vous avez bien sélectionné un événement
2. Vérifiez que les dates/heures sont correctes
3. Contactez l'administrateur technique

### Le statut affiche un avertissement

Si vous voyez "Worker may not be running properly" :
1. Cliquez sur **Stop** puis **Start** pour redémarrer
2. Si le problème persiste, contactez l'administrateur

### Les incrustations n'affichent pas le bon match

1. Vérifiez que le worker est bien en statut "Running"
2. Vérifiez la configuration (nombre de terrains, heure de départ)
3. Essayez de redémarrer le worker

---

## 💡 Conseils

- **Testez avant le tournoi** : Lancez le worker 30 minutes avant pour vérifier que tout fonctionne
- **Vérifiez les horaires** : Assurez-vous que l'heure de départ correspond au planning réel
- **Surveillez le statut** : Gardez l'interface ouverte sur un appareil pour surveiller
- **Pause utile** : Utilisez la pause si vous devez faire une annonce ou gérer un problème technique

---

## 📚 Documentation technique complète

Pour les administrateurs et développeurs, voir la documentation technique complète :
[sources/live/EVENT_WORKER_README.md](../../sources/live/EVENT_WORKER_README.md)

---

**Version** : 1.0
**Date** : Décembre 2025
**Public** : Organisateurs de tournois, responsables live streaming
