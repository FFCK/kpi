# Migration FPDF → mPDF - Plan de migration

**Date**: 19 octobre 2025
**Objectif**: Résoudre les problèmes UTF-8 et moderniser la génération PDF

---

## Problème actuel

### FPDF 1.7 (version actuelle)
- ❌ Pas de support UTF-8 natif (ISO-8859-1 seulement)
- ❌ Dernière mise à jour FPDF : juin 2023
- ❌ 3 versions différentes dans le projet (fpdf/, fpdf-1.7/, fpdf-1.8.4/)
- ⚠️ Méthode `Open()` obsolète dans FPDF 1.8+

### Impact
- 43 fichiers PHP génèrent des PDF
- Risque d'encodage UTF-8 lors de mise à jour FPDF
- Maintenance complexe avec plusieurs versions

---

## Solution recommandée : mPDF

### Avantages
- ✅ **UTF-8 natif** - Support complet Unicode
- ✅ **Activement maintenu** - Compatible PHP 8.3
- ✅ **API similaire FPDF** - Migration progressive possible
- ✅ **HTML/CSS support** - Possibilité de moderniser les templates
- ✅ **Font subsetting** - PDFs optimisés
- ✅ **Composer** - Gestion moderne des dépendances

### Statistiques
- **Téléchargements** : Plus utilisé que TCPDF pour UTF-8
- **GitHub** : https://github.com/mpdf/mpdf
- **Documentation** : https://mpdf.github.io/

---

## Phase 1 : Installation et préparation

### 1.1 Installer mPDF via Composer

```bash
cd /home/laurent/Documents/dev/kpi/sources
composer require mpdf/mpdf
```

### 1.2 Vérifier la compatibilité PHP

mPDF nécessite :
- PHP >= 7.0 (✅ compatible PHP 7.4 et PHP 8.x)
- Extensions : mbstring, gd, zlib

```bash
# Vérifier dans le container PHP
docker exec kpi_php php -m | grep -E "mbstring|gd|zlib"
```

---

## Phase 2 : Créer une classe de compatibilité

### 2.1 Wrapper FPDF → mPDF

Créer `sources/commun/MyPDF.php` :

```php
<?php
/**
 * MyPDF - Wrapper mPDF compatible avec l'API FPDF existante
 * Permet migration progressive sans modifier tous les fichiers
 */

require_once __DIR__ . '/../../vendor/autoload.php';

class MyPDF extends \Mpdf\Mpdf
{
    private $x0;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        // Convertir orientation FPDF vers mPDF
        $mpdfOrientation = ($orientation == 'L') ? 'L' : 'P';

        parent::__construct([
            'mode' => 'utf-8',
            'format' => $format,
            'orientation' => $mpdfOrientation,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
        ]);
    }

    // Méthode obsolète dans FPDF 1.8+, vide dans mPDF
    public function Open()
    {
        // Ne fait rien - compatibilité FPDF
    }

    // Alias pour compatibilité
    public function Output($dest = '', $name = '', $isUTF8 = false)
    {
        if ($dest == 'I') {
            return parent::Output($name, \Mpdf\Output\Destination::INLINE);
        } elseif ($dest == 'D') {
            return parent::Output($name, \Mpdf\Output\Destination::DOWNLOAD);
        } elseif ($dest == 'F') {
            return parent::Output($name, \Mpdf\Output\Destination::FILE);
        } else {
            return parent::Output($name, \Mpdf\Output\Destination::STRING_RETURN);
        }
    }
}
```

### 2.2 Alternative : Classe héritée personnalisée

Pour chaque fichier PDF avec `class PDF extends FPDF` :

```php
<?php
// AVANT
require('lib/fpdf-1.7/fpdf.php');
class PDF extends FPDF {
    var $x0;
}

// APRÈS
require_once(__DIR__ . '/commun/MyPDF.php');
class PDF extends MyPDF {
    var $x0;
}
```

---

## Phase 3 : Migration progressive (43 fichiers)

### Ordre de priorité

#### Groupe A - Feuilles de match (priorité haute)
- [ ] `PdfMatchMulti.php` (erreur constatée)
- [ ] `admin/FeuilleMatchMulti.php`
- [ ] `admin/FeuilleMatchVierge.php`
- [ ] `admin/FeuilleMarque3.php`

#### Groupe B - Feuilles de présence
- [ ] `admin/FeuillePresence.php`
- [ ] `admin/FeuillePresenceEN.php`
- [ ] `admin/FeuillePresenceCat.php`
- [ ] `admin/FeuillePresenceU21.php`
- [ ] `admin/FeuillePresenceVisa.php`
- [ ] `admin/FeuillePresencePhoto.php`
- [ ] `admin/FeuillePresencePhoto2.php`
- [ ] `admin/FeuillePresencePhotoRef.php`

#### Groupe C - Contrôles et stats
- [ ] `admin/FeuilleControle.php`
- [ ] `admin/FeuilleControleEN.php`
- [ ] `admin/FeuilleStats.php`
- [ ] `admin/FeuilleStatsEN.php`
- [ ] `admin/FeuilleCards.php`

#### Groupe D - Classements
- [ ] `admin/FeuilleCltChpt.php`
- [ ] `admin/FeuilleCltChptDetail.php`
- [ ] `admin/FeuilleCltNiveau.php`
- [ ] `admin/FeuilleCltNiveauDetail.php`
- [ ] `admin/FeuilleCltNiveauJournee.php`
- [ ] `admin/FeuilleCltNiveauNiveau.php`
- [ ] `admin/FeuilleCltNiveauPhase.php`
- [ ] `PdfCltChpt.php`
- [ ] `PdfCltChptDetail.php`
- [ ] `PdfCltNiveau.php`
- [ ] `PdfCltNiveauDetail.php`
- [ ] `PdfCltNiveauJournee.php`
- [ ] `PdfCltNiveauNiveau.php`
- [ ] `PdfCltNiveauPhase.php`

#### Groupe E - Listes de matchs
- [ ] `admin/FeuilleListeMatchs.php`
- [ ] `admin/FeuilleListeMatchsEN.php`
- [ ] `PdfListeMatchs.php`
- [ ] `PdfListeMatchsEN.php`
- [ ] `PdfListeMatchs4Terrains.php`
- [ ] `PdfListeMatchs4TerrainsEn.php`
- [ ] `PdfListeMatchs4TerrainsEn2.php`
- [ ] `PdfListeMatchs4TerrainsEn3.php`
- [ ] `PdfListeMatchs4TerrainsEn4.php`

#### Groupe F - Divers
- [ ] `admin/FeuilleGroups.php`
- [ ] `admin/FeuilleInstances.php`
- [ ] `PdfQrCodeApp.php`
- [ ] `PdfQrCodes.php`

### Procédure de migration par fichier

```bash
# 1. Backup
cp sources/PdfMatchMulti.php sources/PdfMatchMulti.php.fpdf.backup

# 2. Modifier le require
# Remplacer : require('lib/fpdf-1.7/fpdf.php');
# Par :       require_once(__DIR__ . '/commun/MyPDF.php');

# 3. Supprimer Open() si présent
# Chercher et supprimer : $pdf->Open();

# 4. Tester
# Générer un PDF et vérifier :
# - Encodage UTF-8 correct
# - Mise en page identique
# - Taille de fichier raisonnable
```

---

## Phase 4 : Tests et validation

### 4.1 Tests unitaires par groupe

Pour chaque groupe (A, B, C, D, E, F) :

1. Générer un PDF avec données UTF-8
2. Vérifier les caractères accentués
3. Comparer visuellement avec ancienne version
4. Vérifier la taille du fichier (mPDF peut être plus gros)

### 4.2 Tests d'intégration

- [ ] Générer feuille de match complet
- [ ] Générer présence avec noms accentués
- [ ] Générer classement
- [ ] Export multi-matchs

### 4.3 Checklist validation

- [ ] Caractères UTF-8 affichés correctement (é, è, à, ç, etc.)
- [ ] Mise en page identique à FPDF
- [ ] Performance acceptable (< 2x temps FPDF)
- [ ] Taille fichier raisonnable (< 1.5x taille FPDF)
- [ ] Pas de warnings PHP 8

---

## Phase 5 : Nettoyage final

### 5.1 Supprimer anciennes versions FPDF

```bash
cd sources/lib
rm -rf fpdf/
rm -rf fpdf-1.7/
# Garder fpdf-1.8.4 en backup temporaire
mv fpdf-1.8.4 fpdf-1.8.4.backup.old
```

### 5.2 Mettre à jour composer.json

Ajouter mPDF comme dépendance officielle dans la documentation.

### 5.3 Commit Git

```bash
git add sources/
git commit -m "feat: migrate from FPDF to mPDF for UTF-8 support

- Install mPDF 8.x via Composer
- Create MyPDF wrapper for FPDF compatibility
- Migrate 43 PDF generation files
- Remove old FPDF versions (1.7, fpdf/)
- Fix UTF-8 encoding issues in all PDFs

All PDF files now properly display accented characters.
Tested on PHP 7.4 and PHP 8.x.
"
```

---

## Risques et mitigation

### Risques identifiés

| Risque | Impact | Probabilité | Mitigation |
|--------|--------|-------------|------------|
| Différences de rendu | Moyen | Moyenne | Tests visuels systématiques |
| Performance dégradée | Faible | Faible | Benchmarks avant/après |
| Taille fichiers | Faible | Moyenne | Font subsetting activé |
| Incompatibilités API | Moyen | Faible | Wrapper de compatibilité |

### Plan de rollback

Si problème majeur :
1. `git revert` du commit de migration
2. Restaurer `fpdf-1.7`
3. Analyser les logs d'erreur
4. Corriger le wrapper MyPDF
5. Re-tester sur fichier isolé

---

## Calendrier estimé

- **Phase 1** (Installation) : 1 heure
- **Phase 2** (Wrapper) : 3 heures
- **Phase 3** (Migration 43 fichiers) : 2-3 jours
  - Groupe A : 4h
  - Groupe B : 6h
  - Groupe C : 3h
  - Groupe D : 8h
  - Groupe E : 6h
  - Groupe F : 3h
- **Phase 4** (Tests) : 1 jour
- **Phase 5** (Nettoyage) : 2 heures

**Total estimé** : 4-5 jours de travail

---

## Alternatives considérées

### TCPDF
- ✅ UTF-8 natif
- ✅ Très complet (barcodes, signatures)
- ❌ API très différente de FPDF → migration complexe
- ❌ Performances parfois lentes

### DomPDF
- ✅ UTF-8 natif
- ✅ HTML to PDF simple
- ❌ Nécessite réécrire tous les PDF en HTML
- ❌ Migration trop lourde

### tFPDF (FPDF-UTF8)
- ✅ API 100% compatible FPDF
- ❌ Non maintenu depuis plusieurs années
- ❌ Pas de support PHP 8.3+

**Conclusion** : mPDF offre le meilleur équilibre compatibilité/fonctionnalités/maintenance.

---

## Références

- [mPDF Documentation](https://mpdf.github.io/)
- [mPDF GitHub](https://github.com/mpdf/mpdf)
- [FPDF vs mPDF comparison](https://pdfbolt.com/blog/top-php-pdf-generation-libraries)
- [Migration guide FPDF → mPDF](https://mpdf.github.io/about-mpdf/requirements-v7.html)

---

**Prochaine étape** : Validation du plan et démarrage Phase 1 (Installation mPDF)
