 	 	&nbsp;(<a href="GestionCalendrier.php">{#Retour#}</a>)

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
					<div class='titrePage'>{#Classement#}</div>
					<label for="saisonTravail">{#Saison#} :</label>
					<select name="saisonTravail" id="saisonTravail" onChange="sessionSaison()">
						{section name=i loop=$arraySaison} 
							<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $sessionSaison}selected{/if}>{$arraySaison[i].Code}{if $arraySaison[i].Code eq $sessionSaison} ({#actuelle#}){/if}</Option>
						{/section}
					</select>
					<label for="codeCompet">{#Competition#} :</label>
					<select name="codeCompet" onChange="changeCompetition();">
                        {section name=i loop=$arrayCompetition}
                            {assign var='options' value=$arrayCompetition[i].options}
                            {assign var='label' value=$arrayCompetition[i].label}
                            <optgroup label="{$smarty.config.$label|default:$label}">
                                {section name=j loop=$options}
                                    {assign var='optionLabel' value=$options[j].Code}
                                    <Option Value="{$options[j].Code}" {$options[j].selected}>{$options[j].Code} - {$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>
                                {/section}
                            </optgroup>
                        {/section}
					</select>
					<br>
					<label for="orderCompet">{#Type_de_classement#} : </label>
					{if $profile <= 3 && $AuthModif == 'O'}
						<select name="orderCompet" onChange="changeOrderCompetition();">
							{section name=i loop=$arrayOrderCompetition}
                                {assign var='type' value=$arrayOrderCompetition[i][0]|cat:'_type'}
								<Option Value="{$arrayOrderCompetition[i][0]}" {$arrayOrderCompetition[i][2]}>{$smarty.config.$type}</Option>
									{if $arrayOrderCompetition[i][2]=='SELECTED'}
                                        {assign var='typeCompetition' value=$arrayOrderCompetition[i][1]}
                                        {assign var='type2' value=$type}
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
					<img height="20" class="cliquable" id="actuButton" title="{#Recharger#}" src="../img/glyphicons-82-refresh.png">
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
									<th>{#Classement#} type {$typeCompetition}</th>
									{if $typeCompetition=='Championnat'}
										<th>{#Pts#}</th>
									{/if}
									<th>{#J#}</th>
									<th>{#G#}</th>
									<th>{#N#}</th>
									<th>{#P#}</th>
									<th>{#F#}</th>
									<th>+</th>
									<th>-</th>
									<th>{#Diff#}</th>
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
						{if $typeCompetition == 'Championnat'}
                            {#Explication_classement_par_points#}
                            <br>
						{else}
                            <br>
							<table id='tableauJQ2' class='tableauJQ tableau'>
								<thead>
									<tr>
										<th colspan="12">{#Deroulement#}</th>
									</tr>
								</thead>
								<tbody>
								{assign var='idJournee' value='0'}

								{section name=i loop=$arrayEquipe_journee} 
									{if $arrayEquipe_journee[i].J != 0 && $arrayEquipe_journee[i].Type == 'C'}
										{if $arrayEquipe_journee[i].Id_journee != $idJournee}
											<tr class='head2'>
												<th colspan="3">{$arrayEquipe_journee[i].Phase} ({$arrayEquipe_journee[i].Lieu})</th>
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
                                    {if $arrayEquipe_journee[i].Type == 'E'}
                                        {if $arrayEquipe_journee[i].Id_journee != $idJournee}
                                            <tr class='head2'>
                                                <th colspan="12">{$arrayEquipe_journee[i].Phase} ({$arrayEquipe_journee[i].Lieu})</th>
                                            </tr>
										{/if}
                                        {assign var='idJournee' value=$arrayEquipe_journee[i].Id_journee}
                                        <tr height="17" class='{cycle values="impair,pair"}'>
                                            {if $arrayEquipe_journee[i].G > 0}
                                                <td colspan="4"><b>{#Vainqueur#}</b></td>
                                                <td colspan="8"><b>{$arrayEquipe_journee[i].Libelle}</b></td>
                                            {elseif $arrayEquipe_journee[i].J > 0}
                                                <td colspan="4"><i>{#Perdant#}</i></td>
                                                <td colspan="8"><i>{$arrayEquipe_journee[i].Libelle}</i></td>
                                            {else}    
                                                <td colspan="4"></td>
                                                <td colspan="8">{$arrayEquipe_journee[i].Libelle}</td>
                                            {/if}
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
									<th colspan="2">{#Classement_public#}</th>
									{if $typeCompetition=='Championnat'}
										<th>{#Pts#}</th>
									{/if}
                                    <th>{#J#}</th>
                                    <th>{#G#}</th>
                                    <th>{#N#}</th>
                                    <th>{#P#}</th>
                                    <th>{#F#}</th>
                                    <th>+</th>
                                    <th>-</th>
                                    <th>{#Diff#}</th>
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
                                            <td width="30">{$arrayEquipe_publi[i].Clt_publi}</td>
                                            <td width="200">{$arrayEquipe_publi[i].Libelle}</td>
                                            <td width="40">{$arrayEquipe_publi[i].Pts_publi/100}</td>
                                        {else}
                                            <td width="30">{$arrayEquipe_publi[i].CltNiveau_publi}</td>
                                            <td width="200">{$arrayEquipe_publi[i].Libelle}</td>
                                            {*<td width="40">{$arrayEquipe_publi[i].PtsNiveau}</td>*}
                                        {/if}

                                        <td width="30">{$arrayEquipe_publi[i].J_publi}</td>
                                        <td width="30">{$arrayEquipe_publi[i].G_publi}</td>
                                        <td width="30">{$arrayEquipe_publi[i].N_publi}</td>
                                        <td width="30">{$arrayEquipe_publi[i].P_publi}</td>
                                        <td width="30">{$arrayEquipe_publi[i].F_publi}</td>
                                        <td width="40">{$arrayEquipe_publi[i].Plus_publi}</td>
                                        <td width="40">{$arrayEquipe_publi[i].Moins_publi}</td>
                                        <td width="40">{$arrayEquipe_publi[i].Diff_publi}</td>

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
										<th colspan="11">{#Deroulement#}</th>
									</tr>
								</thead>
								<tbody>
								{assign var='idJournee' value='0'}

								{section name=i loop=$arrayEquipe_journee_publi} 
									{if $arrayEquipe_journee_publi[i].J_publi != 0 && $arrayEquipe_journee_publi[i].Type == 'C'}
										{if $arrayEquipe_journee_publi[i].Id_journee != $idJournee}
											<tr class='head2Public'>
												<th colspan="3">{$arrayEquipe_journee_publi[i].Phase} ({$arrayEquipe_journee_publi[i].Lieu})</th>
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
											<td>
												{*{if $profile <= 4 && $AuthModif == 'O'}
													<input type="checkbox" name="checkClassement" value="{$arrayEquipe_journee_publi[i].Id}" id="checkClassement{$smarty.section.i.iteration}" />
												{/if}*}
											</td>
											<td width="30">{$arrayEquipe_journee_publi[i].Clt_publi}</td>
											<td width="200">{$arrayEquipe_journee_publi[i].Libelle}</td>
											<td width="40">{$arrayEquipe_journee_publi[i].Pts_publi/100}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].J_publi}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].G_publi}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].N_publi}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].P_publi}</td>
											<td width="30">{$arrayEquipe_journee_publi[i].F_publi}</td>
											<td width="40">{$arrayEquipe_journee_publi[i].Plus_publi}</td>
											<td width="40">{$arrayEquipe_journee_publi[i].Moins_publi}</td>
											<td width="40">{$arrayEquipe_journee_publi[i].Diff_publi}</td>
										</tr>
									{/if}
                                    {if $arrayEquipe_journee_publi[i].Type == 'E'}
                                        {if $arrayEquipe_journee_publi[i].Id_journee != $idJournee}
                                            <tr class='head2Public'>
                                                <th colspan="12">{$arrayEquipe_journee_publi[i].Phase} ({$arrayEquipe_journee_publi[i].Lieu})</th>
                                            </tr>
                                        {/if}
                                        {assign var='idJournee' value=$arrayEquipe_journee_publi[i].Id_journee}
                                        <tr height="17" class='{cycle values="impair,pair"}'>
                                            {if $arrayEquipe_journee_publi[i].G_publi > 0}
                                                <td colspan="4"><b>{#Vainqueur#}</b></td>
                                                <td colspan="8"><b>{$arrayEquipe_journee_publi[i].Libelle}</b></td>
                                            {elseif $arrayEquipe_journee_publi[i].J_publi > 0}
                                                <td colspan="4"><i>{#Perdant#}</i></td>
                                                <td colspan="8"><i>{$arrayEquipe_journee_publi[i].Libelle}</i></td>
                                            {else}
                                                <td colspan="4"></td>
                                                <td colspan="8">{$arrayEquipe_journee_publi[i].Libelle}</td>

                                            {/if}
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
								<label>{#Statut#}</label>
							</th>
						</tr>
						<tr>
							<td align='center' colspan=4>
								{if $profile <= 3 && $AuthModif == 'O'}
                                    <span class="statutCompet statutCompet{$compet.Statut}" data-id="{$compet.Code}" title="{#Detail_statut#}">{$compet.Statut}</span>
                                {else}
                                    <span class="statutCompet{$compet.Statut}">{$compet.Statut}</span>
                                {/if}
							</td>
						</tr>
						<tr>
							<th class='titreForm' colspan=4>
								<label>Type : {$smarty.config.$type2}</label>
							</th>
						</tr>
						<tr>
							<td align='center' colspan=4>
								{if $Date_calcul == '00/00/00 &agrave; 00h00'}{#Classement_non_calcule#}{else}
								{#Calcul#}: {$Date_calcul}<br>&nbsp;({#par#} {$UserName_calcul}){/if}<br>
								<hr>
							</td>
						</tr>
						{if ($profile <= 6 or $profile == 9) && $AuthModif == 'O'}
							<tr>
								<td align='center' width=20><input type="checkbox" name="allMatchs" id="allMatchs" value="ok"{if $Mode_calcul == 'tous'} checked{/if}></td>
								<td colspan=3>{#Inclure_les_matchs_non_verrouilles#}</td>
							</tr>
							<tr>
								<td colspan=4>
									<input class="bigbutton" type="button" onclick="computeClt();" name="Calculer" value="{#Recalculer#}">
								</td>
							</tr>
						{/if}
					</table>
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=4>
								<label>{#Classement_public#}</label>
							</th>
						</tr>
						<tr>
							
							<td colspan=4 align='center' class='color{if $Date_publication_calcul eq $Date_calcul}O{else}N{/if}'>
								{if $Code_uti_publication != ''}
									{if $Date_publication_calcul == '00/00/00 &agrave;  00h00'}
                                        {#Classement_manuel#}
                                    {else}
                                        {#Calcul#}: {$Date_publication_calcul}
                                    {/if}
                                    <br>
									{#Publication#}: {$Date_publication}<br>
									{#par#} {$UserName_publication}
									{if $Date_publication_calcul != $Date_calcul}
                                        <br><br>
                                        {#Attention_classement_different#}
									{/if}
								{else}
									{#Classement_non_publie#} !
								{/if}
							</td>
						</tr>
						{if ($profile <= 4) && $AuthModif == 'O'}
							<tr>
								<td colspan=4 align='center'>
									<input class="bigbutton" type="button" onclick="publicationClt();" name="Publier" value="{#Publier_nouveau_classement#}">
								</td>
							</tr>
						{/if}
                        
                    </table>
                        
                    <table width="100%">
                        {if $typeCompetition=='Championnat'}
                            <tr>
                                <td colspan=2 align='left' title="{#Provisoire#}"><b>Admin</b></td>
                                <td colspan=2 align='right'><b>Public</b></td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltChpt.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Classement_general#} - admin" /></a>
                                </td>
                                <td colspan=2 align='center'>{#Classement_general#}</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltChpt.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Classement_general#} - public" /></a>
                                {/if}
                                </td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltChptDetail.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Detail_par_equipe#} - admin" /></a>
                                </td>
                                <td colspan=2 align='center'>{#Detail_par_equipe#}</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltChptDetail.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Detail_par_equipe#} - public" /></a>
                                {/if}
                                </td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveauJournee.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Detail_par_journee#} - admin" /></a>
                                </td>
                                <td colspan=2 align='center'>{#Detail_par_journee#}</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveauJournee.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Detail_par_journee#} - public" /></a>
                                {/if}
                                </td>
                            </tr>
                        {else}
                            <tr>
                                <td colspan=2 align='left' title="{#Provisoire#}"><b>Admin</b></td>
                                <td colspan=2 align='right'><b>Public</b></td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveau.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Classement_general#} - admin" /></a>
                                </td>
                                <td colspan=2 align='center'>{#Classement_general#}</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveau.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Classement_general#} - public" /></a>
                                {/if}
                                </td>
                            </tr>
                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveauPhase.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Deroulement#} - admin" /></a>
                                </td>
                                <td colspan=2 align='center'>{#Deroulement#}</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveauPhase.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Deroulement#} - public" /></a>
                                {/if}
                                </td>
                            </tr>
{*                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveauNiveau.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par niveau admin" title="D&eacute;tail par niveau admin" /></a>
                                </td>
                                <td colspan=2 align='center'>{#Detail_par_niveau#}</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveauNiveau.php" Target="_blank"><img height="30" src="../img/pdf.png" alt="D&eacute;tail par niveau public" title="D&eacute;tail par niveau public" /></a>
                                {/if}
                                </td>
                            </tr>
*}                            <tr>
                                <td align='left'>
                                    <a href="FeuilleCltNiveauDetail.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Detail_par_equipe#} - admin" /></a>
                                </td>
                                <td colspan=2 align='center'>{#Detail_par_equipe#}</td>
                                <td align='right'>
                                {if $Code_uti_publication != ''}
                                    <a href="../PdfCltNiveauDetail.php" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Detail_par_equipe#} - public" /></a>
                                {/if}
                                </td>
                            </tr>
                        {/if}
						<tr>
							<td colspan=4 align='center'><hr></td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleListeMatchs.php?Compet={$codeCompet}" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Matchs#} - admin" /></a>
							</td>
							<td colspan=2 align='center'>{#Matchs#}</td>
							<td align='right'>
								<a href="../PdfListeMatchs.php?Compet={$codeCompet}" Target="_blank"><img height="30" src="../img/pdf.png" title="{#Matchs#} - public" /></a>
							</td>
						</tr>
						{if ($profile <= 3) && $AuthModif == 'O'}
							<tr>
								<td align='center' colspan="4">
									<br>
									<input type="button" onclick="depublicationClt();" name="D&eacute;-publier" value="-{#Supprimer_classement_public#}-">
								</td>
							</tr>
						{/if}
                        {if $profile <= 6 && $AuthModif == 'O'}
							<tr>
								<td colspan=4>
									<br>
									<input type="button" onclick="initClt();" name="Initialiser" value="{#Classement_initial#}...">
								</td>
							</tr>
						{/if}
                    </table>
                            
					{if $profile <= 4 && $AuthModif == 'O'}
						<br>
						<table width="100%">
							<tr>
								<th class='titreForm'>
									<label>{#Affectation#} - {#Promotion#} - {#Relegation#}</label>
								</th>
							</tr>
							<tr>
								<td>
									<label for="codeSaisonTransfert">{#Affecter_vers_saison#} :</label>
									<select name="codeSaisonTransfert" id="codeSaisonTransfert" onchange="changeSaisonTransfert()">
										{section name=i loop=$arraySaisonTransfert} 
											<Option Value="{$arraySaisonTransfert[i].Code}" {if $arraySaisonTransfert[i].Code == $codeSaisonTransfert}selected{/if}>{$arraySaisonTransfert[i].Code}</Option>
										{/section}
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<label for="codeCompetTransfert">{#Affecter_vers_competition#} :</label>
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
									<input type="button" onclick="transfert();" name="Transfert" value="{#Affecter_equipes_cochees#}">
								</td>
							</tr>
						</table>
					{/if}
				</div>
						
			</form>			
					
		</div>	  	   
