# QA App4 — Vérification par profil

> Référence rapide pour tester les fonctionnalités accessibles à chaque profil sur chaque page.
>
> **Règle de profil** : `hasProfile(N)` retourne `true` si `effectiveProfile <= N`.
> Un profil **bas** = droits **élevés** (1 = super admin, 10 = inutilisé).

---

## Profils

| Profil | Nom | Résumé accès |
|--------|-----|-------------|
| **1** | Super Admin | Tout + changement de profil / mandats |
| **2** | Bureau CNAKP | Gestion complète + TV, journal, cache, opérations |
| **3** | Resp. Division | Gestion compétitions, classements, mandats |
| **4** | Resp. Poule/Compétition | Édition, publication basique |
| **5** | Délégué fédéral | — à préciser lors des tests |
| **6** | Organisateur journée | — à préciser lors des tests |
| **7** | Resp. club/équipe | Lecture + présence équipe (à préciser) |
| **8** | Consultation simple | Lecture seule |
| **9** | Table de marque | Accès restreint (tableau de bord partiel) |
| **10** | (Inutilisé) | Non utilisé actuellement |

> Les profils 5, 6, 7 n'ont pas de guards explicites identifiés dans le code — leurs droits effectifs sont à confirmer en pratique.

---

## Checklist par page

### `/login` — Connexion

| Fonctionnalité | Tous profils | Notes |
|---|:---:|---|
| Formulaire login (identifiant + mot de passe) | ✅ | Public |
| Bascule langue FR/EN | ✅ | |
| Message d'erreur profil restreint | ✅ | |
| Redirection vers `/select-mandate` si mandats présents | ✅ | |

---

### `/select-mandate` — Choix du mandat

| Fonctionnalité | Tous profils | Notes |
|---|:---:|---|
| Voir profil de base et mandats disponibles | ✅ | Authentifié |
| Sélectionner "sans mandat" | ✅ | |
| Sélectionner un mandat (profil effectif modifié) | ✅ | |
| Bascule langue FR/EN | ✅ | |

---

### `/` — Tableau de bord

| Fonctionnalité | P1 | P2 | P3 | P4 | P5 | P6 | P7 | P8 | P9 | P10 |
|---|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| Carte Compétitions | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Carte Équipes | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Carte Journées / Phases | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Carte Classements | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Carte Documents | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Carte Matchs | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Carte Statistiques | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Sélecteur saison / compétition | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

> Guard : `hasProfile(9)` pour les cartes secondaires, `hasProfile(10)` pour la carte Compétitions.

---

### `/users` — Utilisateurs

> **Accès** : profil ≤ 4 requis (redirection vers `/` sinon).
>
> **Règle de hiérarchie** : un utilisateur ne peut créer, modifier ou gérer les mandats que d'utilisateurs ayant un profil **strictement supérieur** au sien — sauf le profil 1 (Super Admin) qui n'a pas cette restriction.

| Fonctionnalité | P1 | P2 | P3 | P4 | P5+ |
|---|:--:|:--:|:--:|:--:|:--:|
| Accès à la page | ✅ | ✅ | ✅ | ✅ | ❌ |
| Recherche (identifiant / email / nom) | ✅ | ✅ | ✅ | ✅ | — |
| Filtres (profil, saison) | ✅ | ✅ | ✅ | ✅ | — |
| Pagination (20/page) | ✅ | ✅ | ✅ | ✅ | — |
| Créer un utilisateur (profil > sien) | ✅ | ✅ | ✅ | ❌ | — |
| Modifier un utilisateur (profil > sien) | ✅ | ✅ | ✅ | ❌ | — |
| Gérer les mandats (profil > sien) | ✅ | ✅ | ✅ | ❌ | — |
| Supprimer un utilisateur | ✅ | ✅ | ❌ | ❌ | — |
| Suppression en masse | ✅ | ✅ | ❌ | ❌ | — |
| Voir infos mandat (infobulle) | ✅ | ✅ | ✅ | ✅ | — |
| Lien vers journal d'activité | ✅ | ✅ | ❌ | ❌ | — |

> Cas à tester : un P2 ne doit pas pouvoir créer/modifier un P1 ou un P2 ; un P3 ne doit pas pouvoir toucher un P2.

---

### `/competitions` — Compétitions

| Fonctionnalité | P1 | P2 | P3 | P4 | P5+ |
|---|:--:|:--:|:--:|:--:|:--:|
| Lister les compétitions par section | ✅ | ✅ | ✅ | ✅ | ✅ |
| Rechercher une compétition | ✅ | ✅ | ✅ | ✅ | ✅ |
| Créer une compétition | ✅ | ✅ | ✅ | ❌ | ❌ |
| Modifier une compétition | ✅ | ✅ | ✅ | ❌ | ❌ |
| Publier / verrouiller une compétition | ✅ | ✅ | ✅ | ✅ | ❌ |
| Supprimer une compétition | ✅ | ✅ | ❌ | ❌ | ❌ |
| Suppression en masse | ✅ | ✅ | ❌ | ❌ | ❌ |
| Sélecteur multi-compétitions | ✅ | ✅ | ✅ | ✅ | ✅ |
| Éditeur grille de points | ✅ | ✅ | ✅ | ❌ | ❌ |
| Gestion logo / bandeau / sponsor | ✅ | ✅ | ✅ | ❌ | ❌ |
| Importer depuis saison précédente | ✅ | ✅ | ❌ | ❌ | ❌ |

---

### `/competitions/copy` — Copie de compétition

> **Accès** : profil ≤ 3 requis.

| Fonctionnalité | P1 | P2 | P3 | P4+ |
|---|:--:|:--:|:--:|:--:|
| Accès à la page | ✅ | ✅ | ✅ | ❌ |
| Rechercher un schéma (nb équipes, type, tri) | ✅ | ✅ | ✅ | — |
| Copier un schéma de compétition | ✅ | ✅ | ✅ | — |
| Éditer le commentaire de copie | ✅ | ✅ | ✅ | — |

---

### `/teams` — Équipes

| Fonctionnalité | P1 | P2 | P3 | P4 | P5+ |
|---|:--:|:--:|:--:|:--:|:--:|
| Lister les équipes | ✅ | ✅ | ✅ | ✅ | ✅ |
| Édition inline (poule / tirage) | ✅ | ✅ | ✅ | ✅ | ? |
| Formulaire couleurs d'équipe | ✅ | ✅ | ✅ | ✅ | ? |
| Dupliquer une équipe | ✅ | ✅ | ✅ | ✅ | ? |
| Ajouter une équipe | ✅ | ✅ | ✅ | ✅ | ? |
| PDF global (feuilles, compo, classement) | ✅ | ✅ | ✅ | ✅ | ✅ |
| PDF présence équipe individuelle | ✅ | ✅ | ✅ | ✅ | ✅ |
| Cases à cocher (sélection) | ✅ | ✅ | ✅ | ✅ | ? |

---

### `/gamedays` — Journées

| Fonctionnalité | P1 | P2 | P3 | P4 | P5+ |
|---|:--:|:--:|:--:|:--:|:--:|
| Lister les journées (filtres, tri) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Créer une journée | ✅ | ✅ | ✅ | ✅ | ❌ |
| Modifier une journée | ✅ | ✅ | ✅ | ✅ | ❌ |
| Supprimer une journée | ✅ | ✅ | ✅ | ✅ | ❌ |
| Suppression en masse | ✅ | ✅ | ✅ | ✅ | ❌ |
| Dupliquer une journée | ✅ | ✅ | ✅ | ✅ | ❌ |
| Mise à jour calendrier en masse | ✅ | ✅ | ✅ | ✅ | ❌ |
| Mise à jour officiels en masse | ✅ | ✅ | ✅ | ✅ | ❌ |
| Modal officiels (arbitres) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Édition inline (date, lieu) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Publier des journées | ✅ | ✅ | ✅ | ✅ | ❌ |
| Lien vers schéma | ✅ | ✅ | ✅ | ✅ | ✅ |
| Sélection (cases à cocher) | ✅ | ✅ | ✅ | ❌ | ❌ |

> Guard édition : `profile <= 4` | Guard sélection : `profile <= 3`.

---

### `/gamedays/schema` — Schéma de journée

| Fonctionnalité | Tous | Notes |
|---|:---:|---|
| Accès à la page | ✅ | Authentifié |

> À compléter lors des tests — composants `schema/` à explorer en détail.

---

### `/games` — Matchs

| Fonctionnalité | P1 | P2 | P3 | P4 | P5+ |
|---|:--:|:--:|:--:|:--:|:--:|
| Lister les matchs (filtres complets) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Créer un match | ✅ | ✅ | ✅ | ✅ | ? |
| Modifier un match | ✅ | ✅ | ✅ | ✅ | ? |
| Supprimer un match | ✅ | ✅ | ✅ | ✅ | ? |
| Suppression en masse | ✅ | ✅ | ✅ | ✅ | ? |
| Changer journée en masse | ✅ | ✅ | ✅ | ✅ | ? |
| Renuméroter en masse | ✅ | ✅ | ✅ | ✅ | ? |
| Changer date en masse | ✅ | ✅ | ✅ | ✅ | ? |
| Décaler horaires en masse | ✅ | ✅ | ✅ | ✅ | ? |
| Changer groupe en masse | ✅ | ✅ | ✅ | ✅ | ? |
| Publier des matchs | ✅ | ✅ | ✅ | ✅ | ? |
| Verrouiller / déverrouiller | ✅ | ✅ | ✅ | ✅ | ? |
| Édition inline (date, heure, terrain…) | ✅ | ✅ | ✅ | ✅ | ? |
| Filtre par statut verrouillage | ✅ | ✅ | ✅ | ✅ | ✅ |
| Pagination (50/page) | ✅ | ✅ | ✅ | ✅ | ✅ |

---

### `/rankings` — Classements

| Fonctionnalité | P1 | P2 | P3 | P4 | P5 | P6 | P7+ |
|---|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| Voir classement calculé | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Voir classement publié | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Calculer le classement | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Édition inline des valeurs | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Publier le classement | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Dépublier le classement | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Consolider le classement | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Changer le type (CHPT/CP) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Changer le statut | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Transférer vers une autre saison | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Accéder aux classements initiaux | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Sélection mode / phase | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Inclure/exclure équipes déverrouillées | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |

> Guard vue calculée/publiée : `profile <= 6` | Guard édition inline / publication / dépublier : `profile <= 4` | Guard changer type / statut : `profile <= 3`.

---

### `/rankings/initial` — Classements initiaux

> **Accès** : profil ≤ 3 requis.

| Fonctionnalité | P1 | P2 | P3 | P4+ |
|---|:--:|:--:|:--:|:--:|
| Accès à la page | ✅ | ✅ | ✅ | ❌ |
| Charger le classement initial | ✅ | ✅ | ✅ | — |
| Édition inline des valeurs initiales | ✅ | ✅ | ✅ | — |
| Réinitialiser (modal de confirmation) | ✅ | ✅ | ✅ | — |

---

### `/clubs` — Clubs

| Fonctionnalité | P1 | P2 | P3+ |
|---|:--:|:--:|:--:|
| Vue carte interactive des clubs | ✅ | ✅ | ✅ |
| Recherche club (autocomplete) | ✅ | ✅ | ✅ |
| Voir équipes d'un club | ✅ | ✅ | ✅ |
| Géocoder une adresse | ✅ | ✅ | ❌ |
| Mettre à jour infos contact | ✅ | ✅ | ❌ |
| Ajouter un comité départemental | ✅ | ✅ | ❌ |
| Ajouter un club | ✅ | ✅ | ❌ |

> Guard édition : `profile <= 2`.

---

### `/clubs/team/[numero]` — Équipe d'un club

| Fonctionnalité | Tous | Notes |
|---|:---:|---|
| Accès à la page | ✅ | Authentifié |

> À compléter lors des tests.

---

### `/athletes` — Athlètes

| Fonctionnalité | P1 | P2 | P3+ |
|---|:--:|:--:|:--:|
| Rechercher un athlète (nom / matricule) | ✅ | ✅ | ✅ |
| Filtres (région, dép, club, sexe, niveau arb) | ✅ | ✅ | ✅ |
| Filtres en cascade (région → dép → club) | ✅ | ✅ | ✅ |
| Voir détails et participations | ✅ | ✅ | ✅ |
| Modifier un athlète | ✅ | ✅ | ❌ |

> Guard édition : `profile <= 2`.

---

### `/events` — Événements

| Fonctionnalité | P1 | P2 | P3 | P4 | P5+ |
|---|:--:|:--:|:--:|:--:|:--:|
| Lister les événements (pagination) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Rechercher un événement | ✅ | ✅ | ✅ | ✅ | ✅ |
| Trier (date/nom ASC-DESC) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Créer un événement | ✅ | ✅ | ✅ | ✅ | ? |
| Modifier un événement | ✅ | ✅ | ✅ | ✅ | ? |
| Supprimer un événement | ✅ | ✅ | ✅ | ✅ | ? |
| Suppression en masse | ✅ | ✅ | ✅ | ✅ | ? |

---

### `/events/[id]/gamedays` — Journées d'un événement

| Fonctionnalité | Tous | Notes |
|---|:---:|---|
| Accès à la page | ✅ | Authentifié |

> À compléter lors des tests.

---

### `/groups` — Groupes

| Fonctionnalité | P1 | P2 | P3 | P4 | P5+ |
|---|:--:|:--:|:--:|:--:|:--:|
| Lister les groupes (accordion par section) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Rechercher un groupe | ✅ | ✅ | ✅ | ✅ | ✅ |
| Créer un groupe | ✅ | ✅ | ✅ | ✅ | ? |
| Modifier un groupe | ✅ | ✅ | ✅ | ✅ | ? |
| Supprimer un groupe | ✅ | ✅ | ✅ | ✅ | ? |
| Déplier / replier les sections | ✅ | ✅ | ✅ | ✅ | ✅ |

---

### `/documents` — Documents

| Fonctionnalité | P1 | P2 | P3+ |
|---|:--:|:--:|:--:|
| Générer des PDF de classement | ✅ | ✅ | ✅ |
| Filtrer par événement | ✅ | ✅ | ✅ |
| Filtrer par IDs de matchs | ✅ | ✅ | ✅ |
| Sélection d'événement (options avancées) | ✅ | ✅ | ❌ |

> Guard options avancées : `profile <= 2`.

---

### `/stats` — Statistiques

| Fonctionnalité | Tous | Notes |
|---|:---:|---|
| Sélectionner le type (Buteurs, Passes…) | ✅ | Authentifié |
| Filtrer par saison / compétition | ✅ | |
| Paramétrer le nb de résultats | ✅ | |
| Voir le tableau de stats | ✅ | |
| Télécharger / exporter | ✅ | |

---

### `/stats/[type]/[saison]/[competition]` — Détail stats

| Fonctionnalité | Tous | Notes |
|---|:---:|---|
| Accès à la page | ✅ | Authentifié |

> À compléter lors des tests.

---

### `/rc` — Commission des arbitres

| Fonctionnalité | P1 | P2 | P3+ |
|---|:--:|:--:|:--:|
| Lister les arbitres | ✅ | ✅ | ✅ |
| Rechercher (nom / matricule) | ✅ | ✅ | ✅ |
| Filtrer (compétition, groupe, événement) | ✅ | ✅ | ✅ |
| Ajouter un arbitre | ✅ | ✅ | ❌ |
| Modifier un arbitre | ✅ | ✅ | ❌ |
| Supprimer un arbitre | ✅ | ✅ | ❌ |
| Copier RC depuis une autre saison | ✅ | ✅ | ❌ |

> Guard édition/suppression/copie : `profile <= 2`.

---

### `/journal` — Journal d'activité

> **Accès** : profil ≤ 2 requis (redirection vers `/` sinon).

| Fonctionnalité | P1 | P2 | P3+ |
|---|:--:|:--:|:--:|
| Accès à la page | ✅ | ✅ | ❌ |
| Voir le journal d'audit | ✅ | ✅ | — |
| Filtrer par utilisateur | ✅ | ✅ | — |
| Filtrer par action (Connexion / Ajout / Modif…) | ✅ | ✅ | — |
| Filtrer par saison / compétition | ✅ | ✅ | — |
| Filtrer par plage de dates | ✅ | ✅ | — |
| Pagination | ✅ | ✅ | — |

---

### `/tv` — TV / Présentation

> **Accès** : profil ≤ 2 requis (redirection vers `/` sinon).

| Fonctionnalité | P1 | P2 | P3+ |
|---|:--:|:--:|:--:|
| Accès à la page | ✅ | ✅ | ❌ |
| Sélecteur et configuration de chaîne | ✅ | ✅ | — |
| Éditeur de scénario | ✅ | ✅ | — |
| Filtres globaux (événement, date, CSS, langue) | ✅ | ✅ | — |
| Aperçu de la présentation | ✅ | ✅ | — |
| Modal labels (chaînes / scénarios) | ✅ | ✅ | — |
| Gestion des panneaux dynamiques | ✅ | ✅ | — |

---

### `/presence/team/[teamId]` — Présence équipe

| Fonctionnalité | Selon verrou | Notes |
|---|:---:|---|
| Voir la composition | ✅ | Authentifié |
| Édition inline (numéro, capitaine) | Permissions présence | Via `usePresencePermissions` |
| Ajouter un joueur existant | Permissions présence | |
| Créer un nouveau joueur (avec détection doublon) | Permissions présence | |
| Copier compo depuis une autre équipe | Permissions présence | |
| Recherche joueur (autocomplete) | Permissions présence | |
| Verrouiller / déverrouiller la compo | Permissions présence | |
| Cases à cocher (sélection) | Permissions présence | |

> Les permissions de présence dépendent du contexte (verrou + profil effectif). Tester avec différentes combinaisons verrouillé / déverrouillé.

---

### `/presence/match/[matchId]/team/[teamCode]` — Présence match

| Fonctionnalité | Tous | Notes |
|---|:---:|---|
| Accès à la page | ✅ | Authentifié |

> À compléter lors des tests — similaire à la présence équipe mais contextuelle au match.

---

### `/live/cache-manager` — Gestionnaire de cache live

> **Accès** : profil ≤ 2 requis (redirection vers `/` sinon).

| Fonctionnalité | P1 | P2 | P3+ |
|---|:--:|:--:|:--:|
| Accès à la page | ✅ | ✅ | ❌ |
| Démarrer / mettre en pause / reprendre le worker | ✅ | ✅ | — |
| Surveiller le statut de génération | ✅ | ✅ | — |
| Configurer le worker (événement, date, heure, terrain, délai) | ✅ | ✅ | — |
| Arrêter le worker (modal confirmation) | ✅ | ✅ | — |

---

### `/operations` — Opérations

> **Accès** : profil ≤ 2 requis (redirection vers `/` sinon).

| Fonctionnalité | P1 | P2 | P3+ |
|---|:--:|:--:|:--:|
| Accès à la page | ✅ | ✅ | ❌ |
| Onglet Images (upload / gestion) | ✅ | ✅ | — |
| Onglet Joueurs (opérations en masse) | ✅ | ✅ | — |
| Onglet Équipes (opérations en masse) | ✅ | ✅ | — |
| Onglet Codes (gestion codes système) | ✅ | ✅ | — |
| Onglet Import/Export | ✅ | ✅ | — |
| Onglet Saisons | ✅ | ✅ | — |
| Onglet Système | ✅ | ✅ | — |

---

## Notes de test

- **Légende** : ✅ accessible | ❌ non accessible | `?` à vérifier | `—` non applicable
- **P = Profil** ; un profil plus bas donne plus de droits.
- Les cellules `?` indiquent qu'aucun guard explicite n'a été trouvé dans le code — comportement à confirmer en pratique.
- Les pages sans guard de profil explicite sont accessibles à tous les profils authentifiés.
- Le **profil effectif** peut différer du profil de base si un mandat est sélectionné.
- Les profils **5, 6, 7** n'ont pas de traitement spécifique identifié dans le code : ils héritent des règles `<= 4` (accès refusé) ou `<= 9` (accès accordé) selon la page.

---

*Généré le 2026-05-10 — à mettre à jour après chaque campagne de test.*
