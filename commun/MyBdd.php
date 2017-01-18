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
	var $m_arrayinfo;
	
	var $m_saisonPCE;
	  
	// Constructeur 
	function MyBdd($mirror=false) {							  
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
		
		$this->Connect();		   
		
		$this->m_saisonPCE = $this->GetActiveSaison();
	}	
					
 	function Connect() {  
		$this->m_link = mysql_connect($this->m_server, $this->m_login, $this->m_password);
		mysql_query("SET NAMES 'UTF8'");
		if (!$this->m_link) {		
			die('Impossible de se connecter : ' . mysql_error());
		}
					  
		$db = mysql_select_db($this->m_database, $this->m_link);
		if (!$db) {
			die('Impossible de sélectionner la base de données : ' . mysql_error());
		}
	}  
					
	// a éliminer ...					 
	function show_table($tableName)
	{							   
		$query = "Select * from ".$tableName;	 
		$result = mysql_query($query, $m_link);
		if (!$result) 
		{
			echo 'Impossible d\'exécuter la requête : ' . mysql_error();
			exit;	  
		}
		
		$nbFields = mysql_num_fields($result);
		$nbRows = mysql_num_rows($result); 
		
		for ($i=0;$i<$nbRows;$i++)
		{			 
			$row = mysql_fetch_row($result);
			
			for ($j=0;$j<$nbFields;$j++)
				echo $row[$j];	   
				
			echo "<BR>";
		}
		
		mysql_free_result($result);
	}	   
		 
	// Importation du fichier PCE 
	function ImportPCE($filePCE)
	{
	 	$fp = fopen($_SERVER['DOCUMENT_ROOT'] . "/PCE/". $filePCE, "r");
		if (!$fp)
		{				
			array_push($this->m_arrayinfo, "Ouverture impossible du fichier ".$filePCE);
		}	  
		
		array_push($this->m_arrayinfo, "Importation du fichier PCE ".$filePCE);
		  		   
		$section = "";
		while (!feof($fp))
		{
			$buffer = trim( fgets($fp, BUFFER_LENGTH) );	
			if (strlen($buffer) == 0)
				continue;
		
			if ($buffer[0] == '[')
			{
				// Prise de la section ...
				$section = substr($buffer, 1, strlen($buffer)-2);	
				continue;
			}	
			
			if (strcasecmp($section, "date_valeur") == 0)
			{
				$this->ImportPCE_DateValeur($buffer);	 
				continue;
			}							
					
			if (strcasecmp($section, "licencies") == 0)
			{
				$this->ImportPCE_Licencies($buffer);	 
				continue;
			}
			
			if (strcasecmp($section, "juges_pol") == 0)
			{
				$this->ImportPCE_Juges($buffer);	 
				continue;
			}
            //surclassements
			if (strcasecmp($section, "surclassements") == 0)
			{
				$this->ImportPCE_Surclassements($buffer);	 
				continue;
			}
		}	

		fclose($fp);  			   
		
		// Lancement des mises à jour ...
		$this->ImportPCE_MajClub();	 
		$this->ImportPCE_MajComiteReg();
		$this->ImportPCE_MajComiteDept(); 
		$this->ImportPCE_MajLicencies();
		// Fin du traitement
		array_push($this->m_arrayinfo, "" );
		array_push($this->m_arrayinfo, "Traitement terminé avec succès." );
	}		

	// Importation du fichier PCE Nouvelle formule
	function ImportPCE2()
	{
		$debutTraitement = time();
		$section = "";
		$nbLicenciés = 0;
		$nbArbitres = 0;
		$file = "https://ffck-goal.multimediabs.com/reportingExterne/getFichierPce";
        $newfile = "pce.pce";
        
        $arrRequestHeaders = array(
            'http'=>array(
                'method'        =>'GET',
                'protocol_version'    =>1.1,
                'follow_location'    =>1,
                'header'=>    "User-Agent: Anamera-Feed/1.0\r\n" .
                "Referer: $source\r\n" .
                $ifmodhdr
            )
        );
        
	 	if (!copy($file, $newfile, stream_context_create($arrRequestHeaders))) {
            array_push($this->m_arrayinfo, "La copie $file du fichier a échoué...\n");
            return;
        }
        
        $tempsIntermediaire = time() - $debutTraitement;
        
		$fp = fopen($newfile, "r");
		if (!$fp)
		{				
			array_push($this->m_arrayinfo, "Ouverture impossible du fichier distant");
		}	  
		
		array_push($this->m_arrayinfo, "Importation du fichier PCE");
		  		   
		while (!feof($fp))
		{
			$buffer = trim( fgets($fp, BUFFER_LENGTH) );	
			if (strlen($buffer) == 0)
				continue;
		
			if ($buffer[0] == '[')
			{
				// Prise de la section ...
				$section = substr($buffer, 1, strlen($buffer)-2);	
				continue;
			}	
			
			if (strcasecmp($section, "date_valeur") == 0)
			{
				$this->ImportPCE_DateValeur($buffer);
				continue;
			}							
					
			if (strcasecmp($section, "licencies") == 0)
			{
				$this->ImportPCE_Licencies($buffer);
				$nbLicenciés++;
				continue;
			}
			
			if (strcasecmp($section, "juges_kap") == 0)
			{
				$this->ImportPCE_Juges($buffer);
				$nbArbitres++;
				continue;
			}

            if (strcasecmp($section, "surclassements") == 0)
			{
				$this->ImportPCE_Surclassements($buffer);
				$nbSurclassements++;
				continue;
			}
}	

		fclose($fp);  			   
		array_push($this->m_arrayinfo, "Mise à jour ".$nbLicenciés." licenciés..." );
		array_push($this->m_arrayinfo, "Mise à jour ".$nbArbitres." arbitres..." );
		array_push($this->m_arrayinfo, "Mise à jour ".$nbSurclassements." surclassements..." );
		// Lancement des mises à jour ...
		$this->ImportPCE_MajClub();	 
		$this->ImportPCE_MajComiteReg();
		$this->ImportPCE_MajComiteDept(); 
		$this->ImportPCE_MajLicencies();
		// Fin du traitement
		array_push($this->m_arrayinfo, "" );
		array_push($this->m_arrayinfo, "Traitement terminé avec succès." );
		$secondes = time() - $debutTraitement;
		array_push($this->m_arrayinfo, $secondes." secondes (dl=".$tempsIntermediaire.")." );

	}		
	
	// Importation de la section [date_valeur] du fichier PCE 
	function ImportPCE_DateValeur($buffer)				
	{										
		$this->m_saisonPCE = $this->GetSaisonNational($buffer);
		 
		array_push($this->m_arrayinfo, "Date du Fichier : ". $buffer." => Saison active : ".$this->m_saisonPCE);
	}
	
	// Importation de la section [licencies] du fichier PCE 
	function ImportPCE_Licencies($buffer)				
	{	
		$arrayToken = explode(";", $buffer);   
		$nbToken = count($arrayToken);
		
		if ($nbToken < 17)
		{												  
			array_push($this->m_arrayinfo, "Erreur [licencies] : ".$buffer." - token = ".$nbToken);
			return;
		}
		
		$matric =  $arrayToken[0];
		$origine = $this->m_saisonPCE;
		$nom = $arrayToken[1];
		$prenom = $arrayToken[2];
		$sexe = $arrayToken[3];
		$naissance = $arrayToken[4];  

		$club = $arrayToken[5];  
		$num_club = $arrayToken[6];
		$comite_dept = $arrayToken[7];
		$num_comite_dept = $arrayToken[8];
		$comite_reg = $arrayToken[9];
		$num_comite_reg = $arrayToken[10];

		$etat = $arrayToken[11];
		$pagaie_evi = $arrayToken[12];
		$pagaie_mer = $arrayToken[13];
		$pagaie_eca = $arrayToken[14];
		$etat_certificat_aps = $arrayToken[15];
		$etat_certificat_ck = $arrayToken[16];
							 
		$query  = "REPLACE INTO gickp_Liste_Coureur VALUES (";							 	   
		$query .= $matric;
		$query .= ",'";
		$query .= $origine;
		$query .= "','";
		$query .= mysql_real_escape_string($nom);
		$query .= "','";
		$query .= mysql_real_escape_string($prenom);
		$query .= "','";
		$query .= $sexe;
		$query .= "','";
		$query .= $naissance;	
		$query .= "','";
		$query .= mysql_real_escape_string($club);
		$query .= "','";
		$query .= mysql_real_escape_string($num_club);	
		$query .= "','";
		$query .= mysql_real_escape_string($comite_dept);	
		$query .= "','";
		$query .= mysql_real_escape_string($num_comite_dept);	
		$query .= "','";
		$query .= mysql_real_escape_string($comite_reg);	
		$query .= "','";
		$query .= mysql_real_escape_string($num_comite_reg);
		$query .= "','";
		$query .= $etat;	
		$query .= "','";
		$query .= $pagaie_evi;	
		$query .= "','";
		$query .= $pagaie_mer;	
		$query .= "','";
		$query .= $pagaie_eca;	
		$query .= "',";
		$query .= "null"; // Date Certificat CK
		$query .= ",";
		$query .= "null"; // Date Certificat APS
		$query .= ",";
		$query .= "null"; // Reserve
		$query .= ",'";
		$query .= $etat_certificat_aps;
		$query .= "','";
		$query .= $etat_certificat_ck;
		$query .= "')";

		$res = mysql_query($query, $this->m_link);
		if (!$res)
			array_push($this->m_arrayinfo, "Erreur SQL ".mysql_error());
	}	 
	
	// Importation de la section [juges_pol] du fichier PCE 
	function ImportPCE_Juges($buffer)				
	{	
		$arrayToken = explode(";", $buffer);   
		$nbToken = count($arrayToken);
		
		if ($nbToken != 8)
		{												  
			array_push($this->m_arrayinfo, "Erreur [juges_pol] : ".$buffer);
			return;
		}
		
		$matric =  $arrayToken[0];
		$nom = $arrayToken[1];
		$prenom = $arrayToken[2];
		$livret = $arrayToken[7];
		$niveau = substr($livret, -1);
		if($niveau != 'A' && $niveau != 'B' && $niveau != 'C')
			$niveau = '';
		$pos = strrpos($livret, "JREG");
		if ($pos != false) { 
			$niveau = 'S';
		}
		$pos = strrpos($livret, "JNAT");
		if ($pos != false) { 
			$niveau = 'S';
		}
		$saisonJuge = $this->m_saisonPCE;
		
		$regional = 'N';
		$interregional = 'N';
		$national = 'N';
		$international = 'N';
		
		if (strlen($arrayToken[3]) > 0)
			$regional = substr($arrayToken[3],0,1);

		if (strlen($arrayToken[4]) > 0)
			$interregional = substr($arrayToken[4],0,1);
		
		if (strlen($arrayToken[5]) > 0)
			$national = substr($arrayToken[5],0,1);
			
		if (strlen($arrayToken[6]) > 0)
			$international = substr($arrayToken[6],0,1);
		
		$query  = "REPLACE INTO gickp_Arbitre VALUES ($matric, '$regional', '$interregional', '$national', '$international', '', '$livret', '$niveau', '$saisonJuge')";							 	   
		$res = mysql_query($query, $this->m_link);
		if (!$res)
			array_push($this->m_arrayinfo, "Erreur SQL ".mysql_error());
		$query  = "UPDATE gickp_Arbitre SET Arb = 'Reg' WHERE Regional = 'O' ";							 	   
		$res = mysql_query($query, $this->m_link);
		$query  = "UPDATE gickp_Arbitre SET Arb = 'IR' WHERE InterRegional = 'O' ";							 	   
		$res = mysql_query($query, $this->m_link);
		$query  = "UPDATE gickp_Arbitre SET Arb = 'Nat' WHERE National = 'O' ";							 	   
		$res = mysql_query($query, $this->m_link);
		$query  = "UPDATE gickp_Arbitre SET Arb = 'Int' WHERE International = 'O' ";							 	   
		$res = mysql_query($query, $this->m_link);

	}	 

    // Importation de la section [surclassements] du fichier PCE 
	function ImportPCE_Surclassements($buffer)				
	{	
		$arrayToken = explode(";", $buffer);   
		$nbToken = count($arrayToken);
		if ($nbToken != 6)
		{												  
			array_push($this->m_arrayinfo, "Erreur [surclassements] : ".$buffer);
			return;
		}
		
		$matric =  $arrayToken[0];
		$nom = $arrayToken[1];
		$prenom = $arrayToken[2];
		$discipline = $arrayToken[3];
		$categorie = $arrayToken[4];
        $dateSurclassement = $arrayToken[5];
        $dateSurclassement = explode('/',$dateSurclassement);
        $dateSurclassement = implode('-',$dateSurclassement);
		$saisonSurclassement = $this->m_saisonPCE;

        if($discipline == 'KAP'){
            $query  = "REPLACE INTO gickp_Surclassements VALUES ($matric, $saisonSurclassement, '$categorie', '$dateSurclassement')";							 	   
            $res = mysql_query($query, $this->m_link);
            if (!$res) {
                array_push($this->m_arrayinfo, "Erreur SQL " . mysql_error());
            }
        }

	}	 

			 
	// Mise à Jour des Clubs à partir de la table gickp_Liste_Coureur ...
	function ImportPCE_MajClub()				
	{ 				  
		array_push($this->m_arrayinfo, "Mise à jour des Clubs ...");

		$query  = "CREATE TEMPORARY TABLE tmpClub ENGINE = HEAP ";
		$query .= "SELECT Numero_club, Min(Club) Club , 'O' Officiel, '' Reserve , Min(Numero_comite_dept) Numero_comite_dept ";
		$query .= "FROM gickp_Liste_Coureur ";
		$query .= "GROUP BY Numero_club ";	  

		$res = mysql_query($query, $this->m_link);
		if (!$res)							 
		{
			array_push($this->m_arrayinfo, "Erreur SQL : ".$query." : ".mysql_error());
			return;
		}		

		$query  = "INSERT INTO gickp_Club (Code, Libelle, Officiel, Reserve, Code_comite_dep) ";
		$query .= "SELECT * FROM tmpClub ";
		$query .= "WHERE tmpClub.Numero_club Not In (Select gickp_Club.Code From gickp_Club) ";	 

		$res = mysql_query($query, $this->m_link);
		if (!$res)							 
		{		
			array_push($this->m_arrayinfo, "Erreur SQL : ".$query." : ".mysql_error());
			return;
		}	
		
		$query = "UPDATE gickp_Club Set libelle = replace(libelle, 'CANOE KAYAK', 'CK') Where Libelle like '%CANOE KAYAK%' ";
		$res = mysql_query($query, $this->m_link);
		if (!$res)							 
		{									   
			array_push($this->m_arrayinfo, "Erreur SQL : ".$query." : ".mysql_error());
			return;
		}	
	}	   
	
	// Mise à Jour des Comités Régionaux à partir de la table gickp_Liste_Coureur ...
	function ImportPCE_MajComiteReg()				
	{ 								 
		array_push($this->m_arrayinfo, "Mise à jour des Comités Régionaux ..." );

		$query  = "CREATE TEMPORARY TABLE tmpcomite_reg ENGINE = HEAP ";
		$query .= "Select Numero_comite_reg, Min(Comite_reg) Comite_reg, 'O' Officiel, '' Reserve ";
		$query .= "FROM gickp_Liste_Coureur ";
		$query .= "GROUP BY Numero_comite_reg ";	  

		$res = mysql_query($query);
		if (!$res)							 
		{					  
			array_push($this->m_arrayinfo, "Erreur SQL ".mysql_error());
			return;
		}		

		$query  = "INSERT INTO gickp_Comite_reg ";
		$query .= "SELECT * FROM tmpcomite_reg ";
		$query .= "WHERE tmpcomite_reg.Numero_comite_reg Not In (Select gickp_Comite_reg.Code from gickp_Comite_reg) ";	 

		$res = mysql_query($query, $this->m_link);
		if (!$res)							 
		{
			array_push($this->m_arrayinfo, "Erreur SQL ".mysql_error());
			return;
		}		
	}

	// Mise à Jour des Comités Départementaux à partir de la table gickp_Liste_Coureur ...
	function ImportPCE_MajComiteDept()				
	{ 		
		array_push($this->m_arrayinfo, "Mise à jour des Comités Départementaux  ...");

		$query  = "CREATE TEMPORARY TABLE tmpcomite_dep ENGINE = HEAP ";		
		$query .= "Select Numero_comite_dept, Min(Comite_dept) Comite_dept, 'O' Officiel, '' Reserve, Min(Numero_comite_reg) Numero_comite_reg ";		
		$query .= "FROM gickp_Liste_Coureur ";
		$query .= "GROUP BY Numero_comite_dept ";	  

		$res = mysql_query($query, $this->m_link);
		if (!$res)							 
		{		
			array_push($this->m_arrayinfo, "Erreur SQL ".mysql_error());
			return;
		}		

		$query  = "INSERT INTO gickp_Comite_dep ";
		$query .= "SELECT * FROM tmpcomite_dep ";
		$query .= "WHERE tmpcomite_dep.Numero_comite_dept Not In (Select gickp_Comite_dep.Code from gickp_Comite_dep) ";	 

		$res = mysql_query($query, $this->m_link);
		if (!$res)							 
		{		
			array_push($this->m_arrayinfo, "Erreur SQL ".mysql_error());
			return;
		}		
	}		
		
	// Mise à Jour globale de certaines colonnes de la table gickp_Liste_Coureur ...
	function ImportPCE_MajLicencies()				
	{	   		
		array_push($this->m_arrayinfo, "Traitement final base des licenciés ..." );
		// Verication Sexe M ou F ...		   
		$query = "Update gickp_Liste_Coureur Set Sexe = 'M' Where Sexe = 'H' ";		
		$res = mysql_query($query, $this->m_link);
		if (!$res)							 
		{		
			array_push($this->m_arrayinfo, "Erreur SQL : ".$query." : ".mysql_error());
			return;
		}		

		$query = "Update gickp_Liste_Coureur Set Sexe = 'F' Where Sexe = 'D' ";		
		$res = mysql_query($query, $this->m_link);
		if (!$res)							 
		{
			array_push($this->m_arrayinfo, "Erreur SQL : ".$query." : ".mysql_error());
			return;
		}		

		// Transformation Club
		// $query = "Update gickp_Liste_Coureur Set Club = replace(Club, 'CANOE KAYAK', 'CK') Where Club like '%CANOE KAYAK%' ";	
		
		// Vidage Club, CD, CR
		$query = "Update gickp_Liste_Coureur Set Club = '', Comite_dept = '', Comite_reg = '' Where 1 ";		
		$res = mysql_query($query, $this->m_link);
		if (!$res)							 
		{
			array_push($this->m_arrayinfo, "Erreur SQL : ".$query." : ".mysql_error());
			return;
		}		
	}


	// Importation du Calendrier  
	function ImportCalendrier($fileCalendrier)
	{
	 	$fp = fopen($_SERVER['DOCUMENT_ROOT']."/PCE/". $fileCalendrier, "r");
		if (!$fp)
		{				
			array_push($this->m_arrayinfo, "Ouverture impossible du fichier ".$fileCalendrier);
			return;
		}	  
		
		array_push($this->m_arrayinfo, "Importation du fichier ".$fileCalendrier);
		  		   
		$row = 0;
		$nbAdd = 0;
		$nbUpdate = 0;
		
		while (!feof($fp))
		{
			$buffer = trim( fgets($fp, BUFFER_LENGTH) );	
			
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
		
			if ($nbToken != 27)
			{												  
				array_push($this->m_arrayinfo, "Erreur : nombre de champs incorrect " .$nbToken. " : ".$buffer);
				continue;
			}
			
			$id = trim($arrayToken[0]);
			$code_niveau = trim($arrayToken[2]);
			$code_compet = trim($arrayToken[3]);
			
			$date_debut = trim($arrayToken[4]);
			$date_debut = substr($date_debut, 0, 10);
			$date_debut = substr($date_debut, 6, 4).'/'.substr($date_debut, 3, 2).'/'.substr($date_debut, 0, 2);
			
			$date_fin = trim($arrayToken[5]);
			$date_fin = substr($date_fin, 0, 10);
			$date_fin = substr($date_fin, 6, 4).'/'.substr($date_fin, 3, 2).'/'.substr($date_fin, 0, 2);

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
			if ($nbTokenAdr == 3)
			{
				$responsable_insc = trim($arrayTokenAdr[0]); 
				$responsable_insc_adr = trim($arrayTokenAdr[1]);
				$responsable_insc_cp = substr(trim($arrayTokenAdr[2]),0,5);
				$responsable_insc_ville = trim(substr(trim($arrayTokenAdr[2]),5));
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
			if ($nbTokenAdr == 3)
			{
				$organisateur = trim($arrayTokenAdr[0]); 
				$organisateur_adr = trim($arrayTokenAdr[1]);
				$organisateur_cp = substr(trim($arrayTokenAdr[2]),0,5);
				$organisateur_ville = trim(substr(trim($arrayTokenAdr[2]),5));
			}
			
			$query  = "Select Id From gickp_Journees Where Id = $id ";
			$res = mysql_query($query, $this->m_link) or die ("Erreur Select ImportCalendrier() ");
			if (mysql_num_rows($res) != 1)
			{
				// Cette journée n'existe pas ...
				$query  = "INSERT INTO gickp_Journees (Id, Code_competition, Code_saison, Date_debut, Date_fin, Nom, Libelle, Lieu, Departement, Plan_eau, ";
				$query .= "Responsable_insc, Responsable_insc_adr, Responsable_insc_cp, Responsable_insc_ville, Responsable_R1,";							 	   
				$query .= "Etat, Code_organisateur, Organisateur, Organisateur_adr, Organisateur_cp, Organisateur_ville) Values (";							 	   
				$query .= $id;
				$query .= ",'";
				$query .= $code_compet;
				$query .= "','";
				$query .= $code_saison;
				$query .= "','";
				$query .= $date_debut;
				$query .= "','";
				$query .= $date_fin;
				$query .= "','";
				$query .= mysql_real_escape_string($nom);
				$query .= "','";
				$query .= mysql_real_escape_string($libelle);
				$query .= "','";
				$query .= mysql_real_escape_string($lieu);
				$query .= "','";
				$query .= mysql_real_escape_string($dept);
				$query .= "','";
				$query .= mysql_real_escape_string($plan_eau);
				$query .= "','";
				$query .= mysql_real_escape_string($responsable_insc);
				$query .= "','";
				$query .= mysql_real_escape_string($responsable_insc_adr);
				$query .= "','";
				$query .= mysql_real_escape_string($responsable_insc_cp);
				$query .= "','";
				$query .= mysql_real_escape_string($responsable_insc_ville);
				$query .= "','";
				$query .= mysql_real_escape_string($responsable_r1);
				$query .= "','";
				$query .= mysql_real_escape_string($etat);
				$query .= "','";
				$query .= mysql_real_escape_string($code_organisateur);
				$query .= "','";
				$query .= mysql_real_escape_string($organisateur);
				$query .= "','";
				$query .= mysql_real_escape_string($organisateur_adr);
				$query .= "','";
				$query .= mysql_real_escape_string($organisateur_cp);
				$query .= "','";
				$query .= mysql_real_escape_string($organisateur_ville);
				$query .= "')";
				
				$nbAdd++;
			}
			else
			{
			/*	// 
				// Cette journée existe déjà ... On met à jour TOUT sauf le code Compet et la Saison
				$query  = "UPDATE gickp_Journees ";
				$query .= "SET Date_debut = '".$date_debut;
				$query .= "', Date_fin = '".$date_fin;
				$query .= "', Nom = '".mysql_real_escape_string($nom);
				$query .= "', Libelle = '".mysql_real_escape_string($libelle);
				$query .= "', Lieu = '".mysql_real_escape_string($lieu);
				$query .= "', Departement = '".mysql_real_escape_string($dept);
				$query .= "', Plan_eau = '".mysql_real_escape_string($plan_eau);
				$query .= "', Responsable_insc = '".mysql_real_escape_string($responsable_insc);
				$query .= "', Responsable_insc_adr = '".mysql_real_escape_string($responsable_insc_adr);
				$query .= "', Responsable_insc_cp = '".mysql_real_escape_string($responsable_insc_cp);
				$query .= "', Responsable_insc_ville = '".mysql_real_escape_string($responsable_insc_ville);
				$query .= "', Responsable_R1 = '".mysql_real_escape_string($responsable_r1);
				$query .= "', Etat = '".mysql_real_escape_string($etat);
				$query .= "', Code_organisateur = '".mysql_real_escape_string($code_organisateur);
				$query .= "', Organisateur = '".mysql_real_escape_string($organisateur);
				$query .= "', Organisateur_adr = '".mysql_real_escape_string($organisateur_adr);
				$query .= "', Organisateur_cp = '".mysql_real_escape_string($organisateur_cp);
				$query .= "', Organisateur_ville = '".mysql_real_escape_string($organisateur_ville);
				$query .= "' WHERE ";
				$query .= "Id = '".$id;
				$query .= "' ";
				
				$nbUpdate++;
			*/
			}
				
				$res = mysql_query($query, $this->m_link);
				if (!$res)
				{
					array_push($this->m_arrayinfo, "Erreur SQL ".mysql_error());
				}
					
				$this->ImportCalendrier_Competition($code_compet, $code_saison, $code_niveau, $nom);
		}
		
		fclose($fp);
		array_push($this->m_arrayinfo, $nbAdd." journées ajoutées, ".$nbUpdate." journées mises à jour.");
		
	}
	
	// Importation du Calendrier  
	function ImportCalendrier_Competition($code_compet, $code_saison, $code_niveau, $libelle)				
	{
		//Chargement des libellés existants
		$query  = "Select Code, Libelle From gickp_Competitions Order by Code_saison";
		$result = mysql_query($query, $this->m_link) or die ("Erreur Select ImportCalendrier_Competition");
		$num_results = mysql_num_rows($result);
		$arrayLibelles = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			$arrayLibelles[$row['Code']]=$row['Libelle'];
		}
		
		$query  = "Select Code From gickp_Competitions ";
		$query .= "Where Code = '";
		$query .= $code_compet;
		$query .= "' And Code_saison = '";
		$query .= $code_saison;
		$query .= "' ";
		
		$result = mysql_query($query, $this->m_link) or die ("Erreur Select ImportCalendrier_Competition");
		$num_results = mysql_num_rows($result);
		if(isset($arrayLibelles[$code_compet]))
			$libelle = $arrayLibelles[$code_compet];
	
		
		if ($num_results == 0)
		{
			$query  = "Insert Into gickp_Competitions (Code, Code_saison, Code_niveau, Libelle) Values ('";
			$query .= $code_compet;
			$query .= "','";
			$query .= $code_saison;
			$query .= "','";
			$query .= $code_niveau;
			$query .= "','";
			$query .= mysql_real_escape_string($libelle);
			$query .= "')";
		
			$result = mysql_query($query, $this->m_link);
			if (!$result)
				array_push($this->m_arrayinfo, "Erreur SQL ".mysql_error());
		}
	}
	
	// GetCategorie
	function GetCategorie($age, &$code, &$libelle)
	{
		$query  = "Select Code, Libelle From gickp_Categorie Where Age_min <= $age And Age_max >= $age ";
		$result = mysql_query($query, $this->m_link) or die ("Erreur Select");
		
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	
			  
			$code = $row['Code'];
			$libelle = $row['Libelle'];
			
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
		$query  = "Select Code_comite_dep From gickp_Club Where Code = '$codeClub' ";
		$result = mysql_query($query, $this->m_link) or die ("Erreur Select");
		
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	
			return $row['Code_comite_dep'];
		}
	
		return '';
	}
	
	// GetCodeComiteReg
	function GetCodeComiteReg($codeComiteDept)
	{
		$query  = "Select Code_comite_reg From gickp_Comite_dep Where Code = '$codeComiteDept' ";
		$result = mysql_query($query, $this->m_link) or die ("Erreur Select");
		
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	
			return $row['Code_comite_reg'];
		}
	
		return '';
	}
	
	// DupliJournee
	function DupliJournee($codeCompet, $codeCompetRef)
	{
		$codeSaison = utyGetSaison();
		
		// Suppression des Matchs  
		$sql  = "Delete FROM gickp_Matchs Where Id_journee In (";
		$sql .= "Select Id From gickp_Journees Where Code_competition = '$codeCompet' And Code_saison = '$codeSaison' )";
		mysql_query($sql, $this->m_link) or die ("Erreur Delete1");

		// Suppression des Journées
		$sql = "Delete From gickp_Journees Where Code_competition = '$codeCompet' And Code_saison = '$codeSaison' ";
		mysql_query($sql, $this->m_link) or die ("Erreur Delete2");

		// Insertion des Journées ...
		$nextIdJournee = $this->GetNextIdJournee();
		
		$sql  = "Insert Into gickp_Journees (Id, Id_dupli, Code_competition, code_saison, Phase, Niveau, Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, ";
		$sql .= "Departement, Responsable_insc, Responsable_R1, Organisateur) ";
		$sql .= "Select $nextIdJournee-abs(Id), Id, '$codeCompet', code_saison, Phase, Niveau, Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, ";
		$sql .= "Departement, Responsable_insc, Responsable_R1, Organisateur ";
		$sql .= "From gickp_Journees ";
		$sql .= "Where Code_competition = '$codeCompetRef' And Code_saison = '$codeSaison' ";
		
		mysql_query($sql, $this->m_link) or die ("Erreur Insert1");
			
		// Insertion des Matchs ...
		$sql  = "Insert Into gickp_Matchs (Id_journee, Numero_ordre, Date_match, Heure_match, Libelle, Terrain, ";
		$sql .= "Id_equipeA, Id_equipeB, ScoreA, ScoreB, Arbitre_principal, Arbitre_secondaire) ";
		$sql .= "Select c.Id, a.Numero_ordre, a.Date_match, a.Heure_match, a.Libelle, a.Terrain, ";
		$sql .= "a.Id_equipeA, a.Id_equipeB, '', '', a.Arbitre_principal, a.Arbitre_secondaire ";
		$sql .= "From gickp_Matchs a, gickp_Journees b, gickp_Journees c ";
		$sql .= "Where a.Id_journee = b.Id ";
		$sql .= "And b.Code_competition = '$codeCompetRef' And b.Code_saison = '$codeSaison' ";
		$sql .= "And a.Id_journee = c.Id_dupli";
		mysql_query($sql, $this->m_link) or die ("Erreur Insert 2");
		
		// Modification des Id_Equipes ...
		$sql  = "Update gickp_Matchs a, gickp_Competitions_Equipes b Set a.Id_equipeA = b.Id ";
		$sql .= "Where a.Id_equipeA = b.Id_dupli ";
		$sql .= "And b.Code_compet = '$codeCompet' And b.Code_saison = '$codeSaison' ";
		$sql .= "And a.Id_journee In (Select Id From gickp_Journees Where Code_competition = '$codeCompet' And Code_saison = '$codeSaison') ";
		mysql_query($sql, $this->m_link) or die ("Erreur Update A");
		
		// Modification des Id_Equipes ...
		$sql  = "Update gickp_Matchs a, gickp_Competitions_Equipes b Set a.Id_equipeB = b.Id ";
		$sql .= "Where a.Id_equipeB = b.Id_dupli ";
		$sql .= "And b.Code_compet = '$codeCompet' And b.Code_saison = '$codeSaison' ";
		$sql .= "And a.Id_journee In (Select Id From gickp_Journees Where Code_competition = '$codeCompet' And Code_saison = '$codeSaison') ";
		mysql_query($sql, $this->m_link) or die ("Erreur Update B");
	}
	
	// GetNextIdJournee 	
	function GetNextIdJournee()
	{
		$sql  = "Select min(Id) minId From gickp_Journees Where Id < 0 ";
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select");
	
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	  
			return ((int) $row['minId'])-1;
		}
		else
		{
			return -1;
		}
	}		
	
	// GetEvenementJournees 	
	function GetEvenementJournees($idEvenement)
	{
		$lstJournee = '0';

		$sql = "Select Id_journee From gickp_Evenement_Journees Where Id_evenement = $idEvenement "; 
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
			
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			
			$lstJournee .= ',';
			$lstJournee .= $row['Id_journee'];
		}
		
		return $lstJournee;
	}
	
	// GetEvenementLibelle	
	function GetEvenementLibelle($idEvenement)
	{
		$sql = "Select Libelle From gickp_Evenement Where Id = $idEvenement "; 
		
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			return $row['Libelle'];
		}
	
		return '';
	}	
	
	// GetActiveSaison 	
	function GetActiveSaison()
	{
		$sql = "Select Code From gickp_Saison Where Etat = 'A' "; 
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetActiveSaison() ");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			return $row['Code'];
		}
		
		// Si Aucune Saison active en BDD on retourne la l'année de la Date actuelle du Serveur 
		$curDate = GetDate();
		return $curDate['year'];
	}
	
	// GetSaison 	
	function GetSaison($date, $bNational)
	{
			if ($bNational)
				return GetSaisonNational($date);
			else
				return GetSaisonInternational($date);
	}

	// GetSaisonNational 	
	function GetSaisonNational($date)
	{
		$sql = "Select Code From gickp_Saison Where Nat_debut <= '$date' And Nat_fin >= '$date' "; 
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetSaisonNational() ");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			return $row['Code'];
		}

		return substr($date,0,4);
	}
	
	// GetSaisonInternational 	
	function GetSaisonInternational($date)
	{
		$sql = "Select Code From gickp_Saison Where Inter_debut <= '$date' And Inter_fin >= '$date' "; 
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetSaisonInternational() ");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			return $row['Code'];
		}
		
		return substr($date,0,4);
	}
	
	// GetLabelCompetition 	
	function GetLabelCompetition($codeCompet, $codeSaison)
	{
		$sql = "Select Libelle From gickp_Competitions Where Code = '$codeCompet' And Code_saison = '$codeSaison' "; 
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetLabelCompetition() ");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			return $row['Libelle'];
		}

		return '';
	}

	// GetSoustitre2Competition 	
	function GetSoustitre2Competition($codeCompet, $codeSaison)
	{
		$sql = "Select Soustitre2 From gickp_Competitions Where Code = '$codeCompet' And Code_saison = '$codeSaison' "; 
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetSoustitre2Competition() ");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			return $row['Soustitre2'];
		}

		return '';
	}
	
	// GetCompetition 	
	function GetCompetition($codeCompet, $codeSaison)
	{
		$sql  = "SELECT *, ";
		$sql .= "DATE_FORMAT(Date_calcul,'%d/%m/%y à %Hh%i') Date_calcul,";
		$sql .= "DATE_FORMAT(Date_publication, '%d/%m/%y à %Hh%i') Date_publication, ";
		$sql .= "DATE_FORMAT(Date_publication_calcul, '%d/%m/%y à %Hh%i') Date_publication_calcul, ";
		$sql .= "Code_uti_calcul, Code_uti_publication, Mode_calcul, Mode_publication_calcul ";
		$sql .= "FROM gickp_Competitions ";
		$sql .= "WHERE Code = '$codeCompet' And Code_saison = '$codeSaison' "; 		
	
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			
		//	return array( 'Code' => $row["Code"], 'Code_niveau' => $row["Code_niveau"], 'Libelle' => $row["Libelle"],
		//				     		'Code_ref' => $row["Code_ref"], 'Code_typeclt' => $row["Code_typeclt"], 
		//								'Age_min' => $row["Age_min"], 'Age_max' => $row["Age_max"], 'Sexe' => $row["Sexe"],
		//						  	'Code_tour' => $row["Code_tour"], 'Qualifies' => $row["Qualifies"], 'Elimines' => $row["Elimines"],
		//						  	'Date_calcul' => $row["Date_calcul"], 'Date_publication' => $row["Date_publication"], 'Date_publication_calcul' => $row["Date_publication_calcul"],
		//						  	'Code_uti_calcul' => $row["Code_uti_calcul"], 'Code_uti_publication' => $row["Code_uti_publication"], 'Mode_calcul' => $row["Mode_calcul"], 'Mode_publication_calcul' => $row["Mode_publication_calcul"]
		//					  	);
			return $row;
		}
		return array( 'Code' => '', 'Code_niveau' => '', 'Libelle' => '',
					     		'Code_ref' => '', 'Code_typeclt' => '', 
									'Age_min' => '', 'Age_max' => '', 'Sexe' => '',
							  	'Code_tour' => '', 'Qualifies' => '', 'Elimines' => '',
							  	'Date_calcul' => '', 'Date_publication' => '', 'Date_publication_calcul' => '',
							  	'Code_uti_calcul' => '', 'Code_uti_publication' => '', 'Mode_calcul' => '', 'Mode_publication_calcul' => ''
								);							  	
	}		
	
	// GetClub 	
	function GetClub($codeClub)
	{
		$sql  = "Select Code, Libelle, Code_comite_dep From gickp_Club Where Code = '$codeClub' ";
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetClub");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			return array( 'Code' => $row["Code"], 'Libelle' => $row["Libelle"], 'Code_comite_dep' => $row["Code_comite_dep"] );
		}
		return array( 'Code' => '', 'Libelle' => '', 'Code_comite_dep' => '' );
	}		
	
	// GetComiteDep 	
	function GetComiteDep($codeComiteDep)
	{
		$sql  = "Select Code, Libelle, Code_comite_reg From gickp_Comite_dep Where Code = '$codeComiteDep' ";
	
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetComiteDep");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			return array( 'Code' => $row["Code"], 'Libelle' => $row["Libelle"], 'Code_comite_reg' => $row["Code_comite_reg"] );
		}
		return array( 'Code' => '', 'Libelle' => '', 'Code_comite_reg' => '' );
	}		
	
	// GetComiteReg 	
	function GetComiteReg($codeComiteReg)
	{
		$sql  = "Select Code, Libelle From gickp_Comite_reg Where Code = '$codeComiteReg' ";
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select codeComiteReg");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);
			return array( 'Code' => $row["Code"], 'Libelle' => $row["Libelle"] );
		}
		return array( 'Code' => '', 'Libelle' => '' );
	}		

	// GetNextMatricLicence 	
	function GetNextMatricLicence()
	{
		$sql = "Select max(Matric) maxMatric From gickp_Liste_Coureur ";
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetNextMatricJoueur");
		if (mysql_num_rows($result) == 1)
		{
				$row = mysql_fetch_array($result);
				$maxMatric = (int) $row['maxMatric'];
				
				return max($maxMatric+1, ***);
		}
		return ***;
	}
	
	// GetCodeClubEquipe 	
	function GetCodeClubEquipe($idEquipe)
	{
			$sql  = "Select Code_club From gickp_Competitions_Equipes Where Id = $idEquipe";
			$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetCodeClubEquipe");
			if (mysql_num_rows($result) == 1)
			{
				$row = mysql_fetch_array($result);
				return $row['Code_club'];
			}
			return '';
	}

	// InsertIfNotExistLicence
	function InsertIfNotExistLicence($matric, $nom, $prenom, $sexe, $naissance, $codeClub)
	{
		$sql  = "Select Count(*) Nb From gickp_Liste_Coureur Where matric = $matric";
		$result = mysql_query($sql, $this->m_link) or die ("Erreur Select InsertIfNotExistLicence");
		$row = mysql_fetch_array($result);
		$nb = (int) $row['Nb'];
		if ($nb != 0)
			return;
		$arrayClub = $this->GetClub($codeClub);
		$arrayComiteDep = $this->GetComiteDep($arrayClub['Code_comite_dep']);
		$arrayComiteReg = $this->GetComiteReg($arrayComiteDep['Code_comite_reg']);
		$sql  = "Insert Into gickp_Liste_Coureur (Matric, Origine, Nom, Prenom, Sexe, Naissance, ";
		$sql .= "Numero_club, Club, Numero_comite_dept, Comite_dept, Numero_comite_reg, Comite_reg) Values ($matric,'";
		$sql .= utyGetSaison();
		$sql .= "','";
		$sql .= mysql_real_escape_string($nom);
		$sql .= "','";
		$sql .= mysql_real_escape_string($prenom);
		$sql .= "','";
		$sql .= $sexe;
		$sql .= "','";
		$sql .= $naissance;
		$sql .= "','";
		$sql .= $codeClub;
		$sql .= "','";
		$sql .= mysql_real_escape_string($arrayClub['Libelle']);
		$sql .= "','";
		$sql .= $arrayClub['Code_comite_dep'];
		$sql .= "','";
		$sql .= mysql_real_escape_string($arrayComiteDep['Libelle']);
		$sql .= "','";
		$sql .= $arrayComiteDep['Code_comite_reg'];
		$sql .= "','";
		$sql .= mysql_real_escape_string($arrayComiteReg['Libelle']);
		$sql .= "')";
		mysql_query($sql, $this->m_link) or die ("Erreur Insert InsertIfNotExistLicence");
	}

	// Journal des manipulations
	function utyJournal($action, $saison='', $competition='', $evenement='NULL', $journee='NULL', $match='NULL', $journal='', $user='')
	{
		if($saison == '')
			$saison = utyGetSaison();
		if($competition == '')
			$competition = utyGetSession('codeCompet', '');
		if($user == '')
			$user = utyGetSession('User');
		$sql  = "INSERT INTO gickp_Journal (Dates ,Users ,Actions ,Saisons ,Competitions ,Evenements ,Journees ,Matchs ,Journal) VALUES (";
		$sql .= "CURRENT_TIMESTAMP, ";
		$sql .= "'".$user."', ";
		$sql .= "'".$action."', ";
		$sql .= "'".$saison."', ";
		$sql .= "'".$competition."', ";
		$sql .= "'".$evenement."', ";
		$sql .= "'".$journee."', ";
		$sql .= "'".$match."', ";
		$sql .= "'".mysql_real_escape_string($journal)."'";
		$sql .= ") ";
		mysql_query($sql, $this->m_link) or die ("Erreur Insert Journal : ".$sql);
	}

	// Journal des exportations
	function EvtExport($user='', $evts, $direction, $nomuser, $erreurs='')
	{
		$sql  = "INSERT INTO gickp_Evenement_Export (Date ,Utilisateur ,Evenement ,Mouvement ,Parametres ,Erreurs) VALUES (";
		$sql .= "CURRENT_TIMESTAMP, ";
		$sql .= "'".$user."', ";
		$sql .= "'".$evts."', ";
		$sql .= "'".$direction."', ";
		$sql .= "'".$nomuser."', ";
		$sql .= "'".$erreurs."'";
		$sql .= ") ";
		mysql_query($sql, $this->m_link) or die ("Erreur Insert Journal Export : ".$sql);
	}
	
	
	// GetUser
	function GetUserName($idUser)
	{
			if($idUser != '')
			{
				$sql  = "Select Identite From gickp_Utilisateur Where Code = '$idUser' ";
				
				$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetUserName<br>".$sql);
				if (mysql_num_rows($result) == 1)
				{
					$row = mysql_fetch_array($result);
					return $row['Identite'];
				}
			}
			return '';
	}

	// GetnumOrdre
	function GetnumOrdre($id)
	{
			if($idUser != '')
			{
				$sql  = "Select Numero_ordre From gickp_Matchs Where Id = '$id' ";
				$result = mysql_query($sql, $this->m_link) or die ("Erreur Select GetnumOrdre<br>".$sql);
				if (mysql_num_rows($result) == 1)
				{
					$row = mysql_fetch_array($result);
					return $row['Numero_ordre'];
				}
			}
			return '';
	}
	
	// AJOUT COSANDCO : 12 Septembre 2014 ...
	
	// Query 		$result = $myBdd->Query($sql);
    function Query($sql)
    {
        $result = mysql_query($sql, $this->m_link) or die ("Error Query : ".$sql);
        return $result;
    }
	
    // NumRows			$num_results = $myBdd->NumRows($result);
    function NumRows($result)
    {
        return mysql_num_rows($result);
    }

    // NumFields			$num_fields = $myBdd->NumFields($result);
    function NumFields($result)
    {
        return mysql_num_fields($result);
    }
    
    // FieldName			$field_name = $myBdd->FieldName($result);
    function FieldName($result, $field)
    {
        return mysql_field_name($result, $field);
    }

    // FetchArray		$row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);
    function FetchArray($result, $resulttype=MYSQL_ASSOC)
    {
        return mysql_fetch_array($result, $resulttype);
    }

     // FetchAssoc		$row = $myBdd->FetchAssoc($result);
    function FetchAssoc($result)
    {
        return mysql_fetch_assoc($result);
    }

   // FetchRow			$row = $myBdd->FetchRow($result);
    function FetchRow($result)
    {
        return mysql_fetch_row($result);
    }
	
    // RealEscapeString			$myBdd->RealEscapeString($codeCompet);
    function RealEscapeString($txt)
    {
        return mysql_real_escape_string($txt, $this->m_link);
    }

    // GetLastAutoIncrement
    function GetLastAutoIncrement()
    {
        $result = $this->Query('Select LAST_INSERT_ID()');
        $row = $this->FetchRow($result);
        return (int) $row[0];
    }

    // ShowColumnsSQL
    function ShowColumnsSQL($tableName, &$arrayColumns)
    {
        if ($arrayColumns == null){ 
			$arrayColumns = array();
		}
        $result = $this->Query("SHOW COLUMNS FROM $tableName");
        $num_results = $this->NumRows($result);
        for ($i=0;$i<$num_results;$i++)
        {
            array_push($arrayColumns, $this->FetchArray($result));
        }
        return $num_results;
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
		for ($i=0;$i<count($arrayColumns);$i++)
		{
			if ($arrayColumns[$i]['Field'] == $columnName)
				return $i;
		}
        return -1;
    }
	
    // IsNullSQL
    function IsNullSQL($value)
    {
        $typeValue = gettype($value);
        if ($typeValue == 'NULL') return true;
        if (($typeValue == 'string') && ($value == '')) return true;

        return false;
    }

    // ValueSQL
    function ValueSQL($value, $type, $null)
    {
        if ($this->IsNullSQL($value))
        {
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
        return "'".utyStringQuote($value)."'";
    }

    // SetSQL
    function SetSQL($tableName, &$record, $bIgnoreNull=true, &$arrayColumns=null)
    {
        if ($arrayColumns == null)
        {
            $arrayColumns = array();
            $this->ShowColumnsSQL($tableName, $arrayColumns);
        }

        $sql = '';

        // Uniquement les colonnes presentes dans $record et $arrayColumns ...
        $count = 0;
        foreach($record as $key => $value)
        {
            if ($bIgnoreNull && $this->IsNullSQL($value))
                continue;

            for ($j=0;$j<count($arrayColumns);$j++)
            {
                if ($arrayColumns[$j]['Field'] == $key)
                {
                    if ($count == 0)
                        $sql .= 'Set ';
                    else
                        $sql .= ',';
                    ++$count;

                    $sql .= $key.'=';
                    $sql .= $this->ValueSQL($value, $arrayColumns[$j]['Type'], $arrayColumns[$j]['Null']);

                    break;
                }				
            }
        }

        return $sql;
    }

    // InsertSQL
    function InsertSQL($tableName, &$record, $bIgnoreNull=true, &$arrayColumns=null)
    {
        return "Insert Into $tableName ".$this->SetSQL($tableName, $record, $bIgnoreNull, $arrayColumns);
    }

    // UpdateSQL
    function UpdateSQL($tableName, &$record, $bIgnoreNull=false, &$arrayColumns=null)
    {
        return "Update $tableName ".$this->SetSQL($tableName, $record, $bIgnoreNull, $arrayColumns);
    }

    // ReplaceSQL
    function ReplaceSQL($tableName, &$record, $bIgnoreNull=false, &$arrayColumns=null)
    {
        return "Replace Into $tableName ".$this->SetSQL($tableName, $record, $bIgnoreNull, $arrayColumns);
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
        for ($j=0;$j<$nbColumns;$j++)
        {
                if ($j >0) $sql .= ',';
                $sql .= $arrayColumns[$j]['Field'];
        }
        $sql .= ') Values ';

        for ($i=0;$i<$nbData;$i++)
        {
            $record = &$tData[$i];

            if ($i > 0) $sql .= ',';
            $sql .= '(';
            for ($j=0;$j<$nbColumns;$j++)
            {
                if ($j > 0) $sql .= ',';	

                $key = $arrayColumns[$j]['Field'];
                if (isset($record[$key]))
                    $sql .= $this->ValueSQL($record[$key], $arrayColumns[$j]['Type'], $arrayColumns[$j]['Null']);
                else
                    //					$sql .= $this->ValueSQL('', $arrayColumns[$j]['Type'], $arrayColumns[$j]['Null']);
                    $sql .= 'null';
            }
            $sql .= ')';
        }
    }

    // ColumnsSQL
    function ColumnsSQL($tableName, &$arrayColumns=null)
    {
        if ($arrayColumns == null)
        {
            $arrayColumns = array();
            $this->ShowColumnsSQL($tableName, $arrayColumns);
        }

        $sql = '';
        for ($j=0;$j<count($arrayColumns);$j++)
        {
            if ($sql != '')
                $sql .= ',';

            $sql .= $arrayColumns[$j]['Field'];
        }
        return $sql;
    }

    // ColumnsRecordSQL
    function ColumnsRecordSQL(&$record, $bIgnoreNull=false)
    {
        $sql = '';
        foreach($record as $key => $value)
        {
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
        $result = $this->Query($sql);
        $num_results = $this->NumRows($result);

        $arrayLoad = array();
        for ($i=0;$i<$num_results;$i++)
        {
            array_push($arrayLoad, $this->FetchArray($result));
        }
    }

    // LoadRecord
    function LoadRecord($sql, &$record)
    {
        $result = $this->Query($sql);
        if ($this->NumRows($result) >= 1)
        {
            $record = $this->FetchArray($result);
        }
        else
        {
            $record = array();
        }
    }
	
	// FIN AJOUT COSANDCO : 12 Septembre 2014 ...
}

