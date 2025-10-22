# Contexte projet

## 📚 Documentation étendue

Voir le dossier **[WORKFLOW_AI/](WORKFLOW_AI/)** pour la documentation technique détaillée :
- Guides de migration (PHP 8, FPDF → mPDF)
- Corrections et optimisations
- Audits de code
- Configuration Docker et infrastructure

Index complet : [WORKFLOW_AI/README.md](WORKFLOW_AI/README.md)

## Architecture

- Backend en **PHP 7.4/8.x**, architecture MVC maison.
- Templates avec **Smarty** (`.tpl`) dans `sources/smarty/templates`
- Base de données MySQL, requêtes préparées uniquement.
- Suivre la convention **PSR-12** pour le code PHP.
- Ne jamais exposer de credentials dans les fichiers versionnés.
- Api maison dans **sources/api**
- Pour le frontend : 3 applications **Vue 3** dans `sources/app_dev` (compilé dans `sources/app`), `sources/app_live_dev` (compilé dans `sources/app_live`) et `sources/app_wsm_dev` (compilé dans `sources/app_wsm`).
- un wordpress sert pour la page d'accueil du site, dossier `wordpress/`
- Les fichiers générés (`vendor/`, `node_modules/`, `dist/`) ne doivent pas être modifiés.
- Les fichiers `sources/commun/MyParams.php` ne doivent jamais être modifiés ni commités.
- Toujours utiliser des requêtes préparées et pdo
- Un dossier `sources/app2` dans lequel je tente de migrer le fontend app_dev vers du nuxt