$(function () {
    ajaxUrl = 'https://www.kayak-polo.info/program.php';
    
    // Cache
    $.ajaxSetup({cache: false});

    // Librairie CacheJs chargée ?
    if (typeof Cachejs === 'undefined') {
        console.log('Lib non chargé');
        return false;
    }
    
    program = {};

    // Paramètres généraux
    program = Cachejs.Local.get('program');
    if (null === program) {
        program = defaultValues();
        Cachejs.Local.set('program', program);
    }
    switchlang(program.lang);
    
    updateSelectGroup('event', program.events[0][program.lang], program.event);
    if(program.event == -1) {
        updateSelectGroup('saison', program.seasons, program.season);
        updateSelectGroup('competition', program.compets[0][program.lang], program.compet);
        $('#saison').parent().show();
        $('#competition').parent().show();
        $('#categorie').parent().hide();
    } else {
        $('#saison').parent().hide();
        $('#competition').parent().hide();
        $('#categorie').parent().show();
    }
    

    // Traduction
    $('.langswitch').click(function () {
        program.lang = $(this).data('lang');
        Cachejs.Local.set('program', program);
        switchlang(program.lang);
        updateSelectGroup('event', program.events[0][program.lang], program.event);
        updateSelectGroup('competition', program.compets[0][program.lang], program.compet);
    });
    
    // Changement d'événement
    $('#event').change(function(){
        program.event = $(this).val();
        if(program.event == -1) {
            updateSelectGroup('saison', program.seasons, program.season);
            updateSelectGroup('competition', program.compets[0][program.lang], program.compet);
            $('#saison').parent().show();
            $('#competition').parent().show();
            $('#categorie').parent().hide();
            //TODO: ajaxer les compétitions
        } else {
            $('#saison').parent().hide();
            $('#competition').parent().hide();
            $('#categorie').parent().show();
            //TODO: ajaxer les categories
        }
        
        //TODO: recharger le tableau
        Cachejs.Local.set('program', program);
    });
    // Changement de saison
    $('#saison').change(function(){
        program.season = $(this).val();
        getAjaxData(program.season, program.event, program.compet);
        //TODO: recharger le tableau
        Cachejs.Local.set('program', program);
    });
    
    
    
    // Init
    $('#init').change(function(){
        program = defaultValues();
        Cachejs.Local.set('program', program);
        
        //TODO: recharger le tableau
    });
 
});

/**
 * 
 * @param {int} season
 * @param {int} event
 * @param {string} compet
 * @returns {undefined}
 */
function getAjaxData(season, event, compet) {
    var request = $.ajax({
        url: ajaxUrl,
        method: "GET",
        data: {season: season, event: event, compet: compet},
        dataType: "jsonp",
        jsonpCallback: "callback",
        success: function(data) {
            var result;
            $.each(data, function(i, item) {
                result += '<tr>' + '<td>' + item.Code + '</td><td>' + item.selected + '</td></tr>';
            });
            $("#mainTable tbody").append(result);
        },
        fail: function (jqXHR, textStatus) {
            alert("Request failed: " + textStatus);
        }
    });
}



/**
 * 
 * @param {string} lang
 * @returns {undefined}
 */
function switchlang(lang) {
    $.getJSON("assets/version_" + lang + ".json", function (vers) {
        $('[data-version]').each(function () {
            param = $(this).data('version');
            if (undefined === vers[param]) {
                console.log(param);
            } else {
                $(this).text(vers[param]);
            }
        });
        $('[data-label]').each(function () {
            param = $(this).data('label');
            if (undefined === vers[param]) {
                console.log(param);
            } else {
                $(this).attr('label', vers[param]);
            }
        });
        
    });
}

/**
 * Valeurs par défaut à initialiser si le stockage local est vide
 * 
 * @returns {defaultValues.programAnonym$0}
 */
function defaultValues() {
    return {
            'lang': 'fr',
            'season': 2017,
            'seasons': [
                {'label': 2019},
                {'label': 2018},
                {'label': 2017},
                {'label': 2016},
                {'label': 1792},
            ],
            'event': -1,
            'events': [
                {
                    'en': [
                        {'valeur': -1, 'label': 'All / None'},
                        {'valeur': 93, 'label': 'International tournament of Pas-de-Calais 2018'},
                        {'valeur': 92, 'label': 'Regional Championships AURA 2018'},
                        {'valeur': 91, 'label': 'US Nationals 2017'},
                        {'valeur': 88, 'label': 'French cup 2017'},
                        {'valeur': 90, 'label': 'French Open 2017'}
                    ],
                    'fr': [
                        {'valeur': -1, 'label': 'Tous / Aucun'},
                        {'valeur': 93, 'label': 'Tournoi International Pas-de-Calais 2018'},
                        {'valeur': 92, 'label': 'Chpt et tournoi régional AURA 2018'},
                        {'valeur': 91, 'label': 'US Nationals 2017'},
                        {'valeur': 88, 'label': 'Finales Coupes de France &amp; NQH 2017'},
                        {'valeur': 90, 'label': 'Open de France 2017'}
                    ]
                }
            ],
            'compet': 'N1H',
            'compets': [
                {
                    'en': [
                        {'optgroup': 'International competitions', 'options': [
                                {'valeur': 'CM', 'label': 'World championships'},
                                {'valeur': 'CE', 'label': 'European championships'},
                                {'valeur': 'CEC', 'label': 'European clubs championships'}
                        ]},
                        {'optgroup': 'National competitions', 'options': [
                                {'valeur': 'N1H', 'label': 'Men national 1'},
                                {'valeur': 'N1F', 'label': 'Women national 1'}
                        ]}
                    ],
                    'fr': [
                        {'optgroup': 'Compétitions internationales', 'options': [
                                {'valeur': 'CM', 'label': 'Championnats du monde'},
                                {'valeur': 'CE', 'label': 'Championnats d\'Europe'},
                                {'valeur': 'CEC', 'label': 'Championnat d\'Europe des Clubs'}
                        ]},
                        {'optgroup': 'Compétitions nationales', 'options': [
                                {'valeur': 'N1H', 'label': 'Nationale 1 hommes'},
                                {'valeur': 'N1F', 'label': 'Nationale 1 femmes'}
                        ]}
                    ]
                }
            ]
            
        };
}




/***************************** OUTILS *************************************/


/**
 * Remplissage d'un sélect
 * 
 * @param {string} selectId
 * @param {json.object} optionList w/optgroup or not
 * @param {string} optionSelected
 * @param {bool} empty
 * @returns {undefined}
 */
function updateSelectGroup(selectId, optionList, optionSelected = '', empty = true) {
    // Si empty, on désélectionne tout
    if(empty) {
        $('#' + selectId).empty();
    }
    for (i in optionList) {
        // si c'est un groupe, on le crée et on le rempli
        if(undefined !== optionList[i].optgroup) {
            $('#' + selectId).append('<optgroup label="' + optionList[i].optgroup + '">');
            updateSelectGroup(selectId, optionList[i].options, optionSelected, false);
            $('#' + selectId).append('</optgroup>');
        // sinon, c'est une option, on l'ajoute
        } else {
            // s'il n'y a pas de valeur, on lui attribue son label
            if(undefined === optionList[i].valeur) {
                optionList[i].valeur = optionList[i].label;
            }
            // s'il est sélectionné
            if(optionList[i].valeur == optionSelected) {
                selectedOption = ' selected';
            } else {
                selectedOption = '';
            }
            // on dessine l'option
            $('#' + selectId).append('<option value="' + optionList[i].valeur + '"' + selectedOption + '>' 
                    + optionList[i].label + '</option>');
        }
    }
}


/******************************************************************/

