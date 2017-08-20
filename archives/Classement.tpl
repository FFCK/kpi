 	 	<span class="repere">&nbsp;(<a href="index.php">{#Retour#}</a>)</span>
	
		<div class="main">
			<form method="POST" action="Classement.php" name="formClassement" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<div class='blocCentre'>
					<div class='titrePage'>{#Classement#}</div>
					<div class='soustitrePage'>
<!--						<label for="codeCompet">{#Competition#} :</label>
						<select name="codeCompet" onChange="changeCompetition();">
								<Option Value="">{#Selectionnez#}...</Option>
							{section name=i loop=$arrayCompetition} 
								<Option Value="{$arrayCompetition[i][0]}" {$arrayCompetition[i][2]}>{$arrayCompetition[i][1]}</Option>
							{/section}
						</select>
							{section name=i loop=$arrayOrderCompetition} 
								{if $arrayOrderCompetition[i][2]=='SELECTED'}
									{assign var='typeCompetition' value=$arrayOrderCompetition[i][1]}
								{/if}
							{/section}
-->						&nbsp;
						{if $arrayEquipe_journee_publi} 
							<a href="Journee.php?Compet={$codeCompet}" title="{#Acces_direct#}">{#Matchs#}...</a>
						{/if}
						{if $typeCompetition!='Championnat'}
							&nbsp;<a href="Classements.php?Compet={$codeCompet}" title="{#Classements#}">{#Classements#}...</a>
						{/if}
						<br>
					</div>
					<div class='blocTable table2'>
					{if $Statut == 'END'}
						<table class="tableau tableauPublic{if $typeCompetition != 'Championnat'} classementCoupe{/if}">
							<thead>
								<tr>
									<th width="17">&nbsp;</th>
									{if $typeCompetition=='Championnat'}
										<th colspan="2">{#Classement#} {$codeCompet} {$codeSaison3}
											<a class="pdfLink" href="PdfCltChpt.php?S={$codeSaison3}" Target="_blank"><img width="16" src="img/pdf.gif" alt="{#Classement#} (pdf)" title="{#Classement#} (pdf)" /></a>
										</th>
										<th>{#Pts#}</th>
										<th>{#J#}</th>
										<th>{#G#}</th>
										<th>{#N#}</th>
										<th>{#P#}</th>
										<th>{#F#}</th>
										<th>+</th>
										<th>-</th>
										<th>{#Diff#}</th>
									{else}
										<th colspan="2">{#Classement#} {$codeCompet} {$codeSaison3}
											<a class="pdfLink" href="PdfCltNiveau.php?S={$codeSaison3}" Target="_blank"><img width="16" src="img/pdf.gif" alt="{#Classement#} (pdf)" title="{#Classement#} (pdf)" /></a>
											{if $Statut != 'END'}<br /><span class="gris">- {#PROVISOIRE#} -</span>{/if}
										</th>
									{/if}
								</tr>
							</thead>
							<tbody>
							{section name=i loop=$arrayEquipe_publi} 
								<tr height="17" class='{cycle values="impair2,pair2"}'>
									{if $smarty.section.i.iteration <= $Qualifies}
										<td><img width="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
									{elseif $smarty.section.i.iteration > $smarty.section.i.total - $Elimines}
										<td><img width="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
									{else}
										<td>&nbsp;</td>
									{/if}
									
									{if $typeCompetition=='Championnat'}
										<td width="45" class="droite">
											{$arrayEquipe_publi[i].Clt}
											{if $Code_niveau == 'INT'}
												<img width="25" src="img/Pays/{$arrayEquipe_publi[i].Code_comite_dep}.png" alt="{$arrayEquipe_publi[i].Code_comite_dep}" title="{$arrayEquipe_publi[i].Code_comite_dep}" />
											{/if}
										</td>
										<td class="cliquableNomEquipe" width="190"><a href="Palmares.php?Equipe={$arrayEquipe_publi[i].Numero}" title="{#Palmares#}">{$arrayEquipe_publi[i].Libelle}</a></td>
										<td width="40">{$arrayEquipe_publi[i].Pts/100}</td>
										<td width="29">{$arrayEquipe_publi[i].J}</td>
										<td width="29">{$arrayEquipe_publi[i].G}</td>
										<td width="29">{$arrayEquipe_publi[i].N}</td>
										<td width="29">{$arrayEquipe_publi[i].P}</td>
										<td width="29">{$arrayEquipe_publi[i].F}</td>
										<td width="40">{$arrayEquipe_publi[i].Plus}</td>
										<td width="40">{$arrayEquipe_publi[i].Moins}</td>
										<td width="40">{$arrayEquipe_publi[i].Diff}</td>
									{else}
										<td width="45" class="droite">
											{$arrayEquipe_publi[i].CltNiveau}
											{if $Code_niveau == 'INT'}
												<img width="25" src="img/Pays/{$arrayEquipe_publi[i].Code_comite_dep}.png" alt="{$arrayEquipe_publi[i].Code_comite_dep}" title="{$arrayEquipe_publi[i].Code_comite_dep}" />
											{/if}
										</td>
										<td class="cliquableNomEquipe" width="190"><a href="Palmares.php?Equipe={$arrayEquipe_publi[i].Numero}" title="{#Palmares#}">{$arrayEquipe_publi[i].Libelle}</a></td>
										{*<td width="40">{$arrayEquipe_publi[i].PtsNiveau}</td>*}
									{/if}
									
								
								</tr>
							{/section}
							</tbody>
						</table>
						<br>
					{/if}
						{if $typeCompetition != 'Championnat'}
							<table class='tableau tableauPublic'>
								{if $arrayEquipe_journee_publi} 
								<thead>
									<tr>
										<th colspan="12">{#Classement_par_phase#}
											<a class="pdfLink" href="PdfCltNiveauPhase.php?S={$codeSaison3}" Target="_blank"><img width="16" src="img/pdf.gif" alt="{#Classement_par_phase#} (pdf)" title="{#Classement_par_phase#} (pdf)" /></a>
										</th>
									</tr>
								</thead>
								{/if}
								<tbody>
									{assign var='idJournee' value='0'}

									{section name=i loop=$arrayEquipe_journee_publi} 
										{if $arrayEquipe_journee_publi[i].J != 0}
											{if $arrayEquipe_journee_publi[i].Id_journee != $idJournee}
												<tr class='head2Public'>
													<th colspan="2">{$arrayEquipe_journee_publi[i].Phase}</th>
													<th>{#Pts#}</th>
													<th>{#J#}</th>
													<th>{#G#}</th>
													<th>{#N#}</th>
													<th>{#P#}</th>
													<th>{#F#}</th>
													<th>+</th>
													<th>-</th>
													<th>{#Diff#}</th>
												</tr>
											{/if}
											{assign var='idJournee' value=$arrayEquipe_journee_publi[i].Id_journee}
											<tr height="17" class='{cycle values="impair2,pair2"}'>
												<td width="30">{$arrayEquipe_journee_publi[i].CltNiveau}</td>
												<td class="cliquableNomEquipe" width="200"><a href="Palmares.php?Equipe={$arrayEquipe_journee_publi[i].Numero}" title="{#Palmares#}">{$arrayEquipe_journee_publi[i].Libelle}</a></td>
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
						{if $Code_uti_publication != ''}
							{#MAJ#} {$Date_publication_calcul} <span class='lienExterne'><a href="http://www.kayak-polo.info/Classement.php?Compet={$codeCompet}&S={$codeSaison3}" target="_blank">En savoir plus...</a></span><br>
						{/if}

					</div>
				</div>
			</form>			
					
		</div>	  	   
