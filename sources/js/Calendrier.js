$(document).ready(function() {
	$('#calendar').fullCalendar({
		editable: false,
		events: "json-events.php",
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
		eventDrop: function(event, delta) {
			alert(event.title + ' was moved ' + delta + ' days\n' +
				'(should probably update your database)');
		},
		loading: function(bool) {
			if (bool) $('#loading').show();
			else $('#loading').hide();
		}
	});
	$('#calendarEN').fullCalendar({
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
			if (bool) $('#loading').show();
			else $('#loading').hide();
		}
	});
});
	
