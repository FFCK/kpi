$(document).ready(function(){
    
	$('#fullpage').fullpage({
		scrollingSpeed: 700,
		autoScrolling: true,
//        sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
        continuousVertical: true,
//        navigation: true,
//        navigationPosition: 'right',
        afterRender: function () {
            //on page load, start the slideshow
//            slideTimeout = setInterval(function () {
//                $.fn.fullpage.moveSectionDown();
//            }, 5000);
        }
    });

});


