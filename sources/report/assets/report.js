$(function () {

    // Cache
    $.ajaxSetup({cache: false});

    // Librairie CacheJs chargée ?
    if (typeof Cachejs === 'undefined') {
        console.log('Lib non chargé');
        return false;
    }
    
    report = {};

    // Paramètres généraux
    report = Cachejs.Local.get('report');
    if (null == report) {
        report = {
            lang: 'fr',
            tab: 'param-tab',
            gameId: 0,
            action: 'stop',
            start_time: 1521800358378,
            run_time: 0,
            max_time: '10:00'
        };
        Cachejs.Local.set('report', report);
    }
    switchlang(report.lang);
    $('#' + report.tab).click();


    // Traduction
    $('.langswitch').click(function () {
        report.lang = $(this).data('lang');
        switchlang(report.lang);
        Cachejs.Local.set('report', report);
    });

    // Tabs
    $('#param-tab, #running-tab').click(function () {
        report.tab = $(this).attr('id');
        Cachejs.Local.set('report', report);
    });

    Raz();
    $('.timer-control').hide();
    $('#start_button').show();
    


});

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
        $('[placeholder]').each(function () {
            if (undefined === vers['placeholder']) {
                console.log('placeholder');
            } else {
                $(this).attr('placeholder', vers['Nom'] + ' ' + vers['Prenom'] + ' ' + vers['Licence']);
            }
        });
    });
}


/**************** CHRONO *******************/
function Raz() {
    split_period = report.max_time.split(':');
    minut_max = split_period[0];
    second_max = split_period[1];
    $('#Chrono').val(minut_max + ':' + second_max);
}

function Horloge() {
    var temp_time = new Date();
    // chrono
    // run_time.setTime(temp_time.getTime() - start_time.getTime());

    // compte à rebours
    var max_time1 = (minut_max * 60000) + (second_max * 1000);
    run_time.setTime(start_time.getTime() + max_time1 - temp_time.getTime());

    var minut_ = run_time.getMinutes();
    if (minut_ < 10) {
        minut_ = '0' + minut_;
    }
    var second_ = run_time.getSeconds();
    if (second_ < 10) {
        second_ = '0' + second_;
    }
    $('#Chrono').val(minut_ + ':' + second_);
    /* Contrôle maxi */
    //if(minut_ >= minut_max && second_ >= second_max)
    if (minut_ <= 0 && second_ <= 0)
    {
        // Temps écoulé
        clearInterval(timer);
        $('#periode_end').text('00:00');
        $('#stop_button').click();
        $("#PeriodEndModal").modal("show");
    }
}



//$.post(
//        'v2/getChrono.php',
//        {
//            idMatch: idMatch,
//        },
//        function (data) {
//            if (data.action == 'start' || data.action == 'run') {
//                temp_time = new Date();
//                start_time = new Date();
//                run_time = new Date();
//                start_time.setTime(data.start_time);
//                run_time.setTime(temp_time.getTime() - start_time.getTime());
//                max_time = data.max_time;
//                split_period = max_time.split(':');
//                minut_max = split_period[0];
//                second_max = split_period[1];
//                $('#start_button').hide();
//                $('#restart_button').hide();
//                $('#stop_button').show();
//                $('#heure').css('background-color', '#009900');
//                timer = setInterval(Horloge, 500);
//                avertissement(lang.Chrono + ' ' + lang.en_cours);
//                $('#tabs-2_link').click();
//            } else if (data.action == 'stop') {
//                temp_time = new Date();
//                start_time = new Date();
//                run_time = new Date();
//                start_time.setTime(data.start_time);
//                run_time.setTime(data.run_time);
//                $('#start_time_display').text(start_time.toLocaleString()); //debug
//                $('#run_time_display').text(run_time.toLocaleString()); //debug
//                max_time = data.max_time;
//                split_period = max_time.split(':');
//                minut_max = split_period[0];
//                second_max = split_period[1];
//                $('#start_button').hide();
//                $('#restart_button').show();
//                $('#stop_button').hide();
//                $('#chrono_moins').show();
//                $('#chrono_plus').show();
//                $('#heure').css('background-color', '#990000');
//                var minut_ = run_time.getMinutes();
//                if (minut_ < 10) {
//                    minut_ = '0' + minut_;
//                }
//                var second_ = run_time.getSeconds();
//                if (second_ < 10) {
//                    second_ = '0' + second_;
//                }
//                $('#heure').val(minut_ + ':' + second_);
//                avertissement(lang.Chrono + ' ' + lang.arrete);
//                $('#tabs-2_link').click();
//            }
//        },
//        'json'
//        );


/*  Fonction InitTime */

//    temp_time = new Date();
//    start_time = new Date();
//    run_time = new Date();
//    start_time.setTime('1521800358378');
//    run_time.setTime('500');
//    $('#start_time_display').text(start_time.toLocaleString()); //debug
//    $('#run_time_display').text(run_time.toLocaleString()); //debug
//    max_time = '10:00';
//    split_period = max_time.split(':');
//    minut_max = split_period[0];
//    second_max = split_period[1];
//    $('#start_button').hide();
//    $('#restart_button').show();
//    $('#stop_button').hide();
//    $('#chrono_moins').show();
//    $('#chrono_plus').show();
//    $('#heure').css('background-color', '#990000');
//    var minut_ = run_time.getMinutes();
//    if (minut_ < 10) {
//        minut_ = '0' + minut_;
//    }
//    var second_ = run_time.getSeconds();
//    if (second_ < 10) {
//        second_ = '0' + second_;
//    }
//    $('#heure').val(minut_ + ':' + second_);
////    avertissement(lang.Chrono + ' ' + lang.arrete);
//    $('#tabs-2_link').click();
    

$('#chrono_moins1').click(function () {
    start_time.setTime(start_time.getTime() - 1000);
    run_time.setTime(run_time.getTime() - 1000);
    var minut_ = run_time.getMinutes();
    if (minut_ < 10) {
        minut_ = '0' + minut_;
    }
    var second_ = run_time.getSeconds();
    if (second_ < 10) {
        second_ = '0' + second_;
    }
    $('#heure').val(minut_ + ':' + second_);
    $('#chronoText').hide();
    $('#checkChrono').show();
});
$('#chrono_plus1').click(function () {
    start_time.setTime(start_time.getTime() + 1000);
    run_time.setTime(run_time.getTime() + 1000);
    var minut_ = run_time.getMinutes();
    if (minut_ < 10) {
        minut_ = '0' + minut_;
    }
    var second_ = run_time.getSeconds();
    if (second_ < 10) {
        second_ = '0' + second_;
    }
    $('#heure').val(minut_ + ':' + second_);
    $('#chronoText').hide();
    $('#checkChrono').show();
});
$('#chrono_moins10').click(function () {
    start_time.setTime(start_time.getTime() - 10000);
    run_time.setTime(run_time.getTime() - 10000);
    var minut_ = run_time.getMinutes();
    if (minut_ < 10) {
        minut_ = '0' + minut_;
    }
    var second_ = run_time.getSeconds();
    if (second_ < 10) {
        second_ = '0' + second_;
    }
    $('#heure').val(minut_ + ':' + second_);
    $('#chronoText').hide();
    $('#checkChrono').show();
});
$('#chrono_plus10').click(function () {
    start_time.setTime(start_time.getTime() + 10000);
    run_time.setTime(run_time.getTime() + 10000);
    var minut_ = run_time.getMinutes();
    if (minut_ < 10) {
        minut_ = '0' + minut_;
    }
    var second_ = run_time.getSeconds();
    if (second_ < 10) {
        second_ = '0' + second_;
    }
    $('#heure').val(minut_ + ':' + second_);
    $('#chronoText').hide();
    $('#checkChrono').show();
});
$('#chrono_moins60').click(function () {
    start_time.setTime(start_time.getTime() - 60000);
    run_time.setTime(run_time.getTime() - 60000);
    var minut_ = run_time.getMinutes();
    if (minut_ < 10) {
        minut_ = '0' + minut_;
    }
    var second_ = run_time.getSeconds();
    if (second_ < 10) {
        second_ = '0' + second_;
    }
    $('#heure').val(minut_ + ':' + second_);
    $('#chronoText').hide();
    $('#checkChrono').show();
});
$('#chrono_plus60').click(function () {
    start_time.setTime(start_time.getTime() + 60000);
    run_time.setTime(run_time.getTime() + 60000);
    var minut_ = run_time.getMinutes();
    if (minut_ < 10) {
        minut_ = '0' + minut_;
    }
    var second_ = run_time.getSeconds();
    if (second_ < 10) {
        second_ = '0' + second_;
    }
    $('#heure').val(minut_ + ':' + second_);
    $('#chronoText').hide();
    $("#checkChrono").show();
    $('#checkChrono').show();
});
$('#time_plus60').click(function () {
    var temp_time2 = $('#Time_evt').val();
    temp_time2 = temp_time2.split(':');
    minut_2 = Number(temp_time2[0]) + 1;
    if (minut_2 > 99) {
        minut_2 = 99;
    }
    if (minut_2 < 0) {
        minut_2 = 0;
    }
    if (minut_2 < 10) {
        minut_2 = '0' + minut_2;
    }
    var second_2 = temp_time2[1];
    if (isNaN(second_2)) {
        second_2 = 0;
    }
    second_2 = Number(second_2);
    if (second_2 > 60) {
        second_2 = 60;
    }
    if (second_2 < 10) {
        second_2 = '0' + second_2;
    }
    $('#Time_evt').val(minut_2 + ':' + second_2);
});
$('#time_moins60').click(function () {
    var temp_time2 = $('#Time_evt').val();
    temp_time2 = temp_time2.split(':');
    minut_2 = Number(temp_time2[0]) - 1;
    if (minut_2 > 99) {
        minut_2 = 99;
    }
    if (minut_2 < 0) {
        minut_2 = 0;
    }
    if (minut_2 < 10) {
        minut_2 = '0' + minut_2;
    }
    var second_2 = temp_time2[1];
    if (isNaN(second_2)) {
        second_2 = 0;
    }
    second_2 = Number(second_2);
    if (second_2 > 60) {
        second_2 = 60;
    }
    if (second_2 < 10) {
        second_2 = '0' + second_2;
    }
    $('#Time_evt').val(minut_2 + ':' + second_2);
});
$('#time_plus10').click(function () {
    var temp_time2 = $('#Time_evt').val();
    temp_time2 = temp_time2.split(':');
    minut_2 = Number(temp_time2[0]);
    if (minut_2 > 99) {
        minut_2 = 99;
    }
    if (minut_2 < 0) {
        minut_2 = 0;
    }
    if (minut_2 < 10) {
        minut_2 = '0' + minut_2;
    }
    var second_2 = temp_time2[1];
    if (isNaN(second_2)) {
        second_2 = 0;
    }
    second_2 = Number(second_2) + 10;
    if (second_2 > 59) {
        second_2 = second_2 - 60;
        $('#time_plus60').click();
    }
    if (second_2 < 10) {
        second_2 = '0' + second_2;
    }
    $('#Time_evt').val(minut_2 + ':' + second_2);
});
$('#time_moins10').click(function () {
    var temp_time2 = $('#Time_evt').val();
    temp_time2 = temp_time2.split(':');
    minut_2 = Number(temp_time2[0]);
    if (minut_2 > 99) {
        minut_2 = 99;
    }
    if (minut_2 < 0) {
        minut_2 = 0;
    }
    if (minut_2 < 10) {
        minut_2 = '0' + minut_2;
    }
    var second_2 = temp_time2[1];
    if (isNaN(second_2)) {
        second_2 = 0;
    }
    second_2 = Number(second_2) - 10;
    if (second_2 < 0) {
        second_2 = second_2 + 60;
        $('#time_moins60').click();
    }
    if (second_2 < 10) {
        second_2 = '0' + second_2;
    }
    $('#Time_evt').val(minut_2 + ':' + second_2);
});
$('#time_plus1').click(function () {
    var temp_time2 = $('#Time_evt').val();
    temp_time2 = temp_time2.split(':');
    minut_2 = Number(temp_time2[0]);
    if (minut_2 > 99) {
        minut_2 = 99;
    }
    if (minut_2 < 0) {
        minut_2 = 0;
    }
    if (minut_2 < 10) {
        minut_2 = '0' + minut_2;
    }
    var second_2 = temp_time2[1];
    if (isNaN(second_2)) {
        second_2 = 0;
    }
    second_2 = Number(second_2) + 1;
    if (second_2 > 59) {
        second_2 = 0;
        $('#time_plus60').click();
    }
    if (second_2 < 10) {
        second_2 = '0' + second_2;
    }
    $('#Time_evt').val(minut_2 + ':' + second_2);
});
$('#time_moins1').click(function () {
    var temp_time2 = $('#Time_evt').val();
    temp_time2 = temp_time2.split(':');
    minut_2 = Number(temp_time2[0]);
    if (minut_2 > 99) {
        minut_2 = 99;
    }
    if (minut_2 < 0) {
        minut_2 = 0;
    }
    if (minut_2 < 10) {
        minut_2 = '0' + minut_2;
    }
    var second_2 = temp_time2[1];
    if (isNaN(second_2)) {
        second_2 = 0;
    }
    second_2 = Number(second_2) - 1;
    if (second_2 < 0) {
        second_2 = 59;
        $('#time_moins60').click();
    }
    if (second_2 < 10) {
        second_2 = '0' + second_2;
    }
    $('#Time_evt').val(minut_2 + ':' + second_2);
});
$('#checkChrono').click(function () {
    $.post(
            'v2/ajax_checkChrono.php',
            {
                idMatch: idMatch,
                start_time: start_time.getTime(),
                run_time: run_time.getTime(),
            },
            function (data) {
                if (data == 'OK') {
                    avertissement('Start chrono');
                }
            },
            'text'
            );
    $('#chronoText').show();
    $('#checkChrono').hide();
});

$('#start_button').click(function () {
    start_time = new Date();
    run_time = new Date();
    run_time.setTime(0);
    run_time2 = new Date();
    run_time2.setTime(0);
    Horloge();
    timer = setInterval(Horloge, 500);
    $('#start_time_display').text(start_time.toLocaleString()); //debug
    $('#run_time_display').text(run_time.toLocaleString()); //debug
    $('#start_button').hide();
    $('#restart_button').hide();
    $('#stop_button').show();
    //	$('#chrono_moins').hide();
    //	$('#chrono_plus').hide();
    $('#heure').css('background-color', '#009900');
    //alert(run_time.getTime());
    $.post(
            'v2/setChrono.php',
            {
                idMatch: idMatch,
                action: 'start',
                start_time: start_time.getTime(),
                run_time: run_time.getTime(),
                max_time: minut_max + ':' + second_max
            },
            function (data) {
                if (data == 'OK') {
                    avertissement('Start chrono');
                }
            },
            'text'
            );
});
$('#stop_button').click(function () {
    if (run_time)
        $('#stop_time_display').text(run_time.toLocaleString()); //debug
    clearInterval(timer);
    $('#restart_button').show();
    $('#start_button').hide();
    $('#stop_button').hide();
    $('#chrono_moins').show();
    $('#chrono_plus').show();
    $('#heure').css('background-color', '#990000');
    $.post(
            'v2/setChrono.php',
            {
                idMatch: idMatch,
                action: 'stop',
                start_time: start_time.getTime(),
                run_time: run_time.getTime(),
                max_time: minut_max + ':' + second_max
            },
            function (data) {
                if (data == 'OK') {
                    avertissement('Stop chrono');
                }
            },
            'text'
            );
});
$('#restart_button').click(function () {
    start_time = new Date();
    // chrono
    // start_time.setTime(start_time.getTime() - run_time.getTime());
    // compte à rebours
    var max_time1 = (minut_max * 60000) + (second_max * 1000);
    start_time.setTime(run_time.getTime() - max_time1 + start_time.getTime());
    Horloge();
    timer = setInterval(Horloge, 500);
    $('#start_time_display').text(start_time.toLocaleString()); //debug
    $('#run_time_display').text(run_time.toLocaleString()); //debug
    $('#restart_button').hide();
    $('#stop_button').show();
    //	$('#chrono_moins').hide();
    //	$('#chrono_plus').hide();
    $('#heure').css('background-color', '#009900');
    $.post(
            'v2/setChrono.php', // : replace table chrono ligne idMatch...
            {
                idMatch: idMatch,
                action: 'run',
                start_time: start_time.getTime(),
                run_time: run_time.getTime(),
                max_time: minut_max + ':' + second_max
            },
            function (data) {
                if (data == 'OK') {
                    avertissement('Run chrono');
                }
            },
            'text'
            );
});
$('#raz_button').click(function () {
    $('#start_button').show();
    $('#restart_button').hide();
    $('#stop_button').hide();
    $('#chrono_moins').show();
    $('#chrono_plus').show();
    clearInterval(timer);
    Raz();
    $('#heure').css('background-color', '#444444');
    $.post(
            'v2/setChrono.php',
            {
                idMatch: idMatch,
                action: 'RAZ',
            },
            function (data) {
                if (data == 'OK') {
                    avertissement(lang.RAZ + ' ' + lang.chrono);
                }
            },
            'text'
            );
});
/******************************************************************/

