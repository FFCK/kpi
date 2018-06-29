{if $navGroup}
    <div class="container-fluid categorie">
        <div class="col-md-12">
            <a class="btn btn-default actif"
                href="kpmatchs.php?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round=*&Css={$Css}&navGroup=1">
                {#Matchs#}
            </a>
            <a class="btn btn-primary">{#Classement_par_phase#}</a>
            <a class="btn btn-default actif" 
                href="frame_classement.php?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Css={$Css}&navGroup=1">
                        {#Classement#}
            </a>
            <a class="btn btn-default actif" 
                href="frame_stats.php?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Css={$Css}&navGroup=1">
                        {#Stats#}
            </a>
            <div class="pull-right">
                {section name=i loop=$arrayNavGroup}
                    {if $arrayNavGroup[i].Code == $codeCompet}
                        <a class="btn btn-primary">{$arrayNavGroup[i].Soustitre2}</a>
                    {else}
                        <a class="btn btn-default actif" 
                           href="?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$arrayNavGroup[i].Code}&Round={$Round}&Css={$Css}&navGroup=1">
                            {$arrayNavGroup[i].Soustitre2}
                        </a>
                    {/if}
                {sectionelse}
                    <h2 class="col-md-12">
                        {$recordCompetition.Soustitre2}
                    </h2>
                {/section}
            </div>
        </div>
    </div>
{else}
    <div class="container categorie">
        <h2 class="col-md-12">
            {$recordCompetition.Soustitre2}
        </h2>
    </div>
{/if}
<div class="container">
    {if $recordCompetition.Code_typeclt == 'CP'}
        {section name=i loop=$arrayJournee}
            {assign var='idJournee' value=$arrayJournee[i].Id_journee}
            {if $arrayJournee[i].Type == 'C'}
                <article class="padTopBottom table-responsive col-md-6 col-sm-12 tablePhase">
                    <h4>{$arrayJournee[i].Phase}</h4>
                    <table class='table table-striped table-condensed table-hover'>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{#Equipes#}</th>
                                <th>{#Pts#}</th>
                                <th>{#J#}</th>
                                <th>{#G#}</th>
                                <th>{#N#}</th>
                                <th>{#P#}</th>
{*                                <th>{#F#}</th>*}
                                <th>+</th>
                                <th>-</th>
                                <th>{#Diff#}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {section name=j loop=$arrayEquipe_journee_publi[$idJournee]}
                                <tr>
                                    <td>
                                        {$arrayEquipe_journee_publi[$idJournee][j].Clt}
                                        {if $arrayEquipe_journee_publi[$idJournee][j].logo != ''}
                                            <img class="img2 pull-right" width="30" src="{$arrayEquipe_journee_publi[$idJournee][j].logo}" alt="{$arrayEquipe_journee_publi[$idJournee][j].club}" />
                                        {/if}

                                    </td>
                                    <td>
                                        <a class="btn btn-xs btn-default equipe">{$arrayEquipe_journee_publi[$idJournee][j].Libelle}</a>
                                    </td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Pts/100}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].J}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].G}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].N}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].P}</td>
{*                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].F}</td>*}
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Plus}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Moins}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Diff}</td>
                                </tr>
                            {/section}
                        </tbody>
                    </table>
                </article>
            {elseif $arrayMatchs[$idJournee]|@count > 0}
                <article class="padTopBottom table-responsive col-md-4 col-md-offset-4 tableMatch">
                    <h4 class="row text-center">{$arrayJournee[i].Phase}</h4>
                    {section name=j loop=$arrayMatchs[$idJournee]}
                        <div class="row cliquableNomEquipe">
                            {if $arrayMatchs[$idJournee][j].ScoreA > $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-primary equipe">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-primary score">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default score">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default equipe">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {elseif $arrayMatchs[$idJournee][j].ScoreA < $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default equipe">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default score">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-primary score">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-primary equipe">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {else}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default equipe">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default score">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default score">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default equipe">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {/if}
                        </div>
                    {/section}
                </article>
            {/if}
        {/section}
    {else}
        {section name=i loop=$arrayJournee}
            {assign var='idJournee' value=$arrayJournee[i].Id_journee}
                <article class="padTopBottom table-responsive col-md-12 tableJournee">
                    <div class="page-header">
                        <h4>
                            {$arrayJournee[i].Lieu} ({$arrayJournee[i].Departement}) {$arrayJournee[i].Date_debut|date_format:'%d/%m/%Y'} - {$arrayJournee[i].Date_fin|date_format:'%d/%m/%Y'}
                        </h4>
                    </div>
                    {section name=j loop=$arrayMatchs[$idJournee]}
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            {if $arrayMatchs[$idJournee][j].ScoreA > $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {elseif $arrayMatchs[$idJournee][j].ScoreA < $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {else}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {/if}
                        </div>
                    {/section}
                </article>
        {/section}
        
    {/if}
</div>
{if $voie}
    <script type="text/javascript" src="js/voie.js?v={$NUM_VERSION}" ></script>
    <script type="text/javascript">SetVoie({$voie});</script>
{/if}