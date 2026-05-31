Exemples

utilise @DOC/developer/reference/APP4_STRUCTURE.md, @DOC/developer/reference/API2_ENDPOINTS.md, @SQL/kpi_structure.sql et les specs existantes dans DOC/specs pour créer dans DOC/specs/PAGE_CLUBS.md les spécifications pour la page Gestion des Clubs, à partir des fonctionnalités de @sources/admin/GestionStructure.php  @sources/smarty/templates/GestionStructure.tpl  @sources/js/GestionStructure.js et de la capture d'écran de la page legacy.
Les select pour les clubs, les comités régionaux et les comités départementaux peuvent être remplacés par des listes d'autocomplétion pour faciliter la sélection. D'autres optimisations sont probablement possibles.
Précise les fonctionnalités avec des questions si nécessaire.

Implémente les fonctionnalités de la page Clubs en utilisant les spécifications définies dans DOC/specs/PAGE_CLUBS.md. Assure-toi de respecter les fonctionnalités et les messages d'erreur spécifiés, ainsi que les spécifications globales de DOC/specs/COMMON_ADMIN_SPECS.md. Tu peux utiliser comme modèle les autres pages que tu as implémenté précédemment, utiliser les composants et composables existants et en créer de nouveaux si nécessaire pour des éléments réutilisables.
Tu as également à ta disposition @DOC/developer/reference/APP4_STRUCTURE.md, @DOC/developer/reference/API2_ENDPOINTS.md, @SQL/kpi_structure.sql pour t'aider dans cette tâche.
---

OK :
Ajoute une fonctionnalité pour afficher les équipes associées au club sélectionné dans la page de gestion des clubs. Cette fonctionnalité doit permettre à l'utilisateur de voir rapidement les équipes liées à un club spécifique, avec la possibilité de cliquer sur une équipe pour accéder à sa page de détails. Assure-toi que cette fonctionnalité est bien intégrée dans l'interface utilisateur et complète les spécifications définies dans DOC/specs/PAGE_CLUBS.md.
Les équipes doivent être triées dans l'ordre décroissant de leur dernière saison de participation à une compétition, et afficher le nom de l'équipe ainsi que la saison de sa dernière participation et le nombre de compétitions auxquelles elle a participé. Si un club n'a pas d'équipes associées, un message indiquant "Aucune équipe associée à ce club" doit être affiché.
Pour chaque équipe, un lien doit être disponible pour accéder à sa page de détails, qui doit afficher des informations supplémentaires sur l'équipe, telles que les membres de l'équipe, les compétitions auxquelles elle a participé, et les résultats obtenus. Assure-toi que cette fonctionnalité est bien testée et fonctionne correctement dans tous les cas d'utilisation.
---

A VERIFIER :
utilise @DOC/developer/reference/APP4_STRUCTURE.md, @DOC/developer/reference/API2_ENDPOINTS.md, @SQL/kpi_structure.sql et les specs existantes dans DOC/specs pour créer dans DOC/specs/PAGE_UTILISATEURS.md les spécifications pour la page Gestion des Utilisateurs, à partir des fonctionnalités de @sources/admin/GestionUtilisateur.php  @sources/smarty/templates/GestionUtilisateur.tpl  @sources/js/GestionUtilisateur.js et de la capture d'écran de la page legacy.
Pour la créatiion et la modification d'un utilisateur, le filtre classique (sélection de saisons et de compétitions) est suffisant.
Le filtre club devrait être remplacé par une autocomplétion pour faciliter la sélection d'un ou plusieurs clubs.
Précise les fonctionnalités avec des questions si nécessaire.
Par défaut, un nouvel utilisateur doit être un profil 7, limité à la saison en cours, à son propre club.
La gestion des utilisateurs est limitée aux profils 1 à 4, les profils 3 et 4 ne peuvent pas créer ou modifier des utilisateurs de profil < 5. Les profils 5 à 7 n'ont pas accès à la page de gestion des utilisateurs.
Je cherche par ailleurs à attribuer plusieurs profils différents à un même utilisateur, par exemple un profil 7 pour gérer les équipes de son club sur les compétitions nationales (championnat de France, coupe de France), et un profil 3 pour gérer des compétitions régionales ou un tournoi. Quelle serait la meilleure approche pour implémenter cette fonctionnalité dans la gestion des utilisateurs ? Faut-il permettre l'attribution de plusieurs profils à un même utilisateur, ou plutôt créer des utilisateurs distincts pour chaque profil nécessaire ? Comment gérer les permissions et les accès dans ce cas, avec le moins de complexité possible et d'impact sur l'authentification et la sécurité dans l'application legacy ainsi que dans app2 et app4 ? Quels seraient les avantages et les inconvénients de chaque approche, et quelle serait la solution la plus adaptée pour répondre à ce besoin tout en assurant une gestion efficace des utilisateurs et de leurs permissions ? Documente ta recommandation et les raisons qui la motivent dans les spécifications de la page de gestion des utilisateurs.
---

A VERIFIER :
utilise @DOC/developer/reference/APP4_STRUCTURE.md, @DOC/developer/reference/API2_ENDPOINTS.md, @SQL/kpi_structure.sql et les specs existantes dans DOC/specs pour créer dans DOC/specs/PAGE_GESTION_JOURNAL.md les spécifications pour la page de gestion du journal d'activité, à partir des fonctionnalités de @sources/admin/GestionJournal.php  @sources/smarty/templates/GestionJournal.tpl  @sources/js/GestionJournal.js et de la capture d'écran de la page legacy.
La page de gestion du journal d'activité doit permettre aux administrateurs de visualiser et de filtrer les événements enregistrés dans le journal d'activité de l'application. Les fonctionnalités clés de cette page incluent :
- Affichage d'une liste paginée des événements du journal d'activité, avec des informations telles que la date et l'heure de l'événement, l'identité de l'acteur, l'action effectuée, la compétition et la saison concernée, la journée concernée, le match concerné, le détail de l'action.
- Filtre par utilisateur (autocomplétion), par type d'action (select), par compétition (select), par saison (select), par journée (select), par match (select), et par période (date range).


OK :
Implémente les fonctionnalités de la page de gestion du journal d'activité en utilisant les spécifications définies dans DOC/specs/PAGE_GESTION_JOURNAL.md. Assure-toi de respecter les fonctionnalités et les messages d'erreur spécifiés, ainsi que les spécifications globales de DOC/specs/COMMON_ADMIN_SPECS.md. Tu peux utiliser comme modèle les autres pages que tu as implémenté précédemment, utiliser les composants et composables existants et en créer de nouveaux si nécessaire pour des éléments réutilisables.

OK :
Uniformise le header des pages de gestion de compétition (compétitions, responsables de compétition, documents, équipes, journées/phases, schéma, matchs, classements) pour faciliter la navigation et l'accès aux différentes sections de la gestion, en optimisant l'utilisation de l'espace vertical et évitant à l'utilisateur de trop scroller :
- filtres Evenements/Groupes et Compétitions avec min-w-48 max-w-96,
- filtres avec les labels au dessus des champs (comme sur la page de gestion des gamedays),
- recherche et boutons d'action à droite, action sur sélection à gauche sur la même ligne, avec éventuellement les boutons Tout déplier/Tout replier (comme c'est déjà le cas sur la page Compétitions)
- titre de la page à gauche au dessus des filtres, contexte de travail aligné à droite sur la même ligne que le titre,
- Notices si possible sur la même ligne que les filtres, sinon juste en dessous avec possibilité de les masquer pour éviter de prendre trop de place à l'écran.
Complète DOC/specs/COMMON_ADMIN_SPECS.md avec les spécifications de ce header commun à toutes les pages de gestion de compétition, et implémente ce header dans les pages concernées en créant un composant réutilisable si nécessaire.

TODO :


Exploiter la charte graphique :

   - header : #20265b                                                                                                                                                     
   - couleur primaire : #1e4385                                                                                                                                           
   - couleur secondaire : #69b9e6                                                                                                                                         
   - couleur light : #c6c7c7                                                                                                                                              
   - couleur dark : #1e1e1c                                                                                                                                               
   - couleur warning : #e9b410                                                                                                                                            
   - couleur success : #209452                                                                                                                                            
   - couleur danger : #c94a4c                                                                                                                                             
   - Police de caractères Pour les titres, sous-titres et textes en exergues : Agency FB                                                                                  
   - Police de caractères Pour les textes courants : Raleway 


TODO :
* ✅ Compétitions : formulaire de création/modification : manque sélection des images (logo, bandeau, sponsor)
* ✅ Uniformisation header des pages de gestion de compétition
* ✅ Synthèse d'une compétition (mise en page des logos, décomptes...)
* Numéro de version dans app2 et app4
* Verrouillage des présences : vérifier les dates de verrouillage et déverrouillage
* ⚠️ Matchs : saisie arbitres ? (inline + formulaire)
* ✅ gamesday et games : manque toast de confirmation lors d'une modification en ligne.
* Journées/Phases : autocomplete sur les officiels, etc...
* ⚠️ Schéma de progression /gamedays/schema : imprimabe en pdf
* ✅ Page/Rubrique TV (Tv control panel)
* ✅ Recherche/Copie système de jeu : Spécifié dans DOC/specs/PAGE_COPIE_COMPETITION.md
* Journées/Phases : 
  * copier les officiels et les paramètres du calendrier public depuis une phase sur toutes les autres phases de la compétition (type CP) (depuis le formulaire ? depuis une action sur la liste ? )
  * Générer la feuille de jury d'appel à partir des officiels de la compétition, avec possibilité de modifier les données avant impression
* ✅ App2 : masquer la progression pout les compétitions en attente. 
* ✅ dans opérations : pouvoir déclencher les cron d'import PCE et de verrou présences manuellement, en plus de leur exécution automatique programmée.
- Reproduire les pdf en stateless ?
- Mode nuit
- ✅ Association événement : à tester
- Matchs : tester en profondeur,
- Classements : tester en profondeur
- ✅ Lien vers app2 depuis app4
- Droits par profil :
  - Les profils > 2 ne peuvent pas créer, modifier ou supprimer quoi que ce soit dans les saisons antérieures à la saison active.
  - Mandats : revoir l'organisation pour simplifier le renouvellement annuel des droits
- Empêcher la création de plusieurs mandats avec le même profil pour un même utilisateur ? (à étudier)
- ✅ Copie de système de jeu : type CP par défaut
- ✅ Gamedays : lien global vers schéma de compétition à partir du moment ou une compétition (CP ?) est sélectionnée.
- ✅ Gamedays + Games : nb éléments par page : par défaut Tous si au moins un événement, un groupe ou une compétition est sélectionné, sinon 50 par page.
- ✅ Teams : focus sur le champs de recherche lors de l'ouverture du formulaire de création/modification d'équipe
- ✅ Teams : recherche avec et sans tiret (Ex : "Team A" doit être trouvé avec "Team A" et "Team-A" et inversement)
- ✅ gamedays/schema : afficher les équipes associées à chaque phase/journée dans le schéma de compétition (actuellement seulement les matchs éliminatoires affichent les équipes associées, pas les phases de poules)
- ✅ presence/team/ focus sur le champs de recherche lors de l'ouverture du formulaire de saisie des présences d'équipe, focus sur le numéro lors de la sélection d'un joueur dans les résultats de recherche.
- ✅ Ajouter un bouton "Enregistrer et ajouter" pour faciliter la saisie de plusieurs joueurs d'une même équipe (ou trouver une formulation plus explicite pour les deux boutons "Enregistrer" et "Enregistrer et ajouter")
- ✅ presence/team/ formulaire ajout de joueur : Le champs de recherche ne trouve pas les noms composés Van De Kapelle Enzo (la recherche Nom Prénom ou Prénom Nom est perturbée par les noms ou prénoms composés, tirets, apostrophes, etc... Il faudrait trouver une solution pour que la recherche soit plus efficace et intuitive, par exemple en permettant de rechercher avec ou sans les caractères spéciaux, ou en utilisant une approche de recherche plus flexible qui prend en compte les différentes variations possibles des noms et prénoms.
- ✅ presence/team/ afficher les noms des joueurs en majuscule, les prénoms avec première lettre de chaque mot en majuscule (et partout où sont affichés des noms de joueurs dans l'application app4)
- ✅ presence/team/ : modification en ligne du numéro de joueur : sélectionner la valeur au clic pour faciliter la modification.
- ✅ suppression d'un joueur : ajouter une confirmation pour éviter les suppressions accidentelles.
- ✅ games : changement de focus avec la tabulation : date -> heure -> terrain -> code match
- ✅ bouton Recharger (à gauche de Documents) pour recharger les données du match dans l'odre choisi après des modifications en ligne (date, heure, terrain, code match)
- ✅ page clubs, j'ai un 404 sur certains clubs, peut-être lié au fait qu'ils ne sont pas localisés ?
- "Faire confiance à cet appareil" ? (trouver une solution pour conserver la session active sur app4 sans avoir à se reconnecter à chaque fois, tout en assurant la sécurité de l'application)
- ⚠️ Import/Export événements en json : vérifier la prise en compte des dernières migrations de la structure des données depuis la création de cette fonctionnalité.
- ✅ Contexte de travail : à la sélection d'un périmètre, ouvrir le select correspondant.
- Créer pages d'administration (profil 1) pour les comités départementaux / pays
- ✅ PdfListeMatchs4TerrainsEn2.php?S=2026&tz=Europe%2FParis&idEvenement=239 je voudrais une version avec 5 terrains (au lieu de 4)
- ✅ Import PCE : manquant dans Opérations, doit être exécutable par cron
- ✅ Verrou présences : manquant dans Opérations, doit être exécutable par cron
- ✅ Vérifier l'envoi de mail des cron et autres alertes (antispam de Hostinger ?)
- ⚠️ Manque actions Affect Auto et Annul Auto dans la gestion des matchs
- api legacy encore utilisée ?
- ✅ Matomo sur app2 et app4
- ✅ Teams : inverser les colonnes Games & Players
- ✅ App2 : refresh automatique des données toutes les 5 minutes lorsque la page est active (pages games, charts, team) en plus du refresh manuel, ou lors de la réactivation de la page lorsque ça fait plus de 5 minutes que la page est inactive.
- depuis la modification des couleurs dans draw progression en dev, je n'ai plus le bouton login sur app4, même en prod, je suppose que c'est lié, à vérifier et corriger si c'est le cas.
- ✅ Ajout de joueur, lors de la recherche d'un joueur, si aucune réponse ne convient, il faut pouvoir transférer la saisie du champ de recherche vers le champ de création du joueur pour éviter d'avoir à ressaisir les informations du joueur à créer.
- ✅ dans la recherche, afficher également le numéro icf dans les résultats
- ✅ dans la recherche du formulaire ajout de joueur, permettre optionnellement de filtrer par club.
- vérifier le contraste des couleurs dans l'application app4.
- ⚠️ Teams: Init titulaires action : HS ! + Ajouter le décompte des matchs concernés.
- Presence/team : bouton Init titulaires (profil <= 6)
- ✅ changer le contexte de travail depuis les stats
- FMV3 : départ du timeshoot manuel et pas en même temps que le chrono + Bouton Pause.
- ✅ App2 : pas d'équipe non affectée (1st Group A, etc.) dans le dropdown team de la page Team
- ✅ Activer Admin2 dans le menu legacy pour le profil 2
- outils de contrôle de planification : pouvoir vérifier dans app4 l'enchainement des matchs et arbitrages
- Scénarios TV : bouton Tester => Contrôle doit ouvrir le scénario dans une nouvelle fenêtre.
- TV : langue FR n'est pas prise en compte.
- Feat: Jury d'appel : 3 représentants des athlètes.
- Ouverture FMV2 et FMV3 depuis app4 : ouvrir dans une fenêtre identique si déjà ouverte.
- ✅ Games : le filtre date, une fois actif sur une date, ne propose plus les autres dates dans le dropdown.
- Classement calculé et publié, progression : dans l'ordre inverse.
- ✅ Games : filtre Matchs non verrouillés : le décompte des matchs n'est pas mis à jour en fonction de ce filtre, il affiche le nombre total de matchs, pas le nombre de matchs non verrouillés. + remplacer Total par Filtré : X matchs (ou indiquer les deux si l'information est disponible.)
- ⚠️