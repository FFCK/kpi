/**
 * Functions and scripts related to the theme.
 * @package Theme_Material
 * GPL3 Licensed
 */
jQuery(document).ready(function (a) {
    
    /*add responsive utilities for images */
    a("article img").addClass("img-responsive");
    a("aside img").addClass("img-responsive");
    
    /*override default calendar widget*/
    a('table[id^="wp-calendar"]').addClass("table");
    
    /*main navbar*/
    if(a(window).width()>=768){
    a(".navbar-collapse li[class*='children']").children('a').append('<i class="fa fa-caret-down"></i>');}
    
    a(".navbar-collapse li[class*='children']").hover(
        function(){
            a(this).css("color","#428bca");
            a(this).children(".navbar-collapse ul.sub-menu").css("display","block");
            a(this).siblings('li').children(".navbar-collapse ul.sub-menu").css("display","none");

            a(this).children(".navbar-collapse ul.children").css("display","block");
            a(this).siblings('li').children(".navbar-collapse ul.children").css("display","none");
        });
    
    a(".navbar-collapse .site-menu > li").hover(
        function(){
             a(this).siblings('li').children(".navbar-collapse ul.sub-menu").css("display","none");
             a(this).siblings('li').children(".navbar-collapse ul.children").css("display","none");

        }
    );

    a(".sub-menu li").mouseleave(function() {
        /* Act on the event */
        a(this).closest(".col-xs-12").mouseleave(
                function(){
               a(".navbar-collapse ul.sub-menu").css("display","none");
                    
                })
    });   

    a("ul.children li").mouseleave(function() {
        /* Act on the event */
        a(this).closest(".col-xs-12").mouseleave(
                function(){
                a(".navbar-collapse ul.children").css("display","none");
                    
                })
    });             
        
    /*override select menu and input box*/
    a("select").addClass("form-control");
    a('form.searchform input[type="text"]').addClass("form-control pull-left").css({
        width: "66%"
    });
    a('form.searchform input[type="submit"]').addClass("btn btn-default pull-left");
    a("label").css("display", "block");
    a('form.post-password-form input[type="submit"]').addClass("btn btn-default");
    a('form.post-password-form input[type="password"]').addClass("form-control").css({
        width: "66%"
    });
    a('form.comment-form input[type="submit"]').addClass("btn btn-default").css("margin-top", "10px");
    
    /*popup comment reply form in mobile view*/
    768 >= a(window).width() && (a(".comment-form").clone(), a("div.copyright").after("<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'><div class='modal-dialog'><div class='modal-content'><div class='modal-header popup-comment-head'><button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button><h4 class='modal-title' id='myModalLabel'></h4></div><div class='modal-body'></div></div></div></div>"),
        a("h4.modal-title").append(a("h3.comment-reply-title").html()), a(".comment-form").clone().appendTo("div.modal-body"), a("a.comment-reply-link").attr({
            "data-toggle": "modal",
            "data-target": "#myModal"
        }).attr("onclick", ""));
    
});