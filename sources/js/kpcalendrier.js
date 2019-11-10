jQuery(document).ready(function() {
    var date = new Date();
    var d = date.getDate();
    var m = date.getMonth();
    var y = date.getFullYear();
	jQuery('#calendar_fr').fullCalendar({
		editable: false,
//		events: [{
//                title:"Tournoi International Seneffe - Charleroi (Seneffe - Charleroi-)",
//                start:"2015-05-08",
//                end:"2015-05-09"
//            },
//            {
//              title: 'Click for Google',
//              start: new Date(y, m, 28),
//              end: new Date(y, m, 29),
//              url: 'http://google.com/'
//            }],
        events: "json-events.php",
        //eventLimit: true,
        monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Decembre'],
		monthNamesShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'],
		dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
		dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
		buttonText: {
			today:    'Aujourdhui',
			month:    'mois',
			week:     'semaine',
			day:      'jour'
		},
		header: {
			right:  'today prev,next prevYear,nextYear'
		},
		height: 550,
		firstDay: 1,
//		eventDrop: function(event, delta) {
//			alert(event.title + ' was moved ' + delta + ' days\n' +
//				'(should probably update your database)');
//		},
		loading: function(bool) {
			if (bool) jQuery('#loading').show();
			else jQuery('#loading').hide();
		}
	});
	jQuery('#calendar_en').fullCalendar({
		editable: false,
		events: "json-events.php",
		monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
		monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
		buttonText: {
			today:    'Today',
			month:    'month',
			week:     'week',
			day:      'day'
		},
		header: {
			right:  'today prev,next prevYear,nextYear'
		},
		height: 550,
		firstDay: 1,
		eventDrop: function(event, delta) {
			alert(event.title + ' was moved ' + delta + ' days\n' +
				'(should probably update your database)');
		},
		loading: function(bool) {
			if (bool) jQuery('#loading').show();
			else jQuery('#loading').hide();
		}
	});
});
	
