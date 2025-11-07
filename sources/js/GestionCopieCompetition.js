jq = jQuery.noConflict();

function Duppli()
{
	if(confirm("Copier la structure des matchs ?"))
	{
		
/*		var memeSaison = 0;
		var memeCompetition = 0;
		if(document.forms['formCopieCompetition'].elements['saisonOrigine'].value == document.forms['formCopieCompetition'].elements['saisonDestination'].value)
		{
			memeSaison = 1;
		}
		if(document.forms['formCopieCompetition'].elements['competOrigine'].value == document.forms['formCopieCompetition'].elements['competDestination'].value)
		{
			memeCompetition = 1;
		}
		if((memeSaison == 1) && (memeCompetition == 1))
		{
			alert("Vous tentez de copier cette compétition sur elle-même !");
		}
		else
		{
*/			document.forms['formCopieCompetition'].elements['Cmd'].value = 'Ok';
			document.forms['formCopieCompetition'].elements['ParamCmd'].value = '';
			document.forms['formCopieCompetition'].submit();
/*		}
*/
	}
}

function Cancel()
{
	document.forms['formCopieCompetition'].elements['Cmd'].value = 'Cancel';
	document.forms['formCopieCompetition'].elements['ParamCmd'].value = '';
	document.forms['formCopieCompetition'].submit();
}
