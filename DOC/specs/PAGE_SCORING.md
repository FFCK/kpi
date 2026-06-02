# Spécification — Scoring (console de match en direct) dans app4

> Statut : en cours — Phase 0 terminée, Phase 1 en cours (voir §11 Suivi)
> Cible : intégration dans **app4** (Nuxt 4, api2 Symfony)
> Remplace : `sources/admin/FeuilleMarque2.php`, `sources/admin/FeuilleMarque3.php`
>            (legacy jQuery) et le prototype standalone `sources/app3`
> Conserve : `sources/admin/FeuilleMatchMulti.php` (= **PDF de contrôle**, document papier)

## 1. Contexte et objectif

Le **Scoring** est l'outil de gestion du déroulé d'un match de kayak-polo : chronomètre,
shotclock, périodes (M1/M2/P1/P2/TB), saisie des événements (buts, cartons), pénalités,
diffusion vers écrans/incrustations, validation et verrouillage.

L'ancienne appellation « feuille de marque » n'a plus de sens : l'outil n'a quasiment plus
rien d'une feuille (la « feuille » papier est désormais un simple **PDF de contrôle**, cf.
`FeuilleMatchMulti.php`). Réglementairement, le match reste géré en priorité sur **feuille de
marque papier** + **panneau de score**, et **en parallèle ou après coup sur KPI**. L'objectif
KPI est de **tendre vers le zéro papier** :

- **saisie directe sur KPI** (Scoring) avec affichage scoreboard + shotclock ; ou
- **captation des live datas** depuis le matériel de scoring / panneau de score (BODET ou
  équivalent) ; puis **diffusion** via WebSocket et **incrustations** (`/live`).

### 1.1 Deux usages

1. **En direct** (table de marque pendant le match) : chrono, shotclock, périodes, événements,
   pénalités, diffusion.
2. **En post-match** : **saisie ou correction** après le déroulement, puis **validation /
   verrouillage** selon le profil.

### 1.2 Convention de nommage (décidée)

| Terme | Désigne | Usage code/UI |
|---|---|---|
| **Scoring** | La **console de saisie KPI** (saisie manuelle : chrono, score, buts/cartons). | Nom unique partout : route `/games/[id]/scoring`, `scoringStore`, api2 `ScoringController` / `/scoring`, libellé UI « Scoring ». |
| **Hardware Scoring** | La **captation des live datas** depuis le **matériel** (panneau de score BODET ou équivalent). **Qualificatif obligatoire** pour ne pas confondre avec la saisie manuelle. | `useHardwareScoring`, mode « Hardware Scoring » dans l'UI. |
| **WSM** (WebSocket Manager) | Brique `app_wsm` de **relai** matériel ↔ KPI (transport WebSocket). | Nom technique inchangé. |
| **broker** | Serveur WebSocket interne (https://github.com/laurentgarrigue/broker), même VPS que KPI. | Nom technique inchangé ; URL via `runtimeConfig.public`. |
| **Feuille de match** | Le **PDF de contrôle** papier (`FeuilleMatchMulti.php`). | Réservé au document imprimé, jamais à l'outil live. |

> Renommage : `WsmController` (annoté « Web Score Management » — traduction erronée de
> WebSocket Manager) sera renommé **`ScoringController`** car il sert la console de saisie, pas
> le relai matériel. Le relai matériel conserve l'appellation **WSM/broker**.

## 2. Pourquoi app4

- app4 possède déjà **JWT + rôles/mandats** (`stores/authStore.ts`, en-tête
  `X-Active-Mandate`), utilise **api2** et gère **games / teams / presence / rankings**.
- Le **backend de scoring existe déjà** dans api2 :
  `sources/api2/src/Controller/WsmController.php` (→ futur `ScoringController`) → `gameParam`
  (score/statut/période), `gameEvent` (buts/cartons B/V/J/R/D dans `kp_match_detail`),
  `gameTimer` (run/stop/RAZ via `kp_chrono`), `playerStatus`, `stats`, `eventNetwork`.
- Le **profil 9 « Table de marque » (`ROLE_SCORER`)** existe déjà dans
  `sources/api2/config/packages/security.yaml`.
- La **validation/verrouillage est déjà câblée** : `AdminGamesController::toggleValidation`
  (PATCH `/admin/games/{id}/validation`, bascule `Validation` O/N, journalisée) + actions bulk
  dans `app4/pages/games/index.vue`. Le Scoring **réutilise** ce mécanisme.
- Pattern de permissions réutilisable : `app4/composables/usePresencePermissions.ts`
  (mode `match`, seuils `authStore.profile`, verrou). Le Scoring est le **jumeau structurel**
  de la « presence sheet » (même modèle matchId/teamCode).

## 3. Cadrage retenu

| Sujet | Décision |
|---|---|
| Mode | **Online-first** (api2 + WebSocket). Offline/PWA **non bloquant** → dernière phase. |
| Usages | Direct **et** post-match (saisie/correction + validation/verrouillage par profil). |
| Captation matériel | Mode **Hardware Scoring** (panneau BODET ou équivalent via WSM/broker), distinct de la saisie manuelle. Branché en Phase 3. |
| Monétisation | À explorer plus tard. **Aucun Stripe/paywall maintenant.** Exigence unique : isolation **par mandat/organisation côté serveur** + gating par rôle via un composable unique. |
| Langues | **fr/en** uniquement (alignement app4). Le **cn** (présent dans app3) = chantier de suivi séparé sur toute app4. |
| Serveur WS | **broker** interne (même VPS), URL via `runtimeConfig.public.wsServerUrl`. |

## 4. Point de sécurité (vérifié)

Les endpoints `/wsm` d'**api2** (futur `/scoring`) tombent actuellement dans le firewall
`main` (**publics, sans JWT**, cf. `security.yaml`) → n'importe qui pourrait modifier un score.
**À corriger dès la Phase 1.**

**Vérification effectuée** : les consommateurs existants (`app_wsm`, `app_wsm_dev`,
`app_wsm_dev/src/network/liveApi.js`) appellent **`/api/wsm/...`** = **API legacy PHP**
(backend distinct), **pas** le `/wsm` d'api2. Sécuriser/renommer le `/wsm` d'api2 **n'impacte
aucun consommateur existant** (il n'est utilisé nulle part aujourd'hui). **Zéro régression.**

## 5. Architecture cible

```
app4 (Nuxt 4 SPA, origine /admin2)
 ├─ pages/games/[id]/scoring.vue      ← console Scoring (direct + post-match)
 ├─ pages/games/[id]/scoreboard.vue   ← affichage public (route Nuxt, même origine)
 ├─ pages/games/[id]/shotclock.vue    ← affichage shotclock (route Nuxt)
 ├─ stores/scoringStore.ts            ← état du match (port app3 → api2)
 ├─ composables/useScoringPermissions.ts
 ├─ composables/useTimer.ts | useBroadcast.ts | useWebSocket.ts (port app3)
 ├─ composables/useHardwareScoring.ts ← mode Hardware Scoring (Phase 3)
 └─ types/scoring.ts

       │ écritures (online-first, useApi + X-Active-Mandate)
       ▼
api2 ScoringController (ex-WsmController) → kp_match / kp_match_detail / kp_chrono / kp_stats
 + AdminGamesController::toggleValidation (verrouillage)
       │
       ├─ BroadcastChannel 'kpi_channel' (même origine) → scoreboard/shotclock locaux
       └─ WebSocket broker (interne) → incrustations /live + clients distants + matériel BODET
```

## 6. Détail fonctionnel

### 6.1 Routing & placement

- `pages/games/[id]/scoring.vue` — console (chrono, score, événements, joueurs A+B, périodes,
  pénalités, validation/verrouillage). Calquée sur le pattern presence
  (`pages/presence/match/[matchId]/team/[teamCode].vue`) mais **les deux équipes sur une page**.
- `pages/games/[id]/scoreboard.vue` et `…/shotclock.vue` — affichages plein écran
  (`definePageMeta({ layout: false })`), remplacent `scoreboard.php`/`shotclock.php`.

#### Cohabitation transitoire (V2 / V3 / Scoring)

**Pendant toute la durée du développement, de l'expérimentation et jusqu'à la validation
définitive du Scoring, les liens vers les feuilles de marque V2 et V3 doivent persister.** On
**ajoute** un nouveau lien « Scoring » à côté, sans toucher aux deux existants.

Dans `app4/pages/games/index.vue`, les liens par match sont rendus à deux endroits :
- **vue tableau** (~ lignes 2095-2104) : boutons « V2 » / « V3 » appelant `openScoresheet(g.id, 2|3)` ;
- **vue carte/mobile** (~ lignes 2818-2824) : mêmes boutons en footer de carte.

À chacun de ces deux endroits, **ajouter un bouton « Scoring »** (après V2/V3), gaté sur
`canScore` + `g.authorized`, qui fait `navigateTo('/games/${g.id}/scoring')`. Le helper
`openScoresheet(gameId, version: 2 | 3)` (~ ligne 1419, ouvre les PHP legacy `FeuilleMarque2/3.php`
dans une fenêtre nommée) **reste inchangé**. Le lien « PDF » (`FeuilleMatchMulti.php`) reste
également inchangé.

Après validation définitive (hors périmètre de ce plan), les boutons V2/V3 et le helper
`openScoresheet` pourront être retirés ; le PDF de contrôle est conservé.

### 6.2 État — `stores/scoringStore.ts`

Port de `app3/stores/matchStore.ts`, en :
- **retirant** IndexedDB/dexie/uuid/toRaw/`saveMatchToLocal` (offline reporté) ;
- **chargeant** le match via `GET /admin/games/{id}` (forme camelCase déjà fournie par
  `AdminGamesController::get`) et les joueurs via `GET /admin/matches/{id}/players?teamCode=A|B`
  (déjà utilisé par `presenceStore.initMatchMode`) ;
- faisant que **chaque action mutante POSTe vers api2 Scoring** via `useApi` (mise à jour
  optimiste + rollback sur erreur, cf. `togglePublication`/`toggleValidation` de
  games/index.vue) :

| Action store | Endpoint api2 (Scoring) |
|---|---|
| `setPeriod` | `PUT /scoring/gameParam` (param `Periode`) |
| score | `PUT /scoring/gameParam` (param `ScoreA`/`ScoreB`/`ScoreDetailA`/`ScoreDetailB`) |
| `setStatus` | `PUT /scoring/gameParam` (param `Statut` ATT/ON/END, `Heure_fin`) |
| `addEvent` / `removeEvent` | `PUT /scoring/gameEvent` (action add/remove, code B/V/J/R/D) |
| chrono | `PUT /scoring/gameTimer` (run/stop/RAZ → `kp_chrono`) |
| statut joueur | `PUT /scoring/playerStatus` |
| **valider/verrouiller** | `PATCH /admin/games/{id}/validation` (réutilise l'existant) |

- `isLocked` dérivé de `validation === 'O'`.
- Nouveau `types/scoring.ts` (miroir de `types/presence.ts`) : `ScoringMatch`, `ScoringPlayer`,
  `ScoringEvent` (code `'B'|'V'|'J'|'R'|'D'`, period, tpsJeu, player, number, team, reason),
  `Penalty`, `Period='M1'|'M2'|'P1'|'P2'|'TB'`, `MatchStatus='ATT'|'ON'|'END'`.

### 6.3 Permissions & monétisation-readiness

- Nouveau `composables/useScoringPermissions.ts`, miroir de `usePresencePermissions`,
  signature `(isLocked: Ref<boolean>)`.
- **Restriction DEV-ONLY (en cours de développement)** : le bouton « Scoring » dans /games
  n'est visible que pour **le seul login `42054`** (constante `SCORING_DEV_USER` dans
  `pages/games/index.vue` : `canScoring = authStore.user?.id === '42054' && profile <= 2`).
  C'est un **masquage UI uniquement** (choix assumé : pas de restriction par login côté serveur).
  À retirer pour revenir à `profile <= 2` quand la fonctionnalité s'ouvre au bureau.
- **Accès restreint au profil ≤ 2 pour l'instant** (phase d'expérimentation : réservé aux
  admins/bureau, pas encore ouvert au profil 9 « Table de marque »). Cohérent avec le bouton V3
  existant déjà gaté `profile <= 2` dans la vue carte. Seuils :
  - `canView = profile <= 2` (accès à la console + visibilité du lien « Scoring »)
  - `canScore = profile <= 2 && !isLocked` (buts/cartons/chrono)
  - `canManagePlayers = profile <= 2 && !isLocked`
  - `canValidate / canLock = profile <= 2`
- **Cible post-validation** (à élargir une fois la fonctionnalité validée, non implémenté
  maintenant) : `canScore = (profile <= 6 || profile === 9)`, `canValidate/canLock = profile <= 6`,
  alignés sur la règle score de games/index.vue et le rôle `ROLE_SCORER` (profil 9).
- **Côté serveur** : appliquer le **même seuil restrictif (≤ 2)** dans `ScoringController`
  pendant l'expérimentation (jamais se fier au gating client), élargi en même temps que le
  client lors de l'ouverture. Profil 9 = `ROLE_SCORER` déjà présent dans la hiérarchie, prêt
  pour l'élargissement futur.
- **Isolation par mandat** : `useApi` envoie déjà `X-Active-Mandate` ; en faisant respecter ce
  scope dans `ScoringController` (restreindre les matchs modifiables au périmètre du mandat,
  via le filtrage `allowedJournees` déjà utilisé par `AdminGamesController`), le Scoring est
  **isolé par organisation dès le départ**. Monétisation future = gater `canScore`/l'accès
  route derrière un flag de mandat, **sans toucher aux points d'appel** (le seuil de profil et
  le flag de mandat se combinent dans le même composable `useScoringPermissions`). Aucune table de
  facturation, aucun paywall maintenant.

### 6.4 Chrono

Réutiliser **easytimer.js** (comme app3/fm3 ; absent de app4 → ajouter aux deps). Port de
`app3/composables/useTimer.ts` (countdown principal + shotclock + buzzer `targetAchieved`,
précision `secondTenths`). **Le chrono devient autoritatif côté serveur** via `gameTimer` →
un rechargement reconstruit l'horloge depuis `kp_chrono` (upgrade clé vs app3).

### 6.5 Temps réel & captation matériel

- **Diffusion locale (Phase 2)** : port de `app3/composables/useBroadcast.ts` (canal
  `kpi_channel`, contrat `timer/timer_status/shotclock/period/teams/scores/penA/penB`).
  **BroadcastChannel est same-origin** → on **porte le markup** de `scoreboard.php` +
  `v2/scoreboard.js` en routes Nuxt (`scoreboard.vue`/`shotclock.vue`), ouvertes même origine
  via `window.open`.
- **WebSocket broker (Phase 3)** : port de `app3/composables/useWebSocket.ts` (format
  `{p:"eventId_terrain", t:type, v:value}`), URL depuis `runtimeConfig.public.wsServerUrl`.
  Mirroring des diffusions vers le broker → incrustations `/live` + clients distants.
  Implémenter en parallèle la génération du cache de diffusion (les `// TODO: Create cache
  here` du contrôleur), calqué sur `AdminEventWorkerController`.
- **Hardware Scoring (Phase 3)** : `useHardwareScoring.ts` reçoit les live datas du matériel
  (panneau BODET ou équivalent) via le broker (WSM) et **alimente le `scoringStore`** au lieu
  de la saisie manuelle. Même store, même diffusion ; seule la **source** des données change
  (humain vs matériel). Un sélecteur de mode (« Scoring » / « Hardware Scoring ») bascule la
  source.

### 6.6 i18n

Namespace `scoring.*` dans `i18n/locales/fr.json` et `en.json` (périodes, statuts, codes
d'événements, motifs de cartons, libellés chrono/shotclock/scoreboard, messages de
verrouillage, libellés « Scoring » / « Hardware Scoring »). Wording FR sourcé de
`FeuilleMarque3.php` + `v2/fm3_*.js`. **cn hors périmètre.**

## 7. Plan par phases

- **Phase 0 — Échafaudage** : dep `easytimer.js` ; `types/scoring.ts` ; coquilles
  `scoringStore.ts` + `useScoringPermissions.ts` ; clés i18n `scoring.*`.
- **Phase 1 — MVP online (livrable principal)** : page `scoring.vue` (chargement match+joueurs),
  store branché sur api2 (score/événements/chrono/statut/joueurs), permissions client+serveur,
  **renommage `WsmController` → `ScoringController` + sécurisation `/scoring`** (firewall JWT +
  `ROLE_SCORER` + scope mandat), validation/verrouillage via l'endpoint existant, **ajout du
  lien « Scoring »** dans games/index.vue (vue tableau + vue carte) **en conservant V2/V3**.
- **Phase 2 — Diffusion locale** : `useBroadcast` + `useTimer`, `scoreboard.vue` +
  `shotclock.vue`, UI pénalités.
- **Phase 3 — WebSocket broker + cache + Hardware Scoring** : `useWebSocket` (broker),
  génération du cache de diffusion, incrustations `/live`, `useHardwareScoring` (captation
  panneau BODET ou équivalent).
- **Phase 4 — Offline/PWA (reporté)** : file d'attente d'écritures IndexedDB derrière le store,
  service worker. Uniquement après un online-first solide.

## 8. Fichiers critiques

| Fichier | Action |
|---|---|
| `sources/app4/pages/games/[id]/scoring.vue` | **Créer** — console Scoring |
| `sources/app4/stores/scoringStore.ts` | **Créer** — port app3 → api2 + useApi |
| `sources/api2/src/Controller/WsmController.php` | **Renommer** en `ScoringController.php` + rôle/mandat (P1) ; cache TODO (P3) |
| `sources/api2/config/packages/security.yaml` | **Modifier** — firewall + access_control `^/scoring` (`ROLE_SCORER`) |
| `sources/app4/composables/useScoringPermissions.ts` | **Créer** — miroir usePresencePermissions |
| `sources/app4/pages/games/index.vue` | **Modifier** — **ajouter** un lien « Scoring » (vue tableau ~2095-2104 + vue carte ~2818-2824) ; **conserver V2/V3** le temps de la validation |

Secondaires à créer : `composables/useTimer.ts`, `useBroadcast.ts`, `useWebSocket.ts`,
`useHardwareScoring.ts` (P3), `pages/games/[id]/scoreboard.vue`, `…/shotclock.vue`,
`types/scoring.ts`.
Références à porter : `sources/admin/v2/fm3_C.js` (chrono/shotclock/pénalités, ~1384 lignes,
**plus gros effort**), `sources/admin/v2/scoreboard.js`.

## 9. Risques / inconnues

1. **Logique chrono/shotclock/pénalités de `fm3_C.js`** — plus gros effort de portage
   (type de match C vs E, expiration des pénalités → messages `penA/penB`).
2. **Génération du cache de diffusion api2** (les TODO) — reporté en Phase 3.
3. **broker / Hardware Scoring** — serveur WS interne maîtrisé ; protocole de captation du
   matériel (BODET ou équivalent) à formaliser (format des live datas entrantes). Risque
   maîtrisé (propriété interne).
4. **cn** — hors périmètre MVP ; chantier de suivi séparé sur toute app4.

## 10. Vérification de bout en bout

- **Phase 1** : lancer app4 + api2, se connecter avec un **profil ≤ 2**, vérifier que le lien
  « Scoring » apparaît (à côté de V2/V3, eux toujours présents), ouvrir `/games/{id}/scoring`,
  saisir buts/cartons, lancer/arrêter le chrono, changer de période, **corriger un match
  post-déroulement puis le valider/verrouiller** ; vérifier en base (`kp_match`,
  `kp_match_detail`, `kp_chrono`) via phpMyAdmin ; recharger → horloge restaurée ; match
  verrouillé → lecture seule ; **profil > 2 → lien masqué + accès refusé (UI + 403)** ; appel
  non authentifié à `PUT /api2/scoring/gameParam/{id}` → 401 ; vérifier que `app_wsm` legacy
  (`/api/wsm/`) fonctionne toujours.
- **Phase 2** : ouvrir scoreboard + shotclock en 2ᵉ fenêtre → synchro live.
- **Phase 3** : connecter le broker + une incrustation `/live` → réception via `{p,t,v}` ;
  brancher un panneau BODET (ou équivalent) en mode Hardware Scoring → le store se met à jour
  depuis le matériel.

## 11. Suivi des développements

> Cette section est enrichie au fil des développements avec le détail des décisions et de
> l'implémentation effective.

### Phase 0 — Échafaudage ✅ (terminée)

Artefacts créés/modifiés dans `sources/app4` :

| Fichier | Détail |
|---|---|
| `types/scoring.ts` | **Créé.** `Period`, `MatchStatus`, `MatchType`, `TeamSide`, `ScoringEventCode`, `ScoringMatch` (calé sur la réponse réelle de `AdminGamesController::get` — champs camelCase : id, validation, statut, type, periode, scoreA/B, scoreDetailA/B, idEquipeA/B, equipeA/B…), `ScoringPlayer`, `ScoringEvent`, `Penalty`, `PeriodDurations`. |
| `stores/scoringStore.ts` | **Créé** (coquille). Store options-API `defineStore('scoring', …)`. State (match, playersA/B, events, penalties, periodDurations, loading). Getters `hasMatch`, `isLocked` (`validation === 'O'`), `currentPeriodDuration`. Durées par défaut M1/M2=600s, P1/P2/TB=180s. **Actions de chargement/mutation → Phase 1.** |
| `composables/useScoringPermissions.ts` | **Créé.** Signature `(isLocked)`. **Accès gaté profil ≤ 2** via constante `SCORING_ACCESS_MAX_PROFILE = 2`. Retourne `canView`, `canScore`, `canManagePlayers`, `canValidate`, `canLock`. Cible post-validation documentée en commentaire (relever la constante + le contrôle serveur). |
| `i18n/locales/fr.json`, `en.json` | **Modifiés.** Namespace `scoring.*` ajouté (title, link, hardware, status ATT/ON/END, period M1/M2/P1/P2/TB, event goal/cards, timer, scoreboard, locked). |
| `package.json` + `package-lock.json` | **Modifiés.** `easytimer.js@^4.6.0` ajouté (même version qu'app3/fm3). |

**Note environnement** : le container `kpi_node_app4` avait un `node_modules` partiellement
détenu par `root` (~7175 entrées, install antérieure en root) + 80 artefacts temporaires
d'installs avortées, qui bloquaient `npm install` (EACCES puis ENOTEMPTY). Corrigé hors
périmètre feature : `chown -R node:node /app/node_modules` (via root) + suppression des
artefacts `.*-RANDOM`. À garder en tête si d'autres installs échouent.

### Phase 1 — MVP online (en cours)

**Backend api2 :**

| Fichier | Détail |
|---|---|
| `src/Controller/ScoringController.php` | **Créé** (repris de `WsmController`). Routes sous **`/admin/scoring/...`** (gameParam, gameEvent, playerStatus, gameTimer, stats) → automatiquement derrière le firewall JWT `^/admin`. Classe annotée **`#[IsGranted('ROLE_ADMIN')]`** = profil ≤ 2 (mapping `User::getRoles()` : niveau ≤ 2 → `ROLE_ADMIN`). Conserve le verrou `Validation != 'O'`. |
| `src/Controller/WsmController.php` | **Supprimé.** N'était consommé par personne (vérifié : app_wsm/legacy utilisent `/api/wsm/`, backend distinct) et exposait `/wsm` en **public** (firewall `main`). Suppression = élimination de la surface non authentifiée. |

> **Décision** : routes sous `/admin/scoring` plutôt que `/scoring` (spec initiale) — réutilise
> le firewall JWT existant sans en créer un nouveau, cohérent avec `useApi` qui parle déjà à
> `/admin/*`. Le contrôle fin de rôle reste dans le contrôleur (`ROLE_ADMIN` = profil ≤ 2,
> à élargir en `ROLE_SCORER` à l'ouverture).

**Vérifié** : `PUT /api2/admin/scoring/gameParam/1` sans token → **401** ; ancien
`PUT /api2/wsm/gameParam/1` → **404**.

**Frontend app4 :**

| Fichier | Détail |
|---|---|
| `stores/scoringStore.ts` | **Complété.** Actions : `load` (GET `/admin/games/{id}` + 2× `/admin/matches/{id}/players?teamCode=A\|B`), `setParam`/`setStatus`/`setPeriod` (PUT gameParam, optimiste + rollback), `addEvent`/`removeEvent` (PUT gameEvent + maj score pour les buts), `setTimer` (PUT gameTimer), `toggleValidation` (PATCH `/admin/games/{id}/validation`). Getters `scoreA`/`scoreB`. |
| `pages/games/[id]/scoring.vue` | **Créée.** Console : header match, score, sélecteurs statut/période, chrono (run/stop/RAZ → api2), listes joueurs A/B (sélection), boutons d'événements (but/cartons), liste des événements, verrouillage. Gatée `useScoringPermissions` (≤ 2). |
| `pages/games/index.vue` | **Modifiée.** Ajout `canScoring` (profil ≤ 2) + helper `openScoring` + **bouton « Scoring »** dans la vue tableau (à côté de V2/V3) et la vue carte. **V2/V3 conservés inchangés.** |
| `i18n/locales/fr.json`, `en.json` | Clés `scoring.*` complétées (field, history, not_found, select_player_first, no_access). |

**Vérifié** : route `/games/{id}/scoring` compile (HTTP 302 → middleware auth, comme les
autres routes protégées) ; ESLint OK sur tous les fichiers Scoring ; aucune erreur dans les
logs du dev server.

**Chrono temps réel ✅ (terminé) :**

| Fichier | Détail |
|---|---|
| `composables/useTimer.ts` | **Créé.** Wrapper Vue réactif autour d'easytimer.js. Countdown du temps de jeu, précision `secondTenths`. Expose `display` (MM:SS.d), `gameTime` (MM:SS, pour horodater les événements), `elapsed`, `isRunning`, et `setPeriod` / `start` / `stop` / `reset` / `restoreFromServer`. Buzzer/stop auto sur `targetAchieved`. |
| `src/Controller/ScoringController.php` | **Ajout** `GET /admin/scoring/gameTimer/{matchId}` : renvoie l'état persisté de `kp_chrono` (action, start_time, start_time_server, run_time, max_time) + `nowServer` (heure serveur en s % 86400) pour le calcul de restauration. |
| `stores/scoringStore.ts` | **Ajout** `loadTimerState()`. `setTimer` persiste `startTime = elapsed` (secondes écoulées dans la période) + `maxTime`. |
| `pages/games/[id]/scoring.vue` | Affichage live du chrono (vert si running) ; `onMounted` appelle `loadTimerState()` → `restoreFromServer()` si un état existe, sinon `setPeriod()` ; les événements sont horodatés via `gameTime` ; changement de période reconfigure le countdown. |

**Modèle de chrono retenu** (plus simple que l'encodage fm3) : `max_time` = durée période,
`start_time` = secondes écoulées au dernier run/stop, `start_time_server` = heure serveur du
dernier run. Restauration : si `action='run'`, `realElapsed = elapsed + (nowServer − startTimeServer)`
(gère le passage de minuit).

**Vérifié** (test DB de bout en bout, match #127) : état `run` inséré (elapsed=120s, démarré
il y a 30s, période 600s) → lecture `kp_chrono` correcte → calcul de restauration
`realElapsed = 150s`, **remaining = 450s → 07:30**. État de test nettoyé. Bug corrigé au
passage : mauvais namespace `IsGranted` (`Bundle\SecurityBundle` → `Component\Security\Http`,
signalé par l'IDE).

**Scoping par mandat ✅ (terminé) :**

`ScoringController` — méthode privée `assertMatchAuthorized(int $matchId)` (miroir de
`AdminGamesController::assertJourneeAuthorized`) : résout la journée du match et vérifie
`User::getAllowedJournees()` (null = accès total ; sinon la journée doit être dans la liste du
mandat actif, déjà résolu depuis `X-Active-Mandate` par la couche auth). Appelée en tête de
**tous** les endpoints : gameParam, gameEvent, playerStatus, gameTimer (GET+PUT), stats
(via `$data->game`). Retourne 404 si match inconnu, 403 si hors périmètre.

**Vérifié** : `PUT/GET /admin/scoring/...` sans token → **401** (auth JWT avant scoping). Le
scoping 403 ne s'évalue que pour un utilisateur authentifié hors de son périmètre.

**Reste à faire en Phase 1** (avant de clore le MVP) :
- Test fonctionnel complet **authentifié** (profil ≤ 2) via l'UI : saisie réelle + vérification
  base + restauration visuelle du chrono au rechargement + vérif 403 hors mandat.
- Motifs de cartons (modal) et statut joueur (capitaine/coach) depuis la console.
- Shotclock (time-shoot) + diffusion broadcast → relève de la Phase 2.
