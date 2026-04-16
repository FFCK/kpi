## App mobile (app2) :

- ✅ Accès à la feuille de match : pas évident de trouver l'endroit où il faut cliquer pour accéder à la feuille (a priori n° du match, à mettre en valeur ou icone ?) — *résolu : le n° de match est maintenant cliquable (comme le score) ; guide illustré ajouté sur la page d'accueil*
- ✅ Équipe - Fiche Complète  : stats des joueurs invisibles si on filtre par compétition au départ ; ne semble fonctionner que pour l'événement sélectionné quand on filtre par ce mode (le reste OK : matchs joués,à venir, progression et classement) — *résolu : nouvel endpoint API2 `/group/{season}/{groupCode}/team/{teamId}/stats` qui identifie l'équipe physique par `Code_club + Numero` et retourne les stats par compétition ; en mode groupe, la fiche affiche un tableau de stats par compétition (une section par compétition où l'équipe participe)*


## Legacy interface administration :

### Event Cache Manager
- marche uniquement sur profil 1 ? pas réusssi à acceder mais probablement normal

### Gestion d'images / uploader
- marche uniquement sur profil 1 ? pas réusssi à acceder, ni pour logo compétition ni pour logo équipe
accès à élargir ? a priori prévu pour accès ±profil 4 d'après la doc, mais je n'ai pas trouvé comment faire avec un profil 2

### ✅ Stats contrôle de cohérence des matchs — résolu, à tester :
- Règlement RP KAP 26.4 maintenant pris en compte, avec différenciation par niveau/type de compétition :
	* ✅ Match < 1h après match précédent (National) — détecte le non-respect de la demi-heure entre fin et début de match
	* ✅ Plus de 2 matchs sur 3 heures (National) — fenêtre glissante 3h
	* ✅ Plus de 3 matchs sur 4 heures — toutes compétitions
	* ✅ Plus de 4 matchs/jour (Championnat) — limité aux compétitions Code_typeclt = CHPT
	* ✅ Plus de 6 matchs/jour (International) — limité aux compétitions Code_niveau = INT
- Multi-compétitions : les calculs s'appuient désormais sur le champ Numero de l'équipe (commun toutes compétitions/saisons) ; une équipe engagée sur 2 compétitions simultanées (ex. REG-18 + T-R18) voit bien tous ses matchs agrégés pour les contrôles de cohérence.

### Réflexions générales :
- renumérotation des clubs/CD/CR dans la base suivant exalto, avec 6 chiffres ?



## Nouvelle interface administration (app4) :

- Remarques / erreurs générales : 
	* ✅ l'actualisation des différentes pages dans le navigateur (F5) renvoie une erreur 404 à part la page de connexion https://preprod.kayak-polo.info/admin2/ → **RÉSOLU** (fix nginx-app4-prod.conf `try_files`)


- Page "Athlètes"
	* ✅ afficher la catégorie d'âge pour la saison en cours → **RÉSOLU**
	* ✅ afficher le surclassement → **RÉSOLU**
	* ✅ afficher le type de licence (même si normalement seules les licences 1 an compétition remontent j'ai déjà eu des surprises) → **RÉSOLU**
	* plus de "recherche avancée" permettant par exemple d'avoir la liste des adhérents d'un club?

- Page gestion des RC → **RÉSOLU**
	* ✅ impossible d'en ajouter, y compris pour une compétition régionale, message d'erreur "Accès refusé / vous n'avez pas les droits nécessaires" y compris au stade de la recherche d'un licencié avant même de valider l'ajout → **RÉSOLU** (canDelete et bulk-delete côté API étaient restreints au profil 1 au lieu de 2 ; select des compétitions groupait tout dans "Autres" à cause d'une erreur dans CompetitionGroupedSelect)
	
- Page gestion des utilisateurs → **EXPLICATION**
	* ✅ notion de "mandat" : si j'ai bien compris ce sont des autorisations délivrées temporairement sur 1 journée ou événement? / permet d'avoir une gestion différenciée des droits selon la compétition? → **EXPLICATION** : les mandats permettent d'attribuer des droits différents selon les compétitions ou ensemble de compétitions (ex. profil 7 responsable d'équipe pour une compétition nationale, limité à un club, profil 6 délégué CNA pour une journée spécifique, profil 3 responsable de compétition pour un tournoi international ou un championnat régional). L'utilisateur, après son authentification, choisit le mandat actif pour la session en cours (s'il en a plusieurs) et n'a accès qu'aux compétitions associées à ce mandat. Il peut changer de mandat à tout moment (c'est un système similaire à celui d'Exalto lorsqu'un utilisateur a plusieurs rôles au sein de son club ou comité).
	Reste à imaginer l'organisation pour le renouvellement annuel des droits des utilisateurs...

- Page contrôle TV : 
	* à quoi correspond la fonction "split URL" ?
	
- ✅ Schémas de compétitions : erreur 404 sur les liens vers les schémas — **RÉSOLU, à tester** (les liens `window.open()` et `NuxtLink target="_blank"` ne préfixaient pas `/admin2` ; corrigé via `router.resolve()` dans `gamedays/index.vue` et `competitions/copy.vue`)

- Feuilles de match PDF : qq détails de mise en page à régler : 
	* la typo a changé, du coup certains champs dépassent des cadres. => réduire globalement la taille de police d'1 ou 2 pts ?
	* le QR code dépasse sur les bordures adjacentes, qui disparaissent à l'édition

- Page Documents :
	* ✅ export des listes de match en format tableur ODS : le numéro du match n'est pas exporté dans le fichier (idem depuis la liste des matchs), c'est pourtant utile → **RÉSOLU**
	* horodatage "variable" sur les fichiers PDF
		- Equipes / feuilles de présence FR : uniquement sur la dernière page, date du 01/01/1970 à 00:00 ? / horodater chacune des pages en pied de page ?
		- Equipes / feuilles de présence EN : aucun horodatage / horodater chacune des pages en pied de page ?
		- Equipes / Présence avec VISA et Présence avec photo : aucun horodatage / horodater chacune des pages en pied de page ?
		- Matchs / Liste des matchs FR : date du jour OK mais heure fixe à 00h00
		- Matchs / Liste des matchs EN : date du 01/01/1970 à 00:00
		- Matchs / feuilles de marque : date du jour OK mais heure en UTC+00
		- Classements / classement général : date du 01/01/1970 à 00:00
		- Classements / détail par phases : date du 01/01/1970 à 00:00
		- Classements / détail par équipes : date du 01/01/1970 à 00:00 pour les 2 cas compétition type championnat ou coupe
		- Evenements / match événement FR : date du jour OK mais heure fixe à 00h00
		- Evenements / match événement EN : date du 01/01/1970 à 00:00
		- Contrôle / Carton cumulés : à horodater en pied de page?
		
	* Classements / détail par équipes  : ordre des équipes OK si compétition type championnat
	* ✅ Classements / détail par équipes  : ordre des équipes erroné si compétition type coupe, a priori classées par ordre alphabétique et non de résultats → **RÉSOLU**
	* pour l'ensemble des documents de "classement" : classement indiqué "provisoire" alors que la compétition est au statut terminée
			
	* ✅ Si on navigue du menu documents à matchs puis retour à documents (sans autre action), le lien documents / matchs / Feuilles de marque indique "aucun match" et ne permet pas de les charger → **RÉSOLU** (`onMounted` appelle désormais `loadMatchIds()` directement si une compétition est déjà sélectionnée, car `initContext()` est sans effet au retour de navigation puisque le store est déjà initialisé)
	
	* ✅ Contrôle / feuille "cartons cumulés", à renommer Fiche de suivi des cartons → **RÉSOLU** (libellé mis à jour en FR et EN dans les fichiers i18n)
	
- Page Gestion des Equipes (liste générale)
	* ✅ ajout d'équipe : en mode "depuis l'historique" : la recherche génère une erreur "Erreur serveur : le serveur a rencontré une erreur" → **RÉSOLU** (fix collision de route `/admin/teams/{numero}` avec `/admin/teams/search` — ajout `requirements: ['numero' => '\d+']`)
	* ✅ ajout d'équipe : en mode "crétion manuelle" : la recherche d'un club génère une erreur de requête : erreur 404 → **RÉSOLU** (fix collision de route `/admin/clubs/{code}` avec `/admin/clubs/search` — ajout `requirements: ['code' => '\d+']`)
	* ajout d'équipe : en mode "crétion manuelle"  : option comité régional / comité départemental : la liste des CDCK est en doublon et 1/2 ne donne pas accès à la liste des clubs du département → **À REPORTER** (lié à la réflexion sur la renumérotation des clubs/CD/CR, cf. ligne 26)
	* ajout d'équipe : en mode "crétion manuelle"  : option comité régional / comité départemental : comment rattacher une équipe à un CD ou un CR ? (option pas trouvée, dans la version actuelle de KPi on retrouve la structure "parente" dans la liste du sous niveau pour faire celà.

	* Editions PDF : 
		- Poules : PDF vide
		- Les 4 autres editions PDF proposées dans le bandeau : erreur "aucune compétition sélectionnée"
		- editions PDF proposées sur chacune des lignes d'équipes : idem  erreur "aucune compétition sélectionnée"

- Page Equipes ( Feuilles de présence)
	* intitulé du bandeau des éditions PDF à revoir ? "Copier depuis" à modifier => "Editer la feuille de présence" ?
	* sur le bandeau des éditions PDF : mêmes remarques qu'à la rubrique "page documents" concernant l'horodatage des feuilles de présence pour les 4 versions proposées
	* Statut des inscrits : changer l'intitulé de colonne "CAP" par "Statut" ? et avoir un libellé plus explicite dans le menu déroulant sur chaque ligne (Joueur / Capitaine / Arbitre non joueur / Entraîneur / Inactif) (1 seule lettre n'est pas le plus clair, surtout confusion "Coach" et "Capitaine".
	* Ergonomie/visuel : pour les joueurs inactifs, griser légèrement la ligne en plus du texte?
	* Pour les novices : garder une "notice" en bas de page précisant que seuls les joueurs, capitaine et entraîneurs sont inscrits sur les feuilles de matchs de la prochaine journée.
	* Garder un extrait du log en bas de page avec les dernières modifications effectuées (a minima qui et quand) ? c'est une info utile pour les RC, et si possible tracer également les simples changements de statut (inactif/actif en particulier, le principal étant de savoir quand et par qui ont eu lieu les dernières modifs sur la feuille de présence)
	* Ajout de joueur : en mode ajout de joueur existant, recherche impossible, génère une erreur : "Accès refusé : vous n'avez pas les droits nécessaires" (idem autres recherches)

- Page Journées/Phases
	* ✅ Les liens vers les schémas de compétitions ne fonctionnent pas (erreur 404) — **RÉSOLU, à tester**
	* Comment peut-on associer des journées à un événement ? je n'ai pas retrouvé l'option dans cette nouvelle interface 

- Page Matchs :
	* Etat des matchs (partie centrale sous le cadenas, au niveau du score) : pour les matchs finalisés (statut "end" et verrouillés), il est affiché comme si ils étaient en cours (M1 ou M2) selon les dernières modifications faites dans la feuille de match en ligne et la mi-temps laissée "active" avant passage au statut "terminé" et verrouillage
	* Création de matchs : si on créée les matchs en mode manuel, malgré le fait de renseigner le champ "intervalle", par défaut pour le match suivant il faut resaisir la date ainsi que l'heure non calculée automatiquement (testé en ajoutant des matchs à une journée déjà existante)
	
- Page classements : 
	* Menu Extraction PDF : les liens classement général / détail par équipe / déroulement renvoient un classement vide, sur une compétition à Belfast ;) (en mode championnat ou coupe)

- Page statistiques : 
	* Les liens export Excel et PDF génèrent une "erreur serveur" 
	* a priori les statistiques sont cohérentes avec la version actuelle de KPI, testé sur plusieurs calculs (buteurs, cartons, irrégularités, contrôle cohérence) sur 1-2 compétitions