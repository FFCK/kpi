__KPI APP (NODE)__

UID=${UID} GID=${GID} docker-compose up

docker exec --user $UID -it docker_node_1 sh

cd app

npm install

npx workbox copyLibraries third_party/

### DEV
npm run serve
http://localhost:9000/#/

### UI
vue ui --headless --port 8000 --host 0.0.0.0
http://0.0.0.0:8000

### PROD
npm run build
http://localhost:8087/app/dist/#/
