__KPI__

___Prérequis___

- Serveur LAMP ou WAMP
- fonctionnel avec PHP 5.5
- fonctionnel avec MySQL 

___Installation___

- installer la base de donnée

- sur Wamp : placer les sources dans le dossier */wamp/www/kpi/*

- sur Debian : placer les sources dans le dossier */var/www/html/kpi/*

- Le fichier *commun/MyConfig.php* détecte un serveur Wamp ou l'IP 192.168.* d'une machine virtuelle pour passer la constante PRODUCTION à False.
Si ce n'est pas le cas, forcer cette constante dans *commun/MyConfig.php*.

- Si l'installation est différentes, ajuster les chemins absolus et relatifs dans *commun/MyConfig.php*.

- Créer et compléter le fichier *commun/MyParams.php* (sur le modèle de *commun/MyParams.php.modele*)

- Le fichier *index.php* n'inclue pas la page Wordpress d'accueil de KPI (utilisable via *index_2.php* si nécessaire)


Si aucun serveur de mail n'est fonctionnel, la réinitialisation du mot de passe ne fonctionnera pas.

___CRON___

- Mise à jour base des licenciés : commun/cron_maj_licencies.php  (quotidien)

- Verrouillage des feuilles de présence (à J-6 de chaque journées de Championnat de France) commun/cron_verrou_presences.php 
