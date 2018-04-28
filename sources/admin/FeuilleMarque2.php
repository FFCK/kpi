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
        
        $motifs_cartons = [
            "r_pad",
            "r_kt",
            "r_ht",
            "r_p",
            "r_o",
            "r_un",
            "r_rep"
        ];

                    
                    
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
			$sql5  = "Select d.Id, d.Id_match, d.Periode, d.Temps, d.Id_evt_match, d.motif, d.Competiteur, d.Numero, d.Equipe_A_B, ";
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
		<title>Match <?= $row['Numero_ordre']; ?></title>
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
                <a class="fm_bouton fm_tabs pull-right" id="tabs-1_link"><?= $lang['Parametres_match']; ?>...</a>
                <a class="fm_bouton fm_tabs pull-right" id="tabs-2_link"><?= $lang['Deroulement_match']; ?>...</a>
            </p>
			<div id="tabs-1" class="tabs_content">
				<div id="accordion">
					<!--<div class="note"><?= $lang['A_remplir']; ?></div>-->
					<!--<h3><?= $lang['Parametres_match']; ?> ID# <?= $idMatch; ?></h3>-->
                    <a class="fm_bouton fm_tabs2" id="tabs2-A_link" data-target="tabs2-A"><?= $lang['Parametres']; ?></a>
                    <a class="fm_bouton fm_tabs2" id="tabs2-B_link" data-target="tabs2-B"><?= $lang['Officiels']; ?></a>
                    <a class="fm_bouton fm_tabs2" id="tabs2-C_link" data-target="tabs2-C">
                        A <img src="../img/Pays/<?= $paysA; ?>.png" width="25" height="16" /> <?= $row['equipeA']; ?>								
						<span class="score" id="scoreA2">0</span>
                    </a>
                    <a class="fm_bouton fm_tabs2" id="tabs2-D_link" data-target="tabs2-D">
                        B <img src="../img/Pays/<?= $paysB; ?>.png" width="25" height="16" /> <?= $row['equipeB']; ?>								
						<span class="score" id="scoreB2">0</span>
                    </a>
                    <div class="tabs2_content" id="tabs2-A">
						<div class="moitie">
                            ID# <?= $idMatch; ?>
							<br />
							<label><?= $lang['Type_match']; ?></label>
							<br />
							<span id="typeMatch">
								<input type="radio" name="typeMatchtypeMatch" id="typeMatchClassement" <?php if($row['Type_match'] == 'C') echo 'checked="checked"'; ?> />
                                <label for="typeMatchClassement" title="<?= $lang['Egalite_possible']; ?>"><?= $lang['Match_classement']; ?></label>
								<input type="radio" name="typeMatch" id="typeMatchElimination" <?php if($row['Type_match'] == 'E') echo 'checked="checked"'; ?> />
                                <label for="typeMatchElimination" title="<?= $lang['Vainqueur_obligatoire']; ?>"><?= $lang['Match_elimination']; ?></label>
							</span>
							<img id="typeMatchImg" style="vertical-align:middle;" title="<?php if($row['Type_match'] == 'C'){ echo $lang['Match_classement']; }else{ echo $lang['Match_elimination'];} ?>" alt="<?= $lang['Type_match']; ?>" src="../img/type<?= $row['Type_match']; ?>.png" />
                            <br>
							<br>
							<?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
								<label><span title="<?= $lang['PC_Course_seulement']; ?>"><?= $lang['Publication']; ?></span></label>
								<br />
								<span id="publiMatch">
									<input type="radio" name="publiMatch" id="prive" <?php if($publiMatch != 'O') echo 'checked="checked"'; ?> /><label for="prive" title="<?= $lang['Match_prive']; ?>"><?= $lang['Prive']; ?></label>
									<input type="radio" name="publiMatch" id="public" <?php if($publiMatch == 'O') echo 'checked="checked"'; ?> /><label for="public" title="<?= $lang['Match_public']; ?>"><?= $lang['Public']; ?></label>
								</span>
							<?php } ?>
							<img height="30" style="vertical-align:middle;" title="<?= $lang['Publier']; ?> ?" alt="<?= $lang['Publier']; ?> ?" src="../img/oeil2<?php if($publiMatch == 'O'){ echo 'O';} else {echo 'N';} ?>.gif" />
							<br />
							<br />
							<input class="ui-button ui-widget ui-corner-all ui-state-default" type="button" id="btn_stats" name="btn_stats" value="Stats" />
							<input class="ui-button ui-widget ui-corner-all ui-state-default" type="button" id="pdfFeuille" name="pdfFeuille" value="PDF" />
                            <a class="ui-button ui-widget ui-corner-all" href="../lang.php?lang=fr&p=fm2&idMatch=<?= $idMatch; ?>"><img src="../img/Pays/FRA.png" height="25" align="bottom"></a>
                            <a class="ui-button ui-widget ui-corner-all" href="../lang.php?lang=en&p=fm2&idMatch=<?= $idMatch; ?>"><img src="../img/Pays/GBR.png" height="25" align="bottom"></a>
							<br />
							<br />
							<label><?= $lang['Charger_autre_feuille']; ?></label>
							<br />
							ID# <input class="ui-button ui-widget ui-corner-all" type="tel" id="idFeuille" pattern="[0-9]{8,9}">
                            <input class="ui-button ui-widget ui-corner-all ui-state-default" type="button" id="chargeFeuille" value="<?= $lang['Charger']; ?>" />
						</div>
						<div class="moitie droite">
							<span id="validScoreMatch">
								<i><?= $lang['Score_officiel']; ?> :<br />
								<span class="presentScore"><?= $row['equipeA']; ?> <span class="score" id="scoreA4"><?= $row['ScoreA']; ?></span> - <span class="score" id="scoreB4"><?= $row['ScoreB']; ?></span> <?= $row['equipeB']; ?></span>
								</i>
								<br />
								<br />
                                <label><?= $lang['Score_provisoire']; ?> :</label><br />
								<span class="presentScore"><?= $row['equipeA']; ?> <span class="score" id="scoreA3">0</span> - <span class="score" id="scoreB3">0</span> <?= $row['equipeB']; ?></span>
								<?php if($verrou != 'O') { ?>
									<br />
									<br />
									<input class="ui-button ui-widget ui-corner-all ui-state-default" type="button" id="validScore" name="validScore" value="<?= $lang['Valider_score']; ?>" />
								<?php } ?>
							</span>
							<br />
							<br />
                            <label><span title="<?= $lang['PC_Course_seulement']; ?>"><?= $lang['Controle_match']; ?></span></label>
							<br />
							<span id="controleMatch">
								<?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
									<input type="radio" name="controleMatch" id="controleOuvert" <?php if($verrou != 'O') echo 'checked="checked"'; ?> /><label for="controleOuvert"><?= $lang['Ouvert']; ?></label>
								<?php } ?>
								<input type="radio" name="controleMatch" id="controleVerrou" <?php if($verrou == 'O') echo 'checked="checked"'; ?> /><label for="controleVerrou"><?= $lang['Verrouille']; ?></label>
							</span>
							<img height="30" style="vertical-align:middle;" title="<?= $lang['Verrouille']; ?> ?" alt="<?= $lang['Verrouille']; ?> ?" src="../img/verrou2<?php if($verrou == 'O'){ echo 'O';} else {echo 'N';} ?>.gif" />
						</div>
					</div>
					<!--<h3><?= $lang['Officiels']; ?></h3>-->
                    <div class="tabs2_content" id="tabs2-B">
						<div class="moitie">
							<label><?= $lang['Secretaire']; ?> : </label><br /><span class="editOfficiel" id="Secretaire"><?= $row['Secretaire']; ?></span><br />
							<label><?= $lang['Chronometre']; ?> : </label><br /><span class="editOfficiel" id="Chronometre"><?= $row['Chronometre']; ?></span><br />
							<label><?= $lang['Time_shoot']; ?> : </label><br /><span class="editOfficiel" id="Timeshoot"><?= $row['Timeshoot']; ?></span><br />
							<br />
							<label><?= $lang['Arbitre_1']; ?> : </label><br /><span class="editArbitres" id="Arbitre_principal"><?= $row['Arbitre_principal']; ?></span><br />
							<label><?= $lang['Arbitre_2']; ?> : </label><br /><span class="editArbitres" id="Arbitre_secondaire"><?= $row['Arbitre_secondaire']; ?></span><br />
							<label><?= $lang['Ligne']; ?> : </label><br /><span class="editOfficiel" id="Ligne1"><?= $row['Ligne1']; ?></span><br />
							<label><?= $lang['Ligne']; ?> : </label><br /><span class="editOfficiel" id="Ligne2"><?= $row['Ligne2']; ?></span><br />
						</div>
						<div class="moitie droite">
							<label><?= $lang['Club_organisateur']; ?> : </label><?= $row['Organisateur']; ?><br />
							<label><?= $lang['R1'] ?> : </label><?= $row['Responsable_R1']; ?><br />
							<label><?= $lang['Delegue'] ?> : </label><?= $row['Delegue']; ?><br />
							<label><?= $lang['Chef_arbitre'] ?> : </label><?= $row['ChefArbitre']; ?><br />
							<label><?= $lang['RC'] ?> : </label><?= $row['Responsable_insc']; ?><br />
							<br />

						</div>
					</div>
<!--					<h3><?= $lang['Equipe'] ?> A - <img src="../img/Pays/<?= $paysA; ?>.png" width="25" height="16" /> <?= $row['equipeA']; ?>								
						<span class="score" id="scoreA2">0</span>
					</h3>-->
                    <div class="tabs2_content" id="tabs2-C">
						<table class="dataTable" id="equipeA">
							<thead>
								<tr>
									<th><?= $lang['Num'] ?></th>
									<th><?= $lang['Statut'] ?></th>
									<th><?= $lang['Nom'] ?></th>
									<th><?= $lang['Prenom'] ?></th>
									<th><?= $lang['Licence'] ?></th>
									<th>Cat.</th>
									<th><?= $lang['Supp'] ?></th>
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
										$joueur_temp .= '<td>'.strtoupper($row3["Nom"]).'</td>';
										$joueur_temp .= '<td>'.utyUcWordNomCompose($row3["Prenom"]).'</td>';
										$joueur_temp .= '<td>';
										if($row3["Matric"] < 2000000)
											$joueur_temp .= $row3["Matric"];
										$joueur_temp .= '</td>';
										$joueur_temp .= '<td>'.$age.'</td>';
										$joueur_temp .= '<td><a class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-A-'.$row3["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
										$joueur_temp .= '</tr>';
                                                                                $entr_temp  = '';
									}else{
										$entr_temp  = '<tr class="entraineur">';
										$entr_temp .= '<td class="editNo" id="No-'.$row3["Matric"].'"></td>';
										$entr_temp .= '<td class="editStatut" id="Statut-'.$row3["Matric"].'">'.$row3["Capitaine"].'</td>';
										$entr_temp .= '<td>'.strtoupper($row3["Nom"]).'</td>';
										$entr_temp .= '<td>'.utyUcWordNomCompose($row3["Prenom"]).'</td>';
										$entr_temp .= '<td>';
										if($row3["Matric"] < 2000000)
											$entr_temp .= $row3["Matric"];
										$entr_temp .= '</td>';
										$entr_temp .= '<td>'.$age.'</td>';
										$entr_temp .= '<td><a class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-A-'.$row3["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
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
						<input class="ui-button ui-widget ui-corner-all" type="button" name="initA" id="initA" value="<?= $lang['Recharger_joueurs'] ?>" />
					</div>			
<!--					<h3><?= $lang['Equipe'] ?> B - <img src="../img/Pays/<?= $paysB; ?>.png" width="25" height="16" /> <?= $row['equipeB']; ?>								
						<span class="score" id="scoreB2">0</span>
					</h3>-->
                    <div class="tabs2_content" id="tabs2-D">
                        <table class="dataTable" id="equipeB">
							<thead>
								<tr>
									<th><?= $lang['Num'] ?></th>
									<th><?= $lang['Statut'] ?></th>
									<th><?= $lang['Nom'] ?></th>
									<th><?= $lang['Prenom'] ?></th>
									<th><?= $lang['Licence'] ?></th>
									<th>Cat.</th>
									<th><?= $lang['Supp'] ?></th>
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
										$joueur_temp .= '<td>'.strtoupper($row4["Nom"]).'</td>';
										$joueur_temp .= '<td>'.utyUcWordNomCompose($row4["Prenom"]).'</td>';
										$joueur_temp .= '<td>';
										if ($row4["Matric"] < 2000000) {
                                            $joueur_temp .= $row4["Matric"];
                                        }
                                        $joueur_temp .= '</td>';
										$joueur_temp .= '<td>'.$age.'</td>';
										$joueur_temp .= '<td><a class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-B-'.$row4["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
										$joueur_temp .= '</tr>';
                                        $entr_temp  = '';
									}else{
										$entr_temp  = '<tr class="entraineur">';
										$entr_temp .= '<td class="editNo" id="No-'.$row4["Matric"].'"></td>';
										$entr_temp .= '<td class="editStatut" id="Statut-'.$row4["Matric"].'">'.$row4["Capitaine"].'</td>';
										$entr_temp .= '<td>'.strtoupper($row4["Nom"]).'</td>';
										$entr_temp .= '<td>'.utyUcWordNomCompose($row4["Prenom"]).'</td>';
										$entr_temp .= '<td>';
										if ($row4["Matric"] < 2000000) {
                                            $entr_temp .= $row4["Matric"];
                                        }
                                        $entr_temp .= '</td>';
										$entr_temp .= '<td>'.$age.'</td>';
										$entr_temp .= '<td><a class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-B-'.$row4["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
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
						<input class="ui-button ui-widget ui-corner-all" type="button" name="initB" id="initB" value="<?= $lang['Recharger_joueurs'] ?>" />
					</div>			
				</div>			
			</div>
			<div id="tabs-2" class="tabs_content">
				<table class="maxWidth" id="deroulement_match">
					<tr>
						<th colspan="3">
							<span class="match"></span>
                            <span class="pull-left">
                                <a id="ATT" class="fm_bouton statut<?php if($statutMatch == 'ATT') echo ' actif'; ?>"><?= $lang['En_attente']; ?></a>
                                <a id="ON" class="fm_bouton statut<?php if($statutMatch == 'ON') echo ' actif'; ?>"><?= $lang['En_cours']; ?></a>
                                <a id="END" class="fm_bouton statut<?php if($statutMatch == 'END') echo ' actif'; ?>"><?= $lang['Termine']; ?></a>
                                <span class="endmatch"><?= $lang['Fin'] ?> : </span><input type="tel" id="end_match_time" class="fm_input_text endmatch" value="<?= $row['Heure_fin']; ?>" />
                            </span>
                            <span class="pull-right">
                                <a id="M1" class="fm_bouton periode<?php if($periodeMatch == 'M1') echo ' actif'; ?>"><?= $lang['period_M1']; ?></a>
                                <a id="M2" class="fm_bouton periode<?php if($periodeMatch == 'M2') echo ' actif'; ?>"><?= $lang['period_M2']; ?></a>
                                <a id="P1" class="fm_bouton periode<?php if($periodeMatch == 'P1') echo ' actif'; ?>"><?= $lang['period_P1']; ?></a>
                                <a id="P2" class="fm_bouton periode<?php if($periodeMatch == 'P2') echo ' actif'; ?>"><?= $lang['period_P2']; ?></a>
                                <a id="TB" class="fm_bouton periode<?php if($periodeMatch == 'TB') echo ' actif'; ?>"><?= $lang['period_TB']; ?></a>
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
							<a class="fm_bouton equipes" data-equipe="A" data-player="<?= $lang['Equipe']; ?> A"><?= $lang['Equipe']; ?> A<br />
								<img src="../img/Pays/<?= $paysA; ?>.png" width="25" height="16" /> <?= $row['equipeA']; ?>
								<span class="score" id="scoreA">0</span>
							</a>
							
							<?php 			
								$joueur_temp = '';
								$entr_temp = '';
								for ($i=1;$i<=$num_results3;$i++)
								{
									$row3 = mysql_fetch_array($result3);
									if($row3["Capitaine"] != 'E'){
										$joueur_temp  = '<a id="A'.$row3["Matric"].'" data-equipe="A" data-player="'.utyUcWordNomCompose($row3["Nom"]).' '.$row3["Prenom"][0].'." data-id="'.$row3["Matric"].'" data-nb="'.$row3["Numero"].'" class="fm_bouton joueurs">';
										$joueur_temp .= '<span class="NumJoueur">'.$row3["Numero"].'</span> - '.utyUcWordNomCompose($row3["Nom"]).' '.$row3["Prenom"][0].'.<span class="StatutJoueur">';
										if($row3["Capitaine"] == 'C')
											$joueur_temp .= ' (Cap.)';
										$joueur_temp .= '</span><span class="c_evt"></span></a>';
									}else{
										$entr_temp .= '<a id="A'.$row3["Matric"].'" data-equipe="A" data-player="'.utyUcWordNomCompose($row3["Nom"]).' '.$row3["Prenom"][0].'." data-id="'.$row3["Matric"].'" data-nb="'.$row3["Numero"].'" class="fm_bouton joueurs coach">';
										$entr_temp .= utyUcWordNomCompose($row3["Nom"]).' '.$row3["Prenom"][0].'.<span class="StatutJoueur"> (Coach)</span>';
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
								<!--<span id="chronoText"><?= $lang['Chrono'] ?> : </span>-->
								<span class="icon_parametres" id="dialog_ajust_opener" title="<?= $lang['Parametres_chrono'] ?>"></span>
                                <input type="tel" id="heure" class="fm_input_text" readonly />
                                <img id="chrono_plus" class="plusmoins" src="../img/plus1.png" alt="">
                                <img id="chrono_plus10" class="plusmoins" src="../img/plus10.png" alt="">
                                <div id="updateChrono" class="centre"><img src="v2/valider.gif"></div>
								
								<a id="start_button" class="fm_bouton chronoButton">Start</a>
								<a id="run_button" class="fm_bouton chronoButton">Run</a>
								<a id="stop_button" class="fm_bouton chronoButton">Stop</a>
								<a id="raz_button" class="fm_bouton chronoButton"><?= $lang['RAZ'] ?></a>
							</div>
							<div id="zoneEvt">
								<a id="evt_but" data-evt="But" data-code="B" class="fm_bouton evtButton"><span class="but"><?= $lang['But'] ?></span></a>
								<a id="evt_vert" data-evt="Carton vert" data-code="V" class="fm_bouton evtButton"><img src="v2/carton_vert.png" /></a>
								<!--<a id="evt_tir" data-evt="Tir" data-code="T" class="fm_bouton evtButton" title="<?= $lang['Tir_non_cadre'] ?>"><?= $lang['Tir'] ?></a>-->
								<a id="evt_jaune" data-evt="Carton jaune" data-code="J" class="fm_bouton evtButton"><img src="v2/carton_jaune.png" /></a>
								<!--<a id="evt_arr" data-evt="Arret" data-code="A" class="fm_bouton evtButton" title="<?= $lang['Tir_contre_gardien'] ?>"><?= $lang['Tir_contre'] ?></a>-->
								<a id="evt_rouge" data-evt="Carton rouge" data-code="R" class="fm_bouton evtButton"><img src="v2/carton_rouge.png" /></a>
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
								<a id="update_evt" data-id="" class="fm_bouton evtButton2"><img src="v2/b_edit.png" /> <?= $lang['Modifier'] ?></a>
								<a id="valid_evt" class="fm_bouton evtButton2 evtButton3">OK</a>
								<a id="delete_evt" class="fm_bouton evtButton2"><img src="v2/supprimer.gif" /> <?= $lang['Supp'] ?></a>
								<a id="reset_evt" class="fm_bouton evtButton2"><?= $lang['Annuler'] ?></a>
                                <a id="liste_evt" class="fm_bouton evtButton2"><?= $lang['Liste'] ?> <img id="list_down" src="../img/down.png"><img id="list_up" src="../img/up.png"></a>
							</div>
						</td>
						<td id="selectionB">
							<a class="fm_bouton equipes" data-equipe="B" data-player="<?= $lang['Equipe']; ?> B">
								<span class="score" id="scoreB">0</span><?= $lang['Equipe']; ?> B<br />
								<img src="../img/Pays/<?= $paysB; ?>.png" width="25" height="16" /> <?= $row['equipeB']; ?>
							</a>
							<?php 			
								$joueur_temp = '';
								$entr_temp = '';
								for ($i=1;$i<=$num_results4;$i++)
								{
									$row4 = mysql_fetch_array($result4);
									if($row4["Capitaine"] != 'E'){
										$joueur_temp  = '<a id="B'.$row4["Matric"].'" data-equipe="B" data-player="'.utyUcWordNomCompose($row4["Nom"]).' '.$row4["Prenom"][0].'." data-id="'.$row4["Matric"].'" data-nb="'.$row4["Numero"].'" class="fm_bouton joueurs">';
										$joueur_temp .= '<span class="NumJoueur">'.$row4["Numero"].'</span> - '.utyUcWordNomCompose($row4["Nom"]).' '.$row4["Prenom"][0].'.<span class="StatutJoueur">';
										if($row4["Capitaine"] == 'C')
											$joueur_temp .= ' (Cap.)';
										$joueur_temp .= '</span><span class="c_evt"></span></a>';
									}else{
										$entr_temp .= '<a id="B'.$row4["Matric"].'" data-equipe="B" data-player="'.utyUcWordNomCompose($row4["Nom"]).' '.$row4["Prenom"][0].'." data-id="'.$row4["Matric"].'" data-nb="'.$row4["Numero"].'" class="fm_bouton joueurs coach">';
										$entr_temp .= utyUcWordNomCompose($row4["Nom"]).' '.$row4["Prenom"][0].'.<span class="StatutJoueur"> (Coach)</span>';
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
									<th class="list_evt_v"><?= $lang['V'] ?></th>
									<th class="list_evt_j"><?= $lang['J'] ?></th>
									<th class="list_evt_r"><?= $lang['R'] ?></th>
									<th class="list_nom"><?= $lang['Equipe'] ?> A</th>
									<th class="list_evt_b"><?= $lang['B'] ?></th>
									<th class="list_chrono" id="change_ordre" title="<?= $lang['Changer_ordre'] ?>"><?= $lang['Temps'] ?> <img src="../img/up.png" /></th>
									<th class="list_evt_b"><?= $lang['B'] ?></th>
									<th class="list_nom"><?= $lang['Equipe'] ?> B</th>
									<th class="list_evt_v"><?= $lang['V'] ?></th>
									<th class="list_evt_j"><?= $lang['J'] ?></th>
									<th class="list_evt_r"><?= $lang['R'] ?></th>
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
                                    if($row5['motif'] != '') {
                                        $row5['motif_texte'] = ' (' . $lang[$row5['motif']] . ')';
                                    } else {
                                        $row5['motif_texte'] = '';
                                    }
                                    if($row5["Id_evt_match"] != 'A' && $row5["Id_evt_match"] != 'T') {
                                        $evtEquipe = $row5['Equipe_A_B'];
                                        if($row5['Competiteur'] == '0'){
                                            $row5["Numero"] = '';
                                            $row5["Separatif"] = '';
                                            $row5["Nom"] = $lang['Equipe'];
                                            $row5["Prenom"] = $evtEquipe;
                                        } else {
                                            $row5["Separatif"] = ' - ';
                                        }
                                        $evt_temp  = '<tr id="ligne_'.$row5["Id"].'" data-code="'.$row5["Periode"].';'
                                                .substr($row5["Temps"],-5).';'.$row5["Id_evt_match"].';'.$evtEquipe.';'
                                                .$row5["Competiteur"].';'.$row5["Numero"].';'.$row5["motif"].'">';
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
                                            $evt_temp .= '<td class="list_nom">'.$row5["Numero"].$row5["Separatif"]
                                                    .utyUcWordNomCompose($row5["Nom"]).' '
                                                    .$row5["Prenom"][0].'.'
                                                    .$row5['motif_texte'];
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
                                            $evt_temp .= '<td class="list_nom">'.$row5["Numero"].$row5["Separatif"]
                                                    .utyUcWordNomCompose($row5["Nom"]).' '
                                                    .$row5["Prenom"][0].'.'
                                                    .$row5['motif_texte'];
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
				<?= $lang['Commentaires'] ?> :
				<div id="comments"><?= $row['Commentaires_officiels'];?></div>
				<br />
				<br />
				<br />
			</div>
            
            <!-- Modales -->
            <div id="dialog_ajust" title="<?= $lang['Parametres_chrono'] ?>">
                <h3 id="dialog_ajust_periode">
                </h3>
                <p>
                    <?= $lang['Ajuster_chrono'] ?> : <input type="tel" id="chrono_ajust" class="fm_input_text" />
                </p>
                <p>
                    <?= $lang['Duree_periode'] ?> : <input type="tel" id="periode_ajust" class="fm_input_text" />
                </p>
            </div>
            <div id="dialog_end" title="<?= $lang['Fin_periode'] ?>">
                <p class="centre">
                    <span class="fm_input_text" id="periode_end">00:00</span><br /><?= $lang['Periode_terminee'] ?>
                </p>
            </div>
            <div id="dialog_end_match" title="<?= $lang['Fin_match'] ?>">
                <p class="centre">
                    <?= $lang['Heure_fin_match'] ?> : <input type="tel" id="time_end_match" class="fm_input_text" />
                </p>
                <p class="centre">
                    <?= $lang['Commentaires_officiels'] ?> :<br />
                    <textarea id="commentaires" rows="4" cols="50"></textarea>
                </p>
            </div>
            <div id="dialog_motif" title="<?= $lang['Motif_carton'] ?>">
                    <?php foreach ($motifs_cartons as $value) {
                        echo "
                            <div class='motifCarton fm_bouton' data-motif='" . $value . "' data-texte='" . $lang[$value] . "'>
                                <img src='../img/referees/" . $value . ".png'>
                                <br>" . $lang[$value . '_lg'] . "
                            </div>";
                    }
                    ?>
                    <input type="hidden" id="motif" value="">
                    <input type="hidden" id="motif_texte" value="">
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
            var idMatch = <?= $idMatch ?>;
            var idEquipeA = <?= $row['Id_equipeA'] ?>;
            var idEquipeB = <?= $row['Id_equipeB'] ?>;
            var typeMatch = "<?= $typeMatch ?>";
            var statutMatch = "<?= $statutMatch ?>";
            var publiMatch = "<?= $publiMatch ?>";
            var periode_en_cours = "<?= $periodeMatch ?>";
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
				$('#end_match_time').val('<?= substr($heure_fin,-5,2).'h'.substr($heure_fin,-2) ?>');
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
