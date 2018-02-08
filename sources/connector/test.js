function showDataConnector(data)
{
//	alert('showDataConnector = '+data);

	$('#result').html("<h1>Importation</h1>");
	$('#result').append("<h2>Les donnees suivantes sont enregistr&eacute;es dans la base locale ...</h2>");
	
	$('#result').append("<div>"+data+"</div>");
}

function showExportReturn(data)
{
	alert('showExportReturn = '+data);

	$('#result').html('<h1>Exportation</h1>');
	$('#result').append('<div>'+data+'</div>');
}

function submitJsonData(json) 
{
	var txtJSON = JSON.stringify(json);
	
	$('#json_data').attr('value', txtJSON);
	document.forms['testForm'].submit();
}

function getRemoteData(url) 
{
    var script = document.createElement("script"); 
	script.type = "text/javascript"; 
	script.src  = url + "&callback=submitJsonData"; //ajout de la fonction de retour
	$("head")[0].appendChild(script);
}

function OnImport()
{
    var urlOrg = $('#urlOrigine').attr('value');
	var lstEvt = $('#lstEvent').attr('value');
		
    alert('OnImport : '+lstEvt);

	$.ajax({
	url : 'read_evenement.php?url='+urlOrg+'&evt='+lstEvt,
	type: 'GET',
	dataType: 'text',
	cache: false,
	async: false,
	success: showDataConnector
	});
}

function OnImport2()
{
    var urlOrg = $('#urlOrigine').attr('value');
	var lstEvt = $('#lstEvent').attr('value');

    alert('OnImport2 : '+lstEvt);
    getRemoteData('https://kayak-polo.info/connector/get_evenement.php?lst='+lstEvt);
}

function OnImportServer()
{
    var lstEvt = $('#lstEvent').attr('value');
    alert('OnImportServer : '+lstEvt);
    
    getRemoteData('http://localhost/connector/get_evenement.php?lst='+lstEvt);
    
/*    
   		$.ajax({
		url : 'http://localhost/connector/get_evenement.php?lst=31',
		type: 'GET',
		dataType: 'text',
		cache: false,
		async: false,
		crossDomain:true,
		success: showExportReturn
		});
*/
/*

	$.ajax({
	url : 'http://localhost/connector/get_evenement.php?lst=31',
	type: 'GET',
	crossDomain:true,
	dataType: 'jsonp',
	cache: false,
	async: false,
	success: showExportReturn
	});

*/
}

function Init()
{
	var href = window.location.href;
/*
	if (href.indexOf('localhost') >= 0)
		$('#urlOrigine').attr('value', "https://kayak-polo.info/connector/get_evenement.php");
	else
		$('#urlOrigine').attr('value', "http://localhost/connector/get_evenement.php");
*/
	$('#urlOrigine').attr('value', "https://kayak-polo.info/connector/get_evenement.php");
	$('#lstEvent').attr('value', "2,5");
	
	if (href.indexOf('kayak-polo.info') >= 0)
	{
		$('#user').attr('value', '***');
		$('#pwd').attr('value', '***');
	}
	
	$('#btnImportServer').click(function() {
		OnImportServer();
	});

	$('#btnImport').click(function() {
		OnImport();
	});

	$('#btnImport2').click(function() {
		OnImport2();
	});
}

