<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion de la Feuille de Match
class GestionMatchDetail extends MyPageSecure	 
{	

	function InitTitulaireEquipe($numEquipe, $idMatch, $idEquipe, $bdd)
	{
		$sql = "Select Count(*) Nb From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = '$numEquipe' ";
		$result = mysql_query($sql, $bdd->m_link) or die ("Erreur Select");

		if (mysql_num_rows($result) != 1)
			return;
			
		$row = mysql_fetch_array($result);
		if ((int) $row['Nb'] > 0)
			return;
			
		$sql  = "Replace Into gickp_Matchs_Joueurs ";
		$sql .= "Select $idMatch, Matric, Numero, '$numEquipe', Capitaine From gickp_Competitions_Equipes_Joueurs ";
		$sql .= "Where Id_equipe = $idEquipe ";
		$sql .= "AND Capitaine <> 'X' ";
		$sql .= "AND Capitaine <> 'A' ";
		mysql_query($sql, $bdd->m_link) or die ("Erreur Replace InitTitulaireEquipe");
 	}
	
	function Load()
	{
		$idMatch = utyGetGet('idMatch', -1);
		$langue = parse_ini_file("../commun/MyLang.ini", true);
		$version = utyGetSession('lang', 'fr');
		$version = utyGetGet('lang', $version);
		$_SESSION['lang'] = $version;
		$lang = $langue[$version];
        
		if( $idMatch < 1 ) {
            header("Location: SelectFeuille.php?target=FeuilleMarque2.php");
			exit;
        }
        
		$myBdd = new MyBdd();
		// Contrôle autorisation journée
		$sql  = "SELECT m.*, m.Statut statutMatch, m.Periode periodeMatch, m.Type typeMatch, m.Heure_fin, j.*, j.Code_saison saison, c.*, "
                . "m.Type Type_match, m.Validation Valid_match, m.Publication PubliMatch, ce1.Libelle equipeA, ce1.Code_club clubA, "
                . "ce2.Libelle equipeB, ce2.Code_club clubB "
                . "FROM gickp_Matchs m left outer join gickp_Competitions_Equipes ce1 on (ce1.Id = m.Id_equipeA) "
                . "left outer join gickp_Competitions_Equipes ce2 on (ce2.Id = m.Id_equipeB), gickp_Journees j, gickp_Competitions c "
                . "WHERE m.Id = $idMatch "
                . "AND m.Id_journee = j.Id "
                . "AND j.Code_competition = c.Code "
                . "AND j.Code_saison = c.Code_saison ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select<br />".$sql);
		$row = mysql_fetch_array($result);
		$saison = $row['saison'];
		$statutMatch = $row['statutMatch'];
		$publiMatch = $row['PubliMatch'];
		$periodeMatch = $row['periodeMatch'];
		$typeMatch = $row['typeMatch'];
		$heure_fin = $row['Heure_fin'];
        $inputText = ' <a href="SelectFeuille.php">' . $lang['Retour'] . '</a>';
		if (!isset($row['saison'])) {
            die( $lang['Numero_non_valide'] . $inputText );
        }
		if ($row['Id_equipeA'] < 1 || $row['Id_equipeB'] < 1) {
            die($lang['Equipes_non_affectees'] . $inputText);
        }
        if($row['ScoreA'] == '')
			$row['ScoreA'] = 0;
		if($row['ScoreB'] == '')
			$row['ScoreB'] = 0;
		if (!utyIsAutorisationJournee($row['Id_journee'])){
			$readonly = 'O';
			$verrou = 'O';
		}elseif ($row['Valid_match']=='O'){
			$readonly = '';
			$verrou = 'O';
		}else{
			$readonly = '';
			$verrou = '';
		}
		// drapeaux
		$paysA = substr($row['clubA'], 0, 3);
		if(is_numeric($paysA[0]) || is_numeric($paysA[1]) || is_numeric($paysA[2]))
			$paysA = 'FRA';
		$paysB = substr($row['clubB'], 0, 3);
		if(is_numeric($paysB[0]) || is_numeric($paysB[1]) || is_numeric($paysB[2]))
			$paysB = 'FRA';
		
		// Compo équipe A
			if ($row['Id_equipeA'] >= 1)
				$this->InitTitulaireEquipe('A', $idMatch, $row['Id_equipeA'], $myBdd);
			$sql3  = "Select a.Matric, a.Numero, a.Capitaine, IF(a.Capitaine = 'E', 1, 0) Entraineur, b.Matric, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Origine, c.Matric Matric_titulaire ";
			$sql3 .= "From gickp_Matchs_Joueurs a ";
			$sql3 .= "Left Outer Join gickp_Competitions_Equipes_Joueurs c On (c.Id_equipe = ".$row['Id_equipeA']." And c.Matric = a.Matric), "; 
			$sql3 .= "gickp_Liste_Coureur b ";
			$sql3 .= "Where a.Matric = b.Matric ";
			$sql3 .= "And a.Id_match = $idMatch ";
			$sql3 .= "And a.Equipe = 'A' ";
			$sql3 .= "Order By Entraineur, Numero, Nom, Prenom ";	 
			$result3 = mysql_query($sql3, $myBdd->m_link) or die ("Erreur Load");
			$num_results3 = mysql_num_rows($result3);
		// Compo équipe B
			if ($row['Id_equipeB'] >= 1)
				$this->InitTitulaireEquipe('B', $idMatch, $row['Id_equipeB'], $myBdd);
			$sql4  = "Select a.Matric, a.Numero, a.Capitaine, IF(a.Capitaine = 'E', 1, 0) Entraineur, b.Matric, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Origine, c.Matric Matric_titulaire ";
			$sql4 .= "From gickp_Matchs_Joueurs a ";
			$sql4 .= "Left Outer Join gickp_Competitions_Equipes_Joueurs c On (c.Id_equipe = ".$row['Id_equipeB']." And c.Matric = a.Matric), "; 
			$sql4 .= "gickp_Liste_Coureur b ";
			$sql4 .= "Where a.Matric = b.Matric ";
			$sql4 .= "And a.Id_match = $idMatch ";
			$sql4 .= "And a.Equipe = 'B' ";
			$sql4 .= "Order By Entraineur, Numero, Nom, Prenom ";	 
			$result4 = mysql_query($sql4, $myBdd->m_link) or die ("Erreur Load<br />".$sql4);
			$num_results4 = mysql_num_rows($result4);

			// Evts
			$sql5  = "Select d.Id, d.Id_match, d.Periode, d.Temps, d.Id_evt_match, d.Competiteur, d.Numero, d.Equipe_A_B, ";
			$sql5 .= "c.Nom, c.Prenom ";
			$sql5 .= "From gickp_Matchs_Detail d Left Outer Join gickp_Liste_Coureur c On d.Competiteur = c.Matric ";
			$sql5 .= "Where d.Id_match = $idMatch ";
			//$sql5 .= "AND d.Equipe_A_B = 'A' ";
			$sql5 .= "Order By d.Periode DESC, d.Temps ASC, d.Id ";
			$result5 = mysql_query($sql5, $myBdd->m_link) or die ("Erreur Load<br />".$sql5);
			$num_results5 = mysql_num_rows($result5);
?>
<!doctype html>
<html lang="fr">
    <head>
		<meta charset="utf-8">
		<title>Match <?php echo $row['Numero_ordre']; ?></title>
		<link href="v2/jquery-ui.min.css" rel="stylesheet">
		<link href="v2/jquery.dataTables.css" rel="stylesheet">
		<link href="v2/fmv2.css?v=<?= NUM_VERSION ?>" rel="stylesheet">
		<?php if($verrou != 'O') { ?>
			<link href="v2/fmv2O.css?v=<?= NUM_VERSION ?>" rel="stylesheet">
		<?php	}	?>
    </head>
    <body>
		<form>
			<!--<img src="v2/FFCK.gif" id="logo" />-->
			<div id="avert"></div>
			<p id="description-match"><?php 
				echo $row['Code_competition'];
				if($row['Code_typeclt'] == 'CHPT')
					echo ' ('.$row['Lieu'].')';
				elseif($row['Soustitre2'] != '')
					echo ' ('.$row['Soustitre2'].')';
				if($row['Phase'] != '')
					echo ' - '.$row['Phase'];
				echo ' - ' . $lang['Match_no'] . $row['Numero_ordre'] . ' - '; ?>
                <?php 
                    if($version == 'en') {
                        echo $row['Date_match'];
                    } else {
                        echo utyDateUsToFr($row['Date_match']);
                    }
                    echo ' ' . $lang['a_'] . ' ' . $row['Heure_match'] . ' - ' . $lang['Terrain'] . ' '.$row['Terrain']; 
                ?>
                <a href="#" class="fm_bouton fm_tabs pull-right" id="tabs-1_link"><?php echo $lang['Parametres_match']; ?>...</a>
                <a href="#" class="fm_bouton fm_tabs pull-right" id="tabs-2_link"><?php echo $lang['Deroulement_match']; ?>...</a>
            </p>
			<div id="tabs-1" class="tabs_content">
				<div id="accordion">
					<!--<div class="note"><?php echo $lang['A_remplir']; ?></div>-->
					<h3><?php echo $lang['Parametres_match']; ?> ID# <?php echo $idMatch; ?></h3>
					<div>
						<div class="moitie">
							<?php echo $lang['Type_match']; ?> : 
							<br />
							<span id="typeMatch">
								<input type="radio" name="typeMatchtypeMatch" id="typeMatchClassement" <?php if($row['Type_match'] == 'C') echo 'checked="checked"'; ?> />
                                <label for="typeMatchClassement" title="<?php echo $lang['Egalite_possible']; ?>"><?php echo $lang['Match_classement']; ?></label>
								<input type="radio" name="typeMatch" id="typeMatchElimination" <?php if($row['Type_match'] == 'E') echo 'checked="checked"'; ?> />
                                <label for="typeMatchElimination" title="<?php echo $lang['Vainqueur_obligatoire']; ?>"><?php echo $lang['Match_elimination']; ?></label>
							</span>
							<img id="typeMatchImg" style="vertical-align:middle;" title="<?php if($row['Type_match'] == 'C'){ echo $lang['Match_classement']; }else{ echo $lang['Match_elimination'];} ?>" alt="<?php echo $lang['Type_match']; ?>" src="../img/type<?php echo $row['Type_match']; ?>.png" />
                            <br>
							<br>
							<?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
								<span title="<?php echo $lang['PC_Course_seulement']; ?>"><?php echo $lang['Publication']; ?> : </span>
								<br />
								<span id="publiMatch">
									<input type="radio" name="publiMatch" id="prive" <?php if($publiMatch != 'O') echo 'checked="checked"'; ?> /><label for="prive" title="<?php echo $lang['Match_prive']; ?>"><?php echo $lang['Prive']; ?></label>
									<input type="radio" name="publiMatch" id="public" <?php if($publiMatch == 'O') echo 'checked="checked"'; ?> /><label for="public" title="<?php echo $lang['Match_public']; ?>"><?php echo $lang['Public']; ?></label>
								</span>
							<?php } ?>
							<img height="30" style="vertical-align:middle;" title="<?php echo $lang['Publier']; ?> ?" alt="<?php echo $lang['Publier']; ?> ?" src="../img/oeil2<?php if($publiMatch == 'O'){ echo 'O';} else {echo 'N';} ?>.gif" />
							<br />
							<br />
							<input class="ui-button ui-widget ui-corner-all ui-state-default" type="button" id="btn_stats" name="btn_stats" value="Stats" />
							<input class="ui-button ui-widget ui-corner-all ui-state-default" type="button" id="pdfFeuille" name="pdfFeuille" value="PDF" />
                            <a class="ui-button ui-widget ui-corner-all" href="../lang.php?lang=fr&p=fm2&idMatch=<?php echo $idMatch; ?>"><img src="../img/Pays/FRA.png" height="25" align="bottom"></a>
                            <a class="ui-button ui-widget ui-corner-all" href="../lang.php?lang=en&p=fm2&idMatch=<?php echo $idMatch; ?>"><img src="../img/Pays/GBR.png" height="25" align="bottom"></a>
							<br />
							<br />
							<?php echo $lang['Charger_autre_feuille']; ?> :
							<br />
							ID# <input class="ui-button ui-widget ui-corner-all" type="tel" id="idFeuille" />
                            <input class="ui-button ui-widget ui-corner-all ui-state-default" type="button" id="chargeFeuille" value="<?php echo $lang['Charger']; ?>" />
						</div>
						<div class="moitie droite">
							<span id="validScoreMatch">
								<i><?php echo $lang['Score_officiel']; ?> :<br />
								<span class="presentScore"><?php echo $row['equipeA']; ?> <span class="score" id="scoreA4"><?php echo $row['ScoreA']; ?></span> - <span class="score" id="scoreB4"><?php echo $row['ScoreB']; ?></span> <?php echo $row['equipeB']; ?></span>
								</i>
								<br />
								<br />
								<?php echo $lang['Score_provisoire']; ?> :<br />
								<span class="presentScore"><?php echo $row['equipeA']; ?> <span class="score" id="scoreA3">0</span> - <span class="score" id="scoreB3">0</span> <?php echo $row['equipeB']; ?></span>
								<?php if($verrou != 'O') { ?>
									<br />
									<br />
									<input class="ui-button ui-widget ui-corner-all ui-state-default" type="button" id="validScore" name="validScore" value="<?php echo $lang['Valider_score']; ?>" />
								<?php } ?>
							</span>
							<br />
							<br />
							<span title="<?php echo $lang['PC_Course_seulement']; ?>"><?php echo $lang['Controle_match']; ?> : </span>
							<br />
							<span id="controleMatch">
								<?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
									<input type="radio" name="controleMatch" id="controleOuvert" <?php if($verrou != 'O') echo 'checked="checked"'; ?> /><label for="controleOuvert"><?php echo $lang['Ouvert']; ?></label>
								<?php } ?>
								<input type="radio" name="controleMatch" id="controleVerrou" <?php if($verrou == 'O') echo 'checked="checked"'; ?> /><label for="controleVerrou"><?php echo $lang['Verrouille']; ?></label>
							</span>
							<img height="30" style="vertical-align:middle;" title="<?php echo $lang['Verrouille']; ?> ?" alt="<?php echo $lang['Verrouille']; ?> ?" src="../img/verrou2<?php if($verrou == 'O'){ echo 'O';} else {echo 'N';} ?>.gif" />
						</div>
					</div>
					<h3><?php echo $lang['Officiels']; ?></h3>
					<div>
						<div class="moitie">
							<label><?php echo $lang['Secretaire']; ?> : </label><br /><span class="editOfficiel" id="Secretaire"><?php echo $row['Secretaire']; ?></span><br />
							<label><?php echo $lang['Chronometre']; ?> : </label><br /><span class="editOfficiel" id="Chronometre"><?php echo $row['Chronometre']; ?></span><br />
							<label><?php echo $lang['Time_shoot']; ?> : </label><br /><span class="editOfficiel" id="Timeshoot"><?php echo $row['Timeshoot']; ?></span><br />
							<br />
							<label><?php echo $lang['Arbitre_1']; ?> : </label><br /><span class="editArbitres" id="Arbitre_principal"><?php echo $row['Arbitre_principal']; ?></span><br />
							<label><?php echo $lang['Arbitre_2']; ?> : </label><br /><span class="editArbitres" id="Arbitre_secondaire"><?php echo $row['Arbitre_secondaire']; ?></span><br />
							<label><?php echo $lang['Ligne']; ?> : </label><br /><span class="editOfficiel" id="Ligne1"><?php echo $row['Ligne1']; ?></span><br />
							<label><?php echo $lang['Ligne']; ?> : </label><br /><span class="editOfficiel" id="Ligne2"><?php echo $row['Ligne2']; ?></span><br />
						</div>
						<div class="moitie droite">
							<label><?php echo $lang['Club_organisateur']; ?> : </label><?php echo $row['Organisateur']; ?><br />
							<label><?php echo $lang['R1'] ?> : </label><?php echo $row['Responsable_R1']; ?><br />
							<label><?php echo $lang['Delegue'] ?> : </label><?php echo $row['Delegue']; ?><br />
							<label><?php echo $lang['Chef_arbitre'] ?> : </label><?php echo $row['ChefArbitre']; ?><br />
							<label><?php echo $lang['RC'] ?> : </label><?php echo $row['Responsable_insc']; ?><br />
							<br />

						</div>
					</div>
					<h3><?php echo $lang['Equipe'] ?> A - <img src="../img/Pays/<?php echo $paysA; ?>.png" width="25" height="16" /> <?php echo $row['equipeA']; ?>								
						<span class="score" id="scoreA2">0</span>
					</h3>
					<div>
						<table class="dataTable" id="equipeA">
							<thead>
								<tr>
									<th><?php echo $lang['Num'] ?></th>
									<th><?php echo $lang['Statut'] ?></th>
									<th><?php echo $lang['Nom'] ?></th>
									<th><?php echo $lang['Prenom'] ?></th>
									<th><?php echo $lang['Licence'] ?></th>
									<th>Cat.</th>
									<th><?php echo $lang['Supp'] ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
								$joueur_temp = '';
								$entr_temp = '';
								for ($i=1;$i<=$num_results3;$i++)
								{
									$row3 = mysql_fetch_array($result3);
									$age = utyCodeCategorie2($row3["Naissance"], $saison);
									if($row3["Capitaine"] != 'E'){
										$joueur_temp  = '<tr>';
										$joueur_temp .= '<td class="editNo" id="No-'.$row3["Matric"].'">'.$row3["Numero"].'</td>';
										$joueur_temp .= '<td class="editStatut" id="Statut-'.$row3["Matric"].'">'.$row3["Capitaine"].'</td>';
										$joueur_temp .= '<td>'.ucwords(strtolower($row3["Nom"])).'</td>';
										$joueur_temp .= '<td>'.ucwords(strtolower($row3["Prenom"])).'</td>';
										$joueur_temp .= '<td>';
										if($row3["Matric"] < 2000000)
											$joueur_temp .= $row3["Matric"];
										$joueur_temp .= '</td>';
										$joueur_temp .= '<td>'.$age.'</td>';
										$joueur_temp .= '<td><a href="#" class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-A-'.$row3["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
										$joueur_temp .= '</tr>';
                                                                                $entr_temp  = '';
									}else{
										$entr_temp  = '<tr class="entraineur">';
										$entr_temp .= '<td class="editNo" id="No-'.$row3["Matric"].'"></td>';
										$entr_temp .= '<td class="editStatut" id="Statut-'.$row3["Matric"].'">'.$row3["Capitaine"].'</td>';
										$entr_temp .= '<td>'.ucwords(strtolower($row3["Nom"])).'</td>';
										$entr_temp .= '<td>'.ucwords(strtolower($row3["Prenom"])).'</td>';
										$entr_temp .= '<td>';
										if($row3["Matric"] < 2000000)
											$entr_temp .= $row3["Matric"];
										$entr_temp .= '</td>';
										$entr_temp .= '<td>'.$age.'</td>';
										$entr_temp .= '<td><a href="#" class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-A-'.$row3["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
										$entr_temp .= '</tr>';
										$joueur_temp = '';
									}
									echo $joueur_temp;
                                                                        echo $entr_temp;
								}
								if($num_results3 >= 1)
									mysql_data_seek($result3,0); 
							?>
							</tbody>
						</table>
						<input class="ui-button ui-widget ui-corner-all" type="button" name="initA" id="initA" value="<?php echo $lang['Recharger_joueurs'] ?>" />
					</div>			
					<h3><?php echo $lang['Equipe'] ?> B - <img src="../img/Pays/<?php echo $paysB; ?>.png" width="25" height="16" /> <?php echo $row['equipeB']; ?>								
						<span class="score" id="scoreB2">0</span>
					</h3>
					<div>
						<table class="dataTable" id="equipeB">
							<thead>
								<tr>
									<th><?php echo $lang['Num'] ?></th>
									<th><?php echo $lang['Statut'] ?></th>
									<th><?php echo $lang['Nom'] ?></th>
									<th><?php echo $lang['Prenom'] ?></th>
									<th><?php echo $lang['Licence'] ?></th>
									<th>Cat.</th>
									<th><?php echo $lang['Supp'] ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
								$joueur_temp = '';
								$entr_temp = '';
								for ($i=1;$i<=$num_results4;$i++)
								{
									$row4 = mysql_fetch_array($result4);
									$age = utyCodeCategorie2($row4["Naissance"], $saison);
									if($row4["Capitaine"] != 'E'){
										$joueur_temp  = '<tr>';
										$joueur_temp .= '<td class="editNo" id="No-'.$row4["Matric"].'">'.$row4["Numero"].'</td>';
										$joueur_temp .= '<td class="editStatut" id="Statut-'.$row4["Matric"].'">'.$row4["Capitaine"].'</td>';
										$joueur_temp .= '<td>'.ucwords(strtolower($row4["Nom"])).'</td>';
										$joueur_temp .= '<td>'.ucwords(strtolower($row4["Prenom"])).'</td>';
										$joueur_temp .= '<td>';
										if ($row4["Matric"] < 2000000) {
                                            $joueur_temp .= $row4["Matric"];
                                        }
                                        $joueur_temp .= '</td>';
										$joueur_temp .= '<td>'.$age.'</td>';
										$joueur_temp .= '<td><a href="#" class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-B-'.$row4["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
										$joueur_temp .= '</tr>';
                                        $entr_temp  = '';
									}else{
										$entr_temp  = '<tr class="entraineur">';
										$entr_temp .= '<td class="editNo" id="No-'.$row4["Matric"].'"></td>';
										$entr_temp .= '<td class="editStatut" id="Statut-'.$row4["Matric"].'">'.$row4["Capitaine"].'</td>';
										$entr_temp .= '<td>'.ucwords(strtolower($row4["Nom"])).'</td>';
										$entr_temp .= '<td>'.ucwords(strtolower($row4["Prenom"])).'</td>';
										$entr_temp .= '<td>';
										if ($row4["Matric"] < 2000000) {
                                            $entr_temp .= $row4["Matric"];
                                        }
                                        $entr_temp .= '</td>';
										$entr_temp .= '<td>'.$age.'</td>';
										$entr_temp .= '<td><a href="#" class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-B-'.$row4["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
										$entr_temp .= '</tr>';
										$joueur_temp = '';
									}
									echo $joueur_temp;
                                    echo $entr_temp;
								}
								if ($num_results4 >= 1) {
                                    mysql_data_seek($result4, 0);
                                }
                            ?>
							</tbody>
						</table>
						<input class="ui-button ui-widget ui-corner-all" type="button" name="initB" id="initB" value="<?php echo $lang['Recharger_joueurs'] ?>" />
					</div>			
				</div>			
			</div>
			<div id="tabs-2" class="tabs_content">
				<table class="maxWidth" id="deroulement_match">
					<tr>
						<th colspan="3">
							<span class="match"></span>
                            <span class="pull-left">
                                <a href="#" id="ATT" class="fm_bouton statut<?php if($statutMatch == 'ATT') echo ' actif'; ?>"><?php echo $lang['En_attente']; ?></a>
                                <a href="#" id="ON" class="fm_bouton statut<?php if($statutMatch == 'ON') echo ' actif'; ?>"><?php echo $lang['En_cours']; ?></a>
                                <a href="#" id="END" class="fm_bouton statut<?php if($statutMatch == 'END') echo ' actif'; ?>"><?php echo $lang['Termine']; ?></a>
                                <span class="endmatch"><?php echo $lang['Fin'] ?> : </span><input type="tel" id="end_match_time" class="fm_input_text endmatch" value="<?php echo $row['Heure_fin']; ?>" />
                            </span>
                            <span class="pull-right">
                                <a href="#" id="M1" class="fm_bouton periode<?php if($periodeMatch == 'M1') echo ' actif'; ?>"><?php echo $lang['period_M1']; ?></a>
                                <a href="#" id="M2" class="fm_bouton periode<?php if($periodeMatch == 'M2') echo ' actif'; ?>"><?php echo $lang['period_M2']; ?></a>
                                <a href="#" id="P1" class="fm_bouton periode<?php if($periodeMatch == 'P1') echo ' actif'; ?>"><?php echo $lang['period_P1']; ?></a>
                                <a href="#" id="P2" class="fm_bouton periode<?php if($periodeMatch == 'P2') echo ' actif'; ?>"><?php echo $lang['period_P2']; ?></a>
                                <a href="#" id="TB" class="fm_bouton periode<?php if($periodeMatch == 'TB') echo ' actif'; ?>"><?php echo $lang['period_TB']; ?></a>
                            </span>
<!-- CHRONO DEBUG
<br />
start_time: <span id="start_time_display"></span><br />
run_time: <span id="run_time_display"></span><br />
stop_time: <span id="stop_time_display"></span><br />

-->
						</th>
					</tr>
					<tr>
						<td id="selectionA">
							<a href="#" class="fm_bouton equipes" data-equipe="A" data-player="Equipe A"><?php echo $lang['Equipe']; ?> A<br />
								<img src="../img/Pays/<?php echo $paysA; ?>.png" width="25" height="16" /> <?php echo $row['equipeA']; ?>
								<span class="score" id="scoreA">0</span>
							</a>
							
							<?php 			
								$joueur_temp = '';
								$entr_temp = '';
								for ($i=1;$i<=$num_results3;$i++)
								{
									$row3 = mysql_fetch_array($result3);
									if($row3["Capitaine"] != 'E'){
										$joueur_temp  = '<a href="#" id="A'.$row3["Matric"].'" data-equipe="A" data-player="'.ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'." data-id="'.$row3["Matric"].'" data-nb="'.$row3["Numero"].'" class="fm_bouton joueurs">';
										$joueur_temp .= '<span class="NumJoueur">'.$row3["Numero"].'</span> - '.ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'.<span class="StatutJoueur">';
										if($row3["Capitaine"] == 'C')
											$joueur_temp .= ' (Cap.)';
										$joueur_temp .= '</span><span class="c_evt"></span></a>';
									}else{
										$entr_temp .= '<a href="#" id="A'.$row3["Matric"].'" data-equipe="A" data-player="'.ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'." data-id="'.$row3["Matric"].'" data-nb="'.$row3["Numero"].'" class="fm_bouton joueurs coach">';
										$entr_temp .= ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'.<span class="StatutJoueur"> (Coach)</span>';
										$entr_temp .= '<span class="c_evt"></span></a>';
										$joueur_temp = '';
									}
									echo $joueur_temp;
								}
								echo $entr_temp;
							?>
                            
<!--                            <div class="fm_bouton fm_stats pull-left">
                                <?= $lang['Tir'] ?> : <span id='nb_tirs_A'></span>
							</div>
                            <div class="fm_bouton fm_stats pull-right">
                                <?= $lang['Tir_contre'] ?> : <span id='nb_arrets_A'></span>
							</div>-->
						</td>
						<td id="selectionChrono" class="centre">
							<div id="zoneChrono">
                                <img id="chrono_moins10" class="plusmoins" src="../img/moins10.png" alt=""/>
                                <img id="chrono_moins" class="plusmoins" src="../img/moins1.png" alt=""/>
								<!--<span id="chronoText"><?php echo $lang['Chrono'] ?> : </span>-->
								<span class="icon_parametres" id="dialog_ajust_opener" title="<?php echo $lang['Parametres_chrono'] ?>"></span>
                                <input type="tel" id="heure" class="fm_input_text" title="<?php echo $lang['Parametres_chrono'] ?>" readonly />
                                <img id="chrono_plus" class="plusmoins" src="../img/plus1.png" alt="">
                                <img id="chrono_plus10" class="plusmoins" src="../img/plus10.png" alt="">
                                <span id="updateChrono" class="center" title="<?= $lang['Confirmer'] ?>"><br><img src="v2/valider.gif"><br></span>
								
								<a href="#" id="start_button" class="fm_bouton chronoButton">Start</a>
								<a href="#" id="run_button" class="fm_bouton chronoButton">Run</a>
								<a href="#" id="stop_button" class="fm_bouton chronoButton">Stop</a>
								<a href="#" id="raz_button" class="fm_bouton chronoButton"><?php echo $lang['RAZ'] ?></a>
							</div>
							<div id="zoneEvt">
								<a href="#" id="evt_but" data-evt="But" data-code="B" class="fm_bouton evtButton"><span class="but"><?php echo $lang['But'] ?></span></a>
								<a href="#" id="evt_vert" data-evt="Carton vert" data-code="V" class="fm_bouton evtButton"><img src="v2/carton_vert.png" /></a>
								<!--<a href="#" id="evt_tir" data-evt="Tir" data-code="T" class="fm_bouton evtButton" title="<?php echo $lang['Tir_non_cadre'] ?>"><?php echo $lang['Tir'] ?></a>-->
								<a href="#" id="evt_jaune" data-evt="Carton jaune" data-code="J" class="fm_bouton evtButton"><img src="v2/carton_jaune.png" /></a>
								<!--<a href="#" id="evt_arr" data-evt="Arret" data-code="A" class="fm_bouton evtButton" title="<?php echo $lang['Tir_contre_gardien'] ?>"><?php echo $lang['Tir_contre'] ?></a>-->
								<a href="#" id="evt_rouge" data-evt="Carton rouge" data-code="R" class="fm_bouton evtButton"><img src="v2/carton_rouge.png" /></a>
							</div>
							<div id="zoneTemps">
								<img id="time_moins60" class="plusmoins" src="../img/moins60.png">
								<img id="time_moins10" class="plusmoins" src="../img/moins10.png">
								<img id="time_moins1" class="plusmoins" src="../img/moins1.png">
								<?php // echo $lang['Temps'] ?>
                                <input type="tel" size="4" class="fm_input_text" id="time_evt" value="00:00">
								<img id="time_plus1" class="plusmoins" src="../img/plus1.png">
								<img id="time_plus10" class="plusmoins" src="../img/plus10.png">
								<img id="time_plus60" class="plusmoins" src="../img/plus60.png">
								<br />
								<a href="#" id="update_evt" data-id="" class="fm_bouton evtButton2"><img src="v2/b_edit.png" /> <?php echo $lang['Modifier'] ?></a>
								<a href="#" id="valid_evt" class="fm_bouton evtButton2 evtButton3">OK</a>
								<a href="#" id="delete_evt" class="fm_bouton evtButton2"><img src="v2/supprimer.gif" /> <?php echo $lang['Supp'] ?></a>
								<a href="#" id="reset_evt" class="fm_bouton evtButton2"><?php echo $lang['Annuler'] ?></a>
                                <a href="#" id="liste_evt" class="fm_bouton evtButton2"><?php echo $lang['Liste'] ?> <img id="list_down" src="../img/down.png"><img id="list_up" src="../img/up.png"></a>
							</div>
						</td>
						<td id="selectionB">
							<a href="#" class="fm_bouton equipes" data-equipe="B" data-player="Equipe B">
								<span class="score" id="scoreB">0</span><?php echo $lang['Equipe'] ?> B<br />
								<img src="../img/Pays/<?php echo $paysB; ?>.png" width="25" height="16" /> <?php echo $row['equipeB']; ?>
							</a>
							<?php 			
								$joueur_temp = '';
								$entr_temp = '';
								for ($i=1;$i<=$num_results4;$i++)
								{
									$row4 = mysql_fetch_array($result4);
									if($row4["Capitaine"] != 'E'){
										$joueur_temp  = '<a href="#" id="B'.$row4["Matric"].'" data-equipe="B" data-player="'.ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'." data-id="'.$row4["Matric"].'" data-nb="'.$row4["Numero"].'" class="fm_bouton joueurs">';
										$joueur_temp .= '<span class="NumJoueur">'.$row4["Numero"].'</span> - '.ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'.<span class="StatutJoueur">';
										if($row4["Capitaine"] == 'C')
											$joueur_temp .= ' (Cap.)';
										$joueur_temp .= '</span><span class="c_evt"></span></a>';
									}else{
										$entr_temp .= '<a href="#" id="B'.$row4["Matric"].'" data-equipe="B" data-player="'.ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'." data-id="'.$row4["Matric"].'" data-nb="'.$row4["Numero"].'" class="fm_bouton joueurs coach">';
										$entr_temp .= ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'.<span class="StatutJoueur"> (Coach)</span>';
										$entr_temp .= '<span class="c_evt"></span></a>';
										$joueur_temp = '';
									}
									echo $joueur_temp;
								}
								echo $entr_temp;
							?>
<!--                            <div class="fm_bouton fm_stats pull-left">
                                <?= $lang['Tir'] ?> : <span id='nb_tirs_B'></span>
							</div>
                            <div class="fm_bouton fm_stats pull-right">
                                <?= $lang['Tir_contre'] ?> : <span id='nb_arrets_B'></span>
							</div>-->
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<table id="list_header" class="maxWidth ui-state-default">
								<tr>
									<th class="list_evt_v"><?php echo $lang['V'] ?></th>
									<th class="list_evt_j"><?php echo $lang['J'] ?></th>
									<th class="list_evt_r"><?php echo $lang['R'] ?></th>
									<th class="list_nom"><?php echo $lang['Equipe'] ?> A</th>
									<th class="list_evt_b"><?php echo $lang['B'] ?></th>
									<th class="list_chrono" id="change_ordre" title="<?php echo $lang['Changer_ordre'] ?>"><?php echo $lang['Temps'] ?> <img src="../img/up.png" /></th>
									<th class="list_evt_b"><?php echo $lang['B'] ?></th>
									<th class="list_nom"><?php echo $lang['Equipe'] ?> B</th>
									<th class="list_evt_v"><?php echo $lang['V'] ?></th>
									<th class="list_evt_j"><?php echo $lang['J'] ?></th>
									<th class="list_evt_r"><?php echo $lang['R'] ?></th>
								</tr>
							</table>
							<table id="list" class="maxWidth">
<!--
-->
								<?php
								$evt_temp = '';
								for ($i=1;$i<=$num_results5;$i++)
								{
									$row5 = mysql_fetch_array($result5);
                                    if($row5["Id_evt_match"] != 'A' && $row5["Id_evt_match"] != 'T') {
                                        $evtEquipe = $row5['Equipe_A_B'];
                                        if($row5['Competiteur'] == '0'){
                                            $row5["Numero"] = '';
                                            $row5["Nom"] = 'Equipe';
                                            $row5["Prenom"] = $evtEquipe;
                                        }
                                        $evt_temp  = '<tr id="ligne_'.$row5["Id"].'" data-code="'.$row5["Periode"].'-'.substr($row5["Temps"],-5).'-'.$row5["Id_evt_match"].'-'.$evtEquipe.'-'.$row5["Competiteur"].'-'.$row5["Numero"].'">';
                                        if($evtEquipe == 'A'){
                                            $evt_temp .= '<td class="list_evt">';
                                            if($row5["Id_evt_match"] == 'V')
                                                $evt_temp .= '<img src="v2/carton_vert.png">';
                                            $evt_temp .= '</td><td class="list_evt">';
                                            if($row5["Id_evt_match"] == 'J')
                                                $evt_temp .= '<img src="v2/carton_jaune.png">';
                                            $evt_temp .= '</td><td class="list_evt">';
                                            if($row5["Id_evt_match"] == 'R')
                                                $evt_temp .= '<img src="v2/carton_rouge.png">';
                                            $evt_temp .= '</td>';
                                            $evt_temp .= '<td class="list_nom">'.$row5["Numero"].' - '.ucwords(strtolower($row5["Nom"])).' '.ucwords(strtolower($row5["Prenom"]));
    //										if($row5["Id_evt_match"] == 'A')
    //											$evt_temp .= ' (arrêt)';
    //										if($row5["Id_evt_match"] == 'T')
    //											$evt_temp .= ' (tir)';
                                            $evt_temp .= '</td>';
                                            $evt_temp .= '<td class="list_evt">';
                                            if($row5["Id_evt_match"] == 'B')
                                                $evt_temp .= '<img src="v2/but1.png">';
                                            $evt_temp .= '</td>';
                                        } else {
                                            $evt_temp .= '<td colspan="5" class="list_evt_vide"></td>';
                                        }
                                        $evt_temp .= '<td class="list_chrono">'.$row5["Periode"].' '.substr($row5["Temps"],-5).'</td>';
                                        if($evtEquipe == 'B'){
                                            $evt_temp .= '<td class="list_evt">';
                                            if($row5["Id_evt_match"] == 'B')
                                                $evt_temp .= '<img src="v2/but1.png">';
                                            $evt_temp .= '</td>';
                                            $evt_temp .= '<td class="list_nom">'.$row5["Numero"].' - '.ucwords(strtolower($row5["Nom"])).' '.ucwords(strtolower($row5["Prenom"]));
    //										if($row5["Id_evt_match"] == 'A')
    //											$evt_temp .= ' (arrêt)';
    //										if($row5["Id_evt_match"] == 'T')
    //											$evt_temp .= ' (tir)';
                                            $evt_temp .= '</td>';
                                            $evt_temp .= '<td class="list_evt">';
                                            if($row5["Id_evt_match"] == 'V')
                                                $evt_temp .= '<img src="v2/carton_vert.png">';
                                            $evt_temp .= '</td><td class="list_evt">';
                                            if($row5["Id_evt_match"] == 'J')
                                                $evt_temp .= '<img src="v2/carton_jaune.png">';
                                            $evt_temp .= '</td><td class="list_evt">';
                                            if($row5["Id_evt_match"] == 'R')
                                                $evt_temp .= '<img src="v2/carton_rouge.png">';
                                            $evt_temp .= '</td>';
                                        } else {
                                            $evt_temp .= '<td colspan="5" class="list_evt_vide"></td>';
                                        }
                                        $evt_temp .= '</tr>';

                                        echo $evt_temp;
                                    }
								}
							?>
							</table>
							&nbsp;
						</td>
					</tr>
				</table>
				<br />
				<?php echo $lang['Commentaires'] ?> :
				<div id="comments"><?php echo $row['Commentaires_officiels'];?></div>
				<br />
				<br />
				<br />
			</div>
            <div id="dialog_ajust" title="<?php echo $lang['Parametres_chrono'] ?>">
                <h3 id="dialog_ajust_periode">
                </h3>
                <p>
                    <?php echo $lang['Ajuster_chrono'] ?> : <input type="tel" id="chrono_ajust" class="fm_input_text" />
                </p>
                <p>
                    <?php echo $lang['Duree_periode'] ?> : <input type="tel" id="periode_ajust" class="fm_input_text" />
                </p>
            </div>
            <div id="dialog_end" title="<?php echo $lang['Fin_periode'] ?>">
                <p class="centre">
                    <span class="fm_input_text" id="periode_end">00:00</span><br /><?php echo $lang['Periode_terminee'] ?>
                </p>
            </div>
            <div id="dialog_end_match" title="<?php echo $lang['Fin_match'] ?>">
                <p class="centre">
                    <?php echo $lang['Heure_fin_match'] ?> : <input type="tel" id="time_end_match" class="fm_input_text" />
                </p>
                <p class="centre">
                    <?php echo $lang['Commentaires_officiels'] ?> :<br />
                    <textarea id="commentaires" rows="4" cols="50"></textarea>
                </p>
            </div>

		</form>
        
        <script type="text/javascript" src="v2/jquery-1.11.0.min.js"></script>
		<script type="text/javascript" src="v2/jquery-ui-1.10.4.custom.min.js"></script>
		<script type="text/javascript" src="v2/jquery.jeditable.js"></script>
		<script type="text/javascript" src="v2/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="v2/jquery.maskedinput.min.js"></script>
		<script>
            //paramètres ajustables
            var duree_prolongations = '05'; // ICF:'05', FFCK:'03'
            var arret_chrono_sur_but = false;
            
            var ancienne_ligne = 0;
            var theInEvent = false;
            var ordre_actuel = 'up';
            var idMatch = <?php echo $idMatch ?>;
            var idEquipeA = <?php echo $row['Id_equipeA'] ?>;
            var idEquipeB = <?php echo $row['Id_equipeB'] ?>;
            var typeMatch = "<?php echo $typeMatch ?>";
            var statutMatch = "<?php echo $statutMatch ?>";
            var publiMatch = "<?php echo $publiMatch ?>";
            var periode_en_cours = "<?php echo $periodeMatch ?>";
            var lang = {};
            <?php foreach ($lang as $key => $value) {
                $key = str_replace('-', '_', $key);
                echo 'lang.'.$key.' = "'.$value.'"; 
                        ' ; 
            }  ?>
            var timer, chrono, start_time, run_time, minut_max = 10, second_max = '00';
            var run_time = new Date();
            var temp_time = new Date();
            var start_time = new Date();
            
        </script>
		<script type="text/javascript" src="v2/fm2_A.js?v=<?= NUM_VERSION ?>"></script>

        <?php if($verrou == 'O' || $_SESSION['Profile'] <= 0 || $_SESSION['Profile'] > 6) { ?>
        <script>    
            $(function() {
                $('#typeMatch').click(function( event ){
                    event.preventDefault();
                });
            });
        </script>
        <?php	}	?>
				
        <?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
        <script type="text/javascript" src="v2/fm2_B.js?v=<?= NUM_VERSION ?>"></script>
        <?php } ?>
        
        <?php if($verrou != 'O') { ?>
        <script type="text/javascript" src="v2/fm2_C.js?v=<?= NUM_VERSION ?>"></script>
        <?php	}	?>
        
        <script type="text/javascript" src="v2/fm2_D.js?v=<?= NUM_VERSION ?>"></script>
        <script>
            
            $(function() {
				/* PARAMETRES PAR DEFAUT */
				<?php if($verrou == 'O') { ?>
					$('#controleVerrou').attr('checked','checked');
					$('#zoneTemps, #zoneChrono, .match, #initA, #initB, .suppression').hide();
					$('#typeMatch label').not('.ui-state-active').hide();				// masque le type match inactif !!
				<?php	}else{	?>
					$('#zoneTemps, #zoneChrono, .match').show();
					//$('.statut[class*="actif"]').click();
					$('#reset_evt').click();
                    if(typeMatch == 'C') {
                        $('#P1, #P2, #TB').hide();
                    } else {
                        $('#P1, #P2, #TB').show();
                    }
					statutActive(statutMatch, 'N');
				<?php	}	?>
				$('#end_match_time').val('<?php echo substr($heure_fin,-5,2).'h'.substr($heure_fin,-2) ?>');
				if(statutMatch != 'END') {
                    $('.endmatch').hide();
                }
				$('#' + periode_en_cours).addClass('actif');
				switch (periode_en_cours) {
					case 'P1':
						texte = lang.period_P1 + ' : 3 minutes';
						minut_max = '03';
						second_max = '00';
						break;
					case 'P2':
						texte = lang.period_P2 + ' : 3 minutes';
						minut_max = '03';
						second_max = '00';
						break;
					case 'TB':
						texte = lang.period_TB;
						minut_max = '03';
						second_max = '00';
						break;
					case 'M2':
						texte = lang.period_M2 + ' : 10 minutes';
						minut_max = '10';
						second_max = '00';
						break;
					default:
						texte = lang.period_M1 + ' : 10 minutes';
						minut_max = '10';
						second_max = '00';
						break;
				}
				$('#update_evt').hide();
				$('#delete_evt').hide();
				
				/* Evt chargés */
				<?php
				if($num_results5 >= 1)
					mysql_data_seek($result5,0);
				$evtEquipe = '';
                $evt_tir['A'] = 0;
                $evt_tir['B'] = 0;
                $evt_arret['A'] = 0;
                $evt_arret['B'] = 0;
				for ($i=1; $i<=$num_results5; $i++)
				{
					$row5 = mysql_fetch_array($result5);
					$evtEquipe = $row5['Equipe_A_B'];
                    $evt_temp_js = '';
					switch($row5["Id_evt_match"]){
						case 'B':
							$evt_temp_js = '$("#'.$evtEquipe.$row5['Competiteur'].' .c_evt").append("<img class=\'c_but\' src=\'v2/but1.png\' />");
							$("#score'.$evtEquipe.', #score'.$evtEquipe.'2, #score'.$evtEquipe.'3").text(parseInt($("#score'.$evtEquipe.'").text()) + 1);
							';
							break;
						case 'V':
							$evt_temp_js = '$("#'.$evtEquipe.$row5['Competiteur'].' .c_evt").append("<img class=\'c_carton\' src=\'v2/carton_vert.png\' />");
							';
							break;
						case 'J':
							$evt_temp_js = '$("#'.$evtEquipe.$row5['Competiteur'].' .c_evt").append("<img class=\'c_carton\' src=\'v2/carton_jaune.png\' />");
							';
							break;
						case 'R':
							$evt_temp_js = '$("#'.$evtEquipe.$row5['Competiteur'].' .c_evt").append("<img class=\'c_carton\' src=\'v2/carton_rouge.png\' />");
							';
							break;
                        case 'T':
                            $evt_tir[$evtEquipe] ++;
							break;
                        case 'A':
                            $evt_arret[$evtEquipe] ++;
							break;
                        default:
                            $evt_temp_js = '';
							break;
					}
					echo $evt_temp_js;
				}
                

				?>
                $('#nb_tirs_A').text(<?= $evt_tir['A'] ?>);
                $('#nb_tirs_B').text(<?= $evt_tir['B'] ?>);
                $('#nb_arrets_A').text(<?= $evt_arret['A'] ?>);
                $('#nb_arrets_B').text(<?= $evt_arret['B'] ?>);
			});
		</script>        

	</body>
</html>

<?php

	}

	function GestionMatchDetail()
	{			
		MyPageSecure::MyPageSecure(10);
		$this->Load();
	}
}		  	

$page = new GestionMatchDetail();
