<div class="container-fluid" id="selector">
    <article id="titre">
        <form id="filtre_evt" name="filtre_evt" method="post" action="">
            <h1 class='col-sm-5'>{#Controle_tv#}</h1>
            <div class='col-sm-4'>
                <label>{#Evenement#}</label>
                <select id="codeEvt" name="codeEvt">
                    <option value="0">{#Selectionnez#}</option>
                    {section name=i loop=$arrayEvts}
                        <option value="{$arrayEvts[i].Id}" {$arrayEvts[i].selected}>{$arrayEvts[i].Id}-{$arrayEvts[i].Libelle} ({$arrayEvts[i].Lieu})</option>
                    {/section}
                </select>
            </div>
            <div class='col-sm-2'>
                <label>{#Date#}</label>
                <select id="jour" name="jour">
                    <option value="0">{#Toutes#}</option>
                    {section name=i loop=$arrayJours}
                        <option value="{$arrayJours[i]}" {if $arrayJours[i] == $jour}selected{/if}>{$arrayJours[i]}</option>
                    {/section}
                </select>
                <input type="hidden" id="filtreCompet" name="filtreCompet" value="{$filtreCompet}">
                <input type="hidden" id="filtreChannel" name="filtreChannel" value="{$filtreChannel}">
                <input type="hidden" id="filtrePres" name="filtrePres" value="{$filtrePres}">
            </div>
            <div class="col-sm-1">
                <a href="live/score.php?voie=1" target="_blank" class="btn btn-warning">Controle</a>
            </div>
        </form>
    </article>
    <article>
        <div class='col-sm-1'>
            <label>Channel</label>
            <select id="channel" name="channel">
                {section name=i start=1 loop=10}
                    <option value="{$smarty.section.i.index}" {if $filtreChannel == $smarty.section.i.index}selected{/if}>
                        {$smarty.section.i.index}
                    </option>
                {/section}
            </select>
        </div>
        <div class='col-sm-2'>
            <label>{#Presentation#}</label>
            <select id="presentation" name="presentation">
                <option value="" {if $filtrePres == ''}selected{/if}>{#Selectionnez#}</option>
                <option value="list_medals" {if $filtrePres == 'list_medals'}selected{/if}>Medals</option>
                <option value="referee" {if $filtrePres == 'referee'}selected{/if}>Referees</option>
                <option value="player" {if $filtrePres == 'player'}selected{/if}>Player</option>
                <option value="player_medal" {if $filtrePres == 'player_medal'}selected{/if}>Player medal</option>
                <option value="team" {if $filtrePres == 'team'}selected{/if}>Team</option>
                <option value="team_medal" {if $filtrePres == 'team_medal'}selected{/if}>Team medal</option>
                <option value="match" {if $filtrePres == 'match'}selected{/if}>Game</option>
                <option value="match_score" {if $filtrePres == 'match_score'}selected{/if}>Game & score</option>
                <option value="list_team" {if $filtrePres == 'list_team'}selected{/if}>List team</option>
                
                <option value="score" {if $filtrePres == 'score'}selected{/if}>Score</option>
                <option value="multi_score" {if $filtrePres == 'multi_score'}selected{/if}>Multi score</option>
                <option value="multi_score_2" {if $filtrePres == 'multi_score_2'}selected{/if}>Multi score 2</option>
                <option value="schema" {if $filtrePres == 'schema'}selected{/if}>Schema</option>
                <option value="frame_terrains" {if $filtrePres == 'frame_terrains'}selected{/if}>Terrains</option>
            </select>
        </div>
        <div class='col-sm-1'>
            <label>{#Competition#}</label>
            <select id="competition" name="competition">
                {section name=i loop=$arrayCompet}
                    <option value="{$arrayCompet[i]}" {if $arrayCompet[i] == $filtreCompet}selected{/if}>{$arrayCompet[i]}</option>
                {/section}
            </select>
        </div>
        <div class='col-sm-3'>
            <label>{#Match#}</label>
            <select id="match" name="match" class="params">
                {section name=i loop=$arrayMatchs}
                    <option value="{$arrayMatchs[i].Id}">
                        #{$arrayMatchs[i].Numero_ordre} ({$arrayMatchs[i].Heure_match})
                        : {$arrayMatchs[i].equipeA} - {$arrayMatchs[i].equipeB}
                    </option>
                {/section}
            </select>
        </div>
        <div class='col-sm-1'>
            <label>{#Equipe#}</label>
            <select id="team" name="team" class="params">
                <option value="A">A</option>
                <option value="B">B</option>
            </select>
        </div>
        <div class='col-sm-1'>
            <label>{#Joueur#}</label>
            <select id="number" name="number" class="params">
                {section name=i start=1 loop=20}
                    <option value="{$smarty.section.i.index}">{$smarty.section.i.index}</option>
                {/section}
            </select>
        </div>
        <div class='col-sm-1'>
            <label>{#Medaille#}</label>
            <select id="medal" name="medal" class="params">
                <option value="BRONZE">Bronze</option>
                <option value="SILVER">Silver</option>
                <option value="GOLD">Gold</option>
            </select>
        </div>
        <div class='col-sm-2'>
            <button id="confirm" name="confirm" class="btn btn-lg btn-primary" data-pres="">
                <label>{#Activer#}</label>
                <img src="img/logo/2017-WG.jpg" class="img-rounded img-fluid">
            </button>
        </div>
        <br>
        <br>
    </article>
</div>

<!-- Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="msgModal">
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
</div><!-- /.modal -->