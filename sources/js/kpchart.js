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

    $('html, body').animate({
        scrollTop: $("#navGroup").prev().offset().top
      }, 1000)

    // equipe rouge au survol
    // $('a.equipe').mouseenter(function(){
    //     var team = $(this).text();
    //     $('a.btn:contains('+team+')').each(function(){
    //         if ($(this).text() == team) {
    //             $(this).addClass('btn-danger');
    //         }
    //     });
    // }).mouseleave(function(){
    //     $('a.btn-danger').removeClass('btn-danger');
    // });


});


document.querySelectorAll(".btn.equipe").forEach((e0) => {
    e0.addEventListener("mouseenter", () => {
        const team = e0.innerText;
        Array.from(document.querySelectorAll(".btn.equipe"))
            .map(element => {
                if (element.textContent === team) {
                    element.classList.add('border')
                    element.classList.add('border-3')
                    element.classList.add('border-danger')
                }
            })
    });
    e0.addEventListener("mouseleave", () => {
        document.querySelectorAll(".equipe.border-danger").forEach((e2) => {
            e2.classList.remove('border')
            e2.classList.remove('border-3')
            e2.classList.remove('border-danger');
        });
    });
});
