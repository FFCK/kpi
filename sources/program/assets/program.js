$(function () {

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
    if (null == program) {
        program = defaultValues();
        Cachejs.Local.set('program', program);
    }
    switchlang(program.lang);
    updateSelect('event', program.events[0][program.lang], program.event);
    if(program.event == -1) {
        updateSelect('saison', program.seasons, program.season);
    } else {
        $('#saison').parent().hide();
    }
    

    // Traduction
    $('.langswitch').click(function () {
        program.lang = $(this).data('lang');
        Cachejs.Local.set('program', program);
        switchlang(program.lang);
        updateSelect('event', program.events[0][program.lang], program.event);
    });
    
    // Changement d'événement
    $('#event').change(function(){
        program.event = $(this).val();
        Cachejs.Local.set('program', program);
        if(program.event == -1) {
            updateSelect('saison', program.seasons, program.season);
            $('#saison').parent().show();
            $('#competition').parent().show();
            $('#categorie').parent().hide();
        } else {
            $('#saison').parent().hide();
            $('#competition').parent().hide();
            $('#categorie').parent().show();
        }
        
        //TODO: recharger le tableau
    });
    // Changement de saison
    $('#saison').change(function(){
        program.season = $(this).val();
        Cachejs.Local.set('program', program);
        
        //TODO: recharger le tableau
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
            lang: 'fr',
            season: 2017,
            seasons: [
                {'label': 2019},
                {'label': 2018},
                {'label': 2017},
                {'label': 2016},
                {'label': 1792},
            ],
            event: -1,
            events: [
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
            ]
            
        };
}




/***************************** OUTILS *************************************/

/**
 * Remplissage d'un sélect
 * 
 * @param {string} selectId
 * @param {json.object} optionList
 * @param {string} optionSelected
 * @returns {undefined}
 */
function updateSelect(selectId, optionList, optionSelected = '') {
    console.log(optionList);
    $('#' + selectId).empty();
    for (i in optionList) {
        if(undefined === optionList[i].valeur) {
            optionList[i].valeur = optionList[i].label;
        }
        if(optionList[i].valeur == optionSelected) {
            selectedOption = ' selected';
        } else {
            selectedOption = '';
        }
         $('#' + selectId).append('<option value="' + optionList[i].valeur + '"' + selectedOption + '>' + optionList[i].label + '</option>');
    }
}


/******************************************************************/

