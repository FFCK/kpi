# Contexte projet

- Backend en **PHP 7.4**, architecture MVC maison.
- Templates avec **Smarty** (`.tpl`) dans `sources/smarty/templates`
- Base de données MySQL, requêtes préparées uniquement.
- Suivre la convention **PSR-12** pour le code PHP.
- Ne jamais exposer de credentials dans les fichiers versionnés.
- Api maison dans **sources/api**
- Pour le frontend : 3 applications **Vue 3** dans `sources/app_dev` (compilé dans `sources/app`), `sources/app_live_dev` (compilé dans `sources/app_live`) et `sources/app_wsm_dev` (compilé dans `sources/app_wsm`).
- un wordpress sert pour la page d'accueil du site, dossier `wordpress/`
- Les fichiers générés (`vendor/`, `node_modules/`, `dist/`) ne doivent pas être modifiés.
- Les fichiers `sources/commun/MyParams.php` ne doivent jamais être modifiés ni commités.
