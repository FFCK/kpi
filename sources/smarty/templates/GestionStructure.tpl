		&nbsp;(<a href="GestionEquipe.php">{#Retour#}</a>)
		<div class="main">
	
			<div class='blocLeft Left2'>
				<div class='titrePage'>{#Structures_pratiquant_le_kayakpolo#}</div>
				<br>
				<div class='blocMap'>
					<div id="map_canvas" style="width: 620px; height: 550px"></div>
					<form name="formGeocode" onsubmit="return geocode(this.address.value);" enctype="multipart/form-data">
						<input type="text" size="50" name="address" id="address" placeholder="{#Adresse_Ville_Pays#}" />
						<input type="button" value="{#Localiser#}" onclick="codeAddress();" />
						{if $lang == 'en'}
							<br><br>If your club is not on the map, send coordinates
							<br>(postal address, e-mail, website, GPS coordinates, logo)
							<br>to laurent@poloweb.org.
                        {else}
							<br><br>Si votre club n'apparait pas sur la carte, transmettez ses coordonnées
							<br>(adresse postale, email, site internet, coordonnées GPS, logo)
							<br>à l'adresse laurent@poloweb.org.
						{/if}

					</form>			
				</div>
			</div>
			
			{if $profile <=3 or $user == '229824' or $user == '115989'}
			<form method="POST" action="GestionStructure.php" name="formStructure" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<div class='blocRight Right2'>		
					<table width=100%>
						<tr>
							<th class='titreForm'>
								<label>{#Localiser#} {#stuctures#}</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="club">Club: </label>				    
								<select name="club" id="club" onChange="handleSelected();">
                                    <Option Value="">{#Selectionnez#}...</Option>
									{section name=i loop=$arrayClub} 
										<Option Value="{$arrayClub[i].Code}" {$arrayClub[i].Selected}>{$arrayClub[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label for="postal">{#Adresse_postale#} :<br>(ex: 27 rue du Bout du Monde - 99000 La Fin)</label>
								<input type="text" name="postal" maxlength=100 id="postal"/>
							</td>
						</tr>
						<tr>
							<td>
								<label for="www">{#Site_internet#} :<br>(ex: http://www.monsite.fr)</label>
								<input type="text" name="www" maxlength=60 id="www"/>
							</td>
						</tr>
						<tr>
							<td>
                                <label for="email">{#Adresse_email#} :<br>(éviter si possible les adresses personnelles)</label>
								<input type="text" name="email" maxlength=40 id="email"/>
							</td>
						</tr>
						<tr>
							<td>
								<label for="coord">{#Coordonnees_geographiques#} lat,long<br>(ex : 48.856614, 2.3522219)</label>
								<input type="text" name="coord" maxlength=60 id="coord"/>
								<input type="hidden" name="coord2" maxlength=60 id="coord2"/>
							</td>
						</tr>
					<!--	<tr>
							<td>
								<label for="coord2">Coordonnées terrain :</label>
								<input type="text" name="coord2" maxlength=60 id="coord2" style="width:65%"/>
								<input type="button" onclick="document.forms['formGeocode'].elements['address'].value = document.forms['formStructure'].elements['coord2'].value;document.forms['formGeocode'].submit;" name="localiser" value="Localiser" style="width:30%">
							</td>
						</tr>-->
						<tr>
							<td>
								<br>
								<input type="button" onclick="UpdatClub();" name="UpdateClub" value="{#MAJ#}">
							</td>
						</tr>
					</table>
					<br>
					<br>
					{if $profile <=2}
					<table width=100%>
						<tr>
							<th colspan=2 class='titreForm'>
								<label>Ajouter un Comité Départemental / un Pays</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="comiteReg">Comité Régional : </label>
								<select name="comiteReg" id="comiteReg">
										<Option Value="">Sélectionner le Comité Régional d'appartenance...</Option>
									{section name=i loop=$arrayComiteReg} 
										<Option Value="{$arrayComiteReg[i].Code}">{$arrayComiteReg[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td width=15%>
								<label for="codeCD">Code :</label>
								<input type="text" name="codeCD" maxlength=5 id="codeCD"/>
							</td>
							<td>
								<label for="libelleCD">Nouveau comité départemental / pays :</label>
								<input type="text" name="libelleCD" maxlength=50 id="libelleCD"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<input type="button" onclick="AddCD();" name="addCD" value="Ajouter">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th colspan=2 class='titreForm'>
								<label>Ajouter un Club / une Structure</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="comiteDep">Comité Départemental / Pays : </label>
								<select name="comiteDep" id="comiteDep">
										<Option Value="">Sélectionner le CD / le pays d'appartenance...</Option>
									{section name=i loop=$arrayComiteDep} 
										<Option Value="{$arrayComiteDep[i].Code}">{$arrayComiteDep[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="ClubInt">Structures internationales déjà existantes</label>
								<select name="ClubInt" id="ClubInt">
										<Option Value="">Vérifier que la nouvelle structure n'existe pas déjà !</Option>
									{section name=i loop=$arrayClubInt} 
										<Option Value="{$arrayClubInt[i].Code}">{$arrayClubInt[i].Libelle}</Option>
									{/section}
								</select>
							</td>
						</tr>
						<tr>
							<td width=15%>
								<label for="codeClub">Code :</label>
								<input type="text" name="codeClub" maxlength=5 id="codeClub"/>
							</td>
							<td>
								<label for="libelleClub">Nouveau club / nouvelle structure :</label>
								<input type="text" name="libelleClub" maxlength=50 id="libelleClub"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="postal2">Adresse postale :</label>
								<input type="text" name="postal2" maxlength=100 id="postal2"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="www2">Adresse Internet :</label>
								<input type="text" name="www2" maxlength=60 id="www2"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="email2">Adresse email :</label>
								<input type="text" name="email2" maxlength=40 id="email2"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="coord2">Coordonnées club :</label>
								<input type="text" name="coord2" maxlength=60 id="coord2"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="libelleEquipe2"><b>Nouvelle Equipe :</b></label>
								<img title="ATTENTION ! Cliquez pour plus d'info." 
								alt="ATTENTION ! Cliquez pour plus d'info." 
								src="../img/b_help.png" 
								onclick="alert('ATTENTION !\n Respectez bien le formalisme :\n \n -Sélectionnez le club d\'appartenance avant tout (+CR +CD),\n -Nom d\'équipe en minuscule, première lettre en majuscule,\n -Un espace avant le numéro d\'ordre et avant la catégorie\n -Numéro d\'ordre obligatoire, en chiffre romain : I II III IV\n -Catégorie féminine avec \' F\' (\' Ladies\' ou \' Women\' pour les équipes étrangères)\n -Catégorie jeunes avec \' JF\' ou \' JH\' (masculine ou mixte)\n -Catégorie -21 ans avec \' -21\' (\' U21\' pour les équipes étrangères)\n \n Exemples :\n Acigné II, Acigné I F, Acigné JH, Belgium U21 Women, Keistad Ladies...')" />
								<input type="text" name="libelleEquipe2" maxlength=40 id="libelleEquipe2" />
							</td>
						</tr>
						{if $codeCompet != ''}  
						<tr>
							<td>
								<input type="checkbox" name="affectEquipe" id="affectEquipe" value="{$codeCompet}">
							</td>
							<td>
								Affecter l'équipe à {$codeCompet}
							</td>
						</tr>
						{/if}
						<tr>
							<td colspan=2>
								<input type="button" onclick="AddClub();" name="addClub" value="Ajouter">
							</td>
						</tr>
					</table>
					{/if}		
					<br>
				</div>
			</form>			
			{/if}		
		</div>	  	   
