function changeEquipeA()
{
}

function changeEquipeB()
{
}

function ChangeOrderMatchs()
{
	document.forms['formJournee'].submit();
}

function changeCompet()
{
	document.forms['formJournee'].elements['Cmd'].value = '';
	document.forms['formJournee'].elements['ParamCmd'].value = 'changeCompet';
	document.forms['formJournee'].submit();
}

function changeCompetition()
{
	//document.forms['formJournee'].elements['Cmd'].value = '';
	//document.forms['formJournee'].elements['ParamCmd'].value = 'changeCompetition';
	document.forms['formJournee'].submit();
}


	