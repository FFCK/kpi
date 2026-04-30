# Spécification - Page Association Journées / Événements

## Statut : ⏳ À IMPLÉMENTER

**Spec créée le** : 2026-04-24
**Auteur** : Claude Code
**Contexte de création** : Extraction du flux d'association depuis `PAGE_JOURNEES_PHASES.md` suite à un retour utilisateur : le flux legacy (bouton caché dans un dropdown, modal qui ne liste que les journées déjà associées) empêchait d'ajouter de nouvelles associations et ne proposait pas de filtre compétition.

---

## 1. Vue d'ensemble

Page d'administration dédiée à la gestion des associations entre une **journée (phase)** et un **événement**. Un événement peut regrouper plusieurs journées de compétitions différentes (ex. une étape de championnat regroupant N1H et N1F). Cette page permet d'associer ou de dissocier librement n'importe quelle journée de la saison à un événement donné.

**Route** : `/events/{id}/gamedays`

**Entry-point** : icône 🔗 (heroicons:link) dans chaque ligne du tableau `/events` (desktop et mobile), visible uniquement pour profil ≤ 3.

**Accès** :
- Profil ≤ 3 : Lecture + modification des associations

**Page PHP Legacy équivalente** : mode "Association événements" (radio button global) dans `GestionCalendrier.php` / `GestionCalendrier.tpl`.

**Implémentation Nuxt** : `sources/app4/pages/events/[id]/gamedays.vue` (à créer)

**Contexte de travail** : Utilise la saison du `workContextStore` par défaut, mais la page accepte aussi une saison explicite via filtre local pour permettre aux administrateurs de gérer des événements hors saison courante.

---

## 2. Motivations / Problèmes résolus

L'ancien flux (dans `/gamedays`) présentait trois défauts bloquants :

1. **Découvrabilité faible** — le bouton "Gérer les associations" était caché dans le dropdown "Actions en masse" et n'apparaissait qu'une fois qu'un événement était sélectionné dans le contexte de travail ET qu'au moins une journée était cochée.
2. **Liste restrictive** — la modal réutilisait la liste `gamedays.value` déjà filtrée par l'événement côté API (`GET /admin/gamedays?event=` fait un INNER JOIN sur `kp_evenement_journee`). Conséquence : seules les journées **déjà associées** étaient visibles, rendant impossible l'ajout de nouvelles associations.
3. **Filtrage insuffisant** — pas de filtre compétition pour rechercher dans un gros volume de journées candidates.

La refonte déplace ce flux sur une page dédiée déclenchée depuis la page `/events` (entry-point plus naturel : on part de l'événement qu'on veut peupler), avec filtres complets et liste de toutes les journées candidates.

---

## 3. Fonctionnalités

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Affichage de l'entête de l'événement (libellé, dates, lieu) | ≤ 3 | Essentielle | ✅ À implémenter |
| 2 | Bouton retour vers `/events` | ≤ 3 | Essentielle | ✅ À implémenter |
| 3 | Liste paginée de toutes les journées de la saison | ≤ 3 | Essentielle | ✅ À implémenter |
| 4 | Filtre saison (par défaut celle du workContext) | ≤ 3 | Essentielle | ✅ À implémenter |
| 5 | Filtre compétition (multi-select via `AdminCompetitionMultiSelect`) | ≤ 3 | Essentielle | ✅ À implémenter |
| 6 | Filtre état (Toutes / Associées / Non associées) | ≤ 3 | Essentielle | ✅ À implémenter |
| 7 | Recherche texte (Phase, Nom, Lieu, Id) | ≤ 3 | Utile | ✅ À implémenter |
| 8 | Checkbox d'association avec toggle AJAX immédiat | ≤ 3 | Essentielle | ✅ À implémenter |
| 9 | Badge visuel "Associée" / "Non associée" par ligne | ≤ 3 | Utile | ✅ À implémenter |
| 10 | Compteur en entête du nombre de journées associées | ≤ 3 | Utile | ✅ À implémenter |
| 11 | Toast de confirmation sur chaque toggle | ≤ 3 | Essentielle | ✅ À implémenter |
| 12 | Pagination via `AdminPagination` (limit 25 par défaut) | ≤ 3 | Essentielle | ✅ À implémenter |
| 13 | Vue mobile en cartes | ≤ 3 | Utile | ✅ À implémenter |
| 14 | Bouton "Tout associer / Tout dissocier" sur la page courante | ≤ 3 | Amélioration | ⏳ Différé (phase 2) |

---

## 4. Structure de la page

### 4.1 Vue Desktop

```
┌────────────────────────────────────────────────────────────────────────────┐
│  ← Retour aux événements                                                   │
│                                                                            │
│  Événement #42 - Championnat de France N1H - Étape 3                       │
│  📅 11/01/2026 - 12/01/2026    📍 Corbeil-Essonnes (91)                   │
│  🔗 8 journées associées                                                  │
├────────────────────────────────────────────────────────────────────────────┤
│  [Saison: ▼ 2026] [🔽 Compétitions (3)] [État: ▼ Toutes] [🔍 Recherche…] │
├────────────────────────────────────────────────────────────────────────────┤
│  │ ☑  │ Id  │ Compétition / Phase  │ Date      │ Lieu       │ Dpt │ État  │
│  │ ✓  │ 8642│ N1H-A - Poule A      │ 11/01/26  │ Corbeil-E. │ 91  │ 🟢 ass│
│  │ ☐  │ 8643│ N1H-A - Poule B      │ 11/01/26  │ Corbeil-E. │ 91  │ ⚪ non│
│  │ ✓  │ 8644│ N1H-B - Poule A      │ 12/01/26  │ Corbeil-E. │ 91  │ 🟢 ass│
│  …                                                                          │
├────────────────────────────────────────────────────────────────────────────┤
│  Pagination: [< 1 2 3 >]  Afficher: [25 ▼]  Total: 68 journées candidates │
└────────────────────────────────────────────────────────────────────────────┘
```

### 4.2 Vue Mobile (cartes)

```
┌────────────────────────────────────┐
│  ← Retour                          │
│  Événement #42 - N1H Étape 3       │
│  📅 11/01 - 12/01/2026             │
│  🔗 8 associées                   │
├────────────────────────────────────┤
│  [Saison] [Compét] [État]          │
│  [🔍 Rechercher...]                │
├────────────────────────────────────┤
│  ┌──────────────────────────────┐  │
│  │ ☑ #8642 N1H-A - Poule A      │  │
│  │ 📅 11/01/2026                │  │
│  │ 📍 Corbeil-Essonnes (91)     │  │
│  │ 🟢 Associée                  │  │
│  └──────────────────────────────┘  │
│  ┌──────────────────────────────┐  │
│  │ ☐ #8643 N1H-A - Poule B      │  │
│  │ ⚪ Non associée              │  │
│  └──────────────────────────────┘  │
│  …                                 │
└────────────────────────────────────┘
```

---

## 5. Filtres

### 5.1 Filtre Saison

| Propriété | Valeur |
|-----------|--------|
| Source | `workContext.seasons` (chargées via `/admin/filters/seasons`) |
| Valeur par défaut | `workContext.season` (saison active du contexte) |
| Impact | Restreint `candidates` aux journées de la saison ; change aussi la référence pour le compte d'associations |
| Persistance | État local (pas de localStorage) |

### 5.2 Filtre Compétition

| Propriété | Valeur |
|-----------|--------|
| Composant | `AdminCompetitionMultiSelect` (composant existant, `sources/app4/components/admin/CompetitionMultiSelect.vue`) |
| Source | `workContext.competitions` pour la saison filtrée |
| Sélection | Multiple (checkboxes) |
| Par défaut | Aucune sélection = aucun filtre |
| Impact | Filtre `candidates` via `?competitions=CODE1,CODE2` |

### 5.3 Filtre État

| Option | Effet |
|--------|-------|
| Toutes | Affiche toutes les candidates (défaut) |
| Associées | Affiche uniquement celles où `associatedIds.has(g.id)` |
| Non associées | Affiche uniquement celles où `!associatedIds.has(g.id)` |

**Note d'implémentation** : le filtre "Associées / Non associées" s'applique **côté client** sur la page courante. Si un utilisateur veut voir l'intégralité des associées, il sélectionne "Associées" + choisit une limite de pagination élevée (ou implémente-t-on ultérieurement une requête dédiée `?onlyLinked=true` côté backend — différé).

### 5.4 Recherche texte

| Propriété | Valeur |
|-----------|--------|
| Champs cibles | Phase, Nom, Lieu, Id |
| Debounce | 300 ms |
| Impact | `?search=` transmis à `GET /admin/gamedays` |

---

## 6. Logique de chargement

### 6.1 Au montage

Deux requêtes parallèles :

1. **Récupérer les IDs associés** :
   ```
   GET /admin/gamedays?event={eventId}&season={season}&limit=9999
   ```
   → remplit `associatedIds: Set<number>` à partir des `items` retournés.
   L'endpoint fait un INNER JOIN sur `kp_evenement_journee`, donc retourne bien uniquement les journées associées.

2. **Récupérer les candidates** :
   ```
   GET /admin/gamedays?season={season}&page=1&limit=25
   ```
   → remplit `candidates: Gameday[]` + `total`, `totalPages`.
   **Aucun paramètre `event=`** pour avoir la liste complète.

3. **Récupérer l'événement pour l'entête** :
   ```
   GET /admin/events/{eventId}
   ```
   → remplit `event: Event`.
   Si l'endpoint n'existe pas dans l'API2, fallback sur `GET /admin/events?search={eventId}` (à confirmer pendant l'implémentation).

### 6.2 Sur changement de filtre

Watchers sur `filterCompetitions`, `filterSeason`, `search` (debounce 300 ms), `page` → re-lancement de la requête **candidates** uniquement. La liste `associatedIds` ne change pas car un changement de filtre ne modifie pas les associations existantes. En revanche, si `filterSeason` change, il faut **aussi** recharger `associatedIds` (une journée associée pourrait être sur une autre saison).

### 6.3 Sur toggle d'une ligne

```
if (associatedIds.has(g.id)):
  DELETE /admin/gamedays/{g.id}/event/{eventId}
  associatedIds.delete(g.id)
  toast.success(t('events.association.unlinked'))
else:
  PUT /admin/gamedays/{g.id}/event/{eventId}
  associatedIds.add(g.id)
  toast.success(t('events.association.linked'))
```

Mise à jour du compteur en entête immédiate (computed sur `associatedIds.size`).

---

## 7. Actions

### 7.1 Associer une journée

| Propriété | Valeur |
|-----------|--------|
| Méthode HTTP | `PUT /admin/gamedays/{id}/event/{eventId}` |
| Profil requis | ≤ 3 (ROLE_DIVISION) |
| Implémentation SQL | `REPLACE INTO kp_evenement_journee (Id_evenement, Id_journee) VALUES (?, ?)` |
| Feedback UI | Toast succès + badge passe à "Associée" + compteur incrémente |

### 7.2 Dissocier une journée

| Propriété | Valeur |
|-----------|--------|
| Méthode HTTP | `DELETE /admin/gamedays/{id}/event/{eventId}` |
| Profil requis | ≤ 3 (ROLE_DIVISION) |
| Implémentation SQL | `DELETE FROM kp_evenement_journee WHERE Id_evenement = ? AND Id_journee = ?` |
| Feedback UI | Toast succès + badge passe à "Non associée" + compteur décrémente |

### 7.3 Retour vers `/events`

Un bouton "← Retour aux événements" en haut de page (et en bouton flottant mobile) permet de revenir vers le tableau des événements. Utilise `navigateTo('/events')`.

---

## 8. Endpoints API2 consommés

Aucun nouvel endpoint à créer. Les endpoints existants sont :

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| GET | `/admin/gamedays` | Liste paginée des journées (avec filtres `season`, `competitions`, `event`, `search`, `page`, `limit`) | ≤ 10 |
| GET | `/admin/events/{id}` | Détail d'un événement (à confirmer — sinon fallback `/admin/events?search=...`) | ≤ 10 |
| PUT | `/admin/gamedays/{id}/event/{eventId}` | Associer une journée à un événement | ≤ 3 |
| DELETE | `/admin/gamedays/{id}/event/{eventId}` | Dissocier une journée d'un événement | ≤ 3 |

**Contrôleur concerné** : `sources/api2/src/Controller/AdminGamedaysController.php`

---

## 9. Entry-point depuis `/events`

### 9.1 Icône action dans le tableau des événements

Dans `sources/app4/pages/events/index.vue`, ajouter une icône 🔗 dans la cellule actions (ligne ~525 pour desktop) :

```vue
<button
  v-if="authStore.profile <= 3"
  class="p-1.5 text-purple-600 hover:text-purple-800"
  :title="t('events.manage_gameday_associations')"
  @click="navigateTo(`/events/${event.id}/gamedays`)"
>
  <UIcon name="heroicons:link" class="w-6 h-6" />
</button>
```

Et équivalent dans le footer mobile `AdminCard` (ligne ~625) avec `AdminActionButton icon="heroicons:link"`.

### 9.2 Ordre des actions

Ordre recommandé dans la cellule actions (desktop) :
1. ✏ Éditer
2. 🔗 Gérer les associations de journées (nouveau)
3. 🗑 Supprimer

---

## 10. Sécurité

### 10.1 Contrôle d'accès

| Opération | Profil requis | Rôle Symfony |
|-----------|--------------|--------------|
| Accéder à la page `/events/{id}/gamedays` | ≤ 3 | ROLE_DIVISION |
| Voir l'icône 🔗 dans `/events` | ≤ 3 | ROLE_DIVISION |
| Associer une journée | ≤ 3 | ROLE_DIVISION |
| Dissocier une journée | ≤ 3 | ROLE_DIVISION |

### 10.2 Autorisation par journée

Le contrôle `utyIsAutorisationJournee($idJournee)` existant dans l'API continue de s'appliquer. Pour les profils 1 et 2, le périmètre est levé (voir `DROITS_PAR_PROFIL.md` §Bypass périmètre).

### 10.3 Journal d'audit

Chaque association/dissociation est loguée dans `kp_journal` (comportement existant) :
- Type : `assoc_event_gameday` / `unassoc_event_gameday`
- Champs : `Id_evenement`, `Id_journee`, `Code_uti`, timestamp

---

## 11. Traductions i18n

### 11.1 Clés françaises (`fr.json`)

Ajouter dans la section `events` :

```json
{
  "events": {
    "manage_gameday_associations": "Gérer les associations de journées",
    "association": {
      "page_title": "Associations de journées",
      "back_to_events": "Retour aux événements",
      "associated_count": "{count} journée associée | {count} journées associées",
      "filter_season": "Saison",
      "filter_competitions": "Compétitions",
      "filter_state": "État",
      "filter_state_all": "Toutes",
      "filter_state_linked": "Associées",
      "filter_state_unlinked": "Non associées",
      "search_placeholder": "Rechercher (Phase, Nom, Lieu, Id)…",
      "linked_badge": "Associée",
      "unlinked_badge": "Non associée",
      "linked": "Journée associée à l'événement.",
      "unlinked": "Journée dissociée de l'événement.",
      "event_not_found": "Événement introuvable.",
      "no_candidates": "Aucune journée candidate pour les filtres sélectionnés."
    }
  }
}
```

### 11.2 Clés anglaises (`en.json`)

```json
{
  "events": {
    "manage_gameday_associations": "Manage gameday associations",
    "association": {
      "page_title": "Gameday associations",
      "back_to_events": "Back to events",
      "associated_count": "{count} gameday associated | {count} gamedays associated",
      "filter_season": "Season",
      "filter_competitions": "Competitions",
      "filter_state": "State",
      "filter_state_all": "All",
      "filter_state_linked": "Linked",
      "filter_state_unlinked": "Unlinked",
      "search_placeholder": "Search (Phase, Name, Location, Id)…",
      "linked_badge": "Linked",
      "unlinked_badge": "Unlinked",
      "linked": "Gameday linked to event.",
      "unlinked": "Gameday unlinked from event.",
      "event_not_found": "Event not found.",
      "no_candidates": "No candidate gameday for the selected filters."
    }
  }
}
```

### 11.3 Clés à retirer de `gamedays.*`

Les clés suivantes, auparavant dans `gamedays.*`, sont **retirées** (flux supprimé de la page gamedays) :
- `gamedays.event_linked`
- `gamedays.event_unlinked`
- `gamedays.manage_event_association`
- `gamedays.event_association_title`
- `gamedays.event_association_hint`

---

## 12. Composants Vue

### 12.1 Fichiers créés

```
sources/app4/pages/events/[id]/gamedays.vue    # Page principale (nouveau)
```

### 12.2 Composants réutilisés (aucune création nécessaire)

| Composant | Usage |
|-----------|-------|
| `AdminCompetitionMultiSelect` | Filtre compétition |
| `AdminPagination` | Pagination |
| `AdminCardList` / `AdminCard` | Vue mobile en cartes |
| `AdminScrollToTop` | Bouton retour en haut |
| `AdminToolbar` | Barre de recherche (optionnel) |

---

## 13. Liens avec autres specs

- **`PAGE_JOURNEES_PHASES.md`** — le flux d'association y était initialement documenté (§5.2 "Mode Association Événements"). Cette section est désormais retirée et renvoie vers la présente spec. Les endpoints API2 restent documentés dans PAGE_JOURNEES_PHASES car c'est le contrôleur `AdminGamedaysController` qui les implémente.
- **`PAGE_EVENEMENTS.md`** — **inexistante actuellement**. Quand elle sera créée, elle devra documenter l'entry-point 🔗 (section "Actions du tableau"). En attendant, la modification de `sources/app4/pages/events/index.vue` est décrite ci-dessus §9.
- **`DROITS_PAR_PROFIL.md`** — doit être mise à jour pour ajouter la section "Bypass périmètre profils 1/2" (filtres `Filtre_*` ignorés pour niveau effectif ≤ 2). Cela impacte cette page : un profil 1/2 peut voir/associer TOUTES les journées, même celles hors de son périmètre habituel.
- **`PAGE_UTILISATEURS.md`** — documenter que la levée de filtres côté admin impacte les listes affichées dans l'éditeur d'utilisateur.

---

## 14. Migration depuis l'ancien flux

### 14.1 Changements dans `sources/app4/pages/gamedays/index.vue`

Les éléments suivants sont **supprimés** :

| Élément | Lignes approximatives |
|---------|----------------------|
| `eventAssociations`, `eventAssociationLoading`, `eventAssociationOpen` (state) | L. 41, 93‑94 |
| `canAssociateEvents` (computed) | L. 99 |
| `selectedEventId` (computed) | L. 582‑587 |
| `openEventAssociation()` | L. 589‑611 |
| `toggleEventAssociation()` | L. 613‑630 |
| Bouton "Gérer les associations" dans le dropdown bulk | L. 838‑847 |
| Modal EVENT ASSOCIATION | L. 1655‑1689 |

### 14.2 Comportement migré

- Tout utilisateur qui voulait associer des journées à un événement via `/gamedays` passera désormais par `/events` → 🔗 → `/events/{id}/gamedays`.
- L'intitulé de menu ou les raccourcis éventuels vers l'ancien flux doivent être supprimés.
- Les endpoints API existants (`PUT/DELETE /admin/gamedays/{id}/event/{eventId}`) sont **conservés** — la nouvelle page les consomme.

---

## 15. Vérification / Tests manuels

1. Se connecter en profil 3. Aller sur `/events` → l'icône 🔗 est visible dans chaque ligne.
2. Cliquer sur 🔗 d'un événement E → navigation vers `/events/{E}/gamedays`. Entête affiche E (libellé, dates, lieu). Compteur affiche le bon nombre.
3. Vérifier que la liste par défaut affiche **toutes** les journées de la saison courante, pas uniquement les associées.
4. Filtrer par compétition C → la liste se restreint.
5. Rechercher "Poule A" → la liste se restreint.
6. Cocher une journée non associée → badge passe à "Associée", toast succès, compteur incrémenté.
7. Décocher une journée associée → badge passe à "Non associée", toast succès, compteur décrémenté.
8. Filtre état = "Associées" → seules les journées associées sont visibles.
9. Filtre état = "Non associées" → seules les non associées sont visibles.
10. Paginer (saison avec > 25 journées) → pagination OK, associations cohérentes entre pages.
11. Changer de saison dans le filtre → liste et compteur se rafraîchissent.
12. Revenir sur `/events` via le bouton retour → tableau des événements à jour.
13. Vérifier que `/gamedays` ne propose plus le bouton "Gérer les associations" dans le dropdown Actions.
14. Profil 4 : l'icône 🔗 n'est pas visible dans `/events` et l'URL `/events/{id}/gamedays` retourne une erreur 403.

---

## 16. Décisions prises

| # | Question | Décision |
|---|----------|----------|
| Q1 | Modal vs page dédiée ? | ✅ Page dédiée `/events/{id}/gamedays` (ergonomie, gros volumes, URL partageable) |
| Q2 | Entry-point ? | ✅ Icône 🔗 dans le tableau `/events` (flux naturel depuis l'événement) |
| Q3 | Retirer le flux de `/gamedays` ? | ✅ Oui, suppression complète pour éviter les doublons et code cassé |
| Q4 | Profil requis ? | ✅ ≤ 3 (cohérent avec `canAssociateEvents` actuel) |
| Q5 | Filtre "état" côté client ou serveur ? | ✅ Client sur la page courante. Serveur (`?onlyLinked=true`) différé à une phase 2 si besoin |
| Q6 | Nouvel endpoint consolidé ? | ❌ Non — 2 requêtes existantes suffisent, pas de duplication de logique |

---

**Document créé le** : 2026-04-24
**Statut** : ⏳ À IMPLÉMENTER
**Auteur** : Claude Code
