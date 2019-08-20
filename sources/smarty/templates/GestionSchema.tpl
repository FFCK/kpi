<div class="container-fluid text-center titre" id="navTitle">
    <div class="col-md-12">
        <h2 class="col-md-12">
            <a class="btn btn-default pull-left" href="javascript:close();">{#Fermer#}</a>
            <span class="label label-primary pull-right">{$Saison}</span>
            {if $event > 0}
                <span>{$eventTitle}</span>
            {elseif '*' == $codeCompet}
                {$arrayNavGroup[0].Soustitre|default:$arrayNavGroup[0].Libelle}
            {elseif $recordCompetition.Titre_actif != 'O' && $recordCompetition.Soustitre2 != ''}
                <span>{$recordCompetition.Soustitre}</span>
            {else}
                <span>{$recordCompetition.Libelle}</span>
            {/if}
        </h2>
    </div>
</div>
<div class="container-fluid">
    {if $recordCompetition.Code_typeclt == 'CP'}
        {assign var='idJournee' value=$arrayListJournees[0]}
        {assign var='Etape' value=$arrayJournees[$idJournee].Etape}
        <article class="col-md-{$largeur}">
        {section name=i loop=$arrayListJournees}
            {assign var='idJournee' value=$arrayListJournees[i]}
            {if $Etape != $arrayJournees[$idJournee].Etape}
                </article><article class="col-md-{$largeur}">
            {/if}
            {assign var='Etape' value=$arrayJournees[$idJournee].Etape}
            {if $arrayJournees[$idJournee].Type == 'C'}
                <div class="padTopBottom table-responsive col-md-12 tablePhase">
                    <h4 class="row text-center">{$arrayJournees[$idJournee].Phase}</h4>
                    <table class='table table-striped table-condensed table-hover'>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{#Equipes#}</th>
                                <th>{#Pts#}</th>
                                <th>{#Diff#}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {if $arrayJournees[$idJournee].Actif == 1}
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
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Diff}</td>
                                </tr>
                            {/section}
                            {else}
                                {if $arrayEquipes[$idJournee]}
                                    {foreach from=$arrayEquipes[$idJournee] key=myId item=j}
                                        <tr>
                                            <td></td>
                                            <td>
                                                <a class="btn btn-xs btn-default equipe">{$j.Libelle}</a>
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    {section name=k loop=$arrayJournees[$idJournee].Nbequipes}
                                        <tr>
                                            <td>{$smarty.section.k.iteration}</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    {/section}
                                {/if}
                            {/if}
                        </tbody>
                    </table>
                </div>
            {elseif $arrayMatchs[$idJournee]|@count > 0}
                <div class="padTopBottom table-responsive col-md-12 tableMatch">
                    <h4 class="row text-center">{$arrayJournees[$idJournee].Phase}</h4>
                    {section name=j loop=$arrayMatchs[$idJournee]}
                        {if $smarty.section.j.index is odd}
                            <br>
                        {/if}
                        <div class="row cliquableNomEquipe">
                            {if $arrayMatchs[$idJournee][j].ScoreA > $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-12 text-right">
                                    <a class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-12 text-right">
                                    <a class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                </div>
                            {elseif $arrayMatchs[$idJournee][j].ScoreA < $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-12 text-right">
                                    <a class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                    <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                </div>
                                <div class="col-md-12 text-right">
                                    <a class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                            {else}
                                <div class="col-md-12 text-right">
                                    <a class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-12 text-right">
                                    <a class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                </div>
                            {/if}
                        </div>
                    {/section}
                </div>
            {/if}
        {/section}
        </article>
    {else}
        {section name=i loop=$arrayJournee}
            {assign var='idJournee' value=$arrayJournee[i].Id_journee}
                <article class="padTopBottom table-responsive col-md-12 tableJournee">
                    <div class="page-header">
                        <h4>
                            {$arrayJournee[i].Lieu} ({$arrayJournee[i].Departement}) {$arrayJournee[i].Date_debut|date_format:'%d/%m/%Y'} - {$arrayJournee[i].Date_fin|date_format:'%d/%m/%Y'}
{*                            <a class="btn btn-xs btn-default pull-right" href="kpdetails.php?Saison={$Saison}&Group={$recordCompetition.Code_ref}&Compet={$codeCompet}&typ={$recordCompetition.Code_typeclt}&J={$arrayJournee[i].Id_journee}" title="{#Infos#}">{#Infos#}</a>*}
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
