// jq = jQuery.noConflict();
jq(document).ready(function(){

    jq('body').popover({
        selector: '.img2',
        html: true,
        trigger: 'hover',
        placement: 'right',
        content: function () {
            var temp = jq(this).attr('src');
            return '<img class="img-rounded" style="float:right;width:100px;max-width:100px;" src="'+temp+'" />';
        }
    });    

    jq('#share_btn').click(function(){
        toCopy = window.location.href;
        jq('#share_alert').remove();
        jq('#navTitle').after('<div class="alert alert-info alert-dismissible" role="alert" id="share_alert">'
                + ' <button type="button" class="close" data-dismiss="alert" aria-label="Close">'
                + '    <span aria-hidden="true">&times;</span>'
                + ' </button><span>' + toCopy + '</span><input type="text" id="share_link" value="' + toCopy + '">'
                + '</div>');
        jq('#share_link').select();
        document.execCommand('copy');
        jq('#share_link').remove();
    });

    jq('html, body').animate({
        scrollTop: jq("#navGroup").prev().offset().top
      }, 1000)

    // equipe rouge au survol
    $('a.equipe').mouseenter(function(){
        var team = $(this).text();
        console.log(team);
        $('a.btn:contains('+team+')').each(function(){
            if ($(this).text() == team) {
                $(this).addClass('btn-danger');
            }
        });
    }).mouseleave(function(){
        $('a.btn-danger').removeClass('btn-danger');
    });


});


