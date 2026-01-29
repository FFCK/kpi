# Fix FeuilleMatchMulti.php - Mémoire et Multi-matchs

**Date**: 29 janvier 2026
**Statut**: ✅ **COMPLÉTÉ**
**Fichier**: `sources/admin/FeuilleMatchMulti.php`
**Contexte**: Erreur mémoire épuisée lors de la génération de plusieurs feuilles de match

---

## 📊 Problèmes identifiés

### 1. Erreur mémoire (PHP Fatal error)

```
PHP Fatal error: Allowed memory size of 134217728 bytes exhausted
(tried to allocate 356352 bytes) in
/var/www/html/vendor/endroid/qr-code/src/Writer/AbstractGdWriter.php on line 57
```

**Cause**: La génération de QR codes via la bibliothèque GD accumulait les ressources mémoire entre les itérations de la boucle sur les matchs.

### 2. Un seul match affiché

**Cause**: Le PDF était créé À CHAQUE ITÉRATION de la boucle (`$pdf = new PDF('L');` ligne 473), écrasant le PDF précédent. Seul le dernier match était donc conservé et affiché.

### 3. Warnings PHP 8.4 deprecated

```
PHP Deprecated: str_split(): Passing null to parameter #1 ($string) of type string is deprecated
PHP Deprecated: strlen(): Passing null to parameter #1 ($string) of type string is deprecated
```

**Cause**: La variable `$Commentaires` pouvait être `null` (venant de la BDD).

---

## 🔧 Corrections appliquées

### 1. Création du PDF avant la boucle (lignes 26-30)

```php
// ❌ AVANT
$chaqueMatch = explode(',', $listMatch);

for ($h = 0; $h < count($chaqueMatch); $h++) {
    // ... code ...
    $pdf = new PDF('L');  // ← Créé à chaque itération!
```

```php
// ✅ APRÈS
$chaqueMatch = explode(',', $listMatch);

// Création du PDF UNE SEULE FOIS avant la boucle
$pdf = new PDF('L');
$pdf->SetAuthor("FFCK - Kayak-polo.info");
$pdf->SetCreator("FFCK - Kayak-polo.info avec mPDF");

for ($h = 0; $h < count($chaqueMatch); $h++) {
    // ... code ...
    // Production de la feuille de match PDF suivante
    $pdf->SetTitle($lang['Feuille_de_marque']);
    $pdf->AddPage();  // ← Ajoute une page au PDF existant
```

### 2. Suppression de la création dupliquée (lignes 477-483)

```php
// ❌ AVANT
//Création du PDF de base
$pdf = new PDF('L');
$pdf->SetTitle($lang['Feuille_de_marque']);
$pdf->SetAuthor("FFCK - Kayak-polo.info");
$pdf->SetCreator("FFCK - Kayak-polo.info avec mPDF");

// Production de la feuille de match PDF suivante
$pdf->AddPage();
```

```php
// ✅ APRÈS
// Production de la feuille de match PDF suivante
$pdf->SetTitle($lang['Feuille_de_marque']);
$pdf->AddPage();
```

### 3. Libération des ressources QR code (lignes 897-899)

```php
// ❌ AVANT
$qrcode = new QRcode('https://kayak-polo.info/admin/FeuilleMarque2.php?idMatch=' . $idMatch, 'L');
$qrcode->displayFPDF($pdf, 264, 164, 21);
```

```php
// ✅ APRÈS
$qrcode = new QRcode('https://kayak-polo.info/admin/FeuilleMarque2.php?idMatch=' . $idMatch, 'L');
$qrcode->displayFPDF($pdf, 264, 164, 21);
unset($qrcode);  // Libérer la ressource QR code
```

### 4. Correction null pour $Commentaires (lignes 237-242)

```php
// ❌ AVANT
$Commentaires = $row['Commentaires_officiels'];
$Commentaires1 = str_split($Commentaires, 85);
```

```php
// ✅ APRÈS
$Commentaires = $row['Commentaires_officiels'] ?? '';
$Commentaires1 = $Commentaires !== '' ? str_split($Commentaires, 85) : [];
```

### 5. Nettoyage mémoire en fin de boucle (lignes 1034-1040)

```php
// ✅ AJOUTÉ - Libérer les ressources après chaque match
unset($row);
unset($result5);
unset($detail);
unset($detail2);
unset($visuels);
gc_collect_cycles();  // Force garbage collection
```

---

## 📋 Résumé des modifications

| Ligne | Type | Description |
|-------|------|-------------|
| 26-30 | Ajout | Création unique du PDF avant la boucle |
| 237 | Modif | Null coalescing pour $Commentaires |
| 238 | Modif | Vérification avant str_split() |
| 477-483 | Suppression | Retrait création PDF dupliquée |
| 897-899 | Ajout | unset($qrcode) après utilisation |
| 1034-1040 | Ajout | Nettoyage mémoire en fin de boucle |

---

## 🎯 Résultats

| Aspect | Avant | Après |
|--------|-------|-------|
| Multi-matchs | ❌ 1 seul affiché | ✅ Tous les matchs |
| Mémoire | ❌ Fatal error | ✅ Stable |
| Warnings PHP 8.4 | ❌ 3 deprecated | ✅ 0 warning |

---

## ✅ Tests de validation

- [x] Génération d'un seul match
- [x] Génération de plusieurs matchs (multi-sélection)
- [x] QR code affiché correctement
- [x] Pas de warnings dans error.log
- [x] Pas d'erreur mémoire

---

**Auteur**: Claude Code
**Branche**: `claude/migrate-admin-backend-A1TBN`
