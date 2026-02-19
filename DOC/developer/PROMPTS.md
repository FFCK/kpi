Implémente les fonctionnalités de la page Gestion des Matchs en utilisant les spécifications définies dans DOC/specs/PAGE_MATCHS.md. Assure-toi de respecter les fonctionnalités et les messages d'erreur spécifiés, ainsi que les spécifications globales de DOC/specs/COMMON_ADMIN_SPECS.md, et de tester soigneusement chaque fonctionnalité pour garantir une expérience utilisateur fluide et sans bugs. Tu peux utiliser comme modèle la page de gestion des Journées/Phases et les autres pages que tu as implémenté précédemment, utiliser les composants et composables existants et en créer de nouveaux si nécessaire pour des éléments réutilisables.
Tu as également à ta disposition @DOC/developer/reference/APP4_STRUCTURE.md, @DOC/developer/reference/API2_ENDPOINTS.md, @SQL/kpi_structure.sql pour t'aider dans cette tâche.


utilise @DOC/developer/reference/APP4_STRUCTURE.md, @DOC/developer/reference/API2_ENDPOINTS.md, @SQL/kpi_structure.sql et les specs existantes dans DOC/specs pour créer dans DOC/specs/PAGE_CLUBS.md les spécifications pour la page Gestion des Clubs, à partir des fonctionnalités de @sources/admin/GestionStructure.php  @sources/smarty/templates/GestionStructure.tpl  @sources/js/GestionStructure.js et de la capture d'écran de la page legacy.
Les select pour les clubs, les comités régionaux et les comités départementaux peuvent être remplacés par des listes d'autocomplétion pour faciliter la sélection. D'autres optimisations sont probablement possibles.
Précise les fonctionnalités avec des questions si nécessaire.


Implémente les fonctionnalités de la page Clubs en utilisant les spécifications définies dans DOC/specs/PAGE_CLUBS.md. Assure-toi de respecter les fonctionnalités et les messages d'erreur spécifiés, ainsi que les spécifications globales de DOC/specs/COMMON_ADMIN_SPECS.md. Tu peux utiliser comme modèle les autres pages que tu as implémenté précédemment, utiliser les composants et composables existants et en créer de nouveaux si nécessaire pour des éléments réutilisables.
Tu as également à ta disposition @DOC/developer/reference/APP4_STRUCTURE.md, @DOC/developer/reference/API2_ENDPOINTS.md, @SQL/kpi_structure.sql pour t'aider dans cette tâche.

---

Ajoute une fonctionnalité pour afficher les équipes associées au club sélectionné dans la page de gestion des clubs. Cette fonctionnalité doit permettre à l'utilisateur de voir rapidement les équipes liées à un club spécifique, avec la possibilité de cliquer sur une équipe pour accéder à sa page de détails. Assure-toi que cette fonctionnalité est bien intégrée dans l'interface utilisateur et complète les spécifications définies dans DOC/specs/PAGE_CLUBS.md.
Les équipes doivent être triées dans l'ordre décroissant de leur dernière saison de participation à une compétition, et afficher le nom de l'équipe ainsi que la saison de sa dernière participation et le nombre de compétitions auxquelles elle a participé. Si un club n'a pas d'équipes associées, un message indiquant "Aucune équipe associée à ce club" doit être affiché.
Pour chaque équipe, un lien doit être disponible pour accéder à sa page de détails, qui doit afficher des informations supplémentaires sur l'équipe, telles que les membres de l'équipe, les compétitions auxquelles elle a participé, et les résultats obtenus. Assure-toi que cette fonctionnalité est bien testée et fonctionne correctement dans tous les cas d'utilisation.

---

utilise @DOC/developer/reference/APP4_STRUCTURE.md, @DOC/developer/reference/API2_ENDPOINTS.md, @SQL/kpi_structure.sql et les specs existantes dans DOC/specs pour créer dans DOC/specs/PAGE_ATHLETES.md les spécifications pour la page Gestion des Athlètes, à partir des fonctionnalités de @sources/admin/GestionAthletes.php  @sources/smarty/templates/GestionAthletes.tpl  @sources/js/GestionAthletes.js et de la capture d'écran de la page legacy.
La page doit afficher toutes les informations courantes sur l'athlète (identité, licence et sa saison, certificats, pagaie couleur, qualification et niveau d'arbitrage), ainsi que ses participations pour une saison donnée, par défaut la saison du contexte de travail, avec la possibilité de changer de saison. 
Les participations doivent indiquer : 
- Feuilles de présence (compétition, Equipe, numéro de joueur, Catégorie)
- Arbitrage (Date, Heure, Compétition, Match, Arbitre principal, Arbitre Secondaire, Secrétaire, Chronométreur)
- Matchs (Date, Heure, Compétition, Match, Equipes, Score, numéro de joueur, Buts, cartons verts, jaunes, rouges, rouges définitifs)

Implémente les fonctionnalités de la page Athlètes en utilisant les spécifications définies dans DOC/specs/PAGE_ATHLETES.md. Assure-toi de respecter les fonctionnalités et les messages d'erreur spécifiés, ainsi que les spécifications globales de DOC/specs/COMMON_ADMIN_SPECS.md. Tu peux utiliser comme modèle les autres pages que tu as implémenté précédemment, utiliser les composants et composables existants et en créer de nouveaux si nécessaire pour des éléments réutilisables.
Tu as également à ta disposition @DOC/developer/reference/APP4_STRUCTURE.md, @DOC/developer/reference/API2_ENDPOINTS.md, @SQL/kpi_structure.sql pour t'aider dans cette tâche.