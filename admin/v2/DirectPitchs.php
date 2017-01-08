<?php

include_once('../../commun/MyPage.php');
include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');

// Gestion de la Feuille de Match
class GestionDirectPitchs extends MyPage	 
{	

	function Load()
	{
        $myBdd = new MyBdd();
		$inputText = '<form method="GET" action="DirectPitchs.php" name="formPitchs" enctype="multipart/form-data">
						Saison:<input type="text" name="saison" value="'.date('Y').'"/><br />
						Compétition:<input type="text" name="idCompet" /><br />
						ou Evénement:<input type="text" name="idEvt" /><br />
						Intervalle matchs:<input type="text" name="intervalle" value="40" />mn (maxi:60)<br />
						<input type="submit" value="Envoyer" />
					</form>'; 
		$saison = utyGetGet('saison', date('Y'));
		$idEvt = (int)utyGetGet('idEvt', '');
		$idCompet = (int)utyGetGet('idCompet', '');
		$intervalle = (int)utyGetGet('intervalle', 40);
		$intervalle -= 5;
		$debug = (int)utyGetGet('debug', 0);
		$datePitch = $myBdd->RealEscapeString(trim(utyGetGet('datePitch', '')));
		$heurePitch = $myBdd->RealEscapeString(trim(utyGetGet('heurePitch', '')));
        
		if ($idCompet == '' && $idEvt == '') {
            die('Sélectionnez une compétition ou un événement<br /><br />' . $inputText);
        }
		// Chargement des matchs à afficher
		$sql  = "SELECT c.Code, c.Libelle nomCompet, c.Soustitre, c.Soustitre2, m.*, j.Id, j.Code_competition, j.Code_saison, ";
		$sql .= "ce1.Libelle equipeA, ce1.Code_club clubA, ce2.Libelle equipeB, ce2.Code_club clubB ";
		$sql .= "FROM gickp_Matchs m left outer join gickp_Competitions_Equipes ce1 on (ce1.Id = m.Id_equipeA) ";
		$sql .= "left outer join gickp_Competitions_Equipes ce2 on (ce2.Id = m.Id_equipeB), ";
		$sql .= "gickp_Journees j, gickp_Competitions c";
		if ($idEvt != '') {
            $sql .= ", gickp_Evenement_Journees ej ";
        }
        $sql .= " WHERE m.Id_journee = j.Id ";
		$sql .= "AND j.Code_competition = c.Code ";
		$sql .= "AND j.Code_saison = c.Code_saison ";
		$sql .= "AND j.Code_saison = ".$saison." ";
		if($idEvt != ''){
			$sql .= "AND j.Id = ej.Id_journee ";
			$sql .= "AND ej.Id_evenement = ".$idEvt." ";
		}elseif($idCompet != ''){
			$sql .= "AND j.Code_competition = '".$idCompet."' ";
		}
		if($datePitch != ''){
			$sql .= "AND m.Date_match = '".$datePitch."' ";
		}else{
			$sql .= "AND m.Date_match = CURDATE() ";
		}
		if($heurePitch != ''){
			$sql .= "AND '".$heurePitch."' > SUBTIME(TIME(m.Heure_match), '00:05:00')  ";
			$sql .= "AND '".$heurePitch."' < ADDTIME(TIME(m.Heure_match), '00:".$intervalle.":00') ";
		}else{
			$sql .= "AND CURTIME() > SUBTIME(TIME(m.Heure_match), '00:05:00')  ";
			$sql .= "AND CURTIME() < ADDTIME(TIME(m.Heure_match), '00:".$intervalle.":00') ";
		}
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select<br />".$sql);
		$num_results = mysql_num_rows($result);
		$array1 = array();
		$array2 = array();
		$array3 = array();
		$array4 = array();
		$array5 = array();
		$array6 = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			// drapeaux
			$row['paysA'] = substr($row['clubA'], 0, 3);
			if(is_numeric($row['paysA'][0]) || is_numeric($row['paysA'][1]) || is_numeric($row['paysA'][2]))
				$row['paysA'] = 'FRA';
			$row['paysB'] = substr($row['clubB'], 0, 3);
			if(is_numeric($row['paysB'][0]) || is_numeric($row['paysB'][1]) || is_numeric($row['paysB'][2]))
				$row['paysB'] = 'FRA';
			// période, statut, validation
			if($row['Statut'] == 'ATT'){
				$row['Periode'] = 'En attente';
				$row['Score'] = $row['Heure_match'];
			}elseif($row['Statut'] == 'END'){
				$row['Periode'] = 'Score prov.';
				$row['Score'] = $row['ScoreDetailA'].' - '.$row['ScoreDetailB'];
			}else{
				$row['Score'] = $row['ScoreDetailA'].' - '.$row['ScoreDetailB'];
				switch($row['Periode']){
					case 'M1' :
						$row['Periode'] = '1ère période';
						break;
					case 'M2' :
						$row['Periode'] = '2de période';
						break;
					case 'P1' :
						$row['Periode'] = '1ère prolongation';
						break;
					case 'P2' :
						$row['Periode'] = '2de prolongation';
						break;
					case 'TB' :
						$row['Periode'] = 'Tirs au but';
						break;
				}
			}
			if($row['Validation'] == 'O'){
				$row['Periode'] = 'Terminé';
				$row['Statut'] = 'OK';
				$row['Score'] = $row['ScoreA'].' - '.$row['ScoreB'];
			}
			// répartition par terrain
			$terrain = $row['Terrain'];
			if($terrain == 1)
				array_push($array1, $row);
			if($terrain == 2)
				array_push($array2, $row);
			if($terrain == 3)
				array_push($array3, $row);
			if($terrain == 4)
				array_push($array4, $row);
			if($terrain == 5)
				array_push($array5, $row);
			if($terrain == 6)
				array_push($array6, $row);
		}
		//print_r($array4);
		/*************************************************************************************/

?>
<!doctype html>
<html lang="fr">
	<head>
		<!--
		debug = 0
		datePitch = 'YYYY-mm-dd'
		heurePitch = '00:00:00'
		-->
		<meta http-equiv="refresh" content="10">
		<meta charset="utf-8">
		<title>Compétition : <?php if($idCompet != '' && $array1[0]['Soustitre'] != ''){ echo $array1[0]['Soustitre'];} else {echo $array1[0]['nomCompet'];} ; ?></title>
		<link href="jquery-ui.min.css" rel="stylesheet">
		<link href="jquery.dataTables.css" rel="stylesheet">
		<style>
			body {font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-weight: bold; background-color: #BCBCB8;}
			html {
			  height: 100%;
			}
			body {
			  min-height: 100%;
			  margin: 0;
			  padding: 0;
			}
			.pitchs {width: 100%; min-height: 80%; margin: auto; }
			.pitch {float: left; width: 49%; min-height: 50%;  }
			#logoFFCK {float: left; height: 60px; }
			#ejs_heure {float: right; font-family: Trebuchet MS, Verdana, sans-serif; font-weight: normal; font-size: 1.6em;
				color: white; background: url("bg_score.png") repeat-x 50% 50%;
				border-radius: 5px; padding: 3px; margin: 10px; text-align: center; 
				width: 120px;
				}
			#incrustation {
				text-align: center;
			}
			.incrust_table {
				width: 100%;
				height: 100%;
				font-family: Verdana, Arial, sans-serif;
				font-size: 9.0pt;
				text-align: center;
				margin: auto;
				padding: 30px;
			}
			.incrust_table thead th {
				font-size: 12.0pt;
				padding: 5px;
				font-style: italic;
			}			
			.incrust_equipe {
				width: 275px;
				height: 60px;
				font-size: 11.0pt;
				font-weight: bold;
				color: #222222;
				text-shadow: 0 0.063em 0 #FFFFFF;
			}
			.incrust_score {
				min-width: 100px;
				height: 34px;
				background: #111111;
				color: #EEEEEE;
				font-size: 14.0pt;
				font-weight: bold;
				border: 4px outset #555555;
			}
			.incrust_situ {
				width: 100px;
				height: 14px;
				background: #0000BB;
				color: #EEEEEE;
				border: 3px outset #3333EE;
			}
			.numMatch {width: 100px; text-align: center; }
			.statutATT {background: #555555; border: 3px outset #888888;}
			.statutON {background: #0000BB; border: 3px outset #3333EE;}
			.statutEND {background: #555555; border: 3px outset #888888;}
			.statutOK {background: #00BB00; border: 3px outset #33EE33;}
		</style>
		
		<script type="text/javascript" src="jquery-1.11.0.min.js"></script>
		<script type="text/javascript" src="jquery-ui-1.10.4.custom.min.js"></script>
		<script type="text/javascript" src="jquery.dataTables.min.js"></script>
		<script>
			/*
				SCRIPT TROUVE SUR L'EDITEUR JAVASCRIPT 	http://www.editeurjavascript.com
			*/
			function HeureCheckEJS()
				{
				krucial = new Date;
				heure = krucial.getHours();
				min = krucial.getMinutes();
				sec = krucial.getSeconds();
				jour = krucial.getDate();
				mois = krucial.getMonth()+1;
				annee = krucial.getFullYear();
				if (sec < 10)
					sec0 = "0";
				else
					sec0 = "";
				if (min < 10)
					min0 = "0";
				else
					min0 = "";
				if (heure < 10)
					heure0 = "0";
				else
					heure0 = "";
				DinaHeure = heure0 + heure + ":" + min0 + min + ":" + sec0 + sec;
				which = DinaHeure
				if (document.getElementById){
					document.getElementById("ejs_heure").innerHTML=which;
				}
				setTimeout("HeureCheckEJS()", 1000)
				}
			window.onload = HeureCheckEJS;
		</script>
</head>
	<body>
		<div class="pitchs">
			<img id="logoFFCK" src="../../img/FFCK1.gif" />
			<div id="ejs_heure">Initialisation...</div>
		</div>
		<br />
		<br />
		<br />
		<br />
		<div class="pitchs">
			<div class="pitch">
				<?php if($array1[0]['Id'] != '') { ?>
					<table class="incrust_table">
						<thead>
							<tr>
								<th colspan="3">Terrain 1 : <?php if($array1[0]['Soustitre2'] != '') {echo $array1[0]['Soustitre2']; }else{ echo $array1[0]['Code'];} ?>
									<br /><?php if($debug) echo $array1[0]['Numero_ordre'].' - '.$array1[0]['Heure_match']; ?>
								</th>
							</tr>
						</thead>
						<tr>
							<td rowspan="2" class="incrust_equipe"><?php echo $array1[0]['equipeA'] ?><br /><img src="../../img/Pays/<?php echo $array1[0]['paysA'] ?>.png" height="20"></td>
							<td class="incrust_score"><?php echo $array1[0]['Score'] ?></td>
							<td rowspan="2" class="incrust_equipe"><?php echo $array1[0]['equipeB'] ?><br /><img src="../../img/Pays/<?php echo $array1[0]['paysB'] ?>.png" height="20"></td>
						</tr>
						<tr>
							<td class="incrust_situ statut<?php echo $array1[0]['Statut'] ?>"><?php echo $array1[0]['Periode'] ?></td>
						</tr>
					</table>
				<?php } ?>
			</div>
			<div class="pitch">
				<?php if($array2[0]['Id'] != '') { ?>
					<table class="incrust_table">
						<thead>
							<tr>
								<th colspan="3">Terrain 2 : <?php if($array2[0]['Soustitre2'] != '') {echo $array2[0]['Soustitre2']; }else{ echo $array2[0]['Code'];} ?>
									<br /><?php if($debug) echo $array2[0]['Numero_ordre'].' - '.$array2[0]['Heure_match']; ?>
								</th>
							</tr>
						</thead>
						<tr>
							<td rowspan="2" class="incrust_equipe"><?php echo $array2[0]['equipeA'] ?><br /><img src="../../img/Pays/<?php echo $array2[0]['paysA'] ?>.png" height="20"></td>
							<td class="incrust_score"><?php echo $array2[0]['Score'] ?></td>
							<td rowspan="2" class="incrust_equipe"><?php echo $array2[0]['equipeB'] ?><br /><img src="../../img/Pays/<?php echo $array2[0]['paysB'] ?>.png" height="20"></td>
						</tr>
						<tr>
							<td class="incrust_situ statut<?php echo $array2[0]['Statut'] ?>"><?php echo $array2[0]['Periode'] ?></td>
						</tr>
					</table>
				<?php } ?>
			</div>
			<div class="pitch">
				<?php if($array3[0]['Id'] != '') { ?>
					<table class="incrust_table">
						<thead>
							<tr>
								<th colspan="3">Terrain 3 : <?php if($array3[0]['Soustitre2'] != '') {echo $array3[0]['Soustitre2']; }else{ echo $array3[0]['Code'];} ?>
									<br /><?php if($debug) echo $array3[0]['Numero_ordre'].' - '.$array3[0]['Heure_match']; ?>
								</th>
							</tr>
						</thead>
						<tr>
							<td rowspan="2" class="incrust_equipe"><?php echo $array3[0]['equipeA'] ?><br /><img src="../../img/Pays/<?php echo $array3[0]['paysA'] ?>.png" height="20"></td>
							<td class="incrust_score"><?php echo $array3[0]['Score'] ?></td>
							<td rowspan="2" class="incrust_equipe"><?php echo $array3[0]['equipeB'] ?><br /><img src="../../img/Pays/<?php echo $array3[0]['paysB'] ?>.png" height="20"></td>
						</tr>
						<tr>
							<td class="incrust_situ statut<?php echo $array3[0]['Statut'] ?>"><?php echo $array3[0]['Periode'] ?></td>
						</tr>
					</table>
				<?php } ?>
			</div>
			<div class="pitch">
				<?php if($array4[0]['Id'] != '') { ?>
					<table class="incrust_table">
						<thead>
							<tr>
								<th colspan="3">Terrain 4 : <?php if($array4[0]['Soustitre2'] != '') {echo $array4[0]['Soustitre2']; }else{ echo $array4[0]['Code'];} ?>
									<br /><?php if($debug) echo $array4[0]['Numero_ordre'].' - '.$array4[0]['Heure_match']; ?>
								</th>
							</tr>
						</thead>
						<tr>
							<td rowspan="2" class="incrust_equipe"><?php echo $array4[0]['equipeA'] ?><br /><img src="../../img/Pays/<?php echo $array4[0]['paysA'] ?>.png" height="20"></td>
							<td class="incrust_score"><?php echo $array4[0]['Score'] ?></td>
							<td rowspan="2" class="incrust_equipe"><?php echo $array4[0]['equipeB'] ?><br /><img src="../../img/Pays/<?php echo $array4[0]['paysB'] ?>.png" height="20"></td>
						</tr>
						<tr>
							<td class="incrust_situ statut<?php echo $array4[0]['Statut'] ?>"><?php echo $array4[0]['Periode'] ?></td>
						</tr>
					</table>
				<?php } ?>
			</div>
			<div class="pitch">
				<?php if($array5[0]['Id'] != '') { ?>
					<table class="incrust_table">
						<thead>
							<tr>
								<th colspan="3">Terrain 5 : <?php if($array5[0]['Soustitre2'] != '') {echo $array5[0]['Soustitre2']; }else{ echo $array5[0]['Code'];} ?>
									<br /><?php if($debug) echo $array5[0]['Numero_ordre'].' - '.$array5[0]['Heure_match']; ?>
								</th>
							</tr>
						</thead>
						<tr>
							<td rowspan="2" class="incrust_equipe"><?php echo $array5[0]['equipeA'] ?><br /><img src="../../img/Pays/<?php echo $array5[0]['paysA'] ?>.png" height="20"></td>
							<td class="incrust_score"><?php echo $array5[0]['Score'] ?></td>
							<td rowspan="2" class="incrust_equipe"><?php echo $array5[0]['equipeB'] ?><br /><img src="../../img/Pays/<?php echo $array5[0]['paysB'] ?>.png" height="20"></td>
						</tr>
						<tr>
							<td class="incrust_situ statut<?php echo $array5[0]['Statut'] ?>"><?php echo $array5[0]['Periode'] ?></td>
						</tr>
					</table>
				<?php } ?>
			</div>
			<div class="pitch">
				<?php if($array6[0]['Id'] != '') { ?>
					<table class="incrust_table">
						<thead>
							<tr>
								<th colspan="3">Terrain 6 : <?php if($array6[0]['Soustitre2'] != '') {echo $array6[0]['Soustitre2']; }else{ echo $array6[0]['Code'];} ?>
									<br /><?php if($debug) echo $array6[0]['Numero_ordre'].' - '.$array6[0]['Heure_match']; ?>
								</th>
							</tr>
						</thead>
						<tr>
							<td rowspan="2" class="incrust_equipe"><?php echo $array6[0]['equipeA'] ?><br /><img src="../../img/Pays/<?php echo $array6[0]['paysA'] ?>.png" height="20"></td>
							<td class="incrust_score"><?php echo $array6[0]['Score'] ?></td>
							<td rowspan="2" class="incrust_equipe"><?php echo $array6[0]['equipeB'] ?><br /><img src="../../img/Pays/<?php echo $array6[0]['paysB'] ?>.png" height="20"></td>
						</tr>
						<tr>
							<td class="incrust_situ statut<?php echo $array6[0]['Statut'] ?>"><?php echo $array6[0]['Periode'] ?></td>
						</tr>
					</table>
				<?php } ?>
			</div>
		</div>		
		
	</body>
</html>

<?php

	}

	function GestionDirectPitchs()
	{			
		MyPage::MyPage();
		$this->Load();
	}
}		  	

$page = new GestionDirectPitchs();


?>