# Pattern 8 : Images en Arrière-Plan - Guide Complet

## 🎯 Quand Utiliser Ce Pattern

Utilisez ce pattern quand vous avez des **images décoratives** insérées au début de chaque page :
- Bandeau en haut (logo, titre)
- Sponsor en bas
- QRCode sur le côté
- Logo d'organisation
- Tout élément graphique qui ne doit PAS déplacer le contenu principal

## ⚠️ Symptômes du Problème

Sans ce pattern, vous verrez :
- ✗ Tout le contenu passe en page 2
- ✗ Seules les images du haut restent en page 1
- ✗ Page 1 presque vide avec juste le bandeau
- ✗ Décalages verticaux inexpliqués

## 🔍 Cause du Problème

**FPDF vs mPDF** :
- FPDF : Les images avec coordonnées absolues ne bougent PAS le curseur
- mPDF : Les images peuvent déplacer le curseur MÊME avec coordonnées absolues

**Cas problématique typique** :
```php
$pdf->AddPage();
$pdf->Image('bandeau.png', 10, 8, 200, 20);  // Y=8mm (haut)
$pdf->Image('sponsor.png', 10, 184, 200, 16); // Y=184mm (bas!)
// Le sponsor à Y=184mm déclenche AutoPageBreak!
// mPDF pense qu'on dépasse la page → saut de page
// Le contenu suivant commence en page 2
```

## ✅ Solution Complète (5 Étapes)

### Étape 1 : Définir la Position de Départ

```php
$pdf->SetTopMargin(30);
$pdf->AddPage();

// Définir où le CONTENU doit commencer (pas les images!)
$yStart = 30;  // Valeur du TopMargin
```

### Étape 2 : Désactiver AutoPageBreak

```php
// CRITIQUE : Empêche les images de déclencher des sauts de page
$pdf->SetAutoPageBreak(false);
```

### Étape 3 : Insérer TOUTES les Images

```php
// Bandeau (haut)
if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
    $img = redimImage($visuels['bandeau'], 262, 10, 20, 'C');
    $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
}

// Logo KPI (milieu-haut)
if ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
    $pdf->Image('img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', 'https://...');
}

// Sponsor (BAS - celui qui cause problème!)
if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
    $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
    $pdf->Image($img['image'], $img['positionX'], 184, 0, $img['newHauteur']);
}

// QRCode (coin supérieur droit)
$qrcode = new QRcode($url, 'L');
$qrcode->displayFPDF($pdf, $qr_x, 9, 21);
```

### Étape 4 : Réactiver AutoPageBreak

```php
// Réactiver avec les bonnes marges
if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
    $pdf->SetAutoPageBreak(true, 28);  // Marge basse = 28mm (pour sponsor)
} else {
    $pdf->SetAutoPageBreak(true, 15);  // Marge basse = 15mm (normale)
}
```

### Étape 5 : Forcer le Curseur à la Position de Départ

```php
// FORCER le curseur à la position où le contenu doit commencer
$pdf->SetY($yStart);  // Y = 30mm (après TopMargin)
$pdf->SetX(15);       // X = marge gauche par défaut
```

### Le Contenu Peut Maintenant Commencer !

```php
// Maintenant le contenu commence à la bonne position
$pdf->SetFont('Arial', 'BI', 12);
$pdf->Cell(137, 5, $titreEvenement, 0, 0, 'L');
$pdf->Cell(136, 5, $titreDate, 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(273, 6, "Liste des Matchs", 0, 1, 'C');
// ...
```

## 🔄 Pour les Nouvelles Pages (Ruptures)

Répétez **EXACTEMENT** la même séquence :

```php
if ($rupture != $Oldrupture) {
    if ($Oldrupture != '') {
        $pdf->Cell(273, 3, '', 'T', '1', 'C');
        $pdf->AddPage();

        // 1. Désactiver AutoPageBreak
        $pdf->SetAutoPageBreak(false);

        // 2. Insérer toutes les images
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            // ...
        }
        // ... autres images ...

        // 3. Réactiver AutoPageBreak
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetAutoPageBreak(true, 28);
        } else {
            $pdf->SetAutoPageBreak(true, 15);
        }

        // 4. Forcer curseur à $yStart
        $pdf->SetY($yStart);
        $pdf->SetX(15);
    }
}
```

## ✅ Checklist de Validation

Avant de considérer la migration terminée, vérifier :

- [ ] `$yStart` défini AVANT les images (valeur = TopMargin)
- [ ] `SetAutoPageBreak(false)` AVANT toutes les images
- [ ] TOUTES les images insérées (bandeau, logo, sponsor, QRcode)
- [ ] `SetAutoPageBreak(true, X)` APRÈS toutes les images
- [ ] Bonne valeur de marge basse (28 si sponsor, 15 sinon)
- [ ] `SetY($yStart)` et `SetX(15)` APRÈS réactivation AutoPageBreak
- [ ] Même logique dans les ruptures de page (`AddPage()` dans boucle)
- [ ] Variable `$yStart` accessible dans le scope des ruptures

## 🧪 Test de Validation

```bash
# Tester avec PHP 7.4
docker compose -f compose.dev.yaml run --rm kpi php -l /var/www/html/VotreFichier.php

# Tester avec PHP 8.3
docker compose -f compose.dev.yaml run --rm kpi8 php -l /var/www/html/VotreFichier.php

# Générer le PDF et vérifier visuellement :
# - Page 1 contient le contenu (titres, tableaux)
# - Images sont en arrière-plan (bandeau, sponsor)
# - Pas de page blanche ou presque vide
```

## 📚 Fichiers de Référence

Voir implémentation complète dans :
- `sources/PdfListeMatchs.php` : Lignes 154-199 (première page) et 266-307 (ruptures)
- `sources/PdfMatchMulti.php` : Lignes 475-506

## 🚨 Erreurs Courantes

### ❌ Erreur 1 : Oublier de Désactiver AutoPageBreak

```php
// MAUVAIS
$pdf->AddPage();
$pdf->Image('sponsor.png', 10, 184, 200, 16);  // Déclenche saut de page!

// BON
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);  // Désactiver AVANT
$pdf->Image('sponsor.png', 10, 184, 200, 16);
```

### ❌ Erreur 2 : Oublier de Repositionner le Curseur

```php
// MAUVAIS
$pdf->SetAutoPageBreak(false);
$pdf->Image('bandeau.png', 10, 8, 200, 20);
$pdf->SetAutoPageBreak(true, 15);
// Curseur n'est pas à la bonne position!

// BON
$pdf->SetAutoPageBreak(false);
$pdf->Image('bandeau.png', 10, 8, 200, 20);
$pdf->SetAutoPageBreak(true, 15);
$pdf->SetY($yStart);  // Forcer position!
$pdf->SetX(15);
```

### ❌ Erreur 3 : Variable $yStart Non Accessible

```php
// MAUVAIS
$pdf->AddPage();
$yStart = 30;  // Défini ici
// ...
if ($rupture != $Oldrupture) {
    $pdf->SetY($yStart);  // $yStart pas accessible ici!
}

// BON
$pdf->AddPage();
$yStart = 30;  // Variable disponible pour toute la suite
// ... (pas de nouveau scope)
if ($rupture != $Oldrupture) {
    $pdf->SetY($yStart);  // OK!
}
```

### ❌ Erreur 4 : Réactiver AutoPageBreak Trop Tôt

```php
// MAUVAIS
$pdf->SetAutoPageBreak(false);
$pdf->Image('bandeau.png', 10, 8, 200, 20);
$pdf->SetAutoPageBreak(true, 15);  // Trop tôt!
$pdf->Image('sponsor.png', 10, 184, 200, 16);  // Déclenche saut!

// BON
$pdf->SetAutoPageBreak(false);
$pdf->Image('bandeau.png', 10, 8, 200, 20);
$pdf->Image('sponsor.png', 10, 184, 200, 16);  // Toutes les images
$qrcode->displayFPDF($pdf, 262, 9, 21);         // QRCode aussi
$pdf->SetAutoPageBreak(true, 15);  // Réactiver APRÈS TOUT
```

## 💡 Points Clés à Retenir

1. **AutoPageBreak est votre ennemi** pendant l'insertion d'images décoratives
2. **Le curseur doit être contrôlé** explicitement avec SetY/SetX
3. **$yStart = valeur du TopMargin** (généralement 30)
4. **Toujours** appliquer la même logique aux ruptures de page
5. **Tester visuellement** le PDF généré (pas seulement la syntaxe)

## 🎓 Résumé en Une Phrase

> **Désactivez AutoPageBreak, insérez toutes vos images, réactivez AutoPageBreak, et forcez le curseur à la position de départ du contenu.**
