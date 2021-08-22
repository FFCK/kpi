		<div class="main">
			<form method="POST" action="GestionJournee.php" name="formJournee" id="formJournee" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value=''/>
				<input type='hidden' name='idEquipeA' id='idEquipeA' Value=''/>
				<input type='hidden' name='idEquipeB' id='idEquipeB' Value=''/>
				<input type='hidden' name='Pub' id='Pub' Value=''/>
				<input type='hidden' name='Verrou' id='Verrou' Value=''/>
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='kp_match'/>
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = '/>
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}'/>
				
				<div class='titrePage'>{#Liste_des_Matchs#}</div>
                <table id="formFiltres">
                    <tr class='filtres cadregris'>
                        <td align="center">
                            <label for="evenement">{#Filtre_evenement#}</label>
                            <br>
                            <select name="evenement" id="evenement" onChange="submit();">
                                {section name=i loop=$arrayEvenement} 
                                    {assign var="evt_libelle" value=$arrayEvenement[i].Libelle}
                                    <Option Value="{$arrayEvenement[i].Id}" {$arrayEvenement[i].Selection}>{$smarty.config.$evt_libelle|default:$evt_libelle}</Option>
                                {/section}
                            </select>
                        </td>
                        <td align="center">
                            <label for="comboCompet">{#Filtre_competition#}</label>
                            {if $profile <= 6 && $AuthModif == 'O'}
                                <a href="#" id="InitTitulaireCompet" title="{#InitTitulaireCompet#}"><img height="22" src="../img/b_update.png" ></a>
                            {/if}
                            <br>
                            <select id="comboCompet" name="comboCompet" onChange="changeCompet();" tabindex="1">
                                {section name=i loop=$arrayCompetition}
                                    {assign var='options' value=$arrayCompetition[i].options}
                                    {assign var='label' value=$arrayCompetition[i].label}
                                    <optgroup label="{$smarty.config.$label|default:$label}">
                                        {section name=j loop=$options}
                                            {assign var='optionLabel' value=$options[j].Code}
                                            {if $options[j].Code == '*'}
                                                <Option Value="{$options[j].Code}" {$options[j].selected}>{$options[j].Code} - {#Toutes_les_competitions_de_l_evenement#}</Option>
                                            {else}
                                                <Option Value="{$options[j].Code}" {$options[j].selected}>{$options[j].Code} - {$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>
                                            {/if}
                                        {/section}
                                    </optgroup>
                                {/section}
                            </select>
                        </td>
                        <td align="center">
                            <label for="comboJournee2">{#Filtre_journee_phase_poule#}</label>
                            <br>
                            <select id="comboJournee2" name="comboJournee2" onChange="changeCompet();" tabindex="2">
                                <Option Value="*" Selected>---{#Tous#}---</Option>
                                {section name=i loop=$arrayJourneesAutoriseesFiltre}
                                    {if $idSelJournee eq $arrayJourneesAutoriseesFiltre[i].Id}
                                        {if $arrayJourneesAutoriseesFiltre[i].Code_typeclt == 'CP'}
                                            <Option Value="{$arrayJourneesAutoriseesFiltre[i].Id}" Selected>{$arrayJourneesAutoriseesFiltre[i].Code_competition} {$arrayJourneesAutoriseesFiltre[i].Id|string_format:"[%s]"} - {$arrayJourneesAutoriseesFiltre[i].Phase|string_format:"%s"}{$arrayJourneesAutoriseesFiltre[i].Niveau|string_format:"(%s)"}</Option>
                                        {else}
                                            <Option Value="{$arrayJourneesAutoriseesFiltre[i].Id}" Selected>{$arrayJourneesAutoriseesFiltre[i].Code_competition} {$arrayJourneesAutoriseesFiltre[i].Id|string_format:"[%s]"} - {$arrayJourneesAutoriseesFiltre[i].Date_debut|string_format:" le %s "}{$arrayJourneesAutoriseesFiltre[i].Lieu|string_format:"à %s"}</Option>
                                        {/if}
                                    {else}
                                        {if $arrayJourneesAutoriseesFiltre[i].Code_typeclt == 'CP'}
                                            <Option Value="{$arrayJourneesAutoriseesFiltre[i].Id}">{$arrayJourneesAutoriseesFiltre[i].Code_competition} {$arrayJourneesAutoriseesFiltre[i].Id|string_format:"[%s]"} - {$arrayJourneesAutoriseesFiltre[i].Phase|string_format:"%s"}{$arrayJourneesAutoriseesFiltre[i].Niveau|string_format:"(%s)"}</Option>
                                        {else}
                                            <Option Value="{$arrayJourneesAutoriseesFiltre[i].Id}">{$arrayJourneesAutoriseesFiltre[i].Code_competition} {$arrayJourneesAutoriseesFiltre[i].Id|string_format:"[%s]"} - {$arrayJourneesAutoriseesFiltre[i].Date_debut|string_format:" le %s "}{$arrayJourneesAutoriseesFiltre[i].Lieu|string_format:"à %s"}</Option>
                                        {/if}
                                    {/if}
                                {/section}
                            </select>
                        </td>
                        <td align="center">
                            <label for="filtreMois">{#Filtre_Date_Terrain#}</label>
                            <br>
                            <select name="filtreJour" id="filtreJour" onChange="submit();">
                                    <Option Value="" {if $filtreJour == ''}selected{/if}>---{#Tous#}---</Option>
                                 {foreach from=$listeJours key=k item=v}
                                    <Option Value="{$k}" {if $filtreJour == $k}selected{/if}>{$v}</Option>
                                 {/foreach}
                            </select>
                            <select name="filtreTerrain" id="filtreTerrain" onChange="submit();">
                                    <Option Value="" {if $filtreTerrain == ''}selected{/if}>---{#Tous#}---</Option>
                                    <Option Value="1" {if $filtreTerrain == '1'}selected{/if}>{#Terr#}. 1</Option>
                                    <Option Value="2" {if $filtreTerrain == '2'}selected{/if}>{#Terr#}. 2</Option>
                                    <Option Value="3" {if $filtreTerrain == '3'}selected{/if}>{#Terr#}. 3</Option>
                                    <Option Value="4" {if $filtreTerrain == '4'}selected{/if}>{#Terr#}. 4</Option>
                                    <Option Value="5" {if $filtreTerrain == '5'}selected{/if}>{#Terr#}. 5</Option>
                                    <Option Value="6" {if $filtreTerrain == '6'}selected{/if}>{#Terr#}. 6</Option>
                                    <Option Value="7" {if $filtreTerrain == '7'}selected{/if}>{#Terr#}. 7</Option>
                                    <Option Value="8" {if $filtreTerrain == '8'}selected{/if}>{#Terr#}. 8</Option>
                            </select>
                        </td>
                        <td align="center">
                            <label for="filtreMois">{#Ordre_tri#}</label>
                            <br>
                            <select name="orderMatchs" onChange="ChangeOrderMatchs('{$idSelJournee}');">
                            {section name=i loop=$arrayOrderMatchs}
                                {assign var='currentOrderMatch' value=$arrayOrderMatchs[i].Value}
                                {if $orderMatchs eq $arrayOrderMatchs[i].Key}
                                    <Option Value="{$arrayOrderMatchs[i].Key}" Selected>{$smarty.config.$currentOrderMatch}</Option>
                                {else}
                                    <Option Value="{$arrayOrderMatchs[i].Key}">{$smarty.config.$currentOrderMatch}</Option>
                                {/if}
                            {/section}
                            </select>
                        </td>
                    </tr>
                </table>
                {if $profile <= 6 && $AuthModif == 'O'}
				<div class='blocTop'>
					<table id="formMatch">
						<tr class="hideTr">
							<td align="left" title="{#Intervale_matchs_title#}">
								<label for="Intervalle_match">{#Intervale_matchs#}</label>
								<br>
								<input type="text" size="1" name="Intervalle_match" value="{$Intervalle_match}">min.
							</td>
							<td>
								Type :
								<img id="typeMatch1" src="../img/type{$Type|default:'C'}.png" {if $Type == "E"}alt="{#Elimination#}" title="{#Match_eliminatoire#}"{else}alt="{#Classement#}" title="{#Match_de_classement#}"{/if} height="23">
								<input type="hidden" name="Type" id="Type" value="{$Type|default:'C'}" />
							</td>
							<td align="left">
								<table>
									<tr>
										<td>
											<label for="Libelle">{#Intitule_codage#}</label> <img src="../img/information.gif" width="14" title="<img src='../img/codage.png' width='250'>">
											<br>
											<input type="text" size="18" name="Libelle" placeholder="{#Code_Exemple#}" value="{$Libelle}" maxlength=30" tabindex="7"/>
										</td>
										<td>
											<label for="Num_match">{#Match#} N°</label>
											<br>
											<input type="text" size="3" name="Num_match" id="Num_match" value="{$Num_match}" tabindex="5"/>
										</td>
									</tr>
								</table>
							</td>
							<td align="left">
								<label for="equipeA">{#Equipe#} A</label>
								<a href="#" id="InitTitulaireEquipeA" title="{#InitTitulaireEquipe#}"><img height="22" src="../img/b_update.png" ></a>
								<br>
								<select name="equipeA" id="equipeA" onChange="changeEquipeA();" tabindex="8">
									<Option Value="-1">---</Option>
									{section name=i loop=$arrayEquipeA}
										<Option Value="{$arrayEquipeA[i].Id}" {$arrayEquipeA[i].Selection}>{$arrayEquipeA[i].Libelle}{if $arrayEquipeA[i].Poule != ''}{$arrayEquipeA[i].Poule|string_format:" (%s)"|default:""}{/if}</Option>
									{/section}
								</select>
								&nbsp;<label for="coeffA">Coef.</label>
								<input size="1" type="text" name="coeffA" value="{$coeffA|default:'1'}" tabindex="9" />
							</td>
							<td align="left">
								<label for="arbitre1">{#Arbitre#}</label>
								<input type="text" size="30" name="arbitre1" id="arbitre1" placeholder="{#ref_placeholder#}" value="{$arbitre1}" tabindex="12"/>
								<input type="text" size="5" name="arbitre1_matric" readonly id="arbitre1_matric" value="{$arbitre1_matric}"/>
								<br />
								<label for="comboarbitre1">{#Principal#}</label>
								<select class="combolong" name="comboarbitre1" onChange="arbitre1_matric.value=this.options[this.selectedIndex].value; arbitre1.value=this.options[this.selectedIndex].text;" tabindex="13">
									<Option Value="-1"></Option>
									{section name=i loop=$arrayArbitre}
										<Option Value="{$arrayArbitre[i].Matric}">{$arrayArbitre[i].Identite}</Option>
									{/section}
								</select>
								<label for="comboarbitre1b">{#Equipe#}</label>
								<select class="combolong" name="comboarbitre1b" id="comboarbitre1b" tabindex="13">
									<Option Value="-1"></Option>
									{section name=i loop=$arrayArbitreEquipes}
										<Option Value="{$arrayArbitreEquipes[i].Matric}">{$arrayArbitreEquipes[i].Identite}</Option>
									{/section}
								</select>
                                    
{*								<a href="#"  id='rechercheArbitre1'><img height="16" src="../img/glyphicons-28-search.png" alt="Recherche Licencié" title="Recherche Licencié" align=absmiddle /></a>*}
							</td>
						</tr>
						<tr class="hideTr">
							<td align="center" colspan="2">
								<label for="comboJournee">{#Journee_Phase_Poule_du_match#}</label>
								<a href="#" id="InitTitulaireJournee" title="{#InitTitulaireJournee#}"><img height="22" src="../img/b_update.png" ></a>
								<br>
								<select id="comboJournee" name="comboJournee" tabindex="2">
									<option Value="*" Selected>--- {#Selectionnez#} --- ({#OBLIGATOIRE#})</Option>
									{section name=i loop=$arrayJourneesAutorisees}
										{if $idCurrentJournee eq $arrayJourneesAutorisees[i].Id}
											{if $arrayJourneesAutorisees[i].Code_typeclt == 'CP'}
												<option value="{$arrayJournees[i].Id}" data-type="{$arrayJournees[i].Type}" data-phase="{$arrayJourneesAutorisees[i].Phase|string_format:'%s'}" Selected>{$arrayJourneesAutorisees[i].Code_competition} - {$arrayJourneesAutorisees[i].Phase|string_format:"%s"} {$arrayJourneesAutorisees[i].Niveau|string_format:"(%s)"}</option>
											{else}
												<option value="{$arrayJournees[i].Id}" data-type="{$arrayJournees[i].Type}" data-phase="{$arrayJourneesAutorisees[i].Phase|string_format:'%s'}" Selected>{$arrayJourneesAutorisees[i].Code_competition} - {$arrayJourneesAutorisees[i].Date_debut|string_format:"%s"} {$arrayJourneesAutorisees[i].Lieu|string_format:"à %s"}</option>
											{/if}
										{else}
											{if $arrayJourneesAutorisees[i].Code_typeclt == 'CP'}
												<option value="{$arrayJournees[i].Id}" data-type="{$arrayJournees[i].Type}" data-phase="{$arrayJourneesAutorisees[i].Phase|string_format:'%s'}">{$arrayJourneesAutorisees[i].Code_competition} - {$arrayJourneesAutorisees[i].Phase|string_format:"%s"} {$arrayJourneesAutorisees[i].Niveau|string_format:"(%s)"}</option>
											{else}
												<option value="{$arrayJournees[i].Id}" data-type="{$arrayJournees[i].Type}" data-phase="{$arrayJourneesAutorisees[i].Phase|string_format:'%s'}">{$arrayJourneesAutorisees[i].Code_competition} - {$arrayJourneesAutorisees[i].Date_debut|string_format:"%s"} {$arrayJourneesAutorisees[i].Lieu|string_format:"à %s"}</option>
											{/if}
										{/if}
									{/section}
								</select>
							</td>
							<td>
								<table>
									<tr>
										<td>
											<label for="Date_match">Date</label>
											<br>
											<input type="text" size="10" class='date' name="Date_match" value="{$Date_match}" tabindex="3" onfocus="displayCalendar(document.forms[0].Date_match,{if $lang=='en'}'yyyy-mm-dd'{else}'dd/mm/yyyy'{/if},this)" >
										</td>
										<td>
											<label for="Heure_match">{#Heure#}</label>
											<br>
											<input type="text" size="5" class='champsHeure' name="Heure_match" value="{$Heure_match}" tabindex="4"/>
										</td>
										<td>
											<label for="Terrain">{#Terrain#}</label>
											<br>
											<input type="text" size="3" name="Terrain" value="{$Terrain}" maxlength=12 tabindex="6"/>
										</td>
									</tr>
								</table>
							</td>
							<td>
								<label for="equipeB">{#Equipe#} B</label>
								<a href="#" id="InitTitulaireEquipeB" title="{#InitTitulaireEquipe#}"><img height="22" src="../img/b_update.png" ></a>
								<br>
								<select name="equipeB" id="equipeB" onChange="changeEquipeB();" tabindex="10">
									<Option Value="-1">---</Option>
									{section name=i loop=$arrayEquipeB}
										<Option Value="{$arrayEquipeB[i].Id}" {$arrayEquipeB[i].Selection}>{$arrayEquipeB[i].Libelle}{if $arrayEquipeB[i].Poule != ''}{$arrayEquipeB[i].Poule|string_format:" (%s)"|default:""}{/if}</Option>
									{/section}
								</select>
								<label for="coeffB">Coef.</label>
								<input size="1" type="text" name="coeffB" value="{$coeffB|default:'1'}" tabindex="11" />
							</td>
							<td>
								<label for="arbitre2">{#Arbitre#}</label>
								<input type="text" size="30" name="arbitre2" id="arbitre2" placeholder="{#ref_placeholder#}" value="{$arbitre2}" tabindex="14"/>
								<input type="text" size="5" name="arbitre2_matric" readonly id="arbitre2_matric" value="{$arbitre2_matric}"/>
								<br />
								<label for="comboarbitre2">{#Secondaire#}</label>
								<select class="combolong" name="comboarbitre2" onChange="arbitre2_matric.value=this.options[this.selectedIndex].value; arbitre2.value=this.options[this.selectedIndex].text;" tabindex="15">
									<Option Value="-1"></Option>
									{section name=i loop=$arrayArbitre}
										<Option Value="{$arrayArbitre[i].Matric}">{$arrayArbitre[i].Identite}</Option>
									{/section}
								</select>
								<label for="comboarbitre2b">{#Equipe#}</label>
								<select class="combolong" name="comboarbitre2b" id="comboarbitre2b" tabindex="13">
									<Option Value="-1"></Option>
									{section name=i loop=$arrayArbitreEquipes}
										<Option Value="{$arrayArbitreEquipes[i].Matric}">{$arrayArbitreEquipes[i].Identite}</Option>
									{/section}
								</select>
{*								<a href="#" id='rechercheArbitre2'><img height="16" src="../img/glyphicons-28-search.png" alt="Recherche Licencié" title="Recherche Licencié" align=absmiddle /></a>
*}							</td>
						</tr>
						<tr class="hideTr">
							<td id='clickup' style="color:#555555" colspan="2">
								<i><u>{#Masquer_le_formulaire#}</u></i>
							</td>
							<td align="center" colspan=2>
								<input type="button" onclick="Add();" id="addMatch" name="addMatch" value="{#Ajouter#}" tabindex="16">
								<input type="button" {if $idMatch eq '-1'} disabled {/if} onclick="Update();" id="updateMatch" name="updateMatch" value="{#Modifier#}" tabindex="17">
								<input type="button" onclick="Raz();" id="razMatch" name="razMatch" value="{#Annuler#}" tabindex="18">
							</td>
							<td align="right">
								<a href="GestionEquipeJoueur.php?idEquipe=1"><i>{#Pool_arbitres#}...</i></a>
							</td>
						</tr>
						<tr id='clickdown'>
							<td colspan="6" align="left" style="color:#555555"><i><u>{#Afficher_le_formulaire#}</u></i></td>
						</tr>
					</table>
				</div>
                {/if}
				<div class='blocMiddle'>
					<table width=100%>
						<tr>
							<td width=480>
						       	<fieldset>
									<label>{#Selection#}:</label>
									&nbsp;
									<a href="#" {$TropDeMatchs} onclick="setCheckboxes('formJournee', 'checkMatch', true);return false;"><img height="22" src="../img/glyphicons-155-more-checked.png" title="{#Tous#}" /></a>
									<a href="#" {$TropDeMatchs} onclick="setCheckboxes('formJournee', 'checkMatch', false);return false;"><img height="22" src="../img/glyphicons-155-more-windows.png" title="{#Aucun#}" /></a>
									{if $profile <= 6 && $AuthModif == 'O'}
										<a href="#" {$TropDeMatchs} onclick="RemoveCheckboxes('formJournee', 'checkMatch')" title="{#Supprimer#} {$TropDeMatchsMsg}"><img height="25" src="../img/glyphicons-17-bin.png" /></a>
										<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');publiMultiMatchs();" title="{#Publier#} {$TropDeMatchsMsg}"><img height="25" src="../img/oeil2.gif" /></a>
										{if $profile <= 4 && $AuthModif == 'O'}
											<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');verrouPubliMultiMatchs();" title="{#Verrouiller_Publier#} {$TropDeMatchsMsg}"><img height="25" src="../img/oeilverrou2.gif" /></a>
											<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');verrouMultiMatchs();" title="{#Verrouiller#} {$TropDeMatchsMsg}"><img height="25" src="../img/verrou2.gif" /></a>
										{/if}
										<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');affectMultiMatchs();" title="{#Affectation_auto#} {$TropDeMatchsMsg}"><img height="25" src="../img/AffectAuto.gif" /></a>
										<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');annulMultiMatchs();" title="{#Annuler_Affectation_auto#} {$TropDeMatchsMsg}"><img height="25" src="../img/AnnulAuto.gif" /></a>
										<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch');changeMultiMatchs();" title="{#Changer_de_poule#} {$TropDeMatchsMsg}"><img height="25" src="../img/Chang.gif" border="0"></a>
                                    {/if}
									<a href="#" {$TropDeMatchs} onclick="SelectedCheckboxes('formJournee', 'checkMatch'); this.href='FeuilleMatchMulti.php?listMatch='+document.formJournee.ParamCmd.value;" Target="_blank" title="{#Feuilles_marque#} {$TropDeMatchsMsg}"><img height="25" src="../img/pdf2.png" /></a>
                                    {if $profile == 1 && $AuthModif == 'O'}
                                        <a id="numMultiMatchsBtn" href="#" {$TropDeMatchs} onclick="numMultiMatchs();" title="{#Renumeroter_les_matchs#} {$TropDeMatchsMsg}"><img height="25" src="../img/numMatchs.png" border="0"></a>
                                    {/if}
								</fieldset>
							</td>
							<td width=520>
						       	<fieldset>
									<label>{#Tous_les_matchs#}:</label>
									&nbsp;
									<a href="FeuilleListeMatchs.php" {$TropDeMatchs} Target="_blank" title="Liste des Matchs {$TropDeMatchsMsg}"><img height="25" src="../img/ListeFR.gif" /></a>
									&nbsp;
									<a href="FeuilleListeMatchsEN.php" {$TropDeMatchs} Target="_blank" title="Game list (EN) {$TropDeMatchsMsg}"><img height="25" src="../img/ListeEN.gif" /></a>
									&nbsp;
									<a href="FeuilleMatchMulti.php?listMatch={$listMatch}" {$TropDeMatchs} Target="_blank" title="{#Feuilles_marque#} {$TropDeMatchsMsg}"><img height="25" src="../img/pdf2.png" /></a>
									&nbsp;
									<a href="tableau_tbs.php" title="Export (ODS)"><img height="25" src="../img/ods.png" /></a>
									&nbsp;
									<a href="../PdfListeMatchs.php" {$TropDeMatchs} Target="_blank" title="Liste publique des Matchs {$TropDeMatchsMsg}"><img height="25" src="../img/ListeFR.gif" /></a>
									&nbsp;
									<a href="../PdfListeMatchsEN.php" {$TropDeMatchs} Target="_blank" title="Public Game list (EN) {$TropDeMatchsMsg}"><img height="25" src="../img/ListeEN.gif" /></a>
									{if $profile <= 2}
                                        &nbsp;
                                        <a href="../PdfListeMatchs4Terrains.php" {$TropDeMatchs} Target="_blank" title="All games in one table (EN) {$TropDeMatchsMsg}"><img height="25" src="../img/Liste.gif" /></a>
                                        &nbsp;
                                        <a href="../fpmatchs.php" {$TropDeMatchs} Target="_blank" title="FullPage {$TropDeMatchsMsg}"><img height="25" src="../img/Liste.gif" /></a>
                                    {/if}
                                </fieldset>
							</td>
							<td>
								&nbsp;&nbsp;
								<span id='reachspan'><i>{#Surligner#}:</i></span><input type=text name='reach' id='reach' size='5'>
                                {if $profile <= 2}
                                    <span id='reachspan2'><i>{#Surligner#}:</i></span><input type=text name='reach2' id='reach2' size='5'>
                                {/if}
							</td>
						</tr>
					</table>
				</div>
				<div class='blocBottom'>
					<div class='blocTable' id='blocMatchs'>
						<table class='tableau' id='tableMatchs'>
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th><img height="25" src="../img/oeil2.gif" title="{#Publier#} ?" border="0"></th>
									<th>{#Num#}</th>
									<th width=45>&nbsp;</th>
									<th>{#Heure#}</th>
									<th>Cat.</th>
									{if $PhaseLibelle == 1}
										<th>{#Phase#}</th>
										<th>{#Code#}</th>
									{else}
										<th>{#Code#}</th>
										<th>{#Lieu#}</th>
									{/if}
									<th>{#Type#}</th>
									<th>{#Terr#}</th>
									<th>{#Equipe#} A</th>
									<th>{#Sc#} A</th>
									<th><img height="25" src="../img/verrou2.gif" title="{#Verrouiller#} ?" border="0"></th>
									<th>{#Sc#} B</th>
									<th>{#Equipe#} B</th>
									<th>{#Arbitre#} 1 </th>	
									<th>{#Arbitre#} 2 </th>	
									<th colspan=2>coef.</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								{section name=i loop=$arrayMatchs}
									<tr class='{cycle values="impair,pair"} {$arrayMatchs[i].StdOrSelected}'>
										<td><input type="checkbox" name="checkMatch" value="{$arrayMatchs[i].Id}" id="checkDelete{$smarty.section.i.iteration}"></td>
										{if $arrayMatchs[i].MatchAutorisation == 'O' && $profile <= 6 && $AuthModif == 'O'}
											<td class='color{$arrayMatchs[i].Publication}2'>
												<img class="publiMatch" data-valeur="{$arrayMatchs[i].Publication}" data-id="{$arrayMatchs[i].Id}" height="25" src="../img/oeil2{$arrayMatchs[i].Publication|default:'N'}.gif" title="{if $arrayMatchs[i].Publication == 'O'}{#Public#}{else}{#Prive#}{/if}" />
											</td>
											{if $arrayMatchs[i].Validation != 'O'}
												<td><span class='directInput numMatch' Id="Numero_ordre-{$arrayMatchs[i].Id}-text" tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}0'>{$arrayMatchs[i].Numero_ordre}</span></td>
												<td width=80>
													<a href="#" class="showOn" onclick="ParamMatch({$arrayMatchs[i].Id})"><img height="20" src="../img/glyphicons-31-pencil.png" title="{#Editer#}" border="0"></a>
													<a href="FeuilleMatchMulti.php?listMatch={$arrayMatchs[i].Id}" Target="_blank"><img height="20" src="../img/pdf.png" title="{#Feuille_marque#} (Pdf)" border="0"></a>
													<br />
													<a href="#" onclick="window.open('FeuilleMarque2.php?idMatch={$arrayMatchs[i].Id}','FeuilleV2'); return false;" ><img height="20" src="../img/glyphicons-163-ipad.png" title="{#Feuille_marque_en_ligne#}" border="0"></a>
												</td>
												<td><span class='directInput date{if $lang=='en'}EN{/if}' Id="Date_match-{$arrayMatchs[i].Id}-date{if $lang=='en'}EN{/if}" tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}1">{$arrayMatchs[i].Date_match}</span><br>
													<span class='directInput heure' Id="Heure_match-{$arrayMatchs[i].Id}-time" tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}2">{$arrayMatchs[i].Heure_match}</span></td>
												<td title="{$arrayMatchs[i].Code_competition}"><span class="compet">{if $arrayMatchs[i].Soustitre2 != ''}{$arrayMatchs[i].Soustitre2}{else}{$arrayMatchs[i].Code_competition}{/if}</span></td>
												{if $PhaseLibelle == 1}
													<td><span class="directInput phase" tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}6' data-match="{$arrayMatchs[i].Id}" data-idphase="{$arrayMatchs[i].Id_journee}" data-phase="{$arrayMatchs[i].Phase}">{$arrayMatchs[i].Phase|default:'&nbsp;'}</span></td>
													<td><span class='directInput text eq' tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}3' Id="Libelle-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Libelle|default:'&nbsp;'}</span></td>
												{else}
													<td><span class='directInput text eq' tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}3' Id="Libelle-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Libelle|default:'&nbsp;'}</span></td>
													<td><span class="lieu">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</span></td>
												{/if}
												<td><img class="typeMatch" data-valeur="{$arrayMatchs[i].Type}" data-id="{$arrayMatchs[i].Id}" src="../img/type{$arrayMatchs[i].Type}.png" title="{if $arrayMatchs[i].Type == 'C'}{#Match_de_classement#}{else}{#Match_eliminatoire#}{/if}" height="23"></td>
												<td><span class='directInput terrain' tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}4" Id="Terrain-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Terrain|default:'&nbsp;'}</span></td>
												<td>
													<span class="directInput equipe{if $arrayMatchs[i].Id_equipeA < 1} undefTeam{/if}"
														tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}9"
														Id="EquipeA-{$arrayMatchs[i].Id}-text"
														data-match="{$arrayMatchs[i].Id}"
														data-journee="{$arrayMatchs[i].Id_journee}"
														data-idequipe="{$arrayMatchs[i].Id_equipeA}"
														data-equipe="A">
															{$arrayMatchs[i].EquipeA}
													</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=A" title="{#Composition_equipe#} A"><img height="20" src="../img/b_compo_match.png"></a>
												</td>
												<td><span class='directInput score' tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}5" Id="ScoreA-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].ScoreA}</span></td>
												<td class='color{$arrayMatchs[i].Validation}2'>
													{if $profile <= 6 && $AuthModif == 'O'}
														<img class="verrouMatch" data-valeur="{$arrayMatchs[i].Validation}" data-id="{$arrayMatchs[i].Id}" height="25" src="../img/verrou2{$arrayMatchs[i].Validation}.gif" title="{if $arrayMatchs[i].Validation == 'O'}{#Verrouille#}{else}{#Deverrouille#}{/if}" />
													{else}
														<img height="25" src="../img/verrou2{$arrayMatchs[i].Validation|default:'N'}.gif" title="{#Verrouiller#}" border="0">
													{/if}
													{if $arrayMatchs[i].Statut == 'ON'}
														<span class="directStatutMatch statutMatchOn" data-statut="ON" data-id="{$arrayMatchs[i].Id}" data-periode="{$arrayMatchs[i].Periode}" title="{#Periode#} {$arrayMatchs[i].Periode}">{$arrayMatchs[i].Periode|default:$arrayMatchs[i].Statut}</span>
														<span class="scoreProvisoire" title="{#Score_provisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{elseif $arrayMatchs[i].Statut == 'END'}
														<span class="directStatutMatch statutMatchOn" data-statut="END" data-id="{$arrayMatchs[i].Id}" data-periode="{$arrayMatchs[i].Periode}" title="{#Match_termine#}">{$arrayMatchs[i].Statut}</span>
														<span class="scoreProvisoire" title="{#Score_provisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{else}
														<span class="directStatutMatch scoreProvisoire" data-statut="ATT" data-id="{$arrayMatchs[i].Id}" data-periode="{$arrayMatchs[i].Periode}" title="{#Match_en_attente#}">{$arrayMatchs[i].Statut}</span>
														<span class="scoreProvisoire hidden" title="{#Score_provisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{/if}
												</td>
												<td><span class='directInput score' tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" Id="ScoreB-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].ScoreB}</span></td>
												<td>
													<span class="directInput equipe{if $arrayMatchs[i].Id_equipeB < 1} undefTeam{/if}" tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}9" Id="EquipeB-{$arrayMatchs[i].Id}-text" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" data-idequipe="{$arrayMatchs[i].Id_equipeB}" data-equipe="B">{$arrayMatchs[i].EquipeB}</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=B" title="{#Composition_equipe#} B"><img height="20" src="../img/b_compo_match.png"></a>
												</td>
												<td>
													<span class="directInput arbitre{if $arrayMatchs[i].Arbitre_principal != '-1' && $arrayMatchs[i].Matric_arbitre_principal == 0} pbArb{/if}" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Arbitre_principal" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Arbitre_principal-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Arbitre_principal|replace:' (':' <br />('|replace:') ':')<br /> '|replace:'-1':''}</span>
												</td>
												<td>
													<span class="directInput arbitre{if $arrayMatchs[i].Arbitre_secondaire != '-1' && $arrayMatchs[i].Matric_arbitre_secondaire == 0} pbArb{/if}" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Arbitre_secondaire" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Arbitre_secondaire-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Arbitre_secondaire|replace:' (':' <br />('|replace:') ':')<br /> '|replace:'-1':''}</span>
												</td>
												<td>{if $arrayMatchs[i].CoeffA != 1}{$arrayMatchs[i].CoeffA}/{$arrayMatchs[i].CoeffB}{/if}</td>
												<td>{if $arrayMatchs[i].CoeffB != 1}{$arrayMatchs[i].CoeffA}/{$arrayMatchs[i].CoeffB}{/if}</td>
												<td><a href="#" class="showOn" onclick="RemoveCheckbox('formJournee', '{$arrayMatchs[i].Id}');return false;"><img height="20" src="../img/glyphicons-17-bin.png" title="{#Supprimer#}" border="0"></a></td>
											{else}
												<td><span class='directInputOff numMatch' Id="Numero_ordre-{$arrayMatchs[i].Id}-text" tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}0'>{$arrayMatchs[i].Numero_ordre}</span></td>
												<td width=80>
													<a href="#" class="showOff" onclick="ParamMatch({$arrayMatchs[i].Id});"><img height="20" src="../img/glyphicons-31-pencil.png" title="{#Editer#}"></a>
													<a href="FeuilleMatchMulti.php?listMatch={$arrayMatchs[i].Id}" Target="_blank"><img height="20" src="../img/pdf.png" title="{#Feuille_marque#} (Pdf)" border="0"></a>
													<br />
													<a href="#" onclick="window.open('FeuilleMarque2.php?idMatch={$arrayMatchs[i].Id}','FeuilleV2'); return false;" ><img height="20" src="../img/glyphicons-163-ipad.png" title="{#Feuille_marque_en_ligne#}" border="0"></a>
												</td>
												<td><span class='directInputOff date{if $lang=='en'}EN{/if}' Id="Date_match-{$arrayMatchs[i].Id}-date{if $lang=='en'}EN{/if}" tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}1">{$arrayMatchs[i].Date_match}</span><br>
													<span class='directInputOff heure' Id="Heure_match-{$arrayMatchs[i].Id}-time" tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}2">{$arrayMatchs[i].Heure_match}</span></td>
												<td title="{$arrayMatchs[i].Code_competition}"><span class="compet">{if $arrayMatchs[i].Soustitre2 != ''}{$arrayMatchs[i].Soustitre2}{else}{$arrayMatchs[i].Code_competition}{/if}</span></td>
												{if $PhaseLibelle == 1}
													<td><span class="directInputOff phase" tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}6' data-match="{$arrayMatchs[i].Id}" data-idphase="{$arrayMatchs[i].Id_journee}" data-phase="{$arrayMatchs[i].Phase}">{$arrayMatchs[i].Phase|default:'&nbsp;'}</span></td>
													<td><span class='directInputOff text eq' tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}3' Id="Libelle-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Libelle|default:'&nbsp;'}</span></td>
												{else}
													<td><span class='directInputOff text eq' tabindex='1{$smarty.section.i.iteration|string_format:"%02d"}3' Id="Libelle-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Libelle|default:'&nbsp;'}</span></td>
													<td><span class="lieu">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</span></td>
												{/if}
												<td><img class="typeMatchOff" data-valeur="{$arrayMatchs[i].Type}" data-id="{$arrayMatchs[i].Id}" src="../img/type{$arrayMatchs[i].Type}.png" title="{if $arrayMatchs[i].Type == 'C'}{#Match_de_classement#}{else}{#Match_eliminatoire#}{/if}" height="23"></td>
												<td><span class='directInputOff terrain' tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}4" Id="Terrain-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Terrain|default:'&nbsp;'}</span></td>
												<td>
													<span class="directInputOff equipe{if $arrayMatchs[i].Id_equipeA < 1} undefTeam{/if}" tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}9" Id="EquipeA-{$arrayMatchs[i].Id}-text" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" data-idequipe="{$arrayMatchs[i].Id_equipeA}" data-equipe="A">{$arrayMatchs[i].EquipeA}</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=A" title="{#Composition_equipe#} A"><img height="20" src="../img/b_compo_match.png"></a>
												</td>
												<td><span class='directInputOff score' tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}5" Id="ScoreA-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].ScoreA}</span></td>
												<td class='color{$arrayMatchs[i].Validation}2'>
													{if $profile <= 6 && $AuthModif == 'O'}
														<img class="verrouMatch" data-valeur="{$arrayMatchs[i].Validation}" data-id="{$arrayMatchs[i].Id}" height="25" src="../img/verrou2{$arrayMatchs[i].Validation}.gif" title="{if $arrayMatchs[i].Validation == 'O'}{#Verrouille#}{else}{#Deverrouille#}{/if}" />
													{else}
														<img height="24" src="../img/verrou2{$arrayMatchs[i].Validation|default:'N'}.gif" title="{#Verrouiller#}" border="0">
													{/if}
													{if $arrayMatchs[i].Statut == 'ON'}
														<span class="directStatutMatchOff statutMatchOn" data-statut="ON" data-id="{$arrayMatchs[i].Id}" data-periode="{$arrayMatchs[i].Periode}" title="{#Periode#} {$arrayMatchs[i].Periode}">{$arrayMatchs[i].Periode|default:$arrayMatchs[i].Statut}</span>
														<span class="scoreProvisoire" title="{#Score_provisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{elseif $arrayMatchs[i].Statut == 'END'}
														<span class="directStatutMatchOff statutMatchOn" data-statut="END" data-id="{$arrayMatchs[i].Id}" data-periode="{$arrayMatchs[i].Periode}" title="{#Match_termine#}">{$arrayMatchs[i].Statut}</span>
														<span class="scoreProvisoire" title="{#Score_provisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{else}
														<span class="directStatutMatchOff scoreProvisoire" data-statut="ATT" data-id="{$arrayMatchs[i].Id}" data-periode="{$arrayMatchs[i].Periode}" title="{#Match_en_attente#}">{$arrayMatchs[i].Statut}</span>
														<span class="scoreProvisoire hidden" title="{#Score_provisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{/if}
												</td>
												<td><span class='directInputOff score' tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" Id="ScoreB-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].ScoreB}</span></td>
												<td>
													<span class="directInputOff equipe{if $arrayMatchs[i].Id_equipeB < 1} undefTeam{/if}" tabindex="1{$smarty.section.i.iteration|string_format:'%02d'}9" Id="EquipeB-{$arrayMatchs[i].Id}-text" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" data-idequipe="{$arrayMatchs[i].Id_equipeB}" data-equipe="B">{$arrayMatchs[i].EquipeB}</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=B" title="{#Composition_equipe#} B"><img height="20" src="../img/b_compo_match.png"></a>
												</td>
												<td>
													<span class="directInputOff arbitre{if $arrayMatchs[i].Arbitre_principal != '-1' && $arrayMatchs[i].Matric_arbitre_principal == 0} pbArb{/if}" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Arbitre_principal" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Arbitre_principal-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Arbitre_principal|replace:' (':' <br />('|replace:') ':')<br /> '|replace:'-1':''}</span>
												</td>
												<td>
													<span class="directInputOff arbitre{if $arrayMatchs[i].Arbitre_secondaire != '-1' && $arrayMatchs[i].Matric_arbitre_secondaire == 0} pbArb{/if}" tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6" data-id="Arbitre_secondaire" data-match="{$arrayMatchs[i].Id}" data-journee="{$arrayMatchs[i].Id_journee}" Id="Arbitre_secondaire-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].Arbitre_secondaire|replace:' (':' <br />('|replace:') ':')<br /> '|replace:'-1':''}</span>
												</td>
												<td>{if $arrayMatchs[i].CoeffA != 1}{$arrayMatchs[i].CoeffA}/{$arrayMatchs[i].CoeffB}{/if}</td>
												<td>{if $arrayMatchs[i].CoeffB != 1}{$arrayMatchs[i].CoeffA}/{$arrayMatchs[i].CoeffB}{/if}</td>
												<td><a href="#" class="showOff" onclick="RemoveCheckbox('formJournee', '{$arrayMatchs[i].Id}');return false;"><img height="20" src="../img/glyphicons-17-bin.png" title="{#Supprimer#}" border="0"></a></td>
											{/if}
										{elseif $arrayMatchs[i].MatchAutorisation == 'O' && $profile == 9 && $AuthModif == 'O'}
											<td>	
												<img height="24" src="../img/oeil2{$arrayMatchs[i].Publication|default:'N'}.gif" title="{if $arrayMatchs[i].Publication == 'O'}{#Public#}{else}{#Prive#}{/if}" border="0">
											</td>
											{if $arrayMatchs[i].Validation != 'O'}
												<td>{$arrayMatchs[i].Numero_ordre}</td>
												<td width=80>
													<a href="FeuilleMatchMulti.php?listMatch={$arrayMatchs[i].Id}" Target="_blank"><img height="20" src="../img/pdf.png" title="{#Feuille_marque#} (Pdf)" border="0"></a>
													<br />
													<a href="hey#" onclick="window.open('FeuilleMarque2.php?idMatch={$arrayMatchs[i].Id}','FeuilleV2'); return false;" ><img height="20" src="../img/glyphicons-163-ipad.png" title="{#Feuille_marque_en_ligne#}" border="0"></a>
												</td>
												<td>{$arrayMatchs[i].Date_match}<br>
													{$arrayMatchs[i].Heure_match}</td>
												<td title="{$arrayMatchs[i].Code_competition}"><span class="compet">{if $arrayMatchs[i].Soustitre2 != ''}{$arrayMatchs[i].Soustitre2}{else}{$arrayMatchs[i].Code_competition}{/if}</span></td>
												{if $PhaseLibelle == 1}
													<td><span class="phase">{$arrayMatchs[i].Phase|default:'&nbsp;'}</span></td>
													<td>{$arrayMatchs[i].Libelle|default:'&nbsp;'}</td>
												{else}
													<td colspan=2><span class="lieu">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</span></td>
												{/if}
												<td><img src="../img/type{$arrayMatchs[i].Type}.png" title="{if $arrayMatchs[i].Type == 'C'}{#Match_de_classement#}{else}{#Match_eliminatoire#}{/if}" /></td>
												<td>{$arrayMatchs[i].Terrain|default:'&nbsp;'}</td>
												<td>
													<span>{$arrayMatchs[i].EquipeA}</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=A" title="{#Composition_equipe#} A"><img height="20" src="../img/b_compo_match.png"></a>
												</td>
												<td class='directInput score' tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}5"><span Id="ScoreA-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].ScoreA}</span></td>
												<td class='color{$arrayMatchs[i].Validation}2'>
													<img height="25" src="../img/verrou2{$arrayMatchs[i].Validation|default:'N'}.gif" title="{#Verrouiller#}" border="0">
													{if $arrayMatchs[i].Statut == 'ON'}
														<span class="statutMatchOn" title="{#Periode#} {$arrayMatchs[i].Periode}">{$arrayMatchs[i].Periode}</span>
														<span class="scoreProvisoire" title="{#Score_provisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{elseif $arrayMatchs[i].Statut == 'END'}
														<span class="statutMatchOn" title="{#Match_termine#}">{$arrayMatchs[i].Statut}</span>
														<span class="scoreProvisoire" title="{#Score_provisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
													{/if}
												</td>
												<td class='directInput score' tabindex="2{$smarty.section.i.iteration|string_format:'%02d'}6"><span Id="ScoreB-{$arrayMatchs[i].Id}-text">{$arrayMatchs[i].ScoreB}</span></td>
												<td>
													<span>{$arrayMatchs[i].EquipeB}</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=B" title="{#Composition_equipe#} B"><img height="20" src="../img/b_compo_match.png"></a>
												</td>
												<td>{if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
												<td>{if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
												<td>{if $arrayMatchs[i].CoeffA != 1}{$arrayMatchs[i].CoeffA}/{$arrayMatchs[i].CoeffB}{/if}</td>
												<td>{if $arrayMatchs[i].CoeffB != 1}{$arrayMatchs[i].CoeffA}/{$arrayMatchs[i].CoeffB}{/if}</td>
												<td>&nbsp;</td>
											{else}
												<td>{$arrayMatchs[i].Numero_ordre}</td>
												<td width=80>
													<a href="FeuilleMatchMulti.php?listMatch={$arrayMatchs[i].Id}" Target="_blank"><img height="20" src="../img/pdf.png" title="{#Feuille_marque#} (Pdf)" border="0"></a>
												</td>
												<td>{$arrayMatchs[i].Date_match}<br>
													{$arrayMatchs[i].Heure_match}</td>
												<td title="{$arrayMatchs[i].Code_competition}"><span class="compet">{if $arrayMatchs[i].Soustitre2 != ''}{$arrayMatchs[i].Soustitre2}{else}{$arrayMatchs[i].Code_competition}{/if}</span></td>
												{if $PhaseLibelle == 1}
													<td><span class="phase">{$arrayMatchs[i].Phase|default:'&nbsp;'}</span></td>
													<td>{$arrayMatchs[i].Libelle|default:'&nbsp;'}</td>
												{else}
													<td>{$arrayMatchs[i].Libelle|default:'&nbsp;'}</td>
													<td><span class="lieu">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</span></td>
												{/if}
												<td><img src="../img/type{$arrayMatchs[i].Type}.png" title="{if $arrayMatchs[i].Type == 'C'}{#Match_de_classement#}{else}{#Match_eliminatoire#}{/if}" /></td>
												<td>{$arrayMatchs[i].Terrain|default:'&nbsp;'}</td>
												<td>
													<span>{$arrayMatchs[i].EquipeA}</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=A" title="{#Composition_equipe#} A"><img height="20" src="../img/b_compo_match.png"></a>
												</td>
												<td>{$arrayMatchs[i].ScoreA}</td>
												<td class='color{$arrayMatchs[i].Validation}2'>
													<img height="25" src="../img/verrou2{$arrayMatchs[i].Validation|default:'N'}.gif" title="{#Verrouiller#}" border="0">
												</td>
												<td>{$arrayMatchs[i].ScoreB}</td>
												<td>
													<span>{$arrayMatchs[i].EquipeB}</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=B" title="{#Composition_equipe#} B"><img height="20" src="../img/b_compo_match.png"></a>
												</td>
												<td>{if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
												<td>{if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
												<td>{if $arrayMatchs[i].CoeffA != 1}{$arrayMatchs[i].CoeffA}/{$arrayMatchs[i].CoeffB}{/if}</td>
												<td>{if $arrayMatchs[i].CoeffB != 1}{$arrayMatchs[i].CoeffA}/{$arrayMatchs[i].CoeffB}{/if}</td>
												<td>&nbsp;</td>
											{/if}
										{else}
											<td>	
												<img height="25" src="../img/oeil2{$arrayMatchs[i].Publication|default:'N'}.gif" {if $arrayMatchs[i].Publication == 'O'}{#Public#}{else}{#Prive#}{/if} border="0">
											</td>
											<td>{$arrayMatchs[i].Numero_ordre}</td>
											<td width=80>
												<a href="FeuilleMatchMulti.php?listMatch={$arrayMatchs[i].Id}" Target="_blank"><img height="20" src="../img/pdf.png" title="{#Feuille_marque#} (Pdf)" ></a>
											</td>
											<td>{$arrayMatchs[i].Date_match}<br>
												{$arrayMatchs[i].Heure_match}</td>
											<td title="{$arrayMatchs[i].Code_competition}"><span class="compet">{if $arrayMatchs[i].Soustitre2 != ''}{$arrayMatchs[i].Soustitre2}{else}{$arrayMatchs[i].Code_competition}{/if}</span></td>
											{if $PhaseLibelle == 1}
												<td><span class="phase">{$arrayMatchs[i].Phase|default:'&nbsp;'}</span></td>
												<td>{$arrayMatchs[i].Libelle|default:'&nbsp;'}</td>
											{else}
												<td colspan=2><span class="lieu">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</span></td>
											{/if}
											<td><img src="../img/type{$arrayMatchs[i].Type}.png" title="{if $arrayMatchs[i].Type == 'C'}{#Match_de_classement#}{else}{#Match_eliminatoire#}{/if}" /></td>
											<td>{$arrayMatchs[i].Terrain|default:'&nbsp;'}</td>
											<td>
												<span>{$arrayMatchs[i].EquipeA}</span>
												<br />
												<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=A" title="{#Composition_equipe#} A"><img height="20" src="../img/b_compo_match.png"></a>
											</td>
											<td>{$arrayMatchs[i].ScoreA|default:'&nbsp;'}</td>
											<td>
												<img height="25" src="../img/verrou2{$arrayMatchs[i].Validation|default:'N'}.gif" title="{#Verrouiller#}" border="0">
											</td>
											<td>{$arrayMatchs[i].ScoreB|default:'&nbsp;'}</td>
											<td>
												<span>{$arrayMatchs[i].EquipeB}</span>
												<br />
												<a href="GestionMatchEquipeJoueur.php?idMatch={$arrayMatchs[i].Id}&codeEquipe=B" title="{#Composition_equipe#} B"><img height="20" src="../img/b_compo_match.png"></a>
											</td>
											<td>{if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
											<td>{if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
											<td>{$arrayMatchs[i].CoeffA}</td>
											<td>{$arrayMatchs[i].CoeffB}</td>
											<td>&nbsp;</td>
										{/if}
									</tr>
								{/section}
							</tbody>
						</table>
						<br />
					</div>
						{assign var='nbmatch' value=$smarty.section.i.iteration-1}
						{if $nbmatch > 0}{#Nb_matchs#} : {$nbmatch}{/if}
				</div>
			</form>
		</div>
                <script>
                    var arrayCheck = '{$arrayCheck}';
                </script>
                    