	<div class="main">
		<div class="ImportPCE">
			<form method="POST" action="ImportPCE.php" name="ImportPCE" id="ImportPCE" enctype="multipart/form-data">

				{if $redirect_notice}
					<div style="padding: 20px; margin: 20px 0; background-color: #ffffcc; border: 2px solid #ffcc00; border-radius: 5px;">
						<h3 style="margin-top: 0; color: #cc6600;">⚠️ Fonctionnalités déplacées</h3>
						<p>{$redirect_notice}</p>
					</div>
				{/if}

				<table>
					<tr>
						<td>
							{section name=i loop=$arrayinfo}
								{$arrayinfo[i]}<BR>
							{/section}
							<br>
						</td>
					</tr>
				</table>

				{if $profile <= 1}
					<h2>Upload images</h2>
					<b>Paramètres upload (avant de sélectionner l'image) :</b><br />
					Type:<select id="TypeImg" name="TypeImg">
						<option value="L-">Logo</option>
						<option value="S-">Sponsor</option>
					</select>
					Compétition:<select id="CompImg" name="CompImg">
					{section name=i loop=$arrayGroupes}
						<option value="{$arrayGroupes[i].Groupe}-">{$arrayGroupes[i].Libelle}</option>
					{/section}
					</select>
					Saison:<input class="court" type="text" size="4" id="SaisonImg" name="SaisonImg" value="{$smarty.now|date_format:'%Y'}">
					<input type="button" id="validNomImg" value="Valider">
					<form action="upload.php" class="dropzone" id="my-awesome-dropzone">
						<label for="titre">Nom du fichier (max. 50 caractères) :</label>
						<input type="text" name="titre" placeholder="Ex: L-N1H-2014.jpg" id="titre" /><br />
						<label for="dest">Dossier de destination :</label>
						<select id="dest" name="dest">
							<option value="logo">Logo</option>
							<option value="Pays">Pays</option>
						</select>
					</form>
				{/if}
			</form>
		</div>
	</div>
