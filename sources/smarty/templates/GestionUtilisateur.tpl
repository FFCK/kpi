		<!--<iframe name="iframeRechercheLicenceIndi" id='iframeRechercheLicenceIndi' SRC="RechercheLicenceIndi.php" scrolling="auto" width="950" height="450" FRAMEBORDER="yes"></iframe>-->
{*		<iframe name="iframeRechercheLicenceIndi2" id='iframeRechercheLicenceIndi2' SRC="RechercheLicenceIndi2.php?zoneMatric=guser&zoneIdentite=gidentite" scrolling="auto" width="950" height="450" FRAMEBORDER="yes"></iframe>
*}		<div class="main">
			<form method="POST" action="GestionUtilisateur.php" name="formUser" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value=''/>
				<input type='hidden' name='Action' id='Action' Value='{$action}'/>
				
				{if $action eq 'Update'}  
					{assign var='readonlyCode' value='READONLY'}
				{else}
					{assign var='readonlyCode' value=''}
				{/if}

				<div class='titrePage'>Utilisateurs accrédités</div>
				<div class='blocTop'>
					<table id='tableup' border=0 width=100% cellspacing=0 cellpadding=0>
						<tr>
							<!--<td rowspan=2 width=50 align=center>
								<a href="#" OnClick="rechercheLicenceUtilisateur();"><img hspace="2" width="16" height="17" src="../img/b_search.png" alt="Recherche Utilisateur" title="Recherche Utilisateur" border="0" align=absbottom></a>
							</td>-->
							<td rowspan=2 colspan=2 width=270>
								<label for="choixJoueur">Recherche (nom, prénom ou licence)</label>
								<input size="50" type="text" name="choixJoueur" id="choixJoueur" >
{*								<img id='rechercheUtilisateur' hspace="2" width="16" height="17" src="../img/b_search.png" alt="Recherche avancée" title="Recherche avancée" border="0" align=absbottom>*}
								<hr>
								<label for="guser">Licence</label>
								<br>
								<input class="boutonlong" type="text" name="guser" id="guser" value="{$guser}" READONLY>
								<br>
								<label for="gidentite">Nom</label>
								<br>
								<input class="boutonlong" type="text" name="gidentite" id="gidentite" value="{$gidentite}" {if $profile != 1}READONLY{/if}>
							</td>
							<td rowspan=2 width=220>
								<label for="gmail">E-mail</label>
								<input class="boutonlong" type="text" name="gmail" maxlength=60 id="gmail" placeholder="obligatoire" value="{$gmail}" autocomplete="off"/>
								<br>
								<label for="gtel">Téléphone</label>
								<input class="boutonlong" type="text" name="gtel" maxlength=60 id="gtel" value="{$gtel}"/>
								<br>
								{if $action != 'Update' or $profile == 1}  
									<label for="gpwd">Mot de passe</label>
									<input class="boutonlong" type="password" name="gpwd" id="gpwd" {if $action == 'Update'}placeholder="identique"{/if} value="" autocomplete="off"/>
								{/if}
								<br>
								<input type="checkbox" name="generepwd" id="generepwd" value="O" {if $action != 'Update'}checked{/if} /><label for="generepwd">Générer un mot de passe aléatoire</label>
							</td>
							<td>
								<label for="gniveau">Profil : </label>
								<select size=1 id="gniveau" name="gniveau">
										{if $profile == 1}
										<Option Value="1" {if $gniveau=='1'}selected{/if}>1 - Webmaster / Président{if $profile>'1'} (INTERDIT){/if}</Option>
										<Option Value="2" {if $gniveau=='2'}selected{/if}>2 - Bureau CNKP{if $profile>'2'} (INTERDIT){/if}</Option>
										{/if}
										{if $profile <= 2}
										<Option Value="3" {if $gniveau=='3'}selected{/if}>3 - Resp. Division (multi-compétitions)</Option>
										{/if}
										<Option Value="4" {if $gniveau=='4'}selected{/if}>4 - Resp. Poule / Compétition</Option>
										<Option Value="5" {if $gniveau=='5'}selected{/if}>5 - Délégué fédéral</Option>
										<Option Value="6" {if $gniveau=='6'}selected{/if}>6 - R1:Organisateur journée</Option>
										<Option Value="7" {if $gniveau=='7' or $action != 'Update'}selected{/if}>7 - Resp. club / équipe (spécifier les clubs)</Option>
										<Option Value="8" {if $gniveau=='8'}selected{/if}>8 - Consultation simple</Option>
										<Option Value="9" {if $gniveau=='9'}selected{/if}>9 - Table de marque</Option>
										<Option Value="10" {if $gniveau=='10'}selected{/if}>10 - Inutilisé</Option>
								</select>
							</td>
							<td>
								<label for="gfonction">Fonctions</label>
								<input class="boutonlong" type="text" name="gfonction" maxlength=60 id="gfonction" value="{$gfonction}"/>
							</td>
						</tr>
						<tr>
							<td colspan="2">
						       	<fieldset>
									<legend>Filtre Club</legend>	
									<label for="limitclub">Limiter l'accès aux équipes du club (codes clubs)</label>
									<input type="text" name="limitclub" id="limitclub" size=40 maxlength=50 value="{$limitclub}">
						        </fieldset>
					        	<fieldset>
									<legend>Filtre Journées</legend>	
									<label for="filtre_journee">Limiter l'accès aux journées (numéros de journées)</label>
						  			<input type="text" name="filtre_journee" id="filtre_journee" size=40 maxlength=50 value="{$filtre_journee}">
						        </fieldset>
							</td>
						</tr>
						<tr>
							<td colspan=6>
								{if $typeFiltreCompetition == '1'}
									{assign var='Checked1' value='Checked'}
									{assign var='Checked2' value=''}
									{assign var='Checked3' value=''}
								{elseif $typeFiltreCompetition == '3'}
									{assign var='Checked1' value=''}
									{assign var='Checked2' value=''}
									{assign var='Checked3' value='Checked'}
								{else}
									{assign var='Checked1' value=''}
									{assign var='Checked2' value='Checked'}
									{assign var='Checked3' value=''}
								{/if}
								<fieldset>
									<legend>Filtre Compétitions/Saisons</legend>	
										<table>
											<tr>
												<td>
													Filtre Classique<input type="radio" onclick="" name="filtre_competition" value="2" {$Checked2}>
													<i>(Sélection obligatoire)</i>
													<br>
													<select name="comboSaison[]" multiple="true" size="8">
														{section name=i loop=$arraySaison} 
															<Option Value="{$arraySaison[i].Code}" {$arraySaison[i].Selection}{if $action != 'Update' && $arraySaison[i].Code == $Saison}selected{/if}>{$arraySaison[i].Libelle}</Option>
														{/section}
													</select>
													
													<select name="comboCompetition[]" multiple="true" size="8">
                                                        {section name=i loop=$arrayCompetition}
                                                            {assign var='options' value=$arrayCompetition[i].options}
                                                            {assign var='label' value=$arrayCompetition[i].label}
                                                            <optgroup label="{$smarty.config.$label|default:$label}">
                                                                {section name=j loop=$options}
                                                                    {assign var='optionLabel' value=$options[j].Code}
                                                                    <Option Value="{$options[j].Code}" {$options[j].selected}>{$options[j].Code}-{$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>
                                                                {/section}
                                                            </optgroup>
                                                        {/section}
{*														{section name=i loop=$arrayCompetition} 
															<Option Value="{$arrayCompetition[i].Code}" {$arrayCompetition[i].Selection}>{$arrayCompetition[i].Libelle}</Option>
														{/section}
*}													</select>
												</td>
												<td>
													{if $profile == 1}
														Aucun Filtre<input type="radio" onclick="" name="filtre_competition" value="1" {$Checked1}>
														<br>
													{/if}
													{if $profile == 1}
														<br>
														<br>
														Filtre Special<input type="radio" onclick="" name="filtre_competition" value="3" {$Checked3}>
														<br>
														<input type="text" name="filtre_competition_special" id="filtre_competition_special" size=60 value="{$filtre_competition_special}">
													{/if}
												</td>
											</tr>
										</table>
								</fieldset>
							</td>
						</tr>
						<tr>
							<td colspan="3">
					        	<fieldset>
									<legend>Filtre Evènement</legend>
									{if $profile > 2}
										<select name="comboEvenement[]" multiple="true" size="7" DISABLED>
											{section name=i loop=$arrayEvenements} 
												<Option Value="{$arrayEvenements[i].Id}" {$arrayEvenements[i].Selection}>{$arrayEvenements[i].Id}-{$arrayEvenements[i].Libelle} - {$arrayEvenements[i].Lieu}</Option>
											{/section}
										</select>
									{else}
										<select name="comboEvenement[]" multiple="true" size="7" >
											{section name=i loop=$arrayEvenements} 
												<Option Value="{$arrayEvenements[i].Id}" {$arrayEvenements[i].Selection}>{$arrayEvenements[i].Id}-{$arrayEvenements[i].Libelle} - {$arrayEvenements[i].Lieu}</Option>
											{/section}
										</select>
									{/if}
						        </fieldset>
<!--				        	<fieldset>
									<legend>Dates limites Export Evènement</legend>
									<input type="text" name="Date_debut" id="Date_debut" value="{$Date_debut}" onfocus="displayCalendar(document.forms[0].Date_debut,'dd/mm/yyyy',this)">
									<input type="text" name="Date_fin" id="Date_fin" value="{$Date_fin}" onfocus="displayCalendar(document.forms[0].Date_fin,'dd/mm/yyyy',this)">
								</fieldset>
-->							</td>
							<td colspan="3">
								<input type="checkbox" name='plusmail' id='plusmail' value='O' checked />Envoyer un email de confirmation<br />
								<!--<input type="checkbox" name='plusPJ' id='plusPJ' value='Manuel7.pdf' />Envoyer le manuel "profil 7-8"<br />-->
								Message complémentaire : <input type="checkbox" id='msgStandard'><i>Message standard</i><br />
								<textarea rows="6" cols="80" name='message_complementaire' id='message_complementaire'></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="6" align=center>
							</td>
						</tr>
						<tr>
							<td colspan="6" align=center>
								{if $action eq 'Update'}  
									<input class="boutonlong" type="button" onclick="Update();" name="addUser" value="Modifier">
									<input class="boutonlong" type="button" onclick="Raz();" id="razUser" name="razUser" value="Annuler">
								{else}
									<input class="boutonlong" type="button" onclick="Add();" name="addUser" value="Ajouter">
								{/if}
						</tr>
						<tr id='clickup' name='clickup'>
							<td colspan="6" align="left" style="color:#555555"><i><u>Masquer le formulaire</u></i></td>
						</tr>
					</table>
					<table id='tabledown' width=100% >
						<tr id='clickdown' name='clickdown'>
							<td colspan="6" align="left" style="color:#555555"><i><u>Afficher le formulaire</u></i></td>
						</tr>
					</table>	

		        </div>
				
				<div class='blocBottom'>
					<div class='liens'>
						<a href="mailto:laurent@poloweb.org?bcc={$emails}">Envoyer un email aux utilisateurs ci-dessous</a>
						<a href="GestionJournal.php">Journal des activités</a>
						<label for='limitProfils'>Profils :</label>
						<select name="limitProfils" onChange="formUser.submit()">
								<Option Value="%">Tous</Option>
								{section name=i start=1 loop=9 } 
									<Option Value="{$smarty.section.i.index}" {if $limitProfils == $smarty.section.i.index}selected{/if}>{$smarty.section.i.index}</Option>
								{/section}
						</select>
						<label for='limitSaisons'>Saisons :</label>
						<select name="limitSaisons" onChange="formUser.submit()">
							<Option Value="">Toutes</Option>
							{section name=j loop=$arraySaison} 
								<Option Value="{$arraySaison[j].Code}" {if $limitSaisons == $arraySaison[j].Code}selected{/if}>{$arraySaison[j].Code}</Option>
							{/section}
						</select>
						<span id='reachspan'><i>Surligner:</i></span><input type=text name='reach' id='reach' size='10'>
					</div>
					<div class='blocTable'>
						<table class='tableau'>
							<thead>
								<tr class='header'>
									<th>sel.</th>
									<th>Utilisateur (licence)</th>
									<th>Fonction</th>
									<th>Profil</th>
									<th>Saisons</th>
									<th width=300>Compétitions</th>
									<th title='Filtre Evènement / Journées'>Evt/J</th>
									<th>Clubs</th>
									<th>Modif.</th>
									<th>Supp.</th>
								</tr>
							</thead>
							<tbody>
								{section name=i loop=$arrayUser} 
								<tr class='{cycle values="impair,pair"} {$arrayUser[i].StdOrSelected}'>
									<td><input type="checkbox" name="checkUser" value="{$arrayUser[i].Code}" id="checkDelete{$smarty.section.i.iteration}" /></td>
									<td><b>{$arrayUser[i].Identite}</b><br><i>{$arrayUser[i].Code}<br>({$arrayUser[i].Mail})</i>
										{if $arrayUser[i].Tel != ''}<br /><i>Tél: {$arrayUser[i].Tel}</i>{/if}
									</td>
									<td>{$arrayUser[i].Fonction}</td>
									<td>{$arrayUser[i].Niveau}</td>
									<td>{$arrayUser[i].filtreSaisons|default:'TOUTES'}</td>
									<td>{$arrayUser[i].filtreCompets}</td>
									<td title='Evènement : {$arrayUser[i].Libelle} {$arrayUser[i].Lieu} ({$arrayUser[i].Date_debut}->{$arrayUser[i].Date_fin})/ Journées : {$arrayUser[i].filtre_journee}'>{$arrayUser[i].Id_Evenement}/{$arrayUser[i].filtre_journee}</td>
									<td>{$arrayUser[i].Limitation_equipe_club}</td>
									<td>{if $arrayUser[i].Niveau > $profile or $profile == 1}<a href="#" onclick="updateUser('{$arrayUser[i].Code}');"><img hspace="2" width="16" height="16" src="../img/b_edit.png" alt="Modifier" title="Modifier" border="0"></a>{/if}</td>
									<td>{if $profile <= 2}<a href="#" onclick="RemoveCheckbox('formUser', '{$arrayUser[i].Code}');return false;"><img hspace="2" width="16" height="16" src="../img/b_drop.png" alt="Supprimer" title="Supprimer" border="0"></a>{/if}</td>
								</tr>
								{/section}
							</tbody>
						</table>
					</div>
		        </div>
	        						
			</form>		
					
		</div>	  	   
