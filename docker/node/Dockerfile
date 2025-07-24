FROM node:20-alpine

ARG GROUP_ID=33

RUN apk update && apk upgrade --no-cache

# Crée le groupe si besoin
RUN getent group $GROUP_ID || addgroup -g $GROUP_ID appgroup

# Ajoute l'utilisateur 'node' au groupe (Alpine syntaxe)
RUN addgroup node $(getent group $GROUP_ID | cut -d: -f1)

WORKDIR /app
COPY . .
EXPOSE 3000

RUN mkdir -p /app/node_modules && chown -R node:node /app /app/node_modules
USER node

CMD ["tail", "-f", "/dev/null"]
