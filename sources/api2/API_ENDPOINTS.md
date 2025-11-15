# API2 Endpoints - Quick Reference

## Base URL
- Development: `https://kpi.localhost/api2/`
- API Platform UI: `https://kpi.localhost/api2/api`

## Public Endpoints

### Events API
```
GET  /api/events/{mode}          Get events (mode: std|champ|all)
GET  /api/event/{id}             Get single event
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
GET  /api/games/{eventId}        Get games for an event
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
GET  /api/charts/{eventId}       Get rankings and brackets
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
GET  /api/team-stats/{teamId}/{eventId}    Get team statistics
GET  /api/stars                             Get app ratings
POST /api/rating                            Submit app rating
```

**Rating Request:**
```json
{
  "uid": "550e8400-e29b-41d4-a716-446655440000",
  "stars": 4
}
```

## Staff Endpoints (Require Token)

### Scrutineering API
```
GET  /api/staff/{token}/test
GET  /api/staff/{token}/teams/{eventId}
GET  /api/staff/{token}/players/{teamId}
PUT  /api/staff/{token}/player/{playerId}/team/{teamId}/{parameter}/{value}
PUT  /api/staff/{token}/player/{playerId}/team/{teamId}/comment
```

**Parameters:**
- `kayak_status` - Kayak inspection status
- `vest_status` - Life vest inspection status
- `helmet_status` - Helmet inspection status
- `paddle_count` - Number of paddles

**Comment Request:**
```json
{
  "comment": "Equipment OK"
}
```

## Report Endpoints (Require Token)

### Match Report API
```
GET  /api/report/{token}/game/{gameId}    Get full game report
```

**Response includes:**
- Game information
- Teams and players
- Match events (goals, cards, etc.)

## WSM Endpoints (Web Score Management)

### Network & Configuration
```
PUT  /api/wsm/eventNetwork/{eventId}      Update event network
```

### Game Management
```
PUT  /api/wsm/gameParam/{matchId}         Update game parameters
PUT  /api/wsm/gameEvent/{matchId}         Add/remove match events
PUT  /api/wsm/playerStatus/{matchId}      Update player status
PUT  /api/wsm/gameTimer/{matchId}         Control match timer
PUT  /api/wsm/stats                       Add match statistics
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

## HTTP Status Codes

- `200` - Success
- `400` - Bad request / Game locked
- `401` - Unauthorized / Invalid action
- `403` - Forbidden / Invalid mode
- `405` - Method not allowed / Invalid data

## CORS

Cross-Origin Resource Sharing is enabled for:
- `localhost`
- `127.0.0.1`
- `*.local` domains

Allowed methods: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`

## Migration from Legacy API

| Legacy Endpoint | New Endpoint |
|----------------|--------------|
| `/api/events/{mode}` | `/api2/api/events/{mode}` |
| `/api/event/{id}` | `/api2/api/event/{id}` |
| `/api/games/{eventId}` | `/api2/api/games/{eventId}` |
| `/api/charts/{eventId}` | `/api2/api/charts/{eventId}` |
| `/api/team-stats/{teamId}/{eventId}` | `/api2/api/team-stats/{teamId}/{eventId}` |
| `/api/stars` | `/api2/api/stars` |
| `/api/rating` | `/api2/api/rating` |
| `/api/staff/{token}/*` | `/api2/api/staff/{token}/*` |
| `/api/report/{token}/*` | `/api2/api/report/{token}/*` |
| `/api/wsm/*` | `/api2/api/wsm/*` |

All endpoints return the same JSON structure as the legacy API.
