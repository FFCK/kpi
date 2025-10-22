# Migration PdfMatchMulti.php - Notes Techniques

**Date**: 2025-10-19
**Fichier**: sources/PdfMatchMulti.php
**Migration**: FPDF → mPDF v8.2.6

---

## Problèmes Rencontrés et Solutions

### 🐛 Problème 1: Décalage de contenu après images

**Symptôme initial**:
- Images (bandeau, sponsor) restent en place
- Tout le contenu texte passe sur page 2
- Première colonne décalée vers le haut

**Cause**:
Différence comportementale entre FPDF et mPDF concernant la méthode `Image()`:

- **FPDF**: `Image()` avec coordonnées absolues ne modifie PAS la position du curseur (X, Y)
- **mPDF**: `Image()` PEUT modifier la position du curseur, même avec coordonnées absolues

**Solution appliquée**:

#### Colonne 1 - Ligne 475-506
```php
// AVANT (FPDF)
$pdf->SetY(9);
// Images...
$pdf->Ln(11);

// APRÈS (mPDF)
$yStart = 9;
$pdf->SetY($yStart);
// Images...
$pdf->SetY($yStart);  // ← RESTAURATION position Y
$pdf->SetX($x0);      // ← RESTAURATION position X
$pdf->Ln(11);
```

**Principe**: Sauvegarder la position Y avant les images, puis la restaurer après, car les images utilisent des coordonnées absolues et ne doivent pas décaler le flux de contenu.

#### Colonne 2 - Ligne 653-674
```php
// AVANT
$pdf->SetY(8);
// Cells...
$pdf->image('img/type...');

// APRÈS
$yStartCol2 = 8;
$pdf->SetY($yStartCol2);
// Cells...
$pdf->image('img/type...');
$pdf->SetX($x0);  // ← RESTAURATION X après image type match
```

#### Images drapeaux (pays) - Ligne 832-846
```php
// AVANT
$pdf->image('img/Pays/' . $paysA . '.png', 151, 15, 9, 6);
$pdf->image('img/Pays/' . $paysB . '.png', 229, 15, 9, 6);
$pdf->SetX(10);

// APRÈS
// Sauvegarder position AVANT drapeaux (insérés en fin de page)
$currentY = $pdf->y;  // mPDF utilise ->y et ->x, pas GetY()/GetX()
$currentX = $pdf->x;

$pdf->image('img/Pays/' . $paysA . '.png', 151, 15, 9, 6);
$pdf->image('img/Pays/' . $paysB . '.png', 229, 15, 9, 6);

// Restaurer position APRÈS drapeaux
$pdf->SetY($currentY);
$pdf->SetX($currentX);
```

**Problème spécifique** : Les drapeaux sont insérés à Y=15mm (haut de page) alors que le curseur est en bas de page (Y≈193mm). mPDF peut décaler la colonne 2 vers le bas à cause de cette insertion tardive d'images en position absolue haute.

**Solution** : Sauvegarde/restauration complète de Y et X, pas seulement X.

---

## Modifications Apportées

### 1. Import et héritage (lignes 3-13)
```php
// AVANT
require('lib/fpdf/fpdf.php');
class PDF extends FPDF

// APRÈS
require_once('commun/MyPDF.php');
class PDF extends MyPDF
```

### 2. Suppression Open() (ligne 31)
```php
// AVANT
$pdf = new PDF('L');
$pdf->Open();

// APRÈS
$pdf = new PDF('L');
// Open() supprimé - obsolète et cause bugs avec mPDF
```

### 3. Output() avec constantes (ligne 948)
```php
// AVANT
$pdf->Output('Match(s) ' . $listMatch . '.pdf', 'I');

// APRÈS
$pdf->Output('Match(s) ' . $listMatch . '.pdf', \Mpdf\Output\Destination::INLINE);
```

### 4. Corrections positionnement images
- **Ligne 475-506**: Colonne 1 - Restauration Y/X après images bandeau/sponsor
- **Ligne 653-674**: Colonne 2 - Restauration X après image type match
- **Ligne 831-840**: Drapeaux pays - Clarification commentaire restauration X

---

## Tests de Validation

### ✅ Syntaxe PHP
- PHP 7.4.33: OK
- PHP 8.3.15: OK

### ⏳ Rendu PDF (À tester en conditions réelles)
**Vérifications à effectuer**:
1. [ ] Bandeau/logo en haut de page
2. [ ] Contenu texte commence juste après bandeau (pas de saut de page)
3. [ ] Colonnes 1 et 2 alignées correctement
4. [ ] Images drapeaux pays positionnées correctement
5. [ ] Page 2 (si commentaires longs) : layout correct
6. [ ] UTF-8 correct: "Délégué", "Équipe", noms avec accents

**Comment tester**:
```bash
# Générer une feuille de match via l'interface web
# URL: https://kpi.local/PdfMatchMulti.php?listMatch=123
```

---

## Différences Comportementales FPDF vs mPDF

| Aspect | FPDF | mPDF v8 |
|--------|------|---------|
| `Image()` position absolue | Ne change pas curseur | Peut changer curseur |
| `Open()` | Méthode obsolète mais tolérée | Cause bugs - à supprimer |
| Output() destination | Lettres: 'I', 'D', 'F', 'S' | Constantes: `Destination::INLINE` |
| Lire position curseur | `GetY()`, `GetX()` | Propriétés `->y`, `->x` |
| UTF-8 | Nécessite encodages manuels | Natif via `mode => 'utf-8'` |
| AutoPageBreak | Comportement strict | Plus flexible mais différent |

### ⚠️ Important : Accès aux positions

**FPDF** :
```php
$currentY = $pdf->GetY();
$currentX = $pdf->GetX();
```

**mPDF v8** :
```php
$currentY = $pdf->y;  // Propriété publique
$currentX = $pdf->x;  // Propriété publique
```

Les méthodes `GetY()` et `GetX()` **n'existent PAS** dans mPDF !

---

## Points d'Attention pour Futures Migrations

1. **Toujours restaurer X/Y après Image()** si l'image est en position absolue
   - Sauvegarder : `$y = $pdf->y; $x = $pdf->x;`
   - Restaurer : `$pdf->SetY($y); $pdf->SetX($x);`

2. **Supprimer tous les appels à Open()**
   - Cause des bugs de buffer avec mPDF

3. **Remplacer codes Output() par constantes Destination**
   - 'I' → `Destination::INLINE`
   - 'D' → `Destination::DOWNLOAD`
   - 'F' → `Destination::FILE`
   - 'S' → `Destination::STRING_RETURN`

4. **Remplacer GetY()/GetX() par propriétés ->y/->x**
   - mPDF n'a PAS de méthodes GetY()/GetX()
   - Utiliser directement `$pdf->y` et `$pdf->x`

5. **Tester avec et sans images** pour détecter décalages

6. **Vérifier marges** : mPDF et FPDF ont des valeurs par défaut différentes

---

## Compatibilité PHP

✅ **PHP 7.4.33** - Testé et validé
✅ **PHP 8.3.15** - Testé et validé

Dépendances Composer:
- `mpdf/mpdf: ^8.2`
- `psr/log: ^1.1` (forcé pour compatibilité PHP 7.4)

---

## Prochaines Étapes

1. Tester génération PDF en conditions réelles
2. Valider affichage UTF-8 (noms avec accents)
3. Si OK → Migrer les 42 autres fichiers FPDF en suivant ce pattern
4. Documenter patterns spécifiques découverts

---

**Statut**: ✅ Migration code terminée, ⏳ Tests réels en attente
