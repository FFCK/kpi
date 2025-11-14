		<div class="main">
			<form method="POST" action="GestionOperations.php" name="formOperations" id="formOperations" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value='' />

				<div class='titrePage'>Opérations (Attention, sensible !!!)</div>

				<div class='blocLeft'>
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
					<br>
					<br>
					<br>
					<br>
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
					{if $profile <= 2}
						<br>
						<hr>
						<div align='center' class='rouge'><i>Profil 1 - 2</i></div>
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
					{/if}
				</div>
				<div class='blocRight'>
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
					<table width="100%">
						<thead>
							<tr>
								<th class="titreForm">Imports PCE...</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<a href="{$url_base|default:''}/admin/ImportPCE.php" target="_blank">Import PCE Extranet FFCK</a>
								</td>
							</tr>
						</tbody>
					</table>
					{if $profile <= 4}
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
						<br>
					{/if}
					{if $user == '42054' or $user == '63155'}
						<hr>
						<div align='center' class='rouge'><i>User Laurent/Eric</i></div>
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
					{if $profile <= 2}
						<hr>
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
					{/if}
				</div>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
			</form>

</div>