		&nbsp;(<a href="GestionCompetition.php">{#Retour#}</a>)
	
		<div class="main">
			<form method="POST" action="GestionParamUser.php" name="formParamUser" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<div class='blocRight Right3'>
					<table class='tableau2' width=100%>
						<tr>
							<th class='titreForm' colspan=4 width='100%'>
								<label>{#Mes_parametres#}</label>
							</th>
						</tr>
						<tr>
							<td width=25%><b>{#Nom#} :</b></td>
							<td width=25%>{$UNom}</td>
							<td width=25%><b>{#Prenom#} :</b></td>
							<td width=25%>{$UPrenom}</td>
						</tr>
						<tr>
							<td><b>{#Licence#} :</b></td>
							<td>{$UCode}</td>
							<td><b>{#Saison#} :</b></td>
							<td>{$USaison}</td>
						</tr>
						<tr>
							<td><b>Club :</b></td>
							<td colspan=3>{$UClub}</td>
						</tr>
                        {if $UCode <= 2000000}
                            <tr>
                                <td width=25%><b>Pagaie EV :</b></td>
                                <td width=25%>{$UPagaie_EVI}</td>
                                <td width=25%><b>Pagaie Mer :</b></td>
                                <td width=25%>{$UPagaie_MER}</td>
                            </tr>
                            <tr>
                                <td width=25%><b>Pagaie EC :</b></td>
                                <td width=25%>{$UPagaie_ECA}</td>
                            </tr>
                        {/if}
						<tr>
							<th class='titreForm' colspan=4 width='100%'>
								<label>{#Changer_parametres#}</label>
							</th>
						</tr>
						<tr>
							<td colspan=4><b>Mission :</b><input type="text" name="Fonction" value="{$UFonction}" maxlength=40 /></td>
						</tr>
						<tr>
							<td colspan=2><b>E-mail :</b><input type="text" name="Mail1" value="{$UMail}" onChange="detectChangeMail()" maxlength=40 /></td>
							<td colspan=2><b>{#Confirmer#} e-mail :</b><input type="text" name="Mail2" value="" maxlength=40 /></td>
						</tr>
						<tr>
							<td colspan=2><b>{#Telephone#} :</b><input type="text" name="Tel" value="{$UTel}" maxlength=15 /></td>
							<td colspan=2></td>
						</tr>
						<tr>
							<td colspan=4>
								<input type="button" onclick="updateParamUser();" name="UpdateParamUser" value="{#Modifier#}">
							</td>
						</tr>
						<tr>
							<th class='titreForm' colspan=4 width='100%'>
								<label>{#Changer_mdp#}</label>
							</th>
						</tr>
						<tr>
							<td colspan=4><b>{#Ancien#} {#Mot_de_passe#} :</b><input type="password" name="Pass1" value="" maxlength=40 /></td>
						</tr>
						<tr>
							<td colspan=4><b>{#Nouveau#} {#Mot_de_passe#} :</b><input type="password" name="Pass2" value="" maxlength=40 /></td>
						</tr>
						<tr>
							<td colspan=4><b>{#Confirmer#} {#Mot_de_passe#} :</b><input type="password" name="Pass3" value="" maxlength=40 /></td>
						</tr>
						<tr>
							<td colspan=4>
								<input type="button" onclick="updatePassword();" name="UpdatePassword" value="{#Modifier#}">
							</td>
						</tr>
					</table>
		        </div>
			</form>			
		</div>	  	   
