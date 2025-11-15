# Swagger / OpenAPI Documentation Setup

## Configuration complète avec NelmioApiDocBundle

### Étape 1 : Installation de NelmioApiDocBundle

Exécutez cette commande sur votre serveur :

```bash
make composer_require_api2 package=nelmio/api-doc-bundle
```

Cette commande installe le bundle nécessaire pour générer automatiquement la documentation OpenAPI à partir des annotations.

### Étape 2 : Vider le cache Symfony

Après l'installation, videz le cache :

```bash
make api2_cache_clear
```

### Étape 3 : Accéder à la documentation Swagger

Une fois installé, la documentation est accessible aux URLs suivantes :

- **Swagger UI** : `https://kpi.localhost/api2/api/doc`
- **JSON OpenAPI** : `https://kpi.localhost/api2/api/doc.json`

### Architecture hybride mise en place

L'API2 utilise désormais une **approche hybride** pour la documentation :

#### 1. API Platform Resources (entités avec #[ApiResource])

**Fichier** : `src/Entity/Event.php`

Ces ressources apparaissent automatiquement dans Swagger via API Platform :
- `GET /api/events` - Liste des événements
- `GET /api/events/{id}` - Détails d'un événement

#### 2. Contrôleurs Symfony avec annotations OpenAPI

**Fichiers annotés** :
- `src/Controller/EventController.php`
- `src/Controller/GamesController.php`
- `src/Controller/ChartsController.php`
- `src/Controller/PublicController.php`
- `src/Controller/StaffController.php`
- `src/Controller/ReportController.php`
- `src/Controller/WsmController.php`

Ces contrôleurs utilisent les **annotations OpenAPI (OA\)** de NelmioApiDocBundle pour documenter chaque endpoint.

### Endpoints documentés dans Swagger

Une fois NelmioApiDocBundle installé, **tous les endpoints suivants** seront visibles dans Swagger UI :

#### Events (Événements)
- `GET /api/events/{mode}` - Liste des événements (std/champ/all)
- `GET /api/event/{id}` - Détails d'un événement

#### Games (Matchs)
- `GET /api/games/{eventId}` - Matchs d'un événement

#### Charts & Rankings (Classements)
- `GET /api/charts/{eventId}` - Classements et tableaux

#### Statistics (Statistiques)
- `GET /api/team-stats/{teamId}/{eventId}` - Statistiques d'équipe

#### App Ratings (Évaluations)
- `GET /api/stars` - Statistiques d'évaluation
- `POST /api/rating` - Soumettre une évaluation

#### Staff - Scrutineering (Contrôle technique)
- `GET /api/staff/{token}/test` - Test authentification
- `GET /api/staff/{token}/teams/{eventId}` - Équipes pour contrôle
- `GET /api/staff/{token}/players/{teamId}` - Joueurs d'une équipe
- `PUT /api/staff/{token}/player/{playerId}/team/{teamId}/{parameter}/{value}` - MAJ données contrôle
- `PUT /api/staff/{token}/player/{playerId}/team/{teamId}/comment` - MAJ commentaire

#### Report (Rapports de match)
- `GET /api/report/{token}/game/{gameId}` - Rapport complet de match

#### WSM - Web Score Management (Gestion scores en direct)
- `PUT /api/wsm/eventNetwork/{eventId}` - Configuration réseau
- `PUT /api/wsm/gameParam/{matchId}` - Paramètres de match
- `PUT /api/wsm/gameEvent/{matchId}` - Événements de match
- `PUT /api/wsm/playerStatus/{matchId}` - Statut joueur
- `PUT /api/wsm/gameTimer/{matchId}` - Chronomètre
- `PUT /api/wsm/stats` - Statistiques

### Tags Swagger

Les endpoints sont organisés par tags :
- **Events** - Gestion des événements
- **Games** - Gestion des matchs
- **Charts & Rankings** - Classements et tableaux
- **Statistics** - Statistiques d'équipe
- **App Ratings** - Évaluations de l'application
- **Staff - Scrutineering** - Contrôle technique
- **Report** - Rapports de match
- **WSM - Web Score Management** - Gestion scores en direct

### Sécurité

Les endpoints protégés (Staff, Report) utilisent un **TokenAuth** (token dans le path) :
- Configuration : `config/packages/nelmio_api_doc.yaml`
- Schéma de sécurité : `apiKey` in `path`

### Fichiers de configuration créés

1. **config/packages/nelmio_api_doc.yaml** - Configuration NelmioApiDocBundle
2. **config/routes.yaml** - Routes Swagger UI et JSON
3. Annotations dans tous les contrôleurs

### Dépannage

Si Swagger n'affiche pas tous les endpoints :

1. **Vérifier l'installation** :
   ```bash
   docker exec kpi_php bash -c "cd /var/www/html/api2 && composer show nelmio/api-doc-bundle"
   ```

2. **Vider le cache** :
   ```bash
   make api2_cache_clear
   make api2_cache_warmup
   ```

3. **Vérifier les logs Symfony** :
   ```bash
   tail -f /home/user/kpi/sources/api2/var/log/dev.log
   ```

4. **Tester l'URL JSON** :
   ```bash
   curl https://kpi.localhost/api2/api/doc.json
   ```

### Avantages de cette approche hybride

✅ **Endpoints fonctionnels** - Tous les endpoints sont opérationnels
✅ **Documentation complète** - Swagger affiche tous les endpoints après installation
✅ **Flexibilité** - Contrôleurs personnalisés pour logique complexe
✅ **Auto-documentation** - Annotations maintenues avec le code
✅ **Compatible API Platform** - Garde les avantages d'API Platform pour les entités simples

### Prochaines étapes

1. Installer NelmioApiDocBundle (commande ci-dessus)
2. Vider le cache
3. Accéder à Swagger UI
4. Tester les endpoints
5. Optionnel : Créer des entités API Platform pour endpoints simples (games, ratings, etc.)

## Support

Pour toute question, consulter :
- [Documentation NelmioApiDocBundle](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html)
- [OpenAPI Specification](https://swagger.io/specification/)
- `/sources/api2/README.md` - Documentation API2
- `/sources/api2/API_ENDPOINTS.md` - Référence rapide des endpoints
