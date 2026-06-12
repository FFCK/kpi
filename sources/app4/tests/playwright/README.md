# Tests Playwright — app4 (dev local)

Procédure pour qu'un agent (ou toi) puisse piloter l'app4 avec Playwright en
dev local **sans gérer les certificats auto-signés** et **sans créer/supprimer
de compte de test**.

## Pré-requis (une fois)

```bash
cp sources/app4/.env.test.local.dist sources/app4/.env.test.local
# puis renseigner TEST_USERNAME / TEST_PASSWORD (compte admin existant, profil <= 2)
```

`.env.test.local` est **gitignoré** (`.env.*.local`) : aucun secret n'est jamais
commité, poussé ou déployé.

Démarrer le serveur de dev :

```bash
make app4_dev   # Nuxt sur http://localhost:3004/admin2 (hot reload)
```

## Pourquoi cette approche

L'app appelle l'API sur `https://kpi.localhost/api2`, dont le certificat Traefik
est auto-signé → le navigateur Playwright le rejette (`ERR_CERT_AUTHORITY_INVALID`).

Deux contournements, **côté navigateur uniquement**, sans toucher au système :

1. **Naviguer en HTTP** sur le serveur de dev : `http://localhost:3004/admin2`.
2. **Réécrire les appels API et les images** vers le port HTTP déjà exposé par
   `kpi_php` (`http://localhost:8003`), via un shim injecté dans la page
   (`fetch` pour `/api2`, `<img src>` pour `/img` avec un `MutationObserver`
   couvrant les images chargées dynamiquement). Le CORS de l'API autorise déjà
   l'origine `http://localhost:3004`.

Aucun certificat à installer, aucune modification d'infra, rien à nettoyer.

## Recette de test (à exécuter par l'agent)

1. `browser_navigate` → `${TEST_APP_URL}/login`
2. Injecter le shim fetch (voir `fetch-shim.js`) via `browser_evaluate`.
3. Se connecter avec `TEST_USERNAME` / `TEST_PASSWORD`.
4. Naviguer dans l'app (SPA : le shim persiste sans rechargement complet).

> Le shim réécrit aussi les logos (`kpi.localhost/img` → `:8003/img`), donc les
> images des nations s'affichent normalement pendant les tests.

## Captures d'écran

Enregistrer les captures dans `screenshots/` (gitignoré, sauf `.gitkeep`).
Ce sont des sorties de test, jamais versionnées. Les artefacts Playwright MCP
(`.playwright-mcp/`, à la racine du repo) sont également gitignorés.

## Sécurité

- Secrets dans `.env.test.local` (gitignoré) — jamais dans le repo.
- Le shim et l'endpoint `:8003` ne servent qu'au test local ; ils ne modifient
  ni le code applicatif ni la config de build/déploiement.
