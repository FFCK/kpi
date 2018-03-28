    &nbsp;(<a href="Admin.php">{#Retour#}</a>)
	<div class="main">
		<form method="POST" action="GestionStats.php" name="formStats" enctype="multipart/form-data">
			<input type='hidden' name='Cmd' Value=''/>
			<input type='hidden' name='ParamCmd' Value=''/>
			<div class='blocLeft'>
				<div class='titrePage'>{#Statistiques#} {$codeSaison} (beta)</div>
				{if $AfficheStat eq 'Buteurs'}
					<div class='titrePage'>{#Meilleur_buteur#}</div>
				{elseif $AfficheStat == 'Attaque'}
					<div class='titrePage'>{#Meilleure_attaque#} ({#Feuilles_marque#} {#seulement#})</div>
				{elseif $AfficheStat == 'Defense'}
					<div class='titrePage'>{#Meilleure_defense#} ({#Feuilles_marque#} {#seulement#})</div>
				{elseif $AfficheStat == 'Cartons'}
					<div class='titrePage'>{#Cartons#}</div>
				{elseif $AfficheStat == 'CartonsEquipe'}
					<div class='titrePage'>{#Cartons_par_equipe#}</div>
				{elseif $AfficheStat == 'Fairplay'}
					<div class='titrePage'>{#Classement_disciplinaire_individuel#} ({#Rouge#}=4, {#Jaune#}=2, {#Vert#}=1)</div>
				{elseif $AfficheStat == 'FairplayEquipe'}
					<div class='titrePage'>{#Classement_disciplinaire_par_equipe#} ({#Rouge#}=4, {#Jaune#}=2, {#Vert#}=1)</div>
				{elseif $AfficheStat == 'Arbitrage'}
					<div class='titrePage'>{#Arbitrage#}</div>
				{elseif $AfficheStat == 'ArbitrageEquipe'}
					<div class='titrePage'>{#Arbitrage_par_equipe#} ({#Arbitrages_nominatifs#} {#seulement#})</div>
				{elseif $AfficheStat == 'CJouees'}
					<div class='titrePage'>{#Competitions_jouees_par_club#} ({#Matchs_verrouilles#} {#seulement#})</div>
				{elseif $AfficheStat == 'CJouees2'}
					<div class='titrePage'>{#Competitions_jouees_par_equipe#} ({#Matchs_verrouilles#} {#seulement#})</div>
				{elseif $AfficheStat == 'CJouees3'}
					<div class='titrePage'>{#Irregularites#} ({#Matchs_verrouilles#} {#seulement#})</div>
				{elseif $AfficheStat == 'OfficielsJournees'}
					<div class='titrePage'>{#Officiels_des_journees#}</div>
				{elseif $AfficheStat == 'OfficielsMatchs'}
					<div class='titrePage'>{#Officiels_des_matchs#}</div>
				{elseif $AfficheStat == 'ListeArbitres'}
					<div class='titrePage'>{#Arbitres#}</div>
				{/if}
				<div class='liens'>
					<a href="FeuilleStats.php" Target="_blank" title="Pdf (FR)"><img height="30" alt="pdf FR" src="../img/pdfFR.png"></a>
					<a href="FeuilleStatsEN.php" Target="_blank" title="Pdf (EN)"><img height="30" alt="pdf EN" src="../img/pdfEN.png"></a>
                    {if $sql_csv != ''}
                        <a href="upload_csv.php?action=export" title="{#Telechargement#} CSV : {$sql_csv}"><img height="30" alt="CSV" src="../img/csv.png"></a>
                    {/if}
					<div align=right><span id='reachspan'><i>Surligner:</i></span><input type=text name='reach' id='reach' size='20'></div>
				</div>
					<div class='blocTable' id='blocCompet'>
						<table class='tableau' id='tableCompet'>
							<thead>
								<tr class='header'>
									{if $AfficheStat == 'Buteurs'}
										<th>#</th>
										<th>{#Comp#}.</th>
										<th>N°</th>
										<th>{#Nom#}</th>
										<th>{#Prenom#}</th>
										<th>{#Sexe#}</th>
										<th>{#Equipe#}</th>
										<th>{#Buts#}</th>
									{elseif $AfficheStat == 'Attaque'}
										<th>#</th>
										<th>{#Comp#}.</th>
										<th>{#Equipe#}</th>
										<th>Buts marqués</th>
									{elseif $AfficheStat == 'Defense'}
										<th>#</th>
										<th>{#Comp#}.</th>
										<th>{#Equipe#}</th>
										<th>Buts concédés</th>
									{elseif $AfficheStat == 'Cartons'}
										<th>#</th>
										<th>{#Comp#}.</th>
										<th>N°</th>
										<th>{#Nom#}</th>
										<th>{#Prenom#}</th>
										<th>{#Sexe#}</th>
										<th>{#Equipe#}</th>
										<th>{#Vert#}</th>
										<th>{#Jaune#}</th>
										<th>{#Rouge#}</th>
									{elseif $AfficheStat == 'CartonsEquipe'}
										<th>#</th>
										<th>{#Comp#}.</th>
										<th>{#Equipe#}</th>
										<th>{#Vert#}</th>
										<th>{#Jaune#}</th>
										<th>{#Rouge#}</th>
									{elseif $AfficheStat == 'Fairplay'}
										<th>#</th>
										<th>{#Comp#}.</th>
										<th>N°</th>
										<th>{#Nom#}</th>
										<th>{#Prenom#}</th>
										<th>{#Sexe#}</th>
										<th>{#Equipe#}</th>
										<th>{#Pts#}</th>
									{elseif $AfficheStat == 'FairplayEquipe'}
										<th>#</th>
										<th>{#Comp#}.</th>
										<th>{#Equipe#}</th>
										<th>{#Pts#}</th>
									{elseif $AfficheStat == 'Arbitrage'}
										<th>#</th>
										<th>{#Comp#}.</th>
										<th>N°</th>
										<th>{#Nom#}</th>
										<th>{#Prenom#}</th>
										<th>{#Sexe#}</th>
										<th>{#Principal#}</th>
										<th>{#Secondaire#}</th>
										<th>Total</th>
									{elseif $AfficheStat == 'ArbitrageEquipe'}
										<th>#</th>
										<th>{#Comp#}.</th>
										<th>{#Equipe#}</th>
										<th>{#Principal#}</th>
										<th>{#Secondaire#}</th>
										<th>Total</th>
									{elseif $AfficheStat == 'CJouees'}
										<th>#</th>
										<th>N°</th>
										<th>{#Nom#}</th>
										<th>{#Prenom#}</th>
										<th>Club</th>
										<th>{#Competition#}</th>
										<th>{#Nb_matchs#}</th>
									{elseif $AfficheStat == 'CJouees2'}
										<th>#</th>
										<th>N°</th>
										<th>{#Nom#}</th>
										<th>{#Prenom#}</th>
										<th>{#Equipe#}</th>
										<th>{#Competition#}</th>
										<th>{#Nb_matchs#}</th>
									{elseif $AfficheStat == 'CJouees3'}
										<th>#</th>
										<th>N°</th>
										<th>{#Nom#}</th>
										<th>{#Prenom#}</th>
										<th>{#Equipe#}</th>
										<th>{#Competition#}</th>
										<th>{#Irregularites#}</th>
									{elseif $AfficheStat == 'OfficielsJournees'}
										<th>{#Comp#}.</th>
										<th>Date</th>
										<th>{#Lieu#}</th>
										<th>{#RC#}</th>
										<th>{#R1#}</th>
										<th>{#Delegue#}</th>
										<th>{#Chef_arbitres#}</th>
									{elseif $AfficheStat == 'OfficielsMatchs'}
										<th>{#Comp#}. - {#Lieu#}</th>
										<th>Date</th>
										<th>{#Arb#}</th>
										<th>{#Lignes#}</th>
										<th>Table</th>
									{elseif $AfficheStat == 'ListeArbitres'}
										<th>#</th>
										<th>{#Arbitre#}</th>
										<th>Club</th>
										<th>{#Niveau#}</th>
										<th>{#Saison#}</th>
										<th>{#Livret#}</th>
									{/if}
								</tr>
							</thead>
						<tbody>
						{if $AfficheStat == 'Buteurs'}
							{section name=i loop=$arrayButeurs}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayButeurs[i].Competition}</td>
									<td>{$arrayButeurs[i].Numero}</td>
									<td>{$arrayButeurs[i].Nom}
										{if $profile <= 6}
												<a href="GestionAthlete.php?Athlete={$arrayButeurs[i].Licence}"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										{/if}</td>
									<td>{$arrayButeurs[i].Prenom}</td>
									<td>{$arrayButeurs[i].Sexe}</td>
									<td>{$arrayButeurs[i].Equipe}</td>
									<td>{$arrayButeurs[i].Buts}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'Attaque'}
							{section name=i loop=$arrayAttaque}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayAttaque[i].Competition}</td>
									<td>{$arrayAttaque[i].Equipe}</td>
									<td>{$arrayAttaque[i].Buts}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'Defense'}
							{section name=i loop=$arrayDefense}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayDefense[i].Competition}</td>
									<td>{$arrayDefense[i].Equipe}</td>
									<td>{$arrayDefense[i].Buts}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'Cartons'}
							{section name=i loop=$arrayCartons}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayCartons[i].Competition}</td>
									<td>{$arrayCartons[i].Numero}</td>
									<td>{$arrayCartons[i].Nom}
										{if $profile <= 6}
                                                                                    <a href="GestionAthlete.php?Athlete={$arrayCartons[i].Licence}"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										{/if}</td>
									<td>{$arrayCartons[i].Prenom}</td>
									<td>{$arrayCartons[i].Sexe}</td>
									<td>{$arrayCartons[i].Equipe}</td>
									<td>{$arrayCartons[i].Vert}</td>
									<td>{$arrayCartons[i].Jaune}</td>
									<td>{$arrayCartons[i].Rouge}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'CartonsEquipe'}
							{section name=i loop=$arrayCartonsEquipe}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayCartonsEquipe[i].Competition}</td>
									<td>{$arrayCartonsEquipe[i].Equipe}</td>
									<td>{$arrayCartonsEquipe[i].Vert}</td>
									<td>{$arrayCartonsEquipe[i].Jaune}</td>
									<td>{$arrayCartonsEquipe[i].Rouge}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'Fairplay'}
							{section name=i loop=$arrayFairplay}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayFairplay[i].Competition}</td>
									<td>{$arrayFairplay[i].Numero}</td>
									<td>{$arrayFairplay[i].Nom}
										{if $profile <= 6}
												<a href="GestionAthlete.php?Athlete={$arrayFairplay[i].Licence}"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										{/if}</td>
									<td>{$arrayFairplay[i].Prenom}</td>
									<td>{$arrayFairplay[i].Sexe}</td>
									<td>{$arrayFairplay[i].Equipe}</td>
									<td>{$arrayFairplay[i].Fairplay}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'FairplayEquipe'}
							{section name=i loop=$arrayFairplayEquipe}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayFairplayEquipe[i].Competition}</td>
									<td>{$arrayFairplayEquipe[i].Equipe}</td>
									<td>{$arrayFairplayEquipe[i].Fairplay}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'Arbitrage'}
							{section name=i loop=$arrayArbitrage}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayArbitrage[i].Competition}</td>
									<td>{$arrayArbitrage[i].Licence}</td>
									<td>{$arrayArbitrage[i].Nom}
										{if $profile <= 6}
												<a href="GestionAthlete.php?Athlete={$arrayArbitrage[i].Licence}"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										{/if}</td>
									<td>{$arrayArbitrage[i].Prenom}</td>
									<td>{$arrayArbitrage[i].Sexe}</td>
									<td>{$arrayArbitrage[i].Principal}</td>
									<td>{$arrayArbitrage[i].Secondaire}</td>
									<td>{$arrayArbitrage[i].Total}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'ArbitrageEquipe'}
							{section name=i loop=$arrayArbitrageEquipe}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayArbitrageEquipe[i].Competition}</td>
									<td>{$arrayArbitrageEquipe[i].Equipe}</td>
									<td>{$arrayArbitrageEquipe[i].Principal}</td>
									<td>{$arrayArbitrageEquipe[i].Secondaire}</td>
									<td>{$arrayArbitrageEquipe[i].Total}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'CJouees'}
							{section name=i loop=$arrayCJouees}
								<tr class='{cycle values="impair,pair"}{if $MatricTemp == $arrayCJouees[i].Matric}{if $arrayCJouees[i].Nb_matchs > 3} rouge{else} vert{/if}{/if}'  title="{$arrayCJouees[i].Nom_club}">
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayCJouees[i].Matric}</td>
									{assign var='MatricTemp' value=$arrayCJouees[i].Matric}
									<td>{$arrayCJouees[i].Nom}
										{if $profile <= 6}
												<a href="GestionAthlete.php?Athlete={$arrayCJouees[i].Matric}"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										{/if}</td>
									<td>{$arrayCJouees[i].Prenom}</td>
									<td>{$arrayCJouees[i].Numero_club}</td>
									<td>{$arrayCJouees[i].Competition}</td>
									<td>{$arrayCJouees[i].Nb_matchs}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'CJouees2'}
							{section name=i loop=$arrayCJouees2}
								<tr class='{cycle values="impair,pair"}{if $MatricTemp == $arrayCJouees2[i].Matric}{if $EquipeTemp == $arrayCJouees2[i].nomEquipe} vert{else} rouge{/if}{/if}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayCJouees2[i].Matric}</td>
									{assign var='MatricTemp' value=$arrayCJouees2[i].Matric}
									{assign var='EquipeTemp' value=$arrayCJouees2[i].nomEquipe}
									<td>{$arrayCJouees2[i].Nom}
										{if $profile <= 6}
												<a href="GestionAthlete.php?Athlete={$arrayCJouees2[i].Matric}"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										{/if}</td>
									<td>{$arrayCJouees2[i].Prenom}</td>
									<td>{$arrayCJouees2[i].nomEquipe}</td>
									<td>{$arrayCJouees2[i].Competition}</td>
									<td>{$arrayCJouees2[i].Nb_matchs}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'CJouees3'}
							{section name=i loop=$arrayCJouees3}
								<tr class='{cycle values="impair,pair"}{if $MatricTemp == $arrayCJouees3[i].Matric} vert{/if}'>
									<td>{$smarty.section.i.iteration}</td>
									<td>{$arrayCJouees3[i].Matric}</td>
									{assign var='MatricTemp' value=$arrayCJouees3[i].Matric}
									<td>{$arrayCJouees3[i].Nom}
										{if $profile <= 6}
												<a href="GestionAthlete.php?Athlete={$arrayCJouees3[i].Matric}"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										{/if}</td>
									<td>{$arrayCJouees3[i].Prenom}</td>
									<td>{$arrayCJouees3[i].nomEquipe}</td>
									<td>{$arrayCJouees3[i].Competition}</td>
									<td>{$arrayCJouees3[i].Irreg}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'OfficielsJournees'}
							{section name=i loop=$arrayOfficielsJournees}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$arrayOfficielsJournees[i].Code_competition}</td>
									<td>{$arrayOfficielsJournees[i].Date_debut|date_format:"%d/%m/%Y"}<br />{$arrayOfficielsJournees[i].Date_fin|date_format:"%d/%m/%Y"}</td>
									<td>{$arrayOfficielsJournees[i].Lieu} ({$arrayOfficielsJournees[i].Departement})</td>
									<td>{$arrayOfficielsJournees[i].Responsable_insc}</td>
									<td>{$arrayOfficielsJournees[i].Responsable_R1}</td>
									<td>{$arrayOfficielsJournees[i].Delegue}</td>
									<td>{$arrayOfficielsJournees[i].ChefArbitre}</td>
								</tr>
							{/section}
								<tr>
									<td colspan="5"><i>Nb journees : {$nbJournees}</i></td>
									<td colspan="3"><i>Nb journées avec officiels : {$nbOfficiels}</i></td>
								</tr>
						{elseif $AfficheStat == 'OfficielsMatchs'}
							{section name=i loop=$arrayOfficielsMatchs}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$arrayOfficielsMatchs[i].Code_competition} - {$arrayOfficielsMatchs[i].Lieu} ({$arrayOfficielsMatchs[i].Departement})</td>
									<td>{$arrayOfficielsMatchs[i].Date_match|date_format:"%d/%m/%Y"}<br />n°{$arrayOfficielsMatchs[i].Numero_ordre} - {$arrayOfficielsMatchs[i].Heure_match}
                                                                            {if $profile <= 6}
                                                                                <a href="FeuilleMatchMulti.php?listMatch={$arrayOfficielsMatchs[i].Id}" target="_blank"><img width="10" src="../img/b_plus.png" alt="Détails" title="{$arrayOfficielsMatchs[i].equipeA} / {$arrayOfficielsMatchs[i].equipeB}" /></a>
                                                                            {/if}
                                                                        </td>
									<td>{$arrayOfficielsMatchs[i].Arbitre_principal}<br />{$arrayOfficielsMatchs[i].Arbitre_secondaire}</td>
									<td>{$arrayOfficielsMatchs[i].Ligne1}<br />{$arrayOfficielsMatchs[i].Ligne2}</td>
									<td>Sec:{$arrayOfficielsMatchs[i].Secretaire}<br />Chr:{$arrayOfficielsMatchs[i].Chronometre}<br />TS:{$arrayOfficielsMatchs[i].Timeshoot}</td>
								</tr>
							{/section}
						{elseif $AfficheStat == 'ListeArbitres'}
							{section name=i loop=$arrayListeArbitres}
								<tr class='{cycle values="impair,pair"}'>
                                                                        <td>{$smarty.section.i.iteration}</td>
                                                                        <td class="cliquableNomEquipe"><a href="GestionAthlete.php?Athlete={$arrayListeArbitres[i].Matric}">{$arrayListeArbitres[i].Nom} {$arrayListeArbitres[i].Prenom} ({$arrayListeArbitres[i].Matric})</a></td>
									<td>{$arrayListeArbitres[i].Club}</td>
									<td>{$arrayListeArbitres[i].Arb} {$arrayListeArbitres[i].niveau}</td>
									<td>{$arrayListeArbitres[i].saison}</td>
									<td>{$arrayListeArbitres[i].Livret}</td>
								</tr>
							{/section}
						{/if}
					</tbody>
				</table>
			</div>
		</div>
		<div class='blocRight'>
			<table width=100%>
				<tr>
					<th class='titreForm' colspan=2>
						<label>{#Selection#}</label>
					</th>
				</tr>
				<tr>
					<td width=65>
						<label for="codeSaison">{#Saison#}:</label>
						<select name="codeSaison" onChange="document.formStats.submit()">
						{section name=i loop=$arraySaison}
							<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $codeSaison}selected{/if}>{$arraySaison[i].Code}</Option>
						{/section}
						</select>
					</td>
					<td>
						<label for="AfficheStat">{#Statistiques#}:</label>
						<select name="AfficheStat" onChange="document.formStats.submit()">
							<Option Value="Buteurs"{if $AfficheStat == 'Buteurs'} selected{/if}>{#Meilleur_buteur#}</Option>
							<Option Value="Attaque"{if $AfficheStat == 'Attaque'} selected{/if}>{#Meilleure_attaque#}</Option>
							<Option Value="Defense"{if $AfficheStat == 'Defense'} selected{/if}>{#Meilleure_defense#}</Option>
							<Option Value="Cartons"{if $AfficheStat == 'Cartons'} selected{/if}>{#Cartons#}</Option>
							<Option Value="CartonsEquipe"{if $AfficheStat == 'CartonsEquipe'} selected{/if}>{#Cartons_par_equipe#}</Option>
							<Option Value="Fairplay"{if $AfficheStat == 'Fairplay'} selected{/if}>{#Classement_disciplinaire_individuel#}</Option>
							<Option Value="FairplayEquipe"{if $AfficheStat == 'FairplayEquipe'} selected{/if}>{#Classement_disciplinaire_par_equipe#}</Option>
							<Option Value="Arbitrage"{if $AfficheStat == 'Arbitrage'} selected{/if}>{#Arbitrage#}</Option>
							<Option Value="ArbitrageEquipe"{if $AfficheStat == 'ArbitrageEquipe'} selected{/if}>{#Arbitrage_par_equipe#}</Option>
							{if $profile <= 6}
								<Option Value="CJouees"{if $AfficheStat == 'CJouees'} selected{/if}>{#Competitions_jouees_par_club#}</Option>
								<Option Value="CJouees2"{if $AfficheStat == 'CJouees2'} selected{/if}>{#Competitions_jouees_par_equipe#}</Option>
								<Option Value="CJouees3"{if $AfficheStat == 'CJouees3'} selected{/if}>{#Irregularites#}</Option>
								<Option Value="OfficielsJournees"{if $AfficheStat == 'OfficielsJournees'} selected{/if}>{#Officiels_des_journees#}</Option>
								<Option Value="OfficielsMatchs"{if $AfficheStat == 'OfficielsMatchs'} selected{/if}>{#Officiels_des_matchs#}</Option>
								<Option Value="ListeArbitres"{if $AfficheStat == 'ListeArbitres'} selected{/if}>{#Arbitres#}</Option>
							{/if}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan=2>
						<label for="Compets">{#Competitions#}:</label>
						<div STYLE="overflow-x:scroll; overflow-y: hidden; height:200px; width:240px"> 
							<select name="Compets[]" multiple size=12 style="width:350px">
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
							</select>
						</div>
						<label><i>({#Selection_multiple_avec_CTRL#})</i></label>
					</td>
				</tr>
				<tr>
					<td>
						<label for="nbLignes">{#Nb_lignes#} :</label>
						<input type="text" size="2" name="nbLignes" id="nbLignes" value="{$nbLignes|default:'30'}">
					</td>
					<td>
						<br>
						<input type="button" value="{#MAJ#}" onClick="submit()">
						<br>
						<br>
					</td>
				</tr>
				<tr>
					<th class='titreForm' colspan=2>
						<label>{#Statistiques_athlete#}</label>
					</th>
				</tr>
				<tr>
					<td colspan=2>
						<label>{#Chercher#}</label>
						<input type="text" name="choixJoueur" id="choixJoueur" size="30" placeholder="{#Nom#}, {#Prenom#}, {#Licence#}" />
						<br />
						<center><a href="GestionAthlete.php" id='rechercheAthlete'>{#Acces#}</a></center>
						<br />
						<center><a href="GestionAthlete.php">{#Recherche_avancee#}</a></center>
						<input type="hidden" name="Athlete" id="Athlete" value="{$Athlete}"/>
					</td>
				</tr>
			</table>
		</div>
	</form>
</div>	