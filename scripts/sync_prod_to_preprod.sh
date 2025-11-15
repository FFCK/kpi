#!/bin/bash
# sync_prod_to_preprod.sh
# Synchronisation WordPress prod → preprod
# Usage: ./scripts/sync_prod_to_preprod.sh

set -e  # Arrêt sur erreur

# Couleurs pour output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROD_PATH="$(dirname "$SCRIPT_DIR")"
PREPROD_PATH="${PREPROD_PATH:-/path/to/kpi_preprod}"  # À configurer
DATE=$(date +%Y%m%d_%H%M%S)

# Vérifier que PREPROD_PATH est configuré
if [ "$PREPROD_PATH" = "/path/to/kpi_preprod" ]; then
    echo -e "${RED}ERREUR: PREPROD_PATH n'est pas configuré${NC}"
    echo "Éditez ce script et définissez PREPROD_PATH"
    exit 1
fi

# Charger variables d'environnement
if [ -f "$PROD_PATH/docker/.env" ]; then
    source "$PROD_PATH/docker/.env"
else
    echo -e "${RED}ERREUR: Fichier docker/.env non trouvé${NC}"
    exit 1
fi

echo "==================================================================="
echo "   Synchronisation WordPress prod → preprod"
echo "==================================================================="
echo "Date: $(date)"
echo "Prod:    $PROD_PATH"
echo "Preprod: $PREPROD_PATH"
echo "==================================================================="
echo ""

# Fonction de confirmation
confirm() {
    read -p "$(echo -e ${YELLOW}$1${NC}) [y/N] " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${RED}Annulé${NC}"
        exit 1
    fi
}

# Demander confirmation
confirm "Cette opération va écraser toutes les données de preprod. Continuer ?"

# 1. Backup preprod avant écrasement
echo -e "${YELLOW}[1/7] Backup preprod actuel...${NC}"
cd "$PREPROD_PATH"

# Vérifier si preprod existe
if [ ! -d "docker/wordpress" ]; then
    echo -e "${RED}ERREUR: Répertoire preprod WordPress non trouvé${NC}"
    exit 1
fi

tar -czf "docker/wordpress_preprod_backup_${DATE}.tar.gz" docker/wordpress/ 2>/dev/null || true

# Backup BDD preprod si conteneur existe
if docker ps -a --format '{{.Names}}' | grep -q "${APPLICATION_NAME}_preprod_dbwp"; then
    docker exec ${APPLICATION_NAME}_preprod_dbwp mysqldump \
        -u root -p${DBWP_ROOT_PASSWORD} ${DBWP_NAME} \
        > "docker/wordpress_preprod_db_backup_${DATE}.sql" 2>/dev/null || true
    echo -e "${GREEN}✓ Backup preprod sauvegardé${NC}"
else
    echo -e "${YELLOW}⚠ Conteneur preprod non trouvé, skip backup BDD${NC}"
fi

# 2. Arrêter conteneurs preprod
echo -e "${YELLOW}[2/7] Arrêt conteneurs preprod...${NC}"
cd "$PREPROD_PATH"
docker-compose -f docker/compose.preprod.yaml down 2>/dev/null || true
echo -e "${GREEN}✓ Conteneurs preprod arrêtés${NC}"

# 3. Copier fichiers WordPress prod → preprod
echo -e "${YELLOW}[3/7] Copie fichiers WordPress...${NC}"
rsync -av --delete \
    --exclude 'wp-config.php' \
    --exclude '.htaccess' \
    --exclude 'wp-content/cache/' \
    --exclude 'wp-content/backup/' \
    "$PROD_PATH/docker/wordpress/" \
    "$PREPROD_PATH/docker/wordpress/"
echo -e "${GREEN}✓ Fichiers copiés ($(du -sh $PREPROD_PATH/docker/wordpress/ | cut -f1))${NC}"

# 4. Copier wp-config.php avec ajustements
echo -e "${YELLOW}[4/7] Ajustement wp-config.php...${NC}"
if [ -f "$PREPROD_PATH/docker/wordpress/wp-config.php.preprod" ]; then
    cp "$PREPROD_PATH/docker/wordpress/wp-config.php.preprod" "$PREPROD_PATH/docker/wordpress/wp-config.php"
    echo -e "${GREEN}✓ wp-config.php preprod restauré${NC}"
else
    echo -e "${YELLOW}⚠ Pas de wp-config.php.preprod, utilisation version prod${NC}"
    cp "$PROD_PATH/docker/wordpress/wp-config.php" "$PREPROD_PATH/docker/wordpress/wp-config.php"
fi

# 5. Exporter BDD prod
echo -e "${YELLOW}[5/7] Export base de données prod...${NC}"
cd "$PROD_PATH"
docker exec ${APPLICATION_NAME}_dbwp mysqldump \
    -u root -p${DBWP_ROOT_PASSWORD} ${DBWP_NAME} \
    > "/tmp/wordpress_prod_export_${DATE}.sql"
DUMP_SIZE=$(du -sh "/tmp/wordpress_prod_export_${DATE}.sql" | cut -f1)
echo -e "${GREEN}✓ BDD prod exportée ($DUMP_SIZE)${NC}"

# 6. Importer BDD dans preprod
echo -e "${YELLOW}[6/7] Import base de données preprod...${NC}"
cd "$PREPROD_PATH"

# Démarrer uniquement dbwp preprod
docker-compose -f docker/compose.preprod.yaml up -d dbwp_preprod
echo "Attente démarrage MySQL (15 secondes)..."
sleep 15

# Drop et recréer la base
docker exec ${APPLICATION_NAME}_preprod_dbwp mysql \
    -u root -p${DBWP_ROOT_PASSWORD} \
    -e "DROP DATABASE IF EXISTS ${DBWP_NAME}; CREATE DATABASE ${DBWP_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" \
    2>/dev/null

# Importer dump prod
docker exec -i ${APPLICATION_NAME}_preprod_dbwp mysql \
    -u root -p${DBWP_ROOT_PASSWORD} ${DBWP_NAME} \
    < "/tmp/wordpress_prod_export_${DATE}.sql"

echo -e "${GREEN}✓ BDD importée${NC}"

# 7. Ajuster URLs preprod
echo -e "${YELLOW}[7/7] Ajustement URLs preprod...${NC}"

# Lire les domaines depuis .env
PROD_DOMAIN="${KPI_DOMAIN_NAME:-kayak-polo.info}"
PREPROD_DOMAIN="${PREPROD_DOMAIN:-preprod.kayak-polo.info}"

echo "  Prod domain:    https://$PROD_DOMAIN"
echo "  Preprod domain: https://$PREPROD_DOMAIN"

docker exec ${APPLICATION_NAME}_preprod_dbwp mysql \
    -u root -p${DBWP_ROOT_PASSWORD} ${DBWP_NAME} \
    -e "
UPDATE wp_options SET option_value = 'https://${PREPROD_DOMAIN}' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'https://${PREPROD_DOMAIN}/wordpress' WHERE option_name = 'siteurl';
UPDATE wp_posts SET post_content = REPLACE(post_content, 'https://${PROD_DOMAIN}', 'https://${PREPROD_DOMAIN}');
UPDATE wp_posts SET guid = REPLACE(guid, 'https://${PROD_DOMAIN}', 'https://${PREPROD_DOMAIN}');
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'https://${PROD_DOMAIN}', 'https://${PREPROD_DOMAIN}');
" 2>/dev/null

echo -e "${GREEN}✓ URLs ajustées${NC}"

# Nettoyer dump temporaire
rm -f "/tmp/wordpress_prod_export_${DATE}.sql"

# Redémarrer preprod complet
echo ""
echo -e "${YELLOW}Redémarrage preprod...${NC}"
docker-compose -f docker/compose.preprod.yaml up -d

echo ""
echo "==================================================================="
echo -e "${GREEN}✓ Synchronisation terminée avec succès !${NC}"
echo "==================================================================="
echo "Backup preprod: docker/wordpress_preprod_backup_${DATE}.tar.gz"
echo "Test preprod:   https://${PREPROD_DOMAIN}/wordpress"
echo ""
echo "Commandes utiles:"
echo "  - Logs:   docker logs -f ${APPLICATION_NAME}_preprod_wordpress"
echo "  - Status: docker ps | grep preprod"
echo "  - Shell:  docker exec -it ${APPLICATION_NAME}_preprod_wordpress bash"
echo "==================================================================="
