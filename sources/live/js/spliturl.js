function Go_url_splitter()
{
	var url = document.location.origin + '/live/splitter.php';
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
		urlRow = urlRow.replace(document.location.origin, "");
//		urlRow = urlRow.replace('http://', "");
//		urlRow = urlRow.replace('https://', "");
        
		if (i==1)
			url += "?";
		else
			url += "&";
		
		url += "frame"+i+"="+urlRow;
	}
    
	$("#tv_message").html("<b>URL split : <a href='" + url + "' target='_blank'>" + url + "</a>");
}


function Init() {
    $('#split_btn').click( function () { 
        Go_url_splitter();
        return false;
    });
    
}
  
  