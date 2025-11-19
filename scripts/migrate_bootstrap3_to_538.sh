#!/bin/bash
# ==============================================================================
# Script de Migration Bootstrap 3.x → 5.3.8 (Phase 3)
# ==============================================================================
# Date: 30 octobre 2025
# Auteur: Laurent Garrigue / Claude Code
# Description: Migre les templates Smarty et fichiers PHP de Bootstrap 3.4.1 vers 5.3.8
# ==============================================================================

set -e  # Exit on error

# Couleurs pour output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Chemins
PROJECT_ROOT="/home/laurent/Documents/dev/kpi"
TEMPLATES_DIR="$PROJECT_ROOT/sources/smarty/templates"
LIVE_DIR="$PROJECT_ROOT/sources/live"
BACKUP_DIR="$PROJECT_ROOT/backups/bootstrap3_migration_$(date +%Y%m%d_%H%M%S)"

# Fichiers à migrer (seulement ceux qui existent)
SMARTY_TEMPLATES=(
    "pagelogin.tpl"
    "kppage.tpl"
    "frame_page.tpl"
    "kppagewide.tpl"
    "kppageleaflet.tpl"
)

LIVE_FILES=(
    "tv.php"
)

# Templates inclus (header/footer)
INCLUDED_TEMPLATES=(
    "kpheader.tpl"
    "kpheaderwide.tpl"
    "kpfooter.tpl"
    "kpmain_menu.tpl"
)

# ==============================================================================
# Fonctions
# ==============================================================================

print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Créer backup d'un fichier
backup_file() {
    local file=$1
    local backup_path="$BACKUP_DIR/$(basename $(dirname $file))/$(basename $file)"

    mkdir -p "$(dirname $backup_path)"
    cp "$file" "$backup_path"
    cp "$file" "$file.bs3.bak"  # Backup local aussi

    print_success "Backup: $(basename $file) → $backup_path"
}

# Migration automatique des patterns simples
migrate_file_automatic() {
    local file=$1
    local filename=$(basename "$file")

    print_info "Migration automatique: $filename"

    # 1. Chemins Bootstrap 3 → Bootstrap 5.3.8
    sed -i 's|js/bootstrap/css/bootstrap\.min\.css|vendor/twbs/bootstrap/dist/css/bootstrap.min.css|g' "$file"
    sed -i 's|js/bootstrap/js/bootstrap\.min\.js|vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js|g' "$file"
    sed -i 's|js/bootstrap-3\.3\.1/|vendor/twbs/bootstrap/dist/|g' "$file"

    # Chemins relatifs (live/)
    sed -i 's|css/bootstrap\.min\.css|../vendor/twbs/bootstrap/dist/css/bootstrap.min.css|g' "$file"
    sed -i 's|js/bootstrap\.min\.js|../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js|g' "$file"

    # 2. Grille: col-xs-* → col-*
    sed -i 's/col-xs-/col-/g' "$file"

    # 3. Visibility utilities
    sed -i 's/\bhidden-xs\b/d-none d-sm-block/g' "$file"
    sed -i 's/\bhidden-sm\b/d-sm-none d-md-block/g' "$file"
    sed -i 's/\bhidden-md\b/d-md-none d-lg-block/g' "$file"
    sed -i 's/\bhidden-lg\b/d-lg-none d-xl-block/g' "$file"

    sed -i 's/\bvisible-xs-block\b/d-block d-sm-none/g' "$file"
    sed -i 's/\bvisible-sm-block\b/d-none d-sm-block d-md-none/g' "$file"
    sed -i 's/\bvisible-md-block\b/d-none d-md-block d-lg-none/g' "$file"
    sed -i 's/\bvisible-lg-block\b/d-none d-lg-block d-xl-none/g' "$file"

    sed -i 's/\bvisible-xs-inline\b/d-inline d-sm-none/g' "$file"
    sed -i 's/\bvisible-sm-inline\b/d-none d-sm-inline d-md-none/g' "$file"

    # 4. Float utilities
    sed -i 's/\bpull-left\b/float-start/g' "$file"
    sed -i 's/\bpull-right\b/float-end/g' "$file"

    # 5. Text utilities
    sed -i 's/\btext-left\b/text-start/g' "$file"
    sed -i 's/\btext-right\b/text-end/g' "$file"

    # 6. Center block
    sed -i 's/\bcenter-block\b/mx-auto/g' "$file"

    # 7. Data attributes Bootstrap 5
    sed -i 's/data-toggle="/data-bs-toggle="/g' "$file"
    sed -i 's/data-target="/data-bs-target="/g' "$file"
    sed -i 's/data-dismiss="/data-bs-dismiss="/g' "$file"
    sed -i 's/data-ride="/data-bs-ride="/g' "$file"
    sed -i 's/data-slide="/data-bs-slide="/g' "$file"
    sed -i 's/data-slide-to="/data-bs-slide-to="/g' "$file"
    sed -i 's/data-parent="/data-bs-parent="/g' "$file"

    # 8. Panel → Card (basique, nécessite révision manuelle)
    sed -i 's/\bpanel panel-default\b/card/g' "$file"
    sed -i 's/\bpanel panel-primary\b/card border-primary/g' "$file"
    sed -i 's/\bpanel panel-success\b/card border-success/g' "$file"
    sed -i 's/\bpanel panel-info\b/card border-info/g' "$file"
    sed -i 's/\bpanel panel-warning\b/card border-warning/g' "$file"
    sed -i 's/\bpanel panel-danger\b/card border-danger/g' "$file"

    sed -i 's/\bpanel-heading\b/card-header/g' "$file"
    sed -i 's/\bpanel-body\b/card-body/g' "$file"
    sed -i 's/\bpanel-footer\b/card-footer/g' "$file"
    sed -i 's/\bpanel-title\b/card-title/g' "$file"

    # 9. Form utilities
    sed -i 's/\bhelp-block\b/form-text/g' "$file"
    sed -i 's/\bcontrol-label\b/form-label/g' "$file"

    # 10. Button sizes
    sed -i 's/\bbtn-xs\b/btn-sm/g' "$file"

    # 11. Labels → Badges
    sed -i 's/\blabel label-default\b/badge bg-secondary/g' "$file"
    sed -i 's/\blabel label-primary\b/badge bg-primary/g' "$file"
    sed -i 's/\blabel label-success\b/badge bg-success/g' "$file"
    sed -i 's/\blabel label-info\b/badge bg-info/g' "$file"
    sed -i 's/\blabel label-warning\b/badge bg-warning/g' "$file"
    sed -i 's/\blabel label-danger\b/badge bg-danger/g' "$file"

    # 12. Navbar
    sed -i 's/\bnavbar-default\b/navbar-light bg-light/g' "$file"
    sed -i 's/\bnavbar-inverse\b/navbar-dark bg-dark/g' "$file"
    sed -i 's/\bnavbar-fixed-top\b/fixed-top/g' "$file"
    sed -i 's/\bnavbar-fixed-bottom\b/fixed-bottom/g' "$file"

    # 13. Pagination
    sed -i 's/\bpagination-lg\b/pagination pagination-lg/g' "$file"
    sed -i 's/\bpagination-sm\b/pagination pagination-sm/g' "$file"

    # 14. Wells → Cards
    sed -i 's/\bwell well-lg\b/card card-body p-4/g' "$file"
    sed -i 's/\bwell well-sm\b/card card-body p-2/g' "$file"
    sed -i 's/\bwell\b/card card-body/g' "$file"

    # 15. Input groups
    sed -i 's/\binput-group-addon\b/input-group-text/g' "$file"
    sed -i 's/\binput-group-btn\b/input-group-append/g' "$file"

    print_success "Migration automatique terminée: $filename"
}

# Analyse manuelle requise
analyze_manual_changes() {
    local file=$1
    local filename=$(basename "$file")

    print_warning "Analyse manuelle requise pour: $filename"

    # Glyphicons
    if grep -q "glyphicon" "$file"; then
        print_warning "  → Glyphicons détectés (migration vers Font Awesome ou Bootstrap Icons requise)"
        grep -n "glyphicon" "$file" | head -5
    fi

    # Panels complexes
    if grep -q "panel" "$file"; then
        print_warning "  → Panels détectés (vérifier structure card)"
    fi

    # Navbars
    if grep -q "navbar" "$file"; then
        print_warning "  → Navbar détecté (vérifier structure Bootstrap 5)"
    fi

    # Modals
    if grep -q "modal" "$file"; then
        print_warning "  → Modal détecté (vérifier data-bs-* attributes)"
    fi

    # Dropdowns
    if grep -q "dropdown" "$file"; then
        print_warning "  → Dropdown détecté (vérifier data-bs-* attributes)"
    fi
}

# Migrer un fichier complet
migrate_file() {
    local file=$1
    local filename=$(basename "$file")

    if [ ! -f "$file" ]; then
        print_error "Fichier introuvable: $filename"
        return 1
    fi

    print_header "Migration: $filename"

    # Backup
    backup_file "$file"

    # Migration automatique
    migrate_file_automatic "$file"

    # Analyse manuelle
    analyze_manual_changes "$file"

    echo ""
}

# ==============================================================================
# Main
# ==============================================================================

main() {
    print_header "Bootstrap 3.x → 5.3.8 Migration (Phase 3)"

    echo -e "${BLUE}Date:${NC} $(date)"
    echo -e "${BLUE}Projet:${NC} $PROJECT_ROOT"
    echo -e "${BLUE}Backup:${NC} $BACKUP_DIR"
    echo ""

    # Créer répertoire backup
    mkdir -p "$BACKUP_DIR/smarty/templates"
    mkdir -p "$BACKUP_DIR/live"
    print_success "Répertoire backup créé: $BACKUP_DIR"
    echo ""

    # Vérifier que Bootstrap 5.3.8 est installé
    if [ ! -d "$PROJECT_ROOT/sources/vendor/twbs/bootstrap" ]; then
        print_error "Bootstrap 5.3.8 non trouvé dans vendor/twbs/bootstrap"
        print_info "Exécutez d'abord: make composer_require package=twbs/bootstrap:^5.3"
        exit 1
    fi
    print_success "Bootstrap 5.3.8 détecté dans vendor/"
    echo ""

    # Migration des templates Smarty
    print_header "1/3 - Migration Templates Smarty"
    for template in "${SMARTY_TEMPLATES[@]}"; do
        migrate_file "$TEMPLATES_DIR/$template"
    done

    # Migration des templates inclus
    print_header "2/3 - Migration Templates Inclus (Header/Footer)"
    for template in "${INCLUDED_TEMPLATES[@]}"; do
        if [ -f "$TEMPLATES_DIR/$template" ]; then
            migrate_file "$TEMPLATES_DIR/$template"
        else
            print_warning "Template non trouvé: $template (ignoré)"
        fi
    done

    # Migration des fichiers live/
    print_header "3/3 - Migration Fichiers Live"
    for file in "${LIVE_FILES[@]}"; do
        migrate_file "$LIVE_DIR/$file"
    done

    # Résumé
    print_header "Migration Phase 3 - Résumé"

    echo -e "${GREEN}✓ Migration automatique terminée${NC}"
    echo ""
    echo -e "${YELLOW}⚠ ACTIONS MANUELLES REQUISES:${NC}"
    echo ""
    echo "1. Vérifier les glyphicons et les remplacer par Font Awesome:"
    echo "   grep -r 'glyphicon' sources/smarty/templates/"
    echo ""
    echo "2. Vérifier les panels/cards:"
    echo "   grep -r 'panel' sources/smarty/templates/"
    echo ""
    echo "3. Vérifier les navbars:"
    echo "   grep -r 'navbar' sources/smarty/templates/"
    echo ""
    echo "4. Tester TOUS les templates dans le navigateur:"
    echo "   - Login page (pagelogin.tpl) ⚠️ CRITIQUE"
    echo "   - Page admin (kppage.tpl) ⚠️ CRITIQUE"
    echo "   - Autres pages"
    echo ""
    echo "5. Vérifier la console JavaScript (aucune erreur)"
    echo ""
    echo "6. Tester responsive (mobile, tablet, desktop)"
    echo ""
    echo -e "${BLUE}Backups disponibles:${NC}"
    echo "   - Archive complète: $BACKUP_DIR"
    echo "   - Backups locaux: *.bs3.bak"
    echo ""
    echo -e "${BLUE}Restauration (si nécessaire):${NC}"
    echo "   cp fichier.tpl.bs3.bak fichier.tpl"
    echo ""
    echo -e "${GREEN}Phase 3 - Migration automatique terminée !${NC}"
    echo -e "${YELLOW}Passez aux tests et validations manuelles.${NC}"
}

# Exécution
main "$@"
