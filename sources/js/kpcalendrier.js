// FullCalendar v6 - Calendrier des compétitions
// Migration depuis FullCalendar v2.3.1 vers v6.1.19

document.addEventListener('DOMContentLoaded', function() {
    // Configuration commune pour les deux calendriers
    var commonConfig = {
        initialView: 'dayGridMonth',
        editable: false,
        displayEventTime: false,
        height: 550,
        firstDay: 1, // Lundi

        // Chargement des événements depuis json-events.php
        events: function(info, successCallback, failureCallback) {
            // json-events.php attend les paramètres start et end au format date ISO (YYYY-MM-DD)
            var start = info.start.toISOString().split('T')[0];
            var end = info.end.toISOString().split('T')[0];

            fetch('json-events.php?start=' + start + '&end=' + end)
                .then(function(response) {
                    return response.json();
                })
                .then(function(events) {
                    // Transformer les événements pour FullCalendar v6
                    // v2 utilisait 'className' (string), v6 utilise 'classNames' (array)
                    var transformedEvents = events.map(function(event) {
                        return {
                            id: event.id,
                            title: event.title,
                            start: event.start,
                            end: event.end,
                            url: event.url,
                            classNames: event.className ? [event.className] : []
                        };
                    });
                    successCallback(transformedEvents);
                })
                .catch(function(error) {
                    console.error('Erreur chargement événements:', error);
                    failureCallback(error);
                });
        },

        // Callback de chargement
        loading: function(isLoading) {
            var loadingEl = document.getElementById('loading');
            if (loadingEl) {
                loadingEl.style.display = isLoading ? 'block' : 'none';
            }
        },

        // Gestion du clic sur événement
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        }
    };

    // Calendrier Français
    var calendarElFr = document.getElementById('calendar_fr');
    if (calendarElFr) {
        var calendarFr = new FullCalendar.Calendar(calendarElFr, Object.assign({}, commonConfig, {
            locale: 'fr',
            buttonText: {
                today: 'Aujourd\'hui',
                month: 'mois',
                week: 'semaine',
                day: 'jour'
            },
            headerToolbar: {
                left: '',
                center: 'title',
                right: 'today prev,next'
            }
        }));
        calendarFr.render();
    }

    // Calendrier Anglais
    var calendarElEn = document.getElementById('calendar_en');
    if (calendarElEn) {
        var calendarEn = new FullCalendar.Calendar(calendarElEn, Object.assign({}, commonConfig, {
            locale: 'en',
            buttonText: {
                today: 'Today',
                month: 'month',
                week: 'week',
                day: 'day'
            },
            headerToolbar: {
                left: '',
                center: 'title',
                right: 'today prev,next'
            }
        }));
        calendarEn.render();
    }
});
