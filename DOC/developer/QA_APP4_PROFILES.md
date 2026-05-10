# QA App4 — Checklist par profil

> Référence rapide pour tester toutes les fonctionnalités accessibles à chaque profil, page par page.
>
> **Règle de profil** : `hasProfile(N)` retourne `true` si `effectiveProfile <= N`.
> Un profil **bas** = droits **élevés** (1 = super admin).
>
> **Légende** : ✅ accessible | ❌ non accessible | `?` à vérifier | `—` non applicable

---

## Profils

| Profil | Nom |
|--------|-----|
| **1** | Super Admin |
| **2** | Bureau CNAKP |
| **3** | Resp. Division |
| **4** | Resp. Poule/Compétition |
| **5** | Délégué fédéral |
| **6** | Organisateur journée |
| **7** | Resp. club/équipe |
| **8** | Consultation simple |
| **9** | Table de marque |
| **10** | (Inutilisé) |

> Le **profil effectif** peut différer du profil de base si un mandat est sélectionné.

---

## Profil 1 — Super Admin

### `/login` — Connexion
- [ ] Formulaire login (identifiant + mot de passe)
- [ ] Bascule langue FR/EN
- [ ] Message d'erreur profil restreint
- [ ] Redirection vers `/select-mandate` si mandats présents

### `/select-mandate` — Choix du mandat
- [ ] Voir profil de base et mandats disponibles
- [ ] Sélectionner "sans mandat"
- [ ] Sélectionner un mandat (profil effectif modifié)
- [ ] Bascule langue FR/EN

### `/` — Tableau de bord
- [ ] Carte Compétitions
- [ ] Carte Équipes
- [ ] Carte Journées / Phases
- [ ] Carte Classements
- [ ] Carte Documents
- [ ] Carte Matchs
- [ ] Carte Statistiques
- [ ] Sélecteur saison / compétition

### `/users` — Utilisateurs
- [ ] Accès à la page
- [ ] Recherche (identifiant / email / nom)
- [ ] Filtres (profil, saison)
- [ ] Pagination (20/page)
- [ ] Créer un utilisateur (profil > sien — aucune restriction pour P1)
- [ ] Modifier un utilisateur (profil > sien — aucune restriction pour P1)
- [ ] Gérer les mandats (profil > sien — aucune restriction pour P1)
  - [ ] Formulaire mandat : au moins une saison obligatoire (pas de case "Toutes les saisons")
  - [ ] Formulaire mandat profil >= 3 : au moins une compétition obligatoire (case "Toutes les compétitions" masquée)
  - [ ] Formulaire mandat profil 5 ou 6 : au moins une journée obligatoire
  - [ ] Formulaire mandat profil 7 : au moins un club obligatoire
  - [ ] Changer le profil du mandat vers >= 3 décoche automatiquement "Toutes les compétitions"
- [ ] Supprimer un utilisateur
- [ ] Suppression en masse
- [ ] Voir infos mandat (infobulle)
- [ ] Lien vers journal d'activité

### `/competitions` — Compétitions
- [ ] Lister les compétitions par section
- [ ] Rechercher une compétition
- [ ] Créer une compétition
- [ ] Modifier une compétition
- [ ] Publier / verrouiller une compétition
- [ ] Supprimer une compétition
- [ ] Suppression en masse
- [ ] Sélecteur multi-compétitions
- [ ] Éditeur grille de points
- [ ] Gestion logo / bandeau / sponsor
- [ ] Importer depuis saison précédente

### `/competitions/copy` — Copie de compétition
- [ ] Accès à la page
- [ ] Rechercher un schéma (nb équipes, type, tri)
- [ ] Copier un schéma de compétition
- [ ] Éditer le commentaire de copie

### `/teams` — Équipes
- [ ] Lister les équipes
- [ ] Édition inline (poule / tirage)
- [ ] Formulaire couleurs d'équipe
- [ ] Dupliquer une équipe
- [ ] Ajouter une équipe
- [ ] PDF global (feuilles, compo, classement)
- [ ] PDF présence équipe individuelle
- [ ] Cases à cocher (sélection)

### `/gamedays` — Journées
- [ ] Lister les journées (filtres, tri)
- [ ] Créer une journée
- [ ] Modifier une journée
- [ ] Supprimer une journée
- [ ] Suppression en masse
- [ ] Dupliquer une journée
- [ ] Mise à jour calendrier en masse
- [ ] Mise à jour officiels en masse
- [ ] Modal officiels (arbitres)
- [ ] Édition inline (date, lieu)
- [ ] Publier des journées
- [ ] Lien vers schéma
- [ ] Sélection (cases à cocher)

### `/gamedays/schema` — Schéma de journée
- [ ] Accès à la page

### `/games` — Matchs
- [ ] Lister les matchs (filtres complets)
- [ ] Créer un match
- [ ] Modifier un match
- [ ] Supprimer un match
- [ ] Suppression en masse
- [ ] Changer journée en masse
- [ ] Renuméroter en masse
- [ ] Changer date en masse
- [ ] Décaler horaires en masse
- [ ] Changer groupe en masse
- [ ] Publier des matchs
- [ ] Verrouiller / déverrouiller
- [ ] Édition inline (date, heure, terrain…)
- [ ] Filtre par statut verrouillage
- [ ] Pagination (50/page)

### `/rankings` — Classements
- [ ] Voir classement calculé
- [ ] Voir classement publié
- [ ] Calculer le classement
- [ ] Édition inline des valeurs
- [ ] Publier le classement
- [ ] Dépublier le classement
- [ ] Consolider le classement
- [ ] Changer le type (CHPT/CP)
- [ ] Changer le statut
- [ ] Transférer vers une autre saison
- [ ] Accéder aux classements initiaux
- [ ] Sélection mode / phase
- [ ] Inclure/exclure équipes déverrouillées

### `/rankings/initial` — Classements initiaux
- [ ] Accès à la page
- [ ] Charger le classement initial
- [ ] Édition inline des valeurs initiales
- [ ] Réinitialiser (modal de confirmation)

### `/clubs` — Clubs
- [ ] Vue carte interactive des clubs
- [ ] Recherche club (autocomplete)
- [ ] Voir équipes d'un club
- [ ] Géocoder une adresse
- [ ] Mettre à jour infos contact
- [ ] Ajouter un comité départemental
- [ ] Ajouter un club

### `/clubs/team/[numero]` — Équipe d'un club
- [ ] Accès à la page

### `/athletes` — Athlètes
- [ ] Rechercher un athlète (nom / matricule)
- [ ] Filtres (région, dép, club, sexe, niveau arb)
- [ ] Filtres en cascade (région → dép → club)
- [ ] Voir détails et participations
- [ ] Modifier un athlète

### `/events` — Événements
- [ ] Lister les événements (pagination)
- [ ] Rechercher un événement
- [ ] Trier (date/nom ASC-DESC)
- [ ] Créer un événement
- [ ] Modifier un événement
- [ ] Supprimer un événement
- [ ] Suppression en masse

### `/events/[id]/gamedays` — Journées d'un événement
- [ ] Accès à la page

### `/groups` — Groupes
- [ ] Lister les groupes (accordion par section)
- [ ] Rechercher un groupe
- [ ] Créer un groupe
- [ ] Modifier un groupe
- [ ] Supprimer un groupe
- [ ] Déplier / replier les sections

### `/documents` — Documents
- [ ] Générer des PDF de classement
- [ ] Filtrer par événement
- [ ] Filtrer par IDs de matchs
- [ ] Sélection d'événement (options avancées)

### `/stats` — Statistiques
- [ ] Sélectionner le type (Buteurs, Passes…)
- [ ] Filtrer par saison / compétition
- [ ] Paramétrer le nb de résultats
- [ ] Voir le tableau de stats
- [ ] Télécharger / exporter

### `/stats/[type]/[saison]/[competition]` — Détail stats
- [ ] Accès à la page

### `/rc` — Commission des arbitres
- [ ] Lister les arbitres
- [ ] Rechercher (nom / matricule)
- [ ] Filtrer (compétition, groupe, événement)
- [ ] Ajouter un arbitre
- [ ] Modifier un arbitre
- [ ] Supprimer un arbitre
- [ ] Copier RC depuis une autre saison

### `/journal` — Journal d'activité
- [ ] Accès à la page
- [ ] Voir le journal d'audit
- [ ] Filtrer par utilisateur
- [ ] Filtrer par action (Connexion / Ajout / Modif…)
- [ ] Filtrer par saison / compétition
- [ ] Filtrer par plage de dates
- [ ] Pagination

### `/tv` — TV / Présentation
- [ ] Accès à la page
- [ ] Sélecteur et configuration de chaîne
- [ ] Éditeur de scénario
- [ ] Filtres globaux (événement, date, CSS, langue)
- [ ] Aperçu de la présentation
- [ ] Modal labels (chaînes / scénarios)
- [ ] Gestion des panneaux dynamiques

### `/presence/team/[teamId]` — Présence équipe
- [ ] Voir la composition
- [ ] Édition inline (numéro, capitaine)
- [ ] Ajouter un joueur existant
- [ ] Créer un nouveau joueur (avec détection doublon)
- [ ] Copier compo depuis une autre équipe
- [ ] Recherche joueur (autocomplete)
- [ ] Verrouiller / déverrouiller la compo
- [ ] Cases à cocher (sélection)

### `/presence/match/[matchId]/team/[teamCode]` — Présence match
- [ ] Accès à la page

### `/live/cache-manager` — Gestionnaire de cache live
- [ ] Accès à la page
- [ ] Démarrer / mettre en pause / reprendre le worker
- [ ] Surveiller le statut de génération
- [ ] Configurer le worker (événement, date, heure, terrain, délai)
- [ ] Arrêter le worker (modal confirmation)

### `/operations` — Opérations
- [ ] Accès à la page
- [ ] Onglet Images (upload / gestion)
- [ ] Onglet Joueurs (opérations en masse)
- [ ] Onglet Équipes (opérations en masse)
- [ ] Onglet Codes (gestion codes système)
- [ ] Onglet Import/Export
- [ ] Onglet Saisons
- [ ] Onglet Système

---

## Profil 2 — Bureau CNAKP

### `/login` — Connexion
- [ ] Formulaire login (identifiant + mot de passe)
- [ ] Bascule langue FR/EN
- [ ] Message d'erreur profil restreint
- [ ] Redirection vers `/select-mandate` si mandats présents

### `/select-mandate` — Choix du mandat
- [ ] Voir profil de base et mandats disponibles
- [ ] Sélectionner "sans mandat"
- [ ] Sélectionner un mandat (profil effectif modifié)
- [ ] Bascule langue FR/EN

### `/` — Tableau de bord
- [ ] Carte Compétitions
- [ ] Carte Équipes
- [ ] Carte Journées / Phases
- [ ] Carte Classements
- [ ] Carte Documents
- [ ] Carte Matchs
- [ ] Carte Statistiques
- [ ] Sélecteur saison / compétition

### `/users` — Utilisateurs
> P2 ne doit pas pouvoir créer/modifier un P1 ou un P2.

- [ ] Accès à la page
- [ ] Recherche (identifiant / email / nom)
- [ ] Filtres (profil, saison)
- [ ] Pagination (20/page)
- [ ] Créer un utilisateur (profil > 2 uniquement)
- [ ] Modifier un utilisateur (profil > 2 uniquement)
- [ ] Gérer les mandats (profil > 2 uniquement)
- [ ] Supprimer un utilisateur
- [ ] Suppression en masse
- [ ] Voir infos mandat (infobulle)
- [ ] Lien vers journal d'activité
- [ ] ❌ Créer/modifier un P1 ou P2

### `/competitions` — Compétitions
- [ ] Lister les compétitions par section
- [ ] Rechercher une compétition
- [ ] Créer une compétition
- [ ] Modifier une compétition
- [ ] Publier / verrouiller une compétition
- [ ] Supprimer une compétition
- [ ] Suppression en masse
- [ ] Sélecteur multi-compétitions
- [ ] Éditeur grille de points
- [ ] Gestion logo / bandeau / sponsor
- [ ] Importer depuis saison précédente

### `/competitions/copy` — Copie de compétition
- [ ] Accès à la page
- [ ] Rechercher un schéma (nb équipes, type, tri)
- [ ] Copier un schéma de compétition
- [ ] Éditer le commentaire de copie

### `/teams` — Équipes
- [ ] Lister les équipes
- [ ] Édition inline (poule / tirage)
- [ ] Formulaire couleurs d'équipe
- [ ] Dupliquer une équipe
- [ ] Ajouter une équipe
- [ ] PDF global (feuilles, compo, classement)
- [ ] PDF présence équipe individuelle
- [ ] Cases à cocher (sélection)

### `/gamedays` — Journées
- [ ] Lister les journées (filtres, tri)
- [ ] Créer une journée
- [ ] Modifier une journée
- [ ] Supprimer une journée
- [ ] Suppression en masse
- [ ] Dupliquer une journée
- [ ] Mise à jour calendrier en masse
- [ ] Mise à jour officiels en masse
- [ ] Modal officiels (arbitres)
- [ ] Édition inline (date, lieu)
- [ ] Publier des journées
- [ ] Lien vers schéma
- [ ] Sélection (cases à cocher)

### `/gamedays/schema` — Schéma de journée
- [ ] Accès à la page

### `/games` — Matchs
- [ ] Lister les matchs (filtres complets)
- [ ] Créer un match
- [ ] Modifier un match
- [ ] Supprimer un match
- [ ] Suppression en masse
- [ ] Changer journée en masse
- [ ] Renuméroter en masse
- [ ] Changer date en masse
- [ ] Décaler horaires en masse
- [ ] Changer groupe en masse
- [ ] Publier des matchs
- [ ] Verrouiller / déverrouiller
- [ ] Édition inline (date, heure, terrain…)
- [ ] Filtre par statut verrouillage
- [ ] Pagination (50/page)

### `/rankings` — Classements
- [ ] Voir classement calculé
- [ ] Voir classement publié
- [ ] Calculer le classement
- [ ] Édition inline des valeurs
- [ ] Publier le classement
- [ ] Dépublier le classement
- [ ] Consolider le classement
- [ ] Changer le type (CHPT/CP)
- [ ] Changer le statut
- [ ] Transférer vers une autre saison
- [ ] Accéder aux classements initiaux
- [ ] Sélection mode / phase
- [ ] Inclure/exclure équipes déverrouillées

### `/rankings/initial` — Classements initiaux
- [ ] Accès à la page
- [ ] Charger le classement initial
- [ ] Édition inline des valeurs initiales
- [ ] Réinitialiser (modal de confirmation)

### `/clubs` — Clubs
- [ ] Vue carte interactive des clubs
- [ ] Recherche club (autocomplete)
- [ ] Voir équipes d'un club
- [ ] Géocoder une adresse
- [ ] Mettre à jour infos contact
- [ ] Ajouter un comité départemental
- [ ] Ajouter un club

### `/clubs/team/[numero]` — Équipe d'un club
- [ ] Accès à la page

### `/athletes` — Athlètes
- [ ] Rechercher un athlète (nom / matricule)
- [ ] Filtres (région, dép, club, sexe, niveau arb)
- [ ] Filtres en cascade (région → dép → club)
- [ ] Voir détails et participations
- [ ] Modifier un athlète

### `/events` — Événements
- [ ] Lister les événements (pagination)
- [ ] Rechercher un événement
- [ ] Trier (date/nom ASC-DESC)
- [ ] Créer un événement
- [ ] Modifier un événement
- [ ] Supprimer un événement
- [ ] Suppression en masse

### `/events/[id]/gamedays` — Journées d'un événement
- [ ] Accès à la page

### `/groups` — Groupes
- [ ] Lister les groupes (accordion par section)
- [ ] Rechercher un groupe
- [ ] Créer un groupe
- [ ] Modifier un groupe
- [ ] Supprimer un groupe
- [ ] Déplier / replier les sections

### `/documents` — Documents
- [ ] Générer des PDF de classement
- [ ] Filtrer par événement
- [ ] Filtrer par IDs de matchs
- [ ] Sélection d'événement (options avancées)

### `/stats` — Statistiques
- [ ] Sélectionner le type (Buteurs, Passes…)
- [ ] Filtrer par saison / compétition
- [ ] Paramétrer le nb de résultats
- [ ] Voir le tableau de stats
- [ ] Télécharger / exporter

### `/stats/[type]/[saison]/[competition]` — Détail stats
- [ ] Accès à la page

### `/rc` — Commission des arbitres
- [ ] Lister les arbitres
- [ ] Rechercher (nom / matricule)
- [ ] Filtrer (compétition, groupe, événement)
- [ ] Ajouter un arbitre
- [ ] Modifier un arbitre
- [ ] Supprimer un arbitre
- [ ] Copier RC depuis une autre saison

### `/journal` — Journal d'activité
- [ ] Accès à la page
- [ ] Voir le journal d'audit
- [ ] Filtrer par utilisateur
- [ ] Filtrer par action (Connexion / Ajout / Modif…)
- [ ] Filtrer par saison / compétition
- [ ] Filtrer par plage de dates
- [ ] Pagination

### `/tv` — TV / Présentation
- [ ] Accès à la page
- [ ] Sélecteur et configuration de chaîne
- [ ] Éditeur de scénario
- [ ] Filtres globaux (événement, date, CSS, langue)
- [ ] Aperçu de la présentation
- [ ] Modal labels (chaînes / scénarios)
- [ ] Gestion des panneaux dynamiques

### `/presence/team/[teamId]` — Présence équipe
- [ ] Voir la composition
- [ ] Édition inline (numéro, capitaine)
- [ ] Ajouter un joueur existant
- [ ] Créer un nouveau joueur (avec détection doublon)
- [ ] Copier compo depuis une autre équipe
- [ ] Recherche joueur (autocomplete)
- [ ] Verrouiller / déverrouiller la compo
- [ ] Cases à cocher (sélection)

### `/presence/match/[matchId]/team/[teamCode]` — Présence match
- [ ] Accès à la page

### `/live/cache-manager` — Gestionnaire de cache live
- [ ] Accès à la page
- [ ] Démarrer / mettre en pause / reprendre le worker
- [ ] Surveiller le statut de génération
- [ ] Configurer le worker (événement, date, heure, terrain, délai)
- [ ] Arrêter le worker (modal confirmation)

### `/operations` — Opérations
- [ ] Accès à la page
- [ ] Onglet Images (upload / gestion)
- [ ] Onglet Joueurs (opérations en masse)
- [ ] Onglet Équipes (opérations en masse)
- [ ] Onglet Codes (gestion codes système)
- [ ] Onglet Import/Export
- [ ] Onglet Saisons
- [ ] Onglet Système

---

## Profil 3 — Resp. Division

### `/login` — Connexion
- [ ] Formulaire login (identifiant + mot de passe)
- [ ] Bascule langue FR/EN
- [ ] Message d'erreur profil restreint
- [ ] Redirection vers `/select-mandate` si mandats présents

### `/select-mandate` — Choix du mandat
- [ ] Voir profil de base et mandats disponibles
- [ ] Sélectionner "sans mandat"
- [ ] Sélectionner un mandat (profil effectif modifié)
- [ ] Bascule langue FR/EN

### `/` — Tableau de bord
- [ ] Carte Compétitions
- [ ] Carte Équipes
- [ ] Carte Journées / Phases
- [ ] Carte Classements
- [ ] Carte Documents
- [ ] Carte Matchs
- [ ] Carte Statistiques
- [ ] Sélecteur saison / compétition

### `/users` — Utilisateurs
> P3 ne doit pas pouvoir toucher un P2 ou inférieur.

- [ ] Accès à la page
- [ ] Recherche (identifiant / email / nom)
- [ ] Filtres (profil, saison)
- [ ] Pagination (20/page)
- [ ] Créer un utilisateur (profil >= 4 uniquement)
- [ ] Voir infos mandat (infobulle)
- [ ] Gérer les mandats d'un utilisateur (profil > 3 uniquement) — ajout et suppression uniquement
  - [ ] Formulaire modal en mode "mandats seuls" : bandeau d'information affiché, champs profil/saisons/compétitions/clubs masqués
  - [ ] Bouton "Fermer" (pas de bouton "Enregistrer")
- [ ] ❌ Modifier les champs de base d'un utilisateur (profil, saisons, compétitions, clubs…)
- [ ] ❌ Supprimer un utilisateur
- [ ] ❌ Suppression en masse
- [ ] ❌ Lien vers journal d'activité
- [ ] ❌ Créer/modifier un P1, P2 ou P3

### `/competitions` — Compétitions
- [ ] Lister les compétitions par section
- [ ] Rechercher une compétition
- [ ] Modifier une compétition
- [ ] Publier / verrouiller une compétition
- [ ] Sélecteur multi-compétitions
- [ ] Éditeur grille de points
- [ ] Gestion logo / bandeau / sponsor
- [ ] ❌ Créer une compétition
- [ ] ❌ Supprimer une compétition
- [ ] ❌ Suppression en masse
- [ ] ❌ Importer depuis saison précédente

### `/competitions/copy` — Copie de compétition
- [ ] ❌ Accès à la page (redirection)

### `/teams` — Équipes
- [ ] Lister les équipes
- [ ] Édition inline (poule / tirage)
- [ ] ❌ Formulaire couleurs d'équipe (guard `profile <= 2`)
- [ ] Dupliquer une équipe
- [ ] Ajouter une équipe
- [ ] PDF global (feuilles, compo, classement)
- [ ] PDF présence équipe individuelle
- [ ] Cases à cocher (sélection)

### `/gamedays` — Journées
- [ ] Lister les journées (filtres, tri)
- [ ] Créer une journée
- [ ] Modifier une journée
- [ ] Supprimer une journée
- [ ] Suppression en masse
- [ ] Dupliquer une journée
- [ ] Mise à jour calendrier en masse
- [ ] Mise à jour officiels en masse
- [ ] Modal officiels (arbitres)
- [ ] Édition inline (date, lieu)
- [ ] Publier des journées
- [ ] Lien vers schéma
- [ ] Sélection (cases à cocher)

### `/gamedays/schema` — Schéma de journée
- [ ] Accès à la page

### `/games` — Matchs
- [ ] Lister les matchs (filtres complets)
- [ ] Créer un match
- [ ] Modifier un match
- [ ] Supprimer un match
- [ ] Suppression en masse
- [ ] Changer journée en masse
- [ ] Renuméroter en masse
- [ ] Changer date en masse
- [ ] Décaler horaires en masse
- [ ] Changer groupe en masse
- [ ] Publier des matchs
- [ ] Verrouiller / déverrouiller
- [ ] Édition inline (date, heure, terrain…)
- [ ] Filtre par statut verrouillage
- [ ] Pagination (50/page)

### `/rankings` — Classements
- [ ] Voir classement calculé
- [ ] Voir classement publié
- [ ] Calculer le classement
- [ ] Édition inline des valeurs
- [ ] Publier le classement
- [ ] Dépublier le classement
- [ ] Consolider le classement
- [ ] Changer le statut
- [ ] Transférer vers une autre saison
- [ ] Accéder aux classements initiaux
- [ ] Sélection mode / phase
- [ ] Inclure/exclure équipes déverrouillées
- [ ] ❌ Changer le type (CHPT/CP) — guard `profile <= 2`

### `/rankings/initial` — Classements initiaux
- [ ] Accès à la page
- [ ] Charger le classement initial
- [ ] Édition inline des valeurs initiales
- [ ] Réinitialiser (modal de confirmation)

### `/clubs` — Clubs
- [ ] Vue carte interactive des clubs
- [ ] Recherche club (autocomplete)
- [ ] Voir équipes d'un club
- [ ] ❌ Géocoder une adresse
- [ ] ❌ Mettre à jour infos contact
- [ ] ❌ Ajouter un comité départemental
- [ ] ❌ Ajouter un club

### `/clubs/team/[numero]` — Équipe d'un club
- [ ] Accès à la page

### `/athletes` — Athlètes
- [ ] Rechercher un athlète (nom / matricule)
- [ ] Filtres (région, dép, club, sexe, niveau arb)
- [ ] Filtres en cascade (région → dép → club)
- [ ] Voir détails et participations
- [ ] ❌ Modifier un athlète

### `/events` — Événements
- [ ] ❌ Accès à la page (menu masqué — guard `profile <= 2`)

### `/events/[id]/gamedays` — Journées d'un événement
- [ ] ❌ Accès à la page

### `/groups` — Groupes
- [ ] ❌ Accès à la page (menu masqué — guard `profile <= 2`)

### `/documents` — Documents
- [ ] Générer des PDF de classement
- [ ] Filtrer par événement
- [ ] Filtrer par IDs de matchs
- [ ] ❌ Sélection d'événement (options avancées)

### `/stats` — Statistiques
- [ ] Sélectionner le type (Buteurs, Passes…)
- [ ] Filtrer par saison / compétition
- [ ] Paramétrer le nb de résultats
- [ ] Voir le tableau de stats
- [ ] Télécharger / exporter

### `/stats/[type]/[saison]/[competition]` — Détail stats
- [ ] Accès à la page

### `/rc` — Commission des arbitres
- [ ] ❌ Accès à la page (menu masqué — guard `profile <= 2`)

### `/journal` — Journal d'activité
- [ ] ❌ Accès à la page (redirection vers `/`)

### `/tv` — TV / Présentation
- [ ] ❌ Accès à la page (redirection vers `/`)

### `/presence/team/[teamId]` — Présence équipe
- [ ] Voir la composition
- [ ] Édition inline (numéro, capitaine) — selon permissions présence
- [ ] Ajouter un joueur existant — selon permissions présence
- [ ] Créer un nouveau joueur (avec détection doublon) — selon permissions présence
- [ ] Copier compo depuis une autre équipe — selon permissions présence
- [ ] Recherche joueur (autocomplete) — selon permissions présence
- [ ] Verrouiller / déverrouiller la compo — selon permissions présence
- [ ] Cases à cocher (sélection) — selon permissions présence

### `/presence/match/[matchId]/team/[teamCode]` — Présence match
- [ ] Accès à la page

### `/live/cache-manager` — Gestionnaire de cache live
- [ ] ❌ Accès à la page (redirection vers `/`)

### `/operations` — Opérations
- [ ] ❌ Accès à la page (redirection vers `/`)

---

## Profil 4 — Resp. Poule/Compétition

### `/login` — Connexion
- [ ] Formulaire login (identifiant + mot de passe)
- [ ] Bascule langue FR/EN
- [ ] Message d'erreur profil restreint
- [ ] Redirection vers `/select-mandate` si mandats présents

### `/select-mandate` — Choix du mandat
- [ ] Voir profil de base et mandats disponibles
- [ ] Sélectionner "sans mandat"
- [ ] Sélectionner un mandat (profil effectif modifié)
- [ ] Bascule langue FR/EN

### `/` — Tableau de bord
- [ ] Carte Compétitions
- [ ] Carte Équipes
- [ ] Carte Journées / Phases
- [ ] Carte Classements
- [ ] Carte Documents
- [ ] Carte Matchs
- [ ] Carte Statistiques
- [ ] Sélecteur saison / compétition

### `/users` — Utilisateurs
> P4 ne peut pas modifier les champs de base des utilisateurs, seulement gérer les mandats.

- [ ] Accès à la page
- [ ] Recherche (identifiant / email / nom)
- [ ] Filtres (profil, saison)
- [ ] Pagination (20/page)
- [ ] Voir infos mandat (infobulle)
- [ ] Créer un utilisateur (profil >= 5 uniquement)
- [ ] Gérer les mandats d'un utilisateur (profil > 4 uniquement) — ajout et suppression uniquement
  - [ ] Formulaire modal en mode "mandats seuls" : bandeau d'information affiché, champs profil/saisons/compétitions/clubs masqués
  - [ ] Bouton "Fermer" (pas de bouton "Enregistrer")
- [ ] ❌ Modifier les champs de base d'un utilisateur
- [ ] ❌ Supprimer un utilisateur
- [ ] ❌ Suppression en masse
- [ ] ❌ Lien vers journal d'activité

### `/competitions` — Compétitions
- [ ] Lister les compétitions par section
- [ ] Rechercher une compétition
- [ ] Publier / verrouiller une compétition
- [ ] Sélecteur multi-compétitions
- [ ] ❌ Créer une compétition
- [ ] ❌ Modifier une compétition
- [ ] ❌ Supprimer une compétition
- [ ] ❌ Suppression en masse
- [ ] ❌ Éditeur grille de points
- [ ] ❌ Gestion logo / bandeau / sponsor
- [ ] ❌ Importer depuis saison précédente

### `/competitions/copy` — Copie de compétition
- [ ] ❌ Accès à la page (redirection)

### `/teams` — Équipes
- [ ] Lister les équipes
- [ ] Édition inline (poule / tirage)
- [ ] ❌ Formulaire couleurs d'équipe (guard `profile <= 2`)
- [ ] Dupliquer une équipe
- [ ] Ajouter une équipe
- [ ] PDF global (feuilles, compo, classement)
- [ ] PDF présence équipe individuelle
- [ ] Cases à cocher (sélection)

### `/gamedays` — Journées
- [ ] Lister les journées (filtres, tri)
- [ ] Créer une journée
- [ ] Modifier une journée
- [ ] Supprimer une journée
- [ ] Suppression en masse
- [ ] Dupliquer une journée
- [ ] Mise à jour calendrier en masse
- [ ] Mise à jour officiels en masse
- [ ] Modal officiels (arbitres)
- [ ] Édition inline (date, lieu)
- [ ] Publier des journées
- [ ] Lien vers schéma
- [ ] ❌ Sélection (cases à cocher) — guard profile <= 3

### `/gamedays/schema` — Schéma de journée
- [ ] Accès à la page

### `/games` — Matchs
- [ ] Lister les matchs (filtres complets)
- [ ] Créer un match
- [ ] Modifier un match
- [ ] Supprimer un match
- [ ] Suppression en masse
- [ ] Changer journée en masse
- [ ] Renuméroter en masse
- [ ] Changer date en masse
- [ ] Décaler horaires en masse
- [ ] Changer groupe en masse
- [ ] Publier des matchs
- [ ] Verrouiller / déverrouiller
- [ ] Édition inline (date, heure, terrain…)
- [ ] Filtre par statut verrouillage
- [ ] Pagination (50/page)

### `/rankings` — Classements
- [ ] Voir classement calculé
- [ ] Voir classement publié
- [ ] Calculer le classement
- [ ] Édition inline des valeurs
- [ ] Publier le classement
- [ ] Dépublier le classement
- [ ] Consolider le classement
- [ ] Transférer vers une autre saison
- [ ] Sélection mode / phase
- [ ] Inclure/exclure équipes déverrouillées
- [ ] ❌ Changer le type (CHPT/CP) — guard `profile <= 2`
- [ ] ❌ Changer le statut
- [ ] ❌ Accéder aux classements initiaux

### `/rankings/initial` — Classements initiaux
- [ ] ❌ Accès à la page (redirection)

### `/clubs` — Clubs
- [ ] Vue carte interactive des clubs
- [ ] Recherche club (autocomplete)
- [ ] Voir équipes d'un club
- [ ] ❌ Géocoder une adresse
- [ ] ❌ Mettre à jour infos contact
- [ ] ❌ Ajouter un comité départemental
- [ ] ❌ Ajouter un club

### `/clubs/team/[numero]` — Équipe d'un club
- [ ] Accès à la page

### `/athletes` — Athlètes
- [ ] Rechercher un athlète (nom / matricule)
- [ ] Filtres (région, dép, club, sexe, niveau arb)
- [ ] Filtres en cascade (région → dép → club)
- [ ] Voir détails et participations
- [ ] ❌ Modifier un athlète

### `/events` — Événements
- [ ] Lister les événements (pagination)
- [ ] Rechercher un événement
- [ ] Trier (date/nom ASC-DESC)
- [ ] Créer un événement
- [ ] Modifier un événement
- [ ] Supprimer un événement
- [ ] Suppression en masse

### `/events/[id]/gamedays` — Journées d'un événement
- [ ] Accès à la page

### `/groups` — Groupes
- [ ] Lister les groupes (accordion par section)
- [ ] Rechercher un groupe
- [ ] Créer un groupe
- [ ] Modifier un groupe
- [ ] Supprimer un groupe
- [ ] Déplier / replier les sections

### `/documents` — Documents
- [ ] Générer des PDF de classement
- [ ] Filtrer par événement
- [ ] Filtrer par IDs de matchs
- [ ] ❌ Sélection d'événement (options avancées)

### `/stats` — Statistiques
- [ ] Sélectionner le type (Buteurs, Passes…)
- [ ] Filtrer par saison / compétition
- [ ] Paramétrer le nb de résultats
- [ ] Voir le tableau de stats
- [ ] Télécharger / exporter

### `/stats/[type]/[saison]/[competition]` — Détail stats
- [ ] Accès à la page

### `/rc` — Commission des arbitres
- [ ] Lister les arbitres
- [ ] Rechercher (nom / matricule)
- [ ] Filtrer (compétition, groupe, événement)
- [ ] ❌ Ajouter un arbitre
- [ ] ❌ Modifier un arbitre
- [ ] ❌ Supprimer un arbitre
- [ ] ❌ Copier RC depuis une autre saison

### `/journal` — Journal d'activité
- [ ] ❌ Accès à la page (redirection vers `/`)

### `/tv` — TV / Présentation
- [ ] ❌ Accès à la page (redirection vers `/`)

### `/presence/team/[teamId]` — Présence équipe
- [ ] Voir la composition
- [ ] Édition inline (numéro, capitaine) — selon permissions présence
- [ ] Ajouter un joueur existant — selon permissions présence
- [ ] Créer un nouveau joueur (avec détection doublon) — selon permissions présence
- [ ] Copier compo depuis une autre équipe — selon permissions présence
- [ ] Recherche joueur (autocomplete) — selon permissions présence
- [ ] Verrouiller / déverrouiller la compo — selon permissions présence
- [ ] Cases à cocher (sélection) — selon permissions présence

### `/presence/match/[matchId]/team/[teamCode]` — Présence match
- [ ] Accès à la page

### `/live/cache-manager` — Gestionnaire de cache live
- [ ] ❌ Accès à la page (redirection vers `/`)

### `/operations` — Opérations
- [ ] ❌ Accès à la page (redirection vers `/`)

---

## Profils 5 et 6 — Délégué fédéral / Organisateur journée

> Ces profils n'ont pas de guards explicites identifiés dans le code. Ils héritent des règles `<= 4` (accès refusé) ou `<= 9` (accès accordé). Comportement à confirmer en pratique.

### `/login` — Connexion
- [ ] Formulaire login (identifiant + mot de passe)
- [ ] Bascule langue FR/EN
- [ ] Redirection vers `/select-mandate` si mandats présents

### `/select-mandate` — Choix du mandat
- [ ] Voir profil de base et mandats disponibles
- [ ] Sélectionner "sans mandat"
- [ ] Sélectionner un mandat
- [ ] Bascule langue FR/EN

### `/` — Tableau de bord
- [ ] Carte Compétitions
- [ ] Carte Équipes
- [ ] Carte Journées / Phases
- [ ] Carte Classements
- [ ] Carte Documents
- [ ] Carte Matchs
- [ ] Carte Statistiques
- [ ] Sélecteur saison / compétition

### `/users` — Utilisateurs
- [ ] ❌ Accès à la page (redirection vers `/`)

### `/competitions` — Compétitions
- [ ] Lister les compétitions par section
- [ ] Rechercher une compétition
- [ ] Sélecteur multi-compétitions
- [ ] ❌ Créer / modifier / supprimer

### `/competitions/copy` — Copie de compétition
- [ ] ❌ Accès à la page

### `/teams` — Équipes
- [ ] Lister les équipes
- [ ] PDF global / présence individuelle
- [ ] Édition inline, ajout, duplication — `?` à vérifier

### `/gamedays` — Journées
- [ ] Lister les journées
- [ ] Lien vers schéma
- [ ] ❌ Créer / modifier / supprimer / actions en masse

### `/gamedays/schema` — Schéma de journée
- [ ] Accès à la page

### `/games` — Matchs
- [ ] Lister les matchs
- [ ] Filtre par statut verrouillage
- [ ] Pagination (50/page)
- [ ] Créer / modifier / supprimer / actions en masse — `?` à vérifier

### `/rankings` — Classements (P5 et P6)
- [ ] Voir classement calculé
- [ ] Voir classement publié
- [ ] Calculer le classement
- [ ] Sélection mode / phase
- [ ] Inclure/exclure équipes déverrouillées
- [ ] ❌ Édition inline, publication, transfert, etc.

### `/rankings/initial` — Classements initiaux
- [ ] ❌ Accès à la page

### `/clubs` — Clubs
- [ ] Vue carte interactive
- [ ] Recherche / voir équipes d'un club
- [ ] ❌ Géocoder / modifier / ajouter

### `/clubs/team/[numero]` — Équipe d'un club
- [ ] Accès à la page

### `/athletes` — Athlètes
- [ ] Rechercher, filtres, voir détails
- [ ] ❌ Modifier un athlète

### `/events` — Événements
- [ ] Lister, rechercher, trier
- [ ] Créer / modifier / supprimer — `?` à vérifier

### `/groups` — Groupes
- [ ] Lister, rechercher, déplier
- [ ] Créer / modifier / supprimer — `?` à vérifier

### `/documents` — Documents
- [ ] Générer des PDF de classement
- [ ] Filtrer par événement / IDs matchs
- [ ] ❌ Sélection d'événement (options avancées)

### `/stats` — Statistiques
- [ ] Accès complet (authentifié)

### `/rc` — Commission des arbitres
- [ ] Lister, rechercher, filtrer
- [ ] ❌ Ajouter / modifier / supprimer / copier

### `/journal` — Journal d'activité
- [ ] ❌ Accès à la page

### `/tv` — TV / Présentation
- [ ] ❌ Accès à la page

### `/presence/team/[teamId]` — Présence équipe
- [ ] Voir la composition
- [ ] Autres fonctions — selon permissions présence (`?`)

### `/presence/match/[matchId]/team/[teamCode]` — Présence match
- [ ] Accès à la page

### `/live/cache-manager` — Gestionnaire de cache live
- [ ] ❌ Accès à la page

### `/operations` — Opérations
- [ ] ❌ Accès à la page

---

## Profils 7 et 8 — Resp. club/équipe / Consultation simple

> Profil 7 : pas de guard explicite identifié — droits proches de P8 à confirmer.
> Profil 8 : lecture seule.

### `/login` et `/select-mandate`
- [ ] Connexion et sélection de mandat fonctionnelles

### `/` — Tableau de bord
- [ ] Carte Compétitions
- [ ] Carte Équipes
- [ ] Carte Journées / Phases
- [ ] Carte Classements
- [ ] Carte Documents
- [ ] Carte Matchs
- [ ] Carte Statistiques
- [ ] Sélecteur saison / compétition

### `/users` — Utilisateurs
- [ ] ❌ Accès à la page (redirection vers `/`)

### `/competitions` — Compétitions
- [ ] Lister les compétitions par section
- [ ] Rechercher une compétition
- [ ] Sélecteur multi-compétitions
- [ ] ❌ Toute action d'édition / suppression

### `/competitions/copy` — Copie de compétition
- [ ] ❌ Accès à la page

### `/teams` — Équipes
- [ ] Lister les équipes
- [ ] PDF global / présence individuelle
- [ ] Édition, ajout, duplication — `?` à vérifier

### `/gamedays` — Journées
- [ ] Lister les journées
- [ ] Lien vers schéma
- [ ] ❌ Créer / modifier / supprimer / actions en masse

### `/gamedays/schema` — Schéma de journée
- [ ] Accès à la page

### `/games` — Matchs
- [ ] Lister les matchs
- [ ] Filtre par statut verrouillage
- [ ] Pagination (50/page)
- [ ] Créer / modifier / supprimer — `?` à vérifier

### `/rankings` — Classements
- [ ] ❌ Voir classement calculé / publié (guard <= 6)
- [ ] ❌ Toutes les actions d'édition

### `/rankings/initial`
- [ ] ❌ Accès à la page

### `/clubs` — Clubs
- [ ] Vue carte interactive
- [ ] Recherche / voir équipes
- [ ] ❌ Géocoder / modifier / ajouter

### `/clubs/team/[numero]`
- [ ] Accès à la page

### `/athletes` — Athlètes
- [ ] Rechercher, filtres, voir détails
- [ ] ❌ Modifier un athlète

### `/events` — Événements
- [ ] Lister, rechercher, trier
- [ ] Créer / modifier / supprimer — `?` à vérifier

### `/groups` — Groupes
- [ ] Lister, rechercher, déplier
- [ ] Créer / modifier / supprimer — `?` à vérifier

### `/documents` — Documents
- [ ] Générer des PDF de classement
- [ ] Filtrer par événement / IDs matchs
- [ ] ❌ Options avancées

### `/stats` — Statistiques
- [ ] Accès complet (authentifié)

### `/rc` — Commission des arbitres
- [ ] Lister, rechercher, filtrer
- [ ] ❌ Ajouter / modifier / supprimer / copier

### `/journal`, `/tv`, `/live/cache-manager`, `/operations`
- [ ] ❌ Accès à toutes ces pages (redirection vers `/`)

### `/presence/team/[teamId]`
- [ ] Voir la composition
- [ ] Autres fonctions — selon permissions présence (`?`)

### `/presence/match/[matchId]/team/[teamCode]`
- [ ] Accès à la page

---

## Profil 9 — Table de marque

### `/login` et `/select-mandate`
- [ ] Connexion et sélection de mandat fonctionnelles

### `/` — Tableau de bord
- [ ] Carte Compétitions
- [ ] Sélecteur saison / compétition
- [ ] ❌ Carte Équipes
- [ ] ❌ Carte Journées / Phases
- [ ] ❌ Carte Classements
- [ ] ❌ Carte Documents
- [ ] ❌ Carte Matchs
- [ ] ❌ Carte Statistiques

### `/users` — Utilisateurs
- [ ] ❌ Accès à la page

### `/competitions` — Compétitions
- [ ] Lister les compétitions par section
- [ ] Rechercher une compétition
- [ ] Sélecteur multi-compétitions
- [ ] ❌ Toute action d'édition / suppression

### `/competitions/copy`
- [ ] ❌ Accès à la page

### `/teams` — Équipes
- [ ] Lister les équipes
- [ ] PDF global / présence individuelle
- [ ] Édition, ajout, duplication — `?` à vérifier

### `/gamedays` — Journées
- [ ] Lister les journées
- [ ] Lien vers schéma
- [ ] ❌ Créer / modifier / supprimer / actions en masse

### `/gamedays/schema` — Schéma de journée
- [ ] Accès à la page

### `/games` — Matchs
- [ ] Lister les matchs
- [ ] Filtre par statut verrouillage
- [ ] Pagination (50/page)
- [ ] Créer / modifier / supprimer — `?` à vérifier

### `/rankings` — Classements
- [ ] ❌ Accès (guard <= 6)

### `/clubs` — Clubs
- [ ] Vue carte interactive
- [ ] Recherche / voir équipes
- [ ] ❌ Géocoder / modifier / ajouter

### `/clubs/team/[numero]`
- [ ] Accès à la page

### `/athletes` — Athlètes
- [ ] Rechercher, filtres, voir détails
- [ ] ❌ Modifier un athlète

### `/events` — Événements
- [ ] Lister, rechercher, trier
- [ ] Créer / modifier / supprimer — `?` à vérifier

### `/groups` — Groupes
- [ ] Lister, rechercher, déplier
- [ ] Créer / modifier / supprimer — `?` à vérifier

### `/documents` — Documents
- [ ] Générer des PDF de classement
- [ ] Filtrer par événement / IDs matchs
- [ ] ❌ Options avancées

### `/stats` — Statistiques
- [ ] Accès complet (authentifié)

### `/rc` — Commission des arbitres
- [ ] Lister, rechercher, filtrer
- [ ] ❌ Ajouter / modifier / supprimer / copier

### `/journal`, `/tv`, `/live/cache-manager`, `/operations`
- [ ] ❌ Accès à toutes ces pages (redirection vers `/`)

### `/presence/team/[teamId]`
- [ ] Voir la composition
- [ ] Autres fonctions — selon permissions présence (`?`)

### `/presence/match/[matchId]/team/[teamCode]`
- [ ] Accès à la page

---

## Notes

- Les cellules `?` indiquent qu'aucun guard explicite n'a été trouvé dans le code — comportement à confirmer en pratique.
- Les profils **5, 6, 7** n'ont pas de guards spécifiques identifiés : ils héritent des règles `<= 4` (refusé) ou `<= 9` (accordé) selon la page.
- Les permissions de présence (`/presence/`) dépendent du contexte (verrou + profil effectif) via `usePresencePermissions` — tester avec des combinaisons verrouillé / déverrouillé.
- Le **profil effectif** peut différer du profil de base si un mandat est sélectionné.
- La **règle de hiérarchie** sur `/users` : un utilisateur ne peut gérer que des utilisateurs ayant un profil **strictement supérieur** au sien — sauf P1 (Super Admin).
- **P3 et P4 en édition** : mode "mandats seuls" — ils ne peuvent pas modifier les champs de base (profil, saisons, compétitions, clubs) d'un utilisateur, uniquement ajouter ou supprimer des mandats.
- **Création** : P3 peut créer des profils 4-10 ; P4 peut créer des profils 5-10.

---

*Généré le 2026-05-10 — à mettre à jour après chaque campagne de test.*
