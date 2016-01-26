<?php

include_once('../../commun/MyPage.php');
include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');

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
		$inputText = '<form method="GET" action="FeuilleMarque2.php" name="formFeuille" enctype="multipart/form-data">
						<input type="text" name="idMatch" /><input type="submit" value="Envoyer" />
					</form>';
		$idMatch = utyGetGet('idMatch', -1);
		$langue = parse_ini_file("../../commun/MyLang.ini", true);
		$version = utyGetSession('lang', 'FR');
		$version = utyGetGet('lang', $version);
		$_SESSION['lang'] = $version;
		$lang = $langue[$version];
		
		if($idMatch < 1)
			die ('Sélectionnez un numéro de feuille de marque !<br />'.$inputText);
		$myBdd = new MyBdd();
		// Contrôle autorisation journée
		$sql  = "SELECT m.*, m.Statut statutMatch, m.Periode periodeMatch, m.Type typeMatch, m.Heure_fin, j.*, j.Code_saison saison, c.*, m.Type Type_match, m.Validation Valid_match, m.Publication PubliMatch, ce1.Libelle equipeA, ce1.Code_club clubA, ce2.Libelle equipeB, ce2.Code_club clubB ";
		$sql .= "FROM gickp_Matchs m left outer join gickp_Competitions_Equipes ce1 on (ce1.Id = m.Id_equipeA) ";
		$sql .= "left outer join gickp_Competitions_Equipes ce2 on (ce2.Id = m.Id_equipeB), gickp_Journees j, gickp_Competitions c ";
		$sql .= "WHERE m.Id = $idMatch ";
		$sql .= "AND m.Id_journee = j.Id ";
		$sql .= "AND j.Code_competition = c.Code ";
		$sql .= "AND j.Code_saison = c.Code_saison ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select<br />".$sql);
		$row = mysql_fetch_array($result);
		$saison = $row['saison'];
		$statutMatch = $row['statutMatch'];
		$publiMatch = $row['PubliMatch'];
		$periodeMatch = $row['periodeMatch'];
		$typeMatch = $row['typeMatch'];
		$heure_fin = $row['Heure_fin'];
		if($row['ScoreA'] == '')
			$row['ScoreA'] = 0;
		if($row['ScoreB'] == '')
			$row['ScoreB'] = 0;
		if(!isset($row['Id_equipeA']))
			die ('Numéro non valide.<br />Sélectionnez une autre feuille de marque !<br />'.$inputText);
		if($row['Id_equipeA'] < 1 || $row['Id_equipeB'] < 1)
			die ('Les équipes ne sont pas affectées.<br />Sélectionnez une autre feuille de marque !<br />'.$inputText);
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
			$sql3  = "Select a.Matric, a.Numero, a.Capitaine, b.Matric, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Origine, c.Matric Matric_titulaire ";
			$sql3 .= "From gickp_Matchs_Joueurs a ";
			$sql3 .= "Left Outer Join gickp_Competitions_Equipes_Joueurs c On (c.Id_equipe = ".$row['Id_equipeA']." And c.Matric = a.Matric), "; 
			$sql3 .= "gickp_Liste_Coureur b ";
			$sql3 .= "Where a.Matric = b.Matric ";
			$sql3 .= "And a.Id_match = $idMatch ";
			$sql3 .= "And a.Equipe = 'A' ";
			$sql3 .= "Order By Numero, Nom, Prenom ";	 
			$result3 = mysql_query($sql3, $myBdd->m_link) or die ("Erreur Load");
			$num_results3 = mysql_num_rows($result3);
		// Compo équipe B
			if ($row['Id_equipeB'] >= 1)
				$this->InitTitulaireEquipe('B', $idMatch, $row['Id_equipeB'], $myBdd);
			$sql4  = "Select a.Matric, a.Numero, a.Capitaine, b.Matric, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Origine, c.Matric Matric_titulaire ";
			$sql4 .= "From gickp_Matchs_Joueurs a ";
			$sql4 .= "Left Outer Join gickp_Competitions_Equipes_Joueurs c On (c.Id_equipe = ".$row['Id_equipeB']." And c.Matric = a.Matric), "; 
			$sql4 .= "gickp_Liste_Coureur b ";
			$sql4 .= "Where a.Matric = b.Matric ";
			$sql4 .= "And a.Id_match = $idMatch ";
			$sql4 .= "And a.Equipe = 'B' ";
			$sql4 .= "Order By Numero, Nom, Prenom ";	 
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
		<link href="jquery-ui.min.css" rel="stylesheet">
		<link href="jquery.dataTables.css" rel="stylesheet">
		<link href="fmv2.css" rel="stylesheet">
		<?php if($verrou != 'O') { ?>
			<link href="fmv2O.css" rel="stylesheet">
		<?php	}	?>
		
		<script type="text/javascript" src="jquery-1.11.0.min.js"></script>
		<script type="text/javascript" src="jquery-ui-1.10.4.custom.min.js"></script>
		<script type="text/javascript" src="jquery.jeditable.js"></script>
		<script type="text/javascript" src="jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="jquery.maskedinput.min.js"></script>
		<script>
			$(function() {
				$('#updateChrono').hide();
				$.editable.addInputType('autocomplete', { //Plugin Autocomplete pour jEditable
					element : $.editable.types.text.element,
					plugin : function(settings, original) {
						$('input', this).autocomplete(settings.autocomplete);
					}
				});
				$.editable.addInputType('catcomplete', { //Plugin Autocomplete avec categories pour jEditable
					element : $.editable.types.text.element,
					plugin : function(settings, original) {
						$('input', this).catcomplete(settings.autocomplete);
					}
				});
				$.editable.addInputType('spinner', { //Plugin spinner pour jEditable
					element : $.editable.types.text.element,
					plugin : function(settings, original) {
						$('input', this).spinner(settings.spinner);
					}
				});
				$.widget( "custom.catcomplete", $.ui.autocomplete, { // Widget autocomplete avec gestion des categories
					_create: function() {
						this._super();
						this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
					},
					_renderMenu: function( ul, items ) {
						var that = this,
						currentCategory = "";
						$.each( items, function( index, item ) {
							var li;
							if ( item.category != currentCategory ) {
								ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
								currentCategory = item.category;
							}
							li = that._renderItemData( ul, item );
							if ( item.category ) {
								li.attr( "aria-label", item.category + " : " + item.label );
							}
						});
					}
				});
				//Messages 
				function avertissement(texte) { 
					$('#avert').append('<div class="avertText">' + texte + '</div>');
					$('.avertText:last').show('blind',{},800).text(texte).delay(1500).fadeOut(1200);
				}
				//Alert
				function custom_alert(output_msg, title_msg) { 
					if (output_msg == '')
						output_msg = 'Pas de message à afficher.';
					if (title_msg == '')
						title_msg = 'Attention';
					$('div.simple_alert').remove();
					$("<div></div>").html(output_msg).dialog({
						dialogClass:'simple_alert',
						title: title_msg,
						resizable: false,
						modal: true,
						buttons: {
							"Ok": function() {
								$( this ).dialog( "close" );
							}
						}
					});
				}
				
				$( document ).tooltip();
				$("#chrono_ajust").mask("99:99");
				$("#periode_ajust").mask("99:99");
				$("#time_evt").mask("99:99");
				$("#end_match_time, #time_end_match").mask("99h99");
				/* COMPO EQUIPE */
				 $('#equipeA, #equipeB').dataTable( {
					"paging":   false,
					"ordering": false,
					"info":     false,
					"searching": false,
					bJQueryUI: true,
				} );
				
				$('#accordion').accordion({
					header: "h3",
					heightStyle: "content"
				});
				$('#typeMatch').buttonset();
			<?php if($verrou == 'O' || $_SESSION['Profile'] <= 0 || $_SESSION['Profile'] > 6) { ?>
				$('#typeMatch').click(function( event ){
					event.preventDefault();
				});
			<?php	}	?>
				/* CONTROLE */
				$('#controleMatch').buttonset();
			<?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
				$('#controleVerrou').click(function( event ){
					event.preventDefault();
					if($('#scoreA3').text() != $('#scoreA4').text() || $('#scoreB3').text() != $('#scoreB4').text()){
						$('div.simple_alert').remove();
						$("<div></div>").html('Le score provisoire n\'a pas été validé !').dialog({
							dialogClass:'simple_alert',
							title: 'Attention !',
							resizable: false,
							modal: true,
							buttons: {
								"Ok": function() {
									$( this ).dialog( "close" );
									$('#controleOuvert').click();
								}
							},
							close: function( event, ui ) {
									$('#controleOuvert').click();
							}
						});
					} else {
						$('div.simple_alert').remove();
						$("<div></div>").html('Contrôle feuille de match, vérifiez :<br />- paramètres<br />- compositions des équipes<br />- buts et cartons, <br />- score final<br /><br /> confirmez-vous ?').dialog({
							dialogClass:'simple_alert',
							title: 'Confirmation ?',
							resizable: false,
							modal: true,
							buttons: {
								"Oui": function() {
									$( this ).dialog( "close" );
									$.post(
										'StatutPeriode.php', // Le fichier cible côté serveur.
										{ // variables
											Id_Match : <?php echo $idMatch ?>,
											Valeur : 'O',
											TypeUpdate : 'Validation'
										},
										function(data){ // callback
											if(data == 'OK'){
												$('.statut, .periode, #zoneTemps, #zoneChrono, .match').hide();
												$('#reset_evt').click();
												window.location = 'FeuilleMarque2.php?idMatch=<?php echo $idMatch ?>';
											}
											else{
												custom_alert('Changement impossible', 'Attention');
											}
										},
										'text' // Format des données reçues.
									);
								},
								"Non": function() {
									$('#controleOuvert').click();
									$( this ).dialog( "close" );
								}
							}
						});
					}
				});
				$('#controleOuvert').click(function( event ){
					event.preventDefault();
					//if(confirm('Déverrouiller la feuille de match ?')){
						$.post(
							'StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : <?php echo $idMatch ?>,
								Valeur : '',
								TypeUpdate : 'Validation'
							},
							function(data){ // callback
								if(data == 'OK'){
									$('.statut, .periode, #zoneTemps, #zoneChrono, .match').show();
									//$('.statut[class*="actif"]').click();
									$('#reset_evt').click();
									window.location = 'FeuilleMarque2.php?idMatch=<?php echo $idMatch ?>';
								}
								else{
									custom_alert('Changement impossible', 'Attention');
								}
							},
							'text' // Format des données reçues.
						);
					//}
				});
			<?php } ?>
				/* PUBLICATION */
				$('#publiMatch').buttonset();
			<?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
				$('#prive').click(function( event ){
					event.preventDefault();
					if(confirm('Dé-publier le match ?')){
						$.post(
							'StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : <?php echo $idMatch ?>,
								Valeur : '',
								TypeUpdate : 'Publication'
							},
							function(data){ // callback
								if(data == 'OK'){
									window.location = 'FeuilleMarque2.php?idMatch=<?php echo $idMatch ?>';
								}
								else{
									custom_alert('Changement impossible', 'Attention');
								}
							},
							'text' // Format des données reçues.
						);
					}else{
						$('#controleOuvert').click();
					}
				});
				$('#public').click(function( event ){
					event.preventDefault();
					if(confirm('Publier le match ?')){
						$.post(
							'StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : <?php echo $idMatch ?>,
								Valeur : 'O',
								TypeUpdate : 'Publication'
							},
							function(data){ // callback
								if(data == 'OK'){
									window.location = 'FeuilleMarque2.php?idMatch=<?php echo $idMatch ?>';
								}
								else{
									custom_alert('Changement impossible', 'Attention');
								}
							},
							'text' // Format des données reçues.
						);
					}
				});
			<?php } ?>
			<?php if($verrou != 'O') { ?>
				/* VALIDATION SCORE */
				$('#validScore').buttonset();
				/****************************************************/
				$('#validScore').click(function( event ){
					event.preventDefault();
					if(confirm('Valider ce score ' + $('#scoreA').text() + '-' + $('#scoreB').text() + ' ?')){
						$.post(
							'StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : <?php echo $idMatch ?>,
								Valeur : $('#scoreA').text() + '-' + $('#scoreB').text(),
								TypeUpdate : 'ValidScore'
							},
							function(data){ // callback
								if(data == 'OK'){
									window.location = 'FeuilleMarque2.php?idMatch=<?php echo $idMatch ?>';
								}
								else{
									custom_alert('Changement impossible', 'Attention');
								}
							},
							'text' // Format des données reçues.
						);
					}
				});
				/*****************************************************/
				
				/* OFFICIELS */
				$('.editOfficiel').editable('saveOfficiel.php', {
					style   : 'display: inline',
					submit  : 'OK',
					cssclass : 'autocompleteOfficiel',
					indicator : '<img src="images/indicator.gif">',
					submitdata : {idMatch: <?php echo $idMatch ?>},
					type      : 'autocomplete',
					placeholder : '<i class="placehold">Cliquez pour modifier...</i>', 
					//tooltip   : "Clic pour modifier",
					//onblur    : "submit",
					autocomplete : { //parametres transmis au plugin autocomplete
						minLength  : 2,
						delay: 200,
						source     : '../Autocompl_joueur2.php'
					}
				});
				$('.editArbitres').editable('saveArbitres.php', {
					style   : 'display: inline',
					submit  : 'OK',
					type      : 'catcomplete',
					placeholder : '<i class="placehold">Cliquez pour modifier...</i>', 
					indicator : '<img src="images/indicator.gif">',
					submitdata : {idMatch: <?php echo $idMatch ?>},
					//tooltip   : "Clic pour modifier",
					//onblur    : "submit",
					autocomplete : { //parametres transmis au plugin autocomplete
						minLength  : 2,
						delay: 200,
						source     : '../Autocompl_arb3.php?idMatch=<?php echo $idMatch ?>',
						//select: function( event, ui ) {
						//	$( "#MatricTransmit" ).val( ui.item.matric );
						//	return false;
						//}
					},
				});
				// COMPO EQUIPES
				$('.editStatut').editable('saveStatut.php', {
					data   : " {'-':'Joueur','C':'Capitaine','E':'Entraîneur'}",
					placeholder : '-', 
					indicator : '<img src="images/indicator.gif">',
					submitdata : {idMatch: <?php echo $idMatch ?>},
					type   : 'select',
					submit  : 'OK',
					callback : function(value, settings) {
						idjoueur = $(this).attr('id').split('-');
						console.log(idjoueur[1]);
						if(value == 'C')
							attrSatut = ' (Cap.)';
						else if(value == 'E')
							attrSatut = ' (Coach)';
						else
							attrSatut = '';
						$('.joueurs[data-id=' + idjoueur[1] + '] .StatutJoueur').text(attrSatut);
					}
				});
				$('.editNo').editable('saveNo.php', {
					style   : 'display: inline',
					placeholder : '-', 
					submit  : 'OK',
					indicator : '<img src="images/indicator.gif">',
					submitdata : {idMatch: <?php echo $idMatch ?>},
					type      : 'spinner',
					callback : function(value, settings) {
						idjoueur = $(this).attr('id').split('-');
						console.log(idjoueur[1]);
						$('.joueurs[data-id=' + idjoueur[1] + ']').attr('data-nb',value).find('.NumJoueur').text(value);
					}
				});
				// SUPPRESSION JOUEUR
				$('.suppression').click(function(){
					matricSupp = $(this).attr('id').split('-');
					$('div.simple_alert').remove();
					$("<div></div>").html('Confirmez-vous la suppression du joueur ' + matricSupp[2] + ' de l\'équipe ' + matricSupp[1] + ' ?').dialog({
						dialogClass:'simple_alert',
						title: 'Suppression de joueur',
						resizable: false,
						modal: true,
						buttons: {
							"Ok": function() {
								$( this ).dialog( "close" );
								$.post(
									'delJoueur.php', // Le fichier cible côté serveur.
									{
										Id_Match : <?php echo $idMatch ?>,
										Matric : matricSupp[2],
										Equipe : matricSupp[1]
									},
									function(data){ // callback
										if(data == 'OK'){
											$('#No-'+matricSupp[2]).parent().remove();
											$('a.joueurs[data-id='+matricSupp[2]+']').remove();
											custom_alert('Joueur ' + matricSupp[2] + ' supprimé', 'Attention');
										}
										else{
											custom_alert('Suppression impossible', 'Attention');
										}
									},
									'text' // Format des données reçues.
								);
							},
							"Annuler": function() {
								$( this ).dialog( "close" );
								custom_alert('joueur non supprimé', 'Attention');
							}
						}
					});
				});
				// Réinitialisation des présents
				$('#initA').click(function(){
					$.post(
						'initPresents.php', // Le fichier cible côté serveur.
						{
							idMatch : <?php echo $idMatch ?>,
							codeEquipe : 'A',
							idEquipe : <?php echo $row['Id_equipeA'] ?>
						},
						function(data){ // callback
							if(data == 'OK'){
								window.location = 'FeuilleMarque2.php?idMatch=<?php echo $idMatch ?>';
							}
							else{
								custom_alert('Initialisation impossible', 'Attention');
							}
						},
						'text' // Format des données reçues.
					);
				});
				$('#initB').click(function(){
					$.post(
						'initPresents.php', // Le fichier cible côté serveur.
						{
							idMatch : <?php echo $idMatch ?>,
							codeEquipe : 'B',
							idEquipe : <?php echo $row['Id_equipeB'] ?>
						},
						function(data){ // callback
							if(data == 'OK'){
								window.location = 'FeuilleMarque2.php?idMatch=<?php echo $idMatch ?>';
							}
							else{
								custom_alert('Initialisation impossible', 'Attention');
							}
						},
						'text' // Format des données reçues.
					);
				});
				// COMMENTAIRES
				$('#comments').editable('saveComments.php', {
					style   : 'display: inline',
					placeholder : 'Cliquer pour modifier...', 
					type : 'textarea',
					indicator : '<img src="images/indicator.gif">',
					tooltip   : "Cliquez pour modifier",
					submitdata : {idMatch: <?php echo $idMatch ?>},
					submit  : 'OK',
				});

				/* TYPE MATCH */
				$('#typeMatchElimination').click(function(){
					if(confirm('Match à élimination : vainqueur obligatoire. Confirmez-vous ?')){
						$.post(
							'StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : <?php echo $idMatch ?>,
								Valeur : 'E',
								TypeUpdate : 'Type'
							},
							function(data){ // callback
								if(data == 'OK'){
									typeMatch = 'elimination';
									$('#P1, #P2, #TB').show();
									$('#typeMatchImg').attr('src', '../../img/typeE.png');
								}
								else{
									custom_alert('Changement impossible<br />' + data, 'Attention');
								}
							},
							'text' // Format des données reçues.
						);
					}
				});
				$('#typeMatchClassement').click(function(){
					if(confirm('Match de classement : égalité possible. Confirmez-vous ?')){
						$.post(
							'StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : <?php echo $idMatch ?>,
								Valeur : 'C',
								TypeUpdate : 'Type'
							},
							function(data){ // callback
								if(data == 'OK'){
									typeMatch = 'classement';
									$('#P1, #P2, #TB').hide();
									$('#typeMatchImg').attr('src', '../../img/typeC.png');
								}
								else{
									custom_alert('Changement impossible<br />' + data, 'Attention');
								}
							},
							'text' // Format des données reçues.
						);
					}
				});
				/* STATUT */
				$('.statut').click(function( event ) {
					event.preventDefault();
					valeur = $(this).attr('id');
					$.post(
						'StatutPeriode.php', // Le fichier cible côté serveur.
						{ // variables
							Id_Match : <?php echo $idMatch ?>,
							Valeur : valeur,
							TypeUpdate : 'Statut'
						},
						function(data){  // callback
							if(data == 'OK'){
								$('.statut').removeClass('actif');
								$('#'+valeur).addClass('actif');
								statutActive(valeur, 'O');
								$('#reset_evt').click();
						//		valeur2 = $('.periode[class*="actif"]').attr('id');
								if(valeur == 'ON')// && valeur2 == ''
									$('#M1').click();
							}else{
								custom_alert('Echec update<br />'+data, 'Echec');
							}
						},
						'text' // Format des données reçues.
					);
				});
				function statutActive(leStatut, leClick){
					if(leStatut == 'ATT'){
						$('#zoneTemps, .periode, #zoneChrono').hide();
						$('.endmatch').hide();
					}else if(leStatut == 'ON'){
						$('.joueurs, #zoneTemps, #M1, #M2, #zoneChrono').show();
						$('.endmatch').hide();
						if(typeMatch == 'elimination') {
							$('#P1, #P2, #TB').show();
						}
					}else if(leStatut == 'END'){
						if(leClick == 'O'){
							avertissement('Match terminé');
							avertissement('Saisissez l\'heure de fin');
							var end_time = new Date();
							var end_hours = end_time.getHours();
							if (end_hours < 10) {
								end_hours = '0' + end_hours;
							}
							var end_minuts = end_time.getMinutes();
							if (end_minuts < 10) {
								end_minuts = '0' + end_minuts;
							}
							if($('#end_match_time').val() == '00:00' || $('#end_match_time').val() == '00h00'){
								$('#time_end_match').val(end_hours + 'h' + end_minuts);
							}else{
								$('#time_end_match').val($('#end_match_time').val());
							}
							$('#commentaires').val($('#comments').text());
							$('#dialog_end_match').dialog('open');
							$('#reset_evt').click();
						}else{
							$('#zoneTemps, .periode, #zoneChrono').hide();
							$('#end_match_time').removeClass('inactif').addClass('actif');
						}
					}
				}
			<?php	}	?>
				/* DIALOG END MATCH */
				$( "#dialog_end_match" ).dialog({
					autoOpen: false,
					modal: true,
					width: 500,
					buttons: {
						Ok: function() {
							$( this ).dialog( "close" );
							$.post(
								'saveComments.php', // Le fichier cible côté serveur.
								{ // variables
									idMatch : <?php echo $idMatch ?>,
									value : $('#commentaires').val(),
									heure_fin_match : $('#time_end_match').val()
								},
								function(data){ // callback
									if(data == $('#commentaires').val()){
										$('#end_match_time').removeClass('inactif').addClass('actif');
										$('#end_match_time').val($('#time_end_match').val());
										$('#raz_button').click();
										$('#run_button').hide();
										$('.statut').removeClass('actif');
										$('#END').addClass('actif');
										$('#zoneTemps, .periode, #zoneChrono').hide();
										$('.endmatch').show();
										$('#comments').text($('#commentaires').val());
									}else{
										custom_alert('Changement impossible<br />' + data, 'Attention');
									}
								},
								'text' // Format des données reçues.
							);
						},
						'Annuler': function() {
							$( this ).dialog( "close" );
						}
					}
				});
				/* DIALOG END */
				$( "#dialog_end" ).dialog({
					autoOpen: false,
					modal: true,
					buttons: {
						Ok: function() {
							$( this ).dialog( "close" );
							$('#heure').val(minut_max + ':' + second_max);
						}
					}
				});
			<?php if($verrou != 'O') { ?>
				$( "#dialog_end_opener" ).click(function() {
					$('#periode_end').text(minut_max + ':' + second_max);
					$( "#dialog_end" ).dialog( "open" );
				});
				
				/* PERIODE */
				$('.periode').click(function( event ) {
					event.preventDefault();
					valeur = $(this).attr('id');
					if(	$('#update_evt').attr('data-id') == ''){
						periode_en_cours = valeur;
						$.post(
							'StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : <?php echo $idMatch ?>,
								Valeur : valeur,
								TypeUpdate : 'Periode'
							},
							function(data){  // callback
								if(data == 'OK'){
									$('.statut').removeClass('actif');
									$('#ON').addClass('actif');
									$('#end_match_time').removeClass('actif').addClass('inactif')
									$('.joueurs, .equipes, .evtButton, .chronoButton, .evtButton2').removeClass('inactif');
									$('.periode').removeClass('actif');
									$('#'+valeur).addClass('actif');
									switch (valeur) {
										case 'P1':
											texte = '1ère prolongation : 5 minutes';
											minut_max = '05';
											second_max = '00';
											break;
										case 'P2':
											texte = '2nde prolongation : 5 minutes';
											minut_max = '05';
											second_max = '00';
											break;
										case 'TB':
											texte = 'Tirs au but';
											minut_max = '05';
											second_max = '00';
											break;
										case 'M2':
											texte = '2nde période : 10 minutes';
											minut_max = '10';
											second_max = '00';
											break;
										default:
											texte = '1ère période : 10 minutes';
											minut_max = '10';
											second_max = '00';
											break;
									}
									avertissement(texte);
									$('#chrono_ajust').val($('#heure').val());
									$('#periode_ajust').val(minut_max + ':' + second_max);
									$('#dialog_ajust_periode').html($('.periode[class*="actif"]').html());
									$( "#dialog_ajust" ).dialog( "open" );
								}else{
									custom_alert('Echec update<br />'+data, 'Echec');
								}
							},
							'text' // Format des données reçues.
						);
					}else{
						$('.periode').removeClass('actif');
						$('#'+valeur).addClass('actif');
					}
				});
			<?php	}	?>
				/* DIALOG AJUST */
				$( "#dialog_ajust" ).dialog({
					autoOpen: false,
					modal: true,
					buttons: {
						Ok: function() {
							$( this ).dialog( "close" );
							var split_period = $('#periode_ajust').val();
							split_period = split_period.split(':');
							minut_max = split_period[0];
							second_max = split_period[1];
							var split_chrono = $('#chrono_ajust').val();
							split_chrono = split_chrono.split(':');
							run_time.setTime(split_chrono[0] * 60000 + split_chrono[1] * 1000);
							$('#stop_button').click();
							$('#run_time_display').text(run_time.toLocaleString()); //debug
							$('#heure').val($('#chrono_ajust').val());
							if($('#chrono_ajust').val() == '00:00'){
								$('#run_button').hide();
							}
						},
						"Annuler": function() {
							$( this ).dialog( "close" );
						}
					}
				});
			<?php if($verrou != 'O') { ?>
				$( "#dialog_ajust_opener, #heure" ).click(function() {
					$('#chrono_ajust').val($('#heure').val());
					$('#periode_ajust').val(minut_max + ':' + second_max);
					$('#dialog_ajust_periode').text($('.periode[class="actif"]').attr('id'));
					$( "#dialog_ajust" ).dialog( "open" );
				});
				/* BOUTONS MATCH */
				$('.joueurs, .equipes').click(function( event ) {
					event.preventDefault();
					$('.joueurs, .equipes').removeClass('actif');
					$(this).addClass('actif');
				});
				$('.evtButton').click(function( event ) {
					event.preventDefault();
					$('.evtButton').removeClass('actif');
					$(this).addClass('actif');
					if($('#update_evt').attr('data-id') == ''){
						$('#time_evt').val($('#heure').val());
					}
					$('#valid_evt').removeClass('inactif')
				});
				/* BUT = TEMPS MORT SYSTEMATIQUE */
		//		$('#evt_but').click(function( event ) {
		//			$('#stop_button').click();
		//		});
				//var id_tr = 0;
				// INSERT 
				$('#valid_evt').click(function( event ) {
					event.preventDefault();
					//	enregistrement, ajout ligne...
				//	$('#valid_evt').html('Enregistrement...');
					var texte;
					var ligne_nom = $('.joueurs[class*="actif"]').attr('data-player');
					var ligne_nb = $('.joueurs[class*="actif"]').attr('data-nb');
					var ligne_num = ligne_nb + ' - ';
					var ligne_id_joueur = $('.joueurs[class*="actif"]').attr('data-id');
					var ligne_equipe = $('.joueurs[class*="actif"]').attr('data-equipe');
					var ligne_evt = $('.evtButton[class*="actif"]').attr('data-evt');
					if(ligne_evt === undefined) {return;}
					var carton_equipe = 0;
					if(ligne_nom === undefined) {
						carton_equipe = 1;
						ligne_nom = $('.equipes[class*="actif"]').attr('data-player');
						ligne_equipe = $('.equipes[class*="actif"]').attr('data-equipe');
						ligne_num = '';
						ligne_id_joueur = '';
						if(ligne_nom === undefined) {
							
							custom_alert('Vous devez sélectionner un joueur ou une équipe !');
							return;
						}
					}
					var code_ligne = $('.periode[class*="actif"]').attr('id');
					code_ligne += '-' + $('#time_evt').val();
					code_ligne += '-' + $('.evtButton[class*="actif"]').attr('data-code');
					code_ligne += '-' + ligne_equipe;
					code_ligne += '-' + ligne_id_joueur;
					code_ligne += '-' + ligne_nb;
					texte  = $('#time_evt').val() + ' ' + ligne_evt;
					texte += ' éq.' + ligne_equipe + ' ' + ligne_num + ligne_nom;
					/* BUT = TEMPS MORT OPTIONNEL ?  */
/*					if(ligne_evt == 'But') {
						$('div.simple_alert').remove();
						$("<div></div>").html('Temps mort ?').dialog({
							dialogClass:'simple_alert',
							title: 'But',
							resizable: false,
							modal: true,
							buttons: {
								"Oui": function() {
									$( this ).dialog( "close" );
									$('#stop_button').click();
								},
								"Non": function() {
									$( this ).dialog( "close" );
								},
							}
						});
					}
*/					
					$.post(
						'evt_match.php', // Le fichier cible côté serveur.
						{ // variables
							idMatch : <?php echo $idMatch ?>,
							ligne : code_ligne,
							idLigne : 0,
							type : 'insert'
						},
						function(data){ // callback
				//			$('#valid_evt').html('Valider');
							if($.isNumeric(data)){
								avertissement(texte);
								texteTR = '<tr id="ligne_' + data + '">';
								texteChrono = '<td class="list_chrono">' + $('.periode[class*="actif"]').attr('id') + ' ' + $('#time_evt').val() + '</td>';
								texteBut = '<td class="list_evt">';
								if(ligne_evt == 'But') {
									texteBut += '<img src="but1.png" />';
									$('.joueurs[class*="actif"]>.c_evt').append('<img class="c_but" src="but1.png" />');
								}
								texteBut += '</td>';
								texteNom = '<td class="list_nom">' + ligne_num + ligne_nom ;
								if(ligne_evt == 'Tir contre')
									texteNom += ' (Tir contré)';
								if(ligne_evt == 'Tir')
									texteNom += ' (Tir)';
								texteNom += '</td>';
								texteVert = '<td class="list_evt">'
								if(ligne_evt == 'Carton vert') {
									texteVert += '<img src="carton_vert.png" />';
									$('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="carton_vert.png" />');
									//si 2 verts...
									var nb_cartons = $('.joueurs[class*="actif"] img[src="carton_vert.png"]').length;
									if(nb_cartons == 2) {
										custom_alert(nb_cartons + 'e <img class="c_carton" src="carton_vert.png" /> pour ce joueur !<br />Vérifier type de faute.', 'Attention');
									}
									//si 3 verts...
									var nb_cartons = $('.joueurs[class*="actif"] img[src="carton_vert.png"]').length;
									if(nb_cartons >= 3) {
										custom_alert(nb_cartons + 'e <img class="c_carton" src="carton_vert.png" /> pour ce joueur !<br />Avertir l\'arbitre, modifier en jaune.', 'Attention');
									}
									// Carton d'équipe
								/*	if(carton_equipe == 1 && ligne_equipe == 'A' && confirm('Carton d\'équipe pour l\'équipe A ?')) {
										$('.joueurs[data-equipe="A"]>.c_evt').append('<img class="c_carton" src="carton_vert.png" />');
										code_ligne += '-teamCard';
									}
									if(carton_equipe == 1 && ligne_equipe == 'B' && confirm('Carton d\'équipe pour l\'équipe B ?')) {
										$('.joueurs[data-equipe="B"]>.c_evt').append('<img class="c_carton" src="carton_vert.png" />');
										code_ligne += '-teamCard';
									}
								*/
								}
								texteVert += '</td>';
								texteJaune = '<td class="list_evt">';
								if(ligne_evt == 'Carton jaune') {
									texteJaune += '<img src="carton_jaune.png" />';
									$('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="carton_jaune.png" />');
									//si 2 jaunes...
									var nb_cartons = $('.joueurs[class*="actif"] img[src="carton_jaune.png"]').length;
									if(nb_cartons >= 2) {
										custom_alert(nb_cartons + 'e <img class="c_carton" src="carton_jaune.png" /> pour ce joueur !', 'Attention');
									}
								}
								texteJaune += '</td>';
								texteRouge = '<td class="list_evt">';
								if(ligne_evt == 'Carton rouge') {
									texteRouge += '<img src="carton_rouge.png" />';
									$('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="carton_rouge.png" />');
								}
								texteRouge += '</td>';
								texteVide = '<td colspan="5"></td>';
								texteTR2 = '</tr>';
								$('.evtButton, .joueurs, .equipes').removeClass('actif');
								$('#valid_evt').addClass('inactif');
								if(ligne_equipe == 'A'){
									if(ligne_evt == 'But')
										$('#scoreA, #scoreA2, #scoreA3').text(parseInt($('#scoreA').text()) + 1);
									if(ordre_actuel == 'up'){
										$('#list').prepend(texteTR + texteVert + texteJaune + texteRouge + texteNom + texteBut + texteChrono + texteVide + texteTR2);
									}else{
										$('#list').append(texteTR + texteVert + texteJaune + texteRouge + texteNom + texteBut + texteChrono + texteVide + texteTR2);
									}
								}else{
									if(ligne_evt == 'But')
										$('#scoreB, #scoreB2, #scoreB3').text(parseInt($('#scoreB').text()) + 1);
									if(ordre_actuel == 'up'){
										$('#list').prepend(texteTR + texteVide + texteChrono + texteBut + texteNom + texteVert + texteJaune + texteRouge + texteTR2);
									}else{
										$('#list').append(texteTR + texteVide + texteChrono + texteBut + texteNom + texteVert + texteJaune + texteRouge + texteTR2);
									}
								}
								$('tr[id="ligne_' + data + '"]').attr('data-code', code_ligne);
								$.post(
									'StatutPeriode.php', // Le fichier cible côté serveur.
									{ // variables
										Id_Match : <?php echo $idMatch ?>,
										Valeur : $('#scoreA').text() + '-' + $('#scoreB').text(),
										TypeUpdate : 'ValidScoreDetail'
									},
									function(data){ },
									'text' // Format des données reçues.
								);
							}else{
								custom_alert('Changement impossible<br />' + data, 'suppression');
							}
						},
						'text' // Format des données reçues.
					);
				});
				// RESET
				$('#reset_evt').click(function( event ) {
					event.preventDefault();
					$('.evtButton, .joueurs, .equipes').removeClass('actif');
					$('#valid_evt').addClass('inactif').show();
					$('#list tr').removeClass('actif');
					$('#time_evt').val('');
					$('#update_evt').attr('data-id', '').attr('data-code', '');
					$('#zoneTemps a').removeClass('actif2');
					$('#update_evt, #delete_evt').hide();
					//$('#reset_evt').removeClass('evtButton3');
					if(periode_en_cours != ''){
						$('.periode').removeClass('actif');
						$('#'+periode_en_cours).addClass('actif');
					}
				});
				// EDIT
				$('#list').on( "click", 'tr', function() {
					$('#reset_evt').click();
					//periode_en_cours = $('.periode[class*="actif"]').attr('id');
					$('.periode').removeClass('actif');
					$(this).addClass('actif'); //Efface la ligne !
					code_ligne = $(this).attr('data-code');
					id_ligne = $(this).attr('id');
					$('#zoneTemps a').addClass('actif2');
					$('#update_evt').show().attr('data-code',code_ligne).attr('data-id',id_ligne);
					$('#delete_evt').show();
					//$('#reset_evt').addClass('evtButton3');
					$('#valid_evt').hide();
					code_split = code_ligne.split('-');
					ancienne_ligne = code_ligne;
					$('a[id="' + code_split[0] + '"]').addClass('actif');
					$('#time_evt').val(code_split[1]);
					$('a[data-code="' + code_split[2] + '"]').addClass('actif');
					if(code_split[4] != '') {
						$('a[data-id="' + code_split[4] + '"]').addClass('actif');
					}else{
						$('a[data-player="Equipe ' + code_split[3] + '"]').addClass('actif');
					}
				});
				// UPDATE
				$('#update_evt').click(function( event ) {
					event.preventDefault();
					var texte;
					var ligne_nom = $('.joueurs[class*="actif"]').attr('data-player');
					var ligne_nb = $('.joueurs[class*="actif"]').attr('data-nb');
					var ligne_num = ligne_nb + ' - ';
					var ligne_id_joueur = $('.joueurs[class*="actif"]').attr('data-id');
					var ligne_equipe = $('.joueurs[class*="actif"]').attr('data-equipe');
					var ligne_evt = $('.evtButton[class*="actif"]').attr('data-evt');
					if(ligne_evt === undefined) {return;}
					var carton_equipe = 0;
					if(ligne_nom === undefined) {
						carton_equipe = 1;
						ligne_nom = $('.equipes[class*="actif"]').attr('data-player');
						ligne_equipe = $('.equipes[class*="actif"]').attr('data-equipe');
						ligne_num = '';
						ligne_id_joueur = '';
						if(ligne_nom === undefined) {
							custom_alert('Vous devez sélectionner un joueur ou une équipe !');
							return;
						}
					}
					var code_ligne = $('.periode[class*="actif"]').attr('id');
					code_ligne += '-' + $('#time_evt').val();
					code_ligne += '-' + $('.evtButton[class*="actif"]').attr('data-code');
					code_ligne += '-' + ligne_equipe;
					code_ligne += '-' + ligne_id_joueur;
					code_ligne += '-' + ligne_nb;
					texte  = $('#time_evt').val() + ' ' + ligne_evt;
					texte += ' éq.' + ligne_equipe + ' ' + ligne_num + ligne_nom;
					$.post(
						'evt_match.php', // Le fichier cible côté serveur.
						{ // variables
							idMatch : <?php echo $idMatch ?>,
							ligne : code_ligne,
							ancienneLigne : ancienne_ligne,
							idLigne : id_ligne,
							type : 'update'
						},
						function(data){ // callback
							if(data == 'OK'){
								// suppression anciens éléments
								if(code_split[2] == 'B'){
									$('#score'+code_split[3]+', #score'+code_split[3]+'2, #score'+code_split[3]+'3').text(parseInt($('#score'+code_split[3]).text()) - 1);
									$('a[data-id="'+code_split[4]+'"] img[class="c_but"]').first().remove();
								}
								if(code_split[2] == 'V'){
									$('a[data-id="'+code_split[4]+'"] img[src="carton_vert.png"]').first().remove();
									if(code_split[5] == 'teamCard') {
										// PREMIER CARTON VERT DE CHAQUE JOUEUR !
										$('.joueurs[data-equipe="'+code_split[3]+'"]').each(function(){
											$(this).find('img[src="carton_vert.png"]').first().remove();
										});
									}
								}
								if(code_split[2] == 'J'){
									$('a[data-id="'+code_split[4]+'"] img[src="carton_jaune.png"]').first().remove();
								}
								if(code_split[2] == 'R'){
									$('a[data-id="'+code_split[4]+'"] img[src="carton_rouge.png"]').first().remove();
								}
								$('tr[id="'+id_ligne+'"] td').remove();
								// insertion nouveaux éléments
								avertissement(texte);
								texteTR = '<tr id="ligne_' + data + '">';
								texteChrono = '<td class="list_chrono">' + $('.periode[class*="actif"]').attr('id') + ' ' + $('#time_evt').val() + '</td>';
								texteBut = '<td class="list_evt">';
								if(ligne_evt == 'But') {
									texteBut += '<img src="but1.png" />';
									$('.joueurs[class*="actif"]>.c_evt').append('<img class="c_but" src="but1.png" />');
								}
								texteBut += '</td>';
								texteNom = '<td class="list_nom">' + ligne_num + ligne_nom ;
								if(ligne_evt == 'Tir contre')
									texteNom += ' (Tir contré)';
								if(ligne_evt == 'Tir')
									texteNom += ' (Tir)';
								texteNom += '</td>';
								texteVert = '<td class="list_evt">'
								if(ligne_evt == 'Carton vert') {
									texteVert += '<img src="carton_vert.png" />';
									$('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="carton_vert.png" />');
									//si 2 verts...
									var nb_cartons = $('.joueurs[class*="actif"] img[src="carton_vert.png"]').length;
									if(nb_cartons == 2) {
										custom_alert(nb_cartons + 'e <img class="c_carton" src="carton_vert.png" /> pour ce joueur !<br />Vérifier type de faute.', 'Attention');
									}
									//si 3 verts...
									var nb_cartons = $('.joueurs[class*="actif"] img[src="carton_vert.png"]').length;
									if(nb_cartons >= 3) {
										custom_alert(nb_cartons + 'e <img class="c_carton" src="carton_vert.png" /> pour ce joueur !<br />Avertir l\'arbitre, modifier en jaune.', 'Attention');
									}
								/*	if(carton_equipe == 1 && ligne_equipe == 'A' && confirm('Carton d\'équipe pour l\'équipe A ?')) {
										$('.joueurs[data-equipe="A"]>.c_evt').append('<img class="c_carton" src="carton_vert.png" />');
										code_ligne += '-teamCard';
									}
									if(carton_equipe == 1 && ligne_equipe == 'B' && confirm('Carton d\'équipe pour l\'équipe B ?')) {
										$('.joueurs[data-equipe="B"]>.c_evt').append('<img class="c_carton" src="carton_vert.png" />');
										code_ligne += '-teamCard';
									}
								*/
								}
								texteVert += '</td>';
								texteJaune = '<td class="list_evt">';
								if(ligne_evt == 'Carton jaune') {
									texteJaune += '<img src="carton_jaune.png" />';
									$('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="carton_jaune.png" />');
									//si 2 jaunes...
									var nb_cartons = $('.joueurs[class*="actif"] img[src="carton_jaune.png"]').length;
									if(nb_cartons >= 2) {
										custom_alert(nb_cartons + 'e <img class="c_carton" src="carton_jaune.png" /> pour ce joueur !', 'Attention');
									}
								}
								texteJaune += '</td>';
								texteRouge = '<td class="list_evt">';
								if(ligne_evt == 'Carton rouge') {
									texteRouge += '<img src="carton_rouge.png" />';
									$('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="carton_rouge.png" />');
								}
								texteRouge += '</td>';
								texteVide = '<td colspan="5"></td>';
								texteTR2 = '</tr>';
								$('.evtButton, .joueurs, .equipes').removeClass('actif');
								$('#valid_evt').addClass('inactif');
								if(ligne_equipe == 'A'){
									if(ligne_evt == 'But')
										$('#scoreA, #scoreA2, #scoreA3').text(parseInt($('#scoreA').text()) + 1);
									texte2 = texteVert + texteJaune + texteRouge + texteNom + texteBut + texteChrono + texteVide;
								}else{
									if(ligne_evt == 'But')
										$('#scoreB, #scoreB2, #scoreB3').text(parseInt($('#scoreB').text()) + 1);
									texte2 = texteVide + texteChrono + texteBut + texteNom + texteVert + texteJaune + texteRouge;
								}


								$('tr[id="'+id_ligne+'"]').attr('data-code', code_ligne).append(texte2);
								$('#reset_evt').click();
								$.post(
									'StatutPeriode.php', // Le fichier cible côté serveur.
									{ // variables
										Id_Match : <?php echo $idMatch ?>,
										Valeur : $('#scoreA').text() + '-' + $('#scoreB').text(),
										TypeUpdate : 'ValidScoreDetail'
									},
									function(data){ },
									'text' // Format des données reçues.
								);
							}else{
								custom_alert('Changement impossible<br />' + data, 'suppression');
							}
						},
						'text' // Format des données reçues.
					);

				});
				
				// DELETE
				$('#delete_evt').click(function( event ) {
					event.preventDefault();

					$.post(
						'evt_match.php', // Le fichier cible côté serveur.
						{ // variables
							idMatch : <?php echo $idMatch ?>,
							ligne : code_ligne,
							ancienneLigne : ancienne_ligne,
							idLigne : id_ligne,
							type : 'delete',
						},
						function(data){ // callback
							if(data == 'OK'){
								// suppression éléments
								if(code_split[2] == 'B'){
									$('#score'+code_split[3]+', #score'+code_split[3]+'2, #score'+code_split[3]+'3').text(parseInt($('#score'+code_split[3]).text()) - 1);
									$('a[data-id="'+code_split[4]+'"] img[class="c_but"]').first().remove();
								}
								if(code_split[2] == 'V'){
									$('a[data-id="'+code_split[4]+'"] img[src="carton_vert.png"]').first().remove();
									if(code_split[5] == 'teamCard') {
										// PREMIER CARTON VERT DE CHAQUE JOUEUR !
										$('.joueurs[data-equipe="'+code_split[3]+'"]').each(function(){
											$(this).find('img[src="carton_vert.png"]').first().remove();
										});
									}
								}
								if(code_split[2] == 'J'){
									$('a[data-id="'+code_split[4]+'"] img[src="carton_jaune.png"]').first().remove();
								}
								if(code_split[2] == 'R'){
									$('a[data-id="'+code_split[4]+'"] img[src="carton_rouge.png"]').first().remove();
								}
								$('tr[id="'+id_ligne+'"]').hide();
								$('#reset_evt').click();
								$.post(
									'StatutPeriode.php', // Le fichier cible côté serveur.
									{ // variables
										Id_Match : <?php echo $idMatch ?>,
										Valeur : $('#scoreA').text() + '-' + $('#scoreB').text(),
										TypeUpdate : 'ValidScoreDetail'
									},
									function(data){ },
									'text' // Format des données reçues.
								);
							}else{
								custom_alert('Changement impossible<br />' + data, 'suppression');
							}
						},
						'text' // Format des données reçues.
					);
				});
				
				/**************** CHRONO *******************/
				var timer, chrono, start_time, run_time, minut_max = 10, second_max = '00';
				Raz();
				$('#stop_button').hide();
				$('#run_button').hide();
				$.post(
					'getChrono.php',
					{
						idMatch : <?php echo $idMatch ?>,
					},
					function(data){
						if(data.action == 'start' || data.action == 'run'){
							temp_time = new Date();
							start_time = new Date();
							run_time = new Date();
							start_time.setTime(data.start_time);
							$('#start_time_display').text(start_time.toLocaleString()); //debug
							run_time.setTime(temp_time.getTime() - start_time.getTime());
							max_time = data.max_time;
							split_period = max_time.split(':');
							minut_max = split_period[0];
							second_max = split_period[1];
							$('#start_button').hide();
							$('#run_button').hide();
							$('#stop_button').show();
					//		$('#chrono_moins').hide();
					//		$('#chrono_plus').hide();
							$('#heure').css('background-color', '#009900');
							timer = setInterval(Horloge, 500);
							avertissement('Chrono en cours');
							$('#tabs-2_link').click();
						}else if(data.action == 'stop'){
							temp_time = new Date();
							start_time = new Date();
							run_time = new Date();
							start_time.setTime(data.start_time);
							run_time.setTime(data.run_time);
							$('#start_time_display').text(start_time.toLocaleString()); //debug
							$('#run_time_display').text(run_time.toLocaleString()); //debug
							max_time = data.max_time;
							split_period = max_time.split(':');
							minut_max = split_period[0];
							second_max = split_period[1];
							$('#start_button').hide();
							$('#run_button').show();
							$('#stop_button').hide();
							$('#chrono_moins').show();
							$('#chrono_plus').show();
							$('#heure').css('background-color', '#990000');
							var minut_ = run_time.getMinutes();
							if (minut_ < 10) {minut_ = '0' + minut_;}
							var second_ = run_time.getSeconds();
							if (second_ < 10) {second_ = '0' + second_;}
							$('#heure').val(minut_ + ':' + second_);
							avertissement('Chrono arrêté');
							$('#tabs-2_link').click();
						}
					},
					'json'
				);
				$('#chrono_moins').click(function(){
					start_time.setTime(start_time.getTime() - 1000);
					run_time.setTime(run_time.getTime() - 1000);
					var minut_ = run_time.getMinutes();
					if (minut_ < 10) {minut_ = '0' + minut_;}
					var second_ = run_time.getSeconds();
					if (second_ < 10) {second_ = '0' + second_;}
					$('#heure').val(minut_ + ':' + second_);
					$('#chronoText').hide();
					$('#updateChrono').show();
				});
				$('#chrono_plus').click(function(){
					start_time.setTime(start_time.getTime() + 1000);
					run_time.setTime(run_time.getTime() + 1000);
					var minut_ = run_time.getMinutes();
					if (minut_ < 10) {minut_ = '0' + minut_;}
					var second_ = run_time.getSeconds();
					if (second_ < 10) {second_ = '0' + second_;}
					$('#heure').val(minut_ + ':' + second_);
					$('#chronoText').hide();
					$('#updateChrono').show();
				});
				//$('#time_evt').val('00:00');
				//alert($('#time_evt').val());
				$('#time_plus').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]) + 1;
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2);
					if (second_2 > 60) {second_2 = 60;}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#time_moins').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]) - 1;
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2);
					if (second_2 > 60) {second_2 = 60;}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#time_plus2').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]);
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2) + 1;
					if (second_2 > 59) {second_2 = 0; $('#time_plus').click();}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#time_moins2').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]);
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2) - 1;
					if (second_2 < 0) {second_2 = 59; $('#time_moins').click();}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#updateChrono').click(function() {
					$.post(
						'ajax_updateChrono.php',
						{
							idMatch : <?php echo $idMatch ?>,
							start_time : start_time.getTime(),
							run_time : run_time.getTime(),
						},
						function(data){
							if(data == 'OK'){
								avertissement('Start chrono');
							}
						},
						'text'
					);
					$('#chronoText').show();
					$('#updateChrono').hide();
				});
				
				function Raz() {
					//$('#heure').val('00:00');
					$('#heure').val(minut_max + ':' + second_max);
				}
				function Horloge() {
					var temp_time = new Date();
					// chrono
					// run_time.setTime(temp_time.getTime() - start_time.getTime());
					// compte à rebours
					var max_time1 = (minut_max * 60000) + (second_max * 1000);
					run_time.setTime(start_time.getTime() + max_time1 - temp_time.getTime());
					$('#run_time_display').text(run_time.toLocaleString()); //debug
					var minut_ = run_time.getMinutes();
					if (minut_ < 10) {minut_ = '0' + minut_;}
					var second_ = run_time.getSeconds();
					if (second_ < 10) {second_ = '0' + second_;}
					$('#heure').val(minut_ + ':' + second_);
					/* Contrôle maxi */
					//if(minut_ >= minut_max && second_ >= second_max)
					if(minut_ <= 0 && second_ <= 0)
					{
						// Temps écoulé
						clearInterval(timer);
						//$('#periode_end').text(minut_max + ':' + second_max);
						$('#periode_end').text('00:00');
						$('#stop_button').click();
						$( "#dialog_end" ).dialog( "open" );
					}
				}
				$('#start_button').click(function(){
					start_time = new Date();
					run_time = new Date();
					run_time.setTime(0);
					run_time2 = new Date();
					run_time2.setTime(0);
					Horloge();
					timer = setInterval(Horloge, 500);
					$('#start_time_display').text(start_time.toLocaleString()); //debug
					$('#run_time_display').text(run_time.toLocaleString()); //debug
					$('#start_button').hide();
					$('#run_button').hide();
					$('#stop_button').show();
				//	$('#chrono_moins').hide();
				//	$('#chrono_plus').hide();
					$('#heure').css('background-color', '#009900');
					//alert(run_time.getTime());
					$.post(
						'setChrono.php',
						{
							idMatch : <?php echo $idMatch ?>,
							action : 'start',
							start_time : start_time.getTime(),
							run_time : run_time.getTime(),
							max_time : minut_max + ':' + second_max
						},
						function(data){
							if(data == 'OK'){
								avertissement('Start chrono');
							}
						},
						'text'
					);
				});
				$('#stop_button').click(function(){
					if (run_time)
						$('#stop_time_display').text(run_time.toLocaleString()); //debug
					clearInterval(timer);
					$('#run_button').show();
					$('#start_button').hide();
					$('#stop_button').hide();
					$('#chrono_moins').show();
					$('#chrono_plus').show();
					$('#heure').css('background-color', '#990000');
					$.post(
						'setChrono.php',
						{
							idMatch : <?php echo $idMatch ?>,
							action : 'stop',
							start_time : start_time.getTime(),
							run_time : run_time.getTime(),
							max_time : minut_max + ':' + second_max
						},
						function(data){
							if(data == 'OK'){
								avertissement('Stop chrono');
							}
						},
						'text' 
					);
				});
				$('#run_button').click(function(){
					start_time = new Date();
					// chrono
					// start_time.setTime(start_time.getTime() - run_time.getTime());
					// compte à rebours
					var max_time1 = (minut_max * 60000) + (second_max * 1000);
					start_time.setTime(run_time.getTime() - max_time1 + start_time.getTime());
					Horloge();
					timer = setInterval(Horloge, 500);
					$('#start_time_display').text(start_time.toLocaleString()); //debug
					$('#run_time_display').text(run_time.toLocaleString()); //debug
					$('#run_button').hide();
					$('#stop_button').show();
				//	$('#chrono_moins').hide();
				//	$('#chrono_plus').hide();
					$('#heure').css('background-color', '#009900');
					$.post(
						'setChrono.php', // : replace table chrono ligne idMatch...
						{
							idMatch : <?php echo $idMatch ?>,
							action : 'run',
							start_time : start_time.getTime(),
							run_time : run_time.getTime(),
							max_time : minut_max + ':' + second_max
						},
						function(data){
							if(data == 'OK'){
								avertissement('Run chrono');
							}
						},
						'text'
					);
				});
				$('#raz_button').click(function(){
					$('#start_button').show();
					$('#run_button').hide();
					$('#stop_button').hide();
					$('#chrono_moins').show();
					$('#chrono_plus').show();
					clearInterval(timer);
					Raz();
					$('#heure').css('background-color', '#444444');
					$.post(
						'setChrono.php',
						{
							idMatch : <?php echo $idMatch ?>,
							action : 'RAZ',
						},
						function(data){
							if(data == 'OK'){
								avertissement('RAZ chrono');
							}
						},
						'text'
					);
				});
			<?php	}	?>

				/* TABS */
				$('#tabs-1_link').hide();
				$('#tabs-2').hide();
				$('.fm_tabs').click(function() {
					$('.fm_tabs').toggle();
					$('.tabs_content').toggle();
				});
				/* END MATCH */
				/* Charge nouvelle feuille */
				$('#chargeFeuille').click(function(event){
					event.preventDefault();
					window.location = 'FeuilleMarque2.php?idMatch=' + $('#idFeuille').val();
				});
				/* ORDRE EVTS */
				var ordre_actuel = 'up';
				$('#change_ordre').click(function() {
					if(ordre_actuel == 'up'){
						ordre_actuel = 'down';
						$('#change_ordre img').attr('src','../../img/down.png');
						$('#list tr').each(function(){
							$(this).prependTo('#list');
						});
					}else{
						ordre_actuel = 'up';
						$('#change_ordre img').attr('src','../../img/up.png');
						$('#list tr').each(function(){
							$(this).prependTo('#list');
						});
					}
				});
				
				/* VERSION PDF */
				$('#pdfFeuille').buttonset();
				$('#pdfFeuille').click(function(event) {
					event.preventDefault();
					window.open('../FeuilleMatchMulti.php?listMatch=<?php echo $idMatch ?>','_blank');
				});
				/* PARAMETRES PAR DEFAUT */
				var run_time = new Date();
				<?php if($verrou == 'O') { ?>
					$('#controleVerrou').attr('checked','checked');
					$('#zoneTemps, #zoneChrono, .match, #initA, #initB, .suppression').hide();
					$('#typeMatch label').not('.ui-state-active').hide();				// masque le type match inactif !!
					//$('#reset_evt').click();
				<?php	}else{	?>
					$('#zoneTemps, #zoneChrono, .match').show();
					//$('.statut[class*="actif"]').click();
					$('#reset_evt').click();
					<?php if($typeMatch == 'C'){	?>
						typeMatch = 'classement';
						$('#P1, #P2, #TB').hide();
					<?php	}else{	?>
						typeMatch = 'elimination';
						$('#P1, #P2, #TB').show();
					<?php	}	?>
					statutActive('<?php echo $statutMatch ?>', 'N');
				<?php	}	?>
				$('#end_match_time').val('<?php echo substr($heure_fin,-5,2).'h'.substr($heure_fin,-2) ?>');
				<?php if($statutMatch != 'END'){	?>
					$('.endmatch').hide();
				<?php	}	?>
				$('#<?php echo $periodeMatch ?>').addClass('actif');
				switch ('<?php echo $periodeMatch ?>') {
					case 'P1':
						texte = '1ère prolongation : 3 minutes';
						minut_max = '03';
						second_max = '00';
						break;
					case 'P2':
						texte = '2nde prolongation : 3 minutes';
						minut_max = '03';
						second_max = '00';
						break;
					case 'TB':
						texte = 'Tirs au but';
						minut_max = '03';
						second_max = '00';
						break;
					case 'M2':
						texte = '2nde période : 10 minutes';
						minut_max = '10';
						second_max = '00';
						break;
					default:
						texte = '1ère période : 10 minutes';
						minut_max = '10';
						second_max = '00';
						break;
				}
				$('#update_evt').hide();
				$('#delete_evt').hide();
				var periode_en_cours = '<?php echo $periodeMatch ?>';
				
				/* Evt chargés */
				<?php
				for ($i=1;$i<=$num_results5;$i++)
				{
					$row5 = mysql_fetch_array($result5);
					$evtEquipe = $row5['Equipe_A_B'];
					switch($row5["Id_evt_match"]){
						case 'B':
							$evt_temp_js = '$("#'.$evtEquipe.$row5['Competiteur'].' .c_evt").append("<img class=\'c_but\' src=\'but1.png\' />");
							$("#score'.$evtEquipe.', #score'.$evtEquipe.'2, #score'.$evtEquipe.'3").text(parseInt($("#score'.$evtEquipe.'").text()) + 1);
							';
							break;
						case 'V':
							$evt_temp_js = '$("#'.$evtEquipe.$row5['Competiteur'].' .c_evt").append("<img class=\'c_carton\' src=\'carton_vert.png\' />");
							';
							break;
						case 'J':
							$evt_temp_js = '$("#'.$evtEquipe.$row5['Competiteur'].' .c_evt").append("<img class=\'c_carton\' src=\'carton_jaune.png\' />");
							';
							break;
						case 'R':
							$evt_temp_js = '$("#'.$evtEquipe.$row5['Competiteur'].' .c_evt").append("<img class=\'c_carton\' src=\'carton_rouge.png\' />");
							';
							break;
					}
					echo $evt_temp_js;
				}
				if($num_results5 >= 1)
					mysql_data_seek($result5,0);
				$evtEquipe = '';

				?>
			});
		</script>
	</head>
	<body>
		<form>
			<img src="FFCK.gif" id="logo" />
			<div id="avert"></div>
			<a href="#" class="fm_bouton fm_tabs" id="tabs-1_link">Paramètres du match<span class="icon_parametres"></span></a>
			<a href="#" class="fm_bouton fm_tabs" id="tabs-2_link">Déroulement du match<span class="icon_rignt"></span></a>
			<h3 class="centre"><?php 
				echo $row['Code_competition'];
				if($row['Code_typeclt'] == 'CHPT')
					echo ' ('.$row['Lieu'].')';
				elseif($row['Soustitre2'] != '')
					echo ' ('.$row['Soustitre2'].')';
				if($row['Phase'] != '')
					echo ' - '.$row['Phase'];
				echo ' - Match n°'.$row['Numero_ordre']; ?><br />
			<?php echo utyDateUsToFr($row['Date_match']).' à '.$row['Heure_match'].' - Terrain '.$row['Terrain']; ?></h3>
			<div id="tabs-1" class="tabs_content">
				<div id="accordion">
					<div class="note">A remplir avant le début du match</div>
					<h3>Paramètres du match ID# <?php echo $idMatch; ?></h3>
					<div>
						<div class="moitie">
							Type de match : 
							<br />
							<span id="typeMatch">
								<input type="radio" name="typeMatchtypeMatch" id="typeMatchClassement" <?php if($row['Type_match'] == 'C') echo 'checked="checked"'; ?> /><label for="typeMatchClassement" title="Egalité possible">match de classement</label>
								<input type="radio" name="typeMatch" id="typeMatchElimination" <?php if($row['Type_match'] == 'E') echo 'checked="checked"'; ?> /><label for="typeMatchElimination" title="Vainqueur obligatoire">match à élimination</label>
							</span>
							<img id="typeMatchImg" style="vertical-align:middle;" title="<?php if($row['Type_match'] == 'C'){ echo 'Match de classement'; }else{ echo 'Match à élimination';} ?>" alt="Type de match" src="../../img/type<?php echo $row['Type_match']; ?>.png" />
							<br />
							<br />
							<?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
								<span title="PC course seulement">Publication match : </span>
								<br />
								<span id="publiMatch">
									<input type="radio" name="publiMatch" id="prive" <?php if($publiMatch != 'O') echo 'checked="checked"'; ?> /><label for="prive" title="Match non public">Privé</label>
									<input type="radio" name="publiMatch" id="public" <?php if($publiMatch == 'O') echo 'checked="checked"'; ?> /><label for="public" title="Match public">Public</label>
								</span>
							<?php } ?>
							<img height="30" style="vertical-align:middle;" title="Publier O/N" alt="Publier O/N" src="../../img/oeil2<?php if($publiMatch == 'O'){ echo 'O';} else {echo 'N';} ?>.gif" />
							<br />
							<br />
							<input type="button" id="pdfFeuille" name="pdfFeuille" value="Version PDF" />
							<br />
							<br />
							Charger une autre feuille de match :
							<br />
							ID# <input type="text" id="idFeuille" /><input type="button" id="chargeFeuille" value="Charger" />
						</div>
						<div class="moitie droite">
							<span id="validScoreMatch">
								<i>Score officiel :<br />
								<span class="presentScore"><?php echo $row['equipeA']; ?> <span class="score" id="scoreA4"><?php echo $row['ScoreA']; ?></span> - <span class="score" id="scoreB4"><?php echo $row['ScoreB']; ?></span> <?php echo $row['equipeB']; ?></span>
								</i>
								<br />
								<br />
								Score provisoire (calculé nb de buts) :<br />
								<span class="presentScore"><?php echo $row['equipeA']; ?> <span class="score" id="scoreA3">0</span> - <span class="score" id="scoreB3">0</span> <?php echo $row['equipeB']; ?></span>
								<?php if($verrou != 'O') { ?>
									<br />
									<br />
									<input type="button" id="validScore" name="validScore" value="Valider ce score" />
								<?php } ?>
							</span>
							<br />
							<br />
							<span title="PC course seulement">Contrôle match : </span>
							<br />
							<span id="controleMatch">
								<?php if($readonly != 'O' && $_SESSION['Profile'] > 0 && $_SESSION['Profile'] <= 6) { ?>
									<input type="radio" name="controleMatch" id="controleOuvert" <?php if($verrou != 'O') echo 'checked="checked"'; ?> /><label for="controleOuvert">Ouvert</label>
								<?php } ?>
								<input type="radio" name="controleMatch" id="controleVerrou" <?php if($verrou == 'O') echo 'checked="checked"'; ?> /><label for="controleVerrou">Verrouillé</label>
							</span>
							<img height="30" style="vertical-align:middle;" title="Verrouiller O/N" alt="Verrouiller O/N" src="../../img/verrou2<?php if($verrou == 'O'){ echo 'O';} else {echo 'N';} ?>.gif" />
						</div>
					</div>
					<h3>Officiels</h3>
					<div>
						<div class="moitie">
							<label>Secrétaire : </label><br /><span class="editOfficiel" id="Secretaire"><?php echo $row['Secretaire']; ?></span><br />
							<label>Chronomètre : </label><br /><span class="editOfficiel" id="Chronometre"><?php echo $row['Chronometre']; ?></span><br />
							<label>Chronomètre temps d'action de but : </label><br /><span class="editOfficiel" id="Timeshoot"><?php echo $row['Timeshoot']; ?></span><br />
							<br />
							<label>Arbitre principal : </label><br /><span class="editArbitres" id="Arbitre_principal"><?php echo $row['Arbitre_principal']; ?></span><br />
							<label>Arbitre secondaire : </label><br /><span class="editArbitres" id="Arbitre_secondaire"><?php echo $row['Arbitre_secondaire']; ?></span><br />
							<label>Juge de ligne : </label><br /><span class="editOfficiel" id="Ligne1"><?php echo $row['Ligne1']; ?></span><br />
							<label>Juge de ligne : </label><br /><span class="editOfficiel" id="Ligne2"><?php echo $row['Ligne2']; ?></span><br />
						</div>
						<div class="moitie droite">
							<label>Club organisateur : </label><?php echo $row['Organisateur']; ?><br />
							<label><?php echo $lang['R1']; //Responsable organisation (R1)?>: </label><?php echo $row['Responsable_R1']; ?><br />
							<label>Délégué CNA : </label><?php echo $row['Delegue']; ?><br />
							<label>Chef des arbitres : </label><?php echo $row['ChefArbitre']; ?><br />
							<label>Responsable compétition (RC): </label><?php echo $row['Responsable_insc']; ?><br />
							<br />

						</div>
					</div>
					<h3>Equipe A - <img src="../../img/Pays/<?php echo $paysA; ?>.png" width="25px" height="16" /> <?php echo $row['equipeA']; ?>								
						<span class="score" id="scoreA2">0</span>
					</h3>
					<div>
						<table class="dataTable" id="equipeA">
							<thead>
								<tr>
									<th>N°</th>
									<th>Statut</th>
									<th>Nom</th>
									<th>Prénom</th>
									<th>Licence</th>
									<th>Cat.</th>
									<th>Supp</th>
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
										$joueur_temp .= '<td><a href="#" class="suppression" title="Supprimer le joueur" id="Supp-A-'.$row3["Matric"].'"><img src="images/trash.png" width="20" /></a></td>';
										$joueur_temp .= '</tr>';
									}else{
										$entr_temp  = '<tr>';
										$entr_temp .= '<td class="editNo" id="No-'.$row3["Matric"].'">'.$row3["Numero"].'</td>';
										$entr_temp .= '<td class="editStatut" id="Statut-'.$row3["Matric"].'">'.$row3["Capitaine"].'</td>';
										$entr_temp .= '<td>'.ucwords(strtolower($row3["Nom"])).'</td>';
										$entr_temp .= '<td>'.ucwords(strtolower($row3["Prenom"])).'</td>';
										$entr_temp .= '<td>';
										if($row3["Matric"] < 2000000)
											$entr_temp .= $row3["Matric"];
										$entr_temp .= '</td>';
										$entr_temp .= '<td>'.$age.'</td>';
										$entr_temp .= '<td><a href="#" class="suppression" title="Supprimer le joueur" id="Supp-A-'.$row3["Matric"].'"><img src="images/trash.png" width="20" /></a></td>';
										$entr_temp .= '</tr>';
										$joueur_temp = '';
									}
									echo $joueur_temp;
								}
								echo $entr_temp;
								if($num_results3 >= 1)
									mysql_data_seek($result3,0); 
							?>
							</tbody>
						</table>
						<input type="button" name="initA" id="initA" value="Recharger les joueurs présents" />
					</div>			
					<h3>Equipe B - <img src="../../img/Pays/<?php echo $paysB; ?>.png" width="25px" height="16" /> <?php echo $row['equipeB']; ?>								
						<span class="score" id="scoreB2">0</span>
					</h3>
					<div>
						<table class="dataTable" id="equipeB">
							<thead>
								<tr>
									<th>N°</th>
									<th>Statut</th>
									<th>Nom</th>
									<th>Prénom</th>
									<th>Licence</th>
									<th>Cat.</th>
									<th>Supp</th>
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
										if($row4["Matric"] < 2000000)
											$joueur_temp .= $row4["Matric"];
										$joueur_temp .= '</td>';
										$joueur_temp .= '<td>'.$age.'</td>';
										$joueur_temp .= '<td><a href="#" class="suppression" title="Supprimer le joueur" id="Supp-B-'.$row4["Matric"].'"><img src="images/trash.png" width="20" /></a></td>';
										$joueur_temp .= '</tr>';
									}else{
										$entr_temp  = '<tr>';
										$entr_temp .= '<td class="editNo" id="No-'.$row4["Matric"].'">'.$row4["Numero"].'</td>';
										$entr_temp .= '<td class="editStatut" id="Statut-'.$row4["Matric"].'">'.$row4["Capitaine"].'</td>';
										$entr_temp .= '<td>'.ucwords(strtolower($row4["Nom"])).'</td>';
										$entr_temp .= '<td>'.ucwords(strtolower($row4["Prenom"])).'</td>';
										$entr_temp .= '<td>';
										if($row4["Matric"] < 2000000)
											$entr_temp .= $row4["Matric"];
										$entr_temp .= '</td>';
										$entr_temp .= '<td>'.$age.'</td>';
										$entr_temp .= '<td><a href="#" class="suppression" title="Supprimer le joueur" id="Supp-B-'.$row4["Matric"].'"><img src="images/trash.png" width="20" /></a></td>';
										$entr_temp .= '</tr>';
										$joueur_temp = '';
									}
									echo $joueur_temp;
								}
								echo $entr_temp;
								if($num_results4 >= 1)
									mysql_data_seek($result4,0); 
							?>
							</tbody>
						</table>
						<input type="button" name="initB" id="initB" value="Recharger les joueurs présents" />
					</div>			
				</div>			
			</div>
			<div id="tabs-2" class="tabs_content">
				<table class="maxWidth" id="deroulement_match">
					<tr>
						<th colspan="3">
							<span class="match">Match :</span>
							<a href="#" id="ATT" class="fm_bouton statut<?php if($statutMatch == 'ATT') echo ' actif'; ?>">En attente</a><a href="#" id="ON" class="fm_bouton statut<?php if($statutMatch == 'ON') echo ' actif'; ?>">En cours</a><a href="#" id="END" class="fm_bouton statut<?php if($statutMatch == 'END') echo ' actif'; ?>">Terminé</a>
							<span class="endmatch">Fin : </span><input type="text" id="end_match_time" class="fm_input_text endmatch" value="<?php echo $row['Heure_fin']; ?>" />
							<br />
							<a href="#" id="M1" class="fm_bouton periode<?php if($periodeMatch == 'M1') echo ' actif'; ?>">1ère période</a><a href="#" id="M2" class="fm_bouton periode<?php if($periodeMatch == 'M2') echo ' actif'; ?>">2nde période</a><a href="#" id="P1" class="fm_bouton periode<?php if($periodeMatch == 'P1') echo ' actif'; ?>">1ère prolong.</a><a href="#" id="P2" class="fm_bouton periode<?php if($periodeMatch == 'P2') echo ' actif'; ?>">2nde prolong.</a><a href="#" id="TB" class="fm_bouton periode<?php if($periodeMatch == 'TB') echo ' actif'; ?>">Tirs au but</a>
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
							<a href="#" class="fm_bouton equipes" data-equipe="A" data-player="Equipe A">Equipe A<br />
								<img src="../../img/Pays/<?php echo $paysA; ?>.png" width="25px" height="16" /> <?php echo $row['equipeA']; ?>
								<span class="score" id="scoreA">0</span>
							</a>
							<br /><br />
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
										$entr_temp = '<br /><br /><a href="#" id="A'.$row3["Matric"].'" data-equipe="A" data-player="'.ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'." data-id="'.$row3["Matric"].'" data-nb="'.$row3["Numero"].'" class="fm_bouton joueurs">';
										$entr_temp .= ucwords(strtolower($row3["Nom"])).' '.$row3["Prenom"][0].'.<span class="StatutJoueur"> (Coach)</span>';
										$entr_temp .= '<span class="c_evt"></span></a>';
										$joueur_temp = '';
									}
									echo $joueur_temp;
								}
								echo $entr_temp;
							?>
						</td>
						<td id="selectionChrono" class="centre">
							<div id="zoneChrono">
								<img id="chrono_moins" src="images/moins.gif" />
								<span id="chronoText">Chrono : </span><span id="updateChrono"><img src="valider.gif" /></span><input type="text" id="heure" class="fm_input_text" title="Paramètres du chrono" readonly />
								<span class="icon_parametres" id="dialog_ajust_opener" title="Paramètres du chrono"></span>
								<img id="chrono_plus" src="images/plus.gif" /><br />
								
								<a href="#" id="start_button" class="fm_bouton chronoButton">Start</a>
								<a href="#" id="run_button" class="fm_bouton chronoButton">Run</a>
								<a href="#" id="stop_button" class="fm_bouton chronoButton">Stop</a>
								<a href="#" id="raz_button" class="fm_bouton chronoButton">RAZ</a>
							</div>
							<div id="zoneEvt">
								<a href="#" id="evt_but" data-evt="But" data-code="B" class="fm_bouton evtButton"><span class="but">But</span></a>
								<a href="#" id="evt_vert" data-evt="Carton vert" data-code="V" class="fm_bouton evtButton"><img src="carton_vert.png" /></a>
								<a href="#" id="evt_arr" data-evt="Tir contre" data-code="A" class="fm_bouton evtButton" title="Tir contré (par le gardien ou un défenseur)">Tir contré</a>
								<a href="#" id="evt_jaune" data-evt="Carton jaune" data-code="J" class="fm_bouton evtButton"><img src="carton_jaune.png" /></a>
								<a href="#" id="evt_tir" data-evt="Tir" data-code="T" class="fm_bouton evtButton" title="Tir non cadré">Tir</a>
								<a href="#" id="evt_rouge" data-evt="Carton rouge" data-code="R" class="fm_bouton evtButton"><img src="carton_rouge.png" /></a>
							</div>
							<div id="zoneTemps">
								<img id="time_moins" src="images/moins.gif" />
								<img id="time_plus" src="images/plus.gif" />
								Temps : <input type="text" size="4" class="fm_input_text" id="time_evt" value="00:00" />
								<img id="time_moins2" src="images/moins.gif" />
								<img id="time_plus2" src="images/plus.gif" />
								<br />
								<a href="#" id="valid_evt" class="fm_bouton evtButton2 evtButton3">Valider</a>
								<a href="#" id="update_evt" data-id="" class="fm_bouton evtButton2"><img src="b_edit.png" /> Modifier</a>
								<a href="#" id="delete_evt" class="fm_bouton evtButton2"><img src="supprimer.gif" /> Suppr.</a>
								<a href="#" id="reset_evt" class="fm_bouton evtButton2">Annuler</a>
							</div>
						</td>
						<td id="selectionB">
							<a href="#" class="fm_bouton equipes" data-equipe="B" data-player="Equipe B">
								<span class="score" id="scoreB">0</span>Equipe B<br />
								<img src="../../img/Pays/<?php echo $paysB; ?>.png" width="25px" height="16" /> <?php echo $row['equipeB']; ?>
							</a>
							<br /><br />
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
										$entr_temp = '<br /><br /><a href="#" id="B'.$row4["Matric"].'" data-equipe="B" data-player="'.ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'." data-id="'.$row4["Matric"].'" data-nb="'.$row4["Numero"].'" class="fm_bouton joueurs">';
										$entr_temp .= ucwords(strtolower($row4["Nom"])).' '.$row4["Prenom"][0].'.<span class="StatutJoueur"> (Coach)</span>';
										$entr_temp .= '<span class="c_evt"></span></a>';
										$joueur_temp = '';
									}
									echo $joueur_temp;
								}
								echo $entr_temp;
							?>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<table id="list_header" class="maxWidth ui-state-default">
								<tr>
									<th class="list_evt_v">V</th>
									<th class="list_evt_j">J</th>
									<th class="list_evt_r">R</th>
									<th class="list_nom">Equipe A</th>
									<th class="list_evt_b">B</th>
									<th class="list_chrono" id="change_ordre" title="Changer l'ordre">Temps <img src="../../img/up.png" /></th>
									<th class="list_evt_b">B</th>
									<th class="list_nom">Equipe B</th>
									<th class="list_evt_v">V</th>
									<th class="list_evt_j">J</th>
									<th class="list_evt_r">R</th>
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
											$evt_temp .= '<img src="carton_vert.png">';
										$evt_temp .= '</td><td class="list_evt">';
										if($row5["Id_evt_match"] == 'J')
											$evt_temp .= '<img src="carton_jaune.png">';
										$evt_temp .= '</td><td class="list_evt">';
										if($row5["Id_evt_match"] == 'R')
											$evt_temp .= '<img src="carton_rouge.png">';
										$evt_temp .= '</td>';
										$evt_temp .= '<td class="list_nom">'.$row5["Numero"].' - '.ucwords(strtolower($row5["Nom"])).' '.ucwords(strtolower($row5["Prenom"]));
										if($row5["Id_evt_match"] == 'A')
											$evt_temp .= ' (tir contré)';
										if($row5["Id_evt_match"] == 'T')
											$evt_temp .= ' (tir)';
										$evt_temp .= '</td>';
										$evt_temp .= '<td class="list_evt">';
										if($row5["Id_evt_match"] == 'B')
											$evt_temp .= '<img src="but1.png">';
										$evt_temp .= '</td>';
									} else {
										$evt_temp .= '<td colspan="5" class="list_evt_vide"></td>';
									}
									$evt_temp .= '<td class="list_chrono">'.$row5["Periode"].' '.substr($row5["Temps"],-5).'</td>';
									if($evtEquipe == 'B'){
										$evt_temp .= '<td class="list_evt">';
										if($row5["Id_evt_match"] == 'B')
											$evt_temp .= '<img src="but1.png">';
										$evt_temp .= '</td>';
										$evt_temp .= '<td class="list_nom">'.$row5["Numero"].' - '.ucwords(strtolower($row5["Nom"])).' '.ucwords(strtolower($row5["Prenom"]));
										if($row5["Id_evt_match"] == 'A')
											$evt_temp .= ' (tir contré)';
										if($row5["Id_evt_match"] == 'T')
											$evt_temp .= ' (tir)';
										$evt_temp .= '</td>';
										$evt_temp .= '<td class="list_evt">';
										if($row5["Id_evt_match"] == 'V')
											$evt_temp .= '<img src="carton_vert.png">';
										$evt_temp .= '</td><td class="list_evt">';
										if($row5["Id_evt_match"] == 'J')
											$evt_temp .= '<img src="carton_jaune.png">';
										$evt_temp .= '</td><td class="list_evt">';
										if($row5["Id_evt_match"] == 'R')
											$evt_temp .= '<img src="carton_rouge.png">';
										$evt_temp .= '</td>';
									} else {
										$evt_temp .= '<td colspan="5" class="list_evt_vide"></td>';
									}
									$evt_temp .= '</tr>';
									
									
									echo $evt_temp;
								}
							?>
							</table>
							&nbsp;
						</td>
					</tr>
				</table>
				<br />
				Commentaires :
				<div id="comments"><?php echo $row['Commentaires_officiels'];?></div>
				<br />
				<br />
				<br />
			</div>
				<div id="dialog_ajust" title="Paramètres du chrono">
					<h3 id="dialog_ajust_periode">
					</h3>
					<p>
						Ajuster le chrono : <input type="text" id="chrono_ajust" class="fm_input_text" />
					</p>
					<p>
						Durée de la période : <input type="text" id="periode_ajust" class="fm_input_text" />
					</p>
				</div>
				<div id="dialog_end" title="Fin de période">
					<p class="centre">
						<span class="fm_input_text" id="periode_end">00:00</span><br />Période terminée
					</p>
				</div>
				<div id="dialog_end_match" title="Fin du match">
					<p class="centre">
						Heure fin de match : <input type="text" id="time_end_match" class="fm_input_text" />
					</p>
					<p class="centre">
						Commentaires officiels :<br />
						<textarea id="commentaires" rows="4" cols="50"></textarea>
					</p>
				</div>

		</form>
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


?>

