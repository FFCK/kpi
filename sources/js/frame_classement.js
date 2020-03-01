// jq = jQuery.noConflict();
$(document).ready(function(){

    $('body').popover({
        selector: '.img2',
        html: true,
        trigger: 'hover',
        placement: 'right',
        content: function () {
            var temp = $(this).attr('src');
            //alert(temp);
            return '<img class="img-rounded" style="float:right;width:100px;max-width:100px;" src="'+temp+'" />';
        }
    });    

});


