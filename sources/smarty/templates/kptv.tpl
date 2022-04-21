<div class="container-fluid" id="selector">
  <article id="titre">
    <form id="filtre_evt" name="filtre_evt" method="post" action="">
      <h1 class='col-sm-3'>{#Controle_tv#}</h1>
      <div class='col-sm-3'>
        <label>{#Evenement#}</label>
        <select id="codeEvt" name="codeEvt">
          <option value="0">{#Selectionnez#}</option>
          {section name=i loop=$arrayEvts}
            <option value="{$arrayEvts[i].Id}" {$arrayEvts[i].selected}>{$arrayEvts[i].Id}-{$arrayEvts[i].Libelle}
              ({$arrayEvts[i].Lieu})</option>
          {/section}
        </select>
      </div>
      <div class='col-sm-2'>
        <label>{#Date#}</label>
        <select id="jour" name="jour">
          <option value="">{#Toutes#}</option>
          {section name=i loop=$arrayJours}
            <option value="{$arrayJours[i]}" {if $arrayJours[i] == $jour}selected{/if}>{$arrayJours[i]}</option>
          {/section}
        </select>
        <input type="hidden" id="saison" name="saison" value="{$saison}">
        <input type="hidden" id="filtrePres" name="filtrePres" value="{$filtrePres}">
        <input type="hidden" id="filtreCompet" name="filtreCompet" value="{$filtreCompet}">
        <input type="hidden" id="filtreChannel" name="filtreChannel" value="{$filtreChannel}">
        <input type="hidden" id="filtreMatch" name="filtreMatch" value="{$filtreMatch}">
        <input type="hidden" id="filtrePres2" name="filtrePres2" value="{$filtrePres2}">
        <input type="hidden" id="filtreCompet2" name="filtreCompet2" value="{$filtreCompet2}">
        <input type="hidden" id="filtreChannel2" name="filtreChannel2" value="{$filtreChannel2}">
        <input type="hidden" id="filtreMatch2" name="filtreMatch2" value="{$filtreMatch2}">
        <input type="hidden" id="filtrePres3" name="filtrePres3" value="{$filtrePres3}">
        <input type="hidden" id="filtreCompet3" name="filtreCompet3" value="{$filtreCompet3}">
        <input type="hidden" id="filtreChannel3" name="filtreChannel3" value="{$filtreChannel3}">
        <input type="hidden" id="filtreMatch3" name="filtreMatch3" value="{$filtreMatch3}">
        <input type="hidden" id="filtrePres4" name="filtrePres4" value="{$filtrePres4}">
        <input type="hidden" id="filtreCompet4" name="filtreCompet4" value="{$filtreCompet4}">
        <input type="hidden" id="filtreChannel4" name="filtreChannel4" value="{$filtreChannel4}">
        <input type="hidden" id="filtreMatch4" name="filtreMatch4" value="{$filtreMatch4}">
      </div>
      <div class="col-sm-2">
        <label>Style</label>
        <select id="style" name="style">
          <option value="saintomer2022" {if $style == 'saintomer2022'}selected{/if}>SaintOmer 2022</option>
          <option value="welland2018" {if $style == 'welland2018'}selected{/if}>Welland 2018</option>
          <option value="saintomer2017" {if $style == 'saintomer2017'}selected{/if}>SaintOmer 2017</option>
          <option value="thury2014" {if $style == 'thury2014'}selected{/if}>Thury 2014</option>
          <option value="usnational" {if $style == 'usnational'}selected{/if}>US National</option>
          <option value="cna" {if $style == 'cna'}selected{/if}>CNA KP</option>
          <option value="simply" {if $style == 'simply'}selected{/if}>Simple</option>
        </select>
      </div>
      <div class="col-sm-1">
        <label>Lang</label>
        <select id="lang" name="lang">
          <option value="en" {if $lang == 'en'}selected{/if}>EN</option>
          <option value="fr" {if $lang == 'fr'}selected{/if}>FR</option>
        </select>
      </div>
      <div class="col-sm-1">
        <label>&nbsp;</label>
        <input type="submit" value="Save" class="btn btn-primary">
      </div>
    </form>
  </article>
  <!-- Article 1 -->
  <article>
    <div class="col-sm-10">
      <div class="row">
        <div class='col-sm-1'>
          <label>Channel</label>
          <select id="channel" name="channel">
            <optgroup label="Channels">
              <option value='1'>{#Selectionnez#}</option>
              {section name=i start=1 loop=41}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
            <optgroup label="Scenario 1">
              {section name=i start=101 loop=110}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
            <optgroup label="Scenario 2">
              {section name=i start=201 loop=210}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
            <optgroup label="Scenario 3">
              {section name=i start=301 loop=310}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
            <optgroup label="Scenario 4">
              {section name=i start=401 loop=410}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
            <optgroup label="Scenario 5 (Screen 1)">
              {section name=i start=501 loop=510}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
            <optgroup label="Scenario 6 (Screen 2)">
              {section name=i start=601 loop=610}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
            <optgroup label="Scenario 7 (Screen 3)">
              {section name=i start=701 loop=710}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
            <optgroup label="Scenario 8 (Screen 4)">
              {section name=i start=801 loop=810}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
            <optgroup label="Scenario 9">
              {section name=i start=901 loop=910}
                <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                  {$smarty.section.i.index}
                </option>
              {/section}
            </optgroup>
          </select>
          <br>
        </div>
        <div class='col-sm-2'>
          <label>{#Presentation#}</label>
          <select id="presentation" name="presentation">
            <option value="" {if $filtrePres == ''}selected{/if}>{#Selectionnez#}</option>
            <option value="voie" {if $filtrePres == 'voie'}selected{/if}>Channel</option>
            <option value="empty" {if $filtrePres == 'empty'}selected{/if}>Empty page</option>
            <optgroup label="Before game inlays">
              <option value="match" {if $filtrePres == 'match'}selected{/if}>Game (Category & teams)</option>
              <option value="list_team" {if $filtrePres == 'list_team'}selected{/if}>Players list</option>
              <option value="list_coachs" {if $filtrePres == 'list_coachs'}selected{/if}>Coaches list</option>
              <option value="team" {if $filtrePres == 'team'}selected{/if}>Team name</option>
              <option value="referee" {if $filtrePres == 'referee'}selected{/if}>Referees</option>
              <option value="player" {if $filtrePres == 'player'}selected{/if}>Player name</option>
              <option value="coach" {if $filtrePres == 'coach'}selected{/if}>Coach name</option>
            </optgroup>
            <optgroup label="Running game (nations)">
              <option value="score" {if $filtrePres == 'score'}selected{/if}>Live score (nations)</option>
              <option value="score_o" {if $filtrePres == 'score_o'}selected{/if}>Score only (nations)</option>
              <option value="score_e" {if $filtrePres == 'score_e'}selected{/if}>Events only (nations)</option>
              <option value="score_s" {if $filtrePres == 'score_s'}selected{/if}>Static events (nations)</option>
              <option value="teams" {if $filtrePres == 'teams'}selected{/if}>Game & score (nations)</option>
            </optgroup>
            <optgroup label="Running game (clubs)">
              <option value="score_club" {if $filtrePres == 'score_club'}selected{/if}>Live score (clubs)</option>
              <option value="score_club_o" {if $filtrePres == 'score_club_o'}selected{/if}>Score only (clubs)</option>
              <option value="score_club_e" {if $filtrePres == 'score_club_e'}selected{/if}>Events only (clubs)</option>
              <option value="score_club_s" {if $filtrePres == 'score_club_s'}selected{/if}>Static events (clubs)
              </option>
              <option value="teams_club" {if $filtrePres == 'teams_club'}selected{/if}>Game & score (clubs)</option>
              <option value="liveteams" {if $filtrePres == 'liveteams'}selected{/if}>Teams only (clubs)</option>
            </optgroup>
            <optgroup label="Game presentation (next game)">
              <option value="match_score" {if $filtrePres == 'match_score'}selected{/if}>Game & score</option>
            </optgroup>
            <optgroup label="After game inlays">
              {*
                <option value="list_medals" {if $filtrePres == 'list_medals'}selected{/if}>Medals (podium)</option>
                <option value="player_medal" {if $filtrePres == 'player_medal'}selected{/if}>Player medal</option>
                <option value="team_medal" {if $filtrePres == 'team_medal'}selected{/if}>Team medal</option>
              *}
              <option value="final_ranking" {if $filtrePres == 'final_ranking'}selected{/if}>Final ranking</option>
              <option value="podium" {if $filtrePres == 'podium'}selected{/if}>Podium</option>
            </optgroup>
            <optgroup label="Screen display">
              <option value="multi_score" {if $filtrePres == 'multi_score'}selected{/if}>Multi score</option>
              <option value="frame_categories" {if $filtrePres == 'frame_categories'}selected{/if}>Cat. games</option>
              <option value="frame_terrains" {if $filtrePres == 'frame_terrains'}selected{/if}>Pitch games</option>
              <option value="frame_chart" {if $filtrePres == 'frame_chart'}selected{/if}>Progress</option>
              <option value="frame_phases" {if $filtrePres == 'frame_phases'}selected{/if}>Phases</option>
              <option value="frame_details" {if $filtrePres == 'frame_details'}selected{/if}>Details</option>
              <option value="frame_team" {if $filtrePres == 'frame_team'}selected{/if}>Team details</option>
              <option value="frame_stats" {if $filtrePres == 'frame_stats'}selected{/if}>Stats</option>
              <option value="frame_classement" {if $filtrePres == 'frame_classement'}selected{/if}>Ranking</option>
              <option value="frame_qr" {if $filtrePres == 'frame_qr'}selected{/if}>QrCodes</option>
            </optgroup>
            <optgroup label="Website / Smartphone">
              <option value="frame_matchs" {if $filtrePres == 'frame_matchs'}selected{/if}>Games</option>
            </optgroup>
            <optgroup label="API">
              <option value="api_players" {if $filtrePres == 'api_players'}selected{/if}>Players</option>
              <option value="api_stats" {if $filtrePres == 'api_stats'}selected{/if}>Stats</option>
            </optgroup>
            <optgroup label="Cache build">
              <option value="force_cache_match" {if $filtrePres == 'force_cache_match'}selected{/if}>Force cache match
              </option>
            </optgroup>
          </select>
        </div>
        <div class='col-sm-2'>
          <label>{#Competition#}</label>
          <select id="competition" name="competition">
            <option value="" {if '' == $filtreCompet}selected{/if}>{#Selectionnez#}</option>
            {section name=i loop=$arrayCompet}
              <option value="{$arrayCompet[i]}" {if $arrayCompet[i] == $filtreCompet}selected{/if}>{$arrayCompet[i]}
              </option>
            {/section}
          </select>
        </div>
        <div class='col-sm-3 params' id='match-col'>
          <label>{#Match#}</label>
          <select id="match" name="match">
            {section name=i loop=$arrayMatchs}
              <option value="{$arrayMatchs[i].Id}" data-terrain="{$arrayMatchs[i].Terrain}"
                {if $arrayMatchs[i].Id == $filtreMatch}selected{/if}>
                #{$arrayMatchs[i].Numero_ordre} {#Terr#}.{$arrayMatchs[i].Terrain} {$arrayMatchs[i].Heure_match}
                : {$arrayMatchs[i].equipeA} - {$arrayMatchs[i].equipeB} [{$arrayMatchs[i].Phase}]
              </option>
            {/section}
          </select>
          <input type="hidden" id="terrain" name="terrain" value="">
        </div>
        <div class='col-sm-1 params' id='team-col'>
          <label>{#Equipe#}</label>
          <select id="team" name="team">
            <option value="A">A</option>
            <option value="B">B</option>
          </select>
        </div>
        <div class='col-sm-2 params' id='teamselect-col'>
          <label>{#Equipe#}</label>
          <select id="teamselect" name="teamselect">
            {section name=i loop=$arrayEquipes}
              <option value="{$arrayEquipes[i].id_equipe}">{$arrayEquipes[i].libelle_equipe}</option>
            {/section}
          </select>
        </div>
        <div class='col-sm-1 params' id='number-col'>
          <label>{#Joueur#}</label>
          <select id="number" name="number">
            {section name=i start=0 loop=22}
              <option value="{$smarty.section.i.index}">{$smarty.section.i.index}</option>
            {/section}
          </select>
        </div>
        <div class='col-sm-1 params' id='medal-col'>
          <label>{#Medaille#}</label>
          <select id="medal" name="medal">
            <option value="BRONZE">Bronze</option>
            <option value="SILVER">Silver</option>
            <option value="GOLD">Gold</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='count-col'>
          <label>Count</label>
          <select id="count" name="count">
            <option value="4">4</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='pitchs-col'>
          <label>Pitchs</label>
          <input class="form-control" type="text" id="pitchs" name="pitchs" value="1,2,3,4">
        </div>
        <div class='col-sm-1 params' id='round-col'>
          <label>Round</label>
          <select id="round" name="round">
            <option value="*">All</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='start-col'>
          <label>Start</label>
          <select id="start" name="start">
            <option value="0">1-10</option>
            <option value="10">11-20</option>
            <option value="20">21-30</option>
            <option value="30">31-40</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='anime-col'>
          <label>Animate</label>
          <select id="anime" name="anime">
            <option value="0">No</option>
            <option value="1">Yes</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='speaker-col'>
          <label>Speaker</label>
          <select id="speaker" name="speaker">
            <option value="0">Non</option>
            <option value="1">Oui</option>
            <option value="2">Peut-être</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='lnstart-col'>
          <label>Start</label>
          <input type="text" id="lnstart" name="lnstart" value="1" size="2">
        </div>
        <div class='col-sm-1 params' id='lnlen-col'>
          <label>Length</label>
          <input type="text" id="lnlen" name="lnlen" value="0" size="2">
        </div>
        <div class='col-sm-2 params' id='competlist-col'>
          <label>Competitions</label>
          <input type="text" id="competlist" name="competlist" placeholder="CMH,CMF" value="{$filtreCompet}">
        </div>
        <div class='col-sm-2 params' id='option-col'>
          <label>option</label>
          <select id="option" name="option">
            <option value="0">Players with stats</option>
            <option value="1">All players</option>
            <option value="2">All players without stats</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='format-col'>
          <label>format</label>
          <select id="format" name="format">
            <option value="json">json</option>
            <option value="csv">csv</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='navgroup-col'>
          <label>navbar</label>
          <select id="navgroup" name="navgroup">
            <option value="0">no</option>
            <option value="1">yes</option>
          </select>
        </div>
      </div>
      <div class="row params text-right mb5" id="number-btn-col">
        {section name=i start=0 loop=22}
          <button class="btn btn-primary number-btn" data-number="{$smarty.section.i.index}">
            {$smarty.section.i.index}
          </button>
        {/section}
      </div>
      <div class="row">
        <a id="control" class="btn btn-warning col-sm-1" href="live/tv2.php?voie={$filtreChannel}" target="_blank">
          {#Controle#}
        </a>
        <a id="game_report" class="params btn btn-info col-sm-1" href="" target="_blank">Report</a>
        <button id="getUrl" name="getUrl" class="btn btn-success col-sm-1" data-showurl="1">
          Url
        </button>
        <input type='text' readonly="readonly" id='showUrl1' name='showUrl1' class="col-sm-7">
        <button id="confirm" name="confirm" class="btn btn-primary pull-right col-sm-2" data-pres="" data-showurl="0">
          {#Activer#}
        </button>
      </div>
    </div>
    <div class="col-sm-2">
      <img id="img-presentation" src="img/logo/2017-WG.jpg" class="img-rounded img-fluid">
    </div>
  </article>

  <!-- Article 2 -->
  <article>
    <div class="col-sm-10">
      <div class="row">
        <div class='col-sm-1'>
          <label>Channel</label>
          <select id="channel2" name="channel2">
            <option value='1'>{#Selectionnez#}</option>
            {section name=i start=1 loop=41}
              <option value="{$smarty.section.i.index}" {if $filtreChannel2 == $smarty.section.i.index}selected{/if}>
                {$smarty.section.i.index}
              </option>
            {/section}
          </select>
          <br>
        </div>
        <div class='col-sm-2'>
          <label>{#Presentation#}</label>
          <select id="presentation2" name="presentation2">
            <option value="" {if $filtrePres2 == ''}selected{/if}>{#Selectionnez#}</option>
            <option value="voie" {if $filtrePres2 == 'voie'}selected{/if}>Channel</option>
            <option value="empty" {if $filtrePres2 == 'empty'}selected{/if}>Empty page</option>
            <optgroup label="Before game inlays">
              <option value="match" {if $filtrePres2 == 'match'}selected{/if}>Game (Category & teams)</option>
              <option value="list_team" {if $filtrePres2 == 'list_team'}selected{/if}>Players list</option>
              <option value="list_coachs" {if $filtrePres2 == 'list_coachs'}selected{/if}>Coaches list</option>
              <option value="team" {if $filtrePres2 == 'team'}selected{/if}>Team name</option>
              <option value="referee" {if $filtrePres2 == 'referee'}selected{/if}>Referees</option>
              <option value="player" {if $filtrePres2 == 'player'}selected{/if}>Player name</option>
              <option value="coach" {if $filtrePres2 == 'coach'}selected{/if}>Coach name</option>
            </optgroup>
            <optgroup label="Running game (nations)">
              <option value="score" {if $filtrePres2 == 'score'}selected{/if}>Live score (nations)</option>
              <option value="score_o" {if $filtrePres2 == 'score_o'}selected{/if}>Score only (nations)</option>
              <option value="score_e" {if $filtrePres2 == 'score_e'}selected{/if}>Events only (nations)</option>
              <option value="score_s" {if $filtrePres2 == 'score_s'}selected{/if}>Static events (nations)</option>
              <option value="teams" {if $filtrePres2 == 'teams'}selected{/if}>Game & score (nations)</option>
            </optgroup>
            <optgroup label="Running game (clubs)">
              <option value="score_club" {if $filtrePres2 == 'score_club'}selected{/if}>Live score (clubs)</option>
              <option value="score_club_o" {if $filtrePres2 == 'score_club_o'}selected{/if}>Score only (clubs)</option>
              <option value="score_club_e" {if $filtrePres2 == 'score_club_e'}selected{/if}>Events only (clubs)</option>
              <option value="score_club_s" {if $filtrePres2 == 'score_club_s'}selected{/if}>Static events (clubs)
              </option>
              <option value="teams_club" {if $filtrePres2 == 'teams_club'}selected{/if}>Game & score (clubs)</option>
              <option value="liveteams" {if $filtrePres2 == 'liveteams'}selected{/if}>Teams only (clubs)</option>
            </optgroup>
            <optgroup label="Game presentation (next game)">
              <option value="match_score" {if $filtrePres2 == 'match_score'}selected{/if}>Game & score</option>
            </optgroup>
            <optgroup label="After game inlays">
              {*
                <option value="list_medals" {if $filtrePres2 == 'list_medals'}selected{/if}>Medals (podium)</option>
                <option value="player_medal" {if $filtrePres2 == 'player_medal'}selected{/if}>Player medal</option>
                <option value="team_medal" {if $filtrePres2 == 'team_medal'}selected{/if}>Team medal</option>
              *}
              <option value="final_ranking" {if $filtrePres2 == 'final_ranking'}selected{/if}>Final ranking</option>
              <option value="podium" {if $filtrePres2 == 'podium'}selected{/if}>Podium</option>
            </optgroup>
            <optgroup label="Screen display">
              <option value="multi_score" {if $filtrePres2 == 'multi_score'}selected{/if}>Multi score</option>
              <option value="frame_categories" {if $filtrePres2 == 'frame_categories'}selected{/if}>Cat. games</option>
              <option value="frame_terrains" {if $filtrePres2 == 'frame_terrains'}selected{/if}>Pitch games</option>
              <option value="frame_chart" {if $filtrePres2 == 'frame_chart'}selected{/if}>Progress</option>
              <option value="frame_phases" {if $filtrePres2 == 'frame_phases'}selected{/if}>Phases</option>
              <option value="frame_details" {if $filtrePres2 == 'frame_details'}selected{/if}>Details</option>
              <option value="frame_team" {if $filtrePres2 == 'frame_team'}selected{/if}>Team details</option>
              <option value="frame_stats" {if $filtrePres2 == 'frame_stats'}selected{/if}>Stats</option>
              <option value="frame_classement" {if $filtrePres2 == 'frame_classement'}selected{/if}>Ranking</option>
              <option value="frame_qr" {if $filtrePres2 == 'frame_qr'}selected{/if}>QrCodes</option>
            </optgroup>

            <option value="force_cache_match" {if $filtrePres2 == 'force_cache_match'}selected{/if}>Force cache match
            </option>
          </select>
        </div>
        <div class='col-sm-2'>
          <label>{#Competition#}</label>
          <select id="competition2" name="competition2">
            <option value="" {if '' == $filtreCompet2}selected{/if}>{#Selectionnez#}</option>
            {section name=i loop=$arrayCompet}
              <option value="{$arrayCompet[i]}" {if $arrayCompet[i] == $filtreCompet2}selected{/if}>{$arrayCompet[i]}
              </option>
            {/section}
          </select>
        </div>
        <div class='col-sm-3 params2' id='match-col2'>
          <label>{#Match#}</label>
          <select id="match2" name="match2">
            {section name=i loop=$arrayMatchs2}
              <option value="{$arrayMatchs2[i].Id}" data-terrain="{$arrayMatchs2[i].Terrain}"
                {if $arrayMatchs2[i].Id == $filtreMatch2}selected{/if}>
                #{$arrayMatchs2[i].Numero_ordre} {#Terr#}.{$arrayMatchs2[i].Terrain} {$arrayMatchs2[i].Heure_match}
                : {$arrayMatchs2[i].equipeA} - {$arrayMatchs2[i].equipeB} [{$arrayMatchs2[i].Phase}]
              </option>
            {/section}
          </select>
          <input type="hidden" id="terrain2" name="terrain2" value="">
        </div>
        <div class='col-sm-1 params2' id='team-col2'>
          <label>{#Equipe#}</label>
          <select id="team2" name="team2">
            <option value="A">A</option>
            <option value="B">B</option>
          </select>
        </div>
        <div class='col-sm-2 params2' id='teamselect-col2'>
          <label>{#Equipe#}</label>
          <select id="teamselect2" name="teamselect2">
            {section name=i loop=$arrayEquipes2}
              <option value="{$arrayEquipes2[i].id_equipe}">{$arrayEquipes2[i].libelle_equipe}</option>
            {/section}
          </select>
        </div>
        <div class='col-sm-1 params2' id='number-col2'>
          <label>{#Joueur#}</label>
          <select id="number2" name="number2">
            {section name=i start=1 loop=20}
              <option value="{$smarty.section.i.index}">{$smarty.section.i.index}</option>
            {/section}
          </select>
        </div>
        <div class='col-sm-1 params2' id='medal-col2'>
          <label>{#Medaille#}</label>
          <select id="medal2" name="medal2">
            <option value="BRONZE">Bronze</option>
            <option value="SILVER">Silver</option>
            <option value="GOLD">Gold</option>
          </select>
        </div>
        <div class='col-sm-1 params2' id='count-col2'>
          <label>Count</label>
          <select id="count2" name="count2">
            <option value="4">4</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1</option>
          </select>
        </div>
        <div class='col-sm-1 params2' id='pitchs-col2'>
          <label>Pitchs</label>
          <input type="text" id="pitchs2" name="pitchs2" value="1,2,3,4">
        </div>
        <div class='col-sm-1 params2' id='round-col2'>
          <label>Round</label>
          <select id="round2" name="round2">
            <option value="*">All</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
          </select>
        </div>
        <div class='col-sm-1 params2' id='start-col2'>
          <label>Start</label>
          <select id="start2" name="start2">
            <option value="0">1-10</option>
            <option value="10">11-20</option>
            <option value="20">21-30</option>
            <option value="30">31-40</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='anime-col2'>
          <label>Animate</label>
          <select id="anime2" name="anime2">
            <option value="0">No</option>
            <option value="1">Yes</option>
          </select>
        </div>
        <div class='col-sm-1 params2' id='speaker-col2'>
          <label>Speaker</label>
          <select id="speaker2" name="speaker2">
            <option value="0">Non</option>
            <option value="1">Oui</option>
            <option value="2">Peut-être</option>
          </select>
        </div>
        <div class='col-sm-1 params2' id='lnstart-col2'>
          <label>Start</label>
          <input type="text" id="lnstart2" name="lnstart2" value="1" size="2">
        </div>
        <div class='col-sm-1 params2' id='lnlen-col2'>
          <label>Length</label>
          <input type="text" id="lnlen2" name="lnlen2" value="0" size="2">
        </div>
      </div>
      <div class="row">
        <a id="control2" class="btn btn-warning col-sm-1" href="live/tv2.php?voie={$filtreChannel2}" target="_blank">
          {#Controle#}
        </a>
        <a id="game_report2" class="params btn btn-info col-sm-1" href="" target="_blank">Report</a>
        <button id="getUrl2" name="getUrl2" class="btn btn-success col-sm-1" data-showurl="2">
          Url
        </button>
        <input type='text' readonly="readonly" id='showUrl2' name='showUrl2' class="col-sm-7">
        <button id="confirm2" name="confirm2" class="btn btn-primary pull-right col-sm-2" data-pres="" data-showurl="0">
          {#Activer#}
        </button>
      </div>
    </div>
    <div class="col-sm-2">
      <img id="img-presentation2" src="img/logo/2017-WG.jpg" class="img-rounded img-fluid">
    </div>
  </article>

  <!-- Article 3 -->
  <article>
    <div class="col-sm-10">
      <div class="row">
        <div class='col-sm-1'>
          <label>Channel</label>
          <select id="channel3" name="channel3">
            <option value='1'>{#Selectionnez#}</option>
            {section name=i start=1 loop=41}
              <option value="{$smarty.section.i.index}" {if $filtreChannel3 == $smarty.section.i.index}selected{/if}>
                {$smarty.section.i.index}
              </option>
            {/section}
          </select>
          <br>
        </div>
        <div class='col-sm-2'>
          <label>{#Presentation#}</label>
          <select id="presentation3" name="presentation3">
            <option value="" {if $filtrePres3 == ''}selected{/if}>{#Selectionnez#}</option>
            <option value="voie" {if $filtrePres3 == 'voie'}selected{/if}>Channel</option>
            <option value="empty" {if $filtrePres3 == 'empty'}selected{/if}>Empty page</option>
            <optgroup label="Before game inlays">
              <option value="match" {if $filtrePres3 == 'match'}selected{/if}>Game (Category & teams)</option>
              <option value="list_team" {if $filtrePres3 == 'list_team'}selected{/if}>Players list</option>
              <option value="list_coachs" {if $filtrePres3 == 'list_coachs'}selected{/if}>Coaches list</option>
              <option value="team" {if $filtrePres3 == 'team'}selected{/if}>Team name</option>
              <option value="referee" {if $filtrePres3 == 'referee'}selected{/if}>Referees</option>
              <option value="player" {if $filtrePres3 == 'player'}selected{/if}>Player name</option>
              <option value="coach" {if $filtrePres3 == 'coach'}selected{/if}>Coach name</option>
            </optgroup>
            <optgroup label="Running game (nations)">
              <option value="score" {if $filtrePres3 == 'score'}selected{/if}>Live score (nations)</option>
              <option value="score_o" {if $filtrePres3 == 'score_o'}selected{/if}>Score only (nations)</option>
              <option value="score_e" {if $filtrePres3 == 'score_e'}selected{/if}>Events only (nations)</option>
              <option value="score_s" {if $filtrePres3 == 'score_s'}selected{/if}>Static events (nations)</option>
              <option value="teams" {if $filtrePres3 == 'teams'}selected{/if}>Game & score (nations)</option>
            </optgroup>
            <optgroup label="Running game (clubs)">
              <option value="score_club" {if $filtrePres3 == 'score_club'}selected{/if}>Live score (clubs)</option>
              <option value="score_club_o" {if $filtrePres3 == 'score_club_o'}selected{/if}>Score only (clubs)</option>
              <option value="score_club_e" {if $filtrePres3 == 'score_club_e'}selected{/if}>Events only (clubs)</option>
              <option value="score_club_s" {if $filtrePres3 == 'score_club_s'}selected{/if}>Static events (clubs)
              </option>
              <option value="teams_club" {if $filtrePres3 == 'teams_club'}selected{/if}>Game & score (clubs)</option>
              <option value="liveteams" {if $filtrePres3 == 'liveteams'}selected{/if}>Teams only (clubs)</option>
            </optgroup>
            <optgroup label="Game presentation (next game)">
              <option value="match_score" {if $filtrePres3 == 'match_score'}selected{/if}>Game & score</option>
            </optgroup>
            <optgroup label="After game inlays">
              {*
                <option value="list_medals" {if $filtrePres3 == 'list_medals'}selected{/if}>Medals (podium)</option>
                <option value="player_medal" {if $filtrePres3 == 'player_medal'}selected{/if}>Player medal</option>
                <option value="team_medal" {if $filtrePres3 == 'team_medal'}selected{/if}>Team medal</option>
              *}
              <option value="final_ranking" {if $filtrePres3 == 'final_ranking'}selected{/if}>Final ranking</option>
              <option value="podium" {if $filtrePres3 == 'podium'}selected{/if}>Podium</option>
            </optgroup>
            <optgroup label="Screen display">
              <option value="multi_score" {if $filtrePres3 == 'multi_score'}selected{/if}>Multi score</option>
              <option value="frame_categories" {if $filtrePres3 == 'frame_categories'}selected{/if}>Cat. games</option>
              <option value="frame_terrains" {if $filtrePres3 == 'frame_terrains'}selected{/if}>Pitch games</option>
              <option value="frame_chart" {if $filtrePres3 == 'frame_chart'}selected{/if}>Progress</option>
              <option value="frame_phases" {if $filtrePres3 == 'frame_phases'}selected{/if}>Phases</option>
              <option value="frame_details" {if $filtrePres3 == 'frame_details'}selected{/if}>Details</option>
              <option value="frame_team" {if $filtrePres3 == 'frame_team'}selected{/if}>Team details</option>
              <option value="frame_stats" {if $filtrePres3 == 'frame_stats'}selected{/if}>Stats</option>
              <option value="frame_classement" {if $filtrePres3 == 'frame_classement'}selected{/if}>Ranking</option>
              <option value="frame_qr" {if $filtrePres3 == 'frame_qr'}selected{/if}>QrCodes</option>
            </optgroup>

            <option value="force_cache_match" {if $filtrePres3 == 'force_cache_match'}selected{/if}>Force cache match
            </option>
          </select>
        </div>
        <div class='col-sm-2'>
          <label>{#Competition#}</label>
          <select id="competition3" name="competition3">
            <option value="" {if '' == $filtreCompet3}selected{/if}>{#Selectionnez#}</option>
            {section name=i loop=$arrayCompet}
              <option value="{$arrayCompet[i]}" {if $arrayCompet[i] == $filtreCompet3}selected{/if}>{$arrayCompet[i]}
              </option>
            {/section}
          </select>
        </div>
        <div class='col-sm-3 params3' id='match-col3'>
          <label>{#Match#}</label>
          <select id="match3" name="match3">
            {section name=i loop=$arrayMatchs3}
              <option value="{$arrayMatchs3[i].Id}" data-terrain="{$arrayMatchs3[i].Terrain}"
                {if $arrayMatchs3[i].Id == $filtreMatch3}selected{/if}>
                #{$arrayMatchs3[i].Numero_ordre} {#Terr#}.{$arrayMatchs3[i].Terrain} {$arrayMatchs3[i].Heure_match}
                : {$arrayMatchs3[i].equipeA} - {$arrayMatchs3[i].equipeB} [{$arrayMatchs3[i].Phase}]
              </option>
            {/section}
          </select>
          <input type="hidden" id="terrain3" name="terrain3" value="">
        </div>
        <div class='col-sm-1 params3' id='team-col3'>
          <label>{#Equipe#}</label>
          <select id="team3" name="team3">
            <option value="A">A</option>
            <option value="B">B</option>
          </select>
        </div>
        <div class='col-sm-2 params3' id='teamselect-col3'>
          <label>{#Equipe#}</label>
          <select id="teamselect3" name="teamselect3">
            {section name=i loop=$arrayEquipes3}
              <option value="{$arrayEquipes3[i].id_equipe}">{$arrayEquipes3[i].libelle_equipe}</option>
            {/section}
          </select>
        </div>
        <div class='col-sm-1 params3' id='number-col3'>
          <label>{#Joueur#}</label>
          <select id="number3" name="number3">
            {section name=i start=1 loop=20}
              <option value="{$smarty.section.i.index}">{$smarty.section.i.index}</option>
            {/section}
          </select>
        </div>
        <div class='col-sm-1 params3' id='medal-col3'>
          <label>{#Medaille#}</label>
          <select id="medal3" name="medal3">
            <option value="BRONZE">Bronze</option>
            <option value="SILVER">Silver</option>
            <option value="GOLD">Gold</option>
          </select>
        </div>
        <div class='col-sm-1 params3' id='count-col3'>
          <label>Count</label>
          <select id="count3" name="count3">
            <option value="4">4</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1</option>
          </select>
        </div>
        <div class='col-sm-1 params3' id='pitchs-col3'>
          <label>Pitchs</label>
          <input type="text" id="pitchs3" name="pitchs3" value="1,2,3,4">
        </div>
        <div class='col-sm-1 params3' id='round-col3'>
          <label>Round</label>
          <select id="round3" name="round3">
            <option value="*">All</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
          </select>
        </div>
        <div class='col-sm-1 params3' id='start-col3'>
          <label>Start</label>
          <select id="start3" name="start3">
            <option value="0">1-10</option>
            <option value="10">11-20</option>
            <option value="20">21-30</option>
            <option value="30">31-40</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='anime-col3'>
          <label>Animate</label>
          <select id="anime3" name="anime3">
            <option value="0">No</option>
            <option value="1">Yes</option>
          </select>
        </div>
        <div class='col-sm-1 params3' id='speaker-col3'>
          <label>Speaker</label>
          <select id="speaker3" name="speaker3">
            <option value="0">Non</option>
            <option value="1">Oui</option>
            <option value="2">Peut-être</option>
          </select>
        </div>
        <div class='col-sm-1 params3' id='lnstart-col3'>
          <label>Start</label>
          <input type="text" id="lnstart3" name="lnstart3" value="1" size="2">
        </div>
        <div class='col-sm-1 params3' id='lnlen-col3'>
          <label>Length</label>
          <input type="text" id="lnlen3" name="lnlen3" value="0" size="2">
        </div>
      </div>
      <div class="row">
        <a id="control3" class="btn btn-warning col-sm-1" href="live/tv2.php?voie={$filtreChannel3}" target="_blank">
          {#Controle#}
        </a>
        <a id="game_report3" class="params btn btn-info col-sm-1" href="" target="_blank">Report</a>
        <button id="getUrl3" name="getUrl3" class="btn btn-success col-sm-1" data-showurl="3">
          Url
        </button>
        <input type='text' readonly="readonly" id='showUrl3' name='showUrl3' class="col-sm-7">
        <button id="confirm3" name="confirm3" class="btn btn-primary pull-right col-sm-2" data-pres="" data-showurl="0">
          {#Activer#}
        </button>
      </div>
    </div>
    <div class="col-sm-2">
      <img id="img-presentation3" src="img/logo/2017-WG.jpg" class="img-rounded img-fluid">
    </div>
  </article>

  <!-- Article 4 -->
  <article>
    <div class="col-sm-10">
      <div class="row">
        <div class='col-sm-1'>
          <label>Channel</label>
          <select id="channel4" name="channel4">
            <option value='1'>{#Selectionnez#}</option>
            {section name=i start=1 loop=41}
              <option value="{$smarty.section.i.index}" {if $filtreChannel4 == $smarty.section.i.index}selected{/if}>
                {$smarty.section.i.index}
              </option>
            {/section}
          </select>
          <br>
        </div>
        <div class='col-sm-2'>
          <label>{#Presentation#}</label>
          <select id="presentation4" name="presentation4">
            <option value="" {if $filtrePres4 == ''}selected{/if}>{#Selectionnez#}</option>
            <option value="voie" {if $filtrePres4 == 'voie'}selected{/if}>Channel</option>
            <option value="empty" {if $filtrePres4 == 'empty'}selected{/if}>Empty page</option>
            <optgroup label="Before game inlays">
              <option value="match" {if $filtrePres4 == 'match'}selected{/if}>Game (Category & teams)</option>
              <option value="list_team" {if $filtrePres4 == 'list_team'}selected{/if}>Players list</option>
              <option value="list_coachs" {if $filtrePres4 == 'list_coachs'}selected{/if}>Coaches list</option>
              <option value="team" {if $filtrePres4 == 'team'}selected{/if}>Team name</option>
              <option value="referee" {if $filtrePres4 == 'referee'}selected{/if}>Referees</option>
              <option value="player" {if $filtrePres4 == 'player'}selected{/if}>Player name</option>
              <option value="coach" {if $filtrePres4 == 'coach'}selected{/if}>Coach name</option>
            </optgroup>
            <optgroup label="Running game (nations)">
              <option value="score" {if $filtrePres4 == 'score'}selected{/if}>Live score (nations)</option>
              <option value="score_o" {if $filtrePres4 == 'score_o'}selected{/if}>Score only (nations)</option>
              <option value="score_e" {if $filtrePres4 == 'score_e'}selected{/if}>Events only (nations)</option>
              <option value="score_s" {if $filtrePres4 == 'score_s'}selected{/if}>Static events (nations)</option>
              <option value="teams" {if $filtrePres4 == 'teams'}selected{/if}>Game & score (nations)</option>
            </optgroup>
            <optgroup label="Running game (clubs)">
              <option value="score_club" {if $filtrePres4 == 'score_club'}selected{/if}>Live score (clubs)</option>
              <option value="score_club_o" {if $filtrePres4 == 'score_club_o'}selected{/if}>Score only (clubs)</option>
              <option value="score_club_e" {if $filtrePres4 == 'score_club_e'}selected{/if}>Events only (clubs)</option>
              <option value="score_club_s" {if $filtrePres4 == 'score_club_s'}selected{/if}>Static events (clubs)
              </option>
              <option value="teams_club" {if $filtrePres4 == 'teams_club'}selected{/if}>Game & score (clubs)</option>
              <option value="liveteams" {if $filtrePres4 == 'liveteams'}selected{/if}>Teams only (clubs)</option>
            </optgroup>
            <optgroup label="Game presentation (next game)">
              <option value="match_score" {if $filtrePres4 == 'match_score'}selected{/if}>Game & score</option>
            </optgroup>
            <optgroup label="After game inlays">
              {*                            
                <option value="list_medals" {if $filtrePres4 == 'list_medals'}selected{/if}>Medals (podium)</option>
                <option value="player_medal" {if $filtrePres4 == 'player_medal'}selected{/if}>Player medal</option>
                <option value="team_medal" {if $filtrePres4 == 'team_medal'}selected{/if}>Team medal</option>
              *}
              <option value="final_ranking" {if $filtrePres4 == 'final_ranking'}selected{/if}>Final ranking</option>
              <option value="podium" {if $filtrePres4 == 'podium'}selected{/if}>Podium</option>
            </optgroup>
            <optgroup label="Screen display">
              <option value="multi_score" {if $filtrePres4 == 'multi_score'}selected{/if}>Multi score</option>
              <option value="frame_categories" {if $filtrePres4 == 'frame_categories'}selected{/if}>Cat. games</option>
              <option value="frame_terrains" {if $filtrePres4 == 'frame_terrains'}selected{/if}>Pitch games</option>
              <option value="frame_chart" {if $filtrePres4 == 'frame_chart'}selected{/if}>Progress</option>
              <option value="frame_phases" {if $filtrePres4 == 'frame_phases'}selected{/if}>Phases</option>
              <option value="frame_details" {if $filtrePres4 == 'frame_details'}selected{/if}>Details</option>
              <option value="frame_team" {if $filtrePres4 == 'frame_team'}selected{/if}>Team details</option>
              <option value="frame_stats" {if $filtrePres4 == 'frame_stats'}selected{/if}>Stats</option>
              <option value="frame_classement" {if $filtrePres4 == 'frame_classement'}selected{/if}>Ranking</option>
              <option value="frame_qr" {if $filtrePres4 == 'frame_qr'}selected{/if}>QrCodes</option>
            </optgroup>

            <option value="force_cache_match" {if $filtrePres4 == 'force_cache_match'}selected{/if}>Force cache match
            </option>
          </select>
        </div>
        <div class='col-sm-2'>
          <label>{#Competition#}</label>
          <select id="competition4" name="competition4">
            <option value="" {if '' == $filtreCompet4}selected{/if}>{#Selectionnez#}</option>
            {section name=i loop=$arrayCompet}
              <option value="{$arrayCompet[i]}" {if $arrayCompet[i] == $filtreCompet4}selected{/if}>{$arrayCompet[i]}
              </option>
            {/section}
          </select>
        </div>
        <div class='col-sm-3 params4' id='match-col4'>
          <label>{#Match#}</label>
          <select id="match4" name="match4">
            {section name=i loop=$arrayMatchs4}
              <option value="{$arrayMatchs4[i].Id}" data-terrain="{$arrayMatchs4[i].Terrain}"
                {if $arrayMatchs4[i].Id == $filtreMatch4}selected{/if}>
                #{$arrayMatchs4[i].Numero_ordre} {#Terr#}.{$arrayMatchs4[i].Terrain} {$arrayMatchs4[i].Heure_match}
                : {$arrayMatchs4[i].equipeA} - {$arrayMatchs4[i].equipeB} [{$arrayMatchs4[i].Phase}]
              </option>
            {/section}
          </select>
          <input type="hidden" id="terrain4" name="terrain4" value="">
        </div>
        <div class='col-sm-1 params4' id='team-col4'>
          <label>{#Equipe#}</label>
          <select id="team4" name="team4">
            <option value="A">A</option>
            <option value="B">B</option>
          </select>
        </div>
        <div class='col-sm-2 params4' id='teamselect-col4'>
          <label>{#Equipe#}</label>
          <select id="teamselect4" name="teamselect4">
            {section name=i loop=$arrayEquipes4}
              <option value="{$arrayEquipes4[i].id_equipe}">{$arrayEquipes4[i].libelle_equipe}</option>
            {/section}
          </select>
        </div>
        <div class='col-sm-1 params4' id='number-col4'>
          <label>{#Joueur#}</label>
          <select id="number4" name="number4">
            {section name=i start=1 loop=20}
              <option value="{$smarty.section.i.index}">{$smarty.section.i.index}</option>
            {/section}
          </select>
        </div>
        <div class='col-sm-1 params4' id='medal-col4'>
          <label>{#Medaille#}</label>
          <select id="medal4" name="medal4">
            <option value="BRONZE">Bronze</option>
            <option value="SILVER">Silver</option>
            <option value="GOLD">Gold</option>
          </select>
        </div>
        <div class='col-sm-1 params4' id='count-col4'>
          <label>Count</label>
          <select id="count4" name="count4">
            <option value="4">4</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1</option>
          </select>
        </div>
        <div class='col-sm-1 params4' id='pitchs-col4'>
          <label>Pitchs</label>
          <input type="text" id="pitchs4" name="pitchs4" value="1,2,3,4">
        </div>
        <div class='col-sm-1 params4' id='round-col4'>
          <label>Round</label>
          <select id="round4" name="round4">
            <option value="*">All</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
          </select>
        </div>
        <div class='col-sm-1 params4' id='start-col4'>
          <label>Start</label>
          <select id="start4" name="start4">
            <option value="0">1-10</option>
            <option value="10">11-20</option>
            <option value="20">21-30</option>
            <option value="30">31-40</option>
          </select>
        </div>
        <div class='col-sm-1 params' id='anime-col4'>
          <label>Animate</label>
          <select id="anime4" name="anime4">
            <option value="0">No</option>
            <option value="1">Yes</option>
          </select>
        </div>
        <div class='col-sm-1 params4' id='speaker-col4'>
          <label>Speaker</label>
          <select id="speaker4" name="speaker4">
            <option value="0">Non</option>
            <option value="1">Oui</option>
            <option value="2">Peut-être</option>
          </select>
        </div>
        <div class='col-sm-1 params4' id='lnstart-col4'>
          <label>Start</label>
          <input type="text" id="lnstart4" name="lnstart4" value="1" size="2">
        </div>
        <div class='col-sm-1 params4' id='lnlen-col4'>
          <label>Length</label>
          <input type="text" id="lnlen4" name="lnlen4" value="0" size="2">
        </div>
      </div>
      <div class="row">
        <a id="control4" class="btn btn-warning col-sm-1" href="live/tv2.php?voie={$filtreChannel4}" target="_blank">
          {#Controle#}
        </a>
        <a id="game_report4" class="params btn btn-info col-sm-1" href="" target="_blank">Report</a>
        <button id="getUrl4" name="getUrl4" class="btn btn-success col-sm-1" data-showurl="4">
          Url
        </button>
        <input type='text' readonly="readonly" id='showUrl4' name='showUrl4' class="col-sm-7">
        <button id="confirm4" name="confirm4" class="btn btn-primary pull-right col-sm-2" data-pres="" data-showurl="0">
          {#Activer#}
        </button>
      </div>
    </div>
    <div class="col-sm-2">
      <img id="img-presentation4" src="img/logo/2017-WG.jpg" class="img-rounded img-fluid">
    </div>
  </article>

  <!-- Liens -->
  <article>
    <a id="event_params" class="btn btn-primary" href="live/event.php" target="_blank">
      Event cache generator
    </a>
    &nbsp;
    <a id="scenario_live_params" class="btn btn-default pull-right" href="live/scenario.php" target="_blank">
      Scenario Live
    </a>
    &nbsp;
    <a id="split_params" class="btn btn-default pull-right" href="live/spliturl.php" target="_blank">
      Split Url
    </a>
    &nbsp;
    <a id="scenario_params" class="btn btn-default" href="kptvscenario.php" target="_blank">
      Scenario
    </a>

  </article>
</div>

<!-- Modal -->
{*<div class="modal fade" tabindex="-1" role="dialog" id="msgModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p id="msg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->*}

<div id="msg" class="alert alert-danger alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
  <p>Bienvenue !</p>
</div>