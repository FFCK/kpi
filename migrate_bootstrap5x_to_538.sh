#!/bin/bash
###############################################################################
# Script de migration Bootstrap 5.x → 5.3.8
# Phase 2: Migration automatique des chemins CSS/JS
#
# Auteur: Claude Code / Laurent Garrigue
# Date: 29 octobre 2025
###############################################################################

set -e

SOURCES_DIR="/home/laurent/Documents/dev/kpi/sources"
DRY_RUN=${1:-false}

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║  Migration Bootstrap 5.x → 5.3.8 - Phase 2                    ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

if [ "$DRY_RUN" = "dry-run" ]; then
    echo -e "${YELLOW}MODE: DRY RUN (aucune modification)${NC}"
else
    echo -e "${GREEN}MODE: MODIFICATION RÉELLE${NC}"
fi
echo ""

###############################################################################
# Groupe A: Bootstrap 5.1.3 → 5.3.8 (13 fichiers live/)
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Groupe A: Migration Bootstrap 5.1.3 → 5.3.8 (fichiers live/)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

LIVE_FILES=(
    "score_e.php"
    "tv2.php"
    "next_game_club.php"
    "teams_club.php"
    "teams.php"
    "next_game.php"
    "score_club_e.php"
    "score_o.php"
    "score.php"
    "score_s.php"
    "score_club.php"
    "score_club_s.php"
    "score_club_o.php"
)

COUNT_LIVE=0

for file in "${LIVE_FILES[@]}"; do
    filepath="$SOURCES_DIR/live/$file"

    if [ ! -f "$filepath" ]; then
        echo -e "${RED}✗ Fichier non trouvé: $file${NC}"
        continue
    fi

    # Vérifier si le fichier contient Bootstrap 5.1.3
    if ! grep -q "bootstrap-5\.1\.3" "$filepath"; then
        echo -e "${YELLOW}○ $file - Pas de référence Bootstrap 5.1.3${NC}"
        continue
    fi

    if [ "$DRY_RUN" = "dry-run" ]; then
        echo -e "${YELLOW}[DRY] $file${NC}"
        echo "      CSS: bootstrap-5.1.3-dist → vendor/twbs/bootstrap/dist"
        echo "      JS:  bootstrap.min.js → bootstrap.bundle.min.js"
    else
        # Backup
        cp "$filepath" "$filepath.bs513.bak"

        # Remplacer CSS
        sed -i 's|../lib/bootstrap-5\.1\.3-dist/css/bootstrap\.min\.css|../vendor/twbs/bootstrap/dist/css/bootstrap.min.css|g' "$filepath"

        # Remplacer JS (passer au bundle qui inclut Popper)
        sed -i 's|../lib/bootstrap-5\.1\.3-dist/js/bootstrap\.min\.js|../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js|g' "$filepath"

        # Mettre à jour version
        sed -i 's|\?v=<?= NUM_VERSION ?>|?v=5.3.8|g' "$filepath"

        echo -e "${GREEN}✓ $file - MIGRÉ${NC}"
        COUNT_LIVE=$((COUNT_LIVE + 1))
    fi
done

echo ""
echo "Groupe A: $COUNT_LIVE fichiers migrés"
echo ""

###############################################################################
# Groupe B: Bootstrap 5.0.2 → 5.3.8 (2 fichiers admin/)
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Groupe B: Migration Bootstrap 5.0.2 → 5.3.8 (fichiers admin/)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

ADMIN_FILES=(
    "scoreboard.php"
)

COUNT_ADMIN=0

for file in "${ADMIN_FILES[@]}"; do
    filepath="$SOURCES_DIR/admin/$file"

    if [ ! -f "$filepath" ]; then
        echo -e "${RED}✗ Fichier non trouvé: $file${NC}"
        continue
    fi

    # Vérifier si le fichier contient Bootstrap 5.0.2
    if ! grep -q "bootstrap-5\.0\.2" "$filepath"; then
        echo -e "${YELLOW}○ $file - Pas de référence Bootstrap 5.0.2${NC}"
        continue
    fi

    if [ "$DRY_RUN" = "dry-run" ]; then
        echo -e "${YELLOW}[DRY] $file${NC}"
        echo "      CSS: bootstrap-5.0.2-dist → vendor/twbs/bootstrap/dist"
        echo "      JS:  bootstrap.bundle.min.js (inchangé, déjà bundle)"
    else
        # Backup
        cp "$filepath" "$filepath.bs502.bak"

        # Remplacer CSS
        sed -i 's|../js/bootstrap-5\.0\.2-dist/css/bootstrap\.min\.css|../vendor/twbs/bootstrap/dist/css/bootstrap.min.css?v=5.3.8|g' "$filepath"

        # Remplacer JS
        sed -i 's|../js/bootstrap-5\.0\.2-dist/js/bootstrap\.bundle\.min\.js|../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js?v=5.3.8|g' "$filepath"

        echo -e "${GREEN}✓ $file - MIGRÉ${NC}"
        COUNT_ADMIN=$((COUNT_ADMIN + 1))
    fi
done

echo ""
echo "Groupe B: $COUNT_ADMIN fichiers migrés"
echo ""

###############################################################################
# Résumé
###############################################################################

TOTAL_MIGRATED=$((COUNT_LIVE + COUNT_ADMIN))

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "RÉSUMÉ MIGRATION PHASE 2"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ "$DRY_RUN" = "dry-run" ]; then
    echo -e "${YELLOW}MODE DRY RUN - Aucune modification effectuée${NC}"
    echo ""
    echo "Pour exécuter réellement les modifications:"
    echo "  bash $0"
else
    echo -e "${GREEN}✅ Migration terminée${NC}"
    echo ""
    echo "Fichiers migrés:"
    echo "  • Groupe A (5.1.3): $COUNT_LIVE fichiers"
    echo "  • Groupe B (5.0.2): $COUNT_ADMIN fichiers"
    echo "  • TOTAL: $TOTAL_MIGRATED fichiers"
    echo ""
    echo "Backups créés:"
    echo "  • *.bs513.bak (Bootstrap 5.1.3)"
    echo "  • *.bs502.bak (Bootstrap 5.0.2)"
    echo ""
    echo "Prochaines étapes:"
    echo "  1. Tester les fichiers migrés"
    echo "  2. Vérifier dans le navigateur"
    echo "  3. Si OK: supprimer backups"
    echo "  4. Si problème: restaurer avec 'bash restore_backups.sh'"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
