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
GET  /event/{eventId}/games        Get games for an event
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
GET  /event/{eventId}/charts       Get rankings and brackets for an event
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

### Groups API (Season-based)
```
GET  /groups/{season}                          Get competition groups for a season
GET  /group/{season}/{groupCode}/games         Get games for a competition group
GET  /group/{season}/{groupCode}/charts        Get rankings and brackets for a group
GET  /group/{season}/{groupCode}/teams         Get teams for a competition group
```

### Statistics & Ratings API
```
GET  /event/{eventId}/team/{teamId}/stats      Get team statistics
GET  /stars                                     Get app ratings
POST /rating                                    Submit app rating
```

**Rating Request:**
```json
{
  "uid": "550e8400-e29b-41d4-a716-446655440000",
  "stars": 4
}
```

### Game Sheet API (Public Share)
```
GET  /game-sheet/{gameId}    Get complete game sheet data (public)
```

**Response includes:**
- Game information (score, date, teams, referees)
- Team A and B compositions with player stats
- Match events timeline (goals, cards)
- Halftime score and stats summary

Only available for games with status `ON` or `END` and published competition/gameday/match.

## Authentication

### Login (Token-based, for App2)
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

### JWT Authentication (for App4 Admin)
```
POST /auth/login             Login and get JWT token
GET  /auth/me                Get current authenticated user info
POST /auth/refresh           Refresh JWT token
```

**Login Request:**
```json
{
  "username": "admin",
  "password": "password"
}
```

**Login Response:**
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

**Token transmission:** Use `Authorization: Bearer {token}` header for all admin endpoints.

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

### Admin Events
```
GET    /admin/events              Get all events (paginated)
POST   /admin/events              Create event
GET    /admin/events/{id}         Get single event
PUT    /admin/events/{id}         Update event
DELETE /admin/events/{id}         Delete event (profile <= 1)
PATCH  /admin/events/{id}/publish Toggle publication
PATCH  /admin/events/{id}/app     Toggle app visibility
POST   /admin/events/bulk-delete  Bulk delete events (profile <= 1)
```

**Query Parameters (GET list):**
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 20, max: 100)
- `search` - Search in libelle/lieu

### Admin Competitions
```
GET    /admin/competitions                                   List competitions (paginated)
GET    /admin/competitions/{code}                            Get single competition
POST   /admin/competitions                                   Create competition (profile <=3)
PUT    /admin/competitions/{code}                            Update competition (profile <=3)
DELETE /admin/competitions/{code}                            Delete competition (profile <=2)
POST   /admin/competitions/bulk-delete                      Bulk delete (profile <=2)
PATCH  /admin/competitions/{code}/publish                   Toggle publication (profile <=4)
PATCH  /admin/competitions/{code}/lock                      Toggle lock (profile <=3)
PATCH  /admin/competitions/{code}/status                    Change status ATT/ON/END (profile <=3)
GET    /admin/competitions-groups                            List groups for select
GET    /admin/competitions-for-multi                         List competitions for MULTI select
GET    /admin/competitions/-search-previous-seasons          Search competitions from previous seasons
GET    /admin/competitions/-from-previous-season/{code}/{seasonCode}  Get competition from previous season
GET    /admin/competitions/-schemas                         Search competition schemas by nb teams (profile <=3)
GET    /admin/competitions/{season}/{code}/copy-detail      Get competition copy detail (profile <=3)
GET    /admin/competitions/-options                         List competitions for destination dropdown (profile <=3)
POST   /admin/competitions/-copy                            Copy competition structure (profile <=3)
PATCH  /admin/competitions/{season}/{code}/comments         Update competition comments (profile <=3)
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

### Admin Filters
```
GET  /admin/filters/seasons              Get available seasons (filtered by user)
GET  /admin/filters/competitions         Get competitions for a season (grouped by section)
GET  /admin/filters/events               Get events for a season
GET  /admin/filters/match-ids            Get match IDs for a competition
GET  /admin/filters/event-competitions   Get competitions linked to an event
```

**Query Parameters:**
- `/competitions`: `season` (optional, uses active season)
- `/events`: `season` (optional)
- `/match-ids`: `season` (required), `competition` (required)
- `/event-competitions`: `eventId` (required)

All filter endpoints respect user restrictions (allowed seasons/competitions/events).

### Admin Gamedays
```
GET    /admin/gamedays                         List gamedays (paginated, filtered)
GET    /admin/gamedays/{id}                    Get single gameday
POST   /admin/gamedays                         Create gameday (profile <=4)
PUT    /admin/gamedays/{id}                    Update gameday (profile <=4)
DELETE /admin/gamedays/{id}                    Delete gameday (profile <=4)
PATCH  /admin/gamedays/{id}/publication        Toggle publication (profile <=4)
PATCH  /admin/gamedays/{id}/type               Toggle type C/E (profile <=4)
PATCH  /admin/gamedays/{id}/inline             Inline field update (profile <=4)
POST   /admin/gamedays/{id}/duplicate          Duplicate gameday (profile <=4)
PATCH  /admin/gamedays/bulk/publication        Bulk toggle publication (profile <=4)
PATCH  /admin/gamedays/bulk/calendar           Bulk calendar update (profile <=4)
DELETE /admin/gamedays/bulk                    Bulk delete gamedays (profile <=4)
PUT    /admin/gamedays/{id}/event/{eventId}    Link gameday to event
DELETE /admin/gamedays/{id}/event/{eventId}    Unlink gameday from event
GET    /admin/gamedays/events                  List events (for filter dropdown)
GET    /admin/gamedays/autocomplete/names      Autocomplete gameday names
GET    /admin/gamedays/autocomplete/communes   Autocomplete commune names
PATCH  /admin/gamedays/bulk/officials          Bulk update officials (profile <=4)
```

**Query Parameters (GET list):**
- `season` - Season code (uses active season if empty)
- `competitions` - Comma-separated competition codes
- `event` - Event ID filter
- `month` - Month number filter (1-12)
- `search` - Search in id/phase/nom/lieu
- `sort` - Sort mode: `date_asc` (default), `date_desc`, `name`, `number`, `level`
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 25, max: 200)

**Inline Update Request:**
```json
{
  "field": "Phase",
  "value": "Poules"
}
```
Allowed inline fields: `Phase`, `Niveau`, `Etape`, `Nbequipes`, `Nom`, `Date_debut`, `Date_fin`, `Lieu`, `Departement`

**Bulk Calendar Update:**
```json
{
  "ids": [1, 2, 3],
  "nom": "Phase 1",
  "dateDebut": "2025-01-15",
  "dateFin": "2025-01-16",
  "lieu": "Paris",
  "departement": "75"
}
```

**Delete Validation:**
A gameday can only be deleted if:
- No matches exist (`matchCount === 0`)
- No event associations exist

### Admin Teams
```
GET    /admin/competition-teams                       List teams for a competition
POST   /admin/competition-teams                       Add team(s) to competition (profile <=3)
DELETE /admin/competition-teams/{id}                  Delete team from competition (profile <=3)
POST   /admin/competition-teams/bulk-delete           Bulk delete teams (profile <=3)
PATCH  /admin/competition-teams/{id}/pool-draw        Update pool and draw (profile <=6)
PATCH  /admin/competition-teams/{id}/colors           Update team colors and logo (profile <=2)
POST   /admin/competition-teams/duplicate             Duplicate teams from another competition (profile <=3)
POST   /admin/competition-teams/update-logos          Auto-update logos from files (profile <=2)
POST   /admin/competition-teams/init-starters         Initialize starters for competition (profile <=4)
PATCH  /admin/competition-teams/toggle-lock           Toggle competition lock (profile <=4)
GET    /admin/teams/search                            Search historical teams (profile <=3)
GET    /admin/teams/{numero}/compositions             Get available compositions for a team (profile <=3)
GET    /admin/clubs/search                            Search clubs autocomplete (profile <=3)
GET    /admin/regional-committees                     List regional committees
GET    /admin/departmental-committees                 List departmental committees
GET    /admin/clubs                                   List clubs
```

**Query Parameters:**
- `/competition-teams`: `season` (required), `competition` (required)
- `/teams/search`: `q` (min 2 chars), `limit` (default: 20, max: 50)
- `/clubs/search`: `q` (min 2 chars), `limit` (default: 20, max: 50)
- `/departmental-committees`: `cr` (optional, filter by regional committee)
- `/clubs`: `cd` (optional, filter by departmental committee)

**Add Team Request (manual mode):**
```json
{
  "season": "2025",
  "competition": "N1H",
  "mode": "manual",
  "libelle": "Team Name",
  "codeClub": "1234",
  "poule": "A",
  "tirage": 1
}
```

**Add Team Request (history mode):**
```json
{
  "season": "2025",
  "competition": "N1H",
  "mode": "history",
  "teamNumbers": [123, 456],
  "poule": "A",
  "tirage": 0,
  "copyComposition": {
    "season": "2024",
    "competition": "N1H"
  }
}
```

**Colors Update Request:**
```json
{
  "logo": "KIP/logo/1234-logo.png",
  "color1": "#FF0000",
  "color2": "#0000FF",
  "colortext": "#FFFFFF",
  "propagateNext": true,
  "propagatePrevious": false,
  "propagateClub": false
}
```

**Delete Validation:**
A team can only be deleted if it has no played/validated matches.

### Admin Groups
```
GET    /admin/groups                   List all groups (with competition count)
POST   /admin/groups                   Create group
PUT    /admin/groups/{id}              Update group
DELETE /admin/groups/{id}              Delete group (Super Admin only)
PATCH  /admin/groups/{id}/reorder      Reorder group (move up/down)
```

**Query Parameters (GET list):**
- `search` - Search in groupe/libelle/libelle_en
- `section` - Filter by section number

**Create/Update Request:**
```json
{
  "section": 2,
  "ordre": 5,
  "codeNiveau": "NAT",
  "groupe": "N1H",
  "libelle": "Nationale 1 Hommes",
  "libelleEn": "National 1 Men"
}
```

**Reorder Request:**
```json
{
  "direction": "up"
}
```
Valid directions: `up`, `down`

**Valid sections:** 1 (International), 2 (National), 3 (Regional), 4 (Tournoi), 5 (Continental), 100 (Divers)

**Valid niveaux:** `REG`, `NAT`, `INT`

**Delete Validation:**
A group can only be deleted if no competitions are linked to it.

### Admin Presence (Player Compositions)
```
GET    /admin/teams/{teamId}/players            Get team players composition
POST   /admin/teams/{teamId}/players/add        Add player to team composition
PATCH  /admin/teams/{teamId}/players/{matric}   Update player (numero/capitaine)
DELETE /admin/teams/{teamId}/players            Delete players from composition
GET    /admin/players/search                    Search players by name or matric
GET    /admin/teams/{teamId}/compositions       Get available compositions for copy
POST   /admin/teams/{teamId}/players/copy       Copy composition from another team
```

**Add Player Request (existing):**
```json
{
  "mode": "existing",
  "matric": 123456,
  "numero": 7,
  "capitaine": "-"
}
```

**Add Player Request (create new):**
```json
{
  "mode": "create",
  "nom": "Dupont",
  "prenom": "Jean",
  "sexe": "M",
  "naissance": "2000-05-15",
  "numicf": null,
  "arbitre": "",
  "numero": 0,
  "capitaine": "-"
}
```

**Update Player Request:**
```json
{
  "numero": 10,
  "capitaine": "C"
}
```

**Delete Players Request:**
```json
{
  "matricIds": [123456, 789012]
}
```

**Copy Composition Request:**
```json
{
  "sourceCompetition": "N2H",
  "sourceSeason": "2025"
}
```

**Capitaine values:** `-` (player), `C` (captain), `E` (coach), `A` (absent), `X` (excluded)

### Admin RC (Competition Officials)
```
GET    /admin/rc                     List RC for a season
POST   /admin/rc                     Create RC
PUT    /admin/rc/{id}                Update RC
DELETE /admin/rc                     Delete RC (bulk, Super Admin)
POST   /admin/rc/bulk-delete         Bulk delete RC (Super Admin)
```

**Query Parameters (GET list):**
- `season` (required) - Season code
- `competitions` (optional) - Comma-separated competition codes filter

**Create/Update Request:**
```json
{
  "season": "2025",
  "competitionCode": "N1H",
  "matric": 123456,
  "ordre": 1
}
```

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

### Admin Operations (Super Admin)
```
GET    /admin/operations/seasons                              List seasons
POST   /admin/operations/seasons                              Add season
PATCH  /admin/operations/seasons/{code}/activate              Activate season
POST   /admin/operations/seasons/copy-rc                      Copy RC from one season to another
POST   /admin/operations/seasons/copy-competitions            Copy competitions between seasons
GET    /admin/operations/seasons/{code}/competitions          Get competitions for a season
GET    /admin/operations/images/types                         Get image types configuration
POST   /admin/operations/images/upload                        Upload image (multipart/form-data)
POST   /admin/operations/images/rename                        Rename image
POST   /admin/operations/players/merge                        Merge two players
POST   /admin/operations/players/auto-merge                   Auto-merge non-federal players
GET    /admin/operations/autocomplete/players                 Search players autocomplete
POST   /admin/operations/teams/rename                         Rename team
POST   /admin/operations/teams/merge                          Merge two teams
POST   /admin/operations/teams/move                           Move team to another club
GET    /admin/operations/autocomplete/teams                   Search teams autocomplete
GET    /admin/operations/autocomplete/clubs                   Search clubs autocomplete
POST   /admin/operations/codes/change                         Change competition/club code
GET    /admin/operations/events/{id}/export                   Export event as JSON
POST   /admin/operations/events/{id}/import                   Import event from JSON
POST   /admin/operations/licenses/import-pce                  Import PCE license file
POST   /admin/operations/cache/purge                          Purge cache files
```

**Add Season Request:**
```json
{
  "code": "2026",
  "natDebut": "2025-09-01",
  "natFin": "2026-07-31",
  "interDebut": "2026-01-01",
  "interFin": "2026-12-31"
}
```

**Copy RC Request:**
```json
{
  "sourceCode": "2025",
  "targetCode": "2026"
}
```

**Copy Competitions Request:**
```json
{
  "sourceCode": "2025",
  "targetCode": "2026",
  "competitionCodes": ["N1H", "N2H"],
  "copyMatches": false
}
```

**Merge Players Request:**
```json
{
  "sourceMatric": 123456,
  "targetMatric": 789012
}
```

**Merge Teams Request:**
```json
{
  "sourceId": 100,
  "targetId": 200
}
```

**Move Team Request:**
```json
{
  "teamId": 100,
  "clubCode": "1234"
}
```

**Change Code Request:**
```json
{
  "sourceCode": "N1H",
  "targetCode": "N1M",
  "allSeasons": false,
  "targetExists": false
}
```

**Image Upload (multipart/form-data):**
- `imageType` - Type: `logo_competition`, `bandeau_competition`, `sponsor_competition`, `logo_club`, `logo_nation`
- `imageFile` - The image file
- Additional params depending on type: `codeCompetition`, `saison`, `numeroClub`, `codeNation`

### Admin Athletes
```
GET    /admin/athletes/search                    Search athletes by name/firstname/licence (autocomplete)
GET    /admin/athletes/{matric}                   Get full athlete profile
GET    /admin/athletes/{matric}/participations    Get athlete participations for a season
PUT    /admin/athletes/{matric}                   Update athlete (profile <=2, Matric > 2000000)
```

**Query Parameters:**
- `/athletes/search`: `q` (min 2 chars), `limit` (default: 20, max: 50)
- `/athletes/{matric}/participations`: `season` (required)

**Search Response:**
```json
[
  {
    "matric": 63155,
    "nom": "VIGNET",
    "prenom": "Eric",
    "sexe": "M",
    "naissance": "1972-10-06",
    "club": "CK LE HAVRE",
    "codeClub": "7603",
    "label": "VIGNET Eric (63155) - CK LE HAVRE"
  }
]
```

**Update Request (PUT):**
```json
{
  "nom": "VIGNET",
  "prenom": "ERIC",
  "sexe": "M",
  "naissance": "1972-10-06",
  "origine": "2026",
  "icf": 12345,
  "arbitrage": {
    "qualification": "Nat",
    "niveau": "C"
  },
  "codeClub": "7603"
}
```

**Update Restrictions:**
- Only athletes with Matric > 2000000 (non-federal) can be modified
- Matric <= 2000000 returns 403 Forbidden

### Admin Rankings
```
GET    /admin/rankings                              Get rankings for a competition (profile <= 10)
POST   /admin/rankings/compute                      Recalculate ranking (profile <= 6)
POST   /admin/rankings/publish                      Publish ranking (profile <= 4)
DELETE /admin/rankings/publish                      Unpublish ranking (profile <= 3)
PATCH  /admin/rankings/{teamId}/inline              Inline edit a ranking value (profile <= 4)
PATCH  /admin/rankings/consolidation/{journeeId}    Toggle phase consolidation (profile <= 4)
DELETE /admin/rankings/phase-team/{journeeId}/{teamId}  Remove team from phase (profile <= 4)
POST   /admin/rankings/transfer                     Transfer teams to another competition (profile <= 4)
GET    /admin/rankings/transfer-competitions        Get available target competitions (profile <= 4)
GET    /admin/rankings/initial                      Get initial ranking values (profile <= 6)
PATCH  /admin/rankings/initial/{teamId}             Edit initial ranking value (profile <= 6)
POST   /admin/rankings/initial/reset                Reset initial ranking values (profile <= 6)
```

**Query Parameters (GET /admin/rankings):**
- `season` - Season code (required)
- `competition` - Competition code (required)
- `type` - Force ranking type: `CHPT`, `CP`, `MULTI` (optional, defaults to competition type)

**Query Parameters (GET /admin/rankings/transfer-competitions):**
- `season` - Target season code (required)

**Query Parameters (GET /admin/rankings/initial):**
- `season` - Season code (required)
- `competition` - Competition code (required)

**Compute Request (POST /admin/rankings/compute):**
```json
{
  "season": "2025",
  "competition": "ECM",
  "includeUnlocked": true
}
```

**Publish/Unpublish Request (POST or DELETE /admin/rankings/publish):**
```json
{
  "season": "2025",
  "competition": "ECM"
}
```

**Inline Edit Request (PATCH /admin/rankings/{teamId}/inline):**
```json
{
  "field": "Pts",
  "value": 900,
  "journeeId": null
}
```
- If `journeeId` is provided, edits `kp_competition_equipe_journee`; otherwise `kp_competition_equipe`
- Allowed fields: `Clt`, `Pts`, `J`, `G`, `N`, `P`, `F`, `Plus`, `Moins`, `Diff`, `CltNiveau`, `PtsNiveau`
- `Pts` must be sent × 100 (frontend multiplies before sending)

**Consolidation Toggle Request (PATCH /admin/rankings/consolidation/{journeeId}):**
```json
{
  "consolidation": true
}
```

**Transfer Request (POST /admin/rankings/transfer):**
```json
{
  "teamIds": [1234, 5678],
  "targetSeason": "2026",
  "targetCompetition": "ECM"
}
```

**Transfer Response:**
```json
{
  "transferred": 2,
  "skipped": 0,
  "details": [
    { "teamId": 1234, "libelle": "ESP Men", "status": "created", "newId": 9876 },
    { "teamId": 5678, "libelle": "ITA Men", "status": "created", "newId": 9877 }
  ]
}
```

**Initial Ranking Edit Request (PATCH /admin/rankings/initial/{teamId}):**
```json
{
  "field": "Pts",
  "value": 5
}
```
- Allowed fields: `Clt`, `Pts`, `J`, `G`, `N`, `P`, `F`, `Plus`, `Moins`, `Diff`
- Values stored as-is (not × 100); multiplication by 100 happens at compute time

**Initial Ranking Reset Request (POST /admin/rankings/initial/reset):**
```json
{
  "season": "2025",
  "competition": "ECM"
}
```

**Validations:**
- All write operations require competition status `ON` (returns 403 otherwise)
- Phase consolidation toggle: competition must be `ON`
- Phase team removal: team must have no played matches in the phase (`J = 0`)
- Transfer: source competition must differ from target; at least one team selected

### Admin Games
```
GET    /admin/games                                    List games (paginated, filtered)
GET    /admin/games/{id}                               Get single game
POST   /admin/games                                    Create game (profile <=4)
PUT    /admin/games/{id}                               Update game (profile <=4)
DELETE /admin/games/{id}                               Delete game (profile <=4)
PATCH  /admin/games/{id}/inline                        Inline field update (profile <=4)
PATCH  /admin/games/{id}/publication                   Toggle publication (profile <=4)
PATCH  /admin/games/{id}/validation                    Toggle validation (profile <=4)
PATCH  /admin/games/{id}/type                          Toggle type (profile <=4)
PATCH  /admin/games/{id}/statut                        Toggle statut (profile <=4)
PATCH  /admin/games/{id}/printed                       Toggle printed (profile <=4)
PATCH  /admin/games/{id}/team                          Change team (profile <=4)
PATCH  /admin/games/{id}/journee                       Change journee (profile <=4)
DELETE /admin/games/bulk                               Bulk delete games (profile <=4)
PATCH  /admin/games/bulk/publication                   Bulk toggle publication (profile <=4)
PATCH  /admin/games/bulk/validation                    Bulk toggle validation (profile <=4)
PATCH  /admin/games/bulk/lock-publish                  Bulk lock & publish (profile <=4)
PATCH  /admin/games/bulk/journee                       Bulk change journee (profile <=4)
PATCH  /admin/games/bulk/renumber                      Bulk renumber games (profile <=4)
PATCH  /admin/games/bulk/date                          Bulk change date (profile <=4)
PATCH  /admin/games/bulk/time                          Bulk change time (profile <=4)
PATCH  /admin/games/bulk/group                         Bulk change group (profile <=4)
GET    /admin/games/teams                              Get teams for game selects
GET    /admin/games/journees                           Get journees for game selects
GET    /admin/games/events                             Get events for game selects
GET    /admin/games/autocomplete/referees              Autocomplete referees
```

### Admin Users
```
GET    /admin/users                                    List users (paginated)
GET    /admin/users/{code}                             Get single user
POST   /admin/users                                    Create user (profile <=2)
PUT    /admin/users/{code}                             Update user (profile <=2)
DELETE /admin/users/{code}                             Delete user (profile <=1)
POST   /admin/users/bulk-delete                        Bulk delete users (profile <=1)
POST   /admin/users/{code}/reset-password              Reset user password (profile <=2)
GET    /admin/users/{code}/mandats                     List user mandates
POST   /admin/users/{code}/mandats                     Create mandate (profile <=2)
PUT    /admin/users/{code}/mandats/{id}                Update mandate (profile <=2)
DELETE /admin/users/{code}/mandats/{id}                Delete mandate (profile <=2)
```

### Admin Clubs
```
GET    /admin/clubs/search-all                         Search all clubs (paginated, with map data)
GET    /admin/clubs/map                                Get clubs for map display (with coordinates)
GET    /admin/clubs/{code}                             Get club detail
PATCH  /admin/clubs/{code}                             Update club (profile <=2)
POST   /admin/clubs                                    Create club (profile <=2)
GET    /admin/clubs/{code}/teams                       Get club teams
GET    /admin/teams/{numero}                           Get team detail
POST   /admin/departmental-committees                  Create departmental committee (profile <=2)
```

### Admin TV Control
```
GET    /admin/tv/events                                List events for TV control
GET    /admin/tv/matches                               List matches for an event
POST   /admin/tv/activate                              Activate a presentation on a channel
POST   /admin/tv/blank                                 Blank a channel (display nothing)
GET    /admin/tv/labels                                Get custom labels for a channel
PUT    /admin/tv/labels                                Update custom labels for a channel
GET    /admin/tv/scenario/{scenarioNumber}             Get scenario configuration
PUT    /admin/tv/scenario/{scenarioNumber}             Update scenario configuration
```

### Admin Schema
```
GET    /admin/schema                                   Get competition schema (phases, brackets, pools)
```

**Query Parameters:**
- `season` - Season code (required)
- `competition` - Competition code (required)

Returns the full competition schema with gamedays, matches, teams, pools, and brackets for CHPT and CP types.

### Admin Journal
```
GET    /admin/journal                                  List journal entries (paginated, filtered)
GET    /admin/journal/users                            List users for journal filter dropdown
GET    /admin/journal/actions                          List actions for journal filter dropdown
```

**Query Parameters (GET list):**
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 50)
- `user` - Filter by user code
- `action` - Filter by action type
- `dateFrom` - Filter from date
- `dateTo` - Filter to date

### Admin Auth (Mandates)
```
GET    /auth/mandates                                  Get available mandates for current user
POST   /auth/switch-mandate                            Switch active mandate
POST   /auth/reset-password                            Request password reset
```

### Admin Presence (Match Mode)
```
GET    /admin/matches/{matchId}/players                Get match players composition
POST   /admin/matches/{matchId}/players/add            Add player to match composition
PATCH  /admin/matches/{matchId}/players/{matric}       Update player (numero/capitaine)
DELETE /admin/matches/{matchId}/players                 Delete players from match composition
DELETE /admin/matches/{matchId}/players/clear           Clear all players from match
POST   /admin/matches/{matchId}/players/initialize     Initialize from team composition
POST   /admin/matches/{matchId}/players/copy-to-competition  Copy match players to competition team
POST   /admin/matches/{matchId}/players/copy-to-day    Copy match players to all matches of the day
GET    /admin/matches/{matchId}/copyable-matches       Get matches available for composition copy
```

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `204` - No content (successful delete)
- `400` - Bad request / Game locked
- `401` - Unauthorized / Invalid action
- `403` - Forbidden / Invalid mode / Insufficient permissions
- `404` - Not found
- `405` - Method not allowed / Invalid data
- `409` - Conflict (cannot delete: has dependencies)
- `422` - Unprocessable entity (validation error, e.g. duplicate code)

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
| `/api/games/{eventId}` | `/api2/event/{eventId}/games` |
| `/api/charts/{eventId}` | `/api2/event/{eventId}/charts` |
| `/api/team-stats/{teamId}/{eventId}` | `/api2/event/{eventId}/team/{teamId}/stats` |
| `/api/stars` | `/api2/stars` |
| `/api/rating` | `/api2/rating` |
| `/api/staff/{token}/teams/{eventId}` | `/api2/staff/{eventId}/teams` + `X-Auth-Token` header |
| `/api/staff/{token}/players/{teamId}` | `/api2/staff/{eventId}/team/{teamId}/players` + `X-Auth-Token` header |
| `/api/staff/{token}/player/{playerId}/team/{teamId}/{param}/{value}` | `/api2/staff/{eventId}/team/{teamId}/player/{playerId}/{param}/{value}` + `X-Auth-Token` header |
| `/api/staff/{token}/player/{playerId}/team/{teamId}/comment` | `/api2/staff/{eventId}/team/{teamId}/player/{playerId}/comment` + `X-Auth-Token` header |
| `/api/report/{token}/game/{gameId}` | `/api2/report/game/{gameId}` + `X-Auth-Token` header |
| `/api/wsm/*` | `/api2/wsm/*` |
| `GestionCompetition.php` | `/api2/admin/competitions/*` |
| `GestionJournee.php` | `/api2/admin/gamedays/*` |
| `GestionEquipe.php` | `/api2/admin/competition-teams/*` |
| `GestionEquipeJoueur.php` | `/api2/admin/teams/{teamId}/players/*` |
| `GestionRc.php` | `/api2/admin/rc/*` |
| `GestionOperations.php` | `/api2/admin/operations/*` |
| `GestionAthlete.php` | `/api2/admin/athletes/*` |
| `GestionClassement.php` | `/api2/admin/rankings/*` |
| `GestionClassementInit.php` | `/api2/admin/rankings/initial/*` |
| `GestionCalendrier.php` | `/api2/admin/games/*` |
| `GestionMatchEquipeJoueur.php` | `/api2/admin/matches/{matchId}/players/*` |
| `GestionUtilisateur.php` | `/api2/admin/users/*` |
| `GestionStructure.php` | `/api2/admin/clubs/*` |
| `GestionSchema.php` | `/api2/admin/schema` |
| `GestionJournal.php` | `/api2/admin/journal/*` |
| TV Control (legacy) | `/api2/admin/tv/*` |

**Important differences:**
- **Authentication:** API2 uses `X-Auth-Token` header instead of token in URL for Staff/Report endpoints
- **JWT for Admin:** App4 admin uses JWT tokens via `Authorization: Bearer` header
- **URL Structure:** Staff routes now include `eventId` at the beginning and follow RESTful patterns (`/staff/{eventId}/team/{teamId}/...`)
- **URL Changes:** Games/Charts/Team-stats routes now use `/event/{eventId}/` prefix pattern
- **Groups API:** New season-based group endpoints (`/groups/{season}`, `/group/{season}/{groupCode}/...`)
- **Caching:** All staff endpoints return fresh data with no-cache headers
- The `/api2/api` or `/api2/doc` URL is only for the API Platform UI documentation. The actual API endpoints use `/api2/` directly.

All endpoints return the same JSON structure as the legacy API.
