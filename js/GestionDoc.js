$(document).ready(function() {
	$("*").tooltip({
		showURL: false
	});
    
    $('#evenement').change(function(){
		theTarget = $(this).val();
		Saison = $('#laSaison').val();
		$('#linkEvt1').attr('href','FeuilleListeMatchs.php?idEvenement=' + theTarget);
		$('#linkEvt2').attr('href','../PdfListeMatchs.php?idEvenement=' + theTarget);
		$('#linkEvt3').attr('href','FeuilleListeMatchsEN.php?idEvenement=' + theTarget);
		$('#linkEvt4').attr('href','../PdfListeMatchsEN.php?idEvenement=' + theTarget);
		$('#linkEvt5').attr('href','../PdfQrCodes.php?Evt=' + theTarget + '&S=' + Saison);
		$('#linkEvt6').attr('href','../PdfQrCodes.php?idEvt=' + theTarget + '&saison=' + Saison);
	});
	

});


