# Documentation Tâches CRON - KPI

**Date**: 19 octobre 2025
**Projet**: KPI
**Serveur**: Production kayak-polo.info

---

## Vue d'ensemble

Le système KPI utilise **2 tâches CRON critiques** pour l'automatisation:

1. **Import licences PCE** (FFCK) - Quotidien
2. **Verrouillage présences** compétitions - Multi-quotidien

---

## 1. Import licences PCE (FFCK)

### Description

Synchronisation quotidienne des licenciés, arbitres et surclassements depuis l'extranet FFCK.

### Fichier exécuté

**Chemin**: `/var/www/html/commun/cron_maj_licencies.php`
**Code source**: [sources/commun/cron_maj_licencies.php](sources/commun/cron_maj_licencies.php)

### Fonctionnement

```php
<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$myBdd = new MyBdd();
$myBdd->ImportPCE2();  // Méthode principale (MyBdd.php:398)

// Logging et email
$msg = /* stats import */;
file_put_contents("log_cron.txt", $msg, FILE_APPEND);
mail('contact@kayak-polo.info', '[KPI-CRON]', $msg);
```

### Actions effectuées

1. **Téléchargement** fichier PCE
   - URL: `https://extranet.ffck.org/reportingExterne/getFichierPce/{YEAR}`
   - Format: Fichier texte .pce (sections [licencies], [juges_kap], [surclassements])

2. **Parsing et import**
   - Batch processing (300 inserts/requête pour performance)
   - Sections traitées:
     - `[licencies]` → Table `kp_licence`
     - `[juges_kap]` → Table `kp_arbitre`
     - `[surclassements]` → Table `kp_surclassement`

3. **Mises à jour structures**
   - `kp_club` - Clubs
   - `kp_cd` - Comités départementaux
   - `kp_cr` - Comités régionaux

### Configuration CRON recommandée

```cron
# Import licences PCE FFCK - Tous les jours à 2h du matin
0 2 * * * /usr/bin/php /var/www/html/commun/cron_maj_licencies.php >> /var/log/kpi/cron_pce.log 2>&1
```

**Fréquence**: Quotidienne (nuit)
**Horaire**: 2h00 (charge serveur faible)
**Durée estimée**: 30-90 secondes

### Logs

**Fichier log**: `/var/www/html/commun/log_cron.txt`

**Format**:
```
2025-10-19 02:00 - MAJ 2547 licenciés (8 req.) : MAJ 342 arbitres (2 req.) : MAJ 45 surclassements (1 req.)
```

**Email notification**: `contact@kayak-polo.info`

### Dépendances

- **Extension PHP**: curl (download fichier)
- **Connexion réseau**: Accès HTTPS extranet FFCK
- **Droits fichier**: Écriture `log_cron.txt`

### Gestion d'erreurs

**Actuellement**: Échec silencieux (pas de retry)

**Améliorations recommandées**:
```php
// Retry logic
$maxRetries = 3;
for ($i = 0; $i < $maxRetries; $i++) {
    if (download_pce()) break;
    sleep(60);
}

// Alerting si échec
if (!success) {
    send_alert_email();
    log_to_monitoring();
}
```

---

## 2. Verrouillage présences compétitions

### Description

Verrouillage/déverrouillage automatique des feuilles de présence selon proximité des compétitions nationales.

### Fichier exécuté

**Chemin**: `/var/www/html/commun/cron_verrou_presences.php`
**Code source**: [sources/commun/cron_verrou_presences.php](sources/commun/cron_verrou_presences.php)

### Fonctionnement

```php
<?php
include_once('../commun/MyBdd.php');
$myBdd = new MyBdd();
$saison = $myBdd->GetActiveSaison();

// VERROUILLAGE (6 jours avant)
// Compétitions N* et CF* dont date_debut dans moins de 6 jours
UPDATE kp_competition SET Verrou = 'O' WHERE ...

// DÉVERROUILLAGE (3 jours après)
// Compétitions terminées depuis moins de 3 jours
UPDATE kp_competition SET Verrou = 'N' WHERE ...

// Log
file_put_contents("log_cron.txt", $msg, FILE_APPEND);
```

### Règles de verrouillage

#### Verrouillage (`Verrou = 'O'`)

**Conditions**:
- Compétition type: `N*` (Nationales) OU `CF*` (Coupe de France)
- Date début: Dans **moins de 6 jours**
- SQL: `DATEDIFF(Date_debut, CURDATE()) < 6`

**Effet**: Empêche modification feuilles présence

#### Déverrouillage (`Verrou = 'N'`)

**Conditions**:
- Compétition type: `N*` OU `CF*`
- Date fin: Depuis **moins de 3 jours**
- SQL: `DATEDIFF(CURDATE(), Date_fin) < 3`

**Effet**: Autorise à nouveau modifications (corrections post-compétition)

### Configuration CRON recommandée

```cron
# Verrouillage présences - Toutes les 6 heures
0 */6 * * * /usr/bin/php /var/www/html/commun/cron_verrou_presences.php >> /var/log/kpi/cron_verrous.log 2>&1
```

**Fréquence**: Toutes les 6 heures (4x/jour)
**Horaires**: 00:00, 06:00, 12:00, 18:00
**Durée estimée**: <5 secondes

### Logs

**Fichier log**: `/var/www/html/commun/log_cron.txt` (partagé avec import PCE)

**Format**:
```
2025-10-19 06:00 - Verrou competitions : "N1M", "N2M", deverrou competitions : "CF2024"
```

---

## Configuration serveur

### À documenter

**Action requise**: Récupérer configuration CRON actuelle du serveur

```bash
# Sur le serveur de production
crontab -l > /tmp/kpi_crontab_backup.txt

# Vérifier utilisateur CRON
whoami

# Vérifier logs système
tail -100 /var/log/cron
```

### Variables d'environnement

Vérifier que PHP CLI utilise la bonne configuration:

```bash
# Version PHP
php -v

# Extensions chargées
php -m | grep -E "curl|pdo|mysql"

# php.ini utilisé
php --ini
```

---

## Monitoring recommandé

### 1. Logs centralisés

**Actuel**: Fichiers plats `log_cron.txt`

**Recommandé**: Logs structurés JSON

```php
// Nouveau format
$log = [
    'timestamp' => date('c'),
    'task' => 'import_pce',
    'status' => 'success',
    'stats' => [
        'licencies' => 2547,
        'arbitres' => 342,
        'surclassements' => 45
    ],
    'duration_seconds' => 87
];

file_put_contents(
    'log_cron.json',
    json_encode($log) . PHP_EOL,
    FILE_APPEND
);
```

### 2. Alerting

**Cas d'alerte**:
- Import PCE échoué (3 jours consécutifs)
- Téléchargement FFCK timeout
- Erreur SQL import
- Aucun licencié importé (fichier vide)

**Channels recommandés**:
- Email (actuel)
- Slack/Discord webhook
- Service monitoring (UptimeRobot, Datadog)

### 3. Health checks

**Endpoint à créer**: `/api/cron/health`

```json
{
  "last_pce_import": "2025-10-19T02:00:15Z",
  "last_pce_success": true,
  "last_pce_stats": {
    "licencies": 2547,
    "arbitres": 342
  },
  "last_verrou_run": "2025-10-19T06:00:03Z",
  "competitions_locked": 3,
  "competitions_unlocked": 1
}
```

---

## Migration vers Command Symfony

### Proposition Phase 2

Lors migration backend Symfony, remplacer scripts PHP par **Commands Symfony**:

```php
// src/Command/ImportPceCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;

class ImportPceCommand extends Command
{
    protected static $defaultName = 'app:import-pce';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('Starting PCE import');

        try {
            $this->pceImporter->import();
            $output->writeln('✅ Import successful');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Import failed', ['exception' => $e]);
            return Command::FAILURE;
        }
    }
}
```

**Avantages**:
- Dependency injection
- Logging intégré
- Tests unitaires
- Retry logic
- Lock files automatiques

**CRON** devient:
```cron
0 2 * * * /var/www/html/bin/console app:import-pce >> /var/log/kpi/cron.log 2>&1
```

---

## Checklist mise en place

### Serveur de production

- [ ] Documenter `crontab -l` actuel
- [ ] Vérifier logs `/var/log/cron`
- [ ] Tester exécution manuelle import PCE
- [ ] Tester exécution manuelle verrous
- [ ] Vérifier permissions fichiers logs
- [ ] Configurer rotation logs (`logrotate`)

### Monitoring

- [ ] Configurer alerting échec import PCE
- [ ] Dashboard stats imports (Grafana/custom)
- [ ] Health check endpoint
- [ ] Backup régulier `log_cron.txt`

### Documentation

- [ ] Procédure intervention échec import
- [ ] Contacts techniques FFCK
- [ ] Procédure réimport manuel complet
- [ ] Timeline critique (J-6, J+3 compétitions)

---

## Procédures d'urgence

### Import PCE échoué

**Symptômes**: Email "[KPI-CRON]" non reçu, log_cron.txt vide

**Actions**:
1. Vérifier accès extranet FFCK
   ```bash
   curl -I https://extranet.ffck.org/reportingExterne/getFichierPce/2025
   ```

2. Exécution manuelle
   ```bash
   cd /var/www/html/commun
   php cron_maj_licencies.php
   ```

3. Vérifier logs
   ```bash
   tail -100 log_cron.txt
   cat pce1.pce | head  # Fichier téléchargé
   ```

4. Réimport si nécessaire
   ```bash
   # Vider tables (ATTENTION!)
   mysql -u user -p kpi_db < scripts/truncate_pce_tables.sql
   php cron_maj_licencies.php
   ```

### Verrouillage incorrect

**Symptômes**: Compétitions verrouillées/déverrouillées à tort

**Actions**:
1. Vérifier `kp_competition.Verrou`
   ```sql
   SELECT Code, Libelle, Verrou, Code_saison
   FROM kp_competition
   WHERE Code_saison = 2025
   AND (Code LIKE 'N%' OR Code LIKE 'CF%');
   ```

2. Correction manuelle si nécessaire
   ```sql
   UPDATE kp_competition
   SET Verrou = 'O'  -- ou 'N'
   WHERE Code = 'N1M' AND Code_saison = 2025;
   ```

3. Vérifier dates journées
   ```sql
   SELECT Id, Code_competition, Date_debut, Date_fin,
          DATEDIFF(Date_debut, CURDATE()) as jours_avant
   FROM kp_journee
   WHERE Code_saison = 2025
   AND Code_competition LIKE 'N%';
   ```

---

**Référence complète**: [AUDIT_PHASE_0.md](AUDIT_PHASE_0.md) - Section 10

**Prochaine action**: Documenter configuration serveur production (`crontab -l`)
