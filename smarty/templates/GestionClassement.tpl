 	 	&nbsp;(<a href="GestionCalendrier.php">Retour</a>)

		<div class="main">
			<form method="POST" action="GestionClassement.php" name="formClassement" id="formClassement" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value=''/>
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Competitions_Equipes'/>
				<input type='hidden' name='AjaxTableName2' id='AjaxTableName2' Value='gickp_Competitions_Equipes_Journee'/>
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = '/>
				<input type='hidden' name='AjaxAnd' id='AjaxAnd' Value='And Id_journee = '/>
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}'/>

				<div class='blocLeft'>
					<div class='titrePage'>Classement</div>
					<label for="saisonTravail">Saison :</label>
					<select name="saisonTravail" onChange="sessionSaison()">
						{section name=i loop=$arraySaison} 
							<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $sessionSaison}selected{/if}>{$arraySaison[i].Code}{if $arraySaison[i].Code eq $sessionSaison} (Travail){/if}</Option>
						{/section}
					</select>
					<label for="codeCompet">Comp&eacute;tition :</label>
					<select name="codeCompet" onChange="changeCompetition();">
						{section name=i loop=$arrayCompetition} 
							<Option Value="{$arrayCompetition[i][0]}" {$arrayCompetition[i][2]}>{$arrayCompetition[i][1]}</Option>
						{/section}
					</select>
					&nbsp;
					<a href="GestionJournee.php?Compet={$codeCompet}" title="Acc&egrave;s direct aux matchs de cette comp&eacute;tition"><img src="../img/b_match.png" alt="Matchs" height="20"></a>
					<br>
					<label for="orderCompet">Type de classement : </label>
					{if $profile <= 3 && $AuthModif == 'O'}
						<select name="orderCompet" onChange="changeOrderCompetition();">
							{section name=i loop=$arrayOrderCompetition} 
								<Option Value="{$arrayOrderCompetition[i][0]}" {$arrayOrderCompetition[i][2]}>{$arrayOrderCompetition[i][1]}</Option>
									{if $arrayOrderCompetition[i][2]=='SELECTED'}
									{assign var='typeCompetition' value=$arrayOrderCompetition[i][1]}
									{/if}
							{/section}
						</select>
					{else}
						{section name=i loop=$arrayOrderCompetition} 
							{if $arrayOrderCompetition[i][2]=='SELECTED'}
								{assign var='typeCompetition' value=$arrayOrderCompetition[i][1]}
								{$typeCompetition}
							{/if}
						{/section}
					{/if}
					<img height="20" class="cliquable" id="actuButton" title="Recharger" src="../img/glyphicons-82-refresh.png">
					<br>
					<div class='blocTable table2'>
						<table class='tableauJQ tableauClassement tableau'>
							<thead>
								<tr>
									<th width="17">&nbsp;</th>
									<th>&nbsp;</th>
									{if $Code_niveau == 'INT'}
										<th></th>
									{/if}
									<th>Cl.</th>
									<th>Classement type {$typeCompetition}</th>
									{if $typeCompetition=='Championnat'}
										<th>Pts</th>
									{/if}
									<th>J</th>
									<th>G</th>
									<th>N</th>
									<th>P</th>
									<th>F</th>
									<th>+</th>
									<th>-</th>
									<th>Diff</th>
								</tr>
							</thead>
							<tbody>
							{section name=i loop=$arrayEquipe} 
								<tr height="17" class='{cycle values="impair,pair"}'>
									{if $smarty.section.i.iteration <= $Qualifies}
										<td class='vert'><img width="16" src="../img/up.gif" alt="Qualifi&eacute;" title="Qualifi&eacute;" /></td>
									{elseif $smarty.section.i.iteration > $smarty.section.i.total - $Elimines}
										<td class='rouge'><img width="16" src="../img/down.gif" alt="Elimin&eacute;s" title="Elimin&eacute;s" /></td>
									{else}
										<td>&nbsp;</td>
									{/if}
									<td>
									{if $profile <= 4 && $AuthModif == 'O'}
										<input type="checkbox" name="checkClassement" value="{$arrayEquipe[i].Id}" id="checkClassement{$smarty.section.i.iteration}" />
									{/if}
									</td>
									{if $Code_niveau == 'INT'}
										<td> <img width="20" src="../img/Pays/{$arrayEquipe[i].Code_comite_dep}.png" alt="{$arrayEquipe[i].Code_comite_dep}" title="{$arrayEquipe[i].Code_comite_dep}" /></td>
									{/if}
									{if $profile <= 4 && $AuthModif == 'O'}
										{if $typeCompetition=='Championnat'}
											<td width="30"><span class='directInput' Id="Clt-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}0">{$arrayEquipe[i].Clt}</span></td>
											<td width="200">{$arrayEquipe[i].Libelle}</td>
											<td width="40"><span class='directInput' Id="Pts-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}1">{$arrayEquipe[i].Pts/100}</span></td>
										{else}
											<td width="30"><span class='directInput' Id="CltNiveau-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}0">{$arrayEquipe[i].CltNiveau}</span></td>
											<td width="200">{$arrayEquipe[i].Libelle}</td>
											{*<td width="40"><span class='directInput' Id="PtsNiveau-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}1">{$arrayEquipe[i].PtsNiveau}</span></td>*}
										{/if}
										<td width="30"><span class='directInput' Id="J-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}2">{$arrayEquipe[i].J}</span></td>
										<td width="30"><span class='directInput' Id="G-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}3">{$arrayEquipe[i].G}</span></td>
										<td width="30"><span class='directInput' Id="N-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}4">{$arrayEquipe[i].N}</span></td>
										<td width="30"><span class='directInput' Id="P-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}5">{$arrayEquipe[i].P}</span></td>
										<td width="30"><span class='directInput' Id="F-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}6">{$arrayEquipe[i].F}</span></td>
										<td width="40"><span class='directInput' Id="Plus-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}7">{$arrayEquipe[i].Plus}</span></td>
										<td width="40"><span class='directInput' Id="Moins-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}8">{$arrayEquipe[i].Moins}</span></td>
										<td width="40"><span class='directInput' Id="Diff-{$arrayEquipe[i].Id}" tabindex="1{$smarty.section.i.iteration}9">{$arrayEquipe[i].Diff}</span></td>
									{else}
										{if $typeCompetition=='Championnat'}
											<td width="30">{$arrayEquipe[i].Clt}</td>
											<td width="200">{$arrayEquipe[i].Libelle}</td>
											<td width="40">{$arrayEquipe[i].Pts/100}</td>
										{else}
											<td width="30">{$arrayEquipe[i].CltNiveau}</td>
											<td width="200">{$arrayEquipe[i].Libelle}</td>
											{*<td width="40">{$arrayEquipe[i].PtsNiveau}</td>*}
										{/if}
										<td width="30">{$arrayEquipe[i].J}</td>
										<td width="30">{$arrayEquipe[i].G}</td>
										<td width="30">{$arrayEquipe[i].N}</td>
										<td width="30">{$arrayEquipe[i].P}</td>
										<td width="30">{$arrayEquipe[i].F}</td>
										<td width="40">{$arrayEquipe[i].Plus}</td>
										<td width="40">{$arrayEquipe[i].Moins}</td>
										<td width="40">{$arrayEquipe[i].Diff}</td>
									{/if}
								
								</tr>
							{/section}
							</tbody>
						</table>
						{if $typeCompetition=='Championnat'}
							Le classement est effectu&eacute; par Points, puis Diff&eacute;rence de but. 
							<br>
							<b>Pour prendre en compte un classement diff&eacute;rent, modifier manuellement
							<br>
							l'ordre de classement des &eacute;quipes &agrave;  &eacute;galit&eacute; de points (colonne Cl.).</b>
						{/if}
						<br>
						{if $typeCompetition != 'Championnat'}
							<table id='tableauJQ2' class='tableauJQ tableau'>
								<thead>
									<tr>
										<th colspan="12">Classement par phase</th>
									</tr>
								</thead>
								<tbody>
								{assign var='idJournee' value='0'}

								{section name=i loop=$arrayEquipe_journee} 
									{if $arrayEquipe_journee[i].J != 0}
										{if $arrayEquipe_journee[i].Id_journee != $idJournee}
											<tr class='head2'>
												<th colspan="3">{$arrayEquipe_journee[i].Phase} ({$arrayEquipe_journee[i].Lieu})</th>
												<th>Pts</th>
												<th>J</th>
												<th>G</th>
												<th>N</th>
												<th>P</th>
												<th>F</th>
												<th>+</th>
												<th>-</th>
												<th>Diff</th>
											</tr>
										{/if}
										{assign var='idJournee' value=$arrayEquipe_journee[i].Id_journee}
										<tr height="17" class='{cycle values="impair,pair"}'>
											<td>&nbsp;</td>
											{if $profile <= 4 && $AuthModif == 'O'}
												<td width="30"><span class='directInput' Id="Clt-{$arrayEquipe_journee[i].Id}-{$arrayEquipe_journee[i].Id_journee}" tabindex="2{$smarty.section.i.iteration}0">{$arrayEquipe_journee[i].Clt}</span></td>
												<td width="200">{$arrayEquipe_journee[i].Libelle}</td>
												<td width="40"><span class='directInput' Id="Pts-{$arrayEquipe_journee[i].Id}-{$arrayEquipe_journee[i].Id_journee}" tabindex="2{$smarty.section.i.iteration}1">{$arrayEquipe_journee[i].Pts/100}</span></td>
											{else}
												<td width="30" >{$arrayEquipe_journee[i].Clt}</td>
												<td width="200">{$arrayEquipe_journee[i].Libelle}</td>
												<td width="40" >{$arrayEquipe_journee[i].Pts/100}</td>
											{/if}
											{*<td width="40">{$arrayEquipe_journee[i].Pts/100}</td>*}
											<td width="30">{$arrayEquipe_journee[i].J}</td>
											<td width="30">{$arrayEquipe_journee[i].G}</td>
											<td width="30">{$arrayEquipe_journee[i].N}</td>
											<td width="30">{$arrayEquipe_journee[i].P}</td>
											<td width="30">{$arrayEquipe_journee[i].F}</td>
											<td width="40">{$arrayEquipe_journee[i].Plus}</td>
											<td width="40">{$arrayEquipe_journee[i].Moins}</td>
											<td width="40">{$arrayEquipe_journee[i].Diff}</td>
										</tr>
									{/if}	
								{/section}
								</tbody>
							</table>
						{/if}
						<br>
						<hr>
						<br>
						<table class='tableau tableauPublic'>
							<thead>
								<tr>
									<th width="17">&nbsp;</th>
									<th></th>
									{if $Code_niveau == 'INT'}
										<th>&nbsp;</th>
									{/if}
									<th colspan="2">Classement public</th>
									{if $typeCompetition=='Championnat'}
										<th>Pts</th>
									{/if}
									<th>J</th>
									<th>G</th>
									<th>N</th>
									<th>P</th>
									<th>F</th>
									<th>+</th>
									<th>-</th>
									<th>Diff</th>
								</tr>
							</thead>
							<tbody>
							{section name=i loop=$arrayEquipe_publi} 
								<tr height="17" class='{cycle values="impair2,pair2"}'>
									{if $smarty.section.i.iteration <= $Qualifies_publi}
										<td class='vert'><img width="16" src="../img/up.gif" alt="Qualifi&eacute;" title="Qualifi&eacute;" /></td>
									{elseif $smarty.section.i.iteration > $smarty.section.i.total - $Elimines_publi}
										<td class='rouge'><img width="16" src="../img/down.gif" alt="Elimin&eacute;s" title="Elimin&eacute;s" /></td>
									{else}
										<td>&nbsp;</td>
									{/if}
									
									<td>
										{if $profile <= 4 && $AuthModif == 'O'}
											<input type="checkbox" name="checkClassement" value="{$arrayEquipe_publi[i].Id}" id="checkClassement{$smarty.section.i.iteration}" />
										{/if}
									</td>
									{if $Code_niveau == 'INT'}
										<td><img width="20" src="../img/Pays/{$arrayEquipe_publi[i].Code_comite_dep}.png" alt="{$arrayEquipe_publi[i].Code_comite_dep}" title="{$arrayEquipe_publi[i].Code_comite_dep}" /></td>
									{/if}
									{if $typeCompetition=='Championnat'}
										<td width="30">{$arrayEquipe_publi[i].Clt}</td>
										<td width="200">{$arrayEquipe_publi[i].Libelle}</td>
										<td width="40">{$arrayEquipe_publi[i].Pts/100}</td>
									{else}
										<td width="30">{$arrayEquipe_publi[i].CltNiveau}</td>
										<td width="200">{$arrayEquipe_publi[i].Libelle}</td>
										{*<td width="40">{$arrayEquipe_publi[i].PtsNiveau}</td>*}
									{/if}
									
									<td width="30">{$arrayEquipe_publi[i].J}</td>
									<td width="30">{$arrayEquipe_publi[i].G}</td>
									<td width="30">{$arrayEquipe_publi[i].N}</td>
									<td width="30">{$arrayEquipe_publi[i].P}</td>
									<td width="30">{$arrayEquipe_publi[i].F}</td>
									<td width="40">{$arrayEquipe_publi[i].Plus}</td>
									<td width="40">{$arrayEquipe_publi[i].Moins}</td>
									<td width="40">{$arrayEquipe_publi[i].Diff}</td>
								
								</tr>
							{/section}
							</tbody>
						</table>
						<br>
						{if $typeCompetition != 'Championnat'}
							<table class='tableau tableauPublic'>
								<thead>
									<tr>
										<th></th>
										<th colspan="11">Classement public par phase</th>
									</tr>
								</thead>
								<tbody>
								{assign var='idJournee' value='0'}

								{section name=i loop=$arrayEquipe_journee_publi} 
									{if $arrayEquipe_journee_publi[i].J != 0}
										{if $arrayEquipe_journee_publi[i].Id_journee != $idJournee}
											<tr class='head2Public'>
												<th colspan="3">{$arrayEquipe_journee_publi[i].Phase} ({$arrayEquipe_journee_publi[i].Lieu})</th>
												<th>Pts</th>
												<th>J</th>
												<th>G</th>
												<th>N</th>
												<th>P</th>
												<th>F</th>
												<th>+</th>
												<th>-</th>
												<th>Diff</th>
											</tr>
										{/if}
										{assign var='idJournee' value=$arrayEquipe_journee_publi[i].Id_journee}
										<tr height="17" class='{cycle values="impair2,pair2"}'>
											
											<td>
												{if $profile <= 4 && $AuthModif == 'O'}
													<input type="checkbox" name="checkClassement" value="{$arrayEquipe_journee_publi[i].Id}" id="checkClassement{$smarty.section.i.iteration}" />
												{/if}
											</td>
											<td width="30">{$arrayEquipe_journee_publi[i].Clt}</td>
											<td width="200">{$arrayEquipe_journee_publi[i].Libelle}</td>
											<td width="40">{$arrayEquipe_journee_publi[i].Pts/100}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].J}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].G}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].N}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].P}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].F}</td>
											<td width="40">{$arrayEquipe_journee_publi[i].Plus}</td>
											<td width="40">{$arrayEquipe_journee_publi[i].Moins}</td>
											<td width="40">{$arrayEquipe_journee_publi[i].Diff}</td>
										</tr>
									{/if}
								{/section}
								</tbody>
							</table>
						{/if}

					</div>
				</div>
				<div class='blocRight'>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=4>
								<label>Classement type {$typeCompetition}</label>
							</th>
						</tr>
						<tr>
							<td align='center' colspan=4>
								{if $Date_calcul == '00/00/00 &agrave;  00h00'}Classement non calcul&eacute;{else}
								Calcul du {$Date_calcul}<br>&nbsp;(par {$UserName_calcul}){/if}<br>
								<hr>
							</td>
						</tr>
						{if ($profile <= 6 or $profile == 9) && $AuthModif == 'O'}
							<tr>
								<td align='center' width=20><input type="checkbox" name="allMatchs" id="allMatchs" value="ok"{if $Mode_calcul == 'tous'} checked{/if}></td>
								<td colspan=3>Inclure les matchs non verrouill&eacute;s</td>
							</tr>
							<tr>
								<td colspan=4>
									<input type="button" onclick="computeClt();" name="Calculer" value="Recalculer">
								</td>
							</tr>
						{/if}
						{if $profile <= 6 && $AuthModif == 'O'}
							<tr>
								<td colspan=4>
									<hr>
									<input type="button" onclick="initClt();" name="Initialiser" value="Classement initial...">
									<hr>
								</td>
							</tr>
						{/if}
					
					</table>
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=4>
								<label>Classement public</label>
							</th>
						</tr>
						<tr>
							
							<td colspan=4 align='center' class='color{if $Date_publication_calcul eq $Date_calcul}O{else}N{/if}'>
								{if $Code_uti_publication != ''}
									{if $Date_publication_calcul == '00/00/00 &agrave;  00h00'}Classement manuel{else}Calcul&eacute; le {$Date_publication_calcul}{/if}<br>
									Publi&eacute; le {$Date_publication}<br>
									par {$UserName_publication}
									{if $Date_publication_calcul != $Date_calcul}
									<br><br>
									Attention : Classement publi&eacute;<br>diff&eacute;rent du dernier calcul !
									{/if}
								{else}
									Classement non publi&eacute; !
								{/if}
							</td>
						</tr>
						{if ($profile <= 4) && $AuthModif == 'O'}
							<tr>
								<td colspan=4 align='center'>
									<input type="button" onclick="publicationClt();" name="Publier" value="Publier le nouveau classement">
								</td>
							</tr>
						{/if}
						{if ($profile <= 3) && $AuthModif == 'O'}
							<tr>
								<td>&nbsp;&nbsp;</td>
								<td align='center' colspan="2">
									<br>
									<input type="button" onclick="depublicationClt();" name="D&eacute;-publier" value="-Supprimer le classement public-">
								</td>
								<td>&nbsp;&nbsp;</td>
							</tr>
						{/if}
                        
                    </table>
                        
                    <table>
                        {if $typeCompetition=='Championnat'}
                            <tr>
                                <td colspan=2 align='left'><b>Admin<br><i>(provisoire)</i></b></td>
                                <td colspan=2 align='right'><b>Public</b></td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltChpt.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="Classement g&eacute;n&eacute;ral admin" title="Classement g&eacute;n&eacute;ral admin" /></a>
                                </td>
                                <td colspan=2 align='center'>Classement g&eacute;n&eacute;ral</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltChpt.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="Classement g&eacute;n&eacute;ral public" title="Classement g&eacute;n&eacute;ral public" /></a>
                                {/if}
                                </td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltChptDetail.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par &eacute;quipe admin" title="D&eacute;tail par &eacute;quipe admin" /></a>
                                </td>
                                <td colspan=2 align='center'>D&eacute;tail par &eacute;quipe</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltChptDetail.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par &eacute;quipe public" title="D&eacute;tail par &eacute;quipe public" /></a>
                                {/if}
                                </td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveauJournee.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par journ&eacute;e admin" title="D&eacute;tail par journ&eacute;e admin" /></a>
                                </td>
                                <td colspan=2 align='center'>D&eacute;tail par journ&eacute;e</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveauJournee.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par journ&eacute;e public" title="D&eacute;tail par journ&eacute;e public" /></a>
                                {/if}
                                </td>
                            </tr>
                        {else}
                            <tr>
                                <td colspan=2 align='left'><b>Admin<br><i>(provisoire)</i></b></td>
                                <td colspan=2 align='right'><b>Public</b></td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveau.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="Classement g&eacute;n&eacute;ral admin" title="Classement g&eacute;n&eacute;ral admin" /></a>
                                </td>
                                <td colspan=2 align='center'>Classement g&eacute;n&eacute;ral</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveau.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="Classement g&eacute;n&eacute;ral public" title="Classement g&eacute;n&eacute;ral public" /></a>
                                {/if}
                                </td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveauPhase.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par phase admin" title="D&eacute;tail par phase admin" /></a>
                                </td>
                                <td colspan=2 align='center'>D&eacute;tail par phase</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveauPhase.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par phase public" title="D&eacute;tail par phase public" /></a>
                                {/if}
                                </td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveauNiveau.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par niveau admin" title="D&eacute;tail par niveau admin" /></a>
                                </td>
                                <td colspan=2 align='center'>D&eacute;tail par niveau</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveauNiveau.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par niveau public" title="D&eacute;tail par niveau public" /></a>
                                {/if}
                                </td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveauDetail.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par &eacute;quipe admin" title="D&eacute;tail par &eacute;quipe admin" /></a>
                                </td>
                                <td colspan=2 align='center'>D&eacute;tail par &eacute;quipe</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveauDetail.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par &eacute;quipe public" title="D&eacute;tail par &eacute;quipe public" /></a>
                                {/if}
                                </td>
                            </tr>
                        {/if}
						<tr>
							<td colspan=4 align='center'><hr></td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleListeMatchs.php?Compet={$codeCompet}" Target="_blank"><img height="30" src="../img/pdf.png" alt="Liste des matchs admin" title="Liste des matchs admin" /></a>
							</td>
							<td colspan=2 align='center'>Matchs</td>
							<td align='right'>
								<a href="../PdfListeMatchs.php?Compet={$codeCompet}" Target="_blank"><img height="30" src="../img/pdf.png" alt="Liste des matchs public" title="Liste des matchs public" /></a>
							</td>
						</tr>
					</table>
                            
					{if $profile <= 4 && $AuthModif == 'O'}
						<br>
						<table width="100%">
							<tr>
								<th class='titreForm'>
									<label>Affectation, promotion, rel&eacute;gation</label>
								</th>
							</tr>
							<tr>
								<td>
									<label for="codeSaisonTransfert">Affecter vers saison :</label>
									<select name="codeSaisonTransfert" id="codeSaisonTransfert" onchange="changeSaisonTransfert()">
										{section name=i loop=$arraySaisonTransfert} 
											<Option Value="{$arraySaisonTransfert[i].Code}" {if $arraySaisonTransfert[i].Code == $codeSaisonTransfert}selected{/if}>{$arraySaisonTransfert[i].Code}</Option>
										{/section}
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<label for="codeCompetTransfert">Affecter vers comp&eacute;tition :</label>
									<select name="codeCompetTransfert" id="codeCompetTransfert">
										{section name=i loop=$arrayCompetitionTransfert} 
											<!--
											<Option Value="{$arrayCompetitionTransfert[i][0]}" {$arrayCompetitionTransfert[i][2]}>{$arrayCompetitionTransfert[i][1]}</Option>
											-->
											<Option Value="{$arrayCompetitionTransfert[i][0]}" {if $arrayCompetitionTransfert[i][0] == $codeCompetTransfert}selected{/if}>{$arrayCompetitionTransfert[i][1]}</Option>
										{/section}
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<br>
									<input type="button" onclick="transfert();" name="Transfert" value="Affecter les &eacute;quipes coch&eacute;es">
								</td>
							</tr>
						</table>
					{/if}
				</div>
						
			</form>			
					
		</div>	  	   
