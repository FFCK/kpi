<div class="main">
<form method="POST" action="GestionCopieCompetition.php" name="formCopieCompetition" enctype="multipart/form-data">
  <input type='hidden' name='Cmd' Value='' />
  <input type='hidden' name='ParamCmd' Value='' />
  <input type='hidden' name='saisonOrigine' Value='{$saisonOrigine}' />
  <input type='hidden' name='competOrigine' Value='{$competOrigine}' />
  <input type='hidden' name='saisonDestination' Value='{$saisonDestination}' />
  <input type='hidden' name='competDestination' Value='{$competDestination}' />
  <div class='blocRight Right3'>
	<table class='tableau2'>
	  <tr>
		<th class='titreForm' colspan=2>
		  <label>Copier la structure des journées/phases et des matchs</label>
		</th>
	  </tr>
	  <tr>
		<td><label for="saisonOrigine">Saison Origine</label>
		  <select name="saisonOrigine" onchange="submit()">
			{section name=i loop=$arraySaisons}
			  <Option Value="{$arraySaisons[i].Code}" {if $arraySaisons[i].Code == $saisonOrigine}selected{/if}>
				{$arraySaisons[i].Code}</Option>
			{/section}
		  </select>
		</td>
		<td><label for="competOrigine">Competition Origine</label>
		  <select name="competOrigine" onchange="submit()">
			{section name=i loop=$arrayCompetitionOrigine}
			  {assign var='options' value=$arrayCompetitionOrigine[i].options}
			  {assign var='label' value=$arrayCompetitionOrigine[i].label}
			  <optgroup label="{$smarty.config.$label|default:$label}">
				{section name=j loop=$options}
				  {assign var='optionLabel' value=$options[j].Code}
				  <Option Value="{$options[j].Code}" {$options[j].selected}>
					{$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>
				{/section}
			  </optgroup>
			{/section}
		  </select>
		</td>
	  </tr>
	  <tr>
		<td colspan=2 align=center>{$Soustitre}</td>
	  </tr>
	  <tr>
		<td colspan=2 align=center>{$Soustitre2}</td>
	  </tr>
	  <tr>
		<td colspan=2 align=center>{$commentairesCompet}</td>
	  </tr>
	  <tr>
		<td>Classement :</td>
		<td>{$codeTypeCltOrigine}</td>
	  </tr>
	  <tr>
		<td>Nbre d'équipes :</td>
		  <td>{$equipesOrigine}</td>
		</tr>
		<tr>
		  <td>Qualifiées :</td>
		  <td>{$qualifiesOrigine}</td>
		</tr>
		<tr>
		  <td>Eliminées :</td>
		  <td>{$eliminesOrigine}</td>
		</tr>
		<tr>
		  <td>Nb Matchs :</td>
		  <td>{$nbMatchs}</td>
		</tr>
		<tr>
		  <td colspan=2>
			<hr>
		  </td>
		</tr>
		<tr>
		  <td colspan=2 align="center">
			{if $codeTypeCltOrigine == 'CHPT'}







			{section name=i loop=$arrayJournees}
				{$arrayJournees[i].Lieu}<br>







			{/section}







		  {else}







			{section name=i loop=$arrayJournees}







			  {if !$smarty.section.i.first}






				{if $arrayJournees[i].Niveau != $niveauTmp}<br>






				{else} |






				{/if}






			  {/if}
				{$arrayJournees[i].Phase}







			  {assign var='niveauTmp' value=$arrayJournees[i].Niveau}







			{/section}







		  {/if}
		  </td>
		</tr>
		<tr>
		  <th class='titreForm' colspan=2>
			<label>Destination</label>
		  </th>
		</tr>
		<tr>
		  <td><label for="saisonDestination">Saison Destination</label>
			<select name="saisonDestination" onchange="submit()">







		  {section name=i loop=$arraySaisons}
				<Option Value="{$arraySaisons[i].Code}" 
			{if $arraySaisons[i].Code == $saisonDestination}selected 
			{/if}>
				  {$arraySaisons[i].Code}</Option>







		  {/section}
			</select>
		  </td>
		  <td><label for="competDestination">Competition Destination</label>
			<select name="competDestination" onchange="submit()">







		  {section name=i loop=$arrayCompetitionDestination}







			{assign var='options' value=$arrayCompetitionDestination[i].options}







			{assign var='label' value=$arrayCompetitionDestination[i].label}
				<optgroup label="{$smarty.config.$label|default:$label}">







			{section name=j loop=$options}







			  {assign var='optionLabel' value=$options[j].Code}
					<Option Value="{$options[j].Code}" {$options[j].selected}>
					  {$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>







			{/section}
				</optgroup>







		  {/section}
			</select>
		  </td>
		</tr>
		<tr>
		  <td colspan=2>
			<hr>
		  </td>
		</tr>
		<tr>
		  <td>Classement :</td>
		  <td>{$codeTypeCltDestination}</td>
		</tr>
		<tr>
		  <td>Nbre d'équipes :</td>
		  <td>{$equipesDestination}</td>
		</tr>
		<tr>
		  <td>Qualifiées :</td>
		  <td>{$qualifiesDestination}</td>
		</tr>
		<tr>
		  <td>Eliminées :</td>
		  <td>{$eliminesDestination}</td>
		</tr>
	  </table>
	  <table class='tableau2'>
		<tr>
		  <th class='titreForm' colspan=2>
			<label>Valeurs communes à chaque journée / phase<br>(% pour reprendre les valeurs individuelles de chaque
			  journée)</label>
		  </th>
		</tr>
		<tr>
		  <td colspan=2 class="vert" align='center'><label><b>Paramètres apparents dans le calendrier
				public</b></label></td>
		</tr>
		<tr>
		  <td class="vert">
			<label for="Date_debut">Date_debut</label>
			<input type="text" class="date" name="Date_debut" value="{$Date_debut}"
			  onfocus="displayCalendar(document.forms[0].Date_debut,'dd/mm/yyyy',this)">
			<input type="hidden" name="Date_origine" value="{$Date_debut}">
		  </td>
		  <td class="vert">
			<label for="Date_fin">Date_fin</label>
			<input type="text" class="date" name="Date_fin" value="{$Date_fin}"
			  onfocus="displayCalendar(document.forms[0].Date_fin,'dd/mm/yyyy',this)">
		  </td>
		</tr>
		<tr>
		  <td class="vert"><label for="Lieu">Lieu</label><input type="text" name="Lieu" value="{$Lieu}" /></td>
		  <td class="vert"><label for="Departement">Département</label><input type="text" class="dpt"
			  name="Departement" value="{$Departement}" /></td>
		</tr>
		<tr>
		  <td class="vert" colspan=2>
			<label for="Nom">Nom journée (Championnat) ou Nom compétition (Coupe)</label>
			<img title="Nom qui apparaîtra dans le calendrier public."
			  alt="Nom qui apparaîtra dans le calendrier public." src="../img/b_help.png"
			  onclick="alert('Nom qui apparaîtra dans le calendrier public.')" />
			<input type="text" name="Nom" value="{$Nom}" />
			<label><i><u>Exemples :</u><br>
				N1F - 3ème journée<br>
				N3H - Demi-finale montante<br>
				N4H - Zone Sud-Ouest<br>
				CF Hommes - 2ème tour zone Sud<br>
				10th Veurne International Canoepolo Tournament</i><br>
			</label>
		  </td>
		</tr>
		<!--					<tr>
				  <td colspan=2><label for="Libelle">Libelle</label><input type="text" name="Libelle" value="{$Libelle}" readonly /></td>
			  </tr>
-->
		<tr>
		  <td><br><label for="Organisateur">Club Organisateur</label><input type="text" name="Organisateur"
			  value="{$Organisateur}" /></td>
		  <td><br><label for="Plan_eau">Plan d'eau</label><input type="text" name="Plan_eau" value="{$Plan_eau}" />
		</td>
	  </tr>
	  <tr>
		<td><label for="Responsable_R1">Responsable local R1</label><input type="text" name="Responsable_R1"
			value="{$Responsable_R1}" /></td>
		<td><label for="Responsable_insc">Responsable insc. RZ</label><input type="text" name="Responsable_insc"
			value="{$Responsable_insc}" /></td>
	  </tr>
	  <tr>
		<td><label for="Delegue">Délégué fédéral</label><input type="text" name="Delegue" value="{$Delegue}" /></td>
	  </tr>
	  <tr>
		<td colspan=2>
		  <hr>
		</td>
	  </tr>
	  <tr>
		<td colspan=2><input type="checkbox" name="init1erTour" value="init"><label for="init1erTour">Encoder les
			équipes au premier tour (préparer le tirage au sort)</label></td>
	  </tr>
	  <tr>
		<td colspan=2><label>(Uniquement si ces matchs ne sont pas déjà encodés !)</label></td>
	  </tr>
	  <tr>
		<td colspan=2><br><input type="button" onclick="Duppli();" name="Dupliquer"
			value="Duppliquer la structure des matchs"></td>
	  </tr>
	  <tr>
		<td colspan=2>&nbsp;</td>
	  </tr>
	</table>
  </div>
  <div class='blocRight Right5'>
	<h3>
	  Rechercher des schémas de compétitions
	</h3>
	<label for="recherche_nb_equipes">Nombre d'équipes : </label>
	  <input type="tel" size="2" name="recherche_nb_equipes" id="recherche_nb_equipes"
		value="{$recherche_nb_equipes}">
	  <label for="tri">Trier par : </label>
	  <select name="recherche_tri" id="recherche_tri" style="width: fit-content;">
		<option value="saison" {if $recherche_tri === 'saison'}selected{/if}>Saison</option>
		<option value="matchs" {if $recherche_tri === 'matchs'}selected{/if}>Nb matchs</option>
	  </select>
	  <input type="submit" name="valid_recherche_schema" id="valid_recherche_schema" value="Valider">
	  <br>
	  <br>
	  <table class='tableau' id='tableCompet'>
		<caption><i>(Certains schémas anciens ne sont peut-être pas totalement encodés)</i></caption>
		<thead>
		  <tr>
			<th title="Saison">Saison</th>
			<th title="Code">Code</th>
			<th title="Niveau">Niv.</th>
			<th title="Titre de la compétition">Libelle</th>
			{* <th title="Compétition de référence">Groupe</th> *}
			<th title="Tour/Phase">Tour</th>
			<th title="Nombre d'équipes affectées">Equipes</th>
			<th title="Nombre de matchs">Matchs</th>
			<th title="Informations">Info</th>
			<th title="Schéma">Schéma</th>
		  </tr>
		</thead>

		<tbody>
		  {section name=i loop=$arraySchemas}
			<tr class='{cycle values="impair,pair"}'>
			  <td>{$arraySchemas[i].Code_saison}</td>
			  <td>{$arraySchemas[i].Code}</td>
			  <td>{$arraySchemas[i].Code_niveau|default:'&nbsp;'}</td>
			  <td>
					{if $arraySchemas[i].Titre_actif != 'O' && $arraySchemas[i].Soustitre != ''}
						{$arraySchemas[i].Soustitre}
					{else}
						{$arraySchemas[i].Libelle}
					{/if}
					&nbsp;
					<a href='GestionDoc.php?Compet={$arraySchemas[i].Code}&Saison={$arraySchemas[i].Code_saison}'>
						<img height="20" src="../img/basculer.png" align="middle" title="Basculer vers cette saison/compétition">
					</a>
				  {if $arraySchemas[i].Soustitre2 != ''}<br />{$arraySchemas[i].Soustitre2}{/if}</td>
			  {* <td>{$arraySchemas[i].Code_ref|default:'&nbsp;'}</td> *}
			  <td>{if $arraySchemas[i].Code_tour == '10'}F{else}{$arraySchemas[i].Code_tour|default:'&nbsp;'}{/if}</td>
			  <td>{$arraySchemas[i].Nb_equipes|default:'&nbsp;'}</td>
			  <td>{$arraySchemas[i].nbMatchs|default:'&nbsp;'}</td>
			  <td>{if $arraySchemas[i].commentairesCompet != ''}<img height="20" src="../img/information.gif" title="{$arraySchemas[i].commentairesCompet}">{/if}</td>
			  <td>
				<a href="GestionSchema.php?Compet={$arraySchemas[i].Code}&Saison={$arraySchemas[i].Code_saison}"
				  target="_blank">
				  <img height="20" src="../img/typeE.png" title="Schéma de progression">
				</a>
			  </td>
			</tr>
			{sectionelse}
			<tr>
			  <td colspan="7">Aucun résultat.</td>
			</tr>

		  {/section}
		</tbody>
	  </table>

	</div>
  </form>
</div>