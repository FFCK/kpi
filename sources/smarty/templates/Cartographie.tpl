		&nbsp;(<a href="index.php">Retour</a>)
		<div class="main">
	
			<div class='blocBottom'>
				<div class='titrePage'>Clubs pratiquant le kayak-polo</div>
				<br>
				<div class='blocMap2'>
					<div class='blocMap2' id="map_canvas" style="width: 850px; height: 650px;"></div>
					<form name="formGeocode" onsubmit="return geocode(this.address.value);" enctype="multipart/form-data">
						Adresse : <input type="text" size="70" name="address" value="Adresse, Ville, Pays" onclick="this.value=''" />
						<input type="submit" value="Localiser" />
					</form>			
				</div>
			</div>
			
			<form method="POST" action="Cartographie.php" name="formCartographie" enctype="multipart/form-data">
				<div class='blocBottom' style='text-align: center'>		
					<table width="100%">
						<tr>
							<th class='titreForm'>
								<label>Informations sur le club</label>
							</th>
						</tr>
					</table>
					<label for="club">Club</label>				    
					<br>
					<select name="club" id="club" onChange="handleSelected();">
							<Option Value="">Sélectionner le Club...</Option>
						{section name=i loop=$arrayClub} 
							<Option Value="{$arrayClub[i].Code}" {$arrayClub[i].Selected}>{$arrayClub[i].Libelle}</Option>
						{/section}
					</select>
					<br>
					<label for="postal">Adresse postale</label>
					<br>
					<input type="text" name="postal" maxlength=100 size=60 id="postal"/>
					<br>
					<label for="www">Adresse Internet</label>
					<br>
					<input type="text" name="www" maxlength=100 size=60 id="www"/>
					<br>
					<label for="email">Adresse email</label>
					<br>
					<input type="text" name="email" maxlength=60 size=60 id="email"/>
					<br>
					<label for="coord">Coordonnées GPS (décimales)</label>
					<br>
					<input type="text" name="coord" maxlength=50 size=50 id="coord"/>
					<br>
					(Utiliser le bouton "Localiser" avec l'adresse postale du club, repositionner le pointeur rouge au besoin,<br>
					puis copier/coller les coordonnées GPS dans la zone ci-dessus)
					<br>
					<br>
					<input type="button" onclick="MailUpdat();" name="MailUpdate" value="Demander la mise à jour des informations de mon club">
					<br>
					(vous pouvez joindre à votre message le logo de votre club, au format .gif ou .jpg, maximum : 500 ko)
				</div>
			</form>			
		</div>	  	   
