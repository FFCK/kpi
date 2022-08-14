//jq = jQuery.noConflict();
jq(document).ready(function () {
    let css = jq('#style').val()

    jq('#style').change(function () {
        let css = jq('#style').val()
    })

    jq('#codeEvt, #jour').change(function () {
        jq('#filtre_evt').submit()
    })

    // Article 1
    jq('#competition').change(function () {
        jq('#filtreCompet').val(jq('#competition').val())
        jq('#filtreChannel').val(jq('#channel').val())
        jq('#filtrePres').val(jq('#presentation').val())
        jq('#filtre_evt').submit()
    })

    jq('#channel').change(function () {
        jq('#control').attr('href', 'live/tv2.php?voie=' + jq(this).val())
        jq('#filtreChannel').val(jq(this).val())
    })

    jq('#match').change(function () {
        jq('#terrain').val(jq(this).find('option:selected').data('terrain'))
        jq('#filtreMatch').val(jq(this).val())
        jq('#filtreCompet').val(jq('#competition').val())
        jq('#game_report').attr('href', '/PdfMatchMulti.php?listMatch=' + jq(this).val())
    })

    jq('#presentation').change(function () {
        var presentation = jq(this).val()
        jq('#filtrePres').val(jq(this).val())
        jq('#confirm').attr('data-pres', presentation)
        if (presentation == '') {
            jq('#img-presentation').attr('src', 'img/presentations/empty.png')
        } else {
            jq('#img-presentation').attr('src', 'img/presentations/' + presentation + '.png')
        }
        jq('.params').hide()
        switch (presentation) {
            case 'list_medals':
                jq('#competition-col').show()
                break
            case 'referee':
                jq('#competition-col, #match-col, #game_report').show()
                break
            case 'player':
                jq('#competition-col, #match-col, #game_report, #team-col, #number-col, #number-btn-col').show()
                break
            case 'coach':
                jq('#competition-col, #match-col, #game_report, #team-col, #number-col, #number-btn-col').show()
                break
            case 'player_medal':
                jq('#competition-col, #match-col, #game_report, #team-col, #number-col, #number-btn-col, #medal-col').show()
                break
            case 'team':
                jq('#competition-col, #match-col, #game_report, #team-col').show()
                break
            case 'team_medal':
                jq('#competition-col, #match-col, #game_report, #team-col, #medal-col').show()
                break
            case 'match':
            case 'match2':
                jq('#competition-col, #match-col, #game_report').show()
                break
            case 'match_score':
                jq('#competition-col, #match-col, #game_report, #anime-col').show()
                break
            case 'list_team':
                jq('#competition-col, #match-col, #game_report, #team-col').show()
                break
            case 'list_coachs':
                jq('#competition-col, #match-col, #game_report, #team-col').show()
                break
            case 'final_ranking':
                jq('#competition-col, #start-col').show()
                break
            case 'podium':
                jq('#competition-col, #anime-col').show()
                break
            case 'score':
            case 'score_o':
            case 'score_e':
            case 'score_s':
                jq('#pitch-col, #game_report').show()
                break
            case 'score_club':
            case 'score_club_o':
            case 'score_club_e':
            case 'score_club_s':
                jq('#pitch-col, #game_report').show()
                break
            case 'live':
                jq('#pitch-col, #game_report, #zone-col, #mode-col').show()
                break
            case 'teams':
            case 'teams_club':
            case 'next_game':
            case 'next_game_club':
                jq('#pitch-col, #game_report').show()
                break
            case 'liveteams':
                jq('#pitch-col, #game_report').show()
                break
            case 'multi_score':
                jq('#speaker-col, #count-col').show()
                break
            case 'force_cache_match':
                jq('#competition-col, #match-col, #game_report').show()
                break
            case 'frame_terrains':
                jq('#competition-col, #pitchs-col').show()
                break
            case 'frame_phases':
                jq('#competition-col, #round-col').show()
                break
            case 'frame_categories':
                jq('#competition-col, #lnstart-col, #lnlen-col').show()
                break
            case 'frame_chart':
                jq('#competition-col, #round-col').show()
                break
            case 'frame_details':
            case 'frame_stats':
            case 'frame_classement':
                jq('#competition-col').show()
                break
            case 'frame_team':
                jq('#competition-col, #teamselect-col').show()
                break
            case 'frame_matchs':
                jq('#competition-col, #navgroup-col').show()
                break
            case 'api_players':
            case 'api_stats':
                jq('#competition-col, #competlist-col, #format-col, #option-col').show()
                break
            default:
                break
        }
    })

    jq('.number-btn').click(function () {
        var number = jq(this).data('number')
        console.log(number)
        jq('#number').val(number)
        jq('#confirm').click()
    })

    jq('#confirm, #getUrl').click(function () {
        showUrl = jq(this).data('showurl')
        switch (jq('#confirm').attr('data-pres')) {
            case 'empty':
                url = 'live/tv2.php?show=empty'
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'voie':
                url = 'live/tv2.php?show=voie'
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'list_medals':
                Go_list_medals(jq('#channel').val(), jq('#saison').val(), jq('#competition').val(), showUrl)
                break
            case 'referee':
                Go_referee(jq('#channel').val(), jq('#match').val(), showUrl)
                break
            case 'player':
                Go_player(jq('#channel').val(), jq('#match').val(), jq('#team').val(), jq('#number').val(), showUrl)
                break
            case 'coach':
                Go_coach(jq('#channel').val(), jq('#match').val(), jq('#team').val(), jq('#number').val(), showUrl)
                break
            case 'player_medal':
                Go_player_medal(jq('#channel').val(), jq('#match').val(), jq('#team').val(),
                    jq('#number').val(), jq('#medal').val(), showUrl)
                break
            case 'team':
                Go_team(jq('#channel').val(), jq('#match').val(), jq('#team').val(), showUrl)
                break
            case 'team_medal':
                Go_team_medal(jq('#channel').val(), jq('#match').val(), jq('#team').val(), jq('#medal').val(), showUrl)
                break
            case 'match':
                Go_match(jq('#channel').val(), jq('#match').val(), showUrl)
                break
            case 'match2':
                Go_match2(jq('#channel').val(), jq('#match').val(), showUrl)
                break
            case 'match_score':
                Go_match_score(jq('#channel').val(), jq('#match').val(), jq('#anime').val(), showUrl)
                break
            case 'list_team':
                Go_list_team(jq('#channel').val(), jq('#match').val(), jq('#team').val(), showUrl)
                break
            case 'list_coachs':
                Go_list_coachs(jq('#channel').val(), jq('#match').val(), jq('#team').val(), showUrl)
                break
            case 'final_ranking':
                Go_final_ranking(jq('#channel').val(), jq('#saison').val(), jq('#competition').val(), jq('#start').val(), showUrl)
                break
            case 'podium':
                Go_podium(jq('#channel').val(), jq('#saison').val(), jq('#competition').val(), jq('#anime').val(), showUrl)
                break
            case 'score':
                url = 'live/score.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'score_o':
                url = 'live/score_o.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'score_e':
                url = 'live/score_e.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'score_s':
                url = 'live/score_s.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'score_club':
                url = 'live/score_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'score_club_o':
                url = 'live/score_club_o.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'score_club_e':
                url = 'live/score_club_e.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'score_club_s':
                url = 'live/score_club_s.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'live':
                url = 'app_live/#/' + jq('#codeEvt').val() + '/' + jq('#pitch').val() + '/score/' + jq('#zone').val() + '/' + jq('#mode').val() + '/' + css + '/en/'
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'teams':
                url = 'live/teams.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&anime=' + jq('#anime').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'teams_club':
                url = 'live/teams_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&anime=' + jq('#anime').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'next_game':
                url = 'live/next_game.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&anime=' + jq('#anime').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'next_game_club':
                url = 'live/next_game_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&css=' + css + '&anime=' + jq('#anime').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'liveteams':
                url = 'live/liveteams.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch').val()
                    + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'multi_score':
                url = 'live/multi_score.php?event=' + jq('#codeEvt').val() + '&count=' + jq('#count').val()
                    + '&speaker=' + jq('#speaker').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break

            case 'schema':
                url = 'live/schema.php'
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_terrains':
                url = 'frame_terrains.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&terrains=' + jq('#pitchs').val()
                    + '&filtreJour=' + jq('#jour').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_phases':
                url = 'frame_phases.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Round=' + jq('#round').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_categories':
                url = 'frame_categories.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&terrains=' + jq('#pitchs').val()
                    + '&filtreJour=' + jq('#jour').val()
                    + '&Css=' + css
                    + '&start=' + jq('#lnstart').val()
                    + '&len=' + jq('#lnlen').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_chart':
                url = 'frame_chart.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Round=' + jq('#round').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_details':
                url = 'frame_details.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Round=' + jq('#round').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_team':
                url = 'frame_team.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Team=' + jq('#teamselect').val()
                    + '&Round=' + jq('#round').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_stats':
                url = 'frame_stats.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_classement':
                url = 'frame_classement.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_qr':
                url = 'frame_qr.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_matchs':
                url = 'frame_matchs.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Team=' + jq('#teamselect').val()
                    + '&Round=' + jq('#round').val()
                    + '&Css=' + css
                    + '&navGroup=' + jq('#navgroup').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'api_players':
                url = 'api_players.php?saison=' + jq('#saison').val()
                    + '&competitions=' + jq('#competlist').val()
                    + '&format=' + jq('#format').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'api_stats':
                url = 'api_stats.php?saison=' + jq('#saison').val()
                    + '&competitions=' + jq('#competlist').val()
                    + '&all=' + jq('#option').val()
                    + '&format=' + jq('#format').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break

            case 'force_cache_match':
                url = 'live/force_cache_match.php?match=' + jq('#match').val()
                Go_ajax(url)
                break
        }
    })

    // Article 2
    jq('#competition2').change(function () {
        jq('#filtreCompet2').val(jq('#competition2').val())
        jq('#filtreChannel2').val(jq('#channel2').val())
        jq('#filtrePres2').val(jq('#presentation2').val())
        jq('#filtre_evt').submit()
    })

    jq('#channel2').change(function () {
        jq('#control2').attr('href', 'live/tv2.php?voie=' + jq(this).val())
        jq('#filtreChannel2').val(jq(this).val())
    })

    jq('#match2').change(function () {
        jq('#terrain2').val(jq(this).find('option:selected').data('terrain'))
        jq('#filtreMatch2').val(jq(this).val())
        jq('#filtreCompet2').val(jq('#competition2').val())
    })

    jq('#presentation2').change(function () {
        var presentation2 = jq(this).val()
        jq('#filtrePres2').val(jq(this).val())
        jq('#confirm2').attr('data-pres', presentation2)
        if (presentation2 == '') {
            jq('#img-presentation2').attr('src', 'img/presentations/empty.png')
        } else {
            jq('#img-presentation2').attr('src', 'img/presentations/' + presentation2 + '.png')
        }
        jq('.params2').hide()
        switch (presentation2) {
            case 'list_medals':
                jq('#competition-col2').show()
                break
            case 'referee':
                jq('#competition-col2, #match-col2, #game_report2').show()
                break
            case 'player':
                jq('#competition-col2, #match-col2, #game_report2, #team-col2, #number-col2').show()
                break
            case 'coach':
                jq('#competition-col2, #match-col2, #game_report2, #team-col2, #number-col2').show()
                break
            case 'player_medal':
                jq('#competition-col2, #match-col2, #game_report2, #team-col2, #number-col2, #medal-col2').show()
                break
            case 'team':
                jq('#competition-col2, #match-col2, #game_report2, #team-col2').show()
                break
            case 'team_medal':
                jq('#competition-col2, #match-col2, #game_report2, #team-col2, #medal-col2').show()
                break
            case 'match':
            case 'match2':
                jq('#competition-col2, #match-col2, #game_report2').show()
                break
            case 'match_score':
                jq('#competition-col2, #match-col2, #game_report2, #anime-col2').show()
                break
            case 'list_team':
                jq('#competition-col2, #match-col2, #game_report2, #team-col2').show()
                break
            case 'list_coachs':
                jq('#competition-col2, #match-col2, #game_report2, #team-col2').show()
                break
            case 'final_ranking':
                jq('#competition-col2, #start-col2').show()
                break
            case 'podium':
                jq('#competition-col2, #anime-col2').show()
                break
            case 'score':
            case 'score_o':
            case 'score_e':
            case 'score_s':
                jq('#pitch-col2, #game_report2').show()
                break
            case 'score_club':
            case 'score_club_o':
            case 'score_club_e':
            case 'score_club_s':
                jq('#pitch-col2, #game_report2').show()
                break
            case 'live':
                jq('#pitch-col2, #game_report2, #zone-col2, #mode-col2').show()
                break
            case 'teams':
            case 'teams_club':
            case 'next_game':
            case 'next_game_club':
                jq('#pitch-col2, #game_report2').show()
                break
            case 'liveteams':
                jq('#pitch-col2, #game_report2').show()
                break
            case 'multi_score':
                jq('#speaker-col2, #count-col2').show()
                break
            case 'force_cache_match':
                jq('#competition-col2, #match-col2, #game_report2').show()
                break
            case 'frame_terrains':
                jq('#competition-col2, #pitchs-col2').show()
                break
            case 'frame_phases':
                jq('#competition-col2, #round-col2').show()
                break
            case 'frame_categories':
                jq('#competition-col2, #lnstart-col2, #lnlen-col2').show()
                break
            case 'frame_chart':
                jq('#competition-col2, #round-col2').show()
                break
            case 'frame_details':
            case 'frame_stats':
            case 'frame_classement':
                jq('#competition-col2').show()
                break
            case 'frame_team':
                jq('#competition-col2, #teamselect-col2').show()
                break
            default:
                break
        }
    })

    jq('#confirm2, #getUrl2').click(function () {
        showUrl = jq(this).data('showurl')
        switch (jq('#confirm2').attr('data-pres')) {
            case 'empty':
                url = 'live/tv2.php?show=empty'
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'voie':
                url = 'live/tv2.php?show=voie'
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'list_medals':
                Go_list_medals(jq('#channel2').val(), jq('#saison').val(), jq('#competition2').val(), showUrl)
                break
            case 'referee':
                Go_referee(jq('#channel2').val(), jq('#match2').val(), showUrl)
                break
            case 'player':
                Go_player(jq('#channel2').val(), jq('#match2').val(), jq('#team2').val(), jq('#number2').val(), showUrl)
                break
            case 'coach':
                Go_coach(jq('#channel2').val(), jq('#match2').val(), jq('#team2').val(), jq('#number2').val(), showUrl)
                break
            case 'player_medal':
                Go_player_medal(jq('#channel2').val(), jq('#match2').val(), jq('#team2').val(),
                    jq('#number2').val(), jq('#medal2').val(), showUrl)
                break
            case 'team':
                Go_team(jq('#channel2').val(), jq('#match2').val(), jq('#team2').val(), showUrl)
                break
            case 'team_medal':
                Go_team_medal(jq('#channel2').val(), jq('#match2').val(), jq('#team2').val(), jq('#medal2').val(), showUrl)
                break
            case 'match':
                Go_match(jq('#channel2').val(), jq('#match2').val(), showUrl)
                break
            case 'match2':
                Go_match2(jq('#channel2').val(), jq('#match2').val(), showUrl)
                break
            case 'match_score':
                Go_match_score(jq('#channel2').val(), jq('#match2').val(), jq('#anime2').val(), showUrl)
                break
            case 'list_team':
                Go_list_team(jq('#channel2').val(), jq('#match2').val(), jq('#team2').val(), showUrl)
                break
            case 'list_coachs':
                Go_list_coachs(jq('#channel2').val(), jq('#match2').val(), jq('#team2').val(), showUrl)
                break
            case 'final_ranking':
                Go_final_ranking(jq('#channel2').val(), jq('#saison').val(), jq('#competition2').val(), jq('#start2').val(), showUrl)
                break
            case 'podium':
                Go_podium(jq('#channel2').val(), jq('#saison').val(), jq('#competition2').val(), jq('#anime2').val(), showUrl)
                break
            case 'score':
                url = 'live/score.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'score_o':
                url = 'live/score_o.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'score_e':
                url = 'live/score_e.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'score_s':
                url = 'live/score_s.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'score_club':
                url = 'live/score_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'score_club_o':
                url = 'live/score_club_o.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'score_club_e':
                url = 'live/score_club_e.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'score_club_s':
                url = 'live/score_club_s.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'live':
                url = 'app_live/#/' + jq('#codeEvt').val() + '/' + jq('#pitch2').val() + '/score/' + jq('#zone2').val() + '/' + jq('#mode2').val() + '/' + css + '/en/'
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'teams':
                url = 'live/teams.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&anime=' + jq('#anime2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'teams_club':
                url = 'live/teams_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&anime=' + jq('#anime2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'next_game':
                url = 'live/next_game.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&anime=' + jq('#anime2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'next_game_club':
                url = 'live/next_game_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&css=' + css + '&anime=' + jq('#anime2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'liveteams':
                url = 'live/liveteams.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch2').val()
                    + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'multi_score':
                url = 'live/multi_score.php?event=' + jq('#codeEvt').val() + '&count=' + jq('#count2').val()
                    + '&speaker=' + jq('#speaker2').val()
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break

            case 'schema':
                url = 'live/schema.php'
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'frame_terrains':
                url = 'frame_terrains.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&terrains=' + jq('#pitchs2').val()
                    + '&filtreJour=' + jq('#jour').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'frame_phases':
                url = 'frame_phases.php?'
                    + 'lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition2').val()
                    + '&Round=' + jq('#round2').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'frame_categories':
                url = 'frame_categories.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition2').val()
                    + '&terrains=' + jq('#pitchs2').val()
                    + '&filtreJour=' + jq('#jour').val()
                    + '&Css=' + css
                    + '&start=' + jq('#lnstart2').val()
                    + '&len=' + jq('#lnlen2').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_chart':
                url = 'frame_chart.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition2').val()
                    + '&Round=' + jq('#round2').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_details':
                url = 'frame_details.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition2').val()
                    + '&Round=' + jq('#round2').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_team':
                url = 'frame_team.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition2').val()
                    + '&Team=' + jq('#teamselect2').val()
                    + '&Round=' + jq('#round2').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_stats':
                url = 'frame_stats.php?'
                    + 'lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition2').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'frame_classement':
                url = 'frame_classement.php?'
                    + 'lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition2').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel2').val(), url, showUrl)
                break
            case 'frame_qr':
                url = 'frame_qr.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break

            case 'force_cache_match':
                url = 'live/force_cache_match.php?match=' + jq('#match2').val()
                Go_ajax(url)
                break
        }
    })

    // Article 3
    jq('#competition3').change(function () {
        jq('#filtreCompet3').val(jq('#competition3').val())
        jq('#filtreChannel3').val(jq('#channel3').val())
        jq('#filtrePres3').val(jq('#presentation3').val())
        jq('#filtre_evt').submit()
    })

    jq('#channel3').change(function () {
        jq('#control3').attr('href', 'live/tv3.php?voie=' + jq(this).val())
        jq('#filtreChannel3').val(jq(this).val())
    })

    jq('#match3').change(function () {
        jq('#terrain3').val(jq(this).find('option:selected').data('terrain'))
        jq('#filtreMatch3').val(jq(this).val())
        jq('#filtreCompet3').val(jq('#competition3').val())
    })

    jq('#presentation3').change(function () {
        var presentation3 = jq(this).val()
        jq('#filtrePres3').val(jq(this).val())
        jq('#confirm3').attr('data-pres', presentation3)
        if (presentation3 == '') {
            jq('#img-presentation3').attr('src', 'img/presentations/empty.png')
        } else {
            jq('#img-presentation3').attr('src', 'img/presentations/' + presentation3 + '.png')
        }
        jq('.params3').hide()
        switch (presentation3) {
            case 'list_medals':
                jq('#competition-col3').show()
                break
            case 'referee':
                jq('#competition-col3, #match-col3, #game_report3').show()
                break
            case 'player':
                jq('#competition-col3, #match-col3, #game_report3, #team-col3, #number-col3').show()
                break
            case 'coach':
                jq('#competition-col3, #match-col3, #game_report3, #team-col3, #number-col3').show()
                break
            case 'player_medal':
                jq('#competition-col3, #match-col3, #game_report3, #team-col3, #number-col3, #medal-col3').show()
                break
            case 'team':
                jq('#competition-col3, #match-col3, #game_report3, #team-col3').show()
                break
            case 'team_medal':
                jq('#competition-col3, #match-col3, #game_report3, #team-col3, #medal-col3').show()
                break
            case 'match':
            case 'match2':
                jq('#competition-col3, #match-col3, #game_report3').show()
                break
            case 'match_score':
                jq('#competition-col3, #match-col3, #game_report3, #anime-col3').show()
                break
            case 'list_team':
                jq('#competition-col3, #match-col3, #game_report3, #team-col3').show()
                break
            case 'list_coachs':
                jq('#competition-col3, #match-col3, #game_report3, #team-col3').show()
                break
            case 'final_ranking':
                jq('#competition-col3, #start-col3').show()
                break
            case 'podium':
                jq('#competition-col3, #anime-col3').show()
                break
            case 'score':
            case 'score_o':
            case 'score_e':
            case 'score_s':
                jq('#pitch-col3, #game_report3').show()
                break
            case 'score_club':
            case 'score_club_o':
            case 'score_club_e':
            case 'score_club_s':
                jq('#pitch-col3, #game_report3').show()
                break
            case 'live':
                jq('#pitch-col3, #game_report3, #zone-col3, #mode-col3').show()
                break
            case 'teams':
            case 'teams_club':
            case 'next_game':
            case 'next_game_club':
                jq('#pitch-col3, #game_report3').show()
                break
            case 'liveteams':
                jq('#pitch-col3, #game_report3').show()
                break
            case 'multi_score':
                jq('#speaker-col3, #count-col3').show()
                break
            case 'force_cache_match':
                jq('#competition-col3, #match-col3, #game_report3').show()
                break
            case 'frame_terrains':
                jq('#competition-col3, #pitchs-col3').show()
                break
            case 'frame_phases':
                jq('#competition-col3, #round-col3').show()
                break
            case 'frame_categories':
                jq('#competition-col3, #lnstart-col3, #lnlen-col3').show()
                break
            case 'frame_chart':
                jq('#competition-col3, #round-col3').show()
                break
            case 'frame_details':
            case 'frame_stats':
            case 'frame_classement':
                jq('#competition-col3').show()
                break
            case 'frame_team':
                jq('#competition-col3, #teamselect-col3').show()
                break
            default:
                break
        }
    })

    jq('#confirm3, #getUrl3').click(function () {
        showUrl = jq(this).data('showurl')
        switch (jq('#confirm3').attr('data-pres')) {
            case 'empty':
                url = 'live/tv2.php?show=empty'
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'voie':
                url = 'live/tv2.php?show=voie'
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'list_medals':
                Go_list_medals(jq('#channel3').val(), jq('#saison').val(), jq('#competition3').val(), showUrl)
                break
            case 'referee':
                Go_referee(jq('#channel3').val(), jq('#match3').val(), showUrl)
                break
            case 'player':
                Go_player(jq('#channel3').val(), jq('#match3').val(), jq('#team3').val(), jq('#number3').val(), showUrl)
                break
            case 'coach':
                Go_coach(jq('#channel3').val(), jq('#match3').val(), jq('#team3').val(), jq('#number3').val(), showUrl)
                break
            case 'player_medal':
                Go_player_medal(jq('#channel3').val(), jq('#match3').val(), jq('#team3').val(), jq('#number3').val(),
                    jq('#medal3').val(), showUrl)
                break
            case 'team':
                Go_team(jq('#channel3').val(), jq('#match3').val(), jq('#team3').val(), showUrl)
                break
            case 'team_medal':
                Go_team_medal(jq('#channel3').val(), jq('#match3').val(), jq('#team3').val(), jq('#medal3').val(), showUrl)
                break
            case 'match':
                Go_match(jq('#channel3').val(), jq('#match3').val(), showUrl)
                break
            case 'match2':
                Go_match2(jq('#channel3').val(), jq('#match3').val(), showUrl)
                break
            case 'match_score':
                Go_match_score(jq('#channel3').val(), jq('#match3').val(), jq('#anime3').val(), showUrl)
                break
            case 'list_team':
                Go_list_team(jq('#channel3').val(), jq('#match3').val(), jq('#team3').val(), showUrl)
                break
            case 'list_coachs':
                Go_list_coachs(jq('#channel3').val(), jq('#match3').val(), jq('#team3').val(), showUrl)
                break
            case 'final_ranking':
                Go_final_ranking(jq('#channel3').val(), jq('#saison').val(), jq('#competition3').val(), jq('#start3').val(), showUrl)
                break
            case 'podium':
                Go_podium(jq('#channel3').val(), jq('#saison').val(), jq('#competition3').val(), jq('#anime3').val(), showUrl)
                break
            case 'score':
                url = 'live/score.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'score_o':
                url = 'live/score_o.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'score_e':
                url = 'live/score_e.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'score_s':
                url = 'live/score_s.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'score_club':
                url = 'live/score_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'score_club_o':
                url = 'live/score_club_o.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'score_club_e':
                url = 'live/score_club_e.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'score_club_s':
                url = 'live/score_club_s.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'live':
                url = 'app_live/#/' + jq('#codeEvt').val() + '/' + jq('#pitch3').val() + '/score/' + jq('#zone3').val() + '/' + jq('#mode3').val() + '/' + css + '/en/'
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'teams':
                url = 'live/teams.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&anime=' + jq('#anime3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'teams_club':
                url = 'live/teams_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&anime=' + jq('#anime3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'next_game':
                url = 'live/next_game.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&anime=' + jq('#anime3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'next_game_club':
                url = 'live/next_game_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&css=' + css + '&anime=' + jq('#anime3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'liveteams':
                url = 'live/liveteams.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch3').val()
                    + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'multi_score':
                url = 'live/multi_score.php?event=' + jq('#codeEvt').val() + '&count=' + jq('#count3').val()
                    + '&speaker=' + jq('#speaker3').val()
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break

            case 'schema':
                url = 'live/schema.php'
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'frame_terrains':
                url = 'frame_terrains.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&terrains=' + jq('#pitchs3').val()
                    + '&filtreJour=' + jq('#jour').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'frame_phases':
                url = 'frame_phases.php?'
                    + 'lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition3').val()
                    + '&Round=' + jq('#round3').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'frame_categories':
                url = 'frame_categories.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition3').val()
                    + '&terrains=' + jq('#pitchs3').val()
                    + '&filtreJour=' + jq('#jour').val()
                    + '&Css=' + css
                    + '&start=' + jq('#lnstart3').val()
                    + '&len=' + jq('#lnlen3').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_chart':
                url = 'frame_chart.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition3').val()
                    + '&Round=' + jq('#round3').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_details':
                url = 'frame_details.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition3').val()
                    + '&Round=' + jq('#round3').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_team':
                url = 'frame_team.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition3').val()
                    + '&Team=' + jq('#teamselect3').val()
                    + '&Round=' + jq('#round3').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_stats':
                url = 'frame_stats.php?'
                    + 'lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition3').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'frame_classement':
                url = 'frame_classement.php?'
                    + 'lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition3').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel3').val(), url, showUrl)
                break
            case 'frame_qr':
                url = 'frame_qr.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break

            case 'force_cache_match':
                url = 'live/force_cache_match.php?match=' + jq('#match3').val()
                Go_ajax(url)
                break
        }
    })

    // Article 4
    jq('#competition4').change(function () {
        jq('#filtreCompet4').val(jq('#competition4').val())
        jq('#filtreChannel4').val(jq('#channel4').val())
        jq('#filtrePres4').val(jq('#presentation4').val())
        jq('#filtre_evt').submit()
    })

    jq('#channel4').change(function () {
        jq('#control4').attr('href', 'live/tv2.php?voie=' + jq(this).val())
        jq('#filtreChannel4').val(jq(this).val())
    })

    jq('#match4').change(function () {
        jq('#terrain4').val(jq(this).find('option:selected').data('terrain'))
        jq('#filtreMatch4').val(jq(this).val())
        jq('#filtreCompet4').val(jq('#competition4').val())
    })

    jq('#presentation4').change(function () {
        var presentation4 = jq(this).val()
        jq('#filtrePres4').val(jq(this).val())
        jq('#confirm4').attr('data-pres', presentation4)
        if (presentation4 == '') {
            jq('#img-presentation4').attr('src', 'img/presentations/empty.png')
        } else {
            jq('#img-presentation4').attr('src', 'img/presentations/' + presentation4 + '.png')
        }
        jq('.params4').hide()
        switch (presentation4) {
            case 'list_medals':
                jq('#competition-col4').show()
                break
            case 'referee':
                jq('#competition-col4, #match-col4, #game_report4').show()
                break
            case 'player':
                jq('#competition-col4, #match-col4, #game_report4, #team-col4, #number-col4').show()
                break
            case 'coach':
                jq('#competition-col4, #match-col4, #game_report4, #team-col4, #number-col4').show()
                break
            case 'player_medal':
                jq('#competition-col4, #match-col4, #game_report4, #team-col4, #number-col4, #medal-col4').show()
                break
            case 'team':
                jq('#competition-col4, #match-col4, #game_report4, #team-col4').show()
                break
            case 'team_medal':
                jq('#competition-col4, #match-col4, #game_report4, #team-col4, #medal-col4').show()
                break
            case 'match':
            case 'match2':
                jq('#competition-col4, #match-col4, #game_report4').show()
                break
            case 'match_score':
                jq('#competition-col4, #match-col4, #game_report4, #anime-col4').show()
                break
            case 'list_team':
                jq('#competition-col4, #match-col4, #game_report4, #team-col4').show()
                break
            case 'list_coachs':
                jq('#competition-col4, #match-col4, #game_report4, #team-col4').show()
                break
            case 'final_ranking':
                jq('#competition-col4, #start-col4').show()
                break
            case 'podium':
                jq('#competition-col4, #anime-col4').show()
                break
            case 'score':
            case 'score_o':
            case 'score_e':
            case 'score_s':
                jq('#pitch-col4, #game_report4').show()
                break
            case 'score_club':
            case 'score_club_o':
            case 'score_club_e':
            case 'score_club_s':
                jq('#pitch-col4, #game_report4').show()
                break
            case 'live':
                jq('#pitch-col4, #game_report4, #zone-col4, #mode-col4').show()
                break
            case 'teams':
            case 'teams_club':
            case 'next_game':
            case 'next_game_club':
                jq('#pitch-col4, #game_report4').show()
                break
            case 'liveteams':
                jq('#pitch-col4, #game_report4').show()
                break
            case 'multi_score':
                jq('#speaker-col4, #count-col4').show()
                break
            case 'force_cache_match':
                jq('#competition-col4, #match-col4, #game_report4').show()
                break
            case 'frame_terrains':
                jq('#competition-col4, #pitchs-col4').show()
                break
            case 'frame_phases':
                jq('#competition-col4, #round-col4').show()
                break
            case 'frame_categories':
                jq('#competition-col4, #lnstart-col4, #lnlen-col4').show()
                break
            case 'frame_chart':
                jq('#competition-col4, #round-col4').show()
                break
            case 'frame_details':
            case 'frame_stats':
            case 'frame_classement':
                jq('#competition-col4').show()
                break
            case 'frame_team':
                jq('#competition-col4, #teamselect-col4').show()
                break
            default:
                break
        }
    })

    jq('#confirm4, #getUrl4').click(function () {
        showUrl = jq(this).data('showurl')
        switch (jq('#confirm4').attr('data-pres')) {
            case 'empty':
                url = 'live/tv2.php?show=empty'
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'voie':
                url = 'live/tv2.php?show=voie'
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'list_medals':
                Go_list_medals(jq('#channel4').val(), jq('#saison').val(), jq('#competition4').val(), showUrl)
                break
            case 'referee':
                Go_referee(jq('#channel4').val(), jq('#match4').val(), showUrl)
                break
            case 'player':
                Go_player(jq('#channel4').val(), jq('#match4').val(), jq('#team4').val(), jq('#number4').val(), showUrl)
                break
            case 'coach':
                Go_coach(jq('#channel4').val(), jq('#match4').val(), jq('#team4').val(), jq('#number4').val(), showUrl)
                break
            case 'player_medal':
                Go_player_medal(jq('#channel4').val(), jq('#match4').val(), jq('#team4').val(), jq('#number4').val(),
                    jq('#medal4').val(), showUrl)
                break
            case 'team':
                Go_team(jq('#channel4').val(), jq('#match4').val(), jq('#team4').val(), showUrl)
                break
            case 'team_medal':
                Go_team_medal(jq('#channel4').val(), jq('#match4').val(), jq('#team4').val(), jq('#medal4').val(), showUrl)
                break
            case 'match':
                Go_match(jq('#channel4').val(), jq('#match4').val(), showUrl)
                break
            case 'match2':
                Go_match2(jq('#channel4').val(), jq('#match4').val(), showUrl)
                break
            case 'match_score':
                Go_match_score(jq('#channel4').val(), jq('#match4').val(), jq('#anime4').val(), showUrl)
                break
            case 'list_team':
                Go_list_team(jq('#channel4').val(), jq('#match4').val(), jq('#team4').val(), showUrl)
                break
            case 'list_coachs':
                Go_list_coachs(jq('#channel4').val(), jq('#match4').val(), jq('#team4').val(), showUrl)
                break
            case 'final_ranking':
                Go_final_ranking(jq('#channel4').val(), jq('#saison').val(), jq('#competition4').val(), jq('#start4').val(), showUrl)
                break
            case 'podium':
                Go_podium(jq('#channel4').val(), jq('#saison').val(), jq('#competition4').val(), jq('#anime4').val(), showUrl)
                break
            case 'score':
                url = 'live/score.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'score_o':
                url = 'live/score_o.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'score_e':
                url = 'live/score_e.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'score_s':
                url = 'live/score_s.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'score_club':
                url = 'live/score_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'score_club_o':
                url = 'live/score_club_o.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'score_club_e':
                url = 'live/score_club_e.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'score_club_s':
                url = 'live/score_club_s.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'live':
                url = 'app_live/#/' + jq('#codeEvt').val() + '/' + jq('#pitch4').val() + '/score/' + jq('#zone4').val() + '/' + jq('#mode4').val() + '/' + css + '/en/'
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'teams':
                url = 'live/teams.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&anime=' + jq('#anime4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'teams_club':
                url = 'live/teams_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&anime=' + jq('#anime4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'next_game':
                url = 'live/next_game.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&anime=' + jq('#anime4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'next_game_club':
                url = 'live/next_game_club.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&css=' + css + '&anime=' + jq('#anime4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'liveteams':
                url = 'live/liveteams.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#pitch4').val()
                    + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'multi_score':
                url = 'live/multi_score.php?event=' + jq('#codeEvt').val() + '&count=' + jq('#count4').val()
                    + '&speaker=' + jq('#speaker4').val()
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break

            case 'schema':
                url = 'live/schema.php'
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'frame_terrains':
                url = 'frame_terrains.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&terrains=' + jq('#pitchs4').val()
                    + '&filtreJour=' + jq('#jour').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'frame_phases':
                url = 'frame_phases.php?'
                    + 'lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition4').val()
                    + '&Round=' + jq('#round4').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'frame_categories':
                url = 'frame_categories.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition4').val()
                    + '&terrains=' + jq('#pitchs4').val()
                    + '&filtreJour=' + jq('#jour').val()
                    + '&Css=' + css
                    + '&start=' + jq('#lnstart4').val()
                    + '&len=' + jq('#lnlen4').val()
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_chart':
                url = 'frame_chart.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition4').val()
                    + '&Round=' + jq('#round4').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_details':
                url = 'frame_details.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition4').val()
                    + '&Round=' + jq('#round4').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_team':
                url = 'frame_team.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition4').val()
                    + '&Team=' + jq('#teamselect4').val()
                    + '&Round=' + jq('#round4').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break
            case 'frame_stats':
                url = 'frame_stats.php?'
                    + 'lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition4').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'frame_classement':
                url = 'frame_classement.php?'
                    + 'lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition4').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel4').val(), url, showUrl)
                break
            case 'frame_qr':
                url = 'frame_qr.php?event=' + jq('#codeEvt').val()
                    + '&lang=en&Saison=' + jq('#saison').val()
                    + '&Compet=' + jq('#competition').val()
                    + '&Css=' + css
                ChangeVoie(jq('#channel').val(), url, showUrl)
                break

            case 'force_cache_match':
                url = 'live/force_cache_match.php?match=' + jq('#match4').val()
                Go_ajax(url)
                break
        }
    })

    // Init
    jq('#presentation, #presentation2, #presentation3, #presentation4').change()
    jq('#match, #match2, #match3, #match4').change()

})




function Go_list_medals (channel, saison, competition, showUrl = 0) {
    var param
    param = "show=list_medals"
    param += "&voie=" + channel
    param += "&saison=" + saison
    param += "&competition=" + competition
    Go(param, showUrl)
}

function Go_referee (channel, match, showUrl = 0) {
    var param
    param = "show=referee"
    param += "&voie=" + channel
    param += "&match=" + match
    Go(param, showUrl)
}

function Go_player (channel, match, team, number, showUrl = 0) {
    var param
    param = "show=player"
    param += "&voie=" + channel
    param += "&match=" + match
    param += "&team=" + team
    param += "&number=" + number
    Go(param, showUrl)
}

function Go_coach (channel, match, team, number, showUrl = 0) {
    var param
    param = "show=coach"
    param += "&voie=" + channel
    param += "&match=" + match
    param += "&team=" + team
    param += "&number=" + number
    Go(param, showUrl)
}

function Go_player_medal (channel, match, team, number, medal, showUrl = 0) {
    var param
    param = "show=player_medal"
    param += "&voie=" + channel
    param += "&match=" + match
    param += "&team=" + team
    param += "&number=" + number
    param += "&medal=" + medal
    Go(param, showUrl)
}

function Go_team (channel, match, team, showUrl = 0) {
    var param
    param = "show=team"
    param += "&voie=" + channel
    param += "&match=" + match
    param += "&team=" + team
    Go(param, showUrl)
}

function Go_team_medal (channel, match, team, medal, showUrl = 0) {
    var param
    param = "show=team_medal"
    param += "&voie=" + channel
    param += "&match=" + match
    param += "&team=" + team
    param += "&medal=" + medal
    Go(param, showUrl)
}

function Go_match (channel, match, showUrl = 0) {
    var param
    param = "show=match"
    param += "&voie=" + channel
    param += "&match=" + match
    Go(param, showUrl)
}

function Go_match2 (channel, match, showUrl = 0) {
    var param
    param = "show=match2"
    param += "&voie=" + channel
    param += "&match=" + match
    Go(param, showUrl)
}

function Go_match_score (channel, match, anime, showUrl = 0) {
    var param
    param = "show=match_score"
    param += "&voie=" + channel
    param += "&match=" + match
    param += "&anime=" + anime
    Go(param, showUrl)
}

function Go_list_team (channel, match, team, showUrl = 0) {
    var param
    param = "show=list_team"
    param += "&voie=" + channel
    param += "&match=" + match
    param += "&team=" + team
    Go(param, showUrl)
}

function Go_list_coachs (channel, match, team, showUrl = 0) {
    var param
    param = "show=list_coachs"
    param += "&voie=" + channel
    param += "&match=" + match
    param += "&team=" + team
    Go(param, showUrl)
}

function Go_final_ranking (channel, saison, competition, start, showUrl = 0) {
    var param
    param = "show=final_ranking"
    param += "&voie=" + channel
    param += "&saison=" + saison
    param += "&competition=" + competition
    param += "&start=" + start
    Go(param, showUrl)
}

function Go_podium (channel, saison, competition, anime, showUrl = 0) {
    var param
    param = "show=podium"
    param += "&voie=" + channel
    param += "&saison=" + saison
    param += "&competition=" + competition
    param += "&anime=" + anime
    Go(param, showUrl)
}

function Go_raz () {
    var param
    param = "show=reset"
    param += "&voie=" + jq('#list_team_channel').val()
    Go(param)
}

function Go (param, showUrl = 0) {
    if (showUrl > 0) {
        jq('#showUrl' + showUrl).val('live/tv2.php?' + param)
    } else {
        jq.ajax({
            type: "GET",
            url: "live/ajax_change_tv.php",
            dataType: "html",
            data: param,
            cache: false,
            success: function (htmlData) {
                alerte(htmlData)
            }
        })
    }
}

function Go_ajax (param) {
    jq.ajax({
        type: "GET",
        url: param,
        dataType: "html",
        cache: false,
        success: function (htmlData) {
            alerte(htmlData)
        }
    })
}

