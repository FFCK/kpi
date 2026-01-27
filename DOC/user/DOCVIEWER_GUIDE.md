# Guide d'utilisation du visualiseur de documentation

## 📚 Présentation

Le visualiseur de documentation KPI permet d'accéder facilement à toute la documentation du système directement depuis l'interface d'administration.

## 🚀 Accès

### Depuis GestionOperations

1. Connectez-vous à l'interface d'administration KPI
2. Accédez à **GestionOperations** (Administration > Opérations)
3. Dans la colonne de droite, cliquez sur le lien **"📚 Documentation"**
4. Le visualiseur s'ouvre dans un nouvel onglet

### URL directe

Accès direct via : `https://votre-domaine.com/admin/DocViewer.php`

## 📖 Utilisation

### Navigation

Le visualiseur est divisé en deux sections :

#### Barre latérale (gauche)
- **📘 Documentation Utilisateur** : Guides et explications pour les utilisateurs finaux
- **💻 Documentation Développeur** : Documentation technique pour les développeurs

Cliquez sur une catégorie pour afficher/masquer son contenu.

#### Zone de contenu (droite)
- Affiche le contenu du document sélectionné
- Supporte le format Markdown avec mise en forme automatique
- Navigation par fil d'Ariane (breadcrumb) en haut de la page

### Recherche

Pour trouver un document :
1. Parcourez les catégories dans la barre latérale
2. Les documents sont organisés par dossiers thématiques
3. Cliquez sur un document pour l'afficher

### Fonctionnalités

- **Liens internes** : Les liens vers d'autres fichiers markdown fonctionnent automatiquement
- **Liens externes** : Ouverts dans un nouvel onglet avec une icône ↗
- **Bouton retour en haut** : Apparaît automatiquement sur les longs documents
- **Responsive** : S'adapte aux écrans mobiles et tablettes
- **Mode impression** : La sidebar est masquée lors de l'impression

## ➕ Ajouter de nouvelles documentations

### Pour les utilisateurs

1. Créez un fichier `.md` (Markdown) dans le dossier `DOC/user/`
2. Vous pouvez créer des sous-dossiers pour organiser la documentation
3. Le fichier apparaîtra automatiquement dans le visualiseur

Exemple de structure :
```
DOC/user/
├── README.md
├── FEATURE_NAME.md
├── guides/
│   ├── GUIDE_1.md
│   └── GUIDE_2.md
└── tutorials/
    └── TUTORIAL_1.md
```

### Pour les développeurs

Même principe dans `DOC/developer/` :

```
DOC/developer/
├── README.md
├── guides/
│   ├── migrations/
│   │   └── MIGRATION_GUIDE.md
│   └── infrastructure/
│       └── INFRASTRUCTURE_GUIDE.md
└── fixes/
    └── FIX_DESCRIPTION.md
```

### Format Markdown recommandé

```markdown
# Titre principal

## Section 1

Paragraphe avec **gras** et *italique*.

### Sous-section

- Liste à puces
- Élément 2
  - Sous-élément

1. Liste numérotée
2. Élément 2

#### Code inline

Utilisez `code` pour du code inline.

#### Bloc de code

\`\`\`php
<?php
echo "Hello World";
?>
\`\`\`

#### Liens

[Texte du lien](URL)
[Autre doc](./autre-doc.md)

#### Images

![Alt text](chemin/vers/image.png)

#### Tables

| Colonne 1 | Colonne 2 |
|-----------|-----------|
| Valeur 1  | Valeur 2  |

#### Citations

> Ceci est une citation
```

### Bonnes pratiques

1. **Titre clair** : Commencez toujours par un titre de niveau 1 (`# Titre`)
2. **Structure hiérarchique** : Utilisez les niveaux de titres (h2, h3, h4...)
3. **Liens relatifs** : Pour les liens vers d'autres docs, utilisez des chemins relatifs
4. **Nommage des fichiers** :
   - UPPERCASE_WITH_UNDERSCORES.md pour les docs importantes
   - Nom descriptif et explicite
5. **Organisation** : Regroupez les docs par thème dans des sous-dossiers

## 🔧 Maintenance

### Bibliothèque Markdown

Le système utilise la bibliothèque **Parsedown** pour convertir le Markdown en HTML.

Installation (si nécessaire) :
```bash
make backend_composer_install
```

### Mise à jour

Pour ajouter de nouvelles fonctionnalités au visualiseur :

1. **PHP Backend** : `sources/admin/DocViewer.php`
2. **Template** : `sources/smarty/templates/DocViewer.tpl`
3. **JavaScript** : `sources/js/DocViewer.js`
4. **CSS** : `sources/css/DocViewer.css`

### Traductions

Les traductions sont dans `sources/commun/MyLang.ini` :

```ini
[fr]
Documentation = "Documentation"
Documentation_KPI = "Documentation KPI"
titre_docviewer = "Visualiseur de Documentation"

[en]
Documentation = "Documentation"
Documentation_KPI = "KPI Documentation"
titre_docviewer = "Documentation Viewer"
```

## ❓ Dépannage

### Le document ne s'affiche pas

1. Vérifiez que le fichier existe bien dans `DOC/user/` ou `DOC/developer/`
2. Vérifiez l'extension : doit être `.md`
3. Vérifiez les permissions de lecture du fichier

### La mise en forme est incorrecte

1. Vérifiez la syntaxe Markdown du document
2. Testez le document sur un éditeur Markdown en ligne
3. Assurez-vous que Parsedown est installé : `make backend_composer_install`

### Les liens ne fonctionnent pas

1. Les liens relatifs doivent pointer vers des fichiers dans `DOC/`
2. Les liens absolus doivent commencer par `http://` ou `https://`
3. Vérifiez l'encodage des caractères spéciaux dans les URLs

## 📝 Support

Pour toute question ou problème :

1. Consultez la documentation développeur dans `DOC/developer/`
2. Vérifiez les logs PHP pour les erreurs
3. Contactez l'équipe de développement

---

**Version** : 1.0
**Date** : 2025-11-23
**Auteur** : KPI Development Team
