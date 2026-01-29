# Spécification - Page Opérations

## 1. Vue d'ensemble

Page d'administration système regroupant les opérations sensibles réservées au profil Super Admin (= 1). Cette page permet la gestion des saisons, la fusion de données, les imports/exports et diverses opérations de maintenance.

**Route** : `/operations`

**Accès** : Profil = 1 (Super Admin uniquement)

**Source PHP** : `sources/admin/GestionOperations.php`

---

## 2. Fonctionnalités

### Colonne gauche (Operations sur les données)

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Upload d'images (5 types) | = 1 | Essentielle | ✅ Conserver |
| 2 | Renommer une image | = 1 | Essentielle | ✅ Conserver |
| 3 | Fusionner des licenciés | = 1 | Essentielle | ✅ Conserver |
| 4 | Fusion automatique licenciés non fédéraux | = 1 | Utile | ✅ Conserver |
| 5 | Renommer une équipe | = 1 | Essentielle | ✅ Conserver |
| 6 | Fusionner deux équipes | = 1 | Essentielle | ✅ Conserver |
| 7 | Changer une équipe de club | = 1 | Essentielle | ✅ Conserver |
| 8 | Changer un code compétition | = 1 | Essentielle | ✅ Conserver |
| 9 | Export événement (ZIP) | = 1 | Essentielle | ✅ Conserver |
| 10 | Import événement (ZIP) | = 1 | Essentielle | ✅ Conserver |

### Colonne droite (Administration système)

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 11 | TV control panel | = 1 | Lien externe | 🔧 Déplacer |
| 12 | Worker Management | = 1 | Lien externe | 🔧 Déplacer |
| 13 | Gestion des groupes | = 1 | Lien externe | 🔧 Déplacer |
| 14 | Mise à jour licenciés (PCE) | = 1 | Essentielle | ✅ Conserver |
| 15 | Purge fichiers cache | = 1 | Essentielle | ✅ Conserver |
| 16 | Changer saison active | ≤ 2 | Essentielle | ✅ Conserver |
| 17 | Ajouter une saison | ≤ 2 | Essentielle | ✅ Conserver |
| 18 | Copier les RC | ≤ 2 | Utile | ✅ Conserver |
| 19 | Copier des compétitions | = 1 | Essentielle | ✅ Conserver |
| 20 | Verrou saisons précédentes | ≤ 2 | Essentielle | ✅ Conserver |
| 21 | Documentation (lien) | Tous | Lien externe | 🔧 Déplacer vers menu |
| 22 | API v2 (lien) | Tous | Lien externe | 🔧 Déplacer vers menu |
| 23 | Imports SDP ICF | = 1 | Spécifique ICF | ✅ Conserver |
| 24 | Tester un autre profil | = 1 | Debug | ✅ Conserver |

---

## Détail des fonctionnalités

### 1. Upload d'images

**Types d'images supportés:**

| Type | Format | Dimensions max | Chemin destination |
|------|--------|----------------|-------------------|
| Logo compétition | JPG | 1000x1000 | `img/Competition/L-{CODE}-{SAISON}.jpg` |
| Bandeau compétition | JPG | 2480x250 | `img/Competition/B-{CODE}-{SAISON}.jpg` |
| Sponsor compétition | JPG | 2480x250 | `img/Competition/S-{CODE}-{SAISON}.jpg` |
| Logo club | PNG | 200x200 | `img/KIP/logo/{NUMERO}-logo.png` |
| Logo nation | PNG | 200x200 | `img/Nations/{CODE}.png` |

**Champs:**
- Type d'image (select)
- Code compétition + Saison (si type compétition)
- Numéro club (si type club)
- Code nation (si type nation)
- Fichier image (file input)

**Validation:**
- Vérification du format (JPG/PNG selon type)
- Vérification des dimensions maximales
- Prévisualisation du nom de fichier généré

---

### 2. Renommer une image

**Champs:**
- Type d'image (select)
- Nom fichier actuel (text + extension)
- Nouveau nom (text sans extension)

**Comportement:**
- L'extension est automatiquement conservée
- Prévisualisation du nouveau nom complet

---

### 3. Fusionner des licenciés

**Description:** Fusionne deux licenciés en transférant toutes les données du licencié source vers le licencié cible.

**Champs:**
- Source (sera supprimé) - Autocomplete joueur
- Cible (sera conservé) - Autocomplete joueur

**Tables impactées:**
| Table | Action |
|-------|--------|
| `kp_match_detail` | UPDATE Competiteur source → cible |
| `kp_match_joueur` | DELETE doublons + UPDATE Matric source → cible |
| `kp_scrutineering` | Fusion avec priorité aux données existantes cible |
| `kp_competition_equipe_joueur` | DELETE source (après fusion scrutineering) |
| `kp_licence` | DELETE source |

**Transaction:** Oui (rollback si erreur)

---

### 4. Fusion automatique licenciés non fédéraux

**Description:** Fusionne automatiquement les licenciés ayant un numéro > 2 000 000 (non fédéraux) avec les mêmes Nom, Prénom et Club.

**Critères de sélection du licencié conservé:**
1. Numéro ICF renseigné (priorité)
2. Date de naissance valide
3. Qualification d'arbitre
4. Saison la plus récente

**Affichage:** Liste des fusions effectuées avec détails

---

### 5. Renommer une équipe

**Champs:**
- Source (ancien nom) - Autocomplete équipe (retourne ID)
- Cible (nouveau nom) - Texte libre

**Tables impactées:**
- `kp_competition_equipe` : UPDATE Libelle

---

### 6. Fusionner deux équipes

**Description:** Fusionne deux équipes en transférant toutes les données.

**Champs:**
- Source (sera supprimé) - Autocomplete équipe
- Cible (sera conservé) - Autocomplete équipe

**Tables impactées:**
| Table | Action |
|-------|--------|
| `kp_match` | UPDATE Id_equipeA/B source → cible |
| `kp_match_joueur` | UPDATE via matchs concernés |
| `kp_competition_equipe_joueur` | Transfert vers équipe cible |
| `kp_competition_equipe` | DELETE source |

---

### 7. Changer une équipe de club

**Champs:**
- Équipe - Autocomplete équipe
- Club cible - Autocomplete club

**Tables impactées:**
- `kp_competition_equipe` : UPDATE Code_club

---

### 8. Changer un code compétition

**Description:** Change un code compétition (ou club, ou sous-code).

**Champs:**
- Code recherché (avec autocomplete)
- Code cible
- Code à changer (readonly, rempli par recherche)
- Checkbox "Existe déjà"
- Checkbox "Toutes saisons"

**Modes:**
1. **Code compétition** : Change dans toutes les tables liées
2. **Code club** : Change dans `kp_competition_equipe`
3. **Sous-code compétition** : Change le sous-code uniquement

**Tables impactées (mode compétition):**
| Table | Champ(s) |
|-------|----------|
| `kp_competition` | Code |
| `kp_journee` | Code_competition |
| `kp_competition_equipe` | Code_competition |
| `kp_classement` | Code_competition |
| `kp_rc` | Code_competition |
| `kp_calendrier` | Code_competition |

---

### 9. Export événement

**Champs:**
- Événement (select liste événements)

**Sortie:** Fichier ZIP contenant:
- `evenement.json` : Données de l'événement
- `journees.json` : Journées associées
- `matchs.json` : Matchs des journées
- `equipes.json` : Équipes des matchs
- `joueurs.json` : Joueurs des équipes
- `details.json` : Détails des matchs (buts, cartons)

---

### 10. Import événement

**Champs:**
- Événement cible (select)
- Fichier JSON (upload)

**Comportement:**
- Importe les données d'un export dans l'événement sélectionné
- Met à jour les données existantes ou crée les nouvelles

---

### 14. Mise à jour licenciés (PCE)

**Description:** Import automatique des licenciés depuis la base fédérale FFCK (fichier PCE).

**Actions:**
- Télécharge le fichier PCE du serveur fédéral
- Parse et met à jour la table `kp_licence`
- Affiche le compte-rendu (créations, mises à jour, erreurs)

---

### 15. Purge fichiers cache

**Description:** Supprime les fichiers de cache obsolètes.

**Règles de suppression:**
- Fichiers match : > 1 an
- Fichiers événement : > 2 ans

**Dossiers concernés:**
- `cache/match/`
- `cache/event/`

---

### 16. Changer saison active

**Champs:**
- Saison active (select toutes saisons)

**Action:**
- Met à jour `kp_saison.Etat` (A = Active, I = Inactive)
- Une seule saison peut être active

---

### 17. Ajouter une saison

**Champs:**
- Code saison (ex: 2026)
- Début National (date)
- Fin National (date)
- Début International (date)
- Fin International (date)

**Action:**
- INSERT dans `kp_saison` avec Etat = 'I'

---

### 18. Copier les RC (Responsables Compétition)

**Champs:**
- Saison source (select)
- Saison cible (select)

**Action:**
- Copie tous les enregistrements `kp_rc` de la saison source vers la cible
- Ignore les doublons existants

---

### 19. Copier des compétitions

**Champs:**
- Saison source (select, charge dynamiquement les compétitions)
- Saison cible (select)
- Compétitions à copier (multi-select)
- Checkbox "Copier les matchs des compétitions CP"

**Données copiées:**

| Élément | Copié | Notes |
|---------|-------|-------|
| Compétition | ✅ | Statut ATT, non publique, sans équipes |
| Journées | ✅ | Dates décalées selon même jour de semaine |
| Matchs (CP) | ✅/❌ | Si checkbox cochée, sans équipes/scores/arbitres |
| Équipes | ❌ | Non copiées |
| Joueurs | ❌ | Non copiées |

---

### 20. Verrou saisons précédentes

**Description:** Active/désactive le verrouillage des inscriptions sur les saisons précédentes.

**État session:** `$_SESSION['AuthSaison']` (O = Ouvert, vide = Verrouillé)

---

### 24. Tester un autre profil

**Description:** Permet au Super Admin de simuler un autre profil utilisateur pour tester les permissions.

**Profils disponibles:**
| Niveau | Description |
|--------|-------------|
| 1 | Webmaster / Président |
| 2 | Bureau |
| 3 | Resp. Compétition |
| 4 | Resp. Poule |
| 5 | Délégué fédéral |
| 6 | Organisateur Journée |
| 7 | Resp. Club / Équipe |
| 8 | Consultation simple |
| 9 | Table de Marque |
| 10 | Non utilisé |

---

## Recommandations pour migration Nuxt

### Architecture proposée

L'interface étant très dense, la migration vers Nuxt devrait organiser les fonctionnalités en **onglets ou sections collapsables** :

```
┌─────────────────────────────────────────────────────────────────────┐
│  Opérations Système                                          [=1]  │
├─────────────────────────────────────────────────────────────────────┤
│  [Images] [Licenciés] [Équipes] [Codes] [Import/Export] [Système]  │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Onglet actif: Licenciés                                           │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Fusionner des licenciés                                    │   │
│  │  ────────────────────────────────────────────────────────   │   │
│  │  Source:  [________________] 🔍                             │   │
│  │  Cible:   [________________] 🔍                             │   │
│  │                                                             │   │
│  │  [Fusionner]                                                │   │
│  └─────────────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Fusion automatique (licenciés non fédéraux)                │   │
│  │  ────────────────────────────────────────────────────────   │   │
│  │  ⚠️ Fusionne automatiquement les doublons > 2M              │   │
│  │  [Lancer la fusion automatique]                             │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### Onglets suggérés

| Onglet | Fonctionnalités |
|--------|-----------------|
| **Images** | Upload d'images, Renommer image |
| **Licenciés** | Fusion manuelle, Fusion automatique, Import PCE |
| **Équipes** | Renommer, Fusionner, Déplacer |
| **Codes** | Changer code compétition/club |
| **Import/Export** | Export/Import événement, Imports ICF |
| **Saisons** | Saison active, Ajouter saison, Copier RC, Copier compétitions |
| **Système** | Purge cache, Verrou saisons, Test profil |

### Endpoints API2 à créer

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/admin/operations/images/upload` | Upload image |
| POST | `/admin/operations/images/rename` | Renommer image |
| POST | `/admin/operations/players/merge` | Fusionner licenciés |
| POST | `/admin/operations/players/auto-merge` | Fusion automatique |
| POST | `/admin/operations/teams/rename` | Renommer équipe |
| POST | `/admin/operations/teams/merge` | Fusionner équipes |
| POST | `/admin/operations/teams/move` | Déplacer équipe |
| POST | `/admin/operations/codes/change` | Changer code |
| GET | `/admin/operations/events/{id}/export` | Export événement |
| POST | `/admin/operations/events/{id}/import` | Import événement |
| POST | `/admin/operations/licenses/import-pce` | Import PCE |
| POST | `/admin/operations/cache/purge` | Purge cache |
| GET | `/admin/operations/seasons` | Liste saisons |
| POST | `/admin/operations/seasons` | Ajouter saison |
| PATCH | `/admin/operations/seasons/{code}/activate` | Activer saison |
| POST | `/admin/operations/seasons/copy-rc` | Copier RC |
| POST | `/admin/operations/seasons/copy-competitions` | Copier compétitions |
| POST | `/admin/operations/seasons/toggle-lock` | Verrou saisons |
| GET | `/admin/operations/competitions` | Liste compétitions par saison |

### Améliorations UX prévues

| # | Amélioration | Description |
|---|--------------|-------------|
| 1 | Onglets | Organisation en onglets pour réduire la densité |
| 2 | Confirmations | Modal de confirmation pour toutes les opérations destructives |
| 3 | Prévisualisations | Aperçu des données avant fusion/modification |
| 4 | Progress bar | Barre de progression pour les opérations longues |
| 5 | Logs détaillés | Affichage structuré des résultats (accordéon) |
| 6 | Validation temps réel | Vérification des champs avant soumission |
| 7 | Autocomplete amélioré | Recherche plus performante avec debounce |
| 8 | Responsive | Interface adaptée mobile/tablet |

---

## Priorité de migration

Cette page est classée en **priorité 4** car :
- Profil 1 uniquement (usage limité)
- Fonctionnalités sensibles nécessitant des tests approfondis
- Pas d'urgence fonctionnelle

**Ordre recommandé pour les migrations app4:**
1. ✅ GestionEvenement (terminé)
2. ⏳ GestionDoc
3. ✅ GestionStats (terminé)
4. ⏳ **GestionOperations** (ce document)

---

## Dépendances

### Bibliothèques PHP utilisées
- `pclzip.lib.php` : Création/extraction ZIP
- Autocomplete jQuery : Recherche joueurs/équipes/clubs

### Tables principales
- `kp_licence` : Licenciés
- `kp_competition_equipe` : Équipes
- `kp_competition_equipe_joueur` : Joueurs par équipe
- `kp_match` : Matchs
- `kp_match_joueur` : Compositions
- `kp_match_detail` : Buts/cartons
- `kp_scrutineering` : Contrôle matériel
- `kp_saison` : Saisons
- `kp_competition` : Compétitions
- `kp_journee` : Journées
- `kp_rc` : Responsables compétition
- `kp_evenement` : Événements

---

**Document créé le**: 29 janvier 2026
**Dernière mise à jour**: 29 janvier 2026
**Auteur**: Claude Code
