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
											<Option Value="{$arrayEvenement[i].Id}" {$arrayEvenement[i].Selection}>
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
											<Option Value="{$arrayEvenement[i].Id}" {$arrayEvenement[i].Selection}>
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
						<thead>
							<tr>
								<th class="titreForm">Imports SDP ICF</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<a href="{$url_base}/admin/xml_icf_import.php" target="_blank">Import XML DT_PARTIC</a>
								</td>
							</tr>
							<tr>
								<td>
									<a href="{$url_base}/admin/xmlparser.php" target="_blank">Parser le fichier XML</a>
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
									<a href="{$url_base}/admin/ImportPCE.php" target="_blank">Import PCE Extranet FFCK</a>
								</td>
							</tr>
						</tbody>
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