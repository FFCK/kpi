    &nbsp;(<a href="GestionJournee.php">Retour</a>)
		<div class="main">
			<form method="POST" action="GestionMatchEquipeJoueur.php" name="formMatchEquipeJoueur" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='kp_match_joueur'/>
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Matric = '/>
				<input type='hidden' name='AjaxAnd' id='AjaxAnd' Value='And Id_match = '/>
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}'/>
				<div class='blocLeft'>
					<div class='titrePage' tabindex='1'>Joueurs de l'équipe {$infoEquipe}<br>participant au match numéro {$Numero_ordre}</div>
					<br>
					{if $Validation != 'O'}
						<div class='liens'>
							<a href="#" onclick="setCheckboxes('formMatchEquipeJoueur', 'checkJoueur', true);return false;"><img width="21" src="../img/tous.gif" alt="Sélectionner tous" title="Sélectionner tous" /></a>
							<a href="#" onclick="setCheckboxes('formMatchEquipeJoueur', 'checkJoueur', false);return false;"><img width="21" src="../img/aucun.gif" alt="Sélectionner aucun" title="Sélectionner aucun" /></a>
							<a href="#" onclick="RemoveCheckboxes('formMatchEquipeJoueur', 'checkJoueur')"><img width="16" src="../img/supprimer.gif" alt="Supprimer la sélection" title="Supprimer la sélection" /></a>
							<button id='actuButton' type="button" onclick="submit()"><img src="../img/actualiser.gif">Recharger</button>
						</div>
					{/if}
					<div class='blocTable'>
						<table class='tableau' id='tableMatchs'>
							<thead>
								<tr class='header'>
									<th>&nbsp;</th>
									<th>N°</th>
									<th>Cap.</th>
									<th>Nom</th>
									<th>Prénom</th>
									<th>Licence</th>
									<th>Club</th>
									<th>Cat.-Sexe</th>
									<th>Pagaie<br />eau calme</th>
									<th>Certificat<br /><span title="CK">Compétition</span></th>
									<th>Arb.</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								{section name=i loop=$arrayJoueur} 
									{if ($arrayJoueur[i].Capitaine == 'E' or $arrayJoueur[i].Capitaine == 'A') && $test != 'OK'}
									{assign var='test' value='OK'}
									<tr class='{cycle values="impair,pair"}'>
										<td><br><br></td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
									</tr>
									{/if}
								<tr class='{cycle values="impair,pair"} colorCap{$arrayJoueur[i].Capitaine}'>

									{if $Validation == 'O' && $profile <= 6}
										<td>&nbsp;</td>
										<td>{$arrayJoueur[i].Numero}</td>
										<td class='colorCap{$arrayJoueur[i].Capitaine}'>{if $arrayJoueur[i].Capitaine=='N'}&nbsp;{else}{$arrayJoueur[i].Capitaine}{/if}</td>
									{else}
										<td><input type="checkbox" name="checkJoueur" value="{$arrayJoueur[i].Matric}" id="checkDelete{$smarty.section.i.iteration}" /></td>
										<td width="30" class='directInput text' tabindex='1{$smarty.section.i.iteration}0'><span href="#" Id="Numero-{$arrayJoueur[i].Matric}-{$idMatch}">{$arrayJoueur[i].Numero}</span></td>
										<!--<td><a href="#" Id="numero{$arrayJoueur[i].Matric}" onclick="DoNumero({$arrayJoueur[i].Matric},'{$arrayJoueur[i].Numero}')">{$arrayJoueur[i].Numero}</a></td>-->
										<td class='directSelect colorCap{$arrayJoueur[i].Capitaine}'>
											<span Id="Capitaine-{$arrayJoueur[i].Matric}-{$idMatch}" class='tooltip'
													title="{$arrayJoueur[i].Capitaine|replace:'C':'Capitaine'|replace:'A':'Arbitre'|replace:'E':'Entraineur'|replace:'X':'Inactif'}">{$arrayJoueur[i].Capitaine}</span>
											<!--<a href="#" Id="Capitaine{$arrayJoueur[i].Matric}" onclick="choixRadioCapitaine('match', '{$idMatch}','{$arrayJoueur[i].Matric}','{$arrayJoueur[i].Capitaine}')">{if $arrayJoueur[i].Capitaine=='N'}&nbsp;{else}{$arrayJoueur[i].Capitaine}{/if}</a>-->
										</td>
									{/if}
									<td>{$arrayJoueur[i].Nom}</td>
									<td>{$arrayJoueur[i].Prenom}</td>

									<td>
										{if $arrayJoueur[i].Matric > 2000000 && $arrayJoueur[i].icf != NULL}Icf-{$arrayJoueur[i].icf}{else}{$arrayJoueur[i].Matric}{/if}
                                        {if $arrayJoueur[i].Saison != $sSaison} <span class='highlight2'>({$arrayJoueur[i].Saison})</span>{/if}
										{if $profile <= 6}
											<a href="GestionAthlete.php?Athlete={$arrayJoueur[i].Matric}"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										{/if}
									</td>
									<td>{$arrayJoueur[i].Numero_club}</td>
									<td>{$arrayJoueur[i].Categ} - {$arrayJoueur[i].Sexe}</td>
									<td {if $arrayJoueur[i].PagaieValide == 0} class='highlight2'{/if}>
										{if $arrayJoueur[i].PagaieValide == 2}
											<img width="16" src="../img/EV-{$arrayJoueur[i].Pagaie_EVI}.gif" title="{#Pagaie_eau_vive#}" />
										{/if}
										<img width="16" src="../img/EC-{$arrayJoueur[i].Pagaie_ECA}.gif" title="{#Pagaie_eau_calme#}" />
										{if $arrayJoueur[i].PagaieValide == 3}
											<img width="16" src="../img/ME-{$arrayJoueur[i].Pagaie_MER}.gif" title="{#Pagaie_mer#}" />
										{/if}
									</td>
									<td><!--<span title='Loisir'>{$arrayJoueur[i].CertifAPS}</span>/-->{if $arrayJoueur[i].CertifCK != 'OUI'}<span class='highlight2' title='Compétition'>NON</span>{else}<span title='Compétition'>OUI</span>{/if}</td>
									<td>{$arrayJoueur[i].Arbitre}</td>
									{if $Validation == 'O'}
										<td>&nbsp;</td>
									{else}
										<td><a href="#" onclick="RemoveCheckbox('formMatchEquipeJoueur', '{$arrayJoueur[i].Matric}');return false;"><img width="16" src="../img/supprimer.gif" alt="Supprimer" title="Supprimer" /></a></td>
									{/if}
								</tr>
								{/section}
							</tbody>
						</table>
						<div>
							Les entraineurs et arbitres ne sont pas comptabilisés dans les statistiques.
						</div>
						<br>
						<br>
						<div class='liens'>
							<a href="#" onclick="CopieCompoEquipeJournee({$idJournee})">Copier cette composition sur les autres matchs (non verrouillés) de la journée n°{$idJournee}.</a>
							<br>
							<br>
							{if $profile <= 4}
							<a href="#" onclick="CopieCompoEquipeCompet({$idJournee})">Copier cette composition sur les autres matchs (non verrouillés) de la compétition.</a>
							<br>
							<br>
							{/if}
							<img width="21" src="../img/verrou{$Validation|default:'N'}" alt="Verrou" title="Verrou" />
						</div>
					</div>
					<div id='directSelecteur'>
						<select id='directSelecteurSelect' size=4>
							<option value='-'>Joueur</option>
							<option value='C'>Capitaine</option>
							<option value='E'>Entraineur (non joueur)</option>
						</select>
						<!--<img id='validButton' width="16" height="16" src="../img/valider.gif" alt="Valider" title="Valider" border="0">-->
						<img id='annulButton' width="16" src="../img/annuler.gif" alt="Annuler" title="Annuler" />
						<input type=hidden id='variables' value='' />
					</div>
		        </div>
		        {if $Validation != 'O'}
					{if $profile <= 6 or $profile == 9}
					<div class='blocRight'>
						<table width=100%>
							<tr>
								<th class='titreForm' colspan=2>
									<label>Sélectionner un athlète</label>
								</th>
							</tr>
							<tr>
								<td colspan=2>
									<label class="rouge">Recherche (nom, prénom ou licence)</label>
									<input type="text" name="choixJoueur" id="choixJoueur"/>
									<hr>
								</td>
							</tr>
							<tr>
								<td width=60%>
									<label for="matricJoueur2">N° Licence :</label>
									<input type="text" name="matricJoueur2" readonly maxlength=10 id="matricJoueur2"/>
								</td>
							</tr>
							<tr>
								<td colspan=2>
									<label for="nomJoueur2">Nom :</label>
									<input type="text" name="nomJoueur2" readonly maxlength=30 id="nomJoueur2"/>
								</td>
							</tr>
							<tr>
								<td colspan=2>
									<label for="prenomJoueur2">Prénom :</label>
									<input type="text" name="prenomJoueur2" readonly maxlength=30 id="prenomJoueur2"/>
								</td>
							</tr>
							<tr>
								<td>
									<label for="naissanceJoueur2">Date Naissance :</label>
									<input type="text" name="naissanceJoueur2" readonly maxlength=10 id="naissanceJoueur2" >
								</td>
								<td>
									<label for="sexeJoueur2">Sexe :</label>
									<input type="text" name="sexeJoueur2" readonly maxlength=1 id="sexeJoueur2" >
								</td>
							</tr>
							<tr>
								<td colspan=2><center><i>Optionnel :</i></center></td>
							</tr>
							<tr>
								<td>
									<label for="capitaineJoueur2">Capit./Entr.:</label>
									<select name="capitaineJoueur2">
										<Option Value="" SELECTED>Joueur</Option>
										<Option Value="C">Capitaine</Option>
										<Option Value="E">Entraineur (non joueur)</Option>
									</select>
								</td>
								<td>
									<label for="numeroJoueur">Numero</label>
									<input type="text" name="numeroJoueur2" maxlength=2 id="numeroJoueur2">
								</td>
							</tr>
							{if $typeCompet == 'CH' or $typeCompet == 'CF'}
								<tr>
									<td colspan=2><center><i>Contrôle :</i></center></td>
								</tr>
								<tr>
									<td>
										Licence<br />
										Certif CK (Compet.)<br />
										Certif APS (Loisir)<br />
										Pagaie ECA<br />
										Cat.
									</td>
									<td>
										<span id="origineJoueur2"></span><br />
										<span id="CKJoueur2"></span><br />
										<span id="APSJoueur2"></span><br />
										<span id="pagaieJoueur2"></span><br />
										<span id="catJoueur2"></span><br />
									</td>
								</tr>
							{/if}
							<tr>
								<td colspan=2>
									<br>
									<input type="button" onclick="Add2();" name="addEquipeJoueur2" value="<< Ajouter à ce match">
								</td>
							</tr>
						</table>
						<table width=100%>
							<tr>
								<th class='titreForm' colspan=2>
									<label>Recherche avancée</label>
								</th>
							</tr>
							<tr>
								<td>
									<input type="button" onclick="FindLicence();" name="findLicence" value="&reg; Recherche Licenciés...">
								</td>
							</tr>
						</table>
						<br>
						<br>
						<table width=100%>
							<tr>
								<th class='titreForm' colspan=2>
									<label>Ajouter les joueurs présents</label>
								</th>
							</tr>
							<tr>
								<td>
									<label class="rouge">(sauf X-Inactifs)</label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="button" onclick="DelJoueurs();" name="delJoueurs" value="<< Supprimer tous les joueurs">
								</td>
							</tr>
							<tr>
								<td>
									<input type="button" onclick="AddJoueurTitulaire();" name="addJoueurTitulaire" value="<< Ajouter les présents">
								</td>
							</tr>
						</table>
					</div>
					{/if}
				{/if}		
			</form>			
		</div>
