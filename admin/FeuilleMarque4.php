<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion de la Feuille de Match
class GestionMatchDetail4 extends MyPageSecure	 
{	

	function InitTitulaireEquipe($numEquipe, $idMatch, $idEquipe, $bdd)
	{
		$sql = "Select Count(*) Nb From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = '$numEquipe' ";
		$result = $bdd->Query($sql) or die ("Erreur Select");

		if ($bdd->NumRows($result) != 1)
			return;
			
        $row = $bdd->FetchArray($result, $resulttype = MYSQL_ASSOC);
		if ((int) $row['Nb'] > 0) {
            return;
        }

        $sql  = "Replace Into gickp_Matchs_Joueurs ";
		$sql .= "Select $idMatch, Matric, Numero, '$numEquipe', Capitaine From gickp_Competitions_Equipes_Joueurs ";
		$sql .= "Where Id_equipe = $idEquipe ";
		$sql .= "AND Capitaine <> 'X' ";
		$sql .= "AND Capitaine <> 'A' ";
		$bdd->Query($sql) or die ("Erreur Replace InitTitulaireEquipe");
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
            header("Location: SelectFeuille.php?target=FeuilleMarque4.php");
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
		$result = $myBdd->Query($sql) or die ("Erreur Select<br />".$sql);
		$row = $myBdd->FetchArray($result, $resulttype = MYSQL_ASSOC);
		$saison = $row['saison'];
		$statutMatch = $row['statutMatch'];
		$publiMatch = $row['PubliMatch'];
		$periodeMatch = $row['periodeMatch'];
		$typeMatch = $row['typeMatch'];
		$heure_fin = $row['Heure_fin'];
		if (!isset($row['saison'])) {
            die( $lang['Numero_non_valide'] . $inputText );
        }
		if ($row['Id_equipeA'] < 1 || $row['Id_equipeB'] < 1) {
            die($lang['Equipes_non_affectees'] . $inputText);
        }
        if ($row['ScoreA'] == '') {
            $row['ScoreA'] = 0;
        }
        if ($row['ScoreB'] == '') {
            $row['ScoreB'] = 0;
        }
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
		if (is_numeric($paysA[0]) || is_numeric($paysA[1]) || is_numeric($paysA[2])) {
            $paysA = 'FRA';
        }
        $paysB = substr($row['clubB'], 0, 3);
		if (is_numeric($paysB[0]) || is_numeric($paysB[1]) || is_numeric($paysB[2])) {
            $paysB = 'FRA';
        }

        // Compo équipe A
        if ($row['Id_equipeA'] >= 1) {
            $this->InitTitulaireEquipe('A', $idMatch, $row['Id_equipeA'], $myBdd);
        }
        $sql3  = "Select a.Matric, a.Numero, a.Capitaine, b.Matric, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Origine, c.Matric Matric_titulaire ";
        $sql3 .= "From gickp_Matchs_Joueurs a ";
        $sql3 .= "Left Outer Join gickp_Competitions_Equipes_Joueurs c On (c.Id_equipe = ".$row['Id_equipeA']." And c.Matric = a.Matric), "; 
        $sql3 .= "gickp_Liste_Coureur b ";
        $sql3 .= "Where a.Matric = b.Matric ";
        $sql3 .= "And a.Id_match = $idMatch ";
        $sql3 .= "And a.Equipe = 'A' ";
        $sql3 .= "Order By Numero, Nom, Prenom ";	 
		$result3 = $myBdd->Query($sql3) or die ("Erreur Load 3");
        // Compo équipe B
        if ($row['Id_equipeB'] >= 1) {
            $this->InitTitulaireEquipe('B', $idMatch, $row['Id_equipeB'], $myBdd);
        }
        $sql4  = "Select a.Matric, a.Numero, a.Capitaine, b.Matric, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Origine, c.Matric Matric_titulaire ";
        $sql4 .= "From gickp_Matchs_Joueurs a ";
        $sql4 .= "Left Outer Join gickp_Competitions_Equipes_Joueurs c On (c.Id_equipe = ".$row['Id_equipeB']." And c.Matric = a.Matric), "; 
        $sql4 .= "gickp_Liste_Coureur b ";
        $sql4 .= "Where a.Matric = b.Matric ";
        $sql4 .= "And a.Id_match = $idMatch ";
        $sql4 .= "And a.Equipe = 'B' ";
        $sql4 .= "Order By Numero, Nom, Prenom ";	 
		$result4 = $myBdd->Query($sql4) or die ("Erreur Load 4");

        // Evts
        $sql5  = "Select d.Id, d.Id_match, d.Periode, d.Temps, d.Id_evt_match, d.Competiteur, d.Numero, d.Equipe_A_B, ";
        $sql5 .= "c.Nom, c.Prenom ";
        $sql5 .= "From gickp_Matchs_Detail d Left Outer Join gickp_Liste_Coureur c On d.Competiteur = c.Matric ";
        $sql5 .= "Where d.Id_match = $idMatch ";
        //$sql5 .= "AND d.Equipe_A_B = 'A' ";
        $sql5 .= "Order By d.Periode DESC, d.Temps ASC, d.Id ";
		$result5 = $myBdd->Query($sql5) or die ("Erreur Load 5");

        
?>
<!doctype html>
<html lang="fr">
    <head>
		<meta charset="utf-8">
		<title><?= $lang['Match']; ?> #<?= $row['Numero_ordre']; ?></title>
		<!--<link href="v2/jquery-ui.min.css" rel="stylesheet">-->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link href="../js/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="../css/fontawesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="../js/fullPage/jquery.fullpage.min.css" rel="stylesheet" rel="stylesheet">
		<link href="../css/jquery.dataTables.css" rel="stylesheet">
        <link href="../css/feuillemarque4.css" rel="stylesheet" type="text/css">
		<?php if($verrou != 'O') { ?>
			<link href="v2/fmv2O.css" rel="stylesheet">
		<?php	}	?>
	</head>
	<body>
        <div class="container-fluid">
            <form class="form-inline">
                <div id="avert"></div>
                <ul class="nav nav-pills" role="tablist">
                    <li role="presentation" class="pull-right active">
                        <a href="#page-2" aria-controls="page-2" role="tab" data-toggle="tab"><?= $lang['Deroulement_match']; ?></a>
                    </li>
                    <li role="presentation" class="pull-right">
                        <a href="#page-1" aria-controls="page-1" role="tab" data-toggle="tab"><?= $lang['Parametres_match']; ?></a>
                    </li>
                    <li class="navbar-text h4">
                        <?php 
                            echo '<span class="hidden-sm hidden-xs">' . $row['Code_competition'];
                            if($row['Code_typeclt'] == 'CHPT')
                                echo ' ('.$row['Lieu'].')';
                            elseif($row['Soustitre2'] != '')
                                echo ' ('.$row['Soustitre2'].')';
                            if($row['Phase'] != '')
                                echo ' - '.$row['Phase'];
                            echo ' - </span>' . $lang['Terrain'] . ' ' . $row['Terrain'];
                            echo ' | ' . $lang['Match_no'] . $row['Numero_ordre'] . '<span class="hidden-sm hidden-xs">' . ' - ';
                            if($version == 'en') {
                                echo $row['Date_match'];
                            } else {
                                echo utyDateUsToFr($row['Date_match']);
                            }
                            echo ' ' . $lang['a_'] . '</span> ' . $row['Heure_match']; 
                        ?>
                    </li>
                </ul>
            </form>
            <div class="tab-content">
                <div role="tabpanel" id="page-1" class="tab-pane">
                    <form class="form-inline">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#parametres" aria-controls="parametres" role="tab" data-toggle="tab"><?= $lang['Parametres_match']; ?></a></li>
                            <li role="presentation"><a href="#officiels" aria-controls="officiels" role="tab" data-toggle="tab"><?= $lang['Officiels']; ?></a></li>
                            <li role="presentation"><a href="#compoA" aria-controls="compoA" role="tab" data-toggle="tab"><img src="../img/Pays/<?= $paysA; ?>.png" width="25" height="16"> <?= $row['equipeA']; ?></a></li>
                            <li role="presentation"><a href="#compoB" aria-controls="compoB" role="tab" data-toggle="tab"><img src="../img/Pays/<?= $paysB; ?>.png" width="25" height="16"> <?= $row['equipeB']; ?></a></li>
                            <li class="navbar-text">ID# <?= $idMatch; ?></li>
                        </ul>
                        <div class="bg-warning text-center"><?= $lang['A_remplir']; ?></div>

                        <div class="tab-content">
                            
                            <!-- Panneau paramètres -->
                            <div role="tabpanel" class="tab-pane active" id="parametres">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <br>
                                        <div class="btn-group" role="group">
                                            <button type="button" name="typeMatchClassement" id="typeMatchClassement" title="<?= $lang['Egalite_possible']; ?>" class="btn <?php if($row['Type_match'] == 'C') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['Match_classement']; ?></button>
                                            <button type="button" name="typeMatchElimination" id="typeMatchElimination" title="<?= $lang['Vainqueur_obligatoire']; ?>" class="btn <?php if($row['Type_match'] == 'E') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['Match_elimination']; ?></button>
                                        </div>
                                        <img id="typeMatchImg" style="vertical-align:middle;" title="<?php if($row['Type_match'] == 'C'){ echo $lang['Match_classement']; }else{ echo $lang['Match_elimination'];} ?>" alt="<?= $lang['Type_match']; ?>" src="../img/type<?= $row['Type_match']; ?>.png" />
                                        <br>
                                        <br>
                                        <?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
                                            <div class="btn-group" role="group">
                                                <button type="button" name="publiMatch" id="prive" class="btn <?php if($publiMatch != 'O') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['Prive']; ?></button>
                                                <button type="button" name="publiMatch" id="public" class="btn <?php if($publiMatch == 'O') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['Public']; ?></button>
                                            </div>
                                            <img height="30" style="vertical-align:middle;" title="<?= $lang['Publier']; ?> ?" alt="<?= $lang['Publier']; ?> ?" src="../img/oeil2<?php if($publiMatch == 'O'){ echo 'O';} else {echo 'N';} ?>.gif" />
                                        <?php } ?>
                                        <br />
                                        <br />
                                        <input class="btn btn-default" type="button" id="btn_stats" name="btn_stats" value="Stats" />
                                        <input class="btn btn-default" type="button" id="pdfFeuille" name="pdfFeuille" value="PDF" />
                                        <a class="btn btn-default" href="../lang.php?lang=fr&p=fm4&idMatch=<?= $idMatch; ?>"><img src="../img/Pays/FRA.png" height="25" align="bottom"></a>
                                        <a class="btn btn-default" href="../lang.php?lang=en&p=fm4&idMatch=<?= $idMatch; ?>"><img src="../img/Pays/GBR.png" height="25" align="bottom"></a>
                                        <br />
                                        <br />
                                        <div class="form-group">
                                            <label><?= $lang['Charger_autre_feuille']; ?> :</label>
                                            <br />
                                            ID# <input class="form-control" type="tel" id="idFeuille" />
                                            <input class="form-control" type="button" id="chargeFeuille" value="<?= $lang['Charger']; ?>" />
                                        </div>
                                    </div>
                                
                                    <div class="col-sm-6">
                                        <br>
                                        <label><?= $lang['Score_officiel']; ?> :</label>
                                        <br />
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-default"><?= $row['equipeA']; ?></button>
                                            <button type="button" class="btn btn-info" id="scoreA4"><?= $row['ScoreA']; ?></button>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-info" id="scoreB4"><?= $row['ScoreA']; ?></button>
                                            <button type="button" class="btn btn-default"><?= $row['equipeB']; ?></button>
                                        </div>
                                        <br />
                                        <br />
                                        <label><?= $lang['Score_provisoire']; ?> :</label>
                                        <br />
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-default"><?= $row['equipeA']; ?></button>
                                            <button type="button" class="btn btn-primary" id="scoreA3"><?= $row['ScoreA']; ?></button>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary" id="scoreB3"><?= $row['ScoreA']; ?></button>
                                            <button type="button" class="btn btn-default"><?= $row['equipeB']; ?></button>
                                        </div>
                                        <br />
                                        <?php if($verrou != 'O') { ?>
                                            <input class="form-control" type="button" id="validScore" name="validScore" value="<?= $lang['Valider_score']; ?>" />
                                        <?php } ?>
                                        <br />
                                        <br />
                                        <label title="<?= $lang['PC_Course_seulement']; ?>"><?= $lang['Controle_match']; ?> : </label>
                                        <br />
                                        <?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
                                            <div class="btn-group" role="group">
                                                <button type="button" name="controleMatch" id="controleOuvert" class="btn <?php if($verrou != 'O') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['Ouvert']; ?></button>
                                                <button type="button" name="controleMatch" id="controleVerrou" class="btn <?php if($verrou == 'O') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['Verrouille']; ?></button>
                                            </div>
                                            <img height="30" style="vertical-align:middle;" title="<?= $lang['Verrouille']; ?> ?" alt="<?= $lang['Verrouille']; ?> ?" src="../img/verrou2<?php if($verrou == 'O'){ echo 'O';} else {echo 'N';} ?>.gif" />
                                        <?php } ?>
                                        
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Panneau officiels -->
                            <div role="tabpanel" class="tab-pane" id="officiels">
                                <div class="col-sm-6">
                                    <br>
                                    <label><?= $lang['Secretaire']; ?> : </label>
                                    <br><span class="editOfficiel" id="Secretaire"><?= $row['Secretaire']; ?></span>
                                    <br>
                                    <label><?= $lang['Chronometre']; ?> : </label>
                                    <br><span class="editOfficiel" id="Chronometre"><?= $row['Chronometre']; ?></span>
                                    <br>
                                    <label><?= $lang['Time_shoot']; ?> : </label>
                                    <br><span class="editOfficiel" id="Timeshoot"><?= $row['Timeshoot']; ?></span>
                                    <br>
                                    <br>
                                    <label><?= $lang['Arbitre_1']; ?> : </label>
                                    <br><span class="editArbitres" id="Arbitre_principal"><?= $row['Arbitre_principal']; ?></span>
                                    <br>
                                    <label><?= $lang['Arbitre_2']; ?> : </label>
                                    <br><span class="editArbitres" id="Arbitre_secondaire"><?= $row['Arbitre_secondaire']; ?></span>
                                    <br>
                                    <label><?= $lang['Ligne']; ?> : </label>
                                    <br><span class="editOfficiel" id="Ligne1"><?= $row['Ligne1']; ?></span>
                                    <br>
                                    <label><?= $lang['Ligne']; ?> : </label>
                                    <br><span class="editOfficiel" id="Ligne2"><?= $row['Ligne2']; ?></span>
                                    <br>
                                </div>
                                <div class="col-sm-6">
                                    <br>
                                    <label><?= $lang['Club_organisateur']; ?> : </label><?= $row['Organisateur']; ?>
                                    <br>
                                    <label><?= $lang['R1'] ?> : </label><?= $row['Responsable_R1']; ?>
                                    <br>
                                    <label><?= $lang['Delegue'] ?> : </label><?= $row['Delegue']; ?>
                                    <br>
                                    <label><?= $lang['Chef_arbitre'] ?> : </label><?= $row['ChefArbitre']; ?>
                                    <br>
                                    <label><?= $lang['RC'] ?> : </label><?= $row['Responsable_insc']; ?>
                                    <br>
                                    <br>
                                </div>
                            </div>
                            
                            <!-- Panneau compoA -->
                            <div role="tabpanel" class="tab-pane" id="compoA">
                                <h3><?= $lang['Equipe'] ?> A - <img src="../img/Pays/<?= $paysA; ?>.png" width="25" height="16"> <?= $row['equipeA']; ?>								
                                    <span class="label label-primary" id="scoreA2"><?= $row['ScoreA']; ?></span>
                                </h3>
                                <table class="table table-condensed" id="equipeA">
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
                                        while ($row3 = $myBdd->FetchArray($result3, $resulttype = MYSQL_ASSOC)){ 
                                            $age = utyCodeCategorie2($row3["Naissance"], $saison);
                                            if($row3["Capitaine"] != 'E'){
                                                $joueur_temp  = '<tr>';
                                                $joueur_temp .= '<td><span class="btn btn-default btn-xs editNo" id="No-'.$row3["Matric"].'">'.$row3["Numero"].'</span></td>';
                                                $joueur_temp .= '<td><span class="btn btn-default btn-xs editStatut" id="Statut-'.$row3["Matric"].'">'.$row3["Capitaine"].'</span></td>';
                                                $joueur_temp .= '<td>'.ucwords(strtolower($row3["Nom"])).'</td>';
                                                $joueur_temp .= '<td>'.ucwords(strtolower($row3["Prenom"])).'</td>';
                                                $joueur_temp .= '<td>';
                                                if($row3["Matric"] < 2000000)
                                                    $joueur_temp .= $row3["Matric"];
                                                $joueur_temp .= '</td>';
                                                $joueur_temp .= '<td>'.$age.'</td>';
                                                $joueur_temp .= '<td><a href="#" class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-A-'.$row3["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
                                                $joueur_temp .= '</tr>';
                                            }else{
                                                $entr_temp  = '<tr>';
                                                $entr_temp .= '<td><span class="btn btn-default btn-xs editNo" id="No-'.$row3["Matric"].'">'.$row3["Numero"].'</span></td>';
                                                $entr_temp .= '<td><span class="btn btn-default btn-xs editStatut" id="Statut-'.$row3["Matric"].'">'.$row3["Capitaine"].'</span></td>';
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
                                        }
                                        echo $entr_temp;
                                        mysql_data_seek($result3,0); 
                                    ?>
                                    </tbody>
                                </table>
                                <input class="btn btn-default" type="button" name="initA" id="initA" value="<?= $lang['Recharger_joueurs'] ?>" />
                            </div>	
                            
                            <!-- Panneau compoB -->
                            <div role="tabpanel" class="tab-pane" id="compoB">
                                <h3><?= $lang['Equipe'] ?> B - <img src="../img/Pays/<?= $paysB; ?>.png" width="25" height="16" /> <?= $row['equipeB']; ?>								
                                    <span class="label label-primary" id="scoreB2"><?= $row['ScoreA']; ?></span>
                                </h3>
                                <table class="table table-condensed" id="equipeB">
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
                                        while ($row4 = $myBdd->FetchArray($result4, $resulttype = MYSQL_ASSOC)){ 
                                            $age = utyCodeCategorie2($row4["Naissance"], $saison);
                                            if($row4["Capitaine"] != 'E'){
                                                $joueur_temp  = '<tr>';
                                                $joueur_temp .= '<td><span class="btn btn-default btn-xs editNo" id="No-'.$row4["Matric"].'">'.$row4["Numero"].'</span></td>';
                                                $joueur_temp .= '<td><span class="btn btn-default btn-xs editStatut" id="Statut-'.$row4["Matric"].'">'.$row4["Capitaine"].'</span></td>';
                                                $joueur_temp .= '<td>'.ucwords(strtolower($row4["Nom"])).'</td>';
                                                $joueur_temp .= '<td>'.ucwords(strtolower($row4["Prenom"])).'</td>';
                                                $joueur_temp .= '<td>';
                                                if($row4["Matric"] < 2000000)
                                                    $joueur_temp .= $row4["Matric"];
                                                $joueur_temp .= '</td>';
                                                $joueur_temp .= '<td>'.$age.'</td>';
                                                $joueur_temp .= '<td><a href="#" class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-B-'.$row4["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
                                                $joueur_temp .= '</tr>';
                                            }else{
                                                $entr_temp  = '<tr>';
                                                $entr_temp .= '<td><span class="btn btn-default btn-xs editNo" id="No-'.$row4["Matric"].'">'.$row4["Numero"].'</span></td>';
                                                $entr_temp .= '<td><span class="btn btn-default btn-xs editStatut" id="Statut-'.$row4["Matric"].'">'.$row4["Capitaine"].'</span></td>';
                                                $entr_temp .= '<td>'.ucwords(strtolower($row4["Nom"])).'</td>';
                                                $entr_temp .= '<td>'.ucwords(strtolower($row4["Prenom"])).'</td>';
                                                $entr_temp .= '<td>';
                                                if($row4["Matric"] < 2000000)
                                                    $entr_temp .= $row4["Matric"];
                                                $entr_temp .= '</td>';
                                                $entr_temp .= '<td>'.$age.'</td>';
                                                $entr_temp .= '<td><a href="#" class="suppression" title="'.$lang['Suppression_joueur'].'" id="Supp-B-'.$row4["Matric"].'"><img src="v2/images/trash.png" width="20" /></a></td>';
                                                $entr_temp .= '</tr>';
                                                $joueur_temp = '';
                                            }
                                            echo $joueur_temp;
                                        }
                                        echo $entr_temp;
                                        mysql_data_seek($result4, 0);
                                    ?>
                                    </tbody>
                                </table>
                                <input class="btn btn-default" type="button" name="initB" id="initB" value="<?= $lang['Recharger_joueurs'] ?>" />
                            </div>			
                        </div>			
                    </form>
                </div>
                <!-- Déroulement du match -->
                <div role="tabpanel" id="page-2" class="tab-pane active">
                    <form class="form-inline" id="fullpage">
                        <div class="section">
                            <div class="row text-center">
                                <div class="btn-group">
                                    <a href="#" id="ATT" class="statut btn btn-lg <?php if($statutMatch == 'ATT') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['En_attente']; ?></a>
                                    <a href="#" id="ON" class="statut btn btn-lg <?php if($statutMatch == 'ON') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['En_cours']; ?></a>
                                    <a href="#" id="END" class="statut btn btn-lg <?php if($statutMatch == 'END') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['Termine']; ?></a>
                                </div>
                                <label class="endmatch"><?= $lang['Fin'] ?> : </label>
                                <input type="time" id="end_match_time" class="form-control endmatch input-time" value="<?= substr($row['Heure_fin'], -5); ?>" />
                                &nbsp;
                                <div class="btn-group">
                                    <a href="#" id="M1" class="periode btn btn-lg <?php if($periodeMatch == 'M1') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['period_M1']; ?></a>
                                    <a href="#" id="M2" class="periode btn btn-lg <?php if($periodeMatch == 'M2') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['period_M2']; ?></a>
                                    <a href="#" id="P1" class="periode btn btn-lg <?php if($periodeMatch == 'P1') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['period_P1']; ?></a>
                                    <a href="#" id="P2" class="periode btn btn-lg <?php if($periodeMatch == 'P2') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['period_P2']; ?></a>
                                    <a href="#" id="TB" class="periode btn btn-lg <?php if($periodeMatch == 'TB') { echo 'btn-primary'; }else{ echo 'btn-default'; } ?>"><?= $lang['period_TB']; ?></a>
                                </div>
                                <!-- CHRONO DEBUG
                                <br />
                                start_time: <span id="start_time_display"></span><br />
                                run_time: <span id="run_time_display"></span><br />
                                stop_time: <span id="stop_time_display"></span><br />

                                -->
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-xs-4">
                                    <a href="#" class="btn btn-default btn-block equipes" data-equipe="A" data-player="Equipe A">
                                        <span class="score label label-primary pull-right" id="scoreA">0</span>
                                        <?= $lang['Equipe']; ?> A<br>
                                        <img src="../img/Pays/<?= $paysA; ?>.png" width="25" height="16" /> <?= $row['equipeA']; ?>
                                    </a>
                                    <br>
                                    <div class="btn-group-vertical btn-block" role="group">
                                        <?php 			
                                            $joueur_temp = '';
                                            $entr_temp = '';
                                            while ($row3 = $myBdd->FetchArray($result3, $resulttype = MYSQL_ASSOC)){ 
                                                if($row3["Capitaine"] != 'E'){
                                                    $joueur_temp  = '<a href="#" id="A'.$row3["Matric"].'" data-equipe="A" data-player="'.ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'." data-id="'.$row3["Matric"].'" data-nb="'.$row3["Numero"].'" class="btn btn-default btn-block joueurs">';
                                                    $joueur_temp .= '<span class="NumJoueur">'.$row3["Numero"].'</span> - '.ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'.<span class="StatutJoueur">';
                                                    if($row3["Capitaine"] == 'C')
                                                        $joueur_temp .= ' (Cap.)';
                                                    $joueur_temp .= '</span><span class="c_evt"></span></a>';
                                                }else{
                                                    $entr_temp = '<br><a href="#" id="A'.$row3["Matric"].'" data-equipe="A" data-player="'.ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'." data-id="'.$row3["Matric"].'" data-nb="'.$row3["Numero"].'" class="btn btn-default btn-block joueurs">';
                                                    $entr_temp .= ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'.<span class="StatutJoueur"> (Coach)</span>';
                                                    $entr_temp .= '<span class="c_evt"></span></a>';
                                                    $joueur_temp = '';
                                                }
                                                echo $joueur_temp;
                                            }
                                            echo $entr_temp;
                                        ?>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div id="zoneChrono" class="text-center">
                                        <div class="row">
                                            <label id="chronoText"><?= $lang['Chrono'] ?> : </label>
                                            <span id="chrono_moins" class="badge">-1</span>
                                            <span id="updateChrono" class="fa fa-check-circle-o fa-2x"></span>
                                            <input type="time" id="heure" class="form-control input-time" title="<?= $lang['Parametres_chrono'] ?>" value="10:00" readonly>
                                            <span class="icon_parametres" id="dialog_ajust_opener" title="<?= $lang['Parametres_chrono'] ?>"></span>
                                            <span id="chrono_plus" class="badge">+1</span>
                                        </div>
                                        <div class="btn-group btn-group-justified">
                                            <a href="#" id="start_button" class="btn btn-success btn-lg chronoButton"><span class="fa fa-play"></span> Start</a>
                                            <a href="#" id="run_button" class="btn btn-success btn-lg chronoButton"><span class="fa fa-play"></span> Run</a>
                                        </div>
                                        <div class="btn-group btn-group-justified">
                                            <a href="#" id="stop_button" class="btn btn-warning btn-lg chronoButton"><span class="fa fa-pause"></span> Stop</a>
                                            <a href="#" id="raz_button" class="btn btn-default btn-lg chronoButton"><span class="fa fa-fast-backward"></span> <?= $lang['RAZ'] ?></a>
                                        </div>
                                    </div>
                                    <div id="zoneEvt" class="text-center">
                                        <div class="btn-group btn-group-justified">
                                            <a href="#" id="evt_but" data-evt="But" data-code="B" class="btn btn-default btn-lg col-xs-2 evtButton"><span class="but"><?= $lang['But'] ?></span></a>
                                            <a href="#" id="evt_vert" data-evt="Carton vert" data-code="V" class="btn btn-default btn-lg  evtButton"><img src="v2/carton_vert.png" /></a>
                                        </div>
                                        <div class="btn-group btn-group-justified">
                                            <a href="#" id="evt_arr" data-evt="Tir contre" data-code="A" class="btn btn-default btn-lg  evtButton" title="<?= $lang['Tir_contre_gardien'] ?>"><?= $lang['Tir_contre'] ?></a>
                                            <a href="#" id="evt_jaune" data-evt="Carton jaune" data-code="J" class="btn btn-default btn-lg  evtButton"><img src="v2/carton_jaune.png" /></a>
                                        </div>
                                        <div class="btn-group btn-group-justified">
                                            <a href="#" id="evt_tir" data-evt="Tir" data-code="T" class="btn btn-default btn-lg  evtButton" title="<?= $lang['Tir_non_cadre'] ?>"><?= $lang['Tir'] ?></a>
                                            <a href="#" id="evt_rouge" data-evt="Carton rouge" data-code="R" class="btn btn-default btn-lg  evtButton"><img src="v2/carton_rouge.png" /></a>
                                        </div>
                                    </div>
                                    <div id="zoneTemps" class="text-center">
                                        <div class="row">
                                            <label><?= $lang['Temps'] ?> :</label>
                                            <span id="time_moins" class="badge">-60</span>
                                            <span id="time_moins3" class="badge">-10</span>
                                            <span id="time_moins2" class="badge">-1</span>
                                            <input type="time" size="4" class="form-control input-time" id="time_evt" value="00:00">
                                            <span id="time_plus2" class="badge">+1</span>
                                            <span id="time_plus3" class="badge">+10</span>
                                            <span id="time_plus" class="badge">+60</span>
                                        </div>
                                        <div class="btn-group btn-group-justified">
                                            <a href="#" id="update_evt" data-id="" class="btn btn-lg btn-default evtButton2"><span class="fa fa-edit"></span> <?= $lang['Modifier'] ?></a>
                                            <a href="#" id="delete_evt" class="btn btn-lg btn-default evtButton2"><span class="fa fa-remove"></span> <?= $lang['Supp'] ?>.</a>
                                        </div>
                                        <div class="btn-group btn-group-justified">
                                            <a href="#" id="valid_evt" class="btn btn-lg btn-default evtButton2 evtButton3"><span class="fa fa-check"></span> OK</a>
                                            <a href="#" id="reset_evt" class="btn btn-lg btn-default evtButton2"><span class="fa fa-ban"></span> <?= $lang['Annuler'] ?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <a href="#" class="btn btn-default btn-block equipes" data-equipe="B" data-player="Equipe B">
                                        <span class="score label label-primary pull-left" id="scoreB">0</span>
                                        <?= $lang['Equipe']; ?> B<br>
                                        <img src="../img/Pays/<?= $paysB; ?>.png" width="25" height="16" /> <?= $row['equipeB']; ?>
                                    </a>
                                    <br>
                                    <div class="btn-group-vertical btn-block" role="group">
                                        <?php 			
                                            $joueur_temp = '';
                                            $entr_temp = '';
                                            while ($row4 = $myBdd->FetchArray($result4, $resulttype = MYSQL_ASSOC)){ 
                                                if($row4["Capitaine"] != 'E'){
                                                    $joueur_temp  = '<a href="#" id="B'.$row4["Matric"].'" data-equipe="B" data-player="'.ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'." data-id="'.$row4["Matric"].'" data-nb="'.$row4["Numero"].'" class="btn btn-default btn-block joueurs">';
                                                    $joueur_temp .= '<span class="NumJoueur">'.$row4["Numero"].'</span> - '.ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'.<span class="StatutJoueur">';
                                                    if($row4["Capitaine"] == 'C')
                                                        $joueur_temp .= ' (Cap.)';
                                                    $joueur_temp .= '</span><span class="c_evt"></span></a>';
                                                }else{
                                                    $entr_temp = '<br><a href="#" id="B'.$row4["Matric"].'" data-equipe="B" data-player="'.ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'." data-id="'.$row4["Matric"].'" data-nb="'.$row4["Numero"].'" class="btn btn-default btn-block joueurs">';
                                                    $entr_temp .= ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'.<span class="StatutJoueur"> (Coach)</span>';
                                                    $entr_temp .= '<span class="c_evt"></span></a>';
                                                    $joueur_temp = '';
                                                }
                                                echo $joueur_temp;
                                            }
                                            echo $entr_temp;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row section">
                            <br />
                            <?= $lang['Commentaires'] ?> :
                            <div id="comments"><?= $row['Commentaires_officiels'];?></div>
                            <br />
                            <br />
                            <br />
                        </div>
                        
                        <div class="row section">
                            <table id="list_evt" class="table table-bordered table-condensed table-striped">
                                <thead>
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
                                </thead>
                                <tbody>
                                    <?php
                                        $evt_temp = '';
                                        while ($row5 = $myBdd->FetchArray($result5, $resulttype = MYSQL_ASSOC)){ 
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
                                                if($row5["Id_evt_match"] == 'A')
                                                    $evt_temp .= ' (tir contré)';
                                                if($row5["Id_evt_match"] == 'T')
                                                    $evt_temp .= ' (tir)';
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
                                                if($row5["Id_evt_match"] == 'A')
                                                    $evt_temp .= ' (tir contré)';
                                                if($row5["Id_evt_match"] == 'T')
                                                    $evt_temp .= ' (tir)';
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
                                    ?>
                                </tbody>
                            </table>
                            
                            
                            <table class="maxWidth" id="deroulement_match">
                                <tr>
                                    <th colspan="3">
                                        <span class="match"></span>
                                    </th>
                                </tr>
                                <tr>
                                    <td id="selectionA">
                                    </td>
                                    <td id="selectionChrono" class="centre">
                                    </td>
                                    <td id="selectionB">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <table id="list" class="maxWidth">
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Modales -->
        <div id="dialog_ajust" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?= $lang['Parametres_chrono'] ?></h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-inline">
                            <label><?= $lang['Ajuster_chrono'] ?></label>
                            <input type="tel" id="chrono_ajust" class="form-control">
                            <br>
                            <label><?= $lang['Duree_periode'] ?></label>
                            <input type="tel" id="periode_ajust" class="form-control">
                            
                            <h3 id="dialog_ajust_periode"></h3>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= $lang['Annuler'] ?></button>
                        <button type="button" class="btn btn-primary"><?= $lang['Confirmer'] ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        
        <div id="dialog_end" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?= $lang['Fin_periode'] ?></h4>
                    </div>
                    <div class="modal-body text-center">
                        <form class="form-inline">
                            <input type="tel" readonly id="periode_end" class="form-control text-center" value="00:00">
                            <br>
                            <h3><?= $lang['Periode_terminee'] ?></h3>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= $lang['Confirmer'] ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        
        <div id="dialog_end_match" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?= $lang['Fin_match'] ?></h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-inline">
                            <label><?= $lang['Heure_fin_match'] ?></label>
                            <input type="tel" id="time_end_match" class="form-control">
                            <br>
                            <label><?= $lang['Commentaires_officiels'] ?></label>
                            <textarea id="commentaires" class="form-control" rows="4" cols="50"></textarea>
                            
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= $lang['Annuler'] ?></button>
                        <button type="button" class="btn btn-primary"><?= $lang['Confirmer'] ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
             
        
		<script type="text/javascript" src="v2/jquery-1.11.0.min.js"></script>
        <script type='text/javascript' src='../js/bootstrap/js/bootstrap.min.js?v={$NUM_VERSION}'></script>
        <script src="../js/fullPage/jquery.fullpage.min.js" type="text/javascript"></script>
        <!--<script type="text/javascript" src="v2/jquery-ui-1.10.4.custom.min.js"></script>-->
		<script type="text/javascript" src="v2/jquery.jeditable.js"></script>
		<script type="text/javascript" src="v2/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="v2/jquery.maskedinput.min.js"></script>
		<script>
//            $('#fullpage').fullpage({
//                sectionsColor: ['#4A6FB1', '#939FAA', '#323539'],
//                scrollOverflow: true
//            });
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
		<script type="text/javascript" src="v2/fm2_A.js"></script>

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
        <script type="text/javascript" src="v2/fm2_B.js"></script>
    <?php } ?>
        
    <?php if($verrou != 'O') { ?>
        <script type="text/javascript" src="v2/fm2_C.js"></script>
    <?php	}	?>
        
        <script type="text/javascript" src="v2/fm2_D.js"></script>
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
				while ($row5 = $myBdd->FetchArray($result5, $resulttype = MYSQL_ASSOC)){ 
					$evtEquipe = $row5['Equipe_A_B'];
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
                        default:
                            $evt_temp_js = '';
							break;
					}
					echo $evt_temp_js;
				}
				mysql_data_seek($result5,0);
				$evtEquipe = '';

				?>
			});
		</script>
	</body>
</html>

<?php

	}

	function GestionMatchDetail4()
	{			
		MyPageSecure::MyPageSecure(10);
		$this->Load();
	}
}		  	

$page = new GestionMatchDetail4();
