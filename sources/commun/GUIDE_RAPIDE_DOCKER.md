# Guide Rapide - Consolidation des Traductions

## 🚀 Commandes Prêtes à l'Emploi

### Pour Analyser les Fichiers (Optionnel)

```bash
# Analyser les différences entre MyLang.conf et MyLang.ini
docker exec -it kpi_php_1 php /sources/commun/compare_translations.php
```

### Pour Appliquer la Consolidation (Développement)

**Copier-coller ces commandes une par une** :

```bash
# 1. Créer le fichier unifié
docker exec -it kpi_php_1 php /sources/commun/merge_translations.php

# 2. Sauvegarder les originaux (depuis l'hôte)
cd sources/commun
cp MyLang.conf MyLang.conf.backup
cp MyLang.ini MyLang.ini.backup
cp MySmarty.php MySmarty.php.backup

# 3. Activer le fichier unifié
mv MyLang_unified.ini MyLang.ini

# 4. Patcher MySmarty.php
cd ../..
docker exec -it kpi_php_1 php /sources/commun/patch_mysmarty.php

# 5. Redémarrer les conteneurs
make dev_restart

# 6. Vérifier les logs
make dev_logs
```

### Pour Revenir en Arrière (si problème)

```bash
# Restaurer les fichiers originaux
cd sources/commun
mv MyLang.conf.backup MyLang.conf
mv MyLang.ini.backup MyLang.ini
mv MySmarty.php.backup MySmarty.php

# Redémarrer
cd ../..
make dev_restart
```

### Pour Nettoyer Après Tests Réussis

```bash
# Supprimer les backups
cd sources/commun
rm -f MyLang.conf.backup MyLang.ini.backup MySmarty.php.backup
rm -f MyLang_processed.conf MyLang_processed.ini

# Nettoyer le cache Smarty
rm -f ../templates_c/*.php
```

## 🔍 Commandes de Vérification

```bash
# Vérifier le nom du conteneur PHP
docker ps | grep php

# Vérifier le contenu du fichier unifié
docker exec -it kpi_php_1 head -50 /sources/commun/MyLang_unified.ini

# Vérifier les logs en temps réel
make dev_logs

# Vérifier l'état des conteneurs
make dev_status
```

## 📝 Tester la Migration

### 1. Tester les PDFs

- Aller sur l'interface admin
- Générer un PDF de classement
- Générer une feuille de marque
- Vérifier que les traductions s'affichent correctement

### 2. Tester l'Interface Smarty

- Naviguer dans les différentes pages de l'interface
- Tester en français : changer la langue en FR
- Tester en anglais : changer la langue en EN
- Vérifier que toutes les traductions sont cohérentes

### 3. Vérifier les Logs

```bash
# Suivre les logs en temps réel
make dev_logs

# Rechercher des erreurs spécifiques
docker logs kpi_php_1 2>&1 | grep -i "error\|warning" | tail -20
```

## 🏭 Migration en Production

**Uniquement après tests réussis en développement !**

```bash
# 1. Créer le fichier unifié
docker exec -it kpi_prod_php_1 php /sources/commun/merge_translations.php

# 2. Sauvegarder (depuis l'hôte)
cd sources/commun
mv MyLang.conf MyLang.conf.backup_$(date +%Y%m%d)
mv MyLang.ini MyLang.ini.backup_$(date +%Y%m%d)

# 3. Activer
mv MyLang_unified.ini MyLang.ini

# 4. Patcher
cd ../..
docker exec -it kpi_prod_php_1 php /sources/commun/patch_mysmarty.php

# 5. Redémarrer production
make prod_restart

# 6. Surveiller les logs
make prod_logs
```

## ⚡ Commandes Alternatives

### Via Shell Interactif (Plus de Contrôle)

```bash
# Entrer dans le conteneur
make php_bash

# Dans le conteneur :
cd /sources/commun
php compare_translations.php    # Analyser
php merge_translations.php      # Fusionner
head -50 MyLang_unified.ini    # Vérifier
php patch_mysmarty.php         # Patcher
exit

# Depuis l'hôte :
cd sources/commun
cp MyLang.conf MyLang.conf.backup
cp MyLang.ini MyLang.ini.backup
mv MyLang_unified.ini MyLang.ini
cd ../..
make dev_restart
```

### Mode Preview (Sans Modification)

```bash
# Aperçu de la fusion (sans créer le fichier)
docker exec -it kpi_php_1 php /sources/commun/merge_translations.php --preview

# Aperçu du patch MySmarty.php (sans modifier)
docker exec -it kpi_php_1 php /sources/commun/patch_mysmarty.php --preview
```

## 📊 Résultats Attendus

Après la consolidation, vous devriez avoir :

- **MyLang.ini** : Fichier unique avec **~740 clés**
  - Français : 737 clés
  - Anglais : 738 clés
  - Chinois : 337 clés

- **MySmarty.php** : Modifié pour utiliser MyLang.ini

- **MyLang.conf** : Peut être supprimé (après tests)

## ❓ Dépannage

### Le conteneur n'est pas trouvé

```bash
# Vérifier le nom exact du conteneur
docker ps | grep php

# Utiliser le nom trouvé dans les commandes
docker exec -it [NOM_CONTENEUR] php /sources/commun/merge_translations.php
```

### Erreur "MyLang.ini not found"

```bash
# Vérifier que les fichiers existent
docker exec -it kpi_php_1 ls -la /sources/commun/MyLang*

# Vérifier les permissions
docker exec -it kpi_php_1 ls -la /sources/commun/
```

### Les traductions ne s'affichent pas

```bash
# Vider le cache Smarty
docker exec -it kpi_php_1 rm -rf /sources/templates_c/*.php

# Supprimer les fichiers traités
cd sources/commun
rm -f MyLang_processed.conf MyLang_processed.ini

# Redémarrer
cd ../..
make dev_restart
```

### Erreurs dans les logs

```bash
# Voir les dernières erreurs PHP
docker logs kpi_php_1 2>&1 | grep -i error | tail -20

# Suivre les logs en direct
make dev_logs
```

## 📚 Documentation Complète

- [CONSOLIDATION_TRADUCTIONS.md](../../WORKFLOW_AI/CONSOLIDATION_TRADUCTIONS.md) - Documentation technique complète
- [README_CONSOLIDATION.md](README_CONSOLIDATION.md) - Guide détaillé des scripts

---

**Date** : 2025-11-22
**Version** : 1.1 (Docker)
**Auteur** : Claude Code
