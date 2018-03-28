<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');
header( 'content-type: text/html; charset=utf-8' );

// Gestion de la Feuille de Match
class GestionDirectPitchs extends MyPage	 
{	

	function Load()
	{
		$inputText = '<form method="GET" action="DirectPitchs.php" name="formPitchs" enctype="multipart/form-data">
						Saison:<input type="text" name="saison" value="'.date('Y').'"/><br />
						Compétition:<input type="text" name="idCompet" /><br />
						ou Evénement:<input type="text" name="idEvt" /><br />
						Intervalle matchs:<input type="text" name="intervalle" value="40" />mn (maxi:60)<br />
						Date:<input type="text" name="dateP" /><br />
						Heure:<input type="text" name="heureP" /><br />
						Debug:<input type="text" name="debug" value="0" /><br />
						<input type="submit" value="Envoyer" />
					</form>'; 
		$saison = utyGetGet('saison', date('Y'));
		$idEvt = utyGetGet('idEvt', '');
		$idCompet = utyGetGet('idCompet', '');
		$intervalle = utyGetGet('intervalle', 40);
		$intervalle -= 5;
		$debug = utyGetGet('debug', 0);
		$dateP = utyGetGet('dateP', '');
		$heureP = utyGetGet('heureP', '');
		if($idCompet == '' && $idEvt == '')
			die ('Sélectionnez une compétition ou un événement<br /><br />'.$inputText);
		$myBdd = new MyBdd();
		// Chargement des matchs à afficher
		$sql  = "SELECT c.Code, c.Libelle nomCompet, c.Soustitre, c.Soustitre2, m.*, j.Id, j.Code_competition, j.Code_saison, ";
		$sql .= "ce1.Libelle equipeA, ce1.Code_club clubA, ce2.Libelle equipeB, ce2.Code_club clubB ";
		$sql .= "FROM gickp_Matchs m left outer join gickp_Competitions_Equipes ce1 on (ce1.Id = m.Id_equipeA) ";
		$sql .= "left outer join gickp_Competitions_Equipes ce2 on (ce2.Id = m.Id_equipeB), ";
		$sql .= "gickp_Journees j, gickp_Competitions c";
		if($idEvt != '')
			$sql .= ", gickp_Evenement_Journees ej ";
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
		if($dateP != ''){
			$sql .= "AND m.Date_match = '".$dateP."' ";
		}else{
			$sql .= "AND m.Date_match = CURDATE() ";
		}
		if($heureP != ''){
			$sql .= "AND '".$heureP."' > CONVERT(SUBTIME(TIME(m.Heure_match), '00:05:00') USING utf8)  ";
			$sql .= "AND '".$heureP."' < CONVERT(ADDTIME(TIME(m.Heure_match), '00:".$intervalle.":00') USING utf8) ";
		}else{
			$sql .= "AND CURTIME() > SUBTIME(TIME(m.Heure_match), '00:05:00')  ";
			$sql .= "AND CURTIME() < ADDTIME(TIME(m.Heure_match), '00:".$intervalle.":00') ";
		}
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select : <br />".$sql);
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
			if(!isset($lastCompetEvt))
				$lastCompetEvt = $row['Code'];
			if(!isset($lastSaisonEvt))
				$lastSaisonEvt = $row['Code_saison'];
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
		// Chargement des infos de l'évènement ou de la compétition
		$titreEvenementCompet = '';
		if ($idEvt != '')
		{
			$libelleEvenement = $myBdd->GetEvenementLibelle($idEvt);
			$titreEvenementCompet = 'Evénement (Event) : '.$libelleEvenement;
			$arrayCompetition = $myBdd->GetCompetition($lastCompetEvt, $lastSaisonEvt);
		}
		else
		{
			$arrayCompetition = $myBdd->GetCompetition($idCompet, $saison);
			if($arrayCompetition['Titre_actif'] == 'O')
				$titreEvenementCompet = $arrayCompetition['Libelle'];
			else
				$titreEvenementCompet = $arrayCompetition['Soustitre'];
			if($arrayCompetition['Soustitre2'] != '')
				$titreEvenementCompet .= ' - '.$arrayCompetition['Soustitre2'];
		}
		$logo = str_replace('http://www.kayak-polo.info/','',$arrayCompetition['LogoLink']);
		$sponsor = str_replace('http://www.kayak-polo.info/','',$arrayCompetition['SponsorLink']);
		/*************************************************************************************/

?>
<!doctype html>
<html lang="fr">
	<head>
		<!--
		debug = 0
		dateP = 'YYYY-mm-dd'
		heureP = '00:00:00'
		-->
		<meta http-equiv="refresh" content="10">
		<meta charset="utf-8">
		<title>Live : <?php echo $idCompet; ?></title>
		<link href="admin/v2/jquery-ui.min.css" rel="stylesheet">
		<link href="admin/v2/jquery.dataTables.css" rel="stylesheet">
		<style>
			html, body { height: 100%; }
			body {font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-weight: bold; background-color: #BCBCB8;}
			#container {
				position: relative;
				min-height: 100%;
			}
			#footer {
				position: absolute;
				bottom: 0;
				width: 100%;
				text-align: center;
			}			
			/*	img, span, div {border: 1px dotted grey; }	*/
			.pitchs1 {width: 100%; background-color: white; line-height: 80px; height: 80px;}
			.pitchs2 {width: 100%; height: 60px;}
			.pitchs {width: 100%; min-height: 80%; margin: auto; }
			.pitch {float: left; width: 49%; min-height: 50%;  }
			#logoKPI {float: left; height: 70px; padding: 5px; }
			#logoFFCK {float: right; height: 70px; padding: 5px; }
			#logoCompet {float: left; height: 70px; padding: 5px; }
			#sponsorCompet {height: 70px; margin: auto; }
			#titreCompet {display: block; float: left; line-height: 80px; padding: 0 10px; }
			#dateCompet {display: block; float: right; line-height: 80px; padding: 0 10px; }
			#direct {color: white; background: url("https://www.kayak-polo.info/admin/v2/bg_score.png") repeat-x 50% 50%;
				border-radius: 5px; font-family: Trebuchet MS, Verdana, sans-serif; float: left; 
				text-align: center; text-shadow: 2px 1px 1px #BBBBBB; font-size: 1.6em; padding: 3px 10px; margin: 10px;}
			#ejs_heure {float: right; font-family: Trebuchet MS, Verdana, sans-serif; font-size: 1.6em;
				color: white; background: url("https://www.kayak-polo.info/admin/v2/bg_score.png") repeat-x 50% 50%;
				border-radius: 5px; padding: 3px 10px; margin: 10px; text-align: center; text-shadow: 2px 1px 1px #BBBBBB;
				
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
		
		<script type="text/javascript" src="admin/v2/jquery-1.11.0.min.js"></script>
		<script type="text/javascript" src="admin/v2/jquery-ui-1.10.4.custom.min.js"></script>
		<script type="text/javascript" src="admin/v2/jquery.dataTables.min.js"></script>
		<script>
			/*
				SCRIPT TROUVE SUR L'EDITEUR JAVASCRIPT 	http://www.editeurjavascript.com
			*/
			function HeureCheckEJS()
				{
				krucial = new Date();
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
		<div id="container">
			<div id="content">
				<div class="pitchs1">
					<?php
						// logo
						if($arrayCompetition['Kpi_ffck_actif'] == 'O')
						{
							echo '<img id="logoKPI" src="css/banniere1.jpg" />';
							echo '<img id="logoFFCK" src="img/ffck2.jpg" />';
						}
						if($arrayCompetition['Logo_actif'] == 'O' && $logo != '')  //&& file_exists($logo)
						{
							echo '<img id="logoCompet" src="'.$logo.'" />';
						}
						$titreDate = "Saison (Season) ".$saison;
						echo '<span id="titreCompet">'.$titreEvenementCompet.'</span>';
						//echo '<span id="dateCompet">'.$titreDate.'</span>';
						// titres
					?>
				</div>
				<div class="pitchs2">
					<span id="direct">Matchs en direct - Live games</span>
					<div id="ejs_heure">Initialisation...</div>
				</div>
				<br />
				<?php 
					if($dateP != '') echo '<i>'.utyDateUsToFr($dateP).'</i>&nbsp;&nbsp;&nbsp;';
					if($heureP != '') echo '<i>'.$heureP.'</i>';
				?>
				<br />
				<div class="pitchs">
					<div class="pitch">
						<?php if(isset($array1[0]) && $array1[0]['Id'] != '') { ?>
							<table class="incrust_table">
								<thead>
									<tr>
										<th colspan="3" title="<?php echo 'N° '.$array1[0]['Numero_ordre'].' - '.$array1[0]['Heure_match']; ?>">
											Terrain 1 : <?php if($array1[0]['Soustitre2'] != '') {echo $array1[0]['Soustitre2']; }else{ echo $array1[0]['Code'];} ?>
										</th>
									</tr>
								</thead>
								<tr>
									<td rowspan="2" class="incrust_equipe"><?php echo $array1[0]['equipeA'] ?><br /><img src="img/Pays/<?php echo $array1[0]['paysA'] ?>.png" height="20"></td>
									<td class="incrust_score"><?php echo $array1[0]['Score'] ?></td>
									<td rowspan="2" class="incrust_equipe"><?php echo $array1[0]['equipeB'] ?><br /><img src="img/Pays/<?php echo $array1[0]['paysB'] ?>.png" height="20"></td>
								</tr>
								<tr>
									<td class="incrust_situ statut<?php echo $array1[0]['Statut'] ?>"><?php echo $array1[0]['Periode'] ?></td>
								</tr>
							</table>
						<?php } ?>
					</div>
					<div class="pitch">
						<?php if(isset($array2[0]) && $array2[0]['Id'] != '') { ?>
							<table class="incrust_table">
								<thead>
									<tr>
										<th colspan="3" title="<?php echo 'N° '.$array2[0]['Numero_ordre'].' - '.$array2[0]['Heure_match']; ?>">
											Terrain 2 : <?php if($array2[0]['Soustitre2'] != '') {echo $array2[0]['Soustitre2']; }else{ echo $array2[0]['Code'];} ?>
										</th>
									</tr>
								</thead>
								<tr>
									<td rowspan="2" class="incrust_equipe"><?php echo $array2[0]['equipeA'] ?><br /><img src="img/Pays/<?php echo $array2[0]['paysA'] ?>.png" height="20"></td>
									<td class="incrust_score"><?php echo $array2[0]['Score'] ?></td>
									<td rowspan="2" class="incrust_equipe"><?php echo $array2[0]['equipeB'] ?><br /><img src="img/Pays/<?php echo $array2[0]['paysB'] ?>.png" height="20"></td>
								</tr>
								<tr>
									<td class="incrust_situ statut<?php echo $array2[0]['Statut'] ?>"><?php echo $array2[0]['Periode'] ?></td>
								</tr>
							</table>
						<?php } ?>
					</div>
					<div class="pitch">
						<?php if(isset($array3[0]) && $array3[0]['Id'] != '') { ?>
							<table class="incrust_table">
								<thead>
									<tr>
										<th colspan="3" title="<?php echo 'N° '.$array3[0]['Numero_ordre'].' - '.$array3[0]['Heure_match']; ?>">
											Terrain 3 : <?php if($array3[0]['Soustitre2'] != '') {echo $array3[0]['Soustitre2']; }else{ echo $array3[0]['Code'];} ?>
										</th>
									</tr>
								</thead>
								<tr>
									<td rowspan="2" class="incrust_equipe"><?php echo $array3[0]['equipeA'] ?><br /><img src="img/Pays/<?php echo $array3[0]['paysA'] ?>.png" height="20"></td>
									<td class="incrust_score"><?php echo $array3[0]['Score'] ?></td>
									<td rowspan="2" class="incrust_equipe"><?php echo $array3[0]['equipeB'] ?><br /><img src="img/Pays/<?php echo $array3[0]['paysB'] ?>.png" height="20"></td>
								</tr>
								<tr>
									<td class="incrust_situ statut<?php echo $array3[0]['Statut'] ?>"><?php echo $array3[0]['Periode'] ?></td>
								</tr>
							</table>
						<?php } ?>
					</div>
					<div class="pitch">
						<?php if(isset($array4[0]) && $array4[0]['Id'] != '') { ?>
							<table class="incrust_table">
								<thead>
									<tr>
										<th colspan="3" title="<?php echo 'N° '.$array4[0]['Numero_ordre'].' - '.$array4[0]['Heure_match']; ?>">
											Terrain 4 : <?php if($array4[0]['Soustitre2'] != '') {echo $array4[0]['Soustitre2']; }else{ echo $array4[0]['Code'];} ?>
										</th>
									</tr>
								</thead>
								<tr>
									<td rowspan="2" class="incrust_equipe"><?php echo $array4[0]['equipeA'] ?><br /><img src="img/Pays/<?php echo $array4[0]['paysA'] ?>.png" height="20"></td>
									<td class="incrust_score"><?php echo $array4[0]['Score'] ?></td>
									<td rowspan="2" class="incrust_equipe"><?php echo $array4[0]['equipeB'] ?><br /><img src="img/Pays/<?php echo $array4[0]['paysB'] ?>.png" height="20"></td>
								</tr>
								<tr>
									<td class="incrust_situ statut<?php echo $array4[0]['Statut'] ?>"><?php echo $array4[0]['Periode'] ?></td>
								</tr>
							</table>
						<?php } ?>
					</div>
					<div class="pitch">
						<?php if(isset($array5[0]) && $array5[0]['Id'] != '') { ?>
							<table class="incrust_table">
								<thead>
									<tr>
										<th colspan="3" title="<?php echo 'N° '.$array5[0]['Numero_ordre'].' - '.$array5[0]['Heure_match']; ?>">
											Terrain 5 : <?php if($array5[0]['Soustitre2'] != '') {echo $array5[0]['Soustitre2']; }else{ echo $array5[0]['Code'];} ?>
										</th>
									</tr>
								</thead>
								<tr>
									<td rowspan="2" class="incrust_equipe"><?php echo $array5[0]['equipeA'] ?><br /><img src="img/Pays/<?php echo $array5[0]['paysA'] ?>.png" height="20"></td>
									<td class="incrust_score"><?php echo $array5[0]['Score'] ?></td>
									<td rowspan="2" class="incrust_equipe"><?php echo $array5[0]['equipeB'] ?><br /><img src="img/Pays/<?php echo $array5[0]['paysB'] ?>.png" height="20"></td>
								</tr>
								<tr>
									<td class="incrust_situ statut<?php echo $array5[0]['Statut'] ?>"><?php echo $array5[0]['Periode'] ?></td>
								</tr>
							</table>
						<?php } ?>
					</div>
					<div class="pitch">
						<?php if(isset($array6[0]) && $array6[0]['Id'] != '') { ?>
							<table class="incrust_table">
								<thead>
									<tr>
										<th colspan="3" title="<?php echo 'N° '.$array6[0]['Numero_ordre'].' - '.$array6[0]['Heure_match']; ?>">
											Terrain 6 : <?php if($array6[0]['Soustitre2'] != '') {echo $array6[0]['Soustitre2']; }else{ echo $array6[0]['Code'];} ?>
										</th>
									</tr>
								</thead>
								<tr>
									<td rowspan="2" class="incrust_equipe"><?php echo $array6[0]['equipeA'] ?><br /><img src="img/Pays/<?php echo $array6[0]['paysA'] ?>.png" height="20"></td>
									<td class="incrust_score"><?php echo $array6[0]['Score'] ?></td>
									<td rowspan="2" class="incrust_equipe"><?php echo $array6[0]['equipeB'] ?><br /><img src="img/Pays/<?php echo $array6[0]['paysB'] ?>.png" height="20"></td>
								</tr>
								<tr>
									<td class="incrust_situ statut<?php echo $array6[0]['Statut'] ?>"><?php echo $array6[0]['Periode'] ?></td>
								</tr>
							</table>
						<?php } ?>
					</div>
				</div>
				<div id="footer">
					<?php
						if($arrayCompetition['Sponsor_actif'] == 'O' && $sponsor != '')  //&& file_exists($sponsor)
						{
							echo '<img id="sponsorCompet" src="'.$sponsor.'" />';
						}
					?>
				</div>				
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

