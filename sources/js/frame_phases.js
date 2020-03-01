// jq = jQuery.noConflict();
jq(document).ready(function(){

    jq('body').popover({
        selector: '.img2',
        html: true,
        trigger: 'hover',
        placement: 'right',
        content: function () {
            var temp = jq(this).attr('src');
            //alert(temp);
            return '<img class="img-rounded" style="float:right;width:100px;max-width:100px;" src="'+temp+'" />';
        }
    });    

});


