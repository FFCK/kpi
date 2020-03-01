//jq = jQuery.noConflict();
jq(document).ready(function(){
    jq('#scenario').change(function() {
        window.location.href = '?scenario=' + jq(this).val();
    });
});
    
function Go_ajax(param)
{
    jq.ajax({   type: "GET", 
                url: param, 
                dataType: "html", 
                cache: false, 
                success: function(htmlData) {
						alerte(htmlData);
				}
	});
}

