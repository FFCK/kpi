function validNews()
{
		var texte = document.forms['formNews'].elements['Texte'].value;
		if (texte.length == 0)
		{
			alert("Le texte de la news est Vide ..., Ajout Impossible !");
			return false;
		}
		
		return true;
}

function addNews()
{
	if (!validNews())
		return;

	document.forms['formNews'].elements['Cmd'].value = 'Add';
	document.forms['formNews'].elements['ParamCmd'].value = '';
	document.forms['formNews'].submit();
}

function updateNews()
{
	if (!validNews())
		return;
						
	document.forms['formNews'].elements['Cmd'].value = 'UpdateNews';
	document.forms['formNews'].elements['ParamCmd'].value = '';
	document.forms['formNews'].submit();
}

function razNews()
{
	document.forms['formNews'].elements['Cmd'].value = 'RazNews';
	document.forms['formNews'].elements['ParamCmd'].value = '';
	document.forms['formNews'].submit();
}


function paramNews(idNews)
{
	document.forms['formNews'].elements['Cmd'].value = 'ParamNews';
	document.forms['formNews'].elements['ParamCmd'].value = idNews;
	document.forms['formNews'].submit();
}
	
function calcHeight()
{
/*	//récupère la hauteur de la page
	var the_height = document.getElementById('WordPressKPI').contentWindow.document.body.scrollHeight;
	//change la hauteur de l'iframe
	alert(the_height);
	document.getElementById('WordPressKPI').height = the_height;
*/
}

$(document).ready(function() {
	//var theFrame = $(“#WordPressKPI, parent.document.body);
	//$(“#WordPressKPI").height($(document.body).height() - 30);
	
	//var height = $('body').height() - $('#WPDiv').get(0).offsetTop;
    //$('#WPDiv').css('height', height+'px');
    //$('#WPDiv').height(height);
	$("#banniere").click(function() {
		$("#WordPressKPI").height($("#WordPressKPI").contents().find("body").outerHeight());
	});
});



