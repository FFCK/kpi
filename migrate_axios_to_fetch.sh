#!/bin/bash
###############################################################################
# migrate_axios_to_fetch.sh
#
# Script de migration automatique : Axios → fetch() natif
#
# Ce script remplace les appels axios() par axiosLikeFetch() dans tous les
# fichiers JavaScript utilisant Axios, permettant une migration progressive
# et sûre vers fetch() natif.
#
# Auteur: Laurent Garrigue / Claude Code
# Date: 2025-11-01
###############################################################################

set -e  # Arrêter en cas d'erreur

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     Migration Axios → fetch() natif                          ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Vérifier que nous sommes dans le bon répertoire
if [ ! -d "sources/js" ]; then
    echo -e "${RED}❌ Erreur: Répertoire sources/js non trouvé${NC}"
    echo "   Veuillez exécuter ce script depuis la racine du projet"
    exit 1
fi

# Liste des fichiers à migrer
FILES=(
    "sources/js/voie.js"
    "sources/live/js/score.js"
    "sources/live/js/score_o.js"
    "sources/live/js/score_club.js"
    "sources/live/js/score_club_o.js"
    "sources/live/js/multi_score.js"
    "sources/live/js/match.js"
    "sources/live/js/tv.js"
    "sources/live/js/voie_ax.js"
)

# Compter les fichiers existants
TOTAL_FILES=0
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        ((TOTAL_FILES++))
    fi
done

echo -e "${YELLOW}📋 Fichiers à migrer: ${TOTAL_FILES}${NC}"
echo ""

# Demander confirmation
read -p "Voulez-vous continuer avec la migration ? (o/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[OoYy]$ ]]; then
    echo -e "${YELLOW}⚠️  Migration annulée${NC}"
    exit 0
fi

echo ""
echo -e "${BLUE}🔄 Début de la migration...${NC}"
echo ""

# Étape 1: Vérifier que fetch-utils.js existe
if [ ! -f "sources/js/fetch-utils.js" ]; then
    echo -e "${RED}❌ Erreur: sources/js/fetch-utils.js non trouvé${NC}"
    echo "   Le fichier fetch-utils.js doit être créé avant la migration"
    exit 1
fi

echo -e "${GREEN}✅ fetch-utils.js trouvé${NC}"
echo ""

# Étape 2: Créer un backup
BACKUP_DIR="backups/axios_migration_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo -e "${BLUE}📦 Création des backups dans ${BACKUP_DIR}/${NC}"

BACKED_UP=0
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        # Créer la structure de répertoires dans le backup
        file_dir=$(dirname "$file")
        mkdir -p "$BACKUP_DIR/$file_dir"

        # Copier le fichier
        cp "$file" "$BACKUP_DIR/$file"
        ((BACKED_UP++))
        echo -e "  ${GREEN}✅${NC} Backup: $file"
    fi
done

echo -e "${GREEN}✅ ${BACKED_UP} fichiers sauvegardés${NC}"
echo ""

# Étape 3: Migration des fichiers
echo -e "${BLUE}🔄 Remplacement axios() → axiosLikeFetch()${NC}"
echo ""

MIGRATED=0
ERRORS=0

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        # Compter les occurrences avant migration
        BEFORE=$(grep -c "axios(" "$file" 2>/dev/null || echo "0")

        # Remplacer axios( par axiosLikeFetch(
        if sed -i 's/axios(/axiosLikeFetch(/g' "$file" 2>/dev/null; then
            # Compter les occurrences après migration
            AFTER=$(grep -c "axiosLikeFetch(" "$file" 2>/dev/null || echo "0")

            if [ "$AFTER" -gt 0 ]; then
                echo -e "  ${GREEN}✅${NC} $file (${AFTER} appels migrés)"
                ((MIGRATED++))
            else
                echo -e "  ${YELLOW}⚠️${NC}  $file (aucun appel axios trouvé)"
            fi
        else
            echo -e "  ${RED}❌${NC} Erreur lors de la migration de $file"
            ((ERRORS++))
        fi
    else
        echo -e "  ${YELLOW}⚠️${NC}  $file (fichier non trouvé)"
    fi
done

echo ""
echo -e "${BLUE}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     Résumé de la Migration                                   ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}✅ Fichiers migrés avec succès: ${MIGRATED}${NC}"
echo -e "${YELLOW}⚠️  Fichiers avec avertissements: $((TOTAL_FILES - MIGRATED - ERRORS))${NC}"
echo -e "${RED}❌ Erreurs: ${ERRORS}${NC}"
echo ""
echo -e "${BLUE}📦 Backups sauvegardés dans: ${BACKUP_DIR}/${NC}"
echo ""

# Étape 4: Instructions suivantes
echo -e "${BLUE}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     Prochaines Étapes                                        ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANT: Actions requises manuellement${NC}"
echo ""
echo "1. Charger fetch-utils.js dans les templates:"
echo "   • Ajouter dans page.tpl (avant les scripts utilisant axios):"
echo "     <script src=\"js/fetch-utils.js\"></script>"
echo ""
echo "   • Ajouter dans tv.php:"
echo "     <script src=\"js/fetch-utils.js\"></script>"
echo ""
echo "2. Tester toutes les pages Live Scores:"
echo "   • Page TV Live (tv.php)"
echo "   • Scores temps réel"
echo "   • Mise à jour automatique"
echo "   • Console JavaScript (F12) - Vérifier aucune erreur"
echo ""
echo "3. Après validation (48h en production):"
echo "   • Supprimer axios.min.js:"
echo "     rm sources/js/axios/axios.min.js"
echo "     rm sources/js/axios/axios.min.map"
echo "     rmdir sources/js/axios"
echo ""
echo "   • Supprimer chargement axios dans templates:"
echo "     grep -r 'axios.min.js' sources/smarty/templates/*.tpl"
echo ""
echo "4. En cas de problème:"
echo "   • Restaurer depuis backup:"
echo "     cp -r ${BACKUP_DIR}/* ."
echo ""
echo -e "${GREEN}✅ Migration terminée avec succès !${NC}"
echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
