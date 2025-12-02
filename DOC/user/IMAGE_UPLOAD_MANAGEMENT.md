# Upload et Gestion d'Images

**Fonctionnalité** : Téléchargement et gestion des logos et images pour les compétitions

---

## 📋 À quoi ça sert ?

La fonctionnalité d'**upload d'images** permet de télécharger et gérer :
- **Logos de compétitions** (affichés sur les classements, feuilles de match, site public)
- **Images diverses** pour personnaliser l'affichage
- **Photos de joueurs** (pour les feuilles de présence)

---

## 🎯 Types d'images supportés

### 1. Logos de compétition

**Formats acceptés** : JPG, JPEG, PNG, GIF
**Taille maximale** : 500 Ko
**Utilisation** :
- Affichage sur les classements PDF
- Feuilles de match
- Pages publiques du site
- Incrustations vidéo

### 2. Images générales

**Formats acceptés** : JPG, JPEG, PNG, GIF
**Taille maximale** : Variable selon le type
**Types disponibles** :
- Bannières
- Arrière-plans
- Illustrations

---

## 🚀 Comment uploader une image

### Upload de logo de compétition

1. **Accéder à la gestion de compétition**
   - Menu : `Administration` → `Gestion Compétition`

2. **Sélectionner la compétition**
   - Choisir la compétition dans la liste

3. **Uploader le logo**
   - Cliquer sur le bouton "Parcourir" ou "Choisir un fichier"
   - Sélectionner votre image (JPG, PNG, GIF)
   - Cliquer sur "Upload" ou "Envoyer"

4. **Vérification**
   - Le logo s'affiche immédiatement dans l'interface
   - Message de confirmation : "Upload effectué avec succès !"

### Upload d'autres images

1. **Accéder à la gestion des opérations**
   - Menu : `Administration` → `Gestion Opérations`

2. **Sélectionner le type d'image**
   - Choisir le type dans le menu déroulant
   - Options disponibles selon votre profil

3. **Uploader l'image**
   - Cliquer sur "Parcourir"
   - Sélectionner votre fichier
   - Valider l'upload

---

## ✅ Bonnes pratiques

### Dimensions recommandées

| Type | Dimensions | Poids |
|------|------------|-------|
| **Logo compétition** | 300x300 px | < 200 Ko |
| **Bannière** | 1920x200 px | < 500 Ko |
| **Photo joueur** | 300x400 px | < 100 Ko |

### Format d'image

- **JPG** : Pour les photos et images avec beaucoup de couleurs
- **PNG** : Pour les logos avec transparence
- **GIF** : Pour les logos simples (non recommandé, préférer PNG)

### Optimisation

✅ **À faire** :
- Redimensionner les images avant l'upload
- Compresser les images (TinyPNG, Compressor.io)
- Utiliser des noms de fichiers clairs (ex: `logo-championnat-n1.jpg`)

❌ **À éviter** :
- Uploader des images trop grandes (plusieurs Mo)
- Utiliser des captures d'écran non recadrées
- Uploader des formats non supportés (BMP, TIFF)

---

## 🖼️ Redimensionnement automatique

Le système peut redimensionner automatiquement certaines images :
- **Conservation des proportions** : L'image est redimensionnée sans déformation
- **Optimisation** : Compression automatique pour réduire la taille
- **Formats multiples** : Génération de miniatures si nécessaire

---

## 🔒 Sécurité et restrictions

### Vérifications automatiques

Le système vérifie :
- ✅ **Format de fichier** : Seuls JPG, PNG, GIF sont acceptés
- ✅ **Taille maximale** : Limite selon le type d'image
- ✅ **Contenu** : Vérification basique du fichier

### Droits d'accès

- **Profil ≤ 4** : Upload sur toutes les compétitions
- **Profil > 4** : Upload uniquement sur les compétitions de vos clubs

---

## 🐛 Problèmes courants

### "Le fichier est trop gros"

**Solution** :
1. Compresser l'image avec un outil en ligne (TinyPNG, Compressor.io)
2. Redimensionner l'image à une taille plus petite
3. Convertir en JPG si l'image est en PNG

### "Format de fichier non supporté"

**Solution** :
1. Vérifier l'extension du fichier (doit être .jpg, .jpeg, .png ou .gif)
2. Convertir l'image dans un format supporté
3. Ne pas utiliser de formats comme .bmp, .tiff, .webp

### "Échec de l'upload"

**Causes possibles** :
- Connexion internet instable
- Taille de fichier trop grande
- Problème de permissions (contactez l'administrateur)

**Solution** :
1. Réessayer l'upload
2. Vérifier votre connexion internet
3. Essayer avec une image plus petite
4. Si le problème persiste, contactez l'administrateur

---

## 💡 Conseils pratiques

### Pour les logos de compétition

- Utilisez un logo **carré** (300x300 px) pour un meilleur rendu
- Préférez le **PNG avec transparence** si votre logo a un fond
- Assurez-vous que le logo est **lisible** en petit format

### Pour les photos de joueurs

- Utilisez le **format portrait** (3:4)
- Privilégiez des **photos récentes**
- Évitez les photos floues ou mal cadrées

### Gestion des fichiers

- Les logos sont nommés automatiquement : `{saison}-{code_compet}.jpg`
- Les anciens logos sont **écrasés** lors d'un nouvel upload
- Conservez une copie locale de vos images

---

## 📁 Où sont stockées les images ?

- **Logos de compétition** : `/img/logo/`
- **Autres images** : `/img/` (avec sous-dossiers selon le type)
- **Photos joueurs** : `/img/photos/`

**Note** : Vous n'avez normalement pas besoin d'accéder directement à ces dossiers, tout se fait via l'interface d'administration.

---

## 📚 Documentation connexe

- [Guide utilisateur général](README.md)
- [Gestion de compétition](MULTI_COMPETITION_TYPE.md)

---

**Version** : 1.0
**Date** : Décembre 2025
**Public** : Administrateurs de compétitions, gestionnaires
