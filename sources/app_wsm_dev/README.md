__KPI WSM APP (NODE)__

UID=${UID} GID=${GID} docker-compose up

docker exec --user $UID -it docker_node_wsm_1 sh

cd app

npm install

// npx workbox copyLibraries third_party/

### DEV
npm run serve
http://localhost:9002/#/

### PROD
npm run build
http://localhost:8087/app_wsm/dist/#/

### Apache ActiveMQ
cd ~/Documents/dev/activemq/apache-activemq-5.17.1/bin
./activemq console

http://localhost:8161/admin/
admin admin

ws://localhost:61614
admin password
