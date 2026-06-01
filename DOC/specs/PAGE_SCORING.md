# Spécification — Scoring (console de match en direct) dans app4

> Statut : proposition / plan de mise en œuvre
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
