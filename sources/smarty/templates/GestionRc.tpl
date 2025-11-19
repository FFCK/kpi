	<div class="main">
		<form method="POST" action="GestionRc.php" name="formRc" enctype="multipart/form-data">
			<input type='hidden' name='Cmd' Value='' />
			<input type='hidden' name='ParamCmd' Value='' />
			<input type='hidden' name='idRc' Value='{$idRc}' />

			<div class='blocLeft'>
				<div class='titrePage'>{#Gestion_des_RC#}</div>
				{if $arrayinfo|default:false}
					<table width="100%">
						<tr>
							<td colspan=2>
								{section name=i loop=$arrayinfo}
									{$arrayinfo[i]}<BR>
								{/section}
							</td>
						</tr>
					</table>
					<br>
				{/if}
                <div class='liens'>
					{if $profile <= 2}
						<label for="saisonTravail">{#Saison#} :</label>
						<select name="saisonTravail"  id="saisonTravail" onChange="sessionSaison()">
							{section name=i loop=$arraySaison} 
								<Option Value="{$arraySaison[i].Code}" {$arraySaison[i].Selected|default: false}>{$arraySaison[i].Code}</Option>
							{/section}
						</select>
						<label for="AfficheCompet">{#Afficher#} :</label>
						<select name="AfficheNiveau" onChange="changeAffiche()">
							<Option Value="" selected>{#Tous_les_niveaux#}</Option>
							<Option Value="INT"{if $AfficheNiveau == 'INT'} selected{/if}>{#Competitions_Internationales#}</Option>
							<Option Value="NAT"{if $AfficheNiveau == 'NAT'} selected{/if}>{#Competitions_Nationales#}</Option>
							<Option Value="REG"{if $AfficheNiveau == 'REG'} selected{/if}>{#Competitions_Regionales#}</Option>
						</select>
						<select name="AfficheCompet" onChange="changeAffiche()">
							<Option Value="" selected>{#Toutes_les_competitions#}</Option>
							<Option Value="N"{if $AfficheCompet == 'N'} selected{/if}>{#Championnat_de_France#}</Option>
							<Option Value="CF"{if $AfficheCompet == 'CF'} selected{/if}>{#Coupe_de_France#}</Option>
							{section name=i loop=10}
								{if $sectionLabels[i]|default: false}
									{assign var='temp' value=$sectionLabels[i]}
									<Option Value="{$smarty.section.i.index}"{if $AfficheCompet == $smarty.section.i.index} selected{/if}>{$smarty.config.$temp|default:$temp}</Option>
								{/if}   
							{/section}
							<Option Value="M"{if $AfficheCompet == 'M'} selected{/if}>{#Modeles#}</Option>
						</select>
					{/if}   

                    <label>{#Filtre#} :</label>
                    <select id="filtreCompetition" style="background-color: #F15A2A">
						<option value="">{#Tous#}</option>
						<option value="- CNA -" {if 'CNA'==$filtreCompet}selected{/if}>- CNA -</option>
						{section name=i loop=$arrayCompetitions}
							<option value="{$arrayCompetitions[i].Code}" {if $arrayCompetitions[i].Code==$filtreCompet}selected{/if}>{$arrayCompetitions[i].Code}</option>
						{/section}
                    </select>
                </div>
				<div class='blocTable' id='blocCompet'>
					<table class='tableau' id='tableRC'>
						<thead>
							<tr class='header'>
								{if $profile <= 2}<th>&nbsp;</th>{/if}
								<th>{#Saison#}</th>
								<th>{#Competition#}</th>
								<th>{#Ordre#}</th>
								<th>{#Nom#}</th>
								<th>{#Licence#}</th>
								<th>{#Adresse_email#}</th>
								{if $profile <= 2}<th>&nbsp;</th>{/if}
							</tr>
						</thead>
						<tbody>
							{section name=i loop=$arrayRc}
								<tr class="{cycle values='impair,pair'} {$arrayRc[i].Selected|default: ''}" data-code="{$arrayRc[i].Code_competition}">
									{if $profile <= 2}
										<td>
											<a href="#" Id="Param{$arrayRc[i].Id}" onclick="paramRc({$arrayRc[i].Id})">
												<img height="18" src="../img/glyphicons-31-pencil.png" alt="{#Editer#}" title="{#Editer#}" />
											</a>
										</td>
									{/if}
									<td>{$arrayRc[i].Code_saison}</td>
									<td>{$arrayRc[i].Code_competition}</td>
									<td>{$arrayRc[i].Ordre}</td>
									<td>{$arrayRc[i].Prenom} {$arrayRc[i].Nom}</td>
									<td class="cliquableNomEquipe">
										<a href="GestionAthlete.php?Athlete={$arrayRc[i].Matric}">{$arrayRc[i].Matric}</a>
									</td>
									<td>{$arrayRc[i].Mail}</td>
									{if $profile <= 1}
										<td><a href="#" onclick="RemoveCheckbox('formRc', '{$arrayRc[i].Id}');return false;"><img height="20" src="../img/glyphicons-17-bin.png" alt="{#Supprimer#}" title="{#Supprimer#}" /></a></td>
									{/if}
								</tr>
							{/section}
						</tbody>
					</table>
				</div>
			</div>
			{if $profile <= 2}
				<div class='blocRight'>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=4>
								<label>{if $idRc == -1}{#Ajouter#}{else}{#Modifier#}{/if}</label>
							</th>
						</tr>
						<tr>
							<td colspan=4>
								<label for="Nom">{#Chercher#} :</label>
								<input type="text" name="Nom" value="" maxlength=40 id="Nom" placeholder="{#Nom_de_famille#}, {#Prenom#}, {#Licence#}">
								<br>
								<b id="NomSelectionne">{$Identite}</b>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="Matric">{#Licence#} :</label>
								<input type="tel" name="Matric" value="{$selectMatric}" id="Matric" readonly>
							</td>
							<td colspan=2>
								<label for="Code_saison">{#Saison#} :</label>
								<input type="tel" name="Code_saison" value="{$selectSaison}" id="Code_saison" maxlength="4" size=4 readonly>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="Code_competition">{#Competition#} :</label>
								<select name="Code_competition" id="Code_competition">
									<option value="- CNA -">- CNA -</option>
									{section name=i loop=$arrayCompetitions}
										<option value="{$arrayCompetitions[i].Code}" {if $arrayCompetitions[i].Code==$selectCompetition}selected{/if}>{$arrayCompetitions[i].Code}</option>
									{/section}
								</select>
							</td>
							<td colspan=2>
								<label for="Ordre">{#Ordre#} :</label>
								<input type="tel" name="Ordre" value="{$selectOrdre}" id="Ordre" maxlength="2" size=1>
							</td>
						</tr>
						<tr>
							{if $idRc != -1}
								<td colspan=2>
									<br>
									<br>
									<input type="button" onclick="updateRc()" value="<< {#Modifier#}">
								</td>
								<td colspan=2>
									<br>
									<br>
									<input type="button" onclick="razRc()" value="{#Annuler#}">
								</td>
							{else}
								<td colspan=4>
									<br>
									<br>
									<input type="button" onclick="addRc()" value="<< {#Ajouter#}">
								</td>
							{/if}
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=4>
								<label>Copier les Responsables de Compétition (RC)</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="saisonSourceRc">Saison source :</label>
								<select name="saisonSourceRc" id="saisonSourceRc">
									<option value="">-- Sélectionnez --</option>
									{section name=i loop=$arraySaison}
										<option value="{$arraySaison[i].Code}">{$arraySaison[i].Code}</option>
									{/section}
								</select>
							</td>
							<td colspan=2>
								<label for="saisonCibleRc">Saison cible :</label>
								<select name="saisonCibleRc" id="saisonCibleRc">
									<option value="">-- Sélectionnez --</option>
									{section name=i loop=$arraySaison}
										<option value="{$arraySaison[i].Code}">{$arraySaison[i].Code}</option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=4>
								<br>
								<input type="button" name="CopyRcBtn" id="CopyRcBtn" onclick="CopyRc();" value="Copier les RC">
								<br>
								<small style="color: #666;">Cette opération copie tous les RC de la saison source vers la saison cible (les doublons sont ignorés).</small>
							</td>
						</tr>
					</table>
				</div>
			{/if}
		</form>
	</div>
	