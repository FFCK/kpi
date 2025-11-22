		<div class="main">
			<form method="POST" action="GestionOperations.php" name="formOperations" id="formOperations" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value='' />
				<input type='hidden' name='json_data' id='json_data' Value=''/>
				<input type='hidden' name='Control' id='Control' Value=''/>

				<div class='titrePage'>Opérations (Attention, sensible !!!)</div>

				<div class='blocLeft'>
					<table width="100%">
						<tr>
							<td colspan=2>
								<span id="json_msg">
									{$msg_json}
								</span>
								<br>
								{section name=i loop=$arrayinfo}
									{$arrayinfo[i]}<BR>
								{/section}
							</td>
						</tr>
					</table>
					<table width="100%">
						<thead>
							<tr>
								<th class="titreForm">
									Upload d'images
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<label for="imageType">Type d'image :</label>
									<br>
									<select name="imageType" id="imageType" onchange="updateImageFields()">
										<option value="">-- Sélectionnez un type --</option>
										<option value="logo_competition">Logo compétition (JPG, 1000x1000 max)</option>
										<option value="bandeau_competition">Bandeau compétition (JPG, 2480x250 max)</option>
										<option value="sponsor_competition">Sponsor compétition (JPG, 2480x250 max)</option>
										<option value="logo_club">Logo club (PNG, 200x200 max)</option>
										<option value="logo_nation">Logo nation (PNG, 200x200 max)</option>
									</select>
									<br><br>

									<div id="competitionFields" style="display:none;">
										<label for="codeCompetition">Code compétition :</label>
										<input type="text" name="codeCompetition" id="codeCompetition" size="10" maxlength="20">
										<br>
										<label for="saison">Saison :</label>
										<input type="text" name="saison" id="saison" size="4" maxlength="4" placeholder="2024">
										<br><br>
									</div>

									<div id="clubFields" style="display:none;">
										<label for="numeroClub">Numéro club :</label>
										<input type="text" name="numeroClub" id="numeroClub" size="10" maxlength="10">
										<br><br>
									</div>

									<div id="nationFields" style="display:none;">
										<label for="codeNation">Code nation (ex: FRA) :</label>
										<input type="text" name="codeNation" id="codeNation" size="3" maxlength="3" style="text-transform: uppercase;">
										<br><br>
									</div>

									<div id="filenamePreview" style="margin-bottom: 10px; padding: 5px; background-color: #f0f0f0; border-radius: 3px; display:none;">
										<strong>Nom du fichier :</strong> <span id="previewFilename">-</span>
									</div>

									<label for="imageFile">Fichier image :</label>
									<input type="file" name="imageFile" id="imageFile" accept="image/jpeg,image/jpg,image/png">
									<br><br>

									<input type="submit" name="uploadImage" id="uploadImageBtn" value="Uploader l'image" disabled>
								</td>
							</tr>
						</tbody>
					</table>
					<br>
					<table width="100%">
						<thead>
							<tr>
								<th class="titreForm">
									Renommer une image existante
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<label for="renameImageType">Type d'image :</label>
									<br>
									<select name="renameImageType" id="renameImageType">
										<option value="">-- Sélectionnez un type --</option>
										<option value="logo_competition" {if isset($duplicate_file.type) && $duplicate_file.type == 'logo_competition'}selected{/if}>Logo compétition</option>
										<option value="bandeau_competition" {if isset($duplicate_file.type) && $duplicate_file.type == 'bandeau_competition'}selected{/if}>Bandeau compétition</option>
										<option value="sponsor_competition" {if isset($duplicate_file.type) && $duplicate_file.type == 'sponsor_competition'}selected{/if}>Sponsor compétition</option>
										<option value="logo_club" {if isset($duplicate_file.type) && $duplicate_file.type == 'logo_club'}selected{/if}>Logo club</option>
										<option value="logo_nation" {if isset($duplicate_file.type) && $duplicate_file.type == 'logo_nation'}selected{/if}>Logo nation</option>
									</select>
									<br><br>

									<label for="currentImageName">Nom du fichier actuel (avec extension) :</label>
									<input type="text" name="currentImageName" id="currentImageName" size="40" placeholder="Ex: L-CDM-2024.jpg" value="{$duplicate_file.filename|default:''}">
									<br><br>

									<label for="newImageBaseName">Nouveau nom (sans extension) :</label>
									<input type="text" name="newImageBaseName" id="newImageBaseName" size="40" placeholder="Ex: ancien-logo">
									<br>
									<small style="color: #666;">L'extension sera automatiquement conservée</small>
									<br><br>

									<div id="newImageNamePreview" style="margin-bottom: 10px; padding: 5px; background-color: #e8f5e9; border-radius: 3px; display:none;">
										<strong>Nouveau nom complet :</strong> <span id="newImageNameDisplay">-</span>
									</div>

									<input type="hidden" name="newImageName" id="newImageName" value="">
									<input type="submit" name="renameImage" id="btnRenameImage" value="Renommer l'image" disabled>
								</td>
							</tr>
						</tbody>
					</table>
					<hr>
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
								<label>Fusion automatique de licenciés non fédéraux</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<p style="color: #666; font-size: 0.9em; margin: 10px 0;">
									Cette fonction fusionne automatiquement les licenciés ayant :
									<br>- Un numéro de licence > 2000000 (licences non fédérales)
									<br>- Les mêmes Nom, Prénom et Club
									<br><br>
									Le licencié conservé sera celui ayant la meilleure cohérence de données (numéro ICF, date de naissance valide, qualification d'arbitre, saison la plus récente).
								</p>
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="FusionAutoLicenciesNonFederaux" id="FusionAutoLicenciesNonFederaux" value="Lancer la fusion automatique" style="background-color: #ff9800; color: white; font-weight: bold;">
							</td>
						</tr>
					</table>
					<hr>
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
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Fusionner deux équipes</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="FusionEquipeSource">Source (sera supprimé)</label>
								<input type="hidden" name="numFusionEquipeSource" id="numFusionEquipeSource">
								<input type="text" name="FusionEquipeSource" size=40 id="FusionEquipeSource">
							</td>
						</tr>
						<tr>
							<td>
								<label for="FusionEquipeCible">Cible (sera conservé)</label>
								<input type="hidden" name="numFusionEquipeCible" id="numFusionEquipeCible">
								<input type="text" name="FusionEquipeCible" size=40 id="FusionEquipeCible">
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="FusionEquipes" id="FusionEquipes" value="Fusionner">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Changer une équipe de club</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="DeplaceEquipeSource">Equipe</label>
								<input type="hidden" name="numDeplaceEquipeSource" id="numDeplaceEquipeSource">
								<input type="text" name="DeplaceEquipeSource" size=40 id="DeplaceEquipeSource">
							</td>
						</tr>
						<tr>
							<td>
								<label for="DeplaceEquipeCible">Club cible</label>
								<input type="hidden" name="numDeplaceEquipeCible" id="numDeplaceEquipeCible">
								<input type="text" name="DeplaceEquipeCible" size=40 id="DeplaceEquipeCible">
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="DeplaceEquipe" id="DeplaceEquipe" value="Déplacer">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Changer un code compétition</label>
							</th>
						</tr>
						<tr>
							<td width=50%>
								<label for="ChangeCodeRecherche">Code recherché</label>
								<input type="text" name="ChangeCodeRecherche" id="ChangeCodeRecherche" class='codecompet' size=40 placeholder="Chercher">
								</td>
								<td width=50%>
								<label for="changeCodeSource">Code à changer</label>
								<input type="text" name="changeCodeSource" id="changeCodeSource" readonly>
							</td>
						</tr>
						<tr>
							<td>
								<label for="changeCodeCible">Code cible</label>
								<input type="text" name="changeCodeCible" id="changeCodeCible" class='codecompet' size=40>
							</td>
							<td>
								<input type='checkbox' name='changeCodeExists' id='changeCodeExists' value='Exists'>
								Existe déjà !
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<div align='center'>
									<input type='checkbox' name='changeCodeAllSeason' id='changeCodeAllSeason' value='All'>
									Toutes saisons
								</div>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<input type="button" name="ChangeCodeBtn" id="ChangeCodeBtn" value="Changer">
							</td>
						</tr>
					</table>
					<br>
					<hr>
					<table width="100%">
						<thead>
							<tr>
								<th class="titreForm">
									Export événement
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<label for="evenementExport">{#Filtre_evenement#}</label>
									<br>
									<select name="evenementExport" id="evenementExport">
										{section name=i loop=$arrayEvenement}
											{assign var="evt_libelle" value=$arrayEvenement[i].Libelle}
											<Option Value="{$arrayEvenement[i].Id}" {$arrayEvenement[i].Selection|default:''}>
											{$arrayEvenement[i].Id} - {$smarty.config.$evt_libelle|default:$evt_libelle}
											</Option>
										{/section}
									</select>
									<br>
									<input type="button" onclick="ExportEvt();" name="btnExportEvt" value="Exporter">
								</td>
							</tr>
						</tbody>
					</table>
					<table width="100%">
						<thead>
							<tr>
								<th class="titreForm">
									Import événement
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<label for="evenementImport">{#Filtre_evenement#}</label>
									<br>
									<select name="evenementImport" id="evenementImport">
										{section name=i loop=$arrayEvenement}
											{assign var="evt_libelle" value=$arrayEvenement[i].Libelle}
											<Option Value="{$arrayEvenement[i].Id}" {$arrayEvenement[i].Selection|default:''}>
											{$arrayEvenement[i].Id} - {$smarty.config.$evt_libelle|default:$evt_libelle}
											</Option>
										{/section}
									</select>
									<br>
									<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
									<input type="file" id="jsonUpload" name="jsonUpload" accept="text/json" />
									<br>
									<input type="button" onclick="ImportEvt();" name="btnImportEvt" value="Importer">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class='blocRight'>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>TV control panel</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<a href="../kptv.php" target="_blank">TV control panel</a>
							</td>
						</tr>
					</table>
					<table width="100%">
						<thead>
							<tr>
								<th class="titreForm">Worker Management</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<a href="{$url_base|default:''}/live/event.php" target="_blank">Event Cache Worker</a>
									<br>
									<small style="color: #666;">Gestion automatique des caches pour les événements live</small>
								</td>
							</tr>
						</tbody>
					</table>
					<hr>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>{#Gestion_des_groupes#}</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<a href="GestionGroupe.php">{#Gestion_des_groupes#}</a>
							</td>
						</tr>
					</table>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Mise à jour licenciés (Imports PCE)</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<input type="button" name="importPCE2" id="importPCE2" value="Mise à jour des licenciés (base fédérale J-1)">
							</td>
						</tr>
					</table>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Purge des fichiers cache</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<input type="button" name="PurgeCache" id="PurgeCache" value="Purger les fichiers cache obsolètes">
								<br>
								<small style="color: #666;">Supprime les fichiers de match (> 1 an) et d'événement (> 2 ans)</small>
							</td>
						</tr>
					</table>
					{* {if $profile <= 2}
						<table width="100%">
							<tr>
								<th class='titreForm' colspan=2>
									<label>Mise à jour calendrier fédéral</label>
								</th>
							</tr>
							<tr>
								<td colspan=2>
									<label for="calendrier">Calendrier fédéral :</label>
									<input type="file" name="calendrier">
									<br>
									<input type="submit" name="uploadCalendrierCsv" value="Importation Calendrier (calendrier.csv)">
								</td>
							</tr>
						</table>
					{/if} *}
					{* {if $profile <= 3}
						<table width="100%">
							<tr>
								<th class='titreForm' colspan=2>
									{if $production eq 'P'}
										<label>Import depuis mode local</label>
									{else}
										<label>Import vers mode local</label>
									{/if}
								</th>
							</tr>
							<tr>
								<td colspan=2>
									<label for="lstEvent">Liste des Evénements à Importer</label>
									<br>
									<input type="text" name="lstEvent" maxlength=20 size=10 id="lstEvent"/>
									<img title="Numéros d'évènements, séparés par une virgule. Vous devez avoir les autorisations adéquates."
										alt="Numéros d'évènements, séparés par une virgule. Vous devez avoir les autorisations adéquates."
										src="../img/b_help.png"
										onclick="alert('Numéros d\'évènements, séparés par une virgule. Vous devez avoir les autorisations adéquates.')" />
									<br>
									{if $production eq 'P'}
										<input type="button" name="btnImportServer" id="btnImportServer" value="Importation (WAMP ==> KPI)">
									{else}
										<br>
										<label for="user">Identifiant KPI</label>
										<input type="text" name="user" maxlength=20 id="user" autocomplete="off" />
										<br>
										<label for="pwd">Mot de passe KPI</label>
										<input type="password" name="pwd" maxlength=20 id="pwd" autocomplete="off" />
										<br>
										<input type="button" name="btnImport" id="btnImport" value="Importation (KPI ==> WAMP)">
									{/if}
								</td>
							</tr>
						</table>
					{/if} *}
					<hr>
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
										<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Etat=='A'}selected{/if}>
											{$arraySaison[i].Code}{if $arraySaison[i].Etat=='A'} (Active){/if}</Option>
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
								<input type="tel" name="newSaison" size="4" maxlength="4">
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>
								<label for="newSaison">Debut National</label>
								<input type="text" class='date' name="newSaisonDN"
									onfocus="displayCalendar(document.forms[0].newSaisonDN,{if $lang=='en'}'yyyy-mm-dd'{else}'dd/mm/yyyy'{/if},this)">
							</td>
							<td>
								<label for="newSaisonFN">Fin National</label>
								<input type="text" class='date' name="newSaisonFN"
									onfocus="displayCalendar(document.forms[0].newSaisonFN,{if $lang=='en'}'yyyy-mm-dd'{else}'dd/mm/yyyy'{/if},this)">
							</td>
						</tr>
						<tr>
							<td>
								<label for="newSaisonDI">Debut International</label>
								<input type="text" class='date' name="newSaisonDI"
									onfocus="displayCalendar(document.forms[0].newSaisonDI,{if $lang=='en'}'yyyy-mm-dd'{else}'dd/mm/yyyy'{/if},this)">
							</td>
							<td>
								<label for="newSaisonFI">Fin International</label>
								<input type="text" class='date' name="newSaisonFI"
									onfocus="displayCalendar(document.forms[0].newSaisonFI,{if $lang=='en'}'yyyy-mm-dd'{else}'dd/mm/yyyy'{/if},this)">
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<br>
								<input type="button" name="AjoutSaison" onclick="AddSaison();" value="Créer">
							</td>
						</tr>
					</table>
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Copier les Responsables de Compétition (RC)</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="saisonSourceRc">Saison source :</label>
								<select name="saisonSourceRc" id="saisonSourceRc">
									<option value="">-- Sélectionnez --</option>
									{section name=i loop=$arraySaison}
										<option value="{$arraySaison[i].Code}">{$arraySaison[i].Code}</option>
									{/section}
								</select>
							</td>
							<td>
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
							<td colspan=2>
								<br>
								<input type="button" name="CopyRcBtn" id="CopyRcBtn" onclick="CopyRc();" value="Copier les RC">
								<br>
								<small style="color: #666;">Cette opération copie tous les RC de la saison source vers la saison cible (les doublons sont ignorés).</small>
							</td>
						</tr>
					</table>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>{#Verrou_saisons_precedentes#}</label>
							</th>
						</tr>
						<tr>
							<td colspan=2 align="center">
								{if $AuthSaison == 'O'}
									<h2><i>{#Ouvert#}</i></h2>
								{else}
									<h2>{#Verrouille#}</h2>
								{/if}
							</td>
						</tr>
						{if $profile <= 2}
							<tr>
								<td>
									<input type="button" name="ChangeAuthSaison" id="ChangeAuthSaison" onclick="changeAuthSaison()"
										value="{#Changer#}">
								</td>
							</tr>
						{/if}

					</table>
					<table width="100%">
						<thead>
							<tr>
								<th class="titreForm">API v2</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<a href="{$url_base|default:''}/api2/doc" target="_blank">REST API</a>
								</td>
							</tr>
						</tbody>
					</table>
					<table width="100%">
						<thead>
							<tr>
								<th class="titreForm">Imports SDP ICF</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<a href="{$url_base|default:''}/admin/xml_icf_import.php" target="_blank">Import XML DT_PARTIC</a>
								</td>
							</tr>
							<tr>
								<td>
									<a href="{$url_base|default:''}/admin/xmlparser.php" target="_blank">Parser le fichier XML</a>
								</td>
							</tr>
						</tbody>
					</table>
					<hr>
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
				</div>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
			</form>

</div>