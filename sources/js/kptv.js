//jq = jQuery.noConflict();
jq(document).ready(function(){
    jq('#codeEvt, #jour').change(function(){
        jq('#filtre_evt').submit();
    });
    
    jq('#competition').change(function(){
        jq('#filtreCompet').val(jq('#competition').val());
        jq('#filtreChannel').val(jq('#channel').val());
        jq('#filtrePres').val(jq('#presentation').val());
        jq('#filtre_evt').submit();
    });
    
    jq('#channel').change(function(){
        jq('#control').attr('href', 'live/tv2.php?voie=' + jq(this).val());
        jq('#filtreChannel').val(jq(this).val());
    })
    
    jq('#match').change(function(){
        jq('#terrain').val(jq(this).find('option:selected').data('terrain'));
    })
    
    jq('#presentation').change(function(){
        var presentation = jq(this).val();
        jq('#confirm').attr('data-pres', presentation)
                .find('img').attr('src', 'img/presentations/' + presentation + '.png')
        jq('.params').hide();
        switch(presentation) {
            case 'list_medals':
                break;
            case 'referee':
                jq('#match-col').show();
                break;
            case 'player':
                jq('#match-col, #team-col, #number-col').show();
                break;
            case 'player_medal':
                jq('#match-col, #team-col, #number-col, #medal-col').show();
                break;
            case 'team':
                jq('#match-col, #team-col').show();
                break;
            case 'team_medal':
                jq('#match-col, #team-col, #medal-col').show();
                break;
            case 'match':
                jq('#match-col').show();
                break;
            case 'match_score':
                jq('#match-col').show();
                break;
            case 'list_team':
                jq('#match-col, #team-col').show();
                break;
            case 'list_coachs':
                jq('#match-col, #team-col').show();
                break;
            case 'final_ranking':
                jq('#start-col').show();
                break;
            case 'score':
                jq('#match-col, #speaker-col').show();
                break;
            case 'multi_score':
                jq('#speaker-col, #count-col').show();
                break;
            case 'force_cache_match':
                jq('#match-col').show();
                break;
            case 'frame_terrains':
                jq('#pitchs-col').show();
                break;
            case 'frame_phases':
                jq('#round-col').show();
                break;
            default:
                break;
        }
    });
    
    jq('#confirm').click(function(){
        switch(jq('#confirm').attr('data-pres')) {
            case 'empty':
                url = 'live/tv2.php?show=empty';
                ChangeVoie(jq('#channel').val(), url);
                break;
            case 'voie':
                url = 'live/tv2.php?show=voie';
                ChangeVoie(jq('#channel').val(), url);
                break;
            case 'list_medals':
                Go_list_medals(jq('#channel').val(), jq('#saison').val(), jq('#competition').val())
                break;
            case 'referee':
                Go_referee(jq('#channel').val(), jq('#match').val())
                break;
            case 'player':
                Go_player(jq('#channel').val(), jq('#match').val(), jq('#team').val(), jq('#number').val())
                break;
            case 'player_medal':
                Go_player_medal(jq('#channel').val(), jq('#match').val(), jq('#team').val(), jq('#number').val(), jq('#medal').val())
                break;
            case 'team':
                Go_team(jq('#channel').val(), jq('#match').val(), jq('#team').val())
                break;
            case 'team_medal':
                Go_team_medal(jq('#channel').val(), jq('#match').val(), jq('#team').val(), jq('#medal').val())
                break;
            case 'match':
                Go_match(jq('#channel').val(), jq('#match').val())
                break;
            case 'match_score':
                Go_match_score(jq('#channel').val(), jq('#match').val())
                break;
            case 'list_team':
                Go_list_team(jq('#channel').val(), jq('#match').val(), jq('#team').val())
                break;
            case 'list_coachs':
                Go_list_coachs(jq('#channel').val(), jq('#match').val(), jq('#team').val())
                break;
            case 'final_ranking':
                Go_final_ranking(jq('#channel').val(), jq('#saison').val(), jq('#competition').val(), jq('#start').val())
                break;
            case 'score':
                url = 'live/score.php?event=' + jq('#codeEvt').val() + '&terrain=' + jq('#terrain').val() + '&speaker=' + jq('#speaker').val();
                ChangeVoie(jq('#channel').val(), url);
                break;
            case 'multi_score':
                url = 'live/multi_score.php?event=' + jq('#codeEvt').val() + '&count=' + jq('#count').val() + '&speaker=' + jq('#speaker').val();
                ChangeVoie(jq('#channel').val(), url);
                break;
                
            case 'schema':
                url = 'live/schema.php';
                ChangeVoie(jq('#channel').val(), url);
                break;
            case 'frame_terrains':
//                frame_terrains.php?Saison=2017&Group=CE&lang=en&Css=sainto_hd&filtreJour=2017-08-24&terrains=1,2,3,4
                url = 'frame_terrains.php?event=' + jq('#codeEvt').val() 
                        + '&lang=en&Saison=' + jq('#saison').val() 
                        + '&Css=sainto_hd&terrains=' + jq('#pitchs').val()
                        + '&filtreJour=' + jq('#jour').val();
                ChangeVoie(jq('#channel').val(), url);
                break;
            case 'frame_phases':
                url = 'frame_phases.php?' 
                        + 'lang=en&Saison=' + jq('#saison').val() 
                        + '&Compet=' + jq('#competition').val() 
                        + '&Round=' + jq('#round').val() 
//                        + '&Css=sainto_hd'
                ChangeVoie(jq('#channel').val(), url);
                break;
                
            case 'force_cache_match':
                url = 'live/force_cache_match.php?match=' + jq('#match').val();
                Go_ajax(url);
                break;
        } 
    });
    
    // Init
    jq('#presentation').change();
    jq('#match').change();

});


function Go_list_medals(channel, saison, competition)
{
	var param;
	param  = "show=list_medals";
	param += "&voie=" + channel;
	param += "&saison=" + saison;
	param += "&competition=" + competition;
	Go(param);
}

function Go_referee(channel, match)
{
	var param;
	param  = "show=referee";
	param += "&voie=" + channel;
	param += "&match=" + match;
	Go(param);
}

function Go_player(channel, match, team, number)
{
	var param;
	param  = "show=player";
	param += "&voie=" + channel;
	param += "&match=" + match;
	param += "&team=" + team;
	param += "&number=" + number;
	Go(param);
}

function Go_player_medal(channel, match, team, number, medal)
{
	var param;
	param  = "show=player_medal";
	param += "&voie=" + channel;
	param += "&match=" + match;
	param += "&team=" + team;
	param += "&number=" + number;
	param += "&medal=" + medal;
	Go(param);
}

function Go_team(channel, match, team)
{
	var param;
	param  = "show=team";
	param += "&voie=" + channel;
	param += "&match=" + match;
	param += "&team=" + team;
	Go(param);
}

function Go_team_medal(channel, match, team, medal)
{
	var param;
	param  = "show=team_medal";
	param += "&voie=" + channel;
	param += "&match=" + match;
	param += "&team=" + team;
	param += "&medal=" + medal;
	Go(param);
}

function Go_match(channel, match)
{
	var param;
	param  = "show=match";
	param += "&voie=" + channel;
	param += "&match=" + match;
	Go(param);
}

function Go_match_score(channel, match)
{
	var param;
	param  = "show=match_score";
	param += "&voie=" + channel;
	param += "&match=" + match;
	Go(param);
}

function Go_list_team(channel, match, team)
{
	var param;
	param  = "show=list_team";
	param += "&voie=" + channel;
	param += "&match=" + match;
	param += "&team=" + team;
	Go(param);
}

function Go_list_coachs(channel, match, team)
{
	var param;
	param  = "show=list_coachs";
	param += "&voie=" + channel;
	param += "&match=" + match;
	param += "&team=" + team;
	Go(param);
}

function Go_final_ranking(channel, saison, competition, start)
{
	var param;
	param  = "show=final_ranking";
	param += "&voie=" + channel;
	param += "&saison=" + saison;
	param += "&competition=" + competition;
	param += "&start=" + start;
	Go(param);
}

//function Go_presentation(channel, url)
//{
//	ChangeVoie(channel, url);
//}

function Go_raz()
{
	var param;
	param  = "show=reset";
	param += "&voie="+jq('#list_team_channel').val();
	Go(param);
}

function Go(param)
{
//    alert("ajax_change_tv.php?"+param);
    jq.ajax({   type: "GET", 
                url: "live/ajax_change_tv.php", 
                dataType: "html", 
                data: param, 
                cache: false, 
                success: function(htmlData) {
						alerte(htmlData);
				}
	});
}

function Go_ajax(param)
{
    jq.ajax({   type: "GET", 
                url: param, 
                dataType: "html", 
                cache: false, 
                success: function(htmlData) {
						alerte(htmlData);
				}
	});
}

