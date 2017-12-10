		<div class="main">
			<form method="POST" action="GestionMaintenance.php" name="formCompet" enctype="multipart/form-data">
						<input type='hidden' name='Cmd' Value=''/>
						<input type='hidden' name='ParamCmd' Value=''/>

				<div class='blocLeft'>
					<h3 class='titrePage'>Groupes de Compétitions</h3>
					<br>
					<div class='blocTable' id='blocCompet'>
						<table class='tableau' id='tableCompet'>
							<thead> 
								<tr>
									<th>Id</th>
									<th title="Niveau">Niv.</th>
									<th title="Référence">Groupe</th>
									<th title="Titre">Libelle</th>
								</tr>
							</thead> 
							
							<tbody>
								{section name=i loop=$arrayGroupCompet} 
									<tr class='{cycle values="impair,pair"} {$arrayGroupCompet[i].StdOrSelected}'>
										<td>{$arrayGroupCompet[i].id}</td>
										<td>{$arrayGroupCompet[i].Code_niveau}</td>
										<td>{$arrayGroupCompet[i].Groupe}</td>
										<td>{$arrayGroupCompet[i].Libelle}</td>
									</tr>
								{/section}
							</tbody>
						</table>
					</div>
					<div id="fileupload-zone">
						<div id="fileuploader">Upload</div>
					</div>
				</div>
		        
  				<div class='blocRight'>
					{if $profile <= 2 && $AuthModif == 'O'}
					<br>
					<br>
					<hr>
					<div align='center' class='rouge'><i>Profils 1 - 2</i></div>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Changer de saison (publique)</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="saisonActive"><b>Saison active :</b></label>
								<select name="saisonActive" onChange="activeSaison()">
									{section name=i loop=$arraySaison} 
										<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Etat=='A'}selected{/if}>{$arraySaison[i].Code}{if $arraySaison[i].Etat=='A'} (Active){/if}</Option>
									{/section}
								</select>
							</td>
						</tr>
					</table>
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Ajouter une saison</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="newSaison">Saison :</label>
								<input type="text" name="newSaison">
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>
								<label for="newSaison">Debut National</label>
								<input type="text" class='date' name="newSaisonDN" onfocus="displayCalendar(document.forms[0].newSaisonDN,'dd/mm/yyyy',this)" >
							</td>
							<td>
								<label for="newSaisonFN">Fin National</label>
								<input type="text" class='date' name="newSaisonFN" onfocus="displayCalendar(document.forms[0].newSaisonFN,'dd/mm/yyyy',this)" >
							</td>
						</tr>
						<tr>
							<td>
								<label for="newSaisonDI">Debut International</label>
								<input type="text" class='date' name="newSaisonDI" onfocus="displayCalendar(document.forms[0].newSaisonDI,'dd/mm/yyyy',this)" >
							</td>
							<td>
								<label for="newSaisonFI">Fin International</label>
								<input type="text" class='date' name="newSaisonFI" onfocus="displayCalendar(document.forms[0].newSaisonFI,'dd/mm/yyyy',this)" >
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<br>
								<input type="button" name="AjoutSaison" onclick="AddSaison();" value="Créer">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Fusionner des licenciés</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="FusionSource">Source (sera supprimé)</label>
								<input type="hidden" name="numFusionSource" id="numFusionSource">
								<input type="text" name="FusionSource" size=40 id="FusionSource">
							</td>
						</tr>
						<tr>
							<td>
								<label for="FusionCible">Cible (sera conservé)</label>
								<input type="hidden" name="numFusionCible" id="numFusionCible">
								<input type="text" name="FusionCible" size=40 id="FusionCible">
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="FusionJoueurs" id="FusionJoueurs" value="Fusionner">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Renommer une équipe</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="RenomSource">Source (ancien nom)</label>
								<input type="hidden" name="numRenomSource" id="numRenomSource">
								<input type="text" name="RenomSource" size=40 id="RenomSource">
							</td>
						</tr>
						<tr>
							<td>
								<label for="RenomCible">Cible (nouveau nom)</label>
								<input type="text" name="RenomCible" size=40 id="RenomCible">
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="RenomEquipe" id="RenomEquipe" value="Renommer">
							</td>
						</tr>
					</table>
					{/if}
					{if $profile <= 4}
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Verrou saisons précédentes</label>
							</th>
						</tr>
						<tr>
							<td colspan=2 align="center">
								<label for="AuthSaison">Verrou</label>
								{if $AuthSaison == 'O'}<b><i>INACTIF</i></b>
								{else}<b>ACTIF</b>
								{/if}
							</td>
						</tr>
						{if $profile <= 2}
						<tr>
							<td>
								<input type="button" name="ChangeAuthSaison" id="ChangeAuthSaison" onclick="changeAuthSaison()" value="Changer">
							</td>
						</tr>
						{/if}
						<tr>
							<td colspan=2 align="center">
								{if $AuthModif == 'O'}<b><i>Compétitions déverrouillées.</i></b>
								{else}<b>Par mesure de sécurité, les compétitions des saisons précédentes sont verrouillées.</b>
								{/if}
							</td>
						</tr>
					</table>
					<br>
					{/if}
					{if $user == '42054'}
					<hr>
					<div align='center' class='rouge'><i>User Laurent</i></div>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Tester un autre profil</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="profilTest">Tester un autre profil</label>
								<select name="profilTest" onChange="submit();">
									<option value="1" {if $profile == 1}Selected{/if}>1 - Webmaster / Président</option>
									<option value="2" {if $profile == 2}Selected{/if}>2 - Bureau</option>
									<option value="3" {if $profile == 3}Selected{/if}>3 - Resp. Compétition</option>
									<option value="4" {if $profile == 4}Selected{/if}>4 - Resp. Poule</option>
									<option value="5" {if $profile == 5}Selected{/if}>5 - Délégué fédéral</option>
									<option value="6" {if $profile == 6}Selected{/if}>6 - Organisateur Journée</option>
									<option value="7" {if $profile == 7}Selected{/if}>7 - Resp. Club / Equipe</option>
									<option value="8" {if $profile == 8}Selected{/if}>8 - Consultation simple</option>
									<option value="9" {if $profile == 9}Selected{/if}>9 - Table de Marque</option>
									<option value="10" {if $profile == 10}Selected{/if}>10 - Non utilisé</option>
								</select>
							</td>
						</tr>
					</table>
					{/if}
		        </div>
			</form>			
		</div>
	