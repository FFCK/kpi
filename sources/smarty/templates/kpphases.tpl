{include file='kpnavgroup.tpl'}
        
{if $recordCompetition.Code_typeclt == 'CP'}
    <div class="container">
        {assign var='idJournee' value=$arrayListJournees[0]}
        {assign var='niveau' value=$arrayJournees[$idJournee].Niveau}
        {assign var='compteur' value=0}
        <article class="col-md-12">
            {section name=i loop=$arrayListJournees}
                {assign var='idJournee' value=$arrayListJournees[i]}
                {if isset($arrayListJournees[i.index_next])}
                    {assign var='idJourneeNext' value=$arrayListJournees[i.index_next]}
                {else}
                    {assign var='idJourneeNext' value=null}
                {/if}
                {if $niveau != $arrayJournees[$idJournee].Niveau}
                    </article><article class="col-md-12">
                {/if}
                {assign var='niveau' value=$arrayJournees[$idJournee].Niveau}
                {if $arrayJournees[$idJournee].Type == 'C'}
                    {assign var='compteur' value=$compteur+1}
                    {if $compteur == 2}
                        {assign var='compteur' value=0}
                    {/if}
                    {if $idJourneeNext !== null && $niveau != $arrayJournees[$idJourneeNext].Niveau && $compteur == 1}
                        <div class="padTopBottom table-responsive col-md-6 col-md-offset-3 col-sm-12 tablePhase">
                        {assign var='compteur' value=0}
                    {else}
                        <div class="padTopBottom table-responsive col-md-6 col-sm-12 tablePhase">
                    {/if}
                        <h4>{$arrayJournees[$idJournee].Phase}</h4>
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
                                {if $arrayJournees[$idJournee].Actif == 1}
                                    {section name=j loop=$arrayEquipe_journee_publi[$idJournee]}
                                        <tr>
                                            <td>
                                                {$arrayEquipe_journee_publi[$idJournee][j].Clt}
                                            </td>
                                            <td class="cliquableNomEquipe">
                                                <a class="btn btn-xs btn-default"{if $arrayEquipe_journee_publi[$idJournee][j].Numero > 0} href="kpequipes.php?Equipe={$arrayEquipe_journee_publi[$idJournee][j].Numero}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayEquipe_journee_publi[$idJournee][j].Libelle}</a>
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
                                {else}
                                    {if $arrayEquipes[$idJournee]}
                                        {foreach from=$arrayEquipes[$idJournee] key=myId item=j}
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <a class="btn btn-xs btn-default"{if $j.Num > 0} href="frame_equipes.php?Equipe={$j.Num}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$j.Libelle}</a>
                                                </td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
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
                                                <td></td>
                                                <td></td>
                                                <td></td>
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
                                    <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                        <a class="btn btn-xs btn-primary"{if $arrayMatchs[$idJournee][j].NumA > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                        <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                        <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                        <a class="btn btn-xs btn-default"{if $arrayMatchs[$idJournee][j].NumB > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                    </div>
                                {elseif $arrayMatchs[$idJournee][j].ScoreA < $arrayMatchs[$idJournee][j].ScoreB}
                                    <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                        <a class="btn btn-xs btn-default"{if $arrayMatchs[$idJournee][j].NumA > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                        <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                        <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                        <a class="btn btn-xs btn-primary"{if $arrayMatchs[$idJournee][j].NumB > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                    </div>
                                {else}
                                    <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                        <a class="btn btn-xs btn-default"{if $arrayMatchs[$idJournee][j].NumA > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                        <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                        <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                        <a class="btn btn-xs btn-default"{if $arrayMatchs[$idJournee][j].NumB > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                    </div>
                                {/if}
                            </div>
                        {/section}
                    </div>
                {/if}
            {/section}
        </article>
    </div>
{else}
    <div class="container-fluid">
        {section name=i loop=$arrayJournee}
            {assign var='idJournee' value=$arrayJournee[i].Id_journee}
                <article class="padTopBottom table-responsive col-md-12 tableJournee">
                    <div class="page-header">
                        <h4>
                            {$arrayJournee[i].Lieu} ({$arrayJournee[i].Departement}) {$arrayJournee[i].Date_debut|date_format:'%d/%m/%Y'} - {$arrayJournee[i].Date_fin|date_format:'%d/%m/%Y'}
                            <a class="btn btn-xs btn-default pull-right" href="kpdetails.php?Saison={$Saison}&Group={$recordCompetition.Code_ref}&Compet={$codeCompet}&typ={$recordCompetition.Code_typeclt}&J={$arrayJournee[i].Id_journee}" title="{#Infos#}">{#Infos#}</a>
                        </h4>
                    </div>
                    {section name=j loop=$arrayMatchs[$idJournee]}
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            {if $arrayMatchs[$idJournee][j].ScoreA > $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-primary"{if $arrayMatchs[$idJournee][j].NumA > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default"{if $arrayMatchs[$idJournee][j].NumB > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {elseif $arrayMatchs[$idJournee][j].ScoreA < $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default"{if $arrayMatchs[$idJournee][j].NumA > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-primary"{if $arrayMatchs[$idJournee][j].NumB > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {else}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default"{if $arrayMatchs[$idJournee][j].NumA > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default"{if $arrayMatchs[$idJournee][j].NumB > 0} href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}"{/if}>{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {/if}
                        </div>
                    {/section}
                </article>
        {/section}
    </div>
{/if}
