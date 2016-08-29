jq = jQuery.noConflict();

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

jq('#CancelRechercheIndi').live('click', function(){
	jq('#iframeRechercheLicenceIndi2', window.parent.document).hide(); 
});

jq('.cliquableCheckbox').live('click', function(){
	jq(this).attr('checked', false);
	var identifiant = jq(this).attr('id');
	var identifiant2 = identifiant.split('-');
	var matric = identifiant2[0];
	var identite = identifiant2[1];
	var zoneMatric = jq('#zoneMatric').val();
	var zoneIdentite = jq('#zoneIdentite').val();
	jq('#'+zoneMatric, window.parent.document).val(matric);
	jq('#'+zoneIdentite, window.parent.document).val(identite);
	
	jq('#iframeRechercheLicenceIndi2', window.parent.document).hide(); 

});
