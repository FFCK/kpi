<div class="main">
	<form method="POST" action="GestionGrillePoints.php" name="formGrillePoints" id="formGrillePoints">
		<input type='hidden' name='Cmd' id="Cmd" value='' />
		<input type='hidden' name='pointsGrid' id="pointsGridInput" value='{$existingJson}' />

		<div class='blocCenter' style="max-width: 800px; margin: 20px auto;">
			<h2 class='titrePage'>{#Editeur_grille_points_MULTI#}</h2>

			<table width="100%" class='vert'>
				<tr>
					<th class='titreForm' colspan="2">
						<label class='maxWith'>{#Configuration_grille#}</label>
					</th>
				</tr>
				<tr>
					<td colspan="2">
						<p style="margin: 10px 0; font-size: 0.9em;">
							<i>{#Info_grille_points#}</i>
						</p>
					</td>
				</tr>
				<tr>
					<td width="50%">
						<label for="numPositions">{#Nombre_positions#} :</label>
					</td>
					<td>
						<input type="number" name="numPositions" id="numPositions"
							value="{$maxPosition}" min="1" max="50"
							onchange="generateFields()" style="width: 80px;" />
					</td>
				</tr>
			</table>

			<!-- Zone dynamique pour les champs de points -->
			<table width="100%" class='vert' id="pointsFieldsTable" style="margin-top: 20px;">
				<tr>
					<th class='titreForm' colspan="2">
						<label class='maxWith'>{#Points_par_position#}</label>
					</th>
				</tr>
			</table>

			<!-- Champ Default -->
			<table width="100%" class='vert' style="margin-top: 20px;">
				<tr>
					<th class='titreForm' colspan="2">
						<label class='maxWith'>{#Valeur_par_defaut#}</label>
					</th>
				</tr>
				<tr>
					<td width="50%">
						<label for="defaultPoints">{#Points_default#} :</label>
						<br><small><i>{#Info_points_default#}</i></small>
					</td>
					<td>
						<input type="number" name="defaultPoints" id="defaultPoints"
							value="{$defaultValue}" min="0" style="width: 80px;" />
					</td>
				</tr>
			</table>

			<!-- Boutons d'action -->
			<table width="100%" style="margin-top: 20px;">
				<tr>
					<td align="center">
						<button type="button" onclick="generateJson()" class="newBtn" style="padding: 10px 20px; font-size: 1em;">
							{#Generer_JSON#}
						</button>
					</td>
				</tr>
			</table>

			{if $showResult}
			<!-- Résultat JSON -->
			<table width="100%" class='vert' style="margin-top: 20px;">
				<tr>
					<th class='titreForm'>
						<label class='maxWith'>{#JSON_genere#}</label>
					</th>
				</tr>
				<tr>
					<td>
						<textarea id="jsonOutput" readonly style="width: 100%; height: 100px; font-family: monospace; padding: 10px;">{$generatedJson}</textarea>
					</td>
				</tr>
				<tr>
					<td align="center" style="padding: 10px;">
						<button type="button" onclick="copyToClipboard()" class="newBtn" style="padding: 10px 20px; margin-right: 10px;">
							{#Copier_JSON#}
						</button>
						<button type="button" onclick="applyToParent()" class="newBtn" style="padding: 10px 20px;">
							{#Appliquer_au_formulaire#}
						</button>
					</td>
				</tr>
			</table>
			{/if}

			<!-- Bouton retour -->
			<table width="100%" style="margin-top: 20px;">
				<tr>
					<td align="center">
						<a href="GestionCompetition.php">
							<button type="button" class="newBtn" style="padding: 10px 20px;">
								{#Retour#}
							</button>
						</a>
					</td>
				</tr>
			</table>
		</div>
	</form>

	<script>
	// Données de la grille existante
	var gridData = {$gridData|json_encode};
	console.log('DEBUG - gridData from PHP:', gridData);
	console.log('DEBUG - type:', typeof gridData, 'isArray:', Array.isArray(gridData));
	if (!gridData || typeof gridData !== 'object' || Array.isArray(gridData)) {
		gridData = {ldelim}{rdelim};
	}
	console.log('DEBUG - gridData after validation:', gridData);

	// Générer les champs de saisie en fonction du nombre de positions
	function generateFields() {
		var numPositions = parseInt(document.getElementById('numPositions').value);
		if (isNaN(numPositions) || numPositions < 1) {
			numPositions = 10;
		}
		if (numPositions > 50) {
			numPositions = 50;
		}

		var table = document.getElementById('pointsFieldsTable');

		// Sauvegarder les valeurs actuelles des champs avant de reconstruire
		var currentValues = {};
		for (var i = 1; i <= 50; i++) {
			var input = document.getElementById('points_' + i);
			if (input && input.value !== '') {
				currentValues[i] = input.value;
			}
		}

		// Supprimer toutes les lignes sauf l'en-tête
		while (table.rows.length > 1) {
			table.deleteRow(1);
		}

		// Créer les champs pour chaque position
		for (var i = 1; i <= numPositions; i++) {
			var row = table.insertRow(-1);
			row.className = (i % 2 === 0) ? 'pair' : 'impair';

			var cell1 = row.insertCell(0);
			cell1.style.width = '50%';
			cell1.innerHTML = '<label for="points_' + i + '">' + getPositionLabel(i) + ' :</label>';

			var cell2 = row.insertCell(1);
			// Priorité : 1) valeur saisie précédemment, 2) valeur du JSON chargé, 3) vide
			var existingValue = '';
			if (currentValues[i] !== undefined) {
				existingValue = currentValues[i];
			} else if (gridData && gridData[i.toString()]) {
				existingValue = gridData[i.toString()];
			}
			console.log('DEBUG - Position ' + i + ':', 'currentValues[' + i + ']=' + currentValues[i], 'gridData[' + i + ']=' + gridData[i.toString()], 'existingValue=' + existingValue);
			cell2.innerHTML = '<input type="number" name="points_' + i + '" id="points_' + i +
				'" value="' + existingValue + '" min="0" style="width: 80px;" />';
		}
	}

	// Obtenir le libellé de position (1er, 2ème, 3ème, etc.)
	function getPositionLabel(position) {
		var lang = '{$lang}' || 'fr';

		if (lang === 'en') {
			if (position === 1) return '1st place';
			if (position === 2) return '2nd place';
			if (position === 3) return '3rd place';
			return position + 'th place';
		} else {
			if (position === 1) return '1ère place';
			return position + 'ème place';
		}
	}

	// Générer le JSON
	function generateJson() {
		document.getElementById('Cmd').value = 'GenerateJson';
		document.forms['formGrillePoints'].submit();
	}

	// Copier le JSON dans le presse-papiers
	function copyToClipboard() {
		var jsonOutput = document.getElementById('jsonOutput');
		if (jsonOutput) {
			jsonOutput.select();
			document.execCommand('copy');
			alert("{#JSON_copie#}");
		}
	}

	// Appliquer le JSON au formulaire parent (si ouvert depuis GestionCompetition)
	function applyToParent() {
		var jsonOutput = document.getElementById('jsonOutput');
		if (jsonOutput && window.opener && !window.opener.closed) {
			var parentInput = window.opener.document.getElementById('pointsGrid');
			if (parentInput) {
				parentInput.value = jsonOutput.value;
				alert("{#JSON_applique#}");
				window.close();
			} else {
				alert("{#Erreur_application#}");
			}
		} else {
			// Si pas de fenêtre parente, juste copier dans le presse-papiers
			copyToClipboard();
		}
	}

	// Initialiser les champs au chargement de la page
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			generateFields();
		});
	} else {
		generateFields();
	}
	</script>
</div>
