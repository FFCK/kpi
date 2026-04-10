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
* Uniformisation header des pages de gestion de compétition
* Synthèse d'une compétition (mise en page des logos, décomptes...)
* Journées/Phases : lien vers les matchs mieux mis en avant,
* Matchs : liens vers actions (feuille de marque V2, V3, pdf, etc...),
* Matchs : saisie arbitres ? (inline + formulaire)
* gamesday et games : manque toast de confirmation lors d'une modification en ligne.
* Journées/Phases : autocomplete sur les officiels, etc...
* Schéma de progression /gamedays/schema : imprimabe en pdf
* Page/Rubrique TV (Tv control panel)
* Recherche/Copie système de jeu : ✅ Spécifié dans DOC/specs/PAGE_COPIE_COMPETITION.md
* Journées/Phases : 
  * copier les officiels et les paramètres du calendrier public depuis une phase sur toutes les autres phases de la compétition (type CP) (depuis le formulaire ? depuis une action sur la liste ? )
  * Générer la feuille de jury d'appel à partir des officiels de la compétition, avec possibilité de modifier les données avant impression


- Reproduire les pdf en stateless ?

- Association événement : à tester
- Matchs : tester en profondeur,
- Classements : tester en profondeur

- Droits par profil :
  - Les profils > 2 ne peuvent pas créer, modifier ou supprimer quoi que ce soit dans les saisons antérieures à la saison active.
- Mandats : revoir l'organisation pour simplifier le renouvellement annuel des droits
- Import PCE (: manquant dans Opérations, doit être exécutable par cron
- Verrou présences : manquant dans Opérations, doit être exécutable par cron
- Vérifier l'envoi de mail des cron et autres alertes (antispam de Hostinger ?)
- 