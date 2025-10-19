<?php

// Connexion à la base de données , Importation des données PCE 

include_once('MyConfig.php');
include_once('MyParams.php');

define("BUFFER_LENGTH", 2048); 		// Taille du Buffer pour la lecture d'une ligne d'un fichier PCE

class MyBdd
{
	var $m_login;
	var $m_password;
	var $m_database;
	var $m_server;
	var $m_link;
	var $pdo;
	var $m_arrayinfo;

	var $m_saisonPCE;

	// Constructeur 
	function __construct($mirror = false)
	{
		if (PRODUCTION) {
			if (isset($_SESSION['mirror'])) {
				if ($_SESSION['mirror'] == '1') {
					$mirror = true;
				}
			}

			/* Parametres Hebergement phpnet.org */
			if ($mirror) {
				$this->m_login = PARAM_MIRROR_LOGIN;
				$this->m_password = PARAM_MIRROR_PASSWORD;
				$this->m_database = PARAM_MIRROR_DB;
				$this->m_server = PARAM_MIRROR_SERVER;
			} else {
				$this->m_login = PARAM_PROD_LOGIN;
				$this->m_password = PARAM_PROD_PASSWORD;
				$this->m_database = PARAM_PROD_DB;
				$this->m_server = PARAM_PROD_SERVER;
			}
		} else {
			/* Parametres Localhost wamp et dev */
			$this->m_login = PARAM_LOCAL_LOGIN;
			$this->m_password = PARAM_LOCAL_PASSWORD;
			$this->m_database = PARAM_LOCAL_DB;
			$this->m_server = PARAM_LOCAL_SERVER;
		}

		$this->m_arrayinfo = array();
		$this->PDO();

		// $this->Connect();
	}

	/**
	 * Connexion PDO
	 */
	function PDO()
	{
		try {
			$this->pdo = new PDO('mysql:host=' . $this->m_server . ';dbname=' . $this->m_database, $this->m_login, $this->m_password);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->pdo->exec("SET NAMES utf8;");
			$this->pdo->exec("SET @@SESSION.sql_mode='';");
			// $this->pdo->exec("SET @@SESSION.sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");
			// error_log("Connexion PDO", 0);
		} catch (PDOException $e) {
			die('Une erreur MySQL est arrivée: ' . $e->getMessage());
		}
	}

	// ShowColumnsSQL
	function ShowColumnsSQL($tableName, &$arrayColumns)
	{
		if ($arrayColumns == null) {
			$arrayColumns = array();
		}
		$sql = "SHOW COLUMNS FROM ? ";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($tableName));
	
		while ($row = $stmt->fetch()) {
			array_push($arrayColumns, $row);
		}
		return $stmt->rowCount();
	}

	// GetIndexColumn
	function GetIndexColumn($tableName, $columnName)
	{
		$arrayColumns = array();
		$this->ShowColumnsSQL($tableName, $arrayColumns);
		return $this->GetIndexColumnByArray($columnName, $arrayColumns);
	}

	function GetIndexColumnByArray($columnName, &$arrayColumns)
	{
		for ($i = 0; $i < count($arrayColumns); $i++) {
			if ($arrayColumns[$i]['Field'] == $columnName)
				return $i;
		}
		return -1;
	}

	// IsNullSQL
	function IsNullSQL($value)
	{
		$typeValue = gettype($value);
		if ($typeValue == null) return true;
		if (($typeValue == 'string') && ($value == '')) return true;

		return false;
	}

	// ValueSQL
	function ValueSQL($value, $type, $null)
	{
		if ($this->IsNullSQL($value)) {
			if ($null == 'YES')
				return 'null';
			else
				return "''";
		}

		$pos = strpos($type, 'int');
		if (($pos !== false) && ($pos == 0)) return $value;

		$pos = strpos($type, 'float');
		if (($pos !== false) && ($pos == 0)) return $value;

		$pos = strpos($type, 'double');
		if (($pos !== false) && ($pos == 0)) return $value;

		// On force le casting en string ...
		settype($value, "string");
		return "'" . utyStringQuote($value) . "'";
	}

	// SetSQL
	function SetSQL($tableName, &$record, $bIgnoreNull = true, &$arrayColumns = null)
	{
		if ($arrayColumns == null) {
			$arrayColumns = array();
			$this->ShowColumnsSQL($tableName, $arrayColumns);
		}

		$sql = '';

		// Uniquement les colonnes presentes dans $record et $arrayColumns ...
		$count = 0;
		foreach ($record as $key => $value) {
			if ($bIgnoreNull && $this->IsNullSQL($value))
				continue;

			for ($j = 0; $j < count($arrayColumns); $j++) {
				if ($arrayColumns[$j]['Field'] == $key) {
					if ($count == 0)
						$sql .= 'Set ';
					else
						$sql .= ',';
					++$count;

					$sql .= $key . '=';
					$sql .= $this->ValueSQL($value, $arrayColumns[$j]['Type'], $arrayColumns[$j]['Null']);

					break;
				}
			}
		}

		return $sql;
	}

	// InsertSQL
	function InsertSQL($tableName, &$record, $bIgnoreNull = true, &$arrayColumns = null)
	{
		return "Insert Into $tableName " . $this->SetSQL($tableName, $record, $bIgnoreNull, $arrayColumns);
	}

	// UpdateSQL
	function UpdateSQL($tableName, &$record, $bIgnoreNull = false, &$arrayColumns = null)
	{
		return "Update $tableName " . $this->SetSQL($tableName, $record, $bIgnoreNull, $arrayColumns);
	}

	// ReplaceSQL
	function ReplaceSQL($tableName, &$record, $bIgnoreNull = false, &$arrayColumns = null)
	{
		return "Replace Into $tableName " . $this->SetSQL($tableName, $record, $bIgnoreNull, $arrayColumns);
	}

	// InsertBlocSQL
	function InsertBlocSQL($tableName, &$tData, &$sql)
	{
		$sql = '';
		$nbData = count($tData);
		if ($nbData == 0) return;

		$arrayColumns = array();
		$this->ShowColumnsSQL($tableName, $arrayColumns);
		$nbColumns = count($arrayColumns);

		$sql .= "Insert Into $tableName (";
		for ($j = 0; $j < $nbColumns; $j++) {
			if ($j > 0) $sql .= ',';
			$sql .= $arrayColumns[$j]['Field'];
		}
		$sql .= ') Values ';

		for ($i = 0; $i < $nbData; $i++) {
			$record = &$tData[$i];

			if ($i > 0) $sql .= ',';
			$sql .= '(';
			for ($j = 0; $j < $nbColumns; $j++) {
				if ($j > 0) $sql .= ',';

				$key = $arrayColumns[$j]['Field'];
				if (isset($record[$key]))
					$sql .= $this->ValueSQL($record[$key], $arrayColumns[$j]['Type'], $arrayColumns[$j]['Null']);
				else
					//	$sql .= $this->ValueSQL('', $arrayColumns[$j]['Type'], $arrayColumns[$j]['Null']);
					$sql .= 'null';
			}
			$sql .= ')';
		}
	}

	// ColumnsSQL
	function ColumnsSQL($tableName, &$arrayColumns = null)
	{
		if ($arrayColumns == null) {
			$arrayColumns = array();
			$this->ShowColumnsSQL($tableName, $arrayColumns);
		}

		$sql = '';
		for ($j = 0; $j < count($arrayColumns); $j++) {
			if ($sql != '')
				$sql .= ',';

			$sql .= $arrayColumns[$j]['Field'];
		}
		return $sql;
	}

	// ColumnsRecordSQL
	function ColumnsRecordSQL(&$record, $bIgnoreNull = false)
	{
		$sql = '';
		foreach ($record as $key => $value) {
			if ($bIgnoreNull && $this->IsNullSQL($value))
				continue;

			if ($sql != '')
				$sql .= ',';

			$sql .= $key;
		}

		return $sql;
	}

	// LoadTable
	function LoadTable($sql, &$arrayLoad)
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		
		$arrayLoad = array();
		while ($row = $stmt->fetch()) {
			array_push($arrayLoad, $row);
		}
	}

	// LoadRecord
	function LoadRecord($sql, &$record)
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		if ($stmt->rowCount() >= 1) {
			$record = $stmt->fetch(PDO::FETCH_ASSOC);
		} else {
			$record = array();
		}
	}

	// FIN AJOUT COSANDCO : 12 Septembre 2014 ...












	// Importation du fichier PCE Nouvelle formule
	function ImportPCE2()
	{
		$debutTraitement = time();
		$section = "";
		$nbLicencies = 0;
		$nbArbitres = 0;
		$nbSurclassements = 0;
		$count_licencies = 0;
		$array_licencies = array();
		$count_arbitres = 0;
		$array_arbitres = array();
		$count_surclassements = 0;
		$array_surclassements = array();
		$nbReq1 = 0;
		$nbReq2 = 0;
		$nbReq3 = 0;

		$url = "https://extranet.ffck.org/reportingExterne/getFichierPce/" . date('Y');
		$newfile = "pce1.pce";

		if (!$header = get_web_page($url)) {
			array_push($this->m_arrayinfo, "Ouverture impossible du fichier distant");
		}
		if (!file_put_contents($newfile, $header['content'])) {
			array_push($this->m_arrayinfo, "Ecriture impossible du fichier local");
		}

		$tempsIntermediaire = time() - $debutTraitement;

		$fp = fopen($newfile, "r");
		if (!$fp) {
			array_push($this->m_arrayinfo, "Ouverture impossible du fichier distant");
		}

		array_push($this->m_arrayinfo, "Importation du fichier PCE");

		while (!feof($fp)) {
			$buffer = trim(fgets($fp, BUFFER_LENGTH));
			if (strlen($buffer) == 0)
				continue;

			if ($buffer[0] == '[') {
				// Prise de la section ...
				$section = substr($buffer, 1, strlen($buffer) - 2);
				continue;
			}

			if (strcasecmp($section, "date_valeur") == 0) {
				$this->ImportPCE_DateValeur($buffer);
				continue;
			}

			if (strcasecmp($section, "licencies") == 0) {
				$temp = $this->ImportPCE_Licencies($buffer);
				$nbLicencies++;
				$count_licencies++;
				$array_licencies = array_merge($array_licencies, $temp);

				if ($count_licencies == 300) { // une requête pour 300 MAJ
					$this->ImportPCE_Query_Licencies($count_licencies, $array_licencies);
					$nbReq1++;
					$array_licencies = [];
					$count_licencies = 0;
				}
				continue;
			}

			if (strcasecmp($section, "juges_kap") == 0) {
				$temp = $this->ImportPCE_Juges($buffer);
				// $temp = $this->ImportPCE_Juges_2($buffer);
				$nbArbitres++;
				$count_arbitres++;
				$array_arbitres = array_merge($array_arbitres, $temp);
				if ($nbArbitres == 1 && $nbReq2 == 0) {
					$this->ImportPCE_Truncate_Juges();
				}
				if ($count_arbitres == 300) { // une requête pour 300 MAJ
					$this->ImportPCE_Query_Juges($count_arbitres, $array_arbitres);
					$nbReq2++;
					$array_arbitres = [];
					$count_arbitres = 0;
				}
				continue;
			}

			if (strcasecmp($section, "surclassements") == 0) {
				$temp = $this->ImportPCE_Surclassements($buffer);
				$nbSurclassements++;
				if ($temp) {
					$count_surclassements++;
					$array_surclassements = array_merge($array_surclassements, $temp);
				}
				if ($count_surclassements == 100) { // une requête pour 100 MAJ
					$this->ImportPCE_Query_Surclassements($count_surclassements, $array_surclassements);
					$nbReq3++;
					$array_surclassements = [];
					$count_surclassements = 0;
				}
				continue;
			}
		}

		// une requête pour les dernières MAJ
		if ($count_licencies > 0) {
			$this->ImportPCE_Query_Licencies($count_licencies, $array_licencies);
			$nbReq1++;
		}
		if ($count_arbitres > 0) {
			$this->ImportPCE_Query_Juges($count_arbitres, $array_arbitres);
			$nbReq2++;
		}
		if ($count_surclassements > 0) {
			$this->ImportPCE_Query_Surclassements($count_surclassements, $array_surclassements);
			$nbReq3++;
		}


		fclose($fp);
		array_push($this->m_arrayinfo, "MAJ " . $nbLicencies . " licenciés (" . $nbReq1 . " req.)...");
		array_push($this->m_arrayinfo, "MAJ " . $nbArbitres . " arbitres (" . $nbReq2 . " req.)...");
		array_push($this->m_arrayinfo, "MAJ " . $nbSurclassements . " surclassements (" . $nbReq3 . " req.)...");
		// Lancement des mises à jour ...
		$this->ImportPCE_MajComiteReg();
		$this->ImportPCE_MajComiteDept();
		$this->ImportPCE_MajClub();
		$this->ImportPCE_MajLicencies();
		// Fin du traitement
		array_push($this->m_arrayinfo, "");
		array_push($this->m_arrayinfo, "Traitement terminé avec succès.");
		$secondes = time() - $debutTraitement;
		array_push($this->m_arrayinfo, $secondes . " secondes (dl=" . $tempsIntermediaire . ").");
	}

	// Importation de la section [date_valeur] du fichier PCE 
	function ImportPCE_DateValeur($buffer)
	{
		$this->m_saisonPCE = $this->GetSaisonNational($buffer);

		array_push($this->m_arrayinfo, "Date du Fichier : " . $buffer . " => Saison active : " . $this->m_saisonPCE);
	}

	// Importation de la section [licencies] du fichier PCE 
	function ImportPCE_Licencies($buffer)
	{
		$replace_search = array('CANOE KAYAK', 'CANOE-KAYAK', 'C.K.');
		$arrayToken = explode(";", $buffer);
		$nbToken = count($arrayToken);

		if ($nbToken < 17) {
			array_push($this->m_arrayinfo, "Erreur [licencies] : " . $buffer . " - token = " . $nbToken);
			return;
		}

		$matric =  $arrayToken[0];
		$origine = $this->m_saisonPCE;
		$nom = $arrayToken[1];
		$prenom = $arrayToken[2];
		$sexe = $arrayToken[3];
		$naissance = $arrayToken[4];

		$club = str_replace($replace_search, 'CK', $arrayToken[5]);
		$num_club = $this->convertirCodeClub($arrayToken[6]);
		$comite_dept = str_replace($replace_search, 'CK', $arrayToken[7]);
		$num_comite_dept = $this->convertirDepartement($arrayToken[8]);
		$comite_reg = str_replace($replace_search, 'CK', $arrayToken[9]);
		$num_comite_reg = $arrayToken[10];

		$etat = $arrayToken[11];
		$pagaie_evi = $arrayToken[12];
		$pagaie_mer = $arrayToken[13];
		$pagaie_eca = $arrayToken[14];
		$etat_certificat_aps = $arrayToken[15];
		$etat_certificat_ck = $arrayToken[16];

		return array(
			$matric, $origine, $nom, $prenom, $sexe, $naissance, $club, $num_club,
			$comite_dept, $num_comite_dept, $comite_reg, $num_comite_reg, $etat,
			$pagaie_evi, $pagaie_mer, $pagaie_eca, null, null, null,
			$etat_certificat_aps, $etat_certificat_ck
		);
	}

	function convertirCodeClub($code) {
		// Extraire les 3 premiers chiffres (département) et les 3 derniers (club)
		$dept = substr($code, 0, 3);
		$club = substr($code, 3);
	
		// Conversion spéciale pour les DOM-TOM
		$domTom = [
			'971' => '9A',
			'972' => '9B',
			'973' => '9C',
			'974' => '9D',
			'976' => '9F',
			'988' => '9G'
		];
	
		if (array_key_exists($dept, $domTom)) {
			return $domTom[$dept] . str_pad(ltrim($club, '0'), 2, '0', STR_PAD_LEFT);
		}
	
		// Pour la Corse
		if ($dept == '02A' || $dept == '02B') {
			return substr($dept, 1) . str_pad(ltrim($club, '0'), 2, '0', STR_PAD_LEFT);
		}
	
		// Pour les autres départements
		$dept = ltrim($dept, '0'); // Supprime les zéros au début
		if (strlen($dept) == 1) {
			$dept = '0' . $dept; // Ajoute un zéro si le département n'a qu'un chiffre
		}
	
		return $dept . str_pad(ltrim($club, '0'), 2, '0', STR_PAD_LEFT);
	}

	function convertirDepartement($code) {
		$code = substr($code, 2); // Supprime les deux premiers caractères (CD)
		
		$conversions = [
			'971' => '9A00',
			'972' => '9B00',
			'973' => '9C00',
			'974' => '9D00',
			'988' => '9G00'
		];
		
		if (array_key_exists($code, $conversions)) {
			return $conversions[$code];
		}

		$code = substr($code, 1); // Supprime les deux premiers caractères (CD)
		
		if (strlen($code) == 2) {
			if (ctype_digit($code)) {
				return $code . '00';
			} elseif (ctype_digit(substr($code, 1, 1))) {
				return $code . '00';
			}
		} elseif (strlen($code) == 1) {
			return '0' . $code . '00';
		}
		
		return $code . '00';
	}
	

	function ImportPCE_Query_Licencies($count_licencies, $array_licencies)
	{
		$placeholders = '';
		for ($i = 0; $i < $count_licencies; $i++) {
			if ($placeholders != '') {
				$placeholders .= ',';
			}
			$placeholders .= '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
		}
		$sql_licencies = "INSERT INTO kp_licence 
			VALUES $placeholders 
			ON DUPLICATE KEY UPDATE 
				Matric = VALUES(Matric), Origine = VALUES(Origine), Nom = VALUES(Nom), 
				Prenom = VALUES(Prenom), Sexe = VALUES(Sexe), Naissance = VALUES(Naissance), 
				Club = VALUES(Club), Numero_club = VALUES(Numero_club), Comite_dept = VALUES(Comite_dept), 
				Numero_comite_dept = VALUES(Numero_comite_dept), Comite_reg = VALUES(Comite_reg), Numero_comite_reg = VALUES(Numero_comite_reg), 
				Etat = VALUES(Etat), Pagaie_EVI = VALUES(Pagaie_EVI), Pagaie_MER = VALUES(Pagaie_MER), 
				Pagaie_ECA = VALUES(Pagaie_ECA), Date_certificat_CK = VALUES(Date_certificat_CK), Date_certificat_APS = VALUES(Date_certificat_APS), 
				Reserve = VALUES(Reserve), Etat_certificat_CK = VALUES(Etat_certificat_CK), Etat_certificat_APS = VALUES(Etat_certificat_APS) 
				";
		$stmt = $this->pdo->prepare($sql_licencies);
		$stmt->execute($array_licencies) or array_push($this->m_arrayinfo, "Erreur SQL " . $stmt->errorInfo());
}


	// Importation de la section [juges_pol] du fichier PCE 
	function ImportPCE_Juges($buffer)
	{
		$arrayToken = explode(";", $buffer);
		$nbToken = count($arrayToken);

		if ($nbToken != 8) {
			array_push($this->m_arrayinfo, "Erreur [juges_pol] : " . $buffer);
			return;
		}

		$matric =  $arrayToken[0];
		$livret = $arrayToken[7];
		$niveau = '';
		$saisonJuge = $this->m_saisonPCE;

		$regional = 'N';
		$interregional = 'N';
		$national = 'N';
		$international = 'N';
		$Arb = '';

		// Nouveau format livret : YYYY-KAP-...
		if (preg_match('/^(\d{4})-KAP-(.+)$/', $livret, $matches)) {
			$saisonJuge = $matches[1];
			$parts = explode('-', $matches[2]);

			if ($parts[0] === 'A') {
				// Arbitre
				if (isset($parts[1])) {
					$levelPrefix = substr($parts[1], 0, 3);
					if ($levelPrefix === 'REG') {
						$Arb = 'Reg';
						$regional = 'O';
						if (isset($parts[2]) && $parts[2] === 'S') {
							$niveau = 'S';
						}
					} elseif ($levelPrefix === 'NAT') {
						$Arb = 'Nat';
						$national = 'O';
						// Déterminer le niveau A, B ou C
						if (strlen($parts[1]) === 4) {
							$niveau = substr($parts[1], 3, 1); // NATA, NATB, NATC
						} elseif (isset($parts[2]) && $parts[2] === 'S') {
							$niveau = 'S';
						}
					} elseif ($levelPrefix === 'INT') {
						$Arb = 'Int';
						$international = 'O';
						$niveau = '';
					}
				}
			} elseif ($parts[0] === 'OTM') {
				// OTM
				$Arb = 'OTM';
				if (isset($parts[1]) && $parts[1] === 'S') {
					$niveau = 'S';
				}
			} elseif ($parts[0] === 'JO') {
				// Juge Observateur
				$Arb = 'JO';
			}
		} else {
			// Traitement ancien format
			$niveau = substr($livret, -1);
			if ($niveau != 'A' && $niveau != 'B' && $niveau != 'C') {
				$niveau = '';
			}
			if (strlen($arrayToken[3]) > 0) {
				$regional = substr($arrayToken[3], 0, 1);
				if ($regional == 'O') {
					$Arb = "Reg";
				}
			}
			if (strrpos($livret, "JREG") !== false) {
				$niveau = 'S';
			}
			if (strlen($arrayToken[4]) > 0) {
				$interregional = substr($arrayToken[4], 0, 1);
				if ($interregional == 'O') {
					$Arb = "IR";
				}
			}
			if (strlen($arrayToken[5]) > 0) {
				$national = substr($arrayToken[5], 0, 1);
				if ($national == 'O') {
					$Arb = "Nat";
				}
			}
			if (strrpos($livret, "JNAT") !== false) {
				$niveau = 'S';
			}
			if (strlen($arrayToken[6]) > 0) {
				$international = substr($arrayToken[6], 0, 1);
				if ($international == 'O') {
					$Arb = "Int";
					// Pas de niveau pour les juges internationaux
					$niveau = '';
				}
			}
			if (strrpos($livret, "OTM") !== false) {
				$Arb = "OTM";
			}
			if (strrpos($livret, "OTMS") !== false) {
				$niveau = 'S';
			}
			if (strrpos($livret, "JO") !== false) {
				$Arb = "JO";
			}
		}

		return array(
			$matric, $regional, $interregional, $national,
			$international, $Arb, $livret, $niveau, $saisonJuge
		);
	}

	// Importation de la section [juges_pol] du fichier PCE 
	function ImportPCE_Juges_2($buffer)
	{
		$arrayToken = explode(";", $buffer);
		$nbToken = count($arrayToken);

		if ($nbToken != 8) {
			array_push($this->m_arrayinfo, "Erreur [juges_pol] : " . $buffer);
			return;
		}

		$matric =  $arrayToken[0];
		$livret = $arrayToken[7];
		if (substr($livret, -4) !== 'KAP') {
			return false; // On ne traite que les juges KAP
		}
		$saisonJuge = substr($livret, 4);
		// on extrait le code du livret entre le tiret et l'underscore (2025-JREGS_KAP donne JREGS)
		$livret = substr($livret, strpos($livret, '-') + 1, strpos($livret, '_') - strpos($livret, '-') - 1);
		/*
			2025-JREGS_KAP
			2025-JREG_KAP
			2025-JNATA_KAP
			2025-JNATB_KAP
			2025-JNATC_KAP
			2025-JNATS_KAP
			2025-JINT_KAP
			2025-OTM_KAP
			2025-OTMS_KAP
			2025-JO_KAP
		*/

		$regional = substr($arrayToken[3], 0, 1);
		$interregional = substr($arrayToken[4], 0, 1);
		$national = substr($arrayToken[5], 0, 1);
		$international = substr($arrayToken[6], 0, 1);
		$Arb = '';

		switch ($livret) {
			case 'JREGS':
				$niveau = 'S';
				$Arb = "Reg";
				break;
			case 'JREG':
				$niveau = 'A';
				$Arb = "Reg";
				break;
			case 'JNATA':
				$niveau = 'A';
				$Arb = "Nat";
				break;
			case 'JNATB':
				$niveau = 'B';
				$Arb = "Nat";
				break;
			case 'JNATC':
				$niveau = 'C';
				$Arb = "Nat";
				break;
			case 'JNATS':
				$niveau = 'S';
				$Arb = "Nat";
				break;
			case 'JINT':
				$niveau = '';
				$Arb = "Int";
				break;
			case 'OTM':
				$niveau = '';
				$Arb = "OTM";
				break;
			case 'OTMS':
				$niveau = 'S';
				$Arb = "OTM";
				break;
			case 'JO':
				$niveau = '';
				$Arb = "JO";
				break;
		}

		return array(
			$matric, $regional, $interregional, $national,
			$international, $Arb, $livret, $niveau, $saisonJuge
		);
	}


	function ImportPCE_Truncate_Juges()
	{
		$sql_truncate_juges = "DELETE FROM kp_arbitre
			WHERE Matric < 2000000 ";
		$stmt = $this->pdo->prepare($sql_truncate_juges);
		$stmt->execute() or array_push($this->m_arrayinfo, "Erreur SQL " . $stmt->errorInfo());
	}

	function ImportPCE_Query_Juges($count_arbitres, $array_arbitres)
	{
		$placeholders = '';
		for ($i = 0; $i < $count_arbitres; $i++) {
			if ($placeholders != '') {
				$placeholders .= ',';
			}
			$placeholders .= '(?,?,?,?,?,?,?,?,?)';
		}

		$sql_juges = "INSERT INTO kp_arbitre 
			VALUES $placeholders 
			ON DUPLICATE KEY UPDATE 
				Matric = VALUES(Matric), regional = VALUES(regional), interregional = VALUES(interregional), 
				national = VALUES(national), international = VALUES(international), arbitre = VALUES(arbitre), 
				livret = VALUES(livret), niveau = VALUES(niveau), saison = VALUES(saison)
				";
		$stmt = $this->pdo->prepare($sql_juges);
		$stmt->execute($array_arbitres) or array_push($this->m_arrayinfo, "Erreur SQL " . $stmt->errorInfo());
	}

	// Importation de la section [surclassements] du fichier PCE 
	function ImportPCE_Surclassements($buffer)
	{
		$arrayToken = explode(";", $buffer);
		$nbToken = count($arrayToken);
		if ($nbToken != 6) {
			array_push($this->m_arrayinfo, "Erreur [surclassements] : " . $buffer);
			return;
		}

		$discipline = $arrayToken[3];
		if ($discipline == 'KAP') {
			$matric =  $arrayToken[0];
			$categorie = $arrayToken[4];
			$dateSurclassement = $arrayToken[5];
			$dateSurclassement = utyDateFrToUs($dateSurclassement);
			$saisonSurclassement = $this->m_saisonPCE;

			return array(
				$matric, $saisonSurclassement, $categorie, $dateSurclassement
			);
		} else {
			return false;
		}
	}

	function ImportPCE_Query_Surclassements($count_surclassements, $array_surclassements)
	{
		$placeholders = '';
		for ($i = 0; $i < $count_surclassements; $i++) {
			if ($placeholders != '') {
				$placeholders .= ',';
			}
			$placeholders .= '(?,?,?,?)';
		}

		$sql_surclassements = "INSERT INTO kp_surclassement 
			VALUES $placeholders 
			ON DUPLICATE KEY UPDATE 
				Matric = VALUES(Matric), Saison = VALUES(Saison), Cat = VALUES(Cat), 
				`Date` = VALUES(`Date`)
				";
		$stmt = $this->pdo->prepare($sql_surclassements);
		$stmt->execute($array_surclassements) or array_push($this->m_arrayinfo, "Erreur SQL " . $stmt->errorInfo());
	}

	// Mise à Jour des Clubs à partir de la table kp_licence ...
	function ImportPCE_MajClub()
	{
		array_push($this->m_arrayinfo, "Mise à jour des Clubs ...");
		$sql = "INSERT INTO kp_club (Code, Libelle, Officiel, Reserve, Code_comite_dep) 
			SELECT lc.Numero_club selNumero_club, lc.Club selClub , 'O' selOfficiel, 
			'' selReserve, MIN(lc.Numero_comite_dept) selNumero_comite_dept 
			FROM kp_licence lc 
			WHERE lc.Numero_club <> '0' 
			AND lc.Numero_club <> '0000' 
			AND lc.Numero_comite_reg <> '98' 
			AND lc.Origine = ? 
			AND lc.Club != '' 
			GROUP BY Numero_club 
			ON DUPLICATE KEY UPDATE 
				Code = VALUES(Code), Libelle = VALUES(Libelle), Officiel = VALUES(Officiel), 
				Reserve = VALUES(Reserve), Code_comite_dep = VALUES(Code_comite_dep) ";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($this->m_saisonPCE)) or array_push($this->m_arrayinfo, "Erreur SQL " . $stmt->errorInfo());
	}

	// Mise à Jour des Comités Départementaux à partir de la table kp_licence ...
	function ImportPCE_MajComiteDept()
	{
		array_push($this->m_arrayinfo, "Mise à jour des Comités Départementaux  ...");
		$sql = "INSERT INTO kp_cd (Code, Libelle, Officiel, Reserve, Code_comite_reg) 
			SELECT lc.Numero_comite_dept, lc.Comite_dept , 'O' selOfficiel, 
			'' selReserve, lc.Numero_comite_reg 
			FROM kp_licence lc 
			WHERE lc.Numero_club <> '0' 
			AND lc.Numero_club <> '0000' 
			AND lc.Numero_comite_reg <> '98' 
			AND lc.Origine = ? 
			AND lc.Comite_dept <> '' 
			GROUP BY Numero_comite_dept 
			ON DUPLICATE KEY UPDATE 
				Code = VALUES(Code), Libelle = VALUES(Libelle), Officiel = VALUES(Officiel), 
				Reserve = VALUES(Reserve), Code_comite_reg = VALUES(Code_comite_reg) ";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($this->m_saisonPCE)) or array_push($this->m_arrayinfo, "Erreur SQL " . $stmt->errorInfo());
	}

	// Mise à Jour des Comités Régionaux à partir de la table kp_licence ...
	function ImportPCE_MajComiteReg()
	{
		array_push($this->m_arrayinfo, "Mise à jour des Comités Régionaux ...");
		$sql = "INSERT INTO kp_cr (Code, Libelle, Officiel, Reserve) 
			SELECT lc.Numero_comite_reg, lc.Comite_reg , 'O' selOfficiel, '' selReserve 
			FROM kp_licence lc 
			WHERE lc.Numero_club <> '0' 
			AND lc.Numero_club <> '0000' 
			AND lc.Numero_comite_reg <> '98' 
			AND lc.Origine = ? 
            AND lc.Comite_reg <> '' 
			GROUP BY Numero_comite_reg 
			ON DUPLICATE KEY UPDATE 
				Code = VALUES(Code), Libelle = VALUES(Libelle), Officiel = VALUES(Officiel), 
				Reserve = VALUES(Reserve) ";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($this->m_saisonPCE)) or array_push($this->m_arrayinfo, "Erreur SQL " . $stmt->errorInfo());
	}

	// Mise à Jour globale de certaines colonnes de la table kp_licence ...
	function ImportPCE_MajLicencies()
	{
		array_push($this->m_arrayinfo, "Traitement final base des licenciés ...");
		// Verication Sexe M ou F ...		   
		$this->pdo->exec("UPDATE kp_licence SET Sexe = 'M' WHERE Sexe = 'H' ");
		$this->pdo->exec("UPDATE kp_licence SET Sexe = 'F' WHERE Sexe = 'D' ");

		// Vidage Club, CD, CR
		$this->pdo->exec("UPDATE kp_licence 
			SET Club = '', Comite_dept = '', Comite_reg = '' 
			WHERE 1 ");
	}


	// Importation du Calendrier  
	function ImportCalendrier($fileCalendrier)
	{
		$fp = fopen($_SERVER['DOCUMENT_ROOT'] . "/PCE/" . $fileCalendrier, "r");
		if (!$fp) {
			array_push($this->m_arrayinfo, "Ouverture impossible du fichier " . $fileCalendrier);
			return;
		}

		array_push($this->m_arrayinfo, "Importation du fichier " . $fileCalendrier);

		$row = 0;
		$nbAdd = 0;
		$nbUpdate = 0;

		while (!feof($fp)) {
			$buffer = trim(fgets($fp, BUFFER_LENGTH));

			++$row;
			if ($row == 1)
				continue;

			if (strlen($buffer) == 0)
				continue;

			//			$arrayToken = explode("\t", $buffer);   
			$arrayToken = explode(";", $buffer);
			$nbToken = count($arrayToken);

			if (ctype_alpha($arrayToken[0][0]))
				continue;

			if ($nbToken != 27) {
				array_push($this->m_arrayinfo, "Erreur : nombre de champs incorrect " . $nbToken . " : " . $buffer);
				continue;
			}

			$id = trim($arrayToken[0]);
			$code_niveau = trim($arrayToken[2]);
			$code_compet = trim($arrayToken[3]);

			$date_debut = trim($arrayToken[4]);
			$date_debut = substr($date_debut, 0, 10);
			$date_debut = substr($date_debut, 6, 4) . '/' . substr($date_debut, 3, 2) . '/' . substr($date_debut, 0, 2);

			$date_fin = trim($arrayToken[5]);
			$date_fin = substr($date_fin, 0, 10);
			$date_fin = substr($date_fin, 6, 4) . '/' . substr($date_fin, 3, 2) . '/' . substr($date_fin, 0, 2);

			// Determination du Code Saison en fonction de la date de début et du niveau de la competition ...
			if ($code_niveau != 'INT')
				$code_saison = $this->GetSaisonNational($date_debut);
			else
				$code_saison = $this->GetSaisonInternational($date_debut);

			$nom = trim($arrayToken[6]);
			$libelle = trim($arrayToken[7]);
			$lieu = trim($arrayToken[9]);
			$dept = trim($arrayToken[10]);
			$plan_eau = trim($arrayToken[11]);

			$responsable_insc = trim($arrayToken[12]);
			$responsable_insc_adr = '';
			$responsable_insc_cp = '';
			$responsable_insc_ville = '';

			$arrayTokenAdr = explode(",", $responsable_insc);
			$nbTokenAdr = count($arrayTokenAdr);
			if ($nbTokenAdr == 3) {
				$responsable_insc = trim($arrayTokenAdr[0]);
				$responsable_insc_adr = trim($arrayTokenAdr[1]);
				$responsable_insc_cp = substr(trim($arrayTokenAdr[2]), 0, 5);
				$responsable_insc_ville = trim(substr(trim($arrayTokenAdr[2]), 5));
			}

			$responsable_r1 = trim($arrayToken[13]);

			$etat = trim($arrayToken[14]);

			$code_organisateur = trim($arrayToken[21]);
			$organisateur = trim($arrayToken[22]);
			$organisateur_adr = '';
			$organisateur_cp = '';
			$organisateur_ville = '';

			$arrayTokenAdr = explode(",", $organisateur);
			$nbTokenAdr = count($arrayTokenAdr);
			if ($nbTokenAdr == 3) {
				$organisateur = trim($arrayTokenAdr[0]);
				$organisateur_adr = trim($arrayTokenAdr[1]);
				$organisateur_cp = substr(trim($arrayTokenAdr[2]), 0, 5);
				$organisateur_ville = trim(substr(trim($arrayTokenAdr[2]), 5));
			}

			$query  = "SELECT Id 
				FROM kp_journee 
				WHERE Id = ? ";
			$result = $this->pdo->prepare($query);
			$result->execute([$id]);
			if ($result->rowCount() != 1) {
				// Cette journée n'existe pas ...
				$query  = "INSERT INTO kp_journee (Id, Code_competition, Code_saison, Date_debut, 
					Date_fin, Nom, Libelle, Lieu, Departement, Plan_eau, Responsable_insc, 
					Responsable_insc_adr, Responsable_insc_cp, Responsable_insc_ville, Responsable_R1,
					Etat, Code_organisateur, Organisateur, Organisateur_adr, Organisateur_cp, 
					Organisateur_ville) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$result = $this->pdo->prepare($query);
				$result->execute([
					$id,
					$code_compet,
					$code_saison,
					$date_debut,
					$date_fin,
					$nom,
					$libelle,
					$lieu,
					$dept,
					$plan_eau,
					$responsable_insc,
					$responsable_insc_adr,
					$responsable_insc_cp,
					$responsable_insc_ville,
					$responsable_r1,
					$etat,
					$code_organisateur,
					$organisateur,
					$organisateur_adr,
					$organisateur_cp,
					$organisateur_ville
				]);
				$nbAdd++;
			} else {
				/*	// 
				// Cette journée existe déjà ... On met à jour TOUT sauf le code Compet et la Saison
				$query  = "UPDATE kp_journee ";
				$query .= "SET Date_debut = '".$date_debut;
				$query .= "', Date_fin = '".$date_fin;
				$query .= "', Nom = '".$this->pdo->quote($nom);
				$query .= "', Libelle = '".$this->pdo->quote($libelle);
				$query .= "', Lieu = '".$this->pdo->quote($lieu);
				$query .= "', Departement = '".$this->pdo->quote($dept);
				$query .= "', Plan_eau = '".$this->pdo->quote($plan_eau);
				$query .= "', Responsable_insc = '".$this->pdo->quote($responsable_insc);
				$query .= "', Responsable_insc_adr = '".$this->pdo->quote($responsable_insc_adr);
				$query .= "', Responsable_insc_cp = '".$this->pdo->quote($responsable_insc_cp);
				$query .= "', Responsable_insc_ville = '".$this->pdo->quote($responsable_insc_ville);
				$query .= "', Responsable_R1 = '".$this->pdo->quote($responsable_r1);
				$query .= "', Etat = '".$this->pdo->quote($etat);
				$query .= "', Code_organisateur = '".$this->pdo->quote($code_organisateur);
				$query .= "', Organisateur = '".$this->pdo->quote($organisateur);
				$query .= "', Organisateur_adr = '".$this->pdo->quote($organisateur_adr);
				$query .= "', Organisateur_cp = '".$this->pdo->quote($organisateur_cp);
				$query .= "', Organisateur_ville = '".$this->pdo->quote($organisateur_ville);
				$query .= "' WHERE ";
				$query .= "Id = '".$id;
				$query .= "' ";
				
				$nbUpdate++;
				$res = $this->Query($query);
			*/
			}

			$this->ImportCalendrier_Competition($code_compet, $code_saison, $code_niveau, $nom);
		}

		fclose($fp);
		array_push($this->m_arrayinfo, $nbAdd . " journées ajoutées, " . $nbUpdate . " journées mises à jour.");
	}

	// Importation du Calendrier  
	function ImportCalendrier_Competition($code_compet, $code_saison, $code_niveau, $libelle)
	{
		//Chargement des libellés existants
		$query  = "SELECT Code, Libelle 
			FROM kp_competition 
			ORDER BY Code_saison";
		$result = $this->pdo->prepare($query);
		$result->execute();
		$num_results = $result->rowCount();
		$arrayLibelles = array();
		while ($row = $result->fetch()) {
			$arrayLibelles[$row['Code']] = $row['Libelle'];
		}

		$query  = "SELECT Code 
			FROM kp_competition 
			WHERE Code = ? 
			AND Code_saison = ? ";
		$result = $this->pdo->prepare($query);
		$result->execute(array($code_compet, $code_saison));

		$num_results = $result->rowCount();
		if (isset($arrayLibelles[$code_compet]))
			$libelle = $arrayLibelles[$code_compet];


		if ($num_results == 0) {
			$sql  = "INSERT INTO kp_competition (Code, Code_saison, Code_niveau, Libelle) 
				VALUES (?, ?, ?, ?)";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute(array($code_compet, $code_saison, $code_niveau, $libelle)) or array_push($this->m_arrayinfo, "Erreur SQL " . $stmt->errorInfo());
		}
	}

	/**
	 * InitTitulaireEquipe
	 *
	 * @param [type] $numEquipe
	 * @param [type] $idMatch
	 * @param [type] $idEquipe
	 * @return void
	 */
	function InitTitulaireEquipe($numEquipe, $idMatch, $idEquipe, $reinit = false)
	{
		$sql = "SELECT Count(*) Nb 
            FROM kp_match_joueur 
            WHERE Id_match = ? 
            AND Equipe = ? ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($idMatch, $numEquipe));

		$row = $result->fetch();
		if ((int) $row['Nb'] > 0 && $reinit == false) {
			return;
		}

		$sql = "REPLACE INTO kp_match_joueur 
            SELECT ?, Matric, Numero, ?, Capitaine 
            FROM kp_competition_equipe_joueur 
            WHERE Id_equipe = ? 
            AND Capitaine <> 'X' 
            AND Capitaine <> 'A' ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($idMatch, $numEquipe, $idEquipe));
	}

	/**
	 * AutorisationMatch (journée autorisée, match non verrouillé)
	 *
	 * @param [int] $idMatch
	 * @return void
	 */
	function AutorisationMatch($idMatch, $journeeUniquement = false)
	{
		if ($idMatch == 0) {
			header('HTTP/1.0 401 Unauthorized');
			die("Vous n'avez pas l'autorisation de modifier ce match !");
		}
		$sql = "SELECT Id_journee, `Validation` 
			FROM kp_match 
			WHERE Id = ? ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($idMatch));
		$row = $result->fetch();
		if (!utyIsAutorisationJournee($row['Id_journee'])) {
			header('HTTP/1.0 401 Unauthorized');
			die("Vous n'avez pas l'autorisation de modifier les matchs de cette journée !");
		}
		if ($row['Validation'] == 'O' && $journeeUniquement == false) {
			header('HTTP/1.0 401 Unauthorized');
			die("Ce match est verrouillé !");
		}
	}

	// Check cumuls cartons
	// Si carton rouge, calculer les cumuls tous cartons et aviser
	// Si vert ou jaune : calculer les cumuls 
	function CheckCardCumulation($matric, $idMatch, $card, $motif)
	{
		$limit = ['V' => 12, 'J' => 3, 'R' => 1, 'D' => 1];
		$cards = ['V', 'J', 'R', 'D'];

		if (!in_array($card, $cards) || $matric == '') {
			return;
		}
		$card_colors = ['V' => 'Vert', 'J' => 'Jaune', 'R' => 'Rouge', 'D' => 'Rouge_definitif'];
		$saison = $this->GetActiveSaison();
		$headers = 'From: kayak-polo.info <contact@kayak-polo.info>' . "\r\n";
		$msg2 = "";
		$msg3 = "\r\ncf. Article RP KAP 57 du règlement sportif.\r\n\r\nCordialement\r\nKayak-polo.info\r\n";
		$sql = "SELECT lc.Nom, lc.Prenom, lc.Numero_club Club, md.Id_evt_match card,  
			COUNT(md.Id_evt_match) nb_total, 
			SUM(IF(j.Code_competition LIKE 'N%', 1, 0)) nb_champ, 
			SUM(IF(j.Code_competition LIKE 'CF%', 1, 0)) nb_coupe, 
			-- SUM(IF(j.Code_competition LIKE 'M%', 1, 0)) nb_modele, 
			SUM(IF(m.Id_journee = ( 
				SELECT Id_journee FROM kp_match WHERE Id = ?  
				), 1, 0)) nb_journee, 
				(SELECT Code_competition FROM kp_journee 
				INNER JOIN kp_match ON (kp_journee.Id = kp_match.Id_journee) 
				WHERE kp_match.Id = ? ) AS compet  
			FROM kp_match_detail md  
			INNER JOIN kp_match m ON (md.Id_match = m.Id) 
			INNER JOIN kp_journee j ON (m.Id_journee = j.Id) 
			INNER JOIN kp_licence lc ON (md.Competiteur = lc.Matric) 
			WHERE md.Competiteur = ? 
			AND j.Code_saison = ? 
			AND md.Id_evt_match IN ('V','J','R','D')  
			AND (
				j.Code_competition LIKE 'N%' 
				OR j.Code_competition LIKE 'CF%' 
				-- OR j.Code_competition LIKE 'M%' 
				)
			GROUP BY Id_evt_match 
			ORDER BY FIELD(Id_evt_match, 'V', 'J', 'R', 'D')";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($idMatch, $idMatch, $matric, $saison));
		if ($result->rowCount() == 0) {
			return;
		}
		while ($row = $result->fetch()) {
			$prenom = $row['Prenom'];
			$nom = $row['Nom'];
			$club = $row['Club'];
			$compet = $row['compet'];
			if (
				substr($compet, 0, 1) != 'N'
				&& substr($compet, 0, 2) != 'CF'
				// && substr($compet, 0, 1) != 'M'
			) {
				return;
			}
			$msg2 .= "\r\n" . $card_colors[$row['card']] . "s \r\n"
				. "------\r\n"
				. "Championnat = " . $row['nb_champ'] . "\r\n"
				. "Coupe = " . $row['nb_coupe'] . "\r\n"
				. "Journée/phase = " . $row['nb_journee'] . "\r\n"
				. "Total = " . $row['nb_total'] . "\r\n"
				//                        . "Tests = " . $row['nb_modele'] . "\r\n"
			;
			$array[$row['card']] = $row;
		}

		$msg1 = "### MESSAGE AUTOMATIQUE, NE PAS REPONDRE ###\r\n"
			. "Bonjour, vous recevez ce message car vous êtes responsable de compétition $compet ou membre du bureau CNA. \r\n\r\n";

		// Check
		$mail = false;
		$titre = '';
		switch ($card) {
			case 'V':
				if (isset($array['V']['nb_total']) && $array['V']['nb_total'] >= $limit['V']) {
					$msg = $msg1
						. $prenom . ' ' . $nom . ', club ' . $club . ' (licence ' . $matric . ")\r\n"
						. "   vient de faire l'objet d'un carton vert sur le match $idMatch (" . $array['V']['compet'] . "),\r\n"
						. "   et cumule les cartons suivants en $saison :"
						. $msg2 . $msg3;
					$fp = fopen("../../commun/log_cards.txt", "a");
					fputs($fp, $msg . "\r\n"); // on ecrit et on va a la ligne
					fclose($fp);
					// Envoi du mail
					$titre = "[KPI] Alerte carton vert $compet";
					$mail = true;
				}
				break;
			case 'J':
				if (isset($array['J']['nb_total']) && $array['J']['nb_total'] >= $limit['J']) {
					$msg = $msg1
						. $prenom . ' ' . $nom . ', club ' . $club . ' (licence ' . $matric . ")\r\n"
						. "   vient de faire l'objet d'un carton jaune sur le match $idMatch (" . $array['J']['compet'] . "),\r\n"
						. "   et cumule les cartons suivants en $saison :"
						. $msg2 . $msg3;
					$fp = fopen("../../commun/log_cards.txt", "a");
					fputs($fp, $msg . "\r\n"); // on ecrit et on va a la ligne
					fclose($fp);
					// Envoi du mail
					$titre = "[KPI] Alerte carton jaune $compet";
					$mail = true;
				}
				break;
			case 'R':
				if (isset($array['R']['nb_total']) && $array['R']['nb_total'] >= $limit['R']) {
					$msg = $msg1
						. $prenom . ' ' . $nom . ', club ' . $club . ' (licence ' . $matric . ")\r\n"
						. "   vient de faire l'objet d'un carton rouge sur le match $idMatch (" . $array['R']['compet'] . "),\r\n"
						. "   et cumule les cartons suivants en $saison :"
						. $msg2 . $msg3;
					$fp = fopen("../../commun/log_cards.txt", "a");
					fputs($fp, $msg . "\r\n"); // on ecrit et on va a la ligne
					fclose($fp);
					// Envoi du mail
					$titre = "[KPI] Alerte carton rouge $compet";
					$mail = true;
				}
				break;
			case 'D':
				if (isset($array['D']['nb_total']) && $array['D']['nb_total'] >= $limit['D']) {
					$msg = $msg1
						. $prenom . ' ' . $nom . ', club ' . $club . ' (licence ' . $matric . ")\r\n"
						. "   vient de faire l'objet d'un carton rouge définitif sur le match $idMatch (" . $array['R']['compet'] . "),\r\n"
						. "   et cumule les cartons suivants en $saison :"
						. $msg2 . $msg3;
					$fp = fopen("../../commun/log_cards.txt", "a");
					fputs($fp, $msg . "\r\n"); // on ecrit et on va a la ligne
					fclose($fp);
					// Envoi du mail
					$titre = "[KPI] Alerte carton rouge $compet";
					$mail = true;
				}
				break;
			default:
				break;
		}
		// envoi
		if ($mail) {
			// Destinataires
			$destinataires = [];
			$sql2 = "SELECT u.Mail 
				FROM kp_rc rc 
				LEFT OUTER JOIN kp_user u ON (rc.Matric = u.Code) 
				WHERE rc.Code_saison = ? 
				AND (rc.Code_competition = ? OR rc.Code_competition = '- CNA -') ";
			$result2 = $this->pdo->prepare($sql2);
			$result2->execute(array($saison, $compet));
			while ($row2 = $result2->fetch()) {
				$destinataires[] = $row2['Mail'];
			}
			$destinataires = implode(',', $destinataires);

			mail($destinataires, $titre, $msg, $headers);
		}
	}

	// GetCategorie
	function GetCategorie($age, &$code, &$libelle)
	{
		$sql = "SELECT id, libelle 
			FROM kp_categorie 
			WHERE age_min <= :age AND age_max >= :age2 ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(':age' => $age, ':age2' => $age));
		if ($row = $result->fetch()) {
			$code = $row['Code'];
			$libelle = $row['libelle'];
			return true;
		}

		// Catégorie non trouvée ...
		$code = '';
		$libelle = '';
		return false;
	}

	// GetCodeComiteDept
	function GetCodeComiteDept($codeClub)
	{
		$sql = "SELECT Code_comite_dep 
			FROM kp_club 
			WHERE Code = :codeClub ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(':codeClub' => $codeClub));

		if ($row = $result->fetch()) {
			return $row['Code_comite_dep'];
		}

		return '';
	}

	// GetCodeComiteReg
	function GetCodeComiteReg($codeComiteDept)
	{
		$sql  = "SELECT Code_comite_reg 
			FROM kp_cd 
			WHERE Code = :codeComiteDept ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(':codeComiteDept' => $codeComiteDept));

		if ($row = $result->fetch()) {
			return $row['Code_comite_reg'];
		}

		return '';
	}

	// DupliJournee
	function DupliJournee($codeCompet, $codeCompetRef)
	{
		$codeSaison = $this->GetActiveSaison();

		// Suppression des Matchs  
		$sql  = "DELETE FROM kp_match 
			WHERE Id_journee IN (
				SELECT Id 
				FROM kp_journee 
				WHERE Code_competition = '$codeCompet' 
				AND Code_saison = '$codeSaison' )";
		$this->pdo->query($sql) or die("Erreur Delete1");

		// Suppression des Journées
		$sql = "DELETE FROM kp_journee 
			WHERE Code_competition = '$codeCompet' 
			AND Code_saison = '$codeSaison' ";
		$this->pdo->query($sql) or die("Erreur Delete2");

		// Insertion des Journées ...
		$nextIdJournee = $this->GetNextIdJournee();

		$sql  = "INSERT INTO kp_journee (Id, Id_dupli, Code_competition, code_saison, 
			Phase, Niveau, Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, 
			Departement, Responsable_insc, Responsable_R1, Organisateur) 
			SELECT $nextIdJournee-abs(Id), Id, '$codeCompet', code_saison, Phase, 
			Niveau, Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, 
			Departement, Responsable_insc, Responsable_R1, Organisateur 
			FROM kp_journee 
			WHERE Code_competition = '$codeCompetRef' 
			AND Code_saison = '$codeSaison' ";

		$this->pdo->query($sql) or die("Erreur Insert1");

		// Insertion des Matchs ...
		$sql  = "INSERT INTO kp_match (Id_journee, Numero_ordre, Date_match, Heure_match, 
			Libelle, Terrain, Id_equipeA, Id_equipeB, ScoreA, ScoreB, Arbitre_principal, 
			Arbitre_secondaire) 
			SELECT c.Id, a.Numero_ordre, a.Date_match, a.Heure_match, a.Libelle, a.Terrain, 
			a.Id_equipeA, a.Id_equipeB, '', '', a.Arbitre_principal, a.Arbitre_secondaire 
			FROM kp_match a, kp_journee b, kp_journee c 
			WHERE a.Id_journee = b.Id 
			AND b.Code_competition = '$codeCompetRef' 
			AND b.Code_saison = '$codeSaison' 
			AND a.Id_journee = c.Id_dupli";
		$this->pdo->query($sql) or die("Erreur Insert 2");

		// Modification des Id_Equipes ...
		$sql  = "UPDATE kp_match a, kp_competition_equipe b 
			SET a.Id_equipeA = b.Id 
			WHERE a.Id_equipeA = b.Id_dupli 
			AND b.Code_compet = '$codeCompet' 
			AND b.Code_saison = '$codeSaison' 
			AND a.Id_journee IN (
				SELECT Id 
				FROM kp_journee 
				WHERE Code_competition = '$codeCompet' 
				AND Code_saison = '$codeSaison') ";
		$this->pdo->query($sql) or die("Erreur Update A");

		// Modification des Id_Equipes ...
		$sql  = "UPDATE kp_match a, kp_competition_equipe b 
			SET a.Id_equipeB = b.Id 
			WHERE a.Id_equipeB = b.Id_dupli 
			AND b.Code_compet = '$codeCompet' 
			AND b.Code_saison = '$codeSaison' 
			AND a.Id_journee IN (
				SELECT Id 
				FROM kp_journee 
				WHERE Code_competition = '$codeCompet' 
				AND Code_saison = '$codeSaison') ";
		$this->pdo->query($sql) or die("Erreur Update B");
	}

	// GetNextIdJournee 	
	function GetNextIdJournee()
	{
		$sql  = "SELECT MAX(Id) maxId 
			FROM kp_journee 
			WHERE Id < 19000001 ";
		if ($row = $this->pdo->query($sql)->fetch()) {
			return ((int) $row['maxId']) + 1;
		} else {
			return 1;
		}
	}

	// GetEvenementJournees 	
	function GetEvenementJournees($idEvenement)
	{
		$lstJournee = '0';

		$sql = "SELECT Id_journee 
			FROM kp_evenement_journee 
			WHERE Id_evenement = $idEvenement ";
		$stmt = $this->pdo->query($sql) or die("Erreur Load");
		while ($row = $stmt->fetch()) {

			$lstJournee .= ',';
			$lstJournee .= $row['Id_journee'];
		}

		return $lstJournee;
	}

	// GetEvenementLibelle	
	function GetEvenementLibelle($idEvenement)
	{
		$sql = "SELECT Libelle 
			FROM kp_evenement 
			WHERE Id = ? ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($idEvenement));
		$row = $result->fetch();
		return $row['Libelle'];
	}

	// GetActiveSaison 	
	function GetActiveSaison()
	{
		if (isset($_SESSION['Saison'])) {
			return $_SESSION['Saison'];
		}

		$sql = "SELECT Code 
			FROM kp_saison 
			WHERE Etat = 'A' ";
		if ($row = $this->pdo->query($sql)->fetch()) {
			$saison =  $row['Code'];
		} else {
			// Si Aucune Saison active en BDD on retourne l'année de la Date actuelle du Serveur 
			$curDate = GetDate();
			$saison = $curDate['year'];
		}

		$_SESSION['Saison'] = $saison;
		return $saison;
	}

	// GetSaison 	
	function GetSaison($date, $bNational)
	{
		if ($bNational)
			return $this->GetSaisonNational($date);
		else
			return $this->GetSaisonInternational($date);
	}

	// GetSaisonNational 	
	function GetSaisonNational($date)
	{
		$sql = "SELECT Code 
			FROM kp_saison 
			WHERE Nat_debut <= :date1 
			AND Nat_fin >= :date2 ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(':date1' => $date, ':date2' => $date));
		if ($row = $result->fetch()) {
			return $row['Code'];
		}

		return substr($date, 0, 4);
	}

	// GetSaisonInternational 	
	function GetSaisonInternational($date)
	{
		$sql = "SELECT Code 
			FROM kp_saison 
			WHERE Inter_debut <= :date1
			AND Inter_fin >= :date2 ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(':date1' => $date, ':date2' => $date));
		if ($row = $result->fetch()) {
			return $row['Code'];
		}

		return substr($date, 0, 4);
	}

	// GetLabelCompetition 	
	function GetLabelCompetition($codeCompet, $codeSaison = false)
	{
		if (!$codeSaison) {
			$codeSaison = $this->GetActiveSaison();
		}
		$sql = "SELECT Libelle 
			FROM kp_competition 
			WHERE Code = :Code_competition 
			AND Code_saison = :Code_saison ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(
			':Code_competition' => $codeCompet,
			':Code_saison' => $codeSaison
		));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			return $row['Libelle'];
		}

		return '';
	}

	// GetCompetition 	
	function GetCompetition($codeCompet, $codeSaison)
	{
		$sql  = "SELECT c.*, cg.Calendar 
			FROM kp_competition c 
			LEFT JOIN kp_groupe cg
				ON c.Code_ref = cg.Groupe
			WHERE c.Code = :Code_competition 
			AND c.Code_saison = :Code_saison ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(
			':Code_competition' => $codeCompet,
			':Code_saison' => $codeSaison
		));
		if ($row = $result->fetch()) {
			return $row;
		}
		return array(
			'Code' => '', 'Code_niveau' => '', 'Libelle' => '',
			'Code_ref' => '', 'Code_typeclt' => '',
			'Age_min' => '', 'Age_max' => '', 'Sexe' => '',
			'Code_tour' => '', 'Qualifies' => '', 'Elimines' => '',
			'Date_calcul' => '', 'Date_publication' => '', 'Date_publication_calcul' => '',
			'Code_uti_calcul' => '', 'Code_uti_publication' => '',
			'Mode_calcul' => '', 'Mode_publication_calcul' => '',
			'Calendar' => null
		);
	}

	// GetCompetition 	
	function GetOtherCompetitions($codeCompet, $codeSaison, $public = false, $event = 0)
	{
		if ($event > 0) { // TODO : SELECTIONNER LES COMPETITIONS DE L'EVENEMENT !
			$sql  = "SELECT c.Code, c.Code_ref, c.Libelle, c.Soustitre, c.Soustitre2, c.Publication 
				FROM `kp_competition` c, `kp_journee` j, `kp_evenement_journee` ej 
				WHERE ej.Id_journee = j.Id 
				AND j.Code_competition = c.Code 
				AND j.Code_saison = c.Code_saison 
				AND ej.Id_evenement = ? ";
			if ($public) {
				$sql .= "AND c.Publication = 'O' ";
			}
			$sql .= "GROUP BY c.Code 
				ORDER BY c.GroupOrder ";
			$result = $this->pdo->prepare($sql);
			$result->execute(array($event));
		} elseif ($codeCompet == '*') {
			$sql  = "SELECT Code, Code_ref, Libelle, Soustitre, Soustitre2, Publication
                FROM `kp_competition`
                WHERE Code_saison = ?
                AND Code_ref = '" . utyGetSession('codeCompetGroup') . "' ";
			if ($public) {
				$sql .= "AND Publication = 'O' ";
			}
			$sql .= "ORDER BY GroupOrder";
			$result = $this->pdo->prepare($sql);
			$result->execute(array($codeSaison));
		} else {
			$sql  = "SELECT Code, Code_ref, Libelle, Soustitre, Soustitre2, Publication
                FROM `kp_competition`
                WHERE Code_saison = :codeSaison
                AND Code_ref = (
                    SELECT Code_ref 
					FROM `kp_competition` 
					WHERE Code = :codeCompet 
					AND Code_saison = :codeSaison2
				) ";
			if ($public) {
				$sql .= "AND Publication = 'O' ";
			}
			$sql .= "ORDER BY GroupOrder";
			$result = $this->pdo->prepare($sql);
			$result->execute(array(
				':codeSaison' => $codeSaison,
				':codeSaison2' => $codeSaison,
				':codeCompet' => $codeCompet
			));
		}

		$return = [];
		while ($row = $result->fetch()) {
			$return[] = $row;
		}
		return $return;
	}

	function getSections()
	{
		$result = array(
			1 => 'Competitions_Internationales',
			2 => 'Competitions_Nationales',
			3 => 'Competitions_Regionales',
			4 => 'Tournois_Internationaux',
			5 => 'Continents',
			100 => 'Divers'
		);
		return $result;
	}

	function GetGroups($public = 'public', $groupActif = '')
	{
		$result = [];
		$label = $this->getSections();
		if ($public == 'public') {
			$where = "WHERE section < 6 ";
		} else {
			$where = "";
		}
		$sql  = "SELECT * 
			FROM kp_groupe 
			$where
			ORDER BY section, ordre ";
		$i = -1;
		$j = '';
		foreach ($this->pdo->query($sql) as $row) {
			if ($j != $row['section']) {
				$i++;
				$result[$i]['label'] = $label[$row['section']];
			}
			if ($groupActif == $row['Groupe']) {
				$row['selected'] = 'selected';
			} else {
				$row['selected'] = '';
			}
			$result[$i]['options'][] = $row;
			$j = $row['section'];
		}
		$_SESSION['groups' . $public] = $result;
		return $result;
	}

	/**
	 * 
	 * GetEvents
	 * Récupère les événements (publics ou tous)
	 * 
	 * @param bool $public
	 * @return array
	 */
	function GetEvents($public = true, $all = true)
	{
		if ($public) {
			$where = "WHERE Publication = 'O' ";
		} else {
			$where = "";
		}
		if ($all) {
			$result[] = array('Id' => 0, 'Libelle' => 'Tous', 'Lieu' => '');
		}
		$sql  = "SELECT * 
			FROM kp_evenement 
			$where 
			ORDER BY Id DESC ";
		foreach ($this->pdo->query($sql) as $row) {
			$result[] = $row;
		}
		return $result;
	}

	// GetClub 	
	function GetClub($codeClub)
	{
		$sql = "SELECT Code, Libelle, Code_comite_dep 
			FROM kp_club 
			WHERE Code = ? ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($codeClub));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			return array('Code' => $row["Code"], 'Libelle' => $row["Libelle"], 'Code_comite_dep' => $row["Code_comite_dep"]);
		}
		return array('Code' => '', 'Libelle' => '', 'Code_comite_dep' => '');
	}

	// GetComiteDep 	
	function GetComiteDep($codeComiteDep)
	{
		$sql = "SELECT Code, Libelle, Code_comite_reg 
			FROM kp_cd 
			WHERE Code = ? ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($codeComiteDep));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			return array('Code' => $row["Code"], 'Libelle' => $row["Libelle"], 'Code_comite_reg' => $row["Code_comite_reg"]);
		}
		return array('Code' => '', 'Libelle' => '', 'Code_comite_reg' => '');
	}

	// GetComiteReg 	
	function GetComiteReg($codeComiteReg)
	{
		$sql = "SELECT Code, Libelle 
			FROM kp_cr 
			WHERE Code = ? ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($codeComiteReg));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			return array('Code' => $row["Code"], 'Libelle' => $row["Libelle"]);
		}
		return array('Code' => '', 'Libelle' => '');
	}

	// GetNextMatricLicence 	
	function GetNextMatricLicence()
	{
		$sql = "SELECT MAX(Matric) maxMatric 
			FROM kp_licence ";
		$result = $this->pdo->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			$maxMatric = (int) $row['maxMatric'];

			return $maxMatric + 1;
		}
		return 0;
	}

	// GetCodeClubEquipe 	
	function GetCodeClubEquipe($idEquipe)
	{
		$sql  = "SELECT Code_club 
			FROM kp_competition_equipe 
			WHERE Id = ? ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($idEquipe));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			return $row['Code_club'];
		}
		return '';
	}

	// InsertIfNotExistLicence
	function InsertIfNotExistLicence($matric, $nom, $prenom, $sexe, $naissance, $codeClub, $numicf)
	{
		$sql = "SELECT COUNT(*) Nb 
			FROM kp_licence 
			WHERE matric = ? ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array($matric));
		$row = $result->fetch();
		$nb = (int) $row['Nb'];
		if ($nb != 0)
			return;
		$arrayClub = $this->GetClub($codeClub);
		$arrayComiteDep = $this->GetComiteDep($arrayClub['Code_comite_dep']);
		$arrayComiteReg = $this->GetComiteReg($arrayComiteDep['Code_comite_reg']);
		$sql = "INSERT INTO kp_licence (Matric, Origine, Nom, Prenom, Sexe, Naissance, 
			Numero_club, Club, Numero_comite_dept, Comite_dept, Numero_comite_reg, 
			Comite_reg, Reserve) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(
			$matric, $this->GetActiveSaison(), $nom, $prenom, $sexe, $naissance, $codeClub,
			$arrayClub['Libelle'], $arrayClub['Code_comite_dep'], $arrayComiteDep['Libelle'],
			$arrayComiteDep['Code_comite_reg'], $arrayComiteReg['Libelle'], $numicf
		));
	}

	// Journal des manipulations
	function utyJournal($action, $saison = '', $competition = '', $evenement = null, $journee = null, $match = null, $journal = '', $user = '')
	{
		if ($saison == '')
			$saison = $this->GetActiveSaison();
		if ($competition == '')
			$competition = utyGetSession('codeCompet', '');
		if ($user == '')
			$user = utyGetSession('User');
		$sql  = "INSERT INTO kp_journal (Dates, Users, Actions, Saisons, Competitions, Evenements, Journees, Matchs, Journal)
			VALUES (CURRENT_TIMESTAMP,?,?,?,?,?,?,?,?) ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(
			$user, $action, $saison, $competition, $evenement, $journee, $match, $journal
		));
	}

	// Journal des exportations
	function EvtExport($user, $evts, $direction, $nomuser, $erreurs = '')
	{
		$sql  = "INSERT INTO kp_evenement_export (Date ,Utilisateur ,Evenement ,Mouvement ,Parametres ,Erreurs) 
			VALUES (CURRENT_TIMESTAMP, ?,?,?,?,?) ";
		$result = $this->pdo->prepare($sql);
		$result->execute(array(
			$user, $evts, $direction, $nomuser, $erreurs
		));
	}


	// GetUser
	function GetUserName($idUser)
	{
		if ($idUser != '') {
			$sql = "SELECT Identite 
				FROM kp_user 
				WHERE Code = ? ";
			$result = $this->pdo->prepare($sql);
			$result->execute(array($idUser));
			if ($result->rowCount() == 1) {
				$row = $result->fetch();
				return $row['Identite'];
			}
		}
		return '';
	}
}
