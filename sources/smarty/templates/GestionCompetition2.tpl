		&nbsp;(<a href="Admin.php">Retour</a>)
		<iframe name="SelectionOuiNon" id="SelectionOuiNon" SRC="SelectionOuiNon.php" width="300" height="120" FRAMEBORDER="yes"></iframe>
		<div class="main">
			<form method="POST" action="GestionCompetition2.php" name="formCompet" enctype="multipart/form-data">
						<input type='hidden' name='Cmd' Value=''/>
						<input type='hidden' name='ParamCmd' Value=''/>
						<input type='hidden' name='verrouCompet' Value=''/>
						<input type='hidden' name='Verrou' Value=''/>
						<input type='hidden' name='Pub' Value=''/>

				<div class='blocLeft'>
					<h3 class='titrePage'>Compétitions / poules</h3>
					<br>
					{*
					<div class='liens'>		
						<a href="#" onclick="setCheckboxes('formCompet', 'checkCompet', true);return false;">Tout cocher</a>
						<a href="#" onclick="setCheckboxes('formCompet', 'checkCompet', false);return false;">Tout décocher</a>
						<a href="#" onclick="RemoveCheckboxes('formCompet', 'checkCompet')">Supprimer la sélection</a>
					</div>
					*}
					<div class='liens'>		
						<label for="saisonTravail">Saison :</label>
						<select name="saisonTravail" onChange="sessionSaison()">
							{section name=i loop=$arraySaison} 
								<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $sessionSaison}selected{/if}>{$arraySaison[i].Code}{if $arraySaison[i].Code eq $sessionSaison} (Travail){/if}</Option>
							{/section}
						</select>
						<label for="AfficheCompet">Afficher :</label>
						<select name="AfficheNiveau" onChange="changeAffiche()">
							<Option Value="" selected>Tous les niveaux</Option>
							<Option Value="INT"{if $AfficheNiveau == 'INT'} selected{/if}>Compétitions Internationales</Option>
							<Option Value="NAT"{if $AfficheNiveau == 'NAT'} selected{/if}>Compétitions Nationales</Option>
							<Option Value="REG"{if $AfficheNiveau == 'REG'} selected{/if}>Compétitions Régionales</Option>
						</select>
						<select name="AfficheCompet" onChange="changeAffiche()">
							<Option Value="" selected>Toutes les compétitions</Option>
							<Option Value="N"{if $AfficheCompet == 'N'} selected{/if}>Championnat de France</Option>
							<Option Value="CF"{if $AfficheCompet == 'CF'} selected{/if}>Coupe de France</Option>
							<Option Value="REG"{if $AfficheCompet == 'REG'} selected{/if}>Championnats régionaux</Option>
							<Option Value="DEP"{if $AfficheCompet == 'DEP'} selected{/if}>Championnats départementaux</Option>
							<Option Value="TI"{if $AfficheCompet == 'TI'} selected{/if}>Tournois Internationaux</Option>
						</select>
					</div>
					<div class='blocTable' id='blocCompet'>
						<table id='tableCompet'>
							<thead> 
								<tr>
									<th><img width="18" src="../img/oeilO" alt="Publication de la compétition" title="Publication de la compétition" /></th>
									<th title="Code">Code</th>
									<th title="Modifier">&nbsp;</th>
									<th title="Niveau">Niv.</th>
									<th title="Titre de la compétition">Libelle</th>
									<th title="Compétition de référence">Ref</th>
									<th title="Tour / Phase">Tour</th>
									<th title="Type de compétition (Championnat ou Coupe/Tournoi)">Type</th>
									<th title="Nombre d'équipes affectées">Nb</th>
									<th><img width="16" src="../img/verrouO.gif" alt="Verrouiller les titulaires" title="Verrouiller les titulaires" /></th>
									<th><img width="16" src="../img/up.gif" alt="Nb d'équipes qualifiées" title="Nb d'équipes qualifiées" /></th>
									<th><img width="16" src="../img/down.gif" alt="Nb d'équipes éliminées" title="Nb d'équipes éliminées" /></th>
									<th title="Suppression">&nbsp;</th>
								</tr>
							</thead> 
							
							<tbody>
							{section name=i loop=$arrayCompet} 
								<tr class='{$arrayCompet[i].StdOrSelected}' title='{$arrayCompet[i].Libelle} - {$arrayCompet[i].Soustitre}'>
									{*
									<td><input type="checkbox" name="checkCompet" value="{$arrayCompet[i].Code}" id="checkDelete{$smarty.section.i.iteration}" /></td>
									*}
									
									<td >
										{if $profile <= 4}
											<a href="#" Id="Publication{$arrayCompet[i].Code}" onclick="publiCompet('{$arrayCompet[i].Code}', '{$arrayCompet[i].Publication}')">
												<img width="18" src="../img/oeil{$arrayCompet[i].Publication}" alt="Publier O/N" title="Publier O/N" />
											</a>
										{else}
											<img width="18" src="../img/oeil{$arrayCompet[i].Publication}" alt="Publier O/N" title="Publier O/N" />
										{/if}
									</td>
									<td>{$arrayCompet[i].Code}</td>
									{if $profile <= 3}
										<td><a href="#" Id="Param{$arrayCompet[i].Code}" onclick="paramCompet('{$arrayCompet[i].Code}')"><img width="18" src="../img/b_edit.png" alt="Modifier" title="Modifier" /></a></td>
									{else}<td>&nbsp;</td>{/if}
									<td>{$arrayCompet[i].Code_niveau|default:'&nbsp;'}</td>
									<td>{$arrayCompet[i].Libelle|default:'&nbsp;'}</td>
									<td>{$arrayCompet[i].Code_ref|default:'&nbsp;'}</td>
									<td>{if $arrayCompet[i].Code_tour == '10'}F{else}{$arrayCompet[i].Code_tour|default:'&nbsp;'}{/if}</td>
									<td>{$arrayCompet[i].codeTypeClt|default:'&nbsp;'}</td>
									<td>{$arrayCompet[i].Nb_equipes|default:'&nbsp;'}</td>
									<td>
										{if $profile <= 3}
											<a href="#" Id="Verrou{$arrayCompet[i].Code}" onclick="verrou('{$arrayCompet[i].Code}', '{$arrayCompet[i].Verrou}')">
												<img width="16" src="../img/verrou{$arrayCompet[i].Verrou|default:'N'}.gif" alt="Verrouillage des Titulaires" title="Verrouillage des Titulaires" />
											</a>
										{else}
											<img width="16" src="../img/verrou{$arrayCompet[i].Verrou|default:'N'}.gif" alt="Verrouillage des Titulaires" title="Verrouillage des Titulaires" />
										{/if}
									</td>
									<td>{$arrayCompet[i].Qualifies|default:'&nbsp;'}</td>
									<td>{$arrayCompet[i].Elimines|default:'&nbsp;'}</td>
									{if $profile <= 2}
										<td><a href="#" onclick="RemoveCheckbox('formCompet', '{$arrayCompet[i].Code}');return false;"><img width="16" src="../img/b_drop.png" alt="Supprimer" title="Supprimer" /></a></td>
									{else}<td>&nbsp;</td>{/if}
								</tr>
							{/section}
							</tbody>
						</table>
					</div>
				</div>
		        
  				<div class='blocRight'>
					{if $profile <= 3}
						<table width="100%">
							<tr>
								<th class='titreForm' colspan=3>
									<label>{if $codeCompet == '-1'}Ajouter une {else}Modifier la {/if}compétition</label>
								</th>
							</tr>
							<tr>
								<td width=45%>
									<label for="codeCompet">Code :</label>
									<input type="text" name="codeCompet" maxlength=8 id="codeCompet" {if $codeCompet != '-1'}value="{$codeCompet}" disabled{/if} />
								</td>
								<td colspan=2>
									<label for="niveauCompet">Niveau : </label>
									<select name="niveauCompet" onChange="">
										<Option Value="REG"{if $niveauCompet == 'REG'} selected{/if}>REG-Régional</Option>
										<Option Value="NAT"{if $niveauCompet == 'NAT' or $niveauCompet == ''} selected{/if}>NAT-National</Option>
										<Option Value="INT"{if $niveauCompet == 'INT'} selected{/if}>INT-International</Option>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan=3>
									<label for="labelCompet">Libellé : </label>
									<input type="text" name="labelCompet" value="{$labelCompet}" maxlength=50 id="labelCompet"/>
								</td>
							</tr>
							<tr>
								<td>
									<label for="codeRef">Code Ref :</label>
									<select name="codeRef">
										<Option Value="">Aucun</Option>
										{section name=i loop=$arrayCompet} 
											<Option Value="{$arrayCompet[i].Code}"{if $arrayCompet[i].Code == $codeRef} selected{/if}>{$arrayCompet[i].Code}</Option>
										{/section}
									</select>
								</td>
								<td colspan=2>
									<label for="codeTypeClt">Type : </label>
									<select name="codeTypeClt" onChange="changeCodeTypeClt();">
										{section name=i loop=$arrayTypeClt} 
										<Option Value="{$arrayTypeClt[i][0]}"{if $arrayTypeClt[i][0] == $codeTypeClt} selected{/if}>{$arrayTypeClt[i][1]}</Option>
										{/section}
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<label for="etape">Tour / Phase :</label>
									<select name="etape">
										{section name=i loop=6 start=1} 
											<Option Value="{$smarty.section.i.index}"{if $smarty.section.i.index == $etape} selected{/if}>{$smarty.section.i.index}</Option>
										{/section}
											<Option Value="10"{if $etape == 10 or $etape == ''} selected{/if}>Unique/Finale</Option>
									</select>
								</td>
								<td>
									<label for="qualifies">Qualifiés</label>
									<input type="text" name="qualifies" value="{$qualifies|default:'3'}" />
								</td>
								<td>
									<label for="elimines">Eliminés</label>
									<input type="text" name="elimines" value="{$elimines|default:'0'}" />
								</td>
							</tr>
							<tr>
								<td colspan=3>
									<label for="soustitre">Sous-titre Compét. internat. ou Tournoi</label>
									<input type="text" name="soustitre" maxlength=50 value="{$soustitre}" />
								</td>
							</tr>
							<tr>
							{if $codeCompet != '-1'}
								<td>
									<br>
									<input type="button" onclick="updateCompet()" id="updateCompetition" name="updateCompetition" value="<< Modifier">
								</td>
								<td colspan=2>
									<br>
									<input type="button" onclick="razCompet()" id="razCompetition" name="razCompetition" value="Annuler">
								</td>
							</tr>
							<tr>
								<td colspan=3 align=center>
									<hr>
									<label for="logo"><b>Logo (500ko max)</b></label>
									<input type=hidden name=MAX_FILE_SIZE  VALUE=500000>
									<input type="file" name="logo1">
									<input type=button onclick="uploadLogo()" value="{if $logo}Changer{else}Envoyer{/if}">
									{if $logo}
										logo actuel :<br>
										<img hspace="2" width="200" src="{$logo}" alt="Logo actuel de la compétition" title="Logo actuel de la compétition" border="0">
										<input type=button onclick="dropLogo()" value="Supprimer">
									{/if}
								</td>
							{else}
								<td colspan=3>
									<br>
									<input type="button" onclick="Add();" name="addCompet" value="<< Ajouter">
								</td>
							{/if}
							{*
								<label for="codeTour">Tour :</label>
								<input type="text" name="codeTour" maxlength=8 id="codeTour"/>
								<label for="sexe">Sexe :</label>
								<input type="text" name="sexe" maxlength=1 id="sexe"/> 
							*}
							</tr>
						</table>
					{else}
						<table width="100%">
							<tr>
								<td align=center>
									<img hspace="2" width="200" src="{$logo}" alt="" border="0">
								</td>
							</tr>
						</table>
					{/if}
					{if $profile <= 2}
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Changer de saison (publique)</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="saisonActive"><b>Saison active :</b></label>
								<select name="saisonActive" onChange="activeSaison()">
									{section name=i loop=$arraySaison} 
										<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Etat=='A'}selected{/if}>{$arraySaison[i].Code}{if $arraySaison[i].Etat=='A'} (Active){/if}</Option>
									{/section}
								</select>
							</td>
						</tr>
					</table>
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Ajouter une saison</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="newSaison">Saison :</label>
								<input type="text" name="newSaison">
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>
								<label for="newSaison">Debut National</label>
								<input type="text" name="newSaisonDN" onfocus="displayCalendar(document.forms[0].newSaisonDN,'dd/mm/yyyy',this)" >
							</td>
							<td>
								<label for="newSaisonFN">Fin National</label>
								<input type="text" name="newSaisonFN" onfocus="displayCalendar(document.forms[0].newSaisonFN,'dd/mm/yyyy',this)" >
							</td>
						</tr>
						<tr>
							<td>
								<label for="newSaisonDI">Debut International</label>
								<input type="text" name="newSaisonDI" onfocus="displayCalendar(document.forms[0].newSaisonDI,'dd/mm/yyyy',this)" >
							</td>
							<td>
								<label for="newSaisonFI">Fin International</label>
								<input type="text" name="newSaisonFI" onfocus="displayCalendar(document.forms[0].newSaisonFI,'dd/mm/yyyy',this)" >
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<br>
								<input type="button" name="AjoutSaison" onclick="AddSaison();" value="Créer">
							</td>
						</tr>
					</table>
					{/if}
					{if $profile <= 4}
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Copie de structure de matchs</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<a href="GestionCopieCompetition.php">Copier la struture des matchs d'une compétition à l'autre...</a>
							</td>
						</tr>
					</table>
					{/if}
					{if $user == '42054' or $user == '63155' or $user == '81815' or $user == '7873'}
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Tester un autre profil</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="profilTest">Tester un autre profil</label>
								<select name="profilTest" onChange="submit();">
									{section name=i start=$profileOrigine loop=11}
										<option value="{$smarty.section.i.index}" {if $smarty.section.i.index == $profile}Selected{/if}>{$smarty.section.i.index}</option>
									{/section}
								</select>
							</td>
						</tr>
					</table>
					{/if}
		        </div>
			</form>			
		</div>
	