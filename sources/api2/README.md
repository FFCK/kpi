# API2 - Symfony 7.3 + API Platform 4.2

This is a modern REST API built with Symfony 7.3 and API Platform 4.2, providing the same functionalities as the legacy PHP API in `/sources/api`.

## Features

- **Symfony 7.3** - Modern PHP framework
- **API Platform 4.2** - REST API with OpenAPI documentation
- **Doctrine ORM** - Database abstraction layer
- **CORS Support** - Cross-Origin Resource Sharing enabled
- **Same Database** - Uses the existing KPI database (MariaDB 11.5)

## Installation

### Initial Setup

1. Copy the `.env.dist` file to `.env`:
   ```bash
   cp sources/api2/.env.dist sources/api2/.env
   ```
   Or use the Makefile command:
   ```bash
   make init_env_api2
   ```

2. Install Composer dependencies:
   ```bash
   make api2_backend_composer_install
   ```

The API dependencies are managed by Composer and will be installed in the `vendor/` directory.

### Configuration

The API is configured through the `.env` file (copied from `.env.dist`):

```env
DATABASE_URL="mysql://root:root@kpi_db:3306/kayak_polo?serverVersion=11.5.2-MariaDB&charset=utf8mb4"
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1|.*\.localhost)(:[0-9]+)?$'
```

**Note**: The `.env` file is not versioned in Git (ignored via `.gitignore`). Always use `.env.dist` as the template and create your local `.env` file from it.

## API Endpoints

### Authentication

#### Login
- `POST /login` - User authentication
  - **Authentication:** HTTP Basic Auth (Authorization header)
  - **Request:** `Authorization: Basic base64(username:password)`
  - **Response:**
    ```json
    {
      "user": {
        "id": "123456",
        "name": "Dupont",
        "firstname": "Jean",
        "profile": "O",
        "events": "123|456|789",
        "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
      }
    }
    ```
  - **Token Usage:** The returned `token` should be used in Staff and Report endpoints
  - **Token Validity:** 10 days from generation
  - **Token Transmission:**
    - **Recommended:** Via header `X-Auth-Token: {token}` (used by app2)
    - Alternative: Via cookie `kpi_app={token}`

### Public Endpoints (No Authentication)

#### Events
- `GET /events/{mode}` - Get events list
  - `mode`: `std`, `champ`, or `all`
- `GET /event/{id}` - Get single event

#### Games
- `GET /games/{eventId}` - Get games for an event

#### Charts & Rankings
- `GET /charts/{eventId}` - Get charts and rankings for an event

#### Team Statistics
- `GET /team-stats/{teamId}/{eventId}` - Get team statistics

#### App Ratings
- `GET /stars` - Get app ratings statistics
- `POST /rating` - Submit app rating
  ```json
  {
    "uid": "uuid-v4",
    "stars": 4
  }
  ```

### Staff Endpoints (Token Authentication Required)

**Authentication:** All staff endpoints require a valid token obtained from `/login`

**Token transmission:** Use `X-Auth-Token` header or `kpi_app` cookie

- `GET /staff/{eventId}/teams` - Get teams for scrutineering
- `GET /staff/{eventId}/team/{teamId}/players` - Get players for a team (returns fresh data with no-cache headers)
- `PUT /staff/{eventId}/team/{teamId}/player/{playerId}/{parameter}/{value}` - Update player scrutineering data
  - `parameter`: `kayak_status`, `vest_status`, `helmet_status`, `paddle_count`
- `PUT /staff/{eventId}/team/{teamId}/player/{playerId}/comment` - Update player comment
  ```json
  {
    "comment": "Comment text"
  }
  ```

### Report Endpoints (Token Authentication Required)

**Authentication:** All report endpoints require a valid token obtained from `/login`

**Token transmission:** Use `X-Auth-Token` header or `kpi_app` cookie

- `GET /report/game/{gameId}` - Get game details with events and players

### WSM (Web Score Management) Endpoints

- `PUT /api/wsm/eventNetwork/{eventId}` - Update event network data
- `PUT /api/wsm/gameParam/{matchId}` - Update game parameters
  ```json
  {
    "param": "Statut|Periode|ScoreA|ScoreB|ScoreDetailA|ScoreDetailB|Heure_fin",
    "value": "..."
  }
  ```
- `PUT /api/wsm/gameEvent/{matchId}` - Add/remove game events
  ```json
  {
    "params": {
      "action": "add|remove",
      "uid": "event-id",
      "period": 1,
      "tpsJeu": "10:00",
      "code": "B|V|J|R|D",
      "player": "123456",
      "number": 5,
      "team": "A|B",
      "reason": "..."
    }
  }
  ```
- `PUT /api/wsm/playerStatus/{matchId}` - Update player status
  ```json
  {
    "params": {
      "team": "A|B",
      "player": "123456",
      "status": "C|E|..."
    }
  }
  ```
- `PUT /api/wsm/gameTimer/{matchId}` - Control game timer
  ```json
  {
    "params": {
      "action": "run|stop|RAZ",
      "startTime": 0,
      "runTime": 600,
      "maxTime": 1200
    }
  }
  ```
- `PUT /api/wsm/stats` - Add game statistics
  ```json
  {
    "user": "user-id",
    "game": 123,
    "team": "A|B",
    "player": "123456",
    "action": "pass|possession|kickoff|shot-in|shot-out|shot-stop",
    "period": 1,
    "timer": "10:00"
  }
  ```

### Admin Competitions (JWT Protected, app4)
```
GET    /admin/competitions                    List competitions (paginated)
GET    /admin/competitions/{code}             Get single competition
POST   /admin/competitions                    Create competition (profile ≤3)
PUT    /admin/competitions/{code}             Update competition (profile ≤3)
DELETE /admin/competitions/{code}             Delete competition (profile ≤2)
POST   /admin/competitions/bulk-delete        Bulk delete (profile ≤2)
PATCH  /admin/competitions/{code}/publish     Toggle publication (profile ≤4)
PATCH  /admin/competitions/{code}/lock        Toggle lock (profile ≤3)
PATCH  /admin/competitions/{code}/status      Change status ATT/ON/END (profile ≤3)
GET    /admin/competitions-groups             List groups for select
GET    /admin/competitions-for-multi          List competitions for MULTI select
```

**Query Parameters (GET list):** `season`, `page`, `limit`, `search`, `level`, `type`, `sortBy`, `sortOrder`

**Delete validation:** Competition must have 0 teams, 0 gamedays, and 0 matches

## Database Tables Used

- `kp_evenement` - Events
- `kp_journee` - Competition days
- `kp_match` - Matches/Games
- `kp_match_detail` - Match events (goals, cards, etc.)
- `kp_match_joueur` - Match players
- `kp_competition` - Competitions
- `kp_competition_equipe` - Teams
- `kp_competition_equipe_joueur` - Team players
- `kp_competition_equipe_journee` - Team standings per day
- `kp_licence` - Player licenses
- `kp_arbitre` - Referees
- `kp_app_rating` - App ratings
- `kp_scrutineering` - Equipment scrutineering data
- `kp_chrono` - Game timer data
- `kp_stats` - Game statistics

## Development

### Running the API

The API is served through Apache in the Docker container. The document root is `/sources/api2/public/`.

### Accessing the API

- Development: `https://kpi.localhost/api2/`
- API Platform UI: `https://kpi.localhost/api2/api` (API Platform interface)
- **Swagger UI**: `https://kpi.localhost/api2/doc` (Complete OpenAPI documentation - requires NelmioApiDocBundle)

**Note**: For complete Swagger/OpenAPI documentation with all endpoints, see [SWAGGER_SETUP.md](SWAGGER_SETUP.md) for installation instructions.

### Cache Management

```bash
# Clear cache
php bin/console cache:clear

# Warmup cache
php bin/console cache:warmup
```

## Complete API Documentation

For complete endpoint reference with examples and migration guide, see:
- **[API2_ENDPOINTS.md](../../DOC/developer/reference/API2_ENDPOINTS.md)** - Complete endpoint reference with examples

## Migration from Legacy API

This API provides the same endpoints and functionality as `/sources/api/` but with:

- **Better structure** - Controllers organized by domain
- **Type safety** - PHP 8 type hints throughout
- **Modern practices** - Dependency injection, service container
- **Auto-documentation** - OpenAPI/Swagger via API Platform
- **Extensibility** - Easy to add new endpoints and features
- **Security** - Built-in CSRF protection, input validation
- **RESTful URLs** - Staff routes follow RESTful patterns (`/staff/{eventId}/team/{teamId}/...`)
- **No-cache headers** - Staff endpoints return fresh data for real-time scrutineering

## TODO

- [x] ✅ Add `/login` endpoint for authentication
- [x] ✅ Add authentication documentation
- [x] ✅ Implement token validation service for staff and report endpoints
- [x] ✅ Update Staff/Report endpoints to use `X-Auth-Token` header (compatible with app2)
- [ ] Add cache layer (similar to json_cache_read/write in legacy API)
- [ ] Implement cache creation for WSM endpoints (CacheMatch equivalent)
- [ ] Add rate limiting
- [ ] Add logging for user actions
- [ ] Add unit and functional tests
- [ ] Optimize database queries with Doctrine Query Builder
- [ ] Add API versioning support

## Architecture Notes

### Why Direct SQL Queries?

The API currently uses direct SQL queries (via Doctrine DBAL) instead of Doctrine ORM entities for several reasons:

1. **Complex queries** - The legacy API uses complex JOINs and aggregations that are easier to write in SQL
2. **Performance** - Direct SQL is faster for read-heavy operations
3. **Backward compatibility** - Ensures exact same results as legacy API
4. **Database schema** - The existing database wasn't designed with ORM in mind

### Future Improvements

For future versions, consider:

1. Creating proper Doctrine entities for write operations
2. Using Query Builder for simpler queries
3. Implementing a repository pattern
4. Adding DTOs (Data Transfer Objects) for API responses
5. Implementing CQRS pattern (Command Query Responsibility Segregation)

## Support

For questions or issues, refer to the main KPI documentation in `/WORKFLOW_AI/` or contact the development team.
