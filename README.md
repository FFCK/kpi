__KPI__

___TODO___

- Structure BDD
- Fixtures

___Prérequis___

- Serveur LAMP ou WAMP
- fonctionnel avec PHP 5.5
- MySQL 5.5

___DOCKER___

`cd docker`

Créer et compléter le fichier .env

`docker network create network_kpi`

`docker compose -f compose.prod.yaml up -d`

alimenter la base de donnée my_database

___Installation (Legacy)___

- installer la base de donnée

- sur Wamp : placer les sources dans le dossier */wamp/www/kpi/*

- sur Debian : placer les sources dans le dossier */var/www/html/kpi/*

- Le fichier *commun/MyConfig.php* détecte un serveur Wamp ou l'IP 192.168.* d'une machine virtuelle pour passer la constante PRODUCTION à False.
Si ce n'est pas le cas, forcer cette constante dans *commun/MyConfig.php*.

- Si l'installation est différentes, ajuster les chemins absolus et relatifs dans *commun/MyConfig.php*.

- Créer et compléter le fichier *commun/MyParams.php* (sur le modèle de *commun/MyParams.php.modele*)

- Pour Wordpress, créer et compléter le fichier *wordpress/wp-config.php* (sur le modèle de *wordpress/wp-config-sample.php*)

- Le fichier *index.php* n'inclue pas la page Wordpress d'accueil de KPI (utilisable via *index_2.php* si nécessaire)


Si aucun serveur de mail n'est fonctionnel, la réinitialisation du mot de passe ne fonctionnera pas.

___CRON___

- Mise à jour base des licenciés : commun/cron_maj_licencies.php  (quotidien)

- Verrouillage des feuilles de présence (à J-6 de chaque journées de Championnat de France et Coupe de France) : commun/cron_verrou_presences.php  (quotidien)


___IFRAMES___

- https://github.com/FFCK/kpi/wiki/Iframes


__KPI APP (NODE)__

UID=${UID} GID=${GID} docker compose up

docker exec --user $UID -it docker_node_1 sh

npm install

### DEV
npm run serve
http://localhost:9000/#/

### UI
vue ui --headless --port 8000 --host 0.0.0.0
http://0.0.0.0:8000

### PROD
npm run build

vider le dossier /app puis déplacer le contenu de /app_dev/dist dans /app et uploader

http://localhost:8087/app/
