#!/bin/bash
#
# Script d'aide pour l'extraction des structures fédérales
# Exemples d'utilisation des commandes d'extraction
#

set -e

# Couleurs pour la sortie
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Extraction des structures fédérales ===${NC}\n"

# Vérifier que le conteneur Docker est actif
if ! docker ps | grep -q kpi_php; then
    echo -e "${RED}❌ Erreur : Le conteneur kpi_php n'est pas démarré${NC}"
    echo -e "${YELLOW}💡 Démarrez-le avec : make dev_up${NC}"
    exit 1
fi

# Vérifier que le fichier pce1.pce existe
if ! docker exec kpi_php test -f /var/www/html/commun/pce1.pce; then
    echo -e "${RED}❌ Erreur : Le fichier pce1.pce n'existe pas dans sources/commun/${NC}"
    echo -e "${YELLOW}💡 Placez le fichier FFCK pce1.pce dans sources/commun/${NC}"
    exit 1
fi

# Afficher le menu
echo -e "${GREEN}Que souhaitez-vous générer ?${NC}"
echo ""
echo "  1) Fichier HTML interactif (structures_federales_*.html)"
echo "  2) Fichiers CSV (comites_regionaux.csv, comites_departementaux.csv, clubs.csv)"
echo "  3) Les deux (HTML + CSV)"
echo "  4) Afficher les statistiques sans générer de fichiers"
echo "  5) Nettoyer les fichiers générés"
echo ""
read -p "Votre choix (1-5) : " choice

case $choice in
    1)
        echo -e "\n${BLUE}📊 Génération du fichier HTML...${NC}"
        docker exec kpi_php php /var/www/html/commun/extract_structures.php
        echo -e "${GREEN}✅ Terminé !${NC}"
        echo -e "${YELLOW}💡 Ouvrez le fichier HTML généré dans sources/commun/${NC}"
        ;;
    2)
        echo -e "\n${BLUE}📊 Génération des fichiers CSV...${NC}"
        docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php 2>&1 | grep -v "Deprecated"
        echo -e "${GREEN}✅ Terminé !${NC}"
        echo -e "${YELLOW}💡 Fichiers CSV disponibles dans sources/commun/${NC}"
        ;;
    3)
        echo -e "\n${BLUE}📊 Génération HTML...${NC}"
        docker exec kpi_php php /var/www/html/commun/extract_structures.php
        echo -e "\n${BLUE}📊 Génération CSV...${NC}"
        docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php 2>&1 | grep -v "Deprecated"
        echo -e "${GREEN}✅ Tous les fichiers ont été générés !${NC}"
        ;;
    4)
        echo -e "\n${BLUE}📊 Statistiques du fichier pce1.pce${NC}"
        docker exec kpi_php php -r "
            \$fichier = '/var/www/html/commun/pce1.pce';
            \$lignes = file(\$fichier);
            \$in_licencies = false;
            \$count = 0;
            foreach (\$lignes as \$ligne) {
                if (trim(\$ligne) === '[licencies]') {
                    \$in_licencies = true;
                    continue;
                }
                if (\$in_licencies && !empty(trim(\$ligne))) {
                    \$count++;
                }
            }
            echo \"Nombre de licenciés : \$count\n\";
            echo \"Taille du fichier : \" . round(filesize(\$fichier) / 1024 / 1024, 2) . \" Mo\n\";
        "
        echo ""
        echo -e "${YELLOW}💡 Pour voir les statistiques complètes, générez les fichiers (option 1, 2 ou 3)${NC}"
        ;;
    5)
        echo -e "\n${BLUE}🧹 Nettoyage des fichiers générés...${NC}"
        rm -f sources/commun/structures_federales_*.html
        rm -f sources/commun/comites_regionaux.csv
        rm -f sources/commun/comites_departementaux.csv
        rm -f sources/commun/clubs.csv
        # Nettoyer aussi les anciens fichiers dans sources/
        rm -f sources/structures_federales_*.html
        rm -f sources/comites_regionaux.csv
        rm -f sources/comites_departementaux.csv
        rm -f sources/clubs.csv
        echo -e "${GREEN}✅ Fichiers nettoyés !${NC}"
        ;;
    *)
        echo -e "${RED}❌ Choix invalide${NC}"
        exit 1
        ;;
esac

echo ""
echo -e "${GREEN}📚 Documentation : sources/commun/README_EXTRACTION_STRUCTURES.md${NC}"
