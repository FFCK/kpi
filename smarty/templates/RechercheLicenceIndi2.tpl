<html>
	<head>				  
	<link rel="stylesheet" type="text/css" href="../css/style.css?v=2.5" />
	<link rel="stylesheet" type="text/css" href="../css/RechercheLicence.css?v=2.5" />
	<title>{$title}</title>
	
	{literal}
		<script language="JavaScript" src="../js/jquery-1.5.2.min.js?v=2.5" type="text/javascript"></script>
		<script language="JavaScript" src="../js/formTools.js?v=2.5" type="text/javascript"></script>
		<script language="JavaScript" src="../js/RechercheLicenceIndi2.js?v=2.5" type="text/javascript"></script>
	{/literal}

	</head>	  
	
	<body>
	
		<div class="main">
					
			<form method="POST" action="RechercheLicenceIndi2.php" name="formRerchercheLicenceIndi2" id="formRerchercheLicenceIndi2" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='zoneMatric' id='zoneMatric' Value='{$zoneMatric}'/>
				<input type='hidden' name='zoneIdentite' id='zoneIdentite' Value='{$zoneIdentite}'/>

				<div class='blocLeft Left2'>
					<div class='liens'>		
						<a href="#" id='CancelRechercheIndi'>Annuler (retour)</a>
					</div>
					<div class='titrePage'>Recherche de licenciés</div>
					
					<div class='blocTable'>
						<table class='tableau' bgcolor='#FFCC99'>
							<thead>
								<tr class='header'>
									<th></th>
									<th>Licence</th>
									<th>Nom</th>
									<th>Prénom</th>
									<th>Sexe</th>
									<th>Categ.</th>
{*									<th>Naissance</th>	*}
									<th>N°Club</th>
									<th>Arbitre</th>
								</tr>
							</thead>
							<tbody>
							{section name=i loop=$arrayCoureur} 
								<tr>
									<td><input type="checkbox" class='cliquableCheckbox' id='{$arrayCoureur[i].Matric}-{$arrayCoureur[i].Nom} {$arrayCoureur[i].Prenom} {$arrayCoureur[i].Arbitre}' /></td>
									<td>{$arrayCoureur[i].Matric}</td>
									<td>{$arrayCoureur[i].Nom}</td>
									<td>{$arrayCoureur[i].Prenom}</td>
									<td>{$arrayCoureur[i].Sexe}</td>
									<td>{$arrayCoureur[i].Categ}</td>
{*									<td>{$arrayCoureur[i].Naissance}</td>	*}
									<td>{$arrayCoureur[i].Numero_club}</td>
 									<td>{$arrayCoureur[i].Arbitre}</td>
{* 									<td>{$arrayCoureur[i].National}</td>	*}
{* 									<td>{$arrayCoureur[i].Regional}</td>	*}
								</tr>
							{/section}
							</tbody>
						</table>
					</div>
		        </div>
		        
		        <div class='blocRight'>
					<table>
						<tr>
							<th colspan=2 class='titreForm'>
								<label>Paramètres de recherche</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="matricJoueur">N° Licence :</label>
								<input type="text" name="matricJoueur" value="{$matricJoueur}"/>
							</td>
							<td>
								<label for="sexeJoueur">Sexe :</label>
								<select name="sexeJoueur" onChange="">
									<Option Value="" SELECTED>Tous</Option>
									<Option Value="M">Masculin</Option>
									<Option Value="F">Féminin</Option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="nomJoueur">Nom :</label>
								<input type="text" name="nomJoueur" maxlength=30 value="{$nomJoueur}"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="prenomJoueur">Prénom :</label>
								<input type="text" name="prenomJoueur" maxlength=30 value="{$prenomJoueur}"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="comiteReg">Comité Régional : </label>
								<select name="comiteReg" onChange="changeComiteReg();">
									{section name=i loop=$arrayComiteReg} 
								<Option Value="{$arrayComiteReg[i].Code}" {$arrayComiteReg[i].Selection}>{$arrayComiteReg[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="comiteDep">Comité Départemental : </label>				    
								<select name="comiteDep" onChange="changeComiteDep();">
									{section name=i loop=$arrayComiteDep} 
								<Option Value="{$arrayComiteDep[i].Code}" {$arrayComiteDep[i].Selection}>{$arrayComiteDep[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="club">Club : </label>				    
								<select name="club">
									{section name=i loop=$arrayClub} 
								<Option Value="{$arrayClub[i].Code}" {$arrayClub[i].Selection}>{$arrayClub[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td><label>Juge International</label></td>
							<td>
								<input type="checkbox" Name="CheckJugeInter" {$CheckJugeInter} />
							</td>
						</tr>
						<tr>
							<td><label>Juge National</label></td>
							<td>
								<input type="checkbox" Name="CheckJugeNational" {$CheckJugeNational}/>
							</td>
						</tr>
						<tr>
							<td><label>Juge Régional</label></td>
							<td>
								<input type="checkbox" Name="CheckJugeReg" {$CheckJugeReg}/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<br>
								<input type="button" onclick="Find();" name="findLicence" value="<< Lancer la recherche">
								<br>
							</td>
						</tr>
					</table>
	    		</div>
				
			</form>			
		</div>	  	   
	</body>
</html>
