FROM node:14-alpine

LABEL maintainer=STI

# Dossier de travail par défaut
WORKDIR /app

# installe un simple serveur http pour servir un contenu statique, ne va servir ici que pour assurer le lancement du container
#Installation de vue-vli
RUN npm install -g http-server 
RUN npm install -g @vue/cli
RUN npm install -g @vue/cli-service

# Ouverture du port
EXPOSE 8000
EXPOSE 8080

# copie 'package.json' et 'package-lock.json' (si disponible)
COPY sources/app_dev/package.json ./

# lance le serveur
CMD [ "npm", "run", "http-server"]
