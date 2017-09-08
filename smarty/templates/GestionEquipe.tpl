		&nbsp;(<a href="Admin.php">Retour</a>)
	
		<div class="main">
					
			<form method="POST" action="GestionEquipe.php" name="formEquipe" id="formEquipe" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value='' />
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Competitions_Equipes' />
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = ' />
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}' />
				<input type='hidden' name='Saison' id='Saison' Value='{$codeSaison}' />
                <input type='hidden' name='Compet' id='Compet' value='{$codeCompet}' />
	
				<div class='blocLeft Left2'>
					<div class='titrePage'>Equipes engag&eacute;es</div>
					<label for="competition">Comp&eacute;tition :</label>
					<select name='competition' id='competition' onChange="changeCompetition();">
                        {section name=i loop=$arrayCompetition}
                            {assign var='options' value=$arrayCompetition[i].options}
                            {assign var='label' value=$arrayCompetition[i].label}
                            <optgroup label="{$smarty.config.$label|default:$label}">
                                {section name=j loop=$options}
                                    {assign var='optionLabel' value=$options[j].Code}
                                    <Option Value="{$options[j].Code}" {$options[j].selected}>{$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>
                                {/section}
                            </optgroup>
                        {/section}
                        <optgroup label="Arbitres / Referees">
                            <Option Value="POOL" {if $codeCompet == 'POOL'}selected{/if}>Pool arbitres</Option>
                        </optgroup>
					</select>

					<div class='liens'>
						<table>
							<tr>
								<td width=200>
									<fieldset>
										<a href="#" title="S&eacute;lectionner tous" onclick="setCheckboxes('formEquipe', 'checkEquipe', true);return false;"><img height="22" src="../img/glyphicons-155-more-checked.png" /></a>
										<a href="#" title="S&eacute;lectionner aucun" onclick="setCheckboxes('formEquipe', 'checkEquipe', false);return false;"><img height="22" src="../img/glyphicons-155-more-windows.png" /></a>
										{if $profile <=6 && $AuthModif == 'O' && $bProd}
											<a href="#" title="Supprimer la s&eacute;lection" onclick="RemoveCheckboxes('formEquipe', 'checkEquipe')"><img height="25" src="../img/glyphicons-17-bin.png" /></a>
										{/if}
										<a href="#" onclick=""><img src="../img/map.gif" height="25" alt="Cartographier la s&eacute;lection (en construction)" title="Cartographier la s&eacute;lection (en construction)" /></a>
										&nbsp;&nbsp;&nbsp;
									</fieldset>
								</td>
								<td>
									<a href="FeuilleGroups.php" target="_blank" title="Liste des &eacute;quipes par poule"><img height="25" src="../img/pdf.png" /></a>						
									<a href="FeuillePresence.php" target="_blank" title="Toutes les feuilles de pr&eacute;sence"><img height="25" src="../img/pdf2.png" /></a>						
									<a href="FeuillePresenceEN.php" target="_blank" title="Toutes les feuilles de pr&eacute;sence - Anglais"><img height="25" src="../img/pdfEN.png" /></a>						
									<a href="FeuillePresenceCat.php" target="_blank" title="Feuilles de pr&eacute;sence par cat&eacute;gorie"><img height="25" src="../img/pdf2.png" />Cat</a>						
									<img class="cliquable" id="actuButton" title="Recharger" height="25" src="../img/glyphicons-82-refresh.png">
                                    {if $profile <= 4 && $Statut == 'ON'}
                                        <img class="cliquable" data-verrou="{$Verrou}" height="25" src="../img/verrou2{$Verrou}.gif" id="verrouCompet" title='(dé)verrouiller les feuilles de présence'>
                                        &nbsp;
                                        <img class="cliquable" height="25" src="../img/b_update.png" id="InitTitulaireCompet" title="Transférer les compos sur les feuilles de match (non verrouillées)">
                                    {/if}
								</td>
							</tr>
						</table>
					</div>
					<div class='blocTable'>
						<table class='tableau' id='tableEquipes'>
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th>Poule</th>
									<th># Tirage</th>
									{if $Code_niveau == 'INT'}
										<th>&nbsp;</th>
									{/if}
									<th>Equipe</th>
									<th>Pr&eacute;sents</th>
									<th># Club</th>
									<th>Nb matchs</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								{section name=i loop=$arrayEquipe} 
									{if $PouleX != $arrayEquipe[i].Poule && $arrayEquipe[i].Poule != ''}
										<tr class='colorO'>
											<th colspan=8><b>Poule {$arrayEquipe[i].Poule}</b></th>
										</tr>
									{/if}
									<tr class='{cycle values="impair,pair"}'>
										{assign var='PouleX' value=$arrayEquipe[i].Poule}
										<td><input type="checkbox" name="checkEquipe" value="{$arrayEquipe[i].Id}" id="checkDelete{$smarty.section.i.iteration}" /></td>
										<td><span {if $profile <=6 && $AuthModif == 'O'}class='directInput textPoule' {/if}tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}0' Id="Poule-{$arrayEquipe[i].Id}-text">{$arrayEquipe[i].Poule}</span></td>
										<td><span {if $profile <=6 && $AuthModif == 'O'}class='directInput textTirage' {/if}tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}1' Id="Tirage-{$arrayEquipe[i].Id}-text">{if $arrayEquipe[i].Tirage == '0'}{else}{$arrayEquipe[i].Tirage}{/if}</span></td>
										{if $Code_niveau == 'INT'}
											<td> <img width="20" src="../img/Pays/{$arrayEquipe[i].Code_comite_dep}.png" alt="{$arrayEquipe[i].Code_comite_dep}" title="{$arrayEquipe[i].Code_comite_dep}" /></td>
										{/if}
		
										<td class="cliquableNomEquipe"><a href="./GestionEquipeJoueur.php?idEquipe={$arrayEquipe[i].Id}" alt="Feuille de pr&eacute;sence" title="Feuille de pr&eacute;sence">{$arrayEquipe[i].Libelle}</A></td>
										<td><a href="./GestionEquipeJoueur.php?idEquipe={$arrayEquipe[i].Id}" alt="Feuille de pr&eacute;sence" title="Feuille de pr&eacute;sence"><img height="25" src="../img/b_sbrowse.png" /></A></td>
										<td>{$arrayEquipe[i].Code_club}</td>
										<td>{$arrayEquipe[i].nbMatchs}</td>
										{if $profile <= 3 && $AuthModif == 'O' && $bProd}
											<td><a href="#" onclick="RemoveCheckbox('formEquipe', '{$arrayEquipe[i].Id}');return false;"><img height="20" src="../img/glyphicons-17-bin.png" alt="Supprimer" title="Supprimer" /></a></td>
										{else}<td>&nbsp;</td>{/if}
									</tr>
								{/section}
							</tbody>
						</table>
					</div>
					<b>TOTAL = {$smarty.section.i.iteration-1|replace:-1:0} &eacute;quipes</b>
				</div>
	        
				{if $profile <=3 && $AuthModif == 'O' && $bProd}
				<div class='blocRight Right2'>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Affecter une &eacute;quipe</label>
							</th>
						</tr>
						<tr>
							<td>
								<label><b>Recherche :</b></label><input type="text" name="choixEquipe" id="choixEquipe" style="width:60%">
								<br>
								<div name="ShowCompo" id="ShowCompo">
									<input type="hidden" name="EquipeNum" id="EquipeNum">
									<input type="hidden" name="EquipeNumero" id="EquipeNumero">
									<input type="text" name="EquipeNom" id="EquipeNom" style="width:100%" readonly>
									<label title="Lettre A Ã  O majuscule" alt="Lettre A Ã  O majuscule">Poule:</label><input type="text" name="plEquipe" title="Lettre A &agrave;  O majuscule" alt="Lettre A &agrave;  O majuscule" id="plEquipe" style="width:8%" size=2>
									<label title="Nombre 1 Ã  99" alt="Nombre 1 Ã  99">Tirage:</label><input type="text" name="tirEquipe" id="tirEquipe" title="Nombre 1 &agrave;  99" alt="Nombre 1 &agrave;  99" style="width:8%" size=2>
									{if $user =='42054'}
										&nbsp;
										<label title="Classement Championnat" alt="Classement Championnat">Clt.Chpt:</label><input type="text" name="cltChEquipe" id="cltChEquipe" style="width:8%" size=2>
										<label title="Classement Coupe" alt="Classement Coupe">Clt.CP:</label><input type="text" name="cltCpEquipe" id="cltCpEquipe" style="width:8%" size=2>
									{/if}
									<span name="GetCompo" id="GetCompo"></span>
									<input type="button" onclick="Add2();" name="addEquipe2" id="addEquipe2" value="<< Ajouter" style="width:45%">
									<input type="button" name="annulEquipe2" id="annulEquipe2" value="Annuler" style="width:45%">
								</div>
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm'>
								<label>Recherche avanc&eacute;e / cr&eacute;ation</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="comiteReg">Comit&eacute; R&eacute;gional : </label>
								<select name="comiteReg" id="comiteReg" onChange="changeComiteReg();">
									{section name=i loop=$arrayComiteReg} 
										<Option Value="{$arrayComiteReg[i].Code}" {$arrayComiteReg[i].Selected}>{$arrayComiteReg[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label for="comiteDep">Comit&eacute; D&eacute;partemental / Pays : </label>				    
								<select name="comiteDep" id="comiteDep" onChange="changeComiteDep();">
									{section name=i loop=$arrayComiteDep} 
										<Option Value="{$arrayComiteDep[i].Code}" {$arrayComiteDep[i].Selected}>{$arrayComiteDep[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label for="club">Club / Structure : </label>				    
								<select name="club" id="club" onChange="changeClub();">
									{section name=i loop=$arrayClub} 
										<Option Value="{$arrayClub[i].Code}" {$arrayClub[i].Selected}>{$arrayClub[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label>Filtre Equipes :</label>
								<input type="radio" name="filtreH" id="filtreH" value=1 {$filtreH} onclick="filtreTous.checked=false;submit()">H
								<input type="radio" name="filtreH" id="filtreH" value=0 {$filtreF} onclick="filtreTous.checked=false;submit()">F
								<input type="checkbox" name="filtreJ" id="filtreJ" {$filtreJ} onclick="filtreTous.checked=false;submit()">J
								<input type="checkbox" name="filtre21" id="filtre21" {$filtre21} onclick="filtreTous.checked=false;submit()">-21
								<input type="checkbox" name="filtreTous" id="filtreTous" {$filtreTous|default:'selected'} onclick="submit()">TOUTES
							</td>
						</tr>
						<tr>
							<td>
								<!--
								<label>Recherche :</label>
								<input type="text" name="filtreText" id="filtreText" style="width:30%">
								<input type="button" id="filtreTextButton" style="width:20%" value="Chercher...">
								<input type="button" id="filtreAnnulButton" style="width:20%" value="Annuler">
								
								<span id='reachspan'><i>Surligner:</i></span><input type=text name='reach' id='reach' size='10'>
								-->
							</td>
						</tr>
						<tr>
							<td>
								<label for="histoEquipe">Choix Equipes :</label>
								<img title="Maintenez la touche CTRL pour s&eacute;lectionner plusieurs &eacute;quipes &agrave;  la fois." 
								alt="Maintenez la touche CTRL pour s&eacute;lectionner plusieurs &eacute;quipes &agrave;  la fois." 
								src="../img/b_help.png" 
								onclick="alert('Maintenez la touche CTRL pour s&eacute;lectionner plusieurs &eacute;quipes &agrave;  la fois.')" />
								<select name="histoEquipe[]" id="histoEquipe" class="histoEquip" onChange="changeHistoEquipe();" size="20" multiple>
									{section name=i loop=$arrayHistoEquipe} 
										{if $arrayHistoEquipe[i].Numero eq ''}
											<Option Value="0">{$arrayHistoEquipe[i].Libelle}</Option>
										{else}
											<Option Value="{$arrayHistoEquipe[i].Numero}">{$arrayHistoEquipe[i].Code_club} - {$arrayHistoEquipe[i].Libelle}</Option>
										{/if}

									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label for="libelleEquipe"><b>Nouvelle Equipe :</b></label>
								<img title="ATTENTION ! Cliquez pour plus d'info." 
								alt="ATTENTION ! Cliquez pour plus d'info." 
								src="../img/b_help.png" 
								onclick="alert('ATTENTION !\n Respectez bien le formalisme :\n \n -S&eacute;lectionnez le club d\'appartenance avant tout (+CR +CD),\n -Nom d\'&eacute;quipe en minuscule, premi&egrave;re lettre en majuscule,\n -Un espace avant le num&eacute;ro d\'ordre et avant la cat&eacute;gorie\n -Num&eacute;ro d\'ordre obligatoire, en chiffre romain : I II III IV\n -Cat&eacute;gorie f&eacute;minine avec \' F\' (\' Ladies\' ou \' Women\' pour les &eacute;quipes &eacute;trang&egrave;res)\n -Cat&eacute;gorie jeunes avec \' JF\' ou \' JH\' (masculine ou mixte)\n -Cat&eacute;gorie -21 ans avec \' -21\' (\' U21\' pour les &eacute;quipes &eacute;trang&egrave;res)\n \n Exemples :\n Acign&eacute; II, Acign&eacute; I F, Acign&eacute; JH, Belgium U21 Women, Keistad Ladies...')" />
								<input type="text" name="libelleEquipe" maxlength=40 id="libelleEquipe"/>
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" onclick="Add();" name="addEquipe" id="addEquipe" value="<< Ajouter">
							</td>
						</tr>
					</table>
					{/if}
					{if $profile <=4 && $AuthModif == 'O'}
					<br>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=3>
								<label>Tirage au sort</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="equipeTirage">Equipe :</label>
								<select name="equipeTirage" id="equipeTirage">
									{section name=i loop=$arrayEquipe} 
											<Option Value="{$arrayEquipe[i].Id}">{$arrayEquipe[i].Libelle}</Option>
									{/section}
								</select>
							</td>
							<td>
								<label for="pouleTirage">Poule :</label>
								<select name="pouleTirage" id="pouleTirage">
									<Option Value="">nc</Option>
									<Option Value="A">A</Option>
									<Option Value="B">B</Option>
									<Option Value="C">C</Option>
									<Option Value="D">D</Option>
									<Option Value="E">E</Option>
									<Option Value="F">F</Option>
									<Option Value="G">G</Option>
									<Option Value="H">H</Option>
									<Option Value="I">I</Option>
									<Option Value="J">J</Option>
									<Option Value="K">K</Option>
									<Option Value="L">L</Option>
									<Option Value="M">M</Option>
									<Option Value="N">N</Option>
									<Option Value="O">O</Option>
								</select>
							</td>
							<td>
								<label for="ordreTirage">Tirage :</label>
								<select name="ordreTirage" id="ordreTirage">
									<Option Value="0">nc</Option>
									{section name=i loop=$arrayEquipe} 
											<Option Value="{$smarty.section.i.iteration}">T{$smarty.section.i.iteration}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=3>
								<input type="button" onclick="Tirage();" name="tirageEquipe" id="tirageEquipe" value="Valider ce tirage" />
							</td>
						</tr>
					</table>
				</div>
				{/if}
					
			</form>			
					
		</div>	  	

		
