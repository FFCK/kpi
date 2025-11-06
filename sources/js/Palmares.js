$(document).ready(function() {
	// Tooltips now handled by Bootstrap 5 (bootstrap-tooltip-init.js)

	vanillaAutocomplete('#choixEquipe', 'Autocompl_equipe.php', {
		width: 350,
		maxResults: 50,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.libelle,
		onSelect: function(item) {
			if (item) {
				$("#choixEquipe").val(item.libelle);
				$("#formPalmares").attr('action', 'Palmares.php?Equipe=' + item.numero);
			}
		}
	});
});
