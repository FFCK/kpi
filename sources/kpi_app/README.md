__KPI APP (NODE)__

UID=${UID} GID=${GID} docker-compose up

docker exec --user $UID -it docker_node_1 sh

cd kpi_app

npm install

### DEV
npm run serve
http://localhost:9000/#/

### UI
vue ui --headless --port 8000 --host 0.0.0.0
http://0.0.0.0:8000

### PROD
npm run build
http://localhost:8087/kpi_app/dist/#/


PWA : après le build, dans service-worker.js, première ligne, supprimer le lien Workbox vers le CDN Google !