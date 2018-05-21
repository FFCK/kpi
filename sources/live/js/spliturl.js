function Go_url_splitter()
{
	var url = document.location.hostname + '/live/splitter.php';
	for (var i=1; i<=5; i++)
	{
		var urlRow = $("#split_url"+i).val();
		if ((urlRow == '') || (typeof(urlRow) == "undefined"))
			break;
		
		urlRow = urlRow.replace("?", "|Q|");
		for (;;)
		{	
			var urlRow2 = urlRow.replace("&", "|A|");
			if (urlRow2 == urlRow) break;
			urlRow = urlRow2;
		}
		
		if (i==1)
			url += "?";
		else
			url += "&";
		
		url += "frame"+i+"="+urlRow;
	}
	
	$("#tv_message").html("<b>URL progression : <br><br>" + url);
}


function Init() {
    $('#split_btn').click( function () { 
        Go_url_splitter();
        return false;
    });
    
}
  
  