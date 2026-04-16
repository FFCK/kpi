# Documentation Tâches CRON - KPI

**Date**: 10 avril 2026
**Projet**: KPI
**Serveur**: Production kayak-polo.info

---

## Vue d'ensemble

Le système KPI utilise **2 tâches automatisées** :

1. **Import licences PCE** (FFCK) - Quotidien
2. **Verrouillage présences** compétitions - Multi-quotidien

Ces tâches sont implémentées comme **services Symfony** dans API2, exposées à la fois comme :
- **Endpoints HTTP** (`POST /admin/operations/...`) utilisables depuis app4
- **Commandes console** (`app:import-pce`, `app:update-competition-locks`) exécutables en cron

---

## 1. Import licences PCE (FFCK)

### Description

Synchronisation des licenciés, arbitres et surclassements depuis l'extranet FFCK.

### Implémentation

| Composant | Fichier |
|-----------|---------|
| Service | `sources/api2/src/Service/PceImportService.php` |
| Endpoint | `POST /admin/operations/licenses/import-pce` |
| Commande | `app:import-pce` |
| Contrôleur | `sources/api2/src/Controller/AdminOperationsController.php` |

### Actions effectuées

1. **Téléchargement** fichier PCE
   - URL configurée via variable d'environnement `FFCK_PCE_URL`
   - Authentification HTTP Basic (`FFCK_PCE_USER`, `FFCK_PCE_PWD`)
   - Format: Fichier texte .pce (sections [licencies], [juges_kap], [surclassements])

2. **Parsing et import**
   - Batch processing (300 inserts/requête pour licenciés et arbitres, 100 pour surclassements)
   - Sections traitées:
     - `[licencies]` → Table `kp_licence`
     - `[juges_kap]` → Table `kp_arbitre`
     - `[surclassements]` → Table `kp_surclassement`

3. **Mises à jour structures**
   - `kp_club` - Clubs
   - `kp_cd` - Comités départementaux
   - `kp_cr` - Comités régionaux

4. **Notification**
   - Email envoyé via `NotificationService` en cas de succès ou d'erreur

### Configuration CRON

```cron
# Import licences PCE FFCK - Tous les jours à 2h du matin
0 2 * * * cd /var/www/html/api2 && php bin/console app:import-pce >> /var/log/kpi/cron_pce.log 2>&1
```

**Fréquence**: Quotidienne (nuit)
**Horaire**: 2h00 (charge serveur faible)
**Durée estimée**: 30-90 secondes

### Variables d'environnement

Définies dans `sources/api2/.env.local` (non versionné) :

```
FFCK_PCE_URL=...
FFCK_PCE_USER=...
FFCK_PCE_PWD=...
```

### Exécution manuelle

Depuis le conteneur PHP :
```bash
cd /var/www/html/api2
php bin/console app:import-pce
```

Ou depuis app4 : **Opérations > Import/Export > Import licences PCE**

---

## 2. Verrouillage présences compétitions

### Description

Verrouillage/déverrouillage automatique des feuilles de présence selon proximité des compétitions nationales.

### Implémentation

| Composant | Fichier |
|-----------|---------|
| Service | `sources/api2/src/Service/CompetitionLockService.php` |
| Endpoint | `POST /admin/operations/competitions/update-locks` |
| Commande | `app:update-competition-locks` |
| Contrôleur | `sources/api2/src/Controller/AdminOperationsController.php` |

### Règles de verrouillage

#### Verrouillage (`Verrou = 'O'`)

**Conditions**:
- Compétition type: `N*` (Nationales) OU `CF*` (Coupe de France)
- Date début: Dans **moins de 6 jours**

**Effet**: Empêche modification feuilles présence

#### Déverrouillage (`Verrou = 'N'`)

**Conditions**:
- Compétition type: `N*` OU `CF*`
- Date fin: Depuis **moins de 3 jours**

**Effet**: Autorise à nouveau modifications (corrections post-compétition)

### Configuration CRON

```cron
# Verrouillage présences - Toutes les 6 heures
0 */6 * * * cd /var/www/html/api2 && php bin/console app:update-competition-locks >> /var/log/kpi/cron_verrous.log 2>&1
```

**Fréquence**: Toutes les 6 heures (4x/jour)
**Durée estimée**: <5 secondes

### Exécution manuelle

Depuis le conteneur PHP :
```bash
cd /var/www/html/api2
php bin/console app:update-competition-locks
```

Ou depuis app4 : **Opérations > Import/Export > Verrouillage compétitions**

---

## Notification email

Les deux tâches utilisent le `NotificationService` centralisé (`sources/api2/src/Service/NotificationService.php`).

### Configuration

Variables d'environnement dans `sources/api2/.env` :

```
MAILER_DSN=sendmail://default
MAILER_FROM='KPI <contact@kayak-polo.info>'
MAILER_ADMIN_TO=contact@kayak-polo.info
```

### Comportement

- **Import PCE** : Email envoyé systématiquement (succès avec stats, ou erreur avec message)
- **Verrouillage** : Email envoyé uniquement si des compétitions sont verrouillées/déverrouillées
- Les erreurs d'envoi email sont loguées mais ne bloquent pas l'exécution

---

## Scripts legacy (dépréciés)

Les scripts suivants sont remplacés par les commandes Symfony ci-dessus :

| Script legacy | Remplacé par |
|---------------|-------------|
| `sources/commun/cron_maj_licencies.php` | `app:import-pce` |
| `sources/commun/cron_verrou_presences.php` | `app:update-competition-locks` |

---

## Procédures d'urgence

### Import PCE échoué

**Symptômes**: Email "[KPI-CRON] Import PCE - ERREUR" reçu, ou pas d'email du tout

**Actions**:
1. Exécution manuelle avec verbose
   ```bash
   cd /var/www/html/api2
   php bin/console app:import-pce -vvv
   ```

2. Vérifier les variables d'environnement FFCK
   ```bash
   php bin/console debug:container --env-vars | grep FFCK
   ```

3. Vérifier les logs
   ```bash
   tail -100 /var/log/kpi/cron_pce.log
   ```

### Verrouillage incorrect

**Symptômes**: Compétitions verrouillées/déverrouillées à tort

**Actions**:
1. Vérifier `kp_competition.Verrou`
   ```sql
   SELECT Code, Libelle, Verrou, Code_saison
   FROM kp_competition
   WHERE Code_saison = 2026
   AND (Code LIKE 'N%' OR Code LIKE 'CF%');
   ```

2. Correction manuelle si nécessaire
   ```sql
   UPDATE kp_competition
   SET Verrou = 'O'  -- ou 'N'
   WHERE Code = 'N1M' AND Code_saison = 2026;
   ```

3. Vérifier dates journées
   ```sql
   SELECT Id, Code_competition, Date_debut, Date_fin,
          DATEDIFF(Date_debut, CURDATE()) as jours_avant
   FROM kp_journee
   WHERE Code_saison = 2026
   AND Code_competition LIKE 'N%';
   ```
