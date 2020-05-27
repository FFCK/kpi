	<div class="main">
		<form method="POST" action="GestionJournal.php" name="formJournal" enctype="multipart/form-data">
			<input type='hidden' name='Cmd' Value=''/>
			<input type='hidden' name='ParamCmd' Value=''/>

			<div class='blocLeft'>
				<div class='titrePage'>Journal</div>
				<div class='blocTable' id='blocJournal'>
					<table class='tableau' id='tableJournal'>
						<thead>
							<tr class='header'>
								<th>Dates</th>
								<th>Identite</th>
								<th>Actions</th>
								<th>Journal</th>
								<th>Comp.</th>
								<th>Journ.</th>
								<th>Match</th>
							</tr>
						</thead>
						<tbody>
							{section name=i loop=$arrayJournal}
								<tr class='{cycle values="impair,pair"}'>
									<td>{$arrayJournal[i].Dates|date_format:"<b>%d/%m/%y</b><br>à  %H:%M"}</td>
									<td title='{$arrayJournal[i].Fonction}'><b>{$arrayJournal[i].Identite}</b></td>
									<td>{$arrayJournal[i].Actions}</td>
									<td>{$arrayJournal[i].Journal}</td>
									<td><b>{$arrayJournal[i].Competitions}</b><br>{$arrayJournal[i].Saisons}</td>
									<td>{$arrayJournal[i].Journees}</td>
									<td>{$arrayJournal[i].Matchs}</td>
								</tr>
							{/section}
						</tbody>
					</table>
				</div>
			</div>
			<div class='blocRight'>
				<table width=100%>
					<tr>
						<th colspan=2 class='titreForm'>
							<label>Sélection</label>
						</th>
					</tr>
					<tr>
						<td colspan=2>
							<label for="theLimit">Nb de lignes :</label>
							<select name="theLimit">
									<Option Value="25" {if $theLimit eq '25'}selected{/if}>25</Option>
									<Option Value="50" {if $theLimit eq '50'}selected{/if}>50</Option>
									<Option Value="100" {if $theLimit eq '100'}selected{/if}>100</Option>
									<Option Value="200" {if $theLimit eq '200'}selected{/if}>200</Option>
									<Option Value="500" {if $theLimit eq '500'}selected{/if}>500</Option>
									<Option Value="1000" {if $theLimit eq '1000'}selected{/if}>1000</Option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<label for="theUser">Utilisateurs :</label>
							<select name="theUser">
								<Option Value="">Tous</Option>
								{section name=i loop=$arrayUsers} 
									<Option Value="{$arrayUsers[i].Code}" {if $arrayUsers[i].Code eq $theUser}selected{/if}>{$arrayUsers[i].Identite} - {$arrayUsers[i].Fonction}</Option>
								{/section}
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<label for="theAction">Actions : {$theAction}</label>
							<select name="theAction">
								<Option Value="" {if $theAction eq ''}selected{/if}>Toutes</Option>
                                <optgroup label="Ensemble">
                                    <Option Value="Connexion" {if $theAction eq 'Connexion'}selected{/if}>Connexions</Option>
                                    <Option Value="Ajout" {if $theAction eq 'Ajout'}selected{/if}>Ajouts</Option>
                                    <Option Value="Modif" {if $theAction eq 'Modif'}selected{/if}>Modifications</Option>
                                    <Option Value="Supp" {if $theAction eq 'Supp'}selected{/if}>Suppressions</Option>
                                    <Option Value="Calcul" {if $theAction eq 'Calcul'}selected{/if}>Calculs</Option>
                                </optgroup>
                                <optgroup label="Détail">
                                    {section name=i loop=$arrayActions} 
                                        <Option Value="{$arrayActions[i].Action}" {if $arrayActions[i].Action eq $theAction}selected{/if}>{$arrayActions[i].Action}</Option>
                                    {/section}
                                </optgroup>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label for="theSaison">Saison :</label>
							<input type=text name='theSaison' value='{$theSaison}' size=4>
						</td>
						<td>
							<label for="theCompet">Compétition :</label>
							<input type=text name='theCompet' value='{$theCompet}' >
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<br> 
							<br>
							<input type="submit" name="Selection" value="Sélectionner">
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
	