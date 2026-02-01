# API2 Endpoints - Quick Reference

## Base URL
- Development: `https://kpi.localhost/api2/`
- API Platform UI: `https://kpi.localhost/api2/api`

## Public Endpoints

### Events API
```
GET  /events/{mode}          Get events (mode: std|champ|all)
GET  /event/{id}             Get single event
```

**Example Response:**
```json
[
  {
    "id": 123,
    "libelle": "Tournoi National",
    "place": "Paris",
    "logo": "logo/event123.png"
  }
]
```

### Games API
```
GET  /games/{eventId}        Get games for an event
```

**Example Response:**
```json
[
  {
    "g_id": 456,
    "c_code": "N1",
    "d_phase": "Poules",
    "t_a_label": "Team A",
    "t_b_label": "Team B",
    "g_score_a": 5,
    "g_score_b": 3,
    "g_date": "2025-01-15",
    "g_time": "10:00:00"
  }
]
```

### Charts & Rankings API
```
GET  /charts/{eventId}       Get rankings and brackets
```

**Example Response:**
```json
[
  {
    "code": "N1",
    "libelle": "Nationale 1",
    "type": "CHPT",
    "rounds": {
      "1": {
        "phases": {
          "100-Poules": {
            "teams": [...],
            "games": [...]
          }
        }
      }
    },
    "ranking": [...]
  }
]
```

### Statistics API
```
GET  /team-stats/{teamId}/{eventId}    Get team statistics
GET  /stars                             Get app ratings
POST /rating                            Submit app rating
```

**Rating Request:**
```json
{
  "uid": "550e8400-e29b-41d4-a716-446655440000",
  "stars": 4
}
```

## Authentication

### Login
```
POST /login
```

**Authentication:** HTTP Basic Auth (use Authorization header)

**Request:**
```bash
# Example with curl
curl -X POST https://kpi.localhost/api2/login \
  -H "Authorization: Basic $(echo -n 'user:password' | base64)"
```

**Response:**
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

**Token Usage:**
- The returned `token` should be used in Staff and Report endpoints
- Token is valid for 10 days from generation
- **Recommended:** Pass token via header `X-Auth-Token: {token}` (used by app2)
- Alternative: Pass via cookie `kpi_app={token}`
- Alternative: Legacy API accepts token in URL (deprecated in API2)

## Staff Endpoints (Require Token)

**Authentication:** All staff endpoints require a valid token obtained from `/login`

**Token transmission:** Use `X-Auth-Token` header or `kpi_app` cookie

### Scrutineering API
```
GET  /staff/{eventId}/teams                                           Get teams for event
GET  /staff/{eventId}/team/{teamId}/players                          Get players for team
PUT  /staff/{eventId}/team/{teamId}/player/{playerId}/{parameter}/{value}   Update player data
PUT  /staff/{eventId}/team/{teamId}/player/{playerId}/comment        Update player comment
```

**Available Parameters:**
- `kayak_status` - Kayak inspection status (0-2)
- `vest_status` - Life vest inspection status (0-2)
- `helmet_status` - Helmet inspection status (0-2)
- `paddle_count` - Number of paddles (0-5)

**Example with curl:**
```bash
# Get teams for event 222
curl -X GET https://kpi.localhost/api2/staff/222/teams \
  -H "X-Auth-Token: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"

# Get players for team 789 (with no-cache headers)
curl -X GET https://kpi.localhost/api2/staff/222/team/789/players \
  -H "X-Auth-Token: your-token-here"

# Update kayak status to 1 (validated)
curl -X PUT https://kpi.localhost/api2/staff/222/team/789/player/123456/kayak_status/1 \
  -H "X-Auth-Token: your-token-here"
```

**Comment Request:**
```json
{
  "comment": "Equipment OK"
}
```

## Report Endpoints (Require Token)

**Authentication:** All report endpoints require a valid token obtained from `/login`

**Token transmission:** Use `X-Auth-Token` header or `kpi_app` cookie

### Match Report API
```
GET  /report/game/{gameId}    Get full game report with events and players
```

**Example with curl:**
```bash
curl -X GET https://kpi.localhost/api2/report/game/456 \
  -H "X-Auth-Token: your-token-here"
```

**Response includes:**
- Game information
- Teams and players
- Match events (goals, cards, etc.)

## WSM Endpoints (Web Score Management)

### Network & Configuration
```
PUT  /wsm/eventNetwork/{eventId}      Update event network
```

### Game Management
```
PUT  /wsm/gameParam/{matchId}         Update game parameters
PUT  /wsm/gameEvent/{matchId}         Add/remove match events
PUT  /wsm/playerStatus/{matchId}      Update player status
PUT  /wsm/gameTimer/{matchId}         Control match timer
PUT  /wsm/stats                       Add match statistics
```

### Game Parameters (`gameParam`)
**Allowed parameters:**
- `Statut` - Match status
- `Periode` - Current period
- `ScoreA` - Team A score
- `ScoreB` - Team B score
- `ScoreDetailA` - Team A detailed score
- `ScoreDetailB` - Team B detailed score
- `Heure_fin` - End time

**Request:**
```json
{
  "param": "ScoreA",
  "value": "5"
}
```

### Match Events (`gameEvent`)
**Event types:**
- `B` - Goal (But)
- `V` - Green card (Vert)
- `J` - Yellow card (Jaune)
- `R` - Red card (Rouge)
- `D` - Definitive red card

**Add Event:**
```json
{
  "params": {
    "action": "add",
    "period": 1,
    "tpsJeu": "10:30",
    "code": "B",
    "player": "123456",
    "number": 5,
    "team": "A",
    "reason": ""
  }
}
```

**Remove Event:**
```json
{
  "params": {
    "action": "remove",
    "period": 1,
    "player": "123456",
    "code": "B"
  }
}
```

### Player Status (`playerStatus`)
**Request:**
```json
{
  "params": {
    "team": "A",
    "player": "123456",
    "status": "C"
  }
}
```

**Status codes:**
- `C` - Captain
- `E` - Coach
- etc.

### Game Timer (`gameTimer`)
**Actions:**
- `run` - Start/resume timer
- `stop` - Pause timer
- `RAZ` - Reset timer

**Request:**
```json
{
  "params": {
    "action": "run",
    "startTime": 0,
    "runTime": 600,
    "maxTime": 1200
  }
}
```

### Statistics (`stats`)
**Actions:**
- `pass` - Pass
- `possession` - Possession
- `kickoff` - Kickoff
- `kickoff-ko` - Kickoff after goal
- `shot-in` - Shot on target
- `shot-out` - Shot off target
- `shot-stop` - Shot saved

**Request:**
```json
{
  "user": "user123",
  "game": 456,
  "team": "A",
  "player": "123456",
  "action": "pass",
  "period": 1,
  "timer": "10:30"
}
```

## Admin Endpoints (JWT Protected)

**Authentication:** All admin endpoints require a valid JWT token obtained from `/auth/login`

**Token transmission:** Use `Authorization: Bearer {token}` header

### JWT Authentication
```
POST /auth/login
```

**Request:**
```json
{
  "username": "admin",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "user": {
    "id": 123456,
    "name": "Admin",
    "firstname": "Super",
    "profile": 1
  }
}
```

### Admin Events
```
GET    /admin/events              Get all events (paginated)
POST   /admin/events              Create event
GET    /admin/events/{id}         Get single event
PUT    /admin/events/{id}         Update event
DELETE /admin/events/{id}         Delete event (profile <= 1)
PATCH  /admin/events/{id}/publish Toggle publication
PATCH  /admin/events/{id}/app     Toggle app visibility
DELETE /admin/events/bulk         Bulk delete events
```

**Query Parameters (GET list):**
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 20, max: 100)
- `search` - Search in libelle/lieu

### Admin Statistics
```
GET /admin/stats/filters          Get available filters
GET /admin/stats/data             Get statistics data
GET /admin/stats/export/xlsx      Export as Excel
GET /admin/stats/export/pdf       Export as PDF
```

**Query Parameters (filters):**
- `season` - Season code (optional, uses active season if not provided)

**Query Parameters (data/export):**
- `season` - Season code
- `type` - Stat type (Buteurs, Attaque, Defense, etc.)
- `competitions[]` - Array of competition codes
- `limit` - Max results (1-500, default: 30)
- `labels` - JSON-encoded column labels for export (optional)
- `title` - Translated stat type name for export (optional)
- `timezone` - User timezone for PDF date (optional)
- `locale` - User locale for PDF translations (optional)

**Available Stat Types:**
- `Buteurs` - Top scorers
- `Attaque` - Team attack stats
- `Defense` - Team defense stats
- `Cartons` - Individual cards
- `CartonsEquipe` - Team cards
- `CartonsCompetition` - Competition cards summary
- `Fairplay` - Individual fairplay score
- `FairplayEquipe` - Team fairplay score
- `Arbitrage` - Individual refereeing stats
- `ArbitrageEquipe` - Team refereeing stats
- `CJouees` - Matches played (by club)
- `CJouees2` - Matches played (by team)
- `CJouees3` - Irregularities (profile <= 6)
- `CJoueesN` - National competitions
- `CJoueesCF` - French Cup
- `OfficielsJournees` - Officials per matchday
- `OfficielsMatchs` - Officials per match
- `ListeArbitres` - Referees list
- `ListeEquipes` - Teams list
- `ListeJoueurs` - Players list
- `ListeJoueurs2` - Players & coaches list
- `LicenciesNationaux` - National licensees (profile <= 6)
- `CoherenceMatchs` - Match consistency check (profile <= 6)

**Example with curl:**
```bash
# Get filters
curl -X GET "https://kpi.localhost/api2/admin/stats/filters?season=2025" \
  -H "Authorization: Bearer eyJ..."

# Get data
curl -X GET "https://kpi.localhost/api2/admin/stats/data?season=2025&type=Buteurs&competitions[]=N1&competitions[]=N2&limit=50" \
  -H "Authorization: Bearer eyJ..."

# Export Excel
curl -X GET "https://kpi.localhost/api2/admin/stats/export/xlsx?season=2025&type=Buteurs&competitions[]=N1" \
  -H "Authorization: Bearer eyJ..." \
  -o stats.xlsx
```

### Admin Competitions
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

**Query Parameters (GET list):**
- `season` - Season code (required)
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 50)
- `search` - Search in libelle/code
- `level` - Filter by level (INT/NAT/REG)
- `type` - Filter by type (CHPT/CP/MULTI)
- `sortBy` - Sort field (default: section)
- `sortOrder` - Sort direction (ASC/DESC)

**Response Fields:**
```json
{
  "items": [{
    "code": "N1H",
    "codeSaison": "2025",
    "codeNiveau": "NAT",
    "libelle": "Nationale 1 Hommes",
    "soustitre": null,
    "soustitre2": null,
    "codeRef": "NAT1",
    "groupOrder": 1,
    "codeTypeclt": "CHPT",
    "codeTour": 1,
    "qualifies": 3,
    "elimines": 0,
    "points": "4-2-1-0",
    "goalaverage": "gen",
    "statut": "ON",
    "publication": true,
    "verrou": false,
    "nbEquipes": 12,
    "nbJournees": 6,
    "nbMatchs": 66,
    "hasRc": true,
    "section": 1,
    "sectionLabel": "France Nationale",
    "web": null,
    "enActif": true,
    "titreActif": true,
    "bandeauActif": true,
    "logoActif": true,
    "sponsorActif": true,
    "kpiFfckActif": true,
    "pointsGrid": null,
    "multiCompetitions": null,
    "rankingStructureType": null,
    "commentairesCompet": null
  }],
  "total": 45,
  "page": 1,
  "limit": 50,
  "totalPages": 1
}
```

**Delete Validation:**
A competition can only be deleted if:
- `nbEquipes === 0` (no teams)
- `nbJournees === 0` (no gamedays/phases)
- `nbMatchs === 0` (no matches)

**Status Change (PATCH /status):**
```json
{
  "statut": "ON"
}
```
Valid values: `ATT` (Pending), `ON` (Ongoing), `END` (Finished)

## HTTP Status Codes

- `200` - Success
- `204` - No content (successful delete)
- `400` - Bad request / Game locked
- `401` - Unauthorized / Invalid action
- `403` - Forbidden / Invalid mode / Insufficient permissions
- `404` - Not found
- `405` - Method not allowed / Invalid data
- `409` - Conflict (cannot delete: has dependencies)

## CORS

Cross-Origin Resource Sharing is enabled for:
- `localhost`
- `127.0.0.1`
- `*.local` domains

Allowed methods: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`

## Error Routes

The `/_error/` routes are internal Symfony routes used for error handling:
- `/_error/{code}` - Displays error pages (404, 500, etc.) in development mode
- These routes are NOT part of the public API
- In production, custom error pages should be configured via `config/packages/framework.yaml`

## Migration from Legacy API

| Legacy Endpoint | New Endpoint |
|----------------|--------------|
| `/api/login` | `/api2/login` |
| `/api/events/{mode}` | `/api2/events/{mode}` |
| `/api/event/{id}` | `/api2/event/{id}` |
| `/api/games/{eventId}` | `/api2/games/{eventId}` |
| `/api/charts/{eventId}` | `/api2/charts/{eventId}` |
| `/api/team-stats/{teamId}/{eventId}` | `/api2/team-stats/{teamId}/{eventId}` |
| `/api/stars` | `/api2/stars` |
| `/api/rating` | `/api2/rating` |
| `/api/staff/{token}/teams/{eventId}` | `/api2/staff/{eventId}/teams` + `X-Auth-Token` header |
| `/api/staff/{token}/players/{teamId}` | `/api2/staff/{eventId}/team/{teamId}/players` + `X-Auth-Token` header |
| `/api/staff/{token}/player/{playerId}/team/{teamId}/{param}/{value}` | `/api2/staff/{eventId}/team/{teamId}/player/{playerId}/{param}/{value}` + `X-Auth-Token` header |
| `/api/staff/{token}/player/{playerId}/team/{teamId}/comment` | `/api2/staff/{eventId}/team/{teamId}/player/{playerId}/comment` + `X-Auth-Token` header |
| `/api/report/{token}/game/{gameId}` | `/api2/report/game/{gameId}` + `X-Auth-Token` header |
| `/api/wsm/*` | `/api2/wsm/*` |
| `GestionCompetition.php` | `/api2/admin/competitions/*` (app4 page) |

**Important differences:**
- **Authentication:** API2 uses `X-Auth-Token` header instead of token in URL for Staff/Report endpoints
- **URL Structure:** Staff routes now include `eventId` at the beginning and follow RESTful patterns (`/staff/{eventId}/team/{teamId}/...`)
- **Caching:** All staff endpoints return fresh data with no-cache headers
- The `/api2/api` or `/api2/doc` URL is only for the API Platform UI documentation. The actual API endpoints use `/api2/` directly.

All endpoints return the same JSON structure as the legacy API.
