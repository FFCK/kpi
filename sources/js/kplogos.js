// jq = jQuery.noConflict();
jq(document).ready(function(){
    jq('body').popover({
        selector: '.img2',
        html: true,
        trigger: 'hover',
        placement: 'bottom',
        content: function () {
            var temp = jq(this).attr('src');
            //alert(temp);
            return '<img class="img-rounded" style="float:right;width:150px;max-width:150px;" src="'+temp+'" />';
        }
    });    

});


