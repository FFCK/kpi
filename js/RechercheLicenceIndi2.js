function changeComiteReg()
{
	document.forms['formRerchercheLicenceIndi2'].elements['Cmd'].value = '';
	document.forms['formRerchercheLicenceIndi2'].submit();
}

function changeComiteDep()
{
	changeComiteReg();
}

function changeClub()
{
	changeComiteReg();
}

function Find()
{
	document.forms['formRerchercheLicenceIndi2'].elements['Cmd'].value = 'Find';
	document.forms['formRerchercheLicenceIndi2'].submit();
}

$('#CancelRechercheIndi').live('click', function(){
	$('#iframeRechercheLicenceIndi2', window.parent.document).hide(); 
});

$('.cliquableCheckbox').live('click', function(){
	$(this).attr('checked', false);
	var identifiant = $(this).attr('id');
	var identifiant2 = identifiant.split('-');
	var matric = identifiant2[0];
	var identite = identifiant2[1];
	var zoneMatric = $('#zoneMatric').val();
	var zoneIdentite = $('#zoneIdentite').val();
	$('#'+zoneMatric, window.parent.document).val(matric);
	$('#'+zoneIdentite, window.parent.document).val(identite);
	
	$('#iframeRechercheLicenceIndi2', window.parent.document).hide(); 

});
