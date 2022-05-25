__KPI LIVE APP (NODE)__

UID=${UID} GID=${GID} docker-compose up

docker exec --user $UID -it docker_node_live_1 sh

cd app

npm install

npx workbox copyLibraries third_party/

### DEV
npm run serve
http://localhost:9001/#/

### UI
vue ui --headless --port 8001 --host 0.0.0.0
http://0.0.0.0:8001

### PROD
npm run build
http://localhost:8087/app_live/dist/#/

