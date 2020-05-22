		<div class="main">
					
			<form method="POST" action="RechercheLicence.php" name="formRerchercheLicence" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<input type='hidden' name='codeComiteReg' Value=''/>
				<input type='hidden' name='codeComiteDep' Value=''/>
				<input type='hidden' name='codeClub' Value=''/>

				<div class='blocLeft'>
					<div class='titrePage'>Recherche de licenciés</div>
					<div class='liens'>
					<a href="#" onclick="setCheckboxes('formRerchercheLicence', 'checkCoureur', true);return false;"><img width="21" src="../img/tous.gif" alt="Sélectionner tous" title="Sélectionner tous" /></a>
					<a href="#" onclick="setCheckboxes('formRerchercheLicence', 'checkCoureur', false);return false;"><img width="21" src="../img/aucun.gif" alt="Sélectionner aucun" title="Sélectionner aucun" /></a>
					<a href="#" onclick="Cancel();"><img width="16" src="../img/supprimer.gif" alt="Supprimer la sélection" title="Supprimer la sélection" /></a>
					<a href="#" onclick="Ok();"><img width="16" src="../img/valider.gif" alt="Valider la sélection" title="Valider la sélection" /></a>
					</div>
					
					<div class='blocTable'>
						<table class='tableau' bgcolor='#FFCC99'>
							<thead>
								<tr class='header'>
									<th>&nbsp;</th>
									<th>Licence</th>
									<th>Nom</th>
									<th>Prénom</th>
									<th>Sexe</th>
									<th>Categ.</th>
									<th>Saison</th>
								{*	<th>Naissance</th>	*}
									<th>N°Club</th>
								{*	<th>Club</th>	*}
									<th>Inter</th>
									<th>Nat.</th>
								{*	<th>Inter-Reg</th>*}
									<th>Reg.</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
							{section name=i loop=$arrayCoureur} 
								<tr>
									<td><input type="checkbox" name="checkCoureur" value="{$arrayCoureur[i].Matric}" id="checkDelete{$smarty.section.i.iteration}" /></td>
									<td>{$arrayCoureur[i].Matric}</td>
									<td>{$arrayCoureur[i].Nom}</td>
									<td>{$arrayCoureur[i].Prenom}</td>
									<td>{$arrayCoureur[i].Sexe}</td>
									<td>{$arrayCoureur[i].Categ}</td>
									<td>{$arrayCoureur[i].Saison}</td>
								{*	<td>{$arrayCoureur[i].Naissance}</td>	*}
									<td>{$arrayCoureur[i].Numero_club}</td>
								{*	<td>{$arrayCoureur[i].Club}</td> *}
 									<td>{$arrayCoureur[i].International}</td>
 									<td>{$arrayCoureur[i].National}</td>
 								{*	<td>{$arrayCoureur[i].InterRegional}</td>*}
 									<td>{$arrayCoureur[i].Regional}</td>
									<td><a href="#" onclick="RemoveCheckbox('formRerchercheLicence', '{$arrayCoureur[i].Matric}');return false;"><img hspace="2" width="16" height="16" src="../img/b_drop.png" alt="Supprimer" title="Supprimer" border="0"></a></td>
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
									<Option Value="M" {if $sexeJoueur=='M'}selected{/if}>Masculin</Option>
									<Option Value="F" {if $sexeJoueur=='F'}selected{/if}>Féminin</Option>
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
						    
								{* Juge Inter-Régional<input type="checkbox" Name="CheckJugeInterReg" {$CheckJugeInterReg}/>*}
	    			    
					</form>			
					
		</div>	  	   
