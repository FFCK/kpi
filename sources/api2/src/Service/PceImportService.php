<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PceImportService
{
    private const BATCH_SIZE_LICENCIES = 300;
    private const BATCH_SIZE_ARBITRES = 300;
    private const BATCH_SIZE_SURCLASSEMENTS = 100;

    private string $season = '';
    private array $messages = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $ffckPceUrl,
        private readonly string $ffckPceUser,
        private readonly string $ffckPcePwd
    ) {
    }

    /**
     * Execute the full PCE import process.
     *
     * @return array{
     *   season: string,
     *   nbLicencies: int,
     *   nbArbitres: int,
     *   nbSurclassements: int,
     *   nbReqLicencies: int,
     *   nbReqArbitres: int,
     *   nbReqSurclassements: int,
     *   downloadTime: int,
     *   totalTime: int,
     *   messages: string[]
     * }
     */
    public function importPce(): array
    {
        $this->messages = [];
        $startTime = time();

        // Download PCE file
        $content = $this->downloadPceFile();
        $downloadTime = time() - $startTime;

        // Parse and import
        $stats = $this->parsePceFile($content);

        // Post-import updates
        $this->updateComiteReg($this->season);
        $this->updateComiteDept($this->season);
        $this->updateClub($this->season);
        $this->updateLicencies();

        $totalTime = time() - $startTime;

        $this->messages[] = "Traitement terminé avec succès.";
        $this->messages[] = $totalTime . " secondes (dl=" . $downloadTime . ").";

        $this->logger->info('PCE import completed', [
            'season' => $this->season,
            'licencies' => $stats['nbLicencies'],
            'arbitres' => $stats['nbArbitres'],
            'surclassements' => $stats['nbSurclassements'],
            'duration' => $totalTime,
        ]);

        return [
            'season' => $this->season,
            'nbLicencies' => $stats['nbLicencies'],
            'nbArbitres' => $stats['nbArbitres'],
            'nbSurclassements' => $stats['nbSurclassements'],
            'nbReqLicencies' => $stats['nbReqLicencies'],
            'nbReqArbitres' => $stats['nbReqArbitres'],
            'nbReqSurclassements' => $stats['nbReqSurclassements'],
            'downloadTime' => $downloadTime,
            'totalTime' => $totalTime,
            'messages' => $this->messages,
        ];
    }

    private function downloadPceFile(): string
    {
        $url = rtrim($this->ffckPceUrl, '/') . '/' . date('Y');

        $response = $this->httpClient->request('GET', $url, [
            'auth_basic' => [$this->ffckPceUser, $this->ffckPcePwd],
            'timeout' => 120,
        ]);

        $content = $response->getContent();

        if (empty($content)) {
            throw new \RuntimeException('Le fichier PCE téléchargé est vide');
        }

        $this->messages[] = "Importation du fichier PCE";

        return $content;
    }

    /**
     * @return array{nbLicencies: int, nbArbitres: int, nbSurclassements: int, nbReqLicencies: int, nbReqArbitres: int, nbReqSurclassements: int}
     */
    private function parsePceFile(string $content): array
    {
        $section = '';
        $nbLicencies = 0;
        $nbArbitres = 0;
        $nbSurclassements = 0;
        $countLicencies = 0;
        $arrayLicencies = [];
        $countArbitres = 0;
        $arrayArbitres = [];
        $countSurclassements = 0;
        $arraySurclassements = [];
        $nbReq1 = 0;
        $nbReq2 = 0;
        $nbReq3 = 0;

        $lines = explode("\n", $content);

        foreach ($lines as $buffer) {
            $buffer = trim($buffer);
            if (strlen($buffer) === 0) {
                continue;
            }

            if ($buffer[0] === '[') {
                $section = substr($buffer, 1, strlen($buffer) - 2);
                continue;
            }

            if (strcasecmp($section, 'date_valeur') === 0) {
                $this->processDateValeur($buffer);
                continue;
            }

            if (strcasecmp($section, 'licencies') === 0) {
                $temp = $this->parseLicencieLine($buffer);
                if ($temp === null) {
                    continue;
                }
                $nbLicencies++;
                $countLicencies++;
                $arrayLicencies = array_merge($arrayLicencies, $temp);

                if ($countLicencies === self::BATCH_SIZE_LICENCIES) {
                    $this->insertLicenciesBatch($countLicencies, $arrayLicencies);
                    $nbReq1++;
                    $arrayLicencies = [];
                    $countLicencies = 0;
                }
                continue;
            }

            if (strcasecmp($section, 'juges_kap') === 0) {
                $temp = $this->parseJugeLine($buffer);
                if ($temp === null) {
                    continue;
                }
                $nbArbitres++;
                $countArbitres++;
                $arrayArbitres = array_merge($arrayArbitres, $temp);

                if ($nbArbitres === 1 && $nbReq2 === 0) {
                    $this->truncateJuges();
                }
                if ($countArbitres === self::BATCH_SIZE_ARBITRES) {
                    $this->insertJugesBatch($countArbitres, $arrayArbitres);
                    $nbReq2++;
                    $arrayArbitres = [];
                    $countArbitres = 0;
                }
                continue;
            }

            if (strcasecmp($section, 'surclassements') === 0) {
                $temp = $this->parseSurclassementLine($buffer);
                $nbSurclassements++;
                if ($temp !== null) {
                    $countSurclassements++;
                    $arraySurclassements = array_merge($arraySurclassements, $temp);
                }
                if ($countSurclassements === self::BATCH_SIZE_SURCLASSEMENTS) {
                    $this->insertSurclassementsBatch($countSurclassements, $arraySurclassements);
                    $nbReq3++;
                    $arraySurclassements = [];
                    $countSurclassements = 0;
                }
                continue;
            }
        }

        // Flush remaining batches
        if ($countLicencies > 0) {
            $this->insertLicenciesBatch($countLicencies, $arrayLicencies);
            $nbReq1++;
        }
        if ($countArbitres > 0) {
            $this->insertJugesBatch($countArbitres, $arrayArbitres);
            $nbReq2++;
        }
        if ($countSurclassements > 0) {
            $this->insertSurclassementsBatch($countSurclassements, $arraySurclassements);
            $nbReq3++;
        }

        $this->messages[] = "MAJ " . $nbLicencies . " licenciés (" . $nbReq1 . " req.)...";
        $this->messages[] = "MAJ " . $nbArbitres . " arbitres (" . $nbReq2 . " req.)...";
        $this->messages[] = "MAJ " . $nbSurclassements . " surclassements (" . $nbReq3 . " req.)...";

        return [
            'nbLicencies' => $nbLicencies,
            'nbArbitres' => $nbArbitres,
            'nbSurclassements' => $nbSurclassements,
            'nbReqLicencies' => $nbReq1,
            'nbReqArbitres' => $nbReq2,
            'nbReqSurclassements' => $nbReq3,
        ];
    }

    private function processDateValeur(string $buffer): void
    {
        $this->season = $this->resolveSeason($buffer);
        $this->messages[] = "Date du Fichier : " . $buffer . " => Saison active : " . $this->season;
    }

    private function resolveSeason(string $date): string
    {
        $sql = "SELECT Code FROM kp_saison WHERE Nat_debut <= ? AND Nat_fin >= ?";
        $result = $this->connection->fetchOne($sql, [$date, $date]);

        if ($result !== false) {
            return (string) $result;
        }

        return substr($date, 0, 4);
    }

    /**
     * @return array|null 22-element flat array for batch insert, or null on error
     */
    private function parseLicencieLine(string $buffer): ?array
    {
        $replaceSearch = ['CANOE KAYAK', 'CANOE-KAYAK', 'C.K.'];
        $arrayToken = explode(';', $buffer);
        $nbToken = count($arrayToken);

        if ($nbToken < 17) {
            $this->messages[] = "Erreur [licencies] : " . $buffer . " - token = " . $nbToken;
            return null;
        }

        $matric = $arrayToken[0];
        $origine = $this->season;
        $nom = $arrayToken[1];
        $prenom = $arrayToken[2];
        $sexe = $arrayToken[3];
        $naissance = $arrayToken[4];

        $club = str_replace($replaceSearch, 'CK', $arrayToken[5]);
        $numClub = $this->convertirCodeClub($arrayToken[6]);
        $comiteDept = str_replace($replaceSearch, 'CK', $arrayToken[7]);
        $numComiteDept = $this->convertirDepartement($arrayToken[8]);
        $comiteReg = str_replace($replaceSearch, 'CK', $arrayToken[9]);
        $numComiteReg = $arrayToken[10];

        $etat = $arrayToken[11];
        $pagaieEvi = $arrayToken[12];
        $pagaieMer = $arrayToken[13];
        $pagaieEca = $arrayToken[14];
        $etatCertificatAps = $arrayToken[15];
        $etatCertificatCk = $arrayToken[16];
        $typeLicence = isset($arrayToken[17]) ? trim($arrayToken[17]) : null;

        return [
            $matric, $origine, $nom, $prenom, $sexe, $naissance, $club, $numClub,
            $comiteDept, $numComiteDept, $comiteReg, $numComiteReg, $etat,
            $pagaieEvi, $pagaieMer, $pagaieEca, null, null, null,
            $etatCertificatAps, $etatCertificatCk, $typeLicence,
        ];
    }

    private function insertLicenciesBatch(int $count, array $params): void
    {
        $placeholders = implode(',', array_fill(0, $count, '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'));

        $sql = "INSERT INTO kp_licence
            (Matric, Origine, Nom, Prenom, Sexe, Naissance, Club, Numero_club,
            Comite_dept, Numero_comite_dept, Comite_reg, Numero_comite_reg, Etat,
            Pagaie_EVI, Pagaie_MER, Pagaie_ECA, Date_certificat_CK, Date_certificat_APS,
            Reserve, Etat_certificat_APS, Etat_certificat_CK, Type_licence)
            VALUES $placeholders
            ON DUPLICATE KEY UPDATE
                Matric = VALUES(Matric), Origine = VALUES(Origine), Nom = VALUES(Nom),
                Prenom = VALUES(Prenom), Sexe = VALUES(Sexe), Naissance = VALUES(Naissance),
                Club = VALUES(Club), Numero_club = VALUES(Numero_club), Comite_dept = VALUES(Comite_dept),
                Numero_comite_dept = VALUES(Numero_comite_dept), Comite_reg = VALUES(Comite_reg), Numero_comite_reg = VALUES(Numero_comite_reg),
                Etat = VALUES(Etat), Pagaie_EVI = VALUES(Pagaie_EVI), Pagaie_MER = VALUES(Pagaie_MER),
                Pagaie_ECA = VALUES(Pagaie_ECA), Date_certificat_CK = VALUES(Date_certificat_CK), Date_certificat_APS = VALUES(Date_certificat_APS),
                Reserve = VALUES(Reserve), Etat_certificat_CK = VALUES(Etat_certificat_CK), Etat_certificat_APS = VALUES(Etat_certificat_APS),
                Type_licence = VALUES(Type_licence)";

        $this->connection->executeStatement($sql, $params);
    }

    /**
     * @return array|null 9-element flat array, or null on error
     */
    private function parseJugeLine(string $buffer): ?array
    {
        $arrayToken = explode(';', $buffer);
        $nbToken = count($arrayToken);

        if ($nbToken !== 8) {
            $this->messages[] = "Erreur [juges_pol] : " . $buffer;
            return null;
        }

        $matric = $arrayToken[0];
        $livret = $arrayToken[7];
        $niveau = '';
        $saisonJuge = $this->season;

        $regional = 'N';
        $interregional = 'N';
        $national = 'N';
        $international = 'N';
        $arb = '';

        // New format: YYYY-KAP-...
        if (preg_match('/^(\d{4})-KAP-(.+)$/', $livret, $matches)) {
            $saisonJuge = $matches[1];
            $parts = explode('-', $matches[2]);

            if ($parts[0] === 'A') {
                // Arbitre
                if (isset($parts[1])) {
                    $levelPrefix = substr($parts[1], 0, 3);
                    if ($levelPrefix === 'REG') {
                        $arb = 'Reg';
                        $regional = 'O';
                        if (isset($parts[2]) && $parts[2] === 'S') {
                            $niveau = 'S';
                        }
                    } elseif ($levelPrefix === 'NAT') {
                        $arb = 'Nat';
                        $national = 'O';
                        if (strlen($parts[1]) === 4) {
                            $niveau = substr($parts[1], 3, 1); // NATA, NATB, NATC
                        } elseif (isset($parts[2]) && $parts[2] === 'S') {
                            $niveau = 'S';
                        }
                    } elseif ($levelPrefix === 'INT') {
                        $arb = 'Int';
                        $international = 'O';
                        $niveau = '';
                    }
                }
            } elseif ($parts[0] === 'OTM') {
                $arb = 'OTM';
                if (isset($parts[1]) && $parts[1] === 'S') {
                    $niveau = 'S';
                }
            } elseif ($parts[0] === 'JO') {
                $arb = 'JO';
            }
        } else {
            // Legacy format
            $niveau = substr($livret, -1);
            if ($niveau !== 'A' && $niveau !== 'B' && $niveau !== 'C') {
                $niveau = '';
            }
            if (strlen($arrayToken[3]) > 0) {
                $regional = substr($arrayToken[3], 0, 1);
                if ($regional === 'O') {
                    $arb = 'Reg';
                }
            }
            if (strrpos($livret, 'JREG') !== false) {
                $niveau = 'S';
            }
            if (strlen($arrayToken[4]) > 0) {
                $interregional = substr($arrayToken[4], 0, 1);
                if ($interregional === 'O') {
                    $arb = 'IR';
                }
            }
            if (strlen($arrayToken[5]) > 0) {
                $national = substr($arrayToken[5], 0, 1);
                if ($national === 'O') {
                    $arb = 'Nat';
                }
            }
            if (strrpos($livret, 'JNAT') !== false) {
                $niveau = 'S';
            }
            if (strlen($arrayToken[6]) > 0) {
                $international = substr($arrayToken[6], 0, 1);
                if ($international === 'O') {
                    $arb = 'Int';
                    $niveau = '';
                }
            }
            if (strrpos($livret, 'OTM') !== false) {
                $arb = 'OTM';
            }
            if (strrpos($livret, 'OTMS') !== false) {
                $niveau = 'S';
            }
            if (strrpos($livret, 'JO') !== false) {
                $arb = 'JO';
            }
        }

        return [
            $matric, $regional, $interregional, $national,
            $international, $arb, $livret, $niveau, $saisonJuge,
        ];
    }

    private function truncateJuges(): void
    {
        $this->connection->executeStatement("DELETE FROM kp_arbitre WHERE Matric < 2000000");
    }

    private function insertJugesBatch(int $count, array $params): void
    {
        $placeholders = implode(',', array_fill(0, $count, '(?,?,?,?,?,?,?,?,?)'));

        $sql = "INSERT INTO kp_arbitre
            VALUES $placeholders
            ON DUPLICATE KEY UPDATE
                Matric = VALUES(Matric), regional = VALUES(regional), interregional = VALUES(interregional),
                national = VALUES(national), international = VALUES(international), arbitre = VALUES(arbitre),
                livret = VALUES(livret), niveau = VALUES(niveau), saison = VALUES(saison)";

        $this->connection->executeStatement($sql, $params);
    }

    /**
     * @return array|null 4-element flat array, or null if not KAP discipline
     */
    private function parseSurclassementLine(string $buffer): ?array
    {
        $arrayToken = explode(';', $buffer);
        $nbToken = count($arrayToken);

        if ($nbToken !== 6) {
            $this->messages[] = "Erreur [surclassements] : " . $buffer;
            return null;
        }

        $discipline = $arrayToken[3];
        if ($discipline !== 'KAP') {
            return null;
        }

        $matric = $arrayToken[0];
        $categorie = $arrayToken[4];
        $dateSurclassement = $this->dateFrToSql($arrayToken[5]);

        return [$matric, $this->season, $categorie, $dateSurclassement];
    }

    private function insertSurclassementsBatch(int $count, array $params): void
    {
        $placeholders = implode(',', array_fill(0, $count, '(?,?,?,?)'));

        $sql = "INSERT INTO kp_surclassement
            VALUES $placeholders
            ON DUPLICATE KEY UPDATE
                Matric = VALUES(Matric), Saison = VALUES(Saison), Cat = VALUES(Cat),
                `Date` = VALUES(`Date`)";

        $this->connection->executeStatement($sql, $params);
    }

    private function updateComiteReg(string $season): void
    {
        $this->messages[] = "Mise à jour des Comités Régionaux ...";
        $sql = "INSERT IGNORE INTO kp_cr (Code, Libelle, Officiel, Reserve)
            SELECT DISTINCT lc.Numero_comite_reg,
                REPLACE(REPLACE(lc.Comite_reg, 'COMITE REGIONAL', 'CR'), 'CANOE KAYAK', 'CK'),
                'O', ''
            FROM kp_licence lc
            WHERE lc.Numero_club NOT IN ('0', '0000')
            AND lc.Numero_comite_reg <> '98'
            AND lc.Origine = ?
            AND lc.Comite_reg <> ''";
        $this->connection->executeStatement($sql, [$season]);
    }

    private function updateComiteDept(string $season): void
    {
        $this->messages[] = "Mise à jour des Comités Départementaux ...";
        $sql = "INSERT IGNORE INTO kp_cd (Code, Libelle, Officiel, Reserve, Code_comite_reg)
            SELECT DISTINCT lc.Numero_comite_dept,
                REPLACE(REPLACE(lc.Comite_dept, 'COMITE DEPARTEMENTAL', 'CD'), 'CANOE KAYAK', 'CK'),
                'O', '', lc.Numero_comite_reg
            FROM kp_licence lc
            WHERE lc.Numero_club NOT IN ('0', '0000')
            AND lc.Numero_comite_reg <> '98'
            AND lc.Origine = ?
            AND lc.Comite_dept <> ''";
        $this->connection->executeStatement($sql, [$season]);
    }

    private function updateClub(string $season): void
    {
        $this->messages[] = "Mise à jour des Clubs ...";
        $sql = "INSERT IGNORE INTO kp_club (Code, Libelle, Officiel, Reserve, Code_comite_dep)
            SELECT DISTINCT lc.Numero_club,
                lc.Club,
                'O', '', MIN(lc.Numero_comite_dept)
            FROM kp_licence lc
            WHERE lc.Numero_club NOT IN ('0', '0000')
            AND lc.Numero_comite_reg <> '98'
            AND lc.Origine = ?
            AND lc.Club != ''";
        $this->connection->executeStatement($sql, [$season]);
    }

    private function updateLicencies(): void
    {
        $this->messages[] = "Traitement final base des licenciés ...";
        // Gender normalization
        $this->connection->executeStatement("UPDATE kp_licence SET Sexe = 'M' WHERE Sexe = 'H'");
        $this->connection->executeStatement("UPDATE kp_licence SET Sexe = 'F' WHERE Sexe = 'D'");

        // Clear club/dept/region names
        $this->connection->executeStatement("UPDATE kp_licence SET Club = '', Comite_dept = '', Comite_reg = '' WHERE 1");
    }

    private function convertirCodeClub(string $code): string
    {
        $dept = substr($code, 0, 3);
        $club = substr($code, 3);

        // DOM-TOM
        $domTom = [
            '971' => '9A',
            '972' => '9B',
            '973' => '9C',
            '974' => '9D',
            '976' => '9F',
            '988' => '9G',
        ];

        if (array_key_exists($dept, $domTom)) {
            return $domTom[$dept] . str_pad(ltrim($club, '0'), 2, '0', STR_PAD_LEFT);
        }

        // Corse
        if ($dept === '02A' || $dept === '02B') {
            return substr($dept, 1) . str_pad(ltrim($club, '0'), 2, '0', STR_PAD_LEFT);
        }

        // Other departments
        $dept = ltrim($dept, '0');
        if (strlen($dept) === 1) {
            $dept = '0' . $dept;
        }

        return $dept . str_pad(ltrim($club, '0'), 2, '0', STR_PAD_LEFT);
    }

    private function convertirDepartement(string $code): string
    {
        $code = substr($code, 2); // Remove first two chars (CD)

        $conversions = [
            '971' => '9A00',
            '972' => '9B00',
            '973' => '9C00',
            '974' => '9D00',
            '988' => '9G00',
        ];

        if (array_key_exists($code, $conversions)) {
            return $conversions[$code];
        }

        $code = substr($code, 1); // Remove leading digit

        if (strlen($code) === 2) {
            return $code . '00';
        } elseif (strlen($code) === 1) {
            return '0' . $code . '00';
        }

        return $code . '00';
    }

    /**
     * Convert French date (DD/MM/YYYY) to SQL date (YYYY-MM-DD).
     */
    private function dateFrToSql(string $dateFr, string $separator = '/'): string
    {
        $data = explode($separator, $dateFr);
        if (count($data) === 3) {
            return $data[2] . '-' . $data[1] . '-' . $data[0];
        }

        return $dateFr;
    }
}
