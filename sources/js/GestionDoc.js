jq = jQuery.noConflict()

jq(document).ready(function () {
	// Tooltips now handled by Bootstrap 5 (bootstrap-tooltip-init.js)

	jq('#evenement').change(function () {
		theTarget = jq(this).val()
		Saison = jq('#laSaison').val()
		jq('#linkEvt1').attr('href', 'FeuilleListeMatchs.php?idEvenement=' + theTarget)
		jq('#linkEvt2').attr('href', '../PdfListeMatchs.php?idEvenement=' + theTarget)
		jq('#linkEvt3').attr('href', 'FeuilleListeMatchsEN.php?idEvenement=' + theTarget)
		jq('#linkEvt4').attr('href', '../PdfListeMatchsEN.php?idEvenement=' + theTarget)
		jq('#linkEvt5').attr('href', '../PdfQrCodes.php?Evt=' + theTarget + '&S=' + Saison)
		jq('#linkEvt6').attr('href', '../PdfQrCodeApp.php?Evt=' + theTarget)
	})


})


