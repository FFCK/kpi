	<div class="main">
		<form method="POST" action="GestionGroupe.php" id="formGroupe" name="formGroupe" enctype="multipart/form-data">
			<input type='hidden' id='Cmd' name='Cmd' Value='' />
			<input type='hidden' id='ParamCmd' name='ParamCmd' Value='' />
			<input type='hidden' id='idGroupe' name='idGroupe' Value='{$groupe.id}' />

			<div class='blocLeft'>
				<div class='titrePage'>{#Groupes#}</div>
				<div class='blocTable' id='blocCompet'>
					<table class='tableau' id='tableCompet'>
						<thead>
							<tr class='header'>
								<th>Id</th>
								<th>{#Editer#}</th>
								<th>Section</th>
								<th>{#Niveau#}</th>
								<th>{#Ordre#}</th>
								<th>{#Groupe#}</th>
								<th>{#Nom#}</th>
								<th>{#Supprimer#}</th>
							</tr>
						</thead>
						<tbody>
							{section name=i loop=$arrayGroupes}{assign name=lastOrder value=$arrayGroupes[i].ordre}
								<tr class='{cycle values="impair,pair"} {$arrayGroupes[i].selected}'>
									<td>
										{$arrayGroupes[i].id}
										<img src="../img/up.png" class="ordre_up" data-id="{$arrayGroupes[i].id}"
											data-order="{$arrayGroupes[i].ordre}" style="cursor: pointer" />
									</td>
									<td>
										<a href="#" Id="Param{$arrayGroupes[i].id}" onclick="editGroupe({$arrayGroupes[i].id})">
											<img height="18" src="../img/glyphicons-31-pencil.png" alt="{#Editer#}" title="{#Editer#}" />
										</a>
									</td>
									<td>{$arrayGroupes[i].section_name}</td>
									<td>{$arrayGroupes[i].Code_niveau}</td>
									<td>{$arrayGroupes[i].ordre}</td>
									<td>{$arrayGroupes[i].Groupe}</td>
									<td>{$arrayGroupes[i].Libelle}</td>
									<td>
										<a href="#" Id="Delete{$arrayGroupes[i].id}" onclick="removeGroupe({$arrayGroupes[i].id})">
											<img height="18" src="../img/glyphicons-17-bin.png" alt="{#Supprimer#}" title="{#Supprimer#}" />
										</a>
									</td>
								</tr>
							{/section}
						</tbody>
					</table>
				</div>
			</div>
			<div class='blocRight'>
				<table width=100%>
					<tr>
						<th class='titreForm' colspan=2>
							<label>{if $groupe.id == -1 || $groupe.id == ''}{#Ajouter#}{else}{#Modifier#}{/if}</label>
						</th>
					</tr>
					<tr>
						<td>
							<label for="section">Section :</label>
							<select name="section" id="section">
								<option value="1" {if $groupe.section == 1}selected{/if}>ICF / ECA</option>
								<option value="2" {if $groupe.section == 2}selected{/if}>National</option>
								<option value="3" {if $groupe.section == 3}selected{/if}>Régional</option>
								<option value="4" {if $groupe.section == 4}selected{/if}>Tournoi</option>
								<option value="5" {if $groupe.section == 5}selected{/if}>Étranger</option>
								<option value="100" {if $groupe.section == 100}selected{/if}>Autres</option>
							</select>
						</td>
						<td>
							<label for="Libelle">{#Niveau#} :</label>
							<select id="Code_niveau" name="Code_niveau">
								<option value="REG" {if $groupe.Code_niveau == "REG"}selected{/if}>REG</option>
								<option value="NAT" {if $groupe.Code_niveau == "NAT"}selected{/if}>NAT</option>
								<option value="INT" {if $groupe.Code_niveau == "INT"}selected{/if}>INT</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label for="ordre">{#Ordre#} :</label>
							<input type="text" size="5" pattern="{literal}[0-9]{1,5}{/literal}" name="ordre"
								value="{$groupe.ordre|default:$lastOrder}" maxlength=5 id="ordre" required />
						</td>
						<td>
							<label for="Groupe">{#Groupe#} :</label>
							<input type="text" name="Groupe" value="{$groupe.Groupe}" maxlength=10 id="Groupe" required />
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<label for="Libelle">{#Nom#} :</label>
							<input type="text" name="Libelle" value="{$groupe.Libelle}" maxlength=40 id="Libelle" required />
						</td>
					</tr>
					<tr>
						{if $groupe.id != -1 && $groupe.id != ''}
							<td>
								<br>
								<br>
								<input type="button" onclick="updateGroupe()" id="updateGroup" name="updateGroup" value="<< {#Modifier#}">
							</td>
							<td>
								<br>
								<br>
								<input type="button" onclick="razGroupe()" id="razGroup" name="razGroup" value="{#Annuler#}">
							</td>
						{else}
							<td colspan=2>
								<br>
								<br>
								<input type="button" onclick="addGroupe()" name="addGroup" value="<< {#Ajouter#}">
							</td>
						{/if}
					</tr>
				</table>
			</div>
		</form>
</div>